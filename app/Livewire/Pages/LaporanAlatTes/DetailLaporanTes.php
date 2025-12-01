<?php

namespace App\Livewire\Pages\LaporanAlatTes;

use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app', ['title' => 'Detail Laporan Alat Tes'])]
class DetailLaporanTes extends Component
{
    // Nanti bisa ditambah property misalnya public $testId;

    public function render()
    {
        // View: resources/views/livewire/pages/laporan-alat-tes/detail-laporan-tes.blade.php
        return view('livewire.pages.laporan-alat-tes.detail-laporan-tes');
    }
}
