<?php

namespace App\Livewire\Pages\GeneralReport;

use App\Models\Mmpi;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app', ['title' => 'MMPI'])]
class MmpiResultsReport extends Component
{
    use WithPagination;

    // Pagination
    public $perPage = 10;

    // Global search
    public $search = '';

    // Sorting
    public $sortField = 'id'; // Default sort by ID

    public $sortDirection = 'asc'; // Default ascending

    // Query string parameters
    protected $queryString = [
        'search' => ['except' => ''],
        'perPage' => ['except' => 10],
        'sortField' => ['except' => 'id'],
        'sortDirection' => ['except' => 'asc'],
    ];

    // Reset pagination when search or per page changes
    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    // Reset search
    public function resetSearch()
    {
        $this->search = '';
        $this->resetPage();
    }

    // Sorting method
    public function sortBy($field)
    {
        // Jika field yang sama diklik, toggle direction
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            // Jika field baru, set ke ascending
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    public function render()
    {
        // Cek apakah tabel ada dan berisi data
        $tableExists = DB::getSchemaBuilder()->hasTable('mmpi');
        $dataCount = 0;

        if ($tableExists) {
            $dataCount = DB::table('mmpi')->count();
        }

        // Ambil data dengan filter
        $mmpiResultsQuery = Mmpi::query();

        // Jika ada pencarian, cari di semua field relevan sekaligus
        if ($this->search) {
            $mmpiResultsQuery->where(function ($query) {
                // Cari berdasarkan kode proyek
                $query->whereHas('event', function ($eventQuery) {
                    $eventQuery->where('code', 'like', '%'.$this->search.'%');
                })
                    // Atau berdasarkan no_test
                    ->orWhere('no_test', 'like', '%'.$this->search.'%')
                    // Atau berdasarkan tingkat_stres
                    ->orWhere('tingkat_stres', 'like', '%'.$this->search.'%');

                // Jika perlu juga cari berdasarkan nilai PQ (jika angka)
                if (is_numeric($this->search)) {
                    $query->orWhere('nilai_pq', $this->search);
                }
            });
        }

        // Sorting Logic
        switch ($this->sortField) {
            case 'kode_proyek':
                // Sort by relation (event code)
                $mmpiResultsQuery->join('events', 'mmpi.event_id', '=', 'events.id')
                    ->orderBy('events.code', $this->sortDirection)
                    ->select('mmpi.*');
                break;

            case 'no_test':
                $mmpiResultsQuery->orderBy('no_test', $this->sortDirection);
                break;

            case 'username':
                $mmpiResultsQuery->orderBy('username', $this->sortDirection);
                break;

            case 'nilai_pq':
                $mmpiResultsQuery->orderBy('nilai_pq', $this->sortDirection);
                break;

            case 'tingkat_stres':
                // Custom order untuk tingkat stres (normal, ringan, sedang, berat, sangat berat)
                $mmpiResultsQuery->orderByRaw("
                    CASE tingkat_stres
                        WHEN 'normal' THEN 1
                        WHEN 'ringan' THEN 2
                        WHEN 'sedang' THEN 3
                        WHEN 'berat' THEN 4
                        WHEN 'sangat berat' THEN 5
                        ELSE 6
                    END {$this->sortDirection}
                ");
                break;

            default:
                $mmpiResultsQuery->orderBy('id', $this->sortDirection);
                break;
        }

        // Pagination atau get all
        if ($this->perPage == -1) {
            $mmpiResults = $mmpiResultsQuery->get();
        } else {
            $mmpiResults = $mmpiResultsQuery->paginate($this->perPage);
        }

        return view('livewire.pages.general-report.mmpi', [
            'mmpiResults' => $mmpiResults,
            'tableExists' => $tableExists,
            'dataCount' => $dataCount,
        ]);
    }
}
