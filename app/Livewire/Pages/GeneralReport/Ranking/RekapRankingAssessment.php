<?php

namespace App\Livewire\Pages\GeneralReport\Ranking;

use App\Models\AssessmentEvent;
use App\Models\CategoryType;
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

    public int $perPage = 10;

    public int $tolerancePercentage = 10;

    // Pie chart data
    public array $chartLabels = [];

    public array $chartData = [];

    public array $chartColors = [];

    // Conclusion configuration - single source of truth
    public array $conclusionConfig = [
        'Di Atas Standar' => [
            'chartColor' => '#10b981',
            'tailwindClass' => 'bg-green-100 border-green-300',
            'rangeText' => 'Gap > 0',
        ],
        'Memenuhi Standar' => [
            'chartColor' => '#3b82f6',
            'tailwindClass' => 'bg-blue-100 border-blue-300',
            'rangeText' => 'Gap ≥ Threshold',
        ],
        'Di Bawah Standar' => [
            'chartColor' => '#ef4444',
            'tailwindClass' => 'bg-red-100 border-red-300',
            'rangeText' => 'Gap < Threshold',
        ],
    ];

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

    public function handleEventSelected(?string $eventCode): void
    {
        $this->resetPage();

        // JANGAN refresh data di sini!
        // Position akan auto-reset dan trigger handlePositionSelected
    }

    public function handlePositionSelected(?int $positionFormationId): void
    {
        $this->resetPage();

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

    private function getStandardInfo(): ?array
    {
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

        // Get average standard scores (they should be same for all participants in this position, but we take first one)
        $firstParticipant = DB::table('aspect_assessments as aa')
            ->join('aspects as a', 'a.id', '=', 'aa.aspect_id')
            ->where('aa.event_id', $event->id)
            ->where('aa.position_formation_id', $positionFormationId)
            ->groupBy('aa.participant_id')
            ->selectRaw('SUM(CASE WHEN a.category_type_id = ? THEN aa.standard_score ELSE 0 END) as potensi_standard_score', [$potensiId])
            ->selectRaw('SUM(CASE WHEN a.category_type_id = ? THEN aa.standard_score ELSE 0 END) as kompetensi_standard_score', [$kompetensiId])
            ->first();

        if (! $firstParticipant) {
            return null;
        }

        $potStd = (float) $firstParticipant->potensi_standard_score;
        $komStd = (float) $firstParticipant->kompetensi_standard_score;

        // Calculate adjusted standard based on tolerance
        $toleranceFactor = 1 - $this->tolerancePercentage / 100;
        $adjustedPotStd = $potStd * $toleranceFactor;
        $adjustedKomStd = $komStd * $toleranceFactor;

        // Weighted standard
        $weightedPotStd = $adjustedPotStd * ($this->potensiWeight / 100);
        $weightedKomStd = $adjustedKomStd * ($this->kompetensiWeight / 100);
        $totalWeightedStd = $weightedPotStd + $weightedKomStd;

        // Threshold calculation
        $threshold = -$totalWeightedStd * ($this->tolerancePercentage / 100);

        return [
            'psy_standard' => round($weightedPotStd, 2),
            'mc_standard' => round($weightedKomStd, 2),
            'total_standard' => round($totalWeightedStd, 2),
            'threshold' => round($threshold, 2),
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

        // Get category weights from selected position's template
        $potensi = CategoryType::query()
            ->where('template_id', $position->template_id)
            ->where('code', 'potensi')
            ->first();

        $kompetensi = CategoryType::query()
            ->where('template_id', $position->template_id)
            ->where('code', 'kompetensi')
            ->first();

        $this->potensiWeight = (int) ($potensi?->weight_percentage ?? 0);
        $this->kompetensiWeight = (int) ($kompetensi?->weight_percentage ?? 0);
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
        $rows = null;
        $standardInfo = $this->getStandardInfo();

        $eventCode = session('filter.event_code');
        $positionFormationId = session('filter.position_formation_id');

        if ($eventCode && $positionFormationId && ($this->potensiWeight + $this->kompetensiWeight) > 0) {
            $event = AssessmentEvent::where('code', $eventCode)->first();

            if ($event) {
                // Get selected position with template
                $position = $event->positionFormations()
                    ->with('template')
                    ->find($positionFormationId);

                if ($position?->template) {
                    // Get categories from selected position's template
                    $potensi = CategoryType::where('template_id', $position->template_id)->where('code', 'potensi')->first();
                    $kompetensi = CategoryType::where('template_id', $position->template_id)->where('code', 'kompetensi')->first();

                    if ($potensi && $kompetensi) {
                        $potensiId = (int) $potensi->id;
                        $kompetensiId = (int) $kompetensi->id;

                        // Aggregate skor per peserta untuk kedua kategori melalui aspect_assessments + aspects - FILTER by position
                        $baseQuery = DB::table('aspect_assessments as aa')
                            ->join('aspects as a', 'a.id', '=', 'aa.aspect_id')
                            ->where('aa.event_id', $event->id)
                            ->where('aa.position_formation_id', $positionFormationId)
                            ->groupBy('aa.participant_id')
                            ->selectRaw('aa.participant_id as participant_id')
                            ->selectRaw('SUM(CASE WHEN a.category_type_id = ? THEN aa.individual_score ELSE 0 END) as potensi_individual_score', [$potensiId])
                            ->selectRaw('SUM(CASE WHEN a.category_type_id = ? THEN aa.standard_score ELSE 0 END) as potensi_standard_score', [$potensiId])
                            ->selectRaw('SUM(CASE WHEN a.category_type_id = ? THEN aa.individual_score ELSE 0 END) as kompetensi_individual_score', [$kompetensiId])
                            ->selectRaw('SUM(CASE WHEN a.category_type_id = ? THEN aa.standard_score ELSE 0 END) as kompetensi_standard_score', [$kompetensiId]);

                        // Hitung ordering berdasarkan total weighted individual
                        $orderingQuery = DB::query()->fromSub($baseQuery, 't')
                            ->select('*')
                            ->selectRaw('? * potensi_individual_score / 100 + ? * kompetensi_individual_score / 100 as total_weighted_individual', [$this->potensiWeight, $this->kompetensiWeight])
                            ->orderByDesc('total_weighted_individual');

                        /** @var LengthAwarePaginator $paginator */
                        $paginator = $orderingQuery->paginate($this->perPage)->withQueryString();

                        $currentPage = (int) $paginator->currentPage();
                        $perPage = (int) $paginator->perPage();
                        $startRank = ($currentPage - 1) * $perPage;

                        $items = collect($paginator->items())->values()->map(function ($row, int $index) use ($startRank) {
                            $participant = DB::table('participants')->where('id', $row->participant_id)->first();

                            // Get original values from database
                            $potInd = (float) $row->potensi_individual_score;
                            $potStd = (float) $row->potensi_standard_score;
                            $komInd = (float) $row->kompetensi_individual_score;
                            $komStd = (float) $row->kompetensi_standard_score;

                            // Calculate adjusted standard based on tolerance
                            $toleranceFactor = 1 - ($this->tolerancePercentage / 100);
                            $adjustedPotStd = $potStd * $toleranceFactor;
                            $adjustedKomStd = $komStd * $toleranceFactor;

                            // Individual scores
                            $totalInd = $potInd + $komInd;
                            $weightedPot = $potInd * ($this->potensiWeight / 100);
                            $weightedKom = $komInd * ($this->kompetensiWeight / 100);
                            $totalWeightedInd = $weightedPot + $weightedKom;

                            // Adjusted standard scores
                            $weightedPotStd = $adjustedPotStd * ($this->potensiWeight / 100);
                            $weightedKomStd = $adjustedKomStd * ($this->kompetensiWeight / 100);
                            $totalWeightedStd = $weightedPotStd + $weightedKomStd;

                            // Gap calculation based on adjusted standard
                            $gap = $totalWeightedInd - $totalWeightedStd;
                            $threshold = -$totalWeightedStd * ($this->tolerancePercentage / 100);
                            $conclusion = $gap > 0 ? 'Di Atas Standar' : ($gap >= $threshold ? 'Memenuhi Standar' : 'Di Bawah Standar');

                            return [
                                'rank' => $startRank + $index + 1,
                                'name' => $participant->name ?? '-',
                                'psy_individual' => round($potInd, 2),
                                'mc_individual' => round($komInd, 2),
                                'total_individual' => round($totalInd, 2),
                                'psy_weighted' => round($weightedPot, 2),
                                'mc_weighted' => round($weightedKom, 2),
                                'total_weighted_individual' => round($totalWeightedInd, 2),
                                'gap' => round($gap, 2),
                                'conclusion' => $conclusion,
                            ];
                        })->all();

                        $rows = new LengthAwarePaginator(
                            $items,
                            $paginator->total(),
                            $paginator->perPage(),
                            $paginator->currentPage(),
                            ['path' => $paginator->path(), 'query' => request()->query()]
                        );
                    }
                }
            }
        }

        $conclusionSummary = $this->getConclusionSummary();

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
        $eventCode = session('filter.event_code');
        $positionFormationId = session('filter.position_formation_id');

        if (! $eventCode || ! $positionFormationId || ($this->potensiWeight + $this->kompetensiWeight) === 0) {
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

        if (! $position?->template) {
            return ['total' => 0, 'passing' => 0, 'percentage' => 0];
        }

        // Get categories from selected position's template
        $potensi = CategoryType::where('template_id', $position->template_id)->where('code', 'potensi')->first();
        $kompetensi = CategoryType::where('template_id', $position->template_id)->where('code', 'kompetensi')->first();
        if (! $potensi || ! $kompetensi) {
            return ['total' => 0, 'passing' => 0, 'percentage' => 0];
        }

        $potensiId = (int) $potensi->id;
        $kompetensiId = (int) $kompetensi->id;

        $baseQuery = DB::table('aspect_assessments as aa')
            ->join('aspects as a', 'a.id', '=', 'aa.aspect_id')
            ->where('aa.event_id', $event->id)
            ->where('aa.position_formation_id', $positionFormationId)
            ->groupBy('aa.participant_id')
            ->selectRaw('aa.participant_id as participant_id')
            ->selectRaw('SUM(CASE WHEN a.category_type_id = ? THEN aa.individual_score ELSE 0 END) as potensi_individual_score', [$potensiId])
            ->selectRaw('SUM(CASE WHEN a.category_type_id = ? THEN aa.standard_score ELSE 0 END) as potensi_standard_score', [$potensiId])
            ->selectRaw('SUM(CASE WHEN a.category_type_id = ? THEN aa.individual_score ELSE 0 END) as kompetensi_individual_score', [$kompetensiId])
            ->selectRaw('SUM(CASE WHEN a.category_type_id = ? THEN aa.standard_score ELSE 0 END) as kompetensi_standard_score', [$kompetensiId]);

        $all = $baseQuery->get();

        $total = $all->count();
        $passing = 0;
        foreach ($all as $row) {
            $potInd = (float) $row->potensi_individual_score;
            $potStd = (float) $row->potensi_standard_score;
            $komInd = (float) $row->kompetensi_individual_score;
            $komStd = (float) $row->kompetensi_standard_score;

            // Calculate adjusted standard based on tolerance
            $toleranceFactor = 1 - ($this->tolerancePercentage / 100);
            $adjustedPotStd = $potStd * $toleranceFactor;
            $adjustedKomStd = $komStd * $toleranceFactor;

            $weightedPot = $potInd * ($this->potensiWeight / 100);
            $weightedKom = $komInd * ($this->kompetensiWeight / 100);
            $totalWeightedInd = $weightedPot + $weightedKom;

            $weightedPotStd = $adjustedPotStd * ($this->potensiWeight / 100);
            $weightedKomStd = $adjustedKomStd * ($this->kompetensiWeight / 100);
            $totalWeightedStd = $weightedPotStd + $weightedKomStd;

            $gap = $totalWeightedInd - $totalWeightedStd;
            $threshold = -$totalWeightedStd * ($this->tolerancePercentage / 100);
            if ($gap > 0 || $gap >= $threshold) {
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
        // Get conclusion summary for all participants in current event + position
        $eventCode = session('filter.event_code');
        $positionFormationId = session('filter.position_formation_id');

        if (! $eventCode || ! $positionFormationId || ($this->potensiWeight + $this->kompetensiWeight) === 0) {
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

        if (! $position?->template) {
            return [];
        }

        // Get categories from selected position's template
        $potensi = CategoryType::where('template_id', $position->template_id)->where('code', 'potensi')->first();
        $kompetensi = CategoryType::where('template_id', $position->template_id)->where('code', 'kompetensi')->first();
        if (! $potensi || ! $kompetensi) {
            return [];
        }

        $potensiId = (int) $potensi->id;
        $kompetensiId = (int) $kompetensi->id;

        $baseQuery = DB::table('aspect_assessments as aa')
            ->join('aspects as a', 'a.id', '=', 'aa.aspect_id')
            ->where('aa.event_id', $event->id)
            ->where('aa.position_formation_id', $positionFormationId)
            ->groupBy('aa.participant_id')
            ->selectRaw('aa.participant_id as participant_id')
            ->selectRaw('SUM(CASE WHEN a.category_type_id = ? THEN aa.individual_score ELSE 0 END) as potensi_individual_score', [$potensiId])
            ->selectRaw('SUM(CASE WHEN a.category_type_id = ? THEN aa.standard_score ELSE 0 END) as potensi_standard_score', [$potensiId])
            ->selectRaw('SUM(CASE WHEN a.category_type_id = ? THEN aa.individual_score ELSE 0 END) as kompetensi_individual_score', [$kompetensiId])
            ->selectRaw('SUM(CASE WHEN a.category_type_id = ? THEN aa.standard_score ELSE 0 END) as kompetensi_standard_score', [$kompetensiId]);

        $all = $baseQuery->get();

        $conclusions = [
            'Di Atas Standar' => 0,
            'Memenuhi Standar' => 0,
            'Di Bawah Standar' => 0,
        ];

        foreach ($all as $row) {
            $potInd = (float) $row->potensi_individual_score;
            $potStd = (float) $row->potensi_standard_score;
            $komInd = (float) $row->kompetensi_individual_score;
            $komStd = (float) $row->kompetensi_standard_score;

            // Calculate adjusted standard based on tolerance
            $toleranceFactor = 1 - ($this->tolerancePercentage / 100);
            $adjustedPotStd = $potStd * $toleranceFactor;
            $adjustedKomStd = $komStd * $toleranceFactor;

            $weightedPot = $potInd * ($this->potensiWeight / 100);
            $weightedKom = $komInd * ($this->kompetensiWeight / 100);
            $totalWeightedInd = $weightedPot + $weightedKom;

            $weightedPotStd = $adjustedPotStd * ($this->potensiWeight / 100);
            $weightedKomStd = $adjustedKomStd * ($this->kompetensiWeight / 100);
            $totalWeightedStd = $weightedPotStd + $weightedKomStd;

            $gap = $totalWeightedInd - $totalWeightedStd;
            $threshold = -$totalWeightedStd * ($this->tolerancePercentage / 100);

            // Determine conclusion based on gap and tolerance threshold
            if ($gap > 0) {
                $conclusion = 'Di Atas Standar';
            } elseif ($gap >= $threshold) {
                $conclusion = 'Memenuhi Standar';
            } else {
                $conclusion = 'Di Bawah Standar';
            }

            $conclusions[$conclusion]++;
        }

        return $conclusions;
    }
}
