<?php

namespace App\Livewire\Components;

use App\Models\Participant;
use Livewire\Component;

class Sidebar extends Component
{
    public ?string $eventCode = null;
    public ?int $positionFormationId = null;
    public ?int $participantId = null;
    public ?string $testNumber = null;

    protected $listeners = [
        'event-selected' => 'handleEventSelected',
        'position-selected' => 'handlePositionSelected',
        'participant-selected' => 'handleParticipantSelected'
    ];

    public function mount(): void
    {
        // Load initial values from session
        $this->eventCode = session('filter.event_code');
        $this->positionFormationId = session('filter.position_formation_id');
        $this->participantId = session('filter.participant_id');

        // Load test number if we have a participant
        if ($this->participantId) {
            $participant = Participant::find($this->participantId);
            $this->testNumber = $participant?->test_number;
        }
    }

    public function handleEventSelected(?string $eventCode): void
    {
        $this->eventCode = $eventCode;
        $this->testNumber = null; // Reset test number when event changes
    }

    public function handlePositionSelected(?int $positionFormationId): void
    {
        $this->positionFormationId = $positionFormationId;
        $this->testNumber = null; // Reset test number when position changes
    }

    public function handleParticipantSelected(?int $participantId): void
    {
        $this->participantId = $participantId;

        // Update test number when participant changes
        if ($participantId) {
            $participant = Participant::find($participantId);
            $this->testNumber = $participant?->test_number;
        } else {
            $this->testNumber = null;
        }
    }

    /**
     * Check if individual report links should be enabled
     */
    public function canShowIndividualReports(): bool
    {
        return $this->eventCode !== null && $this->testNumber !== null;
    }

    public function render()
    {
        return view('livewire.components.sidebar');
    }
}
