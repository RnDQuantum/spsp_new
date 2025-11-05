<?php

namespace App\Livewire\Pages\GeneralReport;

use App\Models\Aspect;
use App\Models\AssessmentEvent;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app', ['title' => 'Kurva Distribusi Frekuensi'])]
class Statistic extends Component
{
    public ?int $aspectId = null;

    /** @var array<int,int> */
    public array $distribution = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];

    public float $standardRating = 0.0;

    public float $averageRating = 0.0;

    public string $chartId = '';

    protected $listeners = [
        'event-selected' => 'handleEventSelected',
        'position-selected' => 'handlePositionSelected',
        'aspect-selected' => 'handleAspectSelected',
    ];

    public function mount(): void
    {
        $this->chartId = 'statistic'.uniqid();

        // Load aspect from session if available
        $this->aspectId = session('filter.aspect_id');

        $this->refreshStatistics();
    }

    public function handleEventSelected(?string $eventCode): void
    {
        // Position will auto-reset, wait for position-selected event
    }

    public function handlePositionSelected(?int $positionFormationId): void
    {
        // Aspect will auto-reset, wait for aspect-selected event
    }

    public function handleAspectSelected(?int $aspectId): void
    {
        $this->aspectId = $aspectId;
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

    private function refreshStatistics(): void
    {
        $this->distribution = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
        $this->standardRating = 0.0;
        $this->averageRating = 0.0;

        $eventCode = session('filter.event_code');
        $positionFormationId = session('filter.position_formation_id');

        if (! $eventCode || ! $positionFormationId || ! $this->aspectId) {
            return;
        }

        $event = AssessmentEvent::query()->where('code', $eventCode)->first();
        if (! $event) {
            return;
        }

        $aspectId = (int) $this->aspectId;

        // Get standard rating from master aspect
        $aspect = Aspect::query()->where('id', $aspectId)->first();
        $this->standardRating = (float) ($aspect?->standard_rating ?? 0.0);

        // Build distribution (bucket 1..5) - FILTER by position
        // Use CASE WHEN to match the classification table ranges:
        // I: 1.00-1.80, II: 1.80-2.60, III: 2.60-3.40, IV: 3.40-4.20, V: 4.20-5.00
        $rows = DB::table('aspect_assessments as aa')
            ->where('aa.event_id', $event->id)
            ->where('aa.position_formation_id', $positionFormationId)
            ->where('aa.aspect_id', $aspectId)
            ->selectRaw('
                CASE
                    WHEN aa.individual_rating >= 1.00 AND aa.individual_rating < 1.80 THEN 1
                    WHEN aa.individual_rating >= 1.80 AND aa.individual_rating < 2.60 THEN 2
                    WHEN aa.individual_rating >= 2.60 AND aa.individual_rating < 3.40 THEN 3
                    WHEN aa.individual_rating >= 3.40 AND aa.individual_rating < 4.20 THEN 4
                    WHEN aa.individual_rating >= 4.20 AND aa.individual_rating <= 5.00 THEN 5
                    ELSE 0
                END as bucket,
                COUNT(*) as total
            ')
            ->groupBy('bucket')
            ->get();

        foreach ($rows as $row) {
            $bucket = (int) $row->bucket;
            if ($bucket >= 1 && $bucket <= 5) {
                $this->distribution[$bucket] = (int) $row->total;
            }
        }

        // Compute average aspect rating for this event/aspect/position
        $avg = DB::table('aspect_assessments as aa')
            ->where('aa.event_id', $event->id)
            ->where('aa.position_formation_id', $positionFormationId)
            ->where('aa.aspect_id', $aspectId)
            ->avg('aa.individual_rating');

        $this->averageRating = round((float) $avg, 2);
    }

    private function getCurrentAspectName(): string
    {
        if (! $this->aspectId) {
            return '';
        }

        $aspect = Aspect::query()->find((int) $this->aspectId);

        return $aspect?->name ?? '';
    }

    public function render()
    {
        return view('livewire.pages.general-report.statistic', [
            'distribution' => $this->distribution,
            'standardRating' => $this->standardRating,
            'averageRating' => $this->averageRating,
            'chartId' => $this->chartId,
            'aspectName' => $this->getCurrentAspectName(),
        ]);
    }
}
