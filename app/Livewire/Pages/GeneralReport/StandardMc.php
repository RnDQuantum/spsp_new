<?php

namespace App\Livewire\Pages\GeneralReport;

use App\Models\AssessmentEvent;
use App\Models\AssessmentTemplate;
use App\Models\CategoryType;
use App\Services\CustomStandardService;
use App\Services\DynamicStandardService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app', ['title' => 'Standar Pemetaan Kompetensi Individu'])]
class StandardMc extends Component
{
    protected $listeners = [
        'event-selected' => 'handleEventSelected',
        'position-selected' => 'handlePositionSelected',
        'standard-adjusted' => 'handleStandardUpdate',
        'handleStandardUpdate' => 'handleStandardUpdate',
        'standard-switched' => 'handleStandardSwitch',
    ];

    public ?AssessmentEvent $selectedEvent = null;

    public ?AssessmentTemplate $selectedTemplate = null;

    /** @var array<int, array{name:string,weight_percentage:int,standard_rating:float,aspects:array}> */
    public array $categoryData = [];

    public array $totals = [
        'total_aspects' => 0,
        'total_weight' => 0,
        'total_standard_rating_sum' => 0.0,
        'total_score' => 0.0,
    ];

    public array $chartData = [
        'labels' => [],
        'ratings' => [],
        'scores' => [],
    ];

    // Unique chart ID
    public string $chartId = '';

    public int $maxScore = 0;

    // PHASE 3: Custom Standard Selection
    public ?int $selectedCustomStandardId = null;

    public array $availableCustomStandards = [];

    // CACHE PROPERTIES - untuk menyimpan hasil kalkulasi
    private ?array $categoryDataCache = null;

    private ?array $chartDataCache = null;

    private ?array $totalsCache = null;

    private ?int $maxScoreCache = null;

    // PHASE 2C: Modal states for inline editing
    public bool $showEditRatingModal = false;

    public bool $showEditCategoryWeightModal = false;

    public string $editingField = '';

    public int|float|null $editingValue = null;

    public int|float|null $editingOriginalValue = null;

    public function mount(): void
    {
        // Generate unique chart ID
        $this->chartId = 'standardMc'.uniqid();

        $this->loadStandardData();
        $this->loadAvailableCustomStandards();
    }

    /**
     * Clear all caches
     */
    private function clearCache(): void
    {
        $this->categoryDataCache = null;
        $this->chartDataCache = null;
        $this->totalsCache = null;
        $this->maxScoreCache = null;
    }

    public function handleEventSelected(?string $eventCode): void
    {
        // Event changed, position will be auto-reset by PositionSelector
        // Wait for position to be selected before updating chart
        $this->clearCache();
    }

    public function handlePositionSelected(?int $positionFormationId): void
    {
        // Position selected, now we have both event and position
        // Load data with the new filters
        $this->clearCache();
        $this->loadStandardData();
        $this->loadAvailableCustomStandards();

        // Dispatch event to update charts
        $this->dispatch('chartDataUpdated', [
            'labels' => $this->chartData['labels'],
            'ratings' => $this->chartData['ratings'],
            'scores' => $this->chartData['scores'],
            'templateName' => $this->selectedTemplate?->name ?? 'Standard',
            'maxScore' => $this->maxScore,
        ]);
    }

    /**
     * PHASE 2C: Handle standard adjustment from SelectiveAspectsModal
     */
    public function handleStandardUpdate(int $templateId): void
    {
        // Only refresh if same template
        if ($this->selectedTemplate && $this->selectedTemplate->id === $templateId) {
            $this->clearCache();
            $this->loadStandardData();

            // Re-dispatch chart update
            $this->dispatch('chartDataUpdated', [
                'labels' => $this->chartData['labels'],
                'ratings' => $this->chartData['ratings'],
                'scores' => $this->chartData['scores'],
                'templateName' => $this->selectedTemplate?->name ?? 'Standard',
                'maxScore' => $this->maxScore,
            ]);
        }
    }

