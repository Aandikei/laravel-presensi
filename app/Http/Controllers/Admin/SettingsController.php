<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    public function index()
    {
        $instansi = Auth::user()->getInstansi();
        return view('admin.settings.index', compact('instansi'));
    }

    public function update(Request $request)
    {
        $instansi = Auth::user()->getInstansi();

        $validated = $request->validate([
            'nama_instansi' => 'required|string|max:255',
            'npsn'          => 'required|string|unique:instansi,npsn,' . $instansi->id_instansi . ',id_instansi',
            'jenjang'       => 'required|in:SD,SMP,SMA,SMK',
            'alamat'        => 'nullable|string',
            'telepon'       => 'nullable|string|max:15',
            'email'         => 'nullable|email',
            'logo'          => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
        ]);

        // Upload logo
        if ($request->hasFile('logo')) {
            // Hapus logo lama
            if ($instansi->logo) {
                Storage::disk('public')->delete($instansi->logo);
            }
            $validated['logo'] = $request->file('logo')->store('logo-instansi', 'public');
        }

        $instansi->update($validated);

        return back()->with('success', 'Pengaturan sekolah berhasil disimpan!');
    }
}