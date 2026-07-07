<?php

declare(strict_types=1);

namespace App\Livewire\Pages\HCA\Sections;

use Livewire\Component;
use Illuminate\View\View;

class ParticipantProfile extends Component
{
    public array $biodata = [
        ['label' => 'Nama Lengkap', 'value' => 'Budi Santoso, M.B.A.'],
        ['label' => 'Nomor Induk Karyawan (NIK)', 'value' => 'EMP2015091204'],
        ['label' => 'Jabatan Saat Ini', 'value' => 'VP of Talent Development'],
        ['label' => 'Unit Kerja / Divisi', 'value' => 'Human Capital Division'],
        ['label' => 'Pendidikan Terakhir', 'value' => 'Master of Business Administration (HR Management)'],
        ['label' => 'Masa Kerja Efektif', 'value' => '11 Tahun 4 Bulan'],
        ['label' => 'Tanggal Assessment', 'value' => '12 Maret 2026'],
        ['label' => 'Asesor Utama', 'value' => 'Dr. H. Hermawan, M.Psi.'],
        ['label' => 'Status Personal', 'value' => 'Menikah (2 Anak)'],
        ['label' => 'Tempat, Tanggal Lahir', 'value' => 'Jakarta, 14 Agustus 1988'],
    ];

    public function render(): View
    {
        return view('livewire.pages.h-c-a.sections.participant-profile');
    }
}
