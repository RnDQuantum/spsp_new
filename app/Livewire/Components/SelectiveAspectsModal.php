<?php

declare(strict_types=1);

namespace App\Livewire\Components;

use App\Models\AssessmentTemplate;
use App\Services\DynamicStandardService;
use Livewire\Attributes\Computed;
use Livewire\Component;

class SelectiveAspectsModal extends Component
{
    public int $templateId = 0;
    public string $categoryCode = '';
    public bool $show = false;
    public bool $dataLoaded = false;

    // Selected aspects and sub-aspects
    public array $selectedAspects = [];
    public array $selectedSubAspects = [];

    // Aspect weights (adjusted)
    public array $aspectWeights = [];

    // Expanded aspects (for tree UI)
    public array $expandedAspects = [];

    // Cache template data
    private ?object $cachedTemplate = null;

    protected $listeners = [
        'openSelectionModal' => 'open',
        'preloadModalData' => 'preload',
        'standard-adjusted' => 'handleStandardAdjusted',
    ];

    /**
     * Mount component
     */
    public function mount(): void
    {
        // Initialize empty state to prevent errors
        $this->resetState();
    }

    /**
     * Reset component state
     */
    private function resetState(): void
    {
        $this->selectedAspects = [];
        $this->selectedSubAspects = [];
        $this->aspectWeights = [];
        $this->expandedAspects = [];
        $this->dataLoaded = false;
    }

    /**
     * Preload modal data (optional - for better performance)
     */
    public function preload(int $templateId, string $categoryCode = 'potensi'): void
    {
        if ($this->templateId === $templateId && $this->categoryCode === $categoryCode) {
            return; // Already loaded
        }

        $this->templateId = $templateId;
        $this->categoryCode = $categoryCode;
        $this->loadSelectionData();
    }

    /**
     * Open modal and load data
     */
    public function open(int $templateId, string $categoryCode = 'potensi'): void
    {
        // Show modal immediately
        $this->show = true;

        // Always load fresh data when opening modal to ensure state is up-to-date
        // (especially after external changes like reset)
        if (!$this->dataLoaded || $this->templateId !== $templateId || $this->categoryCode !== $categoryCode) {
            $this->templateId = $templateId;
            $this->categoryCode = $categoryCode;
        }

        // Dispatch event to show loading state
        $this->dispatch('modal-loading');

        // Always load data to ensure fresh state
        $this->loadSelectionData();

        // Dispatch event that data is ready
        $this->dispatch('modal-ready');
    }

    /**
     * Load selection data from session and template
     */
    private function loadSelectionData(): void
    {
        if ($this->templateId === 0) {
            return;
        }

        try {
            $dynamicService = app(DynamicStandardService::class);

            // Use eager loading to minimize queries
            $template = AssessmentTemplate::with([
                'aspects' => fn($q) => $q->whereHas('categoryType', function ($query) {
                    $query->where('code', $this->categoryCode);
                })->orderBy('order')->with('subAspects'),
            ])->find($this->templateId);

            if (!$template) {
                $this->dataLoaded = false;
                return;
            }

            // Cache template for computed properties
            $this->cachedTemplate = $template;

            // Initialize selection state from session (or default to all active)
            foreach ($template->aspects as $aspect) {
                $isActive = $dynamicService->isAspectActive($this->templateId, $aspect->code);
                $this->selectedAspects[$aspect->code] = $isActive;
                $this->aspectWeights[$aspect->code] = $dynamicService->getAspectWeight($this->templateId, $aspect->code);

                // Initialize sub-aspects (for Potensi only)
                if ($this->categoryCode === 'potensi' && $aspect->subAspects) {
                    $this->selectedSubAspects[$aspect->code] = [];
                    foreach ($aspect->subAspects as $subAspect) {
                        $this->selectedSubAspects[$aspect->code][$subAspect->code] = $dynamicService->isSubAspectActive(
                            $this->templateId,
                            $subAspect->code
                        );
                    }
                    // Expand by default for better UX
                    $this->expandedAspects[$aspect->code] = true;
                }
            }

            $this->dataLoaded = true;
        } catch (\Exception $e) {
            $this->dataLoaded = false;
            logger()->error('Failed to load modal data: ' . $e->getMessage());
        }
    }

    /**
     * Watch for changes to selectedAspects
     */
    public function updatedSelectedAspects($value, $key): void
    {
        // If unchecked, auto-uncheck all sub-aspects and set weight to 0
        if (!$value) {
            if (isset($this->selectedSubAspects[$key])) {
                foreach ($this->selectedSubAspects[$key] as $subCode => $val) {
                    $this->selectedSubAspects[$key][$subCode] = false;
                }
            }
            $this->aspectWeights[$key] = 0;
        } else {
            // If checked and weight is 0, set a default weight
            if ($this->aspectWeights[$key] === 0) {
                // Distribute weight evenly among active aspects
                $activeCount = $this->activeAspectsCount;
                if ($activeCount > 0) {
                    $defaultWeight = intval(100 / $activeCount);
                    $this->aspectWeights[$key] = $defaultWeight;
                }
            }

            // Auto-check at least one sub-aspect if none selected
            if ($this->categoryCode === 'potensi' && isset($this->selectedSubAspects[$key])) {
                $hasActiveSubAspect = false;
                foreach ($this->selectedSubAspects[$key] as $subCode => $isActive) {
                    if ($isActive) {
                        $hasActiveSubAspect = true;
                        break;
                    }
                }
                if (!$hasActiveSubAspect) {
                    // Auto-select first sub-aspect
                    $firstKey = array_key_first($this->selectedSubAspects[$key]);
                    if ($firstKey) {
                        $this->selectedSubAspects[$key][$firstKey] = true;
                    }
                }
            }
        }
    }

