<?php

declare(strict_types=1);

namespace App\Services\Assessment;

use App\Models\AssessmentEvent;
use App\Models\Participant;
use Illuminate\Support\Facades\DB;

class AssessmentCalculationService
{
    public function __construct(
        private readonly SubAspectService $subAspectService,
        private readonly AspectService $aspectService,
        private readonly CategoryService $categoryService,
        private readonly FinalAssessmentService $finalAssessmentService,
    ) {}

    /**
     * Calculate all assessments for a single participant
     *
     * Data structure from API:
     * [
     *     'potensi' => [ ... aspects ... ],
     *     'kompetensi' => [ ... aspects ... ],
     *     // or any other category codes
     * ]
     *
     * @param  array  $assessmentsData  Data from API grouped by category code
     */
    public function calculateParticipant(Participant $participant, array $assessmentsData): void
    {
        DB::transaction(function () use ($participant, $assessmentsData) {
            // ✅ UNIFIED: Process all categories (works for any category code)
            foreach ($assessmentsData as $categoryCode => $categoryData) {
                $this->processCategory($participant, $categoryCode, $categoryData);
            }

            // Calculate Final Assessment (weighted combination of all categories)
            $this->finalAssessmentService->calculateFinal($participant);
        });
    }

    /**
     * Calculate assessments for entire event (all participants)
     */
    public function calculateEvent(AssessmentEvent $event): void
    {
        $participants = Participant::where('event_id', $event->id)->get();

        foreach ($participants as $participant) {
            // Note: This method assumes assessments data is already stored
            // and only recalculates derived values
            $this->recalculateParticipant($participant);
        }
    }

    /**
     * Recalculate existing assessments (no new data from API)
     *
     * This recalculates all aspects and categories for a participant
     * based on existing data (useful when standards change)
     */
    public function recalculateParticipant(Participant $participant): void
    {
        DB::transaction(function () use ($participant) {
            // Get all category assessments for this participant
            $categoryAssessments = $participant->categoryAssessments()
                ->with('aspectAssessments')
                ->get();

            // ✅ UNIFIED: Recalculate all categories dynamically
            foreach ($categoryAssessments as $categoryAssessment) {
                // Recalculate each aspect in this category
                foreach ($categoryAssessment->aspectAssessments as $aspectAssessment) {
                    $this->aspectService->calculateAspect($aspectAssessment);
                }

                // Recalculate category totals
                $this->categoryService->calculateCategory($categoryAssessment);
            }

            // Recalculate Final Assessment
            $this->finalAssessmentService->calculateFinal($participant);
        });
    }

    /**
     * Process category assessments (UNIFIED for all category types)
     *
     * Logic (DATA-DRIVEN):
     * - Create CategoryAssessment
     * - For each aspect in category data:
     *   - Create AspectAssessment
     *   - If aspect has sub-aspects in data: process them
     *   - Calculate aspect from service
     * - Calculate category totals
     *
     * @param  string  $categoryCode  Category code (e.g., 'potensi', 'kompetensi', 'integritas')
     * @param  array  $categoryData  Array of aspects with individual_rating (and optionally sub_aspects)
     */
    private function processCategory(Participant $participant, string $categoryCode, array $categoryData): void
    {
        // 1. Create category assessment
        $categoryAssessment = $this->categoryService->createCategoryAssessment($participant, $categoryCode);

        foreach ($categoryData as $aspectData) {
            // 2. Create aspect assessment
            $aspectAssessment = $this->aspectService->createAspectAssessment(
                $categoryAssessment,
                $aspectData['aspect_code']
            );

            // DATA-DRIVEN: Check if aspect has sub-aspects in the data
            if (isset($aspectData['sub_aspects']) && ! empty($aspectData['sub_aspects'])) {
                // Process sub-aspects (store raw data from API)
                $this->subAspectService->storeMultipleSubAspects(
                    $aspectAssessment,
                    $aspectData['sub_aspects']
                );
            }

            // 3. Calculate aspect (service will determine if from sub-aspects or direct)
            // Pass individual_rating if exists (for aspects without sub-aspects)
            $individualRating = $aspectData['individual_rating'] ?? null;
            $this->aspectService->calculateAspect($aspectAssessment, $individualRating);
        }

        // 4. Calculate category total (SUM from aspects)
        $this->categoryService->calculateCategory($categoryAssessment);
    }
}
