<?php

declare(strict_types=1);

namespace Tests\Integration\Services;

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
use App\Models\SubAspect;
use App\Models\SubAspectAssessment;
use App\Services\DynamicStandardService;
use App\Services\IndividualAssessmentService;
use App\Services\RankingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Cross-Service Consistency Integration Tests
 *
 * CRITICAL TESTS per TESTING_SCENARIOS_BASELINE_3LAYER.md Scenario Group 7
 *
 * These tests verify that ALL services produce CONSISTENT results for the same data:
 * - RankingService
 * - IndividualAssessmentService
 * - StatisticService (future)
 *
 * WHY THIS MATTERS:
 * If different services calculate different values for the same participant,
 * users will see inconsistent data across different pages, causing confusion
 * and loss of trust in the system.
 *
 * @see docs/TESTING_SCENARIOS_BASELINE_3LAYER.md (line 872-948)
 */
class CrossServiceConsistencyTest extends TestCase
{
    use RefreshDatabase;

    protected RankingService $rankingService;

    protected IndividualAssessmentService $individualService;

    protected AssessmentEvent $event;

    protected PositionFormation $position;

    protected AssessmentTemplate $template;

    protected CategoryType $potensiCategory;

    protected CategoryType $kompetensiCategory;

    protected Batch $batch;

    protected function setUp(): void
    {
        parent::setUp();

        // Create complete template structure
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

        // Instantiate services
        $this->rankingService = app(RankingService::class);
        $this->individualService = app(IndividualAssessmentService::class);

        // Clear cache and session
        session()->flush();
        \App\Services\Cache\AspectCacheService::clearCache();
    }

    /**
     * Create a complete assessment template with categories, aspects, and sub-aspects
     */
    private function createCompleteTemplate(): AssessmentTemplate
    {
        $template = AssessmentTemplate::factory()->create([
            'code' => 'test_consistency_'.uniqid(),
            'name' => 'Test Consistency Template',
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
                'standard_rating' => null,
                'order' => $aspectData['order'],
            ]);

            // Create 3 sub-aspects for each Potensi aspect
            for ($i = 1; $i <= 3; $i++) {
                SubAspect::factory()->create([
                    'aspect_id' => $aspect->id,
                    'code' => $aspectData['code'].'_sub_'.$i,
                    'name' => $aspectData['name'].' Sub '.$i,
                    'standard_rating' => 3,
                    'order' => $i,
                ]);
            }
        }

        // Create Kompetensi aspects WITHOUT sub-aspects (3 aspects)
        $kompetensiAspects = [
            ['code' => 'integritas', 'name' => 'Integritas', 'weight' => 20, 'rating' => 3.0, 'order' => 1],
            ['code' => 'kerjasama', 'name' => 'Kerjasama', 'weight' => 15, 'rating' => 3.0, 'order' => 2],
            ['code' => 'komunikasi', 'name' => 'Komunikasi', 'weight' => 15, 'rating' => 3.0, 'order' => 3],
        ];

        foreach ($kompetensiAspects as $aspectData) {
            Aspect::factory()->create([
                'template_id' => $template->id,
                'category_type_id' => $kompetensiCategory->id,
                'code' => $aspectData['code'],
                'name' => $aspectData['name'],
                'weight_percentage' => $aspectData['weight'],
                'standard_rating' => $aspectData['rating'],
                'order' => $aspectData['order'],
            ]);
        }

