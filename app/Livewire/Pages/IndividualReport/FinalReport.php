<?php

namespace App\Livewire\Pages\IndividualReport;

use App\Models\AssessmentEvent;
use App\Models\FinalAssessment;
use App\Models\Participant;
use App\Models\PsychologicalTest;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app', ['title' => 'Final Report'])]
class FinalReport extends Component
{
    public $eventCode;
    public $testNumber;
    public $institutionName;
    public $eventName;
    public $participant;
    public $finalAssessment;
    public $psychologicalTest;

    public function mount($eventCode, $testNumber): void
    {
        $this->eventCode = $eventCode;
        $this->testNumber = $testNumber;

        // Ambil AssessmentEvent dengan relasi institution berdasarkan eventCode
        $assessmentEvent = AssessmentEvent::with('institution')
            ->where('code', $this->eventCode)
            ->first();

        if ($assessmentEvent) {
            $this->institutionName = $assessmentEvent->institution->name ?? '';
            $this->eventName = $assessmentEvent->name ?? '';
        }

        // Ambil participant dengan relasi
        $this->participant = Participant::with([
            'assessmentEvent',
            'batch',
            'positionFormation.template',
        ])
            ->whereHas('assessmentEvent', function ($query) {
                $query->where('code', $this->eventCode);
            })
            ->where('test_number', $this->testNumber)
            ->firstOrFail();

        // Load Final Assessment
        $this->finalAssessment = FinalAssessment::where('participant_id', $this->participant->id)->first();

        // Load Psychological Test
        $this->psychologicalTest = PsychologicalTest::where('participant_id', $this->participant->id)->first();
    }

    public function render()
    {
        return view('livewire.pages.individual-report.final-report');
    }
}
