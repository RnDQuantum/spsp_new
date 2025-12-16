<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AspectAssessment;
use App\Models\CategoryType;
use App\Services\Cache\AspectCacheService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * TalentPoolService - Single Source of Truth for 9-Box Performance Matrix Calculations
 *
 * This service handles 9-Box Performance Matrix logic:
 * - Participant positioning in 9-box matrix
 * - Dynamic box boundaries based on statistics (avg ± std dev)
 * - Box statistics and distribution
 *
 * Works with:
 * - DynamicStandardService (for 3-layer priority system)
 * - AspectCacheService (for N+1 query optimization)
 *
 * Used by:
 * - TalentPool component
 * - PDF/Excel exports (future)
 */
class TalentPoolService
{
    /**
     * 🚀 OPTIMIZED: Cached position formation to prevent duplicate queries
     */
    private ?\App\Models\PositionFormation $cachedPosition = null;

    /**
     * Get complete 9-Box Performance Matrix data
     *
     * Returns:
     * - Participant positions with potensi/kinerja ratings
     * - Box boundaries (avg ± std dev for both axes)
     * - Box statistics (count and percentage per box)
     *
     * @param  int  $eventId  Assessment event ID
     * @param  int  $positionFormationId  Position formation ID
     * @return array Complete 9-box matrix data
     */
    public function getNineBoxMatrixData(
        int $eventId,
        int $positionFormationId
    ): array {
        // 🚀 PERFORMANCE: Load position ONCE and cache it
        $this->loadPosition($positionFormationId);

        if (! $this->cachedPosition) {
            return [
                'participants' => collect([]),
                'box_boundaries' => null,
                'box_statistics' => [],
                'total_participants' => 0,
            ];
        }

        // 🚀 PERFORMANCE: Build config hash ONCE with cached position
        $configHash = $this->buildConfigHash($eventId, $positionFormationId);
        $matrixCacheKey = "talent_pool_matrix:{$eventId}:{$positionFormationId}:{$configHash}";

        return Cache::remember($matrixCacheKey, 7200, function () use ($eventId, $positionFormationId) {
            // Get participants position data
            $participantsData = $this->getParticipantsPositionData($eventId, $positionFormationId);

            if ($participantsData['participants']->isEmpty()) {
                return [
                    'participants' => collect([]),
                    'box_boundaries' => null,
                    'box_statistics' => [],
                    'total_participants' => 0,
                ];
            }

            // Calculate box boundaries from participants data
            $boxBoundaries = $this->calculateBoxBoundaries($participantsData['participants']);

            // Classify participants into boxes
            $participantsWithBoxes = $this->classifyParticipantsToBoxes(
                $participantsData['participants'],
                $boxBoundaries
            );

            // Calculate box statistics
            $boxStatistics = $this->calculateBoxStatistics($participantsWithBoxes);

            return [
                'participants' => $participantsWithBoxes,
                'box_boundaries' => $boxBoundaries,
                'box_statistics' => $boxStatistics,
                'total_participants' => $participantsWithBoxes->count(),
            ];
        });
    }

    /**
     * 🚀 OPTIMIZED: Load and cache position formation to prevent duplicate queries
     */
    private function loadPosition(int $positionFormationId): void
    {
        if ($this->cachedPosition && $this->cachedPosition->id === $positionFormationId) {
            return; // Already loaded
        }

        $this->cachedPosition = \App\Models\PositionFormation::find($positionFormationId);

        // 🚀 PERFORMANCE: Preload aspects for this template to prevent N+1 queries
        if ($this->cachedPosition) {
            AspectCacheService::preloadByTemplate($this->cachedPosition->template_id);
        }
    }