        return $template;
    }

    /**
     * Create a participant with complete assessment data
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
     */
    private function createAspectAssessments(
        Participant $participant,
        string $categoryCode,
        float $performanceMultiplier
    ): void {
        $categoryId = $categoryCode === 'potensi'
            ? $this->potensiCategory->id
            : $this->kompetensiCategory->id;

        // Create CategoryAssessment first
        $categoryAssessment = CategoryAssessment::factory()->create([
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
            // Calculate standard rating
            $standardRating = $this->getAspectStandardRating($aspect);

            // Calculate individual rating
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

            // Create sub-aspect assessments if aspect has sub-aspects
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
     * Get aspect standard rating based on structure
     */
    private function getAspectStandardRating(Aspect $aspect): float
    {
        if ($aspect->subAspects->isNotEmpty()) {
            return round($aspect->subAspects->avg('standard_rating'), 2);
        }

        return (float) $aspect->standard_rating;
    }

    // ========================================
    // TEST 7.1: Same Input → Same Output (CRITICAL)
    // ========================================

    /**
     * Test 7.1: RankingService and IndividualAssessmentService return SAME values
     *
     * CRITICAL TEST per TESTING_SCENARIOS_BASELINE_3LAYER.md line 872-907
     *
     * This test verifies that for the SAME participant:
     * - RankingService.getRankings()
     * - IndividualAssessmentService.getCategoryAssessment()
     *
     * Must return IDENTICAL values for:
     * - individual_rating (recalculated if sub-aspects inactive)
     * - individual_score
     * - standard_rating (recalculated if sub-aspects inactive)
     * - standard_score
     * - gap_rating
     * - gap_score
     * - conclusion
     */
    public function test_ranking_and_individual_service_return_same_values(): void
    {
        // Arrange: Create participant
        $participant = $this->createParticipantWithAssessments('Test Consistency', 1.2);

        // Act 1: Get data from RankingService
        $rankings = $this->rankingService->getRankings(
            $this->event->id,
            $this->position->id,
            $this->template->id,
            'potensi',
            0
        );
        $rankingResult = $rankings->first();

        // Act 2: Get data from IndividualAssessmentService
        $individualResult = $this->individualService->getCategoryAssessment(
            $participant->id,
            'potensi',  // Use category code, not ID
            0
        );

        // Assert: Both services should return IDENTICAL values
        $this->assertEquals(
            $rankingResult['individual_rating'],
            $individualResult['individual_rating'],
            'RankingService and IndividualAssessmentService must return same individual_rating'
        );

        $this->assertEquals(
            $rankingResult['individual_score'],
            $individualResult['individual_score'],
            'RankingService and IndividualAssessmentService must return same individual_score'
        );

        $this->assertEquals(
            $rankingResult['adjusted_standard_rating'],
            $individualResult['standard_rating'],
            'RankingService and IndividualAssessmentService must return same standard_rating'
        );

        $this->assertEquals(
            $rankingResult['adjusted_standard_score'],
            $individualResult['standard_score'],
            'RankingService and IndividualAssessmentService must return same standard_score'
        );

        $this->assertEquals(
            $rankingResult['adjusted_gap_rating'],
            $individualResult['gap_rating'],
            'RankingService and IndividualAssessmentService must return same gap_rating'
        );

        $this->assertEquals(
            $rankingResult['adjusted_gap_score'],
            $individualResult['gap_score'],
            'RankingService and IndividualAssessmentService must return same gap_score'
        );

        $this->assertEquals(
            $rankingResult['conclusion'],
            $individualResult['conclusion'],
            'RankingService and IndividualAssessmentService must return same conclusion'
        );
    }

    /**
     * Test 7.1b: Cross-service consistency WITH sub-aspect disabled
     *
     * CRITICAL TEST - Verifies recalculation consistency across services
     *
     * When sub-aspects are disabled, BOTH services must:
     * - Recalculate individual_rating from active sub-aspects only
     * - Recalculate standard_rating from active sub-aspects only
     * - Return IDENTICAL recalculated values
     */
    public function test_consistency_with_subaspect_disabled(): void
    {
        // Arrange: Create participant
        $participant = $this->createParticipantWithAssessments('Test Recalc Consistency', 1.0);

        // Disable one sub-aspect
        $firstAspect = Aspect::where('category_type_id', $this->potensiCategory->id)
            ->with('subAspects')
            ->orderBy('order')
            ->first();

        $firstSubAspect = $firstAspect->subAspects->first();
        $dynamicService = app(DynamicStandardService::class);
        $dynamicService->setSubAspectActive($this->template->id, $firstSubAspect->code, false);

        // Clear cache
        \App\Services\Cache\AspectCacheService::clearCache();

        // Act 1: Get data from RankingService
        $rankings = $this->rankingService->getRankings(
            $this->event->id,
            $this->position->id,
            $this->template->id,
            'potensi',
            0
        );
        $rankingResult = $rankings->first();

        // Act 2: Get data from IndividualAssessmentService
        $individualResult = $this->individualService->getCategoryAssessment(
            $participant->id,
            'potensi',  // Use category code, not ID
            0
        );

        // Assert: Both services must return IDENTICAL RECALCULATED values
        $this->assertEquals(
            $rankingResult['individual_rating'],
            $individualResult['individual_rating'],
            'Both services must recalculate individual_rating identically'
        );

        $this->assertEquals(
            $rankingResult['adjusted_standard_rating'],
            $individualResult['standard_rating'],
            'Both services must recalculate standard_rating identically'
        );

        // Verify database is UNCHANGED
        $aspectAssessment = AspectAssessment::where('participant_id', $participant->id)
            ->where('aspect_id', $firstAspect->id)
            ->first();

        $this->assertNotEquals(
            $aspectAssessment->individual_rating,
            $rankingResult['individual_rating'],
            'Database should remain unchanged (recalculation is ephemeral)'
        );

        // Cleanup
        \Illuminate\Support\Facades\Session::forget("standard_adjustment.{$this->template->id}");
    }

    /**
     * Test 7.1c: Cross-service consistency WITH session adjustments
     *
     * Verifies that both services apply session adjustments identically
     */
    public function test_consistency_with_session_adjustments(): void
    {
        // Arrange: Create participant
        $participant = $this->createParticipantWithAssessments('Test Session Consistency', 1.0);

        // Apply session adjustment
        $firstAspect = Aspect::where('category_type_id', $this->potensiCategory->id)
            ->orderBy('order')
            ->first();

        $dynamicService = app(DynamicStandardService::class);
        $dynamicService->saveAspectWeight($this->template->id, $firstAspect->code, 50);

        // Clear cache
        \App\Services\Cache\AspectCacheService::clearCache();

        // Act 1: Get data from RankingService
        $rankings = $this->rankingService->getRankings(
            $this->event->id,
            $this->position->id,
            $this->template->id,
            'potensi',
            0
        );
        $rankingResult = $rankings->first();

        // Act 2: Get data from IndividualAssessmentService
        $individualResult = $this->individualService->getCategoryAssessment(
            $participant->id,
            'potensi',  // Use category code, not ID
            0
        );

        // Assert: Both services must apply session adjustments identically
        $this->assertEquals(
            $rankingResult['individual_score'],
            $individualResult['individual_score'],
            'Both services must apply session weight adjustments identically'
        );

        $this->assertEquals(
            $rankingResult['adjusted_standard_score'],
            $individualResult['standard_score'],
            'Both services must calculate standard_score with adjusted weight identically'
        );

        // Cleanup
        \Illuminate\Support\Facades\Session::forget("standard_adjustment.{$this->template->id}");
    }

    /**
     * Test 7.1d: Cross-service consistency WITH custom standard
     *
     * Verifies that both services use custom standard identically
     */
    public function test_consistency_with_custom_standard(): void
    {
        // Arrange: Create participant
        $participant = $this->createParticipantWithAssessments('Test Custom Consistency', 1.0);

        // Select custom standard
        $customStandardService = app(\App\Services\CustomStandardService::class);
        $firstAspect = Aspect::where('category_type_id', $this->potensiCategory->id)
            ->orderBy('order')
            ->first();

        $customStandardData = [
            'institution_id' => $this->event->institution_id,
            'template_id' => $this->template->id,
            'code' => 'CONSISTENCY-TEST',
            'name' => 'Consistency Test Custom Standard',
            'description' => 'Test cross-service consistency',
            'category_weights' => ['potensi' => 50, 'kompetensi' => 50],
            'aspect_configs' => [
                $firstAspect->code => ['weight' => 35, 'active' => true],
            ],
            'sub_aspect_configs' => [],
        ];

        $customStandard = $customStandardService->create($customStandardData);
        $customStandardService->select($this->template->id, $customStandard->id);

        // Clear cache
        \App\Services\Cache\AspectCacheService::clearCache();

        // Act 1: Get data from RankingService
        $rankings = $this->rankingService->getRankings(
            $this->event->id,
            $this->position->id,
            $this->template->id,
            'potensi',
            0
        );
        $rankingResult = $rankings->first();

        // Act 2: Get data from IndividualAssessmentService
        $individualResult = $this->individualService->getCategoryAssessment(
            $participant->id,
            'potensi',  // Use category code, not ID
            0
        );

        // Assert: Both services must use custom standard identically
        $this->assertEquals(
            $rankingResult['adjusted_standard_score'],
            $individualResult['standard_score'],
            'Both services must use custom standard identically'
        );

        $this->assertEquals(
            $rankingResult['individual_score'],
            $individualResult['individual_score'],
            'Both services must calculate scores with custom standard identically'
        );

        // Cleanup
        $customStandardService->clearSelection($this->template->id);
    }
}
