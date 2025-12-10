<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Pages;

use App\Livewire\Pages\GeneralReport\Ranking\RankingMcMapping;
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
 * RankingMcMapping Component Integration Tests
 *
 * Tests the RankingMcMapping Livewire component's integration with
 * the optimized RankingService cache layer.
 *
 * Coverage:
 * - Cache behavior (cold start vs warm)
 * - Tolerance instant update (no cache miss)
 * - Pagination with cached data
 * - Cache invalidation on standard adjustment
 * - Custom standard switch invalidation
 */
class RankingMcMappingTest extends TestCase
{
    use RefreshDatabase;

    protected AssessmentEvent $event;

    protected PositionFormation $position;

    protected AssessmentTemplate $template;

    protected CategoryType $kompetensiCategory;

    protected Aspect $aspect1;

    protected Aspect $aspect2;

    protected array $participants = [];

    protected function setUp(): void
    {
        parent::setUp();

        // Create test data
        $institution = Institution::factory()->create();
        $batch = Batch::factory()->create(['institution_id' => $institution->id]);
        $this->event = AssessmentEvent::factory()->create([
            'institution_id' => $institution->id,
            'batch_id' => $batch->id,
        ]);

        $this->template = AssessmentTemplate::factory()->create([
            'institution_id' => $institution->id,
        ]);

        $this->position = PositionFormation::factory()->create([
            'template_id' => $this->template->id,
            'event_id' => $this->event->id,
        ]);

        // Create Kompetensi category
        $this->kompetensiCategory = CategoryType::factory()->create([
            'template_id' => $this->template->id,
            'code' => 'kompetensi',
            'name' => 'Kompetensi',
            'weight' => 50,
        ]);

        // Create aspects
        $this->aspect1 = Aspect::factory()->create([
            'category_type_id' => $this->kompetensiCategory->id,
            'code' => 'MC001',
            'name' => 'Managerial Competency 1',
            'weight' => 3,
            'standard_rating' => 3.5,
            'order' => 1,
        ]);

        $this->aspect2 = Aspect::factory()->create([
            'category_type_id' => $this->kompetensiCategory->id,
            'code' => 'MC002',
            'name' => 'Managerial Competency 2',
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
                'individual_rating' => 3.0 + ($i * 0.1),
            ]);

            AspectAssessment::create([
                'participant_id' => $participant->id,
                'event_id' => $this->event->id,
                'position_formation_id' => $this->position->id,
                'aspect_id' => $this->aspect2->id,
                'individual_rating' => 3.5 + ($i * 0.1),
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
        Cache::flush();
        DB::enableQueryLog();

        $component = Livewire::test(RankingMcMapping::class);

        $queries = DB::getQueryLog();

        $this->assertGreaterThan(0, count($queries));
        $component->assertStatus(200);
    }

    /** @test */
    public function it_uses_cached_rankings_on_warm_load()
    {
        Livewire::test(RankingMcMapping::class);

        DB::flushQueryLog();
        DB::enableQueryLog();

        Livewire::test(RankingMcMapping::class);

        $queries = DB::getQueryLog();
        $aspectAssessmentQueries = collect($queries)->filter(function ($query) {
            return str_contains($query['query'], 'aspect_assessments');
        });

        $this->assertCount(0, $aspectAssessmentQueries, 'Should not query aspect_assessments on cached load');
    }

    /** @test */
    public function it_updates_tolerance_instantly_without_cache_miss()
    {
        $component = Livewire::test(RankingMcMapping::class);

        DB::flushQueryLog();
        DB::enableQueryLog();

        $component->call('handleToleranceUpdate', 10);

        $queries = DB::getQueryLog();
        $aspectAssessmentQueries = collect($queries)->filter(function ($query) {
            return str_contains($query['query'], 'aspect_assessments');
        });

        $this->assertCount(0, $aspectAssessmentQueries, 'Tolerance update should not trigger cache miss');
    }

    /** @test */
    public function it_paginates_with_cached_data()
    {
        Livewire::test(RankingMcMapping::class);

        DB::flushQueryLog();
        DB::enableQueryLog();

        $component = Livewire::test(RankingMcMapping::class)
            ->set('page', 2);

        $queries = DB::getQueryLog();
        $aspectAssessmentQueries = collect($queries)->filter(function ($query) {
            return str_contains($query['query'], 'aspect_assessments');
        });

        $this->assertCount(0, $aspectAssessmentQueries, 'Pagination should use cached data');
    }

    /** @test */
    public function it_invalidates_cache_on_standard_adjustment()
    {
        Livewire::test(RankingMcMapping::class);

        $standardService = app(DynamicStandardService::class);
        $standardService->setAspectWeight($this->template->id, 'MC001', 5.0);

        DB::flushQueryLog();
        DB::enableQueryLog();

        Livewire::test(RankingMcMapping::class);

        $queries = DB::getQueryLog();
        $aspectAssessmentQueries = collect($queries)->filter(function ($query) {
            return str_contains($query['query'], 'aspect_assessments');
        });

        $this->assertGreaterThan(0, $aspectAssessmentQueries->count(), 'Standard adjustment should invalidate cache');
    }

    /** @test */
    public function it_invalidates_cache_on_custom_standard_switch()
    {
        Livewire::test(RankingMcMapping::class);

        $standardService = app(DynamicStandardService::class);
        $standardService->setActiveBaselineStandard($this->template->id, 'custom');

        DB::flushQueryLog();
        DB::enableQueryLog();

        Livewire::test(RankingMcMapping::class);

        $queries = DB::getQueryLog();
        $aspectAssessmentQueries = collect($queries)->filter(function ($query) {
            return str_contains($query['query'], 'aspect_assessments');
        });

        $this->assertGreaterThan(0, $aspectAssessmentQueries->count(), 'Custom standard switch should invalidate cache');
    }

    /** @test */
    public function it_handles_event_selection()
    {
        $component = Livewire::test(RankingMcMapping::class)
            ->call('handleEventSelected');

        $component->assertStatus(200);
    }

    /** @test */
    public function it_handles_position_selection()
    {
        $component = Livewire::test(RankingMcMapping::class)
            ->call('handlePositionSelected');

        $component->assertStatus(200);
    }

    /** @test */
    public function it_handles_per_page_change()
    {
        $component = Livewire::test(RankingMcMapping::class)
            ->set('perPage', 20);

        $component->assertStatus(200);
        $component->assertSet('perPage', 20);
    }

    /** @test */
    public function it_displays_correct_ranking_data()
    {
        $component = Livewire::test(RankingMcMapping::class);

        $component->assertSee('Participant 1');
        $component->assertSee('#1');
    }
}
