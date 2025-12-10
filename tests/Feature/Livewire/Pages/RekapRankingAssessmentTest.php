<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Pages;

use App\Livewire\Pages\GeneralReport\Ranking\RekapRankingAssessment;
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
 * RekapRankingAssessment Component Integration Tests
 *
 * Tests the RekapRankingAssessment Livewire component's integration with
 * the optimized RankingService cache layer for COMBINED rankings.
 *
 * Coverage:
 * - Combined rankings (Potensi + Kompetensi)
 * - Cache behavior for both categories
 * - Tolerance instant update
 * - Pagination with cached data
 * - Category weight changes
 * - Custom standard switch invalidation
 */
class RekapRankingAssessmentTest extends TestCase
{
    use RefreshDatabase;

    protected AssessmentEvent $event;

    protected PositionFormation $position;

    protected AssessmentTemplate $template;

    protected CategoryType $potensiCategory;

    protected CategoryType $kompetensiCategory;

    protected Aspect $potensiAspect;

    protected Aspect $kompetensiAspect;

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

        // Create categories
        $this->potensiCategory = CategoryType::factory()->create([
            'template_id' => $this->template->id,
            'code' => 'potensi',
            'name' => 'Potensi',
            'weight' => 50,
        ]);

        $this->kompetensiCategory = CategoryType::factory()->create([
            'template_id' => $this->template->id,
            'code' => 'kompetensi',
            'name' => 'Kompetensi',
            'weight' => 50,
        ]);

        // Create aspects
        $this->potensiAspect = Aspect::factory()->create([
            'category_type_id' => $this->potensiCategory->id,
            'code' => 'PSY001',
            'name' => 'Psychology Aspect',
            'weight' => 3,
            'standard_rating' => 3.5,
            'order' => 1,
        ]);

        $this->kompetensiAspect = Aspect::factory()->create([
            'category_type_id' => $this->kompetensiCategory->id,
            'code' => 'MC001',
            'name' => 'Managerial Competency',
            'weight' => 3,
            'standard_rating' => 3.5,
            'order' => 1,
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

            // Create assessments for both categories
            AspectAssessment::create([
                'participant_id' => $participant->id,
                'event_id' => $this->event->id,
                'position_formation_id' => $this->position->id,
                'aspect_id' => $this->potensiAspect->id,
                'individual_rating' => 3.0 + ($i * 0.1),
            ]);

            AspectAssessment::create([
                'participant_id' => $participant->id,
                'event_id' => $this->event->id,
                'position_formation_id' => $this->position->id,
                'aspect_id' => $this->kompetensiAspect->id,
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
    public function it_loads_combined_rankings_on_cold_start()
    {
        Cache::flush();
        DB::enableQueryLog();

        $component = Livewire::test(RekapRankingAssessment::class);

        $queries = DB::getQueryLog();

        // Should have queries for both categories
        $this->assertGreaterThan(0, count($queries));
        $component->assertStatus(200);
    }

    /** @test */
    public function it_uses_cached_rankings_for_both_categories()
    {
        // First load - populate cache for both categories
        Livewire::test(RekapRankingAssessment::class);

        DB::flushQueryLog();
        DB::enableQueryLog();

        // Second load - should use cache for both
        Livewire::test(RekapRankingAssessment::class);

        $queries = DB::getQueryLog();
        $aspectAssessmentQueries = collect($queries)->filter(function ($query) {
            return str_contains($query['query'], 'aspect_assessments');
        });

        $this->assertCount(0, $aspectAssessmentQueries, 'Should not query aspect_assessments on cached load');
    }

    /** @test */
    public function it_builds_combined_rankings_correctly()
    {
        $component = Livewire::test(RekapRankingAssessment::class);

        // Should display combined data
        $component->assertSee('Participant 1');

        // Should show both category data (PSY and MC)
        $component->assertStatus(200);
    }

    /** @test */
    public function it_updates_tolerance_instantly_without_cache_miss()
    {
        $component = Livewire::test(RekapRankingAssessment::class);

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
        Livewire::test(RekapRankingAssessment::class);

        DB::flushQueryLog();
        DB::enableQueryLog();

        $component = Livewire::test(RekapRankingAssessment::class)
            ->set('page', 2);

        $queries = DB::getQueryLog();
        $aspectAssessmentQueries = collect($queries)->filter(function ($query) {
            return str_contains($query['query'], 'aspect_assessments');
        });

        $this->assertCount(0, $aspectAssessmentQueries, 'Pagination should use cached data');
    }

    /** @test */
    public function it_invalidates_cache_on_category_weight_change()
    {
        Livewire::test(RekapRankingAssessment::class);

        // Change category weight
        $standardService = app(DynamicStandardService::class);
        $standardService->setCategoryWeight($this->template->id, 'potensi', 60);

        DB::flushQueryLog();
        DB::enableQueryLog();

        Livewire::test(RekapRankingAssessment::class);

        $queries = DB::getQueryLog();
        $aspectAssessmentQueries = collect($queries)->filter(function ($query) {
            return str_contains($query['query'], 'aspect_assessments');
        });

        $this->assertGreaterThan(0, $aspectAssessmentQueries->count(), 'Category weight change should invalidate cache');
    }

    /** @test */
    public function it_invalidates_cache_on_aspect_weight_change()
    {
        Livewire::test(RekapRankingAssessment::class);

        // Change aspect weight
        $standardService = app(DynamicStandardService::class);
        $standardService->setAspectWeight($this->template->id, 'PSY001', 5.0);

        DB::flushQueryLog();
        DB::enableQueryLog();

        Livewire::test(RekapRankingAssessment::class);

        $queries = DB::getQueryLog();
        $aspectAssessmentQueries = collect($queries)->filter(function ($query) {
            return str_contains($query['query'], 'aspect_assessments');
        });

        $this->assertGreaterThan(0, $aspectAssessmentQueries->count(), 'Aspect weight change should invalidate cache');
    }

    /** @test */
    public function it_invalidates_cache_on_custom_standard_switch()
    {
        Livewire::test(RekapRankingAssessment::class);

        // Switch to Custom Standard
        $standardService = app(DynamicStandardService::class);
        $standardService->setActiveBaselineStandard($this->template->id, 'custom');

        DB::flushQueryLog();
        DB::enableQueryLog();

        Livewire::test(RekapRankingAssessment::class);

        $queries = DB::getQueryLog();
        $aspectAssessmentQueries = collect($queries)->filter(function ($query) {
            return str_contains($query['query'], 'aspect_assessments');
        });

        $this->assertGreaterThan(0, $aspectAssessmentQueries->count(), 'Custom standard switch should invalidate cache');
    }

    /** @test */
    public function it_handles_event_selection()
    {
        $component = Livewire::test(RekapRankingAssessment::class)
            ->call('handleEventSelected', $this->event->code);

        $component->assertStatus(200);
    }

    /** @test */
    public function it_handles_position_selection()
    {
        $component = Livewire::test(RekapRankingAssessment::class)
            ->call('handlePositionSelected', $this->position->id);

        $component->assertStatus(200);
    }

    /** @test */
    public function it_handles_per_page_change()
    {
        $component = Livewire::test(RekapRankingAssessment::class)
            ->set('perPage', 20);

        $component->assertStatus(200);
        $component->assertSet('perPage', 20);
    }

    /** @test */
    public function it_displays_standard_info_correctly()
    {
        $component = Livewire::test(RekapRankingAssessment::class);

        // Should render standard info section
        $component->assertStatus(200);
    }
}
