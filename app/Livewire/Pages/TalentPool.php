<?php

namespace App\Livewire\Pages;

use App\Models\AssessmentEvent;
use App\Services\TalentPoolService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app', ['title' => 'Talent Pool'])]
class TalentPool extends Component
{
    public ?AssessmentEvent $selectedEvent = null;

    public ?int $selectedPositionId = null;

    // Data from TalentPoolService
    public array $matrixData = [];

    public int $totalParticipants = 0;

    // Cache properties
    private ?array $matrixCacheData = null;

    /**
     * Listen to filter component events
     */
    protected $listeners = [
        'event-selected' => 'handleEventSelected',
        'position-selected' => 'handlePositionSelected',
        'standard-adjusted' => 'handleStandardUpdate',
        'standard-switched' => 'handleStandardUpdate',
    ];

    public function mount(): void
    {
        // Load from session
        $eventCode = session('filter.event_code');
        $positionFormationId = session('filter.position_formation_id');

        if ($eventCode && $positionFormationId) {
            $this->loadEventAndPosition();
            $this->loadMatrixData();
        }
    }

    /**
     * Handle event selection
     */
    public function handleEventSelected(?string $eventCode): void
    {
        $this->clearCache();

        // Reset data
        $this->reset(['selectedEvent', 'selectedPositionId', 'matrixData', 'totalParticipants']);
    }

    /**
     * Handle position selection
     */
    public function handlePositionSelected(?int $positionFormationId): void
    {
        $this->selectedPositionId = $positionFormationId;
        $this->clearCache();

        if ($this->selectedEvent && $positionFormationId) {
            $this->loadMatrixData();
        } else {
            $this->reset(['matrixData', 'totalParticipants']);
        }

        // Dispatch chart update to frontend
        $this->dispatchChartUpdate();
    }

    /**
     * Handle standard adjustment from StandardPsikometrik/StandardMc component
     */
    public function handleStandardUpdate(int $templateId): void
    {
        // Validate same template
        if (!$this->selectedEvent || !$this->selectedPositionId) {
            return;
        }

        $position = $this->selectedEvent->positionFormations()
            ->find($this->selectedPositionId);

        if (!$position || $position->template_id !== $templateId) {
            return;
        }

        // Clear cache before reload
        $this->clearCache();

        // Reload data (will call service fresh with new session values)
        $this->loadMatrixData();

        // Dispatch chart update to frontend
        $this->dispatchChartUpdate();
    }

    /**
     * Load event and position from database
     */
    private function loadEventAndPosition(): void
    {
        $eventCode = session('filter.event_code');
        $positionFormationId = session('filter.position_formation_id');

        if (!$eventCode) {
            return;
        }

        // Load event with position and template in one query
        $this->selectedEvent = AssessmentEvent::query()
            ->where('code', $eventCode)
            ->with(['positionFormations' => function ($query) use ($positionFormationId) {
                $query->where('id', $positionFormationId)
                    ->with('template');
            }])
            ->first();

        if ($this->selectedEvent && $positionFormationId) {
            $this->selectedPositionId = $positionFormationId;
        }
    }

    /**
     * Clear all caches
     */
    private function clearCache(): void
    {
        $this->matrixCacheData = null;
    }

    /**
     * Load matrix data from TalentPoolService
     */
    private function loadMatrixData(): void
    {
        if (!$this->selectedEvent || !$this->selectedPositionId) {
            return;
        }

        // Check cache first
        if ($this->matrixCacheData !== null) {
            $this->applyMatrixData($this->matrixCacheData);
            return;
        }

        // Call service
        $service = app(TalentPoolService::class);
        $matrix = $service->getNineBoxMatrixData(
            $this->selectedEvent->id,
            $this->selectedPositionId
        );

        // Cache result
        $this->matrixCacheData = $matrix;

        // Apply to component properties
        $this->applyMatrixData($matrix);

        // Dispatch chart update to frontend
        $this->dispatchChartUpdate();
    }

    /**
     * Apply matrix data to component properties
     */
    private function applyMatrixData(array $matrix): void
    {
        $this->matrixData = $matrix;
        $this->totalParticipants = $matrix['total_participants'] ?? 0;
    }

    /**
     * Get participants data for JavaScript chart
     */
    public function getChartDataProperty(): array
    {
        if (empty($this->matrixData['participants'])) {
            return [];
        }

        return $this->matrixData['participants']->map(function ($participant) {
            return [
                'nama' => $participant['name'],
                'potensi' => $participant['potensi_rating'],
                'kinerja' => $participant['kinerja_rating'],
                'box' => $participant['box_number'],
                'color' => $this->getBoxColor($participant['box_number']),
            ];
        })->toArray();
    }

    /**
     * Get chart data alias for backward compatibility
     */
    public function getChartProperty(): array
    {
        return $this->getChartDataProperty();
    }

    /**
     * Get box boundaries for chart
     */
    public function getBoxBoundariesProperty(): ?array
    {
        return $this->matrixData['box_boundaries'] ?? null;
    }

    /**
     * Get box statistics
     */
    public function getBoxStatisticsProperty(): array
    {
        return $this->matrixData['box_statistics'] ?? [];
    }

    /**
     * Get box color based on box number
     */
    private function getBoxColor(int $boxNumber): string
    {
        $colors = [
            1 => '#D32F2F', // Need Attention - Red
            2 => '#FF9800', // Steady Performer - Orange
            3 => '#E91E63', // Inconsistent - Pink
            4 => '#9C27B0', // Solid Performer - Purple
            5 => '#FFC107', // Core Performer - Amber
            6 => '#FF5722', // Enigma - Deep Orange
            7 => '#2196F3', // Potential Star - Blue
            8 => '#00BCD4', // High Potential - Cyan
            9 => '#00C853', // Star Performer - Green
        ];

        return $colors[$boxNumber] ?? '#9E9E9E'; // Default gray
    }

    /**
     * Get box labels
     */
    public function getBoxLabelsProperty(): array
    {
        return [
            1 => 'Need Attention',
            2 => 'Steady Performer',
            3 => 'Inconsistent',
            4 => 'Solid Performer',
            5 => 'Core Performer',
            6 => 'Enigma',
            7 => 'Potential Star',
            8 => 'High Potential',
            9 => 'Star Performer',
        ];
    }

    /**
     * Dispatch chart update to JavaScript
     */
    private function dispatchChartUpdate(): void
    {
        $this->dispatch('chartDataUpdated', [
            'chartId' => 'talentPoolChart',
            'labels' => ['Box 1', 'Box 2', 'Box 3', 'Box 4', 'Box 5', 'Box 6', 'Box 7', 'Box 8', 'Box 9'],
            'data' => $this->matrixData['box_statistics'] ?? [],
            'boxBoundaries' => $this->matrixData['box_boundaries'] ?? [],
            'boxStatistics' => $this->matrixData['box_statistics'] ?? [],
            'pesertaData' => $this->chart,
            'aspectName' => 'Talent Pool Distribution',
        ]);
    }

    public function render()
    {
        // Dispatch chart update to JavaScript
        $this->dispatchChartUpdate();

        return view('livewire.pages.talentpool', [
            'selectedTemplate' => $this->selectedEvent?->positionFormations?->first()?->template,
        ]);
    }
}
