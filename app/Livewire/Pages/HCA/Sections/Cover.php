<?php

declare(strict_types=1);

namespace App\Livewire\Pages\HCA\Sections;

use App\Models\Participant;
use Illuminate\View\View;
use Livewire\Attributes\Reactive;
use Livewire\Component;

class Cover extends Component
{
    #[Reactive]
    public ?int $participantId = null;

    public function render(): View
    {
        $participant = null;
        if ($this->participantId) {
            $participant = Participant::with([
                'assessmentEvent.institution',
                'positionFormation.template',
                'batch',
                'finalAssessment',
                'institution',
            ])->find($this->participantId);
        }

        if (! $participant) {
            $participant = Participant::with([
                'assessmentEvent.institution',
                'positionFormation.template',
                'batch',
                'finalAssessment',
                'institution',
            ])->first();
        }

        return view('livewire.pages.h-c-a.sections.cover', [
            'participant' => $participant,
        ]);
    }
}
