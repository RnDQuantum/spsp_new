<?php

namespace App\Livewire\Pages\GeneralReport;

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

    public ?string $eventCode = null;

    /** @var array<int, array{code:string,name:string}> */
    public array $availableEvents = [];

    public int $perPage = 10;

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
    }

    public function updatedEventCode(): void
    {
        $this->resetPage();

        $summary = $this->getPassingSummary();
        $this->dispatch('summary-updated', [
            'passing' => $summary['passing'],
            'total' => $summary['total'],
            'percentage' => $summary['percentage'],
        ]);
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

    private function buildRankings(): ?LengthAwarePaginator
    {
        if (! $this->eventCode) {
            return null;
        }

        $event = AssessmentEvent::query()
            ->with('template')
            ->where('code', $this->eventCode)
            ->first();

        if (! $event) {
            return null;
        }

        $potensiCategory = CategoryType::query()
            ->where('template_id', $event->template_id)
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

        // Aggregate by participant in DB and paginate
        $query = AspectAssessment::query()
            ->selectRaw('participant_id, SUM(standard_rating) as sum_standard_rating, SUM(standard_score) as sum_standard_score, SUM(individual_rating) as sum_individual_rating, SUM(individual_score) as sum_individual_score, SUM(gap_score) as sum_gap_score')
            ->where('event_id', $event->id)
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
            $totalGapScore = (float) $row->sum_gap_score;

            return [
                'rank' => $startRank + $index + 1,
                'nip' => $participant?->skb_number ?? $participant?->test_number ?? '-',
                'name' => $participant?->name ?? '-',
                'position' => $participant?->positionFormation?->name ?? '-',
                'rating' => round((float) $row->sum_individual_rating, 2),
                'score' => round((float) $row->sum_individual_score, 2),
                'conclusion' => $this->overallConclusionText($totalGapScore),
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
        // Summary across all participants for current event (not only current page)
        if (! $this->eventCode) {
            return ['total' => 0, 'passing' => 0, 'percentage' => 0];
        }

        $event = AssessmentEvent::where('code', $this->eventCode)->first();
        if (! $event) {
            return ['total' => 0, 'passing' => 0, 'percentage' => 0];
        }

        $potensiCategory = CategoryType::query()
            ->where('template_id', $event->template_id)
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
            ->selectRaw('participant_id, SUM(gap_score) as sum_gap_score')
            ->where('event_id', $event->id)
            ->whereIn('aspect_id', $potensiAspectIds)
            ->groupBy('participant_id')
            ->get();

        $total = $aggregates->count();
        $passing = $aggregates->filter(fn ($r) => (float) $r->sum_gap_score >= 0)->count();

        return [
            'total' => $total,
            'passing' => $passing,
            'percentage' => $total > 0 ? (int) round(($passing / $total) * 100) : 0,
        ];
    }

    public function render()
    {
        $rankings = $this->buildRankings();

        return view('livewire.pages.general-report.ranking-psy-mapping', [
            'rankings' => $rankings,
        ]);
    }
}
