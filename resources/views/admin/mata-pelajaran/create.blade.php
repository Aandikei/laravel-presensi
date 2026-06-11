<x-layouts.admin>
    <x-slot:title>Tambah Mata Pelajaran</x-slot:title>

    <div class="container px-6 mx-auto">
        <div class="my-6">
            <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
                Tambah Mata Pelajaran
            </h2>
            <a href="{{ route('admin.mata-pelajaran.index') }}"
                class="text-sm text-purple-600 hover:underline dark:text-purple-400">
                ← Kembali
            </a>
        </div>

        <div class="max-w-lg p-6 bg-white rounded-lg shadow-xs dark:shadow-none dark:border dark:border-gray-700 dark:bg-gray-800">
            <form method="POST" action="{{ route('admin.mata-pelajaran.store') }}">
                @csrf

                <label class="block text-sm mb-4">
                    <span class="text-gray-700 dark:text-gray-400">Nama Mata Pelajaran</span>
                    <input type="text" name="nama_mapel" value="{{ old('nama_mapel') }}"
                        placeholder="contoh: Matematika"
                        class="block w-full mt-1 text-sm form-input dark:bg-gray-700 dark:text-gray-300 @error('nama_mapel') border-red-500 @enderror" />
                    @error('nama_mapel')
                        <span class="text-xs text-red-500">{{ $message }}</span>
                    @enderror
                </label>

                <label class="block text-sm mb-4">
                    <span class="text-gray-700 dark:text-gray-400">Kode Mapel <span class="text-gray-400">(opsional)</span></span>
                    <input type="text" name="kode_mapel" value="{{ old('kode_mapel') }}"
                        placeholder="contoh: MTK"
                        class="block w-full mt-1 text-sm form-input dark:bg-gray-700 dark:text-gray-300" />
                </label>

                <label class="block text-sm mb-4">
                    <span class="text-gray-700 dark:text-gray-400">Kelompok</span>
                    <select name="kelompok"
                        class="block w-full mt-1 text-sm dark:bg-gray-700 dark:text-gray-300 @error('kelompok') border-red-500 @enderror">
                        <option value="">-- Pilih Kelompok --</option>
                        <option value="Umum" {{ old('kelompok') == 'Umum' ? 'selected' : '' }}>Umum</option>
                        <option value="Jurusan" {{ old('kelompok') == 'Jurusan' ? 'selected' : '' }}>Jurusan</option>
                        <option value="Muatan Lokal" {{ old('kelompok') == 'Muatan Lokal' ? 'selected' : '' }}>Muatan Lokal</option>
                    </select>
                    @error('kelompok')
                        <span class="text-xs text-red-500">{{ $message }}</span>
                    @enderror
                </label>

                <button type="submit"
                    class="w-full px-4 py-2 text-sm font-medium text-white bg-purple-600 rounded-lg hover:bg-purple-700">
                    Simpan
                </button>
            </form>
        </div>
    </div>
</x-layouts.admin>