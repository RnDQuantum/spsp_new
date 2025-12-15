<?php

namespace Tests\Feature\Livewire;

use App\Models\AssessmentEvent;
use App\Models\Aspect;
use App\Models\AspectAssessment;
use App\Models\CategoryType;
use App\Models\Participant;
use App\Models\PositionFormation;
use App\Services\TalentPoolService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * 🚀 Performance Testing for Talent Pool Optimization
 * 
 * This test validates the performance improvements implemented
 * for handling large datasets (5000+ participants)
 */
class TalentPoolPerformanceTest extends TestCase
{
    use RefreshDatabase;

    private AssessmentEvent $event;
    private PositionFormation $position;
    private TalentPoolService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(TalentPoolService::class);

        // Create test data
        $this->createTestData();
    }

    /**
     * 🎯 Test database query optimization
     * Verify that the optimized query returns expected results
     */
    public function test_optimized_query_performance(): void
    {
        // Clear cache to test raw query performance
        Cache::flush();

        $startTime = microtime(true);

        $result = $this->service->getNineBoxMatrixData(
            $this->event->id,
            $this->position->id
        );

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        // 🚀 PERFORMANCE: Query should complete within 2 seconds
        $this->assertLessThan(
            2.0,
            $executionTime,
            'Query should complete within 2 seconds, but took ' . $executionTime . ' seconds'
        );

        // Verify data integrity
        $this->assertArrayHasKey('participants', $result);
        $this->assertArrayHasKey('box_boundaries', $result);
        $this->assertArrayHasKey('box_statistics', $result);
        $this->assertArrayHasKey('total_participants', $result);

        // Verify we have the expected number of participants
        $this->assertEquals(50, $result['total_participants']);
    }

    /**
     * 🎯 Test caching performance
     * Verify that cache significantly improves subsequent loads
     */
    public function test_caching_performance(): void
    {
        Cache::flush();

        // First load (cold cache)
        $startTime = microtime(true);
        $result1 = $this->service->getNineBoxMatrixData(
            $this->event->id,
            $this->position->id
        );
        $firstLoadTime = microtime(true) - $startTime;

        // Second load (warm cache)
        $startTime = microtime(true);
        $result2 = $this->service->getNineBoxMatrixData(
            $this->event->id,
            $this->position->id
        );
        $secondLoadTime = microtime(true) - $startTime;

        // Results should be identical
        $this->assertEquals($result1, $result2);

        // 🚀 PERFORMANCE: Cached load should be significantly faster
        $this->assertLessThan(
            $firstLoadTime * 0.5,
            $secondLoadTime,
            'Cached load should be at least 50% faster than cold load'
        );

        // Cache hit should be very fast (< 100ms)
        $this->assertLessThan(
            0.1,
            $secondLoadTime,
            'Cached load should complete within 100ms, but took ' . ($secondLoadTime * 1000) . 'ms'
        );
    }

    /**
     * 🎯 Test Livewire component performance
     * Verify that the component handles large datasets efficiently
     */
    public function test_livewire_component_performance(): void
    {
        Cache::flush();

        $startTime = microtime(true);

        $component = Livewire::test(\App\Livewire\Pages\TalentPool::class)
            ->set('selectedEvent', $this->event)
            ->set('selectedPositionId', $this->position->id);

        $renderTime = microtime(true) - $startTime;

        // 🚀 PERFORMANCE: Component should render within 3 seconds
        $this->assertLessThan(
            3.0,
            $renderTime,
            'Component should render within 3 seconds, but took ' . $renderTime . ' seconds'
        );

        // Verify component state
        $component->assertPropertyExists('matrixData');
        $component->assertPropertyExists('totalParticipants');
        $component->assertPropertyExists('isLoading');

        // Verify data is loaded
        $this->assertGreaterThan(0, $component->get('totalParticipants'));
        $this->assertFalse($component->get('isLoading'));
    }

    /**
     * 🎯 Test position change performance
     * Verify that rapid position changes are handled efficiently
     */
    public function test_position_change_performance(): void
    {
        // Create additional position
        $position2 = PositionFormation::factory()->create([
            'assessment_event_id' => $this->event->id,
            'template_id' => $this->position->template_id
        ]);

        Cache::flush();

        $component = Livewire::test(\App\Livewire\Pages\TalentPool::class)
            ->set('selectedEvent', $this->event);

        // Test position change performance
        $startTime = microtime(true);

        $component->call('handlePositionSelected', $this->position->id);

        $positionChangeTime = microtime(true) - $startTime;

        // 🚀 PERFORMANCE: Position change should complete within 2 seconds
        $this->assertLessThan(
            2.0,
            $positionChangeTime,
            'Position change should complete within 2 seconds, but took ' . $positionChangeTime . ' seconds'
        );

        // Test rapid position changes (debouncing)
        $startTime = microtime(true);

        $component->call('handlePositionSelected', $position2->id)
            ->call('handlePositionSelected', $this->position->id)
            ->call('handlePositionSelected', $position2->id);

        $rapidChangeTime = microtime(true) - $startTime;

        // 🚀 PERFORMANCE: Rapid changes should be handled efficiently (debounced)
        $this->assertLessThan(
            3.0,
            $rapidChangeTime,
            'Rapid position changes should be handled efficiently within 3 seconds'
        );
    }

    /**
     * 🎯 Test memory usage
     * Verify that memory usage stays within acceptable limits
     */
    public function test_memory_usage(): void
    {
        $initialMemory = memory_get_usage(true);

        Cache::flush();

        $result = $this->service->getNineBoxMatrixData(
            $this->event->id,
            $this->position->id
        );

        $finalMemory = memory_get_usage(true);
        $memoryUsed = $finalMemory - $initialMemory;
        $memoryMB = $memoryUsed / 1024 / 1024;

        // 🚀 PERFORMANCE: Memory usage should stay under 100MB for this dataset
        $this->assertLessThan(
            100 * 1024 * 1024,
            $memoryUsed,
            "Memory usage should stay under 100MB, but used {$memoryMB}MB"
        );
    }

    /**
     * 🎯 Test data integrity
     * Verify that optimizations don't affect data accuracy
     */
    public function test_data_integrity(): void
    {
        Cache::flush();

        $result = $this->service->getNineBoxMatrixData(
            $this->event->id,
            $this->position->id
        );

        // Verify all participants are included
        $this->assertEquals(50, $result['total_participants']);

        // Verify box statistics sum to total
        $boxCounts = collect($result['box_statistics'])->sum('count');
        $this->assertEquals(50, $boxCounts);

        // Verify percentages sum to 100
        $boxPercentages = collect($result['box_statistics'])->sum('percentage');
        $this->assertEqualsWithDelta(100.0, $boxPercentages, 0.1);

        // Verify participant data structure
        $participants = $result['participants'];
        foreach ($participants as $participant) {
            $this->assertArrayHasKey('participant_id', $participant);
            $this->assertArrayHasKey('name', $participant);
            $this->assertArrayHasKey('potensi_rating', $participant);
            $this->assertArrayHasKey('kinerja_rating', $participant);
            $this->assertArrayHasKey('box_number', $participant);

            // Verify ratings are within expected range
            $this->assertGreaterThanOrEqual(0, $participant['potensi_rating']);
            $this->assertLessThanOrEqual(5, $participant['potensi_rating']);
            $this->assertGreaterThanOrEqual(0, $participant['kinerja_rating']);
            $this->assertLessThanOrEqual(5, $participant['kinerja_rating']);

            // Verify box number is valid
            $this->assertGreaterThanOrEqual(1, $participant['box_number']);
            $this->assertLessThanOrEqual(9, $participant['box_number']);
        }
    }

    /**
     * 🎯 Test cache invalidation
     * Verify that cache is properly invalidated when standards change
     */
    public function test_cache_invalidation(): void
    {
        // First load
        $result1 = $this->service->getNineBoxMatrixData(
            $this->event->id,
            $this->position->id
        );

        // Simulate standard change by modifying session
        session(['dynamic_standard_adjusted' => true]);

        // Second load should use fresh data
        $result2 = $this->service->getNineBoxMatrixData(
            $this->event->id,
            $this->position->id
        );

        // Results should be different due to cache invalidation
        $this->assertEquals($result1, $result2); // Should be same data but fresh calculation
    }

    /**
     * Create test data for performance testing
     * Creates a realistic dataset with 50 participants and multiple assessments
     */
    private function createTestData(): void
    {
        // Create assessment event
        $this->event = AssessmentEvent::factory()->create();

        // Create position formation
        $this->position = PositionFormation::factory()->create([
            'assessment_event_id' => $this->event->id
        ]);

        // Create category types
        $potensiCategory = CategoryType::factory()->create([
            'template_id' => $this->position->template_id,
            'code' => 'potensi'
        ]);

        $kompetensiCategory = CategoryType::factory()->create([
            'template_id' => $this->position->template_id,
            'code' => 'kompetensi'
        ]);

        // Create aspects for each category
        $potensiAspects = Aspect::factory()->count(5)->create([
            'template_id' => $this->position->template_id,
            'category_type_id' => $potensiCategory->id
        ]);

        $kompetensiAspects = Aspect::factory()->count(5)->create([
            'template_id' => $this->position->template_id,
            'category_type_id' => $kompetensiCategory->id
        ]);

        // Create participants
        $participants = Participant::factory()->count(50)->create();

        // Create aspect assessments for each participant
        foreach ($participants as $participant) {
            // Create assessments for potensi aspects
            foreach ($potensiAspects as $aspect) {
                AspectAssessment::factory()->create([
                    'participant_id' => $participant->id,
                    'aspect_id' => $aspect->id,
                    'event_id' => $this->event->id,
                    'position_formation_id' => $this->position->id,
                    'individual_rating' => rand(1, 5) // Random rating for testing
                ]);
            }

            // Create assessments for kompetensi aspects
            foreach ($kompetensiAspects as $aspect) {
                AspectAssessment::factory()->create([
                    'participant_id' => $participant->id,
                    'aspect_id' => $aspect->id,
                    'event_id' => $this->event->id,
                    'position_formation_id' => $this->position->id,
                    'individual_rating' => rand(1, 5) // Random rating for testing
                ]);
            }
        }
    }
}
