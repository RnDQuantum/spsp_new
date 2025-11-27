<?php

namespace App\Livewire\Pages;

use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app', ['title' => 'Talent Pool'])]
class TalentPool extends Component
{
    public function render()
    {
        return view('livewire.pages.talentpool');
    }
}
