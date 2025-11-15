<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AspectAssessment;
use App\Models\CategoryType;
use App\Models\Participant;
use Illuminate\Support\Collection;

/**
 * IndividualAssessmentService - Single Source of Truth for Individual Assessment Calculations
 *
 * This service handles all individual participant assessment calculations:
 * - Aspect-level assessments (with tolerance, gaps, conclusions)
 * - Category-level assessments (Potensi, Kompetensi)
 * - Final assessment (combined weighted)
 *
 * Works with:
 * - DynamicStandardService (for session adjustments)
 * - ConclusionService (for conclusion categorization)
 * - RankingService (for ranking info)
 *
 * Used by:
 * - GeneralPsyMapping, GeneralMcMapping, GeneralMapping
 * - GeneralPsyMatching, GeneralMcMatching
 * - PDF/Excel exports
 */
class IndividualAssessmentService
{
    /**
     * Get aspect assessments for a participant in a specific category
     *
     * Returns detailed breakdown of each aspect with:
     * - Original & adjusted standards
     * - Individual ratings & scores
     * - Gaps (original & adjusted)
     * - Conclusions
     *
     * @param  int  $participantId  Participant ID
     * @param  int  $categoryTypeId  Category type ID (Potensi or Kompetensi)
     * @param  int  $tolerancePercentage  Tolerance percentage (0-100)
     * @return Collection Collection of aspect assessments
     */
    public function getAspectAssessments(
        int $participantId,
        int $categoryTypeId,
        int $tolerancePercentage = 10
    ): Collection {
        $participant = Participant::with('positionFormation.template')->findOrFail($participantId);
        $template = $participant->positionFormation->template;
        $standardService = app(DynamicStandardService::class);

        // Get category code
        $category = CategoryType::findOrFail($categoryTypeId);
        $categoryCode = $category->code;

        // Get active aspect IDs
        $activeAspectIds = $standardService->getActiveAspectIds($template->id, $categoryCode);

        // Fallback to all IDs if no adjustments
        if (empty($activeAspectIds)) {
            $activeAspectIds = \App\Models\Aspect::where('category_type_id', $categoryTypeId)
                ->orderBy('order')
                ->pluck('id')
                ->toArray();
        }

        // Get aspect assessments with relationships
        $aspectAssessments = AspectAssessment::with([
            'aspect.subAspects',
            'subAspectAssessments.subAspect',
        ])
            ->where('participant_id', $participantId)
            ->whereIn('aspect_id', $activeAspectIds)
            ->orderBy('aspect_id')
            ->get();

        // Process each assessment
        return $aspectAssessments->map(function ($assessment) use ($template, $standardService, $categoryCode, $tolerancePercentage) {
            return $this->calculateAspectAssessment(
                $assessment,
                $template->id,
                $categoryCode,
                $standardService,
                $tolerancePercentage
            );
        });
    }

