<?php

namespace App\Livewire\Pages\IndividualReport;

use App\Models\Aspect;
use App\Models\AspectAssessment;
use App\Models\CategoryAssessment;
use App\Models\CategoryType;
use App\Models\Participant;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app', ['title' => 'General Mapping'])]
class GeneralMapping extends Component
{
    public ?Participant $participant = null;

    public ?CategoryType $potensiCategory = null;

    public ?CategoryType $kompetensiCategory = null;

    public ?CategoryAssessment $potensiAssessment = null;

    public ?CategoryAssessment $kompetensiAssessment = null;

    public $aspectsData = [];

    public $totalStandardRating = 0;

    public $totalStandardScore = 0;

    public $totalIndividualRating = 0;

    public $totalIndividualScore = 0;

    public $totalGapRating = 0;

    public $totalGapScore = 0;

    public $overallConclusion = '';

    // Data for charts
    public $chartLabels = [];

    public $chartStandardRatings = [];

    public $chartIndividualRatings = [];

    public $chartStandardScores = [];

    public $chartIndividualScores = [];

    public function mount($eventCode, $testNumber): void
    {
        // Load participant
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

        $template = $this->participant->assessmentEvent->template;

        // Get category types
        $this->potensiCategory = CategoryType::where('template_id', $template->id)
            ->where('code', 'potensi')
            ->first();

        $this->kompetensiCategory = CategoryType::where('template_id', $template->id)
            ->where('code', 'kompetensi')
            ->first();

        // Get category assessments
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

        // Load all aspects data
        $this->loadAspectsData();

        // Calculate totals
        $this->calculateTotals();

        // Prepare chart data
        $this->prepareChartData();
    }

    private function loadAspectsData(): void
    {
        $allAspects = [];

        // Load Potensi aspects
        if ($this->potensiCategory) {
            $potensiAspects = $this->loadCategoryAspects($this->potensiCategory->id);
            $allAspects = array_merge($allAspects, $potensiAspects);
        }

        // Load Kompetensi aspects
        if ($this->kompetensiCategory) {
            $kompetensiAspects = $this->loadCategoryAspects($this->kompetensiCategory->id);
            $allAspects = array_merge($allAspects, $kompetensiAspects);
        }

        $this->aspectsData = $allAspects;
    }

    private function loadCategoryAspects(int $categoryTypeId): array
    {
        $aspectIds = Aspect::where('category_type_id', $categoryTypeId)
            ->orderBy('order')
            ->pluck('id')
            ->toArray();

        $aspectAssessments = AspectAssessment::with('aspect')
            ->where('participant_id', $this->participant->id)
            ->whereIn('aspect_id', $aspectIds)
            ->orderBy('aspect_id')
            ->get();

        return $aspectAssessments->map(function ($assessment) {
            return [
                'name' => $assessment->aspect->name,
                'weight_percentage' => $assessment->aspect->weight_percentage,
                'standard_rating' => $assessment->standard_rating,
                'standard_score' => $assessment->standard_score,
                'individual_rating' => $assessment->individual_rating,
                'individual_score' => $assessment->individual_score,
                'gap_rating' => $assessment->gap_rating,
                'gap_score' => $assessment->gap_score,
                'percentage_score' => $assessment->percentage_score,
                'conclusion_text' => $this->getConclusionText($assessment->gap_rating),
            ];
        })->toArray();
    }

    private function calculateTotals(): void
    {
        foreach ($this->aspectsData as $aspect) {
            $this->totalStandardRating += $aspect['standard_rating'];
            $this->totalStandardScore += $aspect['standard_score'];
            $this->totalIndividualRating += $aspect['individual_rating'];
            $this->totalIndividualScore += $aspect['individual_score'];
            $this->totalGapRating += $aspect['gap_rating'];
            $this->totalGapScore += $aspect['gap_score'];
        }

        // Determine overall conclusion based on total gap score
        $this->overallConclusion = $this->getOverallConclusion($this->totalGapScore);
    }

    private function prepareChartData(): void
    {
        foreach ($this->aspectsData as $aspect) {
            $this->chartLabels[] = $aspect['name'];
            $this->chartStandardRatings[] = round($aspect['standard_rating'], 2);
            $this->chartIndividualRatings[] = round($aspect['individual_rating'], 2);
            $this->chartStandardScores[] = round($aspect['standard_score'], 2);
            $this->chartIndividualScores[] = round($aspect['individual_score'], 2);
        }
    }

    private function getConclusionText(float $gapRating): string
    {
        if ($gapRating > 0.5) {
            return 'Lebih Memenuhi/More Requirement';
        } elseif ($gapRating >= -0.5) {
            return 'Memenuhi/Meet Requirement';
        } elseif ($gapRating >= -1.0) {
            return 'Kurang Memenuhi/Below Requirement';
        } else {
            return 'Belum Memenuhi/Under Perform';
        }
    }

    private function getOverallConclusion(float $totalGapScore): string
    {
        if ($totalGapScore >= 0) {
            return 'Memenuhi Standar/Meet Requirement Standard';
        } else {
            return 'Kurang Memenuhi Standar/Below Requirement Standard';
        }
    }

    public function getPercentage(float $individualScore, float $standardScore): int
    {
        if ($standardScore == 0) {
            return 0;
        }

        return round(($individualScore / $standardScore) * 100);
    }

    public function render()
    {
        return view('livewire.pages.individual-report.general-mapping');
    }
}
