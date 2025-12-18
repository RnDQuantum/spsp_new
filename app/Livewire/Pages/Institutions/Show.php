<?php

namespace App\Livewire\Pages\Institutions;

use App\Models\Institution;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app', ['title' => 'Detail Institusi'])]
class Show extends Component
{
    use WithPagination;

    public Institution $institution;

    public $yearFilter = 'all';

    public $statusFilter = 'all';

    public function mount(Institution $institution): void
    {
        $this->institution = $institution->load(['categories']);
    }

    public function updatingYearFilter(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = $this->institution->assessmentEvents()
            ->with(['batches', 'participants'])
            ->withCount(['batches', 'participants']);

        if ($this->yearFilter !== 'all') {
            $query->where('year', $this->yearFilter);
        }

        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        $events = $query->latest()->paginate(10);

        $primaryCategory = $this->institution->categories
            ->where('pivot.is_primary', true)
            ->first() ?? $this->institution->categories->first();

        return view('livewire.pages.institutions.show', [
            'events' => $events,
            'primaryCategory' => $primaryCategory,
            'years' => $this->getAvailableYears(),
            'stats' => $this->getStats(),
        ]);
    }

    private function getAvailableYears(): array
    {
        $years = $this->institution->assessmentEvents()
            ->distinct()
            ->pluck('year')
            ->sort()
            ->values()
            ->toArray();

        return $years ?: [date('Y')];
    }

    private function getStats(): array
    {
        return [
            'total_events' => $this->institution->assessmentEvents()->count(),
            'active_events' => $this->institution->assessmentEvents()->where('status', 'ongoing')->count(),
            'completed_events' => $this->institution->assessmentEvents()->where('status', 'completed')->count(),
            'total_participants' => $this->institution->assessmentEvents()
                ->withCount('participants')
                ->get()
                ->sum('participants_count'),
        ];
    }
}
