<?php

namespace App\Livewire\Pages\IndividualReport;

use App\Models\AssessmentEvent;
use App\Models\CategoryAssessment;
use App\Models\CategoryType;
use App\Models\FinalAssessment;
use App\Models\Participant;
use App\Models\PsychologicalTest;
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
     * Listen for tolerance updates from ToleranceSelector component
     */
    #[On('tolerance-updated')]
    public function handleToleranceUpdate($tolerance): void
    {
        $this->tolerancePercentage = $tolerance;

        // Redirect to reload the page with new tolerance
        $this->redirect(route('final_report', [
            'eventCode' => $this->eventCode,
            'testNumber' => $this->testNumber,
        ]), navigate: true);
    }

    /**
     * Get adjusted standard score for a category
     */
    private function getAdjustedStandardScore(float $originalStandardScore): float
    {
        $toleranceFactor = 1 - ($this->tolerancePercentage / 100);

        return $originalStandardScore * $toleranceFactor;
    }

    /**
     * Get adjusted gap score
     */
    private function getAdjustedGap(float $individualScore, float $originalStandardScore): float
    {
        $adjustedStandard = $this->getAdjustedStandardScore($originalStandardScore);

        return $individualScore - $adjustedStandard;
    }

    /**
     * Get final conclusion text based on tolerance
     * Maps table conclusion to final "Potensial" labels
     */
    public function getFinalConclusionText(): string
    {
        if (! $this->finalAssessment) {
            return 'Tidak Ikut Assessment';
        }

        // Get original values
        $totalIndividualScore = (float) $this->finalAssessment->total_individual_score;
        $originalTotalStandardScore = (float) $this->finalAssessment->total_standard_score;
        $originalTotalGapScore = $totalIndividualScore - $originalTotalStandardScore;

        // Check if not participating: original gap = -standard (individual is 0)
        if ($originalTotalGapScore == -$originalTotalStandardScore) {
            return 'Tidak Ikut Assessment';
        }

        // Calculate adjusted gap based on tolerance
        $adjustedTotalGap = $this->getAdjustedGap($totalIndividualScore, $originalTotalStandardScore);

        // Determine table conclusion first (3 categories)
        if ($originalTotalGapScore > 0) {
            $tableConclusion = 'Di Atas Standar';
        } elseif ($adjustedTotalGap >= 0) {
            $tableConclusion = 'Memenuhi Standar';
        } else {
            $tableConclusion = 'Di Bawah Standar';
        }

        // Map table conclusion to final "Potensial" labels (same as RingkasanAssessment)
        return match ($tableConclusion) {
            'Di Atas Standar' => 'Sangat Potensial',
            'Memenuhi Standar' => 'Potensial',
            'Di Bawah Standar' => 'Kurang Potensial',
            default => 'Kurang Potensial',
        };
    }

    /**
     * Get final conclusion color class
     */
    public function getFinalConclusionColorClass(): string
    {
        $finalConclusion = $this->getFinalConclusionText();

        return match ($finalConclusion) {
            'Sangat Potensial' => 'bg-green-600 text-white',
            'Potensial' => 'bg-yellow-400 text-gray-900',
            'Kurang Potensial' => 'bg-red-600 text-white',
            'Tidak Ikut Assessment' => 'bg-gray-300 text-black',
            default => 'bg-gray-300 text-black',
        };
    }

    public function render()
    {
        return view('livewire.pages.individual-report.final-report');
    }
}
