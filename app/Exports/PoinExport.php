<?php

namespace App\Exports;

use App\Models\Siswa;
use App\Models\LogPoinSiswa;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class PoinExport implements FromCollection, WithHeadings, WithStyles, WithTitle, WithColumnWidths
{
    protected $instansiId;
    protected $kelasId;
    protected $bulan;
    protected $tahun;

    public function __construct(int $instansiId, ?int $kelasId, int $bulan, int $tahun)
    {
        $this->instansiId = $instansiId;
        $this->kelasId    = $kelasId;
        $this->bulan      = $bulan;
        $this->tahun      = $tahun;
    }

    public function collection()
    {
        $siswa = Siswa::with(['logPoin' => fn($q) => $q
            ->whereMonth('tanggal', $this->bulan)
            ->whereYear('tanggal', $this->tahun),
            'logPoin.masterPoin',
        ])
            ->where('instansi_id', $this->instansiId)
            ->whereNull('status')
            ->when($this->kelasId, fn($q) =>
                $q->whereHas('registrasiAktif', fn($q) =>
                    $q->where('kelas_id', $this->kelasId)
                )
            )
            ->orderBy('nama_siswa')
            ->get();

        $rows = collect();
        $no   = 1;

        foreach ($siswa as $s) {
            $logPoin = $s->logPoin;

            $totalPoin = $logPoin->sum(fn($l) => $l->masterPoin->jumlah_poin ?? 0);

            $rows->push([
                $no++,
                $s->nama_siswa,
                $s->nisn,
                $logPoin->count(),
                $totalPoin,
                $totalPoin >= 100 ? 'PERHATIAN' : ($totalPoin >= 50 ? 'WASPADA' : 'AMAN'),
            ]);
        }

        return $rows;
    }

    public function headings(): array
    {
        return ['No', 'Nama Siswa', 'NISN', 'Jumlah Pelanggaran', 'Total Poin', 'Status'];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '7C3AED']],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return ['A' => 5, 'B' => 30, 'C' => 15, 'D' => 20, 'E' => 12, 'F' => 12];
    }

    public function title(): string
    {
        return 'Rekap Poin';
    }
}