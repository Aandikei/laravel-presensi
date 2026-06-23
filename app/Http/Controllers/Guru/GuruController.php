<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\HariLibur;
use App\Models\Jadwal;
use App\Models\LogPoinSiswa;
use App\Models\MasterPoin;
use App\Models\RegistrasiAkademik;
use App\Models\Siswa;
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

        $jadwalHariIni = Jadwal::with(['kurikulum.kelas', 'kurikulum.mataPelajaran'])
            ->whereHas('kurikulum', fn ($q) => $q->where('guru_id', $guru->id_guru)
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

        $isWaliKelas = Auth::user()->hasRole('wali_kelas') || $guru->isWaliKelas();
        $viewData = compact('guru', 'jadwalHariIni', 'hariIni', 'namaLibur', 'isWaliKelas');
        if ($isWaliKelas) {
            $kelasSaya = $guru->kelasWali()->with(['instansi'])->first();
            if ($kelasSaya) {
                $siswaIds = RegistrasiAkademik::where('kelas_id', $kelasSaya->id_kelas)
                    ->aktif()
                    ->whereRaw('tahun_id = (SELECT MAX(r2.tahun_id) FROM registrasi_akademik r2 WHERE r2.siswa_id = registrasi_akademik.siswa_id AND r2.status = ?)', ['Aktif'])
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