    /**
     * PHASE 2C: Open modal to edit category weight
     */
    public function openEditCategoryWeight(string $categoryCode, int $currentWeight): void
    {
        if (! $this->selectedTemplate) {
            return;
        }

        $this->editingField = $categoryCode;
        $this->editingValue = $currentWeight;
        $this->editingOriginalValue = CategoryType::where('template_id', $this->selectedTemplate->id)
            ->where('code', $categoryCode)
            ->first()?->weight_percentage ?? $currentWeight;
        $this->showEditCategoryWeightModal = true;
    }

    /**
     * PHASE 2C: Save category weight adjustment
     */
    public function saveCategoryWeight(): void
    {
        if (! $this->selectedTemplate || ! is_int($this->editingValue)) {
            return;
        }

        app(DynamicStandardService::class)->saveCategoryWeight(
            $this->selectedTemplate->id,
            $this->editingField,
            $this->editingValue
        );

        $this->showEditCategoryWeightModal = false;
        $this->dispatch('standard-adjusted', templateId: $this->selectedTemplate->id);
    }

    /**
     * PHASE 2C: Open modal to edit aspect rating (Kompetensi - direct rating edit)
     */
    public function openEditAspectRating(string $aspectCode, float $currentRating): void
    {
        if (! $this->selectedTemplate) {
            return;
        }

        $this->resetErrorBag(); // Clear any previous errors
        $this->editingField = $aspectCode;
        $this->editingValue = (int) $currentRating; // Convert to int for Kompetensi
        $this->editingOriginalValue = \App\Models\Aspect::where('template_id', $this->selectedTemplate->id)
            ->where('code', $aspectCode)
            ->first()?->standard_rating ?? $currentRating;
        $this->showEditRatingModal = true;
    }

    /**
     * PHASE 2C: Save aspect rating adjustment (Kompetensi)
     */
    public function saveAspectRating(): void
    {
        if (! $this->selectedTemplate || ! is_int($this->editingValue)) {
            return;
        }

        // Validate rating must be between 1 and 5
        if ($this->editingValue < 1 || $this->editingValue > 5) {
            $this->addError('editingValue', 'Rating harus antara 1 sampai 5.');

            return;
        }

        app(DynamicStandardService::class)->saveAspectRating(
            $this->selectedTemplate->id,
            $this->editingField,
            $this->editingValue
        );

        $this->showEditRatingModal = false;
        $this->dispatch('standard-adjusted', templateId: $this->selectedTemplate->id);
    }

    /**
     * PHASE 2C: Open SelectiveAspectsModal
     */
    public function openSelectionModal(): void
    {
        if (! $this->selectedTemplate) {
            return;
        }

        $this->dispatch('openSelectionModal', templateId: $this->selectedTemplate->id, categoryCode: 'kompetensi');
    }

    /**
     * PHASE 2C: Reset adjustments (both category weights + Kompetensi aspects)
     */
    public function resetAdjustments(): void
    {
        if (! $this->selectedTemplate) {
            return;
        }

        $dynamicService = app(DynamicStandardService::class);

        // Reset Kompetensi category adjustments (aspects, ratings)
        $dynamicService->resetCategoryAdjustments($this->selectedTemplate->id, 'kompetensi');

        // Reset both category weights
        $dynamicService->resetCategoryWeights($this->selectedTemplate->id);

        $this->dispatch('standard-adjusted', templateId: $this->selectedTemplate->id);
    }

    /**
     * PHASE 2C: Close modals
     */
    public function closeModal(): void
    {
        $this->resetErrorBag(); // Clear any errors when closing modal
        $this->showEditRatingModal = false;
        $this->showEditCategoryWeightModal = false;
        $this->editingField = '';
        $this->editingValue = null;
        $this->editingOriginalValue = null;
    }

    /**
     * PHASE 3: Load available custom standards for current institution and template
     */
    private function loadAvailableCustomStandards(): void
    {
        $this->availableCustomStandards = [];

        if (! $this->selectedTemplate || ! auth()->user()->institution_id) {
            return;
        }

        $customStandardService = app(CustomStandardService::class);

        $this->availableCustomStandards = $customStandardService
            ->getForInstitution(auth()->user()->institution_id, $this->selectedTemplate->id)
            ->map(fn ($std) => [
                'id' => $std->id,
                'code' => $std->code,
                'name' => $std->name,
                'description' => $std->description,
            ])
            ->toArray();

        // Load currently selected custom standard from session
        $this->selectedCustomStandardId = $customStandardService->getSelected($this->selectedTemplate->id);
    }

