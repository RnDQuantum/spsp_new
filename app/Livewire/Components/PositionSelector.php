<?php

namespace App\Livewire\Components;

use App\Models\AssessmentEvent;
use Livewire\Component;

class PositionSelector extends Component
{
    public ?int $positionFormationId = null;

    /** @var array<int, \stdClass> */
    public array $availablePositions = [];

    public bool $showLabel = true;

    /**
     * Listen to event selection changes
     */
    protected $listeners = ['event-selected' => 'handleEventSelected'];

    /**
     * Mount component and load position from session
     */
    public function mount(?bool $showLabel = null): void
    {
        // Optionally control whether to show label from parent
        if ($showLabel !== null) {
            $this->showLabel = $showLabel;
        }

        // Load positions based on session event code
        $this->loadAvailablePositions();
    }

    /**
     * Update position formation ID and persist to session
     */
    public function updatedPositionFormationId(?int $value): void
    {
        // Validate position ID is in available positions
        if ($value && ! $this->isValidPositionId($value)) {
            $this->positionFormationId = null;
            session()->forget('filter.position_formation_id');
            $this->dispatch('position-selected', positionFormationId: null);

            return;
        }

        // Persist to session
        if ($value) {
            session(['filter.position_formation_id' => $value]);
        } else {
            session()->forget('filter.position_formation_id');
        }

        // Dispatch event to parent component
        $this->dispatch('position-selected', positionFormationId: $value);
    }

    /**
     * Handle event selection from EventSelector component
     */
    public function handleEventSelected(?string $eventCode): void
    {
        // Reset position when event changes
        $this->positionFormationId = null;
        session()->forget('filter.position_formation_id');

        // Reload available positions
        $this->loadAvailablePositions();

        // Dispatch to parent that position was reset
        $this->dispatch('position-selected', positionFormationId: $this->positionFormationId);
    }

    /**
     * Load available positions based on current event from session
     */
    private function loadAvailablePositions(): void
    {
        $eventCode = session('filter.event_code');

        if (! $eventCode) {
            $this->availablePositions = [];
            $this->positionFormationId = null;

            return;
        }

        $event = AssessmentEvent::where('code', $eventCode)->first();

        if (! $event) {
            $this->availablePositions = [];
            $this->positionFormationId = null;

            return;
        }

        $this->availablePositions = $event->positionFormations()
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($p) => (object) ['id' => $p->id, 'name' => $p->name])
            ->all();

        // Load position from session or use first available
        $sessionPositionId = session('filter.position_formation_id');

        if ($sessionPositionId && $this->isValidPositionId($sessionPositionId)) {
            $this->positionFormationId = $sessionPositionId;
        } else {
            $this->positionFormationId = $this->availablePositions[0]->id ?? null;

            // Save to session if we have a default
            if ($this->positionFormationId) {
                session(['filter.position_formation_id' => $this->positionFormationId]);

                // Dispatch event to notify parent that default was selected
                \Log::info('PositionSelector: Dispatching position-selected', ['positionFormationId' => $this->positionFormationId]);
                $this->dispatch('position-selected', positionFormationId: $this->positionFormationId);
            }
        }
    }

    /**
     * Check if position ID is valid in current available positions
     */
    private function isValidPositionId(int $id): bool
    {
        return collect($this->availablePositions)->contains('id', $id);
    }

    public function render()
    {
        return view('livewire.components.position-selector');
    }
}
