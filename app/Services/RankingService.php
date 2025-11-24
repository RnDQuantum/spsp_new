<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Aspect;
use App\Models\AspectAssessment;
use App\Models\CategoryType;
use Illuminate\Support\Collection;

/**
 * RankingService - Single Source of Truth for Ranking Calculations
 *
 * This service ensures consistent ranking logic across all components:
 * - RankingPsyMapping, RankingMcMapping
 * - GeneralPsyMapping, GeneralMcMapping, GeneralMapping
 * - RekapRankingAssessment
 *
 * Key Features:
 * 1. Integrates with DynamicStandardService for session adjustments
 * 2. Integrates with ConclusionService for conclusion categorization
 * 3. Consistent ordering: Score DESC → Name ASC
 * 4. Recalculates standards from active aspects/sub-aspects
 * 5. Supports tolerance adjustment
 */
class RankingService
{
    /**
     * Get all participant rankings for a specific category
     *
     * @param  int  $eventId  Assessment event ID
     * @param  int  $positionFormationId  Position formation ID
     * @param  int  $templateId  Assessment template ID
     * @param  string  $categoryCode  Category code ('potensi' or 'kompetensi')
     * @param  int  $tolerancePercentage  Tolerance percentage (0-100)
     * @return Collection Collection of ranking items with keys: rank, participant_id, name, nip, individual_score, standard_score, gap_score, conclusion
     */
    public function getRankings(
        int $eventId,
        int $positionFormationId,
        int $templateId,
        string $categoryCode,
        int $tolerancePercentage = 10
    ): Collection {
        // Get active aspect IDs
        $activeAspectIds = $this->getActiveAspectIds($templateId, $categoryCode);

        if (empty($activeAspectIds)) {
            return collect();
        }

        $standardService = app(DynamicStandardService::class);

        // Get aspect assessments with aspect data for recalculation
        $assessments = AspectAssessment::query()
            ->with(['aspect.subAspects', 'subAspectAssessments.subAspect'])
            ->join('participants', 'participants.id', '=', 'aspect_assessments.participant_id')
            ->where('aspect_assessments.event_id', $eventId)
            ->where('aspect_assessments.position_formation_id', $positionFormationId)
            ->whereIn('aspect_assessments.aspect_id', $activeAspectIds)
            ->select('aspect_assessments.*', 'participants.name as participant_name')
            ->orderBy('participants.name')
            ->get();

        if ($assessments->isEmpty()) {
            return collect();
        }

        // Group by participant and recalculate scores with adjusted weights
        $participantScores = [];

        foreach ($assessments as $assessment) {
            $participantId = $assessment->participant_id;
            $aspect = $assessment->aspect;

            if (! isset($participantScores[$participantId])) {
                $participantScores[$participantId] = [
                    'participant_id' => $participantId,
                    'participant_name' => $assessment->participant_name,
                    'individual_rating' => 0,
                    'individual_score' => 0,
                ];
            }

            // Get adjusted weight from session
            $adjustedWeight = $standardService->getAspectWeight($templateId, $aspect->code);

            // Recalculate individual rating based on category
            if ($categoryCode === 'potensi') {
                // For Potensi: calculate from active sub-aspects
                $individualRating = $this->calculatePotensiIndividualRating(
                    $assessment,
                    $templateId,
                    $standardService
                );
            } else {
                // For Kompetensi: use direct rating
                $individualRating = (float) $assessment->individual_rating;
            }

            // Recalculate individual score with adjusted weight
            $individualScore = round($individualRating * $adjustedWeight, 2);

            // Accumulate
            $participantScores[$participantId]['individual_rating'] += $individualRating;
            $participantScores[$participantId]['individual_score'] += $individualScore;
        }

        // Convert to collection and sort by score DESC, then name ASC (tiebreaker)
        $rankings = collect($participantScores)
            ->sortBy([
                ['individual_score', 'desc'],
                ['participant_name', 'asc'],
            ])
            ->values();

        // Get adjusted standard values ONCE for all participants
        $adjustedStandards = $this->calculateAdjustedStandards(
            $templateId,
            $categoryCode,
            $activeAspectIds
        );

        // Calculate tolerance factor
        $toleranceFactor = 1 - ($tolerancePercentage / 100);
        $adjustedStandardRating = $adjustedStandards['standard_rating'] * $toleranceFactor;
        $adjustedStandardScore = $adjustedStandards['standard_score'] * $toleranceFactor;

        // Map to ranking items with calculated values
        return $rankings->map(function ($row, $index) use (
            $adjustedStandards,
            $adjustedStandardRating,
            $adjustedStandardScore
        ) {
            $individualRating = round($row['individual_rating'], 2);
            $individualScore = round($row['individual_score'], 2);

            // Calculate gaps
            $originalGapRating = $individualRating - $adjustedStandards['standard_rating'];
            $originalGapScore = $individualScore - $adjustedStandards['standard_score'];
            $adjustedGapRating = $individualRating - $adjustedStandardRating;
            $adjustedGapScore = $individualScore - $adjustedStandardScore;

            // Calculate percentage
            $percentage = $adjustedStandardScore > 0
                ? ($individualScore / $adjustedStandardScore) * 100
                : 0;

            return [
                'rank' => $index + 1,
                'participant_id' => $row['participant_id'],
                'individual_rating' => $individualRating,
                'individual_score' => $individualScore,
                'original_standard_rating' => round($adjustedStandards['standard_rating'], 2),
                'original_standard_score' => round($adjustedStandards['standard_score'], 2),
                'adjusted_standard_rating' => round($adjustedStandardRating, 2),
                'adjusted_standard_score' => round($adjustedStandardScore, 2),
                'original_gap_rating' => round($originalGapRating, 2),
                'original_gap_score' => round($originalGapScore, 2),
                'adjusted_gap_rating' => round($adjustedGapRating, 2),
                'adjusted_gap_score' => round($adjustedGapScore, 2),
                'percentage' => round($percentage, 2),
                'conclusion' => ConclusionService::getGapBasedConclusion($originalGapScore, $adjustedGapScore),
            ];
        });
    }

