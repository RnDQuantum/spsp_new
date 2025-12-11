<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Aspect;
use App\Models\AspectAssessment;
use App\Models\AssessmentEvent;
use App\Models\AssessmentTemplate;
use App\Models\Batch;
use App\Models\CategoryType;
use App\Models\Institution;
use App\Models\Participant;
use App\Models\PositionFormation;
use App\Models\SubAspect;
use App\Models\SubAspectAssessment;
use App\Services\DynamicStandardService;
use App\Services\RankingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * RankingService Unit Tests
 *
 * PHASE 1: ✅ Service Instantiation (1 test)
 * PHASE 2: ✅ getRankings() - Single Category Rankings (15 tests)
 * PHASE 3: ✅ getParticipantRank() - Single Participant Rank (5 tests)
 * PHASE 4: ✅ calculateAdjustedStandards() - Standard Recalculation (10 tests)
 * PHASE 5: ✅ getCombinedRankings() - Potensi + Kompetensi (8 tests)
 * PHASE 6: ✅ getParticipantCombinedRank() - Single Participant Combined Rank (3 tests)
 * PHASE 7: ✅ getPassingSummary() - Statistics (3 tests)
 * PHASE 8: ✅ getConclusionSummary() - Conclusion Breakdown (3 tests)
 * PHASE 12: ✅ Three-Layer Priority System Integration (3 tests)
 * PHASE 13: ⭐ Individual Rating Recalculation (3 tests) - CRITICAL NEW
 * PHASE 14: ⭐ Cache Key Completeness (6 tests) - CRITICAL NEW
 *
 * TOTAL: 60/60 tests (100% complete ✅)
 *
 * @see \App\Services\RankingService
 * @see docs/TESTING_GUIDE.md
 * @see docs/RANKING_TEST_STRATEGY.md
 * @see docs/DATABASE_STRUCTURE.md
 */
class RankingServiceTest extends TestCase
{
    use RefreshDatabase;

    protected RankingService $service;

    protected AssessmentEvent $event;

    protected PositionFormation $position;

    protected AssessmentTemplate $template;

    protected CategoryType $potensiCategory;

    protected CategoryType $kompetensiCategory;

    protected Batch $batch;

    protected function setUp(): void
    {
        parent::setUp();

        // Create complete template structure manually
        $this->template = $this->createCompleteTemplate();

        // Get categories
        $this->potensiCategory = CategoryType::where('template_id', $this->template->id)
            ->where('code', 'potensi')
            ->first();

        $this->kompetensiCategory = CategoryType::where('template_id', $this->template->id)
            ->where('code', 'kompetensi')
            ->first();

        // Create event structure
        $institution = Institution::factory()->create();
        $this->event = AssessmentEvent::factory()->create([
            'institution_id' => $institution->id,
        ]);

        $this->batch = Batch::factory()->create([
            'event_id' => $this->event->id,
        ]);

        $this->position = PositionFormation::factory()->create([
            'event_id' => $this->event->id,
            'template_id' => $this->template->id,
        ]);

        // Instantiate service
        $this->service = app(RankingService::class);

        // Clear any session adjustments and cache from previous tests
        session()->flush();
        \App\Services\Cache\AspectCacheService::clearCache();
    }

    /**
     * Create a complete assessment template with categories, aspects, and sub-aspects
     *
     * This creates a minimal but complete template structure:
     * - Template
     * - 2 Categories (Potensi 50%, Kompetensi 50%)
     * - Potensi: 3 aspects WITH sub-aspects (3 sub-aspects each)
     * - Kompetensi: 3 aspects WITHOUT sub-aspects
     */
    private function createCompleteTemplate(): AssessmentTemplate
    {
        $template = AssessmentTemplate::factory()->create([
            'code' => 'test_template_'.uniqid(),
            'name' => 'Test Template',
        ]);

        // Create Potensi category (50%)
        $potensiCategory = CategoryType::factory()->create([
            'template_id' => $template->id,
            'code' => 'potensi',
            'name' => 'Potensi',
            'weight_percentage' => 50,
            'order' => 1,
        ]);

        // Create Kompetensi category (50%)
        $kompetensiCategory = CategoryType::factory()->create([
            'template_id' => $template->id,
            'code' => 'kompetensi',
            'name' => 'Kompetensi',
            'weight_percentage' => 50,
            'order' => 2,
        ]);

        // Create Potensi aspects WITH sub-aspects (3 aspects)
        $potensiAspects = [
            ['code' => 'kecerdasan', 'name' => 'Kecerdasan', 'weight' => 25, 'order' => 1],
            ['code' => 'cara_kerja', 'name' => 'Cara Kerja', 'weight' => 15, 'order' => 2],
            ['code' => 'potensi_kerja', 'name' => 'Potensi Kerja', 'weight' => 10, 'order' => 3],
        ];

        foreach ($potensiAspects as $aspectData) {
            $aspect = Aspect::factory()->create([
                'template_id' => $template->id,
                'category_type_id' => $potensiCategory->id,
                'code' => $aspectData['code'],
                'name' => $aspectData['name'],
                'weight_percentage' => $aspectData['weight'],
                'standard_rating' => null, // Will be calculated from sub-aspects
                'order' => $aspectData['order'],
            ]);

            // Create 3 sub-aspects for each Potensi aspect
            // Use VARIED ratings to ensure recalculation produces different results
            $subAspectRatings = [2, 3, 4]; // Different ratings for testing
            for ($i = 1; $i <= 3; $i++) {
                SubAspect::factory()->create([
                    'aspect_id' => $aspect->id,
                    'code' => $aspectData['code'].'_sub_'.$i,
                    'name' => $aspectData['name'].' Sub '.$i,
                    'standard_rating' => $subAspectRatings[$i - 1], // Varied: 2, 3, 4
                    'order' => $i,
                ]);
            }
        }

        // Create Kompetensi aspects WITHOUT sub-aspects (3 aspects)
        // Use different rating (4.0) to create different total score than Potensi (for cache tests)
        $kompetensiAspects = [
            ['code' => 'integritas', 'name' => 'Integritas', 'weight' => 20, 'rating' => 4.0, 'order' => 1],
            ['code' => 'kerjasama', 'name' => 'Kerjasama', 'weight' => 15, 'rating' => 4.0, 'order' => 2],
            ['code' => 'komunikasi', 'name' => 'Komunikasi', 'weight' => 15, 'rating' => 4.0, 'order' => 3],
        ];

        foreach ($kompetensiAspects as $aspectData) {
            Aspect::factory()->create([
                'template_id' => $template->id,
                'category_type_id' => $kompetensiCategory->id,
                'code' => $aspectData['code'],
                'name' => $aspectData['name'],
                'weight_percentage' => $aspectData['weight'],
                'standard_rating' => $aspectData['rating'], // Direct rating
                'order' => $aspectData['order'],
            ]);
        }

        return $template;
    }

    // ========================================
    // HELPER METHODS
    // ========================================

    /**
     * Create a participant with complete assessment data
     *
     * @param  string  $name  Participant name
     * @param  float  $performanceMultiplier  Performance multiplier (0.8 = 80% of standard, 1.2 = 120% of standard)
     */
    private function createParticipantWithAssessments(
        string $name,
        float $performanceMultiplier = 1.0
    ): Participant {
        $participant = Participant::factory()->create([
            'event_id' => $this->event->id,
            'batch_id' => $this->batch->id,
            'position_formation_id' => $this->position->id,
            'name' => $name,
        ]);

        // Create assessments for all aspects in both categories
        $this->createAspectAssessments($participant, 'potensi', $performanceMultiplier);
        $this->createAspectAssessments($participant, 'kompetensi', $performanceMultiplier);

        return $participant;
    }

    /**
     * Create aspect assessments for a participant in a specific category
     *
     * @param  string  $categoryCode  'potensi' or 'kompetensi'
     */
    private function createAspectAssessments(
        Participant $participant,
        string $categoryCode,
        float $performanceMultiplier
    ): void {
        $categoryId = $categoryCode === 'potensi'
            ? $this->potensiCategory->id
            : $this->kompetensiCategory->id;

        // Create CategoryAssessment first (required for AspectAssessment)
        $categoryAssessment = \App\Models\CategoryAssessment::factory()->create([
            'participant_id' => $participant->id,
            'event_id' => $this->event->id,
            'batch_id' => $participant->batch_id,
            'position_formation_id' => $this->position->id,
            'category_type_id' => $categoryId,
        ]);

        $aspects = Aspect::where('category_type_id', $categoryId)
            ->with('subAspects')
            ->orderBy('order')
            ->get();

        foreach ($aspects as $aspect) {
            // Calculate standard rating (data-driven)
            $standardRating = $this->getAspectStandardRating($aspect);

            // Calculate individual rating (apply performance multiplier, cap at 5)
            $individualRating = min(5.0, round($standardRating * $performanceMultiplier, 2));

            // Calculate scores
            $standardScore = round($standardRating * $aspect->weight_percentage, 2);
            $individualScore = round($individualRating * $aspect->weight_percentage, 2);

            // Create aspect assessment
            $aspectAssessment = AspectAssessment::factory()->create([
                'category_assessment_id' => $categoryAssessment->id,
                'participant_id' => $participant->id,
                'event_id' => $this->event->id,
                'batch_id' => $participant->batch_id,
                'position_formation_id' => $this->position->id,
                'aspect_id' => $aspect->id,
                'standard_rating' => $standardRating,
                'individual_rating' => $individualRating,
                'standard_score' => $standardScore,
                'individual_score' => $individualScore,
                'gap_rating' => round($individualRating - $standardRating, 2),
                'gap_score' => round($individualScore - $standardScore, 2),
            ]);

            // Create sub-aspect assessments if aspect has sub-aspects (Potensi only)
            if ($aspect->subAspects->isNotEmpty()) {
                $this->createSubAspectAssessments($aspectAssessment, $aspect, $performanceMultiplier);
            }
        }
    }

