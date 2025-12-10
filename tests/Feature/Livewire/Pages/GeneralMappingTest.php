<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Pages;

use App\Livewire\Pages\IndividualReport\GeneralMapping;
use App\Models\Aspect;
use App\Models\AspectAssessment;
use App\Models\AssessmentEvent;
use App\Models\AssessmentTemplate;
use App\Models\Batch;
use App\Models\CategoryAssessment;
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
 * GeneralMapping Component Integration Tests
 *
 * Tests the GeneralMapping Livewire component's integration with
 * the optimized RankingService for COMBINED participant rank.
 *
 * Coverage:
 * - getParticipantCombinedRank() cache behavior
 * - Tolerance instant update
 * - Standard adjustment cache invalidation
 * - Custom standard switch invalidation
 */
class GeneralMappingTest extends TestCase
{
    use RefreshDatabase;

    protected AssessmentEvent $event;

    protected PositionFormation $position;

    protected AssessmentTemplate $template;

    protected CategoryType $potensiCategory;

    protected CategoryType $kompetensiCategory;

    protected Participant $participant;

    protected Aspect $potensiAspect;

    protected Aspect $kompetensiAspect;

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

        // Create participant
        $this->participant = Participant::factory()->create([
            'event_id' => $this->event->id,
            'batch_id' => $batch->id,
            'position_formation_id' => $this->position->id,
            'test_number' => '001',
            'name' => 'Test Participant',
        ]);

        // Create assessments
        AspectAssessment::create([
            'participant_id' => $this->participant->id,
            'event_id' => $this->event->id,
            'position_formation_id' => $this->position->id,
            'aspect_id' => $this->potensiAspect->id,
            'individual_rating' => 4.0,
        ]);

        AspectAssessment::create([
            'participant_id' => $this->participant->id,
            'event_id' => $this->event->id,
            'position_formation_id' => $this->position->id,
            'aspect_id' => $this->kompetensiAspect->id,
            'individual_rating' => 3.8,
        ]);

        // Create category assessments
        CategoryAssessment::create([
            'participant_id' => $this->participant->id,
            'category_type_id' => $this->potensiCategory->id,
        ]);

        CategoryAssessment::create([
            'participant_id' => $this->participant->id,
            'category_type_id' => $this->kompetensiCategory->id,
        ]);

        // Create 9 more participants for ranking context
        for ($i = 2; $i <= 10; $i++) {
            $p = Participant::factory()->create([
                'event_id' => $this->event->id,
                'batch_id' => $batch->id,
                'position_formation_id' => $this->position->id,
                'test_number' => str_pad((string) $i, 3, '0', STR_PAD_LEFT),
                'name' => "Participant {$i}",
            ]);

            AspectAssessment::create([
                'participant_id' => $p->id,
                'event_id' => $this->event->id,
                'position_formation_id' => $this->position->id,
                'aspect_id' => $this->potensiAspect->id,
                'individual_rating' => 3.0 + ($i * 0.05),
            ]);

            AspectAssessment::create([
                'participant_id' => $p->id,
                'event_id' => $this->event->id,
                'position_formation_id' => $this->position->id,
                'aspect_id' => $this->kompetensiAspect->id,
                'individual_rating' => 3.2 + ($i * 0.05),
            ]);

            CategoryAssessment::create([
                'participant_id' => $p->id,
                'category_type_id' => $this->potensiCategory->id,
            ]);

            CategoryAssessment::create([
                'participant_id' => $p->id,
                'category_type_id' => $this->kompetensiCategory->id,
            ]);
        }
    }

    /** @test */
    public function it_loads_participant_combined_ranking_on_cold_start()
    {
        Cache::flush();
        DB::enableQueryLog();

        $component = Livewire::test(GeneralMapping::class, [
            'eventCode' => $this->event->code,
            'testNumber' => '001',
        ]);

        $queries = DB::getQueryLog();

        $this->assertGreaterThan(0, count($queries));
        $component->assertStatus(200);
    }

    /** @test */
    public function it_uses_cached_combined_ranking_on_warm_load()
    {
        // First load - populate cache
        Livewire::test(GeneralMapping::class, [
            'eventCode' => $this->event->code,
            'testNumber' => '001',
        ]);

        DB::flushQueryLog();
        DB::enableQueryLog();

        // Second load - should use cache
        Livewire::test(GeneralMapping::class, [
            'eventCode' => $this->event->code,
            'testNumber' => '001',
        ]);

        $queries = DB::getQueryLog();
        $aspectAssessmentQueries = collect($queries)->filter(function ($query) {
            return str_contains($query['query'], 'aspect_assessments');
        });

        $this->assertCount(0, $aspectAssessmentQueries, 'Should not query aspect_assessments on cached load');
    }

    /** @test */
    public function it_updates_tolerance_instantly_without_cache_miss()
    {
        $component = Livewire::test(GeneralMapping::class, [
            'eventCode' => $this->event->code,
            'testNumber' => '001',
        ]);

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
    public function it_invalidates_cache_on_standard_adjustment()
    {
        Livewire::test(GeneralMapping::class, [
            'eventCode' => $this->event->code,
            'testNumber' => '001',
        ]);

        $standardService = app(DynamicStandardService::class);
        $standardService->setAspectWeight($this->template->id, 'PSY001', 5.0);

        DB::flushQueryLog();
        DB::enableQueryLog();

        Livewire::test(GeneralMapping::class, [
            'eventCode' => $this->event->code,
            'testNumber' => '001',
        ]);

        $queries = DB::getQueryLog();
        $aspectAssessmentQueries = collect($queries)->filter(function ($query) {
            return str_contains($query['query'], 'aspect_assessments');
        });

        $this->assertGreaterThan(0, $aspectAssessmentQueries->count(), 'Standard adjustment should invalidate cache');
    }

    /** @test */
    public function it_invalidates_cache_on_custom_standard_switch()
    {
        Livewire::test(GeneralMapping::class, [
            'eventCode' => $this->event->code,
            'testNumber' => '001',
        ]);

        $standardService = app(DynamicStandardService::class);
        $standardService->setActiveBaselineStandard($this->template->id, 'custom');

        DB::flushQueryLog();
        DB::enableQueryLog();

        Livewire::test(GeneralMapping::class, [
            'eventCode' => $this->event->code,
            'testNumber' => '001',
        ]);

        $queries = DB::getQueryLog();
        $aspectAssessmentQueries = collect($queries)->filter(function ($query) {
            return str_contains($query['query'], 'aspect_assessments');
        });

        $this->assertGreaterThan(0, $aspectAssessmentQueries->count(), 'Custom standard switch should invalidate cache');
    }

    /** @test */
    public function it_displays_participant_ranking_info()
    {
        $component = Livewire::test(GeneralMapping::class, [
            'eventCode' => $this->event->code,
            'testNumber' => '001',
        ]);

        // Should display participant info
        $component->assertSee('Test Participant');
        $component->assertStatus(200);
    }

    /** @test */
    public function it_handles_standard_update_event()
    {
        $component = Livewire::test(GeneralMapping::class, [
            'eventCode' => $this->event->code,
            'testNumber' => '001',
        ]);

        $component->call('handleStandardUpdate', $this->template->id);
        $component->assertStatus(200);
    }

    /** @test */
    public function it_handles_standard_switch_event()
    {
        $component = Livewire::test(GeneralMapping::class, [
            'eventCode' => $this->event->code,
            'testNumber' => '001',
        ]);

        $component->call('handleStandardSwitch', $this->template->id);
        $component->assertStatus(200);
    }
}
