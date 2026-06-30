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

    // Data from TalentPoolService (private to avoid Livewire serialization payload size issues)
    private ?array $matrixData = null;

    public int $totalParticipants = 0;

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
     * 🚀 PERFORMANCE: Session already saved in EventSelector, just dynamic load
     */
    public function handleEventSelected(?string $eventCode): void
    {
        if ($eventCode) {
            session(['filter.event_code' => $eventCode]);
            // Clear position filter on event change to avoid mismatch
            session()->forget('filter.position_formation_id');
            $this->selectedPositionId = null;
        }
        session()->save();

        // Reload data dynamically
        $this->loadEventAndPosition();
        $this->loadMatrixData();
        $this->dispatchChartUpdate();
    }

    /**
     * Handle position selection
     * 🚀 PERFORMANCE: Session already saved in PositionSelector, just dynamic load
     */
    public function handlePositionSelected(?int $positionFormationId): void
    {
        if ($positionFormationId) {
            session(['filter.position_formation_id' => $positionFormationId]);
            $this->selectedPositionId = $positionFormationId;
        }
        session()->save();

        // Reload data dynamically
        $this->loadEventAndPosition();
        $this->loadMatrixData();
        $this->dispatchChartUpdate();
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
     * Get or lazy load the matrix data from service
     */
    private function getMatrixData(): array
    {
        if ($this->matrixData !== null) {
            return $this->matrixData;
        }

        if (! $this->selectedEvent || ! $this->selectedPositionId) {
            $this->loadEventAndPosition();
        }

        // Guard: if still null after load, return early
        if (! $this->selectedEvent || ! $this->selectedPositionId) {
            return [];
        }

        $this->loadMatrixData();
        return $this->matrixData ?? [];
    }

    /**
     * Clear all caches
     */
    private function clearCache(): void
    {
        $this->matrixData = null;
        if ($this->selectedEvent && $this->selectedPositionId) {
            cache()->forget("talent_pool_{$this->selectedEvent->id}_{$this->selectedPositionId}");
        }
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
            $cacheKey = "talent_pool_{$this->selectedEvent->id}_{$this->selectedPositionId}";

            $matrix = cache()->remember($cacheKey, now()->addMinutes(5), function () {
                return app(TalentPoolService::class)->getNineBoxMatrixData(
                    $this->selectedEvent->id,
                    $this->selectedPositionId
                );
            });

            $this->applyMatrixData($matrix);
        } catch (\Exception $e) {
            // Handle error gracefully
            $this->dispatch('error', 'Failed to load talent pool data: ' . $e->getMessage());
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
        $matrix = $this->getMatrixData();
        if (empty($matrix['participants'])) {
            return [];
        }

        return $matrix['participants']->map(function ($participant) {
            return [
                'nama' => $participant['name'],
                'test_number' => $participant['test_number'],
                'potensi' => $participant['potensi_rating'],
                'kinerja' => $participant['kinerja_rating'],
                'box' => $participant['box_number'],
                // Gunakan dot_color (terang) untuk titik di scatter plot
                'color' => $this->boxConfig[$participant['box_number']]['dot_color'] ?? '#FFFFFF',
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
        return $this->getMatrixData()['box_boundaries'] ?? null;
    }

    /**
     * Get box statistics
     */
    public function getBoxStatisticsProperty(): array
    {
        return $this->getMatrixData()['box_statistics'] ?? [];
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
                'label' => 'Kompetensi di bawah ekspektasi dan potensi rendah',
                'color' => '#8B0000', // Merah Gelap
                'dot_color' => '#74FFFF', // Inverted: Cyan Terang
                'overlay_color' => 'rgba(139, 0, 0, 0.85)',
            ],
            2 => [
                'code' => 'K-2',
                'label' => 'Kompetensi sesuai ekspektasi dan potensi rendah',
                'color' => '#EA580C', // Orange Tua
                'dot_color' => '#15A7F3', // Inverted: Biru Terang
                'overlay_color' => 'rgba(234, 88, 12, 0.85)',
            ],
            3 => [
                'code' => 'K-3',
                'label' => 'Kompetensi di bawah ekspektasi dan potensi menengah',
                'color' => '#D97706', // Amber Tua
                'dot_color' => '#2688F9', // Inverted: Biru Azure
                'overlay_color' => 'rgba(217, 119, 6, 0.85)',
            ],
            4 => [
                'code' => 'K-4',
                'label' => 'Kompetensi di atas ekspektasi dan potensi rendah',
                'color' => '#CA8A04', // Kuning Emas Gelap
                'dot_color' => '#3575FB', // Inverted: Biru
                'overlay_color' => 'rgba(202, 138, 4, 0.85)',
            ],
            5 => [
                'code' => 'K-5',
                'label' => 'Kompetensi sesuai ekspektasi dan potensi menengah',
                'color' => '#EAB308', // Kuning Mustard
                'dot_color' => '#154CF7', // Inverted: Biru Elektrik
                'overlay_color' => 'rgba(234, 179, 8, 0.8)',
            ],
            6 => [
                'code' => 'K-6',
                'label' => 'Kompetensi di bawah ekspektasi dan potensi tinggi',
                'color' => '#65A30D', // Lime Tua
                'dot_color' => '#9A5CF2', // Inverted: Ungu
                'overlay_color' => 'rgba(101, 163, 13, 0.85)',
            ],
            7 => [
                'code' => 'K-7',
                'label' => 'Kompetensi di atas ekspektasi dan potensi menengah',
                'color' => '#16A34A', // Hijau Cerah
                'dot_color' => '#E95CB5', // Inverted: Magenta/Pink
                'overlay_color' => 'rgba(22, 163, 74, 0.85)',
            ],
            8 => [
                'code' => 'K-8',
                'label' => 'Kompetensi sesuai ekspektasi dan potensi tinggi',
                'color' => '#15803D', // Hijau Hutan
                'dot_color' => '#EA7FC2', // Inverted: Pink
                'overlay_color' => 'rgba(21, 128, 61, 0.85)',
            ],
            9 => [
                'code' => 'K-9',
                'label' => 'Kompetensi di atas ekspektasi dan potensi tinggi',
                'color' => '#14532D', // Hijau Sangat Gelap
                'dot_color' => '#EBACD2', // Inverted: Pink Muda
                'overlay_color' => 'rgba(20, 83, 45, 0.9)',
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
            ->mapWithKeys(fn($config, $number) => [$number => $config['label']])
            ->toArray();
    }

    /**
     * Dispatch chart update notification to JavaScript
     */
    private function dispatchChartUpdate(): void
    {
        $this->dispatch('chartDataNeedsUpdate');
    }

    /**
     * Get chart data and configurations for initial JavaScript load
     */
    public function getChartInitializationData(): array
    {
        return [
            'pesertaData' => $this->chart,
            'boxBoundaries' => $this->boxBoundaries,
            'boxStatistics' => $this->boxStatistics,
        ];
    }

    /**
     * Retrieve participants for a specific box and trigger modal opening on the client
     */
    public function openBoxModal(int $boxNumber): void
    {
        $matrix = $this->getMatrixData();
        if (empty($matrix['participants'])) {
            return;
        }

        $participantsInBox = $matrix['participants']
            ->filter(fn($p) => (int)$p['box_number'] === $boxNumber)
            ->map(fn($p) => [
                'name' => $p['name'],
                'test_number' => $p['test_number'],
                'potensi_rating' => $p['potensi_rating'],
                'kinerja_rating' => $p['kinerja_rating']
            ])
            ->values()
            ->toArray();

        $this->dispatch('openParticipantModal', $boxNumber, $participantsInBox);
    }

    public function render()
    {
        // 🚀 PERFORMANCE: No extra processing in render to keep it fast
        return view('livewire.pages.talent-pool.index', [
            'selectedTemplate' => $this->selectedEvent?->positionFormations?->first()?->template,
        ]);
    }
}
