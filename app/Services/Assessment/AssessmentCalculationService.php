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
     * @param  array  $assessmentsData  Data from API: ['potensi' => [...], 'kompetensi' => [...]]
     */
    public function calculateParticipant(Participant $participant, array $assessmentsData): void
    {
        DB::transaction(function () use ($participant, $assessmentsData) {
            // 1. Process Potensi (with sub-aspects)
            if (isset($assessmentsData['potensi'])) {
                $this->processPotensi($participant, $assessmentsData['potensi']);
            }

            // 2. Process Kompetensi (direct aspect ratings)
            if (isset($assessmentsData['kompetensi'])) {
                $this->processKompetensi($participant, $assessmentsData['kompetensi']);
            }

            // 3. Calculate Final Assessment
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
     */
    public function recalculateParticipant(Participant $participant): void
    {
        DB::transaction(function () use ($participant) {
            // 1. Recalculate Potensi aspects from existing sub-aspects
            $potensiCategory = $this->categoryService->createCategoryAssessment($participant, 'potensi');
            $aspectAssessments = $potensiCategory->aspectAssessments;

            foreach ($aspectAssessments as $aspectAssessment) {
                $this->aspectService->calculatePotensiAspect($aspectAssessment);
            }

            // 2. Recalculate Potensi category
            $this->categoryService->calculateCategory($potensiCategory);

            // 3. Recalculate Kompetensi category
            $kompetensiCategory = $this->categoryService->createCategoryAssessment($participant, 'kompetensi');
            $this->categoryService->calculateCategory($kompetensiCategory);

            // 4. Recalculate Final Assessment
            $this->finalAssessmentService->calculateFinal($participant);
        });
    }

    /**
     * Process Potensi assessments (with sub-aspects)
     *
     * @param  array  $potensiData  Array of aspects with sub_aspects
     */
    private function processPotensi(Participant $participant, array $potensiData): void
    {
        // 1. Create category assessment
        $categoryAssessment = $this->categoryService->createCategoryAssessment($participant, 'potensi');

        foreach ($potensiData as $aspectData) {
            // 2. Create aspect assessment
            $aspectAssessment = $this->aspectService->createAspectAssessment(
                $categoryAssessment,
                $aspectData['aspect_code']
            );

            // 3. Store sub-aspect assessments (raw data from API)
            if (isset($aspectData['sub_aspects']) && ! empty($aspectData['sub_aspects'])) {
                $this->subAspectService->storeMultipleSubAspects(
                    $aspectAssessment,
                    $aspectData['sub_aspects']
                );
            }

            // 4. Calculate aspect rating (AVG from sub-aspects)
            $this->aspectService->calculatePotensiAspect($aspectAssessment);
        }

        // 5. Calculate category total (SUM from aspects)
        $this->categoryService->calculateCategory($categoryAssessment);
    }

    /**
     * Process Kompetensi assessments (direct aspect ratings, no sub-aspects)
     *
     * @param  array  $kompetensiData  Array of aspects with individual_rating
     */
    private function processKompetensi(Participant $participant, array $kompetensiData): void
    {
        // 1. Create category assessment
        $categoryAssessment = $this->categoryService->createCategoryAssessment($participant, 'kompetensi');

        foreach ($kompetensiData as $aspectData) {
            // 2. Create aspect assessment
            $aspectAssessment = $this->aspectService->createAspectAssessment(
                $categoryAssessment,
                $aspectData['aspect_code']
            );

            // 3. Calculate aspect (direct rating from API, no sub-aspects)
            $this->aspectService->calculateKompetensiAspect(
                $aspectAssessment,
                $aspectData['individual_rating']
            );
        }

        // 4. Calculate category total (SUM from aspects)
        $this->categoryService->calculateCategory($categoryAssessment);
    }
}
