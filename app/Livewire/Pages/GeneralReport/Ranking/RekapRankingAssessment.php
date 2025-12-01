<?php

namespace App\Livewire\Pages\GeneralReport\Ranking;

use App\Models\AssessmentEvent;
use App\Models\AssessmentTemplate;
use App\Services\ConclusionService;
use App\Services\DynamicStandardService;
use App\Services\RankingService;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app', ['title' => 'Rekap Peringkat Skor Penilaian Akhir Asesmen'])]
class RekapRankingAssessment extends Component
{
    use WithPagination;

    public ?AssessmentTemplate $selectedTemplate = null;

    public int $potensiWeight = 0;

    public int $kompetensiWeight = 0;

    public $perPage = 10; // Changed from int to allow 'all'

    public int $tolerancePercentage = 0;

    // Pie chart data
    public array $chartLabels = [];

    public array $chartData = [];

    public array $chartColors = [];

    // Conclusion configuration - loaded from ConclusionService
    public array $conclusionConfig = [];

    // CACHE PROPERTIES - untuk menyimpan hasil service calls
    private ?\Illuminate\Support\Collection $rankingsCache = null;

    private ?\Illuminate\Support\Collection $potensiRankingsCache = null;

    private ?\Illuminate\Support\Collection $kompetensiRankingsCache = null;

    private ?array $eventDataCache = null;

    protected $listeners = [
        'event-selected' => 'handleEventSelected',
        'position-selected' => 'handlePositionSelected',
        'tolerance-updated' => 'handleToleranceUpdate',
        'standard-adjusted' => 'handleStandardUpdate',
        'standard-switched' => 'handleStandardSwitch',
    ];

    public function mount(): void
    {
        $this->tolerancePercentage = session('individual_report.tolerance', 0);

        // Load conclusion configuration from ConclusionService
        $this->conclusionConfig = ConclusionService::getGapConclusionConfig();

        $this->loadWeights();

        // Prepare chart data
        $this->prepareChartData();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
        $this->clearCache(); // Clear cache saat perPage berubah
    }

    public function handleEventSelected(?string $eventCode): void
    {
        $this->resetPage();
        $this->clearCache(); // Clear cache saat event berubah

        // Refresh data di sini
        $this->loadWeights();
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

        // Refresh data di sini
        $this->loadWeights();
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
        $this->rankingsCache = null;
        $this->potensiRankingsCache = null;
        $this->kompetensiRankingsCache = null;
        $this->eventDataCache = null;
    }

    /**
     * Get event and position data (with cache)
     * OPTIMIZED: Shared cache to avoid duplicate queries
     */
    private function getEventData(): ?array
    {
        // Check cache first
        if ($this->eventDataCache !== null) {
            return $this->eventDataCache;
        }

        $eventCode = session('filter.event_code');
        $positionFormationId = session('filter.position_formation_id');

        if (! $eventCode || ! $positionFormationId) {
            return null;
        }

        // Get event and position info
        $event = AssessmentEvent::where('code', $eventCode)
            ->with(['positionFormations' => function ($query) use ($positionFormationId) {
                $query->where('id', $positionFormationId)->with('template');
            }])
            ->first();

        if (! $event) {
            return null;
        }

        $position = $event->positionFormations->first();

        if (! $position || ! $position->template) {
            return null;
        }

        // Cache the result
        $this->eventDataCache = [
            'event' => $event,
            'position' => $position,
        ];

        return $this->eventDataCache;
    }

    /**
     * Get combined rankings from RankingService (with cache)
     */
    private function getRankings(): ?\Illuminate\Support\Collection
    {
        // Check cache first
        if ($this->rankingsCache !== null) {
            return $this->rankingsCache;
        }

        if (($this->potensiWeight + $this->kompetensiWeight) === 0) {
            return null;
        }

        // OPTIMIZED: Use cached event data
        $eventData = $this->getEventData();

        if (! $eventData) {
            return null;
        }

        $event = $eventData['event'];
        $position = $eventData['position'];

        // Call RankingService to get combined rankings
        $rankingService = app(RankingService::class);
        $rankings = $rankingService->getCombinedRankings(
            $event->id,
            $position->id,
            $position->template_id,
            $this->tolerancePercentage
        );

        // Cache the result
        $this->rankingsCache = $rankings;

        return $rankings;
    }

