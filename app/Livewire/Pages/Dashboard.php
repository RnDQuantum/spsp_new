<?php

namespace App\Livewire\Pages;

use App\Models\Aspect;
use App\Models\CategoryAssessment;
use App\Models\CategoryType;
use App\Models\Participant;
use App\Services\DynamicStandardService;
use App\Services\IndividualAssessmentService;
use Illuminate\Support\Facades\Log;
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

    // Loading states
    public bool $isLoading = false;

    public string $loadingMessage = 'Memuat data...';

    // Cache properties
    private ?array $potensiAspectsDataCache = null;

    private ?array $kompetensiAspectsDataCache = null;

    /**
     * Listen to filter changes
     */
    protected $listeners = [
        'event-selected' => 'handleEventSelected',
        'position-selected' => 'handlePositionSelected',
        'participant-selected' => 'handleParticipantSelected',
        'participant-reset' => 'handleParticipantReset',
        'tolerance-updated' => 'handleToleranceUpdate',
        'standard-adjusted' => 'handleStandardUpdate',
    ];

    public function mount(): void
    {
        // Generate static chart IDs (same across re-renders)
        $this->potensiChartId = 'potensiSpider'.uniqid();
        $this->kompetensiChartId = 'kompetensiSpider'.uniqid();
        $this->generalChartId = 'generalSpider'.uniqid();

        // Load tolerance from session
        $this->tolerancePercentage = session('individual_report.tolerance', 10);

        // Load data if session already has filters set (e.g., after refresh)
        $eventCode = session('filter.event_code');
        $positionFormationId = session('filter.position_formation_id');

        if ($eventCode && $positionFormationId) {
            Log::info('Dashboard: mount() - loading data from session', [
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
        Log::info('Dashboard: handleEventSelected called', ['eventCode' => $eventCode]);

        // Check if participant was selected before
        $previousParticipantId = session('filter.participant_id');

        if ($previousParticipantId !== null) {
            // Case 2: We had a participant selected, will be reset to null
            $this->showLoading('Memuat data event dan mereset filter...');
            $this->js('setTimeout(() => window.location.reload(), 800)');
        }
        // Case 1: No participant was selected, just load silently
    }

    /**
     * Handle position selection
     */
    public function handlePositionSelected(?int $positionFormationId): void
    {
        Log::info('Dashboard: handlePositionSelected called', ['positionFormationId' => $positionFormationId]);

        // Check if participant was selected before
        $previousParticipantId = session('filter.participant_id');

        if ($previousParticipantId !== null) {
            // Case 2: We had a participant selected, will be reset to null
            $this->showLoading('Memuat data jabatan dan mereset filter...');
            $this->js('setTimeout(() => window.location.reload(), 800)');
        }
        // Case 1: No participant was selected, just load silently
    }

    /**
     * Handle participant selection
     */
    public function handleParticipantSelected(?int $participantId): void
    {
        Log::info('Dashboard: handleParticipantSelected called', ['participantId' => $participantId]);

        // Check if we're transitioning from null to selected
        $wasNull = $this->participant === null;
        $willBeSelected = $participantId !== null;

        Log::info('Dashboard: Participant transition check', [
            'wasNull' => $wasNull,
            'willBeSelected' => $willBeSelected,
            'participantId' => $participantId,
        ]);

        if ($wasNull && $willBeSelected) {
            $this->showLoading('Memuat data peserta dan menginisialisasi chart...');
            // Trigger smooth reload for chart initialization
            $this->js('setTimeout(() => window.location.reload(), 1000)');
        } else {
            // Load data normally
            $this->loadDashboardData();
            $this->dispatchChartUpdate();
        }
    }

    /**
     * Handle participant reset
     */
    public function handleParticipantReset(): void
    {
        Log::info('Dashboard: handleParticipantReset called');

        // Show loading state
        $this->showLoading('Mereset filter peserta dan memuat data standar...');

        // Trigger reload to show standard data
        $this->js('setTimeout(() => window.location.reload(), 800)');
    }

    /**
     * Handle standard adjustment from other components
     */
    public function handleStandardUpdate(int $templateId): void
    {
        Log::info('Dashboard: handleStandardUpdate called', ['templateId' => $templateId]);

        // Get current template ID to validate
        $currentTemplateId = null;
        if ($this->participant) {
            $currentTemplateId = $this->participant->positionFormation->template_id;
        } else {
            $positionFormationId = session('filter.position_formation_id');
            if ($positionFormationId) {
                $position = \App\Models\PositionFormation::with('template')->find($positionFormationId);
                $currentTemplateId = $position?->template->id;
            }
        }

        // Only update if same template
        if ($currentTemplateId !== $templateId) {
            Log::info('Dashboard: Template ID mismatch, skipping update', [
                'currentTemplateId' => $currentTemplateId,
                'eventTemplateId' => $templateId,
            ]);

            return;
        }

        Log::info('Dashboard: Updating data after standard adjustment');

        // Clear cache
        $this->clearCache();

        // Reload data
        if ($this->participant) {
            $this->loadAspectsData();
            $this->prepareChartData();
        } else {
            $this->loadStandardAspectsData();
            $this->prepareStandardChartData();
        }

        // Dispatch chart update
        $this->dispatchChartUpdate();

        Log::info('Dashboard: Data updated after standard adjustment');
    }

    /**
     * Clear all caches
     */
    private function clearCache(): void
    {
        $this->potensiAspectsDataCache = null;
        $this->kompetensiAspectsDataCache = null;

        Log::info('Dashboard: Cache cleared');
    }

    /**
     * Load dashboard data based on current filters
     */
    private function loadDashboardData(): void
    {
        $eventCode = session('filter.event_code');
        $positionFormationId = session('filter.position_formation_id');
        $participantId = session('filter.participant_id');

        Log::info('Dashboard: loadDashboardData called', [
            'eventCode' => $eventCode,
            'positionFormationId' => $positionFormationId,
            'participantId' => $participantId,
        ]);

        // Clear all data first
        $this->resetChartData();

        // If no event or position, show nothing
        if (! $eventCode || ! $positionFormationId) {
            Log::warning('Dashboard: Missing event or position, aborting data load');

            return;
        }

        // Load template and categories for the position
        $this->loadTemplateAndCategories($positionFormationId);

        Log::info('Dashboard: Categories loaded', [
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

            Log::info('Dashboard: Standard data loaded', [
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
            'participantName' => $this->participant?->name ?? '',
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
        Log::info('Dashboard: loadAspectsData called', [
            'participantId' => $this->participant?->id,
            'potensiCategoryId' => $this->potensiCategory?->id,
            'kompetensiCategoryId' => $this->kompetensiCategory?->id,
            'tolerancePercentage' => $this->tolerancePercentage,
        ]);

        // Check cache first
        if ($this->potensiAspectsDataCache !== null && $this->kompetensiAspectsDataCache !== null) {
            $this->potensiAspectsData = $this->potensiAspectsDataCache;
            $this->kompetensiAspectsData = $this->kompetensiAspectsDataCache;
            $this->allAspectsData = array_merge($this->potensiAspectsData, $this->kompetensiAspectsData);
            Log::info('Dashboard: Using cached data');

            return;
        }

        $service = app(IndividualAssessmentService::class);

        // Load Potensi aspects using service
        if ($this->potensiCategory) {
            $potensiAspects = $service->getAspectAssessments(
                $this->participant->id,
                $this->potensiCategory->id,
                $this->tolerancePercentage
            );

            $this->potensiAspectsData = $potensiAspects->map(function ($aspect) {
                return [
                    'name' => $aspect['name'],
                    'original_standard_rating' => $aspect['original_standard_rating'],
                    'standard_rating' => $aspect['standard_rating'],
                    'individual_rating' => $aspect['individual_rating'],
                ];
            })->toArray();

            Log::info('Dashboard: Potensi aspects loaded (participant)', ['count' => count($this->potensiAspectsData)]);
        }

        // Load Kompetensi aspects using service
        if ($this->kompetensiCategory) {
            $kompetensiAspects = $service->getAspectAssessments(
                $this->participant->id,
                $this->kompetensiCategory->id,
                $this->tolerancePercentage
            );

            $this->kompetensiAspectsData = $kompetensiAspects->map(function ($aspect) {
                return [
                    'name' => $aspect['name'],
                    'original_standard_rating' => $aspect['original_standard_rating'],
                    'standard_rating' => $aspect['standard_rating'],
                    'individual_rating' => $aspect['individual_rating'],
                ];
            })->toArray();

            Log::info('Dashboard: Kompetensi aspects loaded (participant)', ['count' => count($this->kompetensiAspectsData)]);
        }

        // Store in cache
        $this->potensiAspectsDataCache = $this->potensiAspectsData;
        $this->kompetensiAspectsDataCache = $this->kompetensiAspectsData;

        // Combine all aspects
        $this->allAspectsData = array_merge($this->potensiAspectsData, $this->kompetensiAspectsData);
        Log::info('Dashboard: All aspects merged (participant)', ['count' => count($this->allAspectsData)]);
    }

    /**
     * Load standard aspects data (no participant)
     */
    private function loadStandardAspectsData(): void
    {
        Log::info('Dashboard: loadStandardAspectsData called', [
            'tolerancePercentage' => $this->tolerancePercentage,
        ]);

        // Check cache first
        if ($this->potensiAspectsDataCache !== null && $this->kompetensiAspectsDataCache !== null) {
            $this->potensiAspectsData = $this->potensiAspectsDataCache;
            $this->kompetensiAspectsData = $this->kompetensiAspectsDataCache;
            $this->allAspectsData = array_merge($this->potensiAspectsData, $this->kompetensiAspectsData);
            Log::info('Dashboard: Using cached standard data');

            return;
        }

        $positionFormationId = session('filter.position_formation_id');
        if (! $positionFormationId) {
            return;
        }

        $position = \App\Models\PositionFormation::with('template')->find($positionFormationId);
        if (! $position || ! $position->template) {
            return;
        }

        $standardService = app(DynamicStandardService::class);
        $templateId = $position->template->id;

        // Load Potensi aspects
        if ($this->potensiCategory) {
            $this->potensiAspectsData = $this->loadStandardCategoryAspects(
                $this->potensiCategory->id,
                $templateId,
                $standardService,
                'potensi'
            );
            Log::info('Dashboard: Potensi aspects loaded', ['count' => count($this->potensiAspectsData)]);
        }

        // Load Kompetensi aspects
        if ($this->kompetensiCategory) {
            $this->kompetensiAspectsData = $this->loadStandardCategoryAspects(
                $this->kompetensiCategory->id,
                $templateId,
                $standardService,
                'kompetensi'
            );
            Log::info('Dashboard: Kompetensi aspects loaded', ['count' => count($this->kompetensiAspectsData)]);
        }

        // Store in cache
        $this->potensiAspectsDataCache = $this->potensiAspectsData;
        $this->kompetensiAspectsDataCache = $this->kompetensiAspectsData;

        // Combine all aspects
        $this->allAspectsData = array_merge($this->potensiAspectsData, $this->kompetensiAspectsData);
        Log::info('Dashboard: All aspects merged', ['count' => count($this->allAspectsData)]);
    }

    /**
     * Load standard category aspects (no participant data)
     * Uses DynamicStandardService to get adjusted ratings from session
     */
    private function loadStandardCategoryAspects(
        int $categoryTypeId,
        int $templateId,
        DynamicStandardService $standardService,
        string $categoryCode
    ): array {
        $activeAspectIds = $standardService->getActiveAspectIds($templateId, $categoryCode);

        // Fallback to all IDs if no adjustments
        if (empty($activeAspectIds)) {
            $activeAspectIds = Aspect::where('category_type_id', $categoryTypeId)
                ->orderBy('order')
                ->pluck('id')
                ->toArray();
        }

        $aspects = Aspect::whereIn('id', $activeAspectIds)
            ->with('subAspects')
            ->orderBy('order')
            ->get();

        Log::info('Dashboard: loadStandardCategoryAspects', [
            'categoryTypeId' => $categoryTypeId,
            'categoryCode' => $categoryCode,
            'aspectsCount' => $aspects->count(),
        ]);

        $toleranceFactor = 1 - ($this->tolerancePercentage / 100);

        return $aspects->map(function ($aspect) use ($templateId, $standardService, $categoryCode, $toleranceFactor) {
            // Get adjusted aspect rating from session
            if ($categoryCode === 'potensi' && $aspect->subAspects && $aspect->subAspects->count() > 0) {
                // For Potensi: Calculate from active sub-aspects
                $subAspectRatingSum = 0;
                $activeSubAspectsCount = 0;

                foreach ($aspect->subAspects as $subAspect) {
                    if (! $standardService->isSubAspectActive($templateId, $subAspect->code)) {
                        continue;
                    }

                    $adjustedSubRating = $standardService->getSubAspectRating($templateId, $subAspect->code);
                    $subAspectRatingSum += $adjustedSubRating;
                    $activeSubAspectsCount++;
                }

                $originalStandardRating = $activeSubAspectsCount > 0
                    ? round($subAspectRatingSum / $activeSubAspectsCount, 2)
                    : (float) $aspect->standard_rating;
            } else {
                // For Kompetensi: Use direct rating from session
                $originalStandardRating = $standardService->getAspectRating($templateId, $aspect->code);
            }

            // Apply tolerance
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
        Log::info('Dashboard: prepareChartData called', [
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

        Log::info('Dashboard: Chart data prepared (participant)', [
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
        $this->showLoading('Memperbarui toleransi...');

        $this->tolerancePercentage = $tolerance;

        // Clear cache before reload
        $this->clearCache();

        // Reload aspects data with new tolerance
        if ($this->participant) {
            $this->loadAspectsData();
            $this->prepareChartData();
        } else {
            $this->loadStandardAspectsData();
            $this->prepareStandardChartData();
        }

        // Get updated summary statistics
        $summary = $this->getPassingSummary();

        // Dispatch events to update all charts
        $this->dispatch('chartDataUpdated', [
            'hasParticipant' => $this->participant !== null,
            'participantName' => $this->participant?->name ?? '',
            'tolerancePercentage' => $tolerance,
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

        // Hide loading after everything is done
        $this->hideLoading();
    }

    /**
     * Get summary statistics for passing aspects
     * Note: For participant data, we count aspects where individual >= adjusted standard
     */
    public function getPassingSummary(): array
    {
        $totalAspects = count($this->allAspectsData);
        $passingAspects = 0;

        foreach ($this->allAspectsData as $aspect) {
            // Get values from aspect data
            $adjustedStandard = $aspect['standard_rating'];
            $individual = $aspect['individual_rating'];

            // Count as passing if individual >= adjusted standard (accounting for tolerance)
            if ($individual >= $adjustedStandard) {
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
     * Show loading state
     */
    private function showLoading(string $message = 'Memuat data...'): void
    {
        $this->isLoading = true;
        $this->loadingMessage = $message;
        $this->dispatch('showLoading', message: $message);
    }

    /**
     * Hide loading state
     */
    private function hideLoading(): void
    {
        $this->isLoading = false;
        $this->dispatch('hideLoading');
    }

    public function render()
    {
        return view('livewire.pages.dashboard');
    }
}
