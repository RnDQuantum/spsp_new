<?php

namespace App\Livewire\Components;

use App\Models\AssessmentEvent;
use App\Models\Participant;
use Livewire\Component;

class ParticipantSelector extends Component
{
    public ?int $participantId = null;

    public string $search = '';

    /** @var array<int, array{id: int, test_number: string, name: string}> */
    public array $availableParticipants = [];

    public bool $showLabel = true;

    public int $page = 1;

    public bool $hasMorePages = false;

    /**
     * Number of results per page for infinite scroll
     */
    private const PER_PAGE = 50;

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

        // Don't auto-load participants on mount - wait for user search
        $this->availableParticipants = [];

        // Load participant from session if exists
        $sessionParticipantId = session('filter.participant_id');

        if ($sessionParticipantId) {
            $this->participantId = $sessionParticipantId;
            $this->loadParticipantById($sessionParticipantId);
        }
    }

    /**
     * Watch for search input changes
     */
    public function updatedSearch(): void
    {
        // Reset pagination when search changes
        $this->page = 1;
        $this->availableParticipants = [];
        $this->loadAvailableParticipants();
    }

    /**
     * Load initial participants when dropdown opens
     */
    public function loadInitial(): void
    {
        // Only load if not already loaded and filters are set
        if (empty($this->availableParticipants)) {
            $this->page = 1;
            $this->loadAvailableParticipants();
        }
    }

    /**
     * Load more participants (for infinite scroll)
     */
    public function loadMore(): void
    {
        if ($this->hasMorePages) {
            $this->page++;
            $this->loadAvailableParticipants(append: true);
        }
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
        $this->search = '';
        $this->page = 1;
        session()->forget('filter.participant_id');

        // Clear available participants
        $this->availableParticipants = [];

        // Dispatch to parent that participant was reset
        $this->dispatch('participant-selected', participantId: $this->participantId);
    }

    /**
     * Handle position selection from PositionSelector component
     */
    public function handlePositionSelected(?int $positionFormationId): void
    {
        // Reset participant when position changes (don't preserve)
        $this->participantId = null;
        $this->search = '';
        $this->page = 1;
        session()->forget('filter.participant_id');

        // Clear available participants
        $this->availableParticipants = [];

        // Dispatch event to parent that participant was reset
        $this->dispatch('participant-selected', participantId: $this->participantId);
    }

    /**
     * Load available participants based on search query
     * OPTIMIZED: Supports pagination for infinite scroll
     */
    private function loadAvailableParticipants(bool $append = false): void
    {
        if (! $append) {
            $this->availableParticipants = [];
        }

        $eventCode = session('filter.event_code');
        $positionFormationId = session('filter.position_formation_id');

        if (! $eventCode || ! $positionFormationId) {
            return;
        }

        $event = AssessmentEvent::where('code', $eventCode)->first();

        if (! $event) {
            return;
        }

        // OPTIMIZED: Paginated query with LIMIT and OFFSET
        $query = Participant::query()
            ->where('event_id', $event->id)
            ->where('position_formation_id', $positionFormationId);

        // Add search filter if search is not empty
        if (! empty($this->search)) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('test_number', 'like', "%{$this->search}%");
            });
        }

        $query->orderBy('name');

        // Get one extra to check if there are more pages
        $participants = $query
            ->skip(($this->page - 1) * self::PER_PAGE)
            ->take(self::PER_PAGE + 1)
            ->get(['id', 'test_number', 'name']);

        // Check if there are more pages
        $this->hasMorePages = $participants->count() > self::PER_PAGE;

        // Remove the extra record
        if ($this->hasMorePages) {
            $participants = $participants->take(self::PER_PAGE);
        }

        $newParticipants = $participants->map(fn ($p) => [
            'id' => $p->id,
            'test_number' => $p->test_number,
            'name' => $p->name,
        ])->all();

        if ($append) {
            $this->availableParticipants = array_merge($this->availableParticipants, $newParticipants);
        } else {
            $this->availableParticipants = $newParticipants;
        }

        // Validate current selection is in results
        if ($this->participantId && ! $this->isValidParticipantId($this->participantId)) {
            $this->participantId = null;
        }
    }

    /**
     * Load a specific participant by ID (used when restoring from session)
     */
    private function loadParticipantById(int $participantId): void
    {
        $participant = Participant::find($participantId);

        if (! $participant) {
            return;
        }

        // Add to available participants if not already present
        if (! $this->isValidParticipantId($participantId)) {
            $this->availableParticipants[] = [
                'id' => $participant->id,
                'test_number' => $participant->test_number,
                'name' => $participant->name,
            ];
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
        $this->search = '';
        $this->page = 1;
        $this->availableParticipants = [];
        session()->forget('filter.participant_id');

        // Dispatch event to parent component
        $this->dispatch('participant-reset');
    }

    public function render()
    {
        return view('livewire.components.participant-selector');
    }
}
