<?php

namespace App\Livewire\Pages\IndividualReport;

use App\Models\CategoryAssessment;
use App\Models\CategoryType;
use App\Models\FinalAssessment;
use App\Models\Participant;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app', ['title' => 'Ringkasan Assessment'])]
class RingkasanAssessment extends Component
{
    // Participant info
    public ?Participant $participant = null;

    public ?FinalAssessment $finalAssessment = null;

    // Category assessments
    public ?CategoryAssessment $potensiAssessment = null;

    public ?CategoryAssessment $kompetensiAssessment = null;

    // Category types for weights
    public ?CategoryType $potensiCategory = null;

    public ?CategoryType $kompetensiCategory = null;

    // Tolerance percentage (default 10%)
    public int $tolerancePercentage = 10;

    // ADD: Public properties untuk support child component
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
        if (!$this->eventCode || !$this->testNumber) {
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

        // Get template from position
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

        // Load final assessment
        $this->finalAssessment = FinalAssessment::where('participant_id', $this->participant->id)->first();
    }

    public function getPotensiConclusion(): string
    {
        if (! $this->potensiAssessment) {
            return 'Tidak Ikut Assessment';
        }

        // Get original values
        $individualScore = $this->potensiAssessment->total_individual_score;
        $originalStandardScore = $this->potensiAssessment->total_standard_score;
        $originalGapScore = $this->potensiAssessment->gap_score;

        // Check if not participating: original gap = -standard (individual is 0)
        if ($originalGapScore == -$originalStandardScore) {
            return 'Tidak Ikut Assessment';
        }

        // Calculate adjusted gap based on tolerance
        $adjustedGap = $this->getAdjustedGap($individualScore, $originalStandardScore);

        // Adjusted gap > 0: Di Atas Standar
        if ($adjustedGap > 0) {
            return 'Di Atas Standar';
        }

        // Adjusted gap >= 0: Memenuhi Standar (within tolerance)
        if ($adjustedGap >= 0) {
            return 'Memenuhi Standar';
        }

        // Otherwise: Di Bawah Standar
        return 'Di Bawah Standar';
    }

    public function getKompetensiConclusion(): string
    {
        if (! $this->kompetensiAssessment) {
            return 'Tidak Ikut Assessment';
        }

        // Get original values
        $individualScore = $this->kompetensiAssessment->total_individual_score;
        $originalStandardScore = $this->kompetensiAssessment->total_standard_score;
        $originalGapScore = $this->kompetensiAssessment->gap_score;

        // Check if not participating: original gap = -standard (individual is 0)
        if ($originalGapScore == -$originalStandardScore) {
            return 'Tidak Ikut Assessment';
        }

        // Calculate adjusted gap based on tolerance
        $adjustedGap = $this->getAdjustedGap($individualScore, $originalStandardScore);

        // Adjusted gap > 0: Sangat Kompeten
        if ($adjustedGap > 0) {
            return 'Sangat Kompeten';
        }

        // Adjusted gap >= 0: Kompeten (within tolerance)
        if ($adjustedGap >= 0) {
            return 'Kompeten';
        }

        // Otherwise: Belum Kompeten
        return 'Belum Kompeten';
    }

    public function getConclusionColorClass(?string $conclusionCode): string
    {
        if (! $conclusionCode) {
            return 'bg-gray-300 text-black';
        }

        return match ($conclusionCode) {
            // Potensi conclusions
            'Di Atas Standar' => 'bg-green-500 text-white',
            'Memenuhi Standar' => 'bg-yellow-400 text-black',
            'Di Bawah Standar' => 'bg-red-600 text-white',
            // Kompetensi conclusions
            'Sangat Kompeten' => 'bg-green-600 text-white',
            'Kompeten' => 'bg-green-500 text-white',
            'Belum Kompeten' => 'bg-red-600 text-white',
            // Default
            'Tidak Ikut Assessment' => 'bg-gray-300 text-black',
            default => 'bg-gray-300 text-black',
        };
    }

    public function getFinalConclusionColorClass(?string $conclusionCode): string
    {
        if (! $conclusionCode) {
            return 'bg-gray-300 text-black';
        }

        return match ($conclusionCode) {
            'TMS' => 'bg-red-600 text-white',
            'MMS' => 'bg-yellow-400 text-black',
            'MS' => 'bg-green-500 text-white',
            default => 'bg-gray-300 text-black',
        };
    }

    public function getTotalConclusionInTable(): string
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

        // Adjusted gap > 0: Di Atas Standar
        if ($adjustedTotalGap > 0) {
            return 'Di Atas Standar';
        }

        // Adjusted gap >= 0: Memenuhi Standar (within tolerance)
        if ($adjustedTotalGap >= 0) {
            return 'Memenuhi Standar';
        }

