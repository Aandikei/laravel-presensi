<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\HariLibur;
use App\Models\Jadwal;
use App\Models\LogPoinSiswa;
use App\Models\MasterPoin;
use App\Models\RegistrasiAkademik;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use Illuminate\Support\Facades\Auth;

class GuruController extends Controller
{
    public function index()
    {
        $guru = Auth::user()->guru;

        $hariMap = [
            'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu',
            'Sunday' => 'Minggu',
        ];
        $hariIni = $hariMap[now()->format('l')] ?? null;

        // ── Per-jam jadwal: hanya guru_mapel ──
        $jadwalHariIni = Jadwal::with(['kurikulum.kelas', 'kurikulum.mataPelajaran'])
            ->whereHas('kurikulum', fn ($q) => $q->where('guru_id', $guru->id_guru)
                ->where('jenis_pengajar', 'guru_mapel')
                ->whereHas('kelas', fn ($qq) => $qq->where('instansi_id', $guru->instansi_id))
            )
            ->where('hari', $hariIni)
            ->orderBy('jam_mulai')
            ->get()
            ->map(function ($jadwal) {
                $jadwal->sudah_input = $jadwal->absensi()
                    ->where('tanggal', now()->toDateString())
                    ->exists();
                return $jadwal;
            });

        $instansi = Auth::user()->getInstansi();
        $namaLibur = HariLibur::getNamaLibur(now()->toDateString(), $instansi->id_instansi);

        // ── Absen Harian SD: data untuk dashboard card ──
        $kelasGuruKelas = collect();
        if ($instansi->jenjang === 'SD' && $guru->isWaliKelas()) {
            $kelasList = $guru->kelasWali()->with('instansi')->get();
            $kelasIds = $kelasList->pluck('id_kelas');

            $semuaJadwalHarian = Jadwal::with('kurikulum.mataPelajaran')
                ->whereHas('kurikulum', fn($q) => $q
                    ->whereIn('kelas_id', $kelasIds)
                    ->where('guru_id', $guru->id_guru)
                    ->where('jenis_pengajar', 'guru_kelas'))
                ->where('hari', $hariIni)
                ->orderBy('jam_mulai')
                ->get()
                ->groupBy(fn($j) => $j->kurikulum->kelas_id);

            $semuaJadwalIds = $semuaJadwalHarian->flatten()->pluck('id_jadwal');

            $harianExists = $semuaJadwalIds->isNotEmpty()
                ? Absensi::whereIn('jadwal_id', $semuaJadwalIds)
                    ->where('tanggal', now()->toDateString())
                    ->where('cakupan', 'harian')
                    ->exists()
                : false;

            foreach ($kelasList as $kelas) {
                $jadwals = $semuaJadwalHarian->get($kelas->id_kelas);
                if (!$jadwals || $jadwals->isEmpty()) continue;

                $kelas->mapel_list = $jadwals->pluck('kurikulum.mataPelajaran.nama_mapel')->unique()->values();
                $kelas->sudah_absen_harian = $harianExists;
                $kelasGuruKelas->push($kelas);
            }
        }

        // ── Wali kelas stuff (existing) ──
        $isWaliKelas = Auth::user()->hasRole('wali_kelas') || $guru->isWaliKelas();
        $viewData = compact('guru', 'jadwalHariIni', 'hariIni', 'namaLibur', 'isWaliKelas', 'kelasGuruKelas');
        if ($isWaliKelas) {
            $kelasSaya = $guru->kelasWali()->with(['instansi'])->first();
            if ($kelasSaya) {
                $tahunAktif = TahunAjaran::getAktif($guru->instansi_id);
                $siswaIds = RegistrasiAkademik::where('kelas_id', $kelasSaya->id_kelas)
                    ->where('tahun_id', $tahunAktif?->id_tahun)
                    ->where('status', 'Aktif')
                    ->whereHas('siswa', fn($q) => $q->whereNull('status'))
                    ->pluck('siswa_id');
                $jumlahSiswa = $siswaIds->count();

                $totalHadir = 0;
                $totalAbsensi = 0;
                $registrasi = RegistrasiAkademik::with(['absensi' => fn ($q) =>
                    $q->whereMonth('tanggal', now()->month)->whereYear('tanggal', now()->year)
                ])->aktif()->where('kelas_id', $kelasSaya->id_kelas)->get();
                foreach ($registrasi as $reg) {
                    $count = $reg->absensi->count();
                    $totalAbsensi += $count;
                    $totalHadir += $reg->absensi->where('status', 'Hadir')->count();
                }
                $rataKehadiran = $totalAbsensi > 0 ? round(($totalHadir / $totalAbsensi) * 100) : 0;

                $siswaPoinTinggi = Siswa::whereIn('id_siswa', $siswaIds)
                    ->addSelect(['poin_bulan_ini' => LogPoinSiswa::whereColumn('siswa_id', 'siswa.id_siswa')
                        ->where('instansi_id', $instansi->id_instansi)
                        ->whereMonth('tanggal', now()->month)
                        ->whereYear('tanggal', now()->year)
                        ->join('master_poin', 'log_poin_siswa.poin_id', '=', 'master_poin.id_poin')
                        ->selectRaw('COALESCE(SUM(master_poin.jumlah_poin), 0)')
                    ])
                    ->orderBy('poin_bulan_ini', 'desc')
                    ->get()
                    ->filter(fn($s) => $s->poin_bulan_ini > 0)
                    ->take(5)
                    ->values();

                $masterPoin = MasterPoin::where('instansi_id', $instansi->id_instansi)
                    ->orderBy('nama_pelanggaran')->get();

                $viewData['kelasSaya'] = $kelasSaya;
                $viewData['jumlahSiswa'] = $jumlahSiswa;
                $viewData['rataKehadiran'] = $rataKehadiran;
                $viewData['siswaPoinTinggi'] = $siswaPoinTinggi;
                $viewData['masterPoin'] = $masterPoin;
            }
        }

        return view('guru.dashboard', $viewData);
    }
}
