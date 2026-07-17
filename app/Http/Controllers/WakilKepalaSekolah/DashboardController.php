<?php

namespace App\Http\Controllers\WakilKepalaSekolah;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\RegistrasiAkademik;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $guru = $user->guru;
        $instansi = $user->getInstansi();

        $totalGuru = Guru::where('instansi_id', $instansi->id_instansi)->whereNull('status')->count();
        $totalKelas = Kelas::where('instansi_id', $instansi->id_instansi)->count();
        $totalSiswaAktif = RegistrasiAkademik::aktif()
            ->whereHas('kelas', fn($q) => $q->where('instansi_id', $instansi->id_instansi))
            ->count();

        $absensiHariIni = Absensi::whereDate('tanggal', now()->toDateString())
            ->whereHas('registrasi.kelas', fn($q) => $q->where('instansi_id', $instansi->id_instansi))
            ->count();

        return view('wakil-kepala-sekolah.dashboard', compact(
            'guru', 'instansi', 'totalGuru', 'totalKelas', 'totalSiswaAktif', 'absensiHariIni'
        ));
    }
}
