<?php

namespace App\Livewire\Pages\GeneralReport\Ranking;

use App\Models\Aspect;
use App\Models\AspectAssessment;
use App\Models\AssessmentEvent;
use App\Models\CategoryType;
use App\Models\Participant;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app', ['title' => 'Ranking Skor Psychology Mapping'])]
class RankingPsyMapping extends Component
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
            'chartColor' => '#10b981',
            'tailwindClass' => 'bg-green-600 dark:bg-green-600 border-green-300 dark:border-green-400',
            'rangeText' => 'Original Gap ≥ 0',
        ],
        'Memenuhi Standar' => [
            'chartColor' => '#3b82f6',
            'tailwindClass' => 'bg-blue-600 dark:bg-blue-600 border-blue-300 dark:border-blue-400',
            'rangeText' => 'Adjusted Gap ≥ 0',
        ],
        'Di Bawah Standar' => [
            'chartColor' => '#ef4444',
            'tailwindClass' => 'bg-red-600 dark:bg-red-600 border-red-300 dark:border-red-400',
            'rangeText' => 'Adjusted Gap < 0',
        ],
    ];

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

    private function buildRankings(): ?LengthAwarePaginator
    {
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

        // Get Potensi category from selected position's template
        $potensiCategory = CategoryType::query()
            ->where('template_id', $position->template_id)
            ->where('code', 'potensi')
            ->first();

        if (! $potensiCategory) {
            return null;
        }

        $potensiAspectIds = Aspect::query()
            ->where('category_type_id', $potensiCategory->id)
            ->orderBy('order')
            ->pluck('id')
            ->all();

        if (empty($potensiAspectIds)) {
            return null;
        }

        // Aggregate by participant in DB and paginate - FILTER by position
        $query = AspectAssessment::query()
            ->selectRaw('participant_id, SUM(standard_rating) as sum_original_standard_rating, SUM(standard_score) as sum_original_standard_score, SUM(individual_rating) as sum_individual_rating, SUM(individual_score) as sum_individual_score')
            ->where('event_id', $event->id)
            ->where('position_formation_id', $positionFormationId)
            ->whereIn('aspect_id', $potensiAspectIds)
            ->groupBy('participant_id')
            ->orderByDesc('sum_individual_score')
            ->orderByDesc('sum_individual_rating');

        /** @var LengthAwarePaginator $paginator */
        $paginator = $query->paginate($this->perPage, pageName: 'page')->withQueryString();

        $currentPage = (int) $paginator->currentPage();
        $perPage = (int) $paginator->perPage();
        $startRank = ($currentPage - 1) * $perPage;

        $items = collect($paginator->items())->values()->map(function ($row, int $index) use ($startRank): array {
            $participant = Participant::with('positionFormation')->find($row->participant_id);

            // Get original values from database
            $originalStandardRating = (float) $row->sum_original_standard_rating;
            $originalStandardScore = (float) $row->sum_original_standard_score;
            $individualRating = (float) $row->sum_individual_rating;
            $individualScore = (float) $row->sum_individual_score;

            // Calculate adjusted standard based on tolerance
            $toleranceFactor = 1 - ($this->tolerancePercentage / 100);
            $adjustedStandardRating = $originalStandardRating * $toleranceFactor;
            $adjustedStandardScore = $originalStandardScore * $toleranceFactor;

            // Calculate gap based on adjusted standard
            $adjustedGapRating = $individualRating - $adjustedStandardRating;
            $adjustedGapScore = $individualScore - $adjustedStandardScore;

            // Calculate original gap (at tolerance 0)
            $originalGapScore = $individualScore - $originalStandardScore;

            // Calculate percentage based on adjusted standard
            $adjustedPercentage = $adjustedStandardScore > 0
                ? ($individualScore / $adjustedStandardScore) * 100
                : 0;

            return [
                'rank' => $startRank + $index + 1,
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
        })->all();

        return new LengthAwarePaginator(
            $items,
            $paginator->total(),
            $paginator->perPage(),
            $paginator->currentPage(),
            ['path' => $paginator->path(), 'query' => request()->query()]
        );
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

    public function getPassingSummary(): array
    {
        // Summary across all participants for current event + position (not only current page)
        $eventCode = session('filter.event_code');
        $positionFormationId = session('filter.position_formation_id');

        if (! $eventCode || ! $positionFormationId) {
            return ['total' => 0, 'passing' => 0, 'percentage' => 0];
        }

        $event = AssessmentEvent::where('code', $eventCode)->first();
        if (! $event) {
            return ['total' => 0, 'passing' => 0, 'percentage' => 0];
        }

        // Get selected position with template
        $position = $event->positionFormations()
            ->with('template')
            ->find($positionFormationId);

        if (! $position || ! $position->template) {
            return ['total' => 0, 'passing' => 0, 'percentage' => 0];
        }

        // Get Potensi category from selected position's template
        $potensiCategory = CategoryType::query()
            ->where('template_id', $position->template_id)
            ->where('code', 'potensi')
            ->first();

        if (! $potensiCategory) {
            return ['total' => 0, 'passing' => 0, 'percentage' => 0];
        }

        $potensiAspectIds = Aspect::query()
            ->where('category_type_id', $potensiCategory->id)
            ->pluck('id')
            ->all();

        if (empty($potensiAspectIds)) {
            return ['total' => 0, 'passing' => 0, 'percentage' => 0];
        }

        $aggregates = AspectAssessment::query()
            ->selectRaw('participant_id, SUM(standard_score) as sum_original_standard_score, SUM(individual_score) as sum_individual_score')
            ->where('event_id', $event->id)
            ->where('position_formation_id', $positionFormationId)
            ->whereIn('aspect_id', $potensiAspectIds)
            ->groupBy('participant_id')
            ->get();

        $total = $aggregates->count();

        // Calculate passing based on new logic (Di Atas Standar or Memenuhi Standar)
        $passing = $aggregates->filter(function ($r) {
            $originalStandardScore = (float) $r->sum_original_standard_score;
            $individualScore = (float) $r->sum_individual_score;

            // Calculate original gap (at tolerance 0)
            $originalGap = $individualScore - $originalStandardScore;

            // Calculate adjusted standard based on tolerance
            $toleranceFactor = 1 - ($this->tolerancePercentage / 100);
            $adjustedStandardScore = $originalStandardScore * $toleranceFactor;

            // Calculate adjusted gap
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
        $eventCode = session('filter.event_code');
        $positionFormationId = session('filter.position_formation_id');

        if (! $eventCode || ! $positionFormationId) {
            return null;
        }

        $event = AssessmentEvent::where('code', $eventCode)->first();
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

        // Get Potensi category from selected position's template
        $potensiCategory = CategoryType::query()
            ->where('template_id', $position->template_id)
            ->where('code', 'potensi')
            ->first();

        if (! $potensiCategory) {
            return null;
        }

        $potensiAspectIds = Aspect::query()
            ->where('category_type_id', $potensiCategory->id)
            ->pluck('id')
            ->all();

        if (empty($potensiAspectIds)) {
            return null;
        }

        // Get standard scores (they should be same for all participants in this position, but we take first one)
        $firstParticipant = AspectAssessment::query()
            ->selectRaw('SUM(standard_score) as total_standard_score')
            ->where('event_id', $event->id)
            ->where('position_formation_id', $positionFormationId)
            ->whereIn('aspect_id', $potensiAspectIds)
            ->groupBy('participant_id')
            ->first();

        if (! $firstParticipant) {
            return null;
        }

        $originalStandardScore = (float) $firstParticipant->total_standard_score;

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
        // Get conclusion summary for all participants in current event + position
        $eventCode = session('filter.event_code');
        $positionFormationId = session('filter.position_formation_id');

        if (! $eventCode || ! $positionFormationId) {
            return [];
        }

        $event = AssessmentEvent::where('code', $eventCode)->first();
        if (! $event) {
            return [];
        }

        // Get selected position with template
        $position = $event->positionFormations()
            ->with('template')
            ->find($positionFormationId);

        if (! $position || ! $position->template) {
            return [];
        }

        // Get Potensi category from selected position's template
        $potensiCategory = CategoryType::query()
            ->where('template_id', $position->template_id)
            ->where('code', 'potensi')
            ->first();

        if (! $potensiCategory) {
            return [];
        }

        $potensiAspectIds = Aspect::query()
            ->where('category_type_id', $potensiCategory->id)
            ->pluck('id')
            ->all();

        if (empty($potensiAspectIds)) {
            return [];
        }

        $aggregates = AspectAssessment::query()
            ->selectRaw('participant_id, SUM(standard_score) as sum_original_standard_score, SUM(individual_score) as sum_individual_score')
            ->where('event_id', $event->id)
            ->where('position_formation_id', $positionFormationId)
            ->whereIn('aspect_id', $potensiAspectIds)
            ->groupBy('participant_id')
            ->get();

        // Initialize conclusions from config
        $conclusions = array_fill_keys(array_keys($this->conclusionConfig), 0);

        foreach ($aggregates as $r) {
            $originalStandardScore = (float) $r->sum_original_standard_score;
            $individualScore = (float) $r->sum_individual_score;

            // Calculate adjusted standard based on tolerance
            $toleranceFactor = 1 - ($this->tolerancePercentage / 100);
            $adjustedStandardScore = $originalStandardScore * $toleranceFactor;

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

        return view('livewire.pages.general-report.ranking.ranking-psy-mapping', [
            'rankings' => $rankings,
            'conclusionSummary' => $conclusionSummary,
            'standardInfo' => $standardInfo,
        ]);
    }
}
