<?php

declare(strict_types=1);

namespace App\Services\Assessment;

use App\Models\Aspect;
use App\Models\AspectAssessment;
use App\Models\CategoryAssessment;
use App\Models\SubAspectAssessment;
use App\Services\DynamicStandardService;

class AspectService
{
    public function __construct(
        private DynamicStandardService $dynamicStandardService
    ) {}

    /**
     * Calculate aspect assessment for Potensi (from sub-aspects)
     * PHASE 2E: Now integrated with DynamicStandardService
     */
    public function calculatePotensiAspect(AspectAssessment $aspectAssessment): void
    {
        // 1. Get aspect from master
        $aspect = Aspect::with('subAspects')->findOrFail($aspectAssessment->aspect_id);
        $templateId = $aspect->template_id;

        // 2. Get all sub-aspect assessments (filter only active sub-aspects)
        $subAssessments = SubAspectAssessment::where(
            'aspect_assessment_id',
            $aspectAssessment->id
        )->get();

        if ($subAssessments->isEmpty()) {
            return; // Skip if no sub-aspects
        }

        // PHASE 2E: Filter only ACTIVE sub-aspects based on session
        $activeSubAssessments = $subAssessments->filter(function ($subAssessment) use ($templateId) {
            $subAspect = $subAssessment->subAspect;

            return $this->dynamicStandardService->isSubAspectActive($templateId, $subAspect->code);
        });

        if ($activeSubAssessments->isEmpty()) {
            return; // Skip if no active sub-aspects
        }

        // 3. Calculate individual_rating = AVERAGE of ACTIVE sub-aspects (DECIMAL)
        $individualRating = $activeSubAssessments->avg('individual_rating');

        // 4. Get adjusted weight from DynamicStandardService
        $weight = $this->dynamicStandardService->getAspectWeight($templateId, $aspect->code);

        // 5. Calculate scores using adjusted weight
        // Score = rating × weight_percentage
        $standardScore = $aspectAssessment->standard_rating * $weight;
        $individualScore = $individualRating * $weight;

        // 6. Calculate gaps
        $gapRating = $individualRating - $aspectAssessment->standard_rating;
        $gapScore = $individualScore - $standardScore;

        // 7. Calculate percentage for spider chart (rating out of max scale 5)
        $percentageScore = (int) round(($individualRating / 5) * 100);

        // 8. Determine conclusion
        $conclusionCode = $this->determineConclusion($gapRating);
        $conclusionText = $this->getConclusionText($conclusionCode);

        // 9. Update aspect assessment
        $aspectAssessment->update([
            'individual_rating' => round($individualRating, 2),
            'standard_score' => round($standardScore, 2),
            'individual_score' => round($individualScore, 2),
            'gap_rating' => round($gapRating, 2),
            'gap_score' => round($gapScore, 2),
            'percentage_score' => $percentageScore,
            'conclusion_code' => $conclusionCode,
            'conclusion_text' => $conclusionText,
        ]);
    }

    /**
     * Calculate aspect assessment for Kompetensi (direct from API)
     * PHASE 2E: Now integrated with DynamicStandardService
     *
     * @param  int  $individualRating  From API (INTEGER 1-5)
     */
    public function calculateKompetensiAspect(
        AspectAssessment $aspectAssessment,
        int $individualRating
    ): void {
        // 1. Get aspect from master
        $aspect = Aspect::findOrFail($aspectAssessment->aspect_id);
        $templateId = $aspect->template_id;

        // 2. Get adjusted weight from DynamicStandardService
        $weight = $this->dynamicStandardService->getAspectWeight($templateId, $aspect->code);

        // 3. Calculate scores using adjusted weight
        // Score = rating × weight_percentage
        $standardScore = $aspectAssessment->standard_rating * $weight;
        $individualScore = $individualRating * $weight;

        // 4. Calculate gaps
        $gapRating = $individualRating - $aspectAssessment->standard_rating;
        $gapScore = $individualScore - $standardScore;

        // 5. Calculate percentage (rating out of max scale 5)
        $percentageScore = (int) round(($individualRating / 5) * 100);

        // 6. Determine conclusion
        $conclusionCode = $this->determineConclusion($gapRating);
        $conclusionText = $this->getConclusionText($conclusionCode);

        // 7. Update aspect assessment
        $aspectAssessment->update([
            'individual_rating' => $individualRating, // Already INTEGER from API
            'standard_score' => round($standardScore, 2),
            'individual_score' => round($individualScore, 2),
            'gap_rating' => round($gapRating, 2),
            'gap_score' => round($gapScore, 2),
            'percentage_score' => $percentageScore,
            'conclusion_code' => $conclusionCode,
            'conclusion_text' => $conclusionText,
        ]);
    }

    /**
     * Create aspect assessment record (without calculation)
     */
    public function createAspectAssessment(
        CategoryAssessment $categoryAssessment,
        string $aspectCode
    ): AspectAssessment {
        // 1. Find aspect from master
        $aspect = Aspect::where('category_type_id', $categoryAssessment->category_type_id)
            ->where('code', $aspectCode)
            ->firstOrFail();

        // 2. Determine standard_rating based on category type
        $categoryAssessment->loadMissing('categoryType');
        $categoryCode = $categoryAssessment->categoryType->code;

        if ($categoryCode === 'potensi') {
            // POTENSI: Calculate standard_rating from sub-aspects average
            // Load sub-aspects from master
            $aspect->loadMissing('subAspects');

            if ($aspect->subAspects->isNotEmpty()) {
                $standardRating = (float) $aspect->subAspects->avg('standard_rating');
            } else {
                // Fallback: use aspect standard_rating if no sub-aspects
                $standardRating = (float) $aspect->standard_rating;
            }
        } else {
            // KOMPETENSI: Use standard_rating from master (hardcoded)
            $standardRating = (float) $aspect->standard_rating;
        }

        // 3. Create aspect assessment (values will be calculated later)
        return AspectAssessment::updateOrCreate(
            [
                'category_assessment_id' => $categoryAssessment->id,
                'aspect_id' => $aspect->id,
            ],
            [
                'participant_id' => $categoryAssessment->participant_id,
                'event_id' => $categoryAssessment->event_id,
                'batch_id' => $categoryAssessment->batch_id,
                'position_formation_id' => $categoryAssessment->position_formation_id,
                'standard_rating' => round($standardRating, 2),
                // Default values (will be updated by calculate methods)
                'standard_score' => 0,
                'individual_rating' => 0,
                'individual_score' => 0,
                'gap_rating' => 0,
                'gap_score' => 0,
                'percentage_score' => 0,
                'conclusion_code' => 'meets_standard',
                'conclusion_text' => 'Memenuhi Standard',
            ]
        );
    }

    /**
     * Determine conclusion based on gap rating
     */
    private function determineConclusion(float $gapRating): string
    {
        if ($gapRating < -0.5) {
            return 'below_standard';
        } elseif ($gapRating < 0.5) {
            return 'meets_standard';
        } else {
            return 'exceeds_standard';
        }
    }

    /**
     * Get conclusion text from code
     */
    private function getConclusionText(string $code): string
    {
        return match ($code) {
            'below_standard' => 'Kurang Memenuhi Standard',
            'meets_standard' => 'Memenuhi Standard',
            'exceeds_standard' => 'Melebihi Standard',
            default => 'Memenuhi Standard',
        };
    }
}
