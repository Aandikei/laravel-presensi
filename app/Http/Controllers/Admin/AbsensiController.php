<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Jadwal;
use App\Models\Kelas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class AbsensiController extends Controller
{
    public function index(Request $request)
    {
        $instansi = Auth::user()->getInstansi();

        if ($request->ajax()) {
            $tanggal = $request->tanggal ?? now()->toDateString();

            $jadwalAbsensi = Jadwal::with([
                'kurikulum.kelas',
                'kurikulum.mataPelajaran',
                'kurikulum.guru',
            ])
                ->withCount([
                    'absensi as absensi_count' => fn ($q) => $q->where('tanggal', $tanggal),
                    'absensi as locked_count' => fn ($q) => $q->where('tanggal', $tanggal)->where('is_locked', true),
                ])
                ->whereHas('kurikulum.kelas', fn ($q) => $q->where('instansi_id', $instansi->id_instansi))
                ->whereHas('absensi', fn ($q) => $q->where('tanggal', $tanggal))
                ->when($request->kelas_id, fn ($q) => $q->whereHas('kurikulum', fn ($q) => $q->where('kelas_id', $request->kelas_id)))
                ->select('jadwal.*');

            return DataTables::of($jadwalAbsensi)
                ->addIndexColumn()
                ->addColumn('kelas', fn ($row) => $row->kurikulum->kelas->nama_kelas)
                ->addColumn('mata_pelajaran', fn ($row) => $row->kurikulum->mataPelajaran->nama_mapel)
                ->addColumn('guru', function ($row) use ($instansi) {
                    $guru = $row->kurikulum?->guru;
                    if (!$guru) return '-';
                    $name = $guru->nama_guru;
                    if ($guru->transfer_token && !$guru->isTransferTokenExpired()) {
                        return $name . ' <span class="px-2 py-1 text-xs font-medium text-orange-700 bg-orange-100 rounded-full">Mutasi</span>';
                    }
                    if ($guru->instansi_id !== $instansi->id_instansi) {
                        return $name . ' <span class="px-2 py-1 text-xs font-medium text-blue-700 bg-blue-100 rounded-full">Pindah</span>';
                    }
                    if ($guru->status === 'Keluar') {
                        return $name . ' <span class="px-2 py-1 text-xs font-medium text-red-700 bg-red-100 rounded-full">Keluar</span>';
                    }
                    if ($guru->status === 'Pensiun') {
                        return $name . ' <span class="px-2 py-1 text-xs font-medium text-gray-700 bg-gray-200 rounded-full">Pensiun</span>';
                    }
                    return $name;
                })
                ->addColumn('jam', fn ($row) => substr($row->jam_mulai, 0, 5) . ' - ' . substr($row->jam_selesai, 0, 5))
                ->addColumn('jumlah_siswa', fn ($row) => $row->absensi_count)
                ->addColumn('status_kunci', function ($row) {
                    return $row->locked_count > 0
                        ? '<span class="px-2 py-1 text-xs font-medium text-red-700 bg-red-100 rounded-full">🔒 Terkunci</span>'
                        : '<span class="px-2 py-1 text-xs font-medium text-green-700 bg-green-100 rounded-full">🔓 Terbuka</span>';
                })
                ->addColumn('aksi', function ($row) use ($tanggal) {
                    $terkunci = $row->locked_count > 0;

                    $detail = '<a href="' . route('admin.absensi.detail', [
                        'jadwal' => $row->id_jadwal,
                        'tanggal' => $tanggal,
                    ]) . '" title="Detail" class="text-purple-600 hover:text-purple-800">
            <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
            </svg>
        </a>';

                    if ($terkunci) {
                        $toggleBtn = '<form method="POST" action="' . route('admin.absensi.unlock', [
                            'jadwal' => $row->id_jadwal,
                            'tanggal' => $tanggal,
                        ]) . '" class="inline">
                <input type="hidden" name="_token" value="' . csrf_token() . '">
                <input type="hidden" name="_method" value="PATCH">
                <button type="button" title="Buka Kunci" class="text-green-600 hover:text-green-800" onclick="confirmAction(this.closest(\'form\'), \'Buka kunci?\', \'Ya, Buka\')">
                    <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"></path>
                    </svg>
                </button>
                </form>';
                    } else {
                        $toggleBtn = '<form method="POST" action="' . route('admin.absensi.lock', [
                            'jadwal' => $row->id_jadwal,
                            'tanggal' => $tanggal,
                        ]) . '" class="inline">
                <input type="hidden" name="_token" value="' . csrf_token() . '">
                <input type="hidden" name="_method" value="PATCH">
                <button type="button" title="Kunci" class="text-red-600 hover:text-red-800" onclick="confirmAction(this.closest(\'form\'), \'Kunci absensi?\')">
                    <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                </button>
                </form>';
                    }

                    return $detail . $toggleBtn;
                })
                ->filterColumn('kelas', function ($query, $keyword) {
                    $query->whereHas('kurikulum.kelas', fn ($q) => $q->where('nama_kelas', 'like', "%{$keyword}%"));
                })
                ->filterColumn('mata_pelajaran', function ($query, $keyword) {
                    $query->whereHas('kurikulum.mataPelajaran', fn ($q) => $q->where('nama_mapel', 'like', "%{$keyword}%"));
                })
                ->filterColumn('guru', function ($query, $keyword) {
                    $query->whereHas('kurikulum.guru', fn ($q) => $q->where('nama_guru', 'like', "%{$keyword}%"));
                })
                ->rawColumns(['guru', 'status_kunci', 'aksi'])
                ->make(true);
        }

        $kelas = Kelas::where('instansi_id', $instansi->id_instansi)
            ->orderBy('tingkat')
            ->orderBy('nama_kelas')
            ->get();

        return view('admin.absensi.index', compact('kelas'));
    }

    public function detail(Request $request, Jadwal $jadwal)
    {
        $instansi = Auth::user()->getInstansi();
        abort_if($jadwal->kurikulum->kelas->instansi_id !== $instansi->id_instansi, 403);

        $tanggal = $request->tanggal ?? now()->toDateString();

        $absensi = Absensi::with(['registrasi.siswa'])
            ->where('jadwal_id', $jadwal->id_jadwal)
            ->where('tanggal', $tanggal)
            ->get();

        $terkunci = $absensi->where('is_locked', true)->count() > 0;

        return view('admin.absensi.detail', compact('jadwal', 'absensi', 'tanggal', 'terkunci'));
    }

    public function lock(Request $request, Jadwal $jadwal)
    {
        $instansi = Auth::user()->getInstansi();
        abort_if($jadwal->kurikulum->kelas->instansi_id !== $instansi->id_instansi, 403);

        $tanggal = $request->tanggal ?? now()->toDateString();

        // Kunci semua absensi di jadwal ini pada tanggal ini
        Absensi::where('jadwal_id', $jadwal->id_jadwal)
            ->where('tanggal', $tanggal)
            ->update(['is_locked' => true]);

        return back()->with('success', 'Absensi berhasil dikunci!');
    }

    public function unlock(Request $request, Jadwal $jadwal)
    {
        $instansi = Auth::user()->getInstansi();
        abort_if($jadwal->kurikulum->kelas->instansi_id !== $instansi->id_instansi, 403);

        $tanggal = $request->tanggal ?? now()->toDateString();

        // Buka kunci semua absensi di jadwal ini pada tanggal ini
        Absensi::where('jadwal_id', $jadwal->id_jadwal)
            ->where('tanggal', $tanggal)
            ->update(['is_locked' => false]);

        return back()->with('success', 'Absensi berhasil dibuka!');
    }
}
