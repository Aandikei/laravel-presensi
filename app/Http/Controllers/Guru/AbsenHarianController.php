<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\HariLibur;
use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\RegistrasiAkademik;
use App\Models\RekapBulanan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AbsenHarianController extends Controller
{
    private function hariIni(): string
    {
        $map = [
            'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu',
            'Sunday' => 'Minggu',
        ];
        return $map[now()->format('l')] ?? '';
    }

    public function input(Kelas $kelas)
    {
        $guru = Auth::user()->guru;
        $instansi = Auth::user()->getInstansi();

        abort_if($instansi->jenjang !== 'SD', 403);
        abort_if($kelas->guru_wali_id !== $guru->id_guru, 403);

        $hari = $this->hariIni();

        $jadwalHarian = Jadwal::with('kurikulum.mataPelajaran')
            ->whereHas('kurikulum', fn($q) => $q
                ->where('kelas_id', $kelas->id_kelas)
                ->where('guru_id', $guru->id_guru)
                ->where('jenis_pengajar', 'guru_kelas'))
            ->where('hari', $hari)
            ->orderBy('jam_mulai')
            ->get();

        abort_if($jadwalHarian->isEmpty(), 404, 'Tidak ada jadwal guru kelas hari ini.');

        $firstJadwalId = $jadwalHarian->first()->id_jadwal;
        $mapelList = $jadwalHarian->pluck('kurikulum.mataPelajaran.nama_mapel')->unique()->values();

        $namaLibur = HariLibur::getNamaLibur(now()->toDateString(), $instansi->id_instansi);

        // N+1 prevention: 1 query siswa + 1 query absensi
        $registrasi = RegistrasiAkademik::with('siswa')
            ->aktif()
            ->where('kelas_id', $kelas->id_kelas)
            ->whereHas('tahunAjaran', fn($q) => $q->where('is_aktif', true))
            ->orderBy('id_registrasi')
            ->get();

        $regIds = $registrasi->pluck('id_registrasi');

        $absensiHarian = Absensi::whereIn('reg_id', $regIds)
            ->where('tanggal', now()->toDateString())
            ->where('cakupan', 'harian')
            ->where('jadwal_id', $firstJadwalId)
            ->pluck('status', 'reg_id');

        return view('guru.absen-harian.input', compact(
            'kelas',
            'registrasi',
            'absensiHarian',
            'firstJadwalId',
            'mapelList',
            'namaLibur',
            'jadwalHarian',
        ));
    }

    public function store(Request $request, Kelas $kelas)
    {
        $guru = Auth::user()->guru;
        $instansi = Auth::user()->getInstansi();

        abort_if($instansi->jenjang !== 'SD', 403);
        abort_if($kelas->guru_wali_id !== $guru->id_guru, 403);

        $namaLibur = HariLibur::getNamaLibur(now()->toDateString(), $instansi->id_instansi);
        if ($namaLibur) {
            return redirect()->route('guru.absen-harian.input', $kelas->id_kelas)
                ->with('error', "Hari ini adalah hari libur: {$namaLibur}.");
        }

        $request->validate([
            'absensi' => 'required|array',
            'absensi.*.status' => 'required|in:Hadir,Sakit,Izin,Alpa,Terlambat,Bolos',
            'absensi.*.keterangan' => 'nullable|string|max:255',
            'absensi.*.durasi_terlambat' => 'nullable|integer|min:0|max:999',
        ]);

        $hari = $this->hariIni();
        $firstJadwal = Jadwal::whereHas('kurikulum', fn($q) => $q
                ->where('kelas_id', $kelas->id_kelas)
                ->where('guru_id', $guru->id_guru)
                ->where('jenis_pengajar', 'guru_kelas'))
            ->where('hari', $hari)
            ->orderBy('jam_mulai')
            ->firstOrFail();

        DB::transaction(function () use ($request, $firstJadwal) {
            $tanggal = now()->toDateString();

            foreach ($request->absensi as $regId => $data) {
                $absensi = Absensi::firstOrNew([
                    'reg_id'    => $regId,
                    'jadwal_id' => $firstJadwal->id_jadwal,
                    'tanggal'   => $tanggal,
                    'cakupan'   => 'harian',
                ]);

                if (!$absensi->exists) {
                    $absensi->waktu_input = now();
                    $absensi->created_by = Auth::id();
                }

                $absensi->status = $data['status'];
                $absensi->keterangan = $data['keterangan'] ?? null;
                $absensi->durasi_terlambat = $data['durasi_terlambat'] ?? null;
                $absensi->updated_by = Auth::id();
                $absensi->save();

                $this->updateRekap($regId, $tanggal);
            }
        });

        return redirect()->route('guru.absen-harian.input', $kelas->id_kelas)
            ->with('success', 'Absen harian berhasil disimpan!');
    }

    private function updateRekap(int $regId, string $tanggal): void
    {
        $bulan = (int) date('m', strtotime($tanggal));
        $tahun = (int) date('Y', strtotime($tanggal));

        $counts = Absensi::where('reg_id', $regId)
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        RekapBulanan::updateOrCreate(
            ['reg_id' => $regId, 'bulan' => $bulan, 'tahun' => $tahun],
            [
                'hadir'     => $counts['Hadir'] ?? 0,
                'sakit'     => $counts['Sakit'] ?? 0,
                'izin'      => $counts['Izin'] ?? 0,
                'alpa'      => $counts['Alpa'] ?? 0,
                'bolos'     => $counts['Bolos'] ?? 0,
                'terlambat' => $counts['Terlambat'] ?? 0,
            ]
        );
    }
}
