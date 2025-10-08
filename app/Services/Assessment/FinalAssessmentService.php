<?php

declare(strict_types=1);

namespace App\Services\Assessment;

use App\Models\CategoryAssessment;
use App\Models\CategoryType;
use App\Models\FinalAssessment;
use App\Models\Participant;

class FinalAssessmentService
{
    /**
     * Calculate final assessment from category assessments
     */
    public function calculateFinal(Participant $participant): FinalAssessment
    {
        $participant->loadMissing('event');

        // 1. Get category types from template (DYNAMIC - no hard-coding!)
        $potensiCategory = CategoryType::where('template_id', $participant->event->template_id)
            ->where('code', 'potensi')
            ->firstOrFail();

        $kompetensiCategory = CategoryType::where('template_id', $participant->event->template_id)
            ->where('code', 'kompetensi')
            ->firstOrFail();

        // 2. Get category assessments
        $potensiAssessment = CategoryAssessment::where('participant_id', $participant->id)
            ->where('category_type_id', $potensiCategory->id)
            ->firstOrFail();

        $kompetensiAssessment = CategoryAssessment::where('participant_id', $participant->id)
            ->where('category_type_id', $kompetensiCategory->id)
            ->firstOrFail();

        // 3. Get weights from template (DYNAMIC!)
        $potensiWeight = $potensiCategory->weight_percentage;
        $kompetensiWeight = $kompetensiCategory->weight_percentage;

        // 4. Calculate weighted scores
        $totalStandardScore =
            ($potensiAssessment->total_standard_score * ($potensiWeight / 100)) +
            ($kompetensiAssessment->total_standard_score * ($kompetensiWeight / 100));

        $totalIndividualScore =
            ($potensiAssessment->total_individual_score * ($potensiWeight / 100)) +
            ($kompetensiAssessment->total_individual_score * ($kompetensiWeight / 100));

        // 5. Calculate achievement percentage
        $achievementPercentage = $totalStandardScore > 0
            ? ($totalIndividualScore / $totalStandardScore) * 100
            : 0;

        // 6. Determine final conclusion
        $conclusionCode = $this->determineFinalConclusion($achievementPercentage);
        $conclusionText = $this->getFinalConclusionText($conclusionCode);

        // 7. Create or update final assessment
        return FinalAssessment::updateOrCreate(
            ['participant_id' => $participant->id],
            [
                'event_id' => $participant->event_id,
                'batch_id' => $participant->batch_id,
                'position_formation_id' => $participant->position_formation_id,
                'potensi_weight' => $potensiWeight,
                'potensi_standard_score' => round($potensiAssessment->total_standard_score, 2),
                'potensi_individual_score' => round($potensiAssessment->total_individual_score, 2),
                'kompetensi_weight' => $kompetensiWeight,
                'kompetensi_standard_score' => round($kompetensiAssessment->total_standard_score, 2),
                'kompetensi_individual_score' => round($kompetensiAssessment->total_individual_score, 2),
                'total_standard_score' => round($totalStandardScore, 2),
                'total_individual_score' => round($totalIndividualScore, 2),
                'achievement_percentage' => round($achievementPercentage, 2),
                'conclusion_code' => $conclusionCode,
                'conclusion_text' => $conclusionText,
            ]
        );
    }

    /**
     * Determine final conclusion based on achievement percentage
     */
    private function determineFinalConclusion(float $achievementPercentage): string
    {
        if ($achievementPercentage < 80) {
            return 'TMS'; // Tidak Memenuhi Syarat
        } elseif ($achievementPercentage < 90) {
            return 'MMS'; // Masih Memenuhi Syarat
        } else {
            return 'MS'; // Memenuhi Syarat
        }
    }

    /**
     * Get final conclusion text from code
     */
    private function getFinalConclusionText(string $code): string
    {
        return match ($code) {
            'TMS' => 'TIDAK MEMENUHI SYARAT (TMS)',
            'MMS' => 'MASIH MEMENUHI SYARAT (MMS)',
            'MS' => 'MEMENUHI SYARAT (MS)',
            default => 'MEMENUHI SYARAT (MS)',
        };
    }
}
