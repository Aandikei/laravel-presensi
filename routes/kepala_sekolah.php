<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\KepalaSekolah\DashboardController;

Route::middleware(['auth', 'verified', 'role:kepala_sekolah'])->prefix('kepala-sekolah')->name('kepala-sekolah.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});
