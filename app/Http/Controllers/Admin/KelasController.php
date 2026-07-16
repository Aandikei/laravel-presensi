<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\RegistrasiAkademik;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class KelasController extends Controller
{
    public function index(Request $request)
    {
        $instansi = Auth::user()->getInstansi();

        $daftarTahun = TahunAjaran::where('instansi_id', $instansi->id_instansi)
            ->orderBy('nama_tahun', 'desc')
            ->orderByRaw("FIELD(semester, 'Ganjil', 'Genap')")
            ->get();

        $tahunDipilih = $request->tahun_id
            ? TahunAjaran::where('instansi_id', $instansi->id_instansi)->find($request->tahun_id)
            : TahunAjaran::getAktif($instansi->id_instansi);

        $tingkatList = Kelas::where('instansi_id', $instansi->id_instansi)
            ->select('tingkat')
            ->distinct()
            ->orderBy('tingkat')
            ->pluck('tingkat');

        $jurusanList = collect();
        if ($instansi->jenjang === 'SMA') {
            $jurusanList = Jurusan::where('instansi_id', $instansi->id_instansi)
                ->orderBy('kode_jurusan')
                ->get(['id_jurusan', 'kode_jurusan', 'nama_jurusan']);
        }

        if ($request->ajax()) {
            $kelas = Kelas::with(['waliKelas', 'jurusan'])
                ->where('instansi_id', $instansi->id_instansi)
                ->select('kelas.*')
                ->withCount(['registrasiAkademik as jumlah_siswa' => fn ($q) => $q
                    ->aktif()
                    ->where('tahun_id', $tahunDipilih?->id_tahun)
                    ->whereHas('siswa', fn ($q) => $q->whereNull('status'))
                ]);

            if ($request->tingkat) {
                $kelas->where('tingkat', $request->tingkat);
            }

            if ($request->jurusan) {
                $kelas->where('jurusan_id', $request->jurusan);
            }

            return DataTables::of($kelas)
                ->addIndexColumn()
                ->addColumn('wali_kelas', fn ($row) => $row->waliKelas?->nama_guru ?? '<span class="text-gray-400">Belum ditentukan</span>')
                ->addColumn('jumlah_siswa', fn ($row) => $row->jumlah_siswa)
                ->addColumn('tahun_ajaran', fn () => $tahunDipilih
                    ? $tahunDipilih->nama_tahun.' - '.$tahunDipilih->semester
                    : '<span class="text-gray-400 text-xs">Pilih tahun ajaran</span>')
                ->addColumn('aksi', function ($row) use ($tahunDipilih) {
                    $html = '<a href="'.route('admin.kelas.detail', [
                        'kelas' => $row->id_kelas,
                        'tahun_id' => $tahunDipilih?->id_tahun,
                    ]).'" title="Detail" class="text-purple-600 hover:text-purple-800">
                        <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                    </a>';

                    if (Auth::user()->can('manage-kelas')) {
                        $html .= ' <a href="'.route('admin.kelas.edit', $row->id_kelas).'" title="Edit" class="text-blue-600 hover:text-blue-800">
                            <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                        </a>';
                        $html .= ' <form method="POST" action="'.route('admin.kelas.destroy', $row->id_kelas).'" class="inline">
                            <input type="hidden" name="_token" value="'.csrf_token().'">
                            <input type="hidden" name="_method" value="DELETE">
                            <button type="button" title="Hapus" class="text-red-600 hover:text-red-800" onclick="confirmAction(this.closest(\'form\'), \'Yakin hapus kelas ini?\')">
                                <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                            </form>';
                    }

                    return $html;
                })
                ->rawColumns(['wali_kelas', 'tahun_ajaran', 'aksi'])
                ->make(true);
        }

        return view('admin.kelas.index', compact('daftarTahun', 'tahunDipilih', 'tingkatList', 'jurusanList'));
    }

    public function create()
    {
        $instansi = Auth::user()->getInstansi();

        $daftarTingkat = range($instansi->tingkat_min, $instansi->tingkat_maks);

        $jurusanList = collect();
        if ($instansi->jenjang === 'SMA') {
            $jurusanList = Jurusan::where('instansi_id', $instansi->id_instansi)
                ->orderBy('kode_jurusan')
                ->get(['id_jurusan', 'kode_jurusan', 'nama_jurusan']);
        }

        $guru = Guru::where('instansi_id', $instansi->id_instansi)
            ->whereNull('status')
            ->whereDoesntHave('kelasWali')
            ->whereDoesntHave('user', fn ($q) => $q->whereHas('roles', fn ($r) => $r->whereIn('name', ['kepala_sekolah', 'wakil_kepala_sekolah'])))
            ->orderBy('nama_guru')
            ->get();

        return view('admin.kelas.create', compact('guru', 'instansi', 'daftarTingkat', 'jurusanList'));
    }

    public function store(Request $request)
    {
        $instansi = Auth::user()->getInstansi();

        $rules = [
            'tingkat' => 'required|integer|min:' . $instansi->tingkat_min . '|max:' . $instansi->tingkat_maks,
            'guru_wali_id' => 'nullable|exists:guru,id_guru',
            'nomor_kelas' => 'nullable|string|max:10',
        ];

        if ($instansi->jenjang === 'SMA') {
            $rules['jurusan_id'] = 'required|exists:jurusan,id_jurusan';
        } else {
            $rules['jurusan_id'] = 'nullable|exists:jurusan,id_jurusan';
        }

        $validated = $request->validate($rules);

        if (!empty($validated['nomor_kelas'])) {
            $validated['nomor_kelas'] = strtoupper($validated['nomor_kelas']);
        }

        $validated['instansi_id'] = $instansi->id_instansi;

        $jurusanId = $validated['jurusan_id'] ?? null;
        $isNumeric = $instansi->jenjang === 'SMA';

        $existingQuery = Kelas::where('instansi_id', $instansi->id_instansi)
            ->where('tingkat', $validated['tingkat'])
            ->when(!empty($jurusanId), fn ($q) => $q->where('jurusan_id', $jurusanId))
            ->when(empty($jurusanId), fn ($q) => $q->whereNull('jurusan_id'));

        $total = $existingQuery->count();
        $note = null;

        if ($total === 0) {
            if (!empty($validated['nomor_kelas'])) {
                $first = $isNumeric ? '1' : 'A';
                if ($validated['nomor_kelas'] !== $first) {
                    return back()->with('error', "Nomor kelas pertama harus $first atau dikosongkan.")->withInput();
                }
            }
        } else {
            $expected = $this->expectedNextNomor($existingQuery, $isNumeric);
            $hasNull = $existingQuery->clone()->whereNull('nomor_kelas')->exists();

            if ($hasNull) {
                if (empty($validated['nomor_kelas'])) {
                    return back()->with('error', "Silakan isi nomor kelas. Selanjutnya: $expected.")->withInput();
                }

                if ($validated['nomor_kelas'] !== $expected) {
                    return back()->with('error', "Nomor kelas tidak valid. Selanjutnya harus $expected.")->withInput();
                }

                $nullClass = $existingQuery->clone()->whereNull('nomor_kelas')->first();
                $nullClass->update(['nomor_kelas' => $validated['nomor_kelas']]);

                $validated['nomor_kelas'] = $isNumeric
                    ? (string)((int)$validated['nomor_kelas'] + 1)
                    : chr(ord($validated['nomor_kelas']) + 1);

                $note = "Kelas sebelumnya otomatis diisi {$nullClass->fresh()->nomor_kelas}, kelas ini menjadi {$validated['nomor_kelas']}.";
            } else {
                if (empty($validated['nomor_kelas'])) {
                    return back()->with('error', "Tambahkan nomor kelas. Selanjutnya: $expected.")->withInput();
                }

                if ($validated['nomor_kelas'] !== $expected) {
                    return back()->with('error', "Nomor kelas tidak valid. Selanjutnya harus $expected.")->withInput();
                }
            }
        }

        $exists = $existingQuery->clone()
            ->when(
                !empty($validated['nomor_kelas']),
                fn ($q) => $q->where('nomor_kelas', $validated['nomor_kelas']),
                fn ($q) => $q->whereNull('nomor_kelas')
            )
            ->exists();

        if ($exists) {
            return back()->with('error', 'Kelas dengan kombinasi tingkat, jurusan, dan nomor kelas tersebut sudah ada!')
                ->withInput();
        }

        Kelas::create($validated);

        return redirect()->route('admin.kelas.index')
            ->with('success', $note ?: 'Kelas berhasil ditambahkan!');
    }

    public function edit(Kelas $kelas)
    {
        $this->authorizeInstansi($kelas);

        $instansi = Auth::user()->getInstansi();

        $kelas->load('jurusan');

        $daftarTingkat = range($instansi->tingkat_min, $instansi->tingkat_maks);

        $jurusanList = collect();
        if ($instansi->jenjang === 'SMA') {
            $jurusanList = Jurusan::where('instansi_id', $instansi->id_instansi)
                ->orderBy('kode_jurusan')
                ->get(['id_jurusan', 'kode_jurusan', 'nama_jurusan']);
        }

        $guru = Guru::where('instansi_id', $instansi->id_instansi)
            ->whereNull('status')
            ->where(function ($q) use ($kelas) {
                $q->where(function ($q2) {
                    $q2->whereDoesntHave('kelasWali')
                      ->whereDoesntHave('user', fn ($q3) => $q3->whereHas('roles', fn ($r) => $r->whereIn('name', ['kepala_sekolah', 'wakil_kepala_sekolah'])));
                })->orWhere('id_guru', $kelas->guru_wali_id);
            })
            ->orderBy('nama_guru')
            ->get();

        return view('admin.kelas.edit', compact('kelas', 'guru', 'instansi', 'daftarTingkat', 'jurusanList'));
    }

    public function update(Request $request, Kelas $kelas)
    {
        $this->authorizeInstansi($kelas);

        $instansi = Auth::user()->getInstansi();

        $rules = [
            'tingkat' => 'required|integer|min:' . $instansi->tingkat_min . '|max:' . $instansi->tingkat_maks,
            'guru_wali_id' => 'nullable|exists:guru,id_guru',
            'nomor_kelas' => 'nullable|string|max:10',
        ];

        if ($instansi->jenjang === 'SMA') {
            $rules['jurusan_id'] = 'required|exists:jurusan,id_jurusan';
        } else {
            $rules['jurusan_id'] = 'nullable|exists:jurusan,id_jurusan';
        }

        $validated = $request->validate($rules);

        if (isset($validated['nomor_kelas'])) {
            $validated['nomor_kelas'] = strtoupper($validated['nomor_kelas']);
        }

        $exists = Kelas::where('instansi_id', $kelas->instansi_id)
            ->where('tingkat', $validated['tingkat'])
            ->where('id_kelas', '!=', $kelas->id_kelas)
            ->when(
                !empty($validated['jurusan_id']),
                fn ($q) => $q->where('jurusan_id', $validated['jurusan_id']),
                fn ($q) => $q->whereNull('jurusan_id')
            )
            ->when(
                !empty($validated['nomor_kelas']),
                fn ($q) => $q->where('nomor_kelas', $validated['nomor_kelas']),
                fn ($q) => $q->whereNull('nomor_kelas')
            )
            ->exists();

        if ($exists) {
            return back()->with('error', 'Kelas dengan kombinasi tingkat, jurusan, dan nomor kelas tersebut sudah ada!')
                ->withInput();
        }

        $kelas->update($validated);

        return redirect()->route('admin.kelas.index')
            ->with('success', 'Kelas berhasil diupdate!');
    }

    public function destroy(Kelas $kelas)
    {
        $this->authorizeInstansi($kelas);

        if ($kelas->registrasiAkademik()->exists()) {
            return back()->with('error', 'Kelas tidak bisa dihapus karena masih ada siswa terdaftar!');
        }

        $kelas->delete();

        return redirect()->route('admin.kelas.index')
            ->with('success', 'Kelas berhasil dihapus!');
    }

    private function authorizeInstansi(Kelas $kelas): void
    {
        $instansi = Auth::user()->getInstansi();
        abort_if($kelas->instansi_id !== $instansi->id_instansi, 403);
    }

    public function nextNomor(Request $request)
    {
        $instansi = Auth::user()->getInstansi();

        $tingkat = $request->integer('tingkat');
        $jurusanId = $request->integer('jurusan_id');

        if (!$tingkat) {
            return response()->json(['next_nomor' => null]);
        }

        $query = Kelas::where('instansi_id', $instansi->id_instansi)
            ->where('tingkat', $tingkat)
            ->when($jurusanId, fn ($q) => $q->where('jurusan_id', $jurusanId))
            ->when(!$jurusanId, fn ($q) => $q->whereNull('jurusan_id'));

        $total = $query->count();

        if ($total === 0) {
            return response()->json(['next_nomor' => null]);
        }

        $next = $this->expectedNextNomor($query, $instansi->jenjang === 'SMA');

        return response()->json(['next_nomor' => $next]);
    }

    private function expectedNextNomor($query, bool $isNumeric): ?string
    {
        $max = $query->clone()->whereNotNull('nomor_kelas')->max('nomor_kelas');

        if ($max === null) {
            return $isNumeric ? '1' : 'A';
        }

        return $isNumeric
            ? (string)((int)$max + 1)
            : chr(ord($max) + 1);
    }

    public function detail(Request $request, Kelas $kelas)
    {
        $this->authorizeInstansi($kelas);

        $instansi = Auth::user()->getInstansi();

        $daftarTahun = TahunAjaran::where('instansi_id', $instansi->id_instansi)
            ->orderBy('nama_tahun', 'desc')
            ->orderByRaw("FIELD(semester, 'Ganjil', 'Genap')")
            ->get();

        $tahunDipilih = $request->tahun_id
            ? TahunAjaran::where('instansi_id', $instansi->id_instansi)->find($request->tahun_id)
            : TahunAjaran::getAktif($instansi->id_instansi);

        $kelas->load([
            'waliKelas',
            'kurikulum.mataPelajaran',
            'kurikulum.guru',
            'kurikulum.jadwal',
        ]);

        $registrasi = RegistrasiAkademik::where('kelas_id', $kelas->id_kelas)
            ->when($tahunDipilih, fn ($q) => $q->where('tahun_id', $tahunDipilih->id_tahun))
            ->aktif()
            ->with('siswa')
            ->whereHas('siswa', fn ($q) => $q->whereNull('status'))
            ->get();

        return view('admin.kelas.detail', compact('kelas', 'registrasi', 'tahunDipilih', 'daftarTahun'));
    }
}
