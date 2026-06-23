<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateExport;
use App\Models\Absensi;
use App\Models\ExportJob;
use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\TahunAjaran;
use App\Models\RegistrasiAkademik;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

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

        $kelas    = Kelas::with(['waliKelas'])->findOrFail($request->kelas_id);
        abort_if($kelas->instansi_id !== $instansi->id_instansi, 403);

        $mapel = MataPelajaran::where('instansi_id', $instansi->id_instansi)
            ->orderBy('nama_mapel')->get();

        $riwayat = Absensi::selectRaw('
                absensi.jadwal_id,
                absensi.tanggal,
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
            ->where('kurikulum_kelas.kelas_id', $request->kelas_id)
            ->when($request->mapel_id, fn($q) => $q->where('kurikulum_kelas.mapel_id', $request->mapel_id))
            ->whereMonth('absensi.tanggal', $request->bulan)
            ->whereYear('absensi.tanggal', $request->tahun)
            ->groupBy('absensi.jadwal_id', 'absensi.tanggal')
            ->orderBy('absensi.tanggal', 'desc')
            ->orderBy('absensi.jadwal_id')
            ->paginate(50)->withQueryString();

        $jadwalIds = $riwayat->pluck('jadwal_id')->unique();
        $jadwals = Jadwal::with(['kurikulum.kelas', 'kurikulum.mataPelajaran', 'kurikulum.guru'])
            ->whereIn('id_jadwal', $jadwalIds)
            ->get()
            ->keyBy('id_jadwal');

        $riwayat->getCollection()->transform(function ($item) use ($jadwals) {
            $j = $jadwals->get($item->jadwal_id);
            $item->kelas_nama  = $j?->kurikulum?->kelas?->nama_kelas ?? '-';
            $item->mapel_nama  = $j?->kurikulum?->mataPelajaran?->nama_mapel ?? '-';
            $item->jam         = $j ? (substr($j->jam_mulai, 0, 5) . ' - ' . substr($j->jam_selesai, 0, 5)) : '-';
            $item->guru_nama   = $j?->kurikulum?->guru?->nama_guru ?? '-';
            return $item;
        });

        $bulanNama = \Carbon\Carbon::createFromDate($request->tahun, $request->bulan, 1)
            ->locale('id')->monthName;

        return view('admin.laporan.rekap-absensi', compact(
            'riwayat', 'kelas', 'mapel', 'bulanNama', 'request'
        ));
    }

    public function detailAbsensi(Request $request)
    {
        $request->validate([
            'jadwal_id' => 'required|exists:jadwal,id_jadwal',
            'tanggal'   => 'required|date',
        ]);

        $instansi = Auth::user()->getInstansi();

        $jadwal = Jadwal::with(['kurikulum.kelas', 'kurikulum.mataPelajaran', 'kurikulum.guru'])
            ->findOrFail($request->jadwal_id);

        $kelas = $jadwal->kurikulum->kelas;
        abort_if(!$kelas || $kelas->instansi_id !== $instansi->id_instansi, 403);

        $absensi = Absensi::with('registrasi.siswa')
            ->where('jadwal_id', $request->jadwal_id)
            ->whereDate('tanggal', $request->tanggal)
            ->orderBy('status')
            ->get();

        return view('admin.laporan.rekap-absensi-detail', compact('jadwal', 'absensi', 'request'));
    }

    // ── Export via Queue ──────────────────────
    public function exportAbsensiExcel(Request $request)
    {
        $request->validate([
            'kelas_id' => 'required|exists:kelas,id_kelas',
            'bulan'    => 'required|integer|min:1|max:12',
            'tahun'    => 'required|integer|min:2020',
        ]);

        $instansi = Auth::user()->getInstansi();
        $kelas = Kelas::findOrFail($request->kelas_id);
        abort_if($kelas->instansi_id !== $instansi->id_instansi, 403);

        $exportJob = ExportJob::create([
            'user_id' => Auth::id(),
            'type'    => 'absensi-excel',
            'source'  => 'admin',
            'filters' => $request->only(['kelas_id', 'bulan', 'tahun', 'mapel_id']),
            'status'  => 'pending',
        ]);

        GenerateExport::dispatch($exportJob);

        return redirect()->route('admin.laporan.index')
            ->with('info', 'Export Excel Absensi sedang diproses. Silakan cek tab "Export Saya" beberapa saat lagi.');
    }

    public function exportAbsensiPdf(Request $request)
    {
        $request->validate([
            'kelas_id' => 'required|exists:kelas,id_kelas',
            'bulan'    => 'required|integer|min:1|max:12',
            'tahun'    => 'required|integer|min:2020',
        ]);

        $instansi = Auth::user()->getInstansi();
        $kelas = Kelas::findOrFail($request->kelas_id);
        abort_if($kelas->instansi_id !== $instansi->id_instansi, 403);

        $exportJob = ExportJob::create([
            'user_id' => Auth::id(),
            'type'    => 'absensi-pdf',
            'source'  => 'admin',
            'filters' => $request->only(['kelas_id', 'bulan', 'tahun']),
            'status'  => 'pending',
        ]);

        GenerateExport::dispatch($exportJob);

        return redirect()->route('admin.laporan.index')
            ->with('info', 'Export PDF Absensi sedang diproses. Silakan cek tab "Export Saya" beberapa saat lagi.');
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
            ->whereNull('status')
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

    // ── Export via Queue ──────────────────────
    public function exportPoinExcel(Request $request)
    {
        $request->validate([
            'bulan' => 'required|integer|min:1|max:12',
            'tahun' => 'required|integer|min:2020',
        ]);

        $exportJob = ExportJob::create([
            'user_id' => Auth::id(),
            'type'    => 'poin-excel',
            'source'  => 'admin',
            'filters' => $request->only(['kelas_id', 'bulan', 'tahun']),
            'status'  => 'pending',
        ]);

        GenerateExport::dispatch($exportJob);

        return redirect()->route('admin.laporan.index')
            ->with('info', 'Export Excel Poin sedang diproses. Silakan cek tab "Export Saya" beberapa saat lagi.');
    }

    public function exportPoinPdf(Request $request)
    {
        $request->validate([
            'bulan' => 'required|integer|min:1|max:12',
            'tahun' => 'required|integer|min:2020',
        ]);

        $exportJob = ExportJob::create([
            'user_id' => Auth::id(),
            'type'    => 'poin-pdf',
            'source'  => 'admin',
            'filters' => $request->only(['kelas_id', 'bulan', 'tahun']),
            'status'  => 'pending',
        ]);

        GenerateExport::dispatch($exportJob);

        return redirect()->route('admin.laporan.index')
            ->with('info', 'Export PDF Poin sedang diproses. Silakan cek tab "Export Saya" beberapa saat lagi.');
    }

    // ── Daftar Export Saya ────────────────────
    public function exports(Request $request)
    {
        $isAdmin = $request->routeIs('admin.*');

        if ($request->ajax()) {
            $exports = ExportJob::where('user_id', Auth::id())
                ->where('source', 'admin')
                ->latest()
                ->select('export_jobs.*');

            return DataTables::of($exports)
                ->addIndexColumn()
                ->addColumn('type_label', fn($row) => $row->type_label)
                ->addColumn('status_badge', function ($row) {
                    return match ($row->status) {
                        'completed' => '<span class="px-2 py-1 text-xs font-medium text-green-700 bg-green-100 rounded-full">Selesai</span>',
                        'processing' => '<span class="px-2 py-1 text-xs font-medium text-blue-700 bg-blue-100 rounded-full">Memproses</span>',
                        'failed' => '<span class="px-2 py-1 text-xs font-medium text-red-700 bg-red-100 rounded-full" title="' . e($row->error_message) . '">Gagal</span>',
                        default => '<span class="px-2 py-1 text-xs font-medium text-gray-700 bg-gray-100 rounded-full">Menunggu</span>',
                    };
                })
                ->addColumn('aksi', function ($row) use ($isAdmin) {
                    $downloadRoute = $isAdmin ? 'admin.laporan.export-download' : 'guru.export-download';
                    $destroyRoute  = $isAdmin ? 'admin.laporan.export-destroy' : 'guru.export-destroy';
                    $btn = '';
                    if ($row->status === 'completed') {
                        $btn .= '<a href="' . route($downloadRoute, $row) . '" class="text-green-600 hover:text-green-800 mr-3" title="Download">
                            <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                        </a>';
                    } elseif ($row->status === 'failed') {
                        $btn .= '<span class="text-red-500 cursor-help mr-3" title="' . e($row->error_message) . '">Gagal</span>';
                    } else {
                        $btn .= '<span class="text-gray-400 mr-3">Proses...</span>';
                    }
                    $btn .= '<form method="POST" action="' . route($destroyRoute, $row) . '" class="inline" onsubmit="return confirm(\'Hapus export ini?\')">
                        <input type="hidden" name="_token" value="' . csrf_token() . '">
                        <input type="hidden" name="_method" value="DELETE">
                        <button type="submit" class="text-red-600 hover:text-red-800" title="Hapus">
                            <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </form>';
                    return $btn;
                })
                ->editColumn('created_at', fn($row) => $row->created_at->format('d M Y H:i'))
                ->rawColumns(['status_badge', 'aksi'])
                ->make(true);
        }

        return view('admin.laporan.exports', compact('isAdmin'));
    }

    // ── Download Export ───────────────────────
    public function downloadExport(ExportJob $exportJob)
    {
        abort_if($exportJob->user_id !== Auth::id(), 403);
        abort_if($exportJob->status !== 'completed', 404);

        return Storage::disk('local')->download($exportJob->filepath, $exportJob->filename);
    }

    // ── Hapus Export ──────────────────────────
    public function destroyExport(ExportJob $exportJob)
    {
        abort_if($exportJob->user_id !== Auth::id(), 403);

        if ($exportJob->filepath && Storage::disk('local')->exists($exportJob->filepath)) {
            Storage::disk('local')->delete($exportJob->filepath);
        }

        $exportJob->delete();

        if (request()->ajax()) {
            return response()->json(['message' => 'Export berhasil dihapus.']);
        }

        return redirect()->route('admin.laporan.exports')
            ->with('success', 'Export berhasil dihapus.');
    }
}