<?php

namespace App\Exports;

use App\Models\Jadwal;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class JadwalExport implements FromCollection, WithHeadings, WithStyles, WithTitle, WithColumnWidths
{
    protected int $instansiId;
    protected ?int $kelasId;
    protected ?int $tahunId;

    public function __construct(int $instansiId, ?int $kelasId = null, ?int $tahunId = null)
    {
        $this->instansiId = $instansiId;
        $this->kelasId    = $kelasId;
        $this->tahunId    = $tahunId;
    }

    public function collection()
    {
        $query = Jadwal::with(['kurikulum.mataPelajaran', 'kurikulum.guru', 'kurikulum.kelas'])
            ->whereHas('kurikulum.kelas', fn($q) => $q->where('instansi_id', $this->instansiId))
            ->when($this->kelasId, fn($q) => $q->whereHas('kurikulum', fn($q) => $q->where('kelas_id', $this->kelasId)))
            ->when($this->tahunId, fn($q) => $q->whereHas('kurikulum', fn($q) => $q->where('tahun_id', $this->tahunId)))
            ->orderBy('hari')
            ->orderBy('jam_mulai')
            ->get();

        $hariMap = ['Senin' => 1, 'Selasa' => 2, 'Rabu' => 3, 'Kamis' => 4, "Jum'at" => 5, 'Sabtu' => 6, 'Minggu' => 7];
        $query = $query->sortBy(fn($j) => ($hariMap[$j->hari] ?? 99) . $j->jam_mulai);

        $rows = collect();
        $no   = 1;

        foreach ($query as $j) {
            $rows->push([
                $no++,
                $j->hari,
                substr($j->jam_mulai, 0, 5),
                substr($j->jam_selesai, 0, 5),
                $j->kurikulum?->mataPelajaran?->nama_mapel ?? '-',
                $j->kurikulum?->guru?->nama_guru ?? '-',
                $j->kurikulum?->kelas?->nama_kelas ?? '-',
            ]);
        }

        return $rows;
    }

    public function headings(): array
    {
        return ['No', 'Hari', 'Jam Mulai', 'Jam Selesai', 'Mata Pelajaran', 'Guru', 'Kelas'];
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
            'A' => 5, 'B' => 10, 'C' => 10, 'D' => 10, 'E' => 25, 'F' => 28, 'G' => 15,
        ];
    }

    public function title(): string
    {
        return 'Jadwal';
    }
}
