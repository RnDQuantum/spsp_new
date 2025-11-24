<?php

namespace App\Livewire\Pages\IndividualReport;

use App\Models\Participant;
use App\Services\ConclusionService;
use App\Services\IndividualAssessmentService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app', ['title' => 'Ringkasan Hasil Asesmen'])]
class RingkasanAssessment extends Component
{
    // Participant info
    public ?Participant $participant = null;

    // Assessment data from service
    public ?array $finalAssessmentData = null;

    public ?array $potensiData = null;

    public ?array $kompetensiData = null;

    // Tolerance percentage (default 10%)
    public int $tolerancePercentage = 10;

    // CACHE: Store service results
    private ?array $finalAssessmentCache = null;

    // Public properties untuk support child component
    public $eventCode;

    public $testNumber;

    // Flag untuk menentukan apakah standalone atau child
    public $isStandalone = true;

    // Dynamic display parameters
    public $showHeader = true;

    public $showBiodata = true;

    public $showInfoSection = true;

    public $showTable = true;

    /**
     * Listen to tolerance updates and standard adjustments
     */
    protected $listeners = [
        'tolerance-updated' => 'handleToleranceUpdate',
        'standard-adjusted' => 'handleStandardUpdate',
        'standard-switched' => 'handleStandardSwitch',
    ];

    public function mount($eventCode = null, $testNumber = null, $showHeader = true, $showBiodata = true, $showInfoSection = true, $showTable = true): void
    {
        // Gunakan parameter jika ada (dari route), atau fallback ke property (dari parent)
        $this->eventCode = $eventCode ?? $this->eventCode;
        $this->testNumber = $testNumber ?? $this->testNumber;

        // Set dynamic display parameters
        $this->showHeader = $showHeader;
        $this->showBiodata = $showBiodata;
        $this->showInfoSection = $showInfoSection;
        $this->showTable = $showTable;

        // Tentukan apakah standalone (dari route) atau child (dari parent)
        $this->isStandalone = $eventCode !== null && $testNumber !== null;

        // Validate
        if (! $this->eventCode || ! $this->testNumber) {
            abort(404, 'Event code and test number are required');
        }

        // Load tolerance from session (same as GeneralPsyMapping)
        $this->tolerancePercentage = session('individual_report.tolerance', 10);

        // Load participant with relations based on event code and test number
        $this->participant = Participant::with([
            'assessmentEvent',
            'batch',
            'positionFormation.template',
        ])
            ->whereHas('assessmentEvent', function ($query) {
                $query->where('code', $this->eventCode);
            })
            ->where('test_number', $this->testNumber)
            ->firstOrFail();

        // Load assessment data from service
        $this->loadFinalAssessment();
    }

    /**
     * Clear all caches
     */
    private function clearCache(): void
    {
        $this->finalAssessmentCache = null;
    }

    /**
     * Load final assessment data from IndividualAssessmentService
     */
    private function loadFinalAssessment(): void
    {
        // OPTIMIZED: Check cache first
        if ($this->finalAssessmentCache !== null) {
            $this->finalAssessmentData = $this->finalAssessmentCache;
            $this->extractCategoryData();

            return;
        }

        // Use IndividualAssessmentService for consistent calculation
        $service = app(IndividualAssessmentService::class);
        $this->finalAssessmentData = $service->getFinalAssessment(
            $this->participant->id,
            $this->tolerancePercentage
        );

        // OPTIMIZED: Cache the result
        $this->finalAssessmentCache = $this->finalAssessmentData;

        // Extract category data for easy access in view
        $this->extractCategoryData();
    }

    /**
     * Extract category data for view
     */
    private function extractCategoryData(): void
    {
        if (! $this->finalAssessmentData) {
            $this->potensiData = null;
            $this->kompetensiData = null;

            return;
        }

        $this->potensiData = $this->finalAssessmentData['potensi'] ?? null;
        $this->kompetensiData = $this->finalAssessmentData['kompetensi'] ?? null;
    }

    /**
     * Handle tolerance update from child component
     */
    public function handleToleranceUpdate(int $tolerance): void
    {
        $this->tolerancePercentage = $tolerance;

        // OPTIMIZED: Clear cache before reloading
        $this->clearCache();

        // Reload data with new tolerance
        $this->loadFinalAssessment();

        // Get updated summary statistics
        $summary = $this->getPassingSummary();

        // Dispatch event to update summary statistics in ToleranceSelector
        $this->dispatch('summary-updated', [
            'passing' => $summary['passing'],
            'total' => $summary['total'],
            'percentage' => $summary['percentage'],
        ]);
    }

    /**
     * Handle standard adjustment from DynamicStandardService
     */
    public function handleStandardUpdate(int $templateId): void
    {
        // Validate same template
        if ($this->participant->positionFormation->template_id !== $templateId) {
            return;
        }

        // Clear cache & reload with adjusted standards
        $this->clearCache();
        $this->loadFinalAssessment();

        // Get updated summary statistics
        $summary = $this->getPassingSummary();

        // Dispatch event to update summary statistics
        $this->dispatch('summary-updated', [
            'passing' => $summary['passing'],
            'total' => $summary['total'],
            'percentage' => $summary['percentage'],
        ]);
    }

    /**
     * PHASE 3: Handle custom standard switch event
     */
    public function handleStandardSwitch(int $templateId): void
    {
        // Reuse the same logic as handleStandardUpdate
        $this->handleStandardUpdate($templateId);
    }

    /**
     * Get summary statistics for ToleranceSelector
     */
    public function getPassingSummary(): array
    {
        $passing = 0;
        $total = 0;

        // Check Potensi
        if ($this->potensiData) {
            $total++;
            $potensiConclusion = $this->potensiData['overall_conclusion'] ?? 'Di Bawah Standar';
            if (in_array($potensiConclusion, ['Di Atas Standar', 'Memenuhi Standar'])) {
                $passing++;
            }
        }

        // Check Kompetensi
        if ($this->kompetensiData) {
            $total++;
            $kompetensiConclusion = $this->kompetensiData['overall_conclusion'] ?? 'Di Bawah Standar';
            if (in_array($kompetensiConclusion, ['Di Atas Standar', 'Memenuhi Standar'])) {
                $passing++;
            }
        }

        return [
            'total' => $total,
            'passing' => $passing,
            'percentage' => $total > 0 ? round(($passing / $total) * 100) : 0,
        ];
    }

    /**
     * Get final conclusion text (Potensial-based mapping)
     * Used in conclusion section below the table
     */
    public function getFinalConclusionText(): string
    {
        if (! $this->finalAssessmentData) {
            return 'Kurang Potensial';
        }

        $gapConclusion = $this->finalAssessmentData['final_conclusion'] ?? 'Di Bawah Standar';

        // Use ConclusionService to map gap-based to potensial-based
        return ConclusionService::getPotensialConclusion($gapConclusion);
    }

    public function render()
    {
        return view('livewire.pages.individual-report.ringkasan-assessment');
    }
}
