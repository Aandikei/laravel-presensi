<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guru;
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
            $guru = Guru::with(['user'])
                ->where('instansi_id', $instansi->id_instansi)
                ->select('guru.*');

            return DataTables::of($guru)
                ->addIndexColumn()
                ->addColumn('email', fn($row) => $row->user->email ?? '-')
                ->addColumn('wali_kelas', function ($row) {
                    $kelas = $row->kelasWali()->first();
                    if ($kelas) {
                        return '<span class="px-2 py-1 text-xs font-medium text-purple-700 bg-purple-100 rounded-full dark:bg-purple-800 dark:text-purple-200">'
                            . $kelas->nama_kelas . '</span>';
                    }
                    return '<span class="px-2 py-1 text-xs text-gray-500">-</span>';
                })
                ->addColumn('aksi', function ($row) {
                    $edit = '<a href="' . route('admin.guru.edit', $row->id_guru) . '"
                        class="px-3 py-1 text-xs font-medium text-white bg-blue-600 rounded hover:bg-blue-700">
                        Edit</a>';
                    $delete = '<form method="POST" action="' . route('admin.guru.destroy', $row->id_guru) . '" class="inline">
                        <input type="hidden" name="_token" value="' . csrf_token() . '">
                        <input type="hidden" name="_method" value="DELETE">
                        <button type="submit"
                            class="px-3 py-1 text-xs font-medium text-white bg-red-600 rounded hover:bg-red-700"
                            onclick="return confirm(\'Yakin hapus guru ini?\')">
                            Hapus</button>
                        </form>';
                    return $edit . ' ' . $delete;
                })
                ->rawColumns(['wali_kelas', 'aksi'])
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
            'nip'           => 'nullable|string|unique:guru,nip',
            'email'         => 'required|email|unique:users,email',
            'password'      => 'required|min:8',
            'jenis_kelamin' => 'required|in:L,P',
            'no_hp'         => 'nullable|string|max:15',
        ]);

        DB::transaction(function () use ($validated, $instansi) {
            // Buat user
            $user = User::create([
                'name'     => $validated['nama_guru'],
                'email'    => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            // Assign role guru
            $user->assignRole('guru');

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
            'nip'           => 'nullable|string|unique:guru,nip,' . $guru->id_guru . ',id_guru',
            'email'         => 'required|email|unique:users,email,' . $guru->user_id,
            'jenis_kelamin' => 'required|in:L,P',
            'no_hp'         => 'nullable|string|max:15',
            'password'      => 'nullable|min:8',
        ]);

        DB::transaction(function () use ($validated, $guru) {
            // Update user
            $userData = [
                'name'  => $validated['nama_guru'],
                'email' => $validated['email'],
            ];
            if (!empty($validated['password'])) {
                $userData['password'] = Hash::make($validated['password']);
            }
            $guru->user->update($userData);

            // Update guru
            $guru->update([
                'nama_guru'     => $validated['nama_guru'],
                'nip'           => $validated['nip'] ?? null,
                'jenis_kelamin' => $validated['jenis_kelamin'],
                'no_hp'         => $validated['no_hp'] ?? null,
            ]);
        });

        return redirect()->route('admin.guru.index')
            ->with('success', 'Data guru berhasil diupdate!');
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

    private function authorizeInstansi(Guru $guru): void
    {
        $instansi = Auth::user()->getInstansi();
        abort_if($guru->instansi_id !== $instansi->id_instansi, 403);
    }
}