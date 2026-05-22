<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrangTua;
use App\Models\OrtuSiswa;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Yajra\DataTables\Facades\DataTables;

class SiswaController extends Controller
{
    public function index(Request $request)
    {
        $instansi = Auth::user()->getInstansi();

        if ($request->ajax()) {
            $siswa = Siswa::with(['user', 'registrasiAktif.kelas'])
                ->where('instansi_id', $instansi->id_instansi)
                ->select('siswa.*');

            return DataTables::of($siswa)
                ->addIndexColumn()
                ->addColumn('email', fn ($row) => $row->user->email ?? '-')
                ->addColumn('kelas', function ($row) {
                    $kelas = $row->registrasiAktif?->kelas;
                    if ($kelas) {
                        return '<span class="px-2 py-1 text-xs font-medium text-purple-700 bg-purple-100 rounded-full dark:bg-purple-800 dark:text-purple-200">'
                            .$kelas->nama_kelas.'</span>';
                    }

                    return '<span class="px-2 py-1 text-xs text-gray-500">Belum terdaftar</span>';
                })
                // ->addColumn('total_poin', fn($row) => $row->logPoin()->sum('jumlah_poin') ?? 0)
                ->addColumn('total_poin', function ($row) {
                    return $row->logPoin()
                        ->join('master_poin', 'log_poin_siswa.poin_id', '=', 'master_poin.id_poin')
                        ->sum('master_poin.jumlah_poin') ?? 0;
                })
                ->addColumn('aksi', function ($row) {
                    $edit = '<a href="'.route('admin.siswa.edit', $row->id_siswa).'"
                        class="px-3 py-1 text-xs font-medium text-white bg-blue-600 rounded hover:bg-blue-700">
                        Edit</a>';
                    $delete = '<form method="POST" action="'.route('admin.siswa.destroy', $row->id_siswa).'" class="inline">
                        <input type="hidden" name="_token" value="'.csrf_token().'">
                        <input type="hidden" name="_method" value="DELETE">
                        <button type="submit"
                            class="px-3 py-1 text-xs font-medium text-white bg-red-600 rounded hover:bg-red-700"
                            onclick="return confirm(\'Yakin hapus siswa ini?\')">
                            Hapus</button>
                        </form>';

                    return $edit.' '.$delete;
                })
                ->rawColumns(['kelas', 'aksi'])
                ->make(true);
        }

        return view('admin.siswa.index');
    }

    public function create()
    {
        return view('admin.siswa.create');
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
            'email_ortu' => 'required|email|unique:users,email',
            'password_ortu' => 'required|min:8',
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

            // Buat user orang tua
            $userOrtu = User::create([
                'name' => $validated['nama_ortu'],
                'email' => $validated['email_ortu'],
                'password' => Hash::make($validated['password_ortu']),
            ]);
            $userOrtu->assignRole('orang_tua');

            // Buat data orang tua
            $ortu = OrangTua::create([
                'user_id' => $userOrtu->id,
                'nama_ortu' => $validated['nama_ortu'],
                'no_hp' => $validated['no_hp_ortu'] ?? null,
            ]);

            // Link ortu ke siswa
            OrtuSiswa::create([
                'ortu_id' => $ortu->id_ortu,
                'siswa_id' => $siswa->id_siswa,
                'hubungan' => $validated['hubungan'],
                'is_primary' => true,
            ]);
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
}
