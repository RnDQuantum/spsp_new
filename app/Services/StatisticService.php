<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Aspect;
use App\Models\AspectAssessment;
use Illuminate\Support\Facades\DB;

/**
 * StatisticService - Service for Statistical Distribution Analysis
 *
 * This service handles:
 * - Frequency distribution of individual ratings (5 buckets)
 * - Adjusted standard rating calculation (with session adjustments)
 * - Average individual rating calculation (recalculated from active sub-aspects)
 *
 * Works with:
 * - DynamicStandardService (for session adjustments)
 *
 * Used by:
 * - Statistic component (frequency distribution chart)
 */
class StatisticService
{
    /**
     * Get distribution data for an aspect in an event/position
     *
     * Returns:
     * - distribution: Array [1=>count, 2=>count, 3=>count, 4=>count, 5=>count]
     * - standard_rating: Adjusted standard rating from session
     * - average_rating: Average individual rating (recalculated from active sub-aspects)
     *
     * @param  int  $eventId  Assessment event ID
     * @param  int  $positionFormationId  Position formation ID
     * @param  int  $aspectId  Aspect ID
     * @param  int  $templateId  Template ID (for session adjustments)
     * @return array Distribution data
     */
    public function getDistributionData(
        int $eventId,
        int $positionFormationId,
        int $aspectId,
        int $templateId
    ): array {
        // Get aspect data with category type and sub-aspects
        $aspect = Aspect::with(['categoryType', 'subAspects'])
            ->findOrFail($aspectId);

        $standardService = app(DynamicStandardService::class);

        // Calculate adjusted standard rating
        $standardRating = $this->calculateStandardRating(
            $aspect,
            $templateId,
            $standardService
        );

        // Get distribution (recalculated from active sub-aspects for Potensi)
        $distribution = $this->calculateDistribution(
            $eventId,
            $positionFormationId,
            $aspect,
            $templateId,
            $standardService
        );

        // Calculate average rating (recalculated from active sub-aspects for Potensi)
        $averageRating = $this->calculateAverageRating(
            $eventId,
            $positionFormationId,
            $aspect,
            $templateId,
            $standardService
        );

        return [
            'distribution' => $distribution,
            'standard_rating' => round($standardRating, 2),
            'average_rating' => round($averageRating, 2),
        ];
    }

    /**
     * Calculate adjusted standard rating for an aspect (DATA-DRIVEN)
     *
     * For aspects with sub-aspects: Average of active sub-aspect ratings (from session)
     * For aspects without sub-aspects: Direct aspect rating (from session)
     *
     * @param  Aspect  $aspect  Aspect model
     * @param  int  $templateId  Template ID
     * @param  DynamicStandardService  $standardService  Standard service instance
     * @return float Standard rating
     */
    private function calculateStandardRating(
        Aspect $aspect,
        int $templateId,
        DynamicStandardService $standardService
    ): float {
        // ✅ DATA-DRIVEN: Check if aspect has sub-aspects
        if ($aspect->subAspects->isNotEmpty()) {
            $subAspectRatingSum = 0;
            $activeSubAspectsCount = 0;

            foreach ($aspect->subAspects as $subAspect) {
                // Check if sub-aspect is active
                if (! $standardService->isSubAspectActive($templateId, $subAspect->code)) {
                    continue; // Skip inactive sub-aspects
                }

                // Get adjusted sub-aspect rating from session
                $subRating = $standardService->getSubAspectRating($templateId, $subAspect->code);
                $subAspectRatingSum += $subRating;
                $activeSubAspectsCount++;
            }

            // Calculate average of active sub-aspects
            if ($activeSubAspectsCount > 0) {
                return $subAspectRatingSum / $activeSubAspectsCount;
            }

            return 0.0;
        }

        // No sub-aspects: use direct aspect rating
        return $standardService->getAspectRating($templateId, $aspect->code);
    }

    /**
     * Calculate frequency distribution (DATA-DRIVEN)
     *
     * Distribution buckets based on classification table:
     * I:   1.00 - 1.80  (Sangat Kurang)
     * II:  1.80 - 2.60  (Kurang)
     * III: 2.60 - 3.40  (Cukup)
     * IV:  3.40 - 4.20  (Baik)
     * V:   4.20 - 5.00  (Sangat Baik)
     *
     * @param  int  $eventId  Event ID
     * @param  int  $positionFormationId  Position formation ID
     * @param  Aspect  $aspect  Aspect model
     * @param  int  $templateId  Template ID
     * @param  DynamicStandardService  $standardService  Standard service instance
     * @return array Distribution array [1=>count, 2=>count, 3=>count, 4=>count, 5=>count]
     */
    private function calculateDistribution(
        int $eventId,
        int $positionFormationId,
        Aspect $aspect,
        int $templateId,
        DynamicStandardService $standardService
    ): array {
        $distribution = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];

        // ✅ DATA-DRIVEN: Check if we need to recalculate from sub-aspects
        $needsRecalculation = $aspect->subAspects->isNotEmpty();