    /**
     * Get specific participant's rank and conclusion
     *
     * @param  int  $participantId  Participant ID
     * @param  int  $eventId  Assessment event ID
     * @param  int  $positionFormationId  Position formation ID
     * @param  int  $templateId  Assessment template ID
     * @param  string  $categoryCode  Category code ('potensi' or 'kompetensi')
     * @param  int  $tolerancePercentage  Tolerance percentage (0-100)
     * @return array|null Array with keys: rank, total, conclusion, or null if not found
     */
    public function getParticipantRank(
        int $participantId,
        int $eventId,
        int $positionFormationId,
        int $templateId,
        string $categoryCode,
        int $tolerancePercentage = 10
    ): ?array {
        // Get all rankings
        $rankings = $this->getRankings(
            $eventId,
            $positionFormationId,
            $templateId,
            $categoryCode,
            $tolerancePercentage
        );

        if ($rankings->isEmpty()) {
            return null;
        }

        // Find participant's ranking
        $participantRanking = $rankings->firstWhere('participant_id', $participantId);

        if (! $participantRanking) {
            return null;
        }

        return [
            'rank' => $participantRanking['rank'],
            'total' => $rankings->count(),
            'conclusion' => $participantRanking['conclusion'],
            'percentage' => $participantRanking['percentage'],
            'individual_score' => $participantRanking['individual_score'],
            'adjusted_standard_score' => $participantRanking['adjusted_standard_score'],
            'adjusted_gap_score' => $participantRanking['adjusted_gap_score'],
        ];
    }

