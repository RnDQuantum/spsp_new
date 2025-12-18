<?php

namespace App\Livewire\Pages\Admin;

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
        // Handle special cases for related fields
        if ($field === 'category') {
            $field = 'category_name'; // Use a custom field name for category sorting
        } elseif ($field === 'status') {
            $field = 'status_raw'; // Use the raw status field for sorting
        } elseif ($field === 'date') {
            $field = 'created_at'; // Use the actual database field
        }

        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function render()
    {
        // Handle sorting for special fields with joins
        if ($this->sortField === 'category_name') {
            // For category sorting, use subquery to get primary category name
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
            ])->select('institutions.*')
                ->leftJoin('category_institution', function ($join) {
                    $join->on('institutions.id', '=', 'category_institution.institution_id')
                        ->where('category_institution.is_primary', true);
                })
                ->leftJoin('institution_categories', 'category_institution.institution_category_id', '=', 'institution_categories.id')
                ->orderBy('institution_categories.name', $this->sortDirection);
        } elseif ($this->sortField === 'status_raw') {
            // For status sorting, use subquery to get latest event status
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
            ])->select('institutions.*')
                ->leftJoin('assessment_events', function ($join) {
                    $join->on('institutions.id', '=', 'assessment_events.institution_id')
                        ->where('assessment_events.id', '=', function ($subquery) {
                            $subquery->selectRaw('MAX(id)')
                                ->from('assessment_events')
                                ->whereColumn('institution_id', 'institutions.id');
                        });
                })
                ->orderBy('assessment_events.status', $this->sortDirection);
        } else {
            // For regular fields, use the standard approach
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
            ])->orderBy($this->sortField, $this->sortDirection);
        }

        if ($this->search) {
            $query->where('institutions.name', 'like', "%{$this->search}%");
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

        return view('livewire.pages.admin.list-klien', [
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
