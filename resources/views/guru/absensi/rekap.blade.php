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
        <div class="p-4 mb-6 bg-white dark:bg-gray-800 rounded-lg shadow-xs dark:shadow-none dark:border dark:border-gray-700">
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

        {{-- Tabel Grouped --}}
        <div class="w-full overflow-hidden rounded-lg shadow-xs">
            <div class="w-full overflow-x-auto bg-white dark:bg-gray-800 p-4">
                @if($riwayat->isNotEmpty())
                <table id="tabel-rekap" class="w-full whitespace-nowrap">
                    <thead>
                        <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b dark:border-gray-700 bg-gray-50 dark:text-gray-400 dark:bg-gray-900/50">
                            <th class="px-4 py-3">Tanggal</th>
                            <th class="px-4 py-3">Kelas</th>
                            <th class="px-4 py-3">Mapel</th>
                            <th class="px-4 py-3">Jam</th>
                            <th class="px-4 py-3 text-center">Siswa</th>
                            <th class="px-4 py-3 text-center text-green-600">H</th>
                            <th class="px-4 py-3 text-center text-blue-600">S</th>
                            <th class="px-4 py-3 text-center text-yellow-600">I</th>
                            <th class="px-4 py-3 text-center text-red-600">A</th>
                            <th class="px-4 py-3 text-center text-orange-600">T</th>
                            <th class="px-4 py-3 text-center text-pink-600">B</th>
                            <th class="px-4 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y dark:divide-gray-700 dark:bg-gray-800">
                        @foreach($riwayat as $r)
                        <tr class="text-gray-700 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700/70 transition-colors">
                            <td class="px-4 py-3 text-sm">{{ \Carbon\Carbon::parse($r->tanggal)->locale('id')->isoFormat('D MMM YYYY') }}</td>
                            <td class="px-4 py-3 text-sm font-medium">{{ $r->kelas_nama }}</td>
                            <td class="px-4 py-3 text-sm">{{ $r->mapel_nama }}</td>
                            <td class="px-4 py-3 text-sm">{{ $r->jam }}</td>
                            <td class="px-4 py-3 text-sm text-center">{{ $r->total_siswa }}</td>
                            <td class="px-4 py-3 text-sm text-center font-semibold text-green-600">{{ $r->hadir }}</td>
                            <td class="px-4 py-3 text-sm text-center font-semibold text-blue-600">{{ $r->sakit }}</td>
                            <td class="px-4 py-3 text-sm text-center font-semibold text-yellow-600">{{ $r->izin }}</td>
                            <td class="px-4 py-3 text-sm text-center font-semibold text-red-600">{{ $r->alpa }}</td>
                            <td class="px-4 py-3 text-sm text-center font-semibold text-orange-600">{{ $r->terlambat }}</td>
                            <td class="px-4 py-3 text-sm text-center font-semibold text-pink-600">{{ $r->bolos }}</td>
                            <td class="px-4 py-3 text-sm text-center">
                                <a href="{{ route('guru.absensi.rekap.detail', ['jadwal_id' => $r->jadwal_id, 'tanggal' => \Carbon\Carbon::parse($r->tanggal)->format('Y-m-d')]) }}"
                                    class="px-3 py-1 text-xs font-medium text-purple-700 bg-purple-100 rounded-full hover:bg-purple-200 dark:bg-purple-900/30 dark:text-purple-300">
                                    Detail
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            </div>
            <div class="px-4 py-3 border-t dark:border-gray-700 bg-white dark:bg-gray-800">
                {{ $riwayat->links() }}
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        $(document).ready(function() {
            $('#tabel-rekap').DataTable({
                paging: false,
                info: false,
                ordering: true,
                searching: true,
                language: { url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json' }
            });
        });
    </script>
    @endpush
                @else
                <div class="w-full px-5 py-8 text-center text-gray-500 dark:text-gray-400">
                    Tidak ada data absensi untuk periode ini.
                </div>
                @endif
</x-layouts.admin>