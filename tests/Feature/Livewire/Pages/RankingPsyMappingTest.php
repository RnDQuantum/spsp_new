<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Pages;

use App\Livewire\Pages\GeneralReport\Ranking\RankingPsyMapping;
use App\Models\Aspect;
use App\Models\AspectAssessment;
use App\Models\AssessmentEvent;
use App\Models\AssessmentTemplate;
use App\Models\Batch;
use App\Models\CategoryType;
use App\Models\Institution;
use App\Models\Participant;
use App\Models\PositionFormation;
use App\Services\DynamicStandardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * RankingPsyMapping Component Integration Tests
 *
 * Tests the RankingPsyMapping Livewire component's integration with
 * the optimized RankingService cache layer.
 *
 * Coverage:
 * - Cache behavior (cold start vs warm)
 * - Tolerance instant update (no cache miss)
 * - Pagination with cached data
 * - Cache invalidation on standard adjustment
 * - Custom standard switch invalidation
 */
class RankingPsyMappingTest extends TestCase
{
    use RefreshDatabase;

    protected AssessmentEvent $event;

    protected PositionFormation $position;

    protected AssessmentTemplate $template;

    protected CategoryType $potensiCategory;

    protected Aspect $aspect1;

    protected Aspect $aspect2;

    protected array $participants = [];

    protected function setUp(): void
    {
        parent::setUp();

        // Create test data
        $institution = Institution::factory()->create();
        $this->event = AssessmentEvent::factory()->create([
            'institution_id' => $institution->id,
        ]);
        $batch = Batch::factory()->create([
            'event_id' => $this->event->id,
        ]);

        $this->template = AssessmentTemplate::factory()->create([
            'institution_id' => $institution->id,
        ]);

        $this->position = PositionFormation::factory()->create([
            'template_id' => $this->template->id,
            'event_id' => $this->event->id,
        ]);

        // Create Potensi category
        $this->potensiCategory = CategoryType::factory()->create([
            'template_id' => $this->template->id,
            'code' => 'potensi',
            'name' => 'Potensi',
            'weight' => 50,
        ]);

        // Create aspects
        $this->aspect1 = Aspect::factory()->create([
            'category_type_id' => $this->potensiCategory->id,
            'code' => 'ASP001',
            'name' => 'Aspek 1',
            'weight' => 3,
            'standard_rating' => 3.5,
            'order' => 1,
        ]);

        $this->aspect2 = Aspect::factory()->create([
            'category_type_id' => $this->potensiCategory->id,
            'code' => 'ASP002',
            'name' => 'Aspek 2',
            'weight' => 2,
            'standard_rating' => 4.0,
            'order' => 2,
        ]);

        // Create participants (10 for testing)
        for ($i = 1; $i <= 10; $i++) {
            $participant = Participant::factory()->create([
                'event_id' => $this->event->id,
                'batch_id' => $batch->id,
                'position_formation_id' => $this->position->id,
                'test_number' => str_pad((string) $i, 3, '0', STR_PAD_LEFT),
                'name' => "Participant {$i}",
            ]);

            // Create assessments for each aspect
            AspectAssessment::create([
                'participant_id' => $participant->id,
                'event_id' => $this->event->id,
                'position_formation_id' => $this->position->id,
                'aspect_id' => $this->aspect1->id,
                'individual_rating' => 3.0 + ($i * 0.1), // Varying scores
            ]);

            AspectAssessment::create([
                'participant_id' => $participant->id,
                'event_id' => $this->event->id,
                'position_formation_id' => $this->position->id,
                'aspect_id' => $this->aspect2->id,
                'individual_rating' => 3.5 + ($i * 0.1), // Varying scores
            ]);

            $this->participants[] = $participant;
        }

        // Set session filter
        session([
            'filter.event_code' => $this->event->code,
            'filter.position_formation_id' => $this->position->id,
            'individual_report.tolerance' => 0,
        ]);
    }

    /** @test */
    public function it_loads_rankings_on_cold_start()
    {
        // Clear cache to simulate cold start
        Cache::flush();

        // Track queries
        DB::enableQueryLog();

        $component = Livewire::test(RankingPsyMapping::class);

        $queries = DB::getQueryLog();

        // Should have queries on cold start
        $this->assertGreaterThan(0, count($queries));

        // Component should render successfully
        $component->assertStatus(200);
    }

