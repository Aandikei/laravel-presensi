<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\SiswaImport;
use App\Models\Kelas;
use App\Models\OrangTua;
use App\Models\OrtuSiswa;
use App\Models\RegistrasiAkademik;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Models\User;
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
                'registrasiAkademik' => fn ($q) => $q->whereHas('kelas', fn ($qq) => $qq->where('instansi_id', $instansi->id_instansi)),
            ])
                ->where('instansi_id', '=', $instansi->id_instansi)
                ->select('siswa.*');

            // Filter per kelas
            if ($request->kelas_id) {
                $siswa->whereHas('registrasiAktif', fn ($q) => $q->where('kelas_id', '=', $request->kelas_id));
            }

            // Filter status
            if ($request->status === 'Aktif') {
                $siswa->has('registrasiAktif');
            } elseif ($request->status === 'Pindah') {
                $siswa->whereHas('registrasiAkademik', fn ($q) => $q
                    ->where('status', 'Pindah')
                    ->whereHas('kelas', fn ($qq) => $qq->where('instansi_id', $instansi->id_instansi)));
            } elseif ($request->status === 'Alumni') {
                $siswa->whereDoesntHave('registrasiAktif')
                    ->whereHas('registrasiAkademik', fn ($q) => $q
                        ->whereHas('kelas', fn ($qq) => $qq->where('instansi_id', $instansi->id_instansi)))
                    ->whereDoesntHave('registrasiAkademik', fn ($q) => $q
                        ->where('status', 'Pindah')
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
                    if ($regPindah) {
                        return '<span class="font-medium text-yellow-600 dark:text-yellow-400">Pindah</span>';
                    }

                    if ($row->registrasiAkademik->isNotEmpty()) {
                        return '<span class="font-medium text-green-600 dark:text-green-400">Alumni</span>';
                    }

                    return '<span class="text-gray-500">Belum terdaftar</span>';
                })
                // ->addColumn('total_poin', fn($row) => $row->logPoin()->sum('jumlah_poin') ?? 0)
                ->addColumn('total_poin', function ($row) {
                    return $row->logPoin()
                        ->join('master_poin', 'log_poin_siswa.poin_id', '=', 'master_poin.id_poin')
                        ->sum('master_poin.jumlah_poin') ?? 0;
                })
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
                        $delete = '<form method="POST" action="'.route('admin.siswa.destroy', $row->id_siswa).'" class="inline">
                            <input type="hidden" name="_token" value="'.csrf_token().'">
                            <input type="hidden" name="_method" value="DELETE">
                            <button type="submit" title="Hapus" class="text-red-600 hover:text-red-800" onclick="return confirm(\'Yakin hapus siswa ini?\')">
                                <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                            </form>';
                        return $edit.' '.$pindah.' '.$delete;
                    }

                    return '';
                })
                ->rawColumns(['kelas', 'aksi'])
                ->filterColumn('nama_siswa', function ($query, $keyword) {
                    $query->where('nama_siswa', 'like', "%{$keyword}%");
                })
                ->make(true);
        }
        // Tambah data kelas untuk filter
        $tahunAktif = TahunAjaran::where('instansi_id', '=', $instansi->id_instansi)
            ->where('is_aktif', '=', true)->first();

        $kelas = Kelas::where('instansi_id', '=', $instansi->id_instansi)
            ->orderBy('tingkat')
            ->orderBy('nama_kelas')
            ->get();

        $tahunAjaran = TahunAjaran::where('instansi_id', '=', $instansi->id_instansi)
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

    public function create()
    {
        $instansi = Auth::user()->getInstansi();
        $tahunAktif = TahunAjaran::where('instansi_id', '=', $instansi->id_instansi)
            ->where('is_aktif', '=', true)->first();
        $kelas = Kelas::where('instansi_id', '=', $instansi->id_instansi)
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
                $hasActive = $existing->registrasiAkademik()
                    ->aktif()
                    ->whereHas('tahunAjaran', fn ($q) => $q->where('is_aktif', true))
                    ->whereHas('kelas', fn ($q) => $q->where('instansi_id', $existing->instansi_id))
                    ->exists();

                $hasPindah = $existing->registrasiAkademik()
                    ->where('status', 'Pindah')
                    ->exists();

                $hasAnyReg = $existing->registrasiAkademik()->exists();

                if ($hasActive || $hasPindah) {
                    return redirect()->route('admin.siswa.pindah.form-masuk', ['nisn' => $request->nisn])
                        ->with('info', "Siswa dengan NISN {$request->nisn} ({$existing->nama_siswa}) sudah terdaftar di {$existing->instansi->nama_instansi}. Gunakan form Pindah Masuk di bawah untuk memindahkan siswa ini ke sekolah Anda.");
                }

                if (! $hasAnyReg) {
                    return redirect()->route('admin.siswa.pindah.form-masuk', ['nisn' => $request->nisn])
                        ->with('info', "Siswa dengan NISN {$request->nisn} ({$existing->nama_siswa}) terdaftar di {$existing->instansi->nama_instansi} namun belum memiliki data kelas. Minta sekolah asal untuk mendaftarkan ke kelas dan membuat kode transfer.");
                }

                return redirect()->route('admin.siswa.daftar-ulang', $existing->id_siswa)
                    ->with('info', "Siswa dengan NISN {$request->nisn} ({$existing->nama_siswa}) sudah lulus dari {$existing->instansi->nama_instansi}. Silakan lengkapi data untuk mendaftarkan ulang.");
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

        abort_if($siswa->instansi_id === $instansi->id_instansi, 403, 'Siswa sudah terdaftar di sekolah ini.');

        $hasActive = $siswa->registrasiAkademik()
            ->aktif()
            ->whereHas('tahunAjaran', fn ($q) => $q->where('is_aktif', true))
            ->whereHas('kelas', fn ($q) => $q->where('instansi_id', $siswa->instansi_id))
            ->exists();
        abort_if($hasActive, 403, 'Siswa masih aktif dan harus menggunakan Pindah Masuk.');

        // Cegah daftar ulang ke jenjang yang lebih rendah
        $siswaInstansi = $siswa->instansi;
        abort_if($instansi->tingkat_min < $siswaInstansi->tingkat_min, 403, 'Tidak bisa mendaftarkan alumni ke jenjang yang lebih rendah.');

        $siswa->load(['user', 'orangTua.user', 'instansi']);

        $tahunAktif = TahunAjaran::where('instansi_id', $instansi->id_instansi)
            ->where('is_aktif', true)->first();
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

        // Cegah daftar ulang ke jenjang yang lebih rendah
        abort_if($instansi->tingkat_min < $siswa->instansi->tingkat_min, 403, 'Tidak bisa mendaftarkan alumni ke jenjang yang lebih rendah.');

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
}
