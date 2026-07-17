<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\KurikulumKelas;
use App\Models\MataPelajaran;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class KurikulumKelasController extends Controller
{
    public function index(Request $request)
    {
        $instansi = Auth::user()->getInstansi();

        if ($request->ajax()) {
            $kurikulum = KurikulumKelas::with(['kelas', 'mataPelajaran', 'guru'])
                ->whereHas('kelas', function ($q) use ($instansi) {
                    $q->where('instansi_id', '=', $instansi->id_instansi);
                })
                ->when($request->kelas_id, fn($q) => $q->where('kelas_id', $request->kelas_id))
                ->when($request->mapel_id, fn($q) => $q->where('mapel_id', $request->mapel_id))
                ->when($request->status_guru, function ($q) use ($request, $instansi) {
                    match ($request->status_guru) {
                        'Aktif'   => $q->whereHas('guru', fn($qq) => $qq->where('instansi_id', $instansi->id_instansi)->whereNull('status')),
                        'Keluar'  => $q->whereHas('guru', fn($qq) => $qq->where('status', 'Keluar')),
                        'Pensiun' => $q->whereHas('guru', fn($qq) => $qq->where('status', 'Pensiun')),
                        'Mutasi'  => $q->whereHas('guru', fn($qq) => $qq->where('instansi_id', '!=', $instansi->id_instansi)->whereNull('status')),
                        default   => $q,
                    };
                })
                ->select('kurikulum_kelas.*');

            return DataTables::of($kurikulum)
                ->addIndexColumn()
                ->addColumn('kelas', fn ($row) => $row->kelas->nama_kelas)
                ->addColumn('mata_pelajaran', fn ($row) => $row->mataPelajaran->nama_mapel)
                ->addColumn('guru', function ($row) use ($instansi) {
                    $guru = $row->guru;
                    if (!$guru) return '-';
                    $name = $guru->nama_guru;
                    if ($guru->instansi_id !== $instansi->id_instansi) {
                        return $name . ' <span class="px-2 py-1 text-xs font-medium text-orange-700 bg-orange-100 rounded-full">Mutasi</span>';
                    }
                    if ($guru->status === 'Keluar') {
                        return $name . ' <span class="px-2 py-1 text-xs font-medium text-red-700 bg-red-100 rounded-full">Keluar</span>';
                    }
                    if ($guru->status === 'Pensiun') {
                        return $name . ' <span class="px-2 py-1 text-xs font-medium text-gray-700 bg-gray-200 rounded-full">Pensiun</span>';
                    }
                    return $name;
                })
                ->addColumn('aksi', function ($row) {
                    if (!Auth::user()->can('manage-settings')) {
                        return '';
                    }
                    $edit = '<a href="'.route('admin.kurikulum.edit', $row->id_kurikulum).'" title="Edit" class="text-blue-600 hover:text-blue-800">
                        <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                    </a>';
                    $delete = '<form method="POST" action="'.route('admin.kurikulum.destroy', $row->id_kurikulum).'" class="inline">
                        <input type="hidden" name="_token" value="'.csrf_token().'">
                        <input type="hidden" name="_method" value="DELETE">
                        <button type="button" title="Hapus" class="text-red-600 hover:text-red-800" onclick="confirmAction(this.closest(\'form\'), \'Yakin hapus kurikulum ini?\')">
                            <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                        </form>';

                    return $edit.' '.$delete;
                })
                ->rawColumns(['guru', 'aksi'])
                ->make(true);
        }

        $kelas = Kelas::where('instansi_id', $instansi->id_instansi)
            ->orderBy('tingkat')->orderBy('nama_kelas')
            ->get();

        $mapel = MataPelajaran::where('instansi_id', $instansi->id_instansi)
            ->orderBy('nama_mapel')
            ->get();

        return view('admin.kurikulum.index', compact('kelas', 'mapel'));
    }

    public function create()
    {
        $instansi = Auth::user()->getInstansi();

        $tahunAjaran = TahunAjaran::where('instansi_id', '=', $instansi->id_instansi)
            ->orderByDesc('is_aktif')
            ->get();

        $kelas = Kelas::where('instansi_id', '=', $instansi->id_instansi)
            ->orderBy('tingkat')
            ->orderBy('nama_kelas')
            ->get();

        $guru = Guru::where('instansi_id', '=', $instansi->id_instansi)
            ->whereNull('status')
            ->orderBy('nama_guru')
            ->get();

        $mapel = MataPelajaran::where('instansi_id', '=', $instansi->id_instansi)
            ->orderBy('nama_mapel')
            ->get();

        return view('admin.kurikulum.create', compact('tahunAjaran', 'kelas', 'guru', 'mapel'));
    }

    public function store(Request $request)
    {
        $instansi = Auth::user()->getInstansi();

        $validated = $request->validate([
            'kelas_id' => 'required|exists:kelas,id_kelas',
            'mapel_id' => 'required|exists:mata_pelajaran,id_mapel',
            'guru_id' => 'required|exists:guru,id_guru',
        ]);

        // Pastiin kelas milik instansi ini
        $kelas = Kelas::findOrFail($validated['kelas_id']);
        abort_if($kelas->instansi_id !== $instansi->id_instansi, 403);

        // Cek duplikat — tolak hanya jika guru lama masih aktif
        $existing = KurikulumKelas::with('guru')
            ->where('kelas_id', $validated['kelas_id'])
            ->where('mapel_id', $validated['mapel_id'])
            ->first();

        if ($existing) {
            $guruLama = $existing->guru;
            if ($guruLama && $guruLama->instansi_id === $instansi->id_instansi && is_null($guruLama->status)) {
                return back()->withErrors([
                    'mapel_id' => 'Mata pelajaran ini sudah ada di kelas tersebut dengan guru yang masih aktif!',
                ])->withInput();
            }
            // Guru lama non-aktif — insert baru + copy jadwal
            $kurikulumBaru = KurikulumKelas::create($validated);

            $jadwalLama = Jadwal::where('kurikulum_id', $existing->id_kurikulum)->get();

            if ($jadwalLama->isNotEmpty()) {
                Jadwal::insert(
                    $jadwalLama->map(fn($j) => [
                        'kurikulum_id' => $kurikulumBaru->id_kurikulum,
                        'hari'         => $j->hari,
                        'jam_mulai'    => $j->jam_mulai,
                        'jam_selesai'  => $j->jam_selesai,
                        'created_at'   => now(),
                        'updated_at'   => now(),
                    ])->toArray()
                );
            }

            return redirect()->route('admin.kurikulum.index')
                ->with('success', 'Kurikulum berhasil ditambahkan beserta jadwal dari guru sebelumnya!');
        }

        KurikulumKelas::create($validated);

        return redirect()->route('admin.kurikulum.index')
            ->with('success', 'Kurikulum berhasil ditambahkan!');
    }

    public function edit(KurikulumKelas $kurikulum)
    {
        $this->authorizeInstansi($kurikulum);

        $instansi = Auth::user()->getInstansi();

        $kelas = Kelas::where('instansi_id', '=', $instansi->id_instansi)
            ->orderBy('tingkat')
            ->orderBy('nama_kelas')
            ->get();

        $guru = Guru::where('instansi_id', '=', $instansi->id_instansi)
            ->whereNull('status')
            ->orderBy('nama_guru')
            ->get();

        $mapel = MataPelajaran::where('instansi_id', '=', $instansi->id_instansi)
            ->orderBy('nama_mapel')
            ->get();

        return view('admin.kurikulum.edit', compact('kurikulum', 'kelas', 'guru', 'mapel'));
    }

    public function update(Request $request, KurikulumKelas $kurikulum)
    {
        $instansi = Auth::user()->getInstansi();
        $this->authorizeInstansi($kurikulum);

        $validated = $request->validate([
            'kelas_id' => 'required|exists:kelas,id_kelas',
            'mapel_id' => 'required|exists:mata_pelajaran,id_mapel',
            'guru_id' => 'required|exists:guru,id_guru',
        ]);

        // Cek duplikat — tolak hanya jika guru lama masih aktif
        $existing = KurikulumKelas::with('guru')
            ->where('kelas_id', $validated['kelas_id'])
            ->where('mapel_id', $validated['mapel_id'])
            ->where('id_kurikulum', '!=', $kurikulum->id_kurikulum)
            ->first();

        if ($existing) {
            $guruLama = $existing->guru;
            if ($guruLama && $guruLama->instansi_id === $instansi->id_instansi && is_null($guruLama->status)) {
                return back()->withErrors([
                    'mapel_id' => 'Mata pelajaran ini sudah ada di kelas tersebut dengan guru yang masih aktif!',
                ])->withInput();
            }
            // Guru lama non-aktif — biarkan duplikat, data historis tetap tersimpan
        }

        $kurikulum->update($validated);

        return redirect()->route('admin.kurikulum.index')
            ->with('success', 'Kurikulum berhasil diupdate!');
    }

    public function destroy(KurikulumKelas $kurikulum)
    {
        $this->authorizeInstansi($kurikulum);

        if ($kurikulum->jadwal()->exists()) {
            return back()->with('error', 'Kurikulum tidak bisa dihapus karena masih ada jadwal terkait!');
        }

        $kurikulum->delete();

        return redirect()->route('admin.kurikulum.index')
            ->with('success', 'Kurikulum berhasil dihapus!');
    }

    private function authorizeInstansi(KurikulumKelas $kurikulum): void
    {
        $instansi = Auth::user()->getInstansi();
        $kelas = $kurikulum->kelas;
        abort_if($kelas->instansi_id !== $instansi->id_instansi, 403);
    }
}
