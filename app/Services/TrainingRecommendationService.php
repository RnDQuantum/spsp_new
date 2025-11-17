<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Aspect;
use App\Models\AspectAssessment;
use Illuminate\Support\Collection;

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
        // Get aspect data
        $aspect = Aspect::with('categoryType', 'subAspects')->findOrFail($aspectId);

        // Get adjusted standard rating
        $adjustedStandardRating = $this->getAdjustedStandardRating(
            $aspect,
            $positionFormationId,
            $tolerancePercentage
        );

        // Get all aspect assessments, sorted by rating (lowest first)
        $assessments = AspectAssessment::query()
            ->with(['participant.positionFormation'])
            ->where('event_id', $eventId)
            ->where('position_formation_id', $positionFormationId)
            ->where('aspect_id', $aspectId)
            ->orderBy('individual_rating', 'asc')
            ->get();

        // Map to recommendation data
        return $assessments->map(function ($assessment, $index) use ($adjustedStandardRating) {
            $individualRating = (float) $assessment->individual_rating;
            $isRecommended = $individualRating < $adjustedStandardRating;

            return [
                'priority' => $index + 1,
                'participant_id' => $assessment->participant_id,
                'test_number' => $assessment->participant->test_number,
                'name' => $assessment->participant->name,
                'position' => $assessment->participant->positionFormation->name ?? '-',
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
        $standardService = app(DynamicStandardService::class);

        // Get all aspects for the template
        $aspects = Aspect::where('template_id', $templateId)
            ->with('categoryType', 'subAspects')
            ->orderBy('category_type_id', 'asc')
            ->orderBy('order', 'asc')
            ->get();

        // Get all assessments at once to avoid N+1
        $aspectIds = $aspects->pluck('id')->all();
        $assessmentsGrouped = AspectAssessment::query()
            ->where('event_id', $eventId)
            ->where('position_formation_id', $positionFormationId)
            ->whereIn('aspect_id', $aspectIds)
            ->get()
            ->groupBy('aspect_id');

        $aspectData = [];

        foreach ($aspects as $aspect) {
            // Check if aspect is active from session
            if (! $standardService->isAspectActive($templateId, $aspect->code)) {
                continue; // Skip inactive aspects
            }

            // Get assessments from grouped collection
            $assessments = $assessmentsGrouped->get($aspect->id, collect());

            if ($assessments->isEmpty()) {
                continue;
            }

            // Calculate average rating for this aspect
            $averageRating = $assessments->avg('individual_rating');

            // Get original standard rating (before tolerance)
            $originalStandardRating = $this->getOriginalStandardRating($aspect, $templateId);

            // Calculate adjusted standard based on tolerance
            $toleranceFactor = 1 - ($tolerancePercentage / 100);
            $adjustedStandardRating = $originalStandardRating * $toleranceFactor;

            // Calculate gap using adjusted standard
            $gap = $averageRating - $adjustedStandardRating;

            // Determine action (Pelatihan if gap < 0, Dipertahankan if gap >= 0)
            $action = $gap < 0 ? 'Pelatihan' : 'Dipertahankan';

            $aspectData[] = [
                'aspect_name' => $aspect->name,
                'original_standard_rating' => round($originalStandardRating, 2),
                'adjusted_standard_rating' => round($adjustedStandardRating, 2),
                'average_rating' => round($averageRating, 2),
                'gap' => round($gap, 2),
                'action' => $action,
            ];
        }

        // Sort by gap (ascending - most negative first)
        $collection = collect($aspectData)->sortBy('gap')->values();

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
        // Get aspect data
        $aspect = Aspect::with('categoryType', 'subAspects')->findOrFail($aspectId);

        // Get original standard rating (before tolerance)
        $originalStandardRating = $this->getOriginalStandardRating($aspect, $aspect->template_id);

        // Get adjusted standard rating (with tolerance)
        $adjustedStandardRating = $this->getAdjustedStandardRating(
            $aspect,
            $positionFormationId,
            $tolerancePercentage
        );

        // Get all aspect assessments
        $assessments = AspectAssessment::where('event_id', $eventId)
            ->where('position_formation_id', $positionFormationId)
            ->where('aspect_id', $aspectId)
            ->get();

        $totalParticipants = $assessments->count();
        $recommendedCount = 0;
        $notRecommendedCount = 0;
        $totalRating = 0;

        // Calculate summary statistics
        foreach ($assessments as $assessment) {
            $individualRating = (float) $assessment->individual_rating;
            $totalRating += $individualRating;

            // Participant is recommended for training if individual rating < adjusted standard
            if ($individualRating < $adjustedStandardRating) {
                $recommendedCount++;
            } else {
                $notRecommendedCount++;
            }
        }

        // Calculate average rating
        $averageRating = $totalParticipants > 0
            ? round($totalRating / $totalParticipants, 2)
            : 0;

        return [
            'total_participants' => $totalParticipants,
            'recommended_count' => $recommendedCount,
            'not_recommended_count' => $notRecommendedCount,
            'average_rating' => $averageRating,
            'standard_rating' => round($adjustedStandardRating, 2),
            'original_standard_rating' => round($originalStandardRating, 2),
            'recommended_percentage' => $totalParticipants > 0
                ? round(($recommendedCount / $totalParticipants) * 100, 2)
                : 0,
            'not_recommended_percentage' => $totalParticipants > 0
                ? round(($notRecommendedCount / $totalParticipants) * 100, 2)
                : 0,
        ];
    }

    /**
     * Get original standard rating for an aspect (before tolerance adjustment)
     *
     * For Potensi category: Calculate average from active sub-aspects
     * For Kompetensi category: Use aspect rating directly
     *
     * @param  Aspect  $aspect  Aspect model
     * @param  int  $templateId  Template ID
     * @return float Original standard rating
     */
    private function getOriginalStandardRating(Aspect $aspect, int $templateId): float
    {
        $standardService = app(DynamicStandardService::class);

        // Check if aspect belongs to Potensi category and has sub-aspects
        if ($aspect->categoryType->code === 'potensi' && $aspect->subAspects->count() > 0) {
            // Calculate average from active sub-aspects
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

        // For Kompetensi or aspects without sub-aspects, use aspect rating
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
