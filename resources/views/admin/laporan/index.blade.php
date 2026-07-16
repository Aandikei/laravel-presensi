<x-layouts.admin>
    <x-slot:title>Laporan</x-slot:title>

    <div class="container px-6 mx-auto">
        <div class="my-6">
            <x-breadcrumb :items="[
                ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
                ['label' => 'Laporan'],
            ]" />
            <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">Laporan</h2>
        </div>

        <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">

            {{-- Rekap Absensi --}}
            <div class="p-6 bg-white rounded-lg shadow-xs dark:shadow-none dark:border dark:border-gray-700 dark:bg-gray-800">
                <h3 class="mb-1 text-lg font-semibold text-gray-700 dark:text-gray-200">
                    Rekap Absensi
                </h3>
                <p class="mb-4 text-sm text-gray-500 dark:text-gray-400">
                    Rekap kehadiran siswa per kelas per bulan
                </p>
                <form method="GET" action="{{ route('admin.laporan.rekap-absensi') }}">
                    @if ($errors->any())
                        <div class="px-4 py-3 mb-4 text-sm text-red-700 bg-red-100 rounded-lg dark:bg-red-800 dark:text-red-200">
                            {{ $errors->first() }}
                        </div>
                    @endif
                    <label class="block text-sm mb-3">
                        <span class="text-gray-700 dark:text-gray-400">Kelas</span>
                        <select name="kelas_id" required
                            class="block w-full mt-1 text-sm dark:bg-gray-700 dark:text-gray-300">
                            <option value="">-- Pilih Kelas --</option>
                            @foreach($kelas as $k)
                                <option value="{{ $k->id_kelas }}">
                                    {{ $k->nama_kelas }} (Tingkat {{ $k->tingkat }})
                                </option>
                            @endforeach
                        </select>
                    </label>
                    <label class="block text-sm mb-3">
                        <span class="text-gray-700 dark:text-gray-400">Mata Pelajaran <span class="text-gray-400">(opsional)</span></span>
                        <select name="mapel_id"
                            class="block w-full mt-1 text-sm dark:bg-gray-700 dark:text-gray-300">
                            <option value="">Semua Mapel</option>
                            @foreach($mapel as $m)
                                <option value="{{ $m->id_mapel }}">{{ $m->nama_mapel }}</option>
                            @endforeach
                        </select>
                    </label>
                    <div class="grid grid-cols-2 gap-3 mb-4">
                        <label class="block text-sm">
                            <span class="text-gray-700 dark:text-gray-400">Bulan</span>
                            <select name="bulan"
                                class="block w-full mt-1 text-sm dark:bg-gray-700 dark:text-gray-300">
                                @for($i = 1; $i <= 12; $i++)
                                    <option value="{{ $i }}" {{ now()->month == $i ? 'selected' : '' }}>
                                        {{ \Carbon\Carbon::create()->month($i)->locale('id')->monthName }}
                                    </option>
                                @endfor
                            </select>
                        </label>
                        <label class="block text-sm">
                            <span class="text-gray-700 dark:text-gray-400">Tahun</span>
                            <select name="tahun"
                                class="block w-full mt-1 text-sm dark:bg-gray-700 dark:text-gray-300">
                                @for($y = now()->year; $y >= 2020; $y--)
                                    <option value="{{ $y }}" {{ now()->year == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </label>
                    </div>
                    <button type="submit"
                        class="w-full px-4 py-2 text-sm font-medium text-white bg-purple-600 rounded-lg hover:bg-purple-700">
                        Lihat Rekap
                    </button>
                </form>
            </div>

            {{-- Rekap Poin --}}
            <div class="p-6 bg-white rounded-lg shadow-xs dark:shadow-none dark:border dark:border-gray-700 dark:bg-gray-800">
                <h3 class="mb-1 text-lg font-semibold text-gray-700 dark:text-gray-200">
                    Rekap Poin Pelanggaran
                </h3>
                <p class="mb-4 text-sm text-gray-500 dark:text-gray-400">
                    Rekap poin pelanggaran siswa per bulan
                </p>
                <form method="GET" action="{{ route('admin.laporan.rekap-poin') }}">
                    <label class="block text-sm mb-3">
                        <span class="text-gray-700 dark:text-gray-400">Kelas <span class="text-gray-400">(opsional)</span></span>
                        <select name="kelas_id"
                            class="block w-full mt-1 text-sm dark:bg-gray-700 dark:text-gray-300">
                            <option value="">Semua Kelas</option>
                            @foreach($kelas as $k)
                                <option value="{{ $k->id_kelas }}">{{ $k->nama_kelas }}</option>
                            @endforeach
                        </select>
                    </label>
                    <div class="grid grid-cols-2 gap-3 mb-4">
                        <label class="block text-sm">
                            <span class="text-gray-700 dark:text-gray-400">Bulan</span>
                            <select name="bulan"
                                class="block w-full mt-1 text-sm dark:bg-gray-700 dark:text-gray-300">
                                @for($i = 1; $i <= 12; $i++)
                                    <option value="{{ $i }}" {{ now()->month == $i ? 'selected' : '' }}>
                                        {{ \Carbon\Carbon::create()->month($i)->locale('id')->monthName }}
                                    </option>
                                @endfor
                            </select>
                        </label>
                        <label class="block text-sm">
                            <span class="text-gray-700 dark:text-gray-400">Tahun</span>
                            <select name="tahun"
                                class="block w-full mt-1 text-sm dark:bg-gray-700 dark:text-gray-300">
                                @for($y = now()->year; $y >= 2020; $y--)
                                    <option value="{{ $y }}" {{ now()->year == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </label>
                    </div>
                    <button type="submit"
                        class="w-full px-4 py-2 text-sm font-medium text-white bg-purple-600 rounded-lg hover:bg-purple-700">
                        Lihat Rekap
                    </button>
                </form>
            </div>

            {{-- Export Data Siswa --}}
            <div class="p-6 bg-white rounded-lg shadow-xs dark:shadow-none dark:border dark:border-gray-700 dark:bg-gray-800">
                <h3 class="mb-1 text-lg font-semibold text-gray-700 dark:text-gray-200">
                    Data Siswa
                </h3>
                <p class="mb-4 text-sm text-gray-500 dark:text-gray-400">
                    Export daftar siswa per kelas / tahun ajaran
                </p>
                <form method="POST" action="{{ route('admin.laporan.export-siswa-excel') }}">
                    @csrf
                    <label class="block text-sm mb-3">
                        <span class="text-gray-700 dark:text-gray-400">Kelas <span class="text-gray-400">(opsional)</span></span>
                        <select name="kelas_id" class="block w-full mt-1 text-sm dark:bg-gray-700 dark:text-gray-300">
                            <option value="">Semua Kelas</option>
                            @foreach($kelas as $k)
                                <option value="{{ $k->id_kelas }}">{{ $k->nama_kelas }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="block text-sm mb-3">
                        <span class="text-gray-700 dark:text-gray-400">Tahun Ajaran <span class="text-gray-400">(opsional)</span></span>
                        <select name="tahun_id" class="block w-full mt-1 text-sm dark:bg-gray-700 dark:text-gray-300">
                            <option value="">Semua Tahun</option>
                            @foreach($daftarTahun as $t)
                                <option value="{{ $t->id_tahun }}">{{ $t->nama_tahun }} - {{ $t->semester }}@if($t->is_aktif) (Aktif)@endif</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="block text-sm mb-4">
                        <span class="text-gray-700 dark:text-gray-400">Status Registrasi <span class="text-gray-400">(opsional)</span></span>
                        <select name="status" class="block w-full mt-1 text-sm dark:bg-gray-700 dark:text-gray-300">
                            <option value="">Semua Status</option>
                            <option value="Aktif">Aktif</option>
                            <option value="Pindah">Pindah</option>
                            <option value="Alumni">Alumni</option>
                        </select>
                    </label>
                    <button type="submit"
                        class="w-full px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700">
                        Export Excel
                    </button>
                </form>
            </div>

            {{-- Export Data Guru --}}
            <div class="p-6 bg-white rounded-lg shadow-xs dark:shadow-none dark:border dark:border-gray-700 dark:bg-gray-800">
                <h3 class="mb-1 text-lg font-semibold text-gray-700 dark:text-gray-200">
                    Data Guru
                </h3>
                <p class="mb-4 text-sm text-gray-500 dark:text-gray-400">
                    Export daftar guru
                </p>
                <form method="POST" action="{{ route('admin.laporan.export-guru-excel') }}">
                    @csrf
                    <label class="block text-sm mb-4">
                        <span class="text-gray-700 dark:text-gray-400">Status <span class="text-gray-400">(opsional)</span></span>
                        <select name="status" class="block w-full mt-1 text-sm dark:bg-gray-700 dark:text-gray-300">
                            <option value="">Semua Status</option>
                            <option value="Aktif">Aktif</option>
                            <option value="Keluar">Keluar</option>
                            <option value="Pensiun">Pensiun</option>
                        </select>
                    </label>
                    <button type="submit"
                        class="w-full px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700">
                        Export Excel
                    </button>
                </form>
            </div>

            {{-- Export Data Kelas --}}
            <div class="p-6 bg-white rounded-lg shadow-xs dark:shadow-none dark:border dark:border-gray-700 dark:bg-gray-800">
                <h3 class="mb-1 text-lg font-semibold text-gray-700 dark:text-gray-200">
                    Data Kelas
                </h3>
                <p class="mb-4 text-sm text-gray-500 dark:text-gray-400">
                    Export daftar kelas per tahun ajaran
                </p>
                <form method="POST" action="{{ route('admin.laporan.export-kelas-excel') }}">
                    @csrf
                    <label class="block text-sm mb-3">
                        <span class="text-gray-700 dark:text-gray-400">Tahun Ajaran <span class="text-gray-400">(opsional)</span></span>
                        <select name="tahun_id" class="block w-full mt-1 text-sm dark:bg-gray-700 dark:text-gray-300">
                            <option value="">Semua Tahun</option>
                            @foreach($daftarTahun as $t)
                                <option value="{{ $t->id_tahun }}">{{ $t->nama_tahun }} - {{ $t->semester }}@if($t->is_aktif) (Aktif)@endif</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="block text-sm mb-3">
                        <span class="text-gray-700 dark:text-gray-400">Tingkat <span class="text-gray-400">(opsional)</span></span>
                        <select name="tingkat" class="block w-full mt-1 text-sm dark:bg-gray-700 dark:text-gray-300">
                            <option value="">Semua Tingkat</option>
                            @foreach($tingkatList as $t)
                                <option value="{{ $t }}">Kelas {{ $t }}</option>
                            @endforeach
                        </select>
                    </label>
                    @if($jurusanList->isNotEmpty())
                    <label class="block text-sm mb-4">
                        <span class="text-gray-700 dark:text-gray-400">Jurusan <span class="text-gray-400">(opsional)</span></span>
                        <select name="jurusan_id" class="block w-full mt-1 text-sm dark:bg-gray-700 dark:text-gray-300">
                            <option value="">Semua Jurusan</option>
                            @foreach($jurusanList as $j)
                                <option value="{{ $j->id_jurusan }}">{{ $j->kode_jurusan }} - {{ $j->nama_jurusan }}</option>
                            @endforeach
                        </select>
                    </label>
                    @endif
                    <button type="submit"
                        class="w-full px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700">
                        Export Excel
                    </button>
                </form>
            </div>

            {{-- Export Log Poin --}}
            <div class="p-6 bg-white rounded-lg shadow-xs dark:shadow-none dark:border dark:border-gray-700 dark:bg-gray-800">
                <h3 class="mb-1 text-lg font-semibold text-gray-700 dark:text-gray-200">
                    Log Poin
                </h3>
                <p class="mb-4 text-sm text-gray-500 dark:text-gray-400">
                    Export riwayat pelanggaran siswa per periode
                </p>
                <form method="POST" action="{{ route('admin.laporan.export-log-poin-excel') }}">
                    @csrf
                    <label class="block text-sm mb-3">
                        <span class="text-gray-700 dark:text-gray-400">Kelas <span class="text-gray-400">(opsional)</span></span>
                        <select name="kelas_id" class="block w-full mt-1 text-sm dark:bg-gray-700 dark:text-gray-300">
                            <option value="">Semua Kelas</option>
                            @foreach($kelas as $k)
                                <option value="{{ $k->id_kelas }}">{{ $k->nama_kelas }}</option>
                            @endforeach
                        </select>
                    </label>
                    <div class="grid grid-cols-2 gap-3 mb-4">
                        <label class="block text-sm">
                            <span class="text-gray-700 dark:text-gray-400">Tanggal Mulai</span>
                            <input type="date" name="tanggal_mulai"
                                class="block w-full mt-1 text-sm dark:bg-gray-700 dark:text-gray-300" />
                        </label>
                        <label class="block text-sm">
                            <span class="text-gray-700 dark:text-gray-400">Tanggal Selesai</span>
                            <input type="date" name="tanggal_selesai"
                                class="block w-full mt-1 text-sm dark:bg-gray-700 dark:text-gray-300" />
                        </label>
                    </div>
                    <button type="submit"
                        class="w-full px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700">
                        Export Excel
                    </button>
                </form>
            </div>

            {{-- Export Jadwal --}}
            <div class="p-6 bg-white rounded-lg shadow-xs dark:shadow-none dark:border dark:border-gray-700 dark:bg-gray-800">
                <h3 class="mb-1 text-lg font-semibold text-gray-700 dark:text-gray-200">
                    Jadwal Kelas
                </h3>
                <p class="mb-4 text-sm text-gray-500 dark:text-gray-400">
                    Export jadwal pelajaran per kelas
                </p>
                <form method="POST" action="{{ route('admin.laporan.export-jadwal-excel') }}">
                    @csrf
                    <label class="block text-sm mb-3">
                        <span class="text-gray-700 dark:text-gray-400">Kelas <span class="text-gray-400">(opsional)</span></span>
                        <select name="kelas_id" class="block w-full mt-1 text-sm dark:bg-gray-700 dark:text-gray-300">
                            <option value="">Semua Kelas</option>
                            @foreach($kelas as $k)
                                <option value="{{ $k->id_kelas }}">{{ $k->nama_kelas }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="block text-sm mb-4">
                        <span class="text-gray-700 dark:text-gray-400">Tahun Ajaran <span class="text-gray-400">(opsional)</span></span>
                        <select name="tahun_id" class="block w-full mt-1 text-sm dark:bg-gray-700 dark:text-gray-300">
                            <option value="">Semua Tahun</option>
                            @foreach($daftarTahun as $t)
                                <option value="{{ $t->id_tahun }}">{{ $t->nama_tahun }} - {{ $t->semester }}@if($t->is_aktif) (Aktif)@endif</option>
                            @endforeach
                        </select>
                    </label>
                    <button type="submit"
                        class="w-full px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700">
                        Export Excel
                    </button>
                </form>
            </div>

        </div>
    </div>
</x-layouts.admin>