<?php

namespace App\Livewire\Pages\TalentPool;

use Livewire\Component;
use Livewire\WithPagination;

class ParticipantListModal extends Component
{
    use WithPagination;

    public bool $showModal = false;

    public ?int $selectedBox = null;

    public string $search = '';

    public string $sortBy = 'name';

    public string $sortDirection = 'asc';

    public array $participants = [];

    // 🚀 PERFORMANCE: Cache filtered results to avoid re-computation
    private ?array $filteredCache = null;

    private ?string $lastSearchTerm = null;

    private ?string $lastSortKey = null;

    protected $listeners = ['openParticipantModal'];

    /**
     * Open modal with participants data
     */
    public function openParticipantModal(int $boxNumber, array $participants): void
    {
        $this->selectedBox = $boxNumber;
        $this->participants = $participants;
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
        $this->selectedBox = null;
        $this->participants = [];
        $this->search = '';
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

    /**
     * Get box configuration
     */
    public function getBoxConfigProperty(): array
    {
        return [
            1 => ['code' => 'K-1', 'label' => 'Kinerja di bawah ekspektasi dan potensi rendah', 'color' => '#8B0000'],
            2 => ['code' => 'K-2', 'label' => 'Kinerja sesuai ekspektasi dan potensi rendah', 'color' => '#FF4500'],
            3 => ['code' => 'K-3', 'label' => 'Kinerja di bawah ekspektasi dan potensi menengah', 'color' => '#FF8C00'],
            4 => ['code' => 'K-4', 'label' => 'Kinerja di atas ekspektasi dan potensi rendah', 'color' => '#FFD700'],
            5 => ['code' => 'K-5', 'label' => 'Kinerja sesuai ekspektasi dan potensi menengah', 'color' => '#FFFF00'],
            6 => ['code' => 'K-6', 'label' => 'Kinerja di bawah ekspektasi dan potensi tinggi', 'color' => '#CCFF00'],
            7 => ['code' => 'K-7', 'label' => 'Kinerja di atas ekspektasi dan potensi menengah', 'color' => '#32CD32'],
            8 => ['code' => 'K-8', 'label' => 'Kinerja sesuai ekspektasi dan potensi tinggi', 'color' => '#228B22'],
            9 => ['code' => 'K-9', 'label' => 'Kinerja di atas ekspektasi dan potensi tinggi', 'color' => '#006400'],
        ];
    }

    public function updatingSearch(): void
    {
        $this->clearCache();
        $this->resetPage();
    }

    public function render()
    {
        // 🚀 PERFORMANCE: Only compute pagination when modal is actually shown
        $paginatedData = $this->showModal ? $this->paginatedParticipants : [
            'data' => collect([]),
            'total' => 0,
            'per_page' => 15,
            'current_page' => 1,
            'last_page' => 1,
        ];

        return view('livewire.pages.talent-pool.participant-list-modal', [
            'paginatedData' => $paginatedData,
            'boxInfo' => $this->selectedBox ? $this->boxConfig[$this->selectedBox] : null,
        ]);
    }
}
