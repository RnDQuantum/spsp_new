<?php

use App\Livewire\Welcome;
use Illuminate\Support\Facades\Route;

// Route::get('/', Welcome::class);

Route::get('/', function () {
    return view('dashboard');
})->name('dashboard');

Route::get('/shortlist-peserta', \App\Livewire\Pages\ParticipantsList::class)->name('shortlist');

// Detail Peserta Route
Route::get('/participant-detail/{eventCode}/{testNumber}', \App\Livewire\Pages\ParticipantDetail::class)->name('participant_detail');

// Individual Report Route - General Matching
Route::get('/general-matching/{eventCode}/{testNumber}', App\Livewire\Pages\IndividualReport\GeneralMatching::class)->name('general_matching');

// Individual Report Route - General Mapping
Route::get('/general-mapping/{eventCode}/{testNumber}', App\Livewire\Pages\IndividualReport\GeneralMapping::class)->name('general_mapping');

// Individual Report Route - General MC Mapping (Kompetensi Only)
Route::get('/general-mc-mapping/{eventCode}/{testNumber}', App\Livewire\Pages\IndividualReport\GeneralMcMapping::class)->name('general_mc_mapping');

Route::get('/general-mc-mapping', function () {
    return view('livewire.pages.individual-report.general-mc-mapping');
})->name('general-mc-mapping');
