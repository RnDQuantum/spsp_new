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

// Individual Report Route - Spider Plot
Route::get('/spider-plot/{eventCode}/{testNumber}', App\Livewire\Pages\IndividualReport\SpiderPlot::class)->name('spider_plot');

// Individual Report Route - General Psychology Mapping
Route::get('/general-psy-mapping', function () {
    return view('livewire.pages.individual-report.general-psy-mapping');
})->name('general-psy-mapping');

// Individual Report Route - Managerial Potency Mapping
Route::get('/general-mc-mapping', function () {
    return view('livewire.pages.individual-report.general-mc-mapping');
})->name('general-mc-mapping');
