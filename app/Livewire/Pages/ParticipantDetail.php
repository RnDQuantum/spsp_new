<?php

namespace App\Livewire\Pages;

use App\Livewire\Concerns\SyncsSessionFromUrlParams;
use App\Models\Participant;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app', ['title' => 'Detail Peserta'])]
class ParticipantDetail extends Component
{
    use SyncsSessionFromUrlParams;

    public ?Participant $participant = null;

    public function mount($eventCode, $testNumber): void
    {
        // Load participant dengan semua relasi yang diperlukan
        $this->participant = Participant::with([
            'assessmentEvent',
            'batch',
            'positionFormation.template',
        ])
            ->whereHas('assessmentEvent', function ($query) use ($eventCode) {
                $query->where('code', $eventCode);
            })
            ->where('test_number', $testNumber)
            ->firstOrFail();

        // Sync session from URL parameters
        $this->syncSessionFromParticipant($this->participant);
    }

    public function render()
    {
        return view('livewire.pages.participant-detail');
    }
}
