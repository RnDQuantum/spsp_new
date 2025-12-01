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

    // CACHE: Store participant lookup result to avoid N+1 queries
    private ?Participant $participantCache = null;

    // CACHE: Store validation result
    private ?bool $canShowReportsCache = null;

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
            $participant = $this->getCachedParticipantById($this->participantId);
            $this->testNumber = $participant?->test_number;
        }
    }

    /**
     * Get participant by ID with caching to avoid duplicate queries
     */
    private function getCachedParticipantById(int $participantId): ?Participant
    {
        if ($this->participantCache === null || $this->participantCache->id !== $participantId) {
            $this->participantCache = Participant::with('event')->find($participantId);
        }

        return $this->participantCache;
    }

    /**
     * Clear all caches when filters change
     */
    private function clearCache(): void
    {
        $this->participantCache = null;
        $this->canShowReportsCache = null;
    }

    public function handleEventSelected(?string $eventCode): void
    {
        $this->eventCode = $eventCode;
        $this->participantId = null; // Reset participant ID when event changes
        $this->testNumber = null; // Reset test number when event changes
        $this->clearCache(); // Clear cache when filters change
    }

    public function handlePositionSelected(?int $positionFormationId): void
    {
        $this->positionFormationId = $positionFormationId;
        $this->participantId = null; // Reset participant ID when position changes
        $this->testNumber = null; // Reset test number when position changes
        $this->clearCache(); // Clear cache when filters change
    }

    public function handleParticipantSelected(?int $participantId): void
    {
        $this->participantId = $participantId;

        // Clear cache when participant changes
        $this->clearCache();

        // Update test number when participant changes
        if ($participantId) {
            $participant = $this->getCachedParticipantById($participantId);
            $this->testNumber = $participant?->test_number;
        } else {
            $this->testNumber = null;
        }
    }

    /**
     * Check if individual report links should be enabled
     * Validates that the combination of eventCode and testNumber exists in database
     *
     * OPTIMIZED: Uses cache to avoid N+1 queries (previously called 9x per request)
     */
    public function canShowIndividualReports(): bool
    {
        // Basic null check
        if ($this->eventCode === null || $this->testNumber === null) {
            return false;
        }

        // Return cached result if available
        if ($this->canShowReportsCache !== null) {
            return $this->canShowReportsCache;
        }

        // Validate that the combination exists in database
        $participant = Participant::whereHas('event', function ($query) {
            $query->where('code', $this->eventCode);
        })->where('test_number', $this->testNumber)->first();

        // Cache the result
        $this->canShowReportsCache = ($participant !== null);

        return $this->canShowReportsCache;
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
