<?php

namespace App\Livewire\Components;

use App\Models\AssessmentEvent;
use Livewire\Component;

class EventSelector extends Component
{
    public ?string $eventCode = null;

    /** @var array<int, array{code:string,name:string}> */
    public array $availableEvents = [];

    public bool $showLabel = true;

    /**
     * Mount component and load event from session
     */
    public function mount(?bool $showLabel = null): void
    {
        // Load available events
        $this->availableEvents = AssessmentEvent::query()
            ->orderByDesc('start_date')
            ->get(['code', 'name'])
            ->map(fn ($e) => ['code' => $e->code, 'name' => $e->name])
            ->all();

        // Load event from session or use first available
        $sessionEventCode = session('filter.event_code');

        if ($sessionEventCode && $this->isValidEventCode($sessionEventCode)) {
            $this->eventCode = $sessionEventCode;
        } else {
            $this->eventCode = $this->availableEvents[0]['code'] ?? null;

            // Save to session if we have a default
            if ($this->eventCode) {
                session(['filter.event_code' => $this->eventCode]);
            }
        }

        // Optionally control whether to show label from parent
        if ($showLabel !== null) {
            $this->showLabel = $showLabel;
        }
    }

    /**
     * Update event code and persist to session
     */
    public function updatedEventCode(?string $value): void
    {
        // Persist to session
        if ($value) {
            session(['filter.event_code' => $value]);
        } else {
            session()->forget('filter.event_code');
        }

        // Dispatch event to parent component
        $this->dispatch('event-selected', eventCode: $value);
    }

    /**
     * Check if event code is valid
     */
    private function isValidEventCode(string $code): bool
    {
        return collect($this->availableEvents)->contains('code', $code);
    }

    public function render()
    {
        return view('livewire.components.event-selector');
    }
}
