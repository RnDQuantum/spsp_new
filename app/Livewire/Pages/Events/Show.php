<?php

namespace App\Livewire\Pages\Events;

use App\Models\AssessmentEvent;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app', ['title' => 'Detail Event'])]
class Show extends Component
{
    public AssessmentEvent $event;

    public function mount(AssessmentEvent $event): void
    {
        $this->event = $event->load([
            'institution.categories',
            'batches',
            'participants',
            'positionFormations',
        ]);
    }

    public function render()
    {
        $primaryCategory = $this->event->institution->categories
            ->where('pivot.is_primary', true)
            ->first() ?? $this->event->institution->categories->first();

        return view('livewire.pages.events.show', [
            'primaryCategory' => $primaryCategory,
        ]);
    }
}
