<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\HariLibur;
use App\Models\Jadwal;
use Illuminate\Support\Facades\Auth;

class GuruController extends Controller
{
    public function index()
    {
        $guru = Auth::user()->guru;

        // Jadwal hari ini
        $hariIni = now()->locale('id')->dayName;
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
                // Cek apakah sudah diinput hari ini
                $sudahInput = $jadwal->absensi()
                    ->where('tanggal', now()->toDateString())
                    ->exists();
                $jadwal->sudah_input = $sudahInput;

                return $jadwal;
            });

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

        return view('guru.dashboard', compact('guru', 'jadwalHariIni', 'hariIni', 'namaLibur'));
    }
}
