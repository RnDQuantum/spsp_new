<?php

namespace App\Livewire\Pages\GeneralReport;

use App\Models\Aspect;
use App\Models\AssessmentEvent;
use App\Models\CategoryType;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('components.layouts.app', ['title' => 'Kurva Distribusi Frekuensi'])]
class Statistic extends Component
{
    #[Url(as: 'event')]
    public ?string $eventCode = null;

    /** @var array<int, array{code:string,name:string}> */
    public array $availableEvents = [];

    #[Url(as: 'aspect')]
    public $aspectId = null; // cast to int internally

    /** @var array<int, array{id:int,name:string,category:string}> */
    public array $availableAspects = [];

    /** @var array<int,int> */
    public array $distribution = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];

    public float $standardRating = 0.0;

    public float $averageRating = 0.0;

    public string $chartId = '';

    public function mount(): void
    {
        $this->chartId = 'statistic'.uniqid();
        $this->availableEvents = AssessmentEvent::query()
            ->orderByDesc('start_date')
            ->get(['code', 'name'])
            ->map(fn ($e) => ['code' => $e->code, 'name' => $e->name])
            ->all();

        if (! $this->eventCode) {
            $this->eventCode = $this->availableEvents[0]['code'] ?? null;
        }

        $this->loadAspects();

        if (! $this->aspectId && isset($this->availableAspects[0]['id'])) {
            $this->aspectId = (int) $this->availableAspects[0]['id'];
        }

        $this->refreshStatistics();
    }

    public function updatedEventCode(): void
    {
        $previousAspectId = (int) $this->aspectId;
        $this->loadAspects();

        // Preserve aspect if still available for the new event/template; otherwise choose first
        $availableIds = collect($this->availableAspects)->pluck('id')->all();
        if (! in_array($previousAspectId, $availableIds, true)) {
            $this->aspectId = isset($this->availableAspects[0]['id']) ? (int) $this->availableAspects[0]['id'] : null;
        } else {
            $this->aspectId = $previousAspectId;
        }

        $this->refreshStatistics();
        $this->dispatch('chartDataUpdated', [
            'chartId' => $this->chartId,
            'labels' => ['I', 'II', 'III', 'IV', 'V'],
            'data' => array_values($this->distribution),
            'standardRating' => $this->standardRating,
            'averageRating' => $this->averageRating,
            'aspectName' => $this->getCurrentAspectName(),
        ]);
    }

    public function updatedAspectId(): void
    {
        $this->aspectId = (int) $this->aspectId;
        $this->refreshStatistics();
        $this->dispatch('chartDataUpdated', [
            'chartId' => $this->chartId,
            'labels' => ['I', 'II', 'III', 'IV', 'V'],
            'data' => array_values($this->distribution),
            'standardRating' => $this->standardRating,
            'averageRating' => $this->averageRating,
            'aspectName' => $this->getCurrentAspectName(),
        ]);
    }

    private function loadAspects(): void
    {
        $this->availableAspects = [];

        if (! $this->eventCode) {
            return;
        }

        $event = AssessmentEvent::query()->where('code', $this->eventCode)->first();
        if (! $event) {
            return;
        }

        // Get all unique template IDs used by positions in this event
        $templateIds = $event->positionFormations()
            ->distinct()
            ->pluck('template_id')
            ->all();

        if (empty($templateIds)) {
            return;
        }

        // Get category types from all templates used in this event
        $categoryTypes = CategoryType::query()
            ->whereIn('template_id', $templateIds)
            ->get(['id', 'code']);

        $categoryIdToCode = $categoryTypes->pluck('code', 'id');

        // Get aspects from all templates used in this event
        $aspects = Aspect::query()
            ->join('category_types', 'aspects.category_type_id', '=', 'category_types.id')
            ->whereIn('aspects.template_id', $templateIds)
            ->orderByRaw("CASE WHEN LOWER(category_types.code) = 'potensi' THEN 0 ELSE 1 END")
            ->orderBy('aspects.order')
            ->get(['aspects.id', 'aspects.name', 'aspects.category_type_id']);

        $this->availableAspects = $aspects->map(function ($a) use ($categoryIdToCode) {
            return [
                'id' => (int) $a->id,
                'name' => (string) $a->name,
                'category' => (string) ($categoryIdToCode[$a->category_type_id] ?? ''),
            ];
        })->all();
    }

    private function refreshStatistics(): void
    {
        $this->distribution = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
        $this->standardRating = 0.0;
        $this->averageRating = 0.0;

        if (! $this->eventCode || ! $this->aspectId) {
            return;
        }

        $event = AssessmentEvent::query()->where('code', $this->eventCode)->first();
        if (! $event) {
            return;
        }

        $aspectId = (int) $this->aspectId;

        // Get standard rating from master aspect
        $aspect = Aspect::query()->where('id', $aspectId)->first();
        $this->standardRating = (float) ($aspect?->standard_rating ?? 0.0);

        // Build distribution (bucket 1..5)
        $rows = DB::table('aspect_assessments as aa')
            ->where('aa.event_id', $event->id)
            ->where('aa.aspect_id', $aspectId)
            ->selectRaw('ROUND(aa.individual_rating) as bucket, COUNT(*) as total')
            ->groupBy('bucket')
            ->get();

        foreach ($rows as $row) {
            $bucket = (int) $row->bucket;
            if ($bucket >= 1 && $bucket <= 5) {
                $this->distribution[$bucket] = (int) $row->total;
            }
        }

        // Compute average aspect rating for this event/aspect
        $avg = DB::table('aspect_assessments as aa')
            ->where('aa.event_id', $event->id)
            ->where('aa.aspect_id', $aspectId)
            ->avg('aa.individual_rating');

        $this->averageRating = round((float) $avg, 2);
    }

    private function getCurrentAspectName(): string
    {
        $current = collect($this->availableAspects)->firstWhere('id', (int) $this->aspectId);

        return $current['name'] ?? '';
    }

    public function render()
    {
        return view('livewire.pages.general-report.statistic', [
            'availableEvents' => $this->availableEvents,
            'availableAspects' => $this->availableAspects,
            'distribution' => $this->distribution,
            'standardRating' => $this->standardRating,
            'averageRating' => $this->averageRating,
            'chartId' => $this->chartId,
        ]);
    }
}
