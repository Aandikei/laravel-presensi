<x-layouts.admin>
    <x-slot:title>Laporan</x-slot:title>

    <div class="container px-6 mx-auto">
        <div class="my-6">
            <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">Laporan</h2>
        </div>

        <div class="grid gap-6 md:grid-cols-2">

            {{-- Rekap Absensi --}}
            <div class="p-6 bg-white rounded-lg shadow-xs dark:bg-gray-800">
                <h3 class="mb-1 text-lg font-semibold text-gray-700 dark:text-gray-200">
                    Rekap Absensi
                </h3>
                <p class="mb-4 text-sm text-gray-500 dark:text-gray-400">
                    Rekap kehadiran siswa per kelas per bulan
                </p>
                <form method="GET" action="{{ route('admin.laporan.rekap-absensi') }}">
                    <label class="block text-sm mb-3">
                        <span class="text-gray-700 dark:text-gray-400">Kelas</span>
                        <select name="kelas_id"
                            class="block w-full mt-1 text-sm form-select dark:bg-gray-700 dark:text-gray-300">
                            <option value="">-- Pilih Kelas --</option>
                            @foreach($kelas as $k)
                                <option value="{{ $k->id_kelas }}">
                                    {{ $k->nama_kelas }} (Tingkat {{ $k->tingkat }})
                                </option>
                            @endforeach
                        </select>
                    </label>
                    <label class="block text-sm mb-3">
                        <span class="text-gray-700 dark:text-gray-400">Mata Pelajaran <span class="text-gray-400">(opsional)</span></span>
                        <select name="mapel_id"
                            class="block w-full mt-1 text-sm form-select dark:bg-gray-700 dark:text-gray-300">
                            <option value="">Semua Mapel</option>
                            @foreach($mapel as $m)
                                <option value="{{ $m->id_mapel }}">{{ $m->nama_mapel }}</option>
                            @endforeach
                        </select>
                    </label>
                    <div class="grid grid-cols-2 gap-3 mb-4">
                        <label class="block text-sm">
                            <span class="text-gray-700 dark:text-gray-400">Bulan</span>
                            <select name="bulan"
                                class="block w-full mt-1 text-sm form-select dark:bg-gray-700 dark:text-gray-300">
                                @for($i = 1; $i <= 12; $i++)
                                    <option value="{{ $i }}" {{ now()->month == $i ? 'selected' : '' }}>
                                        {{ \Carbon\Carbon::create()->month($i)->locale('id')->monthName }}
                                    </option>
                                @endfor
                            </select>
                        </label>
                        <label class="block text-sm">
                            <span class="text-gray-700 dark:text-gray-400">Tahun</span>
                            <select name="tahun"
                                class="block w-full mt-1 text-sm form-select dark:bg-gray-700 dark:text-gray-300">
                                @for($y = now()->year; $y >= 2020; $y--)
                                    <option value="{{ $y }}" {{ now()->year == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </label>
                    </div>
                    <button type="submit"
                        class="w-full px-4 py-2 text-sm font-medium text-white bg-purple-600 rounded-lg hover:bg-purple-700">
                        Lihat Rekap
                    </button>
                </form>
            </div>

            {{-- Rekap Poin --}}
            <div class="p-6 bg-white rounded-lg shadow-xs dark:bg-gray-800">
                <h3 class="mb-1 text-lg font-semibold text-gray-700 dark:text-gray-200">
                    Rekap Poin Pelanggaran
                </h3>
                <p class="mb-4 text-sm text-gray-500 dark:text-gray-400">
                    Rekap poin pelanggaran siswa per bulan
                </p>
                <form method="GET" action="{{ route('admin.laporan.rekap-poin') }}">
                    <label class="block text-sm mb-3">
                        <span class="text-gray-700 dark:text-gray-400">Kelas <span class="text-gray-400">(opsional)</span></span>
                        <select name="kelas_id"
                            class="block w-full mt-1 text-sm form-select dark:bg-gray-700 dark:text-gray-300">
                            <option value="">Semua Kelas</option>
                            @foreach($kelas as $k)
                                <option value="{{ $k->id_kelas }}">{{ $k->nama_kelas }}</option>
                            @endforeach
                        </select>
                    </label>
                    <div class="grid grid-cols-2 gap-3 mb-4">
                        <label class="block text-sm">
                            <span class="text-gray-700 dark:text-gray-400">Bulan</span>
                            <select name="bulan"
                                class="block w-full mt-1 text-sm form-select dark:bg-gray-700 dark:text-gray-300">
                                @for($i = 1; $i <= 12; $i++)
                                    <option value="{{ $i }}" {{ now()->month == $i ? 'selected' : '' }}>
                                        {{ \Carbon\Carbon::create()->month($i)->locale('id')->monthName }}
                                    </option>
                                @endfor
                            </select>
                        </label>
                        <label class="block text-sm">
                            <span class="text-gray-700 dark:text-gray-400">Tahun</span>
                            <select name="tahun"
                                class="block w-full mt-1 text-sm form-select dark:bg-gray-700 dark:text-gray-300">
                                @for($y = now()->year; $y >= 2020; $y--)
                                    <option value="{{ $y }}" {{ now()->year == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </label>
                    </div>
                    <button type="submit"
                        class="w-full px-4 py-2 text-sm font-medium text-white bg-purple-600 rounded-lg hover:bg-purple-700">
                        Lihat Rekap
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-layouts.admin>