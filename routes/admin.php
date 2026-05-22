<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\GuruController;
use App\Http\Controllers\Admin\KelasController;
use App\Http\Controllers\Admin\MataPelajaranController;
use App\Http\Controllers\Admin\SiswaController;
use App\Http\Controllers\Admin\TahunAjaranController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');

    // Tahun Ajaran
    Route::resource('tahun-ajaran', TahunAjaranController::class)->parameters([
        'tahun-ajaran' => 'tahunAjaran',
    ]);
    Route::patch('tahun-ajaran/{tahunAjaran}/aktivasi', [TahunAjaranController::class, 'aktivasi'])->name('tahun-ajaran.aktivasi');

    // Guru
    Route::resource('guru', GuruController::class)->parameters([
        'guru' => 'guru',
    ]);

    // Siswa
    Route::resource('siswa', SiswaController::class)->parameters([
        'siswa' => 'siswa',
    ]);

    // Kelas
    Route::resource('kelas', KelasController::class)->parameters([
        'kelas' => 'kelas',
    ]);

    // Mata Pelajaran
    Route::resource('mata-pelajaran', MataPelajaranController::class)->parameters([
        'mata-pelajaran' => 'mataPelajaran',
    ]);

});
