<x-layouts.admin>
    <x-slot:title>Tambah Tahun Ajaran</x-slot:title>

    <div class="container px-6 mx-auto">
        <div class="my-6">
            <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
                Tambah Tahun Ajaran
            </h2>
            <a href="{{ route('admin.tahun-ajaran.index') }}"
                class="text-sm text-purple-600 hover:underline dark:text-purple-400">
                ← Kembali
            </a>
        </div>

        <div class="max-w-lg p-6 bg-white rounded-lg shadow-xs dark:shadow-none dark:border dark:border-gray-700 dark:bg-gray-800">
            <form method="POST" action="{{ route('admin.tahun-ajaran.store') }}">
                @csrf

                {{-- Nama Tahun --}}
                <label class="block text-sm mb-4">
                    <span class="text-gray-700 dark:text-gray-400">Tahun Ajaran</span>
                    <input type="text" name="nama_tahun" value="{{ old('nama_tahun') }}"
                        placeholder="contoh: 2024/2025"
                        class="block w-full mt-1 text-sm form-input dark:bg-gray-700 dark:text-gray-300 @error('nama_tahun') border-red-500 @enderror" />
                    @error('nama_tahun')
                        <span class="text-xs text-red-500">{{ $message }}</span>
                    @enderror
                </label>

                {{-- Semester --}}
                <label class="block text-sm mb-4">
                    <span class="text-gray-700 dark:text-gray-400">Semester</span>
                    <select name="semester"
                        class="block w-full mt-1 text-sm dark:bg-gray-700 dark:text-gray-300 @error('semester') border-red-500 @enderror">
                        <option value="">-- Pilih Semester --</option>
                        <option value="Ganjil" {{ old('semester') == 'Ganjil' ? 'selected' : '' }}>Ganjil</option>
                        <option value="Genap" {{ old('semester') == 'Genap' ? 'selected' : '' }}>Genap</option>
                    </select>
                    @error('semester')
                        <span class="text-xs text-red-500">{{ $message }}</span>
                    @enderror
                </label>

                {{-- Tanggal Mulai --}}
                <label class="block text-sm mb-4">
                    <span class="text-gray-700 dark:text-gray-400">Tanggal Mulai</span>
                    <input type="date" name="tanggal_mulai" value="{{ old('tanggal_mulai') }}"
                        class="block w-full mt-1 text-sm form-input dark:bg-gray-700 dark:text-gray-300 @error('tanggal_mulai') border-red-500 @enderror" />
                    @error('tanggal_mulai')
                        <span class="text-xs text-red-500">{{ $message }}</span>
                    @enderror
                </label>

                {{-- Tanggal Selesai --}}
                <label class="block text-sm mb-4">
                    <span class="text-gray-700 dark:text-gray-400">Tanggal Selesai</span>
                    <input type="date" name="tanggal_selesai" value="{{ old('tanggal_selesai') }}"
                        class="block w-full mt-1 text-sm form-input dark:bg-gray-700 dark:text-gray-300 @error('tanggal_selesai') border-red-500 @enderror" />
                    @error('tanggal_selesai')
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