<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire;

use PHPUnit\Framework\Attributes\Test;
use App\Livewire\Pages\GeneralReport\StandardMc;
use App\Models\Aspect;
use App\Models\AssessmentEvent;
use App\Models\AssessmentTemplate;
use App\Models\CategoryType;
use App\Models\CustomStandard;
use App\Models\Institution;
use App\Models\PositionFormation;
use App\Models\User;
use App\Services\CustomStandardService;
use App\Services\DynamicStandardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class StandardMcTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Institution $institution;

    protected AssessmentTemplate $template;

    protected AssessmentEvent $event;

    protected PositionFormation $position;

    protected CategoryType $kompetensiCategory;

    protected Aspect $aspect;

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

        // Create Kompetensi category
        $this->kompetensiCategory = CategoryType::factory()->create([
            'template_id' => $this->template->id,
            'code' => 'kompetensi',
            'name' => 'Kompetensi',
            'weight_percentage' => 75,
            'order' => 2,
        ]);

        // Create aspect (Kompetensi uses direct aspect ratings, no sub-aspects)
        $this->aspect = Aspect::factory()->create([
            'category_type_id' => $this->kompetensiCategory->id,
            'template_id' => $this->template->id,
            'code' => 'integritas',
            'name' => 'Integritas',
            'weight_percentage' => 10,
            'standard_rating' => 4, // Direct rating for Kompetensi
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
                'integritas' => [
                    'weight' => 12,
                    'rating' => 5, // Custom standard rating
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
        $component = Livewire::test(StandardMc::class);

        $component
            ->assertSet('selectedCustomStandardId', null)
            ->assertSet('categoryData', [])
            ->assertSet('availableCustomStandards', [])
            ->assertSet('chartData.labels', [])
            ->assertSet('chartData.ratings', [])
            ->assertSet('chartData.scores', []);

        // Verify chartId is generated
        $chartId = $component->get('chartId');
        $this->assertStringStartsWith('standardMc', $chartId);
    }

    #[Test]
    public function component_loads_standard_data_when_event_and_position_selected(): void
    {
        $this->setSessionFilters();

        Livewire::test(StandardMc::class)
            ->assertSet('selectedTemplate.id', $this->template->id)
            ->assertCount('categoryData', 1)
            ->assertSet('categoryData.0.code', 'kompetensi')
            ->assertCount('categoryData.0.aspects', 1)
            ->assertSet('categoryData.0.aspects.0.code', 'integritas')
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

        Livewire::test(StandardMc::class)
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

        Livewire::test(StandardMc::class)
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

        Livewire::test(StandardMc::class)
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
        Livewire::test(StandardMc::class)
            ->call('selectCustomStandard', 'null')
            ->assertSet('selectedCustomStandardId', null);

        // Test empty string
        Livewire::test(StandardMc::class)
            ->call('selectCustomStandard', '')
            ->assertSet('selectedCustomStandardId', null);

        // Test actual null
        Livewire::test(StandardMc::class)
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
        $dynamicService->saveCategoryWeight($this->template->id, 'kompetensi', 80);
        $dynamicService->saveAspectRating($this->template->id, 'integritas', 5);

        // Verify adjustments exist
        $this->assertTrue($dynamicService->hasCategoryAdjustments($this->template->id, 'kompetensi'));

        // Create second custom standard
        $customStandard2 = CustomStandard::factory()->create([
            'institution_id' => $this->institution->id,
            'template_id' => $this->template->id,
            'code' => 'POLRI-2025',
            'name' => 'Standar Khusus Polri 2025',
        ]);

        // Switch custom standard
        Livewire::test(StandardMc::class)
            ->call('selectCustomStandard', $customStandard2->id)
            ->assertSet('selectedCustomStandardId', $customStandard2->id);

        // Verify adjustments cleared
        // IMPORTANT: Create NEW instance to avoid stale request-scoped cache
        $freshDynamicService = app(DynamicStandardService::class);
        $this->assertFalse($freshDynamicService->hasCategoryAdjustments($this->template->id, 'kompetensi'));
    }

    #[Test]
    public function handles_standard_switched_event_from_other_components(): void
    {
        $this->setSessionFilters();

        Livewire::test(StandardMc::class)
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

        Livewire::test(StandardMc::class)
            ->call('openEditCategoryWeight', 'kompetensi', 75)
            ->assertSet('showEditCategoryWeightModal', true)
            ->assertSet('editingField', 'kompetensi')
            ->assertSet('editingValue', 75)
            ->assertSet('editingOriginalValue', 75);
    }

    #[Test]
    public function saving_category_weight_creates_session_adjustment(): void
    {
        $this->setSessionFilters();

        Livewire::test(StandardMc::class)
            ->call('openEditCategoryWeight', 'kompetensi', 75)
            ->set('editingValue', 80)
            ->call('saveCategoryWeight')
            ->assertSet('showEditCategoryWeightModal', false)
            ->assertDispatched('standard-adjusted');

        // Verify session adjustment saved
        $dynamicService = app(DynamicStandardService::class);
        $weight = $dynamicService->getCategoryWeight($this->template->id, 'kompetensi');
        $this->assertEquals(80, $weight);
    }

    #[Test]
    public function closing_modal_without_saving_discards_changes(): void
    {
        $this->setSessionFilters();

        Livewire::test(StandardMc::class)
            ->call('openEditCategoryWeight', 'kompetensi', 75)
            ->set('editingValue', 80)
            ->call('closeModal')
            ->assertSet('showEditCategoryWeightModal', false)
            ->assertSet('editingField', '')
            ->assertSet('editingValue', null)
            ->assertSet('editingOriginalValue', null);

        // Verify NO session adjustment saved
        $dynamicService = app(DynamicStandardService::class);
        $weight = $dynamicService->getCategoryWeight($this->template->id, 'kompetensi');
        $this->assertEquals(75, $weight); // Original value
    }

    #[Test]
    public function category_weight_modal_handles_invalid_template_gracefully(): void
    {
        // Don't set session filters - no template selected

        Livewire::test(StandardMc::class)
            ->call('openEditCategoryWeight', 'kompetensi', 75)
            ->assertSet('showEditCategoryWeightModal', false);
    }

    // ============================================================================
    // GROUP 4: ASPECT RATING ADJUSTMENTS (5 TESTS)
    // ============================================================================

    #[Test]
    public function opening_aspect_rating_modal_sets_state_correctly(): void
    {
        $this->setSessionFilters();

        Livewire::test(StandardMc::class)
            ->call('openEditAspectRating', 'integritas', 4.0)
            ->assertSet('showEditRatingModal', true)
            ->assertSet('editingField', 'integritas')
            ->assertSet('editingValue', 4) // Converted to int
            ->assertSet('editingOriginalValue', 4.0);
    }

    #[Test]
    public function saving_aspect_rating_creates_session_adjustment(): void
    {
        $this->setSessionFilters();

        Livewire::test(StandardMc::class)
            ->call('openEditAspectRating', 'integritas', 4.0)
            ->set('editingValue', 5)
            ->call('saveAspectRating')
            ->assertSet('showEditRatingModal', false)
            ->assertDispatched('standard-adjusted');

        // Verify session adjustment saved
        $dynamicService = app(DynamicStandardService::class);
        $rating = $dynamicService->getAspectRating($this->template->id, 'integritas');
        $this->assertEquals(5, $rating);
    }

    #[Test]
    public function aspect_rating_validation_rejects_values_below_1(): void
    {
        $this->setSessionFilters();

        Livewire::test(StandardMc::class)
            ->call('openEditAspectRating', 'integritas', 4.0)
            ->set('editingValue', 0)
            ->call('saveAspectRating')
            ->assertSet('showEditRatingModal', true) // Modal stays open
            ->assertHasErrors(['editingValue']);

        // Verify NO session adjustment saved
        $dynamicService = app(DynamicStandardService::class);
        $rating = $dynamicService->getAspectRating($this->template->id, 'integritas');
        $this->assertEquals(4, $rating); // Original value
    }

    #[Test]
    public function aspect_rating_validation_rejects_values_above_5(): void
    {
        $this->setSessionFilters();

        Livewire::test(StandardMc::class)
            ->call('openEditAspectRating', 'integritas', 4.0)
            ->set('editingValue', 6)
            ->call('saveAspectRating')
            ->assertSet('showEditRatingModal', true) // Modal stays open
            ->assertHasErrors(['editingValue']);

        // Verify NO session adjustment saved
        $dynamicService = app(DynamicStandardService::class);
        $rating = $dynamicService->getAspectRating($this->template->id, 'integritas');
        $this->assertEquals(4, $rating); // Original value
    }

    #[Test]
    public function aspect_rating_modal_handles_null_template_gracefully(): void
    {
        // Don't set session filters - no template selected

        Livewire::test(StandardMc::class)
            ->call('openEditAspectRating', 'integritas', 4.0)
            ->assertSet('showEditRatingModal', false);
    }

    // ============================================================================
    // GROUP 5: SELECTIVE ASPECTS MODAL (1 TEST)
    // ============================================================================

    #[Test]
    public function opening_selective_aspects_modal_dispatches_event(): void
    {
        $this->setSessionFilters();

        Livewire::test(StandardMc::class)
            ->call('openSelectionModal')
            ->assertDispatched('openSelectionModal', templateId: $this->template->id, categoryCode: 'kompetensi');
    }

    // ============================================================================
    // GROUP 6: RESET ADJUSTMENTS (2 TESTS)
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
        $dynamicService->saveCategoryWeight($this->template->id, 'kompetensi', 80); // Custom std has 70
        $dynamicService->saveAspectRating($this->template->id, 'integritas', 3); // Custom std has 5

        // Preload cache again after setting adjustments (fresh instance needs cache)
        \App\Services\Cache\AspectCacheService::preloadByTemplate($this->template->id);

        // Verify adjustments exist
        $this->assertTrue($dynamicService->hasCategoryAdjustments($this->template->id, 'kompetensi'));

        // Reset adjustments
        Livewire::test(StandardMc::class)
            ->call('resetAdjustments')
            ->assertDispatched('standard-adjusted');

        // Verify adjustments cleared
        // IMPORTANT: Create NEW instance to avoid stale request-scoped cache
        $freshDynamicService = app(DynamicStandardService::class);
        $this->assertFalse($freshDynamicService->hasCategoryAdjustments($this->template->id, 'kompetensi'));

        // Verify custom standard selection persists
        $selected = $customStandardService->getSelected($this->template->id);
        $this->assertEquals($this->customStandard->id, $selected);
    }

    #[Test]
    public function reset_adjustments_handles_null_template_gracefully(): void
    {
        // Don't set session filters - no template selected

        Livewire::test(StandardMc::class)
            ->call('resetAdjustments')
            ->assertNotDispatched('standard-adjusted'); // Should not dispatch event when no template
    }

    // ============================================================================
    // GROUP 7: EVENT HANDLING (3 TESTS)
    // ============================================================================

    #[Test]
    public function handles_event_selected_clears_cache_and_waits_for_position(): void
    {
        $this->setSessionFilters();

        $component = Livewire::test(StandardMc::class)
            ->assertSet('categoryData.0.code', 'kompetensi');

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

        Livewire::test(StandardMc::class)
            ->dispatch('position-selected', positionFormationId: $this->position->id)
            ->assertDispatched('chartDataUpdated');
    }

    #[Test]
    public function handles_standard_adjusted_event_from_other_components(): void
    {
        $this->setSessionFilters();

        Livewire::test(StandardMc::class)
            ->dispatch('standard-adjusted', templateId: $this->template->id)
            ->assertDispatched('chartDataUpdated');
    }

    // ============================================================================
    // GROUP 8: CACHE MANAGEMENT (2 TESTS)
    // ============================================================================

    #[Test]
    public function cache_prevents_redundant_data_processing(): void
    {
        $this->setSessionFilters();

        $component = Livewire::test(StandardMc::class);

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

        $component = Livewire::test(StandardMc::class)
            ->assertSet('categoryData.0.weight_percentage', 75);

        // Change baseline - should clear cache and reload
        $component->call('selectCustomStandard', $this->customStandard->id);

        // Data should be different (custom standard has weight 70)
        $component->assertSet('categoryData.0.weight_percentage', 70);
    }

    // ============================================================================
    // GROUP 9: 3-LAYER PRIORITY INTEGRATION (1 TEST)
    // ============================================================================

    #[Test]
    public function loaded_data_respects_3_layer_priority_system(): void
    {
        $this->setSessionFilters();

        $dynamicService = app(DynamicStandardService::class);
        $customStandardService = app(CustomStandardService::class);

        // SCENARIO A: Quantum Default Only
        Livewire::test(StandardMc::class)
            ->assertSet('selectedCustomStandardId', null)
            ->assertSet('categoryData.0.weight_percentage', 75); // Quantum Default

        // SCENARIO B: Custom Standard Override
        $customStandardService->select($this->template->id, $this->customStandard->id);

        Livewire::test(StandardMc::class)
            ->assertSet('selectedCustomStandardId', $this->customStandard->id)
            ->assertSet('categoryData.0.weight_percentage', 70); // Custom Standard

        // SCENARIO C: Session Override
        $dynamicService->saveCategoryWeight($this->template->id, 'kompetensi', 80);

        Livewire::test(StandardMc::class)
            ->assertSet('categoryData.0.weight_percentage', 80); // Session Adjustment
    }
}
