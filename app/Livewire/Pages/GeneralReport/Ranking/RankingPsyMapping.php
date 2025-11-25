<?php

namespace App\Livewire\Pages\GeneralReport\Ranking;

use App\Models\AssessmentEvent;
use App\Models\Participant;
use App\Services\RankingService;
use App\Services\ConclusionService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app', ['title' => 'Peringkat Skor Psychology Mapping'])]
class RankingPsyMapping extends Component
{
    use WithPagination;

    public int $tolerancePercentage = 10;

    public int $perPage = 10;

    // Pie chart data
    public array $chartLabels = [];

    public array $chartData = [];

    public array $chartColors = [];

    public array $conclusionConfig = [];

    // CACHE PROPERTIES - untuk menyimpan hasil kalkulasi
    private ?Collection $rankingsCache = null;

    protected $listeners = [
        'tolerance-updated' => 'handleToleranceUpdate',
        'event-selected' => 'handleEventSelected',
        'position-selected' => 'handlePositionSelected',
        'standard-adjusted' => 'handleStandardUpdate',
        'standard-switched' => 'handleStandardSwitch',
    ];

    public function mount(): void
    {
        $this->tolerancePercentage = session('individual_report.tolerance', 10);

        // Load conclusion configuration from ConclusionService
        $this->conclusionConfig = ConclusionService::getGapConclusionConfig();

        // Prepare chart data
        $this->prepareChartData();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
        $this->clearCache();
        $this->refreshData();
    }

    public function handleEventSelected(): void
    {
        $this->resetPage();
        $this->clearCache();
        $this->refreshData();
    }

    public function handlePositionSelected(): void
    {
        $this->resetPage();
        $this->clearCache();
        $this->refreshData();
    }

    public function handleToleranceUpdate(int $tolerance): void
    {
        $this->tolerancePercentage = $tolerance;
        $this->clearCache();
        $this->refreshData();
    }

    /**
     * Handle standard adjustment from DynamicStandardService
     */
    public function handleStandardUpdate(int $templateId): void
    {
        $data = $this->getEventData();

        // Validate same template
        if (! $data || $data['position']->template_id !== $templateId) {
            return;
        }

        // Clear cache & reload with adjusted standards
        $this->clearCache();
        $this->refreshData();
    }

    /**
     * Handle custom standard switch event
     */
    public function handleStandardSwitch(int $templateId): void
    {
        // Reuse the same logic as handleStandardUpdate
        $this->handleStandardUpdate($templateId);
    }

    /**
     * Refresh all data and dispatch update events
     */
    private function refreshData(): void
    {
        // Refresh chart data
        $this->prepareChartData();

        // Get updated summary
        $summary = $this->getPassingSummary();

        // Dispatch summary update event
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
        $this->rankingsCache = null;
    }

    /**
     * Get event and position data
     * REFACTORED: Simplified data retrieval
     */
    private function getEventData(): ?array
    {
        $eventCode = session('filter.event_code');
        $positionFormationId = session('filter.position_formation_id');

        if (! $eventCode || ! $positionFormationId) {
            return null;
        }

        $event = AssessmentEvent::query()
            ->where('code', $eventCode)
            ->with(['positionFormations' => function ($query) use ($positionFormationId) {
                $query->where('id', $positionFormationId)
                    ->with('template');
            }])
            ->first();

        if (! $event) {
            return null;
        }

        $position = $event->positionFormations->first();

        if (! $position || ! $position->template) {
            return null;
        }

        return [
            'event' => $event,
            'position' => $position,
        ];
    }

    /**
     * Get all rankings using RankingService
     * REFACTORED: Use RankingService for consistent calculation
     */
    private function getRankings(): ?Collection
    {
        // Check cache first
        if ($this->rankingsCache !== null) {
            return $this->rankingsCache;
        }

        $data = $this->getEventData();

        if (! $data) {
            return null;
        }

        // Use RankingService for consistent ranking calculation
        $rankingService = app(RankingService::class);
        $this->rankingsCache = $rankingService->getRankings(
            $data['event']->id,
            $data['position']->id,
            $data['position']->template_id,
            'potensi',
            $this->tolerancePercentage
        );

        return $this->rankingsCache;
    }

