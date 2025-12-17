<?php

namespace App\Livewire\Pages\GeneralReport\Training;

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

    protected $listeners = ['openAttributeParticipantModal'];

    /**
     * Open modal with participants data
     */
    public function openAttributeParticipantModal(string $attributeName, array $participants): void
    {
        $this->selectedAttributeName = $attributeName;
        $this->participants = $participants;
        $this->showModal = true;
        $this->resetPage();
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
        $this->resetPage();
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

        $this->resetPage();
    }

    /**
     * Get filtered and sorted participants
     */
    public function getFilteredParticipantsProperty()
    {
        $filtered = collect($this->participants);

        // Search filter
        if ($this->search) {
            $filtered = $filtered->filter(function ($participant) {
                return str_contains(strtolower($participant['name']), strtolower($this->search)) ||
                    str_contains(strtolower($participant['test_number']), strtolower($this->search));
            });
        }

        // Sort
        $filtered = $filtered->sortBy([
            fn ($a, $b) => $this->sortDirection === 'asc'
                ? $a[$this->sortBy] <=> $b[$this->sortBy]
                : $b[$this->sortBy] <=> $a[$this->sortBy],
        ]);

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
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.pages.general-report.training.attribute-participant-list-modal', [
            'paginatedData' => $this->paginatedParticipants,
        ]);
    }
}
