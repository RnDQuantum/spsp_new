<?php

namespace App\Livewire\Pages\LaporanAlatTes;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app', ['title' => 'Laporan Alat Tes'])]
class LaporanAlatTes extends Component
{
    public function render()
    {
        return view('livewire.pages.laporan-alat-tes.index');
    }
}
