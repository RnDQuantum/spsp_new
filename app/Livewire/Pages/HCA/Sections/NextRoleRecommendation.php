<?php

declare(strict_types=1);

namespace App\Livewire\Pages\HCA\Sections;

use Livewire\Component;
use Illuminate\View\View;

class NextRoleRecommendation extends Component
{
    public string $recommendedRole = 'VP of Human Capital Development';
    
    public array $phases = [
        [
            'title' => 'Fase 1: Transisi & Pendampingan',
            'timeframe' => 'Bulan 1–3',
            'desc' => 'Melaksanakan serah terima jabatan secara bertahap dan mengikuti program orientasi kepemimpinan strategis didampingi mentor eksekutif.'
        ],
        [
            'title' => 'Fase 2: Rotasi Proyek Lintas Divisi',
            'timeframe' => 'Bulan 4–6',
            'desc' => 'Memimpin satuan tugas khusus lintas unit bisnis untuk memperluas pemahaman operasional dan melatih pengambilan keputusan makro.'
        ],
        [
            'title' => 'Fase 3: Kemandirian Penuh',
            'timeframe' => 'Bulan 7+',
            'desc' => 'Mengemban tanggung jawab penuh atas target pencapaian divisi dan mengeksekusi strategi pengembangan talenta korporasi.'
        ]
    ];

    public function render(): View
    {
        return view('livewire.pages.h-c-a.sections.next-role-recommendation');
    }
}
