<x-layouts.admin>
    <x-slot:title>Monitor Absensi</x-slot:title>

    <div class="container px-6 mx-auto">
        <div class="flex items-center justify-between my-6">
            <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">Monitor Absensi</h2>
        </div>

        {{-- Filter --}}
        <div class="mb-4 p-4 bg-white rounded-lg shadow-xs dark:shadow-none dark:border dark:border-gray-700 dark:bg-gray-800">
            <div class="flex flex-wrap items-center gap-4">
                <div class="flex items-center gap-2">
                    <label class="text-sm text-gray-700 dark:text-gray-400">Kelas:</label>
                    <select id="filter-kelas" class="text-sm dark:bg-gray-700 dark:text-gray-300">
                        <option value="">Semua</option>
                        @foreach($kelas as $k)
                            <option value="{{ $k->id_kelas }}">{{ $k->nama_kelas }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-center gap-2">
                    <label class="text-sm text-gray-700 dark:text-gray-400">Tanggal:</label>
                    <input type="date" id="filter-tanggal"
                        value="{{ now()->toDateString() }}"
                        class="text-sm form-input dark:bg-gray-700 dark:text-gray-300" />
                </div>
            </div>
        </div>

        <div class="w-full overflow-hidden rounded-lg shadow-xs">
            <div class="w-full overflow-x-auto bg-white dark:bg-gray-800 p-4">
                <table id="tabel-absensi" class="w-full whitespace-nowrap">
                    <thead>
                        <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b dark:border-gray-700 bg-gray-50 dark:text-gray-400 dark:bg-gray-900/50">
                            <th class="px-4 py-3">#</th>
                            <th class="px-4 py-3">Kelas</th>
                            <th class="px-4 py-3">Mata Pelajaran</th>
                            <th class="px-4 py-3">Guru</th>
                            <th class="px-4 py-3">Hari</th>
                            <th class="px-4 py-3">Jam</th>
                            <th class="px-4 py-3">Jumlah Siswa</th>
                            <th class="px-4 py-3">Status</th>
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
            var table = $('#tabel-absensi').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('admin.absensi.index') }}',
                    data: function(d) {
                        d.kelas_id = $('#filter-kelas').val();
                        d.tanggal  = $('#filter-tanggal').val();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'kelas'},
                    { data: 'mata_pelajaran'},
                    { data: 'guru' },
                    { data: 'hari' },
                    { data: 'jam', orderable: false, searchable: false },
                    { data: 'jumlah_siswa', orderable: false, searchable: false },
                    { data: 'status_kunci', orderable: false, searchable: false },
                    { data: 'aksi', orderable: false, searchable: false },
                ],
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
                }
            });

            $('#filter-kelas, #filter-tanggal').on('change', function() {
                table.draw();
            });

            // Auto load hari ini
            table.draw();
        });
    </script>
    @endpush
</x-layouts.admin>