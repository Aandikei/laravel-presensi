<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        $user = auth()->user();
        if ($user->hasRole('super_admin')) return redirect()->route('superadmin.dashboard');
        if ($user->hasRole('admin')) return redirect()->route('admin.dashboard');
        if ($user->hasRole('guru') || $user->hasRole('wali_kelas')) return redirect()->route('guru.dashboard');
        if ($user->hasRole('siswa')) return redirect()->route('siswa.dashboard');
        if ($user->hasRole('orang_tua')) return redirect()->route('orangtua.dashboard');
    }
    return redirect()->route('login');
});

// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
require __DIR__.'/user.php';
require __DIR__.'/admin.php';
require __DIR__.'/guru.php';
require __DIR__.'/superadmin.php';
require __DIR__.'/siswa.php';
require __DIR__.'/orangtua.php';