    /**
     * Get participants with their potensi and kinerja ratings
     *
     * @param  int  $eventId  Assessment event ID
     * @param  int  $positionFormationId  Position formation ID
     * @return array Participants data with category ratings
     */
    public function getParticipantsPositionData(
        int $eventId,
        int $positionFormationId
    ): array {
        // 🚀 PERFORMANCE: Ensure position is loaded (prevents duplicate queries)
        $this->loadPosition($positionFormationId);

        // Build config hash for cache invalidation (respects 3-layer priority)
        $configHash = $this->buildConfigHash($eventId, $positionFormationId);

        $cacheKey = "talent_pool_participants:{$eventId}:{$positionFormationId}:{$configHash}";

        // 🚀 PERFORMANCE: Extended cache TTL (2 jam) - data jarang berubah setelah assessment selesai
        return Cache::remember($cacheKey, 7200, function () use ($eventId, $positionFormationId) {
            // Get category types for the template
            $categoryTypes = $this->getCategoryTypes($eventId, $positionFormationId);

            if ($categoryTypes->isEmpty()) {
                return ['participants' => collect([]), 'category_types' => collect([])];
            }

            // Identify potensi and kompetensi categories
            $potensiCategory = $categoryTypes->firstWhere('code', 'potensi');
            $kompetensiCategory = $categoryTypes->firstWhere('code', 'kompetensi');

            if (! $potensiCategory || ! $kompetensiCategory) {
                return ['participants' => collect([]), 'category_types' => collect([])];
            }

            // Get all aspect assessments for this event and position
            $assessments = $this->getAspectAssessments($eventId, $positionFormationId);

            if ($assessments->isEmpty()) {
                return ['participants' => collect([]), 'category_types' => collect([])];
            }

            // Group assessments by participant and calculate category averages
            $participantsData = $this->calculateCategoryAverages(
                $assessments,
                $potensiCategory->id,
                $kompetensiCategory->id
            );

            return [
                'participants' => $participantsData,
                'category_types' => $categoryTypes,
            ];
        });
    }

    /**
     * Calculate box boundaries using statistics (avg ± std dev)
     *
     * @param  Collection  $participants  Participants with potensi/kinerja ratings
     * @return array Box boundaries for both axes
     */
    private function calculateBoxBoundaries(Collection $participants): array
    {
        $potensiRatings = $participants->pluck('potensi_rating')->filter();
        $kinerjaRatings = $participants->pluck('kinerja_rating')->filter();

        if ($potensiRatings->isEmpty() || $kinerjaRatings->isEmpty()) {
            return [];
        }

        // Calculate statistics for potensi
        $potensiAvg = $potensiRatings->avg();
        $potensiStdDev = $this->calculateStandardDeviation($potensiRatings);

        // Calculate statistics for kinerja
        $kinerjaAvg = $kinerjaRatings->avg();
        $kinerjaStdDev = $this->calculateStandardDeviation($kinerjaRatings);

        return [
            'potensi' => [
                'avg' => round($potensiAvg, 2),
                'std_dev' => round($potensiStdDev, 2),
                'lower_bound' => round($potensiAvg - $potensiStdDev, 2),
                'upper_bound' => round($potensiAvg + $potensiStdDev, 2),
            ],
            'kinerja' => [
                'avg' => round($kinerjaAvg, 2),
                'std_dev' => round($kinerjaStdDev, 2),
                'lower_bound' => round($kinerjaAvg - $kinerjaStdDev, 2),
                'upper_bound' => round($kinerjaAvg + $kinerjaStdDev, 2),
            ],
        ];
    }

    /**
     * Classify participants into 9 boxes based on boundaries
     *
     * @param  Collection  $participants  Participants with potensi/kinerja ratings
     * @param  array  $boundaries  Box boundaries
     * @return Collection Participants with box classification
     */
    private function classifyParticipantsToBoxes(
        Collection $participants,
        array $boundaries
    ): Collection {
        $boxLabels = $this->getBoxLabels();

        return $participants->map(function ($participant) use ($boundaries, $boxLabels) {
            $potensiLevel = $this->determineLevel(
                $participant['potensi_rating'],
                $boundaries['potensi']['lower_bound'],
                $boundaries['potensi']['upper_bound']
            );

            $kinerjaLevel = $this->determineLevel(
                $participant['kinerja_rating'],
                $boundaries['kinerja']['lower_bound'],
                $boundaries['kinerja']['upper_bound']
            );

            $boxNumber = $this->mapLevelsToBox($potensiLevel, $kinerjaLevel);

            return array_merge($participant, [
                'box_number' => $boxNumber,
                'box_label' => $boxLabels[$boxNumber],
            ]);
        });
    }

