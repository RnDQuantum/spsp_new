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

    // Tolerance percentage (loaded from session)
    public int $tolerancePercentage = 10;

    // Unique chart ID
    public string $chartId = '';

    // Data for charts
    public $chartLabels = [];

    public $chartStandardRatings = [];

    public $chartIndividualRatings = [];

    public $chartStandardScores = [];

    public $chartIndividualScores = [];

    public function mount($eventCode, $testNumber): void
    {
        // Generate unique chart ID
        $this->chartId = 'generalMapping'.uniqid();

        // Load tolerance from session
        $this->tolerancePercentage = session('individual_report.tolerance', 10);

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

        return $aspectAssessments->map(fn ($assessment) => [
            'name' => $assessment->aspect->name,
            'weight_percentage' => $assessment->aspect->weight_percentage,
            'standard_rating' => $assessment->standard_rating,
            'standard_score' => $assessment->standard_score,
            'individual_rating' => $assessment->individual_rating,
            'individual_score' => $assessment->individual_score,
            'gap_rating' => $assessment->gap_rating,
            'gap_score' => $assessment->gap_score,
            'percentage_score' => $assessment->percentage_score,
            'conclusion_text' => $this->getConclusionText(
                (float) $assessment->gap_rating,
                (float) $assessment->standard_rating
            ),
        ])->toArray();
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

    private function getConclusionText(float $gapRating, float $standardRating): string
    {
        // Calculate tolerance threshold based on standard rating
        $toleranceThreshold = -($standardRating * ($this->tolerancePercentage / 100));

        if ($gapRating >= 0) {
            return 'Lebih Memenuhi/More Requirement';
        } elseif ($gapRating >= $toleranceThreshold) {
            return 'Memenuhi/Meet Requirement';
        } elseif ($gapRating >= ($toleranceThreshold * 2)) {
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

        // Reload aspects data with new tolerance
        $this->loadAspectsData();

        // Recalculate totals
        $this->calculateTotals();

        // Update chart data
        $this->prepareChartData();

        // Dispatch event to update charts
        $this->dispatch('chartDataUpdated', [
            'tolerance' => $tolerance,
            'labels' => $this->chartLabels,
            'standardRatings' => $this->chartStandardRatings,
            'individualRatings' => $this->chartIndividualRatings,
            'standardScores' => $this->chartStandardScores,
            'individualScores' => $this->chartIndividualScores,
        ]);
    }

    /**
     * Get summary statistics for passing aspects
     */
    public function getPassingSummary(): array
    {
        $totalAspects = count($this->aspectsData);
        $passingAspects = 0;

        foreach ($this->aspectsData as $aspect) {
            if (str_contains($aspect['conclusion_text'], 'Memenuhi') ||
                str_contains($aspect['conclusion_text'], 'Lebih Memenuhi')) {
                $passingAspects++;
            }
        }

        return [
            'total' => $totalAspects,
            'passing' => $passingAspects,
            'percentage' => $totalAspects > 0 ? round(($passingAspects / $totalAspects) * 100) : 0,
        ];
    }

    public function render()
    {
        return view('livewire.pages.individual-report.general-mapping');
    }
}
