<?php

namespace App\Livewire\Pages\IndividualReport;

use App\Models\AssessmentEvent;
use App\Models\CategoryAssessment;
use App\Models\CategoryType;
use App\Models\FinalAssessment;
use App\Models\Participant;
use App\Models\PsychologicalTest;
use App\Services\ConclusionService;
use App\Services\IndividualAssessmentService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

#[Layout('components.layouts.app', ['title' => 'LAPORAN INDIVIDU'])]
class FinalReport extends Component
{
    public $eventCode;

    public $testNumber;

    public $institutionName;

    public $eventName;

    public $participant;

    public $finalAssessment;

    public $psychologicalTest;

    // Category assessments
    public $potensiAssessment = null;

    public $kompetensiAssessment = null;

    // Category types
    public $potensiCategory = null;

    public $kompetensiCategory = null;

    // Tolerance percentage (default 10%)
    public int $tolerancePercentage = 10;

    // Cache for final assessment data from service
    private ?array $finalAssessmentDataCache = null;

    public function mount($eventCode, $testNumber): void
    {
        $this->eventCode = $eventCode;
        $this->testNumber = $testNumber;

        // Load tolerance from session
        $this->tolerancePercentage = session('individual_report.tolerance', 10);

        // Ambil AssessmentEvent dengan relasi institution berdasarkan eventCode
        $assessmentEvent = AssessmentEvent::with('institution')
            ->where('code', $this->eventCode)
            ->first();

        if ($assessmentEvent) {
            $this->institutionName = $assessmentEvent->institution->name ?? '';
            $this->eventName = $assessmentEvent->name ?? '';
        }

        // Ambil participant dengan relasi
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

        // Load Final Assessment
        $this->finalAssessment = FinalAssessment::where('participant_id', $this->participant->id)->first();

        // Load Psychological Test
        $this->psychologicalTest = PsychologicalTest::where('participant_id', $this->participant->id)->first();

        // Load category assessments for conclusion calculation
        $template = $this->participant->positionFormation->template;

        // Get category types for this template
        $this->potensiCategory = CategoryType::where('template_id', $template->id)
            ->where('code', 'potensi')
            ->first();

        $this->kompetensiCategory = CategoryType::where('template_id', $template->id)
            ->where('code', 'kompetensi')
            ->first();

        // Load category assessments
        if ($this->potensiCategory) {
            $this->potensiAssessment = CategoryAssessment::where('participant_id', $this->participant->id)
                ->where('category_type_id', $this->potensiCategory->id)
                ->first();
        }

        if ($this->kompetensiCategory) {
            $this->kompetensiAssessment = CategoryAssessment::where('participant_id', $this->participant->id)
                ->where('category_type_id', $this->kompetensiCategory->id)
                ->first();
        }
    }

    /**
     * Clear all caches
     */
    private function clearCache(): void
    {
        $this->finalAssessmentDataCache = null;
    }

    /**
     * Load final assessment data from service with caching
     */
    private function loadFinalAssessmentData(): ?array
    {
        // Check cache first
        if ($this->finalAssessmentDataCache !== null) {
            return $this->finalAssessmentDataCache;
        }

        // Call service to get fresh data
        $service = app(IndividualAssessmentService::class);

        try {
            $data = $service->getFinalAssessment(
                $this->participant->id,
                $this->tolerancePercentage
            );

            // Cache the result
            $this->finalAssessmentDataCache = $data;

            return $data;
        } catch (\Exception $e) {
            // If error, return null
            return null;
        }
    }

    /**
     * Listen for tolerance updates from ToleranceSelector component
     */
    #[On('tolerance-updated')]
    public function handleToleranceUpdate($tolerance): void
    {
        $this->tolerancePercentage = $tolerance;

        // Clear cache - Livewire will auto re-render
        $this->clearCache();
    }

    /**
     * Listen for standard adjustments
     */
    #[On('standard-adjusted')]
    public function handleStandardUpdate(int $templateId): void
    {
        // Validate same template
        if ($this->participant->positionFormation->template_id !== $templateId) {
            return;
        }

        // Clear cache - Livewire will auto re-render
        $this->clearCache();
    }

    /**
     * Get final conclusion text based on tolerance
     * Uses IndividualAssessmentService and ConclusionService
     */
    public function getFinalConclusionText(): string
    {
        // Load data from service
        $data = $this->loadFinalAssessmentData();

        // If no data, participant not participating
        if (! $data) {
            return 'Tidak Ikut Assessment';
        }

        // Check if not participating: original gap = -standard (individual is 0)
        $originalGap = $data['total_original_gap_score'] ?? 0;
        $originalStandard = $data['total_original_standard_score'] ?? 0;

        if ($originalGap == -$originalStandard) {
            return 'Tidak Ikut Assessment';
        }

        // Get gap-based conclusion from service data
        $adjustedGap = $data['total_gap_score'] ?? 0;

        // Use ConclusionService to get gap-based conclusion
        $gapConclusion = ConclusionService::getGapBasedConclusion(
            $originalGap,
            $adjustedGap
        );

        // Map to potensial conclusion using ConclusionService
        return ConclusionService::getPotensialConclusion($gapConclusion);
    }

    /**
     * Get final conclusion color class
     * Uses ConclusionService for consistent styling
     */
    public function getFinalConclusionColorClass(): string
    {
        $finalConclusion = $this->getFinalConclusionText();

        // Special case for "Tidak Ikut Assessment"
        if ($finalConclusion === 'Tidak Ikut Assessment') {
            return 'bg-gray-300 text-black';
        }

        // Use ConclusionService for potensial conclusions
        return ConclusionService::getTailwindClass($finalConclusion, 'potensial');
    }

    public function render()
    {
        return view('livewire.pages.individual-report.final-report');
    }
}
