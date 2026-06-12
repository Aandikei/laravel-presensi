<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Exports\AbsensiExport;
use App\Exports\PoinExport;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\TahunAjaran;
use App\Models\RegistrasiAkademik;
use App\Models\LogPoinSiswa;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class LaporanController extends Controller
{
    public function index()
    {
        $instansi = Auth::user()->getInstansi();

        $kelas = Kelas::where('instansi_id', $instansi->id_instansi)
            ->orderBy('tingkat')
            ->orderBy('nama_kelas')
            ->get();

        $mapel = MataPelajaran::where('instansi_id', $instansi->id_instansi)
            ->orderBy('nama_mapel')
            ->get();

        return view('admin.laporan.index', compact('kelas', 'mapel'));
    }

    // ── Preview Rekap Absensi ──────────────────
    public function rekapAbsensi(Request $request)
    {
        $request->validate([
            'kelas_id' => 'required|exists:kelas,id_kelas',
            'bulan'    => 'required|integer|min:1|max:12',
            'tahun'    => 'required|integer|min:2020',
        ]);

        $instansi = Auth::user()->getInstansi();
        $tahunAktif = TahunAjaran::where('is_aktif', true)
            ->where('instansi_id', $instansi->id_instansi)
            ->firstOrFail();

        $kelas    = Kelas::with(['waliKelas'])->findOrFail($request->kelas_id);
        abort_if($kelas->instansi_id !== $instansi->id_instansi, 403);

        $mapel = MataPelajaran::where('instansi_id', $instansi->id_instansi)
            ->orderBy('nama_mapel')->get();

        $registrasi = RegistrasiAkademik::with(['siswa', 'absensi' => function ($q) use ($request) {
                $q->whereMonth('tanggal', $request->bulan)
                  ->whereYear('tanggal', $request->tahun)
                  ->when($request->mapel_id, fn($q) =>
                      $q->whereHas('jadwal.kurikulum', fn($q) =>
                          $q->where('mapel_id', $request->mapel_id)
                      )
                  );
            }])
            ->where('kelas_id', $request->kelas_id)
            ->where('tahun_id', $tahunAktif->id_tahun)
            ->get()
            ->map(function($reg) {
                $absensi = $reg->absensi;

                $reg->hadir     = $absensi->where('status', 'Hadir')->count();
                $reg->sakit     = $absensi->where('status', 'Sakit')->count();
                $reg->izin      = $absensi->where('status', 'Izin')->count();
                $reg->alpa      = $absensi->where('status', 'Alpa')->count();
                $reg->terlambat = $absensi->where('status', 'Terlambat')->count();
                $reg->bolos     = $absensi->where('status', 'Bolos')->count();
                $reg->total     = $absensi->count();
                $reg->persen    = $reg->total > 0
                    ? round(($reg->hadir / $reg->total) * 100, 1)
                    : 0;
                return $reg;
            });

        $bulanNama = \Carbon\Carbon::createFromDate($request->tahun, $request->bulan, 1)
            ->locale('id')->monthName;

        return view('admin.laporan.rekap-absensi', compact(
            'registrasi', 'kelas', 'mapel', 'bulanNama', 'request'
        ));
    }

    // ── Export Excel Absensi ──────────────────
    public function exportAbsensiExcel(Request $request)
    {
        $request->validate([
            'kelas_id' => 'required|exists:kelas,id_kelas',
            'bulan'    => 'required|integer|min:1|max:12',
            'tahun'    => 'required|integer|min:2020',
        ]);

        $instansi  = Auth::user()->getInstansi();
        $tahunAktif = TahunAjaran::where('is_aktif', true)
            ->where('instansi_id', $instansi->id_instansi)
            ->firstOrFail();

        $kelas    = Kelas::findOrFail($request->kelas_id);
        abort_if($kelas->instansi_id !== $instansi->id_instansi, 403);

        $filename = 'rekap-absensi-' . $kelas->nama_kelas . '-' . $request->bulan . '-' . $request->tahun . '.xlsx';

        return Excel::download(
            new AbsensiExport($request->kelas_id, $request->bulan, $request->tahun, $request->mapel_id, $tahunAktif->id_tahun),
            $filename
        );
    }

    // ── Export PDF Absensi ────────────────────
    public function exportAbsensiPdf(Request $request)
    {
        $request->validate([
            'kelas_id' => 'required|exists:kelas,id_kelas',
            'bulan'    => 'required|integer|min:1|max:12',
            'tahun'    => 'required|integer|min:2020',
        ]);

        $instansi = Auth::user()->getInstansi();
        $tahunAktif = TahunAjaran::where('is_aktif', true)
            ->where('instansi_id', $instansi->id_instansi)
            ->firstOrFail();

        $kelas    = Kelas::with(['waliKelas'])->findOrFail($request->kelas_id);
        abort_if($kelas->instansi_id !== $instansi->id_instansi, 403);

        $registrasi = RegistrasiAkademik::with(['siswa', 'absensi' => function ($q) use ($request) {
                $q->whereMonth('tanggal', $request->bulan)
                  ->whereYear('tanggal', $request->tahun);
            }])
            ->where('kelas_id', $request->kelas_id)
            ->where('tahun_id', $tahunAktif->id_tahun)
            ->get()
            ->map(function($reg) {
                $absensi = $reg->absensi;

                $reg->hadir     = $absensi->where('status', 'Hadir')->count();
                $reg->sakit     = $absensi->where('status', 'Sakit')->count();
                $reg->izin      = $absensi->where('status', 'Izin')->count();
                $reg->alpa      = $absensi->where('status', 'Alpa')->count();
                $reg->terlambat = $absensi->where('status', 'Terlambat')->count();
                $reg->bolos     = $absensi->where('status', 'Bolos')->count();
                $reg->total     = $absensi->count();
                $reg->persen    = $reg->total > 0
                    ? round(($reg->hadir / $reg->total) * 100, 1) : 0;
                return $reg;
            });

        $bulanNama = \Carbon\Carbon::createFromDate($request->tahun, $request->bulan, 1)
            ->locale('id')->monthName;

        $pdf = Pdf::loadView('admin.laporan.pdf.rekap-absensi', compact(
            'registrasi', 'kelas', 'bulanNama', 'request'
        ))->setPaper('a4', 'landscape');

        return $pdf->download('rekap-absensi-' . $kelas->nama_kelas . '-' . $request->bulan . '-' . $request->tahun . '.pdf');
    }

    // ── Preview Rekap Poin ────────────────────
    public function rekapPoin(Request $request)
    {
        $request->validate([
            'bulan' => 'required|integer|min:1|max:12',
            'tahun' => 'required|integer|min:2020',
        ]);

        $instansi = Auth::user()->getInstansi();

        $kelas = Kelas::where('instansi_id', $instansi->id_instansi)
            ->orderBy('tingkat')->orderBy('nama_kelas')->get();

        $siswa = Siswa::where('instansi_id', $instansi->id_instansi)
            ->when($request->kelas_id, fn($q) =>
                $q->whereHas('registrasiAktif', fn($q) =>
                    $q->where('kelas_id', $request->kelas_id)
                )
            )
            ->with(['logPoin' => fn($q) => $q
                ->whereMonth('tanggal', $request->bulan)
                ->whereYear('tanggal', $request->tahun),
                'logPoin.masterPoin',
            ])
            ->orderBy('nama_siswa')
            ->get()
            ->map(function($s) {
                $s->jumlah_pelanggaran = $s->logPoin->count();
                $s->total_poin = $s->logPoin->sum(fn($l) => $l->masterPoin->jumlah_poin ?? 0);
                $s->status_poin = $s->total_poin >= 100 ? 'PERHATIAN'
                    : ($s->total_poin >= 50 ? 'WASPADA' : 'AMAN');
                return $s;
            });

        $bulanNama = \Carbon\Carbon::createFromDate($request->tahun, $request->bulan, 1)
            ->locale('id')->monthName;

        return view('admin.laporan.rekap-poin', compact('siswa', 'kelas', 'bulanNama', 'request'));
    }

    // ── Export Excel Poin ─────────────────────
    public function exportPoinExcel(Request $request)
    {
        $instansi = Auth::user()->getInstansi();
        $filename = 'rekap-poin-' . $request->bulan . '-' . $request->tahun . '.xlsx';

        return Excel::download(
            new PoinExport($instansi->id_instansi, $request->kelas_id, $request->bulan, $request->tahun),
            $filename
        );
    }

    // ── Export PDF Poin ───────────────────────
    public function exportPoinPdf(Request $request)
    {
        $instansi = Auth::user()->getInstansi();

        $kelas = $request->kelas_id
            ? Kelas::find($request->kelas_id)
            : null;

        $siswa = Siswa::where('instansi_id', $instansi->id_instansi)
            ->when($request->kelas_id, fn($q) =>
                $q->whereHas('registrasiAktif', fn($q) =>
                    $q->where('kelas_id', $request->kelas_id)
                )
            )
            ->with(['logPoin' => fn($q) => $q
                ->whereMonth('tanggal', $request->bulan)
                ->whereYear('tanggal', $request->tahun),
                'logPoin.masterPoin',
            ])
            ->orderBy('nama_siswa')
            ->get()
            ->map(function($s) {
                $s->jumlah_pelanggaran = $s->logPoin->count();
                $s->total_poin = $s->logPoin->sum(fn($l) => $l->masterPoin->jumlah_poin ?? 0);
                $s->status_poin = $s->total_poin >= 100 ? 'PERHATIAN'
                    : ($s->total_poin >= 50 ? 'WASPADA' : 'AMAN');
                return $s;
            });

        $bulanNama = \Carbon\Carbon::createFromDate($request->tahun, $request->bulan, 1)
            ->locale('id')->monthName;

        $pdf = Pdf::loadView('admin.laporan.pdf.rekap-poin', compact(
            'siswa', 'kelas', 'bulanNama', 'request', 'instansi'
        ))->setPaper('a4', 'portrait');

        return $pdf->download('rekap-poin-' . $request->bulan . '-' . $request->tahun . '.pdf');
    }
}