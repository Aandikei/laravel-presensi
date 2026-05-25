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
                    $edit = '<a href="' . route('admin.master-poin.edit', $row->id_poin) . '"
                        class="px-3 py-1 text-xs font-medium text-white bg-blue-600 rounded hover:bg-blue-700">
                        Edit</a>';
                    $delete = '<form method="POST" action="' . route('admin.master-poin.destroy', $row->id_poin) . '" class="inline">
                        <input type="hidden" name="_token" value="' . csrf_token() . '">
                        <input type="hidden" name="_method" value="DELETE">
                        <button type="submit"
                            class="px-3 py-1 text-xs font-medium text-white bg-red-600 rounded hover:bg-red-700"
                            onclick="return confirm(\'Yakin hapus pelanggaran ini?\')">
                            Hapus</button>
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