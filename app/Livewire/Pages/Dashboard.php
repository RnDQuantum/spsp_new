<?php

namespace App\Livewire\Pages;

use App\Models\Aspect;
use App\Models\AspectAssessment;
use App\Models\CategoryAssessment;
use App\Models\CategoryType;
use App\Models\Participant;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app', ['title' => 'Dashboard'])]
class Dashboard extends Component
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

    /**
     * Listen to filter changes
     */
    protected $listeners = [
        'event-selected' => 'handleEventSelected',
        'position-selected' => 'handlePositionSelected',
        'participant-selected' => 'handleParticipantSelected',
        'tolerance-updated' => 'handleToleranceUpdate',
    ];

    public function mount(): void
    {
        // Generate static chart IDs (same across re-renders)
        $this->potensiChartId = 'potensiSpider'.uniqid();
        $this->kompetensiChartId = 'kompetensiSpider'.uniqid();
        $this->generalChartId = 'generalSpider'.uniqid();

        // Load tolerance from session
        $this->tolerancePercentage = session('dashboard.tolerance', 10);

        // Load data if session already has filters set (e.g., after refresh)
        $eventCode = session('filter.event_code');
        $positionFormationId = session('filter.position_formation_id');

        if ($eventCode && $positionFormationId) {
            \Log::info('Dashboard: mount() - loading data from session', [
                'eventCode' => $eventCode,
                'positionFormationId' => $positionFormationId,
            ]);
            $this->loadDashboardData();
        }
    }

    /**
     * Handle event selection
     */
    public function handleEventSelected(?string $eventCode): void
    {
        \Log::info('Dashboard: handleEventSelected called', ['eventCode' => $eventCode]);

        // Position will auto-reset, wait for position-selected event
    }

    /**
     * Handle position selection
     */
    public function handlePositionSelected(?int $positionFormationId): void
    {
        \Log::info('Dashboard: handlePositionSelected called', ['positionFormationId' => $positionFormationId]);

        // Participant will auto-reset, wait for participant-selected event
    }

    /**
     * Handle participant selection
     */
    public function handleParticipantSelected(?int $participantId): void
    {
        \Log::info('Dashboard: handleParticipantSelected called', ['participantId' => $participantId]);

        $this->loadDashboardData();
        $this->dispatchChartUpdate();
    }

    /**
     * Load dashboard data based on current filters
     */
    private function loadDashboardData(): void
    {
        $eventCode = session('filter.event_code');
        $positionFormationId = session('filter.position_formation_id');
        $participantId = session('filter.participant_id');

        \Log::info('Dashboard: loadDashboardData called', [
            'eventCode' => $eventCode,
            'positionFormationId' => $positionFormationId,
            'participantId' => $participantId,
        ]);

        // Clear all data first
        $this->resetChartData();

        // If no event or position, show nothing
        if (! $eventCode || ! $positionFormationId) {
            \Log::warning('Dashboard: Missing event or position, aborting data load');

            return;
        }

        // Load template and categories for the position
        $this->loadTemplateAndCategories($positionFormationId);

        \Log::info('Dashboard: Categories loaded', [
            'potensiCategory' => $this->potensiCategory?->id,
            'kompetensiCategory' => $this->kompetensiCategory?->id,
        ]);

        // If participant is selected, load participant data
        if ($participantId) {
            $this->participant = Participant::with([
                'assessmentEvent',
                'batch',
                'positionFormation.template',
            ])->find($participantId);

            if ($this->participant) {
                // Load category assessments
                $this->loadCategoryAssessments();

                // Load all aspects data
                $this->loadAspectsData();

                // Prepare chart data
                $this->prepareChartData();
            }
        } else {
            // No participant selected, show only standard data
            $this->loadStandardAspectsData();
            $this->prepareStandardChartData();

            \Log::info('Dashboard: Standard data loaded', [
                'potensiAspectsCount' => count($this->potensiAspectsData),
                'kompetensiAspectsCount' => count($this->kompetensiAspectsData),
                'totalAspectsCount' => count($this->allAspectsData),
            ]);
        }

    }

    /**
     * Dispatch chart update event to JavaScript
     */
    private function dispatchChartUpdate(): void
    {
        $hasParticipant = $this->participant !== null;

        $this->dispatch('chartDataUpdated', [
            'hasParticipant' => $hasParticipant,
            'tolerancePercentage' => $this->tolerancePercentage,
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
    }

    /**
     * Load template and categories based on position
     */
    private function loadTemplateAndCategories(int $positionFormationId): void
    {
        $position = \App\Models\PositionFormation::with('template')->find($positionFormationId);

        if (! $position || ! $position->template) {
            return;
        }

        $template = $position->template;

        // Get category types
        $this->potensiCategory = CategoryType::where('template_id', $template->id)
            ->where('code', 'potensi')
            ->first();

        $this->kompetensiCategory = CategoryType::where('template_id', $template->id)
            ->where('code', 'kompetensi')
            ->first();
    }

    /**
     * Load category assessments for participant
     */
    private function loadCategoryAssessments(): void
    {
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
    }

    /**
     * Load aspects data for participant
     */
    private function loadAspectsData(): void
    {
        \Log::info('Dashboard: loadAspectsData called', [
            'participantId' => $this->participant?->id,
            'potensiCategoryId' => $this->potensiCategory?->id,
            'kompetensiCategoryId' => $this->kompetensiCategory?->id,
        ]);

        // Load Potensi aspects
        if ($this->potensiCategory) {
            $this->potensiAspectsData = $this->loadCategoryAspects($this->potensiCategory->id);
            \Log::info('Dashboard: Potensi aspects loaded (participant)', ['count' => count($this->potensiAspectsData)]);
        }

        // Load Kompetensi aspects
        if ($this->kompetensiCategory) {
            $this->kompetensiAspectsData = $this->loadCategoryAspects($this->kompetensiCategory->id);
            \Log::info('Dashboard: Kompetensi aspects loaded (participant)', ['count' => count($this->kompetensiAspectsData)]);
        }

        // Combine all aspects
        $this->allAspectsData = array_merge($this->potensiAspectsData, $this->kompetensiAspectsData);
        \Log::info('Dashboard: All aspects merged (participant)', ['count' => count($this->allAspectsData)]);
    }

    /**
     * Load category aspects for participant
     */
    private function loadCategoryAspects(int $categoryTypeId): array
    {
        $aspectIds = Aspect::where('category_type_id', $categoryTypeId)
            ->orderBy('order')
            ->pluck('id')
            ->toArray();

        \Log::info('Dashboard: loadCategoryAspects - aspects found', [
            'categoryTypeId' => $categoryTypeId,
            'aspectIdsCount' => count($aspectIds),
            'participantId' => $this->participant->id,
        ]);

        $aspectAssessments = AspectAssessment::with('aspect')
            ->where('participant_id', $this->participant->id)
            ->whereIn('aspect_id', $aspectIds)
            ->orderBy('aspect_id')
            ->get();

        \Log::info('Dashboard: loadCategoryAspects - assessments found', [
            'categoryTypeId' => $categoryTypeId,
            'assessmentsCount' => $aspectAssessments->count(),
        ]);

        return $aspectAssessments->map(function ($assessment) {
            // 1. Get original values from database
            $originalStandardRating = (float) $assessment->standard_rating;
            $individualRating = (float) $assessment->individual_rating;

            // 2. Calculate adjusted standard based on tolerance
            $toleranceFactor = 1 - $this->tolerancePercentage / 100;
            $adjustedStandardRating = $originalStandardRating * $toleranceFactor;

            return [
                'name' => $assessment->aspect->name,
                'original_standard_rating' => $originalStandardRating,
                'standard_rating' => $adjustedStandardRating,
                'individual_rating' => $individualRating,
            ];
        })->toArray();
    }

    /**
     * Load standard aspects data (no participant)
     */
    private function loadStandardAspectsData(): void
    {
        // Load Potensi aspects
        if ($this->potensiCategory) {
            $this->potensiAspectsData = $this->loadStandardCategoryAspects($this->potensiCategory->id);
            \Log::info('Dashboard: Potensi aspects loaded', ['count' => count($this->potensiAspectsData)]);
        }

        // Load Kompetensi aspects
        if ($this->kompetensiCategory) {
            $this->kompetensiAspectsData = $this->loadStandardCategoryAspects($this->kompetensiCategory->id);
            \Log::info('Dashboard: Kompetensi aspects loaded', ['count' => count($this->kompetensiAspectsData)]);
        }

        // Combine all aspects
        $this->allAspectsData = array_merge($this->potensiAspectsData, $this->kompetensiAspectsData);
        \Log::info('Dashboard: All aspects merged', ['count' => count($this->allAspectsData)]);
    }

    /**
     * Load standard category aspects (no participant data)
     */
    private function loadStandardCategoryAspects(int $categoryTypeId): array
    {
        $aspects = Aspect::where('category_type_id', $categoryTypeId)
            ->orderBy('order')
            ->get(['id', 'name', 'standard_rating']);

        \Log::info('Dashboard: loadStandardCategoryAspects', [
            'categoryTypeId' => $categoryTypeId,
            'aspectsCount' => $aspects->count(),
        ]);

        return $aspects->map(function ($aspect) {
            $originalStandardRating = (float) $aspect->standard_rating;

            // Calculate adjusted standard based on tolerance
            $toleranceFactor = 1 - ($this->tolerancePercentage / 100);
            $adjustedStandardRating = $originalStandardRating * $toleranceFactor;

            return [
                'name' => $aspect->name,
                'original_standard_rating' => $originalStandardRating,
                'standard_rating' => $adjustedStandardRating,
                'individual_rating' => 0, // No participant data
            ];
        })->toArray();
    }

    /**
     * Prepare chart data for participant
     */
    private function prepareChartData(): void
    {
        \Log::info('Dashboard: prepareChartData called', [
            'potensiAspectsCount' => count($this->potensiAspectsData),
            'kompetensiAspectsCount' => count($this->kompetensiAspectsData),
            'allAspectsCount' => count($this->allAspectsData),
        ]);

        // Clear previous CHART data only (not aspectsData!)
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

        \Log::info('Dashboard: Chart data prepared (participant)', [
            'potensiLabelsCount' => count($this->potensiLabels),
            'kompetensiLabelsCount' => count($this->kompetensiLabels),
            'generalLabelsCount' => count($this->generalLabels),
        ]);
    }

    /**
     * Prepare chart data for standard only (no participant)
     */
    private function prepareStandardChartData(): void
    {
        // Clear previous CHART data only (not aspectsData!)
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
            $this->potensiIndividualRatings[] = 0;
        }

        // Prepare Kompetensi chart data
        foreach ($this->kompetensiAspectsData as $aspect) {
            $this->kompetensiLabels[] = $aspect['name'];
            $this->kompetensiOriginalStandardRatings[] = round($aspect['original_standard_rating'], 2);
            $this->kompetensiStandardRatings[] = round($aspect['standard_rating'], 2);
            $this->kompetensiIndividualRatings[] = 0;
        }

        // Prepare General chart data (all aspects)
        foreach ($this->allAspectsData as $aspect) {
            $this->generalLabels[] = $aspect['name'];
            $this->generalOriginalStandardRatings[] = round($aspect['original_standard_rating'], 2);
            $this->generalStandardRatings[] = round($aspect['standard_rating'], 2);
            $this->generalIndividualRatings[] = 0;
        }
    }

    /**
     * Reset all chart data
     */
    private function resetChartData(): void
    {
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

        $this->potensiAspectsData = [];
        $this->kompetensiAspectsData = [];
        $this->allAspectsData = [];
    }

    /**
     * Handle tolerance update from child component
     */
    public function handleToleranceUpdate(int $tolerance): void
    {
        $this->tolerancePercentage = $tolerance;

        // Reload data with new tolerance
        $this->loadDashboardData();

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
        return view('livewire.pages.dashboard');
    }
}
