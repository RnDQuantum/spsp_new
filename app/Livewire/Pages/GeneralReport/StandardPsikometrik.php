<?php

namespace App\Livewire\Pages\GeneralReport;

use App\Models\AssessmentEvent;
use App\Models\AssessmentTemplate;
use App\Models\CategoryType;
use App\Services\DynamicStandardService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app', ['title' => 'Standar Pemetaan Potensi Individu'])]
class StandardPsikometrik extends Component
{
    protected $listeners = [
        'event-selected' => 'handleEventSelected',
        'position-selected' => 'handlePositionSelected',
        'standard-adjusted' => 'handleStandardUpdate',
        'handleStandardUpdate' => 'handleStandardUpdate',
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

    public int $maxScore = 0;

    // Unique chart ID
    public string $chartId = '';

    // CACHE PROPERTIES - untuk menyimpan hasil kalkulasi
    private ?array $categoryDataCache = null;

    private ?array $chartDataCache = null;

    private ?array $totalsCache = null;

    private ?int $maxScoreCache = null;

    // PHASE 2C: Modal states for inline editing
    public bool $showEditRatingModal = false;

    public bool $showEditCategoryWeightModal = false;

    public bool $showEditCategoryWeightsModal = false;

    public string $editingField = '';

    public int|float|null $editingValue = null;

    public int|float|null $editingOriginalValue = null;

    // Category weights editing
    public int $editingPotensiWeight = 0;

    public int $editingKompetensiWeight = 0;

    public int $originalPotensiWeight = 0;

    public int $originalKompetensiWeight = 0;

    public function mount(): void
    {
        // Generate unique chart ID
        $this->chartId = 'standardPsikometrik'.uniqid();

        $this->loadStandardData();
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
     * PHASE 2C: Open modal to edit sub-aspect rating
     */
    public function openEditSubAspectRating(string $subAspectCode, int $currentRating): void
    {
        if (! $this->selectedTemplate) {
            return;
        }

        $this->editingField = $subAspectCode;
        $this->editingValue = $currentRating;
        $this->editingOriginalValue = \App\Models\SubAspect::whereHas('aspect', function ($query) {
            $query->where('template_id', $this->selectedTemplate->id);
        })->where('code', $subAspectCode)->first()?->standard_rating ?? $currentRating;
        $this->showEditRatingModal = true;
    }

    /**
     * PHASE 2C: Save sub-aspect rating adjustment
     */
    public function saveSubAspectRating(): void
    {
        if (! $this->selectedTemplate || ! is_int($this->editingValue)) {
            return;
        }

        app(DynamicStandardService::class)->saveSubAspectRating(
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

        $this->dispatch('openSelectionModal', templateId: $this->selectedTemplate->id, categoryCode: 'potensi');
    }

    /**
     * PHASE 2C: Reset adjustments (both category weights + Potensi aspects)
     */
    public function resetAdjustments(): void
    {
        if (! $this->selectedTemplate) {
            return;
        }

        $dynamicService = app(DynamicStandardService::class);

        // Reset Potensi category adjustments (aspects, sub-aspects, ratings)
        $dynamicService->resetCategoryAdjustments($this->selectedTemplate->id, 'potensi');

        // Reset both category weights
        $dynamicService->resetCategoryWeights($this->selectedTemplate->id);

        $this->dispatch('standard-adjusted', templateId: $this->selectedTemplate->id);
    }

    /**
     * PHASE 2C: Close modals
     */
    public function closeModal(): void
    {
        $this->showEditRatingModal = false;
        $this->showEditCategoryWeightModal = false;
        $this->editingField = '';
        $this->editingValue = null;
        $this->editingOriginalValue = null;
    }

    /**
     * Open modal to edit both category weights (Potensi + Kompetensi)
     */
    public function openEditCategoryWeights(): void
    {
        if (! $this->selectedTemplate) {
            return;
        }

        $dynamicService = app(DynamicStandardService::class);

        // Get current weights (adjusted or original)
        $this->editingPotensiWeight = $dynamicService->getCategoryWeight(
            $this->selectedTemplate->id,
            'potensi'
        );
        $this->editingKompetensiWeight = $dynamicService->getCategoryWeight(
            $this->selectedTemplate->id,
            'kompetensi'
        );

        // Get original weights from database
        $potensiCategory = CategoryType::where('template_id', $this->selectedTemplate->id)
            ->where('code', 'potensi')
            ->first();
        $kompetensiCategory = CategoryType::where('template_id', $this->selectedTemplate->id)
            ->where('code', 'kompetensi')
            ->first();

        $this->originalPotensiWeight = $potensiCategory?->weight_percentage ?? 50;
        $this->originalKompetensiWeight = $kompetensiCategory?->weight_percentage ?? 50;

        $this->showEditCategoryWeightsModal = true;
    }

    /**
     * Save both category weights with validation
     */
    public function saveCategoryWeights(): void
    {
        if (! $this->selectedTemplate) {
            return;
        }

        // Validate total is 100
        $total = $this->editingPotensiWeight + $this->editingKompetensiWeight;
        if ($total !== 100) {
            return;
        }

        $dynamicService = app(DynamicStandardService::class);

        // Save both category weights
        $dynamicService->saveBothCategoryWeights(
            $this->selectedTemplate->id,
            'potensi',
            $this->editingPotensiWeight,
            'kompetensi',
            $this->editingKompetensiWeight
        );

        $this->showEditCategoryWeightsModal = false;
        $this->dispatch('standard-adjusted', templateId: $this->selectedTemplate->id);
    }

    /**
     * Close category weights modal
     */
    public function closeCategoryWeightsModal(): void
    {
        $this->showEditCategoryWeightsModal = false;
        $this->editingPotensiWeight = 0;
        $this->editingKompetensiWeight = 0;
        $this->originalPotensiWeight = 0;
        $this->originalKompetensiWeight = 0;
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
        $hasAdjustments = $dynamicService->hasCategoryAdjustments($templateId, 'potensi');

        // Load ONLY Potensi category type with aspects and sub-aspects from selected position's template
        // OPTIMIZED: Eager load all relationships in one query
        $categories = CategoryType::query()
            ->where('template_id', $templateId)
            ->where('code', 'potensi')
            ->with([
                'aspects' => fn ($q) => $q->orderBy('order'),
                'aspects.subAspects' => fn ($q) => $q->orderBy('order'),
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
            $categoryAspectsCount = 0;
            $categoryWeightSum = 0;
            $categoryStandardRatingSum = 0.0;
            $categoryScoreSum = 0.0;
            $categorySubAspectStandardSum = 0.0;

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

                $subAspectsData = [];
                $activeSubAspectsCount = 0;
                $activeSubAspectsStandardSum = 0;

                foreach ($aspect->subAspects as $subAspect) {
                    // OPTIMIZED: Check if sub-aspect is active (only if has adjustments)
                    if ($hasAdjustments && ! $dynamicService->isSubAspectActive($templateId, $subAspect->code)) {
                        continue; // Skip inactive sub-aspects
                    }

                    // OPTIMIZED: Get adjusted sub-aspect rating (only if has adjustments)
                    $subAspectRating = $hasAdjustments
                        ? $dynamicService->getSubAspectRating($templateId, $subAspect->code)
                        : (int) $subAspect->standard_rating;
                    $subAspectOriginalRating = (int) $subAspect->standard_rating;

                    $subAspectsData[] = [
                        'code' => $subAspect->code,
                        'name' => $subAspect->name,
                        'standard_rating' => $subAspectRating,
                        'original_rating' => $subAspectOriginalRating,
                        'is_adjusted' => $subAspectRating !== $subAspectOriginalRating,
                        'description' => $subAspect->description,
                    ];
                    $activeSubAspectsCount++;
                    $activeSubAspectsStandardSum += $subAspectRating;
                }

                // Calculate aspect average rating from ACTIVE sub-aspects
                $aspectAvgRating = $activeSubAspectsCount > 0
                    ? round($activeSubAspectsStandardSum / $activeSubAspectsCount, 2)
                    : ($hasAdjustments
                        ? $dynamicService->getAspectRating($templateId, $aspect->code)
                        : (float) $aspect->standard_rating);

                // Calculate aspect score: rating × adjusted weight
                $aspectScore = round($aspectAvgRating * $aspectWeight, 2);

                $aspectsData[] = [
                    'code' => $aspect->code,
                    'name' => $aspect->name,
                    'weight_percentage' => $aspectWeight,
                    'original_weight' => $aspectOriginalWeight,
                    'is_weight_adjusted' => $aspectWeight !== $aspectOriginalWeight,
                    'standard_rating' => $aspectAvgRating,
                    'sub_aspects_count' => $activeSubAspectsCount,
                    'sub_aspects' => $subAspectsData,
                    'sub_aspects_standard_sum' => $activeSubAspectsStandardSum,
                    'score' => $aspectScore,
                ];

                // For chart data - each ACTIVE aspect is a point on the chart
                $this->chartData['labels'][] = $aspect->name;
                $this->chartData['ratings'][] = $aspectAvgRating;
                $this->chartData['scores'][] = $aspectScore;

                // Track maximum score for dynamic chart scaling
                $this->maxScore = max($this->maxScore, $aspectScore);

                $categoryAspectsCount += $activeSubAspectsCount;
                $categoryWeightSum += $aspectWeight;
                $categoryStandardRatingSum += $aspectAvgRating;
                $categoryScoreSum += $aspectScore;
                $categorySubAspectStandardSum += $activeSubAspectsStandardSum;
                $totalAspectRatingSum += $aspectAvgRating; // Track sum of aspect ratings
            }

            // Calculate category average rating
            $categoryAspectCount = count($aspectsData);
            $categoryAvgRating = $categoryAspectCount > 0
                ? round($categoryStandardRatingSum / $categoryAspectCount, 2)
                : 0.0;

            $this->categoryData[] = [
                'name' => $category->name,
                'code' => $category->code,
                'weight_percentage' => $categoryWeight,
                'original_weight' => $categoryOriginalWeight,
                'is_weight_adjusted' => $categoryWeight !== $categoryOriginalWeight,
                'aspects_count' => $categoryAspectsCount,
                'standard_rating' => $categoryAvgRating,
                'score' => $categoryScoreSum,
                'aspects' => $aspectsData,
            ];

            $totalAspects += $categoryAspectsCount;
            $totalWeight += $categoryWeightSum;
            $totalStandardRatingSum += $categorySubAspectStandardSum;
            $totalScore += $categoryScoreSum;
        }

        $this->totals = [
            'total_aspects' => $totalAspects,
            'total_weight' => $totalWeight,
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
        return view('livewire.pages.general-report.standard-psikometrik');
    }
}
