<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\LogPoinSiswa;
use App\Models\RegistrasiAkademik;
use Illuminate\Support\Facades\Auth;

class SiswaDashboardController extends Controller
{
    private function getSiswa()
    {
        return Auth::user()->siswa;
    }

    private function getRegistrasiAktif()
    {
        return RegistrasiAkademik::where('siswa_id', $this->getSiswa()->id_siswa)
            ->whereHas('tahunAjaran', fn($q) => $q->where('is_aktif', true))
            ->with(['kelas', 'tahunAjaran'])
            ->first();
    }

    public function index()
    {
        $siswa      = $this->getSiswa();
        $registrasi = $this->getRegistrasiAktif();

        $bulan = now()->month;
        $tahun = now()->year;

        // Stats bulan ini
        $absensi = $registrasi
            ? Absensi::where('reg_id', $registrasi->id_registrasi)
                ->whereMonth('tanggal', $bulan)
                ->whereYear('tanggal', $tahun)
                ->get()
            : collect();

        $stats = [
            'hadir'     => $absensi->where('status', 'Hadir')->count(),
            'sakit'     => $absensi->where('status', 'Sakit')->count(),
            'izin'      => $absensi->where('status', 'Izin')->count(),
            'alpa'      => $absensi->where('status', 'Alpa')->count(),
            'terlambat' => $absensi->where('status', 'Terlambat')->count(),
            'bolos'     => $absensi->where('status', 'Bolos')->count(),
            'total'     => $absensi->count(),
        ];
        $stats['persen'] = $stats['total'] > 0
            ? round(($stats['hadir'] / $stats['total']) * 100, 1)
            : 0;

        // Total poin bulan ini
        $totalPoin = $siswa
            ? LogPoinSiswa::where('siswa_id', $siswa->id_siswa)
                ->whereMonth('tanggal', $bulan)
                ->whereYear('tanggal', $tahun)
                ->join('master_poin', 'log_poin_siswa.poin_id', '=', 'master_poin.id_poin')
                ->sum('master_poin.jumlah_poin')
            : 0;

        // Absensi terbaru
        $absensiTerbaru = $registrasi
            ? Absensi::where('reg_id', $registrasi->id_registrasi)
                ->with(['jadwal.kurikulum.mataPelajaran'])
                ->latest('tanggal')
                ->take(5)
                ->get()
            : collect();

        $bulanNama = now()->locale('id')->monthName;

        return view('siswa.dashboard', compact(
            'siswa', 'registrasi', 'stats', 'totalPoin',
            'absensiTerbaru', 'bulanNama'
        ));
    }

    public function absensi()
    {
        $siswa      = $this->getSiswa();
        $registrasi = $this->getRegistrasiAktif();

        $bulan = request('bulan', now()->month);
        $tahun = request('tahun', now()->year);

        $absensi = $registrasi
            ? Absensi::where('reg_id', $registrasi->id_registrasi)
                ->with(['jadwal.kurikulum.mataPelajaran', 'jadwal.kurikulum.guru'])
                ->whereMonth('tanggal', $bulan)
                ->whereYear('tanggal', $tahun)
                ->orderBy('tanggal', 'desc')
                ->get()
            : collect();

        $stats = [
            'hadir'     => $absensi->where('status', 'Hadir')->count(),
            'sakit'     => $absensi->where('status', 'Sakit')->count(),
            'izin'      => $absensi->where('status', 'Izin')->count(),
            'alpa'      => $absensi->where('status', 'Alpa')->count(),
            'terlambat' => $absensi->where('status', 'Terlambat')->count(),
            'bolos'     => $absensi->where('status', 'Bolos')->count(),
            'total'     => $absensi->count(),
        ];
        $stats['persen'] = $stats['total'] > 0
            ? round(($stats['hadir'] / $stats['total']) * 100, 1) : 0;

        $bulanNama = \Carbon\Carbon::createFromDate($tahun, $bulan, 1)->locale('id')->monthName;

        return view('siswa.absensi', compact(
            'siswa', 'registrasi', 'absensi', 'stats', 'bulanNama', 'bulan', 'tahun'
        ));
    }

    public function poin()
    {
        $siswa = $this->getSiswa();

        $bulan = request('bulan', now()->month);
        $tahun = request('tahun', now()->year);

        $logPoin = $siswa
            ? LogPoinSiswa::where('siswa_id', $siswa->id_siswa)
                ->with(['masterPoin', 'createdBy'])
                ->whereMonth('tanggal', $bulan)
                ->whereYear('tanggal', $tahun)
                ->orderBy('tanggal', 'desc')
                ->get()
            : collect();

        $totalPoin = $logPoin->sum(fn($l) => $l->masterPoin->jumlah_poin ?? 0);
        $bulanNama = \Carbon\Carbon::createFromDate($tahun, $bulan, 1)->locale('id')->monthName;

        return view('siswa.poin', compact(
            'siswa', 'logPoin', 'totalPoin', 'bulanNama', 'bulan', 'tahun'
        ));
    }
}