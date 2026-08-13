<?php

namespace App\Livewire\Pages\LaporanAlatTes;

use App\Models\Participant;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app', ['title' => 'Laporan Alat Tes'])]
class LaporanAlatTes extends Component
{
    use WithPagination;

    public string $search = '';

    public int $perPage = 15;

    #[On('event-selected')]
    public function handleEventSelected(?string $eventCode = null): void
    {
        $this->resetPage();
    }

    #[On('position-selected')]
    public function handlePositionSelected(?int $positionFormationId = null): void
    {
        $this->resetPage();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $eventCode = session('filter.event_code');
        $positionFormationId = session('filter.position_formation_id');

        $query = Participant::query()
            ->with(['positionFormation', 'assessmentEvent', 'assessmentEvent.project', 'assessmentEvent.institution'])
            ->withCount('testResults');

        if ($eventCode) {
            $query->whereHas('assessmentEvent', function ($q) use ($eventCode) {
                $q->where('code', $eventCode);
            });
        }

        if ($positionFormationId) {
            $query->where('position_formation_id', $positionFormationId);
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'LIKE', "%{$this->search}%")
                    ->orWhere('test_number', 'LIKE', "%{$this->search}%")
                    ->orWhere('skb_number', 'LIKE', "%{$this->search}%")
                    ->orWhere('username', 'LIKE', "%{$this->search}%");
            });
        }

        $participants = $query->orderBy('name', 'asc')->paginate($this->perPage);

        return view('livewire.pages.laporan-alat-tes.index', [
            'participants' => $participants,
        ]);
    }
}