    /**
     * Calculate statistics for each box
     *
     * @param  Collection  $participants  Participants with box classification
     * @return array Box statistics (count and percentage)
     */
    private function calculateBoxStatistics(Collection $participants): array
    {
        $totalParticipants = $participants->count();

        if ($totalParticipants === 0) {
            return [];
        }

        // Group participants by box
        $boxCounts = $participants->groupBy('box_number')->map->count();

        $boxStatistics = [];

        for ($box = 1; $box <= 9; $box++) {
            $count = $boxCounts->get($box, 0);
            $percentage = $totalParticipants > 0 ? round(($count / $totalParticipants) * 100, 1) : 0;

            $boxStatistics[$box] = [
                'count' => $count,
                'percentage' => $percentage,
            ];
        }

        return $boxStatistics;
    }

    /**
     * Get category types for the event and position
     *
     * @param  int  $eventId  Assessment event ID
     * @param  int  $positionFormationId  Position formation ID
     * @return Collection Category types
     */
    private function getCategoryTypes(int $eventId, int $positionFormationId): Collection
    {
        // 🚀 PERFORMANCE: Use cached position instead of querying again
        $this->loadPosition($positionFormationId);

        if (! $this->cachedPosition) {
            return collect([]);
        }

        return CategoryType::where('template_id', $this->cachedPosition->template_id)
            ->orderBy('code')
            ->get();
    }

    /**
     * Get aspect assessments for the event and position
     *
     * @param  int  $eventId  Assessment event ID
     * @param  int  $positionFormationId  Position formation ID
     * @return Collection Aspect assessments
     */
    private function getAspectAssessments(int $eventId, int $positionFormationId): Collection
    {
        // 🚀 PERFORMANCE: Optimized query with database-level aggregation
        // Instead of fetching all raw records, calculate averages at SQL level
        return AspectAssessment::query()
            ->join('participants', 'participants.id', '=', 'aspect_assessments.participant_id')
            ->join('aspects', 'aspects.id', '=', 'aspect_assessments.aspect_id')
            ->join('category_types', 'category_types.id', '=', 'aspects.category_type_id')
            ->where('aspect_assessments.event_id', $eventId)
            ->where('aspect_assessments.position_formation_id', $positionFormationId)
            ->whereIn('category_types.code', ['potensi', 'kompetensi']) // Only fetch needed categories
            ->select(
                'aspect_assessments.participant_id',
                'participants.name',
                'participants.test_number',
                'category_types.code as category_code',
                DB::raw('AVG(aspect_assessments.individual_rating) as rating')
            )
            ->groupBy(
                'aspect_assessments.participant_id',
                'participants.name',
                'participants.test_number',
                'category_types.code'
            )
            ->orderBy('aspect_assessments.participant_id')
            ->toBase()
            ->get();
    }

    /**
     * Calculate category averages for each participant
     *
     * @param  Collection  $assessments  Aspect assessments
     * @param  int  $potensiCategoryId  Potensi category ID
     * @param  int  $kompetensiCategoryId  Kompetensi category ID
     * @return Collection Participants with category averages
     */
    private function calculateCategoryAverages(
        Collection $assessments,
        int $potensiCategoryId,
        int $kompetensiCategoryId
    ): Collection {
        // 🚀 PERFORMANCE: Optimized processing for pre-aggregated data
        // Data is now already grouped by participant and category at SQL level
        $assessmentsByParticipant = $assessments->groupBy('participant_id');

        return $assessmentsByParticipant->map(function ($participantAssessments) {
            $firstAssessment = $participantAssessments->first();

            // Extract potensi and kinerja ratings from pre-calculated data
            $potensiRating = 0;
            $kinerjaRating = 0;

            foreach ($participantAssessments as $assessment) {
                if ($assessment->category_code === 'potensi') {
                    $potensiRating = round((float) $assessment->rating, 2);
                } elseif ($assessment->category_code === 'kompetensi') {
                    $kinerjaRating = round((float) $assessment->rating, 2);
                }
            }

            return [
                'participant_id' => $firstAssessment->participant_id,
                'name' => $firstAssessment->name,
                'test_number' => $firstAssessment->test_number,
                'potensi_rating' => $potensiRating,
                'kinerja_rating' => $kinerjaRating,
            ];
        })->values();
    }

