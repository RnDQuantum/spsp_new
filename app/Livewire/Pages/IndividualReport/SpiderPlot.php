<?php

declare(strict_types=1);

namespace App\Livewire\Pages\IndividualReport;

use App\Models\CategoryAssessment;
use App\Models\CategoryType;
use App\Models\Participant;
use App\Services\IndividualAssessmentService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app', ['title' => 'Spider Plot'])]
class SpiderPlot extends Component
{
    public ?Participant $participant = null;

    public ?CategoryType $potensiCategory = null;

    public ?CategoryType $kompetensiCategory = null;

    public ?CategoryAssessment $potensiAssessment = null;

    public ?CategoryAssessment $kompetensiAssessment = null;

    // Tolerance percentage (loaded from session)
    public int $tolerancePercentage = 10;

    // Chart IDs for unique identification
    public string $potensiChartId = '';

    public string $kompetensiChartId = '';

    public string $generalChartId = '';

    // Potensi Chart Data (5 aspects)
    public $potensiLabels = [];

    public $potensiOriginalStandardRatings = [];

    public $potensiStandardRatings = [];

    public $potensiIndividualRatings = [];

    // Kompetensi Chart Data (9 aspects)
    public $kompetensiLabels = [];

    public $kompetensiOriginalStandardRatings = [];

    public $kompetensiStandardRatings = [];

    public $kompetensiIndividualRatings = [];

    // General Chart Data (all 13+ aspects)
    public $generalLabels = [];

    public $generalOriginalStandardRatings = [];

    public $generalStandardRatings = [];

    public $generalIndividualRatings = [];

    // Aspect data for calculations
    public $potensiAspectsData = [];

    public $kompetensiAspectsData = [];

    public $allAspectsData = [];

    // Cache properties for performance optimization
    private ?array $potensiAspectsDataCache = null;

    private ?array $kompetensiAspectsDataCache = null;

    public function mount($eventCode, $testNumber): void
    {
        // Generate unique chart IDs
        $this->potensiChartId = 'potensiSpider'.uniqid();
        $this->kompetensiChartId = 'kompetensiSpider'.uniqid();
        $this->generalChartId = 'generalSpider'.uniqid();

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

        // Prepare chart data
        $this->prepareChartData();
    }

    /**
     * Clear all caches
     */
    private function clearCache(): void
    {
        $this->potensiAspectsDataCache = null;
        $this->kompetensiAspectsDataCache = null;
    }

    /**
     * Load aspects data using IndividualAssessmentService
     */
    private function loadAspectsData(): void
    {
        // Load Potensi aspects
        if ($this->potensiCategory) {
            // Check cache first
            if ($this->potensiAspectsDataCache !== null) {
                $this->potensiAspectsData = $this->potensiAspectsDataCache;
            } else {
                // Cache miss - load from service
                $this->potensiAspectsData = $this->loadCategoryAspects($this->potensiCategory->id);
                $this->potensiAspectsDataCache = $this->potensiAspectsData;
            }
        }

        // Load Kompetensi aspects
        if ($this->kompetensiCategory) {
            // Check cache first
            if ($this->kompetensiAspectsDataCache !== null) {
                $this->kompetensiAspectsData = $this->kompetensiAspectsDataCache;
            } else {
                // Cache miss - load from service
                $this->kompetensiAspectsData = $this->loadCategoryAspects($this->kompetensiCategory->id);
                $this->kompetensiAspectsDataCache = $this->kompetensiAspectsData;
            }
        }

        // Combine all aspects
        $this->allAspectsData = array_merge($this->potensiAspectsData, $this->kompetensiAspectsData);
    }

    /**
     * Load category aspects using IndividualAssessmentService
     * This replaces manual calculation with service layer
     */
    private function loadCategoryAspects(int $categoryTypeId): array
    {
        $service = app(IndividualAssessmentService::class);

        // Get aspect assessments from service (already adjusted with session values)
        $aspectAssessments = $service->getAspectAssessments(
            $this->participant->id,
            $categoryTypeId,
            $this->tolerancePercentage
        );

        // Transform to format needed for spider chart
        return $aspectAssessments->map(function ($assessment) {
            return [
                'name' => $assessment['name'],
                'original_standard_rating' => $assessment['original_standard_rating'],
                'standard_rating' => $assessment['standard_rating'],
                'individual_rating' => $assessment['individual_rating'],
            ];
        })->toArray();
    }

