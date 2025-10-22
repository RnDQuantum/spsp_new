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

    public $perPage = 10;

    public function updatedPerPage()
    {
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

        // Ambil data dari model dan juga langsung dari DB sebagai cadangan
        $mmpiResultsFromModel = PsychologicalTest::query();

        if ($this->perPage == -1) {
            $mmpiResults = $mmpiResultsFromModel->get();
        } else {
            $mmpiResults = $mmpiResultsFromModel->paginate($this->perPage);
        }

        return view('livewire.pages.general-report.mmpi', [
            'mmpiResults' => $mmpiResults,
            'tableExists' => $tableExists,
            'dataCount' => $dataCount,
        ]);
    }
}
