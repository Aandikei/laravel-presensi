<x-layouts.admin>
    <x-slot:title>Log Poin Siswa</x-slot:title>

    <div class="container px-6 mx-auto">
        <div class="my-6">
            <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">Log Poin Pelanggaran</h2>
        </div>

        @if(session('success'))
            <div class="px-4 py-3 mb-4 text-sm text-green-700 bg-green-100 rounded-lg dark:bg-green-800 dark:text-green-200">
                {{ session('success') }}
            </div>
        @endif

        @can('manage-settings')
        {{-- Form Tambah Poin --}}
        <div class="p-6 mb-6 bg-white rounded-lg shadow-xs dark:shadow-none dark:border dark:border-gray-700 dark:bg-gray-800">
            <h3 class="mb-4 text-lg font-semibold text-gray-700 dark:text-gray-200">
                Tambah Poin Pelanggaran
            </h3>
            <form method="POST" action="{{ route('admin.log-poin.store') }}">
                @csrf

                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <label class="block text-sm">
                        <span class="text-gray-700 dark:text-gray-400">Siswa</span>
                        <select name="siswa_id"
                            class="block w-full mt-1 text-sm dark:bg-gray-700 dark:text-gray-300 @error('siswa_id') border-red-500 @enderror">
                            <option value="">-- Pilih Siswa --</option>
                            @foreach($siswa as $s)
                                <option value="{{ $s->id_siswa }}" {{ old('siswa_id') == $s->id_siswa ? 'selected' : '' }}>
                                    {{ $s->nama_siswa }} ({{ $s->nisn }})
                                </option>
                            @endforeach
                        </select>
                        @error('siswa_id')
                            <span class="text-xs text-red-500">{{ $message }}</span>
                        @enderror
                    </label>

                    <label class="block text-sm">
                        <span class="text-gray-700 dark:text-gray-400">Jenis Pelanggaran</span>
                        <select name="poin_id"
                            class="block w-full mt-1 text-sm dark:bg-gray-700 dark:text-gray-300 @error('poin_id') border-red-500 @enderror">
                            <option value="">-- Pilih Pelanggaran --</option>
                            @foreach($masterPoin as $poin)
                                <option value="{{ $poin->id_poin }}" {{ old('poin_id') == $poin->id_poin ? 'selected' : '' }}>
                                    {{ $poin->nama_pelanggaran }} ({{ $poin->jumlah_poin }} poin)
                                </option>
                            @endforeach
                        </select>
                        @error('poin_id')
                            <span class="text-xs text-red-500">{{ $message }}</span>
                        @enderror
                    </label>

                    <label class="block text-sm">
                        <span class="text-gray-700 dark:text-gray-400">Tanggal</span>
                        <input type="date" name="tanggal"
                            value="{{ old('tanggal', now()->toDateString()) }}"
                            class="block w-full mt-1 text-sm form-input dark:bg-gray-700 dark:text-gray-300 @error('tanggal') border-red-500 @enderror" />
                        @error('tanggal')
                            <span class="text-xs text-red-500">{{ $message }}</span>
                        @enderror
                    </label>

                    <label class="block text-sm">
                        <span class="text-gray-700 dark:text-gray-400">Keterangan <span class="text-gray-400">(opsional)</span></span>
                        <input type="text" name="keterangan" value="{{ old('keterangan') }}"
                            placeholder="contoh: Ketahuan menyontek saat ujian"
                            class="block w-full mt-1 text-sm form-input dark:bg-gray-700 dark:text-gray-300" />
                    </label>
                </div>

                <button type="submit"
                    class="mt-4 px-4 py-2 text-sm font-medium text-white bg-purple-600 rounded-lg hover:bg-purple-700">
                    Tambah Poin
                </button>
            </form>
        </div>
        @endcan

        {{-- Tabel Log --}}
        <div class="bg-white rounded-lg shadow-xs dark:shadow-none dark:border dark:border-gray-700 dark:bg-gray-800 p-4">

            {{-- Filter --}}
            <div class="mb-4 flex items-center gap-3">
                <label class="text-sm text-gray-700 dark:text-gray-400">Filter Siswa:</label>
                <select id="filter-siswa" class="text-sm dark:bg-gray-700 dark:text-gray-300">
                    <option value="">Semua Siswa</option>
                    @foreach($siswa as $s)
                        <option value="{{ $s->id_siswa }}">{{ $s->nama_siswa }}</option>
                    @endforeach
                </select>
            </div>

            <div class="w-full overflow-x-auto">
                <table id="tabel-log-poin" class="w-full whitespace-nowrap min-w-[640px]">
                    <thead>
                        <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b dark:border-gray-700 bg-gray-50 dark:text-gray-400 dark:bg-gray-900/50">
                            <th class="px-4 py-3">#</th>
                            <th class="px-4 py-3">Nama Siswa</th>
                            <th class="px-4 py-3">Pelanggaran</th>
                            <th class="px-4 py-3">Poin</th>
                            <th class="px-4 py-3">Tanggal</th>
                            <th class="px-4 py-3">Keterangan</th>
                            <th class="px-4 py-3">Dicatat Oleh</th>
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
            var table = $('#tabel-log-poin').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('admin.log-poin.index') }}',
                    data: function(d) {
                        d.siswa_id = $('#filter-siswa').val();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'nama_siswa' },
                    { data: 'pelanggaran' },
                    { data: 'poin', orderable: false, searchable: false },
                    { data: 'tanggal' },
                    { data: 'keterangan' },
                    { data: 'dicatat_oleh', orderable: false, searchable: false },
                    { data: 'aksi', orderable: false, searchable: false },
                ],
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
                }
            });

            $('#filter-siswa').on('change', function() {
                table.draw();
            });
        });
    </script>
    @endpush
</x-layouts.admin>