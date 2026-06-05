<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\Absensi;
use App\Models\HariLibur;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    public function index()
    {
        $instansi = Auth::user()->getInstansi();

        // Stats
        $totalGuru   = Guru::where('instansi_id', $instansi->id_instansi)->count();
        $totalSiswa  = Siswa::where('instansi_id', $instansi->id_instansi)->count();
        $totalKelas  = Kelas::where('instansi_id', $instansi->id_instansi)->count();

        // Kehadiran hari ini
        $hariIni       = now()->toDateString();
        $namaLibur     = HariLibur::getNamaLibur($hariIni, $instansi->id_instansi);
        $totalAbsensi  = Absensi::whereHas('registrasi.kelas', fn($q) =>
                $q->where('instansi_id', $instansi->id_instansi)
            )
            ->where('tanggal', $hariIni)
            ->count();
        $totalHadir    = Absensi::whereHas('registrasi.kelas', fn($q) =>
                $q->where('instansi_id', $instansi->id_instansi)
            )
            ->where('tanggal', $hariIni)
            ->where('status', 'Hadir')
            ->count();
        $persenHadir   = $totalAbsensi > 0
            ? round(($totalHadir / $totalAbsensi) * 100, 1)
            : 0;

        // Chart: kehadiran 7 hari terakhir
        $chartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $tanggal = now()->subDays($i)->toDateString();
            $label   = now()->subDays($i)->locale('id')->dayName;

            $total = Absensi::whereHas('registrasi.kelas', fn($q) =>
                    $q->where('instansi_id', $instansi->id_instansi)
                )
                ->where('tanggal', $tanggal)
                ->count();

            $hadir = Absensi::whereHas('registrasi.kelas', fn($q) =>
                    $q->where('instansi_id', $instansi->id_instansi)
                )
                ->where('tanggal', $tanggal)
                ->where('status', 'Hadir')
                ->count();

            $chartData[] = [
                'label'  => $label,
                'hadir'  => $hadir,
                'total'  => $total,
                'persen' => $total > 0 ? round(($hadir / $total) * 100, 1) : 0,
            ];
        }

        // Absensi terbaru hari ini
        $absensiTerbaru = Absensi::with([
                'registrasi.siswa',
                'registrasi.kelas',
                'jadwal.kurikulum.mataPelajaran'
            ])
            ->whereHas('registrasi.kelas', fn($q) =>
                $q->where('instansi_id', $instansi->id_instansi)
            )
            ->where('tanggal', $hariIni)
            ->whereIn('status', ['Alpa', 'Cabut', 'Terlambat'])
            ->latest()
            ->take(10)
            ->get();

        // Distribusi status hari ini
        $distribusi = Absensi::whereHas('registrasi.kelas', fn($q) =>
                $q->where('instansi_id', $instansi->id_instansi)
            )
            ->where('tanggal', $hariIni)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('admin.dashboard', compact(
            'totalGuru',
            'totalSiswa',
            'totalKelas',
            'totalAbsensi',
            'totalHadir',
            'persenHadir',
            'namaLibur',
            'chartData',
            'absensiTerbaru',
            'distribusi'
        ));
    }
}