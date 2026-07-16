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

        $kelompokList = $instansi->jenjang === 'SMA'
            ? ['Umum', 'Jurusan', 'Muatan Lokal']
            : ['Umum', 'Muatan Lokal'];

        if ($request->ajax()) {
            $mapel = MataPelajaran::where('instansi_id', $instansi->id_instansi)
                ->select('mata_pelajaran.*')
                ->withCount('kurikulum as jumlah_kelas');

            if ($request->kelompok) {
                $mapel->where('kelompok', $request->kelompok);
            }

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
                ->addColumn('jumlah_kelas', fn ($row) => $row->jumlah_kelas)
                ->addColumn('aksi', function ($row) {
                    if (!Auth::user()->can('manage-settings')) {
                        return '';
                    }
                    $edit = '<a href="' . route('admin.mata-pelajaran.edit', $row->id_mapel) . '" title="Edit" class="text-blue-600 hover:text-blue-800">
                        <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                    </a>';
                    $delete = '<form method="POST" action="' . route('admin.mata-pelajaran.destroy', $row->id_mapel) . '" class="inline">
                        <input type="hidden" name="_token" value="' . csrf_token() . '">
                        <input type="hidden" name="_method" value="DELETE">
                        <button type="button" title="Hapus" class="text-red-600 hover:text-red-800" onclick="confirmAction(this.closest(\'form\'), \'Yakin hapus mata pelajaran ini?\')">
                            <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                        </form>';
                    return $edit . ' ' . $delete;
                })
                ->rawColumns(['kelompok_badge', 'aksi'])
                ->make(true);
        }

        return view('admin.mata-pelajaran.index', compact('kelompokList'));
    }

    public function create()
    {
        return view('admin.mata-pelajaran.create');
    }

    public function store(Request $request)
    {
        $instansi = Auth::user()->getInstansi();

        $kelompokRule = $instansi->jenjang === 'SMA'
            ? 'required|in:Umum,Jurusan,Muatan Lokal'
            : 'required|in:Umum,Muatan Lokal';

        $validated = $request->validate([
            'nama_mapel'  => 'required|string|max:255',
            'kode_mapel'  => 'required|string|max:20|unique:mata_pelajaran,kode_mapel,NULL,id_mapel,instansi_id,'.$instansi->id_instansi,
            'kelompok'    => $kelompokRule,
        ]);

        $validated['kode_mapel'] = strtoupper($validated['kode_mapel']);
        $validated['nama_mapel'] = ucwords(strtolower($validated['nama_mapel']));

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

        $instansi = Auth::user()->getInstansi();
        $kelompokRule = $instansi->jenjang === 'SMA'
            ? 'required|in:Umum,Jurusan,Muatan Lokal'
            : 'required|in:Umum,Muatan Lokal';

        $validated = $request->validate([
            'nama_mapel' => 'required|string|max:255',
            'kode_mapel' => 'required|string|max:20|unique:mata_pelajaran,kode_mapel,'.$mataPelajaran->id_mapel.',id_mapel,instansi_id,'.$mataPelajaran->instansi_id,
            'kelompok'   => $kelompokRule,
        ]);

        $validated['kode_mapel'] = strtoupper($validated['kode_mapel']);
        $validated['nama_mapel'] = ucwords(strtolower($validated['nama_mapel']));

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