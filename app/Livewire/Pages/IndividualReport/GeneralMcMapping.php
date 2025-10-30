<?php

namespace App\Livewire\Pages\IndividualReport;

use App\Models\Aspect;
use App\Models\AspectAssessment;
use App\Models\CategoryAssessment;
use App\Models\CategoryType;
use App\Models\Participant;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app', ['title' => 'Managerial Competency Mapping'])]
class GeneralMcMapping extends Component
{
    public ?Participant $participant = null;

    public ?CategoryType $kompetensiCategory = null;

    public ?CategoryAssessment $kompetensiAssessment = null;

    public $aspectsData = [];

    public $totalStandardRating = 0;

    public $totalStandardScore = 0;

    public $totalIndividualRating = 0;

    public $totalIndividualScore = 0;

    public $totalGapRating = 0;

    public $totalGapScore = 0;

    public $totalOriginalStandardScore = 0;

    public $totalOriginalGapScore = 0;

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

    // ADD: Public properties untuk support child component
    public $eventCode;

    public $testNumber;

    // Flag untuk menentukan apakah standalone atau child
    public $isStandalone = true;

    // Dynamic display parameters
    public $showHeader = true;

    public $showInfoSection = true;

    public $showTable = true;

    public $showRatingChart = true;

    public $showScoreChart = true;

    public $showRankingInfo = true;

    public function mount($eventCode = null, $testNumber = null, $showHeader = true, $showInfoSection = true, $showTable = true, $showRatingChart = true, $showScoreChart = true, $showRankingInfo = true): void
    {
        // Gunakan parameter jika ada (dari route), atau fallback ke property (dari parent)
        $this->eventCode = $eventCode ?? $this->eventCode;
        $this->testNumber = $testNumber ?? $this->testNumber;

        // Set dynamic display parameters
        $this->showHeader = $showHeader;
        $this->showInfoSection = $showInfoSection;
        $this->showTable = $showTable;
        $this->showRatingChart = $showRatingChart;
        $this->showScoreChart = $showScoreChart;
        $this->showRankingInfo = $showRankingInfo;

        // Tentukan apakah standalone (dari route) atau child (dari parent)
        $this->isStandalone = $eventCode !== null && $testNumber !== null;

        // Validate
        if (! $this->eventCode || ! $this->testNumber) {
            abort(404, 'Event code and test number are required');
        }

        // Generate unique chart ID
        $this->chartId = 'generalMcMapping'.uniqid();

        // Load tolerance from session
        $this->tolerancePercentage = session('individual_report.tolerance', 10);

        // Load participant
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

        $template = $this->participant->positionFormation->template;

        // Get kompetensi category type only
        $this->kompetensiCategory = CategoryType::where('template_id', $template->id)
            ->where('code', 'kompetensi')
            ->first();

        // Get category assessment
        if ($this->kompetensiCategory) {
            $this->kompetensiAssessment = CategoryAssessment::where('participant_id', $this->participant->id)
                ->where('category_type_id', $this->kompetensiCategory->id)
                ->first();
        }

        // Load aspects data
        $this->loadAspectsData();

        // Calculate totals
        $this->calculateTotals();

        // Prepare chart data
        $this->prepareChartData();
    }

