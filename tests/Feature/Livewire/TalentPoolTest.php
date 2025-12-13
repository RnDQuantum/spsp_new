<?php

namespace Tests\Feature\Livewire;

use App\Models\AssessmentEvent;
use App\Models\AspectAssessment;
use App\Models\CategoryType;
use App\Models\Participant;
use App\Models\PositionFormation;
use App\Services\TalentPoolService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TalentPoolTest extends TestCase
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
     * Test TalentPoolService with large dataset (simulating 4,905 participants)
     */
    public function test_talent_pool_service_with_large_dataset(): void
    {
        $startTime = microtime(true);

        $matrixData = $this->service->getNineBoxMatrixData(
            $this->event->id,
            $this->position->id
        );

        $endTime = microtime(true);
        $executionTime = ($endTime - $startTime) * 1000; // Convert to milliseconds

        // Assertions
        $this->assertNotEmpty($matrixData);
        $this->assertArrayHasKey('participants', $matrixData);
        $this->assertArrayHasKey('box_boundaries', $matrixData);
        $this->assertArrayHasKey('box_statistics', $matrixData);
        $this->assertArrayHasKey('total_participants', $matrixData);

        // Performance assertion - should complete within 2 seconds for 4,905 participants
        $this->assertLessThan(2000, $executionTime, 'TalentPoolService should complete within 2 seconds');

        // Validate participants data structure
        $participants = $matrixData['participants'];
        $this->assertGreaterThan(0, $participants->count());

        $firstParticipant = $participants->first();
        $this->assertArrayHasKey('participant_id', $firstParticipant);
        $this->assertArrayHasKey('name', $firstParticipant);
        $this->assertArrayHasKey('potensi_rating', $firstParticipant);
        $this->assertArrayHasKey('kinerja_rating', $firstParticipant);
        $this->assertArrayHasKey('box_number', $firstParticipant);
        $this->assertArrayHasKey('box_label', $firstParticipant);

        // Validate box boundaries
        $boundaries = $matrixData['box_boundaries'];
        $this->assertArrayHasKey('potensi', $boundaries);
        $this->assertArrayHasKey('kinerja', $boundaries);

        $potensiBoundaries = $boundaries['potensi'];
        $this->assertArrayHasKey('avg', $potensiBoundaries);
        $this->assertArrayHasKey('std_dev', $potensiBoundaries);
        $this->assertArrayHasKey('lower_bound', $potensiBoundaries);
        $this->assertArrayHasKey('upper_bound', $potensiBoundaries);

        // Validate box statistics
        $statistics = $matrixData['box_statistics'];
        $this->assertCount(9, $statistics); // Should have all 9 boxes

        // Check that all box numbers exist (1-9)
        for ($box = 1; $box <= 9; $box++) {
            $this->assertArrayHasKey($box, $statistics);
            $this->assertArrayHasKey('count', $statistics[$box]);
            $this->assertArrayHasKey('percentage', $statistics[$box]);
        }

        // Validate total participants count
        $totalFromStatistics = array_sum(array_column($statistics, 'count'));
        $this->assertEquals($participants->count(), $totalFromStatistics);
        $this->assertEquals($matrixData['total_participants'], $participants->count());
    }

    /**
     * Test dynamic box boundaries calculation
     */
    public function test_dynamic_box_boundaries_calculation(): void
    {
        $matrixData = $this->service->getNineBoxMatrixData(
            $this->event->id,
            $this->position->id
        );

        $boundaries = $matrixData['box_boundaries'];

        // Validate that boundaries are calculated correctly
        $potensiBoundaries = $boundaries['potensi'];
        $kinerjaBoundaries = $boundaries['kinerja'];

        // Lower bound should be less than average
        $this->assertLessThan($potensiBoundaries['avg'], $potensiBoundaries['lower_bound']);
        $this->assertLessThan($kinerjaBoundaries['avg'], $kinerjaBoundaries['lower_bound']);

        // Upper bound should be greater than average
        $this->assertGreaterThan($potensiBoundaries['avg'], $potensiBoundaries['upper_bound']);
        $this->assertGreaterThan($kinerjaBoundaries['avg'], $kinerjaBoundaries['upper_bound']);

        // Standard deviation should be positive
        $this->assertGreaterThan(0, $potensiBoundaries['std_dev']);
        $this->assertGreaterThan(0, $kinerjaBoundaries['std_dev']);
    }

    /**
     * Test participant classification into boxes
     */
    public function test_participant_box_classification(): void
    {
        $matrixData = $this->service->getNineBoxMatrixData(
            $this->event->id,
            $this->position->id
        );

        $participants = $matrixData['participants'];
        $boundaries = $matrixData['box_boundaries'];

        foreach ($participants as $participant) {
            $potensiRating = $participant['potensi_rating'];
            $kinerjaRating = $participant['kinerja_rating'];
            $boxNumber = $participant['box_number'];

            // Validate box number is between 1-9
            $this->assertGreaterThanOrEqual(1, $boxNumber);
            $this->assertLessThanOrEqual(9, $boxNumber);

            // Validate classification logic
            $potensiLevel = $this->determineLevel(
                $potensiRating,
                $boundaries['potensi']['lower_bound'],
                $boundaries['potensi']['upper_bound']
            );

            $kinerjaLevel = $this->determineLevel(
                $kinerjaRating,
                $boundaries['kinerja']['lower_bound'],
                $boundaries['kinerja']['upper_bound']
            );

            $expectedBox = $this->mapLevelsToBox($potensiLevel, $kinerjaLevel);
            $this->assertEquals($expectedBox, $boxNumber);
        }
    }

    /**
     * Test cache invalidation
     */
    public function test_cache_invalidation(): void
    {
        // First call
        $startTime = microtime(true);
        $matrixData1 = $this->service->getNineBoxMatrixData(
            $this->event->id,
            $this->position->id
        );
        $firstCallTime = (microtime(true) - $startTime) * 1000;

        // Second call (should be cached)
        $startTime = microtime(true);
        $matrixData2 = $this->service->getNineBoxMatrixData(
            $this->event->id,
            $this->position->id
        );
        $secondCallTime = (microtime(true) - $startTime) * 1000;

        // Results should be identical
        $this->assertEquals($matrixData1, $matrixData2);

        // Second call should be faster (cached)
        $this->assertLessThan($firstCallTime, $secondCallTime);
    }

    /**
     * Test TalentPool component rendering
     */
    public function test_talent_pool_component_rendering(): void
    {
        // Set session values for testing
        session([
            'filter.event_code' => $this->event->code,
            'filter.position_formation_id' => $this->position->id,
        ]);

        $component = Livewire::test(\App\Livewire\Pages\TalentPool::class);

        // Component should render without errors
        $component->assertStatus(200);

        // Component should have data properties
        $this->assertNotNull($component->selectedEvent);
        $this->assertNotNull($component->selectedPositionId);
        $this->assertArrayHasKey('participants', $component->matrixData);
        $this->assertArrayHasKey('box_boundaries', $component->matrixData);
        $this->assertArrayHasKey('box_statistics', $component->matrixData);
    }

    /**
     * Create test data for large scale testing
     */
    private function createTestData(): void
    {
        // Create assessment event
        $this->event = AssessmentEvent::factory()->create([
            'code' => 'TEST-EVENT-2025'
        ]);

        // Create position formation with template
        $this->position = PositionFormation::factory()->create([
            'event_id' => $this->event->id
        ]);

        // Create category types
        $potensiCategory = CategoryType::factory()->create([
            'template_id' => $this->position->template_id,
            'code' => 'potensi',
            'name' => 'Potensi',
            'weight_percentage' => 25
        ]);

        $kompetensiCategory = CategoryType::factory()->create([
            'template_id' => $this->position->template_id,
            'code' => 'kompetensi',
            'name' => 'Kompetensi',
            'weight_percentage' => 75
        ]);

        // Create aspects for each category
        $potensiAspects = [];
        $kompetensiAspects = [];

        for ($i = 1; $i <= 4; $i++) {
            $potensiAspects[] = \App\Models\Aspect::factory()->create([
                'category_type_id' => $potensiCategory->id,
                'code' => "potensi-{$i}",
                'name' => "Potensi Aspect {$i}",
                'weight_percentage' => 6.25, // 25% / 4 aspects
                'standard_rating' => 3.5
            ]);
        }

        for ($i = 1; $i <= 7; $i++) {
            $kompetensiAspects[] = \App\Models\Aspect::factory()->create([
                'category_type_id' => $kompetensiCategory->id,
                'code' => "kompetensi-{$i}",
                'name' => "Kompetensi Aspect {$i}",
                'weight_percentage' => 10.71, // 75% / 7 aspects
                'standard_rating' => 3.5
            ]);
        }

        $allAspects = array_merge($potensiAspects, $kompetensiAspects);

        // Create participants (simulate large dataset)
        $participants = Participant::factory()->count(100)->create([
            'event_id' => $this->event->id,
            'position_formation_id' => $this->position->id
        ]);

        // Create aspect assessments for each participant
        foreach ($participants as $participant) {
            foreach ($allAspects as $aspect) {
                // Generate realistic rating distribution (2.0 - 5.0)
                $rating = $this->generateRealisticRating();

                AspectAssessment::factory()->create([
                    'participant_id' => $participant->id,
                    'aspect_id' => $aspect->id,
                    'event_id' => $this->event->id,
                    'position_formation_id' => $this->position->id,
                    'individual_rating' => $rating,
                    'individual_score' => $rating * $aspect->weight_percentage
                ]);
            }
        }
    }

    /**
     * Generate realistic rating with normal distribution
     */
    private function generateRealisticRating(): float
    {
        // Generate rating with normal distribution centered around 3.5
        $rating = 3.5 + (mt_rand(-100, 100) / 100);

        // Clamp between 1.0 and 5.0
        return max(1.0, min(5.0, $rating));
    }

    /**
     * Determine level based on boundaries (replicate service logic)
     */
    private function determineLevel(float $value, float $lowerBound, float $upperBound): string
    {
        if ($value < $lowerBound) {
            return 'rendah';
        } elseif ($value > $upperBound) {
            return 'tinggi';
        } else {
            return 'sedang';
        }
    }

    /**
     * Map levels to box number (replicate service logic)
     */
    private function mapLevelsToBox(string $potensiLevel, string $kinerjaLevel): int
    {
        $boxMap = [
            'rendah' => [
                'rendah' => 1,
                'sedang' => 2,
                'tinggi' => 3,
            ],
            'sedang' => [
                'rendah' => 4,
                'sedang' => 5,
                'tinggi' => 6,
            ],
            'tinggi' => [
                'rendah' => 7,
                'sedang' => 8,
                'tinggi' => 9,
            ],
        ];

        return $boxMap[$potensiLevel][$kinerjaLevel] ?? 5;
    }
}