        // Otherwise: Di Bawah Standar
        return 'Di Bawah Standar';
    }

    public function getTotalConclusionColorClass(): string
    {
        $conclusion = $this->getTotalConclusionInTable();

        return match ($conclusion) {
            'Di Atas Standar' => 'bg-green-500 text-white',
            'Memenuhi Standar' => 'bg-yellow-400 text-black',
            'Di Bawah Standar' => 'bg-red-600 text-white',
            'Tidak Ikut Assessment' => 'bg-gray-300 text-black',
            default => 'bg-gray-300 text-black',
        };
    }

    public function getFinalConclusionText(): string
    {
        // Get individual category conclusions
        $potensiConclusion = $this->getPotensiConclusion();
        $kompetensiConclusion = $this->getKompetensiConclusion();

        // Otherwise, use table conclusion
        $tableConclusion = $this->getTotalConclusionInTable();

        // Based on table conclusion, determine final conclusion
        return match ($tableConclusion) {
            'Tidak Ikut Assessment' => 'Tidak Ikut Assessment',
            'Di Atas Standar' => 'Potensial',
            'Memenuhi Standar' => 'Potensial Dengan Catatan',
            'Di Bawah Standar' => 'Kurang Potensial',
            default => 'Tidak Ikut Assessment',
        };
    }

    public function getFinalConclusionColorClassByGap(): string
    {
        $finalConclusion = $this->getFinalConclusionText();

        return match ($finalConclusion) {
            'Potensial' => 'bg-green-500 text-white',
            'Potensial Dengan Catatan' => 'bg-yellow-400 text-black',
            'Kurang Potensial' => 'bg-red-600 text-white',
            'Tidak Ikut Assessment' => 'bg-gray-300 text-black',
            default => 'bg-gray-300 text-black',
        };
    }

    /**
     * Listen to tolerance updates from ToleranceSelector component
     */
    protected $listeners = ['tolerance-updated' => 'handleToleranceUpdate'];

    /**
     * Handle tolerance update from child component
     */
    public function handleToleranceUpdate(int $tolerance): void
    {
        $this->tolerancePercentage = $tolerance;

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
     * Public methods to get adjusted values for view
     */
    public function getAdjustedPotensiStandardScore(): float
    {
        if (! $this->potensiAssessment) {
            return 0;
        }

        return $this->getAdjustedStandardScore((float) $this->potensiAssessment->total_standard_score);
    }

    public function getAdjustedPotensiGap(): float
    {
        if (! $this->potensiAssessment) {
            return 0;
        }

        return $this->getAdjustedGap(
            (float) $this->potensiAssessment->total_individual_score,
            (float) $this->potensiAssessment->total_standard_score
        );
    }

    public function getAdjustedKompetensiStandardScore(): float
    {
        if (! $this->kompetensiAssessment) {
            return 0;
        }

        return $this->getAdjustedStandardScore((float) $this->kompetensiAssessment->total_standard_score);
    }

    public function getAdjustedKompetensiGap(): float
    {
        if (! $this->kompetensiAssessment) {
            return 0;
        }

        return $this->getAdjustedGap(
            (float) $this->kompetensiAssessment->total_individual_score,
            (float) $this->kompetensiAssessment->total_standard_score
        );
    }

    public function getAdjustedTotalStandardScore(): float
    {
        if (! $this->finalAssessment) {
            return 0;
        }

        return $this->getAdjustedStandardScore((float) $this->finalAssessment->total_standard_score);
    }

    public function getAdjustedTotalGap(): float
    {
        if (! $this->finalAssessment) {
            return 0;
        }

        return $this->getAdjustedGap(
            (float) $this->finalAssessment->total_individual_score,
            (float) $this->finalAssessment->total_standard_score
        );
    }

    /**
     * Get summary statistics for ToleranceSelector
     */
    public function getPassingSummary(): array
    {
        $passing = 0;
        $total = 0;

        // Check Potensi
        if ($this->potensiAssessment) {
            $total++;
            $potensiConclusion = $this->getPotensiConclusion();
            if (in_array($potensiConclusion, ['Di Atas Standar', 'Memenuhi Standar'])) {
                $passing++;
            }
        }

        // Check Kompetensi
        if ($this->kompetensiAssessment) {
            $total++;
            $kompetensiConclusion = $this->getKompetensiConclusion();
            if (in_array($kompetensiConclusion, ['Sangat Kompeten', 'Kompeten'])) {
                $passing++;
            }
        }

        return [
            'total' => $total,
            'passing' => $passing,
            'percentage' => $total > 0 ? round(($passing / $total) * 100) : 0,
        ];
    }

    public function render()
    {
        return view('livewire.pages.individual-report.ringkasan-assessment');
    }
}
