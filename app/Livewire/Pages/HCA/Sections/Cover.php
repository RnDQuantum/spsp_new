<?php

declare(strict_types=1);

namespace App\Livewire\Pages\HCA\Sections;

use App\Services\HcaDataService;
use Illuminate\View\View;
use Livewire\Attributes\Reactive;
use Livewire\Component;

class Cover extends Component
{
    #[Reactive]
    public ?int $participantId = null;

    public function render(): View
    {
        $participant = app(HcaDataService::class)->getParticipant($this->participantId);

        return view('livewire.pages.h-c-a.sections.cover', [
            'participant' => $participant,
        ]);
    }
}
