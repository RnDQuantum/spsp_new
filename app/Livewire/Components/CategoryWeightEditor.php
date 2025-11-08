<?php

namespace App\Livewire\Components;

use App\Models\CategoryType;
use App\Services\DynamicStandardService;
use Livewire\Component;

class CategoryWeightEditor extends Component
{
    public ?int $templateId = null;

    public string $categoryCode1 = '';

    public string $categoryCode2 = '';

    public bool $showModal = false;

    public int $editingWeight1 = 0;

    public int $editingWeight2 = 0;

    public int $originalWeight1 = 0;

    public int $originalWeight2 = 0;

    public string $categoryName1 = '';

    public string $categoryName2 = '';

    protected $listeners = [
        'open-category-weight-editor' => 'openModal',
        'standard-adjusted' => 'refresh',
    ];

    public function mount(?int $templateId = null, string $categoryCode1 = 'potensi', string $categoryCode2 = 'kompetensi'): void
    {
        $this->templateId = $templateId;
        $this->categoryCode1 = $categoryCode1;
        $this->categoryCode2 = $categoryCode2;
    }

    /**
     * Open modal to edit both category weights
     */
    public function openModal(?int $templateId = null): void
    {
        // Allow override template ID from event
        if ($templateId) {
            $this->templateId = $templateId;
        }

        if (! $this->templateId) {
            return;
        }

        $dynamicService = app(DynamicStandardService::class);

        // Get current weights (adjusted or original)
        $this->editingWeight1 = $dynamicService->getCategoryWeight(
            $this->templateId,
            $this->categoryCode1
        );
        $this->editingWeight2 = $dynamicService->getCategoryWeight(
            $this->templateId,
            $this->categoryCode2
        );

        // Get original weights and names from database
        $category1 = CategoryType::where('template_id', $this->templateId)
            ->where('code', $this->categoryCode1)
            ->first();
        $category2 = CategoryType::where('template_id', $this->templateId)
            ->where('code', $this->categoryCode2)
            ->first();

        $this->originalWeight1 = $category1?->weight_percentage ?? 50;
        $this->originalWeight2 = $category2?->weight_percentage ?? 50;
        $this->categoryName1 = $category1?->name ?? ucfirst($this->categoryCode1);
        $this->categoryName2 = $category2?->name ?? ucfirst($this->categoryCode2);

        $this->showModal = true;
    }

    /**
     * Save both category weights with validation
     */
    public function saveWeights(): void
    {
        if (! $this->templateId) {
            return;
        }

        // Validate total is 100
        $total = $this->editingWeight1 + $this->editingWeight2;
        if ($total !== 100) {
            return;
        }

        $dynamicService = app(DynamicStandardService::class);

        // Save both category weights
        $dynamicService->saveBothCategoryWeights(
            $this->templateId,
            $this->categoryCode1,
            $this->editingWeight1,
            $this->categoryCode2,
            $this->editingWeight2
        );

        $this->showModal = false;
        $this->dispatch('standard-adjusted', templateId: $this->templateId);
    }

    /**
     * Close modal
     */
    public function closeModal(): void
    {
        $this->showModal = false;
        $this->editingWeight1 = 0;
        $this->editingWeight2 = 0;
        $this->originalWeight1 = 0;
        $this->originalWeight2 = 0;
    }

    /**
     * Get current weights for button display
     */
    public function getCurrentWeights(): array
    {
        if (! $this->templateId) {
            return [
                'weight1' => 50,
                'weight2' => 50,
                'original1' => 50,
                'original2' => 50,
                'name1' => ucfirst($this->categoryCode1),
                'name2' => ucfirst($this->categoryCode2),
                'isAdjusted' => false,
            ];
        }

        $dynamicService = app(DynamicStandardService::class);

        $weight1 = $dynamicService->getCategoryWeight($this->templateId, $this->categoryCode1);
        $weight2 = $dynamicService->getCategoryWeight($this->templateId, $this->categoryCode2);

        $category1 = CategoryType::where('template_id', $this->templateId)
            ->where('code', $this->categoryCode1)
            ->first();
        $category2 = CategoryType::where('template_id', $this->templateId)
            ->where('code', $this->categoryCode2)
            ->first();

        $original1 = $category1?->weight_percentage ?? 50;
        $original2 = $category2?->weight_percentage ?? 50;

        return [
            'weight1' => $weight1,
            'weight2' => $weight2,
            'original1' => $original1,
            'original2' => $original2,
            'name1' => $category1?->name ?? ucfirst($this->categoryCode1),
            'name2' => $category2?->name ?? ucfirst($this->categoryCode2),
            'isAdjusted' => $weight1 !== $original1 || $weight2 !== $original2,
        ];
    }

    public function render()
    {
        $weights = $this->getCurrentWeights();

        return view('livewire.components.category-weight-editor', [
            'weights' => $weights,
        ]);
    }
}
