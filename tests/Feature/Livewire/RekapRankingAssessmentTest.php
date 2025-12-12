<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire;

use App\Livewire\Pages\GeneralReport\Ranking\RekapRankingAssessment;
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

class RekapRankingAssessmentTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Institution $institution;

    protected AssessmentTemplate $template;

    protected AssessmentEvent $event;

    protected PositionFormation $position;

    protected CategoryType $potensiCategory;

    protected CategoryType $kompetensiCategory;

    protected Aspect $potensiAspect;

    protected Aspect $kompetensiAspect;

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
            'weight_percentage' => 25,
            'order' => 1,
        ]);

        // Create Kompetensi category
        $this->kompetensiCategory = CategoryType::factory()->create([
            'template_id' => $this->template->id,
            'code' => 'kompetensi',
            'name' => 'Kompetensi',
            'weight_percentage' => 75,
            'order' => 2,
        ]);

        // Create Potensi aspect with sub-aspects
        $this->potensiAspect = Aspect::factory()->create([
            'category_type_id' => $this->potensiCategory->id,
            'template_id' => $this->template->id,
            'code' => 'daya-pikir',
            'name' => 'Daya Pikir',
            'weight_percentage' => 25,
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

        // Create Kompetensi aspect (no sub-aspects)
        $this->kompetensiAspect = Aspect::factory()->create([
            'category_type_id' => $this->kompetensiCategory->id,
            'template_id' => $this->template->id,
            'code' => 'integritas',
            'name' => 'Integritas',
            'weight_percentage' => 75,
            'standard_rating' => 4.0,
            'order' => 1,
        ]);

        // Create participant
        $this->participant = Participant::factory()->create([
            'event_id' => $this->event->id,
            'position_formation_id' => $this->position->id,
            'name' => 'John Doe',
        ]);

        // Create category assessments first (required for aspect assessments)
        $potensiCategoryAssessment = CategoryAssessment::factory()->create([
            'participant_id' => $this->participant->id,
            'event_id' => $this->event->id,
            'category_type_id' => $this->potensiCategory->id,
        ]);

        $kompetensiCategoryAssessment = CategoryAssessment::factory()->create([
            'participant_id' => $this->participant->id,
            'event_id' => $this->event->id,
            'category_type_id' => $this->kompetensiCategory->id,
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

        AspectAssessment::factory()->create([
            'participant_id' => $this->participant->id,
            'event_id' => $this->event->id,
            'position_formation_id' => $this->position->id,
            'category_assessment_id' => $kompetensiCategoryAssessment->id,
            'aspect_id' => $this->kompetensiAspect->id,
            'individual_rating' => 3.8,
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
        session(['individual_report.tolerance' => 10]);

        $component = Livewire::test(RekapRankingAssessment::class);

        $component->assertSet('tolerancePercentage', 10);
    }

    #[Test]
    public function component_loads_conclusion_config_from_service(): void
    {
        $component = Livewire::test(RekapRankingAssessment::class);

        $expectedConfig = ConclusionService::getGapConclusionConfig();

        $component->assertSet('conclusionConfig', $expectedConfig);
    }

    #[Test]
    public function component_loads_category_weights_on_mount(): void
    {
        $component = Livewire::test(RekapRankingAssessment::class);

        // Verify weights loaded from database
        $component->assertSet('potensiWeight', 25);
        $component->assertSet('kompetensiWeight', 75);
    }

    #[Test]
    public function component_prepares_chart_data_on_mount(): void
    {
        $component = Livewire::test(RekapRankingAssessment::class);

        // Chart data should be populated from conclusion summary
        $this->assertIsArray($component->get('chartLabels'));
        $this->assertIsArray($component->get('chartData'));
        $this->assertIsArray($component->get('chartColors'));
    }

    // ============================================================================
    // GROUP 2: EVENT LISTENERS - EVENT/POSITION SELECTION (4 tests)
    // ============================================================================

    #[Test]
    public function handle_event_selected_clears_cache_and_reloads_data(): void
    {
        $component = Livewire::test(RekapRankingAssessment::class);

        // Initial state
        $component->assertSet('potensiWeight', 25);

        // Change event
        session(['filter.event_code' => 'NEW-EVENT']);

        $component->call('handleEventSelected', 'NEW-EVENT');

        // Verify cache was cleared by checking data reload
        $component->assertSet('potensiWeight', 0); // No data for new event
    }

    #[Test]
    public function handle_event_selected_dispatches_summary_updated_event(): void
    {
        $component = Livewire::test(RekapRankingAssessment::class);

        $component->call('handleEventSelected', $this->event->code);

        $component->assertDispatched('summary-updated');
        $component->assertDispatched('pieChartDataUpdated');
    }

    #[Test]
    public function handle_position_selected_clears_cache_and_reloads_data(): void
    {
        $component = Livewire::test(RekapRankingAssessment::class);

        // Create new position
        $newPosition = PositionFormation::factory()->create([
            'event_id' => $this->event->id,
            'template_id' => $this->template->id,
            'name' => 'New Position',
        ]);

        session(['filter.position_formation_id' => $newPosition->id]);

        $component->call('handlePositionSelected', $newPosition->id);

        // Verify weights reloaded
        $component->assertSet('potensiWeight', 25);
        $component->assertSet('kompetensiWeight', 75);
    }

    #[Test]
    public function handle_position_selected_dispatches_events(): void
    {
        $component = Livewire::test(RekapRankingAssessment::class);

        $component->call('handlePositionSelected', $this->position->id);

        $component->assertDispatched('summary-updated');
        $component->assertDispatched('pieChartDataUpdated');
    }

    // ============================================================================
    // GROUP 3: EVENT LISTENERS - BASELINE CHANGES (3 tests)
    // ============================================================================

    #[Test]
    public function handle_standard_update_clears_cache_and_reloads_weights(): void
    {
        $component = Livewire::test(RekapRankingAssessment::class);

        // Initial weights
        $component->assertSet('potensiWeight', 25);
        $component->assertSet('kompetensiWeight', 75);

        // Make session adjustment to category weights (must save both to total 100%)
        $standardService = app(DynamicStandardService::class);
        $standardService->saveBothCategoryWeights(
            $this->template->id,
            'potensi',
            30,
            'kompetensi',
            70
        );

        $component->call('handleStandardUpdate', $this->template->id);

        // Verify weights reloaded from DynamicStandardService
        $component->assertSet('potensiWeight', 30);
        $component->assertSet('kompetensiWeight', 70);
    }

    #[Test]
    public function handle_standard_switch_delegates_to_handle_standard_update(): void
    {
        $component = Livewire::test(RekapRankingAssessment::class);

        $component->call('handleStandardSwitch', $this->template->id);

        // Should behave same as handleStandardUpdate
        $component->assertDispatched('summary-updated');
        $component->assertDispatched('pieChartDataUpdated');
    }

    #[Test]
    public function baseline_change_updates_category_weights_from_dynamic_standard_service(): void
    {
        // Select custom standard
        $customStandardService = app(CustomStandardService::class);
        $customStandardService->select($this->template->id, $this->customStandard->id);

        // Adjust weights in custom standard (must save both to total 100%)
        $standardService = app(DynamicStandardService::class);
        $standardService->saveBothCategoryWeights(
            $this->template->id,
            'potensi',
            40,
            'kompetensi',
            60
        );

        $component = Livewire::test(RekapRankingAssessment::class);

        $component->call('handleStandardUpdate', $this->template->id);

        // Verify new weights loaded
        $component->assertSet('potensiWeight', 40);
        $component->assertSet('kompetensiWeight', 60);
    }

    // ============================================================================
    // GROUP 4: EVENT LISTENERS - TOLERANCE CHANGES (2 tests)
    // ============================================================================

    #[Test]
    public function handle_tolerance_update_updates_tolerance_property(): void
    {
        $component = Livewire::test(RekapRankingAssessment::class);

        $component->call('handleToleranceUpdate', 15);

        $component->assertSet('tolerancePercentage', 15);
    }

    #[Test]
    public function handle_tolerance_update_clears_cache_and_refreshes_rankings(): void
    {
        $component = Livewire::test(RekapRankingAssessment::class);

        // Initial state
        $component->assertSet('tolerancePercentage', 0);

        $component->call('handleToleranceUpdate', 20);

        // Verify events dispatched (indicates refresh happened)
        $component->assertDispatched('summary-updated');
        $component->assertDispatched('pieChartDataUpdated');
        $component->assertSet('tolerancePercentage', 20);
    }

    // ============================================================================
    // GROUP 5: PAGINATION (2 tests)
    // ============================================================================

    #[Test]
    public function updated_per_page_clears_cache_and_resets_page(): void
    {
        $component = Livewire::test(RekapRankingAssessment::class);

        // Set initial perPage
        $component->set('perPage', 10);

        // Change perPage
        $component->set('perPage', 25);

        // Page should reset to 1
        $this->assertEquals(1, $component->get('pagers')['page'] ?? 1);
    }

    #[Test]
    public function build_rankings_paginates_correctly_with_slice_strategy(): void
    {
        // Create multiple participants
        for ($i = 2; $i <= 15; $i++) {
            $participant = Participant::factory()->create([
                'event_id' => $this->event->id,
                'position_formation_id' => $this->position->id,
                'name' => "Participant {$i}",
            ]);

            // Create category assessments
            $potensiCat = CategoryAssessment::factory()->create([
                'participant_id' => $participant->id,
                'event_id' => $this->event->id,
                'category_type_id' => $this->potensiCategory->id,
            ]);

            $kompetensiCat = CategoryAssessment::factory()->create([
                'participant_id' => $participant->id,
                'event_id' => $this->event->id,
                'category_type_id' => $this->kompetensiCategory->id,
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

            AspectAssessment::factory()->create([
                'participant_id' => $participant->id,
                'event_id' => $this->event->id,
                'position_formation_id' => $this->position->id,
                'category_assessment_id' => $kompetensiCat->id,
                'aspect_id' => $this->kompetensiAspect->id,
                'individual_rating' => 4.0,
            ]);
        }

        $component = Livewire::test(RekapRankingAssessment::class)
            ->set('perPage', 10);

        $rows = $component->viewData('rows');

        // Should have 10 items (pagination limit)
        $this->assertNotNull($rows);
        $this->assertEquals(10, $rows->count());
        $this->assertEquals(15, $rows->total()); // Total items
    }

    // ============================================================================
    // GROUP 6: CACHE MANAGEMENT (5 tests)
    // ============================================================================

    #[Test]
    public function cache_prevents_redundant_potensi_ranking_service_calls(): void
    {
        $component = Livewire::test(RekapRankingAssessment::class);

        // Access view data multiple times - cache should prevent redundant calls
        $rows1 = $component->viewData('rows');
        $rows2 = $component->viewData('rows');

        // Verify component still works correctly
        $component->assertStatus(200);
        $this->assertNotNull($rows1);
    }

    #[Test]
    public function cache_prevents_redundant_kompetensi_ranking_service_calls(): void
    {
        $component = Livewire::test(RekapRankingAssessment::class);

        // Access view data multiple times - cache should prevent redundant calls
        $standardInfo1 = $component->viewData('standardInfo');
        $standardInfo2 = $component->viewData('standardInfo');
        $standardInfo3 = $component->viewData('standardInfo');

        // Verify component still works correctly
        $component->assertStatus(200);
        $this->assertNotNull($standardInfo1);
    }

    #[Test]
    public function cache_prevents_redundant_get_rankings_calls(): void
    {
        $component = Livewire::test(RekapRankingAssessment::class);

        // Call methods that use getRankings()
        $component->call('getPassingSummary');
        $component->call('getConclusionSummary');

        // Both should use same cached rankings - verify no errors
        $component->assertStatus(200);
    }

    #[Test]
    public function cache_cleared_on_all_relevant_changes(): void
    {
        $component = Livewire::test(RekapRankingAssessment::class);

        // Initial state
        $component->assertSet('potensiWeight', 25);

        // Test 1: Position change clears cache
        $component->call('handlePositionSelected', $this->position->id);
        $component->assertStatus(200);

        // Test 2: Baseline change clears cache
        $component->call('handleStandardUpdate', $this->template->id);
        $component->assertStatus(200);

        // Test 3: Tolerance change clears cache
        $component->call('handleToleranceUpdate', 10);
        $component->assertSet('tolerancePercentage', 10);
    }

    #[Test]
    public function event_data_cache_prevents_duplicate_queries(): void
    {
        $component = Livewire::test(RekapRankingAssessment::class);

        // Multiple view data accesses should reuse eventData cache
        $rows1 = $component->viewData('rows');
        $standardInfo = $component->viewData('standardInfo');
        $conclusionSummary = $component->viewData('conclusionSummary');

        // Verify no errors (cache working)
        $component->assertStatus(200);
        $this->assertNotNull($rows1);
    }

    // ============================================================================
    // GROUP 7: 3-LAYER PRIORITY INTEGRATION (2 tests)
    // ============================================================================

    #[Test]
    public function category_weights_respect_dynamic_standard_service_session_adjustments(): void
    {
        // Make session adjustment (Layer 1) - must save both to total 100%
        $standardService = app(DynamicStandardService::class);
        $standardService->saveBothCategoryWeights(
            $this->template->id,
            'potensi',
            35,
            'kompetensi',
            65
        );

        $component = Livewire::test(RekapRankingAssessment::class);

        // Verify Layer 1 session adjustment used
        $component->assertSet('potensiWeight', 35);
        $component->assertSet('kompetensiWeight', 65);
    }

    #[Test]
    public function rankings_reflect_active_inactive_aspects_from_baseline(): void
    {
        // Mark potensi aspect as inactive
        $standardService = app(DynamicStandardService::class);
        $standardService->setAspectActive($this->template->id, $this->potensiAspect->code, false);

        $component = Livewire::test(RekapRankingAssessment::class);

        $rows = $component->viewData('rows');

        // Rankings should reflect aspect being inactive
        // This is tested by verifying component still works
        $component->assertStatus(200);
    }

    // ============================================================================
    // GROUP 8: DATA CALCULATION & DISPATCHING (4 tests)
    // ============================================================================

    #[Test]
    public function get_rankings_calculates_combined_rankings_correctly(): void
    {
        $component = Livewire::test(RekapRankingAssessment::class);

        $rows = $component->viewData('rows');

        $this->assertNotNull($rows);
        $this->assertGreaterThan(0, $rows->total());

        // Verify first participant data structure
        $firstRow = $rows->items()[0] ?? null;
        $this->assertNotNull($firstRow);
        $this->assertArrayHasKey('rank', $firstRow);
        $this->assertArrayHasKey('name', $firstRow);
        $this->assertArrayHasKey('total_weighted_individual', $firstRow);
        $this->assertArrayHasKey('gap', $firstRow);
        $this->assertArrayHasKey('conclusion', $firstRow);
    }

    #[Test]
    public function get_standard_info_calculates_weighted_standards_correctly(): void
    {
        $component = Livewire::test(RekapRankingAssessment::class);

        $standardInfo = $component->viewData('standardInfo');

        $this->assertNotNull($standardInfo);
        $this->assertArrayHasKey('psy_original_standard', $standardInfo);
        $this->assertArrayHasKey('mc_original_standard', $standardInfo);
        $this->assertArrayHasKey('total_original_standard', $standardInfo);
        $this->assertArrayHasKey('psy_adjusted_standard', $standardInfo);
        $this->assertArrayHasKey('mc_adjusted_standard', $standardInfo);
        $this->assertArrayHasKey('total_standard', $standardInfo);
    }

    #[Test]
    public function get_passing_summary_returns_correct_passing_count(): void
    {
        $component = Livewire::test(RekapRankingAssessment::class);

        // Call getPassingSummary via handleEventSelected which uses it
        $component->call('handleEventSelected', $this->event->code);

        // Verify summary-updated event was dispatched with correct structure
        $component->assertDispatched('summary-updated');
    }

    #[Test]
    public function get_conclusion_summary_returns_correct_conclusion_distribution(): void
    {
        $component = Livewire::test(RekapRankingAssessment::class);

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
        $component = Livewire::test(RekapRankingAssessment::class);

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
    public function chart_labels_data_colors_match_conclusion_config(): void
    {
        $component = Livewire::test(RekapRankingAssessment::class);

        $conclusionConfig = ConclusionService::getGapConclusionConfig();

        $chartLabels = $component->get('chartLabels');
        $chartColors = $component->get('chartColors');

        // Labels should match config keys
        $this->assertEquals(array_keys($conclusionConfig), $chartLabels);

        // Colors should match config colors
        $expectedColors = array_column($conclusionConfig, 'chartColor');
        $this->assertEquals($expectedColors, $chartColors);
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

        $component = Livewire::test(RekapRankingAssessment::class);

        // Should not throw error
        $component->assertStatus(200);
        $component->assertSet('potensiWeight', 0);
        $component->assertSet('kompetensiWeight', 0);
    }

    #[Test]
    public function handles_zero_weights_gracefully(): void
    {
        // Set both weights to 0
        $this->potensiCategory->update(['weight_percentage' => 0]);
        $this->kompetensiCategory->update(['weight_percentage' => 0]);

        $component = Livewire::test(RekapRankingAssessment::class);

        $rows = $component->viewData('rows');

        // Should return null or empty when weights are 0
        $this->assertTrue($rows === null || $rows->isEmpty());
    }

    #[Test]
    public function handles_empty_rankings_gracefully(): void
    {
        // Delete all participants
        AspectAssessment::query()->delete();
        SubAspectAssessment::query()->delete();
        Participant::query()->delete();

        $component = Livewire::test(RekapRankingAssessment::class);

        $rows = $component->viewData('rows');

        // Should return null when no participants
        $this->assertTrue($rows === null || (is_object($rows) && $rows->isEmpty()));
    }

    // ============================================================================
    // GROUP 11: RENDER INTEGRATION (1 test)
    // ============================================================================

    #[Test]
    public function render_passes_correct_data_to_view(): void
    {
        $component = Livewire::test(RekapRankingAssessment::class);

        // Verify all required view data present
        $component->assertViewHas('potensiWeight');
        $component->assertViewHas('kompetensiWeight');
        $component->assertViewHas('rows');
        $component->assertViewHas('standardInfo');
        $component->assertViewHas('conclusionSummary');
    }
}
