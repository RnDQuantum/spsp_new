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

#[Layout('components.layouts.app', ['title' => 'Ranking Skor General Competency Mapping'])]
class RankingMcMapping extends Component
{
    use WithPagination;

    public int $tolerancePercentage = 10;

    public ?string $eventCode = null;

    /** @var array<int, array{code:string,name:string}> */
    public array $availableEvents = [];

    public int $perPage = 10;

    // Pie chart data
    public array $chartLabels = [];

    public array $chartData = [];

    public array $chartColors = [];

    // Conclusion configuration - single source of truth
    public array $conclusionConfig = [
        'Sangat Kompeten' => [
            'chartColor' => '#10b981',
            'tailwindClass' => 'bg-green-100 border-green-300',
            'rangeText' => 'Gap > 0',
        ],
        'Kompeten' => [
            'chartColor' => '#3b82f6',
            'tailwindClass' => 'bg-blue-100 border-blue-300',
            'rangeText' => 'Gap ≥ Threshold',
        ],
        'Belum Kompeten' => [
            'chartColor' => '#ef4444',
            'tailwindClass' => 'bg-red-100 border-red-300',
            'rangeText' => 'Gap < Threshold',
        ],
    ];

    protected $listeners = ['tolerance-updated' => 'handleToleranceUpdate'];

    public function mount(): void
    {
        $this->tolerancePercentage = session('individual_report.tolerance', 10);

        $this->availableEvents = AssessmentEvent::query()
            ->orderByDesc('start_date')
            ->get(['code', 'name'])
            ->map(fn ($e) => ['code' => $e->code, 'name' => $e->name])
            ->all();

        $this->eventCode = $this->availableEvents[0]['code'] ?? null;

        // Prepare chart data
        $this->prepareChartData();
    }

    public function updatedEventCode(): void
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

