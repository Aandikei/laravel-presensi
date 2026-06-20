<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\Instansi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Yajra\DataTables\Facades\DataTables;

class GuruController extends Controller
{
    public function index(Request $request)
    {
        $instansi = Auth::user()->getInstansi();

        if ($request->ajax()) {
            $guru = Guru::with(['user', 'instansiTujuan', 'kelasWali'])
                ->where('instansi_id', $instansi->id_instansi)
                ->select('guru.*');

            // Filter status dropdown
            if ($statusFilter = $request->query('status_filter')) {
                if ($statusFilter === 'aktif') {
                    $guru->whereNull('status');
                } elseif ($statusFilter === 'mutasi') {
                    $guru->whereNotNull('transfer_token')
                        ->where('transfer_token_expires_at', '>=', now());
                } elseif ($statusFilter === 'keluar') {
                    $guru->where('status', 'Keluar');
                } elseif ($statusFilter === 'pensiun') {
                    $guru->where('status', 'Pensiun');
                }
            } else {
                $guru->whereNull('status')
                    ->where(function ($q) {
                        $q->whereNull('transfer_token')
                          ->orWhere('transfer_token_expires_at', '<', now());
                    });
            }

            return DataTables::of($guru)
                ->addIndexColumn()
                ->addColumn('email', fn($row) => $row->user->email ?? '-')
                ->addColumn('wali_kelas', function ($row) {
                    $kelas = $row->kelasWali->first();
                    if ($kelas) {
                        return $kelas->nama_kelas;
                    }
                    return '<span class="text-gray-500">-</span>';
                })
                ->addColumn('jabatan', function ($row) {
                    if ($row->user->hasRole('kepala_sekolah')) {
                        return 'Kepala Sekolah';
                    }
                    if ($row->user->hasRole('wakil_kepala_sekolah')) {
                        return 'Wakil Kepala Sekolah';
                    }
                    return '-';
                })
                ->addColumn('status_guru', function ($row) {
                    if ($row->transfer_token && !$row->isTransferTokenExpired()) {
                        $tujuan = optional($row->instansiTujuan)->nama_instansi ?? '?';
                        return '<span class="px-2 py-1 text-xs font-medium text-orange-700 bg-orange-100 rounded-full">⏳ Mutasi ke ' . e($tujuan) . '</span>';
                    }
                    if ($row->status === 'Keluar') {
                        return '<span class="px-2 py-1 text-xs font-medium text-red-700 bg-red-100 rounded-full">Keluar</span>';
                    }
                    if ($row->status === 'Pensiun') {
                        return '<span class="px-2 py-1 text-xs font-medium text-gray-700 bg-gray-200 rounded-full">Pensiun</span>';
                    }
                    return '<span class="px-2 py-1 text-xs font-medium text-green-700 bg-green-100 rounded-full">Aktif</span>';
                })
                ->addColumn('aksi', function ($row) {
                    $canManage = Auth::user()->can('manage-guru');
                    $html = '<div class="flex items-center gap-1">';

                    if ($canManage) {
                        // Edit
                        $html .= '<a href="' . route('admin.guru.edit', $row->id_guru) . '" title="Edit" class="text-blue-600 hover:text-blue-800">
                            <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                        </a>';

                        // Mutasi
                        if ($row->transfer_token && !$row->isTransferTokenExpired()) {
                            $html .= '<span class="text-xs text-orange-600 ml-2">⏳</span>
                                <form method="POST" action="' . route('admin.guru.mutasi.batal', $row->id_guru) . '" class="inline ml-1">
                                    <input type="hidden" name="_token" value="' . csrf_token() . '">
                                    <button type="submit" title="Batal Mutasi" class="text-red-600 hover:text-red-800 text-xs" onclick="return confirm(\'Batalkan mutasi?\')">✕</button>
                                </form>';
                        } elseif ($row->isAktif()) {
                            $html .= '<a href="' . route('admin.guru.mutasi', $row->id_guru) . '" title="Mutasi" class="text-orange-600 hover:text-orange-800 ml-2">
                                <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                                </svg>
                            </a>';
                        }

                        // Tandai Keluar/Pensiun (hanya guru aktif & tidak dalam mutasi)
                        if (!$row->transfer_token && $row->isAktif()) {
                            $html .= '<form method="POST" action="' . route('admin.guru.tandai-keluar', $row->id_guru) . '" class="inline ml-1">
                                <input type="hidden" name="_token" value="' . csrf_token() . '">
                                <button type="submit" title="Tandai Keluar" class="text-red-500 hover:text-red-700 text-xs" onclick="return confirm(\'Tandai guru ini sebagai KELUAR?\')">🚪</button>
                            </form>';
                            $html .= '<form method="POST" action="' . route('admin.guru.tandai-pensiun', $row->id_guru) . '" class="inline ml-1">
                                <input type="hidden" name="_token" value="' . csrf_token() . '">
                                <button type="submit" title="Tandai Pensiun" class="text-gray-500 hover:text-gray-700 text-xs" onclick="return confirm(\'Tandai guru ini sebagai PENSIUN?\')">🎓</button>
                            </form>';
                        }

                        // Delete
                        $html .= '<form method="POST" action="' . route('admin.guru.destroy', $row->id_guru) . '" class="inline ml-1">
                            <input type="hidden" name="_token" value="' . csrf_token() . '">
                            <input type="hidden" name="_method" value="DELETE">
                            <button type="submit" title="Hapus" class="text-red-600 hover:text-red-800" onclick="return confirm(\'Yakin hapus guru ini?\')">
                                <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                            </form>';
                    }

                    $html .= '</div>';
                    return $html;
                })
                ->rawColumns(['wali_kelas', 'jabatan', 'status_guru', 'aksi'])
                ->make(true);
        }

        return view('admin.guru.index');
    }