    private function loadAspectsData(): void
    {
        $allAspects = [];

        // Load Kompetensi aspects only
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
            // 1. Get original values from database
            $originalStandardRating = (float) $assessment->standard_rating;
            $originalStandardScore = (float) $assessment->standard_score;
            $individualRating = (float) $assessment->individual_rating;
            $individualScore = (float) $assessment->individual_score;

            // 2. Calculate adjusted standard based on tolerance
            $toleranceFactor = 1 - ($this->tolerancePercentage / 100);
            $adjustedStandardRating = $originalStandardRating * $toleranceFactor;
            $adjustedStandardScore = $originalStandardScore * $toleranceFactor;

            // 3. Calculate original gap (at tolerance 0%)
            $originalGapRating = $individualRating - $originalStandardRating;
            $originalGapScore = $individualScore - $originalStandardScore;

            // 4. Calculate adjusted gap (with tolerance)
            $adjustedGapRating = $individualRating - $adjustedStandardRating;
            $adjustedGapScore = $individualScore - $adjustedStandardScore;

            // 5. Calculate percentage based on adjusted standard
            $adjustedPercentage = $adjustedStandardScore > 0
                ? ($individualScore / $adjustedStandardScore) * 100
                : 0;

            return [
                'name' => $assessment->aspect->name,
                'weight_percentage' => $assessment->aspect->weight_percentage,
                'original_standard_rating' => $originalStandardRating,
                'original_standard_score' => $originalStandardScore,
                'standard_rating' => $adjustedStandardRating,
                'standard_score' => $adjustedStandardScore,
                'individual_rating' => $individualRating,
                'individual_score' => $individualScore,
                'gap_rating' => $adjustedGapRating,
                'gap_score' => $adjustedGapScore,
                'original_gap_rating' => $originalGapRating,
                'original_gap_score' => $originalGapScore,
                'percentage_score' => $adjustedPercentage,
                'conclusion_text' => $this->getConclusionText($originalGapScore, $adjustedGapScore),
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
        $this->totalOriginalStandardScore = 0;
        $this->totalOriginalGapScore = 0;

        foreach ($this->aspectsData as $aspect) {
            $this->totalStandardRating += $aspect['standard_rating'];
            $this->totalStandardScore += $aspect['standard_score'];
            $this->totalIndividualRating += $aspect['individual_rating'];
            $this->totalIndividualScore += $aspect['individual_score'];
            $this->totalGapRating += $aspect['gap_rating'];
            $this->totalGapScore += $aspect['gap_score'];
            $this->totalOriginalStandardScore += $aspect['original_standard_score'];
            $this->totalOriginalGapScore += $aspect['original_gap_score'];
        }

        // Determine overall conclusion based on gap-based logic
        $this->overallConclusion = $this->getOverallConclusion($this->totalOriginalGapScore, $this->totalGapScore);
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

    private function getConclusionText(float $originalGap, float $adjustedGap): string
    {
        if ($originalGap >= 0) {
            return 'Di Atas Standar';
        } elseif ($adjustedGap >= 0) {
            return 'Memenuhi Standar';
        } else {
            return 'Di Bawah Standar';
        }
    }

    private function getOverallConclusion(float $totalOriginalGapScore, float $totalAdjustedGapScore): string
    {
        if ($totalOriginalGapScore >= 0) {
            return 'Di Atas Standar';
        } elseif ($totalAdjustedGapScore >= 0) {
            return 'Memenuhi Standar';
        } else {
            return 'Di Bawah Standar';
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
            // Count as passing if conclusion text is "Di Atas Standar" or "Memenuhi Standar"
            if (
                $aspect['conclusion_text'] === 'Di Atas Standar' ||
                $aspect['conclusion_text'] === 'Memenuhi Standar'
            ) {
                $passingAspects++;
            }
        }

        return [
            'total' => $totalAspects,
            'passing' => $passingAspects,
            'percentage' => $totalAspects > 0 ? round(($passingAspects / $totalAspects) * 100) : 0,
        ];
    }

    /**
     * Get participant ranking information in Kompetensi category
     */
    public function getParticipantRanking(): ?array
    {
        if (! $this->participant || ! $this->kompetensiCategory) {
            return null;
        }

        $event = $this->participant->assessmentEvent;
        $positionFormationId = $this->participant->position_formation_id;

        if (! $event) {
            return null;
        }

        // Get all aspect IDs for Kompetensi category
        $kompetensiAspectIds = Aspect::query()
            ->where('category_type_id', $this->kompetensiCategory->id)
            ->orderBy('order')
            ->pluck('id')
            ->all();

        if (empty($kompetensiAspectIds)) {
            return null;
        }

        // Get all participants with their scores for the same event and position
        $allParticipants = AspectAssessment::query()
            ->selectRaw('participant_id, SUM(standard_score) as sum_original_standard_score, SUM(individual_rating) as sum_individual_rating, SUM(individual_score) as sum_individual_score')
            ->where('event_id', $event->id)
            ->where('position_formation_id', $positionFormationId)
            ->whereIn('aspect_id', $kompetensiAspectIds)
            ->groupBy('participant_id')
            ->orderByDesc('sum_individual_score')
            ->orderByDesc('sum_individual_rating')
            ->orderBy('participant_id')
            ->get();

        $totalParticipants = $allParticipants->count();

        // Find current participant's rank and calculate conclusion
        $rank = null;
        $conclusion = null;
        foreach ($allParticipants as $index => $participant) {
            if ($participant->participant_id === $this->participant->id) {
                $rank = $index + 1;

                // Calculate conclusion using gap-based logic
                $originalStandardScore = (float) $participant->sum_original_standard_score;
                $individualScore = (float) $participant->sum_individual_score;

                // Calculate adjusted standard based on tolerance
                $toleranceFactor = 1 - ($this->tolerancePercentage / 100);
                $adjustedStandardScore = $originalStandardScore * $toleranceFactor;

                // Calculate gaps
                $originalGap = $individualScore - $originalStandardScore;
                $adjustedGap = $individualScore - $adjustedStandardScore;

                // Determine conclusion using gap-based logic (no threshold)
                if ($originalGap >= 0) {
                    $conclusion = 'Di Atas Standar';
                } elseif ($adjustedGap >= 0) {
                    $conclusion = 'Memenuhi Standar';
                } else {
                    $conclusion = 'Di Bawah Standar';
                }

                break;
            }
        }

        if ($rank === null) {
            return null;
        }

        return [
            'rank' => $rank,
            'total' => $totalParticipants,
            'conclusion' => $conclusion,
        ];
    }

    public function render()
    {
        return view('livewire.pages.individual-report.general-mc-mapping');
    }
}
