<?php

namespace App\Livewire\Pages\Events;

use App\Models\AssessmentEvent;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app', ['title' => 'Detail Event'])]
class Show extends Component
{
    use WithPagination;

    public AssessmentEvent $event;

    public $expandedFormation = null;

    public $expandedBatch = null;

    public $perPage = 10;

    public function mount(AssessmentEvent $event): void
    {
        $this->event = $event->load([
            'institution.categories',
        ]);
    }

    public function toggleFormation($formationId): void
    {
        if ($this->expandedFormation === $formationId) {
            $this->expandedFormation = null;
        } else {
            $this->expandedFormation = $formationId;
            $this->expandedBatch = null;
            $this->resetPage('formationPage');
        }
    }

    public function toggleBatch($batchId): void
    {
        if ($this->expandedBatch === $batchId) {
            $this->expandedBatch = null;
        } else {
            $this->expandedBatch = $batchId;
            $this->expandedFormation = null;
            $this->resetPage('batchPage');
        }
    }

    public function render()
    {
        $primaryCategory = $this->event->institution->categories
            ->where('pivot.is_primary', true)
            ->first() ?? $this->event->institution->categories->first();

        $positionFormations = $this->event->positionFormations()
            ->withCount('participants')
            ->get();

        $batches = $this->event->batches()
            ->withCount('participants')
            ->get();

        $formationParticipants = null;
        if ($this->expandedFormation) {
            $formationParticipants = $this->event->participants()
                ->where('position_formation_id', $this->expandedFormation)
                ->with(['batch'])
                ->paginate($this->perPage, ['*'], 'formationPage');
        }

        $batchParticipants = null;
        if ($this->expandedBatch) {
            $batchParticipants = $this->event->participants()
                ->where('batch_id', $this->expandedBatch)
                ->with(['positionFormation'])
                ->paginate($this->perPage, ['*'], 'batchPage');
        }

        return view('livewire.pages.events.show', [
            'primaryCategory' => $primaryCategory,
            'positionFormations' => $positionFormations,
            'batches' => $batches,
            'formationParticipants' => $formationParticipants,
            'batchParticipants' => $batchParticipants,
        ]);
    }
}
