<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\LogPoinSiswa;
use App\Models\MasterPoin;
use App\Models\RegistrasiAkademik;
use App\Models\RekapBulanan;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WaliKelasController extends Controller
{
    public function index()
    {
        $guru = Auth::user()->guru;

        $kelasSaya = Kelas::where('guru_wali_id', $guru->id_guru)
            ->with(['instansi'])
            ->first();

        if (!$kelasSaya) {
            return redirect()->route('guru.dashboard')
                ->with('error', 'Anda belum ditugaskan sebagai wali kelas.');
        }

        $siswaIds = RegistrasiAkademik::where('kelas_id', $kelasSaya->id_kelas)
            ->whereHas('tahunAjaran', fn($q) => $q->where('is_aktif', true))
            ->pluck('siswa_id');

        $jumlahSiswa = $siswaIds->count();

        $rekapBulanIni = RekapBulanan::whereHas('registrasi', function ($q) use ($kelasSaya) {
                $q->where('kelas_id', $kelasSaya->id_kelas);
            })
            ->where('bulan', now()->month)
            ->where('tahun', now()->year)
            ->get();

        $totalHadir = $rekapBulanIni->sum('hadir');
        $totalPertemuan = $rekapBulanIni->sum(fn($r) => $r->total_pertemuan);
        $rataKehadiran = $totalPertemuan > 0
            ? round(($totalHadir / $totalPertemuan) * 100, 1)
            : 0;

        $siswaPoinTinggi = Siswa::whereIn('id_siswa', $siswaIds)
            ->withSum(['logPoin as poin_bulan_ini' => function ($q) {
                $q->whereMonth('tanggal', now()->month)
                  ->whereYear('tanggal', now()->year);
            }], 'poin_id')
            ->orderByDesc('poin_bulan_ini')
            ->take(5)
            ->get()
            ->map(function ($siswa) {
                $siswa->poin_bulan_ini = (int) $siswa->poin_bulan_ini;
                if ($siswa->poin_bulan_ini > 0) {
                    $totalPoin = LogPoinSiswa::where('siswa_id', $siswa->id_siswa)
                        ->whereMonth('tanggal', now()->month)
                        ->whereYear('tanggal', now()->year)
                        ->join('master_poin', 'log_poin_siswa.poin_id', '=', 'master_poin.id_poin')
                        ->sum('master_poin.jumlah_poin');
                    $siswa->poin_bulan_ini = $totalPoin;
                }
                return $siswa;
            })
            ->filter(fn($s) => $s->poin_bulan_ini > 0)
            ->values();

        $hariMap = [
            'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu', 'Sunday' => 'Minggu',
        ];
        $hariIni = $hariMap[now()->format('l')] ?? null;

        $jadwalHariIni = Jadwal::with(['kurikulum.kelas', 'kurikulum.mataPelajaran'])
            ->whereHas('kurikulum', fn($q) => $q->where('guru_id', $guru->id_guru))
            ->where('hari', $hariIni)
            ->orderBy('jam_mulai')
            ->get()
            ->map(function ($jadwal) {
                $jadwal->sudah_input = $jadwal->absensi()
                    ->where('tanggal', now()->toDateString())
                    ->exists();
                return $jadwal;
            });

        $masterPoin = MasterPoin::where('instansi_id', $kelasSaya->instansi_id)
            ->orderBy('nama_pelanggaran')
            ->get();

        return view('guru.wali-kelas.dashboard', compact(
            'guru', 'kelasSaya', 'jumlahSiswa', 'rataKehadiran',
            'siswaPoinTinggi', 'jadwalHariIni', 'hariIni', 'masterPoin'
        ));
    }

    public function siswaByKelas(Kelas $kelas)
    {
        $guru = Auth::user()->guru;
        abort_if($kelas->guru_wali_id !== $guru->id_guru, 403);

        $siswa = RegistrasiAkademik::with('siswa')
            ->where('kelas_id', $kelas->id_kelas)
            ->whereHas('tahunAjaran', fn($q) => $q->where('is_aktif', true))
            ->get()
            ->map(function ($reg) {
                return $reg->siswa;
            })
            ->filter();

        return response()->json($siswa->values());
    }

    public function tambahPoin(Request $request)
    {
        $guru = Auth::user()->guru;

        $validated = $request->validate([
            'siswa_id'   => 'required|exists:siswa,id_siswa',
            'poin_id'    => 'required|exists:master_poin,id_poin',
            'keterangan' => 'nullable|string|max:255',
        ]);

        $siswa = Siswa::findOrFail($validated['siswa_id']);

        $kelasSaya = Kelas::where('guru_wali_id', $guru->id_guru)->first();
        abort_if(!$kelasSaya, 403);

        $isSiswaSaya = RegistrasiAkademik::where('kelas_id', $kelasSaya->id_kelas)
            ->where('siswa_id', $siswa->id_siswa)
            ->whereHas('tahunAjaran', fn($q) => $q->where('is_aktif', true))
            ->exists();
        abort_if(!$isSiswaSaya, 403);

        LogPoinSiswa::create([
            'siswa_id'   => $validated['siswa_id'],
            'poin_id'    => $validated['poin_id'],
            'tanggal'    => now()->toDateString(),
            'keterangan' => $validated['keterangan'] ?? null,
            'created_by' => Auth::id(),
        ]);

        return back()->with('success', 'Poin berhasil ditambahkan ke ' . $siswa->nama_siswa);
    }

    public function daftarSiswa()
    {
        $guru = Auth::user()->guru;
        $kelasSaya = Kelas::where('guru_wali_id', $guru->id_guru)->first();
        abort_if(!$kelasSaya, 403);

        $siswa = Siswa::whereHas('registrasiAkademik', function ($q) use ($kelasSaya) {
                $q->where('kelas_id', $kelasSaya->id_kelas)
                  ->whereHas('tahunAjaran', fn($qq) => $qq->where('is_aktif', true));
            })
            ->with(['logPoin.masterPoin'])
            ->get()
            ->map(function ($s) {
                $s->total_poin = $s->logPoin->sum(fn($lp) => $lp->masterPoin?->jumlah_poin ?? 0);
                return $s;
            })
            ->sortByDesc('total_poin')
            ->values();

        $masterPoin = MasterPoin::where('instansi_id', $kelasSaya->instansi_id)
            ->orderBy('nama_pelanggaran')
            ->get();

        return view('guru.wali-kelas.siswa-poin', compact('kelasSaya', 'siswa', 'masterPoin'));
    }

    public function logPoin()
    {
        $guru = Auth::user()->guru;
        $kelasSaya = Kelas::where('guru_wali_id', $guru->id_guru)->first();
        abort_if(!$kelasSaya, 403);

        $siswaIds = RegistrasiAkademik::where('kelas_id', $kelasSaya->id_kelas)
            ->whereHas('tahunAjaran', fn($q) => $q->where('is_aktif', true))
            ->pluck('siswa_id');

        $logPoin = LogPoinSiswa::with(['siswa', 'masterPoin', 'createdBy'])
            ->whereIn('siswa_id', $siswaIds)
            ->orderBy('tanggal', 'desc')
            ->orderBy('id_log_poin', 'desc')
            ->paginate(30);

        $masterPoin = MasterPoin::where('instansi_id', $kelasSaya->instansi_id)
            ->orderBy('nama_pelanggaran')
            ->get();

        return view('guru.wali-kelas.log-poin', compact('kelasSaya', 'logPoin', 'masterPoin'));
    }

    public function hapusPoin($id)
    {
        $guru = Auth::user()->guru;
        $logPoin = LogPoinSiswa::with(['siswa'])->findOrFail($id);

        $kelasSaya = Kelas::where('guru_wali_id', $guru->id_guru)->first();
        abort_if(!$kelasSaya, 403);

        $isSiswaSaya = RegistrasiAkademik::where('kelas_id', $kelasSaya->id_kelas)
            ->where('siswa_id', $logPoin->siswa_id)
            ->whereHas('tahunAjaran', fn($q) => $q->where('is_aktif', true))
            ->exists();
        abort_if(!$isSiswaSaya, 403);

        $logPoin->delete();

        return back()->with('success', 'Poin berhasil dihapus dari ' . ($logPoin->siswa->nama_siswa ?? '-'));
    }
}
