<?php

namespace App\Livewire\Pages;

use App\Models\Institution;
use App\Models\InstitutionCategory;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app', ['title' => 'Beranda Admin'])]
class DashboardAdmin extends Component
{
    public $selectedYear;
    public $selectedCategory = 'all';
    public $selectedStatus = 'all';

    public function mount(): void
    {
        $this->selectedYear = date('Y');
    }

    public function render()
    {
        $stats = $this->getStatistics();
        $recentClients = $this->getRecentClients();

        return view('livewire.pages.dashboard-admin', [
            'years' => $this->getAvailableYears(),
            'categories' => InstitutionCategory::where('is_active', true)->orderBy('order')->get(),
            'stats' => $stats,
            'recentClients' => $recentClients,
        ]);
    }

    private function getAvailableYears(): array
    {
        $currentYear = date('Y');
        return range($currentYear, $currentYear - 5);
    }

    private function getStatistics(): array
    {
        $query = Institution::query();

        if ($this->selectedYear) {
            $query->whereHas('assessmentEvents', function ($q) {
                $q->where('year', $this->selectedYear);
            });
        }

        $totalClients = $query->count();

        $categoryStats = InstitutionCategory::where('is_active', true)
            ->withCount(['institutions' => function ($q) {
                if ($this->selectedYear) {
                    $q->whereHas('assessmentEvents', function ($aq) {
                        $aq->where('year', $this->selectedYear);
                    });
                }
            }])
            ->orderBy('order')
            ->get()
            ->mapWithKeys(fn ($category) => [$category->code => $category->institutions_count]);

        return [
            'total' => $totalClients,
            'categories' => $categoryStats,
        ];
    }

    private function getRecentClients()
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
            }
        ]);

        if ($this->selectedYear !== 'all' && $this->selectedYear) {
            $query->whereHas('assessmentEvents', function ($q) {
                $q->where('year', $this->selectedYear);
            });
        }

        if ($this->selectedCategory !== 'all') {
            $query->whereHas('categories', function ($q) {
                $q->where('institution_categories.code', $this->selectedCategory);
            });
        }

        return $query->latest()->limit(10)->get()->map(function ($institution) {
            $latestEvent = $institution->assessmentEvents->first();
            $primaryCategory = $institution->categories->first();

            return [
                'name' => $institution->name,
                'category' => $primaryCategory?->name ?? '-',
                'categories' => $institution->categories->pluck('name')->join(', '),
                'date' => $institution->created_at->format('d M Y'),
                'status' => $latestEvent ? $this->mapStatus($latestEvent->status) : 'Pending',
                'status_class' => $latestEvent ? $this->getStatusClass($latestEvent->status) : 'yellow',
            ];
        });
    }

    private function mapStatus(?string $status): string
    {
        return match($status) {
            'ongoing' => 'Aktif',
            'completed' => 'Selesai',
            'draft' => 'Pending',
            default => 'Pending'
        };
    }

    private function getStatusClass(?string $status): string
    {
        return match($status) {
            'ongoing' => 'green',
            'completed' => 'gray',
            'draft' => 'yellow',
            default => 'yellow'
        };
    }
}
