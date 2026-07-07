<?php

declare(strict_types=1);

namespace App\Livewire\Pages\HCA\Sections;

use Livewire\Component;
use Illuminate\View\View;

class RiskIndicators extends Component
{
    public string $overallRisk = 'Rendah';
    
    public array $indicators = [
        ['label' => 'Saturasi Kejenuhan (Burnout Risk)', 'level' => 'Rendah', 'desc' => 'Tingkat kelelahan fisik, emosional, dan mental akibat tekanan tugas harian.'],
        ['label' => 'Kerentanan Stres (Stress Susceptibility)', 'level' => 'Sedang', 'desc' => 'Respon emosional kandidat saat berhadapan dengan tenggat waktu ketat secara beruntun.'],
        ['label' => 'Indeks Konflik Interpersonal', 'level' => 'Rendah', 'desc' => 'Kecenderungan kandidat untuk mengalami gesekan komunikasi dengan rekan sejawat.'],
        ['label' => 'Risiko Penurunan Produktivitas', 'level' => 'Rendah', 'desc' => 'Proyeksi fluktuasi kinerja kandidat dalam situasi perubahan organisasi makro.'],
    ];

    public function render(): View
    {
        return view('livewire.pages.h-c-a.sections.risk-indicators');
    }
}
