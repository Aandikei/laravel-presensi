<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrangTua\OrangTuaDashboardController;

Route::middleware(['auth', 'verified', 'role:orang_tua'])->prefix('orang-tua')->name('orangtua.')->group(function () {
    Route::get('/dashboard', [OrangTuaDashboardController::class, 'index'])->name('dashboard');
    Route::get('/absensi', [OrangTuaDashboardController::class, 'absensi'])->name('absensi');
    Route::get('/poin', [OrangTuaDashboardController::class, 'poin'])->name('poin');
});