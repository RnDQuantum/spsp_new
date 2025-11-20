<?php

namespace App\Concerns;

use App\Models\AssessmentTemplate;
use Livewire\Attributes\Computed;

trait ManagesCustomStandardForm
{
    // Form fields
    public string $code = '';

    public string $name = '';

    public string $description = '';

    // Template data
    public array $categoryWeights = [];

    public array $aspectConfigs = [];

    public array $subAspectConfigs = [];

    // For display
    public array $potensiAspects = [];

    public array $kompetensiAspects = [];

    protected function loadAspectsForDisplay(int $templateId): void
    {
        $template = AssessmentTemplate::with([
            'categoryTypes.aspects.subAspects',
        ])->find($templateId);

        if (! $template) {
            return;
        }

        $this->potensiAspects = [];
        $this->kompetensiAspects = [];

        foreach ($template->categoryTypes as $category) {
            $aspects = $category->aspects->map(function ($aspect) use ($category) {
                $aspectData = [
                    'id' => $aspect->id,
                    'code' => $aspect->code,
                    'name' => $aspect->name,
                    'sub_aspects' => [],
                ];

                if ($category->code === 'potensi') {
                    $aspectData['sub_aspects'] = $aspect->subAspects->map(function ($subAspect) {
                        return [
                            'id' => $subAspect->id,
                            'code' => $subAspect->code,
                            'name' => $subAspect->name,
                        ];
                    })->toArray();
                }

                return $aspectData;
            })->toArray();

            if ($category->code === 'potensi') {
                $this->potensiAspects = $aspects;
            } else {
                $this->kompetensiAspects = $aspects;
            }
        }
    }

    protected function validateCategoryWeights(): bool
    {
        $totalCategoryWeight = array_sum($this->categoryWeights);
        if ($totalCategoryWeight !== 100) {
            $this->addError('categoryWeights', "Total bobot kategori harus 100% (saat ini: {$totalCategoryWeight}%)");

            return false;
        }

        return true;
    }

    // ============================================
    // Computed Properties for Real-time Validation
    // ============================================

    #[Computed]
    public function totalCategoryWeight(): int
    {
        return ($this->categoryWeights['potensi'] ?? 0) + ($this->categoryWeights['kompetensi'] ?? 0);
    }

    #[Computed]
    public function activePotensiAspectsCount(): int
    {
        return count(array_filter($this->aspectConfigs, function ($config, $code) {
            $aspect = collect($this->potensiAspects)->firstWhere('code', $code);

            return $aspect && ($config['active'] ?? false);
        }, ARRAY_FILTER_USE_BOTH));
    }

    #[Computed]
    public function activeKompetensiAspectsCount(): int
    {
        return count(array_filter($this->aspectConfigs, function ($config, $code) {
            $aspect = collect($this->kompetensiAspects)->firstWhere('code', $code);

            return $aspect && ($config['active'] ?? false);
        }, ARRAY_FILTER_USE_BOTH));
    }

    #[Computed]
    public function potensiAspectsWeightTotal(): int
    {
        $total = 0;
        foreach ($this->aspectConfigs as $code => $config) {
            $aspect = collect($this->potensiAspects)->firstWhere('code', $code);
            if ($aspect && ($config['active'] ?? false)) {
                $total += (int) ($config['weight'] ?? 0);
            }
        }

        return $total;
    }

    #[Computed]
    public function kompetensiAspectsWeightTotal(): int
    {
        $total = 0;
        foreach ($this->aspectConfigs as $code => $config) {
            $aspect = collect($this->kompetensiAspects)->firstWhere('code', $code);
            if ($aspect && ($config['active'] ?? false)) {
                $total += (int) ($config['weight'] ?? 0);
            }
        }

        return $total;
    }

