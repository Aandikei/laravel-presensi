<x-layouts.admin>
    <x-slot:title>Detail Absensi</x-slot:title>

    <div class="container px-6 mx-auto">
        <div class="my-6">
            <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">Detail Absensi</h2>
            <a href="{{ route('admin.absensi.index') }}"
                class="text-sm text-purple-600 hover:underline dark:text-purple-400">← Kembali</a>
        </div>

        {{-- Info Jadwal --}}
        <div class="mb-4 p-4 bg-white dark:bg-gray-800 rounded-lg shadow-xs">
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
                        {{ $jadwal->kurikulum->guru->nama_guru }}
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

            {{-- Status & Tombol Kunci --}}
            <div class="mt-4 flex items-center justify-between">
                <span class="px-3 py-1 text-sm font-medium rounded-full
                    {{ $terkunci ? 'text-red-700 bg-red-100 dark:bg-red-800 dark:text-red-200' : 'text-green-700 bg-green-100 dark:bg-green-800 dark:text-green-200' }}">
                    {{ $terkunci ? '🔒 Terkunci' : '🔓 Terbuka' }}
                </span>

                @if($terkunci)
                    <form method="POST" action="{{ route('admin.absensi.unlock', $jadwal->id_jadwal) }}">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="tanggal" value="{{ $tanggal }}">
                        <button type="submit"
                            class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700"
                            onclick="return confirm('Buka kunci absensi ini?')">
                            Buka Kunci
                        </button>
                    </form>
                @else
                    <form method="POST" action="{{ route('admin.absensi.lock', $jadwal->id_jadwal) }}">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="tanggal" value="{{ $tanggal }}">
                        <button type="submit"
                            class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700"
                            onclick="return confirm('Kunci absensi ini?')">
                            Kunci Absensi
                        </button>
                    </form>
                @endif
            </div>
        </div>

        {{-- Rekap Status --}}
        @php
            $rekap = $absensi->groupBy('status')->map->count();
        @endphp
        <div class="grid grid-cols-3 md:grid-cols-6 gap-3 mb-4">
            @foreach(['Hadir' => 'green', 'Sakit' => 'blue', 'Izin' => 'yellow', 'Alpa' => 'red', 'Terlambat' => 'orange', 'Cabut' => 'gray'] as $status => $color)
                <div class="p-3 bg-white dark:bg-gray-800 rounded-lg shadow-xs text-center">
                    <p class="text-2xl font-bold text-{{ $color }}-600">{{ $rekap[$status] ?? 0 }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $status }}</p>
                </div>
            @endforeach
        </div>

        {{-- Tabel Detail --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xs overflow-hidden">
            <table class="w-full">
                <thead>
                    <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b dark:border-gray-700 bg-gray-50 dark:text-gray-400 dark:bg-gray-800">
                        <th class="px-5 py-3">No</th>
                        <th class="px-5 py-3">Nama Siswa</th>
                        <th class="px-5 py-3">NISN</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3">Keterangan</th>
                        <th class="px-5 py-3">Waktu Input</th>
                    </tr>
                </thead>
                <tbody class="divide-y dark:divide-gray-700">
                    @forelse($absensi as $i => $item)
                        @php
                            $colors = [
                                'Hadir'     => 'green',
                                'Sakit'     => 'blue',
                                'Izin'      => 'yellow',
                                'Alpa'      => 'red',
                                'Terlambat' => 'orange',
                                'Cabut'     => 'gray',
                            ];
                            $color = $colors[$item->status] ?? 'gray';
                        @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-5 py-3 text-sm text-gray-600 dark:text-gray-400">{{ $i + 1 }}</td>
                            <td class="px-5 py-3 font-medium text-gray-700 dark:text-gray-200">
                                {{ $item->registrasi->siswa->nama_siswa }}
                            </td>
                            <td class="px-5 py-3 text-sm text-gray-500 dark:text-gray-400">
                                {{ $item->registrasi->siswa->nisn }}
                            </td>
                            <td class="px-5 py-3">
                                <span class="px-2 py-1 text-xs font-medium rounded-full
                                    text-{{ $color }}-700 bg-{{ $color }}-100
                                    dark:bg-{{ $color }}-800 dark:text-{{ $color }}-200">
                                    {{ $item->status }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-sm text-gray-500 dark:text-gray-400">
                                {{ $item->keterangan ?? '-' }}
                            </td>
                            <td class="px-5 py-3 text-sm text-gray-500 dark:text-gray-400">
                                {{ $item->waktu_input ? \Carbon\Carbon::parse($item->waktu_input)->format('H:i') : '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-8 text-center text-gray-500 dark:text-gray-400">
                                Belum ada data absensi.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.admin>