<x-layouts.admin>
    <x-slot:title>Rekap Absensi</x-slot:title>

    <div class="container px-6 mx-auto">
        <div class="flex items-center justify-between my-6">
            <div>
                <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">Rekap Absensi</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    {{ $kelas->nama_kelas }} — {{ ucfirst($bulanNama) }} {{ $request->tahun }}
                </p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.laporan.export-absensi-excel', $request->all()) }}"
                    class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Excel
                </a>
                <a href="{{ route('admin.laporan.export-absensi-pdf', $request->all()) }}"
                    class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    PDF
                </a>
                <a href="{{ route('admin.laporan.index') }}"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300">
                    ← Kembali
                </a>
            </div>
        </div>

        <div class="w-full overflow-x-auto bg-white dark:bg-gray-800 rounded-lg shadow-xs">
            <table class="w-full">
                <thead>
                    <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b dark:border-gray-700 bg-gray-50 dark:text-gray-400 dark:bg-gray-800">
                        <th class="px-4 py-3">No</th>
                        <th class="px-4 py-3">Nama Siswa</th>
                        <th class="px-4 py-3">NISN</th>
                        <th class="px-4 py-3 text-center text-green-600">Hadir</th>
                        <th class="px-4 py-3 text-center text-blue-600">Sakit</th>
                        <th class="px-4 py-3 text-center text-yellow-600">Izin</th>
                        <th class="px-4 py-3 text-center text-red-600">Alpa</th>
                        <th class="px-4 py-3 text-center text-orange-600">Terlambat</th>
                        <th class="px-4 py-3 text-center text-pink-600">Bolos</th>
                        <th class="px-4 py-3 text-center">Total</th>
                        <th class="px-4 py-3 text-center">% Hadir</th>
                    </tr>
                </thead>
                <tbody class="divide-y dark:divide-gray-700">
                    @forelse($registrasi as $i => $reg)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ $i + 1 }}</td>
                            <td class="px-4 py-3 font-medium text-gray-700 dark:text-gray-200">{{ $reg->siswa->nama_siswa }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $reg->siswa->nisn }}</td>
                            <td class="px-4 py-3 text-center font-semibold text-green-600">{{ $reg->hadir }}</td>
                            <td class="px-4 py-3 text-center font-semibold text-blue-600">{{ $reg->sakit }}</td>
                            <td class="px-4 py-3 text-center font-semibold text-yellow-600">{{ $reg->izin }}</td>
                            <td class="px-4 py-3 text-center font-semibold text-red-600">{{ $reg->alpa }}</td>
                            <td class="px-4 py-3 text-center font-semibold text-orange-600">{{ $reg->terlambat }}</td>
                            <td class="px-4 py-3 text-center font-semibold text-pink-600">{{ $reg->bolos }}</td>
                            <td class="px-4 py-3 text-center text-gray-700 dark:text-gray-200">{{ $reg->total }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="px-2 py-1 text-xs font-medium rounded-full
                                    {{ $reg->persen >= 75 ? 'text-green-700 bg-green-100' : ($reg->persen >= 50 ? 'text-yellow-700 bg-yellow-100' : 'text-red-700 bg-red-100') }}">
                                    {{ $reg->persen }}%
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="px-4 py-8 text-center text-gray-500">
                                Tidak ada data absensi untuk periode ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.admin>