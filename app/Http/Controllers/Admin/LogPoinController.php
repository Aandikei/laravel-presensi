<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LogPoinSiswa;
use App\Models\MasterPoin;
use App\Models\Siswa;
use App\Models\RekapBulanan;
use App\Models\RegistrasiAkademik;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class LogPoinController extends Controller
{
    public function index(Request $request)
    {
        $instansi = Auth::user()->getInstansi();

        if ($request->ajax()) {
            $logPoin = LogPoinSiswa::with(['siswa', 'masterPoin', 'createdBy'])
                ->whereHas('siswa', fn($q) =>
                    $q->where('instansi_id', $instansi->id_instansi)
                )
                ->select('log_poin_siswa.*');

            if ($request->siswa_id) {
                $logPoin->where('siswa_id', $request->siswa_id);
            }

            return DataTables::of($logPoin)
                ->addIndexColumn()
                ->addColumn('nama_siswa', fn($row) => $row->siswa->nama_siswa)
                ->addColumn('pelanggaran', fn($row) => $row->masterPoin->nama_pelanggaran)
                ->addColumn('poin', fn($row) => $row->masterPoin->jumlah_poin)
                ->addColumn('dicatat_oleh', fn($row) => $row->createdBy->name ?? '-')
                ->filterColumn('nama_siswa', function($query, $keyword) {
                    $query->whereHas('siswa', fn($q) =>
                        $q->where('nama_siswa', 'like', "%{$keyword}%")
                    );
                })
                ->filterColumn('pelanggaran', function($query, $keyword) {
                    $query->whereHas('masterPoin', fn($q) =>
                        $q->where('nama_pelanggaran', 'like', "%{$keyword}%")
                    );
                })
                ->addColumn('aksi', function($row) {
                    return '<form method="POST" action="' . route('admin.log-poin.destroy', $row->id_log_poin) . '" class="inline">
                        <input type="hidden" name="_token" value="' . csrf_token() . '">
                        <input type="hidden" name="_method" value="DELETE">
                        <button type="submit" title="Hapus" class="text-red-600 hover:text-red-800" onclick="return confirm(\'Yakin hapus log poin ini?\')">
                            <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                        </form>';
                })
                ->rawColumns(['aksi'])
                ->make(true);
        }

        $instansi = Auth::user()->getInstansi();
        $siswa = Siswa::where('instansi_id', $instansi->id_instansi)
            ->orderBy('nama_siswa')
            ->get();
        $masterPoin = MasterPoin::where('instansi_id', $instansi->id_instansi)
            ->orderBy('nama_pelanggaran')
            ->get();

        return view('admin.log-poin.index', compact('siswa', 'masterPoin'));
    }

    public function store(Request $request)
    {
        $instansi = Auth::user()->getInstansi();

        $validated = $request->validate([
            'siswa_id'   => 'required|exists:siswa,id_siswa',
            'poin_id'    => 'required|exists:master_poin,id_poin',
            'tanggal'    => 'required|date',
            'keterangan' => 'nullable|string|max:255',
        ]);

        // Pastiin siswa milik instansi ini
        $siswa = Siswa::findOrFail($validated['siswa_id']);
        abort_if($siswa->instansi_id !== $instansi->id_instansi, 403);

        LogPoinSiswa::create([
            ...$validated,
            'created_by' => Auth::id(),
        ]);

        // Update rekap bulanan poin
        $this->updateRekapPoin($validated['siswa_id'], $validated['tanggal']);

        return back()->with('success', 'Poin pelanggaran berhasil ditambahkan!');
    }

    public function destroy(LogPoinSiswa $logPoin)
    {
        $instansi = Auth::user()->getInstansi();
        abort_if($logPoin->siswa->instansi_id !== $instansi->id_instansi, 403);

        $tanggal = $logPoin->tanggal;
        $siswaId = $logPoin->siswa_id;

        $logPoin->delete();

        // Update rekap
        $this->updateRekapPoin($siswaId, $tanggal);

        return back()->with('success', 'Log poin berhasil dihapus!');
    }

    private function updateRekapPoin(int $siswaId, string $tanggal): void
    {
        $bulan = (int) date('m', strtotime($tanggal));
        $tahun = (int) date('Y', strtotime($tanggal));

        $totalPoin = LogPoinSiswa::where('siswa_id', $siswaId)
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->join('master_poin', 'log_poin_siswa.poin_id', '=', 'master_poin.id_poin')
            ->sum('master_poin.jumlah_poin');

        // Update semua rekap bulanan siswa ini di bulan tsb
        $registrasi = RegistrasiAkademik::where('siswa_id', $siswaId)->pluck('id_registrasi');

        RekapBulanan::whereIn('reg_id', $registrasi)
            ->where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->update(['poin_akumulasi' => $totalPoin]);
    }
}