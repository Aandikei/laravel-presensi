<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\KurikulumKelas;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class JadwalController extends Controller
{
    public function index(Request $request)
    {
        $instansi = Auth::user()->getInstansi();

        if ($request->ajax()) {
            $jadwal = Jadwal::with(['kurikulum.kelas', 'kurikulum.mataPelajaran', 'kurikulum.guru'])
                ->whereHas('kurikulum.kelas', fn($q) => $q->where('instansi_id', $instansi->id_instansi))
                ->select('jadwal.*');

            if ($request->kelas_id) {
                $jadwal->whereHas('kurikulum', fn($q) => $q->where('kelas_id', $request->kelas_id));
            }

            return DataTables::of($jadwal)
                ->addIndexColumn()
                ->addColumn('kelas', fn($row) => $row->kurikulum->kelas->nama_kelas)
                ->addColumn('mata_pelajaran', fn($row) => $row->kurikulum->mataPelajaran->nama_mapel)
                ->addColumn('guru', fn($row) => $row->kurikulum->guru->nama_guru)
                ->addColumn('jam', fn($row) => substr($row->jam_mulai, 0, 5) . ' - ' . substr($row->jam_selesai, 0, 5))
                ->addColumn('aksi', function ($row) {
                    $edit = '<a href="' . route('admin.jadwal.edit', $row->id_jadwal) . '"
                        class="px-3 py-1 text-xs font-medium text-white bg-blue-600 rounded hover:bg-blue-700">
                        Edit</a>';
                    $delete = '<form method="POST" action="' . route('admin.jadwal.destroy', $row->id_jadwal) . '" class="inline">
                        <input type="hidden" name="_token" value="' . csrf_token() . '">
                        <input type="hidden" name="_method" value="DELETE">
                        <button type="submit"
                            class="px-3 py-1 text-xs font-medium text-white bg-red-600 rounded hover:bg-red-700"
                            onclick="return confirm(\'Yakin hapus jadwal ini?\')">
                            Hapus</button>
                        </form>';
                    return $edit . ' ' . $delete;
                })
                ->rawColumns(['aksi'])
                ->make(true);
        }

        $instansi = Auth::user()->getInstansi();
        $tahunAjaran = TahunAjaran::where('instansi_id', $instansi->id_instansi)
            ->orderByDesc('is_aktif')->get();
        $kelas = Kelas::where('instansi_id', $instansi->id_instansi)
            ->orderBy('tingkat')
            ->orderBy('nama_kelas')
            ->get();

        return view('admin.jadwal.index', compact('tahunAjaran', 'kelas'));
    }

    public function create()
    {
        $instansi = Auth::user()->getInstansi();

        $kelas = Kelas::where('instansi_id', $instansi->id_instansi)
            ->orderBy('tingkat')
            ->orderBy('nama_kelas')
            ->get();

        return view('admin.jadwal.create', compact('kelas'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kurikulum_id' => 'required|exists:kurikulum_kelas,id_kurikulum',
            'hari'         => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu',
            'jam_mulai'    => 'required|date_format:H:i',
            'jam_selesai'  => 'required|date_format:H:i|after:jam_mulai',
        ]);

        // Cek konflik jadwal di kelas yang sama
        $konflik = Jadwal::whereHas('kurikulum', function($q) use ($request) {
            $q->where('kelas_id', KurikulumKelas::find($request->kurikulum_id)->kelas_id);
        })
        ->where('hari', $validated['hari'])
        ->where(function($q) use ($validated) {
            $q->whereBetween('jam_mulai', [$validated['jam_mulai'], $validated['jam_selesai']])
              ->orWhereBetween('jam_selesai', [$validated['jam_mulai'], $validated['jam_selesai']])
              ->orWhere(function($q) use ($validated) {
                  $q->where('jam_mulai', '<=', $validated['jam_mulai'])
                    ->where('jam_selesai', '>=', $validated['jam_selesai']);
              });
        })->exists();

        if ($konflik) {
            return back()->withErrors([
                'jam_mulai' => 'Jadwal bentrok dengan jadwal lain di kelas dan hari yang sama!'
            ])->withInput();
        }

        Jadwal::create($validated);

        return redirect()->route('admin.jadwal.index')
            ->with('success', 'Jadwal berhasil ditambahkan!');
    }

    public function edit(Jadwal $jadwal)
    {
        $this->authorizeInstansi($jadwal);

        $instansi = Auth::user()->getInstansi();
        $kelas = Kelas::where('instansi_id', $instansi->id_instansi)
            ->orderBy('tingkat')
            ->orderBy('nama_kelas')
            ->get();

        return view('admin.jadwal.edit', compact('jadwal', 'kelas'));
    }

    public function update(Request $request, Jadwal $jadwal)
    {
        $this->authorizeInstansi($jadwal);

        $validated = $request->validate([
            'kurikulum_id' => 'required|exists:kurikulum_kelas,id_kurikulum',
            'hari'         => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu',
            'jam_mulai'    => 'required|date_format:H:i',
            'jam_selesai'  => 'required|date_format:H:i|after:jam_mulai',
        ]);

        // Cek konflik kecuali jadwal ini sendiri
        $konflik = Jadwal::whereHas('kurikulum', function($q) use ($request) {
            $q->where('kelas_id', KurikulumKelas::find($request->kurikulum_id)->kelas_id);
        })
        ->where('hari', $validated['hari'])
        ->where('id_jadwal', '!=', $jadwal->id_jadwal)
        ->where(function($q) use ($validated) {
            $q->whereBetween('jam_mulai', [$validated['jam_mulai'], $validated['jam_selesai']])
              ->orWhereBetween('jam_selesai', [$validated['jam_mulai'], $validated['jam_selesai']])
              ->orWhere(function($q) use ($validated) {
                  $q->where('jam_mulai', '<=', $validated['jam_mulai'])
                    ->where('jam_selesai', '>=', $validated['jam_selesai']);
              });
        })->exists();

        if ($konflik) {
            return back()->withErrors([
                'jam_mulai' => 'Jadwal bentrok dengan jadwal lain di kelas dan hari yang sama!'
            ])->withInput();
        }

        $jadwal->update($validated);

        return redirect()->route('admin.jadwal.index')
            ->with('success', 'Jadwal berhasil diupdate!');
    }

    public function destroy(Jadwal $jadwal)
    {
        $this->authorizeInstansi($jadwal);

        if ($jadwal->absensi()->exists()) {
            return back()->with('error', 'Jadwal tidak bisa dihapus karena sudah ada data absensi!');
        }

        $jadwal->delete();

        return redirect()->route('admin.jadwal.index')
            ->with('success', 'Jadwal berhasil dihapus!');
    }

    private function authorizeInstansi(Jadwal $jadwal): void
    {
        $instansi = Auth::user()->getInstansi();
        abort_if($jadwal->kurikulum->kelas->instansi_id !== $instansi->id_instansi, 403);
    }
}