<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\SiswaImport;
use App\Models\Kelas;
use App\Models\LogPoinSiswa;
use App\Models\OrangTua;
use App\Models\OrtuSiswa;
use App\Models\RegistrasiAkademik;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class SiswaController extends Controller
{
    public function index(Request $request)
    {
        $instansi = Auth::user()->getInstansi();

        if ($request->ajax()) {
            $siswa = Siswa::with([
                'user',
                'registrasiAktif.kelas',
                'registrasiAkademik' => fn ($q) => $q
                    ->with('kelas')
                    ->whereHas('kelas', fn ($qq) => $qq->where('instansi_id', $instansi->id_instansi)),
            ])
                ->where('instansi_id', '=', $instansi->id_instansi)
                ->select('siswa.*')
                ->addSelect(['total_poin' => LogPoinSiswa::selectRaw('COALESCE(SUM(master_poin.jumlah_poin), 0)')
                    ->join('master_poin', 'log_poin_siswa.poin_id', '=', 'master_poin.id_poin')
                    ->whereColumn('log_poin_siswa.siswa_id', 'siswa.id_siswa')
                ]);

            // Filter per kelas
            if ($request->kelas_id) {
                $siswa->whereHas('registrasiAktif', fn ($q) => $q->where('kelas_id', '=', $request->kelas_id));
            }

            // Filter status
            if ($request->status === 'Keluar') {
                $siswa->where('status', 'Keluar');
            } elseif ($request->status === 'Aktif') {
                $siswa->whereNull('status')->has('registrasiAktif');
            } elseif ($request->status === 'Pindah') {
                $siswa->whereHas('registrasiAkademik', fn ($q) => $q
                    ->where('status', 'Pindah')
                    ->whereHas('kelas', fn ($qq) => $qq->where('instansi_id', $instansi->id_instansi)));
            } elseif ($request->status === 'Alumni') {
                $siswa->whereDoesntHave('registrasiAktif')
                    ->whereHas('registrasiAkademik', fn ($q) => $q
                        ->whereHas('kelas', fn ($qq) => $qq->where('instansi_id', $instansi->id_instansi)))
                    ->whereDoesntHave('registrasiAkademik', fn ($q) => $q
                        ->whereIn('status', ['Pindah', 'Keluar'])
                        ->whereHas('kelas', fn ($qq) => $qq->where('instansi_id', $instansi->id_instansi)));
            } elseif ($request->status === 'belum_terdaftar') {
                $siswa->whereDoesntHave('registrasiAkademik', fn ($q) => $q
                    ->whereHas('kelas', fn ($qq) => $qq->where('instansi_id', $instansi->id_instansi)));
            }

            return DataTables::of($siswa)
                ->addIndexColumn()
                ->addColumn('email', fn ($row) => $row->user->email ?? '-')
                ->addColumn('kelas', function ($row) {
                    if ($row->registrasiAktif?->kelas) {
                        return $row->registrasiAktif->kelas->nama_kelas;
                    }

                    $regPindah = $row->registrasiAkademik->firstWhere('status', 'Pindah');
                    if ($regPindah && $regPindah->kelas) {
                        return $regPindah->kelas->nama_kelas . ' <span class="text-xs text-yellow-600 dark:text-yellow-400">(Pindah)</span>';
                    }

                    $regKeluar = $row->registrasiAkademik->firstWhere('status', 'Keluar');
                    if ($regKeluar && $regKeluar->kelas) {
                        return $regKeluar->kelas->nama_kelas . ' <span class="text-xs text-red-600 dark:text-red-400">(Keluar)</span>';
                    }

                    if ($row->registrasiAkademik->isNotEmpty()) {
                        return '<span class="font-medium text-green-600 dark:text-green-400">Alumni</span>';
                    }

                    return '<span class="text-gray-500">Belum terdaftar</span>';
                })
                // ->addColumn('total_poin', fn($row) => $row->logPoin()->sum('jumlah_poin') ?? 0)
                ->addColumn('status_badge', function ($row) {
                    if (!$row->isAktif()) {
                        return '<span class="px-2 py-1 text-xs font-semibold text-red-700 bg-red-100 rounded-full dark:bg-red-900/30 dark:text-red-400">'.$row->status_label.'</span>';
                    }
                    if ($row->registrasiAktif) {
                        return '<span class="px-2 py-1 text-xs font-semibold text-green-700 bg-green-100 rounded-full dark:bg-green-900/30 dark:text-green-400">Aktif</span>';
                    }
                    if ($row->registrasiAkademik->firstWhere('status', 'Pindah')) {
                        return '<span class="px-2 py-1 text-xs font-semibold text-yellow-700 bg-yellow-100 rounded-full dark:bg-yellow-900/30 dark:text-yellow-400">Pindah</span>';
                    }
                    return '<span class="px-2 py-1 text-xs font-semibold text-gray-600 bg-gray-100 rounded-full dark:bg-gray-700 dark:text-gray-400">-</span>';
                })
                ->addColumn('total_poin', fn($row) => (int) $row->total_poin)
                ->addColumn('aksi', function ($row) {
                    $canManage = Auth::user()->can('manage-siswa');

                    if ($canManage) {
                        $edit = '<a href="'.route('admin.siswa.edit', $row->id_siswa).'" title="Edit" class="text-blue-600 hover:text-blue-800">
                            <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                        </a>';
                        $pindah = $row->registrasiAktif
                            ? '<a href="'.route('admin.siswa.pindah.form', $row->id_siswa).'" title="Pindahkan" class="text-yellow-600 hover:text-yellow-800">
                                <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                                </svg>
                            </a>'
                            : '';
                        $tandaiKeluar = $row->isAktif()
                            ? '<form method="POST" action="'.route('admin.siswa.tandai-keluar', $row->id_siswa).'" class="inline">
                                <input type="hidden" name="_token" value="'.csrf_token().'">
                                <button type="button" title="Tandai Keluar" class="text-red-600 hover:text-red-800" onclick="confirmAction(this.closest(\'form\'), \'Yakin menandai siswa ini sebagai Keluar?\', \'Ya, Keluarkan\')">
                                    <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                    </svg>
                                </button>
                            </form>'
                            : '';
                        $batalkan = !$row->isAktif()
                            ? '<form method="POST" action="'.route('admin.siswa.batalkan-status', $row->id_siswa).'" class="inline">
                                <input type="hidden" name="_token" value="'.csrf_token().'">
                                <button type="button" title="Aktifkan Kembali" class="text-green-600 hover:text-green-800" onclick="confirmAction(this.closest(\'form\'), \'Aktifkan kembali siswa ini?\', \'Ya, Aktifkan\')">
                                    <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                    </svg>
                                </button>
                            </form>'
                            : '';
                        $delete = '<form method="POST" action="'.route('admin.siswa.destroy', $row->id_siswa).'" class="inline">
                            <input type="hidden" name="_token" value="'.csrf_token().'">
                            <input type="hidden" name="_method" value="DELETE">
                            <button type="button" title="Hapus" class="text-red-600 hover:text-red-800" onclick="confirmAction(this.closest(\'form\'), \'Yakin hapus siswa ini?\')">
                                <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                            </form>';
                        return $edit.' '.$pindah.' '.$tandaiKeluar.' '.$batalkan.' '.$delete;
                    }

                    return '';
                })
                ->rawColumns(['kelas', 'status_badge', 'aksi'])
                ->filterColumn('nama_siswa', function ($query, $keyword) {
                    $query->where('nama_siswa', 'like', "%{$keyword}%");
                })
                ->make(true);
        }
        // Tambah data kelas untuk filter
        $tahunAktif = TahunAjaran::getAktif($instansi->id_instansi);

        $kelas = Kelas::where('instansi_id', $instansi->id_instansi)
            ->orderBy('tingkat')
            ->orderBy('nama_kelas')
            ->get();

        $tahunAjaran = TahunAjaran::where('instansi_id', $instansi->id_instansi)
            ->orderByDesc('is_aktif')
            ->get();

        return view('admin.siswa.index', compact('kelas', 'tahunAjaran'));
    }

    public function cekEmailOrtu(Request $request)
    {
        $exists = User::where('email', $request->email)->exists();
        $namaOrtu = null;

        if ($exists) {
            $user = User::where('email', $request->email)->first();
            $ortu = OrangTua::where('user_id', $user->id)->first();
            $namaOrtu = $ortu?->nama_ortu;
        }

        return response()->json([
            'exists' => $exists,
            'nama_ortu' => $namaOrtu,
        ]);
    }

    public function cekNisn(Request $request)
    {
        $existing = Siswa::with('instansi')->where('nisn', $request->nisn)->first();

        if (!$existing) {
            return response()->json(['found' => false]);
        }

        $sameInstansi = $existing->instansi_id === Auth::user()->getInstansi()->id_instansi;

        $reg = $existing->registrasiAkademik();
        $hasActive = (clone $reg)->aktif()->exists();
        $hasPindah = (clone $reg)->where('status', 'Pindah')->exists();
        $hasAlumni = (clone $reg)->alumni()->exists();

        $instansi = Auth::user()->getInstansi();

        // ── Same school ──
        // Prioritas: Keluar > Aktif > Pindah
        // Keluar di-depan karena siswa bisa punya Pindah lama + Keluar sekarang
        if ($sameInstansi && !$existing->isAktif()) {
            return response()->json([
                'found' => true,
                'same_instansi' => true,
                'status' => 'Keluar',
                'siswa_id' => $existing->id_siswa,
                'nama' => $existing->nama_siswa,
            ]);
        }

        if ($sameInstansi && $hasActive) {
            return response()->json([
                'found' => true,
                'same_instansi' => true,
                'nama' => $existing->nama_siswa,
            ]);
        }

        // Pindah di-scope ke kelas instansi ini biar ga nyenggol status lain
        $hasPindahHere = $hasPindah && (clone $reg)->where('status', 'Pindah')
            ->whereHas('kelas', fn ($q) => $q->where('instansi_id', $instansi->id_instansi))
            ->exists();

        if ($sameInstansi && $hasPindahHere) {
            return response()->json([
                'found' => true,
                'same_instansi' => true,
                'status' => 'Pindah',
                'nama' => $existing->nama_siswa,
            ]);
        }

        // ── Cross-school ──
        $currentTk = $instansi->tingkat_min;
        $studentTk = $existing->instansi->tingkat_min;

        $isKeluar = !$existing->isAktif();

        if ($isKeluar) {
            // Keluar: blokir hanya turun jenjang
            if ($currentTk < $studentTk) {
                return $this->blockedResponse($existing, 'Tidak bisa mendaftarkan ulang siswa dari jenjang lebih tinggi ke jenjang yang lebih rendah.');
            }
            return $this->daftarUlangResponse($existing, 'Keluar');
        }

        // Pindah / Aktif dari luar: hanya boleh sama jenjang
        // Dicek sebelum Alumni karena siswa bisa punya Alumni aktif + Pindah/Aktif di sekolah lain
        if ($hasPindah || $hasActive) {
            if ($currentTk !== $studentTk) {
                return $this->blockedResponse($existing, 'Pindahan siswa hanya bisa diterima untuk jenjang yang sama.');
            }
            return response()->json([
                'found' => true,
                'same_instansi' => false,
                'action' => 'pindah',
                'nama' => $existing->nama_siswa,
                'instansi' => $existing->instansi->nama_instansi,
            ]);
        }

        // Alumni: hanya boleh naik jenjang
        if ($hasAlumni) {
            if ($currentTk <= $studentTk) {
                return $this->blockedResponse($existing, 'Tidak bisa mendaftarkan ulang alumni ke jenjang yang sama atau lebih rendah.');
            }
            return $this->daftarUlangResponse($existing, 'Alumni');
        }

        // Fallback: ada catatan tapi ga jelas statusnya → blok
        return $this->blockedResponse($existing, 'Status siswa tidak dikenali.');
    }

    public function create()
    {
        $instansi = Auth::user()->getInstansi();
        $tahunAktif = TahunAjaran::getAktif($instansi->id_instansi);
        $kelas = Kelas::where('instansi_id', $instansi->id_instansi)
            ->orderBy('tingkat')
            ->orderBy('nama_kelas')
            ->get();

        return view('admin.siswa.create', compact('tahunAktif', 'kelas'));
    }

    public function store(Request $request)
    {
        $instansi = Auth::user()->getInstansi();

        // Cek apakah NISN sudah terdaftar di sekolah lain
        if ($request->nisn) {
            $existing = Siswa::where('nisn', $request->nisn)->with('instansi')->first();
            if ($existing && $existing->instansi_id !== $instansi->id_instansi) {
                $currentTk = $instansi->tingkat_min;
                $studentTk = $existing->instansi->tingkat_min;

                // Jika siswa sudah ditandai Keluar, langsung ke daftar ulang
                if (!$existing->isAktif()) {
                    if ($currentTk < $studentTk) {
                        return redirect()->back()->with('error', 'Tidak bisa mendaftarkan ulang siswa dari jenjang lebih tinggi ke jenjang yang lebih rendah.')
                            ->withInput();
                    }
                    return redirect()->route('admin.siswa.daftar-ulang', $existing->id_siswa)
                        ->with('info', "Siswa dengan NISN {$request->nisn} ({$existing->nama_siswa}) sudah tidak aktif di {$existing->instansi->nama_instansi}. Silakan daftarkan ulang.");
                }

                $hasActive = $existing->registrasiAkademik()
                    ->aktif()
                    ->whereHas('tahunAjaran', fn ($q) => $q->where('is_aktif', true))
                    ->whereHas('kelas', fn ($q) => $q->where('instansi_id', $existing->instansi_id))
                    ->exists();

                $hasPindah = $existing->registrasiAkademik()
                    ->where('status', 'Pindah')
                    ->exists();

                $hasAlumni = $existing->registrasiAkademik()->alumni()->exists();

                if ($hasActive || $hasPindah) {
                    // Pindah/Aktif dari luar: hanya boleh sama jenjang
                    if ($currentTk !== $studentTk) {
                        return redirect()->back()->with('error', 'Pindahan siswa hanya bisa diterima untuk jenjang yang sama.')
                            ->withInput();
                    }
                    return redirect()->route('admin.siswa.pindah.form-masuk', ['nisn' => $request->nisn])
                        ->with('info', "Siswa dengan NISN {$request->nisn} ({$existing->nama_siswa}) sudah terdaftar di {$existing->instansi->nama_instansi}. Gunakan form Pindah Masuk di bawah untuk memindahkan siswa ini ke sekolah Anda.");
                }

                if ($hasAlumni) {
                    // Alumni: hanya boleh naik jenjang
                    if ($currentTk <= $studentTk) {
                        return redirect()->back()->with('error', 'Tidak bisa mendaftarkan ulang alumni ke jenjang yang sama atau lebih rendah.')
                            ->withInput();
                    }
                    return redirect()->route('admin.siswa.daftar-ulang', $existing->id_siswa)
                        ->with('info', "Siswa dengan NISN {$request->nisn} ({$existing->nama_siswa}) sudah lulus dari {$existing->instansi->nama_instansi}. Silakan lengkapi data untuk mendaftarkan ulang.");
                }

                // Tidak punya registrasi alias → pindah form (fallback)
                return redirect()->route('admin.siswa.pindah.form-masuk', ['nisn' => $request->nisn])
                    ->with('info', "Siswa dengan NISN {$request->nisn} ({$existing->nama_siswa}) terdaftar di {$existing->instansi->nama_instansi} namun belum memiliki data kelas. Minta sekolah asal untuk mendaftarkan ke kelas dan membuat kode transfer.");
            }
        }

        $validated = $request->validate([
            // Data siswa
            'nama_siswa' => 'required|string|max:255',
            'nisn' => 'required|string|unique:siswa,nisn',
            'jenis_kelamin' => 'required|in:L,P',
            'tanggal_lahir' => 'nullable|date',
            // Akun siswa
            'email' => 'required|email|unique:users,email',
            // Data orang tua
            'nama_ortu' => 'nullable|string|max:255',
            'no_hp_ortu' => 'nullable|string|max:15',
            'hubungan' => 'required|in:Ayah,Ibu,Wali',
            'email_ortu' => 'required|email',
            // Registrasi opsional
            'kelas_id' => 'nullable|exists:kelas,id_kelas',
            'tahun_id' => 'nullable|exists:tahun_ajaran,id_tahun',
        ]);

        $ortuAlreadyExisted = false;
        $namaOrtu = null;

        DB::transaction(function () use ($validated, $instansi, &$ortuAlreadyExisted, &$namaOrtu) {
            // Buat user siswa
            $userSiswa = User::create([
                'name' => $validated['nama_siswa'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['nisn']),
            ]);
            $userSiswa->assignRole('siswa');

            // Buat data siswa
            $siswa = Siswa::create([
                'user_id' => $userSiswa->id,
                'instansi_id' => $instansi->id_instansi,
                'nisn' => $validated['nisn'],
                'nama_siswa' => $validated['nama_siswa'],
                'jenis_kelamin' => $validated['jenis_kelamin'],
                'tanggal_lahir' => $validated['tanggal_lahir'] ?? null,
            ]);

            // Buat user orang tua — cek dulu apakah email sudah ada
            $userOrtu = User::where('email', '=', $validated['email_ortu'])->first();

            if ($userOrtu) {
                $ortu = OrangTua::where('user_id', '=', $userOrtu->id)->first();

                if ($ortu) {
                    $ortuAlreadyExisted = true;
                    $namaOrtu = $ortu->nama_ortu;
                } else {
                    $ortu = OrangTua::create([
                        'user_id' => $userOrtu->id,
                        'nama_ortu' => $validated['nama_ortu'],
                        'no_hp' => $validated['no_hp_ortu'] ?? null,
                    ]);
                    if (! $userOrtu->hasRole('orang_tua')) {
                        $userOrtu->assignRole('orang_tua');
                    }
                }
            } else {
                $userOrtu = User::create([
                    'name' => $validated['nama_ortu'],
                    'email' => $validated['email_ortu'],
                    'password' => Hash::make($validated['nisn']),
                ]);
                $userOrtu->assignRole('orang_tua');

                $ortu = OrangTua::create([
                    'user_id' => $userOrtu->id,
                    'nama_ortu' => $validated['nama_ortu'],
                    'no_hp' => $validated['no_hp_ortu'] ?? null,
                ]);
            }

            // Link ortu ke siswa
            OrtuSiswa::firstOrCreate(
                [
                    'ortu_id' => $ortu->id_ortu,
                    'siswa_id' => $siswa->id_siswa,
                ],
                [
                    'hubungan' => $validated['hubungan'],
                    'is_primary' => true,
                ]
            );

            if (! empty($validated['kelas_id']) && ! empty($validated['tahun_id'])) {
                $sudahTerdaftar = RegistrasiAkademik::where('siswa_id', '=', $siswa->id_siswa)
                    ->where('tahun_id', '=', $validated['tahun_id'])
                    ->exists();

                if (! $sudahTerdaftar) {
                    RegistrasiAkademik::create([
                        'siswa_id' => $siswa->id_siswa,
                        'kelas_id' => $validated['kelas_id'],
                        'tahun_id' => $validated['tahun_id'],
                        'status' => 'Aktif',
                    ]);
                }
            }
        });

        if ($ortuAlreadyExisted) {
            return redirect()->route('admin.siswa.index')
                ->with('success', "Siswa berhasil ditambahkan! {$namaOrtu} sudah terdaftar, data orang tua tidak berubah.");
        }

        return redirect()->route('admin.siswa.index')
            ->with('success', 'Siswa berhasil ditambahkan!');
    }

    public function tandaiKeluar(Siswa $siswa)
    {
        $this->authorizeInstansi($siswa);

        if (!$siswa->isAktif()) {
            return back()->with('error', 'Siswa sudah tidak aktif.');
        }

        DB::transaction(function () use ($siswa) {
            $siswa->registrasiAktif()?->update(['status' => 'Keluar', 'tanggal_mutasi' => now()]);
            $this->nonaktifkanSiswa($siswa);
            $siswa->markAsKeluar();
        });

        return redirect()->route('admin.siswa.index')
            ->with('success', "Siswa {$siswa->nama_siswa} ditandai sebagai Keluar.");
    }

    public function batalkanStatus(Siswa $siswa)
    {
        $this->authorizeInstansi($siswa);

        if ($siswa->isAktif()) {
            return back()->with('error', 'Siswa ini masih aktif.');
        }

        DB::transaction(function () use ($siswa) {
            $siswa->update(['status' => null]);
            $siswa->registrasiAkademik()
                ->where('status', 'Keluar')
                ->update(['status' => 'Aktif', 'tanggal_mutasi' => null]);
            $siswa->user->assignRole('siswa');
        });

        return redirect()->route('admin.siswa.index')
            ->with('success', "Status {$siswa->nama_siswa} dikembalikan ke Aktif.");
    }

    public function edit(Siswa $siswa)
    {
        $instansi = Auth::user()->getInstansi();
        $this->authorizeInstansi($siswa);
        $siswa->load([
            'user',
            'orangTua.user',
            'registrasiAkademik' => fn ($q) => $q->whereHas('kelas', fn ($qq) => $qq->where('instansi_id', $instansi->id_instansi)),
        ]);

        return view('admin.siswa.edit', compact('siswa'));
    }

    public function update(Request $request, Siswa $siswa)
    {
        $this->authorizeInstansi($siswa);

        $validated = $request->validate([
            'nama_siswa' => 'required|string|max:255',
            'nisn' => 'required|string|unique:siswa,nisn,'.$siswa->id_siswa.',id_siswa',
            'jenis_kelamin' => 'required|in:L,P',
            'tanggal_lahir' => 'nullable|date',
            'email' => 'required|email|unique:users,email,'.$siswa->user_id,
            'password' => 'nullable|min:8',
        ]);

        DB::transaction(function () use ($validated, $siswa) {
            $userData = [
                'name' => $validated['nama_siswa'],
                'email' => $validated['email'],
            ];
            if (! empty($validated['password'])) {
                $userData['password'] = Hash::make($validated['password']);
            }
            $siswa->user->update($userData);

            $siswa->update([
                'nama_siswa' => $validated['nama_siswa'],
                'nisn' => $validated['nisn'],
                'jenis_kelamin' => $validated['jenis_kelamin'],
                'tanggal_lahir' => $validated['tanggal_lahir'] ?? null,
            ]);
        });

        return redirect()->route('admin.siswa.index')
            ->with('success', 'Data siswa berhasil diupdate!');
    }

    public function destroy(Siswa $siswa)
    {
        $this->authorizeInstansi($siswa);

        DB::transaction(function () use ($siswa) {
            $orangTuaList = $siswa->orangTua()->withCount(['siswa as other_children_count' => fn ($q) => $q->where('siswa.id_siswa', '!=', $siswa->id_siswa),
            ])->get();

            optional($siswa->user)->delete();
            $siswa->forceDelete();

            foreach ($orangTuaList as $ortu) {
                if ($ortu->other_children_count === 0) {
                    optional($ortu->user)->delete();
                    $ortu->delete();
                }
            }
        });

        return redirect()->route('admin.siswa.index')
            ->with('success', 'Siswa berhasil dihapus permanen!');
    }

    public function formDaftarUlang(Siswa $siswa)
    {
        $instansi = Auth::user()->getInstansi();

        // Hanya blokir jika siswa masih punya registrasi AKTIF di sekolah ini
        $hasActive = $siswa->registrasiAkademik()
            ->aktif()
            ->whereHas('tahunAjaran', fn ($q) => $q->where('is_aktif', true))
            ->whereHas('kelas', fn ($q) => $q->where('instansi_id', $instansi->id_instansi))
            ->exists();
        abort_if($hasActive, 403, 'Siswa masih aktif dan harus menggunakan Pindah Masuk.');

        $currentTk = $instansi->tingkat_min;
        $studentTk = $siswa->instansi->tingkat_min;

        $isKeluar = !$siswa->isAktif();
        $hasActiveAny = $siswa->registrasiAkademik()->aktif()->exists();
        $hasPindahAny = $siswa->registrasiAkademik()->where('status', 'Pindah')->exists();
        $hasAlumni = $siswa->registrasiAkademik()->alumni()->exists();

        if ($isKeluar) {
            // Keluar: blokir hanya turun jenjang
            if ($currentTk < $studentTk) {
                return redirect()->back()->with('error', 'Tidak bisa mendaftarkan ulang siswa dari jenjang lebih tinggi ke jenjang yang lebih rendah.');
            }
        } elseif ($hasPindahAny || $hasActiveAny) {
            // Pindah/Aktif dari luar: hanya boleh sama jenjang
            if ($currentTk !== $studentTk) {
                return redirect()->back()->with('error', 'Pindahan siswa hanya bisa diterima untuk jenjang yang sama.');
            }
            return redirect()->route('admin.siswa.pindah.form-masuk', ['nisn' => $siswa->nisn])
                ->with('info', 'Siswa ini harus menggunakan Pindah Masuk.');
        } elseif ($hasAlumni) {
            // Alumni: hanya boleh naik jenjang
            if ($currentTk <= $studentTk) {
                return redirect()->back()->with('error', 'Tidak bisa mendaftarkan ulang alumni ke jenjang yang sama atau lebih rendah.');
            }
        } else {
            return redirect()->back()->with('error', 'Status siswa tidak dikenali.');
        }

        $siswa->load(['user', 'orangTua.user', 'instansi']);

        $tahunAktif = TahunAjaran::getAktif($instansi->id_instansi);
        $kelas = Kelas::where('instansi_id', $instansi->id_instansi)
            ->orderBy('tingkat')
            ->orderBy('nama_kelas')
            ->get();

        return view('admin.siswa.daftar-ulang', compact('siswa', 'tahunAktif', 'kelas'));
    }

    public function prosesDaftarUlang(Request $request)
    {
        $instansi = Auth::user()->getInstansi();

        $validated = $request->validate([
            'siswa_id' => 'required|exists:siswa,id_siswa',
            'email' => 'required|email',
            'kelas_id' => 'nullable|exists:kelas,id_kelas',
            'tahun_id' => 'nullable|exists:tahun_ajaran,id_tahun',
            'pilihan_ortu' => 'required|in:lama,baru',
            'nama_ortu' => 'nullable|string|max:255',
            'email_ortu' => 'nullable|email',
            'hubungan' => 'nullable|in:Ayah,Ibu,Wali',
            'no_hp_ortu' => 'nullable|string|max:15',
        ]);

        if ($validated['pilihan_ortu'] === 'baru') {
            $request->validate([
                'nama_ortu' => 'nullable|string|max:255',
                'email_ortu' => 'required|email',
                'hubungan' => 'required|in:Ayah,Ibu,Wali',
            ]);
        }

        $siswa = Siswa::with(['user', 'orangTua.user', 'instansi'])->findOrFail($validated['siswa_id']);

        abort_if($siswa->instansi_id === $instansi->id_instansi, 403);

        $currentTk = $instansi->tingkat_min;
        $studentTk = $siswa->instansi->tingkat_min;

        $isKeluar = !$siswa->isAktif();
        $hasActiveAny = $siswa->registrasiAkademik()->aktif()->exists();
        $hasPindahAny = $siswa->registrasiAkademik()->where('status', 'Pindah')->exists();
        $hasAlumni = $siswa->registrasiAkademik()->alumni()->exists();

        if ($isKeluar) {
            if ($currentTk < $studentTk) {
                abort(403, 'Tidak bisa mendaftarkan ulang siswa dari jenjang lebih tinggi ke jenjang yang lebih rendah.');
            }
        } elseif ($hasPindahAny || $hasActiveAny) {
            if ($currentTk !== $studentTk) {
                abort(403, 'Pindahan siswa hanya bisa diterima untuk jenjang yang sama.');
            }
            return redirect()->route('admin.siswa.pindah.form-masuk', ['nisn' => $siswa->nisn])
                ->with('info', 'Siswa ini harus menggunakan Pindah Masuk.');
        } elseif ($hasAlumni) {
            if ($currentTk <= $studentTk) {
                abort(403, 'Tidak bisa mendaftarkan ulang alumni ke jenjang yang sama atau lebih rendah.');
            }
        } else {
            abort(403, 'Status siswa tidak dikenali.');
        }

        $oldUser = $siswa->user;

        if ($validated['email'] !== $oldUser->email && User::where('email', $validated['email'])->exists()) {
            return back()->withInput()->withErrors(['email' => 'Email sudah digunakan oleh akun lain.']);
        }

        DB::transaction(function () use ($validated, $siswa, $instansi, $oldUser) {
            if ($validated['email'] === $oldUser->email) {
                $oldUser->update([
                    'name' => $siswa->nama_siswa,
                    'password' => Hash::make($siswa->nisn),
                ]);
                if (!$oldUser->hasRole('siswa')) {
                    $oldUser->assignRole('siswa');
                }
                $userSiswa = $oldUser;
            } else {
                $userSiswa = User::create([
                    'name' => $siswa->nama_siswa,
                    'email' => $validated['email'],
                    'password' => Hash::make($siswa->nisn),
                ]);
                $userSiswa->assignRole('siswa');

                // Hapus user lama yang jadi orphan (tidak link ke Siswa/Guru/OrangTua manapun)
                $oldUser->delete();
            }

            $siswa->update([
                'user_id' => $userSiswa->id,
                'instansi_id' => $instansi->id_instansi,
                'status' => null,
            ]);

            $siswa->logPoin()->delete();

            if ($validated['pilihan_ortu'] === 'baru') {
                // Hapus link orang tua lama
                $oldOrtuSiswa = OrtuSiswa::where('siswa_id', $siswa->id_siswa)->get();
                foreach ($oldOrtuSiswa as $link) {
                    $ortu = $link->orangTua;
                    $link->delete();

                    // Hapus orang tua jika sudah tidak punya anak lain
                    if ($ortu && $ortu->siswa()->count() === 0) {
                        $ortu->delete(); // Observer akan hapus User otomatis
                    }
                }

                $userOrtu = User::where('email', $validated['email_ortu'])->first();

                if (! $userOrtu) {
                    $userOrtu = User::create([
                        'name' => $validated['nama_ortu'],
                        'email' => $validated['email_ortu'],
                        'password' => Hash::make($siswa->nisn),
                    ]);
                    $userOrtu->assignRole('orang_tua');
                }

                $ortu = OrangTua::firstOrCreate(
                    ['user_id' => $userOrtu->id],
                    [
                        'nama_ortu' => $validated['nama_ortu'],
                        'no_hp' => $validated['no_hp_ortu'] ?? null,
                    ]
                );

                if (! $userOrtu->hasRole('orang_tua')) {
                    $userOrtu->assignRole('orang_tua');
                }

                OrtuSiswa::firstOrCreate(
                    ['ortu_id' => $ortu->id_ortu, 'siswa_id' => $siswa->id_siswa],
                    ['hubungan' => $validated['hubungan'], 'is_primary' => true]
                );
            }

            if (! empty($validated['kelas_id']) && ! empty($validated['tahun_id'])) {
                $sudahTerdaftar = RegistrasiAkademik::where('siswa_id', $siswa->id_siswa)
                    ->where('tahun_id', $validated['tahun_id'])
                    ->exists();

                if (! $sudahTerdaftar) {
                    RegistrasiAkademik::create([
                        'siswa_id' => $siswa->id_siswa,
                        'kelas_id' => $validated['kelas_id'],
                        'tahun_id' => $validated['tahun_id'],
                        'status' => 'Aktif',
                    ]);
                }
            }
        });

        return redirect()->route('admin.siswa.index')
            ->with('success', 'Siswa alumni berhasil didaftarkan ulang!');
    }

    private function nonaktifkanSiswa(Siswa $siswa): void
    {
        $user = $siswa->user;

        $user->removeRole('siswa');

        User::where('id', $user->id)->update(['email_verified_at' => null]);

        $siswa->clearTransferToken();
    }

    private function authorizeInstansi(Siswa $siswa): void
    {
        $instansi = Auth::user()->getInstansi();
        abort_if($siswa->instansi_id !== $instansi->id_instansi, 403);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls|max:2048',
            'tahun_id' => 'nullable|exists:tahun_ajaran,id_tahun',
        ]);

        $instansi = Auth::user()->getInstansi();
        $import = new SiswaImport($instansi->id_instansi, $request->tahun_id);

        Excel::import($import, $request->file('file'));

        $berhasil = $import->getBerhasil();
        $gagal = $import->getGagal();
        $daftarUlang = $import->getDaftarUlang();
        $kelasNotFound = $import->getKelasNotFound();
        $gagalList = $import->getGagalList();

        $message = "Import selesai! {$berhasil} siswa baru berhasil diimport.";
        if ($daftarUlang > 0) {
            $message .= " {$daftarUlang} siswa alumni berhasil didaftarkan ulang.";
        }
        if ($gagal > 0) {
            $message .= " {$gagal} baris dilewati.";
        }
        if (! empty($kelasNotFound)) {
            $message .= ' Kelas tidak ditemukan: '.implode(', ', $kelasNotFound).' — siswa tetap diimport tapi belum terdaftar di kelas.';
        }

        return back()->with('success', $message)->with('gagalList', $gagalList);
    }

    public function downloadTemplate()
    {
        $headers = [
            'nama_siswa',
            'nisn',
            'jenis_kelamin',
            'tanggal_lahir',
            'email_siswa',
            'nama_ortu',
            'email_ortu',
            'hubungan',
            'nama_kelas',
        ];

        $contoh = [
            'Ahmad Fauzi',
            '0012345678',
            'L',
            '2007-05-15',
            'ahmad@example.com',
            'Fauzi Senior',
            'fauzisenior@example.com',
            'Ayah',
            'X IPA 1',
        ];

        $filename = 'template-import-siswa.xlsx';

        return Excel::download(
            new class($headers, $contoh) implements FromArray, WithHeadings
            {
                public function __construct(private array $headers, private array $contoh) {}

                public function headings(): array
                {
                    return $this->headers;
                }

                public function array(): array
                {
                    return [$this->contoh];
                }
            },
            $filename
        );
    }

    private function blockedResponse(Siswa $siswa, string $message): JsonResponse
    {
        return response()->json([
            'found' => true,
            'same_instansi' => false,
            'blocked' => true,
            'message' => $message,
            'nama' => $siswa->nama_siswa,
        ]);
    }

    private function daftarUlangResponse(Siswa $siswa, string $status): JsonResponse
    {
        return response()->json([
            'found' => true,
            'same_instansi' => false,
            'action' => 'daftar-ulang',
            'status' => $status,
            'siswa_id' => $siswa->id_siswa,
            'nama' => $siswa->nama_siswa,
            'instansi' => $siswa->instansi->nama_instansi,
        ]);
    }
}
