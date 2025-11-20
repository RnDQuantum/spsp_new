<?php

namespace App\Concerns;

use App\Models\AssessmentTemplate;

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
}
