<?php

namespace App\Livewire\Pages\CustomStandards;

use App\Models\AssessmentTemplate;
use App\Services\CustomStandardService;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app', ['title' => 'Buat Custom Standard'])]
class Create extends Component
{
    // Form fields
    public ?int $templateId = null;

    public string $code = '';

    public string $name = '';

    public string $description = '';

    // Template data
    public Collection $templates;

    public array $categoryWeights = [];

    public array $aspectConfigs = [];

    public array $subAspectConfigs = [];

    // For display
    public array $potensiAspects = [];

    public array $kompetensiAspects = [];

    public function mount(): void
    {
        $this->templates = AssessmentTemplate::orderBy('name')->get();
    }

    public function updatedTemplateId(CustomStandardService $service): void
    {
        if (! $this->templateId) {
            $this->resetFormData();

            return;
        }

        $defaults = $service->getTemplateDefaults($this->templateId);

        $this->categoryWeights = $defaults['category_weights'];
        $this->aspectConfigs = $defaults['aspect_configs'];
        $this->subAspectConfigs = $defaults['sub_aspect_configs'];

        $this->loadAspectsForDisplay();
    }

    private function loadAspectsForDisplay(): void
    {
        if (! $this->templateId) {
            return;
        }

        $template = AssessmentTemplate::with([
            'categoryTypes.aspects.subAspects',
        ])->find($this->templateId);

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

    private function resetFormData(): void
    {
        $this->categoryWeights = [];
        $this->aspectConfigs = [];
        $this->subAspectConfigs = [];
        $this->potensiAspects = [];
        $this->kompetensiAspects = [];
    }

    public function save(CustomStandardService $service): mixed
    {
        $user = auth()->user();

        if (! $user->institution_id) {
            session()->flash('error', 'Anda tidak memiliki akses untuk membuat custom standard.');

            return null;
        }

        // Validate
        $this->validate([
            'templateId' => 'required|exists:assessment_templates,id',
            'code' => 'required|string|max:255',
            'name' => 'required|string|max:255',
        ]);

        // Check code uniqueness
        if (! $service->isCodeUnique($user->institution_id, $this->code)) {
            $this->addError('code', 'Kode sudah digunakan.');

            return null;
        }

        // Validate category weights sum
        $totalCategoryWeight = array_sum($this->categoryWeights);
        if ($totalCategoryWeight !== 100) {
            $this->addError('categoryWeights', "Total bobot kategori harus 100% (saat ini: {$totalCategoryWeight}%)");

            return null;
        }

        // Create
        $service->create([
            'institution_id' => $user->institution_id,
            'template_id' => $this->templateId,
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description,
            'category_weights' => $this->categoryWeights,
            'aspect_configs' => $this->aspectConfigs,
            'sub_aspect_configs' => $this->subAspectConfigs,
            'created_by' => $user->id,
        ]);

        session()->flash('success', 'Custom standard berhasil dibuat.');

        return $this->redirect(route('custom-standards.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.pages.custom-standards.create');
    }
}
