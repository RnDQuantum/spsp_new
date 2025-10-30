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
        'participant-selected' => 'handleParticipantSelected',
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
        $this->participantId = null; // Reset participant ID when event changes
        $this->testNumber = null; // Reset test number when event changes
    }

    public function handlePositionSelected(?int $positionFormationId): void
    {
        $this->positionFormationId = $positionFormationId;
        $this->participantId = null; // Reset participant ID when position changes
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
     * Validates that the combination of eventCode and testNumber exists in database
     */
    public function canShowIndividualReports(): bool
    {
        // Basic null check
        if ($this->eventCode === null || $this->testNumber === null) {
            return false;
        }

        // Validate that the combination exists in database
        $participant = Participant::whereHas('event', function ($query) {
            $query->where('code', $this->eventCode);
        })->where('test_number', $this->testNumber)->first();

        return $participant !== null;
    }

    /**
     * Check if current route matches the given route name
     */
    public function isActiveRoute(string $routeName, array $params = []): bool
    {
        if (! request()->route()) {
            return false;
        }

        $currentRoute = request()->route()->getName();

        if ($currentRoute === $routeName) {
            // For routes with parameters, also check if params match
            if (! empty($params)) {
                foreach ($params as $key => $value) {
                    if (request()->route($key) !== $value) {
                        return false;
                    }
                }
            }

            return true;
        }

        return false;
    }

    public function render()
    {
        return view('livewire.components.sidebar');
    }
}
