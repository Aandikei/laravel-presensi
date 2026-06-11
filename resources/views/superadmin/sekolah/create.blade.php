<x-layouts.admin>
    <x-slot:title>Tambah Sekolah</x-slot:title>

    <div class="container px-6 mx-auto">
        <div class="my-6">
            <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
                Tambah Sekolah Baru
            </h2>
            <a href="{{ route('superadmin.dashboard') }}"
                class="text-sm text-purple-600 hover:underline dark:text-purple-400">
                ← Kembali ke Dashboard
            </a>
        </div>

        <div class="max-w-2xl p-6 bg-white rounded-lg shadow-xs dark:shadow-none dark:border dark:border-gray-700 dark:bg-gray-800">
            <form method="POST" action="{{ route('superadmin.sekolah.store') }}">
                @csrf

                <h3 class="text-md font-semibold text-gray-700 dark:text-gray-200 mb-4 border-b pb-2">Data Sekolah</h3>

                <label class="block text-sm mb-4">
                    <span class="text-gray-700 dark:text-gray-400">Nama Sekolah</span>
                    <input type="text" name="nama_instansi" value="{{ old('nama_instansi') }}"
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
                            <option value="">-- Pilih --</option>
                            @foreach (['SD', 'SMP', 'SMA', 'SMK'] as $j)
                                <option value="{{ $j }}" {{ old('jenjang') == $j ? 'selected' : '' }}>{{ $j }}</option>
                            @endforeach
                        </select>
                        @error('jenjang')
                            <span class="text-xs text-red-500">{{ $message }}</span>
                        @enderror
                    </label>

                    <label class="block text-sm mb-4">
                        <span class="text-gray-700 dark:text-gray-400">NPSN</span>
                        <input type="text" name="npsn" value="{{ old('npsn') }}"
                            class="block w-full mt-1 text-sm form-input dark:bg-gray-700 dark:text-gray-300 @error('npsn') border-red-500 @enderror" />
                        @error('npsn')
                            <span class="text-xs text-red-500">{{ $message }}</span>
                        @enderror
                    </label>
                </div>

                <label class="block text-sm mb-4">
                    <span class="text-gray-700 dark:text-gray-400">Alamat <span class="text-gray-400">(opsional)</span></span>
                    <textarea name="alamat" rows="2"
                        class="block w-full mt-1 text-sm form-textarea dark:bg-gray-700 dark:text-gray-300">{{ old('alamat') }}</textarea>
                </label>

                <div class="grid grid-cols-2 gap-4">
                    <label class="block text-sm mb-4">
                        <span class="text-gray-700 dark:text-gray-400">Telepon <span class="text-gray-400">(opsional)</span></span>
                        <input type="text" name="telepon" value="{{ old('telepon') }}"
                            class="block w-full mt-1 text-sm form-input dark:bg-gray-700 dark:text-gray-300" />
                    </label>

                    <label class="block text-sm mb-4">
                        <span class="text-gray-700 dark:text-gray-400">Email <span class="text-gray-400">(opsional)</span></span>
                        <input type="email" name="email" value="{{ old('email') }}"
                            class="block w-full mt-1 text-sm form-input dark:bg-gray-700 dark:text-gray-300" />
                    </label>
                </div>

                <h3 class="text-md font-semibold text-gray-700 dark:text-gray-200 mb-4 mt-6 border-b pb-2">Admin Sekolah</h3>

                <div class="grid grid-cols-2 gap-4">
                    <label class="block text-sm mb-4">
                        <span class="text-gray-700 dark:text-gray-400">Nama Admin</span>
                        <input type="text" name="admin_name" value="{{ old('admin_name') }}"
                            class="block w-full mt-1 text-sm form-input dark:bg-gray-700 dark:text-gray-300 @error('admin_name') border-red-500 @enderror" />
                        @error('admin_name')
                            <span class="text-xs text-red-500">{{ $message }}</span>
                        @enderror
                    </label>

                    <label class="block text-sm mb-4">
                        <span class="text-gray-700 dark:text-gray-400">Email Admin</span>
                        <input type="email" name="admin_email" value="{{ old('admin_email') }}"
                            class="block w-full mt-1 text-sm form-input dark:bg-gray-700 dark:text-gray-300 @error('admin_email') border-red-500 @enderror" />
                        @error('admin_email')
                            <span class="text-xs text-red-500">{{ $message }}</span>
                        @enderror
                    </label>
                </div>

                <label class="block text-sm mb-4">
                    <span class="text-gray-700 dark:text-gray-400">Password Admin</span>
                    <input type="password" name="admin_password"
                        class="block w-full mt-1 text-sm form-input dark:bg-gray-700 dark:text-gray-300 @error('admin_password') border-red-500 @enderror" />
                    @error('admin_password')
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
