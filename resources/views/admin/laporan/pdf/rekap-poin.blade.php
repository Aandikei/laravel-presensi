<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rekap Poin</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; }
        h2 { text-align: center; margin-bottom: 4px; }
        p { text-align: center; margin: 0; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        thead th { background: #7C3AED; color: white; padding: 8px; text-align: center; font-size: 10px; }
        tbody td { padding: 6px 8px; border-bottom: 1px solid #eee; text-align: center; }
        tbody tr:nth-child(even) { background: #f9f9f9; }
        .badge { padding: 2px 8px; border-radius: 99px; font-size: 10px; }
        .badge-green { background: #dcfce7; color: #16a34a; }
        .badge-yellow { background: #fef9c3; color: #ca8a04; }
        .badge-red { background: #fee2e2; color: #dc2626; }
    </style>
</head>
<body>
    <h2>Rekap Poin Pelanggaran</h2>
    <p>{{ $instansi->nama_instansi }}</p>
    <p>{{ ucfirst($bulanNama) }} {{ $request->tahun }}</p>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Siswa</th>
                <th>NISN</th>
                <th>Jumlah Pelanggaran</th>
                <th>Total Poin</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($siswa as $i => $s)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td style="text-align: left">{{ $s->nama_siswa }}</td>
                    <td>{{ $s->nisn }}</td>
                    <td>{{ $s->jumlah_pelanggaran }}</td>
                    <td><strong>{{ $s->total_poin }}</strong></td>
                    <td>
                        <span class="badge {{ $s->status_poin == 'PERHATIAN' ? 'badge-red' : ($s->status_poin == 'WASPADA' ? 'badge-yellow' : 'badge-green') }}">
                            {{ $s->status_poin }}
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