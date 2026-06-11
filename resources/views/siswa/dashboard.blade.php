<x-layouts.admin>
    <x-slot:title>Dashboard Siswa</x-slot:title>

    <div class="container px-6 mx-auto">
        <div class="my-6">
            <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
                Halo, {{ $siswa->nama_siswa }}!
            </h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                {{ now()->locale('id')->isoFormat('dddd, D MMMM Y') }}
                @if($registrasi)
                    • Kelas {{ $registrasi->kelas->nama_kelas }}
                @endif
            </p>
        </div>

        @if(!$registrasi)
            <div class="px-4 py-3 mb-6 text-sm text-yellow-700 bg-yellow-100 rounded-lg dark:bg-yellow-800 dark:text-yellow-200">
                Kamu belum terdaftar di kelas manapun untuk tahun ajaran aktif.
            </div>
        @endif

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
                <div class="p-4 bg-white dark:bg-gray-800 rounded-lg shadow-xs text-center">
                    <p class="text-2xl font-bold text-{{ $stat['color'] }}-600">{{ $stat['value'] }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $stat['label'] }}</p>
                </div>
            @endforeach
        </div>

        <div class="grid gap-4 md:grid-cols-3 mb-6">
            {{-- % Kehadiran --}}
            <div class="p-5 bg-white dark:bg-gray-800 rounded-lg shadow-xs text-center">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Kehadiran Bulan Ini</p>
                <p class="text-4xl font-bold {{ $stats['persen'] >= 75 ? 'text-green-600' : ($stats['persen'] >= 50 ? 'text-yellow-600' : 'text-red-600') }}">
                    {{ $stats['persen'] }}%
                </p>
                <p class="text-xs text-gray-400 mt-1">{{ $stats['hadir'] }}/{{ $stats['total'] }} pertemuan</p>
            </div>

            {{-- Total Poin --}}
            <div class="p-5 bg-white dark:bg-gray-800 rounded-lg shadow-xs text-center">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Poin Pelanggaran</p>
                <p class="text-4xl font-bold {{ $totalPoin >= 100 ? 'text-red-600' : ($totalPoin >= 50 ? 'text-yellow-600' : 'text-green-600') }}">
                    {{ $totalPoin }}
                </p>
                <p class="text-xs text-gray-400 mt-1">Bulan {{ ucfirst($bulanNama) }}</p>
            </div>

            {{-- Info Kelas --}}
            <div class="p-5 bg-white dark:bg-gray-800 rounded-lg shadow-xs">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Info Kelas</p>
                @if($registrasi)
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Kelas</span>
                            <span class="font-medium text-gray-700 dark:text-gray-200">{{ $registrasi->kelas->nama_kelas }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Tahun Ajaran</span>
                            <span class="font-medium text-gray-700 dark:text-gray-200">{{ $registrasi->tahunAjaran->nama_tahun }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Semester</span>
                            <span class="font-medium text-gray-700 dark:text-gray-200">{{ $registrasi->tahunAjaran->semester }}</span>
                        </div>
                    </div>
                @else
                    <p class="text-sm text-gray-400">Belum terdaftar</p>
                @endif
            </div>
        </div>

        {{-- Absensi Terbaru --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xs overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200">Absensi Terbaru</h3>
                <a href="{{ route('siswa.absensi') }}" class="text-sm text-purple-600 dark:text-purple-400 hover:underline">
                    Lihat semua →
                </a>
            </div>
            @if($absensiTerbaru->isEmpty())
                <div class="px-5 py-8 text-center text-gray-500 dark:text-gray-400">
                    Belum ada data absensi.
                </div>
            @else
                <table class="w-full">
                    <thead>
                        <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b dark:border-gray-700 bg-gray-50 dark:bg-gray-800">
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
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-5 py-3 text-sm text-gray-600 dark:text-gray-400">
                                    {{ \Carbon\Carbon::parse($absen->tanggal)->format('d M Y') }}
                                </td>
                                <td class="px-5 py-3 text-sm text-gray-700 dark:text-gray-200">
                                    {{ $absen->jadwal->kurikulum->mataPelajaran->nama_mapel }}
                                </td>
                                <td class="px-5 py-3">
                                    <span class="px-2 py-1 text-xs font-medium rounded-full text-{{ $color }}-700 bg-{{ $color }}-100 dark:bg-{{ $color }}-800 dark:text-{{ $color }}-200">
                                        {{ $absen->status }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</x-layouts.admin>