    /**
     * Calculate single aspect assessment with all details
     *
     * @param  AspectAssessment  $assessment  Aspect assessment model
     * @param  int  $templateId  Template ID
     * @param  string  $categoryCode  Category code ('potensi' or 'kompetensi')
     * @param  DynamicStandardService  $standardService  Standard service instance
     * @param  int  $tolerancePercentage  Tolerance percentage
     * @return array Calculated assessment data
     */
    private function calculateAspectAssessment(
        AspectAssessment $assessment,
        int $templateId,
        string $categoryCode,
        DynamicStandardService $standardService,
        int $tolerancePercentage
    ): array {
        $aspect = $assessment->aspect;

        // Get adjusted weight
        $adjustedWeight = $standardService->getAspectWeight($templateId, $aspect->code);
        $originalWeight = $aspect->weight_percentage;

        // Recalculate ratings based on category type
        if ($categoryCode === 'potensi') {
            // Potensi: Calculate from active sub-aspects
            [$recalculatedStandardRating, $recalculatedIndividualRating] = $this->calculatePotensiRatings(
                $assessment,
                $templateId,
                $standardService
            );
        } else {
            // Kompetensi: Use direct ratings
            $recalculatedStandardRating = $standardService->getAspectRating($templateId, $aspect->code);
            $recalculatedIndividualRating = (float) $assessment->individual_rating;
        }

        // Use recalculated ratings or fall back to database values
        $originalStandardRating = $recalculatedStandardRating ?? (float) $assessment->standard_rating;
        $individualRating = $recalculatedIndividualRating ?? (float) $assessment->individual_rating;

        // Calculate scores with adjusted weight
        $originalStandardScore = round($originalStandardRating * $adjustedWeight, 2);
        $individualScore = round($individualRating * $adjustedWeight, 2);

        // Calculate adjusted standard based on tolerance
        $toleranceFactor = 1 - ($tolerancePercentage / 100);
        $adjustedStandardRating = $originalStandardRating * $toleranceFactor;
        $adjustedStandardScore = $originalStandardScore * $toleranceFactor;

        // Calculate original gap (at tolerance 0%)
        $originalGapRating = $individualRating - $originalStandardRating;
        $originalGapScore = $individualScore - $originalStandardScore;

        // Calculate adjusted gap (with tolerance)
        $adjustedGapRating = $individualRating - $adjustedStandardRating;
        $adjustedGapScore = $individualScore - $adjustedStandardScore;

        // Calculate percentage based on adjusted standard
        $adjustedPercentage = $adjustedStandardScore > 0
            ? ($individualScore / $adjustedStandardScore) * 100
            : 0;

        return [
            'aspect_id' => $aspect->id,
            'aspect_code' => $aspect->code,
            'name' => $aspect->name,
            'description' => $aspect->description,
            'weight_percentage' => $adjustedWeight,
            'original_weight' => $originalWeight,
            'is_weight_adjusted' => $adjustedWeight !== $originalWeight,
            'original_standard_rating' => round($originalStandardRating, 2),
            'original_standard_score' => round($originalStandardScore, 2),
            'standard_rating' => round($adjustedStandardRating, 2),
            'standard_score' => round($adjustedStandardScore, 2),
            'individual_rating' => round($individualRating, 2),
            'individual_score' => round($individualScore, 2),
            'gap_rating' => round($adjustedGapRating, 2),
            'gap_score' => round($adjustedGapScore, 2),
            'original_gap_rating' => round($originalGapRating, 2),
            'original_gap_score' => round($originalGapScore, 2),
            'percentage_score' => round($adjustedPercentage, 2),
            'conclusion_text' => ConclusionService::getGapBasedConclusion($originalGapScore, $adjustedGapScore),
        ];
    }

    /**
     * Calculate Potensi aspect ratings from active sub-aspects
     *
     * @param  AspectAssessment  $assessment  Aspect assessment
     * @param  int  $templateId  Template ID
     * @param  DynamicStandardService  $standardService  Standard service
     * @return array [standardRating, individualRating]
     */
    private function calculatePotensiRatings(
        AspectAssessment $assessment,
        int $templateId,
        DynamicStandardService $standardService
    ): array {
        $aspect = $assessment->aspect;

        if (! $aspect->subAspects || $aspect->subAspects->count() === 0) {
            // No sub-aspects, return null to use database values
            return [null, null];
        }

        $activeSubAspectsStandardSum = 0;
        $activeSubAspectsIndividualSum = 0;
        $activeSubAspectsCount = 0;

        foreach ($assessment->subAspectAssessments as $subAssessment) {
            // Check if sub-aspect is active
            if (! $standardService->isSubAspectActive($templateId, $subAssessment->subAspect->code)) {
                continue; // Skip inactive sub-aspects
            }

            // Get adjusted sub-aspect standard rating from session
            $adjustedSubStandardRating = $standardService->getSubAspectRating(
                $templateId,
                $subAssessment->subAspect->code
            );

            $activeSubAspectsStandardSum += $adjustedSubStandardRating;
            $activeSubAspectsIndividualSum += $subAssessment->individual_rating;
            $activeSubAspectsCount++;
        }

        if ($activeSubAspectsCount > 0) {
            return [
                round($activeSubAspectsStandardSum / $activeSubAspectsCount, 2),
                round($activeSubAspectsIndividualSum / $activeSubAspectsCount, 2),
            ];
        }

        // No active sub-aspects, return null
        return [null, null];
    }

