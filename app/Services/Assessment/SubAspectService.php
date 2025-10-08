<?php

declare(strict_types=1);

namespace App\Services\Assessment;

use App\Models\AspectAssessment;
use App\Models\SubAspect;
use App\Models\SubAspectAssessment;

class SubAspectService
{
    /**
     * Store sub-aspect assessment (raw data from API)
     *
     * @param  AspectAssessment  $aspectAssessment  Parent aspect assessment
     * @param  string  $subAspectCode  Sub-aspect code from API
     * @param  int  $individualRating  Raw rating from API (INTEGER 1-5)
     */
    public function storeSubAspectAssessment(
        AspectAssessment $aspectAssessment,
        string $subAspectCode,
        int $individualRating
    ): SubAspectAssessment {
        // 1. Find sub-aspect from master
        $subAspect = SubAspect::where('aspect_id', $aspectAssessment->aspect_id)
            ->where('code', $subAspectCode)
            ->firstOrFail();

        // 2. Snapshot standard_rating from master (for historical integrity)
        $standardRating = $subAspect->standard_rating;

        // 3. Determine rating label based on individual_rating
        $ratingLabel = $this->getRatingLabel($individualRating);

        // 4. Create or update sub-aspect assessment
        return SubAspectAssessment::updateOrCreate(
            [
                'aspect_assessment_id' => $aspectAssessment->id,
                'sub_aspect_id' => $subAspect->id,
            ],
            [
                'participant_id' => $aspectAssessment->participant_id,
                'event_id' => $aspectAssessment->event_id,
                'standard_rating' => $standardRating,
                'individual_rating' => $individualRating,
                'rating_label' => $ratingLabel,
            ]
        );
    }

    /**
     * Store multiple sub-aspect assessments for an aspect
     *
     * @param  array  $subAspectsData  Array of ['sub_aspect_code' => 'code', 'individual_rating' => 3]
     */
    public function storeMultipleSubAspects(
        AspectAssessment $aspectAssessment,
        array $subAspectsData
    ): void {
        foreach ($subAspectsData as $subAspectData) {
            $this->storeSubAspectAssessment(
                $aspectAssessment,
                $subAspectData['sub_aspect_code'],
                $subAspectData['individual_rating']
            );
        }
    }

    /**
     * Get rating label based on individual rating
     */
    private function getRatingLabel(int $rating): string
    {
        return match ($rating) {
            1 => 'Sangat Kurang',
            2 => 'Kurang',
            3 => 'Cukup',
            4 => 'Baik',
            5 => 'Sangat Baik',
            default => 'Cukup',
        };
    }
}
