<x-layouts.admin>
    <x-slot:title>Data Siswa</x-slot:title>

    <div class="container px-6 mx-auto">
        <div class="flex items-center justify-between my-6">
            <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
                Data Siswa
            </h2>
            <div class="flex items-center gap-2">
                @can('manage-siswa')
                    <a href="{{ route('admin.siswa.template') }}"
                        class="flex items-center gap-1 px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        Template
                    </a>
                    <button id="btn-import"
                        class="flex items-center gap-1 px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l4-4m0 0l4 4m-4-4v12"/>
                        </svg>
                        Import Excel
                    </button>
                    <a href="{{ route('admin.siswa.create') }}"
                        class="px-4 py-2 text-sm font-medium text-white bg-purple-600 rounded-lg hover:bg-purple-700">
                        + Tambah Siswa
                    </a>
                @endcan
            </div>
        </div>

        @if(session('gagalList') && count(session('gagalList')) > 0)
            <div id="gagal-import" class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg dark:bg-red-900/20 dark:border-red-800">
                <div class="flex items-center justify-between mb-3">
                    <h4 class="text-sm font-semibold text-red-700 dark:text-red-400">
                        Data gagal diimport ({{ count(session('gagalList')) }})
                    </h4>
                    <button onclick="document.getElementById('gagal-import').remove()"
                        class="text-red-400 hover:text-red-600 text-lg leading-none">&times;</button>
                </div>
                <div class="overflow-x-auto max-h-64 overflow-y-auto">
                    <table class="w-full text-sm text-left">
                        <thead>
                            <tr class="text-xs text-red-600 uppercase border-b border-red-200 dark:border-red-800">
                                <th class="px-3 py-2">Nama</th>
                                <th class="px-3 py-2">NISN</th>
                                <th class="px-3 py-2">Alasan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-red-100 dark:divide-red-900">
                            @foreach(session('gagalList') as $g)
                                <tr class="text-red-800 dark:text-red-300">
                                    <td class="px-3 py-1.5">{{ $g['nama'] }}</td>
                                    <td class="px-3 py-1.5">{{ $g['nisn'] }}</td>
                                    <td class="px-3 py-1.5">{{ $g['alasan'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- Filter --}}
        <div class="mb-4 p-4 bg-white rounded-lg shadow-xs dark:shadow-none dark:border dark:border-gray-700 dark:bg-gray-800 flex items-center gap-4 flex-wrap">
            <label class="text-sm text-gray-700 dark:text-gray-400">Filter Kelas:</label>
            <select id="filter-kelas" class="text-sm dark:bg-gray-700 dark:text-gray-300">
                <option value="">Semua Kelas</option>
                @foreach($kelas as $k)
                    <option value="{{ $k->id_kelas }}">{{ $k->nama_kelas }}</option>
                @endforeach
            </select>

            <label class="text-sm text-gray-700 dark:text-gray-400">Filter Status:</label>
            <select id="filter-status" class="text-sm dark:bg-gray-700 dark:text-gray-300">
                <option value="">Semua Status</option>
                <option value="Aktif">Aktif</option>
                <option value="Keluar">Keluar</option>
                <option value="Pindah">Pindah</option>
                <option value="Alumni">Alumni</option>
                <option value="belum_terdaftar">Belum Terdaftar</option>
            </select>
        </div>

        <div class="w-full overflow-hidden rounded-lg shadow-xs">
            <div class="w-full overflow-x-auto bg-white dark:bg-gray-800 p-4">
                <table id="tabel-siswa" class="w-full whitespace-nowrap">
                    <thead>
                        <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b dark:border-gray-700 bg-gray-50 dark:text-gray-400 dark:bg-gray-900/50">
                            <th class="px-4 py-3">#</th>
                            <th class="px-4 py-3">Nama Siswa</th>
                            <th class="px-4 py-3">NISN</th>
                            <th class="px-4 py-3">Email</th>
                            <th class="px-4 py-3">Jenis Kelamin</th>
                            <th class="px-4 py-3">Kelas Aktif</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Total Poin</th>
                            <th class="px-4 py-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y dark:divide-gray-700 dark:bg-gray-800">
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Modal Import — di luar container, langsung di body --}}
    <div id="modal-import"
        style="display:none; position:fixed; inset:0; z-index:999999; background:rgba(0,0,0,0.5);"
        class="items-center justify-center">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-md mx-auto mt-32 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200">
                    Import Siswa via Excel
                </h3>
                <button id="btn-tutup-modal"
                    class="text-gray-400 hover:text-gray-600 text-xl font-bold">✕</button>
            </div>

            <div class="mb-4 px-3 py-2 text-xs text-blue-700 bg-blue-50 rounded-lg dark:bg-blue-900/30 dark:text-blue-300">
                Download template terlebih dahulu, isi datanya, lalu upload di sini.
                Password default siswa = NISN masing-masing.
            </div>

            <form method="POST" action="{{ route('admin.siswa.import') }}" enctype="multipart/form-data">
                @csrf

                {{-- Tahun Ajaran --}}
                <label class="block text-sm mb-4">
                    <span class="text-gray-700 dark:text-gray-400">
                        Tahun Ajaran
                        <span class="text-gray-400">(untuk registrasi kelas otomatis)</span>
                    </span>
                    <select name="tahun_id"
                        class="block w-full mt-1 text-sm dark:bg-gray-700 dark:text-gray-300">
                        <option value="">-- Tidak daftarkan ke kelas --</option>
                        @foreach($tahunAjaran as $tahun)
                            <option value="{{ $tahun->id_tahun }}" {{ $tahun->is_aktif ? 'selected' : '' }}>
                                {{ $tahun->nama_tahun }} - {{ $tahun->semester }}
                                {{ $tahun->is_aktif ? '(Aktif)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </label>

                <div class="mb-4 px-3 py-2 text-xs text-yellow-700 bg-yellow-50 rounded-lg dark:bg-yellow-900/30 dark:text-yellow-300">
                    Nama kelas di kolom <strong>nama_kelas</strong> harus sama persis dengan nama kelas di sistem.
                </div>

                <label class="block text-sm mb-4">
                    <span class="text-gray-700 dark:text-gray-400">File Excel (.xlsx)</span>
                    <div class="relative mt-1">
                        <input type="file" name="file" accept=".xlsx,.xls" id="file-import-input" required
                            class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" />
                        <div class="flex items-center justify-between px-3 py-2 text-sm border border-gray-300 rounded-lg cursor-pointer dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                            <span id="file-import-label" class="truncate text-gray-400">Pilih file...</span>
                            <svg class="w-5 h-5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                        </div>
                    </div>
                    <script>
                        document.getElementById('file-import-input').addEventListener('change', function() {
                            document.getElementById('file-import-label').textContent = this.files[0]?.name || 'Pilih file...';
                        });
                    </script>
                </label>

                <div class="flex gap-3">
                    <button type="submit"
                        class="flex-1 px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700">
                        Import
                    </button>
                    <button type="button" id="btn-batal-modal"
                        class="flex-1 px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        $(document).ready(function() {
            // DataTables
            var table = $('#tabel-siswa').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('admin.siswa.index') }}',
                    data: function(d) {
                        d.kelas_id = $('#filter-kelas').val();
                        d.status = $('#filter-status').val();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'nama_siswa' },
                    { data: 'nisn' },
                    { data: 'email' },
                    { data: 'jenis_kelamin' },
                    { data: 'kelas', orderable: false },
                    { data: 'status_badge', orderable: false, searchable: false },
                    { data: 'total_poin', orderable: false, searchable: false },
                    { data: 'aksi', orderable: false, searchable: false },
                ],
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
                }
            });

            $('#filter-kelas, #filter-status').on('change', function() {
                table.draw();
            });

            // Modal
            const modal   = document.getElementById('modal-import');
            const btnOpen = document.getElementById('btn-import');
            const btnTutup = document.getElementById('btn-tutup-modal');
            const btnBatal = document.getElementById('btn-batal-modal');

            // Pindah modal ke body supaya tidak tertutup tabel
            document.body.appendChild(modal);

            btnOpen.addEventListener('click', function() {
                modal.style.display = 'flex';
            });

            btnTutup.addEventListener('click', function() {
                modal.style.display = 'none';
            });

            btnBatal.addEventListener('click', function() {
                modal.style.display = 'none';
            });

            // Tutup kalau klik backdrop
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    modal.style.display = 'none';
                }
            });
        });
    </script>
    @endpush
</x-layouts.admin>