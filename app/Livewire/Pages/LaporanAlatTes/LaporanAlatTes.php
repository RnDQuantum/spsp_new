<?php

namespace App\Livewire\Pages\LaporanAlatTes;

use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
class LaporanAlatTes extends Component
{
    public function render()
    {
        return view('livewire.pages.laporan-alat-tes.laporan-alat-tes');
    }
}
