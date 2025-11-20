<?php

namespace App\Livewire\Pages\CustomStandards;

use App\Concerns\ManagesCustomStandardForm;
use App\Models\AssessmentTemplate;
use App\Services\CustomStandardService;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app', ['title' => 'Buat Custom Standard'])]
class Create extends Component
{
    use ManagesCustomStandardForm;

    public ?int $templateId = null;

    public Collection $templates;

    public function mount(CustomStandardService $service): void
    {
        $user = auth()->user();

        if (! $user->institution_id) {
            abort(403, 'Anda tidak memiliki akses untuk membuat custom standard.');
        }

        // Only get templates that are used by this institution's events
        $this->templates = $service->getAvailableTemplatesForInstitution($user->institution_id);

        if ($this->templates->isEmpty()) {
            session()->flash('warning', 'Tidak ada template yang tersedia. Silakan hubungi administrator.');
        }
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

        $this->loadAspectsForDisplay($this->templateId);
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

        // Validate category weights
        if (! $this->validateCategoryWeights()) {
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
