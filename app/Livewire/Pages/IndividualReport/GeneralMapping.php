<?php

namespace App\Livewire\Pages\IndividualReport;

use App\Models\Aspect;
use App\Models\AspectAssessment;
use App\Models\CategoryAssessment;
use App\Models\CategoryType;
use App\Models\Participant;
use Illuminate\Support\Facades\DB;
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

    // Dynamic display parameters
    public $showHeader = true;

    public $showInfoSection = true;

    public $showTable = true;

    public $showRatingChart = true;

    public $showScoreChart = true;

    public $showRankingInfo = true;

    public function mount($eventCode = null, $testNumber = null, $showHeader = true, $showInfoSection = true, $showTable = true, $showRatingChart = true, $showScoreChart = true, $showRankingInfo = true): void
    {
        // Set dynamic display parameters
        $this->showHeader = $showHeader;
        $this->showInfoSection = $showInfoSection;
        $this->showTable = $showTable;
        $this->showRatingChart = $showRatingChart;
        $this->showScoreChart = $showScoreChart;
        $this->showRankingInfo = $showRankingInfo;

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

            // Calculate original gap (at tolerance 0)
            $originalGapRating = $individualRating - $originalStandardRating;
            $originalGapScore = $individualScore - $originalStandardScore;

            // Calculate adjusted gap (with tolerance)
            $adjustedGapRating = $individualRating - $adjustedStandardRating;
            $adjustedGapScore = $individualScore - $adjustedStandardScore;

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
                'original_gap_score' => $originalGapScore,
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

    private function getConclusionText(float $originalGapScore, float $adjustedGapScore): string
    {
        if ($originalGapScore >= 0) {
            return 'Di Atas Standar';
        } elseif ($adjustedGapScore >= 0) {
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
            // Count as passing if conclusion is "Di Atas Standar" or "Memenuhi Standar"
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
     * Get participant ranking information for combined Potensi + Kompetensi weighted score
     */
    public function getParticipantRanking(): ?array
    {
        if (! $this->participant || ! $this->potensiCategory || ! $this->kompetensiCategory) {
            return null;
        }

        $event = $this->participant->assessmentEvent;
        $positionFormationId = $this->participant->position_formation_id;

        if (! $event) {
            return null;
        }

        // Get category weights
        $potensiWeight = (int) ($this->potensiCategory->weight_percentage ?? 0);
        $kompetensiWeight = (int) ($this->kompetensiCategory->weight_percentage ?? 0);

        if (($potensiWeight + $kompetensiWeight) === 0) {
            return null;
        }

        $potensiId = (int) $this->potensiCategory->id;
        $kompetensiId = (int) $this->kompetensiCategory->id;

        // Get all participants with their scores using same logic as RekapRankingAssessment
        $baseQuery = DB::table('aspect_assessments as aa')
            ->join('aspects as a', 'a.id', '=', 'aa.aspect_id')
            ->where('aa.event_id', $event->id)
            ->where('aa.position_formation_id', $positionFormationId)
            ->groupBy('aa.participant_id')
            ->selectRaw('aa.participant_id as participant_id')
            ->selectRaw('SUM(CASE WHEN a.category_type_id = ? THEN aa.individual_score ELSE 0 END) as potensi_individual_score', [$potensiId])
            ->selectRaw('SUM(CASE WHEN a.category_type_id = ? THEN aa.standard_score ELSE 0 END) as potensi_standard_score', [$potensiId])
            ->selectRaw('SUM(CASE WHEN a.category_type_id = ? THEN aa.individual_score ELSE 0 END) as kompetensi_individual_score', [$kompetensiId])
            ->selectRaw('SUM(CASE WHEN a.category_type_id = ? THEN aa.standard_score ELSE 0 END) as kompetensi_standard_score', [$kompetensiId]);

        // Order by total weighted individual score
        $allParticipants = DB::query()->fromSub($baseQuery, 't')
            ->select('*')
            ->selectRaw('? * potensi_individual_score / 100 + ? * kompetensi_individual_score / 100 as total_weighted_individual', [$potensiWeight, $kompetensiWeight])
            ->orderByDesc('total_weighted_individual')
            ->orderBy('participant_id')
            ->get();

        $totalParticipants = $allParticipants->count();

        // Find current participant's rank and calculate conclusion
        $rank = null;
        $conclusion = null;
        foreach ($allParticipants as $index => $row) {
            if ($row->participant_id === $this->participant->id) {
                $rank = $index + 1;

                // Calculate conclusion using same logic as RekapRankingAssessment
                $potInd = (float) $row->potensi_individual_score;
                $potStd = (float) $row->potensi_standard_score;
                $komInd = (float) $row->kompetensi_individual_score;
                $komStd = (float) $row->kompetensi_standard_score;

                // Calculate adjusted standard based on tolerance
                $toleranceFactor = 1 - ($this->tolerancePercentage / 100);
                $adjustedPotStd = $potStd * $toleranceFactor;
                $adjustedKomStd = $komStd * $toleranceFactor;

                // Weighted calculations
                $weightedPot = $potInd * ($potensiWeight / 100);
                $weightedKom = $komInd * ($kompetensiWeight / 100);
                $totalWeightedInd = $weightedPot + $weightedKom;

                $weightedPotStd = $adjustedPotStd * ($potensiWeight / 100);
                $weightedKomStd = $adjustedKomStd * ($kompetensiWeight / 100);
                $totalWeightedStd = $weightedPotStd + $weightedKomStd;

                // Calculate original gap (at tolerance 0)
                $originalWeightedStd = $potStd * ($potensiWeight / 100) + $komStd * ($kompetensiWeight / 100);
                $originalGap = $totalWeightedInd - $originalWeightedStd;

                // Calculate adjusted gap
                $adjustedGap = $totalWeightedInd - $totalWeightedStd;

                // Determine conclusion using same logic as RekapRankingAssessment
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
            'potensi_weight' => $potensiWeight,
            'kompetensi_weight' => $kompetensiWeight,
        ];
    }

    public function render()
    {
        return view('livewire.pages.individual-report.general-mapping');
    }
}
