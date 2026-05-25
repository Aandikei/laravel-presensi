<?php

use App\Http\Controllers\Admin\AbsensiController as AdminAbsensiController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\GuruController;
use App\Http\Controllers\Admin\HariLiburController;
use App\Http\Controllers\Admin\JadwalController;
use App\Http\Controllers\Admin\KelasController;
use App\Http\Controllers\Admin\KurikulumKelasController;
use App\Http\Controllers\Admin\LogPoinController;
use App\Http\Controllers\Admin\MasterPoinController;
use App\Http\Controllers\Admin\MataPelajaranController;
use App\Http\Controllers\Admin\RegistrasiAkademikController;
use App\Http\Controllers\Admin\SiswaController;
use App\Http\Controllers\Admin\TahunAjaranController;
use App\Models\Kelas;
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

    // Kurikulum
    Route::resource('kurikulum', KurikulumKelasController::class)->parameters([
        'kurikulum' => 'kurikulum',
    ]);

    // Jadwal
    Route::resource('jadwal', JadwalController::class)->parameters([
        'jadwal' => 'jadwal',
    ]);

    // Helper untuk load kurikulum by kelas (dipakai di form jadwal)
    Route::get('kurikulum-by-kelas/{kelas}', function (Kelas $kelas) {
        return $kelas->kurikulum()
            ->with(['mataPelajaran', 'guru'])
            ->get()
            ->map(fn ($k) => [
                'id_kurikulum' => $k->id_kurikulum,
                'mata_pelajaran' => $k->mataPelajaran->nama_mapel,
                'guru' => $k->guru->nama_guru,
            ]);
    })->middleware(['auth', 'role:admin']);

    // Registrasi Akademin
    Route::resource('registrasi', RegistrasiAkademikController::class)->parameters([
        'registrasi' => 'registrasi',
    ]);

    // Absensi
    Route::get('absensi', [AdminAbsensiController::class, 'index'])->name('absensi.index');
    Route::get('absensi/{jadwal}/detail', [AdminAbsensiController::class, 'detail'])->name('absensi.detail');
    Route::patch('absensi/{jadwal}/lock', [AdminAbsensiController::class, 'lock'])->name('absensi.lock');
    Route::patch('absensi/{jadwal}/unlock', [AdminAbsensiController::class, 'unlock'])->name('absensi.unlock');

    // Hari Libur
    Route::resource('hari-libur', HariLiburController::class)
    ->parameters(['hari-libur' => 'hariLibur'])
    ->only(['index', 'store', 'destroy']);

    // Tambah route adopt
    Route::post('hari-libur/adopt', [HariLiburController::class, 'adopt'])->name('hari-libur.adopt');

    // Poin
    Route::resource('master-poin', MasterPoinController::class)->parameters([
        'master-poin' => 'masterPoin'
    ]);
    Route::resource('log-poin', LogPoinController::class)->parameters([
        'log-poin' => 'logPoin'
    ])->only(['index', 'store', 'destroy']);
});
