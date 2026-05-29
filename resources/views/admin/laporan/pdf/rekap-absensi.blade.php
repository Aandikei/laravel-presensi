<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rekap Absensi</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; }
        h2 { text-align: center; margin-bottom: 4px; }
        p { text-align: center; margin: 0; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        thead th { background: #7C3AED; color: white; padding: 8px; text-align: center; font-size: 10px; }
        tbody td { padding: 6px 8px; border-bottom: 1px solid #eee; }
        tbody tr:nth-child(even) { background: #f9f9f9; }
        .text-center { text-align: center; }
        .green { color: #16a34a; font-weight: bold; }
        .red { color: #dc2626; font-weight: bold; }
        .yellow { color: #d97706; font-weight: bold; }
        .badge { padding: 2px 8px; border-radius: 99px; font-size: 10px; }
        .badge-green { background: #dcfce7; color: #16a34a; }
        .badge-yellow { background: #fef9c3; color: #ca8a04; }
        .badge-red { background: #fee2e2; color: #dc2626; }
    </style>
</head>
<body>
    <h2>Rekap Absensi</h2>
    <p>{{ $kelas->nama_kelas }} — {{ ucfirst($bulanNama) }} {{ $request->tahun }}</p>
    @if($kelas->waliKelas)
        <p>Wali Kelas: {{ $kelas->waliKelas->nama_guru }}</p>
    @endif

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Siswa</th>
                <th>NISN</th>
                <th>Hadir</th>
                <th>Sakit</th>
                <th>Izin</th>
                <th>Alpa</th>
                <th>Terlambat</th>
                <th>Cabut</th>
                <th>Total</th>
                <th>% Hadir</th>
            </tr>
        </thead>
        <tbody>
            @foreach($registrasi as $i => $reg)
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td>{{ $reg->siswa->nama_siswa }}</td>
                    <td class="text-center">{{ $reg->siswa->nisn }}</td>
                    <td class="text-center green">{{ $reg->hadir }}</td>
                    <td class="text-center">{{ $reg->sakit }}</td>
                    <td class="text-center">{{ $reg->izin }}</td>
                    <td class="text-center red">{{ $reg->alpa }}</td>
                    <td class="text-center yellow">{{ $reg->terlambat }}</td>
                    <td class="text-center">{{ $reg->cabut }}</td>
                    <td class="text-center">{{ $reg->total }}</td>
                    <td class="text-center">
                        <span class="badge {{ $reg->persen >= 75 ? 'badge-green' : ($reg->persen >= 50 ? 'badge-yellow' : 'badge-red') }}">
                            {{ $reg->persen }}%
                        </span>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p style="margin-top: 20px; text-align: right; color: #999;">
        Dicetak: {{ now()->format('d F Y H:i') }}
    </p>
</body>
</html>