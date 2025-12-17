<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Aspect;
use App\Models\AspectAssessment;
use App\Services\Cache\AspectCacheService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * TrainingRecommendationService - Single Source of Truth for Training Recommendation Calculations
 *
 * This service handles training recommendation logic:
 * - Participant training recommendations (based on gap analysis)
 * - Aspect priority for training (sorted by gap)
 * - Training summary statistics
 *
 * Works with:
 * - IndividualAssessmentService (for aspect assessments)
 * - DynamicStandardService (for session adjustments)
 *
 * Used by:
 * - TrainingRecommendation component
 * - PDF/Excel exports (future)
 */
class TrainingRecommendationService
{
    /**
     * Get participants training recommendation for a specific aspect
     *
     * Logic:
     * - If individual_rating < adjusted_standard_rating → Recommended for Training
     * - If individual_rating >= adjusted_standard_rating → Not Recommended
     *
     * Returns ALL participants (component will handle pagination)
     *
     * @param  int  $eventId  Assessment event ID
     * @param  int  $positionFormationId  Position formation ID
     * @param  int  $aspectId  Aspect ID
     * @param  int  $tolerancePercentage  Tolerance percentage (0-100)
     * @return Collection Collection with keys: priority, participant_id, test_number, name, position, rating, is_recommended, statement
     */
    public function getParticipantsRecommendation(
        int $eventId,
        int $positionFormationId,
        int $aspectId,
        int $tolerancePercentage = 10
    ): Collection {
        // 🚀 OPTIMIZATION: Smart caching with lightweight query + lazy loading
        //
        // STRATEGY:
        // 1. Cache lightweight data (IDs + ratings only)
        // 2. Component handles pagination (slice)
        // 3. Component hydrates participant details ONLY for visible items
        //
        // BEFORE: Load 4,944 participants with relationships → 3.98s
        // AFTER: Load 4,944 ratings only → ~200ms (cached: ~50ms)

        $standardService = app(DynamicStandardService::class);

        // Get aspect data (use cache service to avoid N+1)
        $aspect = AspectCacheService::getById($aspectId);

        if (! $aspect) {
            throw new \Exception("Aspect with ID {$aspectId} not found");
        }

        // Build config hash for cache invalidation
        $aspectRating = $standardService->getAspectRating($aspect->template_id, $aspect->code);

        $subAspectRatings = [];
        if ($aspect->subAspects->isNotEmpty()) {
            foreach ($aspect->subAspects as $subAspect) {
                if ($standardService->isSubAspectActive($aspect->template_id, $subAspect->code)) {
                    $subAspectRatings[$subAspect->code] = $standardService->getSubAspectRating($aspect->template_id, $subAspect->code);
                }
            }
        }

        $configHash = md5(json_encode([
            'aspect_code' => $aspect->code,
            'aspect_rating' => $aspectRating,
            'sub_aspect_ratings' => $subAspectRatings,
            'session' => session()->getId(),
        ]));

        $cacheKey = "training_participants:{$aspectId}:{$eventId}:{$positionFormationId}:{$configHash}";

        // Cache lightweight assessment data
        $lightweightAssessments = Cache::remember($cacheKey, 60, function () use ($eventId, $positionFormationId, $aspectId) {
            // 🚀 OPTIMIZATION: Load only IDs and ratings (no relationships)
            return AspectAssessment::query()
                ->join('participants', 'participants.id', '=', 'aspect_assessments.participant_id')
                ->where('aspect_assessments.event_id', $eventId)
                ->where('aspect_assessments.position_formation_id', $positionFormationId)
                ->where('aspect_assessments.aspect_id', $aspectId)
                ->select(
                    'aspect_assessments.id',
                    'aspect_assessments.participant_id',
                    'aspect_assessments.individual_rating',
                    'participants.test_number',
                    'participants.name',
                    'participants.position_formation_id'
                )
                ->orderBy('aspect_assessments.individual_rating', 'asc')
                ->toBase() // Skip model hydration
                ->get();
        });

        // Get adjusted standard rating (with tolerance applied)
        $adjustedStandardRating = $this->getAdjustedStandardRating(
            $aspect,
            $positionFormationId,
            $tolerancePercentage
        );

        // Map to recommendation data (lightweight - no DB queries)
        return $lightweightAssessments->map(function ($assessment, $index) use ($adjustedStandardRating) {
            $individualRating = (float) $assessment->individual_rating;
            $isRecommended = $individualRating < $adjustedStandardRating;

            return [
                'priority' => $index + 1,
                'participant_id' => $assessment->participant_id,
                'test_number' => $assessment->test_number,
                'name' => $assessment->name,
                'position_formation_id' => $assessment->position_formation_id,
                'rating' => $individualRating,
                'is_recommended' => $isRecommended,
                'statement' => $isRecommended ? 'Recommended' : 'Not Recommended',
            ];
        });
    }