    /**
     * Get Potensi rankings (with cache)
     * OPTIMIZED: Cache to avoid duplicate queries
     */
    private function getPotensiRankings(): ?\Illuminate\Support\Collection
    {
        // Check cache first
        if ($this->potensiRankingsCache !== null) {
            return $this->potensiRankingsCache;
        }

        // OPTIMIZED: Use cached event data
        $eventData = $this->getEventData();

        if (! $eventData) {
            return null;
        }

        $event = $eventData['event'];
        $position = $eventData['position'];

        $rankingService = app(RankingService::class);
        $this->potensiRankingsCache = $rankingService->getRankings(
            $event->id,
            $position->id,
            $position->template_id,
            'potensi',
            $this->tolerancePercentage
        );

        return $this->potensiRankingsCache;
    }

    /**
     * Get Kompetensi rankings (with cache)
     * OPTIMIZED: Cache to avoid duplicate queries
     */
    private function getKompetensiRankings(): ?\Illuminate\Support\Collection
    {
        // Check cache first
        if ($this->kompetensiRankingsCache !== null) {
            return $this->kompetensiRankingsCache;
        }

        // OPTIMIZED: Use cached event data
        $eventData = $this->getEventData();

        if (! $eventData) {
            return null;
        }

        $event = $eventData['event'];
        $position = $eventData['position'];

        $rankingService = app(RankingService::class);
        $this->kompetensiRankingsCache = $rankingService->getRankings(
            $event->id,
            $position->id,
            $position->template_id,
            'kompetensi',
            $this->tolerancePercentage
        );

        return $this->kompetensiRankingsCache;
    }

    /**
     * Get standard info for display
     * OPTIMIZED: Use cached rankings
     */
    private function getStandardInfo(): ?array
    {
        $rankings = $this->getRankings();

        if (! $rankings || $rankings->isEmpty()) {
            return null;
        }

        // OPTIMIZED: Use cached rankings instead of querying again
        $potensiRankings = $this->getPotensiRankings();
        $kompetensiRankings = $this->getKompetensiRankings();

        if (! $potensiRankings || $potensiRankings->isEmpty() || ! $kompetensiRankings || $kompetensiRankings->isEmpty()) {
            return null;
        }

        $potensiFirst = $potensiRankings->first();
        $kompetensiFirst = $kompetensiRankings->first();

        // Calculate weighted standards
        $weightedPotensiStd = $potensiFirst['adjusted_standard_score'] * ($this->potensiWeight / 100);
        $weightedKompetensiStd = $kompetensiFirst['adjusted_standard_score'] * ($this->kompetensiWeight / 100);
        $totalWeightedStd = $weightedPotensiStd + $weightedKompetensiStd;

        // Calculate original weighted standards (before tolerance)
        $weightedOrigPotensiStd = $potensiFirst['original_standard_score'] * ($this->potensiWeight / 100);
        $weightedOrigKompetensiStd = $kompetensiFirst['original_standard_score'] * ($this->kompetensiWeight / 100);
        $totalOriginalStd = $weightedOrigPotensiStd + $weightedOrigKompetensiStd;

        return [
            // Original standards (BEFORE tolerance and weighting)
            'psy_original_standard' => $potensiFirst['original_standard_score'],
            'mc_original_standard' => $kompetensiFirst['original_standard_score'],
            'total_original_standard' => round($totalOriginalStd, 2),

            // Adjusted standards (AFTER tolerance, BEFORE weighting)
            'psy_adjusted_standard' => $potensiFirst['adjusted_standard_score'],
            'mc_adjusted_standard' => $kompetensiFirst['adjusted_standard_score'],

            // Weighted standards (AFTER both tolerance AND weighting) - used for GAP calculation
            'psy_standard' => round($weightedPotensiStd, 2),
            'mc_standard' => round($weightedKompetensiStd, 2),
            'total_standard' => round($totalWeightedStd, 2),
        ];
    }

