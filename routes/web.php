<?php

use App\Livewire\Welcome;
use App\Livewire\ReportComponent;
use Illuminate\Support\Facades\Route;

// Route::get('/', Welcome::class);

Route::get('/', function () {
    return view('dashboard');
})->name('dashboard');

Route::get('/shortlist-peserta', function () {
    return view('livewire.pages.shortlist');
})->name('shortlist');


// Individual Report Route
Route::get('/general_matching', ReportComponent::class)->name('general_matching');
