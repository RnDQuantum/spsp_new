<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Aspect;
use App\Models\AspectAssessment;
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

        // 🚀 OPTIMIZATION: PRE-COMPUTE STANDARDS ONCE (instead of 100K+ times!)
        // This reduces 100,000+ calls to DynamicStandardService to just 5-10 calls
        $standardsCache = $this->precomputeStandards($templateId, $activeAspectIds);

        // 🚀 OPTIMIZATION: Check if we need to load sub-aspects
        // If NO adjustments to active sub-aspects, we can use the pre-calculated individual_rating
        // stored in aspect_assessments, avoiding the massive N+1 / hydration overhead.
        $standardService = app(DynamicStandardService::class);
        $hasSubAspectAdjustments = $standardService->hasActiveSubAspectAdjustments($templateId);

        // Map aspect ID to Code for fast lookup (needed for raw query optimization)
        $aspectIdToCode = [];
        foreach ($standardsCache as $code => $data) {
            $aspectIdToCode[$data['id']] = $code;
        }

        // Build query
        $query = AspectAssessment::query()
            ->join('participants', 'participants.id', '=', 'aspect_assessments.participant_id')
            ->where('aspect_assessments.event_id', $eventId)
            ->where('aspect_assessments.position_formation_id', $positionFormationId)
            ->whereIn('aspect_assessments.aspect_id', $activeAspectIds)
            ->select('aspect_assessments.*', 'participants.name as participant_name')
            ->orderBy('participants.name');

        // Only eager load if necessary
        if ($hasSubAspectAdjustments) {
            $query->with(['aspect.subAspects', 'subAspectAssessments.subAspect']);
            $assessments = $query->get(); // Hydrate models if we need complex sub-aspect logic
        } else {
            // 🚀 MAX OPTIMIZATION: Use toBase() to skip Model Hydration completely
            // We get stdClass objects instead of AspectAssessment models.
            // This saves creating ~25,000 model instances when we only need simple values.
            $assessments = $query->toBase()->get();
        }

        if ($assessments->isEmpty()) {
            return collect();
        }

        // Group by participant and recalculate scores with adjusted weights
        $participantScores = [];

        foreach ($assessments as $assessment) {
            $participantId = $assessment->participant_id;

            // Handle object vs model property access
            // In raw object (stdClass), properties are accessed directly.
            // In Eloquent model, they are properly cast types.
            // However, AspectAssessment casts are 'decimal:2', so raw values might be strings.
            // We should cast them to float manually to be safe.

            if (! isset($participantScores[$participantId])) {
                $participantScores[$participantId] = [
                    'participant_id' => $participantId,
                    'participant_name' => $assessment->participant_name,
                    'individual_rating' => 0,
                    'individual_score' => 0,
                ];
            }

            // Resolve Aspect Code Helper
            $aspectCode = null;
            if ($hasSubAspectAdjustments && $assessment instanceof AspectAssessment) {
                 $aspectCode = $assessment->aspect->code;
            } else {
                 $aspectCode = $aspectIdToCode[$assessment->aspect_id] ?? null;
            }

            if (! $aspectCode || ! isset($standardsCache[$aspectCode])) {
                continue;
            }

            // 🚀 USE CACHE instead of calling service (OPTIMIZED!)
            $adjustedWeight = $standardsCache[$aspectCode]['weight'];

            // ✅ DATA-DRIVEN: Recalculate individual rating based on structure
            // Note: $assessment can be Model or stdClass here.
            
            if ($hasSubAspectAdjustments && $assessment instanceof AspectAssessment && $assessment->aspect->subAspects->isNotEmpty()) {
                // 🚀 USE CACHE: Pass pre-computed sub-aspects cache (OPTIMIZED!)
                // Only do this expensive calculation if we actually need to handle inactive sub-aspects
                $individualRating = $this->calculateIndividualRatingFromSubAspectsWithCache(
                    $assessment,
                    $standardsCache[$aspectCode]['sub_aspects']
                );
            } else {
                // No sub-aspects OR no adjustments: use direct rating from DB (FASTEST)
                // Cast to float because raw DB value might be string "3.50"
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

        // 🚀 OPTIMIZATION: Get adjusted standard values ONCE using cache
        $adjustedStandards = $this->calculateAdjustedStandards(
            $templateId,
            $categoryCode,
            $activeAspectIds,
            $standardsCache // Pass cache for optimization
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
     * @param  array|null  $standardsCache  Optional pre-computed standards cache for optimization
     * @return array Array with keys: standard_rating, standard_score
     */
    public function calculateAdjustedStandards(
        int $templateId,
        string $categoryCode,
        array $aspectIds,
        ?array $standardsCache = null
    ): array {
        // 🚀 OPTIMIZATION: Use pre-computed cache if provided
        if ($standardsCache !== null) {
            return $this->calculateAdjustedStandardsWithCache($standardsCache);
        }

        // Fallback to original method (for backward compatibility)
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

            // ✅ DATA-DRIVEN: Get aspect rating based on structure
            if ($aspect->subAspects->isNotEmpty()) {
                // Has sub-aspects: calculate from sub-aspects
                $aspectRating = $this->calculateAspectRatingFromSubAspects(
                    $aspect,
                    $templateId,
                    $standardService
                );
            } else {
                // No sub-aspects: get direct rating from DynamicStandardService
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
     * Calculate adjusted standard values using pre-computed cache (OPTIMIZED)
     *
     * @param  array  $standardsCache  Pre-computed standards cache
     * @return array Array with keys: standard_rating, standard_score
     */
    private function calculateAdjustedStandardsWithCache(array $standardsCache): array
    {
        $adjustedRating = 0;
        $adjustedScore = 0;

        foreach ($standardsCache as $aspectCode => $aspectData) {
            // 🚀 USE CACHE: Check if aspect is active
            if (! $aspectData['active']) {
                continue; // Skip inactive aspects
            }

            // 🚀 USE CACHE: Get weight and rating from cache
            $aspectWeight = $aspectData['weight'];

            // Calculate aspect rating from sub-aspects if exist
            if (! empty($aspectData['sub_aspects'])) {
                $subAspectRatingSum = 0;
                $activeSubAspectsCount = 0;

                foreach ($aspectData['sub_aspects'] as $subAspectCode => $subAspectData) {
                    // 🚀 USE CACHE: Check if sub-aspect is active
                    if (! $subAspectData['active']) {
                        continue;
                    }

                    $subAspectRatingSum += $subAspectData['rating'];
                    $activeSubAspectsCount++;
                }

                $aspectRating = $activeSubAspectsCount > 0
                    ? round($subAspectRatingSum / $activeSubAspectsCount, 2)
                    : $aspectData['rating'];
            } else {
                $aspectRating = $aspectData['rating'];
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
     * Calculate aspect rating from active sub-aspects (DATA-DRIVEN)
     *
     * @param  \App\Models\Aspect  $aspect  Aspect model
     * @param  int  $templateId  Template ID
     * @param  DynamicStandardService  $standardService  Standard service instance
     * @return float Calculated aspect rating
     */
    private function calculateAspectRatingFromSubAspects(
        $aspect,
        int $templateId,
        DynamicStandardService $standardService
    ): float {
        if ($aspect->subAspects->isEmpty()) {
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
     * Calculate individual rating from active sub-aspects (DATA-DRIVEN)
     *
     * @param  \App\Models\AspectAssessment  $assessment  Aspect assessment
     * @param  int  $templateId  Template ID
     * @param  DynamicStandardService  $standardService  Standard service instance
     * @return float Calculated individual rating
     */
    private function calculateIndividualRatingFromSubAspects(
        $assessment,
        int $templateId,
        DynamicStandardService $standardService
    ): float {
        $aspect = $assessment->aspect;

        if ($aspect->subAspects->isEmpty()) {
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
     * Calculate individual rating from sub-aspects using pre-computed cache
     *
     * This is the OPTIMIZED version that uses pre-computed standards cache
     * instead of calling DynamicStandardService for each sub-aspect.
     *
     * @param  \App\Models\AspectAssessment  $assessment  Aspect assessment
     * @param  array  $subAspectsCache  Pre-computed sub-aspect standards from precomputeStandards()
     * @return float Calculated individual rating
     */
    private function calculateIndividualRatingFromSubAspectsWithCache(
        $assessment,
        array $subAspectsCache
    ): float {
        $subAssessments = $assessment->subAspectAssessments;

        if ($subAssessments->isEmpty()) {
            return (float) $assessment->individual_rating;
        }

        $totalRating = 0;
        $activeCount = 0;

        foreach ($subAssessments as $subAssessment) {
            $subAspect = $subAssessment->subAspect;

            if (! $subAspect) {
                continue;
            }

            // USE CACHE instead of calling service (50-100x faster!)
            $isActive = $subAspectsCache[$subAspect->code]['active'] ?? true;

            if (! $isActive) {
                continue;
            }

            $totalRating += (float) $subAssessment->individual_rating;
            $activeCount++;
        }

        return $activeCount > 0 ? round($totalRating / $activeCount, 2) : 0.0;
    }

    /**
     * Pre-compute all standards ONCE per request
     *
     * This method avoids 100,000+ repeated lookups to DynamicStandardService by computing
     * all aspect and sub-aspect standards once and storing them in a PHP array.
     *
     * IMPORTANT: This is NOT persistent cache like Redis!
     * - Cache is created fresh for EACH request
     * - Always reads from DynamicStandardService (which reads session/DB)
     * - Cache destroyed after request ends (automatic PHP garbage collection)
     * - No risk of stale data
     *
     * @param  int  $templateId  Assessment template ID
     * @param  array  $activeAspectIds  Array of active aspect IDs
     * @return array Standards cache indexed by aspect code
     */
    private function precomputeStandards(int $templateId, array $activeAspectIds): array
    {
        $standardService = app(DynamicStandardService::class);
        $cache = [];

        // Get all aspects data ONCE
        $aspects = Aspect::whereIn('id', $activeAspectIds)
            ->with('subAspects')
            ->orderBy('order')
            ->get();

        foreach ($aspects as $aspect) {
            // Compute ONCE per aspect (instead of 20,000 times!)
            $cache[$aspect->code] = [
                'id' => $aspect->id,
                'weight' => $standardService->getAspectWeight($templateId, $aspect->code),
                'rating' => $standardService->getAspectRating($templateId, $aspect->code),
                'active' => $standardService->isAspectActive($templateId, $aspect->code),
                'sub_aspects' => [],
            ];

            // Pre-compute sub-aspects if exist
            if ($aspect->subAspects->isNotEmpty()) {
                foreach ($aspect->subAspects as $subAspect) {
                    $cache[$aspect->code]['sub_aspects'][$subAspect->code] = [
                        'id' => $subAspect->id,
                        'rating' => $standardService->getSubAspectRating($templateId, $subAspect->code),
                        'active' => $standardService->isSubAspectActive($templateId, $subAspect->code),
                    ];
                }
            }
        }

        return $cache;
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

        // Get active aspect IDs from DynamicStandardService (3-layer priority)
        // Will return empty array if all aspects are inactive
        return $standardService->getActiveAspectIds($templateId, $categoryCode);
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
