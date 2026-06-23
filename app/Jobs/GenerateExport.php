<?php

namespace App\Jobs;

use App\Exports\AbsensiExport;
use App\Exports\PoinExport;
use App\Exports\RekapAbsensiExport;
use App\Models\ExportJob;
use App\Models\Kelas;
use App\Models\RegistrasiAkademik;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class GenerateExport implements ShouldQueue
{
    use Dispatchable, Queueable;

    protected ExportJob $exportJob;

    public function __construct(ExportJob $exportJob)
    {
        $this->exportJob = $exportJob;
    }

    public function handle(): void
    {
        $this->exportJob->update(['status' => 'processing']);

        try {
            $filters = $this->exportJob->filters;
            $filename = $this->generateFilename();
            $filepath = 'exports/' . $filename;

            match ($this->exportJob->type) {
                'absensi-excel'   => $this->generateAbsensiExcel($filepath, $filters),
                'absensi-pdf'     => $this->generateAbsensiPdf($filepath, $filters),
                'poin-excel'      => $this->generatePoinExcel($filepath, $filters),
                'poin-pdf'        => $this->generatePoinPdf($filepath, $filters),
                'guru-rekap-excel' => $this->generateGuruRekapExcel($filepath, $filters),
            };

            $this->exportJob->update([
                'status'       => 'completed',
                'filename'     => $filename,
                'filepath'     => $filepath,
                'completed_at' => now(),
            ]);
        } catch (\Throwable $e) {
            $this->exportJob->update([
                'status'         => 'failed',
                'error_message'  => $e->getMessage(),
            ]);
        }
    }

    protected function generateFilename(): string
    {
        $ts = now()->format('Ymd_His');
        $ext = str_contains($this->exportJob->type, 'pdf') ? 'pdf' : 'xlsx';
        return str_replace('-', '_', $this->exportJob->type) . "_{$ts}.{$ext}";
    }

    protected function generateAbsensiExcel(string $filepath, array $filters): void
    {
        $tahunAktif = TahunAjaran::getAktif($this->exportJob->user->getInstansi()->id_instansi);

        Excel::store(
            new AbsensiExport($filters['kelas_id'], $filters['bulan'], $filters['tahun'], $filters['mapel_id'] ?? null, $tahunAktif?->id_tahun),
            $filepath,
            'local'
        );
    }

    protected function generateAbsensiPdf(string $filepath, array $filters): void
    {
        $instansi = $this->exportJob->user->getInstansi();

        $tahunAktif = TahunAjaran::getAktif($instansi->id_instansi);
        $kelas = Kelas::with(['waliKelas'])->findOrFail($filters['kelas_id']);

        $registrasi = RegistrasiAkademik::with(['siswa', 'absensi' => function ($q) use ($filters) {
            $q->whereMonth('tanggal', $filters['bulan'])
              ->whereYear('tanggal', $filters['tahun']);
        }])
            ->aktif()
            ->where('kelas_id', $filters['kelas_id'])
            ->where('tahun_id', $tahunAktif->id_tahun)
            ->get()
            ->map(function ($reg) {
                $absensi = $reg->absensi;
                $reg->hadir     = $absensi->where('status', 'Hadir')->count();
                $reg->sakit     = $absensi->where('status', 'Sakit')->count();
                $reg->izin      = $absensi->where('status', 'Izin')->count();
                $reg->alpa      = $absensi->where('status', 'Alpa')->count();
                $reg->terlambat = $absensi->where('status', 'Terlambat')->count();
                $reg->bolos     = $absensi->where('status', 'Bolos')->count();
                $reg->total     = $absensi->count();
                $reg->persen    = $reg->total > 0 ? round(($reg->hadir / $reg->total) * 100, 1) : 0;
                return $reg;
            });

        $bulanNama = \Carbon\Carbon::createFromDate($filters['tahun'], $filters['bulan'], 1)->locale('id')->monthName;

        $request = (object) $filters;

        $pdf = Pdf::loadView('admin.laporan.pdf.rekap-absensi', compact('registrasi', 'kelas', 'bulanNama', 'request'))
            ->setPaper('a4', 'landscape');

        Storage::disk('local')->put($filepath, $pdf->output());
    }

    protected function generatePoinExcel(string $filepath, array $filters): void
    {
        $instansi = $this->exportJob->user->getInstansi();
        Excel::store(
            new PoinExport($instansi->id_instansi, $filters['kelas_id'] ?? null, $filters['bulan'], $filters['tahun']),
            $filepath,
            'local'
        );
    }

    protected function generatePoinPdf(string $filepath, array $filters): void
    {
        $instansi = $this->exportJob->user->getInstansi();

        $kelas = isset($filters['kelas_id'])
            ? Kelas::find($filters['kelas_id'])
            : null;

        $siswa = Siswa::with(['logPoin' => fn($q) => $q
            ->whereMonth('tanggal', $filters['bulan'])
            ->whereYear('tanggal', $filters['tahun']),
            'logPoin.masterPoin',
        ])
            ->where('instansi_id', $instansi->id_instansi)
            ->whereNull('status')
            ->when($filters['kelas_id'] ?? null, fn($q) => $q->whereHas('registrasiAktif', fn($q) => $q->where('kelas_id', $filters['kelas_id'])))
            ->orderBy('nama_siswa')
            ->get()
            ->map(function ($s) {
                $s->jumlah_pelanggaran = $s->logPoin->count();
                $s->total_poin = $s->logPoin->sum(fn($l) => $l->masterPoin->jumlah_poin ?? 0);
                $s->status_poin = $s->total_poin >= 100 ? 'PERHATIAN'
                    : ($s->total_poin >= 50 ? 'WASPADA' : 'AMAN');
                return $s;
            });

        $bulanNama = \Carbon\Carbon::createFromDate($filters['tahun'], $filters['bulan'], 1)->locale('id')->monthName;

        $request = (object) $filters;

        $pdf = Pdf::loadView('admin.laporan.pdf.rekap-poin', compact('siswa', 'kelas', 'bulanNama', 'request', 'instansi'))
            ->setPaper('a4', 'portrait');

        Storage::disk('local')->put($filepath, $pdf->output());
    }

    protected function generateGuruRekapExcel(string $filepath, array $filters): void
    {
        Excel::store(
            new RekapAbsensiExport(
                (int) $filters['guru_id'],
                (int) $filters['instansi_id'],
                (int) $filters['bulan'],
                (int) $filters['tahun'],
                isset($filters['mapel_id']) ? (int) $filters['mapel_id'] : null,
                $filters['tingkat'] ?? null,
                $filters['jurusan'] ?? null
            ),
            $filepath,
            'local'
        );
    }
}
