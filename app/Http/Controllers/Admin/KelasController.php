<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\RegistrasiAkademik;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class KelasController extends Controller
{
    public function index(Request $request)
    {
        $instansi = Auth::user()->getInstansi();

        if ($request->ajax()) {
            $kelas = Kelas::with(['waliKelas'])
                ->where('instansi_id', $instansi->id_instansi)
                ->select('kelas.*');

            return DataTables::of($kelas)
                ->addIndexColumn()
                ->addColumn('wali_kelas', fn ($row) => $row->waliKelas?->nama_guru ?? '<span class="text-gray-400">Belum ditentukan</span>')
                // ->addColumn('jumlah_siswa', function ($row) {
                //     // Hitung siswa di tahun ajaran aktif
                //     $tahunAktif = TahunAjaran::where('is_aktif', true)->first();

                //     return $tahunAktif
                //         ? $row->registrasiAkademik()->where('tahun_id', $tahunAktif->id_tahun)->count()
                //         : 0;
                // })
                ->addColumn('jumlah_siswa', function ($row) {
                    $instansi = Auth::user()->getInstansi();
                    $tahunAktif = TahunAjaran::where('instansi_id', $instansi->id_instansi)
                        ->where('is_aktif', true)
                        ->first();

                    return $tahunAktif
                        ? $row->registrasiAkademik()->where('tahun_id', $tahunAktif->id_tahun)->count()
                        : 0;
                })
                ->addColumn('tahun_ajaran', function ($row) {
                    // Ambil tahun ajaran dari registrasi terbaru di kelas ini
                    $reg = \App\Models\RegistrasiAkademik::where('kelas_id', $row->id_kelas)
                        ->with('tahunAjaran')
                        ->latest('id_registrasi')
                        ->first();

                    return $reg?->tahunAjaran
                        ? $reg->tahunAjaran->nama_tahun.' - '.$reg->tahunAjaran->semester
                        : '<span class="text-gray-400 text-xs">Belum ada siswa</span>';
                })
                // ->addColumn('jumlah_siswa', fn ($row) => $row->registrasiAkademik()->count())
                ->addColumn('aksi', function ($row) {
                    $detail = '<a href="'.route('admin.kelas.detail', $row->id_kelas).'"
                        class="px-3 py-1 text-xs font-medium text-white bg-purple-600 rounded hover:bg-purple-700 mr-1">
                        Detail</a>';
                    $edit = '<a href="'.route('admin.kelas.edit', $row->id_kelas).'"
                        class="px-3 py-1 text-xs font-medium text-white bg-blue-600 rounded hover:bg-blue-700">
                        Edit</a>';
                    $delete = '<form method="POST" action="'.route('admin.kelas.destroy', $row->id_kelas).'" class="inline">
                        <input type="hidden" name="_token" value="'.csrf_token().'">
                        <input type="hidden" name="_method" value="DELETE">
                        <button type="submit"
                            class="px-3 py-1 text-xs font-medium text-white bg-red-600 rounded hover:bg-red-700"
                            onclick="return confirm(\'Yakin hapus kelas ini?\')">
                            Hapus</button>
                        </form>';

                    return $detail.$edit.' '.$delete;
                })
                ->rawColumns(['wali_kelas', 'tahun_ajaran', 'aksi'])
                ->make(true);
        }

        return view('admin.kelas.index');
    }

    public function create()
    {
        $instansi = Auth::user()->getInstansi();

        $guru = Guru::where('instansi_id', $instansi->id_instansi)
            ->orderBy('nama_guru')
            ->get();

        return view('admin.kelas.create', compact('guru', 'instansi'));
    }

    public function store(Request $request)
    {
        $instansi = Auth::user()->getInstansi();

        $validated = $request->validate([
            'nama_kelas' => 'required|string|max:255',
            'tingkat' => 'required|integer|min:1|max:12',
            'guru_wali_id' => 'nullable|exists:guru,id_guru',
        ]);

        Kelas::create([
            ...$validated,
            'instansi_id' => $instansi->id_instansi,
        ]);

        return redirect()->route('admin.kelas.index')
            ->with('success', 'Kelas berhasil ditambahkan!');
    }

    public function edit(Kelas $kelas)
    {
        $this->authorizeInstansi($kelas);

        $instansi = Auth::user()->getInstansi();

        abort_if($kelas->instansi_id !== $instansi->id_instansi, 403);

        $guru = Guru::where('instansi_id', $instansi->id_instansi)
            ->orderBy('nama_guru')
            ->get();

        return view('admin.kelas.edit', compact('kelas', 'guru', 'instansi'));
    }

    public function update(Request $request, Kelas $kelas)
    {
        $this->authorizeInstansi($kelas);

        $validated = $request->validate([
            'nama_kelas' => 'required|string|max:255',
            'tingkat' => 'required|integer|min:1|max:12',
            'guru_wali_id' => 'nullable|exists:guru,id_guru',
        ]);

        $kelas->update($validated);

        return redirect()->route('admin.kelas.index')
            ->with('success', 'Kelas berhasil diupdate!');
    }

    public function destroy(Kelas $kelas)
    {
        $this->authorizeInstansi($kelas);

        if ($kelas->registrasiAkademik()->exists()) {
            return back()->with('error', 'Kelas tidak bisa dihapus karena masih ada siswa terdaftar!');
        }

        $kelas->delete();

        return redirect()->route('admin.kelas.index')
            ->with('success', 'Kelas berhasil dihapus!');
    }

    private function authorizeInstansi(Kelas $kelas): void
    {
        $instansi = Auth::user()->getInstansi();
        abort_if($kelas->instansi_id !== $instansi->id_instansi, 403);
    }

    public function detail(Kelas $kelas)
    {
        $this->authorizeInstansi($kelas);

        $instansi = Auth::user()->getInstansi();
        $tahunAktif = TahunAjaran::where('instansi_id', $instansi->id_instansi)
            ->where('is_aktif', true)
            ->first();

        $kelas->load([
            'waliKelas',
            'kurikulum.mataPelajaran',
            'kurikulum.guru',
            'kurikulum.jadwal',
        ]);

        // Siswa hanya dari tahun ajaran aktif
        $registrasi = RegistrasiAkademik::where('kelas_id', $kelas->id_kelas)
            ->when($tahunAktif, fn ($q) => $q->where('tahun_id', $tahunAktif->id_tahun))
            ->with('siswa')
            ->get();

        return view('admin.kelas.detail', compact('kelas', 'registrasi', 'tahunAktif'));
    }
}
