<x-layouts.admin>
    <x-slot:title>Rekap Absensi</x-slot:title>

    <div class="container px-6 mx-auto">
        <div class="my-6">
            <x-breadcrumb :items="[
                ['label' => 'Dashboard', 'url' => route('guru.dashboard')],
                ['label' => 'Absensi', 'url' => route('guru.absensi.index')],
                ['label' => 'Rekap'],
            ]" />
            <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
                Rekap Absensi
            </h2>
            <p class="text-gray-500 dark:text-gray-400 mt-1">
                Riwayat input absensi per jadwal
            </p>
        </div>

        {{-- Filter --}}
        <div id="filter-section" class="p-4 mb-6 bg-white dark:bg-gray-800 rounded-lg shadow-xs dark:shadow-none dark:border dark:border-gray-700">
            <div class="flex flex-wrap items-end gap-4">
                <form id="filter-form" method="GET" class="flex flex-wrap items-end gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Bulan</label>
                        <select name="bulan" class="filter-select w-40 text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                            @foreach(range(1, 12) as $m)
                                <option value="{{ $m }}" {{ (int)$bulan === $m ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::create()->month($m)->locale('id')->monthName }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Tahun</label>
                        <select name="tahun" class="filter-select w-24 text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                            @for($y = now()->year; $y >= now()->year - 2; $y--)
                                <option value="{{ $y }}" {{ (int)$tahun === $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Mata Pelajaran</label>
                        <select name="mapel_id" class="filter-select w-48 text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                            <option value="">Semua Mapel</option>
                            @foreach($mapels as $mapel)
                                <option value="{{ $mapel->id_mapel }}" {{ (int)$mapelId === $mapel->id_mapel ? 'selected' : '' }}>
                                    {{ $mapel->nama_mapel }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Tingkat</label>
                        <select name="tingkat" class="filter-select w-24 text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                            <option value="">Semua</option>
                            @foreach($tingkatList as $t)
                                <option value="{{ $t }}" {{ (string)$tingkat === (string)$t ? 'selected' : '' }}>{{ $t }}</option>
                            @endforeach
                        </select>
                    </div>
                    @if($jurusanList->isNotEmpty())
                    <div>
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Jurusan</label>
                        <select name="jurusan" class="filter-select w-24 text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                            <option value="">Semua</option>
                            @foreach($jurusanList as $j)
                                <option value="{{ $j->id_jurusan }}" {{ (string)$jurusan === (string)$j->id_jurusan ? 'selected' : '' }}>{{ $j->kode_jurusan }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                </form>
                <form method="POST" action="{{ route('guru.absensi.rekap.export') }}">
                    @csrf
                    <input type="hidden" name="bulan" value="{{ $bulan }}">
                    <input type="hidden" name="tahun" value="{{ $tahun }}">
                    @if($mapelId)
                        <input type="hidden" name="mapel_id" value="{{ $mapelId }}">
                    @endif
                    @if($tingkat)
                        <input type="hidden" name="tingkat" value="{{ $tingkat }}">
                    @endif
                    @if($jurusan)
                        <input type="hidden" name="jurusan" value="{{ $jurusan }}">
                    @endif
                    <button type="submit"
                        class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        Export Excel
                    </button>
                </form>
            </div>
        </div>

        {{-- Tabel Grouped --}}
        <div id="table-section" class="w-full overflow-hidden rounded-lg shadow-xs">
            <div class="w-full overflow-x-auto bg-white dark:bg-gray-800 p-4">
                @if($riwayat->isNotEmpty())
                <table id="tabel-rekap" class="w-full whitespace-nowrap">
                    <thead>
                        <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b dark:border-gray-700 bg-gray-50 dark:text-gray-400 dark:bg-gray-900/50">
                            <th class="px-4 py-3">Tanggal</th>
                            <th class="px-4 py-3">Kelas</th>
                            <th class="px-4 py-3">Mapel</th>
                            <th class="px-4 py-3">Jam</th>
                            <th class="px-4 py-3">Guru</th>
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
                            <td class="px-4 py-3 text-sm">
                                {{ $r->guru_nama }}
                                @if($r->guru && $r->guru->transfer_token && !$r->guru->isTransferTokenExpired())
                                    <span class="px-2 py-0.5 text-xs font-semibold text-orange-700 bg-orange-100 rounded-full dark:bg-orange-900/30 dark:text-orange-400">Mutasi</span>
                                @elseif($r->guru && $r->guru->instansi_id !== $guru->instansi_id)
                                    <span class="px-2 py-0.5 text-xs font-semibold text-blue-700 bg-blue-100 rounded-full dark:bg-blue-900/30 dark:text-blue-400">Pindah</span>
                                @elseif($r->guru && $r->guru->status_label)
                                    <span class="px-2 py-0.5 text-xs font-semibold text-red-700 bg-red-100 rounded-full dark:bg-red-900/30 dark:text-red-400">{{ $r->guru->status_label }}</span>
                                @endif
                            </td>
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
        </div>
    </div>

    @push('scripts')
    <script>
        function initTable() {
            if ($.fn.DataTable.isDataTable('#tabel-rekap')) {
                $('#tabel-rekap').DataTable().destroy();
            }
            $('#tabel-rekap').DataTable({
                paging: true,
                pageLength: 10,
                lengthMenu: [10, 25, 50, 100],
                info: true,
                ordering: true,
                searching: true,
                language: { url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json' }
            });
        }

        $(document).ready(function() {
            initTable();

            $('.filter-select').on('change', function() {
                var params = $('#filter-form').serialize();
                var url = window.location.pathname + '?' + params;

                $.get(url, function(html) {
                    var newTable = $(html).find('#table-section').html();
                    $('#table-section').html(newTable);
                    initTable();
                });
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