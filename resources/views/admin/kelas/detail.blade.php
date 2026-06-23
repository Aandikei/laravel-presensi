<x-layouts.admin>
    <x-slot:title>Detail Kelas</x-slot:title>

    <div class="container px-6 mx-auto">
        <div class="my-6 flex items-center justify-between">
            <div>
                <x-breadcrumb :items="[
                    ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
                    ['label' => 'Kelas', 'url' => route('admin.kelas.index')],
                    ['label' => 'Detail Kelas'],
                ]" />
                <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
                    Detail Kelas — {{ $kelas->nama_kelas }}
                </h2>
            </div>
            <form method="GET" class="flex items-center gap-3">
                <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Tahun Ajaran:</label>
                <select name="tahun_id" onchange="this.form.submit()"
                    class="text-sm dark:bg-gray-700 dark:text-gray-300">
                    @foreach($daftarTahun as $tahun)
                        <option value="{{ $tahun->id_tahun }}" {{ $tahun->id_tahun == $tahunDipilih?->id_tahun ? 'selected' : '' }}>
                            {{ $tahun->nama_tahun }} - {{ $tahun->semester }}
                        </option>
                    @endforeach
                </select>
            </form>
        </div>

        {{-- Info Kelas --}}
        <div class="grid gap-4 md:grid-cols-3 mb-6">
            <div class="p-5 bg-white dark:bg-gray-800 rounded-lg shadow-xs dark:shadow-none dark:border dark:border-gray-700">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Info Kelas</p>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Nama Kelas</span>
                        <span class="font-medium text-gray-700 dark:text-gray-200">{{ $kelas->nama_kelas }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Tingkat</span>
                        <span class="font-medium text-gray-700 dark:text-gray-200">{{ $kelas->tingkat }}</span>
                    </div>

                    <div class="flex justify-between">
                        <span class="text-gray-500">Wali Kelas</span>
                        <span class="font-medium text-gray-700 dark:text-gray-200">
                            @if($wk = $kelas->waliKelas)
                                {{ $wk->nama_guru }}
                                @if($wk->transfer_token && !$wk->isTransferTokenExpired())
                                    <span class="px-2 py-1 text-xs font-medium text-orange-700 bg-orange-100 rounded-full">Mutasi</span>
                                @elseif($wk->instansi_id !== auth()->user()->instansi_id)
                                    <span class="px-2 py-1 text-xs font-medium text-blue-700 bg-blue-100 rounded-full">Pindah</span>
                                @elseif($wk->status === 'Keluar')
                                    <span class="px-2 py-1 text-xs font-medium text-red-700 bg-red-100 rounded-full">Keluar</span>
                                @elseif($wk->status === 'Pensiun')
                                    <span class="px-2 py-1 text-xs font-medium text-gray-700 bg-gray-200 rounded-full">Pensiun</span>
                                @endif
                            @else
                                -
                            @endif
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Jumlah Siswa</span>
                        <span class="font-medium text-gray-700 dark:text-gray-200">
                            {{ $registrasi->count() }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Stats Absensi Hari Ini --}}
            <div class="p-5 bg-white dark:bg-gray-800 rounded-lg shadow-xs dark:shadow-none dark:border dark:border-gray-700">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Kehadiran Hari Ini</p>
                @php
                    $hariIni = now()->toDateString();
                    $absensiHariIni = \App\Models\Absensi::whereHas('registrasi', fn($q) =>
                        $q->where('kelas_id', $kelas->id_kelas)
                    )->where('tanggal', $hariIni)->get();
                    $totalHadir = $absensiHariIni->where('status', 'Hadir')->count();
                    $total = $absensiHariIni->count();
                @endphp
                @if($total > 0)
                    <p class="text-3xl font-bold text-center {{ ($totalHadir/$total*100) >= 75 ? 'text-green-600' : 'text-red-600' }}">
                        {{ round($totalHadir/$total*100, 1) }}%
                    </p>
                    <p class="text-xs text-center text-gray-400 mt-1">{{ $totalHadir }}/{{ $total }} hadir</p>
                @else
                    <p class="text-sm text-center text-gray-400 mt-4">Belum ada absensi hari ini</p>
                @endif
            </div>

            {{-- Jumlah Mapel --}}
            <div class="p-5 bg-white dark:bg-gray-800 rounded-lg shadow-xs dark:shadow-none dark:border dark:border-gray-700">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Mata Pelajaran</p>
                <p class="text-3xl font-bold text-center text-purple-600">{{ $kelas->kurikulum->count() }}</p>
                <p class="text-xs text-center text-gray-400 mt-1">Total mata pelajaran</p>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">

            {{-- Jadwal Pelajaran --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xs dark:shadow-none dark:border dark:border-gray-700 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200">Jadwal Pelajaran</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50">
                                <th class="px-4 py-3">Hari</th>
                                <th class="px-4 py-3">Jam</th>
                                <th class="px-4 py-3">Mata Pelajaran</th>
                                <th class="px-4 py-3">Guru</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y dark:divide-gray-700">
                            @php
                                $hariOrder = ['Senin'=>1,'Selasa'=>2,'Rabu'=>3,'Kamis'=>4,'Jumat'=>5,'Sabtu'=>6,'Minggu'=>7];
                                $jadwalAll = $kelas->kurikulum->flatMap->jadwal->sortBy([
                                    fn($a, $b) => ($hariOrder[$a->hari] ?? 8) <=> ($hariOrder[$b->hari] ?? 8),
                                    fn($a, $b) => $a->jam_mulai <=> $b->jam_mulai,
                                ]);
                            @endphp
                            @forelse($jadwalAll as $jadwal)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/70">
                                    <td class="px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-200">
                                        {{ $jadwal->hari }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                        {{ substr($jadwal->jam_mulai, 0, 5) }} - {{ substr($jadwal->jam_selesai, 0, 5) }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">
                                        {{ $jadwal->kurikulum->mataPelajaran->nama_mapel }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
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
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-6 text-center text-gray-500">
                                        Belum ada jadwal.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Daftar Siswa --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xs dark:shadow-none dark:border dark:border-gray-700 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200">
                        Daftar Siswa ({{ $registrasi->count() }})
                    </h3>
                    @if($tahunDipilih)
                        <span class="text-xs text-gray-500 dark:text-gray-400">
                            {{ $tahunDipilih->nama_tahun }} - {{ $tahunDipilih->semester }}
                        </span>
                    @endif
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50">
                                <th class="px-4 py-3">No</th>
                                <th class="px-4 py-3">Nama Siswa</th>
                                <th class="px-4 py-3">NISN</th>
                                <th class="px-4 py-3">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y dark:divide-gray-700">
                            @forelse($registrasi as $i => $reg)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/70">
                                    <td class="px-4 py-3 text-sm text-gray-500">{{ $i + 1 }}</td>
                                    <td class="px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-200">
                                        {{ $reg->siswa->nama_siswa }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-500">{{ $reg->siswa->nisn }}</td>
                                    <td class="px-4 py-3">
                                        @php $siswa = $reg->siswa; @endphp
                                        @if(!$siswa->isAktif())
                                            <span class="px-2 py-1 text-xs font-semibold text-red-700 bg-red-100 rounded-full dark:bg-red-900/30 dark:text-red-400">{{ $siswa->status_label }}</span>
                                        @else
                                            <span class="px-2 py-1 text-xs font-semibold text-green-700 bg-green-100 rounded-full dark:bg-green-900/30 dark:text-green-400">Aktif</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-6 text-center text-gray-500">
                                        Belum ada siswa terdaftar di kelas.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-layouts.admin>