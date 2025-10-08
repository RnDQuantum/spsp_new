<?php

namespace App\Livewire\Pages;

use Livewire\Component;
use App\Models\Participant;
use Livewire\WithPagination;
use App\Models\AssessmentEvent;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;


#[Layout('components.layouts.app', ['title' => 'Shortlist Peserta'])]
class ParticipantsList extends Component
{
    use WithPagination;

    public string $selectedEventId = '';
    public string $search = '';

    public function mount(): void
    {
        // Set default to first assessment event if available
        $firstEvent = AssessmentEvent::first();
        if ($firstEvent) {
            $this->selectedEventId = $firstEvent->id;
        }
    }

    #[Computed]
    public function assessmentEvents()
    {
        return AssessmentEvent::select('id', 'code', 'name')
            ->orderBy('code')
            ->get();
    }

    #[Computed]
    public function participants()
    {
        $query = Participant::with([
            'assessmentEvent:id,code,name',
            'batch:id,name',
            'positionFormation:id,name,code'
        ]);

        // Filter berdasarkan assessment event yang dipilih
        if ($this->selectedEventId) {
            $query->where('event_id', $this->selectedEventId);
        }

        // Filter berdasarkan search (nama, NIP jika ada)
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('test_number', 'like', '%' . $this->search . '%')
                  ->orWhere('skb_number', 'like', '%' . $this->search . '%');
            });
        }

        return $query->orderBy('name')->paginate(10);
    }

    public function updatedSelectedEventId(): void
    {
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->selectedEventId = '';
        $this->search = '';
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.pages.shortlist');
    }
}