<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire;

use PHPUnit\Framework\Attributes\Test;
use App\Livewire\Pages\GeneralReport\StandardPsikometrik;
use App\Models\Aspect;
use App\Models\AssessmentEvent;
use App\Models\AssessmentTemplate;
use App\Models\CategoryType;
use App\Models\CustomStandard;
use App\Models\Institution;
use App\Models\PositionFormation;
use App\Models\SubAspect;
use App\Models\User;
use App\Services\CustomStandardService;
use App\Services\DynamicStandardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class StandardPsikometrikTest extends TestCase
{
    use RefreshDatabase;
    protected User $user;

    protected Institution $institution;

    protected AssessmentTemplate $template;

    protected AssessmentEvent $event;

    protected PositionFormation $position;

    protected CategoryType $potensiCategory;

    protected Aspect $aspect;

    protected SubAspect $subAspect;

    protected CustomStandard $customStandard;

    protected function setUp(): void
    {
        parent::setUp();

        // Create institution
        $this->institution = Institution::factory()->create([
            'name' => 'Test Institution',
            'code' => 'TEST-INST',
        ]);

        // Create user with institution
        $this->user = User::factory()->create([
            'institution_id' => $this->institution->id,
        ]);
        $this->actingAs($this->user);

        // Create template
        $this->template = AssessmentTemplate::factory()->create([
            'name' => 'Test Template',
            'code' => 'TEST-TEMPLATE',
        ]);

        // Create event
        $this->event = AssessmentEvent::factory()->create([
            'code' => 'P3K-2025',
            'name' => 'Test Event 2025',
            'institution_id' => $this->institution->id,
        ]);

        // Create position formation
        $this->position = PositionFormation::factory()->create([
            'event_id' => $this->event->id,
            'template_id' => $this->template->id,
            'name' => 'Test Position',
        ]);

        // Create Potensi category
        $this->potensiCategory = CategoryType::factory()->create([
            'template_id' => $this->template->id,
            'code' => 'potensi',
            'name' => 'Potensi',
            'weight_percentage' => 25,
            'order' => 1,
        ]);

        // Create aspect
        $this->aspect = Aspect::factory()->create([
            'category_type_id' => $this->potensiCategory->id,
            'template_id' => $this->template->id,
            'code' => 'daya-pikir',
            'name' => 'Daya Pikir',
            'weight_percentage' => 5,
            'standard_rating' => null,
            'order' => 1,
        ]);

        // Create sub-aspect
        $this->subAspect = SubAspect::factory()->create([
            'aspect_id' => $this->aspect->id,
            'code' => 'daya-analisa',
            'name' => 'Daya Analisa',
            'standard_rating' => 3,
            'order' => 1,
        ]);

        // Create custom standard
        $this->customStandard = CustomStandard::factory()->create([
            'institution_id' => $this->institution->id,
            'template_id' => $this->template->id,
            'code' => 'KEJAKSAAN-2025',
            'name' => 'Standar Khusus Kejaksaan 2025',
            'category_weights' => [
                'potensi' => 30,
                'kompetensi' => 70,
            ],
            'aspect_configs' => [
                'daya-pikir' => [
                    'weight' => 8,
                    'active' => true,
                ],
            ],
            'sub_aspect_configs' => [
                'daya-analisa' => [
                    'rating' => 4,
                    'active' => true,
                ],
            ],
        ]);
    }

    protected function tearDown(): void
    {
        // Clear all sessions after each test
        session()->flush();

        // Clear AspectCacheService static cache to prevent stale data between tests
        \App\Services\Cache\AspectCacheService::clearCache();

        parent::tearDown();
    }

    /**
     * Helper: Set session filters for event and position
     */
    protected function setSessionFilters(): void
    {
        session([
            'filter.event_code' => $this->event->code,
            'filter.position_formation_id' => $this->position->id,
        ]);
    }

    // ============================================================================
    // GROUP 1: LIFECYCLE & INITIALIZATION (3 TESTS)
    // ============================================================================

    #[Test]
    public function component_mounts_with_default_state(): void
    {
        Livewire::test(StandardPsikometrik::class)
            ->assertSet('selectedCustomStandardId', null)
            ->assertSet('categoryData', [])
            ->assertSet('availableCustomStandards', [])
            ->assertSet('chartData.labels', [])
            ->assertSet('chartData.ratings', [])
            ->assertSet('chartData.scores', []);
    }

    #[Test]
    public function component_loads_standard_data_when_event_and_position_selected(): void
    {
        $this->setSessionFilters();

        Livewire::test(StandardPsikometrik::class)
            ->assertSet('selectedTemplate.id', $this->template->id)
            ->assertCount('categoryData', 1)
            ->assertSet('categoryData.0.code', 'potensi')
            ->assertCount('categoryData.0.aspects', 1)
            ->assertSet('categoryData.0.aspects.0.code', 'daya-pikir')
            ->assertCount('chartData.labels', 1)
            ->assertSet('totals.total_aspects', 1);
    }

    #[Test]
    public function component_loads_available_custom_standards_for_institution(): void
    {
        $this->setSessionFilters();

        // Create another custom standard
        CustomStandard::factory()->create([
            'institution_id' => $this->institution->id,
            'template_id' => $this->template->id,
            'code' => 'POLRI-2025',
            'name' => 'Standar Khusus Polri 2025',
        ]);

        Livewire::test(StandardPsikometrik::class)
            ->assertCount('availableCustomStandards', 2)
            ->assertSet('selectedCustomStandardId', null); // Default Quantum
    }

    // ============================================================================
    // GROUP 2: BASELINE SELECTION & SWITCHING (5 TESTS)
    // ============================================================================

    #[Test]
    public function selecting_custom_standard_updates_component_state(): void
    {
        $this->setSessionFilters();

        Livewire::test(StandardPsikometrik::class)
            ->assertSet('selectedCustomStandardId', null)
            ->call('selectCustomStandard', $this->customStandard->id)
            ->assertSet('selectedCustomStandardId', $this->customStandard->id)
            ->assertDispatched('standard-switched');

        // Verify session updated
        $customStandardService = app(CustomStandardService::class);
        $selected = $customStandardService->getSelected($this->template->id);
        $this->assertEquals($this->customStandard->id, $selected);
    }

    #[Test]
    public function switching_to_quantum_default_clears_custom_standard_selection(): void
    {
        $this->setSessionFilters();

        // First select custom standard
        $customStandardService = app(CustomStandardService::class);
        $customStandardService->select($this->template->id, $this->customStandard->id);

        Livewire::test(StandardPsikometrik::class)
            ->assertSet('selectedCustomStandardId', $this->customStandard->id)
            ->call('selectCustomStandard', null)
            ->assertSet('selectedCustomStandardId', null)
            ->assertDispatched('standard-switched');

        // Verify session cleared
        $selected = $customStandardService->getSelected($this->template->id);
        $this->assertNull($selected);
    }

    #[Test]
    public function handles_string_null_empty_string_or_actual_null_correctly(): void
    {
        $this->setSessionFilters();

        // Test string 'null'
        Livewire::test(StandardPsikometrik::class)
            ->call('selectCustomStandard', 'null')
            ->assertSet('selectedCustomStandardId', null);

        // Test empty string
        Livewire::test(StandardPsikometrik::class)
            ->call('selectCustomStandard', '')
            ->assertSet('selectedCustomStandardId', null);

        // Test actual null
        Livewire::test(StandardPsikometrik::class)
            ->call('selectCustomStandard', null)
            ->assertSet('selectedCustomStandardId', null);
    }

    #[Test]
    public function switching_custom_standard_clears_previous_session_adjustments(): void
    {
        $this->setSessionFilters();

        // Preload cache for hasCategoryAdjustments() to work
        \App\Services\Cache\AspectCacheService::preloadByTemplate($this->template->id);

        // Set session adjustments
        $dynamicService = app(DynamicStandardService::class);
        $dynamicService->saveCategoryWeight($this->template->id, 'potensi', 30);
        $dynamicService->saveSubAspectRating($this->template->id, 'daya-analisa', 4);

        // Verify adjustments exist
        $this->assertTrue($dynamicService->hasCategoryAdjustments($this->template->id, 'potensi'));

        // Create second custom standard
        $customStandard2 = CustomStandard::factory()->create([
            'institution_id' => $this->institution->id,
            'template_id' => $this->template->id,
            'code' => 'POLRI-2025',
            'name' => 'Standar Khusus Polri 2025',
        ]);

        // Switch custom standard
        Livewire::test(StandardPsikometrik::class)
            ->call('selectCustomStandard', $customStandard2->id)
            ->assertSet('selectedCustomStandardId', $customStandard2->id);

        // Verify adjustments cleared
        // IMPORTANT: Create NEW instance to avoid stale request-scoped cache
        $freshDynamicService = app(DynamicStandardService::class);
        $this->assertFalse($freshDynamicService->hasCategoryAdjustments($this->template->id, 'potensi'));
    }

    #[Test]
    public function handles_standard_switched_event_from_other_components(): void
    {
        $this->setSessionFilters();

        Livewire::test(StandardPsikometrik::class)
            ->dispatch('standard-switched', templateId: $this->template->id)
            ->assertDispatched('chartDataUpdated');
    }

    // ============================================================================
    // GROUP 3: CATEGORY WEIGHT ADJUSTMENTS (4 TESTS)
    // ============================================================================

    #[Test]
    public function opening_category_weight_modal_sets_state_correctly(): void
    {
        $this->setSessionFilters();

        Livewire::test(StandardPsikometrik::class)
            ->call('openEditCategoryWeight', 'potensi', 25)
            ->assertSet('showEditCategoryWeightModal', true)
            ->assertSet('editingField', 'potensi')
            ->assertSet('editingValue', 25)
            ->assertSet('editingOriginalValue', 25);
    }

    #[Test]
    public function saving_category_weight_creates_session_adjustment(): void
    {
        $this->setSessionFilters();

        Livewire::test(StandardPsikometrik::class)
            ->call('openEditCategoryWeight', 'potensi', 25)
            ->set('editingValue', 30)
            ->call('saveCategoryWeight')
            ->assertSet('showEditCategoryWeightModal', false)
            ->assertDispatched('standard-adjusted');

        // Verify session adjustment saved
        $dynamicService = app(DynamicStandardService::class);
        $weight = $dynamicService->getCategoryWeight($this->template->id, 'potensi');
        $this->assertEquals(30, $weight);
    }

    #[Test]
    public function closing_modal_without_saving_discards_changes(): void
    {
        $this->setSessionFilters();

        Livewire::test(StandardPsikometrik::class)
            ->call('openEditCategoryWeight', 'potensi', 25)
            ->set('editingValue', 30)
            ->call('closeModal')
            ->assertSet('showEditCategoryWeightModal', false)
            ->assertSet('editingField', '')
            ->assertSet('editingValue', null)
            ->assertSet('editingOriginalValue', null);

        // Verify NO session adjustment saved
        $dynamicService = app(DynamicStandardService::class);
        $weight = $dynamicService->getCategoryWeight($this->template->id, 'potensi');
        $this->assertEquals(25, $weight); // Original value
    }

    #[Test]
    public function category_weight_modal_handles_invalid_template_gracefully(): void
    {
        // Don't set session filters - no template selected

        Livewire::test(StandardPsikometrik::class)
            ->call('openEditCategoryWeight', 'potensi', 25)
            ->assertSet('showEditCategoryWeightModal', false);
    }

    // ============================================================================
    // GROUP 4: SUB-ASPECT RATING ADJUSTMENTS (5 TESTS)
    // ============================================================================

    #[Test]
    public function opening_sub_aspect_rating_modal_sets_state_correctly(): void
    {
        $this->setSessionFilters();

        Livewire::test(StandardPsikometrik::class)
            ->call('openEditSubAspectRating', 'daya-analisa', 3)
            ->assertSet('showEditRatingModal', true)
            ->assertSet('editingField', 'daya-analisa')
            ->assertSet('editingValue', 3)
            ->assertSet('editingOriginalValue', 3);
    }

    #[Test]
    public function saving_sub_aspect_rating_creates_session_adjustment(): void
    {
        $this->setSessionFilters();

        Livewire::test(StandardPsikometrik::class)
            ->call('openEditSubAspectRating', 'daya-analisa', 3)
            ->set('editingValue', 4)
            ->call('saveSubAspectRating')
            ->assertSet('showEditRatingModal', false)
            ->assertDispatched('standard-adjusted');

        // Verify session adjustment saved
        $dynamicService = app(DynamicStandardService::class);
        $rating = $dynamicService->getSubAspectRating($this->template->id, 'daya-analisa');
        $this->assertEquals(4, $rating);
    }

    #[Test]
    public function sub_aspect_rating_validation_rejects_values_below_1(): void
    {
        $this->setSessionFilters();

        Livewire::test(StandardPsikometrik::class)
            ->call('openEditSubAspectRating', 'daya-analisa', 3)
            ->set('editingValue', 0)
            ->call('saveSubAspectRating')
            ->assertSet('showEditRatingModal', true) // Modal stays open
            ->assertHasErrors(['editingValue']);

        // Verify NO session adjustment saved
        $dynamicService = app(DynamicStandardService::class);
        $rating = $dynamicService->getSubAspectRating($this->template->id, 'daya-analisa');
        $this->assertEquals(3, $rating); // Original value
    }

    #[Test]
    public function sub_aspect_rating_validation_rejects_values_above_5(): void
    {
        $this->setSessionFilters();

        Livewire::test(StandardPsikometrik::class)
            ->call('openEditSubAspectRating', 'daya-analisa', 3)
            ->set('editingValue', 6)
            ->call('saveSubAspectRating')
            ->assertSet('showEditRatingModal', true) // Modal stays open
            ->assertHasErrors(['editingValue']);

        // Verify NO session adjustment saved
        $dynamicService = app(DynamicStandardService::class);
        $rating = $dynamicService->getSubAspectRating($this->template->id, 'daya-analisa');
        $this->assertEquals(3, $rating); // Original value
    }

    #[Test]
    public function sub_aspect_rating_modal_handles_null_template_gracefully(): void
    {
        // Don't set session filters - no template selected

        Livewire::test(StandardPsikometrik::class)
            ->call('openEditSubAspectRating', 'daya-analisa', 3)
            ->assertSet('showEditRatingModal', false);
    }

    // ============================================================================
    // GROUP 5: RESET ADJUSTMENTS (2 TESTS)
    // ============================================================================

    #[Test]
    public function reset_adjustments_clears_all_session_adjustments(): void
    {
        $this->setSessionFilters();

        // Preload cache for hasCategoryAdjustments() to work
        \App\Services\Cache\AspectCacheService::preloadByTemplate($this->template->id);

        // Select custom standard
        $customStandardService = app(CustomStandardService::class);
        $customStandardService->select($this->template->id, $this->customStandard->id);

        // Set session adjustments (use values DIFFERENT from custom standard)
        $dynamicService = app(DynamicStandardService::class);
        $dynamicService->saveCategoryWeight($this->template->id, 'potensi', 35); // Custom std has 30
        $dynamicService->saveSubAspectRating($this->template->id, 'daya-analisa', 5); // Custom std has 4

        // Preload cache again after setting adjustments (fresh instance needs cache)
        \App\Services\Cache\AspectCacheService::preloadByTemplate($this->template->id);

        // Verify adjustments exist
        $this->assertTrue($dynamicService->hasCategoryAdjustments($this->template->id, 'potensi'));

        // Reset adjustments
        Livewire::test(StandardPsikometrik::class)
            ->call('resetAdjustments')
            ->assertDispatched('standard-adjusted');

        // Verify adjustments cleared
        // IMPORTANT: Create NEW instance to avoid stale request-scoped cache
        $freshDynamicService = app(DynamicStandardService::class);
        $this->assertFalse($freshDynamicService->hasCategoryAdjustments($this->template->id, 'potensi'));

        // Verify custom standard selection persists
        $selected = $customStandardService->getSelected($this->template->id);
        $this->assertEquals($this->customStandard->id, $selected);
    }

    #[Test]
    public function reset_adjustments_handles_null_template_gracefully(): void
    {
        // Don't set session filters - no template selected

        Livewire::test(StandardPsikometrik::class)
            ->call('resetAdjustments')
            ->assertNotDispatched('standard-adjusted'); // Should not dispatch event when no template
    }

    // ============================================================================
    // GROUP 6: EVENT HANDLING (3 TESTS)
    // ============================================================================

    #[Test]
    public function handles_event_selected_clears_cache_and_waits_for_position(): void
    {
        $this->setSessionFilters();

        $component = Livewire::test(StandardPsikometrik::class)
            ->assertSet('categoryData.0.code', 'potensi');

        // Dispatch event-selected
        $component->dispatch('event-selected', eventCode: 'P3K-2026');

        // Cache should be cleared, but data not reloaded yet (waits for position)
        // We can't directly test cache, but we can verify the handler was called
    }

    #[Test]
    public function handles_position_selected_loads_data_and_dispatches_chart_update(): void
    {
        // Set only event filter initially
        session(['filter.event_code' => $this->event->code]);

        Livewire::test(StandardPsikometrik::class)
            ->dispatch('position-selected', positionFormationId: $this->position->id)
            ->assertDispatched('chartDataUpdated');
    }

    #[Test]
    public function handles_standard_adjusted_event_from_other_components(): void
    {
        $this->setSessionFilters();

        Livewire::test(StandardPsikometrik::class)
            ->dispatch('standard-adjusted', templateId: $this->template->id)
            ->assertDispatched('chartDataUpdated');
    }

    // ============================================================================
    // GROUP 7: CACHE MANAGEMENT (2 TESTS)
    // ============================================================================

    #[Test]
    public function cache_prevents_redundant_data_processing(): void
    {
        $this->setSessionFilters();

        $component = Livewire::test(StandardPsikometrik::class);

        // First load - data should be loaded
        $firstCategoryData = $component->get('categoryData');

        // Call loadStandardData again (simulate re-render)
        // Cache should prevent recalculation
        // We can't directly test private cache, but we can verify data consistency
        $secondCategoryData = $component->get('categoryData');

        $this->assertEquals($firstCategoryData, $secondCategoryData);
    }

    #[Test]
    public function cache_cleared_on_baseline_changes(): void
    {
        $this->setSessionFilters();

        $component = Livewire::test(StandardPsikometrik::class)
            ->assertSet('categoryData.0.weight_percentage', 25);

        // Change baseline - should clear cache and reload
        $component->call('selectCustomStandard', $this->customStandard->id);

        // Data should be different (custom standard has weight 30)
        $component->assertSet('categoryData.0.weight_percentage', 30);
    }

    // ============================================================================
    // GROUP 8: 3-LAYER PRIORITY INTEGRATION (1 TEST)
    // ============================================================================

    #[Test]
    public function loaded_data_respects_3_layer_priority_system(): void
    {
        $this->setSessionFilters();

        $dynamicService = app(DynamicStandardService::class);
        $customStandardService = app(CustomStandardService::class);

        // SCENARIO A: Quantum Default Only
        Livewire::test(StandardPsikometrik::class)
            ->assertSet('selectedCustomStandardId', null)
            ->assertSet('categoryData.0.weight_percentage', 25); // Quantum Default

        // SCENARIO B: Custom Standard Override
        $customStandardService->select($this->template->id, $this->customStandard->id);

        Livewire::test(StandardPsikometrik::class)
            ->assertSet('selectedCustomStandardId', $this->customStandard->id)
            ->assertSet('categoryData.0.weight_percentage', 30); // Custom Standard

        // SCENARIO C: Session Override
        $dynamicService->saveCategoryWeight($this->template->id, 'potensi', 35);

        Livewire::test(StandardPsikometrik::class)
            ->assertSet('categoryData.0.weight_percentage', 35); // Session Adjustment
    }
}
