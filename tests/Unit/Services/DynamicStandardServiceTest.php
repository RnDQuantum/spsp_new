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
 * PHASE 1: ⏳ Priority Chain Tests (15-20 tests)
 * PHASE 2: ⏳ Data-Driven Rating Tests (10-15 tests)
 * PHASE 3: ⏳ Session Management Tests (8-10 tests)
 * PHASE 4: ⏳ Active/Inactive Tests (8-10 tests)
 * PHASE 5: ⏳ Validation Tests (5-8 tests)
 *
 * TOTAL TARGET: 40-50 tests
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
