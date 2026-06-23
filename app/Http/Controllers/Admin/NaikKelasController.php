<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\RegistrasiAkademik;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class NaikKelasController extends Controller
{
    public function index()
    {
        $instansi = Auth::user()->getInstansi();

        $tahunAjaran = TahunAjaran::where('instansi_id', $instansi->id_instansi)
            ->orderByDesc('id_tahun')
            ->get();

        $tahunAjaranData = $tahunAjaran->map(fn($t) => [
            'id' => $t->id_tahun,
            'nama_tahun' => $t->nama_tahun,
            'semester' => $t->semester,
            'label' => $t->nama_tahun . ' - ' . $t->semester . ($t->is_aktif ? ' (Aktif)' : ''),
            'is_aktif' => $t->is_aktif,
        ])->values()->toArray();

        return view('admin.naik-kelas.index', compact('tahunAjaran', 'instansi', 'tahunAjaranData'));
    }

    public function preview(Request $request)
    {
        $request->validate([
            'tahun_asal_id' => 'required|exists:tahun_ajaran,id_tahun',
            'tahun_tujuan_id' => 'required|exists:tahun_ajaran,id_tahun|different:tahun_asal_id',
        ]);

        $instansi = Auth::user()->getInstansi();
        $tingkatMaks = $instansi->tingkat_maks;

        $tahunAsal = TahunAjaran::findOrFail($request->tahun_asal_id);
        $tahunTujuan = TahunAjaran::findOrFail($request->tahun_tujuan_id);

        // Ambil semua kelas instansi yang punya siswa terdaftar di tahun asal
        $kelasAsal = Kelas::where('instansi_id', '=', $instansi->id_instansi)
            ->whereHas('registrasiAkademik', fn ($q) => $q
                ->where('tahun_id', '=', $tahunAsal->id_tahun)
                ->where('status', '!=', 'Alumni')
                ->whereHas('siswa', fn ($sq) => $sq->where('instansi_id', $instansi->id_instansi)))
            ->with(['registrasiAkademik' => fn ($q) => $q
                ->where('tahun_id', '=', $tahunAsal->id_tahun)
                ->where('status', '!=', 'Alumni')
                ->whereHas('siswa', fn ($sq) => $sq->where('instansi_id', $instansi->id_instansi)->whereNull('status'))
                ->with('siswa')])
            ->orderBy('tingkat')
            ->orderBy('nama_kelas')
            ->get();

        // Ambil SEMUA kelas instansi (untuk pilihan tujuan), grouped by tingkat
        $semuaKelas = Kelas::where('instansi_id', '=', $instansi->id_instansi)
            ->orderBy('tingkat')
            ->orderBy('nama_kelas')
            ->get()
            ->groupBy('tingkat');

        // Siswa yang sudah terdaftar di tahun tujuan (hanya dari instansi ini)
        $sudahTerdaftar = RegistrasiAkademik::where('tahun_id', '=', $tahunTujuan->id_tahun)
            ->whereHas('kelas', fn($q) => $q->where('instansi_id', $instansi->id_instansi))
            ->aktif()
            ->pluck('siswa_id')
            ->toArray();

        return view('admin.naik-kelas.preview', compact(
            'tahunAsal',
            'tahunTujuan',
            'kelasAsal',
            'semuaKelas',
            'tingkatMaks',
            'sudahTerdaftar',
            'instansi',
            'request'
        ));
    }

    public function proses(Request $request)
    {
        $request->validate([
            'tahun_asal_id' => 'required|exists:tahun_ajaran,id_tahun',
            'tahun_tujuan_id' => 'required|exists:tahun_ajaran,id_tahun',
            'siswa' => 'required|array',
            'siswa.*.action' => 'required|in:naik,tidak_naik,lulus',
            'siswa.*.kelas_tujuan_id' => 'nullable|exists:kelas,id_kelas',
        ]);

        $instansi = Auth::user()->getInstansi();
        $tahunTujuanId = $request->tahun_tujuan_id;

        $berhasilNaik = 0;
        $berhasilTidak = 0;
        $berhasilLulus = 0;
        $dilewati = 0;

        DB::transaction(function () use ($request, $instansi, $tahunTujuanId, &$berhasilNaik, &$berhasilTidak, &$berhasilLulus, &$dilewati) {
            foreach ($request->siswa as $siswaId => $data) {
                $action = $data['action'];

                // Skip kalau sudah terdaftar di tahun tujuan
                $sudahAda = RegistrasiAkademik::where('siswa_id', '=', $siswaId)
                    ->where('tahun_id', '=', $tahunTujuanId)
                    ->whereHas('kelas', fn($q) => $q->where('instansi_id', $instansi->id_instansi))
                    ->aktif()
                    ->exists();

                if ($sudahAda) {
                    $dilewati++;

                    continue;
                }

                if ($action === 'lulus') {
                    $siswa = Siswa::find($siswaId);
                    if ($siswa) {
                        User::where('id', '=', $siswa->user_id)->update(['email_verified_at' => null]);
                        $siswa->user->removeRole('siswa');

                        // Update registrasi aktif jadi Alumni
                        RegistrasiAkademik::where('siswa_id', $siswaId)
                            ->where('status', 'Aktif')
                            ->update(['status' => 'Alumni']);
                    }
                    $berhasilLulus++;

                } elseif (in_array($action, ['naik', 'tidak_naik'])) {
                    $kelasTujuanId = $data['kelas_tujuan_id'] ?? null;

                    if (! $kelasTujuanId) {
                        $dilewati++;

                        continue;
                    }

                    // Pastiin kelas milik instansi ini
                    $kelas = Kelas::where('id_kelas', '=', $kelasTujuanId)
                        ->where('instansi_id', '=', $instansi->id_instansi)
                        ->first();

                    if (! $kelas) {
                        $dilewati++;

                        continue;
                    }

                    RegistrasiAkademik::create([
                        'siswa_id' => $siswaId,
                        'kelas_id' => $kelasTujuanId,
                        'tahun_id' => $tahunTujuanId,
                        'status' => 'Aktif',
                    ]);

                    $action === 'naik' ? $berhasilNaik++ : $berhasilTidak++;
                }
            }
        });

        $message = 'Proses naik kelas selesai! ';
        $message .= "{$berhasilNaik} siswa naik kelas, ";
        $message .= "{$berhasilTidak} siswa tidak naik, ";
        $message .= "{$berhasilLulus} siswa lulus/alumni.";
        if ($dilewati > 0) {
            $message .= " {$dilewati} siswa dilewati (sudah terdaftar atau kelas tujuan tidak dipilih).";
        }

        $this->aktivasiTahunAjaran($tahunTujuanId, $instansi->id_instansi);

        return redirect()->route('admin.naik-kelas.index')->with('success', $message);
    }

    public function salinSemester(Request $request)
    {
        $request->validate([
            'tahun_asal_id' => 'required|exists:tahun_ajaran,id_tahun',
            'tahun_tujuan_id' => 'required|exists:tahun_ajaran,id_tahun|different:tahun_asal_id',
        ]);

        $instansi = Auth::user()->getInstansi();
        $tahunAsal = TahunAjaran::where('id_tahun', $request->tahun_asal_id)
            ->where('instansi_id', $instansi->id_instansi)->firstOrFail();
        $tahunTujuan = TahunAjaran::where('id_tahun', $request->tahun_tujuan_id)
            ->where('instansi_id', $instansi->id_instansi)->firstOrFail();

        // Ambil registrasi unik per siswa di tahun asal
        // Kalau siswa punya multiple registrasi, ambil yang terbaru
        $registrasiAsal = RegistrasiAkademik::where('tahun_id', $tahunAsal->id_tahun)
            ->aktif()
            ->whereHas('kelas', fn ($q) => $q->where('instansi_id', $instansi->id_instansi))
            ->get()
            ->unique('siswa_id'); // ← ambil unik per siswa

        $berhasil = 0;
        $dilewati = 0;

        DB::transaction(function () use ($registrasiAsal, $tahunTujuan, &$berhasil, &$dilewati) {
            foreach ($registrasiAsal as $reg) {
                $sudahAda = RegistrasiAkademik::where('siswa_id', $reg->siswa_id)
                    ->where('tahun_id', $tahunTujuan->id_tahun)
                    ->aktif()
                    ->exists();

                if ($sudahAda) {
                    $dilewati++;

                    continue;
                }

                RegistrasiAkademik::create([
                    'siswa_id' => $reg->siswa_id,
                    'kelas_id' => $reg->kelas_id,
                    'tahun_id' => $tahunTujuan->id_tahun,
                    'status'   => 'Aktif',
                ]);

                $berhasil++;
            }
        });

        $message = "Berhasil menyalin {$berhasil} siswa ke semester baru.";
        if ($dilewati > 0) {
            $message .= " {$dilewati} siswa dilewati (sudah terdaftar).";
        }

        $this->aktivasiTahunAjaran($tahunTujuan->id_tahun, $instansi->id_instansi);

        return redirect()->route('admin.naik-kelas.index')->with('success', $message);
    }

    private function aktivasiTahunAjaran(int $tahunId, int $instansiId): void
    {
        // Nonaktifkan semua tahun ajaran instansi ini
        TahunAjaran::where('instansi_id', $instansiId)
            ->update(['is_aktif' => false]);

        // Aktifkan tahun tujuan
        TahunAjaran::where('id_tahun', $tahunId)
            ->update(['is_aktif' => true]);
    }
}
