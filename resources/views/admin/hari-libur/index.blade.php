<x-layouts.admin>
    <x-slot:title>Hari Libur</x-slot:title>

    <div class="container px-6 mx-auto">
        <div class="my-6">
            <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">Hari Libur</h2>
        </div>

        <div class="grid gap-6 lg:grid-cols-2 mb-6">

            @can('manage-settings')
            {{-- Form Tambah Libur Sekolah (Range Tanggal) --}}
            <div class="p-6 bg-white rounded-lg shadow-xs dark:shadow-none dark:border dark:border-gray-700 dark:bg-gray-800">
                <h3 class="mb-1 text-lg font-semibold text-gray-700 dark:text-gray-200">
                    Tambah Hari Libur Sekolah
                </h3>
                <p class="mb-4 text-xs text-gray-500 dark:text-gray-400">
                    Bisa input range tanggal untuk libur multi-hari (contoh: class meeting 3 hari)
                </p>
                <form method="POST" action="{{ route('admin.hari-libur.store') }}">
                    @csrf

                    <label class="block text-sm mb-4">
                        <span class="text-gray-700 dark:text-gray-400">Nama Libur</span>
                        <input type="text" name="nama_libur" value="{{ old('nama_libur') }}"
                            placeholder="contoh: Class Meeting"
                            class="block w-full mt-1 text-sm form-input dark:bg-gray-700 dark:text-gray-300 @error('nama_libur') border-red-500 @enderror" />
                        @error('nama_libur')
                            <span class="text-xs text-red-500">{{ $message }}</span>
                        @enderror
                    </label>

                    <div class="grid grid-cols-2 gap-4">
                        <label class="block text-sm mb-4">
                            <span class="text-gray-700 dark:text-gray-400">Tanggal Mulai</span>
                            <input type="date" name="tanggal_mulai" value="{{ old('tanggal_mulai') }}"
                                class="block w-full mt-1 text-sm form-input dark:bg-gray-700 dark:text-gray-300 @error('tanggal_mulai') border-red-500 @enderror" />
                            @error('tanggal_mulai')
                                <span class="text-xs text-red-500">{{ $message }}</span>
                            @enderror
                        </label>

                        <label class="block text-sm mb-4">
                            <span class="text-gray-700 dark:text-gray-400">Tanggal Selesai <span class="text-gray-400">(opsional)</span></span>
                            <input type="date" name="tanggal_selesai" value="{{ old('tanggal_selesai') }}"
                                class="block w-full mt-1 text-sm form-input dark:bg-gray-700 dark:text-gray-300 @error('tanggal_selesai') border-red-500 @enderror" />
                            @error('tanggal_selesai')
                                <span class="text-xs text-red-500">{{ $message }}</span>
                            @enderror
                        </label>
                    </div>

                    <button type="submit"
                        class="w-full px-4 py-2 text-sm font-medium text-white bg-purple-600 rounded-lg hover:bg-purple-700">
                        Tambah Libur Sekolah
                    </button>
                </form>
            </div>
            @endcan

            {{-- Libur Nasional Referensi --}}
            <div class="p-6 bg-white rounded-lg shadow-xs dark:shadow-none dark:border dark:border-gray-700 dark:bg-gray-800">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="mb-1 text-lg font-semibold text-gray-700 dark:text-gray-200">
                            Libur Nasional (Referensi)
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Klik "Ikut Libur" untuk menambahkan ke sekolah Anda.
                        </p>
                    </div>
                    @if($liburNasional->isNotEmpty() && Auth::user()->can('manage-settings'))
                        <form method="POST" action="{{ route('admin.hari-libur.adopt-all') }}">
                            @csrf
                            <button type="button" @click="confirmAction($event.currentTarget.closest('form'),
                                'Ikut semua libur nasional?',
                                'Ya, Ikutkan')"
                                class="px-3 py-1 text-xs font-medium text-white bg-green-600 rounded hover:bg-green-700">
                                Ikut Semua
                            </button>
                        </form>
                    @endif
                </div>

                @if($liburNasional->isEmpty())
                    <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">
                        Belum ada data libur nasional.
                    </p>
                @else
                    <div class="space-y-2 max-h-64 overflow-y-auto">
                        @foreach($liburNasional as $libur)
                            @php
                                $sudahAdopt = $liburSekolah->contains('tanggal', $libur->tanggal);
                            @endphp
                            <div class="flex items-center justify-between p-3 rounded-lg border border-gray-200 dark:border-gray-700">
                                <div>
                                    <p class="text-sm font-medium text-gray-700 dark:text-gray-200">
                                        {{ $libur->nama_libur }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ \Carbon\Carbon::parse($libur->tanggal)->format('d F Y') }}
                                    </p>
                                </div>
                                @if($sudahAdopt)
                                    <span class="px-3 py-1 text-xs font-medium text-green-700 bg-green-100 rounded-full dark:bg-green-800 dark:text-green-200">
                                        ✓ Sudah Ikut
                                    </span>
                                @elseif(Auth::user()->can('manage-settings'))
                                    <form method="POST" action="{{ route('admin.hari-libur.adopt') }}">
                                        @csrf
                                        <input type="hidden" name="tanggal" value="{{ $libur->tanggal }}">
                                        <input type="hidden" name="nama_libur" value="{{ $libur->nama_libur }}">
                                        <button type="submit"
                                            class="px-3 py-1 text-xs font-medium text-white bg-blue-600 rounded-full hover:bg-blue-700">
                                            Ikut Libur
                                        </button>
                                    </form>
                                @else
                                    <span class="px-3 py-1 text-xs font-medium text-gray-500 bg-gray-100 rounded-full dark:bg-gray-700 dark:text-gray-400">
                                        Belum Ikut
                                    </span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- Tabel Hari Libur Sekolah --}}
        <div class="bg-white rounded-lg shadow-xs dark:shadow-none dark:border dark:border-gray-700 dark:bg-gray-800 p-4">
            <h3 class="mb-4 text-lg font-semibold text-gray-700 dark:text-gray-200">
                Daftar Hari Libur Sekolah Ini
            </h3>
            <table id="tabel-libur" class="w-full">
                <thead>
                    <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b dark:border-gray-700 bg-gray-50 dark:text-gray-400 dark:bg-gray-900/50">
                        <th class="px-4 py-3">#</th>
                        <th class="px-4 py-3">Tanggal</th>
                        <th class="px-4 py-3">Nama Libur</th>
                        <th class="px-4 py-3">Tipe</th>
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
            $('#tabel-libur').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('admin.hari-libur.index') }}',
                order: [[1, 'asc']],
                columns: [
                    { data: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'tanggal' },
                    { data: 'nama_libur' },
                    { data: 'tipe', orderable: false },
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
