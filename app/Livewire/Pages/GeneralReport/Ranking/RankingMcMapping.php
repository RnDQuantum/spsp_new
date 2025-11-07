<?php

namespace App\Livewire\Pages\GeneralReport\Ranking;

use App\Models\Aspect;
use App\Models\AspectAssessment;
use App\Models\AssessmentEvent;
use App\Models\CategoryType;
use App\Models\Participant;
use App\Services\DynamicStandardService;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app', ['title' => 'Ranking Skor General Competency Mapping'])]
class RankingMcMapping extends Component
{
    use WithPagination;

    public int $tolerancePercentage = 10;

    public int $perPage = 10;

    // Pie chart data
    public array $chartLabels = [];

    public array $chartData = [];

    public array $chartColors = [];

    // Conclusion configuration - single source of truth
    public array $conclusionConfig = [
        'Di Atas Standar' => [
            'chartColor' => '#16a34a',      // green-600
            'tailwindClass' => 'bg-green-600 text-white',
            'rangeText' => 'Original Gap ≥ 0',
        ],
        'Memenuhi Standar' => [
            'chartColor' => '#facc15',      // yellow-400
            'tailwindClass' => 'bg-yellow-400 text-gray-900',
            'rangeText' => 'Adjusted Gap ≥ 0',
        ],
        'Di Bawah Standar' => [
            'chartColor' => '#dc2626',      // red-600
            'tailwindClass' => 'bg-red-600 text-white',
            'rangeText' => 'Adjusted Gap < 0',
        ],
    ];

    // CACHE PROPERTIES - untuk menyimpan hasil kalkulasi
    private ?array $adjustedStandardsCache = null;

    private ?array $participantsCache = null;

    private ?array $aggregatesCache = null;

    protected $listeners = [
        'tolerance-updated' => 'handleToleranceUpdate',
        'event-selected' => 'handleEventSelected',
        'position-selected' => 'handlePositionSelected',
    ];

    public function mount(): void
    {
        $this->tolerancePercentage = session('individual_report.tolerance', 10);

        // Prepare chart data
        $this->prepareChartData();
    }

    public function handleEventSelected(?string $eventCode): void
    {
        $this->resetPage();
        $this->clearCache(); // Clear cache saat event berubah

        // Refresh chart data
        $this->prepareChartData();

        $summary = $this->getPassingSummary();
        $this->dispatch('summary-updated', [
            'passing' => $summary['passing'],
            'total' => $summary['total'],
            'percentage' => $summary['percentage'],
        ]);

        // Dispatch chart update event
        $this->dispatch('pieChartDataUpdated', [
            'labels' => $this->chartLabels,
            'data' => $this->chartData,
            'colors' => $this->chartColors,
        ]);
    }

    public function handlePositionSelected(?int $positionFormationId): void
    {
        $this->resetPage();
        $this->clearCache(); // Clear cache saat position berubah

        // Refresh chart data
        $this->prepareChartData();

        $summary = $this->getPassingSummary();
        $this->dispatch('summary-updated', [
            'passing' => $summary['passing'],
            'total' => $summary['total'],
            'percentage' => $summary['percentage'],
        ]);

        // Dispatch chart update event
        $this->dispatch('pieChartDataUpdated', [
            'labels' => $this->chartLabels,
            'data' => $this->chartData,
            'colors' => $this->chartColors,
        ]);
    }

    public function handleToleranceUpdate(int $tolerance): void
    {
        $this->tolerancePercentage = $tolerance;
        // Note: Tidak perlu clearCache() karena adjustedStandards tidak berubah,
        // hanya toleranceFactor yang berubah (dihitung on-the-fly)

        // Refresh chart data with new tolerance
        $this->prepareChartData();

        $summary = $this->getPassingSummary();
        $this->dispatch('summary-updated', [
            'passing' => $summary['passing'],
            'total' => $summary['total'],
            'percentage' => $summary['percentage'],
        ]);

        // Dispatch chart update event
        $this->dispatch('pieChartDataUpdated', [
            'labels' => $this->chartLabels,
            'data' => $this->chartData,
            'colors' => $this->chartColors,
        ]);
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
        $this->clearCache(); // Clear cache saat perPage berubah

        // Refresh chart data
        $this->prepareChartData();

        $summary = $this->getPassingSummary();
        $this->dispatch('summary-updated', [
            'passing' => $summary['passing'],
            'total' => $summary['total'],
            'percentage' => $summary['percentage'],
        ]);

        // Dispatch chart update event
        $this->dispatch('pieChartDataUpdated', [
            'labels' => $this->chartLabels,
            'data' => $this->chartData,
            'colors' => $this->chartColors,
        ]);
    }

