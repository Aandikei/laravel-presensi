<x-layouts.admin>
    <x-slot:title>Export Saya</x-slot:title>

    <div class="container px-6 mx-auto">
        <div class="my-6">
            <x-breadcrumb :items="[
                ['label' => 'Dashboard', 'url' => $isAdmin ? route('admin.dashboard') : route('guru.dashboard')],
                ['label' => 'Export Saya'],
            ]" />
            <div class="flex items-center justify-between">
                <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">Export Saya</h2>
            </div>
        </div>

        <div class="w-full overflow-hidden rounded-lg shadow-xs">
            <div class="w-full overflow-x-auto bg-white dark:bg-gray-800 p-4">
                <table id="tabel-export" class="w-full whitespace-nowrap">
                    <thead>
                        <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b dark:border-gray-700 bg-gray-50 dark:text-gray-400 dark:bg-gray-900/50">
                            <th class="px-4 py-3">#</th>
                            <th class="px-4 py-3">Tipe</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Dibuat</th>
                            <th class="px-4 py-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y dark:divide-gray-700 dark:bg-gray-800"></tbody>
                </table>
            </div>
        </div>

        <div class="mt-6">
            @if($isAdmin)
            <a href="{{ route('admin.laporan.index') }}"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300">
                &larr; Kembali ke Laporan
            </a>
            @endif
        </div>
    </div>

    @push('scripts')
    <script>
        $(document).ready(function() {
            $('#tabel-export').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ $isAdmin ? route('admin.laporan.exports') : route('guru.exports') }}',
                columns: [
                    { data: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'type_label' },
                    { data: 'status_badge', orderable: false, searchable: false },
                    { data: 'created_at' },
                    { data: 'aksi', orderable: false, searchable: false },
                ],
                order: [[3, 'desc']],
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
                }
            });
        });
    </script>
    @endpush
</x-layouts.admin>
