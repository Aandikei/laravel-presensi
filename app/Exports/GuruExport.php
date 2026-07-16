<?php

namespace App\Exports;

use App\Models\Guru;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class GuruExport implements FromCollection, WithHeadings, WithStyles, WithTitle, WithColumnWidths
{
    protected int $instansiId;
    protected ?string $status;

    public function __construct(int $instansiId, ?string $status = null)
    {
        $this->instansiId = $instansiId;
        $this->status     = $status;
    }

    public function collection()
    {
        $query = Guru::where('instansi_id', $this->instansiId)
            ->when($this->status, fn($q) => $q->where('status', $this->status === 'Aktif' ? null : $this->status))
            ->orderBy('nama_guru')
            ->get();

        $rows = collect();
        $no   = 1;

        foreach ($query as $g) {
            $rows->push([
                $no++,
                $g->nip ?? '-',
                $g->nama_guru,
                $g->jenis_kelamin ?? '-',
                $g->no_hp ?? '-',
                $g->isAktif() ? 'Aktif' : ($g->status ?? '-'),
            ]);
        }

        return $rows;
    }

    public function headings(): array
    {
        return ['No', 'NIP', 'Nama Guru', 'JK', 'No HP', 'Status'];
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
            'A' => 5, 'B' => 20, 'C' => 30, 'D' => 5, 'E' => 15, 'F' => 12,
        ];
    }

    public function title(): string
    {
        return 'Data Guru';
    }
}
