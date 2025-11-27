<?php

namespace App\Livewire\Pages\IndividualReport;

use App\Models\Participant;
use App\Services\InterpretationGeneratorService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app', ['title' => '<i>Interpretation</i>'])]
class InterpretationSection extends Component
{
    // Participant info
    public ?Participant $participant = null;

    // Interpretations
    public ?string $potensiInterpretation = null;

    public ?string $kompetensiInterpretation = null;

    // Props from parent
    public $eventCode;

    public $testNumber;

    // Display parameters
    public $showHeader = true;

    public $showPotensi = true;

    public $showKompetensi = true;

    // Standalone flag
    public $isStandalone = true;

    // Cache for interpretations (cleared on standard changes)
    private ?string $potensiInterpretationCache = null;

    private ?string $kompetensiInterpretationCache = null;

    // Event listeners
    protected $listeners = [
        'standard-switched' => 'handleStandardSwitch',
        'standard-adjusted' => 'handleStandardAdjust',
    ];

    public function mount(
        $eventCode,
        $testNumber,
        $showHeader = true,
        $showPotensi = true,
        $showKompetensi = true
    ): void {
        // Use parameters
        $this->eventCode = $eventCode ?? $this->eventCode;
        $this->testNumber = $testNumber ?? $this->testNumber;

        // Set display parameters
        $this->showHeader = $showHeader;
        $this->showPotensi = $showPotensi;
        $this->showKompetensi = $showKompetensi;

        // Determine if standalone
        $this->isStandalone = $eventCode !== null && $testNumber !== null;

        // Validate
        if (! $this->eventCode || ! $this->testNumber) {
            abort(404, 'Event code and test number are required');
        }

        // Load participant
        $this->participant = Participant::with([
            'assessmentEvent',
            'positionFormation.template',
        ])
            ->whereHas('assessmentEvent', function ($query) {
                $query->where('code', $this->eventCode);
            })
            ->where('test_number', $this->testNumber)
            ->firstOrFail();

        // Load or generate interpretations
        $this->loadInterpretations();
    }

    /**
     * Load interpretations - ALWAYS generate on-the-fly
     * Respects custom standard selection and session adjustments
     */
    protected function loadInterpretations(): void
    {
        // Check cache first
        if ($this->potensiInterpretationCache !== null) {
            $this->potensiInterpretation = $this->potensiInterpretationCache;
            $this->kompetensiInterpretation = $this->kompetensiInterpretationCache;

            return;
        }

        // Generate fresh interpretations on-the-fly
        $generator = app(InterpretationGeneratorService::class);
        $results = $generator->generateForDisplay($this->participant);

        // Set to properties based on display flags
        if ($this->showPotensi) {
            $this->potensiInterpretation = $results['potensi'] ?? null;
        }

        if ($this->showKompetensi) {
            $this->kompetensiInterpretation = $results['kompetensi'] ?? null;
        }

        // Cache for this request lifecycle
        $this->potensiInterpretationCache = $this->potensiInterpretation;
        $this->kompetensiInterpretationCache = $this->kompetensiInterpretation;
    }

    /**
     * Clear cache (called when standard changes)
     */
    private function clearCache(): void
    {
        $this->potensiInterpretationCache = null;
        $this->kompetensiInterpretationCache = null;
    }

    /**
     * Regenerate interpretations (for testing/admin purposes)
     */
    public function regenerate(): void
    {
        // Clear cache and reload
        $this->clearCache();
        $this->loadInterpretations();

        $this->dispatch('interpretation-regenerated');
    }

    /**
     * Handle custom standard switch event
     */
    public function handleStandardSwitch(int $templateId): void
    {
        // Validate same template
        if ($this->participant->positionFormation->template_id !== $templateId) {
            return;
        }

        // Clear cache and reload interpretations
        $this->clearCache();
        $this->loadInterpretations();
    }

    /**
     * Handle standard adjustment event (session changes)
     */
    public function handleStandardAdjust(int $templateId): void
    {
        // Reuse same logic as handleStandardSwitch
        $this->handleStandardSwitch($templateId);
    }

    public function render()
    {
        // If standalone, use layout. If child, no layout
        if ($this->isStandalone) {
            return view('livewire.pages.individual-report.interpretation-section')
                ->layout('components.layouts.app', ['title' => 'Interpretation']);
        }

        return view('livewire.pages.individual-report.interpretation-section');
    }
}
