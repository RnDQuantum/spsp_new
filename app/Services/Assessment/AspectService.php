<?php

declare(strict_types=1);

namespace App\Services\Assessment;

use App\Models\Aspect;
use App\Models\AspectAssessment;
use App\Models\CategoryAssessment;
use App\Models\SubAspectAssessment;

class AspectService
{
    /**
     * Calculate aspect assessment for Potensi (from sub-aspects)
     */
    public function calculatePotensiAspect(AspectAssessment $aspectAssessment): void
    {
        // 1. Get all sub-aspect assessments
        $subAssessments = SubAspectAssessment::where(
            'aspect_assessment_id',
            $aspectAssessment->id
        )->get();

        if ($subAssessments->isEmpty()) {
            return; // Skip if no sub-aspects
        }

        // 2. Calculate individual_rating = AVERAGE of sub-aspects (DECIMAL)
        $individualRating = $subAssessments->avg('individual_rating');

        // 3. Get aspect from master for weight
        $aspect = Aspect::findOrFail($aspectAssessment->aspect_id);

        // 4. Calculate scores
        // Score = rating × weight_percentage
        $standardScore = $aspectAssessment->standard_rating * $aspect->weight_percentage;
        $individualScore = $individualRating * $aspect->weight_percentage;

        // 5. Calculate gaps
        $gapRating = $individualRating - $aspectAssessment->standard_rating;
        $gapScore = $individualScore - $standardScore;

        // 6. Calculate percentage for spider chart (rating out of max scale 5)
        $percentageScore = (int) round(($individualRating / 5) * 100);

        // 7. Determine conclusion
        $conclusionCode = $this->determineConclusion($gapRating);
        $conclusionText = $this->getConclusionText($conclusionCode);

        // 8. Update aspect assessment
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
     *
     * @param  int  $individualRating  From API (INTEGER 1-5)
     */
    public function calculateKompetensiAspect(
        AspectAssessment $aspectAssessment,
        int $individualRating
    ): void {
        // 1. Get aspect from master for weight
        $aspect = Aspect::findOrFail($aspectAssessment->aspect_id);

        // 2. Calculate scores
        // Score = rating × weight_percentage
        $standardScore = $aspectAssessment->standard_rating * $aspect->weight_percentage;
        $individualScore = $individualRating * $aspect->weight_percentage;

        // 3. Calculate gaps
        $gapRating = $individualRating - $aspectAssessment->standard_rating;
        $gapScore = $individualScore - $standardScore;

        // 4. Calculate percentage (rating out of max scale 5)
        $percentageScore = (int) round(($individualRating / 5) * 100);

        // 5. Determine conclusion
        $conclusionCode = $this->determineConclusion($gapRating);
        $conclusionText = $this->getConclusionText($conclusionCode);

        // 6. Update aspect assessment
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

        // 2. Snapshot standard_rating from master
        $standardRating = $aspect->standard_rating;

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
                'standard_rating' => $standardRating,
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