    /**
     * Clear all caches
     */
    private function clearCache(): void
    {
        $this->adjustedStandardsCache = null;
        $this->participantsCache = null;
        $this->aggregatesCache = null;
    }

    /**
     * Get adjusted standard values from session or database
     * OPTIMIZED: Cache result untuk menghindari kalkulasi berulang
     */
    private function getAdjustedStandardValues(
        int $templateId,
        array $kompetensiAspectIds,
        float $originalStandardRating,
        float $originalStandardScore
    ): array {
        // Gunakan cache jika sudah ada
        if ($this->adjustedStandardsCache !== null) {
            return $this->adjustedStandardsCache;
        }

        $standardService = app(DynamicStandardService::class);

        // Check if there are any adjustments for kompetensi category
        if (! $standardService->hasCategoryAdjustments($templateId, 'kompetensi')) {
            // No adjustments, return original values
            $this->adjustedStandardsCache = [
                'standard_rating' => $originalStandardRating,
                'standard_score' => $originalStandardScore,
            ];

            return $this->adjustedStandardsCache;
        }

        // Recalculate based on adjusted standards from session
        $adjustedRating = 0;
        $adjustedScore = 0;

        // Get all aspects data ONCE
        $aspects = Aspect::whereIn('id', $kompetensiAspectIds)
            ->orderBy('order')
            ->get();

        foreach ($aspects as $aspect) {
            // Check if aspect is active
            if (! $standardService->isAspectActive($templateId, $aspect->code)) {
                continue; // Skip inactive aspects
            }

            // Get adjusted weight and rating from session
            $aspectWeight = $standardService->getAspectWeight($templateId, $aspect->code);
            $aspectRating = $standardService->getAspectRating($templateId, $aspect->code);

            $adjustedRating += $aspectRating;
            $adjustedScore += ($aspectRating * $aspectWeight);
        }

        // Cache result
        $this->adjustedStandardsCache = [
            'standard_rating' => $adjustedRating,
            'standard_score' => $adjustedScore,
        ];

        return $this->adjustedStandardsCache;
    }

    /**
     * Get all aggregates data ONCE and cache it
     * OPTIMIZED: Avoid multiple queries
     */
    private function getAggregatesData(): ?array
    {
        if ($this->aggregatesCache !== null) {
            return $this->aggregatesCache;
        }

        $eventCode = session('filter.event_code');
        $positionFormationId = session('filter.position_formation_id');

        if (! $eventCode || ! $positionFormationId) {
            return null;
        }

        $event = AssessmentEvent::query()
            ->where('code', $eventCode)
            ->first();

        if (! $event) {
            return null;
        }

        // Get selected position with template
        $position = $event->positionFormations()
            ->with('template')
            ->find($positionFormationId);

        if (! $position || ! $position->template) {
            return null;
        }

        // Get Kompetensi category from selected position's template
        $kompetensiCategory = CategoryType::query()
            ->where('template_id', $position->template_id)
            ->where('code', 'kompetensi')
            ->first();

        if (! $kompetensiCategory) {
            return null;
        }

        $kompetensiAspectIds = Aspect::query()
            ->where('category_type_id', $kompetensiCategory->id)
            ->orderBy('order')
            ->pluck('id')
            ->all();

        if (empty($kompetensiAspectIds)) {
            return null;
        }

        // Get ALL aggregates at once
        $aggregates = AspectAssessment::query()
            ->selectRaw('participant_id, SUM(standard_rating) as sum_original_standard_rating, SUM(standard_score) as sum_original_standard_score, SUM(individual_rating) as sum_individual_rating, SUM(individual_score) as sum_individual_score')
            ->where('event_id', $event->id)
            ->where('position_formation_id', $positionFormationId)
            ->whereIn('aspect_id', $kompetensiAspectIds)
            ->groupBy('participant_id')
            ->orderByDesc('sum_individual_score')
            ->orderByDesc('sum_individual_rating')
            ->orderBy('participant_id')
            ->get();

        // Get ALL participants at once (avoid N+1)
        $participantIds = $aggregates->pluck('participant_id')->unique()->all();
        $participants = Participant::with('positionFormation')
            ->whereIn('id', $participantIds)
            ->get()
            ->keyBy('id');

        // Get adjusted standards ONCE
        $firstAggregate = $aggregates->first();
        if (! $firstAggregate) {
            return null;
        }

        $adjustedStandards = $this->getAdjustedStandardValues(
            $position->template_id,
            $kompetensiAspectIds,
            (float) $firstAggregate->sum_original_standard_rating,
            (float) $firstAggregate->sum_original_standard_score
        );

        // Cache everything
        $this->aggregatesCache = [
            'event' => $event,
            'position' => $position,
            'kompetensiAspectIds' => $kompetensiAspectIds,
            'aggregates' => $aggregates,
            'participants' => $participants,
            'adjustedStandards' => $adjustedStandards,
        ];

        return $this->aggregatesCache;
    }

