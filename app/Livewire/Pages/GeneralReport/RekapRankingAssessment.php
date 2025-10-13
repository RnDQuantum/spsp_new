<?php

namespace App\Livewire\Pages\GeneralReport;

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

    public ?string $eventCode = null;

    /** @var array<int, array{code:string,name:string}> */
    public array $availableEvents = [];

    public int $potensiWeight = 0;

    public int $kompetensiWeight = 0;

    public int $perPage = 10;

    public int $tolerancePercentage = 10;

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

        $this->loadWeights();
    }

    public function updatedEventCode(): void
    {
        $this->loadWeights();
        $this->resetPage();
    }

    public function handleToleranceUpdate(int $tolerance): void
    {
        $this->tolerancePercentage = $tolerance;
        $summary = $this->getPassingSummary();
        $this->dispatch('summary-updated', [
            'passing' => $summary['passing'],
            'total' => $summary['total'],
            'percentage' => $summary['percentage'],
        ]);
    }

    private function loadWeights(): void
    {
        $this->potensiWeight = 0;
        $this->kompetensiWeight = 0;

        if (! $this->eventCode) {
            return;
        }

        $event = AssessmentEvent::query()
            ->with('template')
            ->where('code', $this->eventCode)
            ->first();

        if (! $event) {
            return;
        }

        $potensi = CategoryType::query()
            ->where('template_id', $event->template_id)
            ->where('code', 'potensi')
            ->first();

        $kompetensi = CategoryType::query()
            ->where('template_id', $event->template_id)
            ->where('code', 'kompetensi')
            ->first();

        $this->potensiWeight = (int) ($potensi?->weight_percentage ?? 0);
        $this->kompetensiWeight = (int) ($kompetensi?->weight_percentage ?? 0);
    }

    public function render()
    {
        $rows = null;

        if ($this->eventCode && ($this->potensiWeight + $this->kompetensiWeight) > 0) {
            $event = AssessmentEvent::where('code', $this->eventCode)->first();

            if ($event) {
                // Ambil id kategori
                $potensi = CategoryType::where('template_id', $event->template_id)->where('code', 'potensi')->first();
                $kompetensi = CategoryType::where('template_id', $event->template_id)->where('code', 'kompetensi')->first();

                if ($potensi && $kompetensi) {
                    $potensiId = (int) $potensi->id;
                    $kompetensiId = (int) $kompetensi->id;

                    // Aggregate skor per peserta untuk kedua kategori melalui aspect_assessments + aspects
                    $baseQuery = DB::table('aspect_assessments as aa')
                        ->join('aspects as a', 'a.id', '=', 'aa.aspect_id')
                        ->where('aa.event_id', $event->id)
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

                        $potInd = (float) $row->potensi_individual_score;
                        $potStd = (float) $row->potensi_standard_score;
                        $komInd = (float) $row->kompetensi_individual_score;
                        $komStd = (float) $row->kompetensi_standard_score;

                        $totalInd = $potInd + $komInd;
                        $weightedPot = $potInd * ($this->potensiWeight / 100);
                        $weightedKom = $komInd * ($this->kompetensiWeight / 100);
                        $totalWeightedInd = $weightedPot + $weightedKom;

                        $weightedPotStd = $potStd * ($this->potensiWeight / 100);
                        $weightedKomStd = $komStd * ($this->kompetensiWeight / 100);
                        $totalWeightedStd = $weightedPotStd + $weightedKomStd;

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

        return view('livewire.pages.general-report.rekap-ranking-assessment', [
            'availableEvents' => $this->availableEvents,
            'potensiWeight' => $this->potensiWeight,
            'kompetensiWeight' => $this->kompetensiWeight,
            'rows' => $rows,
        ]);
    }

    public function getPassingSummary(): array
    {
        if (! $this->eventCode || ($this->potensiWeight + $this->kompetensiWeight) === 0) {
            return ['total' => 0, 'passing' => 0, 'percentage' => 0];
        }

        $event = AssessmentEvent::where('code', $this->eventCode)->first();
        if (! $event) {
            return ['total' => 0, 'passing' => 0, 'percentage' => 0];
        }

        $potensi = CategoryType::where('template_id', $event->template_id)->where('code', 'potensi')->first();
        $kompetensi = CategoryType::where('template_id', $event->template_id)->where('code', 'kompetensi')->first();
        if (! $potensi || ! $kompetensi) {
            return ['total' => 0, 'passing' => 0, 'percentage' => 0];
        }

        $potensiId = (int) $potensi->id;
        $kompetensiId = (int) $kompetensi->id;

        $baseQuery = DB::table('aspect_assessments as aa')
            ->join('aspects as a', 'a.id', '=', 'aa.aspect_id')
            ->where('aa.event_id', $event->id)
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

            $weightedPot = $potInd * ($this->potensiWeight / 100);
            $weightedKom = $komInd * ($this->kompetensiWeight / 100);
            $totalWeightedInd = $weightedPot + $weightedKom;

            $weightedPotStd = $potStd * ($this->potensiWeight / 100);
            $weightedKomStd = $komStd * ($this->kompetensiWeight / 100);
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
}
