<?php

use App\Http\Controllers\Guru\AbsensiController;
use App\Http\Controllers\Guru\ExportController;
use App\Http\Controllers\Guru\WaliKelasController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'role:guru|wali_kelas|kepala_sekolah|wakil_kepala_sekolah'])->prefix('guru')->name('guru.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Guru\GuruController::class, 'index'])->name('dashboard');

    // Absensi
    Route::get('/absensi', [AbsensiController::class, 'index'])->name('absensi.index');
    Route::get('/absensi/input/{jadwal}', [AbsensiController::class, 'input'])->name('absensi.input');
    Route::post('/absensi/input/{jadwal}', [AbsensiController::class, 'store'])->name('absensi.store');
    Route::get('/absensi/rekap', [AbsensiController::class, 'rekap'])->name('absensi.rekap');
    Route::get('/absensi/rekap/detail', [AbsensiController::class, 'detailRekap'])->name('absensi.rekap.detail');
    Route::post('/absensi/rekap/export', [AbsensiController::class, 'exportRekap'])->name('absensi.rekap.export');
});

// Wali Kelas specific routes (separate group with separate prefix)
Route::middleware(['auth', 'verified', 'role:guru|wali_kelas'])->prefix('wali-kelas')->name('guru.wali-kelas.')->group(function () {
    Route::get('/siswa', [WaliKelasController::class, 'daftarSiswa'])->name('siswa-poin');
    Route::get('/siswa/{kelas}', [WaliKelasController::class, 'siswaByKelas'])->name('siswa-by-kelas');
    Route::post('/tambah-poin', [WaliKelasController::class, 'tambahPoin'])->name('tambah-poin');
    Route::get('/log-poin', [WaliKelasController::class, 'logPoin'])->name('log-poin');
    Route::delete('/hapus-poin/{id}', [WaliKelasController::class, 'hapusPoin'])->name('hapus-poin');
    Route::get('/rekap-absensi', [WaliKelasController::class, 'rekapAbsensi'])->name('rekap-absensi');
    Route::get('/rekap-absensi/detail', [WaliKelasController::class, 'detailAbsensi'])->name('rekap-absensi.detail');

    // Export via Queue
    Route::post('/rekap-absensi/export-excel', [WaliKelasController::class, 'exportAbsensiExcel'])->name('rekap-absensi.export-excel');
    Route::post('/rekap-absensi/export-pdf', [WaliKelasController::class, 'exportAbsensiPdf'])->name('rekap-absensi.export-pdf');
});

// Export Saya (for guru, wali kelas, etc.)
Route::middleware(['auth', 'verified', 'role:guru|wali_kelas|kepala_sekolah|wakil_kepala_sekolah'])->prefix('guru')->name('guru.')->group(function () {
    Route::get('/exports', [ExportController::class, 'exports'])->name('exports');
    Route::get('/exports/{exportJob}/download', [ExportController::class, 'downloadExport'])->name('export-download');
    Route::delete('/exports/{exportJob}', [ExportController::class, 'destroyExport'])->name('export-destroy');
});
