<?php

namespace App\Livewire\Pages\GeneralReport\Ranking;

use App\Models\Aspect;
use App\Models\AssessmentEvent;
use App\Models\CategoryType;
use App\Services\DynamicStandardService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app', ['title' => 'Ranking Rekap Skor Penilaian Akhir Assessment'])]
class RekapRankingAssessment extends Component
{
    use WithPagination;

    public int $potensiWeight = 0;

    public int $kompetensiWeight = 0;

    public $perPage = 10; // Changed from int to allow 'all'

    public int $tolerancePercentage = 10;

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

    private ?array $aggregatesCache = null;

    protected $listeners = [
        'event-selected' => 'handleEventSelected',
        'position-selected' => 'handlePositionSelected',
        'tolerance-updated' => 'handleToleranceUpdate',
    ];

    public function mount(): void
    {
        $this->tolerancePercentage = session('individual_report.tolerance', 10);

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
        $this->adjustedStandardsCache = null;
        $this->aggregatesCache = null;
    }

    /**
     * Get adjusted standard values from session or database for both categories
     * OPTIMIZED: Cache result untuk menghindari kalkulasi berulang
     */
    private function getAdjustedStandardValues(
        int $templateId,
        array $potensiAspectIds,
        array $kompetensiAspectIds,
        float $originalPotensiStandardScore,
        float $originalKompetensiStandardScore
    ): array {
        // Gunakan cache jika sudah ada
        if ($this->adjustedStandardsCache !== null) {
            return $this->adjustedStandardsCache;
        }

        $standardService = app(DynamicStandardService::class);

        // Check adjustments for both categories
        $hasPotensiAdjustments = $standardService->hasCategoryAdjustments($templateId, 'potensi');
        $hasKompetensiAdjustments = $standardService->hasCategoryAdjustments($templateId, 'kompetensi');

        // Initialize with original values
        $adjustedPotensiScore = $originalPotensiStandardScore;
        $adjustedKompetensiScore = $originalKompetensiStandardScore;

        // Recalculate Potensi if there are adjustments
        if ($hasPotensiAdjustments) {
            $potensiScore = 0;
            $aspects = Aspect::whereIn('id', $potensiAspectIds)
                ->with('subAspects')
                ->orderBy('order')
                ->get();

            foreach ($aspects as $aspect) {
                if (! $standardService->isAspectActive($templateId, $aspect->code)) {
                    continue;
                }

                $aspectWeight = $standardService->getAspectWeight($templateId, $aspect->code);
                $aspectRating = $standardService->getAspectRating($templateId, $aspect->code);

                // For Potensi, calculate based on sub-aspects if they exist
                if ($aspect->subAspects && $aspect->subAspects->count() > 0) {
                    $subAspectRatingSum = 0;
                    $activeSubAspectsCount = 0;

                    foreach ($aspect->subAspects as $subAspect) {
                        if (! $standardService->isSubAspectActive($templateId, $subAspect->code)) {
                            continue;
                        }

                        $subRating = $standardService->getSubAspectRating($templateId, $subAspect->code);
                        $subAspectRatingSum += $subRating;
                        $activeSubAspectsCount++;
                    }

                    if ($activeSubAspectsCount > 0) {
                        // FIXED: Round aspect rating to match StandardPsikometrik calculation
                        $aspectRating = round($subAspectRatingSum / $activeSubAspectsCount, 2);
                    }
                }

                // FIXED: Round aspect score to match StandardPsikometrik calculation
                $aspectScore = round($aspectRating * $aspectWeight, 2);
                $potensiScore += $aspectScore;
            }

            $adjustedPotensiScore = $potensiScore;
        }

        // Recalculate Kompetensi if there are adjustments
        if ($hasKompetensiAdjustments) {
            $kompetensiScore = 0;
            $aspects = Aspect::whereIn('id', $kompetensiAspectIds)
                ->orderBy('order')
                ->get();

            foreach ($aspects as $aspect) {
                if (! $standardService->isAspectActive($templateId, $aspect->code)) {
                    continue;
                }

                $aspectWeight = $standardService->getAspectWeight($templateId, $aspect->code);
                $aspectRating = $standardService->getAspectRating($templateId, $aspect->code);

                // FIXED: Round aspect score to match StandardMc calculation
                $aspectScore = round($aspectRating * $aspectWeight, 2);
                $kompetensiScore += $aspectScore;
            }

            $adjustedKompetensiScore = $kompetensiScore;
        }

        // Cache result
        $this->adjustedStandardsCache = [
            'potensi_standard_score' => $adjustedPotensiScore,
            'kompetensi_standard_score' => $adjustedKompetensiScore,
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

        if (! $eventCode || ! $positionFormationId || ($this->potensiWeight + $this->kompetensiWeight) === 0) {
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

        // Get categories from selected position's template
        $potensi = CategoryType::where('template_id', $position->template_id)->where('code', 'potensi')->first();
        $kompetensi = CategoryType::where('template_id', $position->template_id)->where('code', 'kompetensi')->first();
        if (! $potensi || ! $kompetensi) {
            return null;
        }

        $potensiId = (int) $potensi->id;
        $kompetensiId = (int) $kompetensi->id;

        // Get aspect IDs for both categories
        $potensiAspectIds = Aspect::where('category_type_id', $potensiId)
            ->orderBy('order')
            ->pluck('id')
            ->all();

        $kompetensiAspectIds = Aspect::where('category_type_id', $kompetensiId)
            ->orderBy('order')
            ->pluck('id')
            ->all();

        // Get ALL aggregates at once
        $aggregates = DB::table('aspect_assessments as aa')
            ->join('aspects as a', 'a.id', '=', 'aa.aspect_id')
            ->where('aa.event_id', $event->id)
            ->where('aa.position_formation_id', $positionFormationId)
            ->groupBy('aa.participant_id')
            ->selectRaw('aa.participant_id as participant_id')
            ->selectRaw('SUM(CASE WHEN a.category_type_id = ? THEN aa.individual_score ELSE 0 END) as potensi_individual_score', [$potensiId])
            ->selectRaw('SUM(CASE WHEN a.category_type_id = ? THEN aa.standard_score ELSE 0 END) as potensi_standard_score', [$potensiId])
            ->selectRaw('SUM(CASE WHEN a.category_type_id = ? THEN aa.individual_score ELSE 0 END) as kompetensi_individual_score', [$kompetensiId])
            ->selectRaw('SUM(CASE WHEN a.category_type_id = ? THEN aa.standard_score ELSE 0 END) as kompetensi_standard_score', [$kompetensiId])
            ->get();

        if ($aggregates->isEmpty()) {
            return null;
        }

        // Get adjusted standards ONCE
        $firstAggregate = $aggregates->first();
        $adjustedStandards = $this->getAdjustedStandardValues(
            $position->template_id,
            $potensiAspectIds,
            $kompetensiAspectIds,
            (float) $firstAggregate->potensi_standard_score,
            (float) $firstAggregate->kompetensi_standard_score
        );

        // Cache everything
        $this->aggregatesCache = [
            'event' => $event,
            'position' => $position,
            'potensi' => $potensi,
            'kompetensi' => $kompetensi,
            'potensiId' => $potensiId,
            'kompetensiId' => $kompetensiId,
            'aggregates' => $aggregates,
            'adjustedStandards' => $adjustedStandards,
        ];

        return $this->aggregatesCache;
    }

    private function getStandardInfo(): ?array
    {
        $data = $this->getAggregatesData();

        if (! $data) {
            return null;
        }

        $adjustedStandards = $data['adjustedStandards'];
        $potStd = $adjustedStandards['potensi_standard_score'];
        $komStd = $adjustedStandards['kompetensi_standard_score'];

        // Calculate adjusted standard based on tolerance
        $toleranceFactor = 1 - $this->tolerancePercentage / 100;
        $adjustedPotStd = $potStd * $toleranceFactor;
        $adjustedKomStd = $komStd * $toleranceFactor;

        // Weighted standard
        $weightedPotStd = $adjustedPotStd * ($this->potensiWeight / 100);
        $weightedKomStd = $adjustedKomStd * ($this->kompetensiWeight / 100);
        $totalWeightedStd = $weightedPotStd + $weightedKomStd;

        return [
            // Original standards (from session, BEFORE tolerance and weighting)
            'psy_original_standard' => round($potStd, 2),
            'mc_original_standard' => round($komStd, 2),
            'total_original_standard' => round($potStd * ($this->potensiWeight / 100) + $komStd * ($this->kompetensiWeight / 100), 2),

            // Adjusted standards (AFTER tolerance, BEFORE weighting)
            'psy_adjusted_standard' => round($adjustedPotStd, 2),
            'mc_adjusted_standard' => round($adjustedKomStd, 2),

            // Weighted standards (AFTER both tolerance AND weighting) - used for GAP calculation
            'psy_standard' => round($weightedPotStd, 2),
            'mc_standard' => round($weightedKomStd, 2),
            'total_standard' => round($totalWeightedStd, 2),
        ];
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

    private function loadWeights(): void
    {
        $this->potensiWeight = 0;
        $this->kompetensiWeight = 0;

        $eventCode = session('filter.event_code');
        $positionFormationId = session('filter.position_formation_id');

        if (! $eventCode || ! $positionFormationId) {
            return;
        }

        $event = AssessmentEvent::query()
            ->where('code', $eventCode)
            ->first();

        if (! $event) {
            return;
        }

        // Get selected position with template
        $position = $event->positionFormations()
            ->with('template')
            ->find($positionFormationId);

        if (! $position || ! $position->template) {
            return;
        }

        $standardService = app(DynamicStandardService::class);

        // Get category weights from selected position's template (with session adjustments)
        $potensi = CategoryType::query()
            ->where('template_id', $position->template_id)
            ->where('code', 'potensi')
            ->first();

        $kompetensi = CategoryType::query()
            ->where('template_id', $position->template_id)
            ->where('code', 'kompetensi')
            ->first();

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

    public function getPassingSummary(): array
    {
        $data = $this->getAggregatesData();

        if (! $data) {
            return ['total' => 0, 'passing' => 0, 'percentage' => 0];
        }

        $aggregates = $data['aggregates'];
        $adjustedStandards = $data['adjustedStandards'];
        $potStd = $adjustedStandards['potensi_standard_score'];
        $komStd = $adjustedStandards['kompetensi_standard_score'];

        // Calculate adjusted standard based on tolerance
        $toleranceFactor = 1 - ($this->tolerancePercentage / 100);
        $adjustedPotStd = $potStd * $toleranceFactor;
        $adjustedKomStd = $komStd * $toleranceFactor;

        $total = $aggregates->count();
        $passing = 0;

        foreach ($aggregates as $row) {
            $potInd = (float) $row->potensi_individual_score;
            $komInd = (float) $row->kompetensi_individual_score;

            $weightedPot = $potInd * ($this->potensiWeight / 100);
            $weightedKom = $komInd * ($this->kompetensiWeight / 100);
            $totalWeightedInd = $weightedPot + $weightedKom;

            $weightedPotStd = $adjustedPotStd * ($this->potensiWeight / 100);
            $weightedKomStd = $adjustedKomStd * ($this->kompetensiWeight / 100);
            $totalWeightedStd = $weightedPotStd + $weightedKomStd;

            // Calculate original gap (at tolerance 0)
            $originalWeightedStd = $potStd * ($this->potensiWeight / 100) + $komStd * ($this->kompetensiWeight / 100);
            $originalGap = $totalWeightedInd - $originalWeightedStd;

            $adjustedGap = $totalWeightedInd - $totalWeightedStd;

            // Passing = Di Atas Standar OR Memenuhi Standar
            if ($originalGap >= 0 || $adjustedGap >= 0) {
                $passing++;
            }
        }

        return [
            'total' => $total,
            'passing' => $passing,
            'percentage' => $total > 0 ? (int) round(($passing / $total) * 100) : 0,
        ];
    }

    public function getConclusionSummary(): array
    {
        $data = $this->getAggregatesData();

        if (! $data) {
            return [];
        }

        $aggregates = $data['aggregates'];
        $adjustedStandards = $data['adjustedStandards'];
        $potStd = $adjustedStandards['potensi_standard_score'];
        $komStd = $adjustedStandards['kompetensi_standard_score'];

        // Calculate adjusted standard based on tolerance
        $toleranceFactor = 1 - ($this->tolerancePercentage / 100);
        $adjustedPotStd = $potStd * $toleranceFactor;
        $adjustedKomStd = $komStd * $toleranceFactor;

        // Initialize conclusions from config
        $conclusions = array_fill_keys(array_keys($this->conclusionConfig), 0);

        foreach ($aggregates as $row) {
            $potInd = (float) $row->potensi_individual_score;
            $komInd = (float) $row->kompetensi_individual_score;

            $weightedPot = $potInd * ($this->potensiWeight / 100);
            $weightedKom = $komInd * ($this->kompetensiWeight / 100);
            $totalWeightedInd = $weightedPot + $weightedKom;

            $weightedPotStd = $adjustedPotStd * ($this->potensiWeight / 100);
            $weightedKomStd = $adjustedKomStd * ($this->kompetensiWeight / 100);
            $totalWeightedStd = $weightedPotStd + $weightedKomStd;

            // Calculate original gap (at tolerance 0)
            $originalWeightedStd = $potStd * ($this->potensiWeight / 100) + $komStd * ($this->kompetensiWeight / 100);
            $originalGap = $totalWeightedInd - $originalWeightedStd;

            $adjustedGap = $totalWeightedInd - $totalWeightedStd;

            // Determine conclusion
            $conclusion = $this->getConclusionText($originalGap, $adjustedGap);
            $conclusions[$conclusion]++;
        }

        return $conclusions;
    }

    private function buildRankings(): ?LengthAwarePaginator
    {
        $data = $this->getAggregatesData();

        if (! $data) {
            return null;
        }

        $aggregates = $data['aggregates'];
        $adjustedStandards = $data['adjustedStandards'];
        $potStd = $adjustedStandards['potensi_standard_score'];
        $komStd = $adjustedStandards['kompetensi_standard_score'];

        // Calculate adjusted standard based on tolerance ONCE
        $toleranceFactor = 1 - ($this->tolerancePercentage / 100);
        $adjustedPotStd = $potStd * $toleranceFactor;
        $adjustedKomStd = $komStd * $toleranceFactor;

        // Sort aggregates by total weighted individual score
        $sortedAggregates = $aggregates->sortByDesc(function ($row) {
            $potInd = (float) $row->potensi_individual_score;
            $komInd = (float) $row->kompetensi_individual_score;

            return ($potInd * ($this->potensiWeight / 100)) + ($komInd * ($this->kompetensiWeight / 100));
        })->values();

        // Check if "Show All" is selected
        if ($this->perPage === 'all' || $this->perPage === 0) {
            $items = $sortedAggregates->map(function ($row, int $index) use ($adjustedPotStd, $adjustedKomStd): array {
                return $this->mapAggregateToRankingItem($row, $index, $adjustedPotStd, $adjustedKomStd);
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

        $paginatedAggregates = $sortedAggregates->slice($offset, $this->perPage)->values();

        $items = $paginatedAggregates->map(function ($row, int $index) use ($offset, $adjustedPotStd, $adjustedKomStd): array {
            return $this->mapAggregateToRankingItem($row, $offset + $index, $adjustedPotStd, $adjustedKomStd);
        })->all();

        return new LengthAwarePaginator(
            $items,
            $sortedAggregates->count(),
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
        float $adjustedPotStd,
        float $adjustedKomStd
    ): array {
        $participant = DB::table('participants')->where('id', $row->participant_id)->first();

        $potInd = (float) $row->potensi_individual_score;
        $potStd = (float) $row->potensi_standard_score;
        $komInd = (float) $row->kompetensi_individual_score;
        $komStd = (float) $row->kompetensi_standard_score;

        // Individual scores
        $totalInd = $potInd + $komInd;
        $weightedPot = $potInd * ($this->potensiWeight / 100);
        $weightedKom = $komInd * ($this->kompetensiWeight / 100);
        $totalWeightedInd = $weightedPot + $weightedKom;

        // Adjusted standard scores
        $weightedPotStd = $adjustedPotStd * ($this->potensiWeight / 100);
        $weightedKomStd = $adjustedKomStd * ($this->kompetensiWeight / 100);
        $totalWeightedStd = $weightedPotStd + $weightedKomStd;

        // Calculate original gap (at tolerance 0)
        $originalWeightedStd = $potStd * ($this->potensiWeight / 100) + $komStd * ($this->kompetensiWeight / 100);
        $originalGap = $totalWeightedInd - $originalWeightedStd;

        // Gap calculation based on adjusted standard
        $adjustedGap = $totalWeightedInd - $totalWeightedStd;
        $conclusion = $this->getConclusionText($originalGap, $adjustedGap);

        return [
            'rank' => $index + 1,
            'name' => $participant->name ?? '-',
            'psy_individual' => round($potInd, 2),
            'mc_individual' => round($komInd, 2),
            'total_individual' => round($totalInd, 2),
            'psy_weighted' => round($weightedPot, 2),
            'mc_weighted' => round($weightedKom, 2),
            'total_weighted_individual' => round($totalWeightedInd, 2),
            'gap' => round($adjustedGap, 2),
            'conclusion' => $conclusion,
        ];
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
}
