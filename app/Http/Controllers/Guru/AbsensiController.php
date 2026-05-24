<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\HariLibur;
use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\RegistrasiAkademik;
use App\Models\RekapBulanan;
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

        if ($namaLibur) {
            return redirect()->route('guru.absensi.index')
                ->with('error', "Hari ini adalah hari libur: {$namaLibur}. Absensi tidak bisa diinput.");
        }

        // Cek apakah sudah dikunci
        $sudahLocked = $jadwal->absensi()
            ->where('tanggal', now()->toDateString())
            ->where('is_locked', true)
            ->exists();

        if ($sudahLocked) {
            return redirect()->route('guru.absensi.index')
                ->with('error', 'Absensi ini sudah dikunci oleh admin! Hubungi admin untuk membuka kunci.');
        }

        // Ambil semua siswa di kelas ini (tahun ajaran aktif)
        $registrasi = RegistrasiAkademik::with('siswa')
            ->where('kelas_id', $jadwal->kurikulum->kelas_id)
            ->whereHas('tahunAjaran', fn ($q) => $q->where('is_aktif', true))
            ->orderBy('id_registrasi')
            ->get();

        // Ambil absensi yang sudah ada hari ini
        $absensiHariIni = $jadwal->absensi()
            ->where('tanggal', now()->toDateString())
            ->pluck('status', 'reg_id');

        return view('guru.absensi.input', compact('jadwal', 'registrasi', 'absensiHariIni'));
    }

    public function store(Request $request, Jadwal $jadwal)
    {
        $guru = Auth::user()->guru;
        abort_if($jadwal->kurikulum->guru_id !== $guru->id_guru, 403);

        $request->validate([
            'absensi' => 'required|array',
            'absensi.*.status' => 'required|in:Hadir,Sakit,Izin,Alpa,Terlambat,Cabut',
            'absensi.*.keterangan' => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($request, $jadwal) {
            $tanggal = now()->toDateString();

            foreach ($request->absensi as $regId => $data) {
                $absensi = Absensi::updateOrCreate(
                    [
                        'reg_id' => $regId,
                        'jadwal_id' => $jadwal->id_jadwal,
                        'tanggal' => $tanggal,
                    ],
                    [
                        'status' => $data['status'],
                        'keterangan' => $data['keterangan'] ?? null,
                        'waktu_input' => now(),
                        'created_by' => Auth::id(),
                        'updated_by' => Auth::id(),
                    ]
                );

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

        $kelas = Kelas::whereHas('kurikulum', fn ($q) => $q->where('guru_id', $guru->id_guru))
            ->with('tahunAjaran')
            ->get();

        return view('guru.absensi.rekap', compact('kelas', 'guru'));
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
                'cabut' => $counts['Cabut'] ?? 0,
                'terlambat' => $counts['Terlambat'] ?? 0,
            ]
        );
    }
}
