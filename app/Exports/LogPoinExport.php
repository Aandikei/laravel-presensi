<?php

namespace App\Exports;

use App\Models\LogPoinSiswa;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class LogPoinExport implements FromCollection, WithHeadings, WithStyles, WithTitle, WithColumnWidths
{
    protected int $instansiId;
    protected ?int $kelasId;
    protected ?string $tanggalMulai;
    protected ?string $tanggalSelesai;

    public function __construct(int $instansiId, ?int $kelasId = null, ?string $tanggalMulai = null, ?string $tanggalSelesai = null)
    {
        $this->instansiId     = $instansiId;
        $this->kelasId        = $kelasId;
        $this->tanggalMulai   = $tanggalMulai;
        $this->tanggalSelesai = $tanggalSelesai;
    }

    public function collection()
    {
        $query = LogPoinSiswa::with(['siswa', 'masterPoin', 'createdBy'])
            ->whereHas('siswa', fn($q) => $q->where('instansi_id', $this->instansiId))
            ->when($this->kelasId, fn($q) => $q->whereHas('siswa.registrasiAktif', fn($q) => $q->where('kelas_id', $this->kelasId)))
            ->when($this->tanggalMulai, fn($q) => $q->whereDate('tanggal', '>=', $this->tanggalMulai))
            ->when($this->tanggalSelesai, fn($q) => $q->whereDate('tanggal', '<=', $this->tanggalSelesai))
            ->orderByDesc('tanggal')
            ->get();

        $rows = collect();
        $no   = 1;

        foreach ($query as $log) {
            $rows->push([
                $no++,
                $log->tanggal->format('Y-m-d'),
                $log->siswa->nama_siswa,
                $log->siswa->nisn,
                $log->masterPoin->nama_poin ?? '-',
                $log->masterPoin->jumlah_poin ?? 0,
                $log->createdBy?->name ?? '-',
                $log->keterangan ?? '-',
            ]);
        }

        return $rows;
    }

    public function headings(): array
    {
        return ['No', 'Tanggal', 'Siswa', 'NISN', 'Pelanggaran', 'Poin', 'Pencatat', 'Keterangan'];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '7C3AED']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5, 'B' => 14, 'C' => 28, 'D' => 16,
            'E' => 25, 'F' => 8, 'G' => 20, 'H' => 25,
        ];
    }

    public function title(): string
    {
        return 'Log Poin';
    }
}
