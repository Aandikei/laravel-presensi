<x-layouts.admin>
    <x-slot:title>Kurikulum Kelas</x-slot:title>

    <div class="container px-6 mx-auto">
        <div class="flex items-center justify-between my-6">
            <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
                Kurikulum Kelas
            </h2>
            @can('manage-settings')
                <a href="{{ route('admin.kurikulum.create') }}"
                    class="px-4 py-2 text-sm font-medium text-white bg-purple-600 rounded-lg hover:bg-purple-700">
                    + Tambah Kurikulum
                </a>
            @endcan
        </div>

        {{-- Filter --}}
        <div class="p-4 mb-6 bg-white dark:bg-gray-800 rounded-lg shadow-xs dark:shadow-none dark:border dark:border-gray-700">
            <div class="flex flex-wrap items-end gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Kelas</label>
                    <select id="filter-kelas" class="w-48 text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                        <option value="">Semua Kelas</option>
                        @foreach($kelas as $k)
                            <option value="{{ $k->id_kelas }}">{{ $k->nama_kelas }} (Tingkat {{ $k->tingkat }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Mata Pelajaran</label>
                    <select id="filter-mapel" class="w-48 text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                        <option value="">Semua Mapel</option>
                        @foreach($mapel as $m)
                            <option value="{{ $m->id_mapel }}">{{ $m->nama_mapel }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Status Guru</label>
                    <select id="filter-status" class="w-40 text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                        <option value="">Semua Status</option>
                        <option value="Aktif">Aktif</option>
                        <option value="Keluar">Keluar</option>
                        <option value="Pensiun">Pensiun</option>
                        <option value="Pindah">Pindah</option>
                        <option value="Mutasi">Mutasi</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="w-full overflow-hidden rounded-lg shadow-xs">
            <div class="w-full overflow-x-auto bg-white dark:bg-gray-800 p-4">
                <table id="tabel-kurikulum" class="w-full whitespace-nowrap">
                    <thead>
                        <tr
                            class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b dark:border-gray-700 bg-gray-50 dark:text-gray-400 dark:bg-gray-900/50">
                            <th class="px-4 py-3">#</th>
                            <th class="px-4 py-3">Kelas</th>
                            <th class="px-4 py-3">Mata Pelajaran</th>
                            <th class="px-4 py-3">Guru</th>
                            <th class="px-4 py-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y dark:divide-gray-700 dark:bg-gray-800">
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            $(document).ready(function() {
                var table = $('#tabel-kurikulum').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: '{{ route('admin.kurikulum.index') }}',
                        data: function(d) {
                            d.kelas_id = $('#filter-kelas').val();
                            d.mapel_id = $('#filter-mapel').val();
                            d.status_guru = $('#filter-status').val();
                        }
                    },
                    columns: [{
                            data: 'DT_RowIndex',
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'kelas'
                        },
                        {
                            data: 'mata_pelajaran'
                        },
                        {
                            data: 'guru'
                        },
                        {
                            data: 'aksi',
                            orderable: false,
                            searchable: false
                        },
                    ],
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
                    }
                });

                $('#filter-kelas, #filter-mapel, #filter-status').on('change', function() {
                    table.ajax.reload();
                });
            });
        </script>
    @endpush
</x-layouts.admin>
