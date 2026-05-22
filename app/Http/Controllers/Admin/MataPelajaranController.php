<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MataPelajaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class MataPelajaranController extends Controller
{
    public function index(Request $request)
    {
        $instansi = Auth::user()->getInstansi();

        if ($request->ajax()) {
            $mapel = MataPelajaran::where('instansi_id', $instansi->id_instansi)
                ->select('mata_pelajaran.*');

            return DataTables::of($mapel)
                ->addIndexColumn()
                ->addColumn('kelompok_badge', function ($row) {
                    $color = match($row->kelompok) {
                        'Umum'         => 'blue',
                        'Jurusan'      => 'purple',
                        'Muatan Lokal' => 'green',
                        default        => 'gray',
                    };
                    return '<span class="px-2 py-1 text-xs font-medium text-' . $color . '-700 bg-' . $color . '-100 rounded-full dark:bg-' . $color . '-800 dark:text-' . $color . '-200">'
                        . $row->kelompok . '</span>';
                })
                ->addColumn('jumlah_kelas', function ($row) {
                    return $row->kurikulum()->count();
                })
                ->addColumn('aksi', function ($row) {
                    $edit = '<a href="' . route('admin.mata-pelajaran.edit', $row->id_mapel) . '"
                        class="px-3 py-1 text-xs font-medium text-white bg-blue-600 rounded hover:bg-blue-700">
                        Edit</a>';
                    $delete = '<form method="POST" action="' . route('admin.mata-pelajaran.destroy', $row->id_mapel) . '" class="inline">
                        <input type="hidden" name="_token" value="' . csrf_token() . '">
                        <input type="hidden" name="_method" value="DELETE">
                        <button type="submit"
                            class="px-3 py-1 text-xs font-medium text-white bg-red-600 rounded hover:bg-red-700"
                            onclick="return confirm(\'Yakin hapus mata pelajaran ini?\')">
                            Hapus</button>
                        </form>';
                    return $edit . ' ' . $delete;
                })
                ->rawColumns(['kelompok_badge', 'aksi'])
                ->make(true);
        }

        return view('admin.mata-pelajaran.index');
    }

    public function create()
    {
        return view('admin.mata-pelajaran.create');
    }

    public function store(Request $request)
    {
        $instansi = Auth::user()->getInstansi();

        $validated = $request->validate([
            'nama_mapel'  => 'required|string|max:255',
            'kode_mapel'  => 'nullable|string|max:20',
            'kelompok'    => 'required|in:Umum,Jurusan,Muatan Lokal',
        ]);

        MataPelajaran::create([
            ...$validated,
            'instansi_id' => $instansi->id_instansi,
        ]);

        return redirect()->route('admin.mata-pelajaran.index')
            ->with('success', 'Mata pelajaran berhasil ditambahkan!');
    }

    public function edit(MataPelajaran $mataPelajaran)
    {
        $this->authorizeInstansi($mataPelajaran);
        return view('admin.mata-pelajaran.edit', compact('mataPelajaran'));
    }

    public function update(Request $request, MataPelajaran $mataPelajaran)
    {
        $this->authorizeInstansi($mataPelajaran);

        $validated = $request->validate([
            'nama_mapel' => 'required|string|max:255',
            'kode_mapel' => 'nullable|string|max:20',
            'kelompok'   => 'required|in:Umum,Jurusan,Muatan Lokal',
        ]);

        $mataPelajaran->update($validated);

        return redirect()->route('admin.mata-pelajaran.index')
            ->with('success', 'Mata pelajaran berhasil diupdate!');
    }

    public function destroy(MataPelajaran $mataPelajaran)
    {
        $this->authorizeInstansi($mataPelajaran);

        if ($mataPelajaran->kurikulum()->exists()) {
            return back()->with('error', 'Mata pelajaran tidak bisa dihapus karena sudah dipakai di kurikulum kelas!');
        }

        $mataPelajaran->delete();

        return redirect()->route('admin.mata-pelajaran.index')
            ->with('success', 'Mata pelajaran berhasil dihapus!');
    }

    private function authorizeInstansi(MataPelajaran $mataPelajaran): void
    {
        $instansi = Auth::user()->getInstansi();
        abort_if($mataPelajaran->instansi_id !== $instansi->id_instansi, 403);
    }
}