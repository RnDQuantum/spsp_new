<?php

declare(strict_types=1);

namespace App\Livewire\Components;

use App\Models\AssessmentTemplate;
use App\Services\DynamicStandardService;
use Livewire\Attributes\Computed;
use Livewire\Component;

class SelectiveAspectsModal extends Component
{
    public int $templateId;

    public string $categoryCode;

    public bool $show = false;

    // Selected aspects and sub-aspects
    public array $selectedAspects = [];

    public array $selectedSubAspects = [];

    // Aspect weights (adjusted)
    public array $aspectWeights = [];

    // Expanded aspects (for tree UI)
    public array $expandedAspects = [];

    protected $listeners = [
        'openSelectionModal' => 'open',
    ];

    /**
     * Open modal and load data
     */
    public function open(int $templateId, string $categoryCode): void
    {
        $this->templateId = $templateId;
        $this->categoryCode = $categoryCode;

        $this->loadSelectionData();

        $this->show = true;
    }

    /**
     * Load selection data from session and template
     */
    private function loadSelectionData(): void
    {
        $dynamicService = app(DynamicStandardService::class);
        $template = AssessmentTemplate::with([
            'aspects' => fn ($q) => $q->where('category_type_id', function ($query) {
                $query->select('id')
                    ->from('category_types')
                    ->where('template_id', $this->templateId)
                    ->where('code', $this->categoryCode)
                    ->limit(1);
            })->orderBy('order'),
            'aspects.subAspects' => fn ($q) => $q->orderBy('order'),
        ])->findOrFail($this->templateId);

        // Initialize selection state from session (or default to all active)
        foreach ($template->aspects as $aspect) {
            $isActive = $dynamicService->isAspectActive($this->templateId, $aspect->code);
            $this->selectedAspects[$aspect->code] = $isActive;
            $this->aspectWeights[$aspect->code] = $dynamicService->getAspectWeight($this->templateId, $aspect->code);

            // Initialize sub-aspects (for Potensi only)
            if ($this->categoryCode === 'potensi') {
                foreach ($aspect->subAspects as $subAspect) {
                    $this->selectedSubAspects[$aspect->code][$subAspect->code] = $dynamicService->isSubAspectActive(
                        $this->templateId,
                        $subAspect->code
                    );
                }
            }

            // Expand all aspects by default
            $this->expandedAspects[$aspect->code] = true;
        }
    }

    /**
     * Toggle aspect active/inactive
     */
    public function toggleAspect(string $aspectCode): void
    {
        $this->selectedAspects[$aspectCode] = ! $this->selectedAspects[$aspectCode];

        // If unchecked, auto-uncheck all sub-aspects and set weight to 0
        if (! $this->selectedAspects[$aspectCode]) {
            if (isset($this->selectedSubAspects[$aspectCode])) {
                foreach ($this->selectedSubAspects[$aspectCode] as $subCode => $val) {
                    $this->selectedSubAspects[$aspectCode][$subCode] = false;
                }
            }
            $this->aspectWeights[$aspectCode] = 0;
        }
    }

    /**
     * Toggle sub-aspect active/inactive
     */
    public function toggleSubAspect(string $aspectCode, string $subAspectCode): void
    {
        $this->selectedSubAspects[$aspectCode][$subAspectCode] =
            ! $this->selectedSubAspects[$aspectCode][$subAspectCode];
    }

    /**
     * Toggle aspect expansion in tree UI
     */
    public function toggleExpand(string $aspectCode): void
    {
        $this->expandedAspects[$aspectCode] = ! ($this->expandedAspects[$aspectCode] ?? false);
    }

    /**
     * Select all aspects
     */
    public function selectAll(): void
    {
        foreach ($this->selectedAspects as $code => $value) {
            $this->selectedAspects[$code] = true;

            // Re-enable sub-aspects
            if (isset($this->selectedSubAspects[$code])) {
                foreach ($this->selectedSubAspects[$code] as $subCode => $subValue) {
                    $this->selectedSubAspects[$code][$subCode] = true;
                }
            }
        }
    }

    /**
     * Deselect all aspects
     */
    public function deselectAll(): void
    {
        foreach ($this->selectedAspects as $code => $value) {
            $this->selectedAspects[$code] = false;
            $this->aspectWeights[$code] = 0;

            // Disable sub-aspects
            if (isset($this->selectedSubAspects[$code])) {
                foreach ($this->selectedSubAspects[$code] as $subCode => $subValue) {
                    $this->selectedSubAspects[$code][$subCode] = false;
                }
            }
        }
    }

    /**
     * Update weight for an aspect
     */
    public function updateWeight(string $aspectCode, int $weight): void
    {
        $this->aspectWeights[$aspectCode] = $weight;
    }

    /**
     * Apply selection to session
     */
    public function applySelection(): void
    {
        $validation = $this->validationResult;

        if (! $validation['valid']) {
            return; // Keep modal open, validation errors will show
        }

        // Save to session via DynamicStandardService
        $dynamicService = app(DynamicStandardService::class);

        $dataToSave = [
            'active_aspects' => $this->selectedAspects,
            'aspect_weights' => $this->aspectWeights,
        ];

        if ($this->categoryCode === 'potensi') {
            $dataToSave['active_sub_aspects'] = [];
            foreach ($this->selectedSubAspects as $aspectCode => $subAspects) {
                foreach ($subAspects as $subCode => $isActive) {
                    $dataToSave['active_sub_aspects'][$subCode] = $isActive;
                }
            }
        }

        $dynamicService->saveBulkSelection($this->templateId, $dataToSave);

        // Dispatch event for other components to refresh
        $this->dispatch('standard-adjusted', templateId: $this->templateId);

        // Close modal
        $this->show = false;
    }

    /**
     * Close modal without saving
     */
    public function close(): void
    {
        $this->show = false;
    }

    /**
     * Get template aspects (computed)
     */
    #[Computed]
    public function templateAspects()
    {
        return AssessmentTemplate::with([
            'aspects' => fn ($q) => $q->where('category_type_id', function ($query) {
                $query->select('id')
                    ->from('category_types')
                    ->where('template_id', $this->templateId)
                    ->where('code', $this->categoryCode)
                    ->limit(1);
            })->orderBy('order'),
            'aspects.subAspects' => fn ($q) => $q->orderBy('order'),
        ])->findOrFail($this->templateId)->aspects;
    }

    /**
     * Validation result (computed)
     */
    #[Computed]
    public function validationResult(): array
    {
        $dynamicService = app(DynamicStandardService::class);

        $data = [
            'active_aspects' => $this->selectedAspects,
            'aspect_weights' => $this->aspectWeights,
        ];

        if ($this->categoryCode === 'potensi') {
            $data['active_sub_aspects'] = [];
            foreach ($this->selectedSubAspects as $aspectCode => $subAspects) {
                foreach ($subAspects as $subCode => $isActive) {
                    $data['active_sub_aspects'][$subCode] = $isActive;
                }
            }
        }

        return $dynamicService->validateSelection($this->templateId, $data);
    }

    /**
     * Total weight (computed)
     */
    #[Computed]
    public function totalWeight(): int
    {
        return array_sum($this->aspectWeights);
    }

    /**
     * Count of active aspects (computed)
     */
    #[Computed]
    public function activeAspectsCount(): int
    {
        return count(array_filter($this->selectedAspects, fn ($active) => $active === true));
    }

    /**
     * Total aspects count (computed)
     */
    #[Computed]
    public function totalAspectsCount(): int
    {
        return count($this->selectedAspects);
    }

    public function render()
    {
        return view('livewire.components.selective-aspects-modal');
    }
}
