<?php

namespace App\Livewire\Components;

use App\Models\Aspect;
use App\Models\AssessmentEvent;
use App\Models\CategoryType;
use Livewire\Component;

class AspectSelector extends Component
{
    public ?int $aspectId = null;

    /** @var array<int, array{id: int, name: string, category: string}> */
    public array $availableAspects = [];

    public bool $showLabel = true;

    protected $listeners = [
        'event-selected' => 'handleEventSelected',
        'position-selected' => 'handlePositionSelected',
    ];

    /**
     * Mount component and load aspect from session
     */
    public function mount(?bool $showLabel = null): void
    {
        // Load available aspects based on current event and position
        $this->loadAvailableAspects();

        // Load aspect from session or use first available
        $sessionAspectId = session('filter.aspect_id');

        if ($sessionAspectId && $this->isValidAspectId($sessionAspectId)) {
            $this->aspectId = $sessionAspectId;
        } else {
            $this->aspectId = $this->availableAspects[0]['id'] ?? null;

            // Save to session if we have a default
            if ($this->aspectId) {
                session(['filter.aspect_id' => $this->aspectId]);
            }
        }

        // Optionally control whether to show label from parent
        if ($showLabel !== null) {
            $this->showLabel = $showLabel;
        }

        $this->dispatch('aspect-selected', aspectId: $this->aspectId);
    }

    /**
     * Handle event selection - reload aspects
     */
    public function handleEventSelected(?string $eventCode): void
    {
        // Position will auto-reset, so we wait for position-selected event
    }

    /**
     * Handle position selection - reload aspects
     */
    public function handlePositionSelected(?int $positionFormationId): void
    {
        $previousAspectId = $this->aspectId;

        // Reload aspects for new position
        $this->loadAvailableAspects();

        // Preserve aspect if still available; otherwise choose first
        if ($previousAspectId && $this->isValidAspectId($previousAspectId)) {
            $this->aspectId = $previousAspectId;
        } else {
            $this->aspectId = $this->availableAspects[0]['id'] ?? null;
        }

        // Update session
        if ($this->aspectId) {
            session(['filter.aspect_id' => $this->aspectId]);
        } else {
            session()->forget('filter.aspect_id');
        }

        // Dispatch event to parent
        $this->dispatch('aspect-selected', aspectId: $this->aspectId);
    }

    /**
     * Update aspect and persist to session
     */
    public function updatedAspectId(?int $value): void
    {
        // Validate aspect ID is in available aspects
        if ($value && ! $this->isValidAspectId($value)) {
            $this->aspectId = null;
            session()->forget('filter.aspect_id');
            $this->dispatch('aspect-selected', aspectId: null);

            return;
        }

        // Persist to session
        if ($value) {
            session(['filter.aspect_id' => $value]);
        } else {
            session()->forget('filter.aspect_id');
        }

        // Dispatch event to parent component
        $this->dispatch('aspect-selected', aspectId: $value);
    }

    /**
     * Load available aspects based on event and position from session
     */
    private function loadAvailableAspects(): void
    {
        $this->availableAspects = [];

        $eventCode = session('filter.event_code');
        $positionFormationId = session('filter.position_formation_id');

        if (! $eventCode || ! $positionFormationId) {
            return;
        }

        $event = AssessmentEvent::query()->where('code', $eventCode)->first();
        if (! $event) {
            return;
        }

        // Get selected position with template
        $position = $event->positionFormations()
            ->with('template')
            ->find($positionFormationId);

        if (! $position || ! $position->template) {
            return;
        }

        // Get category types from selected position's template
        $categoryTypes = CategoryType::query()
            ->where('template_id', $position->template_id)
            ->get(['id', 'code']);

        $categoryIdToCode = $categoryTypes->pluck('code', 'id');

        // Get aspects from selected position's template
        $aspects = Aspect::query()
            ->join('category_types', 'aspects.category_type_id', '=', 'category_types.id')
            ->where('aspects.template_id', $position->template_id)
            ->orderByRaw("CASE WHEN LOWER(category_types.code) = 'potensi' THEN 0 ELSE 1 END")
            ->orderBy('aspects.order')
            ->get(['aspects.id', 'aspects.name', 'aspects.category_type_id']);

        $this->availableAspects = $aspects->map(function ($a) use ($categoryIdToCode) {
            return [
                'id' => (int) $a->id,
                'name' => (string) $a->name,
                'category' => (string) ($categoryIdToCode[$a->category_type_id] ?? ''),
            ];
        })->all();
    }

    /**
     * Check if aspect ID is valid
     */
    private function isValidAspectId(int $id): bool
    {
        return collect($this->availableAspects)->contains('id', $id);
    }

    public function render()
    {
        return view('livewire.components.aspect-selector');
    }
}
