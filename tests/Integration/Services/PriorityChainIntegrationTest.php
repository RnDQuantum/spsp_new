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
use App\Services\CustomStandardService;
use App\Services\DynamicStandardService;
use App\Services\IndividualAssessmentService;
use App\Services\RankingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Priority Chain Integration Tests
 *
 * These tests verify that the 3-layer priority system works correctly
 * across all services from individual assessment to final ranking.
 *
 * PHASE 1: Full Priority Chain - Assessment to Ranking (1 test)
 * PHASE 2: Mixed Priority Layers in Final Assessment (1 test)
 *
 * TOTAL: 2/2 tests
 *
 * @see \App\Services\DynamicStandardService
 * @see \App\Services\IndividualAssessmentService
 * @see \App\Services\RankingService
 * @see docs/TESTING_GUIDE.md
 * @see docs/FLEXIBLE_HIERARCHY_REFACTORING.md
 */
class PriorityChainIntegrationTest extends TestCase
{
    use RefreshDatabase;

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

        // Clear cache and session
        session()->flush();
        \App\Services\Cache\AspectCacheService::clearCache();
    }

    // ========================================
    // PHASE 1: Full Priority Chain - Assessment to Ranking (1 test)
    // ========================================

    /**
     * Test complete priority chain from individual assessment to ranking
     *
     * This test verifies that the 3-layer priority system works correctly
     * across all services (DynamicStandardService, IndividualAssessmentService, RankingService)
     *
     * Flow:
     * 1. Create participant with baseline assessments (Quantum - Layer 3)
     * 2. Verify baseline individual assessment uses quantum defaults
     * 3. Verify baseline ranking uses quantum defaults
     * 4. Apply custom standard (Layer 2)
     * 5. Verify individual assessment updates to custom standard
     * 6. Verify ranking updates to custom standard
     * 7. Apply session adjustment (Layer 1)
     * 8. Verify individual assessment updates to session (overrides custom)
     * 9. Verify ranking updates to session (overrides custom)
     */
    public function test_full_priority_chain_from_assessment_to_ranking(): void
    {
        // Step 1: Create participant with baseline assessments
        $participant = $this->createParticipantWithAssessments('Test Priority Chain', 1.0);

        // Verify participant and assessments created
        $this->assertNotNull($participant);
        $assessmentCount = AspectAssessment::where('participant_id', $participant->id)->count();
        $this->assertGreaterThan(0, $assessmentCount, 'Should have aspect assessments created');

        $firstPotensiAspect = Aspect::where('category_type_id', $this->potensiCategory->id)
            ->orderBy('order')
            ->first();

        // Step 2: Verify baseline individual assessment (Quantum - Layer 3)
        $individualService = app(IndividualAssessmentService::class);
        $baselineAssessment = $individualService->getAspectAssessments(
            $participant->id,
            $this->potensiCategory->id,
            0
        )->first();

        $this->assertEquals(
            20,
            $firstPotensiAspect->weight_percentage,
            'Quantum default weight should be 20%'
        );

        // Step 3: Verify baseline ranking (Quantum - Layer 3)
        $rankingService = app(RankingService::class);
        $baselineRanking = $rankingService->getRankings(
            $this->event->id,
            $this->position->id,
            $this->template->id,
            'potensi',
            0
        )->first();

        $baselineRankingScore = $baselineRanking['adjusted_standard_score'];

        // Step 4: Apply custom standard (Layer 2) with weight 30%
        $customStandardService = app(CustomStandardService::class);
        $customStandardData = [
            'institution_id' => $this->event->institution_id,
            'template_id' => $this->template->id,
            'code' => 'INTEGRATION-CUSTOM',
            'name' => 'Integration Test Custom Standard',
            'description' => 'Custom standard for priority chain integration test',
            'category_weights' => [
                'potensi' => 50,
                'kompetensi' => 50,
            ],
            'aspect_configs' => [
                $firstPotensiAspect->code => [
                    'weight' => 30,
                    'active' => true,
                ],
            ],
            'sub_aspect_configs' => [],
        ];

        $customStandard = $customStandardService->create($customStandardData);
        $customStandardService->select($this->template->id, $customStandard->id);

        // Clear cache after custom standard selection
        \App\Services\Cache\AspectCacheService::clearCache();

        // Step 5: Verify individual assessment updates to custom (Layer 2)
        $customAssessment = $individualService->getAspectAssessments(
            $participant->id,
            $this->potensiCategory->id,
            0
        )->first();

        $this->assertNotEquals(
            $baselineAssessment['standard_score'],
            $customAssessment['standard_score'],
            'Assessment should change when custom standard applied'
        );

        // Step 6: Verify ranking updates to custom (Layer 2)
        $customRanking = $rankingService->getRankings(
            $this->event->id,
            $this->position->id,
            $this->template->id,
            'potensi',
            0
        )->first();

        $customRankingScore = $customRanking['adjusted_standard_score'];

        $this->assertNotEquals(
            $baselineRankingScore,
            $customRankingScore,
            'Ranking should change when custom standard applied'
        );

        $this->assertGreaterThan(
            $baselineRankingScore,
            $customRankingScore,
            'Custom standard (30%) should produce higher score than quantum (20%)'
        );

        // Step 7: Apply session adjustment (Layer 1) with weight 40%
        $dynamicService = app(DynamicStandardService::class);
        $dynamicService->saveAspectWeight($this->template->id, $firstPotensiAspect->code, 40);

        // Clear cache after session adjustment
        \App\Services\Cache\AspectCacheService::clearCache();

        // Step 8: Verify individual assessment updates to session (Layer 1 > Layer 2)
        $sessionAssessment = $individualService->getAspectAssessments(
            $participant->id,
            $this->potensiCategory->id,
            0
        )->first();

        $this->assertNotEquals(
            $customAssessment['standard_score'],
            $sessionAssessment['standard_score'],
            'Assessment should change when session adjustment applied'
        );

        // Step 9: Verify ranking updates to session (Layer 1 > Layer 2)
        $sessionRanking = $rankingService->getRankings(
            $this->event->id,
            $this->position->id,
            $this->template->id,
            'potensi',
            0
        )->first();

        $sessionRankingScore = $sessionRanking['adjusted_standard_score'];

        $this->assertNotEquals(
            $customRankingScore,
            $sessionRankingScore,
            'Ranking should change when session adjustment applied'
        );

        $this->assertGreaterThan(
            $customRankingScore,
            $sessionRankingScore,
            'Session adjustment (40%) should produce higher score than custom (30%)'
        );

        // Verify the complete chain: Quantum (20%) < Custom (30%) < Session (40%)
        $this->assertLessThan($customRankingScore, $baselineRankingScore);
        $this->assertLessThan($sessionRankingScore, $customRankingScore);

        // Cleanup
        \Illuminate\Support\Facades\Session::forget("standard_adjustment.{$this->template->id}");
        $customStandardService->clearSelection($this->template->id);
    }

    // ========================================
    // PHASE 2: Mixed Priority Layers in Final Assessment (1 test)
    // ========================================

    /**
     * Test mixed priority layers across different aspects in final combined ranking
     *
     * This test verifies that different aspects can use different priority layers
     * simultaneously and the final combined ranking is calculated correctly.
     *
     * Scenario:
     * - Aspect 1 (Potensi): Uses Session adjustment (Layer 1) - weight 45%
     * - Aspect 2 (Potensi): Uses Custom standard (Layer 2) - weight 35%
     * - Aspect 3 (Potensi): Uses Quantum default (Layer 3) - weight 20%
     * - Combined ranking should reflect all three layers working together
     */
    public function test_mixed_priority_layers_in_final_assessment(): void
    {
        // Create two participants for ranking comparison
        $participant1 = $this->createParticipantWithAssessments('Participant Alpha', 1.2);
        $participant2 = $this->createParticipantWithAssessments('Participant Beta', 0.8);

        // Get all Potensi aspects
        $potensiAspects = Aspect::where('category_type_id', $this->potensiCategory->id)
            ->orderBy('order')
            ->get();

        $this->assertGreaterThanOrEqual(3, $potensiAspects->count(), 'Need at least 3 aspects for mixed layer test');

        $aspect1 = $potensiAspects[0];
        $aspect2 = $potensiAspects[1];
        $aspect3 = $potensiAspects[2];

        // Setup Layer 2 (Custom Standard) - affects aspect2 (35%)
        $customStandardService = app(CustomStandardService::class);
        $customStandardData = [
            'institution_id' => $this->event->institution_id,
            'template_id' => $this->template->id,
            'code' => 'INTEGRATION-MIXED',
            'name' => 'Integration Test Mixed Layers',
            'description' => 'Custom standard for mixed priority layer test',
            'category_weights' => [
                'potensi' => 50,
                'kompetensi' => 50,
            ],
            'aspect_configs' => [
                $aspect2->code => [
                    'weight' => 35,
                    'active' => true,
                ],
            ],
            'sub_aspect_configs' => [],
        ];

        $customStandard = $customStandardService->create($customStandardData);
        $customStandardService->select($this->template->id, $customStandard->id);

        // Setup Layer 1 (Session Adjustment) - affects aspect1 (45%)
        $dynamicService = app(DynamicStandardService::class);
        $dynamicService->saveAspectWeight($this->template->id, $aspect1->code, 45);

        // Clear cache after all adjustments
        \App\Services\Cache\AspectCacheService::clearCache();

        // Aspect 3 uses quantum default (20%) - no adjustment needed

        // Get individual assessments - should reflect mixed layers
        $individualService = app(IndividualAssessmentService::class);
        $assessments = $individualService->getAspectAssessments(
            $participant1->id,
            $this->potensiCategory->id,
            0
        );

        // Verify each aspect uses correct priority layer
        $assessment1 = $assessments->firstWhere('aspect_id', $aspect1->id);
        $assessment2 = $assessments->firstWhere('aspect_id', $aspect2->id);
        $assessment3 = $assessments->firstWhere('aspect_id', $aspect3->id);

        // We can verify by checking the expected score calculation
        // score = rating * weight
        $expectedScore1 = round($assessment1['standard_rating'] * 45, 2);
        $expectedScore2 = round($assessment2['standard_rating'] * 35, 2);
        $expectedScore3 = round($assessment3['standard_rating'] * 20, 2);

        $this->assertEquals($expectedScore1, $assessment1['standard_score'], 'Aspect 1 should use session weight (45%)');
        $this->assertEquals($expectedScore2, $assessment2['standard_score'], 'Aspect 2 should use custom weight (35%)');
        $this->assertEquals($expectedScore3, $assessment3['standard_score'], 'Aspect 3 should use quantum weight (20%)');

        // Get rankings - should reflect mixed layers in final score
        $rankingService = app(RankingService::class);
        $rankings = $rankingService->getRankings(
            $this->event->id,
            $this->position->id,
            $this->template->id,
            'potensi',
            0
        );

        $this->assertCount(2, $rankings, 'Should have 2 participants in rankings');

        // Verify ranking order is correct (participant1 with 1.2 multiplier should rank higher)
        $rank1 = $rankings->firstWhere('participant_id', $participant1->id);
        $rank2 = $rankings->firstWhere('participant_id', $participant2->id);

        $this->assertEquals(1, $rank1['rank'], 'Participant 1 should rank first');
        $this->assertEquals(2, $rank2['rank'], 'Participant 2 should rank second');

        $this->assertGreaterThan(
            $rank2['individual_score'],
            $rank1['individual_score'],
            'Higher performing participant should have higher score even with mixed priority layers'
        );

        // Verify combined ranking reflects all three layers
        // The standard_score should be sum of all aspect scores (each using their respective layer)
        $totalExpectedStandardScore = $expectedScore1 + $expectedScore2 + $expectedScore3;

        // Allow small floating point difference
        $this->assertEqualsWithDelta(
            $totalExpectedStandardScore,
            $rank1['adjusted_standard_score'],
            0.5,
            'Combined ranking should reflect all three priority layers'
        );

        // Cleanup
        \Illuminate\Support\Facades\Session::forget("standard_adjustment.{$this->template->id}");
        $customStandardService->clearSelection($this->template->id);
    }

    // ========================================
    // HELPER METHODS
    // ========================================

    /**
     * Create a complete assessment template with categories, aspects, and sub-aspects
     */
    private function createCompleteTemplate(): AssessmentTemplate
    {
        $template = AssessmentTemplate::factory()->create([
            'code' => 'test_template_'.uniqid(),
            'name' => 'Integration Test Template',
        ]);

        // Create Potensi category (50%)
        $potensiCategory = CategoryType::factory()->create([
            'template_id' => $template->id,
            'code' => 'potensi',
            'name' => 'Potensi',
            'weight_percentage' => 50,
            'order' => 1,
        ]);

        // Create 3 Potensi aspects WITH sub-aspects (data-driven rating)
        for ($i = 1; $i <= 3; $i++) {
            $aspect = Aspect::factory()->create([
                'template_id' => $template->id,
                'category_type_id' => $potensiCategory->id,
                'code' => 'potensi_aspect_'.$i,
                'name' => 'Potensi Aspect '.$i,
                'weight_percentage' => 20,
                'order' => $i,
            ]);

            // Create 3 sub-aspects for each aspect
            for ($j = 1; $j <= 3; $j++) {
                SubAspect::factory()->create([
                    'aspect_id' => $aspect->id,
                    'code' => 'potensi_sub_'.$i.'_'.$j,
                    'name' => 'Potensi Sub '.$i.'.'.$j,
                    'standard_rating' => 4.0,
                    'order' => $j,
                ]);
            }
        }

        // Create Kompetensi category (50%)
        $kompetensiCategory = CategoryType::factory()->create([
            'template_id' => $template->id,
            'code' => 'kompetensi',
            'name' => 'Kompetensi',
            'weight_percentage' => 50,
            'order' => 2,
        ]);

        // Create 3 Kompetensi aspects WITHOUT sub-aspects (direct rating)
        for ($i = 1; $i <= 3; $i++) {
            Aspect::factory()->create([
                'template_id' => $template->id,
                'category_type_id' => $kompetensiCategory->id,
                'code' => 'kompetensi_aspect_'.$i,
                'name' => 'Kompetensi Aspect '.$i,
                'weight_percentage' => 20,
                'standard_rating' => 4.0,
                'order' => $i,
            ]);
        }

        return $template;
    }

    /**
     * Create a participant with complete assessment data
     *
     * @param  string  $name  Participant name
     * @param  float  $performanceMultiplier  Multiplier for individual scores (1.0 = meets standard, >1.0 = above, <1.0 = below)
     * @return Participant Created participant with assessments
     */
    private function createParticipantWithAssessments(string $name, float $performanceMultiplier): Participant
    {
        $participant = Participant::factory()->create([
            'event_id' => $this->event->id,
            'batch_id' => $this->batch->id,
            'position_formation_id' => $this->position->id,
            'name' => $name,
            'test_number' => 'TEST-'.uniqid(),
        ]);

        // Create Potensi assessments
        $this->createCategoryAssessments($participant, $this->potensiCategory, $performanceMultiplier);

        // Create Kompetensi assessments
        $this->createCategoryAssessments($participant, $this->kompetensiCategory, $performanceMultiplier);

        return $participant;
    }

    /**
     * Create assessments for a category (aspects and sub-aspects)
     */
    private function createCategoryAssessments(
        Participant $participant,
        CategoryType $category,
        float $performanceMultiplier
    ): void {
        $categoryAssessment = CategoryAssessment::factory()
            ->forParticipant($participant)
            ->forCategoryType($category)
            ->create();

        $aspects = Aspect::where('category_type_id', $category->id)
            ->with('subAspects')
            ->get();

        foreach ($aspects as $aspect) {
            // Get standard rating (use data-driven for Potensi, direct for Kompetensi)
            $standardRating = $this->getAspectStandardRating($aspect);

            // Calculate individual rating (apply performance multiplier, cap at 5)
            $individualRating = min(5.0, round($standardRating * $performanceMultiplier, 2));

            // Calculate scores based on rating * weight
            $standardScore = round($standardRating * $aspect->weight_percentage, 2);
            $individualScore = round($individualRating * $aspect->weight_percentage, 2);

            $aspectAssessment = AspectAssessment::factory()->create([
                'category_assessment_id' => $categoryAssessment->id,
                'aspect_id' => $aspect->id,
                'participant_id' => $participant->id,
                'event_id' => $participant->event_id,
                'batch_id' => $participant->batch_id,
                'position_formation_id' => $participant->position_formation_id,
                'standard_rating' => $standardRating,
                'individual_rating' => $individualRating,
                'standard_score' => $standardScore,
                'individual_score' => $individualScore,
                'gap_rating' => round($individualRating - $standardRating, 2),
                'gap_score' => round($individualScore - $standardScore, 2),
            ]);

            // Create sub-aspect assessments if aspect has sub-aspects (Potensi only)
            $subAspects = SubAspect::where('aspect_id', $aspect->id)->get();
            if ($subAspects->isNotEmpty()) {
                foreach ($subAspects as $subAspect) {
                    $subStandardRating = (int) $subAspect->standard_rating;
                    $subIndividualRating = (int) min(5, round($subStandardRating * $performanceMultiplier));

                    SubAspectAssessment::factory()->create([
                        'aspect_assessment_id' => $aspectAssessment->id,
                        'participant_id' => $aspectAssessment->participant_id,
                        'event_id' => $aspectAssessment->event_id,
                        'sub_aspect_id' => $subAspect->id,
                        'standard_rating' => $subStandardRating,
                        'individual_rating' => $subIndividualRating,
                    ]);
                }
            }
        }
    }

    /**
     * Get aspect standard rating (data-driven or direct)
     */
    private function getAspectStandardRating(Aspect $aspect): float
    {
        // If aspect has sub-aspects, calculate average
        if ($aspect->subAspects()->exists()) {
            $subAspects = SubAspect::where('aspect_id', $aspect->id)->get();
            $avgRating = $subAspects->avg('standard_rating');

            return round($avgRating, 2);
        }

        // Otherwise use aspect's direct standard_rating
        return round((float) $aspect->standard_rating, 2);
    }
}
