<x-layouts.admin>
    <x-slot:title>Detail Absensi - {{ $kelasSaya->nama_kelas }}</x-slot:title>

    <div class="container px-6 mx-auto">
        <div class="my-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
                        Detail Absensi
                    </h2>
                    <p class="text-gray-500 dark:text-gray-400 mt-1">
                        {{ $jadwal->kurikulum->kelas->nama_kelas ?? '-' }} —
                        {{ $jadwal->kurikulum->mataPelajaran->nama_mapel ?? '-' }} —
                        {{ \Carbon\Carbon::parse(request('tanggal'))->locale('id')->isoFormat('D MMM YYYY') }}
                    </p>
                </div>
                <a href="{{ route('guru.wali-kelas.rekap-absensi', request()->only(['bulan', 'tahun'])) }}"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300">
                    ← Kembali
                </a>
            </div>
        </div>

        <div class="p-4 mb-6 bg-white dark:bg-gray-800 rounded-lg shadow-xs dark:shadow-none dark:border dark:border-gray-700">
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4 text-sm">
                <div>
                    <span class="text-gray-500 dark:text-gray-400">Kelas</span>
                    <p class="font-medium text-gray-700 dark:text-gray-200">{{ $jadwal->kurikulum->kelas->nama_kelas ?? '-' }}</p>
                </div>
                <div>
                    <span class="text-gray-500 dark:text-gray-400">Mata Pelajaran</span>
                    <p class="font-medium text-gray-700 dark:text-gray-200">{{ $jadwal->kurikulum->mataPelajaran->nama_mapel ?? '-' }}</p>
                </div>
                <div>
                    <span class="text-gray-500 dark:text-gray-400">Guru</span>
                    <p class="font-medium text-gray-700 dark:text-gray-200">
                        @if($guru = $jadwal->kurikulum?->guru)
                            {{ $guru->nama_guru }}
                            @if($guru->status_label)
                                <span class="text-xs text-red-500">({{ $guru->status_label }})</span>
                            @endif
                        @else
                            -
                        @endif
                    </p>
                </div>
                <div>
                    <span class="text-gray-500 dark:text-gray-400">Jam</span>
                    <p class="font-medium text-gray-700 dark:text-gray-200">{{ substr($jadwal->jam_mulai, 0, 5) }} - {{ substr($jadwal->jam_selesai, 0, 5) }}</p>
                </div>
                <div>
                    <span class="text-gray-500 dark:text-gray-400">Tanggal</span>
                    <p class="font-medium text-gray-700 dark:text-gray-200">{{ \Carbon\Carbon::parse(request('tanggal'))->locale('id')->isoFormat('D MMM YYYY') }}</p>
                </div>
            </div>
        </div>

        <div class="w-full overflow-hidden rounded-lg shadow-xs">
            <div class="w-full overflow-x-auto bg-white dark:bg-gray-800 p-4">
                <table id="tabel-detail" class="w-full whitespace-nowrap">
                    <thead>
                        <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b dark:border-gray-700 bg-gray-50 dark:text-gray-400 dark:bg-gray-900/50">
                            <th class="px-4 py-3">No</th>
                            <th class="px-4 py-3">Nama Siswa</th>
                            <th class="px-4 py-3">NISN</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Keterangan</th>
                            <th class="px-4 py-3">Waktu Input</th>
                            <th class="px-4 py-3">Durasi Terlambat</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y dark:divide-gray-700 dark:bg-gray-800">
                        @forelse($absensi as $i => $a)
                            <tr class="text-gray-700 dark:text-gray-400">
                                <td class="px-4 py-3 text-sm">{{ $i + 1 }}</td>
                                <td class="px-4 py-3 text-sm font-medium">{{ $a->registrasi->siswa->nama_siswa ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm">{{ $a->registrasi->siswa->nisn ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm">
                                    @switch($a->status)
                                        @case('Hadir')
                                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-700">Hadir</span>
                                            @break
                                        @case('Sakit')
                                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-700">Sakit</span>
                                            @break
                                        @case('Izin')
                                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-700">Izin</span>
                                            @break
                                        @case('Alpa')
                                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-700">Alpa</span>
                                            @break
                                        @case('Terlambat')
                                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-orange-100 text-orange-700">Terlambat</span>
                                            @break
                                        @case('Bolos')
                                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-pink-100 text-pink-700">Bolos</span>
                                            @break
                                        @default
                                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-700">{{ $a->status }}</span>
                                    @endswitch
                                </td>
                                <td class="px-4 py-3 text-sm">{{ $a->keterangan ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm">{{ $a->waktu_input ? \Carbon\Carbon::parse($a->waktu_input)->format('H:i') : '-' }}</td>
                                <td class="px-4 py-3 text-sm">{{ $a->durasi_terlambat ? $a->durasi_terlambat . ' mnt' : '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-sm text-center text-gray-500 dark:text-gray-400">
                                    Tidak ada data absensi.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        $(document).ready(function() {
            $('#tabel-detail').DataTable({
                paging: false,
                info: false,
                ordering: true,
                searching: true,
                language: { url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json' }
            });
        });
    </script>
    @endpush
</x-layouts.admin>