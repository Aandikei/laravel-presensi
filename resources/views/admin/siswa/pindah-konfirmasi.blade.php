<x-layouts.admin>
    <x-slot:title>Konfirmasi Pindah Siswa</x-slot:title>

    <div class="container px-6 mx-auto">
        <div class="my-6">
            <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
                Konfirmasi Penerimaan Siswa Pindahan
            </h2>
            <a href="{{ route('admin.siswa.pindah.form-masuk') }}"
                class="text-sm text-purple-600 hover:underline dark:text-purple-400">
                ← Cari siswa lain
            </a>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            {{-- Info Siswa --}}
            <div class="p-5 bg-white rounded-lg shadow-xs dark:shadow-none dark:border dark:border-gray-700 dark:bg-gray-800">
                <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-4">Data Siswa</h3>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Nama</span>
                        <span class="font-medium text-gray-700 dark:text-gray-200">{{ $siswa->nama_siswa }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">NISN</span>
                        <span class="font-medium text-gray-700 dark:text-gray-200">{{ $siswa->nisn }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Jenis Kelamin</span>
                        <span class="font-medium text-gray-700 dark:text-gray-200">{{ $siswa->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Sekolah Asal</span>
                        <span class="font-medium text-gray-700 dark:text-gray-200">{{ $siswa->instansi->nama_instansi }}</span>
                    </div>
                    @if($siswa->registrasiAktif?->kelas)
                        <div class="flex justify-between">
                            <span class="text-gray-500">Kelas Terakhir</span>
                            <span class="font-medium text-gray-700 dark:text-gray-200">{{ $siswa->registrasiAktif->kelas->nama_kelas }}</span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Form Konfirmasi --}}
            <div class="p-5 bg-white rounded-lg shadow-xs dark:shadow-none dark:border dark:border-gray-700 dark:bg-gray-800">
                <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-4">Daftarkan ke Kelas Baru</h3>

                <form method="POST" action="{{ route('admin.siswa.pindah.proses') }}">
                    @csrf
                    <input type="hidden" name="siswa_id" value="{{ $siswa->id_siswa }}">
                    <input type="hidden" name="token" value="{{ request('token') }}">

                    <label class="block text-sm mb-4">
                        <span class="text-gray-700 dark:text-gray-400">Tahun Ajaran</span>
                        <select name="tahun_id"
                            class="block w-full mt-1 text-sm dark:bg-gray-700 dark:text-gray-300 @error('tahun_id') border-red-500 @enderror">
                            <option value="">-- Tidak daftarkan dulu --</option>
                            @if($tahunAktif)
                                <option value="{{ $tahunAktif->id_tahun }}" selected>
                                    {{ $tahunAktif->nama_tahun }} - {{ $tahunAktif->semester }} (Aktif)
                                </option>
                            @endif
                        </select>
                        @error('tahun_id')
                            <span class="text-xs text-red-500">{{ $message }}</span>
                        @enderror
                    </label>

                    <label class="block text-sm mb-4">
                        <span class="text-gray-700 dark:text-gray-400">Kelas Tujuan</span>
                        <select name="kelas_id"
                            class="block w-full mt-1 text-sm dark:bg-gray-700 dark:text-gray-300 @error('kelas_id') border-red-500 @enderror">
                            <option value="">-- Pilih Kelas --</option>
                            @foreach($kelas as $k)
                                <option value="{{ $k->id_kelas }}" {{ optional($siswa->registrasiAktif?->kelas)->tingkat == $k->tingkat ? 'selected' : '' }}>
                                    {{ $k->nama_kelas }} (Tingkat {{ $k->tingkat }})
                                </option>
                            @endforeach
                        </select>
                        @error('kelas_id')
                            <span class="text-xs text-red-500">{{ $message }}</span>
                        @enderror
                    </label>

                    <div class="mb-4 px-3 py-2 text-xs text-yellow-700 bg-yellow-50 rounded-lg dark:bg-yellow-900/30 dark:text-yellow-300">
                        Data absensi dan poin dari sekolah asal tetap tersimpan di sekolah lama sebagai riwayat.
                    </div>

                    <button type="submit"
                        class="w-full px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700">
                        Konfirmasi Pindah
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-layouts.admin>
