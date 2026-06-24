<x-layouts.admin>
    <x-slot:title>Detail Absensi</x-slot:title>

    <div class="container px-6 mx-auto">
        {{-- Header --}}
        <div class="flex items-center justify-between my-6">
            <div>
                <x-breadcrumb :items="[
                    ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
                    ['label' => 'Monitor Absensi', 'url' => route('admin.absensi.index')],
                    ['label' => 'Detail'],
                ]" />
                <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">Detail Absensi</h2>
            </div>
            <span class="px-3 py-1 text-sm font-medium rounded-full
                {{ $terkunci ? 'text-red-700 bg-red-100 dark:bg-red-800 dark:text-red-200' : 'text-green-700 bg-green-100 dark:bg-green-800 dark:text-green-200' }}">
                {{ $terkunci ? 'Terkunci' : 'Terbuka' }}
            </span>
        </div>

        {{-- Info Jadwal --}}
        <div class="mb-4 p-4 bg-white dark:bg-gray-800 rounded-lg shadow-xs dark:shadow-none dark:border dark:border-gray-700">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                <div>
                    <p class="text-gray-500 dark:text-gray-400">Mata Pelajaran</p>
                    <p class="font-semibold text-gray-700 dark:text-gray-200">
                        {{ $jadwal->kurikulum->mataPelajaran->nama_mapel }}
                    </p>
                </div>
                <div>
                    <p class="text-gray-500 dark:text-gray-400">Kelas</p>
                    <p class="font-semibold text-gray-700 dark:text-gray-200">
                        {{ $jadwal->kurikulum->kelas->nama_kelas }}
                    </p>
                </div>
                <div>
                    <p class="text-gray-500 dark:text-gray-400">Guru</p>
                    <p class="font-semibold text-gray-700 dark:text-gray-200">
                        @if($guru = $jadwal->kurikulum?->guru)
                            {{ $guru->nama_guru }}
                            @if($guru->transfer_token && !$guru->isTransferTokenExpired())
                                <span class="px-2 py-1 text-xs font-medium text-orange-700 bg-orange-100 rounded-full">Mutasi</span>
                            @elseif($guru->instansi_id !== auth()->user()->instansi_id)
                                <span class="px-2 py-1 text-xs font-medium text-blue-700 bg-blue-100 rounded-full">Pindah</span>
                            @elseif($guru->status === 'Keluar')
                                <span class="px-2 py-1 text-xs font-medium text-red-700 bg-red-100 rounded-full">Keluar</span>
                            @elseif($guru->status === 'Pensiun')
                                <span class="px-2 py-1 text-xs font-medium text-gray-700 bg-gray-200 rounded-full">Pensiun</span>
                            @endif
                        @else
                            -
                        @endif
                    </p>
                </div>
                <div>
                    <p class="text-gray-500 dark:text-gray-400">Tanggal & Jam</p>
                    <p class="font-semibold text-gray-700 dark:text-gray-200">
                        {{ \Carbon\Carbon::parse($tanggal)->format('d F Y') }} •
                        {{ substr($jadwal->jam_mulai, 0, 5) }} - {{ substr($jadwal->jam_selesai, 0, 5) }}
                    </p>
                </div>
            </div>

            {{-- Tombol Kunci --}}
            @can('manage-settings')
            <div class="mt-4 flex justify-end">
                @if($terkunci)
                    <form method="POST" action="{{ route('admin.absensi.unlock', $jadwal->id_jadwal) }}">
                        @csrf @method('PATCH')
                        <input type="hidden" name="tanggal" value="{{ $tanggal }}">
                        <button type="submit"
                            class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700"
                            onclick="return confirm('Buka kunci absensi ini?')">
                            Buka Kunci
                        </button>
                    </form>
                @else
                    <form method="POST" action="{{ route('admin.absensi.lock', $jadwal->id_jadwal) }}">
                        @csrf @method('PATCH')
                        <input type="hidden" name="tanggal" value="{{ $tanggal }}">
                        <button type="submit"
                            class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700"
                            onclick="return confirm('Kunci absensi ini?')">
                            Kunci Absensi
                        </button>
                    </form>
                @endif
            </div>
            @endcan
        </div>

        {{-- Rekap Status --}}
        @php
            $rekap = $absensi->groupBy('status')->map->count();
        @endphp
        <div class="grid grid-cols-3 md:grid-cols-6 gap-3 mb-4">
            @foreach(['Hadir' => 'green', 'Sakit' => 'blue', 'Izin' => 'yellow', 'Alpa' => 'red', 'Terlambat' => 'orange', 'Bolos' => 'pink'] as $status => $color)
                <div class="p-4 bg-white dark:bg-gray-800 rounded-lg shadow-xs dark:shadow-none dark:border dark:border-gray-700 text-center">
                    <p class="text-2xl font-bold text-{{ $color }}-600 dark:text-{{ $color }}-400">{{ $rekap[$status] ?? 0 }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $status }}</p>
                </div>
            @endforeach
        </div>

        {{-- Tabel Detail --}}
        <div class="w-full overflow-x-auto rounded-lg shadow-xs">
            <table class="w-full whitespace-nowrap">
                <thead>
                    <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b bg-gray-50 dark:border-gray-700 dark:text-gray-400 dark:bg-gray-900/50">
                        <th class="px-5 py-3">No</th>
                        <th class="px-5 py-3">Nama Siswa</th>
                        <th class="px-5 py-3">NISN</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3">Keterangan</th>
                        <th class="px-5 py-3">Durasi</th>
                        <th class="px-5 py-3">Waktu Input</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y dark:divide-gray-700 dark:bg-gray-800">
                    @forelse($absensi as $i => $item)
                        @php
                            $colors = [
                                'Hadir'     => 'green',
                                'Sakit'     => 'blue',
                                'Izin'      => 'yellow',
                                'Alpa'      => 'red',
                                'Terlambat' => 'orange',
                                'Bolos'     => 'pink',
                            ];
                            $color = $colors[$item->status] ?? 'gray';
                        @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/70">
                            <td class="px-5 py-3 text-sm text-gray-600 dark:text-gray-400">{{ $i + 1 }}</td>
                            <td class="px-5 py-3 font-medium text-gray-700 dark:text-gray-200">
                                {{ $item->registrasi->siswa->nama_siswa }}
                                @php $siswa = $item->registrasi->siswa; @endphp
                                @if($siswa && !$siswa->isAktif())
                                    <span class="px-2 py-0.5 text-xs font-semibold text-red-700 bg-red-100 rounded-full dark:bg-red-900/30 dark:text-red-400">{{ $siswa->status_label }}</span>
                                @elseif($item->registrasi->status === 'Pindah')
                                    <span class="px-2 py-0.5 text-xs font-semibold text-yellow-700 bg-yellow-100 rounded-full dark:bg-yellow-900/30 dark:text-yellow-400">Pindah</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-sm text-gray-500 dark:text-gray-400">
                                {{ $item->registrasi->siswa->nisn }}
                            </td>
                            <td class="px-5 py-3">
                                <span class="px-2 py-1 text-xs font-medium rounded-full
                                    bg-{{ $color }}-100 text-{{ $color }}-700
                                    dark:bg-{{ $color }}-800 dark:text-{{ $color }}-200">
                                    {{ $item->status }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-sm text-gray-500 dark:text-gray-400">
                                {{ $item->keterangan ?? '-' }}
                            </td>
                            <td class="px-5 py-3 text-sm text-gray-500 dark:text-gray-400">
                                {{ $item->durasi_terlambat ? $item->durasi_terlambat . ' menit' : '-' }}
                            </td>
                            <td class="px-5 py-3 text-sm text-gray-500 dark:text-gray-400">
                                {{ $item->waktu_input ? \Carbon\Carbon::parse($item->waktu_input)->format('H:i') : '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-8 text-center text-gray-500 dark:text-gray-400">
                                Belum ada data absensi.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.admin>