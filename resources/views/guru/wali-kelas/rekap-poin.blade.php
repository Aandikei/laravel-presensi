<x-layouts.admin>
    <x-slot:title>Rekap Poin - {{ $kelasSaya->nama_kelas }}</x-slot:title>

    <div class="container px-6 mx-auto">
        <div class="my-6">
            <x-breadcrumb :items="[
                ['label' => 'Dashboard', 'url' => route('guru.dashboard')],
                ['label' => 'Wali Kelas', 'url' => route('guru.wali-kelas.siswa-poin')],
                ['label' => 'Rekap Poin'],
            ]" />
        </div>
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
                    Rekap Poin Pelanggaran
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Kelas {{ $kelasSaya->nama_kelas }} — {{ ucfirst($bulanNama) }} {{ $tahun }}
                </p>
            </div>
            <a href="{{ route('guru.wali-kelas.log-poin') }}"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300">
                ← Kembali
            </a>
        </div>

        {{-- Filter + Export --}}
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
                </form>
                <form method="POST" action="{{ route('guru.wali-kelas.rekap-poin.export-excel') }}">
                    @csrf
                    <input type="hidden" name="bulan" value="{{ $bulan }}">
                    <input type="hidden" name="tahun" value="{{ $tahun }}">
                    <button type="submit"
                        class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        Excel
                    </button>
                </form>
                <form method="POST" action="{{ route('guru.wali-kelas.rekap-poin.export-pdf') }}">
                    @csrf
                    <input type="hidden" name="bulan" value="{{ $bulan }}">
                    <input type="hidden" name="tahun" value="{{ $tahun }}">
                    <button type="submit"
                        class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        PDF
                    </button>
                </form>
            </div>
        </div>

        {{-- Tabel --}}
        <div id="table-section" class="w-full overflow-hidden rounded-lg shadow-xs">
            <div class="w-full overflow-x-auto bg-white dark:bg-gray-800 p-4">
            @if($siswa->isNotEmpty())
                <table id="tabel-rekap-poin" class="w-full whitespace-nowrap">
                    <thead>
                        <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b dark:border-gray-700 bg-gray-50 dark:text-gray-400 dark:bg-gray-900/50">
                            <th class="px-4 py-3">No</th>
                            <th class="px-4 py-3">Nama Siswa</th>
                            <th class="px-4 py-3">NISN</th>
                            <th class="px-4 py-3 text-center">Pelanggaran</th>
                            <th class="px-4 py-3">Pelanggaran Terakhir</th>
                            <th class="px-4 py-3 text-center">Total Poin</th>
                            <th class="px-4 py-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y dark:divide-gray-700">
                        @foreach($siswa as $i => $s)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/70 transition-colors">
                                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ $i + 1 }}</td>
                                <td class="px-4 py-3 font-medium text-gray-700 dark:text-gray-200">
                                    {{ $s->nama_siswa }}
                                    @if(!$s->isAktif())
                                        <span class="px-2 py-0.5 text-xs font-semibold text-red-700 bg-red-100 rounded-full dark:bg-red-900/30 dark:text-red-400">{{ $s->status_label }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-500">{{ $s->nisn }}</td>
                                <td class="px-4 py-3 text-center text-gray-700 dark:text-gray-200">{{ $s->jumlah_pelanggaran }}</td>
                                <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                    {{ $s->tanggal_terakhir ? \Carbon\Carbon::parse($s->tanggal_terakhir)->locale('id')->isoFormat('D MMM YYYY') : '-' }}
                                </td>
                                <td class="px-4 py-3 text-center font-bold
                                    {{ $s->total_poin >= 100 ? 'text-red-600' : ($s->total_poin >= 50 ? 'text-yellow-600' : 'text-green-600') }}">
                                    {{ $s->total_poin }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="px-2 py-1 text-xs font-medium rounded-full
                                        {{ $s->status_poin == 'PERHATIAN' ? 'text-red-700 bg-red-100' :
                                           ($s->status_poin == 'WASPADA' ? 'text-yellow-700 bg-yellow-100' : 'text-green-700 bg-green-100') }}">
                                        {{ $s->status_poin }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                @push('scripts')
                <script>
                    function initTable() {
                        if ($.fn.DataTable.isDataTable('#tabel-rekap-poin')) {
                            $('#tabel-rekap-poin').DataTable().destroy();
                        }
                        $('#tabel-rekap-poin').DataTable({
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
                    Tidak ada data poin untuk periode ini.
                </div>
            @endif
            </div>
        </div>
    </div>
</x-layouts.admin>
