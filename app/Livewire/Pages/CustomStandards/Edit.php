<?php

namespace App\Livewire\Pages\CustomStandards;

use App\Concerns\ManagesCustomStandardForm;
use App\Models\CustomStandard;
use App\Services\CustomStandardService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app', ['title' => 'Edit Custom Standard'])]
class Edit extends Component
{
    use ManagesCustomStandardForm;

    public CustomStandard $customStandard;

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

        $this->loadAspectsForDisplay($customStandard->template_id);
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

        // Validate category weights
        if (! $this->validateCategoryWeights()) {
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
