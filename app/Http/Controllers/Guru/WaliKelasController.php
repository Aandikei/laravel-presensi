<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateExport;
use App\Models\Absensi;
use App\Models\ExportJob;
use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\LogPoinSiswa;
use App\Models\MasterPoin;
use App\Models\MataPelajaran;
use App\Models\RegistrasiAkademik;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WaliKelasController extends Controller
{
    public function siswaByKelas(Kelas $kelas)
    {
        $guru = Auth::user()->guru;
        abort_if($kelas->guru_wali_id !== $guru->id_guru, 403);

        $siswa = RegistrasiAkademik::with('siswa')
            ->aktif()
            ->where('kelas_id', $kelas->id_kelas)
            ->whereHas('tahunAjaran', fn($q) => $q->where('is_aktif', true))
            ->whereHas('siswa', fn($q) => $q->whereNull('status')->where('instansi_id', $kelas->instansi_id))
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
            ->aktif()
            ->whereHas('tahunAjaran', fn($q) => $q->where('is_aktif', true))
            ->whereHas('siswa', fn($q) => $q->whereNull('status')->where('instansi_id', $kelasSaya->instansi_id))
            ->exists();
        abort_if(!$isSiswaSaya, 403);

        LogPoinSiswa::create([
            'instansi_id' => $kelasSaya->instansi_id,
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

        $siswa = RegistrasiAkademik::with(['siswa' => fn($q) => $q
            ->with(['logPoin' => fn($q2) => $q2->where('instansi_id', $kelasSaya->instansi_id)])
            ->with(['logPoin.masterPoin']),
        ])
            ->aktif()
            ->where('kelas_id', $kelasSaya->id_kelas)
            ->whereHas('tahunAjaran', fn($q) => $q->where('is_aktif', true))
            ->whereHas('siswa', fn($q) => $q->whereNull('status')->where('instansi_id', $kelasSaya->instansi_id))
            ->get()
            ->map(function ($reg) {
                $s = $reg->siswa;
                $s->total_poin = $s->logPoin->sum(fn($lp) => $lp->masterPoin?->jumlah_poin ?? 0);
                return $s;
            })
            ->filter()
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
            ->whereRaw('tahun_id = (SELECT MAX(r2.tahun_id) FROM registrasi_akademik r2 WHERE r2.siswa_id = registrasi_akademik.siswa_id AND r2.status = ?)', ['Aktif'])
            ->whereHas('siswa', fn($q) => $q->where('instansi_id', $kelasSaya->instansi_id))
            ->pluck('siswa_id');

        $logPoin = LogPoinSiswa::with(['siswa', 'masterPoin', 'createdBy'])
            ->whereIn('siswa_id', $siswaIds)
            ->where('instansi_id', $kelasSaya->instansi_id)
            ->orderBy('tanggal', 'desc')
            ->orderBy('id_log_poin', 'desc')
            ->get();

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
            ->aktif()
            ->whereHas('tahunAjaran', fn($q) => $q->where('is_aktif', true))
            ->whereHas('siswa', fn($q) => $q->whereNull('status')->where('instansi_id', $kelasSaya->instansi_id))
            ->exists();
        abort_if(!$isSiswaSaya, 403);

        $logPoin->delete();

        return back()->with('success', 'Poin berhasil dihapus dari ' . ($logPoin->siswa->nama_siswa ?? '-'));
    }

    public function rekapAbsensi(Request $request)
    {
        $guru = Auth::user()->guru;
        $kelasSaya = Kelas::where('guru_wali_id', $guru->id_guru)->first();
        abort_if(!$kelasSaya, 403);

        $bulan = $request->input('bulan', now()->month);
        $tahun = $request->input('tahun', now()->year);
        $mapelId = $request->input('mapel_id');

            $riwayat = Absensi::selectRaw('
                absensi.jadwal_id,
                absensi.tanggal,
                absensi.cakupan,
                COUNT(*) as total_siswa,
                SUM(absensi.status = "Hadir") as hadir,
                SUM(absensi.status = "Sakit") as sakit,
                SUM(absensi.status = "Izin") as izin,
                SUM(absensi.status = "Alpa") as alpa,
                SUM(absensi.status = "Terlambat") as terlambat,
                SUM(absensi.status = "Bolos") as bolos
            ')
            ->join('jadwal', 'absensi.jadwal_id', '=', 'jadwal.id_jadwal')
            ->join('kurikulum_kelas', 'jadwal.kurikulum_id', '=', 'kurikulum_kelas.id_kurikulum')
            ->where('kurikulum_kelas.kelas_id', $kelasSaya->id_kelas)
            ->when($mapelId, fn ($q) => $q->whereHas('jadwal.kurikulum', fn ($qq) => $qq->where('mapel_id', $mapelId)))
            ->whereMonth('absensi.tanggal', $bulan)
            ->whereYear('absensi.tanggal', $tahun)
            ->groupBy('absensi.jadwal_id', 'absensi.tanggal', 'absensi.cakupan')
            ->orderBy('absensi.tanggal', 'desc')
            ->orderBy('absensi.jadwal_id')
            ->get();

        $jadwalIds = $riwayat->pluck('jadwal_id')->unique();
        $jadwals = Jadwal::with(['kurikulum.kelas', 'kurikulum.mataPelajaran', 'kurikulum.guru'])
            ->whereIn('id_jadwal', $jadwalIds)
            ->get()
            ->keyBy('id_jadwal');

        $riwayat = $riwayat->transform(function ($item) use ($jadwals) {
            $j = $jadwals->get($item->jadwal_id);
            $item->kelas_nama  = $j?->kurikulum?->kelas?->nama_kelas ?? '-';
            $item->mapel_nama  = $j?->kurikulum?->mataPelajaran?->nama_mapel ?? '-';
            $item->jam         = $j ? (substr($j->jam_mulai, 0, 5) . ' - ' . substr($j->jam_selesai, 0, 5)) : '-';
            $item->guru        = $j?->kurikulum?->guru;
            $item->guru_nama   = $item->guru?->nama_guru ?? '-';
            return $item;
        });

        $bulanNama = \Carbon\Carbon::createFromDate($tahun, $bulan, 1)
            ->locale('id')->monthName;

        $mapels = MataPelajaran::where('instansi_id', $kelasSaya->instansi_id)
            ->whereHas('kurikulum', fn ($q) => $q->where('kelas_id', $kelasSaya->id_kelas))
            ->get();

        return view('guru.wali-kelas.rekap-absensi', compact(
            'kelasSaya', 'riwayat', 'bulanNama', 'bulan', 'tahun', 'mapels', 'mapelId'
        ));
    }

    public function detailAbsensi(Request $request)
    {
        $request->validate([
            'jadwal_id' => 'required|exists:jadwal,id_jadwal',
            'tanggal'   => 'required|date',
        ]);

        $guru = Auth::user()->guru;
        $kelasSaya = Kelas::where('guru_wali_id', $guru->id_guru)->first();
        abort_if(!$kelasSaya, 403);

        $jadwal = Jadwal::with(['kurikulum.kelas', 'kurikulum.mataPelajaran', 'kurikulum.guru'])
            ->findOrFail($request->jadwal_id);

        abort_if($jadwal->kurikulum->kelas->instansi_id !== $guru->instansi_id, 403);

        $absensi = Absensi::with('registrasi.siswa')
            ->where('jadwal_id', $request->jadwal_id)
            ->whereDate('tanggal', $request->tanggal)
            ->orderBy('status')
            ->get();

        return view('guru.wali-kelas.rekap-absensi-detail', compact('jadwal', 'absensi', 'kelasSaya'));
    }

    // ── Export Absensi Excel via Queue ────────
    public function exportAbsensiExcel(Request $request)
    {
        $guru = Auth::user()->guru;
        $kelasSaya = Kelas::where('guru_wali_id', $guru->id_guru)->first();
        abort_if(!$kelasSaya, 403);

        $exportJob = ExportJob::create([
            'user_id' => Auth::id(),
            'type'    => 'absensi-excel',
            'source'  => 'guru',
            'filters' => [
                'kelas_id' => $kelasSaya->id_kelas,
                'bulan'    => $request->input('bulan', now()->month),
                'tahun'    => $request->input('tahun', now()->year),
                'mapel_id' => $request->input('mapel_id'),
            ],
            'status'  => 'pending',
        ]);

        GenerateExport::dispatch($exportJob);

        return redirect()->route('guru.wali-kelas.rekap-absensi', $request->only(['bulan', 'tahun', 'mapel_id']))
            ->with('info', 'Export Excel sedang diproses. Cek "Export Saya" di halaman Laporan.');
    }

    // ── Export Absensi PDF via Queue ──────────
    public function exportAbsensiPdf(Request $request)
    {
        $guru = Auth::user()->guru;
        $kelasSaya = Kelas::where('guru_wali_id', $guru->id_guru)->first();
        abort_if(!$kelasSaya, 403);

        $exportJob = ExportJob::create([
            'user_id' => Auth::id(),
            'type'    => 'absensi-pdf',
            'source'  => 'guru',
            'filters' => [
                'kelas_id' => $kelasSaya->id_kelas,
                'bulan'    => $request->input('bulan', now()->month),
                'tahun'    => $request->input('tahun', now()->year),
                'mapel_id' => $request->input('mapel_id'),
            ],
            'status'  => 'pending',
        ]);

        GenerateExport::dispatch($exportJob);

        return redirect()->route('guru.wali-kelas.rekap-absensi', $request->only(['bulan', 'tahun', 'mapel_id']))
            ->with('info', 'Export PDF sedang diproses. Cek "Export Saya" di halaman Laporan.');
    }

    // ── Rekap Poin ──────────────────────────────
    public function rekapPoin(Request $request)
    {
        $guru = Auth::user()->guru;
        $kelasSaya = Kelas::where('guru_wali_id', $guru->id_guru)->first();
        abort_if(!$kelasSaya, 403);

        $bulan = $request->input('bulan', now()->month);
        $tahun = $request->input('tahun', now()->year);

        $siswaIds = RegistrasiAkademik::where('kelas_id', $kelasSaya->id_kelas)
            ->aktif()
            ->pluck('siswa_id');

        $siswa = Siswa::whereIn('id_siswa', $siswaIds)
            ->where('instansi_id', $kelasSaya->instansi_id)
            ->with(['logPoin' => fn($q) => $q->where('instansi_id', $kelasSaya->instansi_id)->whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun), 'logPoin.masterPoin'])
            ->orderBy('nama_siswa')
            ->get()
            ->map(function ($s) {
                $s->jumlah_pelanggaran = $s->logPoin->count();
                $totalPoin = $s->logPoin->sum(fn($l) => $l->masterPoin->jumlah_poin ?? 0);
                $s->total_poin = $totalPoin;
                $s->status_poin = $totalPoin >= 100 ? 'PERHATIAN' : ($totalPoin >= 50 ? 'WASPADA' : 'AMAN');
                $s->tanggal_terakhir = $s->logPoin->max('tanggal');
                return $s;
            });

        $bulanNama = \Carbon\Carbon::createFromDate($tahun, $bulan, 1)->locale('id')->monthName;

        return view('guru.wali-kelas.rekap-poin', compact('kelasSaya', 'siswa', 'bulan', 'tahun', 'bulanNama'));
    }

    public function exportPoinExcel(Request $request)
    {
        $guru = Auth::user()->guru;
        $kelasSaya = Kelas::where('guru_wali_id', $guru->id_guru)->first();
        abort_if(!$kelasSaya, 403);

        $exportJob = ExportJob::create([
            'user_id' => Auth::id(),
            'type'    => 'poin-excel',
            'source'  => 'guru',
            'filters' => [
                'kelas_id' => $kelasSaya->id_kelas,
                'bulan'    => $request->input('bulan', now()->month),
                'tahun'    => $request->input('tahun', now()->year),
            ],
            'status'  => 'pending',
        ]);

        GenerateExport::dispatch($exportJob);

        return redirect()->route('guru.wali-kelas.rekap-poin', $request->only(['bulan', 'tahun']))
            ->with('info', 'Export Excel sedang diproses. Cek "Export Saya" di halaman Laporan.');
    }

    public function exportPoinPdf(Request $request)
    {
        $guru = Auth::user()->guru;
        $kelasSaya = Kelas::where('guru_wali_id', $guru->id_guru)->first();
        abort_if(!$kelasSaya, 403);

        $exportJob = ExportJob::create([
            'user_id' => Auth::id(),
            'type'    => 'poin-pdf',
            'source'  => 'guru',
            'filters' => [
                'kelas_id' => $kelasSaya->id_kelas,
                'bulan'    => $request->input('bulan', now()->month),
                'tahun'    => $request->input('tahun', now()->year),
            ],
            'status'  => 'pending',
        ]);

        GenerateExport::dispatch($exportJob);

        return redirect()->route('guru.wali-kelas.rekap-poin', $request->only(['bulan', 'tahun']))
            ->with('info', 'Export PDF sedang diproses. Cek "Export Saya" di halaman Laporan.');
    }
}