    private function prepareChartData(): void
    {
        // Clear previous data
        $this->potensiLabels = [];
        $this->potensiOriginalStandardRatings = [];
        $this->potensiStandardRatings = [];
        $this->potensiIndividualRatings = [];

        $this->kompetensiLabels = [];
        $this->kompetensiOriginalStandardRatings = [];
        $this->kompetensiStandardRatings = [];
        $this->kompetensiIndividualRatings = [];

        $this->generalLabels = [];
        $this->generalOriginalStandardRatings = [];
        $this->generalStandardRatings = [];
        $this->generalIndividualRatings = [];

        // Prepare Potensi chart data
        foreach ($this->potensiAspectsData as $aspect) {
            $this->potensiLabels[] = $aspect['name'];
            $this->potensiOriginalStandardRatings[] = round($aspect['original_standard_rating'], 2);
            $this->potensiStandardRatings[] = round($aspect['standard_rating'], 2);
            $this->potensiIndividualRatings[] = round($aspect['individual_rating'], 2);
        }

        // Prepare Kompetensi chart data
        foreach ($this->kompetensiAspectsData as $aspect) {
            $this->kompetensiLabels[] = $aspect['name'];
            $this->kompetensiOriginalStandardRatings[] = round($aspect['original_standard_rating'], 2);
            $this->kompetensiStandardRatings[] = round($aspect['standard_rating'], 2);
            $this->kompetensiIndividualRatings[] = round($aspect['individual_rating'], 2);
        }

        // Prepare General chart data (all aspects)
        foreach ($this->allAspectsData as $aspect) {
            $this->generalLabels[] = $aspect['name'];
            $this->generalOriginalStandardRatings[] = round($aspect['original_standard_rating'], 2);
            $this->generalStandardRatings[] = round($aspect['standard_rating'], 2);
            $this->generalIndividualRatings[] = round($aspect['individual_rating'], 2);
        }
    }

    /**
     * Listen to events from other components
     */
    protected $listeners = [
        'tolerance-updated' => 'handleToleranceUpdate',
        'standard-adjusted' => 'handleStandardUpdate',
        'standard-switched' => 'handleStandardSwitch',
    ];

    /**
     * Handle tolerance update from ToleranceSelector component
     */
    public function handleToleranceUpdate(int $tolerance): void
    {
        $this->tolerancePercentage = $tolerance;

        // Clear cache before reload
        $this->clearCache();

        // Reload aspects data with new tolerance
        $this->loadAspectsData();

        // Update chart data
        $this->prepareChartData();

        // Get updated summary statistics
        $summary = $this->getPassingSummary();

        // Dispatch events to update all charts
        $this->dispatch('chartDataUpdated', [
            'chartType' => 'all',
            'tolerance' => $tolerance,
            'potensi' => [
                'labels' => $this->potensiLabels,
                'originalStandardRatings' => $this->potensiOriginalStandardRatings,
                'standardRatings' => $this->potensiStandardRatings,
                'individualRatings' => $this->potensiIndividualRatings,
            ],
            'kompetensi' => [
                'labels' => $this->kompetensiLabels,
                'originalStandardRatings' => $this->kompetensiOriginalStandardRatings,
                'standardRatings' => $this->kompetensiStandardRatings,
                'individualRatings' => $this->kompetensiIndividualRatings,
            ],
            'general' => [
                'labels' => $this->generalLabels,
                'originalStandardRatings' => $this->generalOriginalStandardRatings,
                'standardRatings' => $this->generalStandardRatings,
                'individualRatings' => $this->generalIndividualRatings,
            ],
        ]);

        // Dispatch event to update summary statistics in ToleranceSelector
        $this->dispatch('summary-updated', [
            'passing' => $summary['passing'],
            'total' => $summary['total'],
            'percentage' => $summary['percentage'],
        ]);
    }

    /**
     * Handle standard adjustment from StandardPsikometrik/StandardMc components
     */
    public function handleStandardUpdate(int $templateId): void
    {
        // Validate same template
        if ($this->participant->positionFormation->template_id !== $templateId) {
            return;
        }

        // Clear cache before reload
        $this->clearCache();

        // Reload aspects data (will read fresh from session via service)
        $this->loadAspectsData();

        // Update chart data
        $this->prepareChartData();

        // Get updated summary statistics
        $summary = $this->getPassingSummary();

        // Dispatch events to update all charts
        $this->dispatch('chartDataUpdated', [
            'chartType' => 'all',
            'tolerance' => $this->tolerancePercentage,
            'potensi' => [
                'labels' => $this->potensiLabels,
                'originalStandardRatings' => $this->potensiOriginalStandardRatings,
                'standardRatings' => $this->potensiStandardRatings,
                'individualRatings' => $this->potensiIndividualRatings,
            ],
            'kompetensi' => [
                'labels' => $this->kompetensiLabels,
                'originalStandardRatings' => $this->kompetensiOriginalStandardRatings,
                'standardRatings' => $this->kompetensiStandardRatings,
                'individualRatings' => $this->kompetensiIndividualRatings,
            ],
            'general' => [
                'labels' => $this->generalLabels,
                'originalStandardRatings' => $this->generalOriginalStandardRatings,
                'standardRatings' => $this->generalStandardRatings,
                'individualRatings' => $this->generalIndividualRatings,
            ],
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
        $totalAspects = count($this->allAspectsData);
        $passingAspects = 0;

        foreach ($this->allAspectsData as $aspect) {
            // Calculate percentage based on adjusted standard (already calculated in aspect data)
            $adjustedStandard = $aspect['standard_rating'];
            $individual = $aspect['individual_rating'];

            // Calculate percentage: (individual / adjusted) * 100
            $percentage = $adjustedStandard > 0 ? ($individual / $adjustedStandard) * 100 : 0;

            // Count as passing if percentage >= 100% (meets or exceeds adjusted standard)
            if ($percentage >= 100) {
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
        return view('livewire.pages.individual-report.spider-plot');
    }
}
