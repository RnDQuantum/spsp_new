<?php

use App\Livewire\Welcome;
use App\Livewire\ReportComponent;
use Illuminate\Support\Facades\Route;

// Route::get('/', Welcome::class);

Route::get('/', function () {
    return view('dashboard');
})->name('dashboard');

Route::get('/shortlist-peserta', \App\Livewire\Pages\ParticipantsList::class)->name('shortlist');



// Individual Report Route - General Matching
Route::get('/general-matching/{eventCode}/{testNumber}', ReportComponent::class)
    ->name('general_matching');

Route::get('/general-mapping', function () {
    return view('livewire.pages.individual_report.general_mapping');
})->name('general_mapping');
