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

// Individual Report Route - General MC Mapping (Kompetensi Only)
Route::get('/general-mc-mapping/{eventCode}/{testNumber}', App\Livewire\Pages\IndividualReport\GeneralMcMapping::class)->name('general_mc_mapping');

// Individual Report Route - General PSY Mapping (Potensi Only)
Route::get('/general-psy-mapping/{eventCode}/{testNumber}', App\Livewire\Pages\IndividualReport\GeneralPsyMapping::class)->name('general_psy_mapping');

// Individual Report Route - Spider Plot
Route::get('/spider-plot/{eventCode}/{testNumber}', App\Livewire\Pages\IndividualReport\SpiderPlot::class)->name('spider_plot');

// Individual Report Route - Ringkasan MC Mapping (Kompetensi Summary)
Route::get('/ringkasan-mc-mapping/{eventCode}/{testNumber}', App\Livewire\Pages\IndividualReport\RingkasanMcMapping::class)->name('ringkasan_mc_mapping');

// Individual Report Route - Ringkasan Assessment
Route::get('/ringkasan-assessment/{eventCode}/{testNumber}', App\Livewire\Pages\IndividualReport\RingkasanAssessment::class)->name('ringkasan_assessment');

// General report Routes
Route::get('/ranking-psy-mapping', App\Livewire\Pages\GeneralReport\Ranking\RankingPsyMapping::class)->name('ranking-psy-mapping');

Route::get('/ranking-mc-mapping', App\Livewire\Pages\GeneralReport\Ranking\RankingMcMapping::class)->name('ranking-mc-mapping');

Route::get('/rekap-ranking-assessment', App\Livewire\Pages\GeneralReport\RekapRankingAssessment::class)->name('rekap-ranking-assessment');

Route::get('/statistic', App\Livewire\Pages\GeneralReport\Statistic::class)->name('statistic');

Route::get('/training-recomendation', function () {
    return view('livewire.pages.general-report.training.training-recomendation');
})->name('training-recomendation');

Route::get('/standard-mc', App\Livewire\Pages\GeneralReport\StandardMc::class)->name('standard-mc');
Route::get('/standard-mc-copy', function () {
    return view('livewire.pages.general-report.standard-mc-copy');
})->name('standard-mc-copy');

Route::get('/standard-psikometrik', App\Livewire\Pages\GeneralReport\StandardPsikometrik::class)->name('standard-psikometrik');

Route::get('/tkmi', function () {
    return view('livewire.pages.general-report.tkmi');
})->name('tkmi');