    /** @test */
    public function it_uses_cached_rankings_on_warm_load()
    {
        // First load - populate cache
        Livewire::test(RankingPsyMapping::class);

        // Clear query log
        DB::flushQueryLog();
        DB::enableQueryLog();

        // Second load - should use cache
        Livewire::test(RankingPsyMapping::class);

        $queries = DB::getQueryLog();

        // Should have minimal queries (no aspect_assessments query)
        // Only session, filter, and basic component queries
        $aspectAssessmentQueries = collect($queries)->filter(function ($query) {
            return str_contains($query['query'], 'aspect_assessments');
        });

        $this->assertCount(0, $aspectAssessmentQueries, 'Should not query aspect_assessments on cached load');
    }

    /** @test */
    public function it_updates_tolerance_instantly_without_cache_miss()
    {
        // First load - populate cache
        $component = Livewire::test(RankingPsyMapping::class);

        // Clear query log
        DB::flushQueryLog();
        DB::enableQueryLog();

        // Update tolerance
        $component->call('handleToleranceUpdate', 10);

        $queries = DB::getQueryLog();

        // Should NOT have aspect_assessments queries (tolerance applied after cache)
        $aspectAssessmentQueries = collect($queries)->filter(function ($query) {
            return str_contains($query['query'], 'aspect_assessments');
        });

        $this->assertCount(0, $aspectAssessmentQueries, 'Tolerance update should not trigger cache miss');
    }

    /** @test */
    public function it_paginates_with_cached_data()
    {
        // First load - populate cache
        Livewire::test(RankingPsyMapping::class);

        // Clear query log
        DB::flushQueryLog();
        DB::enableQueryLog();

        // Navigate to page 2
        $component = Livewire::test(RankingPsyMapping::class)
            ->set('page', 2);

        $queries = DB::getQueryLog();

        // Should NOT have aspect_assessments queries (data from cache)
        $aspectAssessmentQueries = collect($queries)->filter(function ($query) {
            return str_contains($query['query'], 'aspect_assessments');
        });

        $this->assertCount(0, $aspectAssessmentQueries, 'Pagination should use cached data');
    }

    /** @test */
    public function it_invalidates_cache_on_standard_adjustment()
    {
        // First load - populate cache
        Livewire::test(RankingPsyMapping::class);

        // Adjust standard via DynamicStandardService (session adjustment)
        $standardService = app(DynamicStandardService::class);
        $standardService->setAspectWeight($this->template->id, 'ASP001', 5.0);

        // Clear query log
        DB::flushQueryLog();
        DB::enableQueryLog();

        // Second load - should re-compute due to config hash change
        Livewire::test(RankingPsyMapping::class);

        $queries = DB::getQueryLog();

        // Should have aspect_assessments queries (cache miss)
        $aspectAssessmentQueries = collect($queries)->filter(function ($query) {
            return str_contains($query['query'], 'aspect_assessments');
        });

        $this->assertGreaterThan(0, $aspectAssessmentQueries->count(), 'Standard adjustment should invalidate cache');
    }

    /** @test */
    public function it_invalidates_cache_on_custom_standard_switch()
    {
        // First load with Quantum Default - populate cache
        Livewire::test(RankingPsyMapping::class);

        // Switch to Custom Standard (simulate baseline custom standard selection)
        // This changes the config hash because getCategoryWeight() returns different values
        $standardService = app(DynamicStandardService::class);
        $standardService->setActiveBaselineStandard($this->template->id, 'custom');

        // Clear query log
        DB::flushQueryLog();
        DB::enableQueryLog();

        // Second load - should re-compute due to config hash change
        Livewire::test(RankingPsyMapping::class);

        $queries = DB::getQueryLog();

        // Should have aspect_assessments queries (cache miss)
        $aspectAssessmentQueries = collect($queries)->filter(function ($query) {
            return str_contains($query['query'], 'aspect_assessments');
        });

        $this->assertGreaterThan(0, $aspectAssessmentQueries->count(), 'Custom standard switch should invalidate cache');
    }

    /** @test */
    public function it_handles_event_selection()
    {
        $component = Livewire::test(RankingPsyMapping::class)
            ->call('handleEventSelected');

        $component->assertStatus(200);
    }

    /** @test */
    public function it_handles_position_selection()
    {
        $component = Livewire::test(RankingPsyMapping::class)
            ->call('handlePositionSelected');

        $component->assertStatus(200);
    }

    /** @test */
    public function it_handles_per_page_change()
    {
        $component = Livewire::test(RankingPsyMapping::class)
            ->set('perPage', 20);

        $component->assertStatus(200);
        $component->assertSet('perPage', 20);
    }

    /** @test */
    public function it_displays_correct_ranking_data()
    {
        $component = Livewire::test(RankingPsyMapping::class);

        // Should display participant names
        $component->assertSee('Participant 1');

        // Should display ranking numbers
        $component->assertSee('#1');
    }
}
