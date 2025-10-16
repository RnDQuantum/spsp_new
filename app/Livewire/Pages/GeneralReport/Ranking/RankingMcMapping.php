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

    private function getConclusionText(float $percentageScore): string
    {
        // Conclusion based on percentage score relative to adjusted standard
        if ($percentageScore >= 110) {
            return 'Lebih Memenuhi/More Requirement';
        } elseif ($percentageScore >= 100) {
            return 'Memenuhi/Meet Requirement';
        } elseif ($percentageScore >= 90) {
            return 'Kurang Memenuhi/Below Requirement';
        } else {
            return 'Belum Memenuhi/Under Perform';
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
            ->with('template')
            ->where('code', $this->eventCode)
            ->first();

        if (! $event) {
            return null;
        }

        // Ambil kategori kompetensi
        $kompetensiCategory = CategoryType::query()
            ->where('template_id', $event->template_id)
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
                'conclusion' => $this->getConclusionText($adjustedPercentage),
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

        $event = AssessmentEvent::where('code', $this->eventCode)->first();
        if (! $event) {
            return ['total' => 0, 'passing' => 0, 'percentage' => 0];
        }

        $kompetensiCategory = CategoryType::query()
            ->where('template_id', $event->template_id)
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

        // Calculate passing based on adjusted percentage
        $passing = $aggregates->filter(function ($r) {
            $originalStandardScore = (float) $r->sum_original_standard_score;
            $individualScore = (float) $r->sum_individual_score;

            // Calculate adjusted standard based on tolerance
            $toleranceFactor = 1 - ($this->tolerancePercentage / 100);
            $adjustedStandardScore = $originalStandardScore * $toleranceFactor;

            // Calculate percentage based on adjusted standard
            $adjustedPercentage = $adjustedStandardScore > 0
                ? ($individualScore / $adjustedStandardScore) * 100
                : 0;

            // Count as passing if percentage >= 100% (Memenuhi or Lebih Memenuhi)
            return $adjustedPercentage >= 100;
        })->count();

        return [
            'total' => $total,
            'passing' => $passing,
            'percentage' => $total > 0 ? (int) round(($passing / $total) * 100) : 0,
        ];
    }

    public function getConclusionSummary(): array
    {
        // Get conclusion summary for all participants in current event
        if (! $this->eventCode) {
            return [];
        }

        $event = AssessmentEvent::where('code', $this->eventCode)->first();
        if (! $event) {
            return [];
        }

        $kompetensiCategory = CategoryType::query()
            ->where('template_id', $event->template_id)
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

        $conclusions = [
            'Lebih Memenuhi/More Requirement' => 0,
            'Memenuhi/Meet Requirement' => 0,
            'Kurang Memenuhi/Below Requirement' => 0,
            'Belum Memenuhi/Under Perform' => 0,
        ];

        foreach ($aggregates as $r) {
            $originalStandardScore = (float) $r->sum_original_standard_score;
            $individualScore = (float) $r->sum_individual_score;

            // Calculate adjusted standard based on tolerance
            $toleranceFactor = 1 - ($this->tolerancePercentage / 100);
            $adjustedStandardScore = $originalStandardScore * $toleranceFactor;

            // Calculate percentage based on adjusted standard
            $adjustedPercentage = $adjustedStandardScore > 0
                ? ($individualScore / $adjustedStandardScore) * 100
                : 0;

            // Determine conclusion
            $conclusion = $this->getConclusionText($adjustedPercentage);
            $conclusions[$conclusion]++;
        }

        return $conclusions;
    }

    public function render()
    {
        $rankings = $this->buildRankings();
        $conclusionSummary = $this->getConclusionSummary();

        return view('livewire.pages.general-report.ranking.ranking-mc-mapping', [
            'rankings' => $rankings,
            'conclusionSummary' => $conclusionSummary,
        ]);
    }
}
