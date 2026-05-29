<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Siswa\SiswaDashboardController;

Route::middleware(['auth', 'verified', 'role:siswa'])->prefix('siswa')->name('siswa.')->group(function () {
    Route::get('/dashboard', [SiswaDashboardController::class, 'index'])->name('dashboard');
    Route::get('/absensi', [SiswaDashboardController::class, 'absensi'])->name('absensi');
    Route::get('/poin', [SiswaDashboardController::class, 'poin'])->name('poin');
});