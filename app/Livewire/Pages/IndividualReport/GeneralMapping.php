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

    public $chartOriginalStandardRatings = [];

    public $chartStandardRatings = [];

    public $chartIndividualRatings = [];

    public $chartOriginalStandardScores = [];

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
            'assessmentEvent',
            'batch',
            'positionFormation.template',
        ])
            ->whereHas('assessmentEvent', function ($query) use ($eventCode) {
                $query->where('code', $eventCode);
            })
            ->where('test_number', $testNumber)
            ->firstOrFail();

        $template = $this->participant->positionFormation->template;

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
            // Original values from database
            $originalStandardRating = (float) $assessment->standard_rating;
            $originalStandardScore = (float) $assessment->standard_score;
            $individualRating = (float) $assessment->individual_rating;
            $individualScore = (float) $assessment->individual_score;

            // Calculate adjusted standard based on tolerance
            $toleranceFactor = 1 - ($this->tolerancePercentage / 100);
            $adjustedStandardRating = $originalStandardRating * $toleranceFactor;
            $adjustedStandardScore = $originalStandardScore * $toleranceFactor;

            // Recalculate gap based on adjusted standard
            $adjustedGapRating = $individualRating - $adjustedStandardRating;
            $adjustedGapScore = $individualScore - $adjustedStandardScore;

            // Calculate percentage based on adjusted standard
            $adjustedPercentage = $adjustedStandardScore > 0
                ? ($individualScore / $adjustedStandardScore) * 100
                : 0;

            return [
                'name' => $assessment->aspect->name,
                'order' => $assessment->aspect->order,
                'weight_percentage' => $assessment->aspect->weight_percentage,
                'original_standard_rating' => $originalStandardRating,
                'original_standard_score' => $originalStandardScore,
                'standard_rating' => $adjustedStandardRating,
                'standard_score' => $adjustedStandardScore,
                'individual_rating' => $individualRating,
                'individual_score' => $individualScore,
                'gap_rating' => $adjustedGapRating,
                'gap_score' => $adjustedGapScore,
                'percentage_score' => $adjustedPercentage,
                'conclusion_text' => $this->getConclusionText($adjustedPercentage),
            ];
        })->toArray();
    }

    private function calculateTotals(): void
    {
        // Reset totals before recalculating
        $this->totalStandardRating = 0;
        $this->totalStandardScore = 0;
        $this->totalIndividualRating = 0;
        $this->totalIndividualScore = 0;
        $this->totalGapRating = 0;
        $this->totalGapScore = 0;

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
        // Reset chart data arrays before repopulating
        $this->chartLabels = [];
        $this->chartOriginalStandardRatings = [];
        $this->chartStandardRatings = [];
        $this->chartIndividualRatings = [];
        $this->chartOriginalStandardScores = [];
        $this->chartStandardScores = [];
        $this->chartIndividualScores = [];

        foreach ($this->aspectsData as $aspect) {
            $this->chartLabels[] = $aspect['name'];
            $this->chartOriginalStandardRatings[] = round($aspect['original_standard_rating'], 2);
            $this->chartStandardRatings[] = round($aspect['standard_rating'], 2);
            $this->chartIndividualRatings[] = round($aspect['individual_rating'], 2);
            $this->chartOriginalStandardScores[] = round($aspect['original_standard_score'], 2);
            $this->chartStandardScores[] = round($aspect['standard_score'], 2);
            $this->chartIndividualScores[] = round($aspect['individual_score'], 2);
        }
    }

    private function getConclusionText(float $percentageScore): string
    {
        // Conclusion based on percentage score relative to adjusted standard
        if ($percentageScore >= 110) {
            return 'Lebih Memenuhi/More Requirement';
        } elseif ($percentageScore >= 100) {
            return 'Memenuhi/Meet Requirement';
        } elseif ($percentageScore >= 90) {
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

        // Get updated summary statistics
        $summary = $this->getPassingSummary();

        // Dispatch event to update charts
        $this->dispatch('chartDataUpdated', [
            'tolerance' => $tolerance,
            'labels' => $this->chartLabels,
            'originalStandardRatings' => $this->chartOriginalStandardRatings,
            'standardRatings' => $this->chartStandardRatings,
            'individualRatings' => $this->chartIndividualRatings,
            'originalStandardScores' => $this->chartOriginalStandardScores,
            'standardScores' => $this->chartStandardScores,
            'individualScores' => $this->chartIndividualScores,
        ]);

        // Dispatch event to update summary statistics in ToleranceSelector
        $this->dispatch('summary-updated', [
            'passing' => $summary['passing'],
            'total' => $summary['total'],
            'percentage' => $summary['percentage'],
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
            // Count as passing if conclusion text is "Memenuhi" or "Lebih Memenuhi"
            // Exclude "Belum Memenuhi" and "Kurang Memenuhi"
            if ($aspect['conclusion_text'] === 'Memenuhi/Meet Requirement' ||
                $aspect['conclusion_text'] === 'Lebih Memenuhi/More Requirement') {
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
