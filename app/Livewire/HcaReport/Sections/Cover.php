<?php

declare(strict_types=1);

namespace App\Livewire\HcaReport\Sections;

use Livewire\Component;
use Illuminate\View\View;

class Cover extends Component
{
    public function render(): View
    {
        return view('livewire.hca-report.sections.cover');
    }
}
