<?php

namespace App\Livewire\Pages\GeneralReport;

use App\Models\AssessmentEvent;
use App\Services\Cache\AspectCacheService;
use App\Services\StatisticService;
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

    public ?object $selectedTemplate = null;

    public string $chartId = '';

    protected $listeners = [
        'event-selected' => 'handleEventSelected',
        'position-selected' => 'handlePositionSelected',
        'aspect-selected' => 'handleAspectSelected',
        'standard-adjusted' => 'handleStandardUpdate',
        'standard-switched' => 'handleStandardUpdate',
    ];

    public function mount(): void
    {
        $this->chartId = 'statistic'.uniqid();

        // Load aspect from session if available
        $this->aspectId = session('filter.aspect_id');

        // 🚀 Preload aspect cache if we have a position with template
        $eventCode = session('filter.event_code');
        $positionFormationId = session('filter.position_formation_id');

        if ($eventCode && $positionFormationId) {
            $event = AssessmentEvent::where('code', $eventCode)->first();
            if ($event) {
                $position = $event->positionFormations()->find($positionFormationId);
                if ($position && $position->template_id) {
                    // Preload all aspects for this template to avoid N+1 queries
                    AspectCacheService::preloadByTemplate($position->template_id);
                }
            }
        }

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
        $this->dispatchChartUpdate();
    }

    public function handleStandardUpdate(int $templateId): void
    {
        // Refresh statistics when standard is adjusted
        $this->refreshStatistics();
        $this->dispatchChartUpdate();
    }

    private function refreshStatistics(): void
    {
        // Reset values
        $this->distribution = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
        $this->standardRating = 0.0;
        $this->averageRating = 0.0;

        // Get filter values from session
        $eventCode = session('filter.event_code');
        $positionFormationId = session('filter.position_formation_id');

        if (! $eventCode || ! $positionFormationId || ! $this->aspectId) {
            return;
        }

        // Get event
        $event = AssessmentEvent::where('code', $eventCode)->first();
        if (! $event) {
            return;
        }

        // Get position to access template_id
        $position = $event->positionFormations()->with('template')->find($positionFormationId);
        if (! $position || ! $position->template_id) {
            $this->selectedTemplate = null;

            return;
        }

        $this->selectedTemplate = $position->template;

        // Use StatisticService to get distribution data
        $service = app(StatisticService::class);

        $data = $service->getDistributionData(
            $event->id,
            $positionFormationId,
            (int) $this->aspectId,
            $position->template_id
        );

        // Update component properties
        $this->distribution = $data['distribution'];
        $this->standardRating = $data['standard_rating'];
        $this->averageRating = $data['average_rating'];
    }

    private function dispatchChartUpdate(): void
    {
        $this->dispatch('chartDataUpdated', [
            'chartId' => $this->chartId,
            'labels' => ['I', 'II', 'III', 'IV', 'V'],
            'data' => array_values($this->distribution),
            'standardRating' => $this->standardRating,
            'averageRating' => $this->averageRating,
            'aspectName' => $this->getCurrentAspectName(),
        ]);
    }

    private function getCurrentAspectName(): string
    {
        if (! $this->aspectId) {
            return '';
        }

        // 🚀 Use AspectCacheService to prevent duplicate queries
        $aspect = AspectCacheService::getById((int) $this->aspectId);

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
