<x-layouts.admin>
    <x-slot:title>Riwayat Absensi</x-slot:title>

    <div class="container px-6 mx-auto">
        <div class="my-6">
            <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">Riwayat Absensi</h2>
        </div>

        {{-- Tambah di atas filter, di orangtua/absensi.blade.php dan orangtua/poin.blade.php --}}
        @if ($anak->count() > 1)
            <div class="mb-4 p-4 bg-white dark:bg-gray-800 rounded-lg shadow-xs dark:shadow-none dark:border dark:border-gray-700 flex items-center gap-4">
                <span class="text-sm text-gray-700 dark:text-gray-400 font-medium">Anak:</span>
                <div class="flex gap-2 flex-wrap">
                    @foreach ($anak as $a)
                        <a href="?anak_id={{ $a->id_siswa }}&bulan={{ $bulan }}&tahun={{ $tahun }}"
                            class="px-3 py-1 text-sm rounded-lg transition-colors
                    {{ $anakDipilih?->id_siswa == $a->id_siswa
                        ? 'bg-purple-600 text-white'
                        : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                            {{ $a->nama_siswa }}
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Filter --}}
        <form method="GET" action="{{ route('orangtua.absensi') }}"
            class="mb-4 p-4 bg-white dark:bg-gray-800 rounded-lg shadow-xs dark:shadow-none dark:border dark:border-gray-700 flex flex-wrap gap-4 items-end">
            <input type="hidden" name="anak_id" value="{{ $anakDipilih?->id_siswa }}">
            <label class="block text-sm">
                <span class="text-gray-700 dark:text-gray-400">Bulan</span>
                <select name="bulan" class="block mt-1 text-sm dark:bg-gray-700 dark:text-gray-300">
                    @for ($i = 1; $i <= 12; $i++)
                        <option value="{{ $i }}" {{ $bulan == $i ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create()->month($i)->locale('id')->monthName }}
                        </option>
                    @endfor
                </select>
            </label>
            <label class="block text-sm">
                <span class="text-gray-700 dark:text-gray-400">Tahun</span>
                <select name="tahun" class="block mt-1 text-sm dark:bg-gray-700 dark:text-gray-300">
                    @for ($y = now()->year; $y >= 2020; $y--)
                        <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}
                        </option>
                    @endfor
                </select>
            </label>
            <button type="submit"
                class="px-4 py-2 text-sm font-medium text-white bg-purple-600 rounded-lg hover:bg-purple-700">
                Filter
            </button>
        </form>

        {{-- Stats --}}
        <div class="grid grid-cols-3 md:grid-cols-6 gap-3 mb-4">
            @foreach (['Hadir' => 'green', 'Sakit' => 'blue', 'Izin' => 'yellow', 'Alpa' => 'red', 'Terlambat' => 'orange', 'Bolos' => 'pink'] as $status => $color)
                <div class="p-3 bg-white dark:bg-gray-800 rounded-lg shadow-xs dark:shadow-none dark:border dark:border-gray-700 text-center">
                    <p class="text-xl font-bold text-{{ $color }}-600">
                        {{ $stats[$status === 'Terlambat' ? 'terlambat' : strtolower($status)] }}</p>
                    <p class="text-xs text-gray-500 mt-1">{{ $status }}</p>
                </div>
            @endforeach
        </div>

        {{-- Table --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xs dark:shadow-none dark:border dark:border-gray-700 overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-200 dark:border-gray-700">
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    {{ ucfirst($bulanNama) }} {{ $tahun }} •
                    {{ $stats['total'] }} pertemuan •
                    <span class="{{ $stats['persen'] >= 75 ? 'text-green-600' : 'text-red-600' }} font-semibold">
                        {{ $stats['persen'] }}% hadir
                    </span>
                </p>
            </div>
            <table class="w-full">
                <thead>
                    <tr
                        class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50">
                        <th class="px-5 py-3">Tanggal</th>
                        <th class="px-5 py-3">Mata Pelajaran</th>
                        <th class="px-5 py-3">Guru</th>
                        <th class="px-5 py-3">Jam</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3">Keterangan</th>
                    </tr>
                </thead>
                <tbody class="divide-y dark:divide-gray-700">
                    @forelse($absensi as $absen)
                        @php
                            $colors = [
                                'Hadir' => 'green',
                                'Sakit' => 'blue',
                                'Izin' => 'yellow',
                                'Alpa' => 'red',
                                'Terlambat' => 'orange',
                                'Bolos' => 'pink',
                            ];
                            $color = $colors[$absen->status] ?? 'gray';
                        @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/70">
                            <td class="px-5 py-3 text-sm text-gray-600 dark:text-gray-400">
                                {{ \Carbon\Carbon::parse($absen->tanggal)->format('d M Y') }}
                            </td>
                            <td class="px-5 py-3 text-sm font-medium text-gray-700 dark:text-gray-200">
                                {{ $absen->jadwal->kurikulum->mataPelajaran->nama_mapel }}
                            </td>
                            <td class="px-5 py-3 text-sm text-gray-500">
                                @if($guru = $absen->jadwal->kurikulum?->guru)
                                    {{ $guru->nama_guru }}
                                    @if($guru->status_label)
                                        <span class="text-xs text-red-500">({{ $guru->status_label }})</span>
                                    @endif
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-5 py-3 text-sm text-gray-500">
                                {{ substr($absen->jadwal->jam_mulai, 0, 5) }}
                            </td>
                            <td class="px-5 py-3">
                                <span
                                    class="px-2 py-1 text-xs font-medium rounded-full text-{{ $color }}-700 bg-{{ $color }}-100 dark:bg-{{ $color }}-800 dark:text-{{ $color }}-200">
                                    {{ $absen->status }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-sm text-gray-500">{{ $absen->keterangan ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-8 text-center text-gray-500">
                                Tidak ada data absensi untuk periode ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.admin>
