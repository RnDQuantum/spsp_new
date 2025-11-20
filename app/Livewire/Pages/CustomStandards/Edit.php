<?php

namespace App\Livewire\Pages\CustomStandards;

use App\Models\AssessmentTemplate;
use App\Models\CustomStandard;
use App\Services\CustomStandardService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app', ['title' => 'Edit Custom Standard'])]
class Edit extends Component
{
    public CustomStandard $customStandard;

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

    public function mount(CustomStandard $customStandard): void
    {
        $this->authorize('update', $customStandard);

        $this->customStandard = $customStandard;
        $this->code = $customStandard->code;
        $this->name = $customStandard->name;
        $this->description = $customStandard->description ?? '';
        $this->categoryWeights = $customStandard->category_weights;
        $this->aspectConfigs = $customStandard->aspect_configs;
        $this->subAspectConfigs = $customStandard->sub_aspect_configs;

        $this->loadAspectsForDisplay();
    }

    private function loadAspectsForDisplay(): void
    {
        $template = AssessmentTemplate::with([
            'categoryTypes.aspects.subAspects',
        ])->find($this->customStandard->template_id);

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

    public function save(CustomStandardService $service): mixed
    {
        $this->authorize('update', $this->customStandard);

        // Validate
        $this->validate([
            'code' => 'required|string|max:255',
            'name' => 'required|string|max:255',
        ]);

        // Check code uniqueness (exclude current)
        if (! $service->isCodeUnique($this->customStandard->institution_id, $this->code, $this->customStandard->id)) {
            $this->addError('code', 'Kode sudah digunakan.');

            return null;
        }

        // Validate category weights sum
        $totalCategoryWeight = array_sum($this->categoryWeights);
        if ($totalCategoryWeight !== 100) {
            $this->addError('categoryWeights', "Total bobot kategori harus 100% (saat ini: {$totalCategoryWeight}%)");

            return null;
        }

        // Update
        $service->update($this->customStandard, [
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description,
            'category_weights' => $this->categoryWeights,
            'aspect_configs' => $this->aspectConfigs,
            'sub_aspect_configs' => $this->subAspectConfigs,
        ]);

        session()->flash('success', 'Custom standard berhasil diperbarui.');

        return $this->redirect(route('custom-standards.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.pages.custom-standards.edit');
    }
}
