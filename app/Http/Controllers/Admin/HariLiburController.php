<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HariLibur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class HariLiburController extends Controller
{
    public function index(Request $request)
    {
        $instansi = Auth::user()->getInstansi();

        if ($request->ajax()) {
            $hariLibur = HariLibur::where('instansi_id', $instansi->id_instansi)
                ->select('hari_libur.*');

            return DataTables::of($hariLibur)
                ->addIndexColumn()
                ->addColumn('tipe', function($row) {
                    return $row->is_nasional
                        ? '<span class="px-2 py-1 text-xs font-medium text-blue-700 bg-blue-100 rounded-full">Nasional</span>'
                        : '<span class="px-2 py-1 text-xs font-medium text-purple-700 bg-purple-100 rounded-full">Sekolah</span>';
                })
                ->addColumn('aksi', function($row) {
                    return '<form method="POST" action="' . route('admin.hari-libur.destroy', $row->id_libur) . '" class="inline">
                        <input type="hidden" name="_token" value="' . csrf_token() . '">
                        <input type="hidden" name="_method" value="DELETE">
                        <button type="submit"
                            class="px-3 py-1 text-xs font-medium text-white bg-red-600 rounded hover:bg-red-700"
                            onclick="return confirm(\'Yakin hapus hari libur ini?\')">
                            Hapus</button>
                        </form>';
                })
                ->rawColumns(['tipe', 'aksi'])
                ->make(true);
        }

        // Data untuk view
        $liburNasional = HariLibur::where('is_nasional', true)
            ->orderBy('tanggal')
            ->get();

        $liburSekolah = HariLibur::where('instansi_id', $instansi->id_instansi)
            ->get();

        return view('admin.hari-libur.index', compact('liburNasional', 'liburSekolah'));
    }

    public function store(Request $request)
    {
        $instansi = Auth::user()->getInstansi();

        $validated = $request->validate([
            'tanggal'    => 'required|date',
            'nama_libur' => 'required|string|max:255',
        ]);

        $exists = HariLibur::where('tanggal', $validated['tanggal'])
            ->where('instansi_id', $instansi->id_instansi)
            ->exists();

        if ($exists) {
            return back()->withErrors([
                'tanggal' => 'Tanggal ini sudah ditandai sebagai hari libur!'
            ])->withInput();
        }

        HariLibur::create([
            'instansi_id' => $instansi->id_instansi,
            'tanggal'     => $validated['tanggal'],
            'nama_libur'  => $validated['nama_libur'],
            'is_nasional' => false,
        ]);

        return back()->with('success', 'Hari libur sekolah berhasil ditambahkan!');
    }

    public function adopt(Request $request)
    {
        $instansi = Auth::user()->getInstansi();

        $validated = $request->validate([
            'tanggal'    => 'required|date',
            'nama_libur' => 'required|string|max:255',
        ]);

        // Pastiin memang ada di libur nasional
        $liburNasional = HariLibur::where('tanggal', $validated['tanggal'])
            ->where('is_nasional', true)
            ->exists();

        if (!$liburNasional) {
            return back()->with('error', 'Libur nasional tidak ditemukan!');
        }

        // Cek duplikat
        $exists = HariLibur::where('tanggal', $validated['tanggal'])
            ->where('instansi_id', $instansi->id_instansi)
            ->exists();

        if ($exists) {
            return back()->with('error', 'Tanggal ini sudah ada di daftar libur sekolah!');
        }

        // Simpan sebagai libur sekolah dengan flag is_nasional = true
        // supaya keliatan asalnya dari libur nasional
        HariLibur::create([
            'instansi_id' => $instansi->id_instansi,
            'tanggal'     => $validated['tanggal'],
            'nama_libur'  => $validated['nama_libur'],
            'is_nasional' => true,
        ]);

        return back()->with('success', 'Berhasil mengikuti libur nasional!');
    }

    public function destroy(HariLibur $hariLibur)
    {
        $instansi = Auth::user()->getInstansi();
        abort_if($hariLibur->instansi_id !== $instansi->id_instansi, 403);

        $hariLibur->delete();

        return back()->with('success', 'Hari libur berhasil dihapus!');
    }
}