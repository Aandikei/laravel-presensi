<x-layouts.admin>
    <x-slot:title>Libur Nasional</x-slot:title>

    <div class="container px-6 mx-auto">
        <div class="my-6">
            <x-breadcrumb :items="[
                ['label' => 'Dashboard', 'url' => route('superadmin.dashboard')],
                ['label' => 'Libur Nasional'],
            ]" />
            <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
                Kelola Libur Nasional
            </h2>
        </div>

        {{-- Form Tambah Libur Nasional (Range Tanggal) --}}
        <div class="p-6 mb-6 bg-white rounded-lg shadow-xs dark:shadow-none dark:border dark:border-gray-700 dark:bg-gray-800">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200">
                        Tambah Libur Nasional
                    </h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Bisa input range tanggal (contoh: libur Idul Fitri 1-7 hari sekaligus)
                    </p>
                </div>
            </div>
            <form method="POST" action="{{ route('superadmin.hari-libur.store') }}">
                @csrf

                <div class="grid gap-4 md:grid-cols-3">
                    <label class="block text-sm">
                        <span class="text-gray-700 dark:text-gray-400">Nama Libur</span>
                        <input type="text" name="nama_libur" value="{{ old('nama_libur') }}"
                            placeholder="contoh: Idul Fitri"
                            class="block w-full mt-1 text-sm dark:bg-gray-700 dark:text-gray-300 @error('nama_libur') border-red-500 @enderror" />
                        @error('nama_libur')
                            <span class="text-xs text-red-500">{{ $message }}</span>
                        @enderror
                    </label>

                    <label class="block text-sm">
                        <span class="text-gray-700 dark:text-gray-400">Tanggal Mulai</span>
                        <input type="date" name="tanggal_mulai" value="{{ old('tanggal_mulai') }}"
                            class="block w-full mt-1 text-sm dark:bg-gray-700 dark:text-gray-300 @error('tanggal_mulai') border-red-500 @enderror" />
                        @error('tanggal_mulai')
                            <span class="text-xs text-red-500">{{ $message }}</span>
                        @enderror
                    </label>

                    <label class="block text-sm">
                        <span class="text-gray-700 dark:text-gray-400">Tanggal Selesai</span>
                        <input type="date" name="tanggal_selesai" value="{{ old('tanggal_selesai') }}"
                            class="block w-full mt-1 text-sm dark:bg-gray-700 dark:text-gray-300 @error('tanggal_selesai') border-red-500 @enderror" />
                        @error('tanggal_selesai')
                            <span class="text-xs text-red-500">{{ $message }}</span>
                        @enderror
                    </label>
                </div>

                <div class="mt-4">
                    <button type="submit"
                        class="px-4 py-2 text-sm font-medium text-white bg-purple-600 rounded-lg hover:bg-purple-700">
                        Tambah Libur Nasional
                    </button>
                </div>
            </form>
        </div>

        {{-- Tabel Libur Nasional --}}
        <div class="bg-white rounded-lg shadow-xs dark:shadow-none dark:border dark:border-gray-700 dark:bg-gray-800 p-4">
            <h3 class="mb-4 text-lg font-semibold text-gray-700 dark:text-gray-200">
                Daftar Libur Nasional
            </h3>
            <table id="tabel-libur-nasional" class="w-full whitespace-nowrap">
                <thead>
                    <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b dark:border-gray-700 bg-gray-50 dark:text-gray-400 dark:bg-gray-900/50">
                        <th class="px-4 py-3">#</th>
                        <th class="px-4 py-3">Tanggal</th>
                        <th class="px-4 py-3">Nama Libur</th>
                        <th class="px-4 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y dark:divide-gray-700 dark:bg-gray-800"></tbody>
            </table>
        </div>
    </div>

    @push('scripts')
    <script>
        $(document).ready(function() {
            $('#tabel-libur-nasional').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('superadmin.hari-libur.index') }}',
                order: [[1, 'asc']],
                columns: [
                    { data: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'tanggal' },
                    { data: 'nama_libur' },
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
