<x-layouts.admin>
    <x-slot:title>Dashboard Orang Tua</x-slot:title>

    <div class="container px-6 mx-auto">
        <div class="my-6">
            <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
                Dashboard Orang Tua
            </h2>
        </div>

        {{-- Child Selector --}}
        @if($anak->count() > 1)
            <div class="mb-4 p-4 bg-white dark:bg-gray-800 rounded-lg shadow-xs dark:shadow-none dark:border dark:border-gray-700 flex items-center gap-4">
                <span class="text-sm text-gray-700 dark:text-gray-400 font-medium">Pilih Anak:</span>
                <div class="flex gap-2 flex-wrap">
                    @foreach($anak as $a)
                        <a href="{{ route('orangtua.dashboard', ['anak_id' => $a->id_siswa]) }}"
                            class="px-4 py-2 text-sm font-medium rounded-lg transition-colors
                            {{ $anakDipilih?->id_siswa == $a->id_siswa
                                ? 'bg-purple-600 text-white'
                                : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200' }}">
                            {{ $a->nama_siswa }}
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        @if($anakDipilih)
            {{-- Info Anak --}}
            <div class="mb-4 p-4 bg-purple-50 dark:bg-purple-900/20 rounded-lg border border-purple-200 dark:border-purple-800">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-purple-600 flex items-center justify-center text-white font-bold">
                        {{ strtoupper(substr($anakDipilih->nama_siswa, 0, 1)) }}
                    </div>
                    <div>
                        <p class="font-semibold text-gray-700 dark:text-gray-200">{{ $anakDipilih->nama_siswa }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            NISN: {{ $anakDipilih->nisn }}
                            @if($registrasi) • Kelas {{ $registrasi->kelas->nama_kelas }} @endif
                        </p>
                    </div>
                </div>
            </div>

            {{-- Stats --}}
            <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-4 mb-6">
                @foreach([
                    ['label' => 'Hadir', 'value' => $stats['hadir'], 'color' => 'green'],
                    ['label' => 'Sakit', 'value' => $stats['sakit'], 'color' => 'blue'],
                    ['label' => 'Izin', 'value' => $stats['izin'], 'color' => 'yellow'],
                    ['label' => 'Alpa', 'value' => $stats['alpa'], 'color' => 'red'],
                    ['label' => 'Terlambat', 'value' => $stats['terlambat'], 'color' => 'orange'],
                    ['label' => 'Bolos', 'value' => $stats['bolos'], 'color' => 'pink'],
                ] as $stat)
                    <div class="p-4 bg-white dark:bg-gray-800 rounded-lg shadow-xs dark:shadow-none dark:border dark:border-gray-700 text-center">
                        <p class="text-2xl font-bold text-{{ $stat['color'] }}-600">{{ $stat['value'] }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $stat['label'] }}</p>
                    </div>
                @endforeach
            </div>

            <div class="grid gap-4 md:grid-cols-2 mb-6">
                <div class="p-5 bg-white dark:bg-gray-800 rounded-lg shadow-xs dark:shadow-none dark:border dark:border-gray-700 text-center">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Kehadiran Bulan Ini</p>
                    <p class="text-4xl font-bold {{ $stats['persen'] >= 75 ? 'text-green-600' : ($stats['persen'] >= 50 ? 'text-yellow-600' : 'text-red-600') }}">
                        {{ $stats['persen'] }}%
                    </p>
                    <p class="text-xs text-gray-400 mt-1">{{ $stats['hadir'] }}/{{ $stats['total'] }} pertemuan</p>
                    <a href="{{ route('orangtua.absensi', ['anak_id' => $anakDipilih->id_siswa]) }}"
                        class="mt-3 inline-block text-xs text-purple-600 hover:underline">Lihat detail →</a>
                </div>
                <div class="p-5 bg-white dark:bg-gray-800 rounded-lg shadow-xs dark:shadow-none dark:border dark:border-gray-700 text-center">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Poin Pelanggaran</p>
                    <p class="text-4xl font-bold {{ $totalPoin >= 100 ? 'text-red-600' : ($totalPoin >= 50 ? 'text-yellow-600' : 'text-green-600') }}">
                        {{ $totalPoin }}
                    </p>
                    <p class="text-xs text-gray-400 mt-1">Bulan {{ ucfirst($bulanNama) }}</p>
                    <a href="{{ route('orangtua.poin', ['anak_id' => $anakDipilih->id_siswa]) }}"
                        class="mt-3 inline-block text-xs text-purple-600 hover:underline">Lihat detail →</a>
                </div>
            </div>

            {{-- Absensi Terbaru --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xs dark:shadow-none dark:border dark:border-gray-700 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200">Absensi Terbaru</h3>
                    <a href="{{ route('orangtua.absensi', ['anak_id' => $anakDipilih->id_siswa]) }}"
                        class="text-sm text-purple-600 hover:underline">Lihat semua →</a>
                </div>
                @if($absensiTerbaru->isEmpty())
                    <div class="px-5 py-8 text-center text-gray-500">Belum ada data absensi.</div>
                @else
                    <table class="w-full">
                        <thead>
                            <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50">
                                <th class="px-5 py-3">Tanggal</th>
                                <th class="px-5 py-3">Mata Pelajaran</th>
                                <th class="px-5 py-3">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y dark:divide-gray-700">
                            @foreach($absensiTerbaru as $absen)
                                @php
                                    $colors = ['Hadir'=>'green','Sakit'=>'blue','Izin'=>'yellow','Alpa'=>'red','Terlambat'=>'orange','Bolos'=>'pink'];
                                    $color = $colors[$absen->status] ?? 'gray';
                                @endphp
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/70">
                                    <td class="px-5 py-3 text-sm text-gray-600 dark:text-gray-400">
                                        {{ \Carbon\Carbon::parse($absen->tanggal)->format('d M Y') }}
                                    </td>
                                    <td class="px-5 py-3 text-sm text-gray-700 dark:text-gray-200">
                                        {{ $absen->jadwal->kurikulum->mataPelajaran->nama_mapel }}
                                    </td>
                                    <td class="px-5 py-3">
                                        <span class="px-2 py-1 text-xs font-medium rounded-full text-{{ $color }}-700 bg-{{ $color }}-100">
                                            {{ $absen->status }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        @else
            <div class="px-4 py-8 text-center text-gray-500 bg-white dark:bg-gray-800 rounded-lg shadow-xs dark:shadow-none dark:border dark:border-gray-700">
                Tidak ada data anak yang terdaftar.
            </div>
        @endif
    </div>
</x-layouts.admin>