    private function getConclusionText(float $gap, float $threshold): string
    {
        // Logic sama dengan RekapRankingAssessment
        if ($gap > 0) {
            return 'Sangat Kompeten';
        } elseif ($gap >= $threshold) {
            return 'Kompeten';
        } else {
            return 'Belum Kompeten';
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
        if (! $this->eventCode) {
            return null;
        }

        $event = AssessmentEvent::query()
            ->with('positionFormations.template')
            ->where('code', $this->eventCode)
            ->first();

        if (! $event) {
            return null;
        }

        // Get all unique template IDs used by positions in this event
        $templateIds = $event->positionFormations->pluck('template_id')->unique()->all();
        if (empty($templateIds)) {
            return null;
        }

        // Ambil kategori kompetensi
        $kompetensiCategory = CategoryType::query()
            ->whereIn('template_id', $templateIds)
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

        // Aggregate by participant in DB and paginate
        $query = AspectAssessment::query()
            ->selectRaw('participant_id, SUM(standard_rating) as sum_original_standard_rating, SUM(standard_score) as sum_original_standard_score, SUM(individual_rating) as sum_individual_rating, SUM(individual_score) as sum_individual_score')
            ->where('event_id', $event->id)
            ->whereIn('aspect_id', $kompetensiAspectIds)
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

            // Calculate percentage based on adjusted standard
            $adjustedPercentage = $adjustedStandardScore > 0
                ? ($individualScore / $adjustedStandardScore) * 100
                : 0;

            // Calculate threshold: -(Adjusted Standard × Tolerance%)
            $threshold = -$adjustedStandardScore * ($this->tolerancePercentage / 100);

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
                'conclusion' => $this->getConclusionText($adjustedGapScore, $threshold),
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

    public function getPassingSummary(): array
    {
        // Summary across all participants for current event (not only current page)
        if (! $this->eventCode) {
            return ['total' => 0, 'passing' => 0, 'percentage' => 0];
        }

        $event = AssessmentEvent::with('positionFormations.template')->where('code', $this->eventCode)->first();
        if (! $event) {
            return ['total' => 0, 'passing' => 0, 'percentage' => 0];
        }

        // Get all unique template IDs used by positions in this event
        $templateIds = $event->positionFormations->pluck('template_id')->unique()->all();
        if (empty($templateIds)) {
            return ['total' => 0, 'passing' => 0, 'percentage' => 0];
        }

        $kompetensiCategory = CategoryType::query()
            ->whereIn('template_id', $templateIds)
            ->where('code', 'kompetensi')
            ->first();

        if (! $kompetensiCategory) {
            return ['total' => 0, 'passing' => 0, 'percentage' => 0];
        }

        $kompetensiAspectIds = Aspect::query()
            ->where('category_type_id', $kompetensiCategory->id)
            ->pluck('id')
            ->all();

        if (empty($kompetensiAspectIds)) {
            return ['total' => 0, 'passing' => 0, 'percentage' => 0];
        }

        $aggregates = AspectAssessment::query()
            ->selectRaw('participant_id, SUM(standard_score) as sum_original_standard_score, SUM(individual_score) as sum_individual_score')
            ->where('event_id', $event->id)
            ->whereIn('aspect_id', $kompetensiAspectIds)
            ->groupBy('participant_id')
            ->get();

        $total = $aggregates->count();

        // Calculate passing based on gap and threshold (Sangat Kompeten or Kompeten)
        $passing = $aggregates->filter(function ($r) {
            $originalStandardScore = (float) $r->sum_original_standard_score;
            $individualScore = (float) $r->sum_individual_score;

            // Calculate adjusted standard based on tolerance
            $toleranceFactor = 1 - ($this->tolerancePercentage / 100);
            $adjustedStandardScore = $originalStandardScore * $toleranceFactor;

            // Calculate gap
            $gap = $individualScore - $adjustedStandardScore;

            // Calculate threshold
            $threshold = -$adjustedStandardScore * ($this->tolerancePercentage / 100);

            // Count as passing if gap > 0 OR gap >= threshold (Sangat Kompeten or Kompeten)
            return $gap > 0 || $gap >= $threshold;
        })->count();

        return [
            'total' => $total,
            'passing' => $passing,
            'percentage' => $total > 0 ? (int) round(($passing / $total) * 100) : 0,
        ];
    }

    public function getStandardInfo(): ?array
    {
        if (! $this->eventCode) {
            return null;
        }

        $event = AssessmentEvent::with('positionFormations.template')->where('code', $this->eventCode)->first();
        if (! $event) {
            return null;
        }

        // Get all unique template IDs used by positions in this event
        $templateIds = $event->positionFormations->pluck('template_id')->unique()->all();
        if (empty($templateIds)) {
            return null;
        }

        $kompetensiCategory = CategoryType::query()
            ->whereIn('template_id', $templateIds)
            ->where('code', 'kompetensi')
            ->first();

        if (! $kompetensiCategory) {
            return null;
        }

        $kompetensiAspectIds = Aspect::query()
            ->where('category_type_id', $kompetensiCategory->id)
            ->pluck('id')
            ->all();

        if (empty($kompetensiAspectIds)) {
            return null;
        }

        // Get standard scores (they should be same for all participants, but we take first one)
        $firstParticipant = AspectAssessment::query()
            ->selectRaw('SUM(standard_score) as total_standard_score')
            ->where('event_id', $event->id)
            ->whereIn('aspect_id', $kompetensiAspectIds)
            ->groupBy('participant_id')
            ->first();

        if (! $firstParticipant) {
            return null;
        }

        $originalStandardScore = (float) $firstParticipant->total_standard_score;

        // Calculate adjusted standard based on tolerance
        $toleranceFactor = 1 - $this->tolerancePercentage / 100;
        $adjustedStandardScore = $originalStandardScore * $toleranceFactor;

        // Threshold calculation
        $threshold = -$adjustedStandardScore * ($this->tolerancePercentage / 100);

        return [
            'original_standard' => round($originalStandardScore, 2),
            'adjusted_standard' => round($adjustedStandardScore, 2),
            'threshold' => round($threshold, 2),
        ];
    }

    public function getConclusionSummary(): array
    {
        // Get conclusion summary for all participants in current event
        if (! $this->eventCode) {
            return [];
        }

        $event = AssessmentEvent::with('positionFormations.template')->where('code', $this->eventCode)->first();
        if (! $event) {
            return [];
        }

        // Get all unique template IDs used by positions in this event
        $templateIds = $event->positionFormations->pluck('template_id')->unique()->all();
        if (empty($templateIds)) {
            return [];
        }

        $kompetensiCategory = CategoryType::query()
            ->whereIn('template_id', $templateIds)
            ->where('code', 'kompetensi')
            ->first();

        if (! $kompetensiCategory) {
            return [];
        }

        $kompetensiAspectIds = Aspect::query()
            ->where('category_type_id', $kompetensiCategory->id)
            ->pluck('id')
            ->all();

        if (empty($kompetensiAspectIds)) {
            return [];
        }

        $aggregates = AspectAssessment::query()
            ->selectRaw('participant_id, SUM(standard_score) as sum_original_standard_score, SUM(individual_score) as sum_individual_score')
            ->where('event_id', $event->id)
            ->whereIn('aspect_id', $kompetensiAspectIds)
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

            // Calculate gap based on adjusted standard
            $gap = $individualScore - $adjustedStandardScore;

            // Calculate threshold: -(Adjusted Standard × Tolerance%)
            $threshold = -$adjustedStandardScore * ($this->tolerancePercentage / 100);

            // Determine conclusion
            $conclusion = $this->getConclusionText($gap, $threshold);
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