    /**
     * PHASE 3: Handle custom standard selection change
     */
    public function selectCustomStandard(?int $customStandardId): void
    {
        if (! $this->selectedTemplate) {
            return;
        }

        $customStandardService = app(CustomStandardService::class);

        // Save selection to session
        $customStandardService->select($this->selectedTemplate->id, $customStandardId);

        $this->selectedCustomStandardId = $customStandardId;

        // Clear cache and reload data
        $this->clearCache();
        $this->loadStandardData();

        // Dispatch event to notify other components
        $this->dispatch('standard-switched', templateId: $this->selectedTemplate->id);
    }

    /**
     * PHASE 3: Handle standard switch event from other components
     */
    public function handleStandardSwitch(int $templateId): void
    {
        // Only refresh if same template
        if ($this->selectedTemplate && $this->selectedTemplate->id === $templateId) {
            $this->clearCache();
            $this->loadStandardData();
            $this->loadAvailableCustomStandards();

            // Re-dispatch chart update
            $this->dispatch('chartDataUpdated', [
                'labels' => $this->chartData['labels'],
                'ratings' => $this->chartData['ratings'],
                'scores' => $this->chartData['scores'],
                'templateName' => $this->selectedTemplate?->name ?? 'Standard',
                'maxScore' => $this->maxScore,
            ]);
        }
    }

