<?php

declare(strict_types=1);

namespace App\Livewire\Pages\HCA\Sections;

use Livewire\Component;
use Illuminate\View\View;

class NineBoxMatrix extends Component
{
    // The active box coordinates: potential (high=3, med=2, low=1) and performance (high=3, med=2, low=1)
    // Budi Santoso is typically High Potential (3) and Med Performance (2) or High Performance (3) -> Let's say High Potential, High Performance (Box 9 - Star Player / Future Leader)
    public int $activePotential = 3;
    public int $activePerformance = 3;
    
    public array $grid = [
        // Format: [pot_level, perf_level, label, desc]
        [3, 1, 'Enigma', 'Potensi Tinggi, Kinerja Rendah'],
        [3, 2, 'High Potential', 'Potensi Tinggi, Kinerja Sedang'],
        [3, 3, 'Star Talent', 'Potensi Tinggi, Kinerja Tinggi'],
        
        [2, 1, 'Dilemma', 'Potensi Sedang, Kinerja Rendah'],
        [2, 2, 'Core Player', 'Potensi Sedang, Kinerja Sedang'],
        [2, 3, 'High Performer', 'Potensi Sedang, Kinerja Tinggi'],
        
        [1, 1, 'Underperformer', 'Potensi Rendah, Kinerja Rendah'],
        [1, 2, 'Effective Organiser', 'Potensi Rendah, Kinerja Sedang'],
        [1, 3, 'Solid Professional', 'Potensi Rendah, Kinerja Tinggi'],
    ];

    public function render(): View
    {
        return view('livewire.pages.h-c-a.sections.nine-box-matrix');
    }
}