    /**
     * Get category assessment totals for a participant
     *
     * Returns aggregated totals for a category (Potensi or Kompetensi):
     * - Total standard rating/score
     * - Total individual rating/score
     * - Total gaps
     * - Overall conclusion
     *
     * @param  int  $participantId  Participant ID
     * @param  string  $categoryCode  Category code ('potensi' or 'kompetensi')
     * @param  int  $tolerancePercentage  Tolerance percentage (0-100)
     * @return array Category totals
     */
    public function getCategoryAssessment(
        int $participantId,
        string $categoryCode,
        int $tolerancePercentage = 10
    ): array {
        $participant = Participant::with('positionFormation.template')->findOrFail($participantId);
        $template = $participant->positionFormation->template;

        // Get category type
        $category = CategoryType::where('template_id', $template->id)
            ->where('code', $categoryCode)
            ->firstOrFail();

        // Get aspect assessments
        $aspectAssessments = $this->getAspectAssessments(
            $participantId,
            $category->id,
            $tolerancePercentage
        );

        // Calculate totals
        $totalStandardRating = 0;
        $totalStandardScore = 0;
        $totalIndividualRating = 0;
        $totalIndividualScore = 0;
        $totalGapRating = 0;
        $totalGapScore = 0;
        $totalOriginalStandardScore = 0;
        $totalOriginalGapScore = 0;

        foreach ($aspectAssessments as $aspect) {
            $totalStandardRating += $aspect['standard_rating'];
            $totalStandardScore += $aspect['standard_score'];
            $totalIndividualRating += $aspect['individual_rating'];
            $totalIndividualScore += $aspect['individual_score'];
            $totalGapRating += $aspect['gap_rating'];
            $totalGapScore += $aspect['gap_score'];
            $totalOriginalStandardScore += $aspect['original_standard_score'];
            $totalOriginalGapScore += $aspect['original_gap_score'];
        }

        // Get category weight from DynamicStandardService
        $standardService = app(DynamicStandardService::class);
        $categoryWeight = $standardService->getCategoryWeight($template->id, $categoryCode);

        // Calculate weighted scores (multiplied by category weight)
        $weightedStandardScore = round($totalStandardScore * ($categoryWeight / 100), 2);
        $weightedIndividualScore = round($totalIndividualScore * ($categoryWeight / 100), 2);
        $weightedGapScore = round($weightedIndividualScore - $weightedStandardScore, 2);

        // Determine overall conclusion
        $overallConclusion = ConclusionService::getGapBasedConclusion($totalOriginalGapScore, $totalGapScore);

        return [
            'category_code' => $categoryCode,
            'category_name' => $category->name,
            'category_weight' => $categoryWeight,
            'aspect_count' => $aspectAssessments->count(),
            'total_standard_rating' => round($totalStandardRating, 2),
            'total_standard_score' => round($totalStandardScore, 2),
            'total_individual_rating' => round($totalIndividualRating, 2),
            'total_individual_score' => round($totalIndividualScore, 2),
            'total_gap_rating' => round($totalGapRating, 2),
            'total_gap_score' => round($totalGapScore, 2),
            'total_original_standard_score' => round($totalOriginalStandardScore, 2),
            'total_original_gap_score' => round($totalOriginalGapScore, 2),
            'overall_conclusion' => $overallConclusion,
            // NEW: Weighted scores (after applying category weight)
            'weighted_standard_score' => $weightedStandardScore,
            'weighted_individual_score' => $weightedIndividualScore,
            'weighted_gap_score' => $weightedGapScore,
            'aspects' => $aspectAssessments,
        ];
    }

