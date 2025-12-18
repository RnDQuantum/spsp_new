<?php

namespace App\Livewire\Pages\TalentPool;

use App\Models\AssessmentEvent;
use App\Services\TalentPoolService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app', ['title' => 'Talent Pool'])]
class Index extends Component
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
     * 🚀 OPTIMIZED: Load event and position from database with minimal queries
     */
    private function loadEventAndPosition(): void
    {
        $eventCode = session('filter.event_code');
        $positionFormationId = session('filter.position_formation_id');

        if (! $eventCode) {
            return;
        }

        // 🚀 PERFORMANCE: Single optimized query with eager loading
        $this->selectedEvent = AssessmentEvent::query()
            ->where('code', $eventCode)
            ->with([
                'positionFormations' => function ($query) use ($positionFormationId) {
                    $query->where('id', $positionFormationId)
                        ->with('template');
                },
            ])
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
                'test_number' => $participant['test_number'],
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
     * Get centralized box configuration (Single Source of Truth)
     * Contains all box metadata: colors, labels, and overlay colors
     */
    public function getBoxConfigProperty(): array
    {
        return [
            1 => [
                'code' => 'K-1',
                'label' => 'Kinerja di bawah ekspektasi dan potensi rendah',
                'color' => '#8B0000', // Merah gelap (Dark Red)
                'overlay_color' => 'rgba(139, 0, 0, 0.3)',
            ],
            2 => [
                'code' => 'K-2',
                'label' => 'Kinerja sesuai ekspektasi dan potensi rendah',
                'color' => '#FF4500', // Merah-oranye
                'overlay_color' => 'rgba(255, 69, 0, 0.3)',
            ],
            3 => [
                'code' => 'K-3',
                'label' => 'Kinerja di bawah ekspektasi dan potensi menengah',
                'color' => '#FF8C00', // Oranye gelap
                'overlay_color' => 'rgba(255, 140, 0, 0.3)',
            ],
            4 => [
                'code' => 'K-4',
                'label' => 'Kinerja di atas ekspektasi dan potensi rendah',
                'color' => '#FFD700', // Kuning emas
                'overlay_color' => 'rgba(255, 215, 0, 0.4)',
            ],
            5 => [
                'code' => 'K-5',
                'label' => 'Kinerja sesuai ekspektasi dan potensi menengah',
                'color' => '#FFFF00', // Kuning murni
                'overlay_color' => 'rgba(255, 255, 0, 0.3)',
            ],
            6 => [
                'code' => 'K-6',
                'label' => 'Kinerja di bawah ekspektasi dan potensi tinggi',
                'color' => '#CCFF00', // Kuning-hijau terang (Electric Lime)
                'overlay_color' => 'rgba(204, 255, 0, 0.3)',
            ],
            7 => [
                'code' => 'K-7',
                'label' => 'Kinerja di atas ekspektasi dan potensi menengah',
                'color' => '#32CD32', // Lime Green (hijau terang)
                'overlay_color' => 'rgba(50, 205, 50, 0.3)',
            ],
            8 => [
                'code' => 'K-8',
                'label' => 'Kinerja sesuai ekspektasi dan potensi tinggi',
                'color' => '#228B22', // Forest Green (hijau sedang)
                'overlay_color' => 'rgba(34, 139, 34, 0.3)',
            ],
            9 => [
                'code' => 'K-9',
                'label' => 'Kinerja di atas ekspektasi dan potensi tinggi',
                'color' => '#006400', // Dark Green (hijau gelap)
                'overlay_color' => 'rgba(0, 100, 0, 0.4)',
            ],
        ];
    }

    /**
     * Get box color based on box number
     */
    private function getBoxColor(int $boxNumber): string
    {
        return $this->boxConfig[$boxNumber]['color'] ?? '#9E9E9E';
    }

    /**
     * Get box labels
     */
    public function getBoxLabelsProperty(): array
    {
        return collect($this->boxConfig)
            ->mapWithKeys(fn ($config, $number) => [$number => $config['label']])
            ->toArray();
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
        return view('livewire.pages.talent-pool.index', [
            'selectedTemplate' => $this->selectedEvent?->positionFormations?->first()?->template,
        ]);
    }
}
