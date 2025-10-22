<?php

namespace App\Livewire\Pages\IndividualReport;

use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app', ['title' => 'Final Report'])]
class FinalReport extends Component
{
    public $eventCode;
    public $testNumber;

    public function mount($eventCode, $testNumber): void
    {
        $this->eventCode = $eventCode;
        $this->testNumber = $testNumber;
    }

    public function render()
    {
        return view('livewire.pages.individual-report.final-report');
    }
}