        if (! $needsRecalculation) {
            // No sub-aspects: Use stored individual_rating directly
            $rows = DB::table('aspect_assessments')
                ->where('event_id', $eventId)
                ->where('position_formation_id', $positionFormationId)
                ->where('aspect_id', $aspect->id)
                ->selectRaw('
                    CASE
                        WHEN individual_rating >= 1.00 AND individual_rating < 1.80 THEN 1
                        WHEN individual_rating >= 1.80 AND individual_rating < 2.60 THEN 2
                        WHEN individual_rating >= 2.60 AND individual_rating < 3.40 THEN 3
                        WHEN individual_rating >= 3.40 AND individual_rating < 4.20 THEN 4
                        WHEN individual_rating >= 4.20 AND individual_rating <= 5.00 THEN 5
                        ELSE 0
                    END as bucket,
                    COUNT(*) as total
                ')
                ->groupBy('bucket')
                ->get();

            foreach ($rows as $row) {
                $bucket = (int) $row->bucket;
                if ($bucket >= 1 && $bucket <= 5) {
                    $distribution[$bucket] = (int) $row->total;
                }
            }

            return $distribution;
        }

        // Has sub-aspects: Recalculate individual rating from active sub-aspects
        $assessments = AspectAssessment::with('subAspectAssessments.subAspect')
            ->where('event_id', $eventId)
            ->where('position_formation_id', $positionFormationId)
            ->where('aspect_id', $aspect->id)
            ->get();

        foreach ($assessments as $assessment) {
            // Recalculate individual rating from active sub-aspects
            $recalculatedRating = $this->calculateIndividualRatingFromSubAspects(
                $assessment,
                $templateId,
                $standardService
            );

            // Determine bucket
            $bucket = $this->getRatingBucket($recalculatedRating);

            if ($bucket >= 1 && $bucket <= 5) {
                $distribution[$bucket]++;
            }
        }

        return $distribution;
    }

    /**
     * Calculate average individual rating (DATA-DRIVEN)
     *
     * @param  int  $eventId  Event ID
     * @param  int  $positionFormationId  Position formation ID
     * @param  Aspect  $aspect  Aspect model
     * @param  int  $templateId  Template ID
     * @param  DynamicStandardService  $standardService  Standard service instance
     * @return float Average rating
     */
    private function calculateAverageRating(
        int $eventId,
        int $positionFormationId,
        Aspect $aspect,
        int $templateId,
        DynamicStandardService $standardService
    ): float {
        // ✅ DATA-DRIVEN: Check if we need to recalculate from sub-aspects
        $needsRecalculation = $aspect->subAspects->isNotEmpty();

        if (! $needsRecalculation) {
            // No sub-aspects: Use stored individual_rating directly
            $avg = DB::table('aspect_assessments')
                ->where('event_id', $eventId)
                ->where('position_formation_id', $positionFormationId)
                ->where('aspect_id', $aspect->id)
                ->avg('individual_rating');

            return (float) ($avg ?? 0);
        }

        // Has sub-aspects: Recalculate from active sub-aspects
        $assessments = AspectAssessment::with('subAspectAssessments.subAspect')
            ->where('event_id', $eventId)
            ->where('position_formation_id', $positionFormationId)
            ->where('aspect_id', $aspect->id)
            ->get();

        if ($assessments->isEmpty()) {
            return 0.0;
        }

        $totalRating = 0;
        $count = 0;

        foreach ($assessments as $assessment) {
            // Recalculate individual rating from active sub-aspects
            $recalculatedRating = $this->calculateIndividualRatingFromSubAspects(
                $assessment,
                $templateId,
                $standardService
            );

            $totalRating += $recalculatedRating;
            $count++;
        }

        return $count > 0 ? $totalRating / $count : 0.0;
    }

    /**
     * Calculate individual rating from active sub-aspects (DATA-DRIVEN)
     *
     * @param  AspectAssessment  $assessment  Aspect assessment
     * @param  int  $templateId  Template ID
     * @param  DynamicStandardService  $standardService  Standard service instance
     * @return float Individual rating
     */
    private function calculateIndividualRatingFromSubAspects(
        AspectAssessment $assessment,
        int $templateId,
        DynamicStandardService $standardService
    ): float {
        if ($assessment->subAspectAssessments->isEmpty()) {
            // No sub-aspects, use direct rating
            return (float) $assessment->individual_rating;
        }

        $subAspectIndividualSum = 0;
        $activeSubAspectsCount = 0;

        foreach ($assessment->subAspectAssessments as $subAssessment) {
            // Check if sub-aspect is active
            if (! $standardService->isSubAspectActive($templateId, $subAssessment->subAspect->code)) {
                continue; // Skip inactive sub-aspects
            }

            $subAspectIndividualSum += $subAssessment->individual_rating;
            $activeSubAspectsCount++;
        }

        if ($activeSubAspectsCount > 0) {
            return $subAspectIndividualSum / $activeSubAspectsCount;
        }

        // Fallback to direct rating if no active sub-aspects
        return (float) $assessment->individual_rating;
    }

    /**
     * Get rating bucket (1-5) based on classification table ranges
     *
     * @param  float  $rating  Rating value
     * @return int Bucket number (1-5, or 0 if out of range)
     */
    private function getRatingBucket(float $rating): int
    {
        if ($rating >= 1.00 && $rating < 1.80) {
            return 1;
        }
        if ($rating >= 1.80 && $rating < 2.60) {
            return 2;
        }
        if ($rating >= 2.60 && $rating < 3.40) {
            return 3;
        }
        if ($rating >= 3.40 && $rating < 4.20) {
            return 4;
        }
        if ($rating >= 4.20 && $rating <= 5.00) {
            return 5;
        }

        return 0; // Out of range
    }
}
