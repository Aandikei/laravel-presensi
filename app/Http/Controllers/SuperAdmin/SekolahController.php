<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\Instansi;
use App\Models\OrangTua;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Yajra\DataTables\Facades\DataTables;

class SekolahController extends Controller
{
    public function index(Request $request)
    {
        if (!$request->ajax()) {
            return view('superadmin.sekolah.index');
        }

        $sekolah = Instansi::select('instansi.*')
            ->selectSub(function ($q) {
                $q->from('guru')->whereColumn('guru.instansi_id', 'instansi.id_instansi')->selectRaw('COUNT(*)');
            }, 'guru_count')
            ->selectSub(function ($q) {
                $q->from('siswa')->whereColumn('siswa.instansi_id', 'instansi.id_instansi')->selectRaw('COUNT(*)');
            }, 'siswa_count');

        return DataTables::of($sekolah)
            ->addIndexColumn()
            ->addColumn('aksi', function ($row) {
                $show = '<a href="' . route('superadmin.sekolah.show', $row->id_instansi) . '" title="Detail" class="text-green-600 hover:text-green-800">
                    <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                </a>';
                $edit = '<a href="' . route('superadmin.sekolah.edit', $row->id_instansi) . '" title="Edit" class="text-blue-600 hover:text-blue-800">
                    <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                </a>';
                $delete = '<form method="POST" action="' . route('superadmin.sekolah.destroy', $row->id_instansi) . '" class="inline">
                    <input type="hidden" name="_token" value="' . csrf_token() . '">
                    <input type="hidden" name="_method" value="DELETE">
                    <button type="submit" title="Hapus" class="text-red-600 hover:text-red-800" onclick="return confirm(\'YAKIN HAPUS PERMANEN?\\n\\nSemua data terkait sekolah ini (siswa, guru, kelas, absensi, poin, laporan) akan ikut TERHAPUS PERMANEN.\\nTindakan ini tidak bisa dibatalkan.\')">
                        <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </button>
                </form>';
                return $show . ' ' . $edit . ' ' . $delete;
            })
            ->rawColumns(['aksi'])
            ->make(true);
    }

    public function create()
    {
        return view('superadmin.sekolah.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_instansi' => 'required|string|max:255',
            'jenjang'       => 'required|in:SD,SMP,SMA',
            'npsn'          => 'required|string|unique:instansi,npsn',
            'alamat'        => 'nullable|string',
            'telepon'       => 'nullable|string|max:20',
            'email'         => 'nullable|email|max:255',
            'admin_name'     => 'required|string|max:255',
            'admin_email'    => 'required|email|unique:users,email',
            'admin_password' => 'required|min:8',
        ]);

        DB::transaction(function () use ($validated) {
            $instansi = Instansi::create([
                'nama_instansi' => $validated['nama_instansi'],
                'jenjang'       => $validated['jenjang'],
                'npsn'          => $validated['npsn'],
                'alamat'        => $validated['alamat'] ?? null,
                'telepon'       => $validated['telepon'] ?? null,
                'email'         => $validated['email'] ?? null,
            ]);

            $user = User::create([
                'name'         => $validated['admin_name'],
                'email'        => $validated['admin_email'],
                'password'     => Hash::make($validated['admin_password']),
                'instansi_id'  => $instansi->id_instansi,
            ]);

            $user->assignRole('admin');
        });