    private function getConclusionText(float $originalGap, float $adjustedGap): string
    {
        if ($originalGap >= 0) {
            return 'Di Atas Standar';
        } elseif ($adjustedGap >= 0) {
            return 'Memenuhi Standar';
        } else {
            return 'Di Bawah Standar';
        }
    }

    private function overallConclusionText(float $totalGapScore): string
    {
        if ($totalGapScore > 0) {
            return 'Di Atas Standar';
        }
        if ($totalGapScore === 0.0) {
            return 'Memenuhi Standar';
        }

        return 'Di Bawah Standar';
    }

    private function buildRankings(): ?LengthAwarePaginator
    {
        $data = $this->getAggregatesData();

        if (! $data) {
            return null;
        }

        $aggregates = $data['aggregates'];
        $participants = $data['participants'];
        $originalStandardRating = $data['adjustedStandards']['standard_rating'];
        $originalStandardScore = $data['adjustedStandards']['standard_score'];

        // Calculate adjusted standard based on tolerance ONCE
        $toleranceFactor = 1 - ($this->tolerancePercentage / 100);
        $adjustedStandardRating = $originalStandardRating * $toleranceFactor;
        $adjustedStandardScore = $originalStandardScore * $toleranceFactor;

        // Check if "Show All" is selected
        if ($this->perPage === 0) {
            $items = $aggregates->map(function ($row, int $index) use (
                $participants,
                $originalStandardRating,
                $originalStandardScore,
                $adjustedStandardRating,
                $adjustedStandardScore
            ): array {
                return $this->mapAggregateToRankingItem(
                    $row,
                    $index,
                    $participants,
                    $originalStandardRating,
                    $originalStandardScore,
                    $adjustedStandardRating,
                    $adjustedStandardScore
                );
            })->all();

            $totalItems = count($items);

            return new LengthAwarePaginator(
                $items,
                $totalItems,
                $totalItems > 0 ? $totalItems : 1,
                1,
                ['path' => request()->url(), 'query' => request()->query()]
            );
        }

        // Normal pagination
        $currentPage = (int) request()->get('page', 1);
        $offset = ($currentPage - 1) * $this->perPage;

        $paginatedAggregates = $aggregates->slice($offset, $this->perPage)->values();

        $items = $paginatedAggregates->map(function ($row, int $index) use (
            $offset,
            $participants,
            $originalStandardRating,
            $originalStandardScore,
            $adjustedStandardRating,
            $adjustedStandardScore
        ): array {
            return $this->mapAggregateToRankingItem(
                $row,
                $offset + $index,
                $participants,
                $originalStandardRating,
                $originalStandardScore,
                $adjustedStandardRating,
                $adjustedStandardScore
            );
        })->all();

        return new LengthAwarePaginator(
            $items,
            $aggregates->count(),
            $this->perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }

    /**
     * Map aggregate data to ranking item
     * OPTIMIZED: Extract to separate method to avoid duplication
     */
    private function mapAggregateToRankingItem(
        $row,
        int $index,
        $participants,
        float $originalStandardRating,
        float $originalStandardScore,
        float $adjustedStandardRating,
        float $adjustedStandardScore
    ): array {
        $participant = $participants->get($row->participant_id);

        $individualRating = (float) $row->sum_individual_rating;
        $individualScore = (float) $row->sum_individual_score;

        // Calculate gaps
        $adjustedGapRating = $individualRating - $adjustedStandardRating;
        $adjustedGapScore = $individualScore - $adjustedStandardScore;
        $originalGapScore = $individualScore - $originalStandardScore;

        // Calculate percentage based on adjusted standard
        $adjustedPercentage = $adjustedStandardScore > 0
            ? ($individualScore / $adjustedStandardScore) * 100
            : 0;

        return [
            'rank' => $index + 1,
            'nip' => $participant?->skb_number ?? $participant?->test_number ?? '-',
            'name' => $participant?->name ?? '-',
            'position' => $participant?->positionFormation?->name ?? '-',
            'original_standard_rating' => round($originalStandardRating, 2),
            'original_standard_score' => round($originalStandardScore, 2),
            'standard_rating' => round($adjustedStandardRating, 2),
            'standard_score' => round($adjustedStandardScore, 2),
            'individual_rating' => round($individualRating, 2),
            'individual_score' => round($individualScore, 2),
            'gap_rating' => round($adjustedGapRating, 2),
            'gap_score' => round($adjustedGapScore, 2),
            'percentage_score' => round($adjustedPercentage, 2),
            'conclusion' => $this->getConclusionText($originalGapScore, $adjustedGapScore),
            'matrix' => 1,
        ];
    }

    public function getPassingSummary(): array
    {
        $data = $this->getAggregatesData();

        if (! $data) {
            return ['total' => 0, 'passing' => 0, 'percentage' => 0];
        }

        $aggregates = $data['aggregates'];
        $originalStandardScore = $data['adjustedStandards']['standard_score'];

        // Calculate adjusted standard based on tolerance
        $toleranceFactor = 1 - ($this->tolerancePercentage / 100);
        $adjustedStandardScore = $originalStandardScore * $toleranceFactor;

        $total = $aggregates->count();

        // Calculate passing
        $passing = $aggregates->filter(function ($r) use ($originalStandardScore, $adjustedStandardScore) {
            $individualScore = (float) $r->sum_individual_score;

            // Calculate gaps
            $originalGap = $individualScore - $originalStandardScore;
            $adjustedGap = $individualScore - $adjustedStandardScore;

            // Count as passing if original gap >= 0 OR adjusted gap >= 0
            return $originalGap >= 0 || $adjustedGap >= 0;
        })->count();

        return [
            'total' => $total,
            'passing' => $passing,
            'percentage' => $total > 0 ? (int) round(($passing / $total) * 100) : 0,
        ];
    }

    public function getStandardInfo(): ?array
    {
        $data = $this->getAggregatesData();

        if (! $data) {
            return null;
        }

        $originalStandardScore = $data['adjustedStandards']['standard_score'];

        // Calculate adjusted standard based on tolerance
        $toleranceFactor = 1 - $this->tolerancePercentage / 100;
        $adjustedStandardScore = $originalStandardScore * $toleranceFactor;

        return [
            'original_standard' => round($originalStandardScore, 2),
            'adjusted_standard' => round($adjustedStandardScore, 2),
        ];
    }

    public function getConclusionSummary(): array
    {
        $data = $this->getAggregatesData();

        if (! $data) {
            return [];
        }

        $aggregates = $data['aggregates'];
        $originalStandardScore = $data['adjustedStandards']['standard_score'];

        // Calculate adjusted standard based on tolerance
        $toleranceFactor = 1 - ($this->tolerancePercentage / 100);
        $adjustedStandardScore = $originalStandardScore * $toleranceFactor;

        // Initialize conclusions from config
        $conclusions = array_fill_keys(array_keys($this->conclusionConfig), 0);

        foreach ($aggregates as $r) {
            $individualScore = (float) $r->sum_individual_score;

            // Calculate gaps
            $originalGap = $individualScore - $originalStandardScore;
            $adjustedGap = $individualScore - $adjustedStandardScore;

            // Determine conclusion
            $conclusion = $this->getConclusionText($originalGap, $adjustedGap);
            $conclusions[$conclusion]++;
        }

        return $conclusions;
    }

    private function prepareChartData(): void
    {
        $conclusionSummary = $this->getConclusionSummary();

        if (empty($conclusionSummary)) {
            $this->chartLabels = [];
            $this->chartData = [];
            $this->chartColors = [];

            return;
        }

        // Build chart data from conclusionConfig
        $this->chartLabels = array_keys($this->conclusionConfig);
        $this->chartData = array_values($conclusionSummary);
        $this->chartColors = array_column($this->conclusionConfig, 'chartColor');
    }

    public function render()
    {
        $rankings = $this->buildRankings();
        $conclusionSummary = $this->getConclusionSummary();
        $standardInfo = $this->getStandardInfo();

        return view('livewire.pages.general-report.ranking.ranking-mc-mapping', [
            'rankings' => $rankings,
            'conclusionSummary' => $conclusionSummary,
            'standardInfo' => $standardInfo,
        ]);
    }
}
