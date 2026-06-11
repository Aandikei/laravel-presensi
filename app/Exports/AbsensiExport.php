<?php

namespace App\Exports;

use App\Models\RegistrasiAkademik;
use App\Models\Jadwal;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class AbsensiExport implements FromCollection, WithHeadings, WithStyles, WithTitle, WithColumnWidths
{
    protected $kelasId;
    protected $bulan;
    protected $tahun;
    protected $mapelId;

    public function __construct(int $kelasId, int $bulan, int $tahun, ?int $mapelId = null)
    {
        $this->kelasId  = $kelasId;
        $this->bulan    = $bulan;
        $this->tahun    = $tahun;
        $this->mapelId  = $mapelId;
    }

    public function collection()
    {
        $registrasi = RegistrasiAkademik::with(['siswa', 'absensi.jadwal.kurikulum.mataPelajaran'])
            ->where('kelas_id', $this->kelasId)
            ->get();

        $rows = collect();
        $no   = 1;

        foreach ($registrasi as $reg) {
            $absensi = $reg->absensi()
                ->whereMonth('tanggal', $this->bulan)
                ->whereYear('tanggal', $this->tahun)
                ->when($this->mapelId, fn($q) =>
                    $q->whereHas('jadwal.kurikulum', fn($q) =>
                        $q->where('mapel_id', $this->mapelId)
                    )
                )
                ->get();

            $hadir     = $absensi->where('status', 'Hadir')->count();
            $sakit     = $absensi->where('status', 'Sakit')->count();
            $izin      = $absensi->where('status', 'Izin')->count();
            $alpa      = $absensi->where('status', 'Alpa')->count();
            $terlambat = $absensi->where('status', 'Terlambat')->count();
            $bolos     = $absensi->where('status', 'Bolos')->count();
            $total     = $absensi->count();

            $rows->push([
                $no++,
                $reg->siswa->nama_siswa,
                $reg->siswa->nisn,
                $hadir,
                $sakit,
                $izin,
                $alpa,
                $terlambat,
                $bolos,
                $total,
                $total > 0 ? round(($hadir / $total) * 100, 1) . '%' : '0%',
            ]);
        }

        return $rows;
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Siswa',
            'NISN',
            'Hadir',
            'Sakit',
            'Izin',
            'Alpa',
            'Terlambat',
            'Bolos',
            'Total',
            '% Kehadiran',
        ];
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
            'A' => 5,
            'B' => 30,
            'C' => 15,
            'D' => 8,
            'E' => 8,
            'F' => 8,
            'G' => 8,
            'H' => 12,
            'I' => 8,
            'J' => 8,
            'K' => 14,
        ];
    }

    public function title(): string
    {
        return 'Rekap Absensi';
    }
}