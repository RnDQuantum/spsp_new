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
     * 🚀 PERFORMANCE: Session already saved in EventSelector, just trigger reload
     */
    public function handleEventSelected(?string $eventCode): void
    {
        // Session is already saved in EventSelector::updatedEventCode()
        // Parameter kept for event listener compatibility
        // Just ensure it's committed to storage before reload
        session()->save();

        // Dispatch event to JavaScript to handle redirect
        $this->dispatch('trigger-reload');
    }

    /**
     * Handle position selection
     * 🚀 PERFORMANCE: Session already saved in PositionSelector, just trigger reload
     */
    public function handlePositionSelected(?int $positionFormationId): void
    {
        // Session is already saved in PositionSelector::updatedPositionFormationId()
        // Parameter kept for event listener compatibility
        // Just ensure it's committed to storage before reload
        session()->save();

        // Dispatch event to JavaScript to handle redirect
        $this->dispatch('trigger-reload');
    }

    /**
     * Handle standard adjustment
     */
    public function handleStandardUpdate(int $templateId): void
    {
        // Validate same template
        if (! $this->selectedEvent || ! $this->selectedPositionId) {
            return;
        }

        $position = $this->selectedEvent->positionFormations()
            ->find($this->selectedPositionId);

        if (! $position || $position->template_id !== $templateId) {
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

        if (! $eventCode) {
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
     * Load matrix data from TalentPoolService with error handling
     */
    private function loadMatrixData(): void
    {
        if (! $this->selectedEvent || ! $this->selectedPositionId) {
            return;
        }

        try {
            // Check cache first
            if ($this->matrixCacheData !== null) {
                $this->applyMatrixData($this->matrixCacheData);

                return;
            }

            // 🚀 PERFORMANCE: Direct service call with proper error handling
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

            // Note: We DO NOT dispatch chart update here anymore to prevent double rendering on initial load.
            // Explicit dispatch is handled by the caller if needed (e.g. handleStandardUpdate).
        } catch (\Exception $e) {
            // Handle error gracefully
            $this->dispatch('error', 'Failed to load talent pool data: '.$e->getMessage());
        }
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
            1 => '#D32F2F', // K-1: Kinerja di bawah ekspektasi dan potensi rendah - Red
            2 => '#F8BBD0', // K-2: Kinerja sesuai ekspektasi dan potensi rendah - Light Pink
            3 => '#F48FB1', // K-3: Kinerja di bawah ekspektasi dan potensi menengah - Pink
            4 => '#FFF9C4', // K-4: Kinerja di atas ekspektasi dan potensi rendah - Light Yellow
            5 => '#FFEB3B', // K-5: Kinerja sesuai ekspektasi dan potensi menengah - Yellow
            6 => '#FFEB3B', // K-6: Kinerja di bawah ekspektasi dan potensi tinggi - Yellow
            7 => '#81C784', // K-7: Kinerja di atas ekspektasi dan potensi menengah - Medium Green
            8 => '#AED581', // K-8: Kinerja sesuai ekspektasi dan potensi tinggi - Light Green
            9 => '#388E3C', // K-9: Kinerja di atas ekspektasi dan potensi tinggi - Dark Green
        ];

        return $colors[$boxNumber] ?? '#9E9E9E'; // Default gray
    }

    /**
     * Get box labels
     */
    public function getBoxLabelsProperty(): array
    {
        return [
            1 => 'Kinerja di bawah ekspektasi dan potensi rendah',
            2 => 'Kinerja sesuai ekspektasi dan potensi rendah',
            3 => 'Kinerja di bawah ekspektasi dan potensi menengah',
            4 => 'Kinerja di atas ekspektasi dan potensi rendah',
            5 => 'Kinerja sesuai ekspektasi dan potensi menengah',
            6 => 'Kinerja di bawah ekspektasi dan potensi tinggi',
            7 => 'Kinerja di atas ekspektasi dan potensi menengah',
            8 => 'Kinerja sesuai ekspektasi dan potensi tinggi',
            9 => 'Kinerja di atas ekspektasi dan potensi tinggi',
        ];
    }

    /**
     * Dispatch chart update to JavaScript
     */
    private function dispatchChartUpdate(): void
    {
        $data = [
            'chartId' => 'talentPoolChart',
            'labels' => ['Box 1', 'Box 2', 'Box 3', 'Box 4', 'Box 5', 'Box 6', 'Box 7', 'Box 8', 'Box 9'],
            'data' => $this->matrixData['box_statistics'] ?? [],
            'boxBoundaries' => $this->matrixData['box_boundaries'] ?? [],
            'boxStatistics' => $this->matrixData['box_statistics'] ?? [],
            'pesertaData' => $this->chart,
            'aspectName' => 'Talent Pool Distribution',
        ];

        // Debug logging
        logger('TalentPool: Dispatching chart update', [
            'total_participants' => $this->totalParticipants,
            'matrix_data_count' => count($this->matrixData),
            'chart_data_count' => count($this->chart),
        ]);

        $this->dispatch('chartDataUpdated', $data);
    }

    public function render()
    {
        // 🚀 PERFORMANCE: No extra processing in render to keep it fast
        return view('livewire.pages.talentpool', [
            'selectedTemplate' => $this->selectedEvent?->positionFormations?->first()?->template,
        ]);
    }
}
