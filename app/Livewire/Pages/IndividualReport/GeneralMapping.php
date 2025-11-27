<?php

namespace App\Livewire\Pages\IndividualReport;

use App\Models\CategoryAssessment;
use App\Models\CategoryType;
use App\Models\Participant;
use App\Services\ConclusionService;
use App\Services\IndividualAssessmentService;
use App\Services\RankingService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app', ['title' => '<i>General Mapping</i>'])]
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

    // Conclusion configuration (from ConclusionService)
    public array $conclusionConfig = [];

    // Cache properties
    private ?array $aspectsDataCache = null;

    private ?array $finalAssessmentCache = null;

    private ?array $participantRankingCache = null;

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
        $this->chartId = 'generalMapping' . uniqid();

        // Load tolerance from session
        $this->tolerancePercentage = session('individual_report.tolerance', 10);

        // Load conclusion configuration from ConclusionService
        $this->conclusionConfig = ConclusionService::getGapConclusionConfig();

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

    /**
     * Clear all caches
     */
    private function clearCache(): void
    {
        $this->aspectsDataCache = null;
        $this->finalAssessmentCache = null;
        $this->participantRankingCache = null;
    }

    private function loadAspectsData(): void
    {
        // Check cache first
        if ($this->aspectsDataCache !== null) {
            $this->aspectsData = $this->aspectsDataCache;

            return;
        }

        $service = app(IndividualAssessmentService::class);
        $allAspects = [];

        // Load Potensi aspects
        if ($this->potensiCategory) {
            $potensiAspects = $service->getAspectAssessments(
                $this->participant->id,
                $this->potensiCategory->id,
                $this->tolerancePercentage
            )->toArray();
            $allAspects = array_merge($allAspects, $potensiAspects);
        }

        // Load Kompetensi aspects
        if ($this->kompetensiCategory) {
            $kompetensiAspects = $service->getAspectAssessments(
                $this->participant->id,
                $this->kompetensiCategory->id,
                $this->tolerancePercentage
            )->toArray();
            $allAspects = array_merge($allAspects, $kompetensiAspects);
        }

        $this->aspectsData = $allAspects;
        $this->aspectsDataCache = $allAspects;
    }

    private function calculateTotals(): void
    {
        // Check cache first
        if ($this->finalAssessmentCache !== null) {
            $finalAssessment = $this->finalAssessmentCache;
        } else {
            // Use IndividualAssessmentService to get weighted totals
            $service = app(IndividualAssessmentService::class);
            $finalAssessment = $service->getFinalAssessment(
                $this->participant->id,
                $this->tolerancePercentage
            );
            $this->finalAssessmentCache = $finalAssessment;
        }

        // Use weighted totals from service
        $this->totalStandardScore = $finalAssessment['total_standard_score'];
        $this->totalIndividualScore = $finalAssessment['total_individual_score'];
        $this->totalOriginalStandardScore = $finalAssessment['total_original_standard_score'];
        $this->totalGapScore = $finalAssessment['total_gap_score'];
        $this->totalOriginalGapScore = $finalAssessment['total_original_gap_score'];
        $this->overallConclusion = $finalAssessment['final_conclusion'];

        // Calculate rating totals from aspects data (for display purposes)
        $this->totalStandardRating = 0;
        $this->totalIndividualRating = 0;
        $this->totalGapRating = 0;

        foreach ($this->aspectsData as $aspect) {
            $this->totalStandardRating += $aspect['standard_rating'];
            $this->totalIndividualRating += $aspect['individual_rating'];
            $this->totalGapRating += $aspect['gap_rating'];
        }
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

    public function getPercentage(float $individualScore, float $standardScore): int
    {
        if ($standardScore == 0) {
            return 0;
        }

        return round(($individualScore / $standardScore) * 100);
    }

    /**
     * Listen to tolerance updates and standard adjustments
     */
    protected $listeners = [
        'tolerance-updated' => 'handleToleranceUpdate',
        'standard-adjusted' => 'handleStandardUpdate',
        'standard-switched' => 'handleStandardSwitch',
    ];

    /**
     * Handle tolerance update from child component
     */
    public function handleToleranceUpdate(int $tolerance): void
    {
        $this->tolerancePercentage = $tolerance;

        // Clear cache before reload
        $this->clearCache();

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
     * Handle standard adjustment event
     */
    public function handleStandardUpdate(int $templateId): void
    {
        // Validate same template
        if ($this->participant->positionFormation->template_id !== $templateId) {
            return;
        }

        // Clear cache before reload
        $this->clearCache();

        // Reload aspects data with new adjusted standards
        $this->loadAspectsData();

        // Recalculate totals
        $this->calculateTotals();

        // Update chart data
        $this->prepareChartData();

        // Get updated summary statistics
        $summary = $this->getPassingSummary();

        // Dispatch event to update charts
        $this->dispatch('chartDataUpdated', [
            'tolerance' => $this->tolerancePercentage,
            'labels' => $this->chartLabels,
            'originalStandardRatings' => $this->chartOriginalStandardRatings,
            'standardRatings' => $this->chartStandardRatings,
            'individualRatings' => $this->chartIndividualRatings,
            'originalStandardScores' => $this->chartOriginalStandardScores,
            'standardScores' => $this->chartStandardScores,
            'individualScores' => $this->chartIndividualScores,
        ]);

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
     * REFACTORED: Use RankingService for consistent calculation
     */
    public function getParticipantRanking(): ?array
    {
        // Check cache first
        if ($this->participantRankingCache !== null) {
            return $this->participantRankingCache;
        }

        if (! $this->participant || ! $this->potensiCategory || ! $this->kompetensiCategory) {
            return null;
        }

        $event = $this->participant->assessmentEvent;
        $positionFormationId = $this->participant->position_formation_id;
        $template = $this->participant->positionFormation->template;

        if (! $event || ! $template) {
            return null;
        }

        // Use RankingService for consistent combined ranking calculation
        $rankingService = app(RankingService::class);
        $ranking = $rankingService->getParticipantCombinedRank(
            $this->participant->id,
            $event->id,
            $positionFormationId,
            $template->id,
            $this->tolerancePercentage
        );

        if (! $ranking) {
            return null;
        }

        // Cache the result
        $this->participantRankingCache = $ranking;

        return $ranking;
    }

    public function render()
    {
        return view('livewire.pages.individual-report.general-mapping');
    }
}