    /**
     * Calculate adjusted standard values from session adjustments
     *
     * This method recalculates standard rating and score based on:
     * 1. Active aspects/sub-aspects (from DynamicStandardService)
     * 2. Adjusted weights (from session)
     * 3. Adjusted ratings (from session)
     *
     * @param  int  $templateId  Assessment template ID
     * @param  string  $categoryCode  Category code ('potensi' or 'kompetensi')
     * @param  array  $aspectIds  Array of aspect IDs to include
     * @return array Array with keys: standard_rating, standard_score
     */
    public function calculateAdjustedStandards(
        int $templateId,
        string $categoryCode,
        array $aspectIds
    ): array {
        $standardService = app(DynamicStandardService::class);

        // ALWAYS recalculate from DynamicStandardService
        // DynamicStandardService handles priority: Session → Custom Standard → Quantum Default
        // We should NOT check hasCategoryAdjustments() because it only checks session,
        // and misses custom standard selection!

        $adjustedRating = 0;
        $adjustedScore = 0;

        // Get all aspects data ONCE
        $aspects = Aspect::whereIn('id', $aspectIds)
            ->with('subAspects')
            ->orderBy('order')
            ->get();

        foreach ($aspects as $aspect) {
            // Check if aspect is active
            if (! $standardService->isAspectActive($templateId, $aspect->code)) {
                continue; // Skip inactive aspects
            }

            // Get adjusted weight from DynamicStandardService (handles priority chain)
            $aspectWeight = $standardService->getAspectWeight($templateId, $aspect->code);

            // Get aspect rating based on category
            if ($categoryCode === 'potensi') {
                // For Potensi: calculate from sub-aspects
                $aspectRating = $this->calculatePotensiAspectRating(
                    $aspect,
                    $templateId,
                    $standardService
                );
            } else {
                // For Kompetensi: get direct rating from DynamicStandardService
                $aspectRating = $standardService->getAspectRating($templateId, $aspect->code);
            }

            // Accumulate
            $adjustedRating += $aspectRating;
            $aspectScore = round($aspectRating * $aspectWeight, 2);
            $adjustedScore += $aspectScore;
        }

        return [
            'standard_rating' => round($adjustedRating, 2),
            'standard_score' => round($adjustedScore, 2),
        ];
    }

    /**
     * Calculate original standard values from database (no adjustments)
     *
     * @param  array  $aspectIds  Array of aspect IDs
     * @return array Array with keys: standard_rating, standard_score
     */
    private function calculateOriginalStandards(array $aspectIds): array
    {
        $aspects = Aspect::whereIn('id', $aspectIds)
            ->with('subAspects')
            ->orderBy('order')
            ->get();

        $totalRating = 0;
        $totalScore = 0;

        foreach ($aspects as $aspect) {
            // For Potensi, calculate rating from sub-aspects
            if ($aspect->subAspects && $aspect->subAspects->count() > 0) {
                $subAspectRatingSum = 0;
                foreach ($aspect->subAspects as $subAspect) {
                    $subAspectRatingSum += $subAspect->standard_rating;
                }
                $aspectRating = round($subAspectRatingSum / $aspect->subAspects->count(), 2);
            } else {
                // For Kompetensi, use direct rating
                $aspectRating = (float) $aspect->standard_rating;
            }

            $totalRating += $aspectRating;
            $aspectScore = round($aspectRating * $aspect->weight_percentage, 2);
            $totalScore += $aspectScore;
        }

        return [
            'standard_rating' => round($totalRating, 2),
            'standard_score' => round($totalScore, 2),
        ];
    }

    /**
     * Calculate Potensi aspect rating from active sub-aspects
     *
     * @param  \App\Models\Aspect  $aspect  Aspect model
     * @param  int  $templateId  Template ID
     * @param  DynamicStandardService  $standardService  Standard service instance
     * @return float Calculated aspect rating
     */
    private function calculatePotensiAspectRating(
        $aspect,
        int $templateId,
        DynamicStandardService $standardService
    ): float {
        if (! $aspect->subAspects || $aspect->subAspects->count() === 0) {
            // No sub-aspects, use direct rating from session
            return $standardService->getAspectRating($templateId, $aspect->code);
        }

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

        if ($activeSubAspectsCount > 0) {
            return round($subAspectRatingSum / $activeSubAspectsCount, 2);
        }

        // Fallback to session rating if no active sub-aspects
        return $standardService->getAspectRating($templateId, $aspect->code);
    }

    /**
     * Calculate Potensi individual rating from active sub-aspects
     *
     * @param  \App\Models\AspectAssessment  $assessment  Aspect assessment
     * @param  int  $templateId  Template ID
     * @param  DynamicStandardService  $standardService  Standard service instance
     * @return float Calculated individual rating
     */
    private function calculatePotensiIndividualRating(
        $assessment,
        int $templateId,
        DynamicStandardService $standardService
    ): float {
        $aspect = $assessment->aspect;

        if (! $aspect->subAspects || $aspect->subAspects->count() === 0) {
            // No sub-aspects, use direct individual rating
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
            return round($subAspectIndividualSum / $activeSubAspectsCount, 2);
        }

        // Fallback to direct rating if no active sub-aspects
        return (float) $assessment->individual_rating;
    }

