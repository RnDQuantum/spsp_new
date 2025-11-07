<?php

namespace App\Livewire\Pages\GeneralReport;

use App\Models\AssessmentEvent;
use App\Models\AssessmentTemplate;
use App\Models\CategoryType;
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
    }

    public function handleEventSelected(?string $eventCode): void
    {
        // Event changed, position will be auto-reset by PositionSelector
        // Wait for position to be selected before updating chart
    }

    public function handlePositionSelected(?int $positionFormationId): void
    {
        // Position selected, now we have both event and position
        // Load data with the new filters
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
     * PHASE 2C: Reset adjustments for Kompetensi category only
     */
    public function resetAdjustments(): void
    {
        if (! $this->selectedTemplate) {
            return;
        }

        app(DynamicStandardService::class)->resetCategoryAdjustments($this->selectedTemplate->id, 'kompetensi');
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
     * PHASE 2C: Load standard data with DynamicStandardService integration
     */
    private function loadStandardData(): void
    {
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

        // Load ONLY Kompetensi category type with aspects from selected position's template
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

            // PHASE 2C: Get adjusted category weight
            $dynamicService = app(DynamicStandardService::class);
            $categoryWeight = $dynamicService->getCategoryWeight($templateId, $category->code);
            $categoryOriginalWeight = $category->weight_percentage;

            foreach ($category->aspects as $aspect) {
                // PHASE 2C: Check if aspect is active
                if (! $dynamicService->isAspectActive($templateId, $aspect->code)) {
                    continue; // Skip inactive aspects
                }

                // PHASE 2C: Get adjusted aspect weight
                $aspectWeight = $dynamicService->getAspectWeight($templateId, $aspect->code);
                $aspectOriginalWeight = $aspect->weight_percentage;

                // PHASE 2C: Get adjusted aspect rating (Kompetensi - direct rating)
                $aspectRating = $dynamicService->getAspectRating($templateId, $aspect->code);
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
                'score' => $categoryScoreSum,
                'aspects' => $aspectsData,
            ];

            $totalAspects += $categoryAspectCount;
            $totalWeight += $categoryWeightSum;
            $totalStandardRatingSum += $categoryStandardRatingSum;
            $totalScore += $categoryScoreSum;
        }

        $this->totals = [
            'total_aspects' => $totalAspects,
            'total_weight' => $totalWeight,
            'total_standard_rating_sum' => round($totalStandardRatingSum, 2),
            'total_rating_sum' => round($totalAspectRatingSum, 2), // Sum of aspect average ratings
            'total_score' => round($totalScore, 2),
        ];
    }

    public function render()
    {
        // dd($this->categoryData);

        return view('livewire.pages.general-report.standard-mc');
    }
}
