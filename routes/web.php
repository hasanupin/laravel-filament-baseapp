<?php

use App\Http\Controllers\LocaleController;
use Illuminate\Support\Facades\Route;

// The Filament panel owns "/" (see AdminPanelProvider::path('')).

Route::get('/locale/{locale}', LocaleController::class)->name('locale.switch');