    public function create()
    {
        return view('admin.guru.create');
    }

    public function store(Request $request)
    {
        $instansi = Auth::user()->getInstansi();

        $validated = $request->validate([
            'nama_guru'     => 'required|string|max:255',
            'nip'           => [
                'nullable',
                'string',
                function ($attribute, $value, $fail) {
                    if (!$value) return;
                    $guruExist = Guru::with('instansi')->where('nip', $value)->first();
                    if ($guruExist) {
                        $fail("NIP ini sudah terdaftar atas nama {$guruExist->nama_guru} di {$guruExist->instansi->nama_instansi}. Jika guru tersebut pindah tugas, gunakan menu Mutasi.");
                    }
                },
            ],
            'email'         => 'required|email|unique:users,email',
            'password'      => 'required|min:8',
            'jenis_kelamin' => 'required|in:L,P',
            'no_hp'         => 'nullable|string|max:15',
            'jabatan'       => 'nullable|in:kepala_sekolah,wakil_kepala_sekolah',
        ]);

        // Validasi unik jabatan per sekolah
        if (!empty($validated['jabatan'])) {
            $sudahAda = User::whereHas('guru', fn ($q) => $q->where('instansi_id', $instansi->id_instansi))
                ->whereHas('roles', fn ($q) => $q->where('name', $validated['jabatan']))
                ->exists();

            if ($sudahAda) {
                $label = $validated['jabatan'] === 'kepala_sekolah' ? 'Kepala Sekolah' : 'Wakil Kepala Sekolah';
                return back()->withErrors(['jabatan' => "Sudah ada $label di sekolah ini."])->withInput();
            }
        }

        DB::transaction(function () use ($validated, $instansi) {
            // Buat user
            $user = User::create([
                'name' => $validated['nama_guru'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'instansi_id' => $instansi->id_instansi,
            ]);

            // Assign role guru
            $user->assignRole('guru');

            if (!empty($validated['jabatan'])) {
                $user->assignRole($validated['jabatan']);
                $user->forceFill(['email_verified_at' => now()])->save();
            }

            // Buat data guru
            Guru::create([
                'user_id'       => $user->id,
                'instansi_id'   => $instansi->id_instansi,
                'nama_guru'     => $validated['nama_guru'],
                'nip'           => $validated['nip'] ?? null,
                'jenis_kelamin' => $validated['jenis_kelamin'],
                'no_hp'         => $validated['no_hp'] ?? null,
            ]);
        });

        return redirect()->route('admin.guru.index')
            ->with('success', 'Guru berhasil ditambahkan!');
    }

    public function edit(Guru $guru)
    {
        $this->authorizeInstansi($guru);
        return view('admin.guru.edit', compact('guru'));
    }

    public function update(Request $request, Guru $guru)
    {
        $this->authorizeInstansi($guru);

        $validated = $request->validate([
            'nama_guru'     => 'required|string|max:255',
            'nip'           => [
                'nullable',
                'string',
                function ($attribute, $value, $fail) use ($guru) {
                    if (!$value) return;
                    $guruExist = Guru::with('instansi')
                        ->where('nip', $value)
                        ->where('id_guru', '!=', $guru->id_guru)
                        ->first();
                    if ($guruExist) {
                        $fail("NIP ini sudah terdaftar atas nama {$guruExist->nama_guru} di {$guruExist->instansi->nama_instansi}. Jika guru tersebut pindah tugas, gunakan menu Mutasi.");
                    }
                },
            ],
            'email'         => 'required|email|unique:users,email,' . $guru->user_id,
            'jenis_kelamin' => 'required|in:L,P',
            'no_hp'         => 'nullable|string|max:15',
            'password'      => 'nullable|min:8',
            'jabatan'       => 'nullable|in:kepala_sekolah,wakil_kepala_sekolah',
        ]);

        $user = $guru->user;

        // Validasi unik jabatan per sekolah (exclude diri sendiri)
        if (!empty($validated['jabatan'])) {
            $instansi = Auth::user()->getInstansi();
            $sudahAda = User::whereHas('guru', fn ($q) => $q->where('instansi_id', $instansi->id_instansi))
                ->whereHas('roles', fn ($q) => $q->where('name', $validated['jabatan']))
                ->where('id', '!=', $user->id)
                ->exists();

            if ($sudahAda) {
                $label = $validated['jabatan'] === 'kepala_sekolah' ? 'Kepala Sekolah' : 'Wakil Kepala Sekolah';
                return back()->withErrors(['jabatan' => "Sudah ada $label di sekolah ini."])->withInput();
            }
        }

        // Mutual exclusion: cegah kasek/wakasek menjadi wali_kelas
        if (!empty($validated['jabatan']) && $guru->kelasWali()->exists()) {
            return back()->withErrors([
                'jabatan' => 'Guru ini adalah wali kelas. Tidak bisa ditetapkan sebagai ' . $validated['jabatan'] . '. Lepaskan dulu jabatan wali kelasnya.',
            ])->withInput();
        }

        DB::transaction(function () use ($validated, $guru, $user) {
            // Update user
            $userData = [
                'name'  => $validated['nama_guru'],
                'email' => $validated['email'],
            ];
            if (!empty($validated['password'])) {
                $userData['password'] = Hash::make($validated['password']);
            }
            $user->update($userData);

            // Update guru
            $guru->update([
                'nama_guru'     => $validated['nama_guru'],
                'nip'           => $validated['nip'] ?? null,
                'jenis_kelamin' => $validated['jenis_kelamin'],
                'no_hp'         => $validated['no_hp'] ?? null,
            ]);

            // Sync jabatan roles
            $jabatanRoles = ['kepala_sekolah', 'wakil_kepala_sekolah'];
            foreach ($jabatanRoles as $role) {
                if ($role === ($validated['jabatan'] ?? null)) {
                    if (!$user->hasRole($role)) {
                        $user->assignRole($role);
                    }
                } else {
                    if ($user->hasRole($role)) {
                        $user->removeRole($role);
                    }
                }
            }

            // Auto verify jika punya jabatan kepala/wakil
            if (!empty($validated['jabatan'])) {
                $user->forceFill(['email_verified_at' => now()])->save();
            }
        });

        return redirect()->route('admin.guru.index')
            ->with('success', 'Data guru berhasil diupdate!');
    }

    public function mutasiForm(Guru $guru)
    {
        $this->authorizeInstansi($guru);

        if ($guru->transfer_token && !$guru->isTransferTokenExpired()) {
            return redirect()->route('admin.guru.index')
                ->with('error', "Guru {$guru->nama_guru} sedang dalam proses mutasi. Token: {$guru->transfer_token}");
        }

        $instansi = Auth::user()->getInstansi();
        $sekolahTujuan = Instansi::where('id_instansi', '!=', $instansi->id_instansi)
            ->orderBy('jenjang')
            ->orderBy('nama_instansi')
            ->get();

        return view('admin.guru.mutasi', compact('guru', 'instansi', 'sekolahTujuan'));
    }

    public function prosesMutasi(Request $request, Guru $guru)
    {
        $this->authorizeInstansi($guru);

        $validated = $request->validate([
            'instansi_tujuan' => 'required|exists:instansi,id_instansi',
        ]);

        $instansiTujuan = Instansi::findOrFail($validated['instansi_tujuan']);

        // Simpan tujuan & generate token
        $guru->update(['instansi_tujuan_id' => $instansiTujuan->id_instansi]);
        $guru->generateTransferToken();

        return redirect()->route('admin.guru.index')
            ->with('success', "Token mutasi untuk {$guru->nama_guru} ke {$instansiTujuan->nama_instansi}: {$guru->transfer_token}");
    }

    public function formTerimaMutasi()
    {
        return view('admin.guru.terima-mutasi');
    }

    public function verifikasiTerimaMutasi(Request $request)
    {
        $validated = $request->validate([
            'token' => 'required|string|size:6',
        ]);

        $guru = Guru::where('transfer_token', strtoupper($validated['token']))
            ->whereNotNull('instansi_tujuan_id')
            ->first();

        if (!$guru) {
            return back()->with('error', 'Token tidak ditemukan.')->withInput();
        }

        if ($guru->isTransferTokenExpired()) {
            return back()->with('error', 'Token sudah kedaluwarsa. Minta sekolah asal untuk membuat token baru.')->withInput();
        }

        $instansiTujuan = Instansi::find($guru->instansi_tujuan_id);
        $instansiAsal = $guru->instansi;
        $userInstansi = $request->user()->getInstansi();

        // Hanya sekolah tujuan yang bisa menerima
        if ($userInstansi->id_instansi !== $instansiTujuan->id_instansi) {
            return back()->with('error', 'Token ini untuk sekolah ' . $instansiTujuan->nama_instansi . '.')->withInput();
        }

        return view('admin.guru.terima-konfirmasi', compact('guru', 'instansiTujuan', 'instansiAsal'));
    }

    public function prosesTerimaMutasi(Request $request)
    {
        $validated = $request->validate([
            'guru_id' => 'required|exists:guru,id_guru',
            'token' => 'required|string|size:6',
        ]);

        $guru = Guru::where('id_guru', $validated['guru_id'])
            ->where('transfer_token', strtoupper($validated['token']))
            ->whereNotNull('instansi_tujuan_id')
            ->firstOrFail();

        if ($guru->isTransferTokenExpired()) {
            return back()->with('error', 'Token sudah kedaluwarsa.');
        }

        $instansiTujuan = Instansi::findOrFail($guru->instansi_tujuan_id);
        $userInstansi = $request->user()->getInstansi();

        if ($userInstansi->id_instansi !== $instansiTujuan->id_instansi) {
            return back()->with('error', 'Anda tidak berhak menerima mutasi ini.');
        }

        DB::transaction(function () use ($guru, $instansiTujuan) {
            // Lepaskan wali kelas jika ada
            if ($guru->kelasWali()->exists()) {
                $guru->kelasWali()->update(['guru_wali_id' => null]);
            }

            // Lepaskan guru dari kurikulum (data historis tetap ada)
            $guru->kurikulum()->update(['guru_id' => null]);

            // Pindahkan guru
            $guru->update([
                'instansi_id' => $instansiTujuan->id_instansi,
            ]);

            $guru->clearTransferToken();
        });

        return redirect()->route('admin.guru.index')
            ->with('success', "Guru {$guru->nama_guru} berhasil dimutasi ke {$instansiTujuan->nama_instansi}.");
    }

    public function batalMutasi(Guru $guru)
    {
        $this->authorizeInstansi($guru);

        $guru->clearTransferToken();

        return redirect()->route('admin.guru.index')
            ->with('success', "Mutasi {$guru->nama_guru} dibatalkan.");
    }

    public function tandaiKeluar(Guru $guru)
    {
        $this->authorizeInstansi($guru);

        if (!$guru->isAktif()) {
            return back()->with('error', 'Guru sudah tidak aktif.');
        }

        DB::transaction(function () use ($guru) {
            $this->nonaktifkanGuru($guru);
            $guru->markAsKeluar();
        });

        return redirect()->route('admin.guru.index')
            ->with('success', "Guru {$guru->nama_guru} ditandai sebagai Keluar.");
    }

    public function tandaiPensiun(Guru $guru)
    {
        $this->authorizeInstansi($guru);

        if (!$guru->isAktif()) {
            return back()->with('error', 'Guru sudah tidak aktif.');
        }

        DB::transaction(function () use ($guru) {
            $this->nonaktifkanGuru($guru);
            $guru->markAsPensiun();
        });

        return redirect()->route('admin.guru.index')
            ->with('success', "Guru {$guru->nama_guru} ditandai sebagai Pensiun.");
    }

    public function destroy(Guru $guru)
    {
        $this->authorizeInstansi($guru);

        DB::transaction(function () use ($guru) {
            $user = $guru->user;
            $guru->delete();
            $user->delete();
        });

        return redirect()->route('admin.guru.index')
            ->with('success', 'Guru berhasil dihapus!');
    }

    private function nonaktifkanGuru(Guru $guru): void
    {
        $user = $guru->user;

        // Hapus role
        $user->removeRole('guru');
        $user->removeRole('wali_kelas');
        $user->removeRole('kepala_sekolah');
        $user->removeRole('wakil_kepala_sekolah');

        // Cegah login
        User::where('id', $user->id)->update(['email_verified_at' => null]);

        // Lepaskan wali kelas
        $guru->kelasWali()->update(['guru_wali_id' => null]);

        // Hapus kurikulum & jadwal
        $guru->kurikulum()->each(fn ($k) => $k->jadwal()->delete());
        $guru->kurikulum()->delete();

        // Hapus token mutasi jika ada
        $guru->clearTransferToken();
    }

    private function authorizeInstansi(Guru $guru): void
    {
        $instansi = Auth::user()->getInstansi();
        abort_if($guru->instansi_id !== $instansi->id_instansi, 403);
    }
}