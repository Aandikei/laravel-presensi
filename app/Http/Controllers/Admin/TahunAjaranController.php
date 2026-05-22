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
            ->orderByDesc('is_aktif')
            ->orderByDesc('created_at')
            ->get();

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
            'nama_tahun'      => 'required|string',
            'semester'        => 'required|in:Ganjil,Genap',
            'tanggal_mulai'   => 'required|date',
            'tanggal_selesai' => 'required|date|after:tanggal_mulai',
        ]);

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

        TahunAjaran::create([
            ...$validated,
            'instansi_id' => $instansi->id_instansi,
            'is_aktif'    => false,
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
            'nama_tahun'      => 'required|string',
            'semester'        => 'required|in:Ganjil,Genap',
            'tanggal_mulai'   => 'required|date',
            'tanggal_selesai' => 'required|date|after:tanggal_mulai',
        ]);

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

        // Nonaktifkan semua tahun ajaran instansi ini
        TahunAjaran::where('instansi_id', $instansi->id_instansi)
            ->update(['is_aktif' => false]);

        // Aktifkan yang dipilih
        $tahunAjaran->update(['is_aktif' => true]);

        return back()->with('success', "Tahun ajaran {$tahunAjaran->nama_tahun} {$tahunAjaran->semester} berhasil diaktifkan!");
    }

    // Pastiin admin hanya bisa akses data instansinya sendiri
    private function authorizeInstansi(TahunAjaran $tahunAjaran): void
    {
        $instansi = Auth::user()->getInstansi();
        abort_if($tahunAjaran->instansi_id !== $instansi->id_instansi, 403);
    }
}