    /**
     * Get final assessment (combined Potensi + Kompetensi)
     *
     * Returns weighted combination of both categories with:
     * - Category weights (from template)
     * - Weighted scores
     * - Achievement percentage
     * - Final conclusion
     *
     * @param  int  $participantId  Participant ID
     * @param  int  $tolerancePercentage  Tolerance percentage (0-100)
     * @return array Final assessment data
     */
    public function getFinalAssessment(
        int $participantId,
        int $tolerancePercentage = 10
    ): array {
        $participant = Participant::with('positionFormation.template')->findOrFail($participantId);
        $template = $participant->positionFormation->template;

        // Get category assessments
        $potensiAssessment = $this->getCategoryAssessment($participantId, 'potensi', $tolerancePercentage);
        $kompetensiAssessment = $this->getCategoryAssessment($participantId, 'kompetensi', $tolerancePercentage);

        // Get weights from DynamicStandardService (with fallback to original)
        $standardService = app(DynamicStandardService::class);
        $potensiWeight = $standardService->getCategoryWeight($template->id, 'potensi');
        $kompetensiWeight = $standardService->getCategoryWeight($template->id, 'kompetensi');

        // Calculate weighted scores
        $totalStandardScore = round(
            ($potensiAssessment['total_standard_score'] * ($potensiWeight / 100)) +
            ($kompetensiAssessment['total_standard_score'] * ($kompetensiWeight / 100)),
            2
        );

        $totalIndividualScore = round(
            ($potensiAssessment['total_individual_score'] * ($potensiWeight / 100)) +
            ($kompetensiAssessment['total_individual_score'] * ($kompetensiWeight / 100)),
            2
        );

        $totalOriginalStandardScore = round(
            ($potensiAssessment['total_original_standard_score'] * ($potensiWeight / 100)) +
            ($kompetensiAssessment['total_original_standard_score'] * ($kompetensiWeight / 100)),
            2
        );

        // Calculate gaps
        $totalGapScore = round($totalIndividualScore - $totalStandardScore, 2);
        $totalOriginalGapScore = round($totalIndividualScore - $totalOriginalStandardScore, 2);

        // Calculate achievement percentage
        $achievementPercentage = $totalStandardScore > 0
            ? ($totalIndividualScore / $totalStandardScore) * 100
            : 0;

        // FIXED: Use gap-based conclusion (not percentage-based)
        $finalConclusion = ConclusionService::getGapBasedConclusion($totalOriginalGapScore, $totalGapScore);

        return [
            'participant_id' => $participantId,
            'template_id' => $template->id,
            'template_name' => $template->name,
            'tolerance_percentage' => $tolerancePercentage,
            'potensi_weight' => $potensiWeight,
            'kompetensi_weight' => $kompetensiWeight,
            'potensi' => $potensiAssessment,
            'kompetensi' => $kompetensiAssessment,
            'total_standard_score' => $totalStandardScore,
            'total_individual_score' => $totalIndividualScore,
            'total_original_standard_score' => $totalOriginalStandardScore,
            'total_gap_score' => $totalGapScore,
            'total_original_gap_score' => $totalOriginalGapScore,
            'achievement_percentage' => round($achievementPercentage, 2),
            'final_conclusion' => $finalConclusion,
        ];
    }

    /**
     * Get passing summary for aspects
     *
     * @param  Collection  $aspectAssessments  Aspect assessments collection
     * @return array Passing summary
     */
    public function getPassingSummary(Collection $aspectAssessments): array
    {
        $totalAspects = $aspectAssessments->count();

        $passingAspects = $aspectAssessments->filter(function ($aspect) {
            return $aspect['conclusion_text'] === 'Di Atas Standar'
                || $aspect['conclusion_text'] === 'Memenuhi Standar';
        })->count();

        return [
            'total' => $totalAspects,
            'passing' => $passingAspects,
            'percentage' => $totalAspects > 0 ? round(($passingAspects / $totalAspects) * 100) : 0,
        ];
    }
}
