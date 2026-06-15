<x-layouts.admin>
    <x-slot:title>Edit Sekolah</x-slot:title>

    <div class="container px-6 mx-auto">
        <div class="my-6">
            <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
                Edit Sekolah
            </h2>
            <a href="{{ route('superadmin.dashboard') }}"
                class="text-sm text-purple-600 hover:underline dark:text-purple-400">
                ← Kembali ke Dashboard
            </a>
        </div>

        <div class="max-w-2xl p-6 bg-white rounded-lg shadow-xs dark:shadow-none dark:border dark:border-gray-700 dark:bg-gray-800">
            <form method="POST" action="{{ route('superadmin.sekolah.update', $instansi->id_instansi) }}">
                @csrf
                @method('PUT')

                <label class="block text-sm mb-4">
                    <span class="text-gray-700 dark:text-gray-400">Nama Sekolah</span>
                    <input type="text" name="nama_instansi" value="{{ old('nama_instansi', $instansi->nama_instansi) }}"
                        class="block w-full mt-1 text-sm form-input dark:bg-gray-700 dark:text-gray-300 @error('nama_instansi') border-red-500 @enderror" />
                    @error('nama_instansi')
                        <span class="text-xs text-red-500">{{ $message }}</span>
                    @enderror
                </label>

                <div class="grid grid-cols-2 gap-4">
                    <label class="block text-sm mb-4">
                        <span class="text-gray-700 dark:text-gray-400">Jenjang</span>
                        <select name="jenjang"
                            class="block w-full mt-1 text-sm dark:bg-gray-700 dark:text-gray-300 @error('jenjang') border-red-500 @enderror">
                            @foreach (['SD', 'SMP', 'SMA'] as $j)
                                <option value="{{ $j }}" {{ old('jenjang', $instansi->jenjang) == $j ? 'selected' : '' }}>{{ $j }}</option>
                            @endforeach
                        </select>
                        @error('jenjang')
                            <span class="text-xs text-red-500">{{ $message }}</span>
                        @enderror
                    </label>

                    <label class="block text-sm mb-4">
                        <span class="text-gray-700 dark:text-gray-400">NPSN</span>
                        <input type="text" name="npsn" value="{{ old('npsn', $instansi->npsn) }}"
                            class="block w-full mt-1 text-sm form-input dark:bg-gray-700 dark:text-gray-300 @error('npsn') border-red-500 @enderror" />
                        @error('npsn')
                            <span class="text-xs text-red-500">{{ $message }}</span>
                        @enderror
                    </label>
                </div>

                <label class="block text-sm mb-4">
                    <span class="text-gray-700 dark:text-gray-400">Alamat</span>
                    <textarea name="alamat" rows="2"
                        class="block w-full mt-1 text-sm form-textarea dark:bg-gray-700 dark:text-gray-300">{{ old('alamat', $instansi->alamat) }}</textarea>
                </label>

                <div class="grid grid-cols-2 gap-4">
                    <label class="block text-sm mb-4">
                        <span class="text-gray-700 dark:text-gray-400">Telepon</span>
                        <input type="text" name="telepon" value="{{ old('telepon', $instansi->telepon) }}"
                            class="block w-full mt-1 text-sm form-input dark:bg-gray-700 dark:text-gray-300" />
                    </label>

                    <label class="block text-sm mb-4">
                        <span class="text-gray-700 dark:text-gray-400">Email</span>
                        <input type="email" name="email" value="{{ old('email', $instansi->email) }}"
                            class="block w-full mt-1 text-sm form-input dark:bg-gray-700 dark:text-gray-300" />
                    </label>
                </div>

                <button type="submit"
                    class="w-full px-4 py-2 text-sm font-medium text-white bg-purple-600 rounded-lg hover:bg-purple-700">
                    Simpan Perubahan
                </button>
            </form>
        </div>
    </div>
</x-layouts.admin>