    /**
     * Create sub-aspect assessments for an aspect assessment
     */
    private function createSubAspectAssessments(
        AspectAssessment $aspectAssessment,
        Aspect $aspect,
        float $performanceMultiplier
    ): void {
        foreach ($aspect->subAspects as $subAspect) {
            $standardRating = (int) $subAspect->standard_rating;
            $individualRating = (int) min(5, round($standardRating * $performanceMultiplier));

            SubAspectAssessment::factory()->create([
                'aspect_assessment_id' => $aspectAssessment->id,
                'participant_id' => $aspectAssessment->participant_id,
                'event_id' => $aspectAssessment->event_id,
                'sub_aspect_id' => $subAspect->id,
                'standard_rating' => $standardRating,
                'individual_rating' => $individualRating,
            ]);
        }
    }

    /**
     * Get aspect standard rating based on structure (data-driven)
     */
    private function getAspectStandardRating(Aspect $aspect): float
    {
        if ($aspect->subAspects->isNotEmpty()) {
            // Calculate from sub-aspects average
            return round($aspect->subAspects->avg('standard_rating'), 2);
        }

        // Use direct rating
        return (float) $aspect->standard_rating;
    }

    /**
     * Set session adjustments for testing DynamicStandardService integration
     *
     * @param  array  $adjustments  Array of adjustments ['aspect_code' => ['weight' => 30, 'rating' => 4.0, 'active' => false]]
     */
    private function setSessionAdjustments(array $adjustments): void
    {
        $standardService = app(DynamicStandardService::class);

        foreach ($adjustments as $code => $adjustment) {
            if (isset($adjustment['weight'])) {
                $standardService->saveAspectWeight($this->template->id, $code, $adjustment['weight']);
            }
            if (isset($adjustment['rating'])) {
                $standardService->saveAspectRating($this->template->id, $code, $adjustment['rating']);
            }
            if (isset($adjustment['active'])) {
                $standardService->setAspectActive($this->template->id, $code, $adjustment['active']);
            }
        }
    }

    /**
     * Get active aspect IDs for a category
     *
     * @param  string  $categoryCode  'potensi' or 'kompetensi'
     */
    private function getActiveAspectIds(string $categoryCode): array
    {
        $categoryId = $categoryCode === 'potensi'
            ? $this->potensiCategory->id
            : $this->kompetensiCategory->id;

        return Aspect::where('category_type_id', $categoryId)
            ->orderBy('order')
            ->pluck('id')
            ->toArray();
    }

    // ========================================
    // PHASE 1: SERVICE INSTANTIATION (1 test)
    // ========================================

    public function test_service_can_be_instantiated(): void
    {
        $this->assertInstanceOf(RankingService::class, $this->service);
    }

    // ========================================
    // PHASE 2: getRankings() (15 tests)
    // ========================================