        return redirect()->route('superadmin.sekolah.index')
            ->with('success', 'Sekolah berhasil ditambahkan!');
    }

    public function show(Instansi $instansi)
    {
        $instansi->loadCount(['guru', 'siswa', 'kelas']);

        $adminUsers = User::where('instansi_id', $instansi->id_instansi)
            ->role('admin')
            ->get(['id', 'name', 'email']);

        return view('superadmin.sekolah.show', compact('instansi', 'adminUsers'));
    }

    public function edit(Instansi $instansi)
    {
        return view('superadmin.sekolah.edit', compact('instansi'));
    }

    public function update(Request $request, Instansi $instansi)
    {
        $validated = $request->validate([
            'nama_instansi' => 'required|string|max:255',
            'jenjang'       => 'required|in:SD,SMP,SMA',
            'npsn'          => 'required|string|unique:instansi,npsn,' . $instansi->id_instansi . ',id_instansi',
            'alamat'        => 'nullable|string',
            'telepon'       => 'nullable|string|max:20',
            'email'         => 'nullable|email|max:255',
        ]);

        $instansi->update($validated);

        return redirect()->route('superadmin.sekolah.index')
            ->with('success', 'Data sekolah berhasil diperbarui!');
    }

    public function destroy(Instansi $instansi)
    {
        DB::transaction(function () use ($instansi) {
            $id = $instansi->id_instansi;

            // Ambil semua siswa di sekolah ini
            $siswaIds = Siswa::where('instansi_id', $id)->pluck('id_siswa');

            // Ambil orang tua yang link ke siswa sekolah ini
            $ortuIds = OrtuSiswa::whereIn('siswa_id', $siswaIds)->pluck('ortu_id')->unique();

            // Cari orang tua yang PUNYA anak di sekolah LAIN (jangan hapus)
            $parentsWithOtherChildren = OrtuSiswa::whereIn('ortu_id', $ortuIds)
                ->whereNotIn('siswa_id', $siswaIds)
                ->pluck('ortu_id')
                ->unique();

            // Hapus semua user milik sekolah ini (guru, siswa, admin)
            $userIds = Guru::where('instansi_id', $id)->pluck('user_id')
                ->merge(Siswa::where('instansi_id', $id)->pluck('user_id'))
                ->merge(User::where('instansi_id', $id)->pluck('id'));
            User::whereIn('id', $userIds)->delete();

            // Hapus sekolah → cascade ke semua data terkait (kelas, tahun, mapel, dll)
            $instansi->delete();

            // Bersihin orang_tua yang HANYA punya anak di sekolah ini (tidak punya anak di sekolah lain)
            $orphanOrtuIds = $ortuIds->diff($parentsWithOtherChildren);
            OrangTua::whereIn('id_ortu', $orphanOrtuIds)->each(fn($ortu) => $ortu->user?->delete());
            OrangTua::whereIn('id_ortu', $orphanOrtuIds)->delete();
        });

        return redirect()->route('superadmin.sekolah.index')
            ->with('warning', "Sekolah beserta seluruh data (siswa, guru, kelas, absensi, dll) berhasil dihapus permanen!");
    }

    public function assignAdmin(Instansi $instansi)
    {
        $currentAdmins = User::where('instansi_id', $instansi->id_instansi)
            ->role('admin')
            ->get(['id', 'name', 'email']);

        return view('superadmin.sekolah.assign-admin', compact('instansi', 'currentAdmins'));
    }

    public function storeAdmin(Request $request, Instansi $instansi)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:8',
        ]);

        DB::transaction(function () use ($validated, $instansi) {
            $user = User::create([
                'name'         => $validated['name'],
                'email'        => $validated['email'],
                'password'     => Hash::make($validated['password']),
                'instansi_id'  => $instansi->id_instansi,
            ]);

            $user->assignRole('admin');
        });

        return redirect()->route('superadmin.sekolah.assign-admin', $instansi->id_instansi)
            ->with('success', 'Admin berhasil ditambahkan ke sekolah!');
    }

    public function editAdmin(Instansi $instansi, User $user)
    {
        abort_if($user->instansi_id !== $instansi->id_instansi || !$user->hasRole('admin'), 404);

        return view('superadmin.sekolah.edit-admin', compact('instansi', 'user'));
    }

    public function updateAdmin(Request $request, Instansi $instansi, User $user)
    {
        abort_if($user->instansi_id !== $instansi->id_instansi || !$user->hasRole('admin'), 404);

        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|min:8',
        ]);

        $updateData = ['name' => $validated['name'], 'email' => $validated['email']];
        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        return redirect()->route('superadmin.sekolah.assign-admin', $instansi->id_instansi)
            ->with('success', 'Admin berhasil diperbarui!');
    }

    public function destroyAdmin(Instansi $instansi, User $user)
    {
        abort_if($user->instansi_id !== $instansi->id_instansi || !$user->hasRole('admin'), 404);

        $user->delete();

        return redirect()->route('superadmin.sekolah.assign-admin', $instansi->id_instansi)
            ->with('success', 'Admin berhasil dihapus!');
    }
}