    public function handleStandardUpdate(int $templateId): void
    {
        // Clear cache when standards are adjusted
        $this->clearCache();

        // Reload weights (in case category weights were adjusted)
        $this->loadWeights();

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
     * Handle custom standard switch event
     */
    public function handleStandardSwitch(int $templateId): void
    {
        // Reuse the same logic as handleStandardUpdate
        $this->handleStandardUpdate($templateId);
    }

    public function handleToleranceUpdate(int $tolerance): void
    {
        $this->tolerancePercentage = $tolerance;

        // Clear cache when tolerance changes
        $this->clearCache();

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

    private function loadWeights(): void
    {
        $this->potensiWeight = 0;
        $this->kompetensiWeight = 0;

        $eventCode = session('filter.event_code');
        $positionFormationId = session('filter.position_formation_id');

        if (! $eventCode || ! $positionFormationId) {
            return;
        }

        // OPTIMIZED: Get event with position, template and categories in one query
        $event = AssessmentEvent::query()
            ->where('code', $eventCode)
            ->with(['positionFormations' => function ($query) use ($positionFormationId) {
                $query->where('id', $positionFormationId)
                    ->with(['template.categoryTypes' => function ($q) {
                        $q->whereIn('code', ['potensi', 'kompetensi']);
                    }]);
            }])
            ->first();

        if (! $event) {
            return;
        }

        $position = $event->positionFormations->first();

        if (! $position || ! $position->template) {
            $this->selectedTemplate = null;

            return;
        }

        $this->selectedTemplate = $position->template;

        $standardService = app(DynamicStandardService::class);

        // Get categories from eager loaded data
        $categories = $position->template->categoryTypes->keyBy('code');
        $potensi = $categories->get('potensi');
        $kompetensi = $categories->get('kompetensi');

        // Use adjusted weights from session if available
        $this->potensiWeight = $potensi
            ? $standardService->getCategoryWeight($position->template_id, 'potensi')
            : 0;
        $this->kompetensiWeight = $kompetensi
            ? $standardService->getCategoryWeight($position->template_id, 'kompetensi')
            : 0;
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
        $rows = $this->buildRankings();
        $conclusionSummary = $this->getConclusionSummary();
        $standardInfo = $this->getStandardInfo();

        return view('livewire.pages.general-report.rekap-ranking-assessment', [
            'potensiWeight' => $this->potensiWeight,
            'kompetensiWeight' => $this->kompetensiWeight,
            'rows' => $rows,
            'standardInfo' => $standardInfo,
            'conclusionSummary' => $conclusionSummary,
        ]);
    }

    /**
     * Get passing summary using RankingService
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
     * Get conclusion summary using RankingService
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

    /**
     * Build rankings with pagination using RankingService
     * OPTIMIZED: Use cached rankings
     */
    private function buildRankings(): ?LengthAwarePaginator
    {
        $rankings = $this->getRankings();

        if (! $rankings || $rankings->isEmpty()) {
            return null;
        }

        // OPTIMIZED: Use cached rankings instead of querying again
        $potensiRankings = $this->getPotensiRankings();
        $kompetensiRankings = $this->getKompetensiRankings();

        if (! $potensiRankings || ! $kompetensiRankings) {
            return null;
        }

        // Key by participant_id for fast lookup
        $potensiRankings = $potensiRankings->keyBy('participant_id');
        $kompetensiRankings = $kompetensiRankings->keyBy('participant_id');

        // Map combined rankings to view format
        $items = $rankings->map(function ($ranking) use ($potensiRankings, $kompetensiRankings) {
            $participantId = $ranking['participant_id'];
            $potensiRank = $potensiRankings->get($participantId);
            $kompetensiRank = $kompetensiRankings->get($participantId);

            // Calculate per-category scores
            $psyIndividual = $potensiRank ? $potensiRank['individual_score'] : 0;
            $mcIndividual = $kompetensiRank ? $kompetensiRank['individual_score'] : 0;
            $totalIndividual = $psyIndividual + $mcIndividual;

            // Calculate weighted scores
            $psyWeighted = $psyIndividual * ($this->potensiWeight / 100);
            $mcWeighted = $mcIndividual * ($this->kompetensiWeight / 100);

            return [
                'rank' => $ranking['rank'],
                'name' => $ranking['participant_name'],
                'psy_individual' => round($psyIndividual, 2),
                'mc_individual' => round($mcIndividual, 2),
                'total_individual' => round($totalIndividual, 2),
                'psy_weighted' => round($psyWeighted, 2),
                'mc_weighted' => round($mcWeighted, 2),
                'total_weighted_individual' => $ranking['total_individual_score'],
                'gap' => $ranking['total_gap_score'],
                'conclusion' => $ranking['conclusion'],
            ];
        });

        // Handle "Show All" option
        if ($this->perPage === 'all' || $this->perPage === 0) {
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
}
