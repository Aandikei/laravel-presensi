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

        @if($jadwalHariIni->isEmpty())
            <div class="p-8 text-center bg-white dark:bg-gray-800 rounded-lg shadow-xs dark:shadow-none dark:border dark:border-gray-700">
                <p class="text-gray-500 dark:text-gray-400">Tidak ada jadwal mengajar hari ini.</p>
            </div>
        @else
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @foreach($jadwalHariIni as $jadwal)
                    <div class="p-5 bg-white dark:bg-gray-800 rounded-lg shadow-xs dark:shadow-none dark:border dark:border-gray-700 border-l-4
                        {{ $jadwal->sudah_input ? 'border-green-500' : 'border-yellow-500' }}">
                        <p class="font-semibold text-gray-700 dark:text-gray-200">
                            {{ $jadwal->kurikulum->mataPelajaran->nama_mapel }}
                        </p>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                            {{ $jadwal->kurikulum->kelas->nama_kelas }} •
                            {{ substr($jadwal->jam_mulai, 0, 5) }} - {{ substr($jadwal->jam_selesai, 0, 5) }}
                        </p>
                        <div class="mt-3">
                            @if($jadwal->sudah_input)
                                <span class="inline-block mb-2 px-2 py-1 text-xs font-medium text-green-700 bg-green-100 rounded-full">✓ Sudah diinput</span>
                            @else
                                <span class="inline-block mb-2 px-2 py-1 text-xs font-medium text-yellow-700 bg-yellow-100 rounded-full">⚠ Belum diinput</span>
                            @endif
                            <a href="{{ route('guru.absensi.input', $jadwal->id_jadwal) }}"
                                class="block w-full text-center px-4 py-2 text-sm font-medium text-white bg-purple-600 rounded-lg hover:bg-purple-700">
                                {{ $jadwal->sudah_input ? 'Lihat / Edit' : 'Input Absensi' }}
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-layouts.admin>