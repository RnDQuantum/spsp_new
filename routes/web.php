<?php

use App\Http\Controllers\Auth\LoginController;
use App\Livewire\Welcome;
use Illuminate\Support\Facades\Route;

// Route::get('/', Welcome::class);
Route::get('/', function () {
    // TEMPORARY: Set force_reload session for bypass authentication
    // TODO: Remove/comment this line when restoring authentication (force_reload will be set in LoginController)
    // session()->flash('force_reload', true);

    return view('welcome');
})->name('welcome');

Route::middleware(['guest'])->group(function () {
    // Authentication Routes
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'authenticate']);
});

// Protected Routes (with Multi-Tenant Access Control)
// TEMPORARY: Comment out 'auth' middleware to bypass authentication
// TODO: Uncomment line below and remove the line after to restore authentication
// Route::middleware(['auth', 'institution.access'])->group(function () {
Route::middleware(['auth', 'institution.access'])->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::livewire('/dashboard', \App\Livewire\Pages\Dashboard::class)->name('dashboard');
    Route::middleware(['role:admin'])->group(function () {
        Route::livewire('/dashboard-admin', \App\Livewire\Pages\Admin\DashboardAdmin::class)->name('dashboard-admin');
        // List Klien Route
        Route::livewire('/list-klien', \App\Livewire\Pages\Admin\ClientList::class)->name('daftar-klien');

        // Institution Routes
        Route::livewire('/institutions/{institution}', \App\Livewire\Pages\Institutions\Show::class)->name('institutions.show');

        // Event Routes
        Route::livewire('/events', \App\Livewire\Pages\Events\Index::class)->name('events.index');
        Route::livewire('/events/{event:code}', \App\Livewire\Pages\Events\Show::class)->name('events.show');
    });


    Route::livewire('/shortlist-peserta', \App\Livewire\Pages\ParticipantsList::class)->name('shortlist');

    // Detail Peserta Route
    Route::livewire('/participant-detail/{eventCode}/{testNumber}', \App\Livewire\Pages\ParticipantDetail::class)->name('participant_detail');

    // Individual Report Route - General Matching
    Route::livewire('/general-matching/{eventCode}/{testNumber}', App\Livewire\Pages\IndividualReport\GeneralMatching::class)->name('general_matching');

    // Individual Report Route - General Mapping
    Route::livewire('/general-mapping/{eventCode}/{testNumber}', App\Livewire\Pages\IndividualReport\GeneralMapping::class)->name('general_mapping');

    // Individual Report Route - General MC Mapping (Kompetensi Only)
    Route::livewire('/general-mc-mapping/{eventCode}/{testNumber}', App\Livewire\Pages\IndividualReport\GeneralMcMapping::class)->name('general_mc_mapping');

    // Individual Report Route - General PSY Mapping (Potensi Only)
    Route::livewire('/general-psy-mapping/{eventCode}/{testNumber}', App\Livewire\Pages\IndividualReport\GeneralPsyMapping::class)->name('general_psy_mapping');

    // Individual Report Route - Spider Plot
    Route::livewire('/spider-plot/{eventCode}/{testNumber}', App\Livewire\Pages\IndividualReport\SpiderPlot::class)->name('spider_plot');

    // Individual Report Route - Ringkasan MC Mapping (Kompetensi Summary)
    Route::livewire('/ringkasan-mc-mapping/{eventCode}/{testNumber}', App\Livewire\Pages\IndividualReport\RingkasanMcMapping::class)->name('ringkasan_mc_mapping');

    // Individual Report Route - Ringkasan Asesmen
    Route::livewire('/ringkasan-assessment/{eventCode}/{testNumber}', App\Livewire\Pages\IndividualReport\RingkasanAssessment::class)->name('ringkasan_assessment');

    // General report Routes
    Route::livewire('/ranking-psy-mapping', App\Livewire\Pages\GeneralReport\Ranking\RankingPsyMapping::class)->name('ranking-psy-mapping');

    // General Report Route - MMPI Results
    Route::livewire('/ranking-mc-mapping', App\Livewire\Pages\GeneralReport\Ranking\RankingMcMapping::class)->name('ranking-mc-mapping');

    Route::livewire('/rekap-ranking-assessment', App\Livewire\Pages\GeneralReport\Ranking\RekapRankingAssessment::class)->name('rekap-ranking-assessment');

    Route::livewire('/statistic', App\Livewire\Pages\GeneralReport\Statistic::class)->name('statistic');

    Route::livewire('/training-recommendation', App\Livewire\Pages\GeneralReport\Training\TrainingRecommendation::class)->name('training-recommendation');

    Route::livewire('/standard-mc', App\Livewire\Pages\GeneralReport\StandardMc::class)->name('standard-mc');
    Route::get('/standard-mc-copy', function () {
        return view('livewire.pages.general-report.standard-mc-copy');
    })->name('standard-mc-copy');

    Route::get('/cb-mc', function () {
        return view('livewire.pages.general-report.ranking.capacitybuilding-mc');
    })->name('cb-mc');

    Route::get('/cb-psy', function () {
        return view('livewire.pages.general-report.ranking.capacitybuilding-psy');
    })->name('cb-psy');

    Route::livewire('/standard-psikometrik', App\Livewire\Pages\GeneralReport\StandardPsikometrik::class)->name('standard-psikometrik');
    Route::livewire('/general-report/mmpi', App\Livewire\Pages\GeneralReport\MmpiResultsReport::class)->name('general-report.mmpi');

    Route::livewire('/final-report/{eventCode}/{testNumber}', App\Livewire\Pages\IndividualReport\FinalReport::class)->name('final_report');

    // Custom Standards Routes
    Route::livewire('/custom-standards', App\Livewire\Pages\CustomStandards\Index::class)->name('custom-standards.index');
    Route::livewire('/custom-standards/create', App\Livewire\Pages\CustomStandards\Create::class)->name('custom-standards.create');
    Route::livewire('/custom-standards/{customStandard}/edit', App\Livewire\Pages\CustomStandards\Edit::class)->name('custom-standards.edit');

    // Laporan Alat Tes
    Route::livewire('/laporan-alat-tes', App\Livewire\Pages\LaporanAlatTes\LaporanAlatTes::class)->name('laporan-alat-tes');
    Route::livewire('/laporan-alat-tes-detail', App\Livewire\Pages\LaporanAlatTes\DetailLaporanTes::class)->name('laporan-alat-tes-detail');

    // Talent Pool Management
    Route::livewire('/talentpool', App\Livewire\Pages\TalentPool\Index::class)->name('talentpool');
});
