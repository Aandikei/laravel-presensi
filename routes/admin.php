<?php

use App\Http\Controllers\Admin\AbsensiController as AdminAbsensiController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\GuruController;
use App\Http\Controllers\Admin\HariLiburController;
use App\Http\Controllers\Admin\JadwalController;
use App\Http\Controllers\Admin\KelasController;
use App\Http\Controllers\Admin\KurikulumKelasController;
use App\Http\Controllers\Admin\LaporanController;
use App\Http\Controllers\Admin\LogPoinController;
use App\Http\Controllers\Admin\MasterPoinController;
use App\Http\Controllers\Admin\MataPelajaranController;
use App\Http\Controllers\Admin\NaikKelasController;
use App\Http\Controllers\Admin\RegistrasiAkademikController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\SiswaController;
use App\Http\Controllers\Admin\PindahSiswaController;
use App\Http\Controllers\Admin\TahunAjaranController;
use App\Models\Kelas;
use App\Models\TahunAjaran;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'role:admin|kepala_sekolah|wakil_kepala_sekolah'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');

    // Tahun Ajaran
    Route::get('tahun-ajaran', [TahunAjaranController::class, 'index'])->name('tahun-ajaran.index');
    Route::get('tahun-ajaran/create', [TahunAjaranController::class, 'create'])->name('tahun-ajaran.create');
    Route::post('tahun-ajaran', [TahunAjaranController::class, 'store'])->name('tahun-ajaran.store');
    Route::get('tahun-ajaran/{tahunAjaran}/edit', [TahunAjaranController::class, 'edit'])->name('tahun-ajaran.edit');
    Route::put('tahun-ajaran/{tahunAjaran}', [TahunAjaranController::class, 'update'])->name('tahun-ajaran.update');
    Route::delete('tahun-ajaran/{tahunAjaran}', [TahunAjaranController::class, 'destroy'])->name('tahun-ajaran.destroy');
    Route::patch('tahun-ajaran/{tahunAjaran}/aktivasi', [TahunAjaranController::class, 'aktivasi'])->name('tahun-ajaran.aktivasi');

    // Guru — view (semua)
    Route::get('guru', [GuruController::class, 'index'])->name('guru.index');
    Route::get('guru/mutasi/terima', [GuruController::class, 'formTerimaMutasi'])->name('guru.mutasi.terima');
    Route::post('guru/mutasi/terima/verifikasi', [GuruController::class, 'verifikasiTerimaMutasi'])->name('guru.mutasi.terima.verifikasi');
    Route::post('guru/mutasi/terima/proses', [GuruController::class, 'prosesTerimaMutasi'])->name('guru.mutasi.terima.proses');

    // Guru — manage (hanya admin & kasek)
    Route::middleware('permission:manage-guru')->group(function () {
        Route::get('guru/create', [GuruController::class, 'create'])->name('guru.create');
        Route::post('guru', [GuruController::class, 'store'])->name('guru.store');
        Route::get('guru/{guru}/edit', [GuruController::class, 'edit'])->name('guru.edit');
        Route::put('guru/{guru}', [GuruController::class, 'update'])->name('guru.update');
        Route::delete('guru/{guru}', [GuruController::class, 'destroy'])->name('guru.destroy');
        Route::get('guru/{guru}/mutasi', [GuruController::class, 'mutasiForm'])->name('guru.mutasi');
        Route::post('guru/{guru}/mutasi', [GuruController::class, 'prosesMutasi'])->name('guru.mutasi.proses');
        Route::post('guru/{guru}/mutasi/batal', [GuruController::class, 'batalMutasi'])->name('guru.mutasi.batal');
        Route::post('guru/{guru}/tandai-keluar', [GuruController::class, 'tandaiKeluar'])->name('guru.tandai-keluar');
        Route::post('guru/{guru}/tandai-pensiun', [GuruController::class, 'tandaiPensiun'])->name('guru.tandai-pensiun');
    });

    // Siswa — view (semua)
    Route::get('siswa', [SiswaController::class, 'index'])->name('siswa.index');

    // Pindah Siswa (Transfer) — semua bisa terima
    Route::prefix('siswa/pindah')->name('siswa.pindah.')->group(function () {
        Route::get('masuk', [PindahSiswaController::class, 'formMasuk'])->name('form-masuk');
        Route::post('verifikasi', [PindahSiswaController::class, 'verifikasi'])->name('verifikasi');
        Route::post('proses', [PindahSiswaController::class, 'prosesMasuk'])->name('proses');
    });

    // Siswa — manage (hanya admin & kasek)
    Route::middleware('permission:manage-siswa')->group(function () {
        Route::get('siswa/create', [SiswaController::class, 'create'])->name('siswa.create');
        Route::post('siswa', [SiswaController::class, 'store'])->name('siswa.store');
        Route::get('siswa/{siswa}', [SiswaController::class, 'show'])->name('siswa.show');
        Route::get('siswa/{siswa}/edit', [SiswaController::class, 'edit'])->name('siswa.edit');
        Route::put('siswa/{siswa}', [SiswaController::class, 'update'])->name('siswa.update');
        Route::delete('siswa/{siswa}', [SiswaController::class, 'destroy'])->name('siswa.destroy');
        Route::get('siswa/daftar-ulang/{siswa}', [SiswaController::class, 'formDaftarUlang'])->name('siswa.daftar-ulang');
        Route::post('siswa/daftar-ulang', [SiswaController::class, 'prosesDaftarUlang'])->name('siswa.proses-daftar-ulang');
        Route::post('siswa/import', [SiswaController::class, 'import'])->name('siswa.import');
        Route::get('siswa/template', [SiswaController::class, 'downloadTemplate'])->name('siswa.template');
        Route::post('siswa/{siswa}/pindah', [PindahSiswaController::class, 'out'])->name('siswa.pindah');
        Route::post('siswa/{siswa}/batal-pindah', [PindahSiswaController::class, 'batal'])->name('siswa.batal-pindah');
    });

    // Kelas — view (semua)
    Route::get('kelas/{kelas}/detail', [KelasController::class, 'detail'])->name('kelas.detail');
    Route::get('kelas', [KelasController::class, 'index'])->name('kelas.index');
    Route::get('kelas-list', function () {
        $instansi = Auth::user()->getInstansi();
        return Kelas::where('instansi_id', $instansi->id_instansi)
            ->orderBy('tingkat')
            ->orderBy('nama_kelas')
            ->get(['id_kelas', 'nama_kelas', 'tingkat']);
    })->name('kelas-list');

    // Kelas — manage (hanya admin & kasek)
    Route::middleware('permission:manage-kelas')->group(function () {
        Route::get('kelas/create', [KelasController::class, 'create'])->name('kelas.create');
        Route::post('kelas', [KelasController::class, 'store'])->name('kelas.store');
        Route::get('kelas/{kelas}/edit', [KelasController::class, 'edit'])->name('kelas.edit');
        Route::put('kelas/{kelas}', [KelasController::class, 'update'])->name('kelas.update');
        Route::delete('kelas/{kelas}', [KelasController::class, 'destroy'])->name('kelas.destroy');
        // Naik Kelas
        Route::prefix('naik-kelas')->name('naik-kelas.')->group(function () {
            Route::get('/', [NaikKelasController::class, 'index'])->name('index');
            Route::get('/preview', [NaikKelasController::class, 'preview'])->name('preview');
            Route::post('/proses', [NaikKelasController::class, 'proses'])->name('proses');
            Route::post('/salin-semester', [NaikKelasController::class, 'salinSemester'])->name('salin-semester');
        });
    });

    // Mata Pelajaran
    Route::get('mata-pelajaran', [MataPelajaranController::class, 'index'])->name('mata-pelajaran.index');
    Route::middleware('permission:manage-settings')->group(function () {
        Route::get('mata-pelajaran/create', [MataPelajaranController::class, 'create'])->name('mata-pelajaran.create');
        Route::post('mata-pelajaran', [MataPelajaranController::class, 'store'])->name('mata-pelajaran.store');
        Route::get('mata-pelajaran/{mataPelajaran}/edit', [MataPelajaranController::class, 'edit'])->name('mata-pelajaran.edit');
        Route::put('mata-pelajaran/{mataPelajaran}', [MataPelajaranController::class, 'update'])->name('mata-pelajaran.update');
        Route::delete('mata-pelajaran/{mataPelajaran}', [MataPelajaranController::class, 'destroy'])->name('mata-pelajaran.destroy');
    });

    // Kurikulum
    Route::get('kurikulum', [KurikulumKelasController::class, 'index'])->name('kurikulum.index');
    Route::middleware('permission:manage-settings')->group(function () {
        Route::get('kurikulum/create', [KurikulumKelasController::class, 'create'])->name('kurikulum.create');
        Route::post('kurikulum', [KurikulumKelasController::class, 'store'])->name('kurikulum.store');
        Route::get('kurikulum/{kurikulum}/edit', [KurikulumKelasController::class, 'edit'])->name('kurikulum.edit');
        Route::put('kurikulum/{kurikulum}', [KurikulumKelasController::class, 'update'])->name('kurikulum.update');
        Route::delete('kurikulum/{kurikulum}', [KurikulumKelasController::class, 'destroy'])->name('kurikulum.destroy');
    });

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

    // Jadwal
    Route::get('jadwal', [JadwalController::class, 'index'])->name('jadwal.index');
    Route::middleware('permission:manage-settings')->group(function () {
        Route::get('jadwal/create', [JadwalController::class, 'create'])->name('jadwal.create');
        Route::post('jadwal', [JadwalController::class, 'store'])->name('jadwal.store');
        Route::get('jadwal/{jadwal}/edit', [JadwalController::class, 'edit'])->name('jadwal.edit');
        Route::put('jadwal/{jadwal}', [JadwalController::class, 'update'])->name('jadwal.update');
        Route::delete('jadwal/{jadwal}', [JadwalController::class, 'destroy'])->name('jadwal.destroy');
    });

    // Registrasi Akademik
    Route::get('registrasi', [RegistrasiAkademikController::class, 'index'])->name('registrasi.index');
    Route::middleware('permission:manage-siswa')->group(function () {
        Route::get('registrasi/create', [RegistrasiAkademikController::class, 'create'])->name('registrasi.create');
        Route::post('registrasi', [RegistrasiAkademikController::class, 'store'])->name('registrasi.store');
        Route::get('registrasi/{registrasi}/edit', [RegistrasiAkademikController::class, 'edit'])->name('registrasi.edit');
        Route::put('registrasi/{registrasi}', [RegistrasiAkademikController::class, 'update'])->name('registrasi.update');
        Route::delete('registrasi/{registrasi}', [RegistrasiAkademikController::class, 'destroy'])->name('registrasi.destroy');
    });

    // Absensi
    Route::get('absensi', [AdminAbsensiController::class, 'index'])->name('absensi.index');
    Route::get('absensi/{jadwal}/detail', [AdminAbsensiController::class, 'detail'])->name('absensi.detail');
    Route::patch('absensi/{jadwal}/lock', [AdminAbsensiController::class, 'lock'])->name('absensi.lock');
    Route::patch('absensi/{jadwal}/unlock', [AdminAbsensiController::class, 'unlock'])->name('absensi.unlock');

    // Hari Libur
    Route::get('hari-libur', [HariLiburController::class, 'index'])->name('hari-libur.index');
    Route::middleware('permission:manage-settings')->group(function () {
        Route::post('hari-libur', [HariLiburController::class, 'store'])->name('hari-libur.store');
        Route::delete('hari-libur/{hariLibur}', [HariLiburController::class, 'destroy'])->name('hari-libur.destroy');
        Route::post('hari-libur/adopt', [HariLiburController::class, 'adopt'])->name('hari-libur.adopt');
        Route::post('hari-libur/adopt-all', [HariLiburController::class, 'adoptAll'])->name('hari-libur.adopt-all');
    });

    // Poin
    Route::get('master-poin', [MasterPoinController::class, 'index'])->name('master-poin.index');
    Route::middleware('permission:manage-settings')->group(function () {
        Route::get('master-poin/create', [MasterPoinController::class, 'create'])->name('master-poin.create');
        Route::post('master-poin', [MasterPoinController::class, 'store'])->name('master-poin.store');
        Route::get('master-poin/{masterPoin}/edit', [MasterPoinController::class, 'edit'])->name('master-poin.edit');
        Route::put('master-poin/{masterPoin}', [MasterPoinController::class, 'update'])->name('master-poin.update');
        Route::delete('master-poin/{masterPoin}', [MasterPoinController::class, 'destroy'])->name('master-poin.destroy');
    });
    Route::get('log-poin', [LogPoinController::class, 'index'])->name('log-poin.index');
    Route::middleware('permission:manage-settings')->group(function () {
        Route::post('log-poin', [LogPoinController::class, 'store'])->name('log-poin.store');
        Route::delete('log-poin/{logPoin}', [LogPoinController::class, 'destroy'])->name('log-poin.destroy');
    });

    // Laporan
    Route::prefix('laporan')->name('laporan.')->group(function () {
        Route::get('/', [LaporanController::class, 'index'])->name('index');
        Route::get('/rekap-absensi', [LaporanController::class, 'rekapAbsensi'])->name('rekap-absensi');
        Route::get('/rekap-absensi/detail', [LaporanController::class, 'detailAbsensi'])->name('rekap-absensi.detail');
        Route::get('/export-absensi-excel', [LaporanController::class, 'exportAbsensiExcel'])->name('export-absensi-excel');
        Route::get('/export-absensi-pdf', [LaporanController::class, 'exportAbsensiPdf'])->name('export-absensi-pdf');
        Route::get('/rekap-poin', [LaporanController::class, 'rekapPoin'])->name('rekap-poin');
        Route::get('/export-poin-excel', [LaporanController::class, 'exportPoinExcel'])->name('export-poin-excel');
        Route::get('/export-poin-pdf', [LaporanController::class, 'exportPoinPdf'])->name('export-poin-pdf');
    });

    // Setting
    Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::patch('settings', [SettingsController::class, 'update'])->name('settings.update');
});
