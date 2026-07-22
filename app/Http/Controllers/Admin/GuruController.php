<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\Instansi;
use App\Models\Kelas;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;

class GuruController extends Controller
{
    public function index(Request $request)
    {
        $instansi = Auth::user()->getInstansi();

        if ($request->ajax()) {
            $statusFilter = $request->query('status_filter');

            $guru = Guru::with(['user.roles', 'instansiTujuan', 'asalInstansi', 'instansi', 'kelasWali'])
                ->select('guru.*');

            if ($statusFilter === 'mutasi') {
                $guru->where('asal_instansi_id', $instansi->id_instansi)
                    ->where('instansi_id', '!=', $instansi->id_instansi);
            } else {
                $guru->where('instansi_id', $instansi->id_instansi);

                if ($statusFilter === 'aktif') {
                    $guru->whereNull('status');
                } elseif ($statusFilter === 'keluar') {
                    $guru->where('status', 'Keluar');
                } elseif ($statusFilter === 'pensiun') {
                    $guru->where('status', 'Pensiun');
                } else {
                    $guru->whereNull('status');
                }
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
                ->addColumn('status_guru', function ($row) use ($instansi) {
                    if ($row->status === 'Keluar') {
                        return '<span class="px-2 py-1 text-xs font-medium text-red-700 bg-red-100 rounded-full">Keluar</span>';
                    }
                    if ($row->status === 'Pensiun') {
                        return '<span class="px-2 py-1 text-xs font-medium text-gray-700 bg-gray-200 rounded-full">Pensiun</span>';
                    }
                    if ($row->instansi_id !== $instansi->id_instansi) {
                        $sekolah = $row->instansi?->nama_instansi ?? '?';
                        return '<span class="px-2 py-1 text-xs font-medium text-orange-700 bg-orange-100 rounded-full">Mutasi ke ' . e($sekolah) . '</span>';
                    }
                    return '<span class="px-2 py-1 text-xs font-medium text-green-700 bg-green-100 rounded-full">Aktif</span>';
                })
                ->addColumn('aksi', function ($row) use ($instansi) {
                    $canManage = Auth::user()->can('manage-guru');
                    $html = '<div class="flex items-center gap-1">';

                    if ($row->instansi_id !== $instansi->id_instansi) {
                        $html .= '<span class="text-xs text-gray-500 italic">Read-only</span>';
                    } elseif ($canManage) {
                        // Edit
                        $html .= '<a href="' . route('admin.guru.edit', $row->id_guru) . '" title="Edit" class="text-blue-600 hover:text-blue-800">
                            <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                        </a>';

                        // Mutasi
                        if ($row->transfer_token && !$row->isTransferTokenExpired()) {
                            $html .= '<span class="text-xs text-orange-600">Pending</span>
                                <form method="POST" action="' . route('admin.guru.mutasi.batal', $row->id_guru) . '" class="inline">
                                    <input type="hidden" name="_token" value="' . csrf_token() . '">
                                    <button type="button" title="Batal Mutasi" class="text-red-600 hover:text-red-800" onclick="confirmAction(this.closest(\'form\'), \'Batalkan mutasi?\')">
                                        <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </form>';
                        } elseif ($row->isAktif()) {
                            $html .= '<a href="' . route('admin.guru.mutasi', $row->id_guru) . '" title="Mutasi" class="text-orange-600 hover:text-orange-800">
                                <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                                </svg>
                            </a>';
                        }

                        // Tandai Keluar/Pensiun (hanya guru aktif & tidak dalam mutasi)
                        if (!$row->transfer_token && $row->isAktif()) {
                            $html .= '<form method="POST" action="' . route('admin.guru.tandai-keluar', $row->id_guru) . '" class="inline">
                                <input type="hidden" name="_token" value="' . csrf_token() . '">
                                <button type="button" title="Tandai Keluar" class="text-red-600 hover:text-red-800" onclick="confirmAction(this.closest(\'form\'), \'Tandai guru ini sebagai KELUAR?\', \'Ya, Keluarkan\')">
                                    <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                    </svg>
                                </button>
                            </form>';
                            $html .= '<form method="POST" action="' . route('admin.guru.tandai-pensiun', $row->id_guru) . '" class="inline">
                                <input type="hidden" name="_token" value="' . csrf_token() . '">
                                <button type="button" title="Tandai Pensiun" class="text-gray-500 hover:text-gray-700" onclick="confirmAction(this.closest(\'form\'), \'Tandai guru ini sebagai PENSIUN?\', \'Ya, Pensiunkan\')">
                                    <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                </button>
                            </form>';
                        } elseif (!$row->transfer_token && !$row->isAktif()) {
                            $html .= '<form method="POST" action="' . route('admin.guru.batalkan-status', $row->id_guru) . '" class="inline">
                                <input type="hidden" name="_token" value="' . csrf_token() . '">
                                <button type="button" title="Aktifkan Kembali" class="text-green-600 hover:text-green-800" onclick="confirmAction(this.closest(\'form\'), \'Aktifkan kembali guru ini?\', \'Ya, Aktifkan\')">
                                    <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                    </svg>
                                </button>
                            </form>';
                        }

                        // Hapus
                        $html .= '<form method="POST" action="' . route('admin.guru.destroy', $row->id_guru) . '" class="inline">
                            <input type="hidden" name="_token" value="' . csrf_token() . '">
                            <input type="hidden" name="_method" value="DELETE">
                            <button type="button" title="Hapus" class="text-red-600 hover:text-red-800" onclick="confirmAction(this.closest(\'form\'), \'Yakin hapus data guru ini?\')">
                                <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                            </form>';

                        $html .= '</div>';
                    }

                    return $html;
                })
                ->rawColumns(['wali_kelas', 'jabatan', 'status_guru', 'aksi'])
                ->make(true);
        }

        return view('admin.guru.index');
    }

    public function create()
    {
        $instansi = Auth::user()->getInstansi();

        $jabatanTerpakai = Role::whereIn('name', ['kepala_sekolah', 'wakil_kepala_sekolah'])
            ->whereHas('users', fn ($q) => $q->whereHas('guru', fn ($q) => $q->where('instansi_id', $instansi->id_instansi)->whereNull('status')))
            ->pluck('name')
            ->toArray();

        $semuaJabatan = [
            'kepala_sekolah' => 'Kepala Sekolah',
            'wakil_kepala_sekolah' => 'Wakil Kepala Sekolah',
        ];

        $jabatanTersedia = array_diff_key($semuaJabatan, array_flip($jabatanTerpakai));

        return view('admin.guru.create', compact('jabatanTersedia'));
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

        $instansi = Auth::user()->getInstansi();

        $jabatanTerpakai = Role::whereIn('name', ['kepala_sekolah', 'wakil_kepala_sekolah'])
            ->whereHas('users', fn ($q) => $q
                ->whereHas('guru', fn ($q) => $q
                    ->where('instansi_id', $instansi->id_instansi)
                    ->whereNull('status')
                    ->where('id_guru', '!=', $guru->id_guru)
                )
            )
            ->pluck('name')
            ->toArray();

        $semuaJabatan = [
            'kepala_sekolah' => 'Kepala Sekolah',
            'wakil_kepala_sekolah' => 'Wakil Kepala Sekolah',
        ];

        $jabatanTersedia = array_diff_key($semuaJabatan, array_flip($jabatanTerpakai));

        return view('admin.guru.edit', compact('guru', 'jabatanTersedia'));
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
        $guru->update([
            'instansi_tujuan_id' => $instansiTujuan->id_instansi,
            'asal_instansi_id' => $guru->instansi_id,
        ]);
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

            // Pindahkan guru
            $guru->update([
                'instansi_id' => $instansiTujuan->id_instansi,
            ]);

            // Sync users.instansi_id agar getInstansi() mengembalikan sekolah baru
            $guru->user()->update([
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
        $guru->clearAsalInstansi();

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

    public function batalkanStatus(Guru $guru)
    {
        $this->authorizeInstansi($guru);

        if ($guru->isAktif()) {
            return back()->with('error', 'Guru ini masih aktif.');
        }

        $info = [];

        DB::transaction(function () use ($guru, &$info) {
            $instansi = Auth::user()->getInstansi();
            $data = $guru->deactivated_data;
            $user = $guru->user;

            $guru->update(['status' => null]);
            $user->assignRole('guru');
            $info[] = '✅ Role guru — restored';

            // Restore roles (skip guru — already done above)
            if ($data && isset($data['roles'])) {
                foreach ($data['roles'] as $role) {
                    if ($role === 'guru') continue;

                    if (in_array($role, ['kepala_sekolah', 'wakil_kepala_sekolah'])) {
                        $alreadyTaken = User::whereHas('guru', fn($q) => $q
                            ->where('instansi_id', $instansi->id_instansi)
                            ->whereNull('status')
                        )
                            ->whereHas('roles', fn($q) => $q->where('name', $role))
                            ->where('id', '!=', $user->id)
                            ->exists();

                        $label = $role === 'kepala_sekolah' ? 'Kepala Sekolah' : 'Wakil Kepala Sekolah';
                        if (!$alreadyTaken) {
                            $user->assignRole($role);
                            $info[] = "✅ Role {$label} — restored";
                        } else {
                            $info[] = "⚠️ Role {$label} — skipped (sudah ada guru lain)";
                        }
                    }
                }
            }

            // Restore wali kelas
            $restoredAnyWali = false;
            if ($data && isset($data['wali_of'])) {
                foreach ($data['wali_of'] as $kelasId) {
                    $kelas = Kelas::find($kelasId);
                    if ($kelas && $kelas->guru_wali_id === null) {
                        $kelas->update(['guru_wali_id' => $guru->id_guru]);
                        $info[] = "✅ Wali kelas {$kelas->nama_kelas} — restored";
                        $restoredAnyWali = true;
                    } elseif ($kelas) {
                        $info[] = "⚠️ Wali kelas {$kelas->nama_kelas} — skipped (sudah ada wali baru)";
                    }
                }
            }

            if ($restoredAnyWali) {
                $user->assignRole('wali_kelas');
            }

            $guru->update(['deactivated_data' => null]);
        });

        return redirect()->route('admin.guru.index')
            ->with('success', "Status {$guru->nama_guru} dikembalikan ke Aktif.")
            ->with('restore_info', $info);
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

        // Simpan data untuk restore nanti
        $guru->update([
            'deactivated_data' => [
                'roles' => $user->roles->pluck('name')->toArray(),
                'wali_of' => $guru->kelasWali->pluck('id_kelas')->toArray(),
            ],
        ]);

        // Hapus role
        $user->removeRole('guru');
        $user->removeRole('wali_kelas');
        $user->removeRole('kepala_sekolah');
        $user->removeRole('wakil_kepala_sekolah');

        // Cegah login
        User::where('id', $user->id)->update(['email_verified_at' => null]);

        // Lepaskan wali kelas
        $guru->kelasWali()->update(['guru_wali_id' => null]);

        // Hapus token mutasi jika ada
        $guru->clearTransferToken();
    }

    private function authorizeInstansi(Guru $guru): void
    {
        $instansi = Auth::user()->getInstansi();
        abort_if($guru->instansi_id !== $instansi->id_instansi, 403);
    }
}