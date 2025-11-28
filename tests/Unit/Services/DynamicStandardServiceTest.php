<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Aspect;
use App\Models\AssessmentTemplate;
use App\Models\CategoryType;
use App\Models\CustomStandard;
use App\Models\Institution;
use App\Models\SubAspect;
use App\Services\DynamicStandardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

/**
 * DynamicStandardService Unit Tests
 *
 * Testing the 3-layer priority system:
 * 1. Session Adjustment (temporary, logout → hilang)
 * 2. Custom Standard (persistent, saved to DB)
 * 3. Quantum Default (from aspects/sub_aspects table)
 *
 * PHASE 1: ✅ Priority Chain Tests (8/8 tests) - COMPLETE
 * PHASE 2: ✅ Data-Driven Rating Tests (4/4 tests) - COMPLETE
 * PHASE 3: ✅ Session Management Tests (5/5 tests) - COMPLETE
 * PHASE 4: ✅ Active/Inactive Tests (4/4 tests) - COMPLETE
 * PHASE 5: ✅ Validation Tests (5/5 tests) - COMPLETE
 * PHASE 6: ✅ Edge Cases & Additional Tests (14/14 tests) - COMPLETE
 *
 * TOTAL: 40/50 tests (80% coverage) ✅
 *
 * @see \App\Services\DynamicStandardService
 * @see docs/TESTING_GUIDE.md
 * @see docs/CUSTOM_STANDARD_FEATURE.md
 */
class DynamicStandardServiceTest extends TestCase
{
    use RefreshDatabase;

