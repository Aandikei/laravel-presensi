<?php

namespace App\Http\Controllers\OrangTua;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\LogPoinSiswa;
use App\Models\RegistrasiAkademik;
use Illuminate\Support\Facades\Auth;

class OrangTuaDashboardController extends Controller
{
    private function getOrangTua()
    {
        return Auth::user()->orangTua;
    }

    private function getAnakAktif()
    {
        $ortu   = $this->getOrangTua();
        $anakId = request('anak_id');

        if (!$ortu) {
            return [collect(), null];
        }

        $anak = $ortu->siswa()->with('instansi')->get();

        $anakDipilih = $anakId
            ? $anak->firstWhere('id_siswa', $anakId)
            : $anak->first();

        return [$anak, $anakDipilih];
    }

    public function index()
    {
        [$anak, $anakDipilih] = $this->getAnakAktif();

        $bulan = now()->month;
        $tahun = now()->year;

        $registrasi = $anakDipilih
            ? RegistrasiAkademik::where('siswa_id', $anakDipilih->id_siswa)
                ->whereHas('tahunAjaran', fn($q) => $q->where('is_aktif', true))
                ->with(['kelas', 'tahunAjaran'])
                ->first()
            : null;

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
            'cabut'     => $absensi->where('status', 'Cabut')->count(),
            'total'     => $absensi->count(),
        ];
        $stats['persen'] = $stats['total'] > 0
            ? round(($stats['hadir'] / $stats['total']) * 100, 1) : 0;

        $totalPoin = $anakDipilih
            ? LogPoinSiswa::where('siswa_id', $anakDipilih->id_siswa)
                ->whereMonth('tanggal', $bulan)
                ->whereYear('tanggal', $tahun)
                ->join('master_poin', 'log_poin_siswa.poin_id', '=', 'master_poin.id_poin')
                ->sum('master_poin.jumlah_poin')
            : 0;

        $absensiTerbaru = $registrasi
            ? Absensi::where('reg_id', $registrasi->id_registrasi)
                ->with(['jadwal.kurikulum.mataPelajaran'])
                ->latest('tanggal')
                ->take(5)
                ->get()
            : collect();

        $bulanNama = now()->locale('id')->monthName;

        return view('orangtua.dashboard', compact(
            'anak', 'anakDipilih', 'registrasi', 'stats',
            'totalPoin', 'absensiTerbaru', 'bulanNama'
        ));
    }

    public function absensi()
    {
        [$anak, $anakDipilih] = $this->getAnakAktif();

        $bulan = request('bulan', now()->month);
        $tahun = request('tahun', now()->year);

        $registrasi = $anakDipilih
            ? RegistrasiAkademik::where('siswa_id', $anakDipilih->id_siswa)
                ->whereHas('tahunAjaran', fn($q) => $q->where('is_aktif', true))
                ->first()
            : null;

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
            'cabut'     => $absensi->where('status', 'Cabut')->count(),
            'total'     => $absensi->count(),
        ];
        $stats['persen'] = $stats['total'] > 0
            ? round(($stats['hadir'] / $stats['total']) * 100, 1) : 0;

        $bulanNama = \Carbon\Carbon::createFromDate($tahun, $bulan, 1)->locale('id')->monthName;

        return view('orangtua.absensi', compact(
            'anak', 'anakDipilih', 'registrasi', 'absensi',
            'stats', 'bulanNama', 'bulan', 'tahun'
        ));
    }

    public function poin()
    {
        [$anak, $anakDipilih] = $this->getAnakAktif();

        $bulan = request('bulan', now()->month);
        $tahun = request('tahun', now()->year);

        $logPoin = $anakDipilih
            ? LogPoinSiswa::where('siswa_id', $anakDipilih->id_siswa)
                ->with(['masterPoin', 'createdBy'])
                ->whereMonth('tanggal', $bulan)
                ->whereYear('tanggal', $tahun)
                ->orderBy('tanggal', 'desc')
                ->get()
            : collect();

        $totalPoin = $logPoin->sum(fn($l) => $l->masterPoin->jumlah_poin ?? 0);
        $bulanNama = \Carbon\Carbon::createFromDate($tahun, $bulan, 1)->locale('id')->monthName;

        return view('orangtua.poin', compact(
            'anak', 'anakDipilih', 'logPoin', 'totalPoin', 'bulanNama', 'bulan', 'tahun'
        ));
    }
}