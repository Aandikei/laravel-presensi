<x-layouts.admin>
    <x-slot:title>Dashboard Wali Kelas</x-slot:title>

    <div class="container px-6 mx-auto">
        <div class="my-6">
            <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
                Dashboard Wali Kelas
            </h2>
            <p class="text-gray-500 dark:text-gray-400 mt-1">
                Selamat datang, {{ $guru->nama_guru }} •
                {{ $hariIni }}, {{ now()->format('d F Y') }}
            </p>
        </div>

        @if(session('success'))
            <div class="px-4 py-3 mb-4 text-sm text-green-700 bg-green-100 rounded-lg dark:bg-green-800 dark:text-green-200">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="px-4 py-3 mb-4 text-sm text-red-700 bg-red-100 rounded-lg dark:bg-red-800 dark:text-red-200">
                {{ session('error') }}
            </div>
        @endif

        {{-- Section: Kelas Saya --}}
        <div class="mb-6">
            <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-3">Kelas Saya</h3>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xs overflow-hidden">
                <div class="p-5 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-full bg-purple-600 flex items-center justify-center text-white font-bold text-lg">
                            {{ strtoupper(substr($kelasSaya->nama_kelas, 0, 1)) }}
                        </div>
                        <div>
                            <p class="text-xl font-bold text-gray-800 dark:text-gray-100">{{ $kelasSaya->nama_kelas }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Tingkat {{ $kelasSaya->tingkat }} • {{ $kelasSaya->instansi->jenjang }}
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Stats Kelas --}}
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 p-5">
                    <div class="text-center p-4 bg-purple-50 dark:bg-purple-900/20 rounded-lg">
                        <p class="text-3xl font-bold text-purple-600">{{ $jumlahSiswa }}</p>
                        <p class="text-xs text-gray-500 mt-1">Jumlah Siswa</p>
                    </div>
                    <div class="text-center p-4 bg-green-50 dark:bg-green-900/20 rounded-lg">
                        <p class="text-3xl font-bold text-green-600">{{ $rataKehadiran }}%</p>
                        <p class="text-xs text-gray-500 mt-1">Rata-rata Kehadiran</p>
                    </div>
                    <div class="text-center p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg">
                        <p class="text-3xl font-bold text-yellow-600">{{ $siswaPoinTinggi->count() }}</p>
                        <p class="text-xs text-gray-500 mt-1">Siswa Kena Poin (Bulan Ini)</p>
                    </div>
                    <div class="text-center p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                        <p class="text-3xl font-bold text-blue-600">{{ $jadwalHariIni->count() }}</p>
                        <p class="text-xs text-gray-500 mt-1">Jadwal Hari Ini</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-2 mb-6">
            {{-- Siswa dengan Poin Tertinggi --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xs overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200">
                        🚨 Siswa dengan Poin Tertinggi
                    </h3>
                    <p class="text-xs text-gray-500 mt-1">Bulan {{ now()->locale('id')->monthName }}</p>
                </div>
                @if($siswaPoinTinggi->isEmpty())
                    <div class="p-8 text-center text-gray-500">Belum ada pelanggaran bulan ini.</div>
                @else
                    <div class="divide-y dark:divide-gray-700">
                        @foreach($siswaPoinTinggi as $i => $siswa)
                            <div class="flex items-center justify-between px-5 py-3 hover:bg-gray-50 dark:hover:bg-gray-700">
                                <div class="flex items-center gap-3">
                                    <span class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold
                                        {{ $i == 0 ? 'bg-red-500 text-white' : ($i == 1 ? 'bg-orange-500 text-white' : ($i == 2 ? 'bg-yellow-500 text-white' : 'bg-gray-200 text-gray-600')) }}">
                                        {{ $i + 1 }}
                                    </span>
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-200">
                                        {{ $siswa->nama_siswa }}
                                    </span>
                                </div>
                                <span class="px-3 py-1 text-sm font-bold text-red-700 bg-red-100 rounded-full">
                                    {{ $siswa->poin_bulan_ini }} poin
                                </span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Tambah Poin Cepat --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xs overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200">
                        ➕ Tambah Poin ke Siswa
                    </h3>
                </div>
                <div class="p-5">
                    <form method="POST" action="{{ route('guru.wali-kelas.tambah-poin') }}">
                        @csrf
                        <label class="block text-sm mb-4">
                            <span class="text-gray-700 dark:text-gray-400">Siswa</span>
                            <select name="siswa_id" id="siswa-select"
                                class="block w-full mt-1 text-sm form-select dark:bg-gray-700 dark:text-gray-300 @error('siswa_id') border-red-500 @enderror">
                                <option value="">-- Pilih Siswa --</option>
                            </select>
                            @error('siswa_id')
                                <span class="text-xs text-red-500">{{ $message }}</span>
                            @enderror
                        </label>

                        <label class="block text-sm mb-4">
                            <span class="text-gray-700 dark:text-gray-400">Pelanggaran</span>
                            <select name="poin_id"
                                class="block w-full mt-1 text-sm form-select dark:bg-gray-700 dark:text-gray-300 @error('poin_id') border-red-500 @enderror">
                                <option value="">-- Pilih --</option>
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

                        <label class="block text-sm mb-4">
                            <span class="text-gray-700 dark:text-gray-400">Keterangan <span class="text-gray-400">(opsional)</span></span>
                            <input type="text" name="keterangan" value="{{ old('keterangan') }}"
                                class="block w-full mt-1 text-sm form-input dark:bg-gray-700 dark:text-gray-300" />
                        </label>

                        <button type="submit"
                            class="w-full px-4 py-2 text-sm font-medium text-white bg-purple-600 rounded-lg hover:bg-purple-700">
                            Tambah Poin
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Jadwal Hari Ini --}}
        <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-3">
            Jadwal Mengajar Hari Ini
        </h3>

        @if ($jadwalHariIni->isEmpty())
            <div class="p-8 text-center bg-white dark:bg-gray-800 rounded-lg shadow-xs mb-6">
                <p class="text-gray-500 dark:text-gray-400">Tidak ada jadwal mengajar hari ini.</p>
            </div>
        @else
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3 mb-6">
                @foreach ($jadwalHariIni as $jadwal)
                    <div class="p-5 bg-white dark:bg-gray-800 rounded-lg shadow-xs border-l-4
                        {{ $jadwal->sudah_input ? 'border-green-500' : 'border-yellow-500' }}">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="font-semibold text-gray-700 dark:text-gray-200">
                                    {{ $jadwal->kurikulum->mataPelajaran->nama_mapel }}
                                </p>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                    {{ $jadwal->kurikulum->kelas->nama_kelas }}
                                </p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ substr($jadwal->jam_mulai, 0, 5) }} - {{ substr($jadwal->jam_selesai, 0, 5) }}
                                </p>
                            </div>
                            @if ($jadwal->sudah_input)
                                <span class="px-2 py-1 text-xs font-medium text-green-700 bg-green-100 rounded-full dark:bg-green-800 dark:text-green-200">
                                    Sudah Input
                                </span>
                            @else
                                <span class="px-2 py-1 text-xs font-medium text-yellow-700 bg-yellow-100 rounded-full dark:bg-yellow-800 dark:text-yellow-200">
                                    Belum Input
                                </span>
                            @endif
                        </div>
                        @if (!$jadwal->sudah_input)
                            <a href="{{ route('guru.absensi.input', $jadwal->id_jadwal) }}"
                                class="mt-3 block w-full text-center px-4 py-2 text-sm font-medium text-white bg-purple-600 rounded-lg hover:bg-purple-700">
                                Input Absensi
                            </a>
                        @else
                            <a href="{{ route('guru.absensi.input', $jadwal->id_jadwal) }}"
                                class="mt-3 block w-full text-center px-4 py-2 text-sm font-medium text-purple-600 bg-purple-50 rounded-lg hover:bg-purple-100">
                                Lihat / Edit
                            </a>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    @push('scripts')
    <script>
        $(document).ready(function() {
            const kelasId = {{ $kelasSaya->id_kelas }};

            $.getJSON('{{ route('guru.wali-kelas.siswa-by-kelas', $kelasSaya->id_kelas) }}', function(data) {
                const select = $('#siswa-select');
                select.empty().append('<option value="">-- Pilih Siswa --</option>');
                $.each(data, function(i, siswa) {
                    select.append($('<option>', {
                        value: siswa.id_siswa,
                        text: siswa.nama_siswa + ' (' + siswa.nisn + ')'
                    }));
                });
            });
        });
    </script>
    @endpush
</x-layouts.admin>
