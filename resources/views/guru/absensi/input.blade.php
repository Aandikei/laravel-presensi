<x-layouts.admin>
    <x-slot:title>Input Absensi</x-slot:title>

    <div class="container px-6 mx-auto">
        <div class="my-6">
            <x-breadcrumb :items="[
                ['label' => 'Dashboard', 'url' => route('guru.dashboard')],
                ['label' => 'Absensi', 'url' => route('guru.absensi.index')],
                ['label' => 'Input Absensi'],
            ]" />
            <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">Input Absensi</h2>
        </div>

        {{-- Info Jadwal --}}
        <div class="mb-4 p-4 bg-white dark:bg-gray-800 rounded-lg shadow-xs dark:shadow-none dark:border dark:border-gray-700">
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

        {{-- Hari Libur --}}
        @if(isset($namaLibur) && $namaLibur)
            <div class="mb-4 p-8 text-center bg-white dark:bg-gray-800 rounded-lg shadow-xs dark:shadow-none dark:border dark:border-gray-700">
                <p class="text-lg font-semibold text-gray-500 dark:text-gray-400">
                    Hari ini libur: <strong>{{ $namaLibur }}</strong>
                </p>
                <p class="text-sm text-gray-400 dark:text-gray-500 mt-1">Tidak dapat melakukan absensi pada hari libur.</p>
            </div>
        @elseif(isset($locked) && $locked)
        <div class="mb-4 px-4 py-3 text-sm text-yellow-700 bg-yellow-100 rounded-lg dark:bg-yellow-800 dark:text-yellow-200">
            Absensi sudah dikunci oleh admin. Data hanya bisa dilihat, tidak bisa diedit.
        </div>
        {{-- Form Absensi (Read-only) --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xs dark:shadow-none dark:border dark:border-gray-700 overflow-hidden">
            <table class="w-full">
                <thead>
                    <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b dark:border-gray-700 bg-gray-50 dark:text-gray-400 dark:bg-gray-900/50">
                        <th class="px-5 py-3">No</th>
                        <th class="px-5 py-3">Nama Siswa</th>
                        <th class="px-5 py-3">NISN</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3">Durasi (mnt)</th>
                        <th class="px-5 py-3">Keterangan</th>
                    </tr>
                </thead>
                <tbody class="divide-y dark:divide-gray-700">
                    @foreach($registrasi as $i => $reg)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/70">
                            <td class="px-5 py-3 text-sm text-gray-600 dark:text-gray-400">{{ $i + 1 }}</td>
                            <td class="px-5 py-3 font-medium text-gray-700 dark:text-gray-200">
                                {{ $reg->siswa->nama_siswa }}
                                @if($reg->siswa && !$reg->siswa->isAktif())
                                    <span class="px-2 py-0.5 text-xs font-semibold text-red-700 bg-red-100 rounded-full dark:bg-red-900/30 dark:text-red-400">{{ $reg->siswa->status_label }}</span>
                                @elseif($reg->status === 'Pindah')
                                    <span class="px-2 py-0.5 text-xs font-semibold text-yellow-700 bg-yellow-100 rounded-full dark:bg-yellow-900/30 dark:text-yellow-400">Pindah</span>
                                @elseif($reg->status === 'Alumni')
                                    <span class="px-2 py-0.5 text-xs font-semibold text-green-700 bg-green-100 rounded-full dark:bg-green-900/30 dark:text-green-400">Alumni</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-sm text-gray-500 dark:text-gray-400">{{ $reg->siswa->nisn }}</td>
                            <td class="px-5 py-3">
                                <span class="px-3 py-1 text-xs font-medium rounded-full
                                    {{ ($absensiHariIni[$reg->id_registrasi] ?? '-') == 'Hadir' ? 'bg-green-100 text-green-700' : '' }}
                                    {{ ($absensiHariIni[$reg->id_registrasi] ?? '-') == 'Sakit' ? 'bg-blue-100 text-blue-700' : '' }}
                                    {{ ($absensiHariIni[$reg->id_registrasi] ?? '-') == 'Izin' ? 'bg-amber-100 text-amber-700' : '' }}
                                    {{ ($absensiHariIni[$reg->id_registrasi] ?? '-') == 'Alpa' ? 'bg-red-100 text-red-700' : '' }}
                                    {{ ($absensiHariIni[$reg->id_registrasi] ?? '-') == 'Terlambat' ? 'bg-orange-100 text-orange-700' : '' }}
                                    {{ ($absensiHariIni[$reg->id_registrasi] ?? '-') == 'Bolos' ? 'bg-pink-100 text-pink-700' : '' }}
                                    {{ !in_array(($absensiHariIni[$reg->id_registrasi] ?? '-'), ['Hadir','Sakit','Izin','Alpa','Terlambat','Bolos']) ? 'bg-gray-100 text-gray-500' : '' }}">
                                    {{ $absensiHariIni[$reg->id_registrasi] ?? 'Belum diisi' }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-sm text-gray-600 dark:text-gray-400"></td>
                            <td class="px-5 py-3 text-sm text-gray-600 dark:text-gray-400"></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="px-5 py-4 border-t border-gray-200 dark:border-gray-700 flex justify-end">
                <a href="{{ route('guru.absensi.index') }}"
                    class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200">
                    Kembali
                </a>
            </div>
        </div>
        @else
        {{-- Form Absensi --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xs dark:shadow-none dark:border dark:border-gray-700 overflow-hidden">
            <form method="POST" action="{{ route('guru.absensi.store', $jadwal->id_jadwal) }}">
                @csrf

                {{-- Bulk action --}}
                <div class="px-5 py-3 border-b border-gray-200 dark:border-gray-700 flex items-center gap-3">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Set semua:</span>
                    @foreach(['Hadir','Sakit','Izin','Alpa','Terlambat','Bolos'] as $status)
                        <button type="button" onclick="setAll('{{ $status }}')"
                            class="px-3 py-1 text-xs font-medium rounded-full border transition-colors
                            {{ $status == 'Hadir' ? 'border-green-300 text-green-700 hover:bg-green-50' :
                                ($status == 'Sakit' ? 'border-blue-300 text-blue-700 hover:bg-blue-50' :
                                ($status == 'Izin' ? 'border-amber-300 text-amber-700 hover:bg-amber-50' :
                                ($status == 'Alpa' ? 'border-red-300 text-red-700 hover:bg-red-50' :
                                ($status == 'Terlambat' ? 'border-orange-300 text-orange-700 hover:bg-orange-50' :
                                ($status == 'Bolos' ? 'border-pink-300 text-pink-700 hover:bg-pink-50' :
                                'border-gray-300 text-gray-600 hover:bg-gray-50'))))) }}">
                            {{ $status }}
                        </button>
                    @endforeach
                </div>

                <table class="w-full">
                    <thead>
                        <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b dark:border-gray-700 bg-gray-50 dark:text-gray-400 dark:bg-gray-900/50">
                            <th class="px-5 py-3">No</th>
                            <th class="px-5 py-3">Nama Siswa</th>
                            <th class="px-5 py-3">NISN</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3">Durasi (mnt)</th>
                            <th class="px-5 py-3">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y dark:divide-gray-700">
                        @php $isLocked = isset($locked) && $locked; @endphp
                        @foreach($registrasi as $i => $reg)
                            @php
                                $isNonAktif = !$isLocked && $reg->status !== 'Aktif';
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/70">
                                <td class="px-5 py-3 text-sm text-gray-600 dark:text-gray-400">{{ $i + 1 }}</td>
                                <td class="px-5 py-3 font-medium text-gray-700 dark:text-gray-200">
                                    {{ $reg->siswa->nama_siswa }}
                                    @if($reg->siswa && !$reg->siswa->isAktif())
                                        <span class="px-2 py-0.5 text-xs font-semibold text-red-700 bg-red-100 rounded-full dark:bg-red-900/30 dark:text-red-400">{{ $reg->siswa->status_label }}</span>
                                    @elseif($reg->status === 'Pindah')
                                        <span class="px-2 py-0.5 text-xs font-semibold text-yellow-700 bg-yellow-100 rounded-full dark:bg-yellow-900/30 dark:text-yellow-400">Pindah</span>
                                    @elseif($reg->status === 'Alumni')
                                        <span class="px-2 py-0.5 text-xs font-semibold text-green-700 bg-green-100 rounded-full dark:bg-green-900/30 dark:text-green-400">Alumni</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-sm text-gray-500 dark:text-gray-400">
                                    {{ $reg->siswa->nisn }}
                                </td>
                                @if($isNonAktif)
                                    <td class="px-5 py-3 text-sm font-medium text-gray-600 dark:text-gray-400">
                                        {{ $absensiHariIni[$reg->id_registrasi] ?? '-' }}
                                    </td>
                                    <td class="px-5 py-3 text-sm text-gray-500 dark:text-gray-400">
                                        {{ $reg->durasi_terlambat ?? '-' }}
                                    </td>
                                    <td class="px-5 py-3 text-sm text-gray-500 dark:text-gray-400">
                                        {{ $reg->keterangan ?? '-' }}
                                    </td>
                                @else
                                    <td class="px-5 py-3">
                                        <select name="absensi[{{ $reg->id_registrasi }}][status]"
                                            class="status-select text-sm dark:bg-gray-700 dark:text-gray-300 py-1"
                                            onchange="toggleDurasi(this);">
                                            @foreach(['Hadir','Sakit','Izin','Alpa','Terlambat','Bolos'] as $status)
                                                <option value="{{ $status }}"
                                                    {{ ($absensiHariIni[$reg->id_registrasi] ?? 'Hadir') == $status ? 'selected' : '' }}>
                                                    {{ $status }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="px-5 py-3">
                                        <input type="number"
                                            name="absensi[{{ $reg->id_registrasi }}][durasi_terlambat]"
                                            placeholder="mnt"
                                            class="durasi-input text-sm form-input dark:bg-gray-700 dark:text-gray-300 py-1 w-20 hidden"
                                            min="0" max="999" />
                                    </td>
                                    <td class="px-5 py-3">
                                        <input type="text"
                                            name="absensi[{{ $reg->id_registrasi }}][keterangan]"
                                            placeholder="Opsional..."
                                            class="text-sm form-input dark:bg-gray-700 dark:text-gray-300 py-1 w-full" />
                                    </td>
                                @endif
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
    @endif
    </div>

    @push('scripts')
    <script>
        function setAll(status) {
            document.querySelectorAll('.status-select').forEach(select => {
                select.value = status;
                toggleDurasi(select);
            });
        }

        function toggleDurasi(select) {
            const row = select.closest('tr');
            const input = row.querySelector('.durasi-input');
            if (select.value === 'Terlambat') {
                input.classList.remove('hidden');
            } else {
                input.classList.add('hidden');
                input.value = '';
            }
        }

        document.querySelectorAll('.status-select').forEach(select => {
            toggleDurasi(select);
        });
    </script>
    @endpush
</x-layouts.admin>