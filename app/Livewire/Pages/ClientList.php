<?php

namespace App\Livewire\Pages;

use App\Models\Institution;
use App\Models\InstitutionCategory;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app', ['title' => 'Daftar Klien'])]
class ClientList extends Component
{
    use WithPagination;

    public $search = '';

    public $categoryFilter = 'all';

    public $statusFilter = 'all';

    public $yearFilter = 'all';

    public $perPage = 10;

    public $sortField = 'name';

    public $sortDirection = 'asc';

    protected $queryString = [
        'search' => ['except' => ''],
        'categoryFilter' => ['except' => 'all'],
        'statusFilter' => ['except' => 'all'],
        'yearFilter' => ['except' => 'all'],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingCategoryFilter(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingYearFilter(): void
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
        $query = Institution::with([
            'categories' => function ($q) {
                $q->select('institution_categories.id', 'institution_categories.code', 'institution_categories.name')
                    ->orderByDesc('category_institution.is_primary');
            },
            'assessmentEvents' => function ($q) {
                $q->select('id', 'institution_id', 'year', 'status')
                    ->latest()
                    ->limit(1);
            },
        ]);

        if ($this->search) {
            $query->where('name', 'like', "%{$this->search}%");
        }

        if ($this->categoryFilter !== 'all') {
            $query->whereHas('categories', function ($q) {
                $q->where('institution_categories.code', $this->categoryFilter);
            });
        }

        if ($this->yearFilter !== 'all') {
            $query->whereHas('assessmentEvents', function ($q) {
                $q->where('year', $this->yearFilter);
            });
        }

        if ($this->statusFilter !== 'all') {
            $query->whereHas('assessmentEvents', function ($q) {
                $q->where('status', $this->statusFilter);
            });
        }

        $query->orderBy($this->sortField, $this->sortDirection);

        $institutions = $query->paginate($this->perPage);

        $clients = $institutions->map(function ($institution) {
            $latestEvent = $institution->assessmentEvents->first();
            $primaryCategory = $institution->categories->first();

            return [
                'id' => $institution->id,
                'name' => $institution->name,
                'code' => $institution->code,
                'category' => $primaryCategory?->name ?? '-',
                'category_code' => $primaryCategory?->code ?? null,
                'categories' => $institution->categories->pluck('name')->join(', '),
                'date' => $institution->created_at->format('d M Y'),
                'status' => $latestEvent ? $this->mapStatus($latestEvent->status) : 'Pending',
                'status_raw' => $latestEvent?->status ?? 'draft',
                'status_class' => $latestEvent ? $this->getStatusClass($latestEvent->status) : 'yellow',
                'events_count' => $institution->assessmentEvents->count(),
            ];
        });

        return view('livewire.pages.list-klien', [
            'clients' => $clients,
            'categories' => InstitutionCategory::where('is_active', true)->orderBy('order')->get(),
            'years' => $this->getAvailableYears(),
            'total' => $institutions->total(),
            'from' => $institutions->firstItem() ?? 0,
            'to' => $institutions->lastItem() ?? 0,
            'currentPage' => $institutions->currentPage(),
            'lastPage' => $institutions->lastPage(),
        ]);
    }

    private function getAvailableYears(): array
    {
        $currentYear = date('Y');

        return range($currentYear, $currentYear - 5);
    }

    private function mapStatus(?string $status): string
    {
        return match ($status) {
            'ongoing' => 'Aktif',
            'completed' => 'Selesai',
            'draft' => 'Pending',
            default => 'Pending'
        };
    }

    private function getStatusClass(?string $status): string
    {
        return match ($status) {
            'ongoing' => 'green',
            'completed' => 'gray',
            'draft' => 'yellow',
            default => 'yellow'
        };
    }
}
