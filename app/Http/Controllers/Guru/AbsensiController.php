<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\HariLibur;
use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\RegistrasiAkademik;
use App\Models\RekapBulanan;
use App\Exports\RekapAbsensiExport;
use App\Models\MataPelajaran;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

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
            ->whereHas('kurikulum', fn ($q) => $q->where('guru_id', $guru->id_guru))
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

        // Pastiin jadwal ini milik guru yang login
        abort_if($jadwal->kurikulum->guru_id !== $guru->id_guru, 403);

        // Cek hari libur
        $instansi = Auth::user()->getInstansi();
        $namaLibur = HariLibur::getNamaLibur(
            now()->toDateString(),
            $instansi->id_instansi
        );

        $locked = $jadwal->absensi()
            ->where('tanggal', now()->toDateString())
            ->where('is_locked', true)
            ->exists();

        // Ambil semua siswa aktif di kelas ini (tahun ajaran aktif)
        $registrasi = RegistrasiAkademik::with('siswa')
            ->aktif()
            ->where('kelas_id', $jadwal->kurikulum->kelas_id)
            ->whereHas('tahunAjaran', fn ($q) => $q->where('is_aktif', true))
            ->orderBy('id_registrasi')
            ->get();

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

        $instansi = Auth::user()->getInstansi();
        $namaLibur = HariLibur::getNamaLibur(now()->toDateString(), $instansi->id_instansi);
        if ($namaLibur) {
            return redirect()->route('guru.absensi.index')
                ->with('error', "Hari ini adalah hari libur: {$namaLibur}. Absensi tidak bisa diinput.");
        }

        $locked = $jadwal->absensi()
            ->where('tanggal', now()->toDateString())
            ->where('is_locked', true)
            ->exists();

        if ($locked) {
            return redirect()->route('guru.absensi.index')
                ->with('error', 'Absensi sudah dikunci oleh admin! Hubungi admin untuk membuka kunci.');
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

        return redirect()->route('guru.absensi.index')
            ->with('success', 'Absensi berhasil disimpan!');
    }

    public function rekap(Request $request)
    {
        $guru = Auth::user()->guru;

        $mapelId = $request->input('mapel_id');
        $bulan = $request->input('bulan', now()->month);
        $tahun = $request->input('tahun', now()->year);
        $tahunAjaranAktif = TahunAjaran::where('is_aktif', true)
            ->where('instansi_id', $guru->instansi_id)
            ->first();

        $riwayat = Absensi::with([
                'jadwal.kurikulum.mataPelajaran',
                'jadwal.kurikulum.kelas',
                'registrasi.siswa'
            ])
            ->whereHas('jadwal.kurikulum', fn ($q) => $q->where('guru_id', $guru->id_guru))
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->when($mapelId, fn ($q) => $q->whereHas('jadwal.kurikulum', fn ($qq) => $qq->where('mapel_id', $mapelId)))
            ->orderBy('tanggal', 'desc')
            ->orderBy('jadwal_id')
            ->paginate(50)->withQueryString();

        $kelas = Kelas::whereHas('kurikulum', fn ($q) => $q->where('guru_id', $guru->id_guru))
            ->get();

        $mapels = MataPelajaran::where('instansi_id', $guru->instansi_id)
            ->whereHas('kurikulum', fn ($q) => $q->where('guru_id', $guru->id_guru))
            ->get();

        return view('guru.absensi.rekap', compact('riwayat', 'kelas', 'guru', 'mapels', 'mapelId', 'bulan', 'tahun', 'tahunAjaranAktif'));
    }

    public function exportRekap(Request $request)
    {
        $guru = Auth::user()->guru;

        $bulan   = $request->input('bulan', now()->month);
        $tahun   = $request->input('tahun', now()->year);
        $mapelId = $request->input('mapel_id');

        $namaBulan = \Carbon\Carbon::create()->month((int) $bulan)->locale('id')->monthName;

        return Excel::download(
            new RekapAbsensiExport($guru->id_guru, (int) $bulan, (int) $tahun, $mapelId ? (int) $mapelId : null),
            "rekap-absensi-{$namaBulan}-{$tahun}.xlsx"
        );
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
