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
     * Get combined rankings (with cache)
     * 🚀 CRITICAL OPTIMIZATION: Build combined rankings from cached potensi & kompetensi
     * to avoid duplicate database queries
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

        // Get potensi and kompetensi rankings (will be cached after first call)
        $potensiRankings = $this->getPotensiRankings();
        $kompetensiRankings = $this->getKompetensiRankings();

        if (! $potensiRankings || $potensiRankings->isEmpty() || ! $kompetensiRankings || $kompetensiRankings->isEmpty()) {
            return null;
        }

        // 🚀 OPTIMIZATION: Build combined rankings locally instead of calling getCombinedRankings()
        // This avoids duplicate queries since getCombinedRankings() would call getRankings() again
        // Logic replicated from RankingService::getCombinedRankings() (lines 676-743)

        $kompetensiRankingsKeyed = $kompetensiRankings->keyBy('participant_id');

        $participantScores = [];
        foreach ($potensiRankings as $potensiRank) {
            $participantId = $potensiRank['participant_id'];

            $kompetensiRank = $kompetensiRankingsKeyed->get($participantId);
            if (! $kompetensiRank) {
                continue;
            }

            // Calculate weighted total score
            $weightedPotensiScore = $potensiRank['individual_score'] * ($this->potensiWeight / 100);
            $weightedKompetensiScore = $kompetensiRank['individual_score'] * ($this->kompetensiWeight / 100);
            $totalIndividualScore = round($weightedPotensiScore + $weightedKompetensiScore, 2);

            // Calculate weighted standard score (with tolerance)
            $weightedPotensiStd = $potensiRank['adjusted_standard_score'] * ($this->potensiWeight / 100);
            $weightedKompetensiStd = $kompetensiRank['adjusted_standard_score'] * ($this->kompetensiWeight / 100);
            $totalStandardScore = round($weightedPotensiStd + $weightedKompetensiStd, 2);

            // Calculate weighted original standard score
            $weightedOrigPotensiStd = $potensiRank['original_standard_score'] * ($this->potensiWeight / 100);
            $weightedOrigKompetensiStd = $kompetensiRank['original_standard_score'] * ($this->kompetensiWeight / 100);
            $totalOriginalStandardScore = round($weightedOrigPotensiStd + $weightedOrigKompetensiStd, 2);

            // Calculate gaps
            $totalGapScore = round($totalIndividualScore - $totalStandardScore, 2);
            $totalOriginalGapScore = round($totalIndividualScore - $totalOriginalStandardScore, 2);

            // Calculate percentage
            $percentage = $totalStandardScore > 0
                ? ($totalIndividualScore / $totalStandardScore) * 100
                : 0;

            // Determine conclusion
            $conclusion = ConclusionService::getGapBasedConclusion($totalOriginalGapScore, $totalGapScore);

            $participantScores[] = [
                'participant_id' => $participantId,
                'participant_name' => $potensiRank['participant_name'] ?? '',
                'total_individual_score' => $totalIndividualScore,
                'total_standard_score' => $totalStandardScore,
                'total_original_standard_score' => $totalOriginalStandardScore,
                'total_gap_score' => $totalGapScore,
                'total_original_gap_score' => $totalOriginalGapScore,
                'percentage' => round($percentage, 2),
                'conclusion' => $conclusion,
                'potensi_weight' => $this->potensiWeight,
                'kompetensi_weight' => $this->kompetensiWeight,
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
        $this->rankingsCache = $rankings->map(fn ($row, $index) => [...$row, 'rank' => $index + 1]);

        return $this->rankingsCache;
    }

    /**
     * Get Potensi rankings (with cache)
     * 🚀 OPTIMIZED: Fetch once and cache, reused by getRankings()
     */
    private function getPotensiRankings(): ?\Illuminate\Support\Collection
    {
        // Return cached if available
        if ($this->potensiRankingsCache !== null) {
            return $this->potensiRankingsCache;
        }

        // Fetch from service and cache
        $eventData = $this->getEventData();

        if (! $eventData) {
            return null;
        }

        $rankingService = app(RankingService::class);
        $this->potensiRankingsCache = $rankingService->getRankings(
            $eventData['event']->id,
            $eventData['position']->id,
            $eventData['position']->template_id,
            'potensi',
            $this->tolerancePercentage
        );

        return $this->potensiRankingsCache;
    }

    /**
     * Get Kompetensi rankings (with cache)
     * 🚀 OPTIMIZED: Fetch once and cache, reused by getRankings()
     */
    private function getKompetensiRankings(): ?\Illuminate\Support\Collection
    {
        // Return cached if available
        if ($this->kompetensiRankingsCache !== null) {
            return $this->kompetensiRankingsCache;
        }

        // Fetch from service and cache
        $eventData = $this->getEventData();

        if (! $eventData) {
            return null;
        }

        $rankingService = app(RankingService::class);
        $this->kompetensiRankingsCache = $rankingService->getRankings(
            $eventData['event']->id,
            $eventData['position']->id,
            $eventData['position']->template_id,
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
     * OPTIMIZED: Use cached rankings and Slice -> Process strategy
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
        $potensiRankingsKeyed = $potensiRankings->keyBy('participant_id');
        $kompetensiRankingsKeyed = $kompetensiRankings->keyBy('participant_id');

        $totalItems = $rankings->count();
        $slicedRankings = $rankings;
        $currentPage = 1;

        // 🚀 OPTIMIZATION: Slice the collection FIRST
        // This ensures we only process (map/format) the 10 items we show, not all 5000+.
        if ($this->perPage !== 'all' && $this->perPage > 0) {
            $currentPage = $this->getPage();
            $offset = ($currentPage - 1) * $this->perPage;

            // Slice the RAW data first
            $slicedRankings = $rankings->slice($offset, $this->perPage);
        }

        // Map ONLY the sliced items to view format
        $items = $slicedRankings->map(function ($ranking) use ($potensiRankingsKeyed, $kompetensiRankingsKeyed) {
            $participantId = $ranking['participant_id'];
            $potensiRank = $potensiRankingsKeyed->get($participantId);
            $kompetensiRank = $kompetensiRankingsKeyed->get($participantId);

            // Calculate per-category scores
            $psyIndividual = $potensiRank ? $potensiRank['individual_score'] : 0;
            $mcIndividual = $kompetensiRank ? $kompetensiRank['individual_score'] : 0;
            $totalIndividual = $psyIndividual + $mcIndividual;

            // Calculate weighted scores
            $psyWeighted = $psyIndividual * ($this->potensiWeight / 100);
            $mcWeighted = $mcIndividual * ($this->kompetensiWeight / 100);

            return [
                'rank' => $ranking['rank'],
                'name' => $ranking['participant_name'], // Should be populated from optimized Service
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

        // Use appropriate items for paginator (sliced or all)
        return new LengthAwarePaginator(
            $items->values()->all(),
            $totalItems,
            $this->perPage === 'all' || $this->perPage === 0 ? ($totalItems > 0 ? $totalItems : 1) : $this->perPage,
            $currentPage,
            [
                'path' => LengthAwarePaginator::resolveCurrentPath(),
                'pageName' => 'page',
            ]
        );
    }
}
