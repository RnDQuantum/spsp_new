<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Aspect;
use App\Models\AspectAssessment;
use App\Models\CategoryType;
use App\Services\Cache\AspectCacheService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

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
     * Get complete 9-Box Performance Matrix data
     *
     * Returns:
     * - Participant positions with potensi/kinerja ratings
     * - Box boundaries (avg ± std dev for both axes)
     * - Box statistics (count and percentage per box)
     *
     * @param int $eventId Assessment event ID
     * @param int $positionFormationId Position formation ID
     * @return array Complete 9-box matrix data
     */
    public function getNineBoxMatrixData(
        int $eventId,
        int $positionFormationId
    ): array {
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
    }

    /**
     * Get participants with their potensi and kinerja ratings
     *
     * @param int $eventId Assessment event ID
     * @param int $positionFormationId Position formation ID
     * @return array Participants data with category ratings
     */
    public function getParticipantsPositionData(
        int $eventId,
        int $positionFormationId
    ): array {
        // Build config hash for cache invalidation (respects 3-layer priority)
        $configHash = $this->buildConfigHash($eventId, $positionFormationId);

        $cacheKey = "talent_pool_participants:{$eventId}:{$positionFormationId}:{$configHash}";

        // 🚀 PERFORMANCE: Cache dengan TTL lebih pendek untuk data dinamis
        return Cache::remember($cacheKey, 30, function () use ($eventId, $positionFormationId) {
            // Get category types for the template
            $categoryTypes = $this->getCategoryTypes($eventId, $positionFormationId);

            if ($categoryTypes->isEmpty()) {
                return ['participants' => collect([]), 'category_types' => collect([])];
            }

            // Identify potensi and kompetensi categories
            $potensiCategory = $categoryTypes->firstWhere('code', 'potensi');
            $kompetensiCategory = $categoryTypes->firstWhere('code', 'kompetensi');

            if (!$potensiCategory || !$kompetensiCategory) {
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
                'category_types' => $categoryTypes
            ];
        });
    }

    /**
     * Calculate box boundaries using statistics (avg ± std dev)
     *
     * @param Collection $participants Participants with potensi/kinerja ratings
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
     * @param Collection $participants Participants with potensi/kinerja ratings
     * @param array $boundaries Box boundaries
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
     * @param Collection $participants Participants with box classification
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
     * @param int $eventId Assessment event ID
     * @param int $positionFormationId Position formation ID
     * @return Collection Category types
     */
    private function getCategoryTypes(int $eventId, int $positionFormationId): Collection
    {
        // Get template ID from position formation
        $position = \App\Models\PositionFormation::find($positionFormationId);

        if (!$position) {
            return collect([]);
        }

        return CategoryType::where('template_id', $position->template_id)
            ->orderBy('code')
            ->get();
    }

    /**
     * Get aspect assessments for the event and position
     *
     * @param int $eventId Assessment event ID
     * @param int $positionFormationId Position formation ID
     * @return Collection Aspect assessments
     */
    private function getAspectAssessments(int $eventId, int $positionFormationId): Collection
    {
        return AspectAssessment::query()
            ->join('participants', 'participants.id', '=', 'aspect_assessments.participant_id')
            ->join('aspects', 'aspects.id', '=', 'aspect_assessments.aspect_id')
            ->where('aspect_assessments.event_id', $eventId)
            ->where('aspect_assessments.position_formation_id', $positionFormationId)
            ->select(
                'aspect_assessments.participant_id',
                'aspect_assessments.aspect_id',
                'aspect_assessments.individual_rating',
                'participants.name',
                'participants.test_number',
                'aspects.category_type_id'
            )
            ->toBase()
            ->get();
    }

    /**
     * Calculate category averages for each participant
     *
     * @param Collection $assessments Aspect assessments
     * @param int $potensiCategoryId Potensi category ID
     * @param int $kompetensiCategoryId Kompetensi category ID
     * @return Collection Participants with category averages
     */
    private function calculateCategoryAverages(
        Collection $assessments,
        int $potensiCategoryId,
        int $kompetensiCategoryId
    ): Collection {
        // Group assessments by participant
        $assessmentsByParticipant = $assessments->groupBy('participant_id');

        return $assessmentsByParticipant->map(function ($participantAssessments, $participantId) use ($potensiCategoryId, $kompetensiCategoryId) {
            // Group by category type
            $assessmentsByCategory = $participantAssessments->groupBy('category_type_id');

            // Calculate potensi average
            $potensiAssessments = $assessmentsByCategory->get($potensiCategoryId, collect());
            $potensiRating = $potensiAssessments->isNotEmpty()
                ? $potensiAssessments->avg('individual_rating')
                : 0;

            // Calculate kinerja average
            $kinerjaAssessments = $assessmentsByCategory->get($kompetensiCategoryId, collect());
            $kinerjaRating = $kinerjaAssessments->isNotEmpty()
                ? $kinerjaAssessments->avg('individual_rating')
                : 0;

            $firstAssessment = $participantAssessments->first();

            return [
                'participant_id' => $participantId,
                'name' => $firstAssessment->name,
                'test_number' => $firstAssessment->test_number,
                'potensi_rating' => round($potensiRating, 2),
                'kinerja_rating' => round($kinerjaRating, 2),
            ];
        })->values();
    }

    /**
     * Calculate standard deviation
     *
     * @param Collection $values Numeric values
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
     * @param float $value Rating value
     * @param float $lowerBound Lower boundary
     * @param float $upperBound Upper boundary
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
     * @param string $potensiLevel Potensi level (rendah/sedang/tinggi)
     * @param string $kinerjaLevel Kinerja level (rendah/sedang/tinggi)
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
     * Build config hash for cache invalidation (respects 3-layer priority)
     *
     * @param int $eventId Assessment event ID
     * @param int $positionFormationId Position formation ID
     * @return string Config hash
     */
    private function buildConfigHash(int $eventId, int $positionFormationId): string
    {
        // Get template ID from position formation
        $position = \App\Models\PositionFormation::find($positionFormationId);

        if (!$position) {
            return md5('no_position');
        }

        $standardService = app(DynamicStandardService::class);

        // Get active aspects and their ratings for config hash
        $aspects = Aspect::where('template_id', $position->template_id)
            ->with('categoryType')
            ->get();

        $aspectRatings = [];
        foreach ($aspects as $aspect) {
            if ($standardService->isAspectActive($position->template_id, $aspect->code)) {
                $aspectRatings[$aspect->code] = $standardService->getAspectRating($position->template_id, $aspect->code);
            }
        }

        return md5(json_encode([
            'template_id' => $position->template_id,
            'aspect_ratings' => $aspectRatings,
            'session' => session()->getId(),
        ]));
    }
}
