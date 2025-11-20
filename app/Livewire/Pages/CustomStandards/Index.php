<?php

namespace App\Livewire\Pages\CustomStandards;

use App\Models\CustomStandard;
use App\Services\CustomStandardService;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app', ['title' => 'Custom Standards'])]
class Index extends Component
{
    public Collection $customStandards;

    public ?int $deleteId = null;

    public function mount(CustomStandardService $service): void
    {
        $this->loadCustomStandards($service);
    }

    public function loadCustomStandards(CustomStandardService $service): void
    {
        $user = auth()->user();

        if ($user->institution_id) {
            $this->customStandards = $service->getAllForInstitution($user->institution_id);
        } else {
            $this->customStandards = collect();
        }
    }

    public function confirmDelete(int $id): void
    {
        $this->deleteId = $id;
    }

    public function cancelDelete(): void
    {
        $this->deleteId = null;
    }

    public function delete(CustomStandardService $service): void
    {
        if (! $this->deleteId) {
            return;
        }

        $customStandard = CustomStandard::find($this->deleteId);

        if ($customStandard && $this->authorize('delete', $customStandard)) {
            $service->delete($customStandard);
            $this->loadCustomStandards($service);
        }

        $this->deleteId = null;
    }

    public function render()
    {
        return view('livewire.pages.custom-standards.index');
    }
}
