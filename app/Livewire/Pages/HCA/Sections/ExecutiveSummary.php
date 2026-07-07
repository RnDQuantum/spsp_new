<?php

declare(strict_types=1);

namespace App\Livewire\Pages\HCA\Sections;

use Livewire\Component;
use Illuminate\View\View;

class ExecutiveSummary extends Component
{
    public float $talentIndex = 4.12;
    public string $talentCategory = 'Strong Talent';
    
    public array $pillars = [
        ['name' => 'Kompetensi', 'rating' => 4.00, 'label' => 'Sangat Baik'],
        ['name' => 'Potensi', 'rating' => 4.25, 'label' => 'Sangat Baik'],
        ['name' => 'Kinerja', 'rating' => 4.50, 'label' => 'Istimewa'],
        ['name' => 'Kepemimpinan', 'rating' => 3.80, 'label' => 'Baik'],
        ['name' => 'Integritas', 'rating' => 4.30, 'label' => 'Sangat Baik'],
    ];

    public function render(): View
    {
        return view('livewire.pages.h-c-a.sections.executive-summary');
    }
}