    /**
     * Build paginated rankings
     * REFACTORED: Use RankingService data for pagination
     */
    private function buildRankings(): ?LengthAwarePaginator
    {
        $rankings = $this->getRankings();

        if (! $rankings || $rankings->isEmpty()) {
            return null;
        }

        // Get participant details for display
        $participantIds = $rankings->pluck('participant_id')->unique()->all();
        $participants = Participant::with('positionFormation')
            ->whereIn('id', $participantIds)
            ->get()
            ->keyBy('id');

        // Map rankings to display format
        $items = $rankings->map(function ($ranking) use ($participants) {
            $participant = $participants->get($ranking['participant_id']);

            return [
                'rank' => $ranking['rank'],
                'nip' => $participant?->skb_number ?? $participant?->test_number ?? '-',
                'name' => $participant?->name ?? '-',
                'position' => $participant?->positionFormation?->name ?? '-',
                'original_standard_rating' => $ranking['original_standard_rating'],
                'original_standard_score' => $ranking['original_standard_score'],
                'standard_rating' => $ranking['adjusted_standard_rating'],
                'standard_score' => $ranking['adjusted_standard_score'],
                'individual_rating' => $ranking['individual_rating'],
                'individual_score' => $ranking['individual_score'],
                'gap_rating' => $ranking['adjusted_gap_rating'],
                'gap_score' => $ranking['adjusted_gap_score'],
                'percentage_score' => $ranking['percentage'],
                'conclusion' => $ranking['conclusion'],
                'matrix' => 1,
            ];
        });

        // Check if "Show All" is selected
        if ($this->perPage === 0) {
            $totalItems = $items->count();

            return new LengthAwarePaginator(
                $items->all(),
                $totalItems,
                $totalItems > 0 ? $totalItems : 1,
                1,
                [
                    'path' => LengthAwarePaginator::resolveCurrentPath(),
                    'pageName' => 'page',
                ]
            );
        }

        // Normal pagination
        $currentPage = $this->getPage();
        $offset = ($currentPage - 1) * $this->perPage;

        $paginatedItems = $items->slice($offset, $this->perPage)->values();

        return new LengthAwarePaginator(
            $paginatedItems->all(),
            $items->count(),
            $this->perPage,
            $currentPage,
            [
                'path' => LengthAwarePaginator::resolveCurrentPath(),
                'pageName' => 'page',
            ]
        );
    }

    /**
     * Get passing summary statistics
     * REFACTORED: Use RankingService
     */
    public function getPassingSummary(): array
    {
        $rankings = $this->getRankings();

        if (! $rankings || $rankings->isEmpty()) {
            return ['total' => 0, 'passing' => 0, 'percentage' => 0];
        }

        $rankingService = app(RankingService::class);

        return $rankingService->getPassingSummary($rankings);
    }

    /**
     * Get standard info (original and adjusted)
     * REFACTORED: Use RankingService data
     */
    public function getStandardInfo(): ?array
    {
        $rankings = $this->getRankings();

        if (! $rankings || $rankings->isEmpty()) {
            return null;
        }

        $firstRanking = $rankings->first();

        return [
            'original_standard' => $firstRanking['original_standard_score'],
            'adjusted_standard' => $firstRanking['adjusted_standard_score'],
        ];
    }

    /**
     * Get conclusion summary (counts by type)
     * REFACTORED: Use RankingService
     */
    public function getConclusionSummary(): array
    {
        $rankings = $this->getRankings();

        if (! $rankings || $rankings->isEmpty()) {
            return array_fill_keys(array_keys($this->conclusionConfig), 0);
        }

        $rankingService = app(RankingService::class);

        return $rankingService->getConclusionSummary($rankings);
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
