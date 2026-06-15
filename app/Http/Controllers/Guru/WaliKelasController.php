<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\LogPoinSiswa;
use App\Models\MasterPoin;
use App\Models\RegistrasiAkademik;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WaliKelasController extends Controller
{
    public function siswaByKelas(Kelas $kelas)
    {
        $guru = Auth::user()->guru;
        abort_if($kelas->guru_wali_id !== $guru->id_guru, 403);

        $siswa = RegistrasiAkademik::with('siswa')
            ->aktif()
            ->where('kelas_id', $kelas->id_kelas)
            ->whereHas('tahunAjaran', fn($q) => $q->where('is_aktif', true))
            ->get()
            ->map(function ($reg) {
                return $reg->siswa;
            })
            ->filter();

        return response()->json($siswa->values());
    }

    public function tambahPoin(Request $request)
    {
        $guru = Auth::user()->guru;

        $validated = $request->validate([
            'siswa_id'   => 'required|exists:siswa,id_siswa',
            'poin_id'    => 'required|exists:master_poin,id_poin',
            'keterangan' => 'nullable|string|max:255',
        ]);

        $siswa = Siswa::findOrFail($validated['siswa_id']);

        $kelasSaya = Kelas::where('guru_wali_id', $guru->id_guru)->first();
        abort_if(!$kelasSaya, 403);

        $isSiswaSaya = RegistrasiAkademik::where('kelas_id', $kelasSaya->id_kelas)
            ->where('siswa_id', $siswa->id_siswa)
            ->aktif()
            ->whereHas('tahunAjaran', fn($q) => $q->where('is_aktif', true))
            ->exists();
        abort_if(!$isSiswaSaya, 403);

        LogPoinSiswa::create([
            'siswa_id'   => $validated['siswa_id'],
            'poin_id'    => $validated['poin_id'],
            'tanggal'    => now()->toDateString(),
            'keterangan' => $validated['keterangan'] ?? null,
            'created_by' => Auth::id(),
        ]);

        return back()->with('success', 'Poin berhasil ditambahkan ke ' . $siswa->nama_siswa);
    }

    public function daftarSiswa()
    {
        $guru = Auth::user()->guru;
        $kelasSaya = Kelas::where('guru_wali_id', $guru->id_guru)->first();
        abort_if(!$kelasSaya, 403);

        $siswa = Siswa::whereHas('registrasiAkademik', function ($q) use ($kelasSaya) {
                $q->aktif()
                  ->where('kelas_id', $kelasSaya->id_kelas)
                  ->whereHas('tahunAjaran', fn($qq) => $qq->where('is_aktif', true));
            })
            ->with(['logPoin.masterPoin'])
            ->get()
            ->map(function ($s) {
                $s->total_poin = $s->logPoin->sum(fn($lp) => $lp->masterPoin?->jumlah_poin ?? 0);
                return $s;
            })
            ->sortByDesc('total_poin')
            ->values();

        $masterPoin = MasterPoin::where('instansi_id', $kelasSaya->instansi_id)
            ->orderBy('nama_pelanggaran')
            ->get();

        return view('guru.wali-kelas.siswa-poin', compact('kelasSaya', 'siswa', 'masterPoin'));
    }

    public function logPoin()
    {
        $guru = Auth::user()->guru;
        $kelasSaya = Kelas::where('guru_wali_id', $guru->id_guru)->first();
        abort_if(!$kelasSaya, 403);

        $siswaIds = RegistrasiAkademik::where('kelas_id', $kelasSaya->id_kelas)
            ->aktif()
            ->whereRaw('tahun_id = (SELECT MAX(r2.tahun_id) FROM registrasi_akademik r2 WHERE r2.siswa_id = registrasi_akademik.siswa_id AND r2.status = ?)', ['Aktif'])
            ->pluck('siswa_id');

        $logPoin = LogPoinSiswa::with(['siswa', 'masterPoin', 'createdBy'])
            ->whereIn('siswa_id', $siswaIds)
            ->orderBy('tanggal', 'desc')
            ->orderBy('id_log_poin', 'desc')
            ->paginate(30);

        $masterPoin = MasterPoin::where('instansi_id', $kelasSaya->instansi_id)
            ->orderBy('nama_pelanggaran')
            ->get();

        return view('guru.wali-kelas.log-poin', compact('kelasSaya', 'logPoin', 'masterPoin'));
    }

    public function hapusPoin($id)
    {
        $guru = Auth::user()->guru;
        $logPoin = LogPoinSiswa::with(['siswa'])->findOrFail($id);

        $kelasSaya = Kelas::where('guru_wali_id', $guru->id_guru)->first();
        abort_if(!$kelasSaya, 403);

        $isSiswaSaya = RegistrasiAkademik::where('kelas_id', $kelasSaya->id_kelas)
            ->where('siswa_id', $logPoin->siswa_id)
            ->aktif()
            ->whereHas('tahunAjaran', fn($q) => $q->where('is_aktif', true))
            ->exists();
        abort_if(!$isSiswaSaya, 403);

        $logPoin->delete();

        return back()->with('success', 'Poin berhasil dihapus dari ' . ($logPoin->siswa->nama_siswa ?? '-'));
    }
}
