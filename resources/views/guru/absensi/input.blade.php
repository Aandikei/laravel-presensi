<x-layouts.admin>
    <x-slot:title>Input Absensi</x-slot:title>

    <div class="container px-6 mx-auto">
        <div class="my-6">
            <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">Input Absensi</h2>
            <a href="{{ route('guru.absensi.index') }}"
                class="text-sm text-purple-600 hover:underline dark:text-purple-400">← Kembali</a>
        </div>

        {{-- Info Jadwal --}}
        <div class="mb-4 p-4 bg-white dark:bg-gray-800 rounded-lg shadow-xs">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                <div>
                    <p class="text-gray-500 dark:text-gray-400">Mata Pelajaran</p>
                    <p class="font-semibold text-gray-700 dark:text-gray-200">{{ $jadwal->kurikulum->mataPelajaran->nama_mapel }}</p>
                </div>
                <div>
                    <p class="text-gray-500 dark:text-gray-400">Kelas</p>
                    <p class="font-semibold text-gray-700 dark:text-gray-200">{{ $jadwal->kurikulum->kelas->nama_kelas }}</p>
                </div>
                <div>
                    <p class="text-gray-500 dark:text-gray-400">Hari & Jam</p>
                    <p class="font-semibold text-gray-700 dark:text-gray-200">
                        {{ $jadwal->hari }}, {{ substr($jadwal->jam_mulai, 0, 5) }} - {{ substr($jadwal->jam_selesai, 0, 5) }}
                    </p>
                </div>
                <div>
                    <p class="text-gray-500 dark:text-gray-400">Tanggal</p>
                    <p class="font-semibold text-gray-700 dark:text-gray-200">{{ now()->format('d F Y') }}</p>
                </div>
            </div>
        </div>

        {{-- Form Absensi --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xs overflow-hidden">
            <form method="POST" action="{{ route('guru.absensi.store', $jadwal->id_jadwal) }}">
                @csrf

                {{-- Bulk action --}}
                <div class="px-5 py-3 border-b border-gray-200 dark:border-gray-700 flex items-center gap-3">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Set semua:</span>
                    @foreach(['Hadir','Sakit','Izin','Alpa','Terlambat','Cabut'] as $status)
                        <button type="button" onclick="setAll('{{ $status }}')"
                            class="px-3 py-1 text-xs font-medium rounded-full border transition-colors
                            {{ $status == 'Hadir' ? 'border-green-300 text-green-700 hover:bg-green-50' :
                               ($status == 'Alpa' ? 'border-red-300 text-red-700 hover:bg-red-50' :
                               'border-gray-300 text-gray-600 hover:bg-gray-50') }}">
                            {{ $status }}
                        </button>
                    @endforeach
                </div>

                <table class="w-full">
                    <thead>
                        <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b dark:border-gray-700 bg-gray-50 dark:text-gray-400 dark:bg-gray-800">
                            <th class="px-5 py-3">No</th>
                            <th class="px-5 py-3">Nama Siswa</th>
                            <th class="px-5 py-3">NISN</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y dark:divide-gray-700">
                        @foreach($registrasi as $i => $reg)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-5 py-3 text-sm text-gray-600 dark:text-gray-400">{{ $i + 1 }}</td>
                                <td class="px-5 py-3 font-medium text-gray-700 dark:text-gray-200">
                                    {{ $reg->siswa->nama_siswa }}
                                </td>
                                <td class="px-5 py-3 text-sm text-gray-500 dark:text-gray-400">
                                    {{ $reg->siswa->nisn }}
                                </td>
                                <td class="px-5 py-3">
                                    <select name="absensi[{{ $reg->id_registrasi }}][status]"
                                        class="status-select text-sm form-select dark:bg-gray-700 dark:text-gray-300 py-1"
                                        onchange="updateRowColor(this)">
                                        @foreach(['Hadir','Sakit','Izin','Alpa','Terlambat','Cabut'] as $status)
                                            <option value="{{ $status }}"
                                                {{ ($absensiHariIni[$reg->id_registrasi] ?? 'Hadir') == $status ? 'selected' : '' }}>
                                                {{ $status }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="px-5 py-3">
                                    <input type="text"
                                        name="absensi[{{ $reg->id_registrasi }}][keterangan]"
                                        placeholder="Opsional..."
                                        class="text-sm form-input dark:bg-gray-700 dark:text-gray-300 py-1 w-full" />
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="px-5 py-4 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-3">
                    <a href="{{ route('guru.absensi.index') }}"
                        class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200">
                        Batal
                    </a>
                    <button type="submit"
                        class="px-6 py-2 text-sm font-medium text-white bg-purple-600 rounded-lg hover:bg-purple-700">
                        Simpan Absensi
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        function setAll(status) {
            document.querySelectorAll('.status-select').forEach(select => {
                select.value = status;
                updateRowColor(select);
            });
        }

        function updateRowColor(select) {
            const row = select.closest('tr');
            row.classList.remove('bg-green-50', 'bg-red-50', 'bg-yellow-50', 'bg-blue-50', 'bg-gray-50');
            const colors = {
                'Hadir'    : 'bg-green-50',
                'Alpa'     : 'bg-red-50',
                'Terlambat': 'bg-yellow-50',
                'Cabut'    : 'bg-red-50',
                'Sakit'    : 'bg-blue-50',
                'Izin'     : 'bg-yellow-50',
            };
            if (colors[select.value]) {
                row.classList.add(colors[select.value]);
            }
        }

        // Init warna saat load
        document.querySelectorAll('.status-select').forEach(select => updateRowColor(select));
    </script>
    @endpush
</x-layouts.admin>