    /**
     * Get aspect priority for training (sorted by gap)
     *
     * Returns all aspects for a template, sorted by gap (most negative first).
     * Aspects with negative gap need training priority.
     *
     * @param  int  $eventId  Assessment event ID
     * @param  int  $positionFormationId  Position formation ID
     * @param  int  $templateId  Assessment template ID
     * @param  int  $tolerancePercentage  Tolerance percentage (0-100)
     * @return Collection Collection with keys: priority, aspect_name, original_standard_rating, adjusted_standard_rating, average_rating, gap, action
     */
    public function getAspectTrainingPriority(
        int $eventId,
        int $positionFormationId,
        int $templateId,
        int $tolerancePercentage = 10
    ): Collection {
        // 🚀 OPTIMIZATION: Smart caching with lightweight query
        //
        // STRATEGY:
        // 1. Preload aspects using AspectCacheService
        // 2. Use lightweight query for assessments (select specific columns only)
        // 3. Cache results with config hash
        // 4. Apply tolerance after cache for instant UX

        $standardService = app(DynamicStandardService::class);

        // Preload aspects to avoid N+1
        AspectCacheService::preloadByTemplate($templateId);

        // Get all aspects for the template
        $aspects = Aspect::where('template_id', $templateId)
            ->with('categoryType', 'subAspects')
            ->orderBy('category_type_id', 'asc')
            ->orderBy('order', 'asc')
            ->get();

        // Filter active aspects
        $activeAspects = $aspects->filter(function ($aspect) use ($standardService, $templateId) {
            return $standardService->isAspectActive($templateId, $aspect->code);
        });

        if ($activeAspects->isEmpty()) {
            return collect();
        }

        // Build config hash for cache invalidation
        $aspectRatings = [];
        foreach ($activeAspects as $aspect) {
            $aspectRatings[$aspect->code] = $standardService->getAspectRating($templateId, $aspect->code);
        }

        $configHash = md5(json_encode([
            'template_id' => $templateId,
            'aspect_ratings' => $aspectRatings,
            'session' => session()->getId(),
        ]));

        $cacheKey = "training_priority:{$templateId}:{$eventId}:{$positionFormationId}:{$configHash}";

        // Cache aspect priority data (without tolerance applied)
        $rawPriorityData = Cache::remember($cacheKey, 60, function () use ($eventId, $positionFormationId, $activeAspects, $templateId) {
            $aspectIds = $activeAspects->pluck('id')->all();

            // 🚀 OPTIMIZATION: Use lightweight query with specific columns only
            $assessmentsGrouped = AspectAssessment::query()
                ->where('event_id', $eventId)
                ->where('position_formation_id', $positionFormationId)
                ->whereIn('aspect_id', $aspectIds)
                ->select('id', 'aspect_id', 'individual_rating')
                ->toBase() // Skip model hydration
                ->get()
                ->groupBy('aspect_id');

            $aspectData = [];

            foreach ($activeAspects as $aspect) {
                // Get assessments from grouped collection
                $assessments = $assessmentsGrouped->get($aspect->id, collect());

                if ($assessments->isEmpty()) {
                    continue;
                }

                // Calculate average rating for this aspect
                $totalRating = 0;
                foreach ($assessments as $assessment) {
                    $totalRating += (float) $assessment->individual_rating;
                }
                $averageRating = $totalRating / $assessments->count();

                // Get original standard rating (before tolerance)
                $originalStandardRating = $this->getOriginalStandardRating($aspect, $templateId);

                $aspectData[] = [
                    'aspect_id' => $aspect->id,
                    'aspect_name' => $aspect->name,
                    'original_standard_rating' => $originalStandardRating,
                    'average_rating' => $averageRating,
                ];
            }

            return $aspectData;
        });

        // Apply tolerance adjustment (not cached for instant UX)
        $aspectDataWithTolerance = [];
        $toleranceFactor = 1 - ($tolerancePercentage / 100);

        foreach ($rawPriorityData as $data) {
            $adjustedStandardRating = $data['original_standard_rating'] * $toleranceFactor;
            $gap = $data['average_rating'] - $adjustedStandardRating;
            $action = $gap < 0 ? 'Pelatihan' : 'Dipertahankan';

            $aspectDataWithTolerance[] = [
                'aspect_id' => $data['aspect_id'],
                'aspect_name' => $data['aspect_name'],
                'original_standard_rating' => round($data['original_standard_rating'], 2),
                'adjusted_standard_rating' => round($adjustedStandardRating, 2),
                'average_rating' => round($data['average_rating'], 2),
                'gap' => round($gap, 2),
                'action' => $action,
            ];
        }

        // Sort by gap (ascending - most negative first)
        $collection = collect($aspectDataWithTolerance)->sortBy('gap')->values();

        // Add priority number
        return $collection->map(function ($item, $index) {
            $item['priority'] = $index + 1;

            return $item;
        });
    }

