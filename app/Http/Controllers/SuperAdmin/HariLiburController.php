<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\HariLibur;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class HariLiburController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $hariLibur = HariLibur::whereNull('instansi_id')
                ->where('is_nasional', true)
                ->select('hari_libur.*');

            return DataTables::of($hariLibur)
                ->addIndexColumn()
                ->addColumn('tanggal', fn($row) => Carbon::parse($row->tanggal)->format('d F Y'))
                ->addColumn('aksi', function ($row) {
                    return '<form method="POST" action="' . route('superadmin.hari-libur.destroy', $row->id_libur) . '" class="inline">
                        <input type="hidden" name="_token" value="' . csrf_token() . '">
                        <input type="hidden" name="_method" value="DELETE">
                        <button type="submit" title="Hapus" class="text-red-600 hover:text-red-800" onclick="return confirm(\'Yakin hapus libur nasional ini?\')">
                            <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                        </form>';
                })
                ->rawColumns(['aksi'])
                ->make(true);
        }

        return view('superadmin.hari-libur.index');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_libur'     => 'required|string|max:255',
            'tanggal_mulai'  => 'required|date',
            'tanggal_selesai'=> 'required|date|after_or_equal:tanggal_mulai',
        ]);

        $period = CarbonPeriod::create($validated['tanggal_mulai'], $validated['tanggal_selesai']);
        $inserted = 0;
        $skipped = 0;

        foreach ($period as $date) {
            $exists = HariLibur::whereNull('instansi_id')
                ->where('is_nasional', true)
                ->where('tanggal', $date->format('Y-m-d'))
                ->exists();

            if ($exists) {
                $skipped++;
                continue;
            }

            HariLibur::create([
                'instansi_id' => null,
                'tanggal'     => $date->format('Y-m-d'),
                'nama_libur'  => $validated['nama_libur'],
                'is_nasional' => true,
            ]);
            $inserted++;
        }

        $msg = $inserted . ' hari libur nasional berhasil ditambahkan.';
        if ($skipped > 0) {
            $msg .= ' ' . $skipped . ' tanggal sudah ada (di-skip).';
        }

        return redirect()->route('superadmin.hari-libur.index')
            ->with('success', $msg);
    }

    public function destroy(HariLibur $hariLibur)
    {
        abort_if($hariLibur->instansi_id !== null || !$hariLibur->is_nasional, 403);

        $hariLibur->delete();

        return redirect()->route('superadmin.hari-libur.index')
            ->with('success', 'Libur nasional berhasil dihapus!');
    }
}
