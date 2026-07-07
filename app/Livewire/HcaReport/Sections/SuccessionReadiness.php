<?php

declare(strict_types=1);

namespace App\Livewire\HcaReport\Sections;

use Livewire\Component;
use Illuminate\View\View;

class SuccessionReadiness extends Component
{
    public array $horizons = [
        [
            'timeframe' => 'Siap Sekarang',
            'status' => 'Ready Now',
            'percentage' => 95,
            'role' => 'VP of Human Capital Development',
            'desc' => 'Menunjukkan kesiapan penuh secara kompetensi manajerial dan rekam jejak kinerja. Dapat langsung memimpin unit tanpa masa transisi panjang.'
        ],
        [
            'timeframe' => 'Kesiapan < 1 Tahun',
            'status' => 'Ready in 12 Months',
            'percentage' => 80,
            'role' => 'Director of Human Resources',
            'desc' => 'Kandidat siap dipromosikan ke jenjang direksi dalam 12 bulan mendatang setelah melalui program akselerasi kepemimpinan strategis.'
        ],
        [
            'timeframe' => 'Kesiapan < 2 Tahun',
            'status' => 'Ready in 24 Months',
            'percentage' => 60,
            'role' => 'Chief Human Resources Officer (CHRO)',
            'desc' => 'Proyeksi suksesi jangka panjang menuju puncak kepemimpinan korporasi. Membutuhkan rotasi ke unit bisnis operasional non-HR.'
        ]
    ];

    public function render(): View
    {
        return view('livewire.hca-report.sections.succession-readiness');
    }
}