    /**
     * Get training summary statistics for a specific aspect
     *
     * @param  int  $eventId  Assessment event ID
     * @param  int  $positionFormationId  Position formation ID
     * @param  int  $aspectId  Aspect ID
     * @param  int  $tolerancePercentage  Tolerance percentage (0-100)
     * @return array Array with keys: total_participants, recommended_count, not_recommended_count, average_rating, standard_rating, original_standard_rating
     */
    public function getTrainingSummary(
        int $eventId,
        int $positionFormationId,
        int $aspectId,
        int $tolerancePercentage = 10
    ): array {
        // 🚀 OPTIMIZATION: Smart caching with 3-layer priority support (60s TTL)
        //
        // CACHE STRATEGY:
        // - Cache key includes: event, position, aspect, config hash, and session ID
        // - Config hash based on aspect ratings from DynamicStandardService
        // - This respects 3-layer priority automatically:
        //
        // ✅ Layer 1 (Session Adjustment): Config hash changes → Cache miss → Re-compute
        // ✅ Layer 2 (Custom Standard): Config hash changes → Cache miss → Re-compute
        // ✅ Layer 3 (Quantum Default): Config hash stable → Cache hit (until TTL)
        //
        // TOLERANCE HANDLING:
        // - Tolerance is NOT in cache key (applied after cache for instant UX)

        $standardService = app(DynamicStandardService::class);

        // Get aspect data (use cache service to avoid N+1)
        $aspect = AspectCacheService::getById($aspectId);

        if (! $aspect) {
            throw new \Exception("Aspect with ID {$aspectId} not found");
        }

        // Build config hash from aspect rating for cache invalidation
        $aspectRating = $standardService->getAspectRating($aspect->template_id, $aspect->code);

        // Include sub-aspect ratings if aspect has sub-aspects
        $subAspectRatings = [];
        if ($aspect->subAspects->isNotEmpty()) {
            foreach ($aspect->subAspects as $subAspect) {
                if ($standardService->isSubAspectActive($aspect->template_id, $subAspect->code)) {
                    $subAspectRatings[$subAspect->code] = $standardService->getSubAspectRating($aspect->template_id, $subAspect->code);
                }
            }
        }

        $configHash = md5(json_encode([
            'aspect_code' => $aspect->code,
            'aspect_rating' => $aspectRating,
            'sub_aspect_ratings' => $subAspectRatings,
            'session' => session()->getId(), // Isolates per-user session adjustments
        ]));

        $cacheKey = "training_summary:{$aspectId}:{$eventId}:{$positionFormationId}:{$configHash}";

        // Cache the summary (without tolerance applied)
        $rawSummary = Cache::remember($cacheKey, 60, function () use ($eventId, $positionFormationId, $aspectId, $aspect) {
            // 🚀 OPTIMIZATION: Use lightweight query with selective columns
            // Only select columns we need for calculations
            $assessments = AspectAssessment::query()
                ->where('event_id', $eventId)
                ->where('position_formation_id', $positionFormationId)
                ->where('aspect_id', $aspectId)
                ->select('id', 'participant_id', 'individual_rating')
                ->toBase() // Skip model hydration for performance
                ->get();

            $totalParticipants = $assessments->count();
            $totalRating = 0;

            // Calculate basic statistics (without tolerance)
            foreach ($assessments as $assessment) {
                $totalRating += (float) $assessment->individual_rating;
            }

            $averageRating = $totalParticipants > 0
                ? $totalRating / $totalParticipants
                : 0;

            // Get original standard rating (before tolerance)
            $originalStandardRating = $this->getOriginalStandardRating($aspect, $aspect->template_id);

            return [
                'total_participants' => $totalParticipants,
                'average_rating' => $averageRating,
                'original_standard_rating' => $originalStandardRating,
                'assessments_ratings' => $assessments->pluck('individual_rating')->all(), // Store for tolerance calculation
            ];
        });

        // Apply tolerance adjustment (not cached for instant UX)
        $originalStandardRating = $rawSummary['original_standard_rating'];
        $toleranceFactor = 1 - ($tolerancePercentage / 100);
        $adjustedStandardRating = $originalStandardRating * $toleranceFactor;

        // Calculate recommended counts based on tolerance
        $recommendedCount = 0;
        $notRecommendedCount = 0;

        foreach ($rawSummary['assessments_ratings'] as $rating) {
            if ($rating < $adjustedStandardRating) {
                $recommendedCount++;
            } else {
                $notRecommendedCount++;
            }
        }

        return [
            'total_participants' => $rawSummary['total_participants'],
            'recommended_count' => $recommendedCount,
            'not_recommended_count' => $notRecommendedCount,
            'average_rating' => round($rawSummary['average_rating'], 2),
            'standard_rating' => round($adjustedStandardRating, 2),
            'original_standard_rating' => round($originalStandardRating, 2),
            'recommended_percentage' => $rawSummary['total_participants'] > 0
                ? round(($recommendedCount / $rawSummary['total_participants']) * 100, 2)
                : 0,
            'not_recommended_percentage' => $rawSummary['total_participants'] > 0
                ? round(($notRecommendedCount / $rawSummary['total_participants']) * 100, 2)
                : 0,
        ];
    }

