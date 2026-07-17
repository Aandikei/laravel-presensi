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
        $totalGuru   = Guru::where('instansi_id', $instansi->id_instansi)->whereNull('status')->count();
        $totalSiswa  = Siswa::where('instansi_id', $instansi->id_instansi)->whereNull('status')->count();
        $totalKelas  = Kelas::where('instansi_id', $instansi->id_instansi)->count();

        // Kehadiran hari ini
        $hariIni       = now()->toDateString();
        $namaLibur     = HariLibur::getNamaLibur($hariIni, $instansi->id_instansi);
        $totalAbsensi  = $namaLibur ? 0 : Absensi::whereHas('registrasi.kelas', fn($q) =>
                $q->where('instansi_id', $instansi->id_instansi)
            )
            ->where('tanggal', $hariIni)
            ->count();
        $totalHadir    = $namaLibur ? 0 : Absensi::whereHas('registrasi.kelas', fn($q) =>
                $q->where('instansi_id', $instansi->id_instansi)
            )
            ->where('tanggal', $hariIni)
            ->where('status', 'Hadir')
            ->count();
        $persenHadir   = $namaLibur ? 0 : ($totalAbsensi > 0
            ? round(($totalHadir / $totalAbsensi) * 100, 1)
            : 0);

        // Chart: kehadiran 7 hari terakhir (2 query total, bukan 14)
        $dates = collect();
        for ($i = 6; $i >= 0; $i--) {
            $dates->push(now()->subDays($i)->toDateString());
        }

        $chartTotals = Absensi::whereHas('registrasi.kelas', fn($q) =>
            $q->where('instansi_id', $instansi->id_instansi)
        )
            ->whereIn('tanggal', $dates)
            ->selectRaw('tanggal, count(*) as total')
            ->groupBy('tanggal')
            ->pluck('total', 'tanggal');

        $chartHadir = Absensi::whereHas('registrasi.kelas', fn($q) =>
            $q->where('instansi_id', $instansi->id_instansi)
        )
            ->whereIn('tanggal', $dates)
            ->where('status', 'Hadir')
            ->selectRaw('tanggal, count(*) as total')
            ->groupBy('tanggal')
            ->pluck('total', 'tanggal');

        $chartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $tanggal = now()->subDays($i)->toDateString();
            $label   = now()->subDays($i)->locale('id')->dayName;
            $total   = (int) ($chartTotals[$tanggal] ?? 0);
            $hadir   = (int) ($chartHadir[$tanggal] ?? 0);

            $chartData[] = [
                'label'  => $label,
                'hadir'  => $hadir,
                'total'  => $total,
                'persen' => $total > 0 ? round(($hadir / $total) * 100, 1) : 0,
            ];
        }

        // Absensi terbaru hari ini
        $absensiTerbaru = $namaLibur ? collect() : Absensi::with([
                'registrasi.siswa',
                'registrasi.kelas',
                'jadwal.kurikulum.mataPelajaran'
            ])
            ->whereHas('registrasi.kelas', fn($q) =>
                $q->where('instansi_id', $instansi->id_instansi)
            )
            ->where('tanggal', $hariIni)
            ->whereIn('status', ['Alpa', 'Bolos', 'Terlambat'])
            ->latest()
            ->take(10)
            ->get();

        // Distribusi status hari ini
        $distribusi = $namaLibur ? collect() : Absensi::whereHas('registrasi.kelas', fn($q) =>
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