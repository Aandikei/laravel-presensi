<x-layouts.admin>
    <x-slot:title>Tandai Pindah</x-slot:title>

    <div class="container px-6 mx-auto">
        <div class="my-6">
            <x-breadcrumb :items="[
                ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
                ['label' => 'Data Siswa', 'url' => route('admin.siswa.index')],
                ['label' => 'Pindahkan Siswa'],
            ]" />
            <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
                Tandai Pindah Siswa
            </h2>
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
                    @if($siswa->registrasiAktif?->kelas)
                        <div class="flex justify-between">
                            <span class="text-gray-500">Kelas Aktif</span>
                            <span class="font-medium text-gray-700 dark:text-gray-200">{{ $siswa->registrasiAktif->kelas->nama_kelas }}</span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Form Alasan Pindah --}}
            <div class="p-5 bg-white rounded-lg shadow-xs dark:shadow-none dark:border dark:border-gray-700 dark:bg-gray-800">
                <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-4">Alasan Pindah</h3>

                <form method="POST" action="{{ route('admin.siswa.pindah', $siswa->id_siswa) }}">
                    @csrf

                    <label class="block text-sm mb-4">
                        <span class="text-gray-700 dark:text-gray-400">Alasan siswa dipindahkan</span>
                        <textarea name="alasan" rows="3" required maxlength="255"
                            class="block w-full mt-1 text-sm form-textarea dark:bg-gray-700 dark:text-gray-300 @error('alasan') border-red-500 @enderror"
                            placeholder="Contoh: Pindah domisili ke Kota Bandung, Orang tua pindah tugas, dll.">{{ old('alasan') }}</textarea>
                        @error('alasan')
                            <span class="text-xs text-red-500">{{ $message }}</span>
                        @enderror
                    </label>

                    <div class="mb-4 px-3 py-2 text-xs text-yellow-700 bg-yellow-50 rounded-lg dark:bg-yellow-900/30 dark:text-yellow-300">
                        Data absensi siswa akan tetap tersimpan sebagai riwayat di sekolah ini.
                    </div>

                    <div class="flex gap-2">
                        <a href="{{ route('admin.siswa.index') }}"
                            class="w-full px-4 py-2 text-sm font-medium text-center text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                            Batal
                        </a>
                        <button type="submit"
                            class="w-full px-4 py-2 text-sm font-medium text-white bg-yellow-600 rounded-lg hover:bg-yellow-700">
                            Tandai Pindah
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.admin>