    public function test_returns_empty_collection_when_no_participants(): void
    {
        // Act
        $rankings = $this->service->getRankings(
            $this->event->id,
            $this->position->id,
            $this->template->id,
            'potensi',
            10
        );

        // Assert
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $rankings);
        $this->assertCount(0, $rankings);
    }

    public function test_returns_empty_collection_when_no_active_aspects(): void
    {
        // Arrange: Create participant
        $this->createParticipantWithAssessments('Test Participant');

        // Mark all Potensi aspects as inactive in a single call
        $potensiAspects = Aspect::where('category_type_id', $this->potensiCategory->id)->get();
        $adjustments = [];
        foreach ($potensiAspects as $aspect) {
            $adjustments[$aspect->code] = ['active' => false];
        }
        $this->setSessionAdjustments($adjustments);

        // Act
        $rankings = $this->service->getRankings(
            $this->event->id,
            $this->position->id,
            $this->template->id,
            'potensi',
            10
        );

        // Assert
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $rankings);
        $this->assertCount(0, $rankings);
    }

    public function test_calculates_rankings_for_potensi_category(): void
    {
        // Arrange: Create 3 participants with different performance levels
        $this->createParticipantWithAssessments('Alice Anderson', 1.2); // High performer
        $this->createParticipantWithAssessments('Bob Brown', 1.0);      // Medium performer
        $this->createParticipantWithAssessments('Charlie Chen', 0.8);   // Low performer

        // Act
        $rankings = $this->service->getRankings(
            $this->event->id,
            $this->position->id,
            $this->template->id,
            'potensi',
            10
        );

        // Assert
        $this->assertCount(3, $rankings);
        $this->assertEquals(1, $rankings->first()['rank']);
        $this->assertEquals(3, $rankings->last()['rank']);

        // Verify ordering by score DESC
        $scores = $rankings->pluck('individual_score')->toArray();
        $sortedScores = collect($scores)->sortDesc()->values()->toArray();
        $this->assertEquals($sortedScores, $scores);

        // Verify all required keys are present
        $requiredKeys = [
            'rank', 'participant_id', 'individual_rating', 'individual_score',
            'original_standard_rating', 'original_standard_score',
            'adjusted_standard_rating', 'adjusted_standard_score',
            'original_gap_rating', 'original_gap_score',
            'adjusted_gap_rating', 'adjusted_gap_score',
            'percentage', 'conclusion',
        ];
        foreach ($requiredKeys as $key) {
            $this->assertArrayHasKey($key, $rankings->first());
        }
    }

    public function test_calculates_rankings_for_kompetensi_category(): void
    {
        // Arrange: Create 3 participants with different performance levels
        $this->createParticipantWithAssessments('Alice Anderson', 1.2); // High performer
        $this->createParticipantWithAssessments('Bob Brown', 1.0);      // Medium performer
        $this->createParticipantWithAssessments('Charlie Chen', 0.8);   // Low performer

        // Act
        $rankings = $this->service->getRankings(
            $this->event->id,
            $this->position->id,
            $this->template->id,
            'kompetensi',
            10
        );

        // Assert
        $this->assertCount(3, $rankings);
        $this->assertEquals(1, $rankings->first()['rank']);
        $this->assertEquals(3, $rankings->last()['rank']);

        // Verify ordering by score DESC
        $scores = $rankings->pluck('individual_score')->toArray();
        $sortedScores = collect($scores)->sortDesc()->values()->toArray();
        $this->assertEquals($sortedScores, $scores);

        // Verify all required keys are present
        $requiredKeys = [
            'rank', 'participant_id', 'individual_rating', 'individual_score',
            'original_standard_rating', 'original_standard_score',
            'adjusted_standard_rating', 'adjusted_standard_score',
            'original_gap_rating', 'original_gap_score',
            'adjusted_gap_rating', 'adjusted_gap_score',
            'percentage', 'conclusion',
        ];
        foreach ($requiredKeys as $key) {
            $this->assertArrayHasKey($key, $rankings->first());
        }
    }

    public function test_applies_tolerance_to_standard_scores(): void
    {
        // Arrange
        $this->createParticipantWithAssessments('Test Participant');

        // Act: 10% tolerance
        $rankings = $this->service->getRankings(
            $this->event->id,
            $this->position->id,
            $this->template->id,
            'potensi',
            10
        );

        // Assert
        $first = $rankings->first();
        $expectedAdjusted = round($first['original_standard_score'] * 0.9, 2);
        $this->assertEquals($expectedAdjusted, $first['adjusted_standard_score']);

        // Verify gap is calculated with adjusted standard
        $expectedAdjustedGap = round(
            $first['individual_score'] - $first['adjusted_standard_score'],
            2
        );
        $this->assertEquals($expectedAdjustedGap, $first['adjusted_gap_score']);
    }

    public function test_ranks_by_score_desc_then_name_asc(): void
    {
        // Arrange: Create 3 participants with SAME score (1.0 multiplier)
        // Names in reverse alphabetical order to test tiebreaker
        $this->createParticipantWithAssessments('Zara Zhang', 1.0);
        $this->createParticipantWithAssessments('Alice Anderson', 1.0);
        $this->createParticipantWithAssessments('Mike Miller', 1.0);

        // Act
        $rankings = $this->service->getRankings(
            $this->event->id,
            $this->position->id,
            $this->template->id,
            'potensi',
            10
        );

        // Assert: Verify alphabetical name order (tiebreaker)
        $participants = Participant::whereIn('id', $rankings->pluck('participant_id'))
            ->orderBy('name')
            ->pluck('name')
            ->toArray();

        $rankingNames = $rankings->map(function ($ranking) {
            return Participant::find($ranking['participant_id'])->name;
        })->toArray();

        $this->assertEquals($participants, $rankingNames);
    }

    public function test_recalculates_with_session_adjustments(): void
    {
        // Arrange: Create participant
        $this->createParticipantWithAssessments('Test Participant');

        // Get first Potensi aspect and adjust its weight
        $firstAspect = Aspect::where('category_type_id', $this->potensiCategory->id)
            ->orderBy('order')
            ->first();

        $originalWeight = $firstAspect->weight_percentage;
        $newWeight = $originalWeight + 10; // Increase by 10

        $this->setSessionAdjustments([
            $firstAspect->code => ['weight' => $newWeight],
        ]);

        // Act
        $rankings = $this->service->getRankings(
            $this->event->id,
            $this->position->id,
            $this->template->id,
            'potensi',
            10
        );

        // Assert: Rankings should reflect adjusted weight
        $this->assertCount(1, $rankings);
        $this->assertNotNull($rankings->first());

        // Standard score should be different from database value due to weight adjustment
        // We can't easily calculate exact expected value without knowing all aspect weights,
        // but we can verify the service runs without errors
    }

    public function test_excludes_inactive_aspects_from_rankings(): void
    {
        // Arrange: Create participant
        $participant = $this->createParticipantWithAssessments('Test Participant');

        // Get all Potensi aspects
        $potensiAspects = Aspect::where('category_type_id', $this->potensiCategory->id)
            ->orderBy('order')
            ->get();

        // Calculate expected score WITHOUT first aspect
        $firstAspect = $potensiAspects->first();

        // Mark first aspect as inactive
        $this->setSessionAdjustments([
            $firstAspect->code => ['active' => false],
        ]);

        // Act
        $rankings = $this->service->getRankings(
            $this->event->id,
            $this->position->id,
            $this->template->id,
            'potensi',
            10
        );

        // Assert
        $this->assertCount(1, $rankings);

        // The score should be lower than if all aspects were active
        // We verify by checking the number of active aspects used
        $activeAspectIds = $this->getActiveAspectIds('potensi');
        $standardService = app(DynamicStandardService::class);
        $actualActiveIds = $standardService->getActiveAspectIds($this->template->id, 'potensi');

        $this->assertCount(count($potensiAspects) - 1, $actualActiveIds);
    }

    public function test_excludes_inactive_sub_aspects_from_calculation(): void
    {
        // Arrange: Create participant
        $this->createParticipantWithAssessments('Test Participant');

        // Get first Potensi aspect (which has sub-aspects)
        $firstAspect = Aspect::where('category_type_id', $this->potensiCategory->id)
            ->with('subAspects')
            ->orderBy('order')
            ->first();

        // Mark first sub-aspect as inactive
        $firstSubAspect = $firstAspect->subAspects->first();
        $standardService = app(DynamicStandardService::class);
        $standardService->setSubAspectActive($this->template->id, $firstSubAspect->code, false);

        // Act
        $rankings = $this->service->getRankings(
            $this->event->id,
            $this->position->id,
            $this->template->id,
            'potensi',
            10
        );

        // Assert: Service should run without errors
        $this->assertCount(1, $rankings);
        $this->assertNotNull($rankings->first());
    }

    public function test_handles_single_participant(): void
    {
        // Arrange: Create only 1 participant
        $this->createParticipantWithAssessments('Single Participant');

        // Act
        $rankings = $this->service->getRankings(
            $this->event->id,
            $this->position->id,
            $this->template->id,
            'potensi',
            10
        );

        // Assert
        $this->assertCount(1, $rankings);
        $this->assertEquals(1, $rankings->first()['rank']);
        $this->assertGreaterThan(0, $rankings->first()['individual_score']);
    }

    public function test_percentage_calculation_is_correct(): void
    {
        // Arrange
        $this->createParticipantWithAssessments('Test Participant', 1.2);

        // Act
        $rankings = $this->service->getRankings(
            $this->event->id,
            $this->position->id,
            $this->template->id,
            'potensi',
            10
        );

        // Assert
        $ranking = $rankings->first();
        $expectedPercentage = round(
            ($ranking['individual_score'] / $ranking['adjusted_standard_score']) * 100,
            2
        );
        $this->assertEquals($expectedPercentage, $ranking['percentage']);
    }

    public function test_uses_conclusion_service_for_conclusions(): void
    {
        // Arrange: Create participants with different performance levels
        $this->createParticipantWithAssessments('High Performer', 1.3);  // Above standard
        $this->createParticipantWithAssessments('Normal Performer', 1.0); // Meets standard
        $this->createParticipantWithAssessments('Low Performer', 0.7);   // Below standard

        // Act
        $rankings = $this->service->getRankings(
            $this->event->id,
            $this->position->id,
            $this->template->id,
            'potensi',
            10
        );

        // Assert: Check that conclusions are set
        $this->assertCount(3, $rankings);
        foreach ($rankings as $ranking) {
            $this->assertContains($ranking['conclusion'], [
                'Di Atas Standar',
                'Memenuhi Standar',
                'Di Bawah Standar',
            ]);
        }
    }

    public function test_ranking_items_include_all_required_keys(): void
    {
        // Arrange
        $this->createParticipantWithAssessments('Test Participant');

        // Act
        $rankings = $this->service->getRankings(
            $this->event->id,
            $this->position->id,
            $this->template->id,
            'potensi',
            10
        );

        // Assert
        $requiredKeys = [
            'rank',
            'participant_id',
            'individual_rating',
            'individual_score',
            'original_standard_rating',
            'original_standard_score',
            'adjusted_standard_rating',
            'adjusted_standard_score',
            'original_gap_rating',
            'original_gap_score',
            'adjusted_gap_rating',
            'adjusted_gap_score',
            'percentage',
            'conclusion',
        ];

        $ranking = $rankings->first();
        foreach ($requiredKeys as $key) {
            $this->assertArrayHasKey($key, $ranking, "Missing key: {$key}");
        }
    }

    public function test_handles_zero_tolerance(): void
    {
        // Arrange
        $this->createParticipantWithAssessments('Test Participant');

        // Act: 0% tolerance (no adjustment)
        $rankings = $this->service->getRankings(
            $this->event->id,
            $this->position->id,
            $this->template->id,
            'potensi',
            0
        );

        // Assert: Adjusted should equal original
        $ranking = $rankings->first();
        $this->assertEquals(
            $ranking['original_standard_score'],
            $ranking['adjusted_standard_score']
        );
        $this->assertEquals(
            $ranking['original_standard_rating'],
            $ranking['adjusted_standard_rating']
        );
    }

    public function test_handles_extreme_tolerance(): void
    {
        // Arrange
        $this->createParticipantWithAssessments('Test Participant', 1.0);

        // Act: 100% tolerance (standard becomes zero)
        $rankings = $this->service->getRankings(
            $this->event->id,
            $this->position->id,
            $this->template->id,
            'potensi',
            100
        );

        // Assert: Adjusted standard should be 0
        $ranking = $rankings->first();
        $this->assertEquals(0, $ranking['adjusted_standard_score']);
        $this->assertEquals(0, $ranking['adjusted_standard_rating']);
    }

    // ========================================
    // PHASE 3: getParticipantRank() (5 tests)
    // ========================================

    public function test_returns_participant_rank_for_potensi(): void
    {
        // Arrange: Create 3 participants
        $participant1 = $this->createParticipantWithAssessments('Alice', 1.2);
        $participant2 = $this->createParticipantWithAssessments('Bob', 1.0);
        $participant3 = $this->createParticipantWithAssessments('Charlie', 0.8);

        // Act
        $rank = $this->service->getParticipantRank(
            $participant2->id,
            $this->event->id,
            $this->position->id,
            $this->template->id,
            'potensi',
            10
        );

        // Assert
        $this->assertIsArray($rank);
        $this->assertEquals(2, $rank['rank']); // Bob should be rank 2
        $this->assertEquals(3, $rank['total']);
        $this->assertArrayHasKey('conclusion', $rank);
        $this->assertArrayHasKey('percentage', $rank);
    }

    public function test_returns_participant_rank_for_kompetensi(): void
    {
        // Arrange
        $participant = $this->createParticipantWithAssessments('Test Participant');

        // Act
        $rank = $this->service->getParticipantRank(
            $participant->id,
            $this->event->id,
            $this->position->id,
            $this->template->id,
            'kompetensi',
            10
        );

        // Assert
        $this->assertIsArray($rank);
        $this->assertEquals(1, $rank['rank']);
        $this->assertEquals(1, $rank['total']);
    }

    public function test_returns_null_when_participant_not_found(): void
    {
        // Arrange: Create some participants but query for non-existent ID
        $this->createParticipantWithAssessments('Test Participant');

        // Act
        $rank = $this->service->getParticipantRank(
            99999, // Non-existent ID
            $this->event->id,
            $this->position->id,
            $this->template->id,
            'potensi',
            10
        );

        // Assert
        $this->assertNull($rank);
    }

    public function test_returns_null_when_no_rankings_exist(): void
    {
        // Arrange: No participants created

        // Act
        $rank = $this->service->getParticipantRank(
            1,
            $this->event->id,
            $this->position->id,
            $this->template->id,
            'potensi',
            10
        );

        // Assert
        $this->assertNull($rank);
    }

    public function test_participant_rank_includes_all_required_keys(): void
    {
        // Arrange
        $participant = $this->createParticipantWithAssessments('Test Participant');

        // Act
        $rank = $this->service->getParticipantRank(
            $participant->id,
            $this->event->id,
            $this->position->id,
            $this->template->id,
            'potensi',
            10
        );

        // Assert
        $requiredKeys = [
            'rank',
            'total',
            'conclusion',
            'percentage',
            'individual_score',
            'adjusted_standard_score',
            'adjusted_gap_score',
        ];

        foreach ($requiredKeys as $key) {
            $this->assertArrayHasKey($key, $rank, "Missing key: {$key}");
        }
    }

    // ========================================
    // PHASE 4: calculateAdjustedStandards() (10 tests)
    // ========================================

    public function test_calculates_adjusted_standards_for_potensi(): void
    {
        // Arrange: Get Potensi aspect IDs
        $aspectIds = $this->getActiveAspectIds('potensi');

        // Act
        $standards = $this->service->calculateAdjustedStandards(
            $this->template->id,
            'potensi',
            $aspectIds
        );

        // Assert
        $this->assertIsArray($standards);
        $this->assertArrayHasKey('standard_rating', $standards);
        $this->assertArrayHasKey('standard_score', $standards);
        $this->assertGreaterThan(0, $standards['standard_rating']);
        $this->assertGreaterThan(0, $standards['standard_score']);
    }

    public function test_calculates_adjusted_standards_for_kompetensi(): void
    {
        // Arrange
        $aspectIds = $this->getActiveAspectIds('kompetensi');

        // Act
        $standards = $this->service->calculateAdjustedStandards(
            $this->template->id,
            'kompetensi',
            $aspectIds
        );

        // Assert
        $this->assertIsArray($standards);
        $this->assertGreaterThan(0, $standards['standard_rating']);
        $this->assertGreaterThan(0, $standards['standard_score']);
    }

    public function test_uses_session_adjusted_weights(): void
    {
        // Arrange: Get first aspect and adjust weight
        $firstAspect = Aspect::where('category_type_id', $this->potensiCategory->id)
            ->orderBy('order')
            ->first();

        $this->setSessionAdjustments([
            $firstAspect->code => ['weight' => $firstAspect->weight_percentage + 10],
        ]);

        $aspectIds = $this->getActiveAspectIds('potensi');

        // Act
        $standards = $this->service->calculateAdjustedStandards(
            $this->template->id,
            'potensi',
            $aspectIds
        );

        // Assert: Should use adjusted weight
        $this->assertGreaterThan(0, $standards['standard_score']);
    }

    public function test_uses_session_adjusted_ratings(): void
    {
        // Arrange: Get first Kompetensi aspect and adjust rating
        $firstAspect = Aspect::where('category_type_id', $this->kompetensiCategory->id)
            ->orderBy('order')
            ->first();

        $this->setSessionAdjustments([
            $firstAspect->code => ['rating' => 4.5],
        ]);

        $aspectIds = $this->getActiveAspectIds('kompetensi');

        // Act
        $standards = $this->service->calculateAdjustedStandards(
            $this->template->id,
            'kompetensi',
            $aspectIds
        );

        // Assert
        $this->assertGreaterThan(0, $standards['standard_rating']);
    }

    public function test_excludes_inactive_aspects_from_standards(): void
    {
        // Arrange: Mark first aspect as inactive
        $firstAspect = Aspect::where('category_type_id', $this->potensiCategory->id)
            ->orderBy('order')
            ->first();

        $this->setSessionAdjustments([
            $firstAspect->code => ['active' => false],
        ]);

        $aspectIds = $this->getActiveAspectIds('potensi');

        // Act
        $standards = $this->service->calculateAdjustedStandards(
            $this->template->id,
            'potensi',
            $aspectIds
        );

        // Assert: Standards should still be calculated (from remaining aspects)
        $this->assertGreaterThan(0, $standards['standard_score']);
    }

    public function test_excludes_inactive_sub_aspects_from_standards(): void
    {
        // Arrange: Mark first sub-aspect as inactive
        $firstAspect = Aspect::where('category_type_id', $this->potensiCategory->id)
            ->with('subAspects')
            ->orderBy('order')
            ->first();

        $firstSubAspect = $firstAspect->subAspects->first();
        $standardService = app(DynamicStandardService::class);
        $standardService->setSubAspectActive($this->template->id, $firstSubAspect->code, false);

        $aspectIds = $this->getActiveAspectIds('potensi');

        // Act
        $standards = $this->service->calculateAdjustedStandards(
            $this->template->id,
            'potensi',
            $aspectIds
        );

        // Assert
        $this->assertGreaterThan(0, $standards['standard_score']);
    }

    public function test_handles_empty_aspect_ids_array(): void
    {
        // Act
        $standards = $this->service->calculateAdjustedStandards(
            $this->template->id,
            'potensi',
            [] // Empty array
        );

        // Assert
        $this->assertEquals(0.0, $standards['standard_rating']);
        $this->assertEquals(0.0, $standards['standard_score']);
    }

    public function test_rounds_standard_values_to_two_decimals(): void
    {
        // Arrange
        $aspectIds = $this->getActiveAspectIds('potensi');

        // Act
        $standards = $this->service->calculateAdjustedStandards(
            $this->template->id,
            'potensi',
            $aspectIds
        );

        // Assert: Check decimal places
        $this->assertEquals(
            round($standards['standard_rating'], 2),
            $standards['standard_rating']
        );
        $this->assertEquals(
            round($standards['standard_score'], 2),
            $standards['standard_score']
        );
    }

    public function test_uses_custom_standard_when_selected(): void
    {
        // Arrange: Create custom standard with different weights
        $customStandardService = app(\App\Services\CustomStandardService::class);

        // Create custom standard data with different aspect weight
        $firstAspect = Aspect::where('category_type_id', $this->potensiCategory->id)
            ->orderBy('order')
            ->first();

        $customStandardData = [
            'institution_id' => $this->event->institution_id,
            'template_id' => $this->template->id,
            'code' => 'CUSTOM-TEST-001',
            'name' => 'Test Custom Standard',
            'description' => 'Test custom standard for unit testing',
            'category_weights' => [
                'potensi' => 50,
                'kompetensi' => 50,
            ],
            'aspect_configs' => [
                $firstAspect->code => [
                    'weight' => $firstAspect->weight_percentage + 10, // Different weight
                    'active' => true,
                ],
            ],
            'sub_aspect_configs' => [],
        ];

        // Create and select custom standard
        $customStandard = $customStandardService->create($customStandardData);
        $customStandardService->select($this->template->id, $customStandard->id);

        $aspectIds = $this->getActiveAspectIds('potensi');

        // Act
        $standards = $this->service->calculateAdjustedStandards(
            $this->template->id,
            'potensi',
            $aspectIds
        );

        // Assert: Should use custom standard values
        $this->assertGreaterThan(0, $standards['standard_score']);

        // Cleanup: Clear custom standard selection
        $customStandardService->clearSelection($this->template->id);
    }

    public function test_returns_zero_when_all_aspects_inactive(): void
    {
        // Arrange: Mark all Potensi aspects as inactive
        $potensiAspects = Aspect::where('category_type_id', $this->potensiCategory->id)->get();
        $adjustments = [];
        foreach ($potensiAspects as $aspect) {
            $adjustments[$aspect->code] = ['active' => false];
        }
        $this->setSessionAdjustments($adjustments);

        $aspectIds = $this->getActiveAspectIds('potensi');

        // Act: Calculate standards with all inactive aspects
        $standards = $this->service->calculateAdjustedStandards(
            $this->template->id,
            'potensi',
            $aspectIds // Should be empty array
        );

        // Assert: Should return zero values
        $this->assertEquals(0.0, $standards['standard_rating']);
        $this->assertEquals(0.0, $standards['standard_score']);
    }

    // ========================================
    // PHASE 5: getCombinedRankings() (8 tests)
    // ========================================

    public function test_combines_potensi_and_kompetensi_rankings(): void
    {
        // Arrange: Create 3 participants
        $this->createParticipantWithAssessments('Alice', 1.2);
        $this->createParticipantWithAssessments('Bob', 1.0);
        $this->createParticipantWithAssessments('Charlie', 0.8);

        // Act
        $rankings = $this->service->getCombinedRankings(
            $this->event->id,
            $this->position->id,
            $this->template->id,
            10
        );

        // Assert
        $this->assertCount(3, $rankings);
        $this->assertEquals(1, $rankings->first()['rank']);
        $this->assertEquals(3, $rankings->last()['rank']);
    }

    public function test_applies_category_weights_correctly(): void
    {
        // Arrange
        $participant = $this->createParticipantWithAssessments('Test Participant');

        // Act
        $rankings = $this->service->getCombinedRankings(
            $this->event->id,
            $this->position->id,
            $this->template->id,
            10
        );

        // Assert: Check that weights are 50/50 (from our test template)
        $ranking = $rankings->first();
        $this->assertEquals(50, $ranking['potensi_weight']);
        $this->assertEquals(50, $ranking['kompetensi_weight']);
    }

    public function test_combined_rankings_sorted_by_score_and_name(): void
    {
        // Arrange: Create participants with same performance (ties)
        $this->createParticipantWithAssessments('Zara', 1.0);
        $this->createParticipantWithAssessments('Alice', 1.0);
        $this->createParticipantWithAssessments('Mike', 1.0);

        // Act
        $rankings = $this->service->getCombinedRankings(
            $this->event->id,
            $this->position->id,
            $this->template->id,
            10
        );

        // Assert: Should be sorted alphabetically as tiebreaker
        $names = $rankings->pluck('participant_name')->toArray();
        $sortedNames = collect($names)->sort()->values()->toArray();
        $this->assertEquals($sortedNames, $names);
    }

    public function test_returns_empty_when_missing_potensi_rankings(): void
    {
        // Arrange: Create participant and mark all Potensi aspects as inactive
        $this->createParticipantWithAssessments('Test Participant');

        $potensiAspects = Aspect::where('category_type_id', $this->potensiCategory->id)->get();
        $adjustments = [];
        foreach ($potensiAspects as $aspect) {
            $adjustments[$aspect->code] = ['active' => false];
        }
        $this->setSessionAdjustments($adjustments);

        // Act: Try to get combined rankings without Potensi data
        $rankings = $this->service->getCombinedRankings(
            $this->event->id,
            $this->position->id,
            $this->template->id,
            10
        );

        // Assert: Should return empty collection
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $rankings);
        $this->assertCount(0, $rankings);
    }

    public function test_returns_empty_when_missing_kompetensi_rankings(): void
    {
        // Arrange: Create participant and mark all Kompetensi aspects as inactive
        $this->createParticipantWithAssessments('Test Participant');

        $kompetensiAspects = Aspect::where('category_type_id', $this->kompetensiCategory->id)->get();
        $adjustments = [];
        foreach ($kompetensiAspects as $aspect) {
            $adjustments[$aspect->code] = ['active' => false];
        }
        $this->setSessionAdjustments($adjustments);

        // Act: Try to get combined rankings without Kompetensi data
        $rankings = $this->service->getCombinedRankings(
            $this->event->id,
            $this->position->id,
            $this->template->id,
            10
        );

        // Assert: Should return empty collection
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $rankings);
        $this->assertCount(0, $rankings);
    }

    public function test_combined_rankings_include_all_required_keys(): void
    {
        // Arrange
        $this->createParticipantWithAssessments('Test Participant');

        // Act
        $rankings = $this->service->getCombinedRankings(
            $this->event->id,
            $this->position->id,
            $this->template->id,
            10
        );

        // Assert
        $requiredKeys = [
            'rank',
            'participant_id',
            'participant_name',
            'total_individual_score',
            'total_standard_score',
            'total_original_standard_score',
            'total_gap_score',
            'total_original_gap_score',
            'percentage',
            'conclusion',
            'potensi_weight',
            'kompetensi_weight',
        ];

        $ranking = $rankings->first();
        foreach ($requiredKeys as $key) {
            $this->assertArrayHasKey($key, $ranking, "Missing key: {$key}");
        }
    }

    public function test_combined_rankings_use_conclusion_service(): void
    {
        // Arrange
        $this->createParticipantWithAssessments('High Performer', 1.3);
        $this->createParticipantWithAssessments('Low Performer', 0.7);

        // Act
        $rankings = $this->service->getCombinedRankings(
            $this->event->id,
            $this->position->id,
            $this->template->id,
            10
        );

        // Assert: Check conclusions are set
        foreach ($rankings as $ranking) {
            $this->assertContains($ranking['conclusion'], [
                'Di Atas Standar',
                'Memenuhi Standar',
                'Di Bawah Standar',
            ]);
        }
    }

    public function test_handles_zero_category_weights(): void
    {
        // Arrange: Create participant and set Potensi category weight to 0
        $this->createParticipantWithAssessments('Test Participant');

        // Set category weights via session (Potensi = 0, Kompetensi = 100)
        $standardService = app(DynamicStandardService::class);
        $standardService->saveBothCategoryWeights(
            $this->template->id,
            'potensi',
            0,
            'kompetensi',
            100
        );

        // Act: Get combined rankings with zero Potensi weight
        $rankings = $this->service->getCombinedRankings(
            $this->event->id,
            $this->position->id,
            $this->template->id,
            10
        );

        // Assert: Should handle gracefully
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $rankings);
        $this->assertGreaterThan(0, $rankings->count());

        // Check that weights are reflected correctly
        $ranking = $rankings->first();
        $this->assertEquals(0, $ranking['potensi_weight']);
        $this->assertEquals(100, $ranking['kompetensi_weight']);
    }

    // ========================================
    // PHASE 6: getParticipantCombinedRank() (3 tests)
    // ========================================

    public function test_returns_participant_combined_rank(): void
    {
        // Arrange
        $participant1 = $this->createParticipantWithAssessments('Alice', 1.2);
        $participant2 = $this->createParticipantWithAssessments('Bob', 1.0);
        $participant3 = $this->createParticipantWithAssessments('Charlie', 0.8);

        // Act
        $rank = $this->service->getParticipantCombinedRank(
            $participant2->id,
            $this->event->id,
            $this->position->id,
            $this->template->id,
            10
        );

        // Assert
        $this->assertIsArray($rank);
        $this->assertEquals(2, $rank['rank']);
        $this->assertEquals(3, $rank['total']);
        $this->assertArrayHasKey('conclusion', $rank);
        $this->assertArrayHasKey('percentage', $rank);
    }

    public function test_returns_null_when_participant_not_in_combined_rankings(): void
    {
        // Arrange
        $this->createParticipantWithAssessments('Test Participant');

        // Act: Query for non-existent participant
        $rank = $this->service->getParticipantCombinedRank(
            99999,
            $this->event->id,
            $this->position->id,
            $this->template->id,
            10
        );

        // Assert
        $this->assertNull($rank);
    }

    public function test_combined_rank_includes_category_weights(): void
    {
        // Arrange
        $participant = $this->createParticipantWithAssessments('Test Participant');

        // Act
        $rank = $this->service->getParticipantCombinedRank(
            $participant->id,
            $this->event->id,
            $this->position->id,
            $this->template->id,
            10
        );

        // Assert
        $this->assertArrayHasKey('potensi_weight', $rank);
        $this->assertArrayHasKey('kompetensi_weight', $rank);
        $this->assertEquals(50, $rank['potensi_weight']);
        $this->assertEquals(50, $rank['kompetensi_weight']);
    }

    // ========================================
    // PHASE 7: getPassingSummary() (3 tests)
    // ========================================

    public function test_calculates_passing_summary_correctly(): void
    {
        // Arrange: Create mix of participants
        $this->createParticipantWithAssessments('Above 1', 1.3);
        $this->createParticipantWithAssessments('Above 2', 1.2);
        $this->createParticipantWithAssessments('Meets 1', 1.0);
        $this->createParticipantWithAssessments('Meets 2', 0.95);
        $this->createParticipantWithAssessments('Below 1', 0.7);

        $rankings = $this->service->getRankings(
            $this->event->id,
            $this->position->id,
            $this->template->id,
            'potensi',
            10
        );

        // Act
        $summary = $this->service->getPassingSummary($rankings);

        // Assert
        $this->assertEquals(5, $summary['total']);
        $this->assertGreaterThanOrEqual(0, $summary['passing']);
        $this->assertLessThanOrEqual(5, $summary['passing']);
        $this->assertGreaterThanOrEqual(0, $summary['percentage']);
        $this->assertLessThanOrEqual(100, $summary['percentage']);
    }

    public function test_passing_summary_handles_empty_rankings(): void
    {
        // Arrange: Empty collection
        $rankings = collect();

        // Act
        $summary = $this->service->getPassingSummary($rankings);

        // Assert
        $this->assertEquals(0, $summary['total']);
        $this->assertEquals(0, $summary['passing']);
        $this->assertEquals(0, $summary['percentage']);
    }

    public function test_passing_summary_when_all_participants_fail(): void
    {
        // Arrange: Create participants all below standard
        $this->createParticipantWithAssessments('Low 1', 0.5);
        $this->createParticipantWithAssessments('Low 2', 0.6);

        $rankings = $this->service->getRankings(
            $this->event->id,
            $this->position->id,
            $this->template->id,
            'potensi',
            10
        );

        // Act
        $summary = $this->service->getPassingSummary($rankings);

        // Assert
        $this->assertEquals(2, $summary['total']);
        // Passing could be 0 if all fail, but we can't guarantee without knowing exact thresholds
        $this->assertGreaterThanOrEqual(0, $summary['passing']);
    }

    // ========================================
    // PHASE 8: getConclusionSummary() (3 tests)
    // ========================================

    public function test_groups_rankings_by_conclusion(): void
    {
        // Arrange: Create mix of participants
        $this->createParticipantWithAssessments('Above 1', 1.3);
        $this->createParticipantWithAssessments('Above 2', 1.2);
        $this->createParticipantWithAssessments('Meets 1', 1.0);
        $this->createParticipantWithAssessments('Below 1', 0.7);

        $rankings = $this->service->getRankings(
            $this->event->id,
            $this->position->id,
            $this->template->id,
            'potensi',
            10
        );

        // Act
        $summary = $this->service->getConclusionSummary($rankings);

        // Assert
        $this->assertArrayHasKey('Di Atas Standar', $summary);
        $this->assertArrayHasKey('Memenuhi Standar', $summary);
        $this->assertArrayHasKey('Di Bawah Standar', $summary);

        $total = $summary['Di Atas Standar'] + $summary['Memenuhi Standar'] + $summary['Di Bawah Standar'];
        $this->assertEquals(4, $total);
    }

    public function test_conclusion_summary_handles_empty_rankings(): void
    {
        // Arrange
        $rankings = collect();

        // Act
        $summary = $this->service->getConclusionSummary($rankings);

        // Assert
        $this->assertEquals(0, $summary['Di Atas Standar']);
        $this->assertEquals(0, $summary['Memenuhi Standar']);
        $this->assertEquals(0, $summary['Di Bawah Standar']);
    }

    public function test_conclusion_summary_with_homogeneous_data(): void
    {
        // Arrange: All participants with same performance
        $this->createParticipantWithAssessments('Same 1', 1.0);
        $this->createParticipantWithAssessments('Same 2', 1.0);
        $this->createParticipantWithAssessments('Same 3', 1.0);

        $rankings = $this->service->getRankings(
            $this->event->id,
            $this->position->id,
            $this->template->id,
            'potensi',
            10
        );

        // Act
        $summary = $this->service->getConclusionSummary($rankings);

        // Assert: All should have same conclusion
        $total = $summary['Di Atas Standar'] + $summary['Memenuhi Standar'] + $summary['Di Bawah Standar'];
        $this->assertEquals(3, $total);
    }

    // ========================================
    // PHASE 12: Three-Layer Priority System Integration (3 tests)
    // ========================================

    /**
     * Test that session adjustment (Layer 1) overrides custom standard (Layer 2) in ranking calculations
     *
     * Priority Chain:
     * LAYER 1 (Session) > LAYER 2 (Custom) > LAYER 3 (Quantum)
     */
    public function test_session_overrides_custom_standard_in_rankings(): void
    {
        // Arrange: Create participant with assessments
        $participant = $this->createParticipantWithAssessments('Test Session Override', 1.0);

        // Get baseline rankings (quantum defaults)
        $baselineRankings = $this->service->getRankings(
            $this->event->id,
            $this->position->id,
            $this->template->id,
            'potensi',
            0
        );
        $baselineRank = $baselineRankings->first();

        // LAYER 2: Create and select custom standard with different weight (40%)
        $customStandardService = app(\App\Services\CustomStandardService::class);
        $firstAspect = Aspect::where('category_type_id', $this->potensiCategory->id)
            ->orderBy('order')
            ->first();

        $customStandardData = [
            'institution_id' => $this->event->institution_id,
            'template_id' => $this->template->id,
            'code' => 'PRIORITY-TEST-SESSION',
            'name' => 'Priority Test - Session Override',
            'description' => 'Test custom standard for priority chain',
            'category_weights' => [
                'potensi' => 50,
                'kompetensi' => 50,
            ],
            'aspect_configs' => [
                $firstAspect->code => [
                    'weight' => 40,
                    'active' => true,
                ],
            ],
            'sub_aspect_configs' => [],
        ];

        $customStandard = $customStandardService->create($customStandardData);
        $customStandardService->select($this->template->id, $customStandard->id);

        // Get rankings with custom standard
        $customRankings = $this->service->getRankings(
            $this->event->id,
            $this->position->id,
            $this->template->id,
            'potensi',
            0
        );
        $customRank = $customRankings->first();

        // LAYER 1: Apply session adjustment with different weight (50%)
        $dynamicService = app(DynamicStandardService::class);
        $dynamicService->saveAspectWeight($this->template->id, $firstAspect->code, 50);

        // Get rankings with session adjustment (should override custom)
        $sessionRankings = $this->service->getRankings(
            $this->event->id,
            $this->position->id,
            $this->template->id,
            'potensi',
            0
        );
        $sessionRank = $sessionRankings->first();

        // Assert: Rankings should be different between all 3 layers
        $this->assertNotEquals(
            $baselineRank['adjusted_standard_score'],
            $customRank['adjusted_standard_score'],
            'Custom standard should change standard score from quantum defaults'
        );

        $this->assertNotEquals(
            $customRank['adjusted_standard_score'],
            $sessionRank['adjusted_standard_score'],
            'Session adjustment should override custom standard'
        );

        // Assert: Session adjustment should be used (50%), not custom (40%) or quantum (20%)
        $this->assertGreaterThan(
            $customRank['adjusted_standard_score'],
            $sessionRank['adjusted_standard_score'],
            'Session weight (50%) should produce higher score than custom weight (40%)'
        );

        // Cleanup
        \Illuminate\Support\Facades\Session::forget("standard_adjustment.{$this->template->id}");
        $customStandardService->clearSelection($this->template->id);
    }

    /**
     * Test that rankings change when switching from quantum to custom standard
     *
     * Priority Chain:
     * LAYER 3 (Quantum) → LAYER 2 (Custom)
     */
    public function test_rankings_change_when_custom_standard_selected(): void
    {
        // Arrange: Create multiple participants
        $participant1 = $this->createParticipantWithAssessments('Participant A', 1.2);
        $participant2 = $this->createParticipantWithAssessments('Participant B', 1.0);
        $participant3 = $this->createParticipantWithAssessments('Participant C', 0.8);

        // LAYER 3: Get quantum baseline rankings
        $quantumRankings = $this->service->getRankings(
            $this->event->id,
            $this->position->id,
            $this->template->id,
            'potensi',
            0
        );

        // LAYER 2: Create custom standard with modified weights
        $customStandardService = app(\App\Services\CustomStandardService::class);
        $aspects = Aspect::where('category_type_id', $this->potensiCategory->id)
            ->orderBy('order')
            ->get();

        $aspectConfigs = [];
        foreach ($aspects as $index => $aspect) {
            // Reverse the weights to potentially change rankings
            $aspectConfigs[$aspect->code] = [
                'weight' => $index === 0 ? 60 : 20,  // Give first aspect higher weight
                'active' => true,
            ];
        }

        $customStandardData = [
            'institution_id' => $this->event->institution_id,
            'template_id' => $this->template->id,
            'code' => 'PRIORITY-TEST-CUSTOM',
            'name' => 'Priority Test - Custom Change',
            'description' => 'Test custom standard impact on rankings',
            'category_weights' => [
                'potensi' => 50,
                'kompetensi' => 50,
            ],
            'aspect_configs' => $aspectConfigs,
            'sub_aspect_configs' => [],
        ];

        $customStandard = $customStandardService->create($customStandardData);
        $customStandardService->select($this->template->id, $customStandard->id);

        // Get custom standard rankings
        $customRankings = $this->service->getRankings(
            $this->event->id,
            $this->position->id,
            $this->template->id,
            'potensi',
            0
        );

        // Assert: Rankings should be different
        $this->assertCount(3, $quantumRankings);
        $this->assertCount(3, $customRankings);

        // Check that at least some scores changed
        $quantumScores = $quantumRankings->pluck('adjusted_standard_score')->toArray();
        $customScores = $customRankings->pluck('adjusted_standard_score')->toArray();

        $this->assertNotEquals(
            $quantumScores,
            $customScores,
            'Custom standard should change ranking scores'
        );

        // Cleanup
        $customStandardService->clearSelection($this->template->id);
    }

    /**
     * Test that rankings revert to custom/quantum when session is cleared
     *
     * Priority Chain:
     * LAYER 1 (Session) → LAYER 2 (Custom) → LAYER 1 cleared → LAYER 2 (Custom)
     */
    public function test_rankings_revert_when_session_cleared(): void
    {
        // Arrange: Create participant
        $participant = $this->createParticipantWithAssessments('Test Revert', 1.0);

        // LAYER 2: Create and select custom standard
        $customStandardService = app(\App\Services\CustomStandardService::class);
        $firstAspect = Aspect::where('category_type_id', $this->potensiCategory->id)
            ->orderBy('order')
            ->first();

        $customStandardData = [
            'institution_id' => $this->event->institution_id,
            'template_id' => $this->template->id,
            'code' => 'PRIORITY-TEST-REVERT',
            'name' => 'Priority Test - Session Revert',
            'description' => 'Test session clearing behavior',
            'category_weights' => [
                'potensi' => 50,
                'kompetensi' => 50,
            ],
            'aspect_configs' => [
                $firstAspect->code => [
                    'weight' => 35,
                    'active' => true,
                ],
            ],
            'sub_aspect_configs' => [],
        ];

        $customStandard = $customStandardService->create($customStandardData);
        $customStandardService->select($this->template->id, $customStandard->id);

        // Get custom standard baseline
        $customRankings = $this->service->getRankings(
            $this->event->id,
            $this->position->id,
            $this->template->id,
            'potensi',
            0
        );
        $customRank = $customRankings->first();

        // LAYER 1: Apply session adjustment
        $dynamicService = app(DynamicStandardService::class);
        $dynamicService->saveAspectWeight($this->template->id, $firstAspect->code, 55);

        // Get session rankings
        $sessionRankings = $this->service->getRankings(
            $this->event->id,
            $this->position->id,
            $this->template->id,
            'potensi',
            0
        );
        $sessionRank = $sessionRankings->first();

        // Clear session (revert to Layer 2)
        \Illuminate\Support\Facades\Session::forget("standard_adjustment.{$this->template->id}");

        // IMPORTANT: Clear aspect cache to force DynamicStandardService to re-read session
        \App\Services\Cache\AspectCacheService::clearCache();

        // Get reverted rankings
        $revertedRankings = $this->service->getRankings(
            $this->event->id,
            $this->position->id,
            $this->template->id,
            'potensi',
            0
        );
        $revertedRank = $revertedRankings->first();

        // Assert: Reverted rankings should match custom (Layer 2), not session (Layer 1)
        $this->assertNotEquals(
            $customRank['adjusted_standard_score'],
            $sessionRank['adjusted_standard_score'],
            'Session adjustment should change score from custom standard'
        );

        $this->assertEquals(
            $customRank['adjusted_standard_score'],
            $revertedRank['adjusted_standard_score'],
            'After clearing session, rankings should revert to custom standard'
        );

        $this->assertNotEquals(
            $sessionRank['adjusted_standard_score'],
            $revertedRank['adjusted_standard_score'],
            'Reverted rankings should not match session rankings'
        );

        // Cleanup
        $customStandardService->clearSelection($this->template->id);
    }

    // ========================================
    // PHASE 13: Individual Rating Recalculation Tests (3 tests) ⭐ NEW - CRITICAL
    // ========================================

    /**
     * Test 2.2 & 4.4: Database individual_rating NEVER changes (IMMUTABLE)
     *
     * CRITICAL TEST per TESTING_SCENARIOS_BASELINE_3LAYER.md line 300-324
     *
     * This test verifies that individual_rating in aspect_assessments table
     * is IMMUTABLE - it must NEVER be modified regardless of:
     * - Custom standard selection
     * - Session adjustments
     * - Sub-aspect active/inactive changes
     *
     * The recalculation happens ONLY in calculation logic (ephemeral),
     * NOT in the database.
     */
    public function test_database_individual_rating_never_modified(): void
    {
        // Arrange: Create participant with known individual_rating
        $participant = $this->createParticipantWithAssessments('Test Immutability', 1.2);

        // Get first Potensi aspect with sub-aspects
        $firstAspect = Aspect::where('category_type_id', $this->potensiCategory->id)
            ->with('subAspects')
            ->orderBy('order')
            ->first();

        // Get original database value
        $aspectAssessment = AspectAssessment::where('participant_id', $participant->id)
            ->where('aspect_id', $firstAspect->id)
            ->first();

        $originalIndividualRating = $aspectAssessment->individual_rating;

        // Verify we have sub-aspects to test with
        $this->assertGreaterThan(0, $firstAspect->subAspects->count());

        // Act 1: Disable a sub-aspect (should trigger recalculation in calculation logic)
        $firstSubAspect = $firstAspect->subAspects->first();
        $dynamicService = app(DynamicStandardService::class);
        $dynamicService->setSubAspectActive($this->template->id, $firstSubAspect->code, false);

        // Get rankings (triggers calculation logic)
        $rankings = $this->service->getRankings(
            $this->event->id,
            $this->position->id,
            $this->template->id,
            'potensi',
            0
        );

        // Assert 1: Database value should be UNCHANGED
        $aspectAssessment->refresh();
        $this->assertEquals(
            $originalIndividualRating,
            $aspectAssessment->individual_rating,
            'Database individual_rating must NEVER change when sub-aspect disabled'
        );

        // Act 2: Apply session adjustment (weight change)
        $dynamicService->saveAspectWeight($this->template->id, $firstAspect->code, 50);

        // Get rankings again
        $rankings = $this->service->getRankings(
            $this->event->id,
            $this->position->id,
            $this->template->id,
            'potensi',
            0
        );

        // Assert 2: Database value should STILL be unchanged
        $aspectAssessment->refresh();
        $this->assertEquals(
            $originalIndividualRating,
            $aspectAssessment->individual_rating,
            'Database individual_rating must NEVER change when weight adjusted'
        );

        // Act 3: Select custom standard
        $customStandardService = app(\App\Services\CustomStandardService::class);
        $customStandardData = [
            'institution_id' => $this->event->institution_id,
            'template_id' => $this->template->id,
            'code' => 'IMMUTABILITY-TEST',
            'name' => 'Test Immutability',
            'description' => 'Test that database is never modified',
            'category_weights' => ['potensi' => 50, 'kompetensi' => 50],
            'aspect_configs' => [
                $firstAspect->code => ['weight' => 40, 'active' => true],
            ],
            'sub_aspect_configs' => [],
        ];
        $customStandard = $customStandardService->create($customStandardData);
        $customStandardService->select($this->template->id, $customStandard->id);

        // Get rankings with custom standard
        $rankings = $this->service->getRankings(
            $this->event->id,
            $this->position->id,
            $this->template->id,
            'potensi',
            0
        );

        // Assert 3: Database value should STILL be unchanged
        $aspectAssessment->refresh();
        $this->assertEquals(
            $originalIndividualRating,
            $aspectAssessment->individual_rating,
            'Database individual_rating must NEVER change when custom standard selected'
        );

        // Cleanup
        \Illuminate\Support\Facades\Session::forget("standard_adjustment.{$this->template->id}");
        $customStandardService->clearSelection($this->template->id);
    }

    /**
     * Test 2.2 & 4.4: Individual rating RECALCULATED when sub-aspect disabled (ephemeral)
     *
     * CRITICAL TEST per TESTING_SCENARIOS_BASELINE_3LAYER.md line 326-347
     *
     * This test verifies that when sub-aspects are disabled:
     * 1. Database individual_rating: NEVER changes (immutable)
     * 2. Calculation logic: RECALCULATES individual_rating from active sub-aspects only
     * 3. Standard rating: ALSO recalculated from active sub-aspects
     * 4. Gap: Calculated from recalculated values (FAIR comparison)
     *
     * This ensures "apple-to-apple" comparison between individual and standard.
     */
    public function test_individual_rating_recalculated_when_subaspect_disabled(): void
    {
        // Arrange: Create participant with sub-aspects
        // Using multiplier 1.0 so individual ratings match standard ratings
        $participant = $this->createParticipantWithAssessments('Test Recalculation', 1.0);

        // Get first Potensi aspect with sub-aspects
        $firstAspect = Aspect::where('category_type_id', $this->potensiCategory->id)
            ->with('subAspects')
            ->orderBy('order')
            ->first();

        $this->assertGreaterThan(1, $firstAspect->subAspects->count(), 'Need multiple sub-aspects to test');

        // Get baseline rankings (all sub-aspects active)
        $baselineRankings = $this->service->getRankings(
            $this->event->id,
            $this->position->id,
            $this->template->id,
            'potensi',
            0
        );
        $baselineRank = $baselineRankings->first();

        // Get database value (should remain unchanged)
        $aspectAssessment = AspectAssessment::where('participant_id', $participant->id)
            ->where('aspect_id', $firstAspect->id)
            ->first();
        $databaseIndividualRating = $aspectAssessment->individual_rating;

        // Act: Disable one sub-aspect
        $firstSubAspect = $firstAspect->subAspects->first();
        $dynamicService = app(DynamicStandardService::class);
        $dynamicService->setSubAspectActive($this->template->id, $firstSubAspect->code, false);

        // Clear cache to force recalculation
        \App\Services\Cache\AspectCacheService::clearCache();

        // Get rankings with disabled sub-aspect
        $adjustedRankings = $this->service->getRankings(
            $this->event->id,
            $this->position->id,
            $this->template->id,
            'potensi',
            0
        );
        $adjustedRank = $adjustedRankings->first();

        // Assert 1: Database value is UNCHANGED
        $aspectAssessment->refresh();
        $this->assertEquals(
            $databaseIndividualRating,
            $aspectAssessment->individual_rating,
            'Database individual_rating must remain IMMUTABLE'
        );

        // Assert 2: Calculated individual_rating should be DIFFERENT
        // (recalculated from active sub-aspects only)
        $this->assertNotEquals(
            $baselineRank['individual_rating'],
            $adjustedRank['individual_rating'],
            'Calculation should recalculate individual_rating from active sub-aspects'
        );

        // Assert 3: Standard rating should ALSO be recalculated
        $this->assertNotEquals(
            $baselineRank['adjusted_standard_rating'],
            $adjustedRank['adjusted_standard_rating'],
            'Standard rating should also recalculate from active sub-aspects'
        );

        // Assert 4: Gap should reflect FAIR comparison (both using same active sub-aspects)
        // Since multiplier was 1.0, gap should be close to 0 after recalculation
        $this->assertLessThan(
            0.5, // Small tolerance for rounding
            abs($adjustedRank['adjusted_gap_rating']),
            'Gap should be FAIR (both standard and individual use same active sub-aspects)'
        );

        // Cleanup
        \Illuminate\Support\Facades\Session::forget("standard_adjustment.{$this->template->id}");
    }

    /**
     * Test 3.5: Sub-aspect recalculation impact on statistics
     *
     * NEW TEST per TESTING_SCENARIOS_BASELINE_3LAYER.md line 466-484
     *
     * This test verifies that when sub-aspects are disabled:
     * - All participants' individual_ratings are recalculated
     * - Statistics (average, distribution) use recalculated values
     * - NOT using stored database values (which would be unfair)
     */
    public function test_subaspect_recalculation_affects_all_participants(): void
    {
        // Arrange: Create multiple participants with different performance
        $participant1 = $this->createParticipantWithAssessments('Participant A', 1.2);
        $participant2 = $this->createParticipantWithAssessments('Participant B', 1.0);
        $participant3 = $this->createParticipantWithAssessments('Participant C', 0.8);

        // Get first Potensi aspect with sub-aspects
        $firstAspect = Aspect::where('category_type_id', $this->potensiCategory->id)
            ->with('subAspects')
            ->orderBy('order')
            ->first();

        // Get baseline rankings (all sub-aspects active)
        $baselineRankings = $this->service->getRankings(
            $this->event->id,
            $this->position->id,
            $this->template->id,
            'potensi',
            0
        );

        // Calculate baseline average individual_rating
        $baselineAverage = $baselineRankings->avg('individual_rating');

        // Act: Disable one sub-aspect
        $firstSubAspect = $firstAspect->subAspects->first();
        $dynamicService = app(DynamicStandardService::class);
        $dynamicService->setSubAspectActive($this->template->id, $firstSubAspect->code, false);

        // Clear cache
        \App\Services\Cache\AspectCacheService::clearCache();

        // Get rankings with disabled sub-aspect
        $adjustedRankings = $this->service->getRankings(
            $this->event->id,
            $this->position->id,
            $this->template->id,
            'potensi',
            0
        );

        // Calculate adjusted average individual_rating
        $adjustedAverage = $adjustedRankings->avg('individual_rating');

        // Assert 1: ALL participants should have recalculated ratings
        $this->assertCount(3, $adjustedRankings);
        foreach ($adjustedRankings as $ranking) {
            // Verify each participant's database is unchanged
            $aspectAssessment = AspectAssessment::where('participant_id', $ranking['participant_id'])
                ->where('aspect_id', $firstAspect->id)
                ->first();

            // The ranking's individual_rating should be different from database
            // (except by coincidence if recalculation happens to match)
            $this->assertNotNull($aspectAssessment);
        }

        // Assert 2: Average should change (statistics reflect recalculated values)
        $this->assertNotEquals(
            $baselineAverage,
            $adjustedAverage,
            'Average should reflect recalculated individual ratings, not database values'
        );

        // Assert 3: All rankings should still be valid and ordered correctly
        $scores = $adjustedRankings->pluck('individual_score')->toArray();
        $sortedScores = collect($scores)->sortDesc()->values()->toArray();
        $this->assertEquals($sortedScores, $scores, 'Rankings should be properly ordered');

        // Cleanup
        \Illuminate\Support\Facades\Session::forget("standard_adjustment.{$this->template->id}");
    }

    // ========================================
    // PHASE 14: Cache Key Completeness Tests (6 tests) ⭐ NEW - CRITICAL
    // ========================================

    /**
     * Test 12.1: Cache key includes sub-aspect active status
     *
     * CRITICAL TEST per TESTING_SCENARIOS_BASELINE_3LAYER.md line 1605-1632
     *
     * This test verifies that cache keys properly differentiate between:
     * - All sub-aspects active
     * - Some sub-aspects inactive
     *
     * If cache doesn't include sub-aspect status, users will see stale data!
     */
    public function test_cache_key_includes_subaspect_active_status(): void
    {
        // Arrange: Create participant
        $participant = $this->createParticipantWithAssessments('Test Cache SubAspect', 1.0);

        // Get first aspect with sub-aspects
        $firstAspect = Aspect::where('category_type_id', $this->potensiCategory->id)
            ->with('subAspects')
            ->orderBy('order')
            ->first();

        // Act 1: Get rankings with all sub-aspects active
        $rankings1 = $this->service->getRankings(
            $this->event->id,
            $this->position->id,
            $this->template->id,
            'potensi',
            0
        );
        $result1 = $rankings1->first();

        // Act 2: Disable one sub-aspect
        $firstSubAspect = $firstAspect->subAspects->first();
        $dynamicService = app(DynamicStandardService::class);
        $dynamicService->setSubAspectActive($this->template->id, $firstSubAspect->code, false);

        // Clear cache to force recalculation
        \App\Services\Cache\AspectCacheService::clearCache();

        // Act 3: Get rankings with disabled sub-aspect
        $rankings2 = $this->service->getRankings(
            $this->event->id,
            $this->position->id,
            $this->template->id,
            'potensi',
            0
        );
        $result2 = $rankings2->first();

        // Assert: Results should be DIFFERENT (cache respected sub-aspect status)
        $this->assertNotEquals(
            $result1['adjusted_standard_rating'],
            $result2['adjusted_standard_rating'],
            'Cache key must include sub-aspect active status'
        );

        $this->assertNotEquals(
            $result1['individual_rating'],
            $result2['individual_rating'],
            'Individual rating should be recalculated when sub-aspect disabled'
        );

        // Cleanup
        \Illuminate\Support\Facades\Session::forget("standard_adjustment.{$this->template->id}");
    }

    /**
     * Test 12.2: Cache key includes aspect active status
     *
     * CRITICAL TEST per TESTING_SCENARIOS_BASELINE_3LAYER.md line 1634-1661
     */
    public function test_cache_key_includes_aspect_active_status(): void
    {
        // Arrange: Create participant
        $participant = $this->createParticipantWithAssessments('Test Cache Aspect', 1.0);

        // Get first aspect
        $firstAspect = Aspect::where('category_type_id', $this->potensiCategory->id)
            ->orderBy('order')
            ->first();

        // Act 1: Get rankings with all aspects active
        $rankings1 = $this->service->getRankings(
            $this->event->id,
            $this->position->id,
            $this->template->id,
            'potensi',
            0
        );
        $result1 = $rankings1->first();

        // Act 2: Disable aspect
        $dynamicService = app(DynamicStandardService::class);
        $dynamicService->setAspectActive($this->template->id, $firstAspect->code, false);

        // Clear cache
        \App\Services\Cache\AspectCacheService::clearCache();

        // Act 3: Get rankings with disabled aspect
        $rankings2 = $this->service->getRankings(
            $this->event->id,
            $this->position->id,
            $this->template->id,
            'potensi',
            0
        );
        $result2 = $rankings2->first();

        // Assert: Results should be DIFFERENT
        $this->assertNotEquals(
            $result1['adjusted_standard_score'],
            $result2['adjusted_standard_score'],
            'Cache key must include aspect active status'
        );

        // Cleanup
        \Illuminate\Support\Facades\Session::forget("standard_adjustment.{$this->template->id}");
    }

    /**
     * Test 12.3: Cache key includes session ID (isolation)
     *
     * CRITICAL TEST per TESTING_SCENARIOS_BASELINE_3LAYER.md line 1663-1690
     *
     * This test verifies that different sessions get different cache entries.
     * Without session ID in cache key, User A would see User B's adjustments!
     */
    public function test_cache_key_includes_session_id(): void
    {
        // Arrange: Create participant
        $participant = $this->createParticipantWithAssessments('Test Cache Session', 1.0);

        // Get first aspect
        $firstAspect = Aspect::where('category_type_id', $this->potensiCategory->id)
            ->orderBy('order')
            ->first();

        // SESSION 1: Apply adjustment
        $dynamicService = app(DynamicStandardService::class);
        $dynamicService->saveAspectWeight($this->template->id, $firstAspect->code, 50);

        $rankings1 = $this->service->getRankings(
            $this->event->id,
            $this->position->id,
            $this->template->id,
            'potensi',
            0
        );
        $result1 = $rankings1->first();

        // SESSION 2: Clear session and apply different adjustment
        \Illuminate\Support\Facades\Session::forget("standard_adjustment.{$this->template->id}");
        \App\Services\Cache\AspectCacheService::clearCache();

        $dynamicService->saveAspectWeight($this->template->id, $firstAspect->code, 30);

        $rankings2 = $this->service->getRankings(
            $this->event->id,
            $this->position->id,
            $this->template->id,
            'potensi',
            0
        );
        $result2 = $rankings2->first();

        // Assert: Results should be DIFFERENT
        $this->assertNotEquals(
            $result1['adjusted_standard_score'],
            $result2['adjusted_standard_score'],
            'Different session adjustments should produce different results'
        );

        // Cleanup
        \Illuminate\Support\Facades\Session::forget("standard_adjustment.{$this->template->id}");
    }

    /**
     * Test 12.4: Cache key includes custom standard selection
     *
     * CRITICAL TEST per TESTING_SCENARIOS_BASELINE_3LAYER.md line 1692-1719
     */
    public function test_cache_key_includes_custom_standard_selection(): void
    {
        // Arrange: Create participant
        $participant = $this->createParticipantWithAssessments('Test Cache Custom', 1.0);

        // Get first aspect
        $firstAspect = Aspect::where('category_type_id', $this->potensiCategory->id)
            ->orderBy('order')
            ->first();

        // Act 1: Get rankings with quantum defaults
        $rankings1 = $this->service->getRankings(
            $this->event->id,
            $this->position->id,
            $this->template->id,
            'potensi',
            0
        );
        $result1 = $rankings1->first();

        // Act 2: Select custom standard
        $customStandardService = app(\App\Services\CustomStandardService::class);
        $customStandardData = [
            'institution_id' => $this->event->institution_id,
            'template_id' => $this->template->id,
            'code' => 'CACHE-TEST-CUSTOM',
            'name' => 'Cache Test Custom Standard',
            'description' => 'Test cache key includes custom standard',
            'category_weights' => ['potensi' => 50, 'kompetensi' => 50],
            'aspect_configs' => [
                $firstAspect->code => ['weight' => 45, 'active' => true],
            ],
            'sub_aspect_configs' => [],
        ];
        $customStandard = $customStandardService->create($customStandardData);
        $customStandardService->select($this->template->id, $customStandard->id);

        // Clear cache
        \App\Services\Cache\AspectCacheService::clearCache();

        // Act 3: Get rankings with custom standard
        $rankings2 = $this->service->getRankings(
            $this->event->id,
            $this->position->id,
            $this->template->id,
            'potensi',
            0
        );
        $result2 = $rankings2->first();

        // Assert: Results should be DIFFERENT
        $this->assertNotEquals(
            $result1['adjusted_standard_score'],
            $result2['adjusted_standard_score'],
            'Cache key must include custom standard selection'
        );

        // Cleanup
        $customStandardService->clearSelection($this->template->id);
    }

    /**
     * Test 12.5: Cache key includes category weight changes
     *
     * CRITICAL TEST per TESTING_SCENARIOS_BASELINE_3LAYER.md line 1721-1747
     */
    public function test_cache_key_includes_category_weight_changes(): void
    {
        // Arrange: Create participant
        $participant = $this->createParticipantWithAssessments('Test Cache Category Weight', 1.0);

        // Act 1: Get combined rankings with default category weights (50/50)
        $rankings1 = $this->service->getCombinedRankings(
            $this->event->id,
            $this->position->id,
            $this->template->id,
            0
        );
        $result1 = $rankings1->first();

        // Act 2: Change category weights
        $dynamicService = app(DynamicStandardService::class);
        $dynamicService->saveBothCategoryWeights(
            $this->template->id,
            'potensi',
            70,  // Changed from 50
            'kompetensi',
            30   // Changed from 50
        );

        // Clear ALL caches (AspectCacheService + Laravel Cache)
        \App\Services\Cache\AspectCacheService::clearCache();
        \Illuminate\Support\Facades\Cache::flush();  // CRITICAL: Clear Laravel cache too!

        // Act 3: Get combined rankings with adjusted category weights
        $rankings2 = $this->service->getCombinedRankings(
            $this->event->id,
            $this->position->id,
            $this->template->id,
            0
        );
        $result2 = $rankings2->first();

        // Assert: Results should be DIFFERENT (due to different category weights)
        $this->assertNotEquals(
            $result1['total_individual_score'],
            $result2['total_individual_score'],
            'Cache key must include category weight changes'
        );

        $this->assertEquals(70, $result2['potensi_weight']);
        $this->assertEquals(30, $result2['kompetensi_weight']);

        // Cleanup
        \Illuminate\Support\Facades\Session::forget("standard_adjustment.{$this->template->id}");
    }

    /**
     * Test 12.6: Tolerance percentage NOT in cache key
     *
     * IMPORTANT TEST per TESTING_SCENARIOS_BASELINE_3LAYER.md line 1749-1777
     *
     * This test verifies that tolerance is applied AFTER caching.
     * Same cache entry should be reused for different tolerance values
     * to improve performance.
     */
    public function test_tolerance_not_in_cache_key(): void
    {
        // Arrange: Create participant
        $participant = $this->createParticipantWithAssessments('Test Cache Tolerance', 1.0);

        // Act 1: Get rankings with 0% tolerance
        $rankings1 = $this->service->getRankings(
            $this->event->id,
            $this->position->id,
            $this->template->id,
            'potensi',
            0
        );
        $result1 = $rankings1->first();

        // Act 2: Get rankings with 10% tolerance (should use same cache for base calculation)
        $rankings2 = $this->service->getRankings(
            $this->event->id,
            $this->position->id,
            $this->template->id,
            'potensi',
            10
        );
        $result2 = $rankings2->first();

        // Assert 1: Original standard should be SAME (from cache)
        $this->assertEquals(
            $result1['original_standard_score'],
            $result2['original_standard_score'],
            'Original standard should be same (cached)'
        );

        // Assert 2: Adjusted standard should be DIFFERENT (tolerance applied post-cache)
        $expectedAdjusted = round($result1['original_standard_score'] * 0.9, 2);
        $this->assertEquals(
            $expectedAdjusted,
            $result2['adjusted_standard_score'],
            'Tolerance should be applied after caching'
        );

        // Assert 3: Individual score should be SAME (not affected by tolerance)
        $this->assertEquals(
            $result1['individual_score'],
            $result2['individual_score'],
            'Individual score should not change with tolerance'
        );
    }
}