    /**
     * Get active aspect IDs for a category
     *
     * @param  int  $templateId  Template ID
     * @param  string  $categoryCode  Category code ('potensi' or 'kompetensi')
     * @return array Array of aspect IDs
     */
    private function getActiveAspectIds(int $templateId, string $categoryCode): array
    {
        $standardService = app(DynamicStandardService::class);

        // Get active aspect IDs from DynamicStandardService
        $activeAspectIds = $standardService->getActiveAspectIds($templateId, $categoryCode);

        // Fallback to all IDs if no adjustments (performance optimization)
        if (empty($activeAspectIds)) {
            $category = CategoryType::where('template_id', $templateId)
                ->where('code', $categoryCode)
                ->first();

            if (! $category) {
                return [];
            }

            $activeAspectIds = Aspect::where('category_type_id', $category->id)
                ->orderBy('order')
                ->pluck('id')
                ->toArray();
        }

        return $activeAspectIds;
    }

    /**
     * Get passing summary statistics
     *
     * @param  Collection  $rankings  Rankings collection from getRankings()
     * @return array Array with keys: total, passing, percentage
     */
    public function getPassingSummary(Collection $rankings): array
    {
        $total = $rankings->count();

        $passing = $rankings->filter(function ($ranking) {
            return $ranking['conclusion'] === 'Di Atas Standar'
                || $ranking['conclusion'] === 'Memenuhi Standar';
        })->count();

        return [
            'total' => $total,
            'passing' => $passing,
            'percentage' => $total > 0 ? (int) round(($passing / $total) * 100) : 0,
        ];
    }

    /**
     * Get conclusion summary (counts by conclusion type)
     *
     * @param  Collection  $rankings  Rankings collection from getRankings()
     * @return array Array with conclusion text as keys and counts as values
     */
    public function getConclusionSummary(Collection $rankings): array
    {
        return [
            'Di Atas Standar' => $rankings->where('conclusion', 'Di Atas Standar')->count(),
            'Memenuhi Standar' => $rankings->where('conclusion', 'Memenuhi Standar')->count(),
            'Di Bawah Standar' => $rankings->where('conclusion', 'Di Bawah Standar')->count(),
        ];
    }

