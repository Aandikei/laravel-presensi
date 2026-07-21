<x-layouts.admin>
    <x-slot:title>Absensi Hari Ini</x-slot:title>

    <div class="container px-6 mx-auto">
        <div class="my-6">
            <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
                Absensi Hari Ini
            </h2>
            <p class="text-gray-500 dark:text-gray-400 mt-1">
                {{ $hariIni }}, {{ now()->format('d F Y') }}
            </p>
        </div>

        {{-- Absen Harian SD (Guru Kelas / Wali Kelas SD) --}}
        @if(isset($kelasGuruKelas) && $kelasGuruKelas->isNotEmpty())
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-3">
                    Absen Harian
                </h3>
                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    @foreach($kelasGuruKelas as $kelas)
                        <div class="p-5 bg-white dark:bg-gray-800 rounded-lg shadow-xs dark:shadow-none dark:border dark:border-gray-700 border-l-4
                            {{ $kelas->sudah_absen_harian ? 'border-green-500' : 'border-yellow-500' }}">
                            <div class="flex items-start justify-between">
                                <div>
                                    <p class="font-semibold text-gray-700 dark:text-gray-200">
                                        {{ $kelas->nama_kelas }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        {{ $kelas->mapel_list->implode(', ') }}
                                    </p>
                                </div>
                                @if($kelas->sudah_absen_harian)
                                    <span class="px-2 py-1 text-xs font-medium text-green-700 bg-green-100 rounded-full dark:bg-green-800 dark:text-green-200">
                                        Sudah Input
                                    </span>
                                @else
                                    <span class="px-2 py-1 text-xs font-medium text-yellow-700 bg-yellow-100 rounded-full dark:bg-yellow-800 dark:text-yellow-200">
                                        Belum Input
                                    </span>
                                @endif
                            </div>
                            <a href="{{ route('guru.absen-harian.input', $kelas->id_kelas) }}"
                                class="mt-3 block w-full text-center px-4 py-2 text-sm font-medium rounded-lg
                                {{ $kelas->sudah_absen_harian
                                    ? 'text-purple-600 bg-purple-50 hover:bg-purple-100 dark:text-purple-300 dark:bg-purple-900/30'
                                    : 'text-white bg-purple-600 hover:bg-purple-700' }}">
                                {{ $kelas->sudah_absen_harian ? 'Lihat / Edit' : 'Input Absen Harian' }}
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Absensi Per Jam (guru_mapel) --}}
        <div class="mb-6">
            <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-3">
                Jadwal Hari Ini
            </h3>

            @if($jadwalHariIni->isEmpty())
                <div class="p-8 text-center bg-white dark:bg-gray-800 rounded-lg shadow-xs dark:shadow-none dark:border dark:border-gray-700">
                    <p class="text-gray-500 dark:text-gray-400">Tidak ada jadwal mengajar hari ini.</p>
                </div>
            @else
                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    @foreach($jadwalHariIni as $jadwal)
                        <div
                            class="p-5 bg-white dark:bg-gray-800 rounded-lg shadow-xs dark:shadow-none dark:border dark:border-gray-700 border-l-4
                            {{ $jadwal->sudah_input ? 'border-green-500' : 'border-yellow-500' }}">
                            <div class="flex items-start justify-between">
                                <div>
                                    <p class="font-semibold text-gray-700 dark:text-gray-200">
                                        {{ $jadwal->kurikulum->mataPelajaran->nama_mapel }}
                                    </p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                        {{ $jadwal->kurikulum->kelas->nama_kelas }}
                                    </p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ substr($jadwal->jam_mulai, 0, 5) }} - {{ substr($jadwal->jam_selesai, 0, 5) }}
                                    </p>
                                </div>
                                @if($jadwal->sudah_input)
                                    <span class="px-2 py-1 text-xs font-medium text-green-700 bg-green-100 rounded-full dark:bg-green-800 dark:text-green-200">Sudah Input</span>
                                @else
                                    <span class="px-2 py-1 text-xs font-medium text-yellow-700 bg-yellow-100 rounded-full dark:bg-yellow-800 dark:text-yellow-200">Belum Input</span>
                                @endif
                            </div>
                            @if($jadwal->sudah_input)
                                <a href="{{ route('guru.absensi.input', $jadwal->id_jadwal) }}"
                                    class="mt-3 block w-full text-center px-4 py-2 text-sm font-medium text-purple-600 bg-purple-50 rounded-lg hover:bg-purple-100 dark:text-purple-300 dark:bg-purple-900/30">
                                    Lihat / Edit
                                </a>
                            @else
                                <a href="{{ route('guru.absensi.input', $jadwal->id_jadwal) }}"
                                    class="mt-3 block w-full text-center px-4 py-2 text-sm font-medium text-white bg-purple-600 rounded-lg hover:bg-purple-700">
                                    Input Absensi
                                </a>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-layouts.admin>