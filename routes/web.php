<?php

use App\Livewire\Welcome;
use App\Livewire\ReportComponent;
use Illuminate\Support\Facades\Route;

// Route::get('/', Welcome::class);

Route::get('/', function () {
    return view('dashboard');
})->name('dashboard');

Route::get('/shortlist-peserta', \App\Livewire\Pages\ParticipantsList::class)->name('shortlist');



<<<<<<< HEAD
// Individual Report Route
Route::get('/general_matching', ReportComponent::class)->name('general_matching');
Route::get('/general_mapping', function () {
    return view('livewire.pages.individual_report.general_mapping');
})->name('general_mapping');
=======
// Individual Report Route - General Matching
Route::get('/general-matching/{eventCode}/{testNumber}', ReportComponent::class)
    ->name('general_matching');
>>>>>>> 3330c1b70ca37a82f6d5be75eea3acf65275f109
