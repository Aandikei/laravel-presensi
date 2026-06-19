<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RegistrasiAkademik;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class RegistrasiAkademikController extends Controller
{
    public function index(Request $request)
    {
        $instansi = Auth::user()->getInstansi();

        if ($request->ajax()) {
            $registrasi = RegistrasiAkademik::with(['siswa', 'kelas', 'tahunAjaran'])
                ->whereHas('kelas', fn($q) => $q->where('instansi_id', $instansi->id_instansi));

            if ($request->tahun_id) {
                $registrasi->where('tahun_id', $request->tahun_id);
            }

            if ($request->kelas_id) {
                $registrasi->where('kelas_id', $request->kelas_id);
            }

            return DataTables::of($registrasi)
                ->addIndexColumn()
                ->addColumn('nama_siswa', fn($row) => $row->siswa->nama_siswa)
                ->addColumn('nisn', fn($row) => $row->siswa->nisn)
                ->addColumn('kelas', fn($row) => $row->kelas->nama_kelas)
                ->addColumn('status', fn($row) => $row->status)
                ->addColumn('tahun_ajaran', fn($row) => $row->tahunAjaran->nama_tahun . ' - ' . $row->tahunAjaran->semester)
                ->addColumn('aksi', function ($row) {
                    if (!Auth::user()->can('manage-siswa')) {
                        return '';
                    }
                    $delete = '<form method="POST" action="' . route('admin.registrasi.destroy', $row->id_registrasi) . '" class="inline">
                        <input type="hidden" name="_token" value="' . csrf_token() . '">
                        <input type="hidden" name="_method" value="DELETE">
                        <button type="submit" title="Hapus" class="text-red-600 hover:text-red-800" onclick="return confirm(\'Yakin hapus registrasi ini?\')">
                            <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                        </form>';
                    return $delete;
                })
                ->rawColumns(['aksi'])
                ->make(true);
        }

        $tahunAjaran = TahunAjaran::where('instansi_id', $instansi->id_instansi)
            ->orderByDesc('is_aktif')->get();

        $kelas = Kelas::where('instansi_id', $instansi->id_instansi)
            ->orderBy('tingkat')
            ->orderBy('nama_kelas')
            ->get();

        return view('admin.registrasi.index', compact('tahunAjaran', 'kelas'));
    }

    public function create()
    {
        $instansi = Auth::user()->getInstansi();

        $tahunAjaran = TahunAjaran::where('instansi_id', $instansi->id_instansi)
            ->orderByDesc('is_aktif')->get();

        $kelas = Kelas::where('instansi_id', $instansi->id_instansi)
            ->orderBy('tingkat')
            ->orderBy('nama_kelas')
            ->get();

        // Siswa yang belum terdaftar di tahun ajaran aktif
        $tahunAktif = TahunAjaran::where('instansi_id', $instansi->id_instansi)
            ->where('is_aktif', true)->first();

        $siswaTeregistrasi = $tahunAktif
            ? RegistrasiAkademik::where('tahun_id', $tahunAktif->id_tahun)
                ->aktif()
                ->pluck('siswa_id')->toArray()
            : [];

        $siswaAlumni = RegistrasiAkademik::alumni()
            ->whereHas('kelas', fn($q) => $q->where('instansi_id', $instansi->id_instansi))
            ->pluck('siswa_id')->toArray();

        $siswa = Siswa::where('instansi_id', $instansi->id_instansi)
            ->whereNotIn('id_siswa', array_merge($siswaTeregistrasi, $siswaAlumni))
            ->orderBy('nama_siswa')
            ->get();

        return view('admin.registrasi.create', compact('tahunAjaran', 'kelas', 'siswa', 'tahunAktif'));
    }

    public function store(Request $request)
    {
        $instansi = Auth::user()->getInstansi();

        $validated = $request->validate([
            'siswa_id'  => 'required|array|min:1',
            'siswa_id.*'=> 'exists:siswa,id_siswa',
            'kelas_id'  => 'required|exists:kelas,id_kelas',
            'tahun_id'  => 'required|exists:tahun_ajaran,id_tahun',
        ]);

        // Pastiin kelas milik instansi ini
        $kelas = Kelas::findOrFail($validated['kelas_id']);
        abort_if($kelas->instansi_id !== $instansi->id_instansi, 403);

        // Ambil data terkait sekali aja biar gak N+1
        $existingIds = RegistrasiAkademik::where('tahun_id', $validated['tahun_id'])
            ->whereIn('siswa_id', $validated['siswa_id'])
            ->pluck('siswa_id')->toArray();

        $alumniIds = RegistrasiAkademik::alumni()
            ->whereIn('siswa_id', $validated['siswa_id'])
            ->whereHas('kelas', fn($q) => $q->where('instansi_id', $instansi->id_instansi))
            ->pluck('siswa_id')->toArray();

        $berhasil = 0;
        $gagal    = 0;

        foreach ($validated['siswa_id'] as $siswaId) {
            if (in_array($siswaId, $existingIds) || in_array($siswaId, $alumniIds)) {
                $gagal++;
                continue;
            }

            RegistrasiAkademik::create([
                'siswa_id' => $siswaId,
                'kelas_id' => $validated['kelas_id'],
                'tahun_id' => $validated['tahun_id'],
                'status'   => 'Aktif',
            ]);
            $berhasil++;
        }

        $message = "Berhasil mendaftarkan {$berhasil} siswa.";
        if ($gagal > 0) {
            $message .= " {$gagal} siswa dilewati (sudah terdaftar atau alumni).";
        }

        return redirect()->route('admin.registrasi.index')->with('success', $message);
    }

    public function destroy(RegistrasiAkademik $registrasi)
    {
        $instansi = Auth::user()->getInstansi();
        abort_if($registrasi->kelas->instansi_id !== $instansi->id_instansi, 403);

        if ($registrasi->absensi()->exists()) {
            return back()->with('error', 'Registrasi tidak bisa dihapus karena sudah ada data absensi!');
        }

        $registrasi->delete();

        return redirect()->route('admin.registrasi.index')
            ->with('success', 'Registrasi berhasil dihapus!');
    }
}