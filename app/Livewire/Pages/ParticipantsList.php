<?php

namespace App\Livewire\Pages;

use App\Models\AssessmentEvent;
use App\Models\Participant;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app', ['title' => 'Shortlist Peserta'])]
class ParticipantsList extends Component
{
    use WithPagination;

    public string $selectedEventId = '';

    public string $search = '';

    public array $assessmentEventsSearchable = [];

    public bool $readyToLoad = false;

    // Add perPage property
    public int $perPage = 10;

    // Add sorting properties
    public string $sortField = 'name';
    public string $sortDirection = 'asc';

    public function mount(): void
    {
        // Don't set default event - let user choose
        // Initialize searchable events with "Show All" option
        $this->searchEvents();
    }

    public function loadParticipants(): void
    {
        $this->readyToLoad = true;
    }

    #[Computed]
    public function assessmentEvents()
    {
        return AssessmentEvent::select('id', 'code', 'name')
            ->orderBy('code')
            ->get();
    }

    public function searchEvents(string $value = ''): void
    {
        // Get currently selected event if exists
        $selectedEvent = $this->selectedEventId
            ? AssessmentEvent::where('id', $this->selectedEventId)->get()
            : collect();

        // Search events
        $events = AssessmentEvent::query()
            ->select('id', 'code', 'name')
            ->when($value, function ($query) use ($value) {
                $query->where(function ($q) use ($value) {
                    $q->where('code', 'like', "%{$value}%")
                        ->orWhere('name', 'like', "%{$value}%");
                });
            })
            ->orderBy('code')
            ->take(10)
            ->get()
            ->merge($selectedEvent)
            ->unique('id')
            ->map(function ($event) {
                return [
                    'id' => $event->id,
                    'name' => "{$event->code} - {$event->name}",
                ];
            })
            ->values();

        // Prepend "Tampilkan Semua" option
        $this->assessmentEventsSearchable = collect([
            [
                'id' => '',
                'name' => '🔍 Tampilkan Semua Proyek',
            ],
        ])->merge($events)->toArray();
    }

    // Sort method to handle column sorting
    public function sort($field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    // Get the sort direction icon for UI
    public function getSortDirectionIcon($field): string
    {
        if ($this->sortField !== $field) {
            return 'none';
        }

        return $this->sortDirection === 'asc' ? 'asc' : 'desc';
    }

    #[Computed]
    public function participants()
    {
        if (! $this->readyToLoad) {
            return new \Illuminate\Pagination\LengthAwarePaginator([], 0, $this->perPage);
        }

        $query = Participant::with([
            'assessmentEvent:id,code,name',
            'batch:id,name',
            'positionFormation:id,name,code',
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

        // Apply sorting based on field
        // Handle relationship sorting with special cases
        switch ($this->sortField) {
            case 'event_code':
                $query->join('assessment_events', 'participants.event_id', '=', 'assessment_events.id')
                    ->orderBy('assessment_events.code', $this->sortDirection)
                    ->select('participants.*');
                break;
            case 'batch_name':
                $query->join('batches', 'participants.batch_id', '=', 'batches.id')
                    ->orderBy('batches.name', $this->sortDirection)
                    ->select('participants.*');
                break;
            case 'position_name':
                $query->join('position_formations', 'participants.position_formation_id', '=', 'position_formations.id')
                    ->orderBy('position_formations.name', $this->sortDirection)
                    ->select('participants.*');
                break;
            default:
                // For direct participant fields
                $query->orderBy($this->sortField, $this->sortDirection);
        }

        // Handle "All" option
        if ($this->perPage === 0) {
            $results = $query->get();
            return new \Illuminate\Pagination\LengthAwarePaginator(
                $results,
                $results->count(),
                $results->count(),
                1,
                ['path' => request()->url()]
            );
        }

        return $query->paginate($this->perPage);
    }

    public function updatedSelectedEventId(): void
    {
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    // Add updatedPerPage method
    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->selectedEventId = '';
        $this->search = '';
        $this->perPage = 10;
        // Reset to default sort
        $this->sortField = 'name';
        $this->sortDirection = 'asc';
        $this->searchEvents();
        $this->resetPage();
    }

    public function handleDetail(Participant $participant): void
    {
        // Simpan data ke session untuk sidebar menggunakan session()->put()
        session()->put([
            'filter.event_code' => $participant->assessmentEvent->code,
            'filter.position_formation_id' => $participant->position_formation_id,
            'filter.participant_id' => $participant->id
        ]);

        // Redirect ke halaman detail dengan eventCode dan testNumber
        $this->redirect(route('participant_detail', [
            'eventCode' => $participant->assessmentEvent->code,
            'testNumber' => $participant->test_number
        ]));
    }

    public function render()
    {
        return view('livewire.pages.shortlist');
    }
}
