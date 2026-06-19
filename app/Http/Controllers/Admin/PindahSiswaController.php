<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\RegistrasiAkademik;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PindahSiswaController extends Controller
{
    public function formPindah($id)
    {
        $instansi = Auth::user()->getInstansi();
        $siswa = Siswa::with('registrasiAktif.kelas')->findOrFail($id);

        abort_if($siswa->instansi_id !== $instansi->id_instansi, 403);
        abort_if(!$siswa->registrasiAktif, 403, 'Siswa ini tidak memiliki registrasi aktif.');

        return view('admin.siswa.pindah-form', compact('siswa'));
    }

    public function formMasuk(Request $request)
    {
        $instansi = Auth::user()->getInstansi();
        $tahunAktif = TahunAjaran::where('instansi_id', $instansi->id_instansi)
            ->where('is_aktif', true)
            ->first();
        $kelas = Kelas::where('instansi_id', $instansi->id_instansi)
            ->orderBy('tingkat')
            ->orderBy('nama_kelas')
            ->get();

        return view('admin.siswa.pindah-masuk', compact('tahunAktif', 'kelas'));
    }

    public function verifikasi(Request $request)
    {
        $request->validate([
            'nisn' => 'required|string|exists:siswa,nisn',
            'token' => 'required|string|size:6',
        ]);

        $instansi = Auth::user()->getInstansi();

        $siswa = Siswa::with(['instansi', 'registrasiAktif.kelas', 'registrasiAkademik' => function ($q) {
            $q->where('status', 'Pindah')->latest()->limit(1);
        }])->where('nisn', $request->nisn)
            ->firstOrFail();

        $alasanMutasi = $siswa->registrasiAkademik->first()?->alasan_mutasi;

        if ($siswa->instansi_id === $instansi->id_instansi) {
            return back()
                ->withInput()
                ->with('error', "Siswa {$siswa->nama_siswa} sudah terdaftar di sekolah ini.");
        }

        if ($siswa->instansi->jenjang !== $instansi->jenjang) {
            return back()
                ->withInput()
                ->with('error', "Tidak bisa memindahkan siswa dari jenjang {$siswa->instansi->jenjang} ke {$instansi->jenjang}. Pindah hanya untuk jenjang yang sama.");
        }

        if ($siswa->transfer_token !== strtoupper($request->token)) {
            return back()
                ->withInput()
                ->with('error', 'Kode transfer tidak valid. Silakan hubungi sekolah asal.');
        }

        if ($siswa->isTransferTokenExpired()) {
            return back()
                ->withInput()
                ->with('error', 'Kode transfer sudah kedaluwarsa. Minta sekolah asal untuk membuat kode baru.');
        }

        $tahunAktif = TahunAjaran::where('instansi_id', $instansi->id_instansi)
            ->where('is_aktif', true)
            ->first();
        $kelas = Kelas::where('instansi_id', $instansi->id_instansi)
            ->orderBy('tingkat')
            ->orderBy('nama_kelas')
            ->get();

        return view('admin.siswa.pindah-konfirmasi', compact('siswa', 'instansi', 'tahunAktif', 'kelas', 'alasanMutasi'));
    }

    public function prosesMasuk(Request $request)
    {
        $request->validate([
            'siswa_id' => 'required|exists:siswa,id_siswa',
            'token' => 'required|string|size:6',
            'kelas_id' => 'nullable|exists:kelas,id_kelas',
            'tahun_id' => 'nullable|exists:tahun_ajaran,id_tahun',
        ]);

        $instansi = Auth::user()->getInstansi();

        DB::transaction(function () use ($request, $instansi) {
            $siswa = Siswa::with('instansi')->findOrFail($request->siswa_id);

            if ($siswa->transfer_token !== strtoupper($request->token)) {
                abort(422, 'Kode transfer tidak valid.');
            }

            if ($siswa->isTransferTokenExpired()) {
                abort(422, 'Kode transfer sudah kedaluwarsa.');
            }

            if ($siswa->instansi_id === $instansi->id_instansi) {
                abort(422, 'Siswa sudah terdaftar di sekolah ini.');
            }

            if ($siswa->instansi->jenjang !== $instansi->jenjang) {
                abort(422, "Tidak bisa memindahkan siswa dari jenjang {$siswa->instansi->jenjang} ke {$instansi->jenjang}.");
            }

            $registrasiLama = RegistrasiAkademik::where('siswa_id', $siswa->id_siswa)
                ->where('status', 'Pindah')
                ->whereHas('tahunAjaran', fn($q) => $q
                    ->where('is_aktif', true)
                    ->where('instansi_id', $siswa->instansi_id))
                ->first();

            if ($registrasiLama) {
                $registrasiLama->update([
                    'status' => 'Pindah',
                    'tanggal_mutasi' => now(),
                ]);
            }

            $siswa->update([
                'instansi_id' => $instansi->id_instansi,
            ]);
            $siswa->clearTransferToken();

            $siswa->logPoin()->delete();

            if (!empty($request->kelas_id) && !empty($request->tahun_id)) {
                $sudahAda = RegistrasiAkademik::where('siswa_id', $siswa->id_siswa)
                    ->where('tahun_id', $request->tahun_id)
                    ->exists();

                if (!$sudahAda) {
                    RegistrasiAkademik::create([
                        'siswa_id' => $siswa->id_siswa,
                        'kelas_id' => $request->kelas_id,
                        'tahun_id' => $request->tahun_id,
                        'status' => 'Aktif',
                    ]);
                }
            }

            $user = $siswa->user;
            if (!$user->hasRole('siswa')) {
                $user->assignRole('siswa');
            }
        });

        return redirect()->route('admin.siswa.index')
            ->with('success', 'Siswa berhasil dipindahkan ke sekolah ini.');
    }

    public function out(Request $request, $id)
    {
        $request->validate([
            'alasan' => 'required|string|max:255',
        ]);

        $instansi = Auth::user()->getInstansi();
        $siswa = Siswa::with(['registrasiAktif'])->findOrFail($id);

        abort_if($siswa->instansi_id !== $instansi->id_instansi, 403);

        $registrasiAktif = $siswa->registrasiAktif;

        if (!$registrasiAktif) {
            return back()->with('error', 'Siswa ini tidak memiliki registrasi aktif.');
        }

        DB::transaction(function () use ($registrasiAktif, $siswa, $request) {
            $registrasiAktif->update([
                'status' => 'Pindah',
                'tanggal_mutasi' => now(),
                'alasan_mutasi' => $request->alasan,
            ]);

            $siswa->generateTransferToken();
        });

        $siswaSegar = $siswa->fresh();
        return redirect()->route('admin.siswa.index')->with([
            'success' => 'Siswa berhasil ditandai pindah. Kode transfer: ' . $siswaSegar->transfer_token
                . ' (berlaku sampai ' . $siswaSegar->transfer_token_expires_at->format('j M Y H:i') . ')',
            'transfer_token' => $siswaSegar->transfer_token,
        ]);
    }

    public function batal($id)
    {
        $instansi = Auth::user()->getInstansi();
        $siswa = Siswa::findOrFail($id);
        abort_if($siswa->instansi_id !== $instansi->id_instansi, 403);

        DB::transaction(function () use ($siswa) {
            $registrasiPindah = RegistrasiAkademik::where('siswa_id', $siswa->id_siswa)
                ->where('status', 'Pindah')
                ->latest('tanggal_mutasi')
                ->first();

            if ($registrasiPindah) {
                $registrasiPindah->update([
                    'status' => 'Aktif',
                    'tanggal_mutasi' => null,
                    'alasan_mutasi' => null,
                ]);
            }

            $siswa->clearTransferToken();
        });

        return redirect()->route('admin.siswa.index')
            ->with('success', 'Status pindah siswa dibatalkan. Siswa kembali aktif.');
    }
}
