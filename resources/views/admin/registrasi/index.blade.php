<x-layouts.admin>
    <x-slot:title>Registrasi Akademik</x-slot:title>

    <div class="container px-6 mx-auto">
        <div class="flex items-center justify-between my-6">
            <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
                Registrasi Akademik
            </h2>
            <a href="{{ route('admin.registrasi.create') }}"
                class="px-4 py-2 text-sm font-medium text-white bg-purple-600 rounded-lg hover:bg-purple-700">
                + Daftarkan Siswa
            </a>
        </div>

        @if(session('success'))
            <div class="px-4 py-3 mb-4 text-sm text-green-700 bg-green-100 rounded-lg dark:bg-green-800 dark:text-green-200">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="px-4 py-3 mb-4 text-sm text-red-700 bg-red-100 rounded-lg dark:bg-red-800 dark:text-red-200">
                {{ session('error') }}
            </div>
        @endif

        {{-- Filter --}}
        <div class="mb-4 p-4 bg-white rounded-lg shadow-xs dark:shadow-none dark:border dark:border-gray-700 dark:bg-gray-800">
            <div class="flex flex-wrap items-center gap-4">
                <div class="flex items-center gap-2">
                    <label class="text-sm text-gray-700 dark:text-gray-400">Tahun Ajaran:</label>
                    <select id="filter-tahun" class="text-sm dark:bg-gray-700 dark:text-gray-300">
                        <option value="">Semua</option>
                        @foreach($tahunAjaran as $tahun)
                            <option value="{{ $tahun->id_tahun }}" {{ $tahun->is_aktif ? 'selected' : '' }}>
                                {{ $tahun->nama_tahun }} - {{ $tahun->semester }}
                                {{ $tahun->is_aktif ? '(Aktif)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-center gap-2">
                    <label class="text-sm text-gray-700 dark:text-gray-400">Kelas:</label>
                    <select id="filter-kelas" class="text-sm dark:bg-gray-700 dark:text-gray-300">
                        <option value="">Semua</option>
                        @foreach($kelas as $k)
                            <option value="{{ $k->id_kelas }}">{{ $k->nama_kelas }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="w-full overflow-hidden rounded-lg shadow-xs">
            <div class="w-full overflow-x-auto bg-white dark:bg-gray-800 p-4">
                <table id="tabel-registrasi" class="w-full whitespace-nowrap">
                    <thead>
                        <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b dark:border-gray-700 bg-gray-50 dark:text-gray-400 dark:bg-gray-900/50">
                            <th class="px-4 py-3">#</th>
                            <th class="px-4 py-3">Nama Siswa</th>
                            <th class="px-4 py-3">NISN</th>
                            <th class="px-4 py-3">Kelas</th>
                            <th class="px-4 py-3">Tahun Ajaran</th>
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
            var table = $('#tabel-registrasi').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('admin.registrasi.index') }}',
                    data: function(d) {
                        d.tahun_id = $('#filter-tahun').val();
                        d.kelas_id = $('#filter-kelas').val();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'nama_siswa' },
                    { data: 'nisn' },
                    { data: 'kelas' },
                    { data: 'tahun_ajaran', orderable: false },
                    { data: 'aksi', orderable: false, searchable: false },
                ],
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
                }
            });

            $('#filter-tahun, #filter-kelas').on('change', function() {
                table.draw();
            });

            // Auto filter tahun aktif
            if ($('#filter-tahun').val()) {
                table.draw();
            }
        });
    </script>
    @endpush
</x-layouts.admin>