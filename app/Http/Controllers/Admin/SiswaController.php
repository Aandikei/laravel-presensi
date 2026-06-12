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
            $siswa = Siswa::with(['user', 'registrasiAktif.kelas', 'registrasiAkademik'])
                ->where('instansi_id', '=', $instansi->id_instansi)
                ->select('siswa.*');

            // Filter per kelas
            if ($request->kelas_id) {
                $siswa->whereHas('registrasiAktif', fn ($q) => $q->where('kelas_id', '=', $request->kelas_id));
            }

            // Filter status
            if ($request->status === 'aktif') {
                $siswa->has('registrasiAktif');
            } elseif ($request->status === 'alumni') {
                $siswa->whereDoesntHave('registrasiAktif')->has('registrasiAkademik', '>', 0);
            } elseif ($request->status === 'belum_terdaftar') {
                $siswa->whereDoesntHave('registrasiAkademik');
            }

            return DataTables::of($siswa)
                ->addIndexColumn()
                ->addColumn('email', fn ($row) => $row->user->email ?? '-')
                ->addColumn('kelas', function ($row) {
                    $kelas = $row->registrasiAktif?->kelas;
                    if ($kelas) {
                        return $kelas->nama_kelas;
                    }

                    if ($row->registrasiAkademik->isNotEmpty() && !$row->registrasiAktif) {
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
                    $edit = '<a href="'.route('admin.siswa.edit', $row->id_siswa).'" title="Edit" class="text-blue-600 hover:text-blue-800">
                        <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                    </a>';
                    $delete = '<form method="POST" action="'.route('admin.siswa.destroy', $row->id_siswa).'" class="inline">
                        <input type="hidden" name="_token" value="'.csrf_token().'">
                        <input type="hidden" name="_method" value="DELETE">
                        <button type="submit" title="Hapus" class="text-red-600 hover:text-red-800" onclick="return confirm(\'Yakin hapus siswa ini?\')">
                            <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                        </form>';

                    return $edit.' '.$delete;
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

        $validated = $request->validate([
            // Data siswa
            'nama_siswa' => 'required|string|max:255',
            'nisn' => 'required|string|unique:siswa,nisn',
            'jenis_kelamin' => 'required|in:L,P',
            'tanggal_lahir' => 'nullable|date',
            // Akun siswa
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            // Data orang tua
            'nama_ortu' => 'required|string|max:255',
            'no_hp_ortu' => 'nullable|string|max:15',
            'hubungan' => 'required|in:Ayah,Ibu,Wali',
            'email_ortu' => 'required|email|',
            'password_ortu' => 'nullable|min:8',
            // Registrasi opsional
            'kelas_id' => 'nullable|exists:kelas,id_kelas',
            'tahun_id' => 'nullable|exists:tahun_ajaran,id_tahun',
        ]);

        DB::transaction(function () use ($validated, $instansi) {
            // Buat user siswa
            $userSiswa = User::create([
                'name' => $validated['nama_siswa'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
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
                // User sudah ada, cek apakah sudah punya data orang tua
                $ortu = OrangTua::where('user_id', '=', $userOrtu->id)->first();

                if (! $ortu) {
                    // Punya akun tapi belum ada data orang tua — buatkan
                    $ortu = OrangTua::create([
                        'user_id' => $userOrtu->id,
                        'nama_ortu' => $validated['nama_ortu'],
                        'no_hp' => $validated['no_hp_ortu'] ?? null,
                    ]);
                    // Assign role kalau belum punya
                    if (! $userOrtu->hasRole('orang_tua')) {
                        $userOrtu->assignRole('orang_tua');
                    }
                }
            } else {
                // User belum ada, buat baru
                $userOrtu = User::create([
                    'name' => $validated['nama_ortu'],
                    'email' => $validated['email_ortu'],
                    'password' => Hash::make($validated['password_ortu']),
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
                    ]);
                }
            }
        });

        return redirect()->route('admin.siswa.index')
            ->with('success', 'Siswa berhasil ditambahkan!');
    }

    public function edit(Siswa $siswa)
    {
        $this->authorizeInstansi($siswa);
        $siswa->load(['user', 'orangTua.user']);

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
            $user = $siswa->user;
            $siswa->delete();
            $user->delete();
        });

        return redirect()->route('admin.siswa.index')
            ->with('success', 'Siswa berhasil dihapus!');
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
        $kelasNotFound = $import->getKelasNotFound();

        $message = "Import selesai! {$berhasil} siswa berhasil diimport.";
        if ($gagal > 0) {
            $message .= " {$gagal} baris dilewati (NISN/email sudah ada).";
        }
        if (! empty($kelasNotFound)) {
            $message .= ' Kelas tidak ditemukan: '.implode(', ', $kelasNotFound).' — siswa tetap diimport tapi belum terdaftar di kelas.';
        }

        return back()->with('success', $message);
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
