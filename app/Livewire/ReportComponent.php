<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app', ['title' => 'General Matching'])]
class ReportComponent extends Component
{
    // Public properties to hold percentage data
    public $jobMatchPercentage;
    public $kecerdasanPercentage;
    public $caraKerjaPercentage;
    public $potensiKerjaPercentage;
    public $hubunganSosialPercentage;
    public $kepribadianPercentage;
    public $integritasPercentage;
    public $kerjasamaPercentage;
    public $komunikasiPercentage;
    public $orientasiHasilPercentage;
    public $pelayananPublikPercentage;
    public $pengembanganDiriPercentage;
    public $mengelolaPerubahanPercentage;
    public $pengambilanKeputusanPercentage;
    public $perekatBangsaPercentage;

    public function mount()
    {
        // Inisialisasi nilai persentase
        $this->jobMatchPercentage = 94;
        $this->kecerdasanPercentage = 100;
        $this->caraKerjaPercentage = 100;
        $this->potensiKerjaPercentage = 94;
        $this->hubunganSosialPercentage = 83;
        $this->kepribadianPercentage = 100;
        $this->integritasPercentage = 100;
        $this->kerjasamaPercentage = 75;
        $this->komunikasiPercentage = 75;
        $this->orientasiHasilPercentage = 100;
        $this->pelayananPublikPercentage = 75;
        $this->pengembanganDiriPercentage = 100;
        $this->mengelolaPerubahanPercentage = 75;
        $this->pengambilanKeputusanPercentage = 100;
        $this->perekatBangsaPercentage = 100;
    }

    public function render()
    {
        return view('livewire.pages.general_matching');
    }
}
