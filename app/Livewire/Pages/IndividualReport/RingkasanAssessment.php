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

    public function mount($eventCode, $testNumber): void
    {
        // Load tolerance from session (same as GeneralPsyMapping)
        $this->tolerancePercentage = session('individual_report.tolerance', 10);

        // Load participant with relations based on event code and test number
        $this->participant = Participant::with([
            'assessmentEvent.template',
            'batch',
            'positionFormation',
        ])
            ->whereHas('assessmentEvent', function ($query) use ($eventCode) {
                $query->where('code', $eventCode);
            })
            ->where('test_number', $testNumber)
            ->firstOrFail();

        // Get template from event
        $template = $this->participant->assessmentEvent->template;

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

        // R17 = Gap Score (Potensi)
        $gapScore = $this->potensiAssessment->gap_score;

        // N17 = Standard Score (Potensi)
        $standardScore = $this->potensiAssessment->total_standard_score;

        // Tolerance (e.g., 10% = 0.1)
        $tolerance = $this->tolerancePercentage / 100;

        // Check if not participating: R17 = N17-(N17*2) atau R17 = -N17
        if ($gapScore == -$standardScore) {
            return 'Tidak Ikut Assessment';
        }

        // R17 > 0: Di Atas Standar
        if ($gapScore > 0) {
            return 'Di Atas Standar';
        }

        // R17 >= (N17*(1-tolerance) - N17): Memenuhi Standar
        // Simplified: R17 >= -N17*tolerance
        $minGapForStandard = -($standardScore * $tolerance);

        if ($gapScore >= $minGapForStandard) {
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

        // R18 = Gap Score (Kompetensi)
        $gapScore = $this->kompetensiAssessment->gap_score;

        // N18 = Standard Score (Kompetensi)
        $standardScore = $this->kompetensiAssessment->total_standard_score;

        // Tolerance (e.g., 10% = 0.1)
        $tolerance = $this->tolerancePercentage / 100;

        // Check if not participating: R18 = N18-(N18*2) atau R18 = -N18
        if ($gapScore == -$standardScore) {
            return 'Tidak Ikut Assessment';
        }

        // R18 > 0: Sangat Kompeten
        if ($gapScore > 0) {
            return 'Sangat Kompeten';
        }

        // R18 >= (N18*(1-tolerance) - N18): Kompeten
        // Simplified: R18 >= -N18*tolerance
        $minGapForStandard = -($standardScore * $tolerance);

        if ($gapScore >= $minGapForStandard) {
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

        // R20 = Total Gap Score
        $totalGapScore = $this->finalAssessment->total_individual_score - $this->finalAssessment->total_standard_score;

        // N20 = Total Standard Score
        $totalStandardScore = $this->finalAssessment->total_standard_score;

        // Tolerance (e.g., 10% = 0.1)
        $tolerance = $this->tolerancePercentage / 100;

        // Check if not participating: R20 = N20-(N20*2) atau R20 = -N20
        if ($totalGapScore == -$totalStandardScore) {
            return 'Tidak Ikut Assessment';
        }

        // R20 > 0: Di Atas Standar
        if ($totalGapScore > 0) {
            return 'Di Atas Standar';
        }

        // R20 >= (N20*(1-tolerance) - N20): Memenuhi Standar
        // Simplified: R20 >= -N20*tolerance
        $minGapForStandard = -($totalStandardScore * $tolerance);

        if ($totalGapScore >= $minGapForStandard) {
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
