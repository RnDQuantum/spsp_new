<?php

declare(strict_types=1);

namespace App\Livewire\Pages\HCA\Sections;

use Livewire\Component;
use Illuminate\View\View;

class DevelopmentRecommendation extends Component
{
    public array $strengths = [
        'Kapasitas kepemimpinan strategis yang sangat mapan, terbukti dari nilai visi organisasi dan pengelolaan tim yang solid.',
        'Kelincahan belajar tinggi (*learning agility*), memudahkan adaptasi pada ekosistem digital baru dan perubahan proses bisnis cepat.',
        'Integritas etika tinggi, memperkuat akuntabilitas dalam pengambilan keputusan penting di bawah pengawasan ketat.',
    ];

    public array $gaps = [
        'Perlu meningkatkan keterlibatan operasional langsung dalam pemantauan detail kepatuhan teknis di lapangan.',
        'Membutuhkan eksposur kerja operasional di luar bidang fungsional utama (HR) untuk memperluas perspektif makro bisnis organisasi.',
        'Dapat melatih pengelolaan stres taktis saat menghadapi tenggat waktu kerja yang menumpuk secara berurutan.',
    ];

    public function render(): View
    {
        return view('livewire.pages.h-c-a.sections.development-recommendation');
    }
}
