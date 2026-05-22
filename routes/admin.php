<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\TahunAjaranController;

Route::middleware(['auth', 'verified', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');

    // Tahun Ajaran
    Route::resource('tahun-ajaran', TahunAjaranController::class);
    Route::patch('tahun-ajaran/{tahunAjaran}/aktivasi', [TahunAjaranController::class, 'aktivasi'])->name('tahun-ajaran.aktivasi');
});
