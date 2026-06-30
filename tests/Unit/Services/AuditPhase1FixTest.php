<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Aspect;
use App\Models\AssessmentTemplate;
use App\Models\CategoryType;
use App\Models\CustomStandard;
use App\Models\Institution;
use App\Models\SubAspect;
use App\Services\Cache\AspectCacheService;
use App\Services\CustomStandardService;
use App\Services\DynamicStandardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

/**
 * Audit Phase 1 Fix Tests
 *
 * Tests for critical fixes identified in the technical audit:
 *
 * - Bug 2.1: CustomStandard is_active filter + self-healing
 * - Issue 3.3: hasCategoryAdjustments() checks custom standard
 * - Issue 3.4: Auto-preload AspectCacheService
 *
 * @see docs/SPSP_AUDIT.md
 */
class AuditPhase1FixTest extends TestCase
{
    use RefreshDatabase;

    private DynamicStandardService $dynamicService;

    private CustomStandardService $customStandardService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dynamicService = app(DynamicStandardService::class);
        $this->customStandardService = new CustomStandardService;

        AspectCacheService::clearCache();
    }

    // ========================================
    // BUG 2.1: is_active FILTER TESTS
    // ========================================

    /**
     * Test: DynamicStandardService ignores inactive custom standards
     *
     * Scenario: Admin deactivates a custom standard while user still has it in session.
     * Expected: getCustomStandard() returns null, system falls back to Quantum Default.
     */
    public function test_dynamic_service_ignores_inactive_custom_standard(): void
    {
        // Arrange
        $institution = $this->createInstitution();
        $template = $this->createTemplateWithAspects();
        $templateId = $template->id;

        // Create a custom standard and set it as selected
        $customStandard = $this->createCustomStandard($institution->id, $templateId, 'STD-001', [
            'potensi' => 60,
            'kompetensi' => 40,
        ]);

        Session::put("selected_standard.{$templateId}", $customStandard->id);

        // Verify it works when active
        $weight = $this->dynamicService->getCategoryWeight($templateId, 'potensi');
        $this->assertEquals(60, $weight); // From custom standard

        // Act: Admin deactivates the custom standard
        $customStandard->update(['is_active' => false]);

        // Clear the request-scoped cache to force re-query
        $this->dynamicService = app(DynamicStandardService::class);

        // Assert: Should fallback to quantum default (50)
        $weight = $this->dynamicService->getCategoryWeight($templateId, 'potensi');
        $this->assertEquals(50, $weight); // Quantum default, NOT 60
    }

    /**
     * Test: Self-healing clears session when inactive standard is accessed
     * via CustomStandardService::getSelectedStandard()
     */
    public function test_self_healing_clears_session_for_inactive_standard(): void
    {
        // Arrange
        $institution = $this->createInstitution();
        $template = $this->createTemplateWithAspects();
        $templateId = $template->id;

        $customStandard = $this->createCustomStandard($institution->id, $templateId, 'STD-001');

        $this->customStandardService->select($templateId, $customStandard->id);
        $this->assertNotNull(Session::get("selected_standard.{$templateId}"));

        // Act: Deactivate and access via CustomStandardService
        $customStandard->update(['is_active' => false]);
        $result = $this->customStandardService->getSelectedStandard($templateId);

        // Assert: Returns null and session is cleaned
        $this->assertNull($result);
        $this->assertNull(Session::get("selected_standard.{$templateId}"));
    }

    /**
     * Test: DynamicStandardService self-healing on subsequent calls
     * After accessing an inactive standard, session references are cleaned
     * so the next call doesn't even attempt the custom standard path.
     */
    public function test_dynamic_service_self_healing_on_subsequent_calls(): void
    {
        // Arrange
        $institution = $this->createInstitution();
        $template = $this->createTemplateWithAspects();
        $templateId = $template->id;

        $customStandard = $this->createCustomStandard($institution->id, $templateId, 'STD-001', [
            'potensi' => 60,
            'kompetensi' => 40,
        ]);

        Session::put("selected_standard.{$templateId}", $customStandard->id);

        // Deactivate the custom standard
        $customStandard->update(['is_active' => false]);

        // First call: detects inactive standard and self-heals
        $service1 = app(DynamicStandardService::class);
        $weight1 = $service1->getCategoryWeight($templateId, 'potensi');
        $this->assertEquals(50, $weight1); // Quantum default

        // Second call with fresh service: should not find any custom standard in session
        $service2 = app(DynamicStandardService::class);
        $weight2 = $service2->getCategoryWeight($templateId, 'potensi');
        $this->assertEquals(50, $weight2); // Still quantum default, confirmed session was cleaned
    }

    /**
     * Test: CustomStandardService::getSelectedStandard() filters is_active
     */
    public function test_custom_standard_service_filters_is_active(): void
    {
        // Arrange
        $institution = $this->createInstitution();
        $template = $this->createTemplateWithAspects();

        $customStandard = $this->createCustomStandard($institution->id, $template->id, 'STD-001');

        $this->customStandardService->select($template->id, $customStandard->id);

        // Verify it works when active
        $selected = $this->customStandardService->getSelectedStandard($template->id);
        $this->assertNotNull($selected);
        $this->assertEquals($customStandard->id, $selected->id);

        // Act: Deactivate
        $customStandard->update(['is_active' => false]);

        // Assert: Returns null and clears session
        $selected = $this->customStandardService->getSelectedStandard($template->id);
        $this->assertNull($selected);
        $this->assertNull($this->customStandardService->getSelected($template->id));
    }

    /**
     * Test: Active custom standard still works correctly
     */
    public function test_active_custom_standard_still_works(): void
    {
        // Arrange
        $institution = $this->createInstitution();
        $template = $this->createTemplateWithAspects();
        $templateId = $template->id;

        $customStandard = $this->createCustomStandard($institution->id, $templateId, 'STD-001', [
            'potensi' => 70,
            'kompetensi' => 30,
        ]);

        Session::put("selected_standard.{$templateId}", $customStandard->id);

        // Act & Assert: Active standard should work
        $weight = $this->dynamicService->getCategoryWeight($templateId, 'potensi');
        $this->assertEquals(70, $weight);

        // Session should NOT be cleared
        $this->assertEquals($customStandard->id, Session::get("selected_standard.{$templateId}"));
    }

    // ========================================
    // ISSUE 3.3: hasCategoryAdjustments() TESTS
    // ========================================

    /**
     * Test: hasCategoryAdjustments returns true when custom standard is selected
     */
    public function test_has_category_adjustments_detects_custom_standard(): void
    {
        // Arrange
        $institution = $this->createInstitution();
        $template = $this->createTemplateWithAspects();
        $templateId = $template->id;

        $customStandard = $this->createCustomStandard($institution->id, $templateId, 'STD-001');

        // Before selecting custom standard
        $this->assertFalse($this->dynamicService->hasCategoryAdjustments($templateId, 'potensi'));

        // Act: Select custom standard (without any session adjustments)
        Session::put("selected_standard.{$templateId}", $customStandard->id);

        // Clear cache to force re-evaluation
        $this->dynamicService = app(DynamicStandardService::class);

        // Assert: Should detect custom standard as "has adjustments"
        $this->assertTrue($this->dynamicService->hasCategoryAdjustments($templateId, 'potensi'));
    }

    /**
     * Test: hasCategoryAdjustments returns false when no adjustments and no custom standard
     */
    public function test_has_category_adjustments_false_when_no_adjustments(): void
    {
        $template = $this->createTemplateWithAspects();

        $this->assertFalse($this->dynamicService->hasCategoryAdjustments($template->id, 'potensi'));
    }

    /**
     * Test: hasCategoryAdjustments still detects session adjustments
     */
    public function test_has_category_adjustments_still_detects_session_adjustments(): void
    {
        $template = $this->createTemplateWithAspects();
        $templateId = $template->id;

        // Set session adjustment
        $this->dynamicService->saveCategoryWeight($templateId, 'potensi', 60);

        // Clear cache
        $this->dynamicService = app(DynamicStandardService::class);

        $this->assertTrue($this->dynamicService->hasCategoryAdjustments($templateId, 'potensi'));
    }

    // ========================================
    // ISSUE 3.4: AUTO-PRELOAD TESTS
    // ========================================

    /**
     * Test: hasCategoryAdjustments works without manual preload
     * (Previously would silent-fail in production if preload was forgotten)
     */
    public function test_has_category_adjustments_works_without_manual_preload(): void
    {
        $template = $this->createTemplateWithAspects();
        $templateId = $template->id;

        // Explicitly clear cache to simulate no preload
        AspectCacheService::clearCache();

        // Set some adjustment to trigger the full check path
        $this->dynamicService->saveCategoryWeight($templateId, 'potensi', 60);
        $this->dynamicService = app(DynamicStandardService::class);

        // Act & Assert: Should NOT throw RuntimeException and should return correct result
        $result = $this->dynamicService->hasCategoryAdjustments($templateId, 'potensi');
        $this->assertTrue($result);
    }

    // ========================================
    // HELPER METHODS
    // ========================================

    private function createInstitution(): Institution
    {
        return Institution::create([
            'code' => 'INST_'.uniqid(),
            'name' => 'Test Institution',
            'api_key' => 'test_api_key_'.uniqid(),
        ]);
    }

    private function createTemplateWithAspects(): AssessmentTemplate
    {
        $template = AssessmentTemplate::factory()->create();

        $potensiCategory = CategoryType::factory()->create([
            'template_id' => $template->id,
            'code' => 'potensi',
            'name' => 'Potensi',
            'weight_percentage' => 50,
            'order' => 1,
        ]);

        $kompetensiCategory = CategoryType::factory()->create([
            'template_id' => $template->id,
            'code' => 'kompetensi',
            'name' => 'Kompetensi',
            'weight_percentage' => 50,
            'order' => 2,
        ]);

        $potensiAspect = Aspect::factory()->create([
            'template_id' => $template->id,
            'category_type_id' => $potensiCategory->id,
            'code' => 'asp_pot_01',
            'name' => 'Aspek Potensi 1',
            'weight_percentage' => 100,
            'standard_rating' => 3.0,
            'order' => 1,
        ]);

        SubAspect::factory()->create([
            'aspect_id' => $potensiAspect->id,
            'code' => 'sub_pot_01',
            'name' => 'Sub Potensi 1',
            'standard_rating' => 3,
            'order' => 1,
        ]);

        Aspect::factory()->create([
            'template_id' => $template->id,
            'category_type_id' => $kompetensiCategory->id,
            'code' => 'asp_kom_01',
            'name' => 'Aspek Kompetensi 1',
            'weight_percentage' => 100,
            'standard_rating' => 4.0,
            'order' => 1,
        ]);

        return $template->fresh(['categoryTypes.aspects.subAspects']);
    }

    private function createCustomStandard(
        int $institutionId,
        int $templateId,
        string $code,
        array $categoryWeights = ['potensi' => 50, 'kompetensi' => 50]
    ): CustomStandard {
        return CustomStandard::create([
            'institution_id' => $institutionId,
            'template_id' => $templateId,
            'code' => $code,
            'name' => "Test Standard {$code}",
            'category_weights' => $categoryWeights,
            'aspect_configs' => [
                'asp_pot_01' => ['weight' => 100, 'active' => true],
                'asp_kom_01' => ['weight' => 100, 'active' => true],
            ],
            'sub_aspect_configs' => [
                'sub_pot_01' => ['rating' => 3, 'active' => true],
            ],
            'is_active' => true,
        ]);
    }
}
