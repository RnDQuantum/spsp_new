<?php

namespace App\Livewire\Components;

use Livewire\Component;

class ToleranceSelector extends Component
{
    public int $tolerancePercentage = 10;

    public array $toleranceOptions = [
        0 => '0% (Standard)',
        5 => '5%',
        10 => '10%',
        15 => '15%',
        20 => '20%',
    ];

    public bool $showSummary = true;

    public int $passingCount = 0;

    public int $totalCount = 0;

    /**
     * Listen to summary updates from parent component
     */
    protected $listeners = ['summary-updated' => 'handleSummaryUpdate'];

    /**
     * Mount component and load tolerance from session
     */
    public function mount(?int $passing = null, ?int $total = null): void
    {
        // Load tolerance from session or use default
        $this->tolerancePercentage = session('individual_report.tolerance', 10);

        // Set summary data if provided
        if ($passing !== null && $total !== null) {
            $this->passingCount = $passing;
            $this->totalCount = $total;
        }
    }

    /**
     * Update tolerance and persist to session
     */
    public function updatedTolerancePercentage(int $value): void
    {
        // Persist to session
        session(['individual_report.tolerance' => $value]);

        // Dispatch event to parent component
        $this->dispatch('tolerance-updated', tolerance: $value);
    }

    /**
     * Update summary statistics from parent
     */
    public function updateSummary(int $passing, int $total): void
    {
        $this->passingCount = $passing;
        $this->totalCount = $total;
    }

    /**
     * Handle summary update from parent component
     */
    public function handleSummaryUpdate(array $data): void
    {
        $this->passingCount = $data['passing'];
        $this->totalCount = $data['total'];
    }

    /**
     * Get percentage for summary
     */
    public function getPassingPercentageProperty(): int
    {
        if ($this->totalCount === 0) {
            return 0;
        }

        return round(($this->passingCount / $this->totalCount) * 100);
    }

    public function render()
    {
        return view('livewire.components.tolerance-selector');
    }
}
