<x-layouts.admin>
    <x-slot:title>Master Poin Pelanggaran</x-slot:title>

    <div class="container px-6 mx-auto">
        <div class="flex items-center justify-between my-6">
            <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
                Master Poin Pelanggaran
            </h2>
            @can('manage-settings')
                <a href="{{ route('admin.master-poin.create') }}"
                    class="px-4 py-2 text-sm font-medium text-white bg-purple-600 rounded-lg hover:bg-purple-700">
                    + Tambah Pelanggaran
                </a>
            @endcan
        </div>

        @if(session('success'))
            <div class="px-4 py-3 mb-4 text-sm text-green-700 bg-green-100 rounded-lg dark:bg-green-800 dark:text-green-200">
                {{ session('success') }}
            </div>
        @endif

        <div class="w-full overflow-hidden rounded-lg shadow-xs">
            <div class="w-full overflow-x-auto bg-white dark:bg-gray-800 p-4">
                <table id="tabel-master-poin" class="w-full whitespace-nowrap">
                    <thead>
                        <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b dark:border-gray-700 bg-gray-50 dark:text-gray-400 dark:bg-gray-900/50">
                            <th class="px-4 py-3">#</th>
                            <th class="px-4 py-3">Nama Pelanggaran</th>
                            <th class="px-4 py-3">Deskripsi</th>
                            <th class="px-4 py-3">Poin</th>
                            <th class="px-4 py-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y dark:divide-gray-700 dark:bg-gray-800"></tbody>
                </table>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        $(document).ready(function() {
            $('#tabel-master-poin').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('admin.master-poin.index') }}',
                columns: [
                    { data: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'nama_pelanggaran' },
                    { data: 'deskripsi' },
                    { data: 'jumlah_poin' },
                    { data: 'aksi', orderable: false, searchable: false },
                ],
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
                }
            });
        });
    </script>
    @endpush
</x-layouts.admin>