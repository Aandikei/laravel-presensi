<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateExport;
use App\Models\Absensi;
use App\Models\ExportJob;
use App\Models\HariLibur;
use App\Models\Jadwal;
use App\Models\KurikulumKelas;
use App\Models\Kelas;
use App\Models\RegistrasiAkademik;
use App\Models\RekapBulanan;
use App\Models\MataPelajaran;
use App\Models\TahunAjaran;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AbsensiController extends Controller
{
    public function index()
    {
        $guru = Auth::user()->guru;
        $hariMap = [
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu',
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
                $sudahInput = $jadwal->absensi()
                    ->where('tanggal', now()->toDateString())
                    ->exists();
                $jadwal->sudah_input = $sudahInput;

                return $jadwal;
            });

        return view('guru.absensi.index', compact('jadwalHariIni', 'hariIni'));
    }

    public function input(Jadwal $jadwal)
    {
        $guru = Auth::user()->guru;

        // Pastiin jadwal ini milik guru yang login & di sekolahnya
        abort_if($jadwal->kurikulum->guru_id !== $guru->id_guru, 403);
        abort_if($jadwal->kurikulum->kelas->instansi_id !== $guru->instansi_id, 403);

        // Cek hari libur
        $instansi = Auth::user()->getInstansi();
        $namaLibur = HariLibur::getNamaLibur(
            now()->toDateString(),
            $instansi->id_instansi
        );

        // Cek batas waktu input
        $graceMinutes = config('absensi.auto_lock_grace_minutes', 30);
        $batasAkhir = Carbon::parse($jadwal->jam_selesai)->addMinutes($graceMinutes);

        $locked = $jadwal->absensi()
            ->where('tanggal', now()->toDateString())
            ->where('is_locked', true)
            ->exists();

        if (now()->greaterThanOrEqualTo($batasAkhir)) {
            if ($locked) {
                // Udah terkunci & lewat grace → normal, tolak akses
                return redirect()->route('guru.absensi.index')
                    ->with('error', 'Waktu input absensi sudah berakhir.');
            }

            // Belum terkunci tapi lewat grace
            // Cek apakah ada data (admin buka kunci) atau kosong (telat input)
            $hasData = $jadwal->absensi()->where('tanggal', today())->exists();
            if (!$hasData) {
                return redirect()->route('guru.absensi.index')
                    ->with('error', 'Waktu input absensi sudah berakhir.');
            }
            // Ada data & ga terkunci → admin baru buka kunci → lanjut ke form
        }

        if (now()->lessThan(Carbon::parse($jadwal->jam_mulai))) {
            return redirect()->route('guru.absensi.index')
                ->with('error', 'Belum waktunya input absensi.');
        }

        // Ambil siswa di kelas ini (tahun ajaran aktif)
        $registrasi = RegistrasiAkademik::with('siswa')
            ->aktif()
            ->where('kelas_id', $jadwal->kurikulum->kelas_id)
            ->whereHas('tahunAjaran', fn ($q) => $q->where('is_aktif', true))
            ->orderBy('id_registrasi');

        // Mode edit: hanya siswa aktif. Mode locked (histori): tampilkan semua
        if (!$locked) {
            $registrasi->whereHas('siswa', fn ($q) => $q->whereNull('status'));
        }

        $registrasi = $registrasi->get();

        // Ambil absensi yang sudah ada hari ini
        $absensiHariIni = $jadwal->absensi()
            ->where('tanggal', now()->toDateString())
            ->pluck('status', 'reg_id');

        return view('guru.absensi.input', compact('jadwal', 'registrasi', 'absensiHariIni', 'namaLibur', 'locked'));
    }

    public function store(Request $request, Jadwal $jadwal)
    {
        $guru = Auth::user()->guru;
        abort_if($jadwal->kurikulum->guru_id !== $guru->id_guru, 403);
        abort_if($jadwal->kurikulum->kelas->instansi_id !== $guru->instansi_id, 403);

        $instansi = Auth::user()->getInstansi();
        $namaLibur = HariLibur::getNamaLibur(now()->toDateString(), $instansi->id_instansi);
        if ($namaLibur) {
            return redirect()->route('guru.absensi.index')
                ->with('error', "Hari ini adalah hari libur: {$namaLibur}. Absensi tidak bisa diinput.");
        }

        $graceMinutes = config('absensi.auto_lock_grace_minutes', 30);
        $batasAkhir = Carbon::parse($jadwal->jam_selesai)->addMinutes($graceMinutes);

        $locked = $jadwal->absensi()
            ->where('tanggal', now()->toDateString())
            ->where('is_locked', true)
            ->exists();

        if ($locked) {
            return redirect()->route('guru.absensi.index')
                ->with('error', 'Absensi sudah dikunci oleh admin! Hubungi admin untuk membuka kunci.');
        }

        // Past grace + tidak ada data sama sekali → tolak (guru telat input)
        if (now()->greaterThanOrEqualTo($batasAkhir)) {
            $hasData = $jadwal->absensi()->where('tanggal', today())->exists();
            if (!$hasData) {
                return redirect()->route('guru.absensi.index')
                    ->with('error', 'Waktu input absensi sudah berakhir. Hubungi admin untuk membuka kunci.');
            }
        }

        $request->validate([
            'absensi' => 'required|array',
            'absensi.*.status' => 'required|in:Hadir,Sakit,Izin,Alpa,Terlambat,Bolos',
            'absensi.*.keterangan' => 'nullable|string|max:255',
            'absensi.*.durasi_terlambat' => 'nullable|integer|min:0|max:999',
        ]);

        DB::transaction(function () use ($request, $jadwal) {
            $tanggal = now()->toDateString();

            foreach ($request->absensi as $regId => $data) {
                $absensi = Absensi::firstOrNew([
                    'reg_id' => $regId,
                    'jadwal_id' => $jadwal->id_jadwal,
                    'tanggal' => $tanggal,
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

                // Update rekap bulanan
                $this->updateRekap($regId, $tanggal);
            }
        });

        // Auto-lock jika sudah lewat jam_selesai + grace period
        if (now()->greaterThanOrEqualTo($batasAkhir)) {
            Absensi::where('jadwal_id', $jadwal->id_jadwal)
                ->where('tanggal', today())
                ->where('is_locked', false)
                ->update(['is_locked' => true]);
        }

        return redirect()->route('guru.absensi.index')
            ->with('success', 'Absensi berhasil disimpan!');
    }

    public function rekap(Request $request)
    {
        $guru = Auth::user()->guru;
        $instansi = Auth::user()->getInstansi();

        $mapelId = $request->input('mapel_id');
        $bulan = $request->input('bulan', now()->month);
        $tahun = $request->input('tahun', now()->year);
        $tingkat = $request->input('tingkat');
        $jurusan = $request->input('jurusan');

        $myCombos = KurikulumKelas::where('guru_id', $guru->id_guru)
            ->whereHas('kelas', fn ($q) => $q->where('instansi_id', $guru->instansi_id))
            ->select('kelas_id', 'mapel_id')
            ->get();

        $tahunAktif = TahunAjaran::where('instansi_id', $guru->instansi_id)->where('is_aktif', true)->first();

        $mapels = MataPelajaran::where('instansi_id', $guru->instansi_id)
            ->whereHas('kurikulum', fn ($q) => $q->where('guru_id', $guru->id_guru)
                ->whereHas('kelas', fn ($qq) => $qq->where('instansi_id', $guru->instansi_id))
            )
            ->get();

        $tingkatList = Kelas::where('instansi_id', $instansi->id_instansi)
            ->selectRaw('DISTINCT tingkat')->pluck('tingkat')->sort()->values();

        $jurusanList = collect();
        if ($instansi->jenjang === 'SMA') {
            $jurusanList = Kelas::where('instansi_id', $instansi->id_instansi)
                ->selectRaw('DISTINCT SUBSTRING_INDEX(SUBSTRING_INDEX(nama_kelas, " ", 2), " ", -1) as jurusan')
                ->pluck('jurusan')->sort()->values();
        }

        if ($myCombos->isEmpty()) {
            return view('guru.absensi.rekap', array_merge(
                compact('guru', 'mapels', 'mapelId', 'bulan', 'tahun', 'tingkat', 'jurusan', 'tingkatList', 'jurusanList'),
                ['riwayat' => collect()]
            ));
        }

        $filterJadwalIds = DB::table('jadwal')
            ->join('kurikulum_kelas', 'jadwal.kurikulum_id', '=', 'kurikulum_kelas.id_kurikulum')
            ->where(function ($q) use ($myCombos) {
                foreach ($myCombos as $combo) {
                    $q->orWhere(function ($qq) use ($combo) {
                        $qq->where('kurikulum_kelas.kelas_id', $combo->kelas_id)
                           ->where('kurikulum_kelas.mapel_id', $combo->mapel_id);
                    });
                }
            })
            ->pluck('jadwal.id_jadwal');

        $semesterMulai = $tahunAktif?->tanggal_mulai?->startOfDay();
        $semesterSelesai = $tahunAktif?->tanggal_selesai?->endOfDay();

        $riwayat = Absensi::selectRaw('
                jadwal_id,
                tanggal,
                COUNT(*) as total_siswa,
                SUM(status = "Hadir") as hadir,
                SUM(status = "Sakit") as sakit,
                SUM(status = "Izin") as izin,
                SUM(status = "Alpa") as alpa,
                SUM(status = "Terlambat") as terlambat,
                SUM(status = "Bolos") as bolos
            ')
            ->whereIn('jadwal_id', $filterJadwalIds)
            ->when($semesterMulai && $semesterSelesai, fn ($q) => $q->whereBetween('tanggal', [$semesterMulai, $semesterSelesai]))
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->when($mapelId, fn ($q) => $q->whereHas('jadwal.kurikulum', fn ($qq) => $qq->where('mapel_id', $mapelId)))
            ->when($tingkat, fn ($q) => $q->whereHas('jadwal.kurikulum.kelas', fn ($qq) => $qq->where('tingkat', $tingkat)))
            ->when($jurusan, fn ($q) => $q->whereHas('jadwal.kurikulum.kelas', fn ($qq) => $qq->where('nama_kelas', 'like', '% ' . $jurusan . ' %')))
            ->groupBy('jadwal_id', 'tanggal')
            ->orderBy('tanggal', 'desc')
            ->orderBy('jadwal_id')
            ->get();

        $jadwalIds = $riwayat->pluck('jadwal_id')->unique();
        $jadwals = Jadwal::with(['kurikulum.kelas', 'kurikulum.mataPelajaran', 'kurikulum.guru'])
            ->whereIn('id_jadwal', $jadwalIds)
            ->get()
            ->keyBy('id_jadwal');

        $riwayat = $riwayat->transform(function ($item) use ($jadwals) {
            $j = $jadwals->get($item->jadwal_id);
            $item->kelas_nama  = $j?->kurikulum?->kelas?->nama_kelas ?? '-';
            $item->mapel_nama  = $j?->kurikulum?->mataPelajaran?->nama_mapel ?? '-';
            $item->jam         = $j ? (substr($j->jam_mulai, 0, 5) . ' - ' . substr($j->jam_selesai, 0, 5)) : '-';
            $item->guru        = $j?->kurikulum?->guru;
            $item->guru_nama   = $item->guru?->nama_guru ?? '-';
            return $item;
        });

        return view('guru.absensi.rekap', compact('riwayat', 'guru', 'mapels', 'mapelId', 'bulan', 'tahun', 'tingkat', 'jurusan', 'tingkatList', 'jurusanList'));
    }

    public function detailRekap(Request $request)
    {
        $request->validate([
            'jadwal_id' => 'required|exists:jadwal,id_jadwal',
            'tanggal'   => 'required|date',
        ]);

        $guru = Auth::user()->guru;
        $jadwal = Jadwal::with(['kurikulum.kelas', 'kurikulum.mataPelajaran', 'kurikulum.guru'])
            ->findOrFail($request->jadwal_id);

        abort_if($jadwal->kurikulum->kelas->instansi_id !== $guru->instansi_id, 403);

        $absensi = Absensi::with('registrasi.siswa')
            ->where('jadwal_id', $request->jadwal_id)
            ->whereDate('tanggal', $request->tanggal)
            ->orderBy('status')
            ->get();

        return view('guru.absensi.rekap-detail', compact('jadwal', 'absensi', 'guru'));
    }

    public function exportRekap(Request $request)
    {
        $guru = Auth::user()->guru;

        $exportJob = ExportJob::create([
            'user_id' => Auth::id(),
            'type'    => 'guru-rekap-excel',
            'source'  => 'guru',
            'filters' => [
                'guru_id'    => $guru->id_guru,
                'instansi_id'=> $guru->instansi_id,
                'bulan'      => $request->input('bulan', now()->month),
                'tahun'      => $request->input('tahun', now()->year),
                'mapel_id'   => $request->input('mapel_id'),
                'tingkat'    => $request->input('tingkat'),
                'jurusan'    => $request->input('jurusan'),
            ],
            'status'  => 'pending',
        ]);

        GenerateExport::dispatch($exportJob);

        return redirect()->route('guru.absensi.rekap', $request->only(['bulan', 'tahun', 'mapel_id']))
            ->with('info', 'Export Excel sedang diproses. Cek "Export Saya" di halaman Laporan.');
    }

    private function updateRekap(int $regId, string $tanggal): void
    {
        $bulan = (int) date('m', strtotime($tanggal));
        $tahun = (int) date('Y', strtotime($tanggal));

        // Hitung ulang dari data absensi
        $counts = Absensi::where('reg_id', $regId)
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        RekapBulanan::updateOrCreate(
            ['reg_id' => $regId, 'bulan' => $bulan, 'tahun' => $tahun],
            [
                'hadir' => $counts['Hadir'] ?? 0,
                'sakit' => $counts['Sakit'] ?? 0,
                'izin' => $counts['Izin'] ?? 0,
                'alpa' => $counts['Alpa'] ?? 0,
                'bolos' => $counts['Bolos'] ?? 0,
                'terlambat' => $counts['Terlambat'] ?? 0,
            ]
        );
    }
}
