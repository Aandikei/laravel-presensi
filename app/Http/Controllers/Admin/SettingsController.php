<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        $allowedLabelMap = ['SD' => ['SD', 'MI'], 'SMP' => ['SMP', 'MTs'], 'SMA' => ['SMA', 'MA', 'SMK']];
        $allowedLabels = $allowedLabelMap[$request->jenjang] ?? [];

        $validated = $request->validate([
            'nama_instansi' => 'required|string|max:255',
            'npsn'          => 'required|string|unique:instansi,npsn,' . $instansi->id_instansi . ',id_instansi',
            'jenjang'       => 'required|in:SD,SMP,SMA',
            'label_jenjang' => 'nullable|string|in:' . implode(',', $allowedLabels),
            'alamat'        => 'nullable|string',
            'telepon'       => 'nullable|string|max:15',
            'email'         => 'nullable|email',
        ]);

        $instansi->update($validated);

        return back()->with('success', 'Pengaturan sekolah berhasil disimpan!');
    }
}