    /**
     * Get original standard rating for an aspect (before tolerance adjustment)
     *
     * Logic (DATA-DRIVEN):
     * - If aspect has sub-aspects: calculate average from active sub-aspects
     * - If aspect has no sub-aspects: use aspect rating directly
     *
     * @param  Aspect  $aspect  Aspect model
     * @param  int  $templateId  Template ID
     * @return float Original standard rating
     */
    private function getOriginalStandardRating(Aspect $aspect, int $templateId): float
    {
        $standardService = app(DynamicStandardService::class);

        // ✅ DATA-DRIVEN: Check if aspect has sub-aspects
        if ($aspect->subAspects->isNotEmpty()) {
            // Has sub-aspects: Calculate average from active sub-aspects
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
                return $subAspectRatingSum / $activeSubAspectsCount;
            }
        }

        // No sub-aspects: use aspect rating directly
        return $standardService->getAspectRating($templateId, $aspect->code);
    }

    /**
     * Get adjusted standard rating with tolerance applied
     *
     * @param  Aspect  $aspect  Aspect model
     * @param  int  $positionFormationId  Position formation ID
     * @param  int  $tolerancePercentage  Tolerance percentage (0-100)
     * @return float Adjusted standard rating
     */
    private function getAdjustedStandardRating(
        Aspect $aspect,
        int $positionFormationId,
        int $tolerancePercentage
    ): float {
        // Get original standard rating
        $originalStandardRating = $this->getOriginalStandardRating($aspect, $aspect->template_id);

        // Calculate adjusted standard based on tolerance
        $toleranceFactor = 1 - ($tolerancePercentage / 100);

        return $originalStandardRating * $toleranceFactor;
    }
}
