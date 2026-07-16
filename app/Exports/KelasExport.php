<?php

namespace App\Exports;

use App\Models\Kelas;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class KelasExport implements FromCollection, WithHeadings, WithStyles, WithTitle, WithColumnWidths
{
    protected int $instansiId;
    protected ?int $tahunId;
    protected ?int $tingkat;
    protected ?int $jurusanId;

    public function __construct(int $instansiId, ?int $tahunId = null, ?int $tingkat = null, ?int $jurusanId = null)
    {
        $this->instansiId = $instansiId;
        $this->tahunId    = $tahunId;
        $this->tingkat    = $tingkat;
        $this->jurusanId  = $jurusanId;
    }

    public function collection()
    {
        $query = Kelas::with(['waliKelas', 'jurusan'])
            ->where('instansi_id', $this->instansiId)
            ->when($this->tingkat, fn($q) => $q->where('tingkat', $this->tingkat))
            ->when($this->jurusanId, fn($q) => $q->where('jurusan_id', $this->jurusanId))
            ->withCount(['registrasiAkademik as jumlah_siswa' => fn($q) => $q
                ->aktif()
                ->when($this->tahunId, fn($q) => $q->where('tahun_id', $this->tahunId))
            ])
            ->orderBy('tingkat')
            ->orderBy('nama_kelas')
            ->get();

        $rows = collect();
        $no   = 1;

        foreach ($query as $k) {
            $rows->push([
                $no++,
                $k->nama_kelas,
                $k->tingkat_roman,
                $k->jurusan?->kode_jurusan ?? '-',
                $k->waliKelas?->nama_guru ?? '-',
                $k->jumlah_siswa,
            ]);
        }

        return $rows;
    }

    public function headings(): array
    {
        return ['No', 'Nama Kelas', 'Tingkat', 'Jurusan', 'Wali Kelas', 'Jml Siswa'];
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
            'A' => 5, 'B' => 20, 'C' => 8, 'D' => 10, 'E' => 30, 'F' => 10,
        ];
    }

    public function title(): string
    {
        return 'Data Kelas';
    }
}
