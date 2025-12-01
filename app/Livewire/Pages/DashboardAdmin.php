<?php

namespace App\Livewire\Pages;

use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app', ['title' => 'Beranda Admin'])]
class DashboardAdmin extends Component
{
    public function render()
    {
        return view('livewire.pages.dashboard-admin');
    }
}