    /**
     * Get combined rankings for Potensi + Kompetensi with category weights
     *
     * This method calculates weighted total scores from both categories and ranks
     * all participants based on the combined score.
     *
     * @param  int  $eventId  Assessment event ID
     * @param  int  $positionFormationId  Position formation ID
     * @param  int  $templateId  Assessment template ID
     * @param  int  $tolerancePercentage  Tolerance percentage (0-100)
     * @return Collection Collection of combined ranking items
     */
    public function getCombinedRankings(
        int $eventId,
        int $positionFormationId,
        int $templateId,
        int $tolerancePercentage = 10
    ): Collection {
        // Get category weights from DynamicStandardService
        $standardService = app(DynamicStandardService::class);
        $potensiWeight = $standardService->getCategoryWeight($templateId, 'potensi');
        $kompetensiWeight = $standardService->getCategoryWeight($templateId, 'kompetensi');

        if (($potensiWeight + $kompetensiWeight) === 0) {
            return collect();
        }

        // Get rankings for both categories
        $potensiRankings = $this->getRankings(
            $eventId,
            $positionFormationId,
            $templateId,
            'potensi',
            $tolerancePercentage
        );

        $kompetensiRankings = $this->getRankings(
            $eventId,
            $positionFormationId,
            $templateId,
            'kompetensi',
            $tolerancePercentage
        );

        if ($potensiRankings->isEmpty() || $kompetensiRankings->isEmpty()) {
            return collect();
        }

        // Get participant names in one query
        $participantIds = $potensiRankings->pluck('participant_id')->unique()->toArray();
        $participantNames = \App\Models\Participant::whereIn('id', $participantIds)
            ->pluck('name', 'id')
            ->toArray();

        // OPTIMIZED: Key by participant_id for O(1) lookup instead of O(n)
        $kompetensiRankingsKeyed = $kompetensiRankings->keyBy('participant_id');

        // Combine scores with category weights
        $participantScores = [];
        foreach ($potensiRankings as $potensiRank) {
            $participantId = $potensiRank['participant_id'];

            // OPTIMIZED: O(1) lookup instead of O(n) firstWhere
            $kompetensiRank = $kompetensiRankingsKeyed->get($participantId);
            if (! $kompetensiRank) {
                continue;
            }

            // Calculate weighted total score
            $weightedPotensiScore = $potensiRank['individual_score'] * ($potensiWeight / 100);
            $weightedKompetensiScore = $kompetensiRank['individual_score'] * ($kompetensiWeight / 100);
            $totalIndividualScore = round($weightedPotensiScore + $weightedKompetensiScore, 2);

            // Calculate weighted standard score (with tolerance)
            $weightedPotensiStd = $potensiRank['adjusted_standard_score'] * ($potensiWeight / 100);
            $weightedKompetensiStd = $kompetensiRank['adjusted_standard_score'] * ($kompetensiWeight / 100);
            $totalStandardScore = round($weightedPotensiStd + $weightedKompetensiStd, 2);

            // Calculate weighted original standard score
            $weightedOrigPotensiStd = $potensiRank['original_standard_score'] * ($potensiWeight / 100);
            $weightedOrigKompetensiStd = $kompetensiRank['original_standard_score'] * ($kompetensiWeight / 100);
            $totalOriginalStandardScore = round($weightedOrigPotensiStd + $weightedOrigKompetensiStd, 2);

            // Calculate gaps
            $totalGapScore = round($totalIndividualScore - $totalStandardScore, 2);
            $totalOriginalGapScore = round($totalIndividualScore - $totalOriginalStandardScore, 2);

            // Calculate percentage
            $percentage = $totalStandardScore > 0
                ? ($totalIndividualScore / $totalStandardScore) * 100
                : 0;

            // Determine conclusion using same logic as IndividualAssessmentService
            $conclusion = ConclusionService::getGapBasedConclusion($totalOriginalGapScore, $totalGapScore);

            $participantScores[] = [
                'participant_id' => $participantId,
                'participant_name' => $participantNames[$participantId] ?? '',
                'total_individual_score' => $totalIndividualScore,
                'total_standard_score' => $totalStandardScore,
                'total_original_standard_score' => $totalOriginalStandardScore,
                'total_gap_score' => $totalGapScore,
                'total_original_gap_score' => $totalOriginalGapScore,
                'percentage' => round($percentage, 2),
                'conclusion' => $conclusion,
                'potensi_weight' => $potensiWeight,
                'kompetensi_weight' => $kompetensiWeight,
            ];
        }

        // Sort by total_individual_score DESC, then participant_name ASC (tiebreaker)
        $rankings = collect($participantScores)
            ->sortBy([
                ['total_individual_score', 'desc'],
                ['participant_name', 'asc'],
            ])
            ->values();

        // Add rank number
        return $rankings->map(function ($row, $index) {
            return array_merge($row, ['rank' => $index + 1]);
        });
    }

    /**
     * Get specific participant's combined rank (Potensi + Kompetensi)
     *
     * @param  int  $participantId  Participant ID
     * @param  int  $eventId  Assessment event ID
     * @param  int  $positionFormationId  Position formation ID
     * @param  int  $templateId  Assessment template ID
     * @param  int  $tolerancePercentage  Tolerance percentage (0-100)
     * @return array|null Array with keys: rank, total, conclusion, percentage, potensi_weight, kompetensi_weight
     */
    public function getParticipantCombinedRank(
        int $participantId,
        int $eventId,
        int $positionFormationId,
        int $templateId,
        int $tolerancePercentage = 10
    ): ?array {
        // Get all combined rankings
        $rankings = $this->getCombinedRankings(
            $eventId,
            $positionFormationId,
            $templateId,
            $tolerancePercentage
        );

        if ($rankings->isEmpty()) {
            return null;
        }

        // Find participant's ranking
        $participantRanking = $rankings->firstWhere('participant_id', $participantId);

        if (! $participantRanking) {
            return null;
        }

        return [
            'rank' => $participantRanking['rank'],
            'total' => $rankings->count(),
            'conclusion' => $participantRanking['conclusion'],
            'percentage' => $participantRanking['percentage'],
            'potensi_weight' => $participantRanking['potensi_weight'],
            'kompetensi_weight' => $participantRanking['kompetensi_weight'],
        ];
    }
}
