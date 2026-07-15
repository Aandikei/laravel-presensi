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
                ->addColumn('guru', function ($row) use ($instansi) {
                    $guru = $row->kurikulum?->guru;
                    if (!$guru) return '-';
                    $name = $guru->nama_guru;
                    if ($guru->transfer_token && !$guru->isTransferTokenExpired()) {
                        return $name . ' <span class="px-2 py-1 text-xs font-medium text-orange-700 bg-orange-100 rounded-full">Mutasi</span>';
                    }
                    if ($guru->instansi_id !== $instansi->id_instansi) {
                        return $name . ' <span class="px-2 py-1 text-xs font-medium text-blue-700 bg-blue-100 rounded-full">Pindah</span>';
                    }
                    if ($guru->status === 'Keluar') {
                        return $name . ' <span class="px-2 py-1 text-xs font-medium text-red-700 bg-red-100 rounded-full">Keluar</span>';
                    }
                    if ($guru->status === 'Pensiun') {
                        return $name . ' <span class="px-2 py-1 text-xs font-medium text-gray-700 bg-gray-200 rounded-full">Pensiun</span>';
                    }
                    return $name;
                })
                ->addColumn('jam', fn($row) => substr($row->jam_mulai, 0, 5) . ' - ' . substr($row->jam_selesai, 0, 5))
                ->addColumn('aksi', function ($row) {
                    if (!Auth::user()->can('manage-settings')) {
                        return '';
                    }
                    $edit = '<a href="' . route('admin.jadwal.edit', $row->id_jadwal) . '" title="Edit" class="text-blue-600 hover:text-blue-800">
                        <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                    </a>';
                    $delete = '<form method="POST" action="' . route('admin.jadwal.destroy', $row->id_jadwal) . '" class="inline">
                        <input type="hidden" name="_token" value="' . csrf_token() . '">
                        <input type="hidden" name="_method" value="DELETE">
                        <button type="button" title="Hapus" class="text-red-600 hover:text-red-800" onclick="confirmAction(this.closest(\'form\'), \'Yakin hapus jadwal ini?\')">
                            <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                        </form>';
                    return $edit . ' ' . $delete;
                })
                ->rawColumns(['guru', 'aksi'])
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

        $kurikulum = KurikulumKelas::with('kelas')->findOrFail($request->kurikulum_id);

        // Cek 1: jam bentrok di kelas & hari yang sama
        $konflik = Jadwal::whereHas('kurikulum', fn($q) => $q->where('kelas_id', $kurikulum->kelas_id))
            ->where('hari', $validated['hari'])
            ->where(fn($q) => $q
                ->whereBetween('jam_mulai', [$validated['jam_mulai'], $validated['jam_selesai']])
                ->orWhereBetween('jam_selesai', [$validated['jam_mulai'], $validated['jam_selesai']])
                ->orWhere(fn($q) => $q
                    ->where('jam_mulai', '<=', $validated['jam_mulai'])
                    ->where('jam_selesai', '>=', $validated['jam_selesai'])
                )
            )->exists();

        if ($konflik) {
            return back()->withErrors([
                'jam_mulai' => 'Jadwal bentrok dengan jadwal lain di kelas dan hari yang sama!'
            ])->withInput();
        }

        // Cek 2: mapel sama di kelas & hari yang sama
        $mapelSama = Jadwal::whereHas('kurikulum', fn($q) => $q
            ->where('kelas_id', $kurikulum->kelas_id)
            ->where('mapel_id', $kurikulum->mapel_id))
            ->where('hari', $validated['hari'])
            ->exists();

        if ($mapelSama) {
            return back()->withErrors([
                'hari' => 'Mata pelajaran ini sudah dijadwalkan di kelas yang sama pada hari yang sama!'
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

        $kurikulum = KurikulumKelas::with('kelas')->findOrFail($request->kurikulum_id);

        // Cek 1: jam bentrok (kecuali jadwal ini sendiri)
        $konflik = Jadwal::whereHas('kurikulum', fn($q) => $q->where('kelas_id', $kurikulum->kelas_id))
            ->where('hari', $validated['hari'])
            ->where('id_jadwal', '!=', $jadwal->id_jadwal)
            ->where(fn($q) => $q
                ->whereBetween('jam_mulai', [$validated['jam_mulai'], $validated['jam_selesai']])
                ->orWhereBetween('jam_selesai', [$validated['jam_mulai'], $validated['jam_selesai']])
                ->orWhere(fn($q) => $q
                    ->where('jam_mulai', '<=', $validated['jam_mulai'])
                    ->where('jam_selesai', '>=', $validated['jam_selesai'])
                )
            )->exists();

        if ($konflik) {
            return back()->withErrors([
                'jam_mulai' => 'Jadwal bentrok dengan jadwal lain di kelas dan hari yang sama!'
            ])->withInput();
        }

        // Cek 2: mapel sama di kelas & hari yang sama (kecuali jadwal ini)
        $mapelSama = Jadwal::whereHas('kurikulum', fn($q) => $q
            ->where('kelas_id', $kurikulum->kelas_id)
            ->where('mapel_id', $kurikulum->mapel_id))
            ->where('hari', $validated['hari'])
            ->where('id_jadwal', '!=', $jadwal->id_jadwal)
            ->exists();

        if ($mapelSama) {
            return back()->withErrors([
                'hari' => 'Mata pelajaran ini sudah dijadwalkan di kelas yang sama pada hari yang sama!'
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