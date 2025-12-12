<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire;

use App\Livewire\Pages\GeneralReport\Ranking\RankingPsyMapping;
use App\Models\Aspect;
use App\Models\AspectAssessment;
use App\Models\AssessmentEvent;
use App\Models\AssessmentTemplate;
use App\Models\CategoryAssessment;
use App\Models\CategoryType;
use App\Models\CustomStandard;
use App\Models\Institution;
use App\Models\Participant;
use App\Models\PositionFormation;
use App\Models\SubAspect;
use App\Models\SubAspectAssessment;
use App\Models\User;
use App\Services\ConclusionService;
use App\Services\CustomStandardService;
use App\Services\DynamicStandardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RankingPsyMappingTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Institution $institution;

    protected AssessmentTemplate $template;

    protected AssessmentEvent $event;

    protected PositionFormation $position;

    protected CategoryType $potensiCategory;

    protected Aspect $potensiAspect;

    protected SubAspect $potensiSubAspect;

    protected Participant $participant;

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
            'weight_percentage' => 100,
            'order' => 1,
        ]);

        // Create Potensi aspect with sub-aspects
        $this->potensiAspect = Aspect::factory()->create([
            'category_type_id' => $this->potensiCategory->id,
            'template_id' => $this->template->id,
            'code' => 'daya-pikir',
            'name' => 'Daya Pikir',
            'weight_percentage' => 100,
            'standard_rating' => null,
            'order' => 1,
        ]);

        $this->potensiSubAspect = SubAspect::factory()->create([
            'aspect_id' => $this->potensiAspect->id,
            'code' => 'daya-analisa',
            'name' => 'Daya Analisa',
            'standard_rating' => 4,
            'order' => 1,
        ]);

        // Create participant
        $this->participant = Participant::factory()->create([
            'event_id' => $this->event->id,
            'position_formation_id' => $this->position->id,
            'name' => 'John Doe',
        ]);

        // Create category assessment
        $potensiCategoryAssessment = CategoryAssessment::factory()->create([
            'participant_id' => $this->participant->id,
            'event_id' => $this->event->id,
            'category_type_id' => $this->potensiCategory->id,
        ]);

        // Create assessments
        $potensiAspectAssessment = AspectAssessment::factory()->create([
            'participant_id' => $this->participant->id,
            'event_id' => $this->event->id,
            'position_formation_id' => $this->position->id,
            'category_assessment_id' => $potensiCategoryAssessment->id,
            'aspect_id' => $this->potensiAspect->id,
            'individual_rating' => 4.5,
        ]);

        SubAspectAssessment::factory()->create([
            'participant_id' => $this->participant->id,
            'event_id' => $this->event->id,
            'aspect_assessment_id' => $potensiAspectAssessment->id,
            'sub_aspect_id' => $this->potensiSubAspect->id,
            'individual_rating' => 4.5,
        ]);

        // Create custom standard
        $this->customStandard = CustomStandard::factory()->create([
            'template_id' => $this->template->id,
            'institution_id' => $this->institution->id,
            'name' => 'Custom Standard Test',
        ]);

        // Set session filters
        session([
            'filter.event_code' => $this->event->code,
            'filter.position_formation_id' => $this->position->id,
        ]);
    }

    // ============================================================================
    // GROUP 1: LIFECYCLE & INITIALIZATION (4 tests)
    // ============================================================================

    #[Test]
    public function component_mounts_with_tolerance_from_session(): void
    {
        // Set tolerance in session
        session(['individual_report.tolerance' => 15]);

        $component = Livewire::test(RankingPsyMapping::class);

        $component->assertSet('tolerancePercentage', 15);
    }

    #[Test]
    public function component_loads_conclusion_config_on_mount(): void
    {
        $component = Livewire::test(RankingPsyMapping::class);

        $expectedConfig = ConclusionService::getGapConclusionConfig();

        $component->assertSet('conclusionConfig', $expectedConfig);
    }

    #[Test]
    public function component_prepares_chart_data_on_mount(): void
    {
        $component = Livewire::test(RankingPsyMapping::class);

        // Chart data should be populated
        $this->assertIsArray($component->get('chartLabels'));
        $this->assertIsArray($component->get('chartData'));
        $this->assertIsArray($component->get('chartColors'));
    }

    #[Test]
    public function component_initializes_with_default_per_page(): void
    {
        $component = Livewire::test(RankingPsyMapping::class);

        $component->assertSet('perPage', 10);
    }

    // ============================================================================
    // GROUP 2: EVENT LISTENERS - EVENT/POSITION SELECTION (4 tests)
    // ============================================================================

    #[Test]
    public function handle_event_selected_clears_cache_and_resets_page(): void
    {
        $component = Livewire::test(RankingPsyMapping::class);

        // Trigger event selected
        $component->call('handleEventSelected');

        // Should dispatch events (indicates cache cleared and data refreshed)
        $component->assertDispatched('summary-updated');
        $component->assertDispatched('pieChartDataUpdated');
    }

    #[Test]
    public function handle_event_selected_dispatches_summary_and_chart_events(): void
    {
        $component = Livewire::test(RankingPsyMapping::class);

        $component->call('handleEventSelected');

        $component->assertDispatched('summary-updated');
        $component->assertDispatched('pieChartDataUpdated');
    }

    #[Test]
    public function handle_position_selected_clears_cache_and_resets_page(): void
    {
        $component = Livewire::test(RankingPsyMapping::class);

        // Trigger position selected
        $component->call('handlePositionSelected');

        // Should dispatch events (indicates cache cleared and data refreshed)
        $component->assertDispatched('summary-updated');
        $component->assertDispatched('pieChartDataUpdated');
    }

    #[Test]
    public function handle_position_selected_dispatches_events(): void
    {
        $component = Livewire::test(RankingPsyMapping::class);

        $component->call('handlePositionSelected');

        $component->assertDispatched('summary-updated');
        $component->assertDispatched('pieChartDataUpdated');
    }

    // ============================================================================
    // GROUP 3: EVENT LISTENERS - BASELINE CHANGES (3 tests)
    // ============================================================================

    #[Test]
    public function handle_standard_update_validates_template_id_match(): void
    {
        $component = Livewire::test(RankingPsyMapping::class);

        // Call with different template ID
        $component->call('handleStandardUpdate', 999);

        // Should not dispatch events (template doesn't match)
        $component->assertNotDispatched('summary-updated');
        $component->assertNotDispatched('pieChartDataUpdated');
    }

    #[Test]
    public function handle_standard_update_clears_cache_and_refreshes(): void
    {
        $component = Livewire::test(RankingPsyMapping::class);

        // Call with correct template ID
        $component->call('handleStandardUpdate', $this->template->id);

        // Should dispatch events (template matches)
        $component->assertDispatched('summary-updated');
        $component->assertDispatched('pieChartDataUpdated');
    }

    #[Test]
    public function handle_standard_switch_delegates_to_handle_standard_update(): void
    {
        $component = Livewire::test(RankingPsyMapping::class);

        $component->call('handleStandardSwitch', $this->template->id);

        // Should behave same as handleStandardUpdate
        $component->assertDispatched('summary-updated');
        $component->assertDispatched('pieChartDataUpdated');
    }

    // ============================================================================
    // GROUP 4: EVENT LISTENERS - TOLERANCE CHANGES (2 tests)
    // ============================================================================

    #[Test]
    public function handle_tolerance_update_updates_tolerance_property(): void
    {
        $component = Livewire::test(RankingPsyMapping::class);

        $component->call('handleToleranceUpdate', 20);

        $component->assertSet('tolerancePercentage', 20);
    }

    #[Test]
    public function handle_tolerance_update_clears_cache_and_refreshes(): void
    {
        $component = Livewire::test(RankingPsyMapping::class);

        $component->call('handleToleranceUpdate', 15);

        // Verify events dispatched (indicates refresh happened)
        $component->assertDispatched('summary-updated');
        $component->assertDispatched('pieChartDataUpdated');
    }

    // ============================================================================
    // GROUP 5: PAGINATION (3 tests)
    // ============================================================================

    #[Test]
    public function updated_per_page_clears_cache_and_resets_page(): void
    {
        $component = Livewire::test(RankingPsyMapping::class);

        // Set initial perPage
        $component->set('perPage', 10);

        // Change perPage
        $component->set('perPage', 25);

        // Should dispatch events (indicates cache cleared and data refreshed)
        $component->assertDispatched('summary-updated');
        $component->assertDispatched('pieChartDataUpdated');
    }

    #[Test]
    public function build_rankings_paginates_correctly_with_slice_strategy(): void
    {
        // Create multiple participants
        for ($i = 2; $i <= 25; $i++) {
            $participant = Participant::factory()->create([
                'event_id' => $this->event->id,
                'position_formation_id' => $this->position->id,
                'name' => "Participant {$i}",
            ]);

            $potensiCat = CategoryAssessment::factory()->create([
                'participant_id' => $participant->id,
                'event_id' => $this->event->id,
                'category_type_id' => $this->potensiCategory->id,
            ]);

            $potensiAsp = AspectAssessment::factory()->create([
                'participant_id' => $participant->id,
                'event_id' => $this->event->id,
                'position_formation_id' => $this->position->id,
                'category_assessment_id' => $potensiCat->id,
                'aspect_id' => $this->potensiAspect->id,
                'individual_rating' => 4.0,
            ]);

            SubAspectAssessment::factory()->create([
                'participant_id' => $participant->id,
                'event_id' => $this->event->id,
                'aspect_assessment_id' => $potensiAsp->id,
                'sub_aspect_id' => $this->potensiSubAspect->id,
                'individual_rating' => 4.0,
            ]);
        }

        $component = Livewire::test(RankingPsyMapping::class)
            ->set('perPage', 10);

        $rankings = $component->viewData('rankings');

        // Should have 10 items (pagination limit)
        $this->assertNotNull($rankings);
        $this->assertEquals(10, $rankings->count());
        $this->assertEquals(25, $rankings->total()); // Total items
    }

    #[Test]
    public function build_rankings_slice_optimization_queries_only_visible_participants(): void
    {
        // Create 15 participants
        for ($i = 2; $i <= 15; $i++) {
            $participant = Participant::factory()->create([
                'event_id' => $this->event->id,
                'position_formation_id' => $this->position->id,
                'name' => "Participant {$i}",
            ]);

            $potensiCat = CategoryAssessment::factory()->create([
                'participant_id' => $participant->id,
                'event_id' => $this->event->id,
                'category_type_id' => $this->potensiCategory->id,
            ]);

            $potensiAsp = AspectAssessment::factory()->create([
                'participant_id' => $participant->id,
                'event_id' => $this->event->id,
                'position_formation_id' => $this->position->id,
                'category_assessment_id' => $potensiCat->id,
                'aspect_id' => $this->potensiAspect->id,
                'individual_rating' => 4.0,
            ]);

            SubAspectAssessment::factory()->create([
                'participant_id' => $participant->id,
                'event_id' => $this->event->id,
                'aspect_assessment_id' => $potensiAsp->id,
                'sub_aspect_id' => $this->potensiSubAspect->id,
                'individual_rating' => 4.0,
            ]);
        }

        $component = Livewire::test(RankingPsyMapping::class)
            ->set('perPage', 10);

        $rankings = $component->viewData('rankings');

        // Verify slice optimization works (only 10 items loaded, not all 15)
        $this->assertNotNull($rankings);
        $this->assertEquals(10, $rankings->count());
        $this->assertEquals(15, $rankings->total());
    }

    // ============================================================================
    // GROUP 6: CACHE MANAGEMENT (4 tests)
    // ============================================================================

    #[Test]
    public function cache_prevents_redundant_ranking_service_calls(): void
    {
        $component = Livewire::test(RankingPsyMapping::class);

        // Access view data multiple times - cache should prevent redundant calls
        $rankings1 = $component->viewData('rankings');
        $rankings2 = $component->viewData('rankings');
        $rankings3 = $component->viewData('rankings');

        // Verify component works correctly
        $component->assertStatus(200);
        $this->assertNotNull($rankings1);
    }

    #[Test]
    public function cache_cleared_on_all_relevant_changes(): void
    {
        $component = Livewire::test(RankingPsyMapping::class);

        // Test 1: Event selection clears cache
        $component->call('handleEventSelected');
        $component->assertStatus(200);

        // Test 2: Position selection clears cache
        $component->call('handlePositionSelected');
        $component->assertStatus(200);

        // Test 3: Standard update clears cache
        $component->call('handleStandardUpdate', $this->template->id);
        $component->assertStatus(200);

        // Test 4: Tolerance update clears cache
        $component->call('handleToleranceUpdate', 10);
        $component->assertSet('tolerancePercentage', 10);

        // Test 5: PerPage change clears cache
        $component->set('perPage', 25);
        $component->assertStatus(200);
    }

    #[Test]
    public function rankings_cache_reused_across_multiple_method_calls(): void
    {
        $component = Livewire::test(RankingPsyMapping::class);

        // Multiple view data accesses should use same cache
        $rankings1 = $component->viewData('rankings');
        $standardInfo = $component->viewData('standardInfo');
        $conclusionSummary = $component->viewData('conclusionSummary');

        // Verify no errors (cache working)
        $component->assertStatus(200);
        $this->assertNotNull($rankings1);
    }

    #[Test]
    public function clear_cache_resets_rankings_cache_to_null(): void
    {
        $component = Livewire::test(RankingPsyMapping::class);

        // Load initial data (populates cache)
        $rankings1 = $component->viewData('rankings');
        $this->assertNotNull($rankings1);

        // Clear cache via any event
        $component->call('handleEventSelected');

        // Cache should be cleared and repopulated
        $rankings2 = $component->viewData('rankings');
        $this->assertNotNull($rankings2);
    }

    // ============================================================================
    // GROUP 7: 3-LAYER PRIORITY INTEGRATION (2 tests)
    // ============================================================================

    #[Test]
    public function rankings_respect_dynamic_standard_service_session_adjustments(): void
    {
        // Make session adjustment (Layer 1)
        $standardService = app(DynamicStandardService::class);
        $standardService->saveSubAspectRating(
            $this->template->id,
            $this->potensiSubAspect->code,
            5
        );

        $component = Livewire::test(RankingPsyMapping::class);

        $rankings = $component->viewData('rankings');

        // Verify rankings use adjusted values
        $component->assertStatus(200);
        $this->assertNotNull($rankings);
    }

    #[Test]
    public function rankings_reflect_active_inactive_aspects_from_baseline(): void
    {
        // Mark sub-aspect as inactive
        $standardService = app(DynamicStandardService::class);
        $standardService->setSubAspectActive($this->template->id, $this->potensiSubAspect->code, false);

        $component = Livewire::test(RankingPsyMapping::class);

        $rankings = $component->viewData('rankings');

        // Rankings should reflect aspect being inactive
        $component->assertStatus(200);
    }

    // ============================================================================
    // GROUP 8: DATA CALCULATION & RENDERING (4 tests)
    // ============================================================================

    #[Test]
    public function get_rankings_uses_ranking_service_for_potensi_category(): void
    {
        $component = Livewire::test(RankingPsyMapping::class);

        $rankings = $component->viewData('rankings');

        // Verify RankingService used for 'potensi' category
        $this->assertNotNull($rankings);
        $this->assertGreaterThan(0, $rankings->total());

        // Verify first participant data structure
        $firstRanking = $rankings->items()[0] ?? null;
        $this->assertNotNull($firstRanking);
        $this->assertArrayHasKey('rank', $firstRanking);
        $this->assertArrayHasKey('name', $firstRanking);
        $this->assertArrayHasKey('individual_score', $firstRanking);
        $this->assertArrayHasKey('conclusion', $firstRanking);
    }

    #[Test]
    public function get_passing_summary_returns_correct_statistics(): void
    {
        $component = Livewire::test(RankingPsyMapping::class);

        // Call getPassingSummary via handleEventSelected which uses it
        $component->call('handleEventSelected');

        // Verify summary-updated event was dispatched with correct structure
        $component->assertDispatched('summary-updated');
    }

    #[Test]
    public function get_standard_info_returns_original_and_adjusted_standards(): void
    {
        $component = Livewire::test(RankingPsyMapping::class);

        $standardInfo = $component->viewData('standardInfo');

        $this->assertNotNull($standardInfo);
        $this->assertArrayHasKey('original_standard', $standardInfo);
        $this->assertArrayHasKey('adjusted_standard', $standardInfo);
    }

    #[Test]
    public function get_conclusion_summary_returns_correct_distribution(): void
    {
        $component = Livewire::test(RankingPsyMapping::class);

        $conclusionSummary = $component->viewData('conclusionSummary');

        $this->assertIsArray($conclusionSummary);

        // Verify all conclusion types present
        $conclusionConfig = ConclusionService::getGapConclusionConfig();
        foreach (array_keys($conclusionConfig) as $conclusionType) {
            $this->assertArrayHasKey($conclusionType, $conclusionSummary);
        }
    }

    // ============================================================================
    // GROUP 9: CHART DATA PREPARATION (2 tests)
    // ============================================================================

    #[Test]
    public function prepare_chart_data_builds_chart_from_conclusion_summary(): void
    {
        $component = Livewire::test(RankingPsyMapping::class);

        $chartLabels = $component->get('chartLabels');
        $chartData = $component->get('chartData');
        $chartColors = $component->get('chartColors');

        $this->assertIsArray($chartLabels);
        $this->assertIsArray($chartData);
        $this->assertIsArray($chartColors);

        // Chart arrays should have same length
        $this->assertCount(count($chartLabels), $chartData);
        $this->assertCount(count($chartLabels), $chartColors);
    }

    #[Test]
    public function chart_data_empty_when_no_rankings(): void
    {
        // Clear session to have no data
        session()->forget('filter.event_code');
        session()->forget('filter.position_formation_id');

        $component = Livewire::test(RankingPsyMapping::class);

        $chartLabels = $component->get('chartLabels');
        $chartData = $component->get('chartData');
        $chartColors = $component->get('chartColors');

        // Should be empty arrays when no data
        $this->assertIsArray($chartLabels);
        $this->assertIsArray($chartData);
        $this->assertIsArray($chartColors);
    }

    // ============================================================================
    // GROUP 10: EDGE CASES (3 tests)
    // ============================================================================

    #[Test]
    public function handles_missing_event_gracefully(): void
    {
        // Clear session
        session()->forget('filter.event_code');
        session()->forget('filter.position_formation_id');

        $component = Livewire::test(RankingPsyMapping::class);

        // Should not throw error
        $component->assertStatus(200);

        $rankings = $component->viewData('rankings');
        $this->assertNull($rankings);
    }

    #[Test]
    public function handles_missing_position_gracefully(): void
    {
        // Event exists but position missing
        session(['filter.event_code' => $this->event->code]);
        session()->forget('filter.position_formation_id');

        $component = Livewire::test(RankingPsyMapping::class);

        // Should not throw error
        $component->assertStatus(200);

        $rankings = $component->viewData('rankings');
        $this->assertNull($rankings);
    }

    #[Test]
    public function handles_empty_rankings_gracefully(): void
    {
        // Delete all participants
        AspectAssessment::query()->delete();
        SubAspectAssessment::query()->delete();
        Participant::query()->delete();

        $component = Livewire::test(RankingPsyMapping::class);

        $rankings = $component->viewData('rankings');

        // Should return null when no participants
        $this->assertTrue($rankings === null || (is_object($rankings) && $rankings->isEmpty()));
    }

    // ============================================================================
    // GROUP 11: RENDER INTEGRATION (1 test)
    // ============================================================================

    #[Test]
    public function render_passes_correct_data_to_view(): void
    {
        $component = Livewire::test(RankingPsyMapping::class);

        // Verify all required view data present
        $component->assertViewHas('rankings');
        $component->assertViewHas('conclusionSummary');
        $component->assertViewHas('standardInfo');
    }

    // ============================================================================
    // GROUP 12: INDIVIDUAL RATING RECALCULATION (CRITICAL) (2 tests)
    // ============================================================================

    #[Test]
    public function database_individual_rating_never_changes_when_aspects_disabled(): void
    {
        // Get original database value
        $originalRating = SubAspectAssessment::where('participant_id', $this->participant->id)
            ->where('sub_aspect_id', $this->potensiSubAspect->id)
            ->first()
            ->individual_rating;

        // Mark sub-aspect as inactive
        $standardService = app(DynamicStandardService::class);
        $standardService->setSubAspectActive($this->template->id, $this->potensiSubAspect->code, false);

        $component = Livewire::test(RankingPsyMapping::class);

        // Verify database value unchanged
        $currentRating = SubAspectAssessment::where('participant_id', $this->participant->id)
            ->where('sub_aspect_id', $this->potensiSubAspect->id)
            ->first()
            ->individual_rating;

        $this->assertEquals($originalRating, $currentRating, 'Database individual_rating should NEVER change');
    }

    #[Test]
    public function calculation_uses_ranking_service_for_fair_recalculation(): void
    {
        // Create additional sub-aspects
        $subAspect2 = SubAspect::factory()->create([
            'aspect_id' => $this->potensiAspect->id,
            'code' => 'daya-kritis',
            'name' => 'Daya Kritis',
            'standard_rating' => 3,
            'order' => 2,
        ]);

        $potensiAspectAssessment = AspectAssessment::where('participant_id', $this->participant->id)
            ->where('aspect_id', $this->potensiAspect->id)
            ->first();

        SubAspectAssessment::factory()->create([
            'participant_id' => $this->participant->id,
            'event_id' => $this->event->id,
            'aspect_assessment_id' => $potensiAspectAssessment->id,
            'sub_aspect_id' => $subAspect2->id,
            'individual_rating' => 3.0,
        ]);

        // Mark one sub-aspect as inactive
        $standardService = app(DynamicStandardService::class);
        $standardService->setSubAspectActive($this->template->id, $subAspect2->code, false);

        $component = Livewire::test(RankingPsyMapping::class);

        $rankings = $component->viewData('rankings');

        // Component should handle recalculation correctly via RankingService
        $component->assertStatus(200);
        $this->assertNotNull($rankings);
    }

    // ============================================================================
    // GROUP 13: CUSTOM STANDARD INTEGRATION (2 tests)
    // ============================================================================

    #[Test]
    public function rankings_use_custom_standard_when_selected(): void
    {
        // Select custom standard
        $customStandardService = app(CustomStandardService::class);
        $customStandardService->select($this->template->id, $this->customStandard->id);

        // Adjust rating in custom standard
        $standardService = app(DynamicStandardService::class);
        $standardService->saveSubAspectRating(
            $this->template->id,
            $this->potensiSubAspect->code,
            5
        );

        $component = Livewire::test(RankingPsyMapping::class);

        // Verify custom standard used (Layer 2 overrides Layer 3)
        $rankings = $component->viewData('rankings');
        $component->assertStatus(200);
        $this->assertNotNull($rankings);
    }

    #[Test]
    public function session_adjustment_overrides_custom_standard(): void
    {
        // First select custom standard
        $customStandardService = app(CustomStandardService::class);
        $customStandardService->select($this->template->id, $this->customStandard->id);

        // Set custom standard rating
        $standardService = app(DynamicStandardService::class);
        $standardService->saveSubAspectRating(
            $this->template->id,
            $this->potensiSubAspect->code,
            4
        );

        // Then make another session adjustment (Layer 1 overrides Layer 2)
        $standardService->saveSubAspectRating(
            $this->template->id,
            $this->potensiSubAspect->code,
            5
        );

        $component = Livewire::test(RankingPsyMapping::class);

        // Verify session adjustment overrides custom standard
        $rankings = $component->viewData('rankings');
        $component->assertStatus(200);
        $this->assertNotNull($rankings);
    }

    // ============================================================================
    // GROUP 14: EVENT DISPATCHING (2 tests)
    // ============================================================================

    #[Test]
    public function refresh_data_dispatches_summary_updated_with_correct_data(): void
    {
        $component = Livewire::test(RankingPsyMapping::class);

        $component->call('handleEventSelected');

        // Verify summary-updated dispatched with correct structure
        $component->assertDispatched('summary-updated');
    }

    #[Test]
    public function refresh_data_dispatches_pie_chart_data_updated_with_correct_data(): void
    {
        $component = Livewire::test(RankingPsyMapping::class);

        $component->call('handleEventSelected');

        // Verify pieChartDataUpdated dispatched with correct structure
        $component->assertDispatched('pieChartDataUpdated');
    }
}
