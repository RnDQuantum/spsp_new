<?php

namespace App\Livewire\Pages\GeneralReport;

use App\Models\PsychologicalTest;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app', ['title' => 'MMPI'])]
class MmpiResultsReport extends Component
{
    use WithPagination;

    // Pagination
    public $perPage = 10;

    // Global search
    public $search = '';

    // Query string parameters
    protected $queryString = [
        'search' => ['except' => ''],
        'perPage' => ['except' => 10],
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

    public function render()
    {
        // Cek apakah tabel ada dan berisi data
        $tableExists = DB::getSchemaBuilder()->hasTable('mmpi_results');
        $dataCount = 0;

        if ($tableExists) {
            $dataCount = DB::table('mmpi_results')->count();
        }

        // Ambil data dengan filter
        $mmpiResultsQuery = PsychologicalTest::query();

        // Jika ada pencarian, cari di semua field relevan sekaligus
        if ($this->search) {
            $mmpiResultsQuery->where(function ($query) {
                // Cari berdasarkan kode proyek
                $query->whereHas('event', function ($eventQuery) {
                    $eventQuery->where('code', 'like', '%' . $this->search . '%');
                })
                    // Atau berdasarkan no_test
                    ->orWhere('no_test', 'like', '%' . $this->search . '%')
                    // Atau berdasarkan tingkat_stres - gunakan where exact atau like sesuai kebutuhan
                    ->orWhere('tingkat_stres', 'like', '%' . $this->search . '%');

                // Jika perlu juga cari berdasarkan nilai PQ (jika angka)
                if (is_numeric($this->search)) {
                    $query->orWhere('nilai_pq', $this->search);
                }
            });
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
