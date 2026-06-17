<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WakilKepalaSekolah\DashboardController;

Route::middleware(['auth', 'verified', 'role:wakil_kepala_sekolah'])->prefix('wakil-kepala-sekolah')->name('wakil-kepala-sekolah.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});
