<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Guru\AbsensiController;

Route::middleware(['auth', 'verified', 'role:guru|wali_kelas'])->prefix('guru')->name('guru.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Guru\GuruController::class, 'index'])->name('dashboard');

    // Absensi
    Route::get('/absensi', [AbsensiController::class, 'index'])->name('absensi.index');
    Route::get('/absensi/input/{jadwal}', [AbsensiController::class, 'input'])->name('absensi.input');
    Route::post('/absensi/input/{jadwal}', [AbsensiController::class, 'store'])->name('absensi.store');
    Route::get('/absensi/rekap', [AbsensiController::class, 'rekap'])->name('absensi.rekap');
});