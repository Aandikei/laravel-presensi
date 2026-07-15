<?php

namespace App\Exports;

use App\Models\Absensi;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class RekapAbsensiExport implements FromCollection, WithHeadings, WithStyles, WithTitle, WithColumnWidths, WithColumnFormatting
{
    protected $guruId;
    protected $instansiId;
    protected $bulan;
    protected $tahun;
    protected $mapelId;
    protected $tingkat;
    protected $jurusan;

    public function __construct(int $guruId, int $instansiId, int $bulan, int $tahun, ?int $mapelId = null, ?string $tingkat = null, ?int $jurusan = null)
    {
        $this->guruId      = $guruId;
        $this->instansiId  = $instansiId;
        $this->bulan       = $bulan;
        $this->tahun       = $tahun;
        $this->mapelId     = $mapelId;
        $this->tingkat     = $tingkat;
        $this->jurusan     = $jurusan;
    }

    public function collection()
    {
        $absensi = Absensi::with([
                'jadwal.kurikulum.mataPelajaran',
                'jadwal.kurikulum.kelas',
                'registrasi.siswa'
            ])
            ->whereHas('jadwal.kurikulum', fn ($q) => $q->where('guru_id', $this->guruId)
                ->whereHas('kelas', fn ($qq) => $qq->where('instansi_id', $this->instansiId))
            )
            ->whereHas('registrasi', fn ($q) => $q->aktif())
            ->whereMonth('tanggal', $this->bulan)
            ->whereYear('tanggal', $this->tahun)
            ->when($this->mapelId, fn ($q) => $q->whereHas('jadwal.kurikulum', fn ($qq) => $qq->where('mapel_id', $this->mapelId)))
            ->when($this->tingkat, fn ($q) => $q->whereHas('jadwal.kurikulum.kelas', fn ($qq) => $qq->where('tingkat', $this->tingkat)))
            ->when($this->jurusan, fn ($q) => $q->whereHas('jadwal.kurikulum.kelas', fn ($qq) => $qq->where('jurusan_id', $this->jurusan)))
            ->orderBy('tanggal', 'desc')
            ->orderBy('jadwal_id')
            ->get();

        $rows = collect();
        $no   = 1;

        foreach ($absensi as $a) {
            $rows->push([
                $no++,
                \Carbon\Carbon::parse($a->tanggal)->format('d M Y'),
                $a->jadwal->kurikulum->kelas->nama_kelas ?? '-',
                $a->jadwal->kurikulum->mataPelajaran->nama_mapel ?? '-',
                $a->jadwal->jam_mulai . ' - ' . $a->jadwal->jam_selesai,
                $a->registrasi->siswa->nama_siswa ?? '-',
                $a->registrasi->siswa->nisn ?? '-',
                $a->status,
                $a->keterangan ?? '-',
                $a->waktu_input ? \Carbon\Carbon::parse($a->waktu_input)->format('H:i') : '-',
                $a->durasi_terlambat ? $a->durasi_terlambat . ' mnt' : '-',
            ]);
        }

        return $rows;
    }

    public function headings(): array
    {
        return [
            'No',
            'Tanggal',
            'Kelas',
            'Mata Pelajaran',
            'Jam',
            'Nama Siswa',
            'NISN',
            'Status',
            'Keterangan',
            'Waktu Input',
            'Durasi Terlambat',
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
            'B' => 14,
            'C' => 15,
            'D' => 20,
            'E' => 14,
            'F' => 30,
            'G' => 15,
            'H' => 14,
            'I' => 25,
            'J' => 14,
            'K' => 16,
        ];
    }

    public function columnFormats(): array
    {
        return [
            'G' => NumberFormat::FORMAT_TEXT,
        ];
    }

    public function title(): string
    {
        return 'Riwayat Absensi';
    }
}
