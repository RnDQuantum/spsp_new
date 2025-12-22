<?php

namespace App\Livewire\Pages\GeneralReport\Training;

use App\Services\TrainingRecommendationService;
use Livewire\Component;
use Livewire\WithPagination;

class AttributeParticipantListModal extends Component
{
    use WithPagination;

    public bool $showModal = false;

    public ?string $selectedAttributeName = null;

    public string $search = '';

    public string $sortBy = 'priority';

    public string $sortDirection = 'asc';

    public array $participants = [];

    // 🚀 PERFORMANCE: Data untuk lazy loading
    public ?int $eventId = null;

    public ?int $positionFormationId = null;

    public ?int $aspectId = null;

    public ?int $tolerancePercentage = null;

    // 🚀 PERFORMANCE: Cache filtered results to avoid re-computation
    private ?array $filteredCache = null;

    private ?string $lastSearchTerm = null;

    private ?string $lastSortKey = null;

    protected $listeners = ['openAttributeParticipantModal'];

    /**
     * Open modal with lazy loading data
     * 🚀 PERFORMANCE: Only store IDs, load data when modal renders
     */
    public function openAttributeParticipantModal(
        string $attributeName,
        int $eventId,
        int $positionFormationId,
        int $aspectId,
        int $tolerancePercentage
    ): void {
        $this->selectedAttributeName = $attributeName;
        $this->eventId = $eventId;
        $this->positionFormationId = $positionFormationId;
        $this->aspectId = $aspectId;
        $this->tolerancePercentage = $tolerancePercentage;
        $this->showModal = true;
        $this->resetPage();
        $this->clearCache();
    }

    /**
     * Close modal
     */
    public function closeModal(): void
    {
        $this->showModal = false;
        $this->selectedAttributeName = null;
        $this->participants = [];
        $this->search = '';
        $this->eventId = null;
        $this->positionFormationId = null;
        $this->aspectId = null;
        $this->tolerancePercentage = null;
        $this->resetPage();
        $this->clearCache();
    }

    /**
     * Clear filter cache
     */
    private function clearCache(): void
    {
        $this->filteredCache = null;
        $this->lastSearchTerm = null;
        $this->lastSortKey = null;
    }

    /**
     * Load participants data from service
     * 🚀 PERFORMANCE: Lazy load only when modal is shown
     */
    private function loadParticipants(): void
    {
        if (! $this->showModal || ! $this->eventId || ! $this->positionFormationId || ! $this->aspectId) {
            return;
        }

        // Return if already loaded
        if (! empty($this->participants)) {
            return;
        }

        // Get participants for this aspect
        $service = app(TrainingRecommendationService::class);
        $participants = $service->getParticipantsRecommendation(
            $this->eventId,
            $this->positionFormationId,
            $this->aspectId,
            $this->tolerancePercentage
        );

        // Filter only recommended participants
        $participants = $participants->filter(function ($participant) {
            return $participant['is_recommended'] === true;
        });

        // Hydrate position names
        $positionIds = $participants->pluck('position_formation_id')->unique()->filter()->all();

        if (! empty($positionIds)) {
            $positions = \App\Models\PositionFormation::whereIn('id', $positionIds)
                ->select('id', 'name')
                ->get()
                ->keyBy('id');

            $participants = $participants->map(function ($participant) use ($positions) {
                $participant['position'] = $positions->get($participant['position_formation_id'])->name ?? '-';

                return $participant;
            });
        }

        $this->participants = $participants->values()->toArray();
    }

    /**
     * Sort by column
     */
    public function sortBy(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }

        $this->clearCache();
        $this->resetPage();
    }

    /**
     * Get filtered and sorted participants
     * 🚀 PERFORMANCE: Cached to avoid re-computation on pagination
     */
    public function getFilteredParticipantsProperty()
    {
        // 🚀 Check cache validity
        $currentSortKey = "{$this->sortBy}_{$this->sortDirection}";

        if ($this->filteredCache !== null
            && $this->lastSearchTerm === $this->search
            && $this->lastSortKey === $currentSortKey) {
            return collect($this->filteredCache);
        }

        // Cache miss - perform filtering and sorting
        $filtered = collect($this->participants);

        // Search filter
        if ($this->search) {
            $searchLower = strtolower($this->search);
            $filtered = $filtered->filter(function ($participant) use ($searchLower) {
                return str_contains(strtolower($participant['name']), $searchLower) ||
                    str_contains(strtolower($participant['test_number']), $searchLower);
            });
        }

        // Sort
        $filtered = $filtered->sortBy([
            fn ($a, $b) => $this->sortDirection === 'asc'
                ? $a[$this->sortBy] <=> $b[$this->sortBy]
                : $b[$this->sortBy] <=> $a[$this->sortBy],
        ]);

        // 🚀 Update cache
        $this->filteredCache = $filtered->values()->all();
        $this->lastSearchTerm = $this->search;
        $this->lastSortKey = $currentSortKey;

        return $filtered;
    }

    /**
     * Get paginated participants
     */
    public function getPaginatedParticipantsProperty()
    {
        $perPage = 15;
        $page = $this->getPage();
        $filtered = $this->filteredParticipants;

        return [
            'data' => $filtered->forPage($page, $perPage)->values(),
            'total' => $filtered->count(),
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => ceil($filtered->count() / $perPage),
        ];
    }

    public function updatingSearch(): void
    {
        $this->clearCache();
        $this->resetPage();
    }

    public function render()
    {
        // 🚀 PERFORMANCE: Load participants only when modal is shown
        if ($this->showModal) {
            $this->loadParticipants();
        }

        // 🚀 PERFORMANCE: Only compute pagination when modal is actually shown
        $paginatedData = $this->showModal ? $this->paginatedParticipants : [
            'data' => collect([]),
            'total' => 0,
            'per_page' => 15,
            'current_page' => 1,
            'last_page' => 1,
        ];

        return view('livewire.pages.general-report.training.attribute-participant-list-modal', [
            'paginatedData' => $paginatedData,
        ]);
    }
}
