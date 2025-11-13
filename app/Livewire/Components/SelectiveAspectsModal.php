<?php

declare(strict_types=1);

namespace App\Livewire\Components;

use App\Models\AssessmentTemplate;
use App\Services\DynamicStandardService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
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

    /**
     * Mount component
     */
    public function mount(): void
    {
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
        $this->cachedTemplate = null;
    }

    /**
     * Listen to open modal event
     */
    #[On('openSelectionModal')]
    public function open(int $templateId, string $categoryCode = 'potensi'): void
    {
        // Reset state if different template
        if ($this->templateId !== $templateId || $this->categoryCode !== $categoryCode) {
            $this->resetState();
            $this->templateId = $templateId;
            $this->categoryCode = $categoryCode;
        }

        // Show modal
        $this->show = true;

        // Load data
        $this->loadSelectionData();
    }

    /**
     * Listen to standard adjusted event from other components
     */
    #[On('standard-adjusted')]
    public function handleStandardAdjusted(int $templateId): void
    {
        // Only refresh if same template and modal is open
        if ($this->templateId === $templateId && $this->show) {
            $this->loadSelectionData();
        }
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

            // Initialize selection state from session
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
            if (($this->aspectWeights[$key] ?? 0) === 0) {
                $activeCount = $this->activeAspectsCount;
                if ($activeCount > 0) {
                    $this->aspectWeights[$key] = intval(100 / $activeCount);
                }
            }

            // Auto-check at least one sub-aspect if none selected (for Potensi)
            if ($this->categoryCode === 'potensi' && isset($this->selectedSubAspects[$key])) {
                $hasActiveSubAspect = collect($this->selectedSubAspects[$key])->contains(true);

                if (!$hasActiveSubAspect) {
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
            // Dispatch validation error event for toast
            $this->dispatch('show-validation-error', errors: $validation['errors']);
            return;
        }

        try {
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

            // Show success message
            $this->dispatch('show-success', message: 'Perubahan berhasil diterapkan!');

            // Close modal after short delay (handled by Alpine toast)
            $this->show = false;

            // Notify parent component to refresh data
            $this->dispatch('standard-adjusted', templateId: $this->templateId);
        } catch (\Exception $e) {
            logger()->error('Failed to apply selection: ' . $e->getMessage());
            $this->dispatch('show-validation-error', errors: ['Gagal menyimpan perubahan. Silakan coba lagi.']);
        }
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
        if ($this->totalWeight !== 100 && $this->activeAspectsCount > 0) {
            $errors[] = 'Total bobot harus 100% (saat ini: ' . $this->totalWeight . '%)';
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
                $total += (int) $weight;
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
