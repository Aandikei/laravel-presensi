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
            // Group by jadwal + tanggal, bukan per siswa
            $jadwalAbsensi = Jadwal::with([
                'kurikulum.kelas',
                'kurikulum.mataPelajaran',
                'kurikulum.guru',
            ])
                ->whereHas('kurikulum.kelas', fn ($q) => $q->where('instansi_id', $instansi->id_instansi)
                )
                ->whereHas('absensi', function ($q) use ($request) {
                    if ($request->tanggal) {
                        $q->where('tanggal', $request->tanggal);
                    }
                })
                ->when($request->kelas_id, fn ($q) => $q->whereHas('kurikulum', fn ($q) => $q->where('kelas_id', $request->kelas_id)
                )
                )
                ->select('jadwal.*');

            return DataTables::of($jadwalAbsensi)
                ->addIndexColumn()
                ->addColumn('kelas', fn ($row) => $row->kurikulum->kelas->nama_kelas)
                ->addColumn('mata_pelajaran', fn ($row) => $row->kurikulum->mataPelajaran->nama_mapel)
                ->addColumn('guru', fn ($row) => $row->kurikulum->guru->nama_guru)
                ->addColumn('jam', fn ($row) => substr($row->jam_mulai, 0, 5).' - '.substr($row->jam_selesai, 0, 5)
                )
                ->addColumn('jumlah_siswa', function ($row) use ($request) {
                    $tanggal = $request->tanggal ?? now()->toDateString();

                    return $row->absensi()->where('tanggal', $tanggal)->count();
                })
                ->addColumn('status_kunci', function ($row) use ($request) {
                    $tanggal = $request->tanggal ?? now()->toDateString();
                    $terkunci = $row->absensi()
                        ->where('tanggal', $tanggal)
                        ->where('is_locked', true)
                        ->exists();

                    return $terkunci
                        ? '<span class="px-2 py-1 text-xs font-medium text-red-700 bg-red-100 rounded-full">🔒 Terkunci</span>'
                        : '<span class="px-2 py-1 text-xs font-medium text-green-700 bg-green-100 rounded-full">🔓 Terbuka</span>';
                })
                ->addColumn('aksi', function ($row) use ($request) {
                    $tanggal = $request->tanggal ?? now()->toDateString();
                    $terkunci = $row->absensi()
                        ->where('tanggal', $tanggal)
                        ->where('is_locked', true)
                        ->exists();

                    $detail = '<a href="'.route('admin.absensi.detail', [
                        'jadwal' => $row->id_jadwal,
                        'tanggal' => $tanggal,
                    ]).'"
            class="px-3 py-1 text-xs font-medium text-white bg-blue-600 rounded hover:bg-blue-700 mr-1">
            Detail</a>';

                    if ($terkunci) {
                        $toggleBtn = '<form method="POST" action="'.route('admin.absensi.unlock', [
                            'jadwal' => $row->id_jadwal,
                            'tanggal' => $tanggal,
                        ]).'" class="inline">
                <input type="hidden" name="_token" value="'.csrf_token().'">
                <input type="hidden" name="_method" value="PATCH">
                <button type="submit" class="px-3 py-1 text-xs font-medium  text-green-700 bg-green-100 rounded hover:bg-green-700"
                    onclick="return confirm(\'Buka kunci?\')">Buka Kunci</button>
                </form>';
                    } else {
                        $toggleBtn = '<form method="POST" action="'.route('admin.absensi.lock', [
                            'jadwal' => $row->id_jadwal,
                            'tanggal' => $tanggal,
                        ]).'" class="inline">
                <input type="hidden" name="_token" value="'.csrf_token().'">
                <input type="hidden" name="_method" value="PATCH">
                <button type="submit" class="px-3 py-1 text-xs font-medium text-white bg-red-600 rounded hover:bg-red-700"
                    onclick="return confirm(\'Kunci absensi?\')">Kunci</button>
                </form>';
                    }

                    return $detail.$toggleBtn;
                })
    // ← Tambah filterColumn di sini
                ->filterColumn('kelas', function ($query, $keyword) {
                    $query->whereHas('kurikulum.kelas', fn ($q) => $q->where('nama_kelas', 'like', "%{$keyword}%")
                    );
                })
                ->filterColumn('mata_pelajaran', function ($query, $keyword) {
                    $query->whereHas('kurikulum.mataPelajaran', fn ($q) => $q->where('nama_mapel', 'like', "%{$keyword}%")
                    );
                })
                ->filterColumn('guru', function ($query, $keyword) {
                    $query->whereHas('kurikulum.guru', fn ($q) => $q->where('nama_guru', 'like', "%{$keyword}%")
                    );
                })
                ->rawColumns(['status_kunci', 'aksi'])
                ->make(true);
        }

        $kelas = Kelas::where('instansi_id', $instansi->id_instansi)
            ->with('tahunAjaran')
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
