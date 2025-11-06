<?php

declare(strict_types=1);

namespace App\Services\Assessment;

use App\Models\Aspect;
use App\Models\AspectAssessment;
use App\Models\CategoryAssessment;
use App\Models\CategoryType;
use App\Models\Participant;
use App\Services\DynamicStandardService;

class CategoryService
{
    public function __construct(
        private DynamicStandardService $dynamicStandardService
    ) {}

    /**
     * Calculate category assessment from aspects
     * PHASE 2E: Now integrated with DynamicStandardService - only sum ACTIVE aspects
     */
    public function calculateCategory(CategoryAssessment $categoryAssessment): void
    {
        // 1. Get all aspect assessments for this category
        $aspectAssessments = AspectAssessment::with('aspect')->where(
            'category_assessment_id',
            $categoryAssessment->id
        )->get();

        if ($aspectAssessments->isEmpty()) {
            return;
        }

        // Get template ID from first aspect
        $firstAspect = $aspectAssessments->first()->aspect;
        $templateId = $firstAspect->template_id;

        // PHASE 2E: Filter only ACTIVE aspects based on session
        $activeAspectAssessments = $aspectAssessments->filter(function ($aspectAssessment) use ($templateId) {
            return $this->dynamicStandardService->isAspectActive($templateId, $aspectAssessment->aspect->code);
        });

        if ($activeAspectAssessments->isEmpty()) {
            return;
        }

        // 2. Aggregate only ACTIVE aspects (SUM)
        $totalStandardRating = $activeAspectAssessments->sum('standard_rating');
        $totalStandardScore = $activeAspectAssessments->sum('standard_score');
        $totalIndividualRating = $activeAspectAssessments->sum('individual_rating');
        $totalIndividualScore = $activeAspectAssessments->sum('individual_score');

        // 3. Calculate gaps
        $gapRating = $totalIndividualRating - $totalStandardRating;
        $gapScore = $totalIndividualScore - $totalStandardScore;

        // 4. Determine conclusion based on gap score
        $conclusionCode = $this->determineCategoryConclusion($gapScore);
        $conclusionText = $this->getCategoryConclusionText($conclusionCode);

        // 5. Update category assessment
        $categoryAssessment->update([
            'total_standard_rating' => round($totalStandardRating, 2),
            'total_standard_score' => round($totalStandardScore, 2),
            'total_individual_rating' => round($totalIndividualRating, 2),
            'total_individual_score' => round($totalIndividualScore, 2),
            'gap_rating' => round($gapRating, 2),
            'gap_score' => round($gapScore, 2),
            'conclusion_code' => $conclusionCode,
            'conclusion_text' => $conclusionText,
        ]);
    }

    /**
     * Create category assessment record (without calculation)
     */
    public function createCategoryAssessment(
        Participant $participant,
        string $categoryCode
    ): CategoryAssessment {
        // 1. Find category type from master (template is from position, not event!)
        $participant->loadMissing('positionFormation.template');
        $categoryType = CategoryType::where('template_id', $participant->positionFormation->template_id)
            ->where('code', $categoryCode)
            ->firstOrFail();

        // 2. Create category assessment (values will be calculated later)
        return CategoryAssessment::updateOrCreate(
            [
                'participant_id' => $participant->id,
                'category_type_id' => $categoryType->id,
            ],
            [
                'event_id' => $participant->event_id,
                'batch_id' => $participant->batch_id,
                'position_formation_id' => $participant->position_formation_id,
                // Default values (will be updated by calculateCategory)
                'total_standard_rating' => 0,
                'total_standard_score' => 0,
                'total_individual_rating' => 0,
                'total_individual_score' => 0,
                'gap_rating' => 0,
                'gap_score' => 0,
                'conclusion_code' => 'MS',
                'conclusion_text' => 'MEMENUHI STANDARD',
            ]
        );
    }

    /**
     * Determine category conclusion based on gap score
     */
    private function determineCategoryConclusion(float $gapScore): string
    {
        if ($gapScore < -10) {
            return 'DBS'; // Di Bawah Standard
        } elseif ($gapScore < 0) {
            return 'MS'; // Memenuhi Standard
        } elseif ($gapScore < 20) {
            return 'K'; // Kompeten
        } else {
            return 'SK'; // Sangat Kompeten
        }
    }

    /**
     * Get category conclusion text from code
     */
    private function getCategoryConclusionText(string $code): string
    {
        return match ($code) {
            'DBS' => 'DI BAWAH STANDARD',
            'MS' => 'MEMENUHI STANDARD',
            'K' => 'KOMPETEN',
            'SK' => 'SANGAT KOMPETEN',
            default => 'MEMENUHI STANDARD',
        };
    }
}
