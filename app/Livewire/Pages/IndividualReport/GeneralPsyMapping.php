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

#[Layout('components.layouts.app', ['title' => '<i>General Psychology Mapping</i>'])]
class GeneralPsyMapping extends Component
{
    public ?Participant $participant = null;

    public ?CategoryType $potensiCategory = null;

    public ?CategoryAssessment $potensiAssessment = null;

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

    // Unique chart ID
    public string $chartId = '';

    // CACHE PROPERTIES - untuk menyimpan hasil kalkulasi
    private ?array $aspectsDataCache = null;

    private ?array $participantRankingCache = null;

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
        $this->chartId = 'generalPsyMapping' . uniqid();

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
            ->whereHas('assessmentEvent', function ($query) {
                $query->where('code', $this->eventCode);
            })
            ->where('test_number', $this->testNumber)
            ->firstOrFail();

        $template = $this->participant->positionFormation->template;

        // Get potensi category type only
        $this->potensiCategory = CategoryType::where('template_id', $template->id)
            ->where('code', 'potensi')
            ->first();

        // Get category assessment
        if ($this->potensiCategory) {
            $this->potensiAssessment = CategoryAssessment::where('participant_id', $this->participant->id)
                ->where('category_type_id', $this->potensiCategory->id)
                ->first();
        }

        // Load aspects data
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
        $this->participantRankingCache = null;
    }

    private function loadAspectsData(): void
    {
        // OPTIMIZED: Check cache first
        if ($this->aspectsDataCache !== null) {
            $this->aspectsData = $this->aspectsDataCache;

            return;
        }

        $allAspects = [];

        // Load Potensi aspects only
        if ($this->potensiCategory) {
            $potensiAspects = $this->loadCategoryAspects($this->potensiCategory->id);
            $allAspects = array_merge($allAspects, $potensiAspects);
        }

        $this->aspectsData = $allAspects;

        // OPTIMIZED: Cache the result
        $this->aspectsDataCache = $allAspects;
    }

    /**
     * Load aspects data for a category
     * REFACTORED: Use IndividualAssessmentService for consistent calculation
     */
    private function loadCategoryAspects(int $categoryTypeId): array
    {
        // Use IndividualAssessmentService for consistent calculation
        $service = app(IndividualAssessmentService::class);

        return $service->getAspectAssessments(
            $this->participant->id,
            $categoryTypeId,
            $this->tolerancePercentage
        )->toArray();
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

        // FIXED: Round all totals to 2 decimals for consistency
        $this->totalStandardRating = round($this->totalStandardRating, 2);
        $this->totalStandardScore = round($this->totalStandardScore, 2);
        $this->totalIndividualRating = round($this->totalIndividualRating, 2);
        $this->totalIndividualScore = round($this->totalIndividualScore, 2);
        $this->totalGapRating = round($this->totalGapRating, 2);
        $this->totalGapScore = round($this->totalGapScore, 2);
        $this->totalOriginalStandardScore = round($this->totalOriginalStandardScore, 2);
        $this->totalOriginalGapScore = round($this->totalOriginalGapScore, 2);

        // Determine overall conclusion based on gap-based logic
        $this->overallConclusion = ConclusionService::getGapBasedConclusion($this->totalOriginalGapScore, $this->totalGapScore);
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

        // OPTIMIZED: Clear cache before reloading (tolerance affects calculations, not data structure)
        // Note: We clear cache even though tolerance doesn't affect active aspects,
        // because it affects calculated values (adjusted standards)
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

        // Note: Ranking info will automatically update on next render due to reactive call in view
    }

    /**
     * Handle standard adjustment from DynamicStandardService
     */
    public function handleStandardUpdate(int $templateId): void
    {
        // Validate same template
        if ($this->participant->positionFormation->template_id !== $templateId) {
            return;
        }

        // Clear cache & reload with adjusted standards
        $this->clearCache();
        $this->loadAspectsData();
        $this->calculateTotals();
        $this->prepareChartData();

        // Get updated summary statistics
        $summary = $this->getPassingSummary();

        // Dispatch event to update charts with new adjusted standards
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
     * Get participant ranking information in Potensi category
     * REFACTORED: Use RankingService for consistency
     */
    public function getParticipantRanking(): ?array
    {
        // OPTIMIZED: Check cache first
        if ($this->participantRankingCache !== null) {
            return $this->participantRankingCache;
        }

        if (! $this->participant || ! $this->potensiCategory) {
            return null;
        }

        $event = $this->participant->assessmentEvent;
        $positionFormationId = $this->participant->position_formation_id;
        $template = $this->participant->positionFormation->template;

        if (! $event || ! $template) {
            return null;
        }

        // Use RankingService for consistent ranking calculation
        $rankingService = app(RankingService::class);
        $ranking = $rankingService->getParticipantRank(
            $this->participant->id,
            $event->id,
            $positionFormationId,
            $template->id,
            'potensi',
            $this->tolerancePercentage
        );

        if (! $ranking) {
            return null;
        }

        // OPTIMIZED: Cache the result
        $this->participantRankingCache = $ranking;

        return $this->participantRankingCache;
    }

    public function render()
    {
        return view('livewire.pages.individual-report.general-psy-mapping');
    }
}
