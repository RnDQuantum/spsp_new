<?php

namespace App\Livewire\Events;

use App\Models\AssessmentEvent;
use App\Models\Institution;
use App\Models\InstitutionCategory;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app', ['title' => 'Daftar Assessment Event'])]
class Index extends Component
{
    use WithPagination;

    public $search = '';

    public $institutionFilter = 'all';

    public $categoryFilter = 'all';

    public $yearFilter = 'all';

    public $statusFilter = 'all';

    public $perPage = 10;

    public $sortField = 'created_at';

    public $sortDirection = 'desc';

    protected $queryString = [
        'search' => ['except' => ''],
        'institutionFilter' => ['except' => 'all'],
        'categoryFilter' => ['except' => 'all'],
        'yearFilter' => ['except' => 'all'],
        'statusFilter' => ['except' => 'all'],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingInstitutionFilter(): void
    {
        $this->resetPage();
    }

    public function updatingCategoryFilter(): void
    {
        $this->resetPage();
    }

    public function updatingYearFilter(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function render()
    {
        $query = AssessmentEvent::with([
            'institution' => function ($q) {
                $q->select('id', 'code', 'name')
                    ->with(['categories' => function ($cq) {
                        $cq->select('institution_categories.id', 'institution_categories.code', 'institution_categories.name')
                            ->orderByDesc('category_institution.is_primary');
                    }]);
            },
        ])->withCount(['batches', 'participants']);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('code', 'like', "%{$this->search}%")
                    ->orWhere('name', 'like', "%{$this->search}%");
            });
        }

        if ($this->institutionFilter !== 'all') {
            $query->where('institution_id', $this->institutionFilter);
        }

        if ($this->categoryFilter !== 'all') {
            $query->whereHas('institution.categories', function ($q) {
                $q->where('institution_categories.code', $this->categoryFilter);
            });
        }

        if ($this->yearFilter !== 'all') {
            $query->where('year', $this->yearFilter);
        }

        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        $query->orderBy($this->sortField, $this->sortDirection);

        $events = $query->paginate($this->perPage);

        return view('livewire.events.index', [
            'events' => $events,
            'institutions' => Institution::select('id', 'name')->orderBy('name')->get(),
            'categories' => InstitutionCategory::where('is_active', true)->orderBy('order')->get(),
            'years' => $this->getAvailableYears(),
            'stats' => $this->getStats(),
        ]);
    }

    private function getAvailableYears(): array
    {
        $currentYear = date('Y');

        return range($currentYear, $currentYear - 5);
    }

    private function getStats(): array
    {
        return [
            'total' => AssessmentEvent::count(),
            'ongoing' => AssessmentEvent::where('status', 'ongoing')->count(),
            'completed' => AssessmentEvent::where('status', 'completed')->count(),
            'draft' => AssessmentEvent::where('status', 'draft')->count(),
        ];
    }
}
