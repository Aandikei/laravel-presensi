<x-layouts.admin>
    <x-slot:title>Rekap Absensi</x-slot:title>

    <div class="container px-6 mx-auto">
        <div class="my-6">
            <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
                Rekap Absensi
            </h2>
            <p class="text-gray-500 dark:text-gray-400 mt-1">
                Riwayat input absensi per jadwal
            </p>
        </div>

        @if(session('success'))
            <div class="px-4 py-3 mb-4 text-sm text-green-700 bg-green-100 rounded-lg dark:bg-green-800 dark:text-green-200">
                {{ session('success') }}
            </div>
        @endif

        {{-- Filter --}}
        <div class="p-4 mb-6 bg-white dark:bg-gray-800 rounded-lg shadow-xs">
            <form method="GET" class="flex flex-wrap items-end gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Bulan</label>
                    <select name="bulan" class="w-40 text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                        @foreach(range(1, 12) as $m)
                            <option value="{{ $m }}" {{ (int)$bulan === $m ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create()->month($m)->locale('id')->monthName }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Tahun</label>
                    <select name="tahun" class="w-24 text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                        @for($y = now()->year; $y >= now()->year - 2; $y--)
                            <option value="{{ $y }}" {{ (int)$tahun === $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Mata Pelajaran</label>
                    <select name="mapel_id" class="w-48 text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                        <option value="">Semua Mapel</option>
                        @foreach($mapels as $mapel)
                            <option value="{{ $mapel->id_mapel }}" {{ (int)$mapelId === $mapel->id_mapel ? 'selected' : '' }}>
                                {{ $mapel->nama_mapel }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-purple-600 rounded-lg hover:bg-purple-700">
                    Filter
                </button>
                                <a href="{{ route('guru.absensi.rekap.export', request()->only(['bulan', 'tahun', 'mapel_id'])) }}"
                                    class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700">
                                    <svg class="inline w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    Export Excel
                                </a>
            </form>
        </div>

        {{-- Tabel --}}
        <div class="w-full overflow-hidden rounded-lg shadow-xs">
            <div class="w-full overflow-x-auto">
                <table class="w-full whitespace-no-wrap">
                    <thead>
                        <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b dark:border-gray-700 bg-gray-50 dark:text-gray-400 dark:bg-gray-800">
                            <th class="px-4 py-3">Tanggal</th>
                            <th class="px-4 py-3">Kelas</th>
                            <th class="px-4 py-3">Mapel</th>
                            <th class="px-4 py-3">Jam</th>
                            <th class="px-4 py-3">Nama Siswa</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Ket.</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y dark:divide-gray-700 dark:bg-gray-800">
                        @forelse($riwayat as $a)
                            <tr class="text-gray-700 dark:text-gray-400">
                                <td class="px-4 py-3 text-sm">{{ \Carbon\Carbon::parse($a->tanggal)->locale('id')->isoFormat('D MMM YYYY') }}</td>
                                <td class="px-4 py-3 text-sm">{{ $a->jadwal->kurikulum->kelas->nama_kelas ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm">{{ $a->jadwal->kurikulum->mataPelajaran->nama_mapel ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm">{{ substr($a->jadwal->jam_mulai, 0, 5) }} - {{ substr($a->jadwal->jam_selesai, 0, 5) }}</td>
                                <td class="px-4 py-3 text-sm font-medium">{{ $a->registrasi->siswa->nama_siswa ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm">
                                    @switch($a->status)
                                        @case('Hadir')
                                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-700">Hadir</span>
                                            @break
                                        @case('Sakit')
                                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-700">Sakit</span>
                                            @break
                                        @case('Izin')
                                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-700">Izin</span>
                                            @break
                                        @case('Alpa')
                                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-700">Alpa</span>
                                            @break
                                        @case('Terlambat')
                                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-orange-100 text-orange-700">Terlambat</span>
                                            @break
                                        @case('Cabut')
                                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-pink-100 text-pink-700">Cabut</span>
                                            @break
                                        @default
                                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-700">{{ $a->status }}</span>
                                    @endswitch
                                </td>
                                <td class="px-4 py-3 text-sm">{{ $a->keterangan ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-sm text-center text-gray-500 dark:text-gray-400">
                                    Tidak ada data absensi untuk periode ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3 border-t dark:border-gray-700">
                {{ $riwayat->links() }}
            </div>
        </div>
    </div>
</x-layouts.admin>
