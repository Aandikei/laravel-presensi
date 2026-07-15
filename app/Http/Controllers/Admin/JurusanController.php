<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Jurusan;
use App\Models\Kelas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class JurusanController extends Controller
{
    public function index(Request $request)
    {
        $instansi = Auth::user()->getInstansi();

        if ($request->ajax()) {
            $jurusan = Jurusan::withCount('kelas')
                ->where('instansi_id', $instansi->id_instansi);

            return DataTables::of($jurusan)
                ->addIndexColumn()
                ->addColumn('kelas_count', fn ($row) => $row->kelas_count ?? 0)
                ->addColumn('aksi', function ($row) {
                    $html = '<a href="' . route('admin.jurusan.edit', $row->id_jurusan) . '" title="Edit" class="text-blue-600 hover:text-blue-800">
                        <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </a>';
                    $html .= ' <form method="POST" action="' . route('admin.jurusan.destroy', $row->id_jurusan) . '" class="inline">
                        <input type="hidden" name="_token" value="' . csrf_token() . '">
                        <input type="hidden" name="_method" value="DELETE">
                        <button type="button" title="Hapus" class="text-red-600 hover:text-red-800" onclick="confirmAction(this.closest(\'form\'), \'Yakin hapus jurusan ini? Semua kelas dengan jurusan ini akan kehilangan relasi jurusan.\')">
                            <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </form>';
                    return $html;
                })
                ->rawColumns(['aksi'])
                ->make(true);
        }

        return view('admin.jurusan.index');
    }

    public function create()
    {
        return view('admin.jurusan.create');
    }

    public function store(Request $request)
    {
        $instansi = Auth::user()->getInstansi();

        $validated = $request->validate([
            'kode_jurusan' => 'required|string|max:50',
            'nama_jurusan' => 'required|string|max:255',
        ]);

        $validated['kode_jurusan'] = strtoupper($validated['kode_jurusan']);
        $validated['nama_jurusan'] = ucwords(strtolower($validated['nama_jurusan']));

        $exists = Jurusan::where('instansi_id', $instansi->id_instansi)
            ->where(function ($q) use ($validated) {
                $q->where('kode_jurusan', $validated['kode_jurusan'])
                  ->orWhere('nama_jurusan', $validated['nama_jurusan']);
            })
            ->exists();

        if ($exists) {
            return back()->with('error', 'Kode atau nama jurusan sudah ada!')->withInput();
        }

        Jurusan::create([
            ...$validated,
            'instansi_id' => $instansi->id_instansi,
        ]);

        return redirect()->route('admin.jurusan.index')
            ->with('success', 'Jurusan berhasil ditambahkan!');
    }

    public function edit(Jurusan $jurusan)
    {
        $this->authorizeInstansi($jurusan);
        return view('admin.jurusan.edit', compact('jurusan'));
    }

    public function update(Request $request, Jurusan $jurusan)
    {
        $this->authorizeInstansi($jurusan);

        $validated = $request->validate([
            'kode_jurusan' => 'required|string|max:50',
            'nama_jurusan' => 'required|string|max:255',
        ]);

        $validated['kode_jurusan'] = strtoupper($validated['kode_jurusan']);
        $validated['nama_jurusan'] = ucwords(strtolower($validated['nama_jurusan']));

        $exists = Jurusan::where('instansi_id', $jurusan->instansi_id)
            ->where('id_jurusan', '!=', $jurusan->id_jurusan)
            ->where(function ($q) use ($validated) {
                $q->where('kode_jurusan', $validated['kode_jurusan'])
                  ->orWhere('nama_jurusan', $validated['nama_jurusan']);
            })
            ->exists();

        if ($exists) {
            return back()->with('error', 'Kode atau nama jurusan sudah digunakan jurusan lain!')->withInput();
        }

        $jurusan->update($validated);

        return redirect()->route('admin.jurusan.index')
            ->with('success', 'Jurusan berhasil diupdate!');
    }

    public function destroy(Jurusan $jurusan)
    {
        $this->authorizeInstansi($jurusan);

        if ($jurusan->kelas()->exists()) {
            foreach ($jurusan->kelas as $k) {
                $k->jurusan_id = null;
                $k->save();
            }
        }

        $jurusan->delete();

        return redirect()->route('admin.jurusan.index')
            ->with('success', 'Jurusan berhasil dihapus!');
    }

    private function authorizeInstansi(Jurusan $jurusan): void
    {
        $instansi = Auth::user()->getInstansi();
        abort_if($jurusan->instansi_id !== $instansi->id_instansi, 403);
    }
}
