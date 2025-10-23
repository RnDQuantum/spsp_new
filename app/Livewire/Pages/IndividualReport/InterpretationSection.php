<?php

namespace App\Livewire\Pages\IndividualReport;

use App\Models\Participant;
use App\Models\Interpretation;
use App\Services\InterpretationGeneratorService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app', ['title' => 'Interpretation'])]
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
     * Load interpretations from database or generate if not exist
     */
    protected function loadInterpretations(): void
    {
        // Get template & category IDs
        $template = $this->participant->positionFormation->template;

        // Get categories
        $potensiCategory = $template->categoryTypes()
            ->where('code', 'potensi')
            ->first();

        $kompetensiCategory = $template->categoryTypes()
            ->where('code', 'kompetensi')
            ->first();

        // Check if interpretations exist in DB
        $existingInterpretations = Interpretation::where('participant_id', $this->participant->id)
            ->whereIn('category_type_id', [
                $potensiCategory?->id,
                $kompetensiCategory?->id,
            ])
            ->get()
            ->keyBy('category_type_id');

        // If any interpretation is missing, generate all
        $potensiExists = $potensiCategory && $existingInterpretations->has($potensiCategory->id);
        $kompetensiExists = $kompetensiCategory && $existingInterpretations->has($kompetensiCategory->id);

        if (! $potensiExists || ! $kompetensiExists) {
            // Generate all interpretations (both Potensi and Kompetensi)
            $generator = app(InterpretationGeneratorService::class);
            $generator->generateForParticipant($this->participant);

            // Reload from database
            $existingInterpretations = Interpretation::where('participant_id', $this->participant->id)
                ->whereIn('category_type_id', [
                    $potensiCategory?->id,
                    $kompetensiCategory?->id,
                ])
                ->get()
                ->keyBy('category_type_id');
        }

        // Load Potensi interpretation
        if ($this->showPotensi && $potensiCategory) {
            $potensiInterpretation = $existingInterpretations->get($potensiCategory->id);
            $this->potensiInterpretation = $potensiInterpretation?->interpretation_text;
        }

        // Load Kompetensi interpretation
        if ($this->showKompetensi && $kompetensiCategory) {
            $kompetensiInterpretation = $existingInterpretations->get($kompetensiCategory->id);
            $this->kompetensiInterpretation = $kompetensiInterpretation?->interpretation_text;
        }
    }

    /**
     * Regenerate interpretations (for testing/admin purposes)
     */
    public function regenerate(): void
    {
        $generator = app(InterpretationGeneratorService::class);
        $results = $generator->regenerateInterpretations($this->participant);

        $this->potensiInterpretation = $results['potensi'] ?? null;
        $this->kompetensiInterpretation = $results['kompetensi'] ?? null;

        $this->dispatch('interpretation-regenerated');
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
