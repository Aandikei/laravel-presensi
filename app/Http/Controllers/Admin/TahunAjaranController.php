<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TahunAjaranController extends Controller
{
    public function index()
    {
        $instansi = Auth::user()->getInstansi();

        $tahunAjaran = TahunAjaran::where('instansi_id', $instansi->id_instansi)
            ->withCount(['registrasiAkademik' => fn ($q) => $q->aktif()])
            ->orderByDesc('is_aktif')
            ->orderByDesc('created_at')
            ->get();

        $activeNow = $tahunAjaran->firstWhere('is_aktif', true);
        $activeHasRegistrasi = $activeNow ? $activeNow->registrasi_akademik_count > 0 : false;

        $tahunAjaran->each(function ($item) use ($activeNow, $activeHasRegistrasi) {
            $item->can_activate = true;

            if ($activeNow && $activeNow->id_tahun !== $item->id_tahun) {
                $selisih = $activeNow->tahun_mulai - $item->tahun_mulai;

                if ($selisih > 1) {
                    $item->can_activate = false;
                } elseif ($selisih === 1) {
                    $item->can_activate = !$activeHasRegistrasi;
                } elseif ($selisih === 0 && $activeNow->semester === 'Genap' && $item->semester === 'Ganjil') {
                    $item->can_activate = !$activeHasRegistrasi;
                }
            }
        });

        return view('admin.tahun-ajaran.index', compact('tahunAjaran'));
    }

    public function create()
    {
        return view('admin.tahun-ajaran.create');
    }

    public function store(Request $request)
    {
        $instansi = Auth::user()->getInstansi();

        $validated = $request->validate([
            'nama_tahun'      => ['required', 'string', 'regex:/^\d{4}\/\d{4}$/'],
            'semester'        => 'required|in:Ganjil,Genap',
            'tanggal_mulai'   => 'required|date',
            'tanggal_selesai' => 'required|date|after:tanggal_mulai',
        ]);

        [$tahun1, $tahun2] = explode('/', $validated['nama_tahun']);
        if ((int)$tahun2 !== (int)$tahun1 + 1) {
            return back()->withErrors([
                'nama_tahun' => 'Format tahun ajaran tidak valid. Contoh: 2026/2027'
            ])->withInput();
        }

        // Cek duplikat
        $exists = TahunAjaran::where('instansi_id', $instansi->id_instansi)
            ->where('nama_tahun', $validated['nama_tahun'])
            ->where('semester', $validated['semester'])
            ->exists();

        if ($exists) {
            return back()->withErrors([
                'nama_tahun' => 'Tahun ajaran dan semester ini sudah ada.'
            ])->withInput();
        }

        $hasActive = TahunAjaran::where('instansi_id', $instansi->id_instansi)
            ->where('is_aktif', true)
            ->exists();

        TahunAjaran::create([
            ...$validated,
            'instansi_id' => $instansi->id_instansi,
            'is_aktif'    => !$hasActive,
        ]);

        return redirect()->route('admin.tahun-ajaran.index')
            ->with('success', 'Tahun ajaran berhasil ditambahkan!');
    }

    public function edit(TahunAjaran $tahunAjaran)
    {
        $this->authorizeInstansi($tahunAjaran);
        return view('admin.tahun-ajaran.edit', compact('tahunAjaran'));
    }

    public function update(Request $request, TahunAjaran $tahunAjaran)
    {
        $this->authorizeInstansi($tahunAjaran);

        $validated = $request->validate([
            'nama_tahun'      => ['required', 'string', 'regex:/^\d{4}\/\d{4}$/'],
            'semester'        => 'required|in:Ganjil,Genap',
            'tanggal_mulai'   => 'required|date',
            'tanggal_selesai' => 'required|date|after:tanggal_mulai',
        ]);

        [$tahun1, $tahun2] = explode('/', $validated['nama_tahun']);
        if ((int)$tahun2 !== (int)$tahun1 + 1) {
            return back()->withErrors([
                'nama_tahun' => 'Format tahun ajaran tidak valid. Contoh: 2026/2027'
            ])->withInput();
        }

        $tahunAjaran->update($validated);

        return redirect()->route('admin.tahun-ajaran.index')
            ->with('success', 'Tahun ajaran berhasil diupdate!');
    }

    public function destroy(TahunAjaran $tahunAjaran)
    {
        $this->authorizeInstansi($tahunAjaran);

        if ($tahunAjaran->is_aktif) {
            return back()->with('error', 'Tidak bisa hapus tahun ajaran yang sedang aktif!');
        }

        $tahunAjaran->delete();

        return redirect()->route('admin.tahun-ajaran.index')
            ->with('success', 'Tahun ajaran berhasil dihapus!');
    }

    public function aktivasi(TahunAjaran $tahunAjaran)
    {
        $this->authorizeInstansi($tahunAjaran);
        $instansi = Auth::user()->getInstansi();

        $activeNow = TahunAjaran::where('instansi_id', $instansi->id_instansi)
            ->where('is_aktif', true)
            ->first();

        if ($activeNow && $activeNow->id_tahun !== $tahunAjaran->id_tahun) {
            $selisih = $activeNow->tahun_mulai - $tahunAjaran->tahun_mulai;

            if ($selisih > 1) {
                return back()->with('error', "Tidak bisa mengaktifkan {$tahunAjaran->nama_tahun} karena {$activeNow->nama_tahun} sudah aktif dan lebih baru 2 tahun atau lebih.");
            }

            if ($selisih === 1) {
                $hasRegistrasi = $activeNow->registrasiAkademik()->aktif()->exists();
                if ($hasRegistrasi) {
                    return back()->with('error', "Tidak bisa mengaktifkan {$tahunAjaran->nama_tahun} karena {$activeNow->nama_tahun} sudah memiliki data registrasi siswa.");
                }
            }

            if ($selisih === 0 && $activeNow->semester === 'Genap' && $tahunAjaran->semester === 'Ganjil') {
                $hasRegistrasi = $activeNow->registrasiAkademik()->aktif()->exists();
                if ($hasRegistrasi) {
                    return back()->with('error', "Tidak bisa mengaktifkan {$tahunAjaran->nama_tahun} Ganjil karena {$activeNow->nama_tahun} Genap sudah memiliki data registrasi siswa.");
                }
            }
        }

        // Nonaktifkan semua tahun ajaran instansi ini
        TahunAjaran::where('instansi_id', $instansi->id_instansi)
            ->update(['is_aktif' => false]);

        // Aktifkan yang dipilih
        $tahunAjaran->update(['is_aktif' => true]);

        TahunAjaran::flushAktifCache($instansi->id_instansi);

        return back()->with('success', "Tahun ajaran {$tahunAjaran->nama_tahun} {$tahunAjaran->semester} berhasil diaktifkan!");
    }

    // Pastiin admin hanya bisa akses data instansinya sendiri
    private function authorizeInstansi(TahunAjaran $tahunAjaran): void
    {
        $instansi = Auth::user()->getInstansi();
        abort_if($tahunAjaran->instansi_id !== $instansi->id_instansi, 403);
    }
}