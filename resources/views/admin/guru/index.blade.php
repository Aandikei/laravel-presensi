<x-layouts.admin>
    <x-slot:title>Data Guru</x-slot:title>

    <div class="container px-6 mx-auto">
        <div class="flex items-center justify-between my-6">
            <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
                Data Guru
            </h2>
            @can('manage-guru')
                <a href="{{ route('admin.guru.create') }}"
                    class="px-4 py-2 text-sm font-medium text-white bg-purple-600 rounded-lg hover:bg-purple-700">
                    + Tambah Guru
                </a>
            @endcan
        </div>

        {{-- Filter --}}
        <div class="mb-4 p-4 bg-white rounded-lg shadow-xs dark:shadow-none dark:border dark:border-gray-700 dark:bg-gray-800 flex items-center gap-4 flex-wrap">
            <label class="text-sm text-gray-700 dark:text-gray-400">Filter Status:</label>
            <select id="filter-status" class="text-sm dark:bg-gray-700 dark:text-gray-300">
                <option value="">Aktif</option>
                <option value="semua">Semua</option>
                <option value="mutasi">Mutasi</option>
                <option value="keluar">Keluar</option>
                <option value="pensiun">Pensiun</option>
            </select>
        </div>

        <div class="w-full overflow-hidden rounded-lg shadow-xs">
            <div class="w-full overflow-x-auto bg-white dark:bg-gray-800 p-4">
                <table id="tabel-guru" class="w-full whitespace-nowrap">
                    <thead>
                        <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b dark:border-gray-700 bg-gray-50 dark:text-gray-400 dark:bg-gray-900/50">
                            <th class="px-4 py-3">#</th>
                            <th class="px-4 py-3">Nama Guru</th>
                            <th class="px-4 py-3">NIP</th>
                            <th class="px-4 py-3">Email</th>
                            <th class="px-4 py-3">Jenis Kelamin</th>
                            <th class="px-4 py-3">No HP</th>
                            <th class="px-4 py-3">Wali Kelas</th>
                            <th class="px-4 py-3">Jabatan</th>
                            <th class="px-4 py-3">Status</th>
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
            var table = $('#tabel-guru').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('admin.guru.index') }}',
                    data: function (d) {
                        d.status_filter = $('#filter-status').val();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'nama_guru' },
                    { data: 'nip' },
                    { data: 'email' },
                    { data: 'jenis_kelamin' },
                    { data: 'no_hp' },
                    { data: 'wali_kelas', orderable: false },
                    { data: 'jabatan', orderable: false },
                    { data: 'status_guru', orderable: false },
                    { data: 'aksi', orderable: false, searchable: false },
                ],
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
                }
            });

            $('#filter-status').on('change', function () {
                table.ajax.reload();
            });
        });
    </script>
    @endpush
</x-layouts.admin>