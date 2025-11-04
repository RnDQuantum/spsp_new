<?php

namespace App\Livewire\Components;

use App\Models\AssessmentEvent;
use App\Models\Participant;
use Livewire\Component;

class ParticipantSelector extends Component
{
    public ?int $participantId = null;

    /** @var array<int, array{id: int, test_number: string, name: string}> */
    public array $availableParticipants = [];

    public bool $showLabel = true;

    /**
     * Listen to event and position selection changes
     */
    protected $listeners = [
        'event-selected' => 'handleEventSelected',
        'position-selected' => 'handlePositionSelected',
    ];

    /**
     * Mount component and load participant from session
     */
    public function mount(?bool $showLabel = null): void
    {
        // Optionally control whether to show label from parent
        if ($showLabel !== null) {
            $this->showLabel = $showLabel;
        }

        // Load participants based on session event code and position
        $this->loadAvailableParticipants();
    }

    /**
     * Update participant ID and persist to session
     */
    public function updatedParticipantId(?int $value): void
    {
        // Validate participant ID is in available participants
        if ($value && ! $this->isValidParticipantId($value)) {
            $this->participantId = null;
            session()->forget('filter.participant_id');
            $this->dispatch('participant-selected', participantId: null);

            return;
        }

        // Persist to session
        if ($value) {
            session(['filter.participant_id' => $value]);
        } else {
            session()->forget('filter.participant_id');
        }

        // Dispatch event to parent component
        $this->dispatch('participant-selected', participantId: $value);
    }

    /**
     * Handle event selection from EventSelector component
     */
    public function handleEventSelected(?string $eventCode): void
    {
        // Reset participant when event changes
        $this->participantId = null;
        session()->forget('filter.participant_id');

        // Reload available participants (will be empty until position is selected)
        $this->loadAvailableParticipants();

        // Dispatch to parent that participant was reset
        $this->dispatch('participant-selected', participantId: $this->participantId);
    }

    /**
     * Handle position selection from PositionSelector component
     */
    public function handlePositionSelected(?int $positionFormationId): void
    {
        $previousParticipantId = $this->participantId;

        // Reload participants for new position
        $this->loadAvailableParticipants();

        // Preserve participant if still available; otherwise leave empty
        if ($previousParticipantId && $this->isValidParticipantId($previousParticipantId)) {
            $this->participantId = $previousParticipantId;
        } else {
            $this->participantId = null;
        }

        // Update session
        if ($this->participantId) {
            session(['filter.participant_id' => $this->participantId]);
        } else {
            session()->forget('filter.participant_id');
        }

        // Dispatch event to parent
        $this->dispatch('participant-selected', participantId: $this->participantId);
    }

    /**
     * Load available participants based on current event and position from session
     */
    private function loadAvailableParticipants(): void
    {
        $this->availableParticipants = [];

        $eventCode = session('filter.event_code');
        $positionFormationId = session('filter.position_formation_id');

        if (! $eventCode || ! $positionFormationId) {
            return;
        }

        $event = AssessmentEvent::where('code', $eventCode)->first();

        if (! $event) {
            return;
        }

        $participants = Participant::query()
            ->where('event_id', $event->id)
            ->where('position_formation_id', $positionFormationId)
            ->orderBy('name')
            ->get(['id', 'test_number', 'name']);

        $this->availableParticipants = $participants->map(fn ($p) => [
            'id' => $p->id,
            'test_number' => $p->test_number,
            'name' => $p->name,
        ])->all();

        // Load participant from session (do NOT auto-select first participant)
        $sessionParticipantId = session('filter.participant_id');

        if ($sessionParticipantId && $this->isValidParticipantId($sessionParticipantId)) {
            $this->participantId = $sessionParticipantId;
        } else {
            $this->participantId = null;
        }
    }

    /**
     * Check if participant ID is valid in current available participants
     */
    private function isValidParticipantId(int $id): bool
    {
        return collect($this->availableParticipants)->contains('id', $id);
    }

    /**
     * Reset participant filter
     */
    public function resetParticipant(): void
    {
        $this->participantId = null;
        session()->forget('filter.participant_id');

        // Dispatch event to parent component
        $this->dispatch('participant-reset');
    }

    public function render()
    {
        return view('livewire.components.participant-selector');
    }
}