    /**
     * Calculate standard deviation
     *
     * @param  Collection  $values  Numeric values
     * @return float Standard deviation
     */
    private function calculateStandardDeviation(Collection $values): float
    {
        if ($values->isEmpty()) {
            return 0;
        }

        $mean = $values->avg();
        $squaredDiffs = $values->map(function ($value) use ($mean) {
            return pow($value - $mean, 2);
        });

        $variance = $squaredDiffs->avg();

        return sqrt($variance);
    }

    /**
     * Determine level (rendah/sedang/tinggi) based on boundaries
     *
     * @param  float  $value  Rating value
     * @param  float  $lowerBound  Lower boundary
     * @param  float  $upperBound  Upper boundary
     * @return string Level (rendah/sedang/tinggi)
     */
    private function determineLevel(float $value, float $lowerBound, float $upperBound): string
    {
        if ($value < $lowerBound) {
            return 'rendah';
        } elseif ($value > $upperBound) {
            return 'tinggi';
        } else {
            return 'sedang';
        }
    }

    /**
     * Map potensi and kinerja levels to box number
     *
     * @param  string  $potensiLevel  Potensi level (rendah/sedang/tinggi)
     * @param  string  $kinerjaLevel  Kinerja level (rendah/sedang/tinggi)
     * @return int Box number (1-9)
     */
    private function mapLevelsToBox(string $potensiLevel, string $kinerjaLevel): int
    {
        $boxMap = [
            'rendah' => [
                'rendah' => 1,
                'sedang' => 2,
                'tinggi' => 3,
            ],
            'sedang' => [
                'rendah' => 4,
                'sedang' => 5,
                'tinggi' => 6,
            ],
            'tinggi' => [
                'rendah' => 7,
                'sedang' => 8,
                'tinggi' => 9,
            ],
        ];

        return $boxMap[$potensiLevel][$kinerjaLevel] ?? 5;
    }

    /**
     * Get box labels for all 9 boxes
     *
     * @return array Box labels indexed by box number
     */
    private function getBoxLabels(): array
    {
        return [
            1 => 'Need Attention',
            2 => 'Steady Performer',
            3 => 'Inconsistent',
            4 => 'Solid Performer',
            5 => 'Core Performer',
            6 => 'Enigma',
            7 => 'Potential Star',
            8 => 'High Potential',
            9 => 'Star Performer',
        ];
    }

    /**
     * 🚀 OPTIMIZED: Cached config hash to prevent redundant calculations
     */
    private ?string $cachedConfigHash = null;

    /**
     * Build config hash for cache invalidation (respects 3-layer priority)
     *
     * @param  int  $eventId  Assessment event ID
     * @param  int  $positionFormationId  Position formation ID
     * @return string Config hash
     */
    private function buildConfigHash(int $eventId, int $positionFormationId): string
    {
        // 🚀 PERFORMANCE: Return cached hash if already calculated
        if ($this->cachedConfigHash !== null) {
            return $this->cachedConfigHash;
        }

        // 🚀 PERFORMANCE: Use cached position instead of querying again
        $this->loadPosition($positionFormationId);

        if (! $this->cachedPosition) {
            return md5('no_position');
        }

        // 🚀 PERFORMANCE: Simpler hash based on session adjustments only
        // Instead of querying all aspects and checking each one, we use the session data directly
        $templateId = $this->cachedPosition->template_id;
        $sessionAdjustments = session()->get("standard_adjustment.{$templateId}", []);
        $selectedStandard = session()->get("selected_standard.{$templateId}");

        $this->cachedConfigHash = md5(json_encode([
            'template_id' => $templateId,
            'session_id' => session()->getId(),
            'selected_standard' => $selectedStandard,
            'adjustments' => $sessionAdjustments,
        ]));

        return $this->cachedConfigHash;
    }
}
