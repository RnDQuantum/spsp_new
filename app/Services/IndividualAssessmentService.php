<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AspectAssessment;
use App\Models\CategoryType;
use App\Models\Participant;
use App\Services\Cache\AspectCacheService;
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
     * Participant cache to avoid duplicate queries
     */
    private static array $participantCache = [];

    /**
     * Get participant with template (with caching)
     * If participant object is passed, use it directly to avoid duplicate queries
     */
    private function getParticipant(int|Participant $participant): Participant
    {
        if ($participant instanceof Participant) {
            // Participant object passed, cache it and return
            $participantId = $participant->id;
            if (! isset(self::$participantCache[$participantId])) {
                self::$participantCache[$participantId] = $participant;
            }

            return $participant;
        }

        // Participant ID passed, load from cache or database
        $participantId = $participant;
        if (! isset(self::$participantCache[$participantId])) {
            self::$participantCache[$participantId] = Participant::with('positionFormation.template')
                ->findOrFail($participantId);
        }

        return self::$participantCache[$participantId];
    }

    /**
     * Clear participant cache
     */
    public static function clearParticipantCache(): void
    {
        self::$participantCache = [];
    }

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
        $participant = $this->getParticipant($participantId);
        $template = $participant->positionFormation->template;
        $standardService = app(DynamicStandardService::class);

        // Preload aspect cache for this template
        AspectCacheService::preloadByTemplate($template->id);

        // Get category using cache
        $category = AspectCacheService::getCategoryById($categoryTypeId);
        if (! $category) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException("CategoryType not found: {$categoryTypeId}");
        }
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

        // 🚀 OPTIMIZATION: Conditional eager loading
        // Only load sub-aspects relationships if custom standard has adjustments
        // Similar to RankingService optimization
        $hasSubAspectAdjustments = $standardService->hasActiveSubAspectAdjustments($template->id);

        $query = AspectAssessment::query();

        // CRITICAL FIX: Always check if aspect has sub-aspects structure
        // Even without custom adjustments, code ACCESSES sub-aspects for calculation
        // So we MUST eager load to prevent N+1
        $query->with([
            'aspect.subAspects',
            'subAspectAssessments.subAspect',
        ]);

        // TODO: Future optimization - only load if aspect structure has sub-aspects
        // Current issue: calculateAspectAssessment() ALWAYS accesses $aspect->subAspects (line 174)
        // This causes N+1 even when hasSubAspectAdjustments = false

        // Get aspect assessments
        $aspectAssessments = $query
            ->where('participant_id', $participantId)
            ->whereIn('aspect_id', $activeAspectIds)
            ->orderBy('aspect_id')
            ->get();

        // Process each assessment
        return $aspectAssessments->map(function ($assessment) use ($template, $standardService, $tolerancePercentage) {
            return $this->calculateAspectAssessment(
                $assessment,
                $template->id,
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
     * @param  DynamicStandardService  $standardService  Standard service instance
     * @param  int  $tolerancePercentage  Tolerance percentage
     * @return array Calculated assessment data
     */
    private function calculateAspectAssessment(
        AspectAssessment $assessment,
        int $templateId,
        DynamicStandardService $standardService,
        int $tolerancePercentage
    ): array {
        $aspect = $assessment->aspect;

        // Get adjusted weight
        $adjustedWeight = $standardService->getAspectWeight($templateId, $aspect->code);
        $originalWeight = $aspect->weight_percentage;

        // ✅ DATA-DRIVEN: Recalculate ratings based on structure
        if ($aspect->subAspects->isNotEmpty()) {
            // Has sub-aspects: Calculate from active sub-aspects
            [$recalculatedStandardRating, $recalculatedIndividualRating] = $this->calculateRatingsFromSubAspects(
                $assessment,
                $templateId,
                $standardService
            );
        } else {
            // No sub-aspects: Use direct ratings
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
     * Calculate aspect ratings from active sub-aspects (DATA-DRIVEN)
     *
     * @param  AspectAssessment  $assessment  Aspect assessment
     * @param  int  $templateId  Template ID
     * @param  DynamicStandardService  $standardService  Standard service
     * @return array [standardRating, individualRating]
     */
    private function calculateRatingsFromSubAspects(
        AspectAssessment $assessment,
        int $templateId,
        DynamicStandardService $standardService
    ): array {
        $aspect = $assessment->aspect;

        if ($aspect->subAspects->isEmpty()) {
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
        $participant = $this->getParticipant($participantId);
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
        $participant = $this->getParticipant($participantId);
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
     * OPTIMIZATION: Get participant full assessment in single pass
     *
     * This method loads all aspect data once and reuses it for:
     * - Aspects array (for table display)
     * - Final assessment calculations (weighted totals)
     *
     * Prevents duplicate queries to AspectAssessment table
     *
     * @param  int  $participantId  Participant ID
     * @param  int|null  $potensiCategoryId  Potensi category ID (optional)
     * @param  int|null  $kompetensiCategoryId  Kompetensi category ID (optional)
     * @param  int  $tolerancePercentage  Tolerance percentage
     * @return array Full assessment data
     */
    public function getParticipantFullAssessment(
        int $participantId,
        ?int $potensiCategoryId,
        ?int $kompetensiCategoryId,
        int $tolerancePercentage = 10
    ): array {
        $participant = $this->getParticipant($participantId);
        $template = $participant->positionFormation->template;
        $standardService = app(DynamicStandardService::class);

        // Load aspect assessments ONCE for each category
        $potensiAspects = collect();
        $kompetensiAspects = collect();

        if ($potensiCategoryId) {
            $potensiAspects = $this->getAspectAssessments(
                $participantId,
                $potensiCategoryId,
                $tolerancePercentage
            );
        }

        if ($kompetensiCategoryId) {
            $kompetensiAspects = $this->getAspectAssessments(
                $participantId,
                $kompetensiCategoryId,
                $tolerancePercentage
            );
        }

        // Merge all aspects for table display
        $allAspects = $potensiAspects->merge($kompetensiAspects);

        // Calculate final assessment FROM already loaded data (no additional queries)
        $finalAssessment = $this->calculateFinalFromAspects(
            $potensiAspects,
            $kompetensiAspects,
            $template->id,
            $standardService,
            $tolerancePercentage
        );

        return [
            'aspects' => $allAspects->toArray(),
            'final_assessment' => $finalAssessment,
        ];
    }

    /**
     * Calculate final assessment from already-loaded aspect collections
     *
     * @param  Collection  $potensiAspects  Potensi aspects (already computed)
     * @param  Collection  $kompetensiAspects  Kompetensi aspects (already computed)
     * @param  int  $templateId  Template ID
     * @param  DynamicStandardService  $standardService  Standard service
     * @param  int  $tolerancePercentage  Tolerance percentage
     * @return array Final assessment
     */
    private function calculateFinalFromAspects(
        Collection $potensiAspects,
        Collection $kompetensiAspects,
        int $templateId,
        DynamicStandardService $standardService,
        int $tolerancePercentage
    ): array {
        // Calculate category totals from aspects
        $potensiTotals = $this->calculateCategoryTotalsFromAspects($potensiAspects);
        $kompetensiTotals = $this->calculateCategoryTotalsFromAspects($kompetensiAspects);

        // Get category weights
        $potensiWeight = $standardService->getCategoryWeight($templateId, 'potensi');
        $kompetensiWeight = $standardService->getCategoryWeight($templateId, 'kompetensi');

        // Calculate weighted scores
        $totalStandardScore = round(
            ($potensiTotals['total_standard_score'] * ($potensiWeight / 100)) +
            ($kompetensiTotals['total_standard_score'] * ($kompetensiWeight / 100)),
            2
        );

        $totalIndividualScore = round(
            ($potensiTotals['total_individual_score'] * ($potensiWeight / 100)) +
            ($kompetensiTotals['total_individual_score'] * ($kompetensiWeight / 100)),
            2
        );

        $totalOriginalStandardScore = round(
            ($potensiTotals['total_original_standard_score'] * ($potensiWeight / 100)) +
            ($kompetensiTotals['total_original_standard_score'] * ($kompetensiWeight / 100)),
            2
        );

        // Calculate gaps
        $totalGapScore = round($totalIndividualScore - $totalStandardScore, 2);
        $totalOriginalGapScore = round($totalIndividualScore - $totalOriginalStandardScore, 2);

        // Achievement percentage
        $achievementPercentage = $totalStandardScore > 0
            ? ($totalIndividualScore / $totalStandardScore) * 100
            : 0;

        // Final conclusion
        $finalConclusion = ConclusionService::getGapBasedConclusion($totalOriginalGapScore, $totalGapScore);

        return [
            'tolerance_percentage' => $tolerancePercentage,
            'potensi_weight' => $potensiWeight,
            'kompetensi_weight' => $kompetensiWeight,
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
     * Calculate category totals from aspect collection
     *
     * @param  Collection  $aspects  Aspect assessments
     * @return array Category totals
     */
    private function calculateCategoryTotalsFromAspects(Collection $aspects): array
    {
        $totalStandardScore = 0;
        $totalIndividualScore = 0;
        $totalOriginalStandardScore = 0;
        $totalOriginalGapScore = 0;

        foreach ($aspects as $aspect) {
            $totalStandardScore += $aspect['standard_score'];
            $totalIndividualScore += $aspect['individual_score'];
            $totalOriginalStandardScore += $aspect['original_standard_score'];
            $totalOriginalGapScore += $aspect['original_gap_score'];
        }

        return [
            'total_standard_score' => $totalStandardScore,
            'total_individual_score' => $totalIndividualScore,
            'total_original_standard_score' => $totalOriginalStandardScore,
            'total_original_gap_score' => $totalOriginalGapScore,
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

    /**
     * Get all aspect matching data for GeneralMatching component (BATCH LOADING)
     *
     * Returns both Potensi and Kompetensi data in single query to eliminate duplicates
     *
     * @param  int  $participantId  Participant ID
     * @return array Array with 'potensi' and 'kompetensi' keys containing matching data
     */
    public function getAllAspectMatchingData(int|Participant $participant): array
    {
        $participant = $this->getParticipant($participant);
        $participantId = $participant->id;
        $template = $participant->positionFormation->template;
        $standardService = app(DynamicStandardService::class);

        // Preload aspect cache for this template (once)
        AspectCacheService::preloadByTemplate($template->id);

        // Get category types using cache
        $potensiCategory = AspectCacheService::getCategoryByCode($template->id, 'potensi');
        $kompetensiCategory = AspectCacheService::getCategoryByCode($template->id, 'kompetensi');

        // Get active aspect IDs for both categories
        $allActiveAspectIds = [];

        if ($potensiCategory) {
            $potensiAspectIds = $standardService->getActiveAspectIds($template->id, 'potensi');
            if (empty($potensiAspectIds)) {
                $potensiAspectIds = \App\Models\Aspect::where('category_type_id', $potensiCategory->id)
                    ->orderBy('order')
                    ->pluck('id')
                    ->toArray();
            }
            $allActiveAspectIds = array_merge($allActiveAspectIds, $potensiAspectIds);
        }

        if ($kompetensiCategory) {
            $kompetensiAspectIds = $standardService->getActiveAspectIds($template->id, 'kompetensi');
            if (empty($kompetensiAspectIds)) {
                $kompetensiAspectIds = \App\Models\Aspect::where('category_type_id', $kompetensiCategory->id)
                    ->orderBy('order')
                    ->pluck('id')
                    ->toArray();
            }
            $allActiveAspectIds = array_merge($allActiveAspectIds, $kompetensiAspectIds);
        }

        // Single query to get ALL aspect assessments with relationships
        $allAspectAssessments = AspectAssessment::with([
            'aspect.subAspects',
            'subAspectAssessments.subAspect',
        ])
            ->where('participant_id', $participantId)
            ->whereIn('aspect_id', array_unique($allActiveAspectIds))
            ->orderBy('aspect_id')
            ->get();

        // Group by category
        $groupedAssessments = $allAspectAssessments->groupBy(function ($assessment) {
            return $assessment->aspect->category_type_id;
        });

        // Process each category
        $result = [];

        if ($potensiCategory) {
            $potensiAssessments = $groupedAssessments->get($potensiCategory->id, collect());
            $result['potensi'] = $potensiAssessments->map(function ($assessment) use ($template, $standardService) {
                return $this->calculateAspectMatching($assessment, $template->id, $standardService);
            })->values();
        }

        if ($kompetensiCategory) {
            $kompetensiAssessments = $groupedAssessments->get($kompetensiCategory->id, collect());
            $result['kompetensi'] = $kompetensiAssessments->map(function ($assessment) use ($template, $standardService) {
                return $this->calculateAspectMatching($assessment, $template->id, $standardService);
            })->values();
        }

        return $result;
    }

    /**
     * Get aspect matching data for GeneralMatching component
     *
     * Returns detailed aspect data with matching percentages:
     * - For Potensi: Sub-aspect level details with individual matching percentages
     * - For Kompetensi: Aspect level details with matching percentages
     *
     * Matching percentage logic:
     * - If individual >= standard: 100%
     * - Else: (individual / standard) × 100
     *
     * @param  int  $participantId  Participant ID
     * @param  int  $categoryTypeId  Category type ID (Potensi or Kompetensi)
     * @return Collection Collection of aspect matching data
     */
    public function getAspectMatchingData(
        int $participantId,
        int $categoryTypeId
    ): Collection {
        $participant = $this->getParticipant($participantId);
        $template = $participant->positionFormation->template;
        $standardService = app(DynamicStandardService::class);

        // Preload aspect cache for this template
        AspectCacheService::preloadByTemplate($template->id);

        // Get category using cache
        $category = AspectCacheService::getCategoryById($categoryTypeId);
        if (! $category) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException("CategoryType not found: {$categoryTypeId}");
        }
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

        // 🚀 OPTIMIZATION: Eager load to prevent N+1
        // Always load sub-aspects because matching logic accesses them
        $query = AspectAssessment::query();
        $query->with([
            'aspect.subAspects',
            'subAspectAssessments.subAspect',
        ]);

        // Get aspect assessments
        $aspectAssessments = $query
            ->where('participant_id', $participantId)
            ->whereIn('aspect_id', $activeAspectIds)
            ->orderBy('aspect_id')
            ->get();

        // Process each assessment
        return $aspectAssessments->map(function ($assessment) use ($template, $standardService) {
            return $this->calculateAspectMatching(
                $assessment,
                $template->id,
                $standardService
            );
        });
    }

    /**
     * Calculate aspect matching data with sub-aspect details
     *
     * @param  AspectAssessment  $assessment  Aspect assessment model
     * @param  int  $templateId  Template ID
     * @param  DynamicStandardService  $standardService  Standard service instance
     * @return array Matching data
     */
    private function calculateAspectMatching(
        AspectAssessment $assessment,
        int $templateId,
        DynamicStandardService $standardService
    ): array {
        $aspect = $assessment->aspect;

        // ✅ DATA-DRIVEN: Calculate aspect-level matching percentage and ratings
        if ($aspect->subAspects->isNotEmpty()) {
            // Has sub-aspects: Calculate from active sub-aspects
            [$standardRating, $individualRating, $matchingPercentage] = $this->calculateMatchingFromSubAspects(
                $assessment,
                $templateId,
                $standardService
            );
        } else {
            // No sub-aspects: Use direct ratings
            $standardRating = $standardService->getAspectRating($templateId, $aspect->code);
            $individualRating = (float) $assessment->individual_rating;
            $matchingPercentage = $this->calculateMatchingPercentage($individualRating, $standardRating);
        }

        // Load sub-aspects data (if aspect has sub-aspects)
        $subAspects = [];
        if ($aspect->subAspects->isNotEmpty() && $assessment->subAspectAssessments->isNotEmpty()) {
            $subAspects = $this->getSubAspectMatchingData(
                $assessment,
                $templateId,
                $standardService
            );
        }

        return [
            'name' => $aspect->name,
            'code' => $aspect->code,
            'description' => $aspect->description,
            'percentage' => round($matchingPercentage),
            'individual_rating' => round($individualRating, 2),
            'standard_rating' => round($standardRating, 2),
            'original_standard_rating' => (float) $assessment->standard_rating,
            'sub_aspects' => $subAspects,
        ];
    }

    /**
     * Calculate aspect matching from active sub-aspects (DATA-DRIVEN)
     *
     * @param  AspectAssessment  $assessment  Aspect assessment
     * @param  int  $templateId  Template ID
     * @param  DynamicStandardService  $standardService  Standard service
     * @return array [standardRating, individualRating, matchingPercentage]
     */
    private function calculateMatchingFromSubAspects(
        AspectAssessment $assessment,
        int $templateId,
        DynamicStandardService $standardService
    ): array {
        $aspect = $assessment->aspect;

        if ($aspect->subAspects->isEmpty()) {
            // No sub-aspects, use aspect-level ratings
            $standardRating = $standardService->getAspectRating($templateId, $aspect->code);
            $individualRating = (float) $assessment->individual_rating;
            $matchingPercentage = $this->calculateMatchingPercentage($individualRating, $standardRating);

            return [$standardRating, $individualRating, $matchingPercentage];
        }

        // Calculate from active sub-aspects
        $totalMatchingValue = 0;
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

            $subIndividualRating = $subAssessment->individual_rating;

            // Calculate matching value for this sub-aspect
            if ($subIndividualRating >= $adjustedSubStandardRating) {
                $totalMatchingValue += 1.0;
            } else {
                $totalMatchingValue += $subIndividualRating / $adjustedSubStandardRating;
            }

            $activeSubAspectsStandardSum += $adjustedSubStandardRating;
            $activeSubAspectsIndividualSum += $subIndividualRating;
            $activeSubAspectsCount++;
        }

        if ($activeSubAspectsCount > 0) {
            $standardRating = round($activeSubAspectsStandardSum / $activeSubAspectsCount, 2);
            $individualRating = round($activeSubAspectsIndividualSum / $activeSubAspectsCount, 2);
            $matchingPercentage = ($totalMatchingValue / $activeSubAspectsCount) * 100;

            return [$standardRating, $individualRating, $matchingPercentage];
        }

        // No active sub-aspects, return zeros
        return [0, 0, 0];
    }

    /**
     * Get sub-aspect matching data (for Potensi)
     *
     * @param  AspectAssessment  $assessment  Aspect assessment
     * @param  int  $templateId  Template ID
     * @param  DynamicStandardService  $standardService  Standard service
     * @return array Array of sub-aspect matching data
     */
    private function getSubAspectMatchingData(
        AspectAssessment $assessment,
        int $templateId,
        DynamicStandardService $standardService
    ): array {
        return $assessment->subAspectAssessments
            ->filter(function ($subAssessment) use ($templateId, $standardService) {
                // Only include active sub-aspects
                return $standardService->isSubAspectActive($templateId, $subAssessment->subAspect->code);
            })
            ->map(function ($subAssessment) use ($templateId, $standardService) {
                // Get adjusted standard rating from session
                $adjustedStandardRating = $standardService->getSubAspectRating(
                    $templateId,
                    $subAssessment->subAspect->code
                );

                return [
                    'name' => $subAssessment->subAspect->name,
                    'individual_rating' => $subAssessment->individual_rating,
                    'standard_rating' => $adjustedStandardRating,
                    'original_standard_rating' => $subAssessment->standard_rating,
                    'rating_label' => $subAssessment->rating_label,
                ];
            })
            ->values() // Reset array keys after filter
            ->toArray();
    }

    /**
     * Calculate matching percentage
     *
     * Logic: If individual >= standard → 100%
     *        Else → (individual / standard) × 100
     *
     * @param  float  $individualRating  Individual rating
     * @param  float  $standardRating  Standard rating
     * @return float Matching percentage
     */
    private function calculateMatchingPercentage(float $individualRating, float $standardRating): float
    {
        if ($standardRating <= 0) {
            return 0;
        }

        if ($individualRating >= $standardRating) {
            return 100.0;
        }

        return ($individualRating / $standardRating) * 100;
    }

    /**
     * Get job matching percentage (average of all aspect matching percentages)
     *
     * @param  int  $participantId  Participant ID
     * @return array Array with keys: job_match_percentage, potensi_percentage, kompetensi_percentage
     */
    public function getJobMatchingPercentage(int|Participant $participant): array
    {
        $participant = $this->getParticipant($participant);
        $template = $participant->positionFormation->template;

        // Preload aspect cache for this template
        AspectCacheService::preloadByTemplate($template->id);

        // Use batch loading to get all data in single query
        $allData = $this->getAllAspectMatchingData($participant);

        $allPercentages = [];
        $potensiPercentages = [];
        $kompetensiPercentages = [];

        // Extract percentages from batch data
        if (isset($allData['potensi'])) {
            foreach ($allData['potensi'] as $aspect) {
                $allPercentages[] = $aspect['percentage'];
                $potensiPercentages[] = $aspect['percentage'];
            }
        }

        if (isset($allData['kompetensi'])) {
            foreach ($allData['kompetensi'] as $aspect) {
                $allPercentages[] = $aspect['percentage'];
                $kompetensiPercentages[] = $aspect['percentage'];
            }
        }

        // Calculate averages
        $jobMatchPercentage = count($allPercentages) > 0
            ? round(array_sum($allPercentages) / count($allPercentages))
            : 0;

        $potensiAverage = count($potensiPercentages) > 0
            ? round(array_sum($potensiPercentages) / count($potensiPercentages))
            : 0;

        $kompetensiAverage = count($kompetensiPercentages) > 0
            ? round(array_sum($kompetensiPercentages) / count($kompetensiPercentages))
            : 0;

        return [
            'job_match_percentage' => $jobMatchPercentage,
            'potensi_percentage' => $potensiAverage,
            'kompetensi_percentage' => $kompetensiAverage,
        ];
    }
}
