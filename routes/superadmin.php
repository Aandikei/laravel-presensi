<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SuperAdmin\SuperAdminController;
use App\Http\Controllers\SuperAdmin\SekolahController;
use App\Http\Controllers\SuperAdmin\RoleController;
use App\Http\Controllers\SuperAdmin\HariLiburController;

Route::middleware(['auth', 'verified', 'role:super_admin'])->prefix('superadmin')->name('superadmin.')->group(function () {
    Route::get('/dashboard', [SuperAdminController::class, 'index'])->name('dashboard');

    // Sekolah CRUD
    Route::resource('sekolah', SekolahController::class)->parameters([
        'sekolah' => 'instansi',
    ]);

    // Assign admin to sekolah
    Route::get('sekolah/{instansi}/assign-admin', [SekolahController::class, 'assignAdmin'])->name('sekolah.assign-admin');
    Route::post('sekolah/{instansi}/assign-admin', [SekolahController::class, 'storeAdmin'])->name('sekolah.store-admin');

    // Manage Roles
    Route::resource('roles', RoleController::class)->parameters([
        'roles' => 'role',
    ])->except(['show']);

    // Hari Libur Nasional
    Route::get('hari-libur', [HariLiburController::class, 'index'])->name('hari-libur.index');
    Route::post('hari-libur', [HariLiburController::class, 'store'])->name('hari-libur.store');
    Route::delete('hari-libur/{hariLibur}', [HariLiburController::class, 'destroy'])->name('hari-libur.destroy');
});
