<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MasterPoin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class MasterPoinController extends Controller
{
    public function index(Request $request)
    {
        $instansi = Auth::user()->getInstansi();

        if ($request->ajax()) {
            $masterPoin = MasterPoin::where('instansi_id', $instansi->id_instansi)
                ->select('master_poin.*');

            return DataTables::of($masterPoin)
                ->addIndexColumn()
                ->addColumn('aksi', function($row) {
                    $edit = '<a href="' . route('admin.master-poin.edit', $row->id_poin) . '" title="Edit" class="text-blue-600 hover:text-blue-800">
                        <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                    </a>';
                    $delete = '<form method="POST" action="' . route('admin.master-poin.destroy', $row->id_poin) . '" class="inline">
                        <input type="hidden" name="_token" value="' . csrf_token() . '">
                        <input type="hidden" name="_method" value="DELETE">
                        <button type="submit" title="Hapus" class="text-red-600 hover:text-red-800" onclick="return confirm(\'Yakin hapus pelanggaran ini?\')">
                            <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                        </form>';
                    return $edit . ' ' . $delete;
                })
                ->rawColumns(['aksi'])
                ->make(true);
        }

        return view('admin.master-poin.index');
    }

    public function create()
    {
        return view('admin.master-poin.create');
    }

    public function store(Request $request)
    {
        $instansi = Auth::user()->getInstansi();

        $validated = $request->validate([
            'nama_pelanggaran' => 'required|string|max:255',
            'deskripsi'        => 'nullable|string',
            'jumlah_poin'      => 'required|integer|min:1|max:100',
        ]);

        MasterPoin::create([
            ...$validated,
            'instansi_id' => $instansi->id_instansi,
        ]);

        return redirect()->route('admin.master-poin.index')
            ->with('success', 'Pelanggaran berhasil ditambahkan!');
    }

    public function edit(MasterPoin $masterPoin)
    {
        $this->authorizeInstansi($masterPoin);
        return view('admin.master-poin.edit', compact('masterPoin'));
    }

    public function update(Request $request, MasterPoin $masterPoin)
    {
        $this->authorizeInstansi($masterPoin);

        $validated = $request->validate([
            'nama_pelanggaran' => 'required|string|max:255',
            'deskripsi'        => 'nullable|string',
            'jumlah_poin'      => 'required|integer|min:1|max:100',
        ]);

        $masterPoin->update($validated);

        return redirect()->route('admin.master-poin.index')
            ->with('success', 'Pelanggaran berhasil diupdate!');
    }

    public function destroy(MasterPoin $masterPoin)
    {
        $this->authorizeInstansi($masterPoin);

        if ($masterPoin->logPoin()->exists()) {
            return back()->with('error', 'Pelanggaran tidak bisa dihapus karena sudah ada log poin terkait!');
        }

        $masterPoin->delete();

        return redirect()->route('admin.master-poin.index')
            ->with('success', 'Pelanggaran berhasil dihapus!');
    }

    private function authorizeInstansi(MasterPoin $masterPoin): void
    {
        $instansi = Auth::user()->getInstansi();
        abort_if($masterPoin->instansi_id !== $instansi->id_instansi, 403);
    }
}