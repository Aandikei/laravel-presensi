<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Instansi;
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
            return redirect()->route('superadmin.dashboard');
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
                $show = '<a href="' . route('superadmin.sekolah.show', $row->id_instansi) . '"
                    class="px-3 py-1 text-xs font-medium text-white bg-green-600 rounded hover:bg-green-700">
                    Detail</a> ';
                $edit = '<a href="' . route('superadmin.sekolah.edit', $row->id_instansi) . '"
                    class="px-3 py-1 text-xs font-medium text-white bg-blue-600 rounded hover:bg-blue-700">
                    Edit</a> ';
                $delete = '<form method="POST" action="' . route('superadmin.sekolah.destroy', $row->id_instansi) . '" class="inline">
                    <input type="hidden" name="_token" value="' . csrf_token() . '">
                    <input type="hidden" name="_method" value="DELETE">
                    <button type="submit"
                        class="px-3 py-1 text-xs font-medium text-white bg-red-600 rounded hover:bg-red-700"
                        onclick="return confirm(\'Yakin hapus sekolah ini?\')">
                        Hapus</button>
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
            'jenjang'       => 'required|in:SD,SMP,SMA,SMK',
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

        return redirect()->route('superadmin.dashboard')
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
            'jenjang'       => 'required|in:SD,SMP,SMA,SMK',
            'npsn'          => 'required|string|unique:instansi,npsn,' . $instansi->id_instansi . ',id_instansi',
            'alamat'        => 'nullable|string',
            'telepon'       => 'nullable|string|max:20',
            'email'         => 'nullable|email|max:255',
        ]);

        $instansi->update($validated);

        return redirect()->route('superadmin.dashboard')
            ->with('success', 'Data sekolah berhasil diperbarui!');
    }

    public function destroy(Instansi $instansi)
    {
        $instansi->delete();

        return redirect()->route('superadmin.dashboard')
            ->with('success', 'Sekolah berhasil dihapus!');
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
}
