<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HariLibur;
use Carbon\CarbonPeriod;
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
                        <button type="submit" title="Hapus" class="text-red-600 hover:text-red-800" onclick="return confirm(\'Yakin hapus hari libur ini?\')">
                            <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                        </form>';
                })
                ->rawColumns(['tipe', 'aksi'])
                ->make(true);
        }

        $liburNasional = HariLibur::whereNull('instansi_id')
            ->where('is_nasional', true)
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
            'nama_libur'      => 'required|string|max:255',
            'tanggal_mulai'   => 'required|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
        ]);

        $tanggalMulai = $validated['tanggal_mulai'];
        $tanggalSelesai = $validated['tanggal_selesai'] ?? $tanggalMulai;

        $period = CarbonPeriod::create($tanggalMulai, $tanggalSelesai);
        $inserted = 0;
        $skipped = 0;

        foreach ($period as $date) {
            $exists = HariLibur::where('tanggal', $date->format('Y-m-d'))
                ->where('instansi_id', $instansi->id_instansi)
                ->exists();

            if ($exists) {
                $skipped++;
                continue;
            }

            HariLibur::create([
                'instansi_id' => $instansi->id_instansi,
                'tanggal'     => $date->format('Y-m-d'),
                'nama_libur'  => $validated['nama_libur'],
                'is_nasional' => false,
            ]);
            $inserted++;
        }

        $msg = $inserted . ' hari libur berhasil ditambahkan.';
        if ($skipped > 0) {
            $msg .= ' ' . $skipped . ' tanggal sudah ada (di-skip).';
        }

        return back()->with('success', $msg);
    }

    public function adopt(Request $request)
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
            return back()->with('error', 'Tanggal ini sudah ada di daftar libur sekolah!');
        }

        HariLibur::create([
            'instansi_id' => $instansi->id_instansi,
            'tanggal'     => $validated['tanggal'],
            'nama_libur'  => $validated['nama_libur'],
            'is_nasional' => true,
        ]);

        return back()->with('success', 'Berhasil mengikuti libur nasional!');
    }

    public function adoptAll()
    {
        $instansi = Auth::user()->getInstansi();

        $nasional = HariLibur::whereNull('instansi_id')
            ->where('is_nasional', true)
            ->get();

        $inserted = 0;
        $skipped = 0;

        foreach ($nasional as $libur) {
            $exists = HariLibur::where('tanggal', $libur->tanggal)
                ->where('instansi_id', $instansi->id_instansi)
                ->exists();

            if ($exists) {
                $skipped++;
                continue;
            }

            HariLibur::create([
                'instansi_id' => $instansi->id_instansi,
                'tanggal'     => $libur->tanggal,
                'nama_libur'  => $libur->nama_libur,
                'is_nasional' => true,
            ]);
            $inserted++;
        }

        $msg = $inserted . ' libur nasional berhasil diikuti.';
        if ($skipped > 0) {
            $msg .= ' ' . $skipped . ' sudah ada sebelumnya.';
        }

        return back()->with('success', $msg);
    }

    public function destroy(HariLibur $hariLibur)
    {
        $instansi = Auth::user()->getInstansi();
        abort_if($hariLibur->instansi_id !== $instansi->id_instansi, 403);

        $hariLibur->delete();

        return back()->with('success', 'Hari libur berhasil dihapus!');
    }
}