    /**
     * PHASE 2C: Load standard data with DynamicStandardService integration
     * OPTIMIZED: Use caching to avoid recalculating data
     */
    private function loadStandardData(): void
    {
        // Check if data already cached
        if ($this->categoryDataCache !== null) {
            $this->categoryData = $this->categoryDataCache;
            $this->totals = $this->totalsCache ?? [];
            $this->chartData = $this->chartDataCache ?? [];
            $this->maxScore = $this->maxScoreCache ?? 0;

            return;
        }

        $this->categoryData = [];
        $this->totals = [
            'total_aspects' => 0,
            'total_weight' => 0,
            'total_standard_rating_sum' => 0.0,
            'total_score' => 0.0,
        ];
        $this->chartData = [
            'labels' => [],
            'ratings' => [],
            'scores' => [],
        ];
        $this->maxScore = 0;

        // Read filters from session
        $eventCode = session('filter.event_code');
        $positionFormationId = session('filter.position_formation_id');

        if (! $eventCode || ! $positionFormationId) {
            return;
        }

        $this->selectedEvent = AssessmentEvent::query()
            ->with('institution', 'positionFormations.template')
            ->where('code', $eventCode)
            ->first();

        if (! $this->selectedEvent) {
            return;
        }

        // Get the selected position formation
        $selectedPosition = $this->selectedEvent->positionFormations
            ->where('id', $positionFormationId)
            ->first();

        if (! $selectedPosition) {
            return;
        }

        // Get template from the selected position
        $this->selectedTemplate = $selectedPosition->template;

        if (! $this->selectedTemplate) {
            return;
        }

        $templateId = $this->selectedTemplate->id;

        // OPTIMIZED: Get DynamicStandardService instance ONCE
        $dynamicService = app(DynamicStandardService::class);

        // OPTIMIZED: Check if there are adjustments - skip complex calculation if not needed
        $hasAdjustments = $dynamicService->hasCategoryAdjustments($templateId, 'kompetensi');

        // Load ONLY Kompetensi category type with aspects from selected position's template
        // OPTIMIZED: Eager load all relationships in one query
        $categories = CategoryType::query()
            ->where('template_id', $templateId)
            ->where('code', 'kompetensi')
            ->with([
                'aspects' => fn ($q) => $q->orderBy('order'),
            ])
            ->orderBy('order')
            ->get();

        $totalAspects = 0;
        $totalWeight = 0;
        $totalStandardRatingSum = 0.0;
        $totalScore = 0.0;
        $totalAspectRatingSum = 0.0; // Sum of aspect average ratings

        foreach ($categories as $category) {
            $aspectsData = [];
            $categoryAspectCount = 0;
            $categoryWeightSum = 0;
            $categoryStandardRatingSum = 0.0;
            $categoryScoreSum = 0.0;

            // OPTIMIZED: Get adjusted category weight (dynamicService already instantiated)
            $categoryWeight = $hasAdjustments
                ? $dynamicService->getCategoryWeight($templateId, $category->code)
                : $category->weight_percentage;
            $categoryOriginalWeight = $category->weight_percentage;

            foreach ($category->aspects as $aspect) {
                // OPTIMIZED: Check if aspect is active (only if has adjustments)
                if ($hasAdjustments && ! $dynamicService->isAspectActive($templateId, $aspect->code)) {
                    continue; // Skip inactive aspects
                }

                // OPTIMIZED: Get adjusted aspect weight (only if has adjustments)
                $aspectWeight = $hasAdjustments
                    ? $dynamicService->getAspectWeight($templateId, $aspect->code)
                    : $aspect->weight_percentage;
                $aspectOriginalWeight = $aspect->weight_percentage;

                // OPTIMIZED: Get adjusted aspect rating (only if has adjustments)
                $aspectRating = $hasAdjustments
                    ? $dynamicService->getAspectRating($templateId, $aspect->code)
                    : (float) $aspect->standard_rating;
                $aspectOriginalRating = (float) $aspect->standard_rating;

                // Calculate aspect score: rating × adjusted weight
                $aspectScore = round($aspectRating * $aspectWeight, 2);

                $aspectsData[] = [
                    'code' => $aspect->code,
                    'name' => $aspect->name,
                    'weight_percentage' => $aspectWeight,
                    'original_weight' => $aspectOriginalWeight,
                    'is_weight_adjusted' => $aspectWeight !== $aspectOriginalWeight,
                    'standard_rating' => $aspectRating,
                    'original_rating' => $aspectOriginalRating,
                    'is_rating_adjusted' => abs($aspectRating - $aspectOriginalRating) > 0.001,
                    'score' => $aspectScore,
                    'attribute_count' => 1,
                    'average_rating' => $aspectRating,
                    'description' => $aspect->description,
                ];

                // For chart data - each ACTIVE aspect is a point on the chart
                $this->chartData['labels'][] = $aspect->name;
                $this->chartData['ratings'][] = $aspectRating;
                $this->chartData['scores'][] = $aspectScore;

                // Track maximum score for dynamic chart scaling
                $this->maxScore = max($this->maxScore, $aspectScore);

                $categoryAspectCount++;
                $categoryWeightSum += $aspectWeight;
                $categoryStandardRatingSum += $aspectRating;
                $categoryScoreSum += $aspectScore;
                $totalAspectRatingSum += $aspectRating; // Track sum of aspect ratings
            }

            // Calculate category average rating
            $categoryAvgRating = $categoryAspectCount > 0
                ? round($categoryStandardRatingSum / $categoryAspectCount, 2)
                : 0.0;

            $this->categoryData[] = [
                'name' => $category->name,
                'code' => $category->code,
                'weight_percentage' => $categoryWeight,
                'original_weight' => $categoryOriginalWeight,
                'is_weight_adjusted' => $categoryWeight !== $categoryOriginalWeight,
                'attribute_count' => $categoryAspectCount,
                'standard_rating' => $categoryAvgRating,
                'score' => round($categoryScoreSum, 2),
                'aspects' => $aspectsData,
            ];

            $totalAspects += $categoryAspectCount;
            $totalWeight += $categoryWeightSum;
            $totalStandardRatingSum += $categoryStandardRatingSum;
            $totalScore += $categoryScoreSum;
        }

        $this->totals = [
            'total_aspects' => $totalAspects,
            'total_weight' => round($totalWeight, 2),
            'total_standard_rating_sum' => round($totalStandardRatingSum, 2),
            'total_rating_sum' => round($totalAspectRatingSum, 2), // Sum of aspect average ratings
            'total_score' => round($totalScore, 2),
        ];

        // OPTIMIZED: Cache the results
        $this->categoryDataCache = $this->categoryData;
        $this->chartDataCache = $this->chartData;
        $this->totalsCache = $this->totals;
        $this->maxScoreCache = $this->maxScore;
    }

    public function render()
    {
        // dd($this->categoryData);

        return view('livewire.pages.general-report.standard-mc');
    }
}
