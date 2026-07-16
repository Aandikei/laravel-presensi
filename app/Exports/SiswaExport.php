<?php

namespace App\Exports;

use App\Models\RegistrasiAkademik;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class SiswaExport implements FromCollection, WithHeadings, WithStyles, WithTitle, WithColumnWidths
{
    protected int $instansiId;
    protected ?int $kelasId;
    protected ?int $tahunId;
    protected ?string $status;

    public function __construct(int $instansiId, ?int $kelasId = null, ?int $tahunId = null, ?string $status = null)
    {
        $this->instansiId = $instansiId;
        $this->kelasId    = $kelasId;
        $this->tahunId    = $tahunId;
        $this->status     = $status;
    }

    public function collection()
    {
        $query = RegistrasiAkademik::with(['siswa', 'kelas'])
            ->whereHas('kelas', fn($q) => $q->where('instansi_id', $this->instansiId))
            ->when($this->kelasId, fn($q) => $q->where('kelas_id', $this->kelasId))
            ->when($this->tahunId, fn($q) => $q->where('tahun_id', $this->tahunId))
            ->when($this->status, fn($q) => $q->where('status', $this->status))
            ->orderBy('kelas_id')
            ->orderBy('siswa_id')
            ->get();

        $rows = collect();
        $no   = 1;

        foreach ($query as $reg) {
            $rows->push([
                $no++,
                $reg->siswa->nisn,
                $reg->siswa->nama_siswa,
                $reg->siswa->jenis_kelamin ?? '-',
                $reg->siswa->tempat_lahir ?? '-',
                $reg->siswa->tanggal_lahir?->format('Y-m-d') ?? '-',
                $reg->kelas->nama_kelas,
                $reg->status,
            ]);
        }

        return $rows;
    }

    public function headings(): array
    {
        return ['No', 'NISN', 'Nama Siswa', 'JK', 'Tempat Lahir', 'Tanggal Lahir', 'Kelas', 'Status'];
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
            'A' => 5, 'B' => 18, 'C' => 30, 'D' => 5,
            'E' => 15, 'F' => 14, 'G' => 15, 'H' => 10,
        ];
    }

    public function title(): string
    {
        return 'Data Siswa';
    }
}
