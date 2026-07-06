<?php

declare(strict_types=1);

namespace App\Livewire\HcaReport\Sections;

use Livewire\Component;
use Illuminate\View\View;

class IndexRadarSection extends Component
{
    public string $chartId;

    public float $talentIndex = 4.12; // Out of 5.00
    public float $talentIndexPercent = 82.40; // Out of 100%
    public string $talentCategory = 'Strong Talent';

    public array $labels = ['Kompetensi', 'Potensi', 'Kinerja', 'Kepemimpinan', 'Integritas'];
    
    // Ratings are on 1.00 - 5.00 scale
    public array $actualRatings = [4.00, 4.25, 4.50, 3.80, 4.30];
    public array $standardRatings = [3.00, 3.00, 3.00, 3.00, 3.00];
    public array $toleranceRatings = [2.70, 2.70, 2.70, 2.70, 2.70];

    public function mount(): void
    {
        $this->chartId = 'hciRadar_' . uniqid();
    }

    public function render(): View
    {
        return view('livewire.hca-report.sections.index-radar-section');
    }
}