    #[Computed]
    public function validationResult(): array
    {
        $errors = [];

        // Check category weights total
        if ($this->totalCategoryWeight !== 100) {
            $errors[] = 'Total bobot kategori harus 100% (saat ini: '.$this->totalCategoryWeight.'%)';
        }

        // Check minimum active aspects for Potensi
        if ($this->activePotensiAspectsCount < 3) {
            $errors[] = 'Minimal 3 aspek Potensi harus aktif (saat ini: '.$this->activePotensiAspectsCount.')';
        }

        // Check minimum active aspects for Kompetensi
        if ($this->activeKompetensiAspectsCount < 3) {
            $errors[] = 'Minimal 3 aspek Kompetensi harus aktif (saat ini: '.$this->activeKompetensiAspectsCount.')';
        }

        // Check Potensi aspects weight total
        if ($this->activePotensiAspectsCount > 0 && $this->potensiAspectsWeightTotal !== 100) {
            $errors[] = 'Total bobot aspek Potensi harus 100% (saat ini: '.$this->potensiAspectsWeightTotal.'%)';
        }

        // Check Kompetensi aspects weight total
        if ($this->activeKompetensiAspectsCount > 0 && $this->kompetensiAspectsWeightTotal !== 100) {
            $errors[] = 'Total bobot aspek Kompetensi harus 100% (saat ini: '.$this->kompetensiAspectsWeightTotal.'%)';
        }

        // Check each active Potensi aspect has at least 1 active sub-aspect
        foreach ($this->aspectConfigs as $code => $config) {
            $aspect = collect($this->potensiAspects)->firstWhere('code', $code);
            if ($aspect && ($config['active'] ?? false)) {
                $activeSubCount = 0;
                foreach ($aspect['sub_aspects'] ?? [] as $subAspect) {
                    if ($this->subAspectConfigs[$subAspect['code']]['active'] ?? false) {
                        $activeSubCount++;
                    }
                }
                if ($activeSubCount === 0 && count($aspect['sub_aspects'] ?? []) > 0) {
                    $errors[] = "Aspek {$aspect['name']} harus memiliki minimal 1 sub-aspek aktif";
                }
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    // ============================================
    // Helper Actions
    // ============================================

    public function autoDistributePotensiWeights(): void
    {
        $activeAspects = array_filter($this->aspectConfigs, function ($config, $code) {
            $aspect = collect($this->potensiAspects)->firstWhere('code', $code);

            return $aspect && ($config['active'] ?? false);
        }, ARRAY_FILTER_USE_BOTH);

        $count = count($activeAspects);
        if ($count > 0) {
            $weightPerAspect = intval(100 / $count);
            $remainder = 100 - ($weightPerAspect * $count);

            $index = 0;
            foreach ($this->aspectConfigs as $code => $config) {
                $aspect = collect($this->potensiAspects)->firstWhere('code', $code);
                if ($aspect && ($config['active'] ?? false)) {
                    $this->aspectConfigs[$code]['weight'] = $weightPerAspect + ($index === 0 ? $remainder : 0);
                    $index++;
                }
            }
        }
    }

    public function autoDistributeKompetensiWeights(): void
    {
        $activeAspects = array_filter($this->aspectConfigs, function ($config, $code) {
            $aspect = collect($this->kompetensiAspects)->firstWhere('code', $code);

            return $aspect && ($config['active'] ?? false);
        }, ARRAY_FILTER_USE_BOTH);

        $count = count($activeAspects);
        if ($count > 0) {
            $weightPerAspect = intval(100 / $count);
            $remainder = 100 - ($weightPerAspect * $count);

            $index = 0;
            foreach ($this->aspectConfigs as $code => $config) {
                $aspect = collect($this->kompetensiAspects)->firstWhere('code', $code);
                if ($aspect && ($config['active'] ?? false)) {
                    $this->aspectConfigs[$code]['weight'] = $weightPerAspect + ($index === 0 ? $remainder : 0);
                    $index++;
                }
            }
        }
    }

    public function selectAllPotensiAspects(): void
    {
        foreach ($this->potensiAspects as $aspect) {
            $this->aspectConfigs[$aspect['code']]['active'] = true;

            // Select all sub-aspects too
            foreach ($aspect['sub_aspects'] ?? [] as $subAspect) {
                $this->subAspectConfigs[$subAspect['code']]['active'] = true;
            }
        }
        $this->autoDistributePotensiWeights();
    }

    public function deselectAllPotensiAspects(): void
    {
        foreach ($this->potensiAspects as $aspect) {
            $this->aspectConfigs[$aspect['code']]['active'] = false;
            $this->aspectConfigs[$aspect['code']]['weight'] = 0;

            // Deselect all sub-aspects too
            foreach ($aspect['sub_aspects'] ?? [] as $subAspect) {
                $this->subAspectConfigs[$subAspect['code']]['active'] = false;
            }
        }
    }

    public function selectAllKompetensiAspects(): void
    {
        foreach ($this->kompetensiAspects as $aspect) {
            $this->aspectConfigs[$aspect['code']]['active'] = true;
        }
        $this->autoDistributeKompetensiWeights();
    }

    public function deselectAllKompetensiAspects(): void
    {
        foreach ($this->kompetensiAspects as $aspect) {
            $this->aspectConfigs[$aspect['code']]['active'] = false;
            $this->aspectConfigs[$aspect['code']]['weight'] = 0;
        }
    }

    public function updatedAspectConfigs($value, $key): void
    {
        // Parse the key to get aspect code and field
        // Key format: "aspectCode.active" or "aspectCode.weight"
        $parts = explode('.', $key);
        if (count($parts) !== 2) {
            return;
        }

        [$aspectCode, $field] = $parts;

        // If unchecked, auto-uncheck sub-aspects and set weight to 0
        if ($field === 'active' && ! $value) {
            $aspect = collect($this->potensiAspects)->firstWhere('code', $aspectCode);
            if ($aspect) {
                foreach ($aspect['sub_aspects'] ?? [] as $subAspect) {
                    $this->subAspectConfigs[$subAspect['code']]['active'] = false;
                }
            }
            $this->aspectConfigs[$aspectCode]['weight'] = 0;
        }

        // If checked and weight is 0, try to set a reasonable default
        if ($field === 'active' && $value && ($this->aspectConfigs[$aspectCode]['weight'] ?? 0) === 0) {
            $aspect = collect($this->potensiAspects)->firstWhere('code', $aspectCode);
            if ($aspect) {
                $activeCount = $this->activePotensiAspectsCount;
                if ($activeCount > 0) {
                    $this->aspectConfigs[$aspectCode]['weight'] = intval(100 / $activeCount);
                }

                // Auto-check first sub-aspect if none selected
                if (count($aspect['sub_aspects'] ?? []) > 0) {
                    $hasActiveSubAspect = false;
                    foreach ($aspect['sub_aspects'] ?? [] as $subAspect) {
                        if ($this->subAspectConfigs[$subAspect['code']]['active'] ?? false) {
                            $hasActiveSubAspect = true;
                            break;
                        }
                    }
                    if (! $hasActiveSubAspect) {
                        $firstSubAspect = $aspect['sub_aspects'][0] ?? null;
                        if ($firstSubAspect) {
                            $this->subAspectConfigs[$firstSubAspect['code']]['active'] = true;
                        }
                    }
                }
            } else {
                $aspect = collect($this->kompetensiAspects)->firstWhere('code', $aspectCode);
                if ($aspect) {
                    $activeCount = $this->activeKompetensiAspectsCount;
                    if ($activeCount > 0) {
                        $this->aspectConfigs[$aspectCode]['weight'] = intval(100 / $activeCount);
                    }
                }
            }
        }
    }
}