    private DynamicStandardService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(DynamicStandardService::class);
    }

    // ========================================
    // PHASE 1: PRIORITY CHAIN TESTS (15-20 tests)
    // ========================================

    /**
     * Test: Returns session adjustment when it exists (highest priority)
     *
     * Priority chain: Session > Custom > Quantum
     * When session adjustment exists, it should always win
     */
    public function test_returns_session_adjustment_when_exists(): void
    {
        // Arrange: Create quantum default
        $template = $this->createTemplate();
        $aspect = Aspect::create([
            'template_id' => $template->id,
            'category_type_id' => $this->createCategory($template->id, 'potensi')->id,
            'code' => 'asp_01',
            'name' => 'Test Aspect',
            'weight_percentage' => 10, // Quantum default
            'standard_rating' => 3.0,
            'order' => 1,
        ]);

        // Setup custom standard
        $institution = $this->createInstitution();
        $customStandard = CustomStandard::create([
            'institution_id' => $institution->id,
            'template_id' => $template->id,
            'code' => 'CS_001',
            'name' => 'Custom Standard 1',
            'category_weights' => ['potensi' => 60, 'kompetensi' => 40],
            'aspect_configs' => [
                'asp_01' => ['weight' => 12, 'active' => true], // Custom value
            ],
            'sub_aspect_configs' => [],
        ]);
        Session::put("selected_standard.{$template->id}", $customStandard->id);

        // Setup session adjustment (highest priority)
        $this->service->saveAspectWeight($template->id, 'asp_01', 15);

        // Act: Get aspect weight
        $result = $this->service->getAspectWeight($template->id, 'asp_01');

        // Assert: Session wins (15)
        $this->assertEquals(15, $result);
    }

    /**
     * Test: Returns custom standard when no session adjustment exists
     *
     * Priority chain: Session > Custom > Quantum
     * When no session adjustment, custom standard should be used
     */
    public function test_returns_custom_standard_when_no_session_adjustment(): void
    {
        // Arrange: Create quantum default
        $template = $this->createTemplate();
        $aspect = Aspect::create([
            'template_id' => $template->id,
            'category_type_id' => $this->createCategory($template->id, 'potensi')->id,
            'code' => 'asp_01',
            'name' => 'Test Aspect',
            'weight_percentage' => 10, // Quantum default
            'standard_rating' => 3.0,
            'order' => 1,
        ]);

        // Setup custom standard
        $institution = $this->createInstitution();
        $customStandard = CustomStandard::create([
            'institution_id' => $institution->id,
            'template_id' => $template->id,
            'code' => 'CS_001',
            'name' => 'Custom Standard 1',
            'category_weights' => ['potensi' => 60, 'kompetensi' => 40],
            'aspect_configs' => [
                'asp_01' => ['weight' => 12, 'active' => true], // Custom value
            ],
            'sub_aspect_configs' => [],
        ]);
        Session::put("selected_standard.{$template->id}", $customStandard->id);

        // No session adjustment

        // Act: Get aspect weight
        $result = $this->service->getAspectWeight($template->id, 'asp_01');

        // Assert: Custom standard wins (12)
        $this->assertEquals(12, $result);
    }

    /**
     * Test: Returns quantum default when no adjustments exist
     *
     * Priority chain: Session > Custom > Quantum
     * When no session and no custom standard, quantum default is used
     */
    public function test_returns_quantum_default_when_no_adjustments(): void
    {
        // Arrange: Create quantum default only
        $template = $this->createTemplate();
        $aspect = Aspect::create([
            'template_id' => $template->id,
            'category_type_id' => $this->createCategory($template->id, 'potensi')->id,
            'code' => 'asp_01',
            'name' => 'Test Aspect',
            'weight_percentage' => 10, // Quantum default
            'standard_rating' => 3.0,
            'order' => 1,
        ]);

        // No custom standard selected
        // No session adjustment

        // Act: Get aspect weight
        $result = $this->service->getAspectWeight($template->id, 'asp_01');

        // Assert: Quantum default wins (10)
        $this->assertEquals(10, $result);
    }

    /**
     * Test: Priority chain for aspect rating (Session > Custom > Quantum)
     */
    public function test_priority_chain_for_aspect_rating(): void
    {
        // Arrange: Create quantum default
        $template = $this->createTemplate();
        $aspect = Aspect::create([
            'template_id' => $template->id,
            'category_type_id' => $this->createCategory($template->id, 'kompetensi')->id,
            'code' => 'asp_kom_01',
            'name' => 'Test Kompetensi',
            'weight_percentage' => 15,
            'standard_rating' => 3.0, // Quantum default (no sub-aspects)
            'order' => 1,
        ]);

        // Setup custom standard
        $institution = $this->createInstitution();
        $customStandard = CustomStandard::create([
            'institution_id' => $institution->id,
            'template_id' => $template->id,
            'code' => 'CS_001',
            'name' => 'Custom Standard 1',
            'category_weights' => ['potensi' => 50, 'kompetensi' => 50],
            'aspect_configs' => [
                'asp_kom_01' => ['weight' => 15, 'rating' => 3.5, 'active' => true], // Custom rating
            ],
            'sub_aspect_configs' => [],
        ]);
        Session::put("selected_standard.{$template->id}", $customStandard->id);

        // Setup session adjustment (highest priority)
        $this->service->saveAspectRating($template->id, 'asp_kom_01', 4.0);

        // Act: Get aspect rating
        $result = $this->service->getAspectRating($template->id, 'asp_kom_01');

        // Assert: Session wins (4.0)
        $this->assertEquals(4.0, $result);
    }

    /**
     * Test: Priority chain for sub-aspect rating (Session > Custom > Quantum)
     */
    public function test_priority_chain_for_sub_aspect_rating(): void
    {
        // Arrange: Create quantum default
        $template = $this->createTemplate();
        $category = $this->createCategory($template->id, 'potensi');
        $aspect = Aspect::create([
            'template_id' => $template->id,
            'category_type_id' => $category->id,
            'code' => 'asp_kecerdasan',
            'name' => 'Kecerdasan',
            'weight_percentage' => 25,
            'standard_rating' => null, // Has sub-aspects
            'order' => 1,
        ]);

        $subAspect = SubAspect::create([
            'aspect_id' => $aspect->id,
            'code' => 'sub_01',
            'name' => 'Sub Aspect 1',
            'standard_rating' => 3, // Quantum default
            'order' => 1,
        ]);

        // Setup custom standard
        $institution = $this->createInstitution();
        $customStandard = CustomStandard::create([
            'institution_id' => $institution->id,
            'template_id' => $template->id,
            'code' => 'CS_001',
            'name' => 'Custom Standard 1',
            'category_weights' => ['potensi' => 60, 'kompetensi' => 40],
            'aspect_configs' => [
                'asp_kecerdasan' => ['weight' => 25, 'active' => true],
            ],
            'sub_aspect_configs' => [
                'sub_01' => ['rating' => 4, 'active' => true], // Custom rating
            ],
        ]);
        Session::put("selected_standard.{$template->id}", $customStandard->id);

        // Setup session adjustment (highest priority)
        $this->service->saveSubAspectRating($template->id, 'sub_01', 5);

        // Act: Get sub-aspect rating
        $result = $this->service->getSubAspectRating($template->id, 'sub_01');

        // Assert: Session wins (5)
        $this->assertEquals(5, $result);
    }

    /**
     * Test: Priority chain for category weight (Session > Custom > Quantum)
     */
    public function test_priority_chain_for_category_weight(): void
    {
        // Arrange: Create quantum default
        $template = $this->createTemplate();
        $category = $this->createCategory($template->id, 'potensi', 50); // Quantum default: 50%

        // Setup custom standard
        $institution = $this->createInstitution();
        $customStandard = CustomStandard::create([
            'institution_id' => $institution->id,
            'template_id' => $template->id,
            'code' => 'CS_001',
            'name' => 'Custom Standard 1',
            'category_weights' => ['potensi' => 60, 'kompetensi' => 40], // Custom: 60%
            'aspect_configs' => [],
            'sub_aspect_configs' => [],
        ]);
        Session::put("selected_standard.{$template->id}", $customStandard->id);

        // Setup session adjustment (highest priority)
        $this->service->saveCategoryWeight($template->id, 'potensi', 70);

        // Act: Get category weight
        $result = $this->service->getCategoryWeight($template->id, 'potensi');

        // Assert: Session wins (70)
        $this->assertEquals(70, $result);
    }

    /**
     * Test: Priority chain for aspect active status (Session > Custom > Default true)
     */
    public function test_priority_chain_for_aspect_active_status(): void
    {
        // Arrange
        $template = $this->createTemplate();
        $aspect = Aspect::create([
            'template_id' => $template->id,
            'category_type_id' => $this->createCategory($template->id, 'potensi')->id,
            'code' => 'asp_01',
            'name' => 'Test Aspect',
            'weight_percentage' => 10,
            'standard_rating' => 3.0,
            'order' => 1,
        ]);

        // Setup custom standard (active: true)
        $institution = $this->createInstitution();
        $customStandard = CustomStandard::create([
            'institution_id' => $institution->id,
            'template_id' => $template->id,
            'code' => 'CS_001',
            'name' => 'Custom Standard 1',
            'category_weights' => ['potensi' => 50, 'kompetensi' => 50],
            'aspect_configs' => [
                'asp_01' => ['weight' => 10, 'active' => true], // Custom: active
            ],
            'sub_aspect_configs' => [],
        ]);
        Session::put("selected_standard.{$template->id}", $customStandard->id);

        // Setup session adjustment (inactive)
        $this->service->setAspectActive($template->id, 'asp_01', false);

        // Act: Check if active
        $result = $this->service->isAspectActive($template->id, 'asp_01');

        // Assert: Session wins (false)
        $this->assertFalse($result);
    }

    /**
     * Test: Priority chain for sub-aspect active status (Session > Custom > Default true)
     */
    public function test_priority_chain_for_sub_aspect_active_status(): void
    {
        // Arrange
        $template = $this->createTemplate();
        $aspect = Aspect::create([
            'template_id' => $template->id,
            'category_type_id' => $this->createCategory($template->id, 'potensi')->id,
            'code' => 'asp_kecerdasan',
            'name' => 'Kecerdasan',
            'weight_percentage' => 25,
            'standard_rating' => null,
            'order' => 1,
        ]);

        $subAspect = SubAspect::create([
            'aspect_id' => $aspect->id,
            'code' => 'sub_01',
            'name' => 'Sub Aspect 1',
            'standard_rating' => 3,
            'order' => 1,
        ]);

        // Setup custom standard (active: true)
        $institution = $this->createInstitution();
        $customStandard = CustomStandard::create([
            'institution_id' => $institution->id,
            'template_id' => $template->id,
            'code' => 'CS_001',
            'name' => 'Custom Standard 1',
            'category_weights' => ['potensi' => 50, 'kompetensi' => 50],
            'aspect_configs' => [
                'asp_kecerdasan' => ['weight' => 25, 'active' => true],
            ],
            'sub_aspect_configs' => [
                'sub_01' => ['rating' => 3, 'active' => true], // Custom: active
            ],
        ]);
        Session::put("selected_standard.{$template->id}", $customStandard->id);

        // Setup session adjustment (inactive)
        $this->service->setSubAspectActive($template->id, 'sub_01', false);

        // Act: Check if active
        $result = $this->service->isSubAspectActive($template->id, 'sub_01');

        // Assert: Session wins (false)
        $this->assertFalse($result);
    }

    // ========================================
    // PHASE 2: DATA-DRIVEN RATING TESTS (10-15 tests)
    // ========================================

    /**
     * Test: Calculates aspect rating from sub-aspects (WITH sub-aspects)
     *
     * DATA-DRIVEN: Aspect has sub-aspects → rating = average of active sub-aspects
     */
    public function test_calculates_aspect_rating_from_sub_aspects(): void
    {
        // Arrange: Create aspect WITH sub-aspects
        $template = $this->createTemplate();
        $category = $this->createCategory($template->id, 'potensi');
        $aspect = Aspect::create([
            'template_id' => $template->id,
            'category_type_id' => $category->id,
            'code' => 'asp_kecerdasan',
            'name' => 'Kecerdasan',
            'weight_percentage' => 25,
            'standard_rating' => null, // NULL because has sub-aspects
            'order' => 1,
        ]);

        // Create 3 sub-aspects
        SubAspect::create([
            'aspect_id' => $aspect->id,
            'code' => 'sub_01',
            'name' => 'Sub 1',
            'standard_rating' => 3,
            'order' => 1,
        ]);
        SubAspect::create([
            'aspect_id' => $aspect->id,
            'code' => 'sub_02',
            'name' => 'Sub 2',
            'standard_rating' => 4,
            'order' => 2,
        ]);
        SubAspect::create([
            'aspect_id' => $aspect->id,
            'code' => 'sub_03',
            'name' => 'Sub 3',
            'standard_rating' => 5,
            'order' => 3,
        ]);

        // Act: Get aspect rating (should calculate from sub-aspects)
        $result = $this->service->getAspectRating($template->id, 'asp_kecerdasan');

        // Assert: (3 + 4 + 5) / 3 = 4.0
        $this->assertEquals(4.0, $result);
    }

    /**
     * Test: Uses direct rating when aspect has NO sub-aspects
     *
     * DATA-DRIVEN: Aspect has NO sub-aspects → use aspect.standard_rating directly
     */
    public function test_uses_direct_rating_when_no_sub_aspects(): void
    {
        // Arrange: Create aspect WITHOUT sub-aspects (Kompetensi)
        $template = $this->createTemplate();
        $category = $this->createCategory($template->id, 'kompetensi');
        $aspect = Aspect::create([
            'template_id' => $template->id,
            'category_type_id' => $category->id,
            'code' => 'asp_integritas',
            'name' => 'Integritas',
            'weight_percentage' => 15,
            'standard_rating' => 4.0, // Direct value (no sub-aspects)
            'order' => 1,
        ]);

        // Act: Get aspect rating (should use direct value)
        $result = $this->service->getAspectRating($template->id, 'asp_integritas');

        // Assert: Should return 4.0 directly
        $this->assertEquals(4.0, $result);
    }

    /**
     * Test: Filters inactive sub-aspects from calculation
     *
     * DATA-DRIVEN: Only ACTIVE sub-aspects should be included in average
     */
    public function test_filters_inactive_sub_aspects_from_calculation(): void
    {
        // Arrange: Create aspect with 3 sub-aspects
        $template = $this->createTemplate();
        $category = $this->createCategory($template->id, 'potensi');
        $aspect = Aspect::create([
            'template_id' => $template->id,
            'category_type_id' => $category->id,
            'code' => 'asp_kecerdasan',
            'name' => 'Kecerdasan',
            'weight_percentage' => 25,
            'standard_rating' => null,
            'order' => 1,
        ]);

        // Sub 1: 3 (active), Sub 2: 4 (INACTIVE), Sub 3: 5 (active)
        SubAspect::create([
            'aspect_id' => $aspect->id,
            'code' => 'sub_01',
            'name' => 'Sub 1',
            'standard_rating' => 3,
            'order' => 1,
        ]);
        SubAspect::create([
            'aspect_id' => $aspect->id,
            'code' => 'sub_02',
            'name' => 'Sub 2',
            'standard_rating' => 4,
            'order' => 2,
        ]);
        SubAspect::create([
            'aspect_id' => $aspect->id,
            'code' => 'sub_03',
            'name' => 'Sub 3',
            'standard_rating' => 5,
            'order' => 3,
        ]);

        // Set sub_02 as inactive
        $this->service->setSubAspectActive($template->id, 'sub_02', false);

        // Act: Get aspect rating (should skip inactive sub_02)
        $result = $this->service->getAspectRating($template->id, 'asp_kecerdasan');

        // Assert: (3 + 5) / 2 = 4.0 (skips sub_02 with rating 4)
        $this->assertEquals(4.0, $result);
    }

    /**
     * Test: Custom standard calculates aspect rating from its sub-aspect configs
     *
     * DATA-DRIVEN: Custom standard should also calculate from sub-aspects
     */
    public function test_custom_standard_calculates_rating_from_sub_aspects(): void
    {
        // Arrange: Create quantum structure with sub-aspects
        $template = $this->createTemplate();
        $category = $this->createCategory($template->id, 'potensi');
        $aspect = Aspect::create([
            'template_id' => $template->id,
            'category_type_id' => $category->id,
            'code' => 'asp_kecerdasan',
            'name' => 'Kecerdasan',
            'weight_percentage' => 25,
            'standard_rating' => null,
            'order' => 1,
        ]);

        // Create sub-aspects in quantum
        SubAspect::create([
            'aspect_id' => $aspect->id,
            'code' => 'sub_01',
            'name' => 'Sub 1',
            'standard_rating' => 3, // Quantum default
            'order' => 1,
        ]);
        SubAspect::create([
            'aspect_id' => $aspect->id,
            'code' => 'sub_02',
            'name' => 'Sub 2',
            'standard_rating' => 3, // Quantum default
            'order' => 2,
        ]);
        SubAspect::create([
            'aspect_id' => $aspect->id,
            'code' => 'sub_03',
            'name' => 'Sub 3',
            'standard_rating' => 3, // Quantum default
            'order' => 3,
        ]);

        // Create custom standard with different sub-aspect ratings
        $institution = $this->createInstitution();
        $customStandard = CustomStandard::create([
            'institution_id' => $institution->id,
            'template_id' => $template->id,
            'code' => 'CS_001',
            'name' => 'Custom Standard 1',
            'category_weights' => ['potensi' => 50, 'kompetensi' => 50],
            'aspect_configs' => [
                'asp_kecerdasan' => ['weight' => 25, 'active' => true],
            ],
            'sub_aspect_configs' => [
                'sub_01' => ['rating' => 4, 'active' => true], // Custom ratings
                'sub_02' => ['rating' => 4, 'active' => true],
                'sub_03' => ['rating' => 5, 'active' => true],
            ],
        ]);
        Session::put("selected_standard.{$template->id}", $customStandard->id);

        // Act: Get aspect rating (should calculate from custom standard's sub-aspects)
        $result = $this->service->getAspectRating($template->id, 'asp_kecerdasan');

        // Assert: (4 + 4 + 5) / 3 = 4.33
        $this->assertEquals(4.33, round($result, 2));
    }

    // ========================================
    // PHASE 3: SESSION MANAGEMENT TESTS (8-10 tests)
    // ========================================

    /**
     * Test: Saves adjustment only when different from original
     *
     * Session should only save if value differs from baseline
     */
    public function test_saves_adjustment_when_different_from_original(): void
    {
        // Arrange
        $template = $this->createTemplate();
        Aspect::create([
            'template_id' => $template->id,
            'category_type_id' => $this->createCategory($template->id, 'potensi')->id,
            'code' => 'asp_01',
            'name' => 'Test Aspect',
            'weight_percentage' => 10, // Original
            'standard_rating' => 3.0,
            'order' => 1,
        ]);

        // Act: Save different value (15 != 10)
        $this->service->saveAspectWeight($template->id, 'asp_01', 15);

        // Assert: Should be saved in session
        $adjustments = $this->service->getAdjustments($template->id);
        $this->assertArrayHasKey('aspect_weights', $adjustments);
        $this->assertEquals(15, $adjustments['aspect_weights']['asp_01']);
    }

    /**
     * Test: Does not save when value equals original
     *
     * If value same as original, don't save to session (optimization)
     */
    public function test_does_not_save_when_equals_original(): void
    {
        // Arrange
        $template = $this->createTemplate();
        Aspect::create([
            'template_id' => $template->id,
            'category_type_id' => $this->createCategory($template->id, 'potensi')->id,
            'code' => 'asp_01',
            'name' => 'Test Aspect',
            'weight_percentage' => 10, // Original
            'standard_rating' => 3.0,
            'order' => 1,
        ]);

        // Act: Save same value (10 == 10)
        $this->service->saveAspectWeight($template->id, 'asp_01', 10);

        // Assert: Should NOT be saved in session
        $adjustments = $this->service->getAdjustments($template->id);
        $this->assertArrayNotHasKey('aspect_weights', $adjustments);
    }

    /**
     * Test: Compares against custom standard when selected
     *
     * When custom standard is selected, comparison should be against custom, not quantum
     */
    public function test_compares_against_custom_standard_when_selected(): void
    {
        // Arrange: Quantum = 10, Custom = 12
        $template = $this->createTemplate();
        Aspect::create([
            'template_id' => $template->id,
            'category_type_id' => $this->createCategory($template->id, 'potensi')->id,
            'code' => 'asp_01',
            'name' => 'Test Aspect',
            'weight_percentage' => 10, // Quantum
            'standard_rating' => 3.0,
            'order' => 1,
        ]);

        // Setup custom standard
        $institution = $this->createInstitution();
        $customStandard = CustomStandard::create([
            'institution_id' => $institution->id,
            'template_id' => $template->id,
            'code' => 'CS_001',
            'name' => 'Custom Standard 1',
            'category_weights' => ['potensi' => 50, 'kompetensi' => 50],
            'aspect_configs' => [
                'asp_01' => ['weight' => 12, 'active' => true], // Custom baseline
            ],
            'sub_aspect_configs' => [],
        ]);
        Session::put("selected_standard.{$template->id}", $customStandard->id);

        // Act: Save value = 12 (equals custom, not quantum)
        $this->service->saveAspectWeight($template->id, 'asp_01', 12);

        // Assert: Should NOT be saved (equals custom baseline)
        $adjustments = $this->service->getAdjustments($template->id);
        $this->assertArrayNotHasKey('aspect_weights', $adjustments);
    }

    /**
     * Test: Removes adjustment from session when reset to original
     *
     * If user changes value then changes back, session should be cleaned up
     */
    public function test_removes_adjustment_when_reset_to_original(): void
    {
        // Arrange
        $template = $this->createTemplate();
        Aspect::create([
            'template_id' => $template->id,
            'category_type_id' => $this->createCategory($template->id, 'potensi')->id,
            'code' => 'asp_01',
            'name' => 'Test Aspect',
            'weight_percentage' => 10,
            'standard_rating' => 3.0,
            'order' => 1,
        ]);

        // Act 1: Save different value
        $this->service->saveAspectWeight($template->id, 'asp_01', 15);
        $adjustments1 = $this->service->getAdjustments($template->id);
        $this->assertArrayHasKey('aspect_weights', $adjustments1);

        // Act 2: Reset to original (10)
        $this->service->saveAspectWeight($template->id, 'asp_01', 10);

        // Assert: Should be removed from session
        $adjustments2 = $this->service->getAdjustments($template->id);
        $this->assertArrayNotHasKey('aspect_weights', $adjustments2);
    }

    /**
     * Test: Session is completely forgotten when all adjustments removed
     *
     * When no adjustments remain, session key should be deleted (memory optimization)
     */
    public function test_forgets_session_when_all_adjustments_removed(): void
    {
        // Arrange
        $template = $this->createTemplate();
        Aspect::create([
            'template_id' => $template->id,
            'category_type_id' => $this->createCategory($template->id, 'potensi')->id,
            'code' => 'asp_01',
            'name' => 'Test Aspect',
            'weight_percentage' => 10,
            'standard_rating' => 3.0,
            'order' => 1,
        ]);

        // Act 1: Save adjustment
        $this->service->saveAspectWeight($template->id, 'asp_01', 15);

        // Verify session exists
        $this->assertTrue(Session::has("standard_adjustment.{$template->id}"));

        // Act 2: Remove adjustment
        $this->service->saveAspectWeight($template->id, 'asp_01', 10);

        // Assert: Session key should be deleted
        $this->assertFalse(Session::has("standard_adjustment.{$template->id}"));
    }

    // ========================================
    // PHASE 4: ACTIVE/INACTIVE TESTS (8-10 tests)
    // ========================================

    /**
     * Test: Defaults to active when no adjustments
     *
     * All aspects/sub-aspects are active by default
     */
    public function test_defaults_to_active_when_no_adjustments(): void
    {
        // Arrange
        $template = $this->createTemplate();

        // Act: Check aspect active status (no adjustments)
        $result = $this->service->isAspectActive($template->id, 'asp_01');

        // Assert: Should be active (default)
        $this->assertTrue($result);
    }

    /**
     * Test: Sets weight to zero when aspect set inactive
     *
     * Inactive aspect should have weight = 0
     */
    public function test_sets_weight_to_zero_when_aspect_set_inactive(): void
    {
        // Arrange
        $template = $this->createTemplate();
        Aspect::create([
            'template_id' => $template->id,
            'category_type_id' => $this->createCategory($template->id, 'potensi')->id,
            'code' => 'asp_01',
            'name' => 'Test Aspect',
            'weight_percentage' => 10,
            'standard_rating' => 3.0,
            'order' => 1,
        ]);

        // Act: Set aspect inactive
        $this->service->setAspectActive($template->id, 'asp_01', false);

        // Assert: Weight should be 0
        $this->assertEquals(0, $this->service->getAspectWeight($template->id, 'asp_01'));
        $this->assertFalse($this->service->isAspectActive($template->id, 'asp_01'));
    }

    /**
     * Test: Removes inactive flag when set back to active
     *
     * Setting aspect to active should remove adjustment (return to default)
     */
    public function test_removes_inactive_flag_when_set_active(): void
    {
        // Arrange
        $template = $this->createTemplate();
        Aspect::create([
            'template_id' => $template->id,
            'category_type_id' => $this->createCategory($template->id, 'potensi')->id,
            'code' => 'asp_01',
            'name' => 'Test Aspect',
            'weight_percentage' => 10,
            'standard_rating' => 3.0,
            'order' => 1,
        ]);

        // Act 1: Set inactive
        $this->service->setAspectActive($template->id, 'asp_01', false);
        $adjustments1 = $this->service->getAdjustments($template->id);
        $this->assertArrayHasKey('active_aspects', $adjustments1);

        // Act 2: Set back to active
        $this->service->setAspectActive($template->id, 'asp_01', true);

        // Assert: Should be removed from session
        $adjustments2 = $this->service->getAdjustments($template->id);
        $this->assertArrayNotHasKey('active_aspects', $adjustments2);
    }

    /**
     * Test: Sub-aspect inactive flag is managed correctly
     */
    public function test_manages_sub_aspect_inactive_flag(): void
    {
        // Arrange
        $template = $this->createTemplate();
        $aspect = Aspect::create([
            'template_id' => $template->id,
            'category_type_id' => $this->createCategory($template->id, 'potensi')->id,
            'code' => 'asp_kecerdasan',
            'name' => 'Kecerdasan',
            'weight_percentage' => 25,
            'standard_rating' => null,
            'order' => 1,
        ]);

        SubAspect::create([
            'aspect_id' => $aspect->id,
            'code' => 'sub_01',
            'name' => 'Sub 1',
            'standard_rating' => 3,
            'order' => 1,
        ]);

        // Act 1: Set inactive
        $this->service->setSubAspectActive($template->id, 'sub_01', false);
        $this->assertFalse($this->service->isSubAspectActive($template->id, 'sub_01'));

        // Act 2: Set back to active
        $this->service->setSubAspectActive($template->id, 'sub_01', true);
        $this->assertTrue($this->service->isSubAspectActive($template->id, 'sub_01'));

        // Assert: Session should be cleaned
        $adjustments = $this->service->getAdjustments($template->id);
        $this->assertArrayNotHasKey('active_sub_aspects', $adjustments);
    }

    // ========================================
    // PHASE 5: VALIDATION TESTS (5-8 tests)
    // ========================================

    /**
     * Test: Validates category weights must sum to 100
     */
    public function test_validates_category_weights_sum_to_100(): void
    {
        // Arrange
        $template = $this->createTemplate();
        $this->createCategory($template->id, 'potensi', 50);
        $this->createCategory($template->id, 'kompetensi', 50);

        // Act & Assert: Should throw exception for invalid total
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Total bobot kategori harus 100%');

        $this->service->saveBothCategoryWeights(
            $template->id,
            'potensi',
            60,
            'kompetensi',
            30 // Total: 90 (invalid)
        );
    }

    /**
     * Test: Accepts category weights that sum to 100
     */
    public function test_accepts_category_weights_sum_to_100(): void
    {
        // Arrange
        $template = $this->createTemplate();
        $this->createCategory($template->id, 'potensi', 50);
        $this->createCategory($template->id, 'kompetensi', 50);

        // Act: Save valid weights (should not throw)
        $this->service->saveBothCategoryWeights(
            $template->id,
            'potensi',
            60,
            'kompetensi',
            40 // Total: 100 (valid)
        );

        // Assert: Should be saved
        $this->assertEquals(60, $this->service->getCategoryWeight($template->id, 'potensi'));
        $this->assertEquals(40, $this->service->getCategoryWeight($template->id, 'kompetensi'));
    }

    /**
     * Test: Validates rating range 1-5 for aspects
     */
    public function test_validates_aspect_rating_between_1_and_5(): void
    {
        // Arrange
        $adjustments = [
            'aspect_ratings' => [
                'asp_01' => 6, // Invalid (> 5)
            ],
        ];

        // Act: Validate
        $errors = $this->service->validateAdjustments($adjustments);

        // Assert: Should have error
        $this->assertNotEmpty($errors);
        $this->assertArrayHasKey('aspect_ratings.asp_01', $errors);
    }

    /**
     * Test: Validates rating range 1-5 for sub-aspects
     */
    public function test_validates_sub_aspect_rating_between_1_and_5(): void
    {
        // Arrange
        $adjustments = [
            'sub_aspect_ratings' => [
                'sub_01' => 0, // Invalid (< 1)
            ],
        ];

        // Act: Validate
        $errors = $this->service->validateAdjustments($adjustments);

        // Assert: Should have error
        $this->assertNotEmpty($errors);
        $this->assertArrayHasKey('sub_aspect_ratings.sub_01', $errors);
    }

    /**
     * Test: Accepts valid rating values
     */
    public function test_accepts_valid_rating_values(): void
    {
        // Arrange
        $adjustments = [
            'aspect_ratings' => [
                'asp_01' => 3.5,
                'asp_02' => 5.0,
            ],
            'sub_aspect_ratings' => [
                'sub_01' => 1,
                'sub_02' => 5,
            ],
        ];

        // Act: Validate
        $errors = $this->service->validateAdjustments($adjustments);

        // Assert: Should have no errors
        $this->assertEmpty($errors);
    }

    // ========================================
    // PHASE 6: EDGE CASES & ADDITIONAL TESTS (10-15 tests)
    // ========================================

    /**
     * Test: Returns empty array when no active aspects
     */
    public function test_returns_empty_array_when_no_active_aspects(): void
    {
        // Arrange
        $template = $this->createTemplate();
        $category = $this->createCategory($template->id, 'potensi');
        Aspect::create([
            'template_id' => $template->id,
            'category_type_id' => $category->id,
            'code' => 'asp_01',
            'name' => 'Test Aspect',
            'weight_percentage' => 10,
            'standard_rating' => 3.0,
            'order' => 1,
        ]);

        // Set aspect as inactive
        $this->service->setAspectActive($template->id, 'asp_01', false);

        // Act: Get active aspect IDs
        $result = $this->service->getActiveAspectIds($template->id, 'potensi');

        // Assert: Should return empty array
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /**
     * Test: Returns all aspect IDs when all are active
     */
    public function test_returns_all_aspect_ids_when_all_active(): void
    {
        // Arrange
        $template = $this->createTemplate();
        $category = $this->createCategory($template->id, 'potensi');

        $aspect1 = Aspect::create([
            'template_id' => $template->id,
            'category_type_id' => $category->id,
            'code' => 'asp_01',
            'name' => 'Aspect 1',
            'weight_percentage' => 10,
            'standard_rating' => 3.0,
            'order' => 1,
        ]);

        $aspect2 = Aspect::create([
            'template_id' => $template->id,
            'category_type_id' => $category->id,
            'code' => 'asp_02',
            'name' => 'Aspect 2',
            'weight_percentage' => 15,
            'standard_rating' => 4.0,
            'order' => 2,
        ]);

        // Act: Get active aspect IDs (all active by default)
        $result = $this->service->getActiveAspectIds($template->id, 'potensi');

        // Assert: Should return both IDs
        $this->assertCount(2, $result);
        $this->assertContains($aspect1->id, $result);
        $this->assertContains($aspect2->id, $result);
    }

    /**
     * Test: Filters out inactive aspects from active IDs list
     */
    public function test_filters_inactive_aspects_from_active_ids(): void
    {
        // Arrange
        $template = $this->createTemplate();
        $category = $this->createCategory($template->id, 'potensi');

        $aspect1 = Aspect::create([
            'template_id' => $template->id,
            'category_type_id' => $category->id,
            'code' => 'asp_01',
            'name' => 'Aspect 1',
            'weight_percentage' => 10,
            'standard_rating' => 3.0,
            'order' => 1,
        ]);

        $aspect2 = Aspect::create([
            'template_id' => $template->id,
            'category_type_id' => $category->id,
            'code' => 'asp_02',
            'name' => 'Aspect 2',
            'weight_percentage' => 15,
            'standard_rating' => 4.0,
            'order' => 2,
        ]);

        // Set aspect2 as inactive
        $this->service->setAspectActive($template->id, 'asp_02', false);

        // Act: Get active aspect IDs
        $result = $this->service->getActiveAspectIds($template->id, 'potensi');

        // Assert: Should only return aspect1 ID
        $this->assertCount(1, $result);
        $this->assertContains($aspect1->id, $result);
        $this->assertNotContains($aspect2->id, $result);
    }

    /**
     * Test: Handles aspect with zero weight correctly
     */
    public function test_handles_aspect_with_zero_weight(): void
    {
        // Arrange
        $template = $this->createTemplate();
        Aspect::create([
            'template_id' => $template->id,
            'category_type_id' => $this->createCategory($template->id, 'potensi')->id,
            'code' => 'asp_01',
            'name' => 'Test Aspect',
            'weight_percentage' => 10,
            'standard_rating' => 3.0,
            'order' => 1,
        ]);

        // Act: Save weight as 0
        $this->service->saveAspectWeight($template->id, 'asp_01', 0);

        // Assert: Should save and return 0
        $result = $this->service->getAspectWeight($template->id, 'asp_01');
        $this->assertEquals(0, $result);
    }

    /**
     * Test: Returns zero for non-existent aspect
     */
    public function test_returns_zero_for_nonexistent_aspect(): void
    {
        // Arrange
        $template = $this->createTemplate();

        // Act: Get weight for non-existent aspect
        $result = $this->service->getAspectWeight($template->id, 'asp_nonexistent');

        // Assert: Should return 0
        $this->assertEquals(0, $result);
    }

    /**
     * Test: Returns zero rating for non-existent aspect
     */
    public function test_returns_zero_rating_for_nonexistent_aspect(): void
    {
        // Arrange
        $template = $this->createTemplate();

        // Act: Get rating for non-existent aspect
        $result = $this->service->getAspectRating($template->id, 'asp_nonexistent');

        // Assert: Should return 0.0
        $this->assertEquals(0.0, $result);
    }

    /**
     * Test: Handles aspect with one sub-aspect correctly
     */
    public function test_handles_aspect_with_one_sub_aspect(): void
    {
        // Arrange
        $template = $this->createTemplate();
        $category = $this->createCategory($template->id, 'potensi');
        $aspect = Aspect::create([
            'template_id' => $template->id,
            'category_type_id' => $category->id,
            'code' => 'asp_kecerdasan',
            'name' => 'Kecerdasan',
            'weight_percentage' => 25,
            'standard_rating' => null,
            'order' => 1,
        ]);

        // Create only 1 sub-aspect
        SubAspect::create([
            'aspect_id' => $aspect->id,
            'code' => 'sub_01',
            'name' => 'Sub 1',
            'standard_rating' => 4,
            'order' => 1,
        ]);

        // Act: Get aspect rating
        $result = $this->service->getAspectRating($template->id, 'asp_kecerdasan');

        // Assert: Should return 4.0 (average of 1 value)
        $this->assertEquals(4.0, $result);
    }

    /**
     * Test: Session adjustment persists across multiple calls
     */
    public function test_session_adjustment_persists_across_calls(): void
    {
        // Arrange
        $template = $this->createTemplate();
        Aspect::create([
            'template_id' => $template->id,
            'category_type_id' => $this->createCategory($template->id, 'potensi')->id,
            'code' => 'asp_01',
            'name' => 'Test Aspect',
            'weight_percentage' => 10,
            'standard_rating' => 3.0,
            'order' => 1,
        ]);

        // Act: Save session adjustment
        $this->service->saveAspectWeight($template->id, 'asp_01', 15);

        // Call multiple times
        $result1 = $this->service->getAspectWeight($template->id, 'asp_01');
        $result2 = $this->service->getAspectWeight($template->id, 'asp_01');
        $result3 = $this->service->getAspectWeight($template->id, 'asp_01');

        // Assert: All calls should return same session value
        $this->assertEquals(15, $result1);
        $this->assertEquals(15, $result2);
        $this->assertEquals(15, $result3);
    }

    /**
     * Test: Multiple session adjustments for different aspects
     */
    public function test_multiple_session_adjustments_for_different_aspects(): void
    {
        // Arrange
        $template = $this->createTemplate();
        $category = $this->createCategory($template->id, 'potensi');

        Aspect::create([
            'template_id' => $template->id,
            'category_type_id' => $category->id,
            'code' => 'asp_01',
            'name' => 'Aspect 1',
            'weight_percentage' => 10,
            'standard_rating' => 3.0,
            'order' => 1,
        ]);

        Aspect::create([
            'template_id' => $template->id,
            'category_type_id' => $category->id,
            'code' => 'asp_02',
            'name' => 'Aspect 2',
            'weight_percentage' => 15,
            'standard_rating' => 4.0,
            'order' => 2,
        ]);

        // Act: Save different adjustments
        $this->service->saveAspectWeight($template->id, 'asp_01', 12);
        $this->service->saveAspectWeight($template->id, 'asp_02', 18);

        // Assert: Each should return its own adjusted value
        $this->assertEquals(12, $this->service->getAspectWeight($template->id, 'asp_01'));
        $this->assertEquals(18, $this->service->getAspectWeight($template->id, 'asp_02'));
    }

    /**
     * Test: Custom standard respects active/inactive flags for sub-aspects
     */
    public function test_custom_standard_respects_inactive_sub_aspects(): void
    {
        // Arrange
        $template = $this->createTemplate();
        $category = $this->createCategory($template->id, 'potensi');
        $aspect = Aspect::create([
            'template_id' => $template->id,
            'category_type_id' => $category->id,
            'code' => 'asp_kecerdasan',
            'name' => 'Kecerdasan',
            'weight_percentage' => 25,
            'standard_rating' => null,
            'order' => 1,
        ]);

        SubAspect::create([
            'aspect_id' => $aspect->id,
            'code' => 'sub_01',
            'name' => 'Sub 1',
            'standard_rating' => 3,
            'order' => 1,
        ]);
        SubAspect::create([
            'aspect_id' => $aspect->id,
            'code' => 'sub_02',
            'name' => 'Sub 2',
            'standard_rating' => 5,
            'order' => 2,
        ]);

        // Create custom standard with sub_02 inactive
        $institution = $this->createInstitution();
        $customStandard = CustomStandard::create([
            'institution_id' => $institution->id,
            'template_id' => $template->id,
            'code' => 'CS_001',
            'name' => 'Custom Standard 1',
            'category_weights' => ['potensi' => 50, 'kompetensi' => 50],
            'aspect_configs' => [
                'asp_kecerdasan' => ['weight' => 25, 'active' => true],
            ],
            'sub_aspect_configs' => [
                'sub_01' => ['rating' => 3, 'active' => true],
                'sub_02' => ['rating' => 5, 'active' => false], // INACTIVE
            ],
        ]);
        Session::put("selected_standard.{$template->id}", $customStandard->id);

        // Act: Get aspect rating (should only count active sub-aspect)
        $result = $this->service->getAspectRating($template->id, 'asp_kecerdasan');

        // Assert: Should only use sub_01 (3.0), not sub_02
        $this->assertEquals(3.0, $result);
    }

    /**
     * Test: Clearing session resets to baseline
     */
    public function test_clearing_session_resets_to_baseline(): void
    {
        // Arrange
        $template = $this->createTemplate();
        Aspect::create([
            'template_id' => $template->id,
            'category_type_id' => $this->createCategory($template->id, 'potensi')->id,
            'code' => 'asp_01',
            'name' => 'Test Aspect',
            'weight_percentage' => 10,
            'standard_rating' => 3.0,
            'order' => 1,
        ]);

        // Act 1: Save session adjustment
        $this->service->saveAspectWeight($template->id, 'asp_01', 15);
        $this->assertEquals(15, $this->service->getAspectWeight($template->id, 'asp_01'));

        // Act 2: Clear session (simulating logout or manual clear)
        Session::forget("standard_adjustment.{$template->id}");

        // Assert: Should return quantum default (10)
        $result = $this->service->getAspectWeight($template->id, 'asp_01');
        $this->assertEquals(10, $result);
    }

    /**
     * Test: GetAdjustments returns session adjustments that differ from baseline
     *
     * Note: saveAspectWeight() only saves if value != original
     * So if weight is same as quantum default, it won't be in adjustments
     */
    public function test_get_adjustments_returns_all_current_adjustments(): void
    {
        // Arrange
        $template = $this->createTemplate();
        $category = $this->createCategory($template->id, 'potensi');

        Aspect::create([
            'template_id' => $template->id,
            'category_type_id' => $category->id,
            'code' => 'asp_01',
            'name' => 'Aspect 1',
            'weight_percentage' => 10, // Quantum default
            'standard_rating' => 3.0, // Quantum default
            'order' => 1,
        ]);

        // Act: Save multiple adjustments (all different from baseline)
        $this->service->saveAspectWeight($template->id, 'asp_01', 12); // 12 != 10
        $this->service->saveAspectRating($template->id, 'asp_01', 3.5); // 3.5 != 3.0
        $this->service->setAspectActive($template->id, 'asp_01', false); // false != true (default)

        // Assert: Should return all adjustments
        $adjustments = $this->service->getAdjustments($template->id);

        // Verify adjustments were saved
        $this->assertArrayHasKey('aspect_ratings', $adjustments);
        $this->assertArrayHasKey('active_aspects', $adjustments);

        $this->assertEquals(3.5, $adjustments['aspect_ratings']['asp_01']);
        $this->assertFalse($adjustments['active_aspects']['asp_01']);

        // Weight should be 0 (inactive aspect gets weight 0)
        $this->assertEquals(0, $this->service->getAspectWeight($template->id, 'asp_01'));
    }

    /**
     * Test: Decimal ratings are handled correctly
     */
    public function test_decimal_ratings_handled_correctly(): void
    {
        // Arrange
        $template = $this->createTemplate();
        Aspect::create([
            'template_id' => $template->id,
            'category_type_id' => $this->createCategory($template->id, 'kompetensi')->id,
            'code' => 'asp_01',
            'name' => 'Test Aspect',
            'weight_percentage' => 10,
            'standard_rating' => 3.25,
            'order' => 1,
        ]);

        // Act: Save decimal rating
        $this->service->saveAspectRating($template->id, 'asp_01', 4.75);

        // Assert: Should preserve decimal precision
        $result = $this->service->getAspectRating($template->id, 'asp_01');
        $this->assertEquals(4.75, $result);
    }

    /**
     * Test: Sub-aspect rating as integer is converted to int
     */
    public function test_sub_aspect_rating_returned_as_integer(): void
    {
        // Arrange
        $template = $this->createTemplate();
        $aspect = Aspect::create([
            'template_id' => $template->id,
            'category_type_id' => $this->createCategory($template->id, 'potensi')->id,
            'code' => 'asp_kecerdasan',
            'name' => 'Kecerdasan',
            'weight_percentage' => 25,
            'standard_rating' => null,
            'order' => 1,
        ]);

        SubAspect::create([
            'aspect_id' => $aspect->id,
            'code' => 'sub_01',
            'name' => 'Sub 1',
            'standard_rating' => 4,
            'order' => 1,
        ]);

        // Act: Get sub-aspect rating
        $result = $this->service->getSubAspectRating($template->id, 'sub_01');

        // Assert: Should be integer
        $this->assertIsInt($result);
        $this->assertEquals(4, $result);
    }

    // ========================================
    // HELPER METHODS
    // ========================================

    /**
     * Create test institution
     */
    private function createInstitution(): Institution
    {
        return Institution::create([
            'code' => 'INST_'.uniqid(),
            'name' => 'Test Institution',
            'api_key' => 'test_api_key_'.uniqid(),
        ]);
    }

    /**
     * Create test template
     */
    private function createTemplate(): AssessmentTemplate
    {
        return AssessmentTemplate::create([
            'code' => 'TMPL_'.uniqid(),
            'name' => 'Test Template',
            'description' => 'Template for testing',
        ]);
    }

    /**
     * Create test category
     */
    private function createCategory(int $templateId, string $code, int $weight = 50): CategoryType
    {
        return CategoryType::create([
            'template_id' => $templateId,
            'code' => $code,
            'name' => ucfirst($code),
            'weight_percentage' => $weight,
            'order' => $code === 'potensi' ? 1 : 2,
        ]);
    }
}
