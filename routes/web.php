<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\HcaPdfExportController;
use App\Livewire\Pages\Admin\ClientList;
use App\Livewire\Pages\Admin\DashboardAdmin;
use App\Livewire\Pages\Admin\Institutions\Show;
use App\Livewire\Pages\CustomStandards\Create;
use App\Livewire\Pages\CustomStandards\Edit;
use App\Livewire\Pages\CustomStandards\Index;
use App\Livewire\Pages\Dashboard;
use App\Livewire\Pages\GeneralReport\MmpiResultsReport;
use App\Livewire\Pages\GeneralReport\Ranking\RankingMcMapping;
use App\Livewire\Pages\GeneralReport\Ranking\RankingPsyMapping;
use App\Livewire\Pages\GeneralReport\Ranking\RekapRankingAssessment;
use App\Livewire\Pages\GeneralReport\Statistic;
use App\Livewire\Pages\GeneralReport\Training\TrainingRecommendation;
use App\Livewire\Pages\HCA\HcaReportPage;
use App\Livewire\Pages\IndividualReport\FinalReport;
use App\Livewire\Pages\IndividualReport\GeneralMapping;
use App\Livewire\Pages\IndividualReport\GeneralMatching;
use App\Livewire\Pages\IndividualReport\GeneralMcMapping;
use App\Livewire\Pages\IndividualReport\GeneralPsyMapping;
use App\Livewire\Pages\IndividualReport\RingkasanAssessment;
use App\Livewire\Pages\IndividualReport\RingkasanMcMapping;
use App\Livewire\Pages\IndividualReport\SpiderPlot;
use App\Livewire\Pages\LaporanAlatTes\DetailLaporanTes;
use App\Livewire\Pages\LaporanAlatTes\LaporanAlatTes;
use App\Livewire\Pages\ParticipantDetail;
use App\Livewire\Pages\ParticipantsList;
use App\Livewire\Pages\Simulation\StandardMc;
use App\Livewire\Pages\Simulation\StandardPsikometrik;
use App\Livewire\Welcome;
use Illuminate\Support\Facades\Route;

// Route::get('/', Welcome::class);
Route::get('/', function () {
    // TEMPORARY: Set force_reload session for bypass authentication
    // TODO: Remove/comment this line when restoring authentication (force_reload will be set in LoginController)
    // session()->flash('force_reload', true);

    return view('welcome');
})->name('welcome');

Route::get('/kebijakan-privasi', function () {
    return view('privacy');
})->name('privacy');

Route::get('/hca-report-demo', HcaReportPage::class)->name('hca-report-demo');
Route::get('/hca-report-demo/{participant}/download', [HcaPdfExportController::class, 'download'])->name('hca-report.download-demo');
Route::get('/hca-report-demo/{participant}/preview', [HcaPdfExportController::class, 'preview'])->name('hca-report.preview-demo');

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
    Route::livewire('/dashboard', Dashboard::class)->name('dashboard');
    Route::middleware(['role:admin'])->group(function () {
        Route::livewire('/dashboard-admin', DashboardAdmin::class)->name('dashboard-admin');
        // List Klien Route
        Route::livewire('/list-klien', ClientList::class)->name('daftar-klien');

        // Institution Routes
        Route::livewire('/institutions/{institution}', Show::class)->name('institutions.show');

        // Event Routes
        Route::livewire('/events', App\Livewire\Pages\Admin\Events\Index::class)->name('events.index');
        Route::livewire('/events/{event:code}', App\Livewire\Pages\Admin\Events\Show::class)->name('events.show');
    });

    Route::livewire('/shortlist-peserta', ParticipantsList::class)->name('shortlist');

    // Detail Peserta Route
    Route::livewire('/participant-detail/{eventCode}/{testNumber}', ParticipantDetail::class)->name('participant_detail');

    // Individual Report Route - General Matching
    Route::livewire('/general-matching/{eventCode}/{testNumber}', GeneralMatching::class)->name('general_matching');

    // Individual Report Route - General Mapping
    Route::livewire('/general-mapping/{eventCode}/{testNumber}', GeneralMapping::class)->name('general_mapping');

    // Individual Report Route - General MC Mapping (Kompetensi Only)
    Route::livewire('/general-mc-mapping/{eventCode}/{testNumber}', GeneralMcMapping::class)->name('general_mc_mapping');

    // Individual Report Route - General PSY Mapping (Potensi Only)
    Route::livewire('/general-psy-mapping/{eventCode}/{testNumber}', GeneralPsyMapping::class)->name('general_psy_mapping');

    // Individual Report Route - Spider Plot
    Route::livewire('/spider-plot/{eventCode}/{testNumber}', SpiderPlot::class)->name('spider_plot');

    // Individual Report Route - Ringkasan MC Mapping (Kompetensi Summary)
    Route::livewire('/ringkasan-mc-mapping/{eventCode}/{testNumber}', RingkasanMcMapping::class)->name('ringkasan_mc_mapping');

    // Individual Report Route - Ringkasan Asesmen
    Route::livewire('/ringkasan-assessment/{eventCode}/{testNumber}', RingkasanAssessment::class)->name('ringkasan_assessment');

    // Individual Report Route - Final Report (Laporan Individu)
    Route::livewire('/final-report/{eventCode}/{testNumber}', FinalReport::class)->name('final_report');

    // General report Routes
    Route::livewire('/ranking-psy-mapping', RankingPsyMapping::class)->name('ranking-psy-mapping');

    // General Report Route - MMPI Results
    Route::livewire('/ranking-mc-mapping', RankingMcMapping::class)->name('ranking-mc-mapping');

    Route::livewire('/rekap-ranking-assessment', RekapRankingAssessment::class)->name('rekap-ranking-assessment');

    Route::livewire('/statistic', Statistic::class)->name('statistic');

    Route::livewire('/training-recommendation', TrainingRecommendation::class)->name('training-recommendation');

    Route::livewire('/standard-mc', StandardMc::class)->name('standard-mc');

    Route::livewire('/standard-psikometrik', StandardPsikometrik::class)->name('standard-psikometrik');
    Route::livewire('/general-report/mmpi', MmpiResultsReport::class)->name('general-report.mmpi');

    Route::livewire('/hca-report/{participant?}', HcaReportPage::class)->name('hca-report');
    Route::get('/hca-report/{participant}/download-pdf', [HcaPdfExportController::class, 'download'])->name('hca-report.download-pdf');
    Route::get('/hca-report/{participant}/preview-pdf', [HcaPdfExportController::class, 'preview'])->name('hca-report.preview-pdf');

    // Custom Standards Routes
    Route::livewire('/custom-standards', Index::class)->name('custom-standards.index');
    Route::livewire('/custom-standards/create', Create::class)->name('custom-standards.create');
    Route::livewire('/custom-standards/{customStandard}/edit', Edit::class)->name('custom-standards.edit');

    // Laporan Alat Tes
    Route::livewire('/laporan-alat-tes', LaporanAlatTes::class)->name('laporan-alat-tes');
    Route::livewire('/laporan-alat-tes-detail/{participantId?}', DetailLaporanTes::class)->name('laporan-alat-tes-detail');

    // Talent Pool Management
    Route::livewire('/talentpool', App\Livewire\Pages\GeneralReport\TalentPool\Index::class)->name('talentpool');
});