    /**
     * Toggle aspect expansion in tree UI
     */
    public function toggleExpand(string $aspectCode): void
    {
        $this->expandedAspects[$aspectCode] = !($this->expandedAspects[$aspectCode] ?? false);
    }

    /**
     * Select all aspects
     */
    public function selectAll(): void
    {
        foreach ($this->selectedAspects as $code => $value) {
            $this->selectedAspects[$code] = true;

            // Re-enable all sub-aspects
            if (isset($this->selectedSubAspects[$code])) {
                foreach ($this->selectedSubAspects[$code] as $subCode => $subValue) {
                    $this->selectedSubAspects[$code][$subCode] = true;
                }
            }
        }

        // Auto-distribute weights
        $this->autoDistributeWeights();
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
     * Auto-distribute weights evenly among active aspects
     */
    public function autoDistributeWeights(): void
    {
        $activeAspects = array_filter($this->selectedAspects, fn($active) => $active === true);
        $activeCount = count($activeAspects);

        if ($activeCount > 0) {
            $weightPerAspect = intval(100 / $activeCount);
            $remainder = 100 - ($weightPerAspect * $activeCount);

            $index = 0;
            foreach ($this->selectedAspects as $code => $isActive) {
                if ($isActive) {
                    // Add remainder to first aspect to ensure total = 100
                    $this->aspectWeights[$code] = $weightPerAspect + ($index === 0 ? $remainder : 0);
                    $index++;
                }
            }
        }
    }

    /**
     * Apply selection to session
     */
    public function applySelection(): void
    {
        $validation = $this->validationResult;

        if (!$validation['valid']) {
            // Show validation errors via toast or alert
            $this->dispatch('show-validation-error', errors: $validation['errors']);
            return;
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

        // Show success message FIRST before closing
        $this->dispatch('show-success', message: 'Perubahan berhasil diterapkan');

        // Close modal
        $this->show = false;

        // Dispatch event to parent component to refresh data
        // Use dispatch()->to() for targeted dispatch or global dispatch
        $this->dispatch('standard-adjusted', templateId: $this->templateId)->self();

        // Also dispatch directly to parent component
        $this->dispatch('handleStandardUpdate', templateId: $this->templateId);

        // Dispatch browser event to close modal via Alpine
        $this->dispatch('close-modal');
    }

    /**
     * Handle standard adjusted event - refresh modal data
     */
    public function handleStandardAdjusted(int $templateId): void
    {
        // Only refresh if same template
        if ($this->templateId === $templateId) {
            $this->loadSelectionData();
        }
    }

    /**
     * Close modal without saving
     */
    public function close(): void
    {
        $this->show = false;
        // Reset state after small delay to avoid flicker
        $this->dispatch('modal-closed');
    }

    /**
     * Get template aspects (computed)
     */
    #[Computed]
    public function templateAspects()
    {
        if (!$this->dataLoaded || $this->templateId === 0) {
            return collect([]);
        }

        // Use cached template if available
        if ($this->cachedTemplate && $this->cachedTemplate->id === $this->templateId) {
            return $this->cachedTemplate->aspects;
        }

        return AssessmentTemplate::with([
            'aspects' => fn($q) => $q->whereHas('categoryType', function ($query) {
                $query->where('code', $this->categoryCode);
            })->orderBy('order')->with('subAspects'),
        ])->find($this->templateId)?->aspects ?? collect([]);
    }

    /**
     * Validation result (computed)
     */
    #[Computed]
    public function validationResult(): array
    {
        if (!$this->dataLoaded) {
            return ['valid' => false, 'errors' => ['Data belum dimuat']];
        }

        $errors = [];

        // Check minimum active aspects (at least 3)
        if ($this->activeAspectsCount < 3) {
            $errors[] = 'Minimal 3 aspek harus aktif (saat ini: ' . $this->activeAspectsCount . ')';
        }

        // Check total weight must be 100%
        $totalWeight = $this->totalWeight;
        if ($totalWeight !== 100 && $this->activeAspectsCount > 0) {
            $errors[] = 'Total bobot harus 100% (saat ini: ' . $totalWeight . '%)';
        }

        // For Potensi, check each active aspect has at least 1 active sub-aspect
        if ($this->categoryCode === 'potensi') {
            foreach ($this->selectedAspects as $aspectCode => $isActive) {
                if ($isActive && isset($this->selectedSubAspects[$aspectCode])) {
                    $activeSubCount = count(array_filter(
                        $this->selectedSubAspects[$aspectCode],
                        fn($active) => $active === true
                    ));
                    if ($activeSubCount === 0) {
                        $errors[] = "Aspek {$aspectCode} harus memiliki minimal 1 sub-aspek aktif";
                    }
                }
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Total weight (computed)
     */
    #[Computed]
    public function totalWeight(): int
    {
        $total = 0;
        foreach ($this->aspectWeights as $code => $weight) {
            if ($this->selectedAspects[$code] ?? false) {
                $total += $weight;
            }
        }
        return $total;
    }

    /**
     * Count of active aspects (computed)
     */
    #[Computed]
    public function activeAspectsCount(): int
    {
        return count(array_filter($this->selectedAspects, fn($active) => $active === true));
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
