<x-layouts.admin>
    <x-slot:title>Tambah Siswa</x-slot:title>

    <div class="container px-6 mx-auto">
        <div class="my-6">
            <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
                Tambah Siswa
            </h2>
            <a href="{{ route('admin.siswa.index') }}"
                class="text-sm text-purple-600 hover:underline dark:text-purple-400">
                ← Kembali
            </a>
        </div>

        <div class="grid gap-6 md:grid-cols-2">
            <form method="POST" action="{{ route('admin.siswa.store') }}">
                @csrf

                {{-- Data Siswa --}}
                <div class="p-6 bg-white rounded-lg shadow-xs dark:bg-gray-800 mb-6">
                    <h3 class="mb-4 text-lg font-semibold text-gray-700 dark:text-gray-200">
                        Data Siswa
                    </h3>

                    <label class="block text-sm mb-4">
                        <span class="text-gray-700 dark:text-gray-400">Nama Siswa</span>
                        <input type="text" name="nama_siswa" value="{{ old('nama_siswa') }}"
                            class="block w-full mt-1 text-sm form-input dark:bg-gray-700 dark:text-gray-300 @error('nama_siswa') border-red-500 @enderror" />
                        @error('nama_siswa')
                            <span class="text-xs text-red-500">{{ $message }}</span>
                        @enderror
                    </label>

                    <label class="block text-sm mb-4">
                        <span class="text-gray-700 dark:text-gray-400">NISN</span>
                        <input type="text" name="nisn" value="{{ old('nisn') }}"
                            class="block w-full mt-1 text-sm form-input dark:bg-gray-700 dark:text-gray-300 @error('nisn') border-red-500 @enderror" />
                        @error('nisn')
                            <span class="text-xs text-red-500">{{ $message }}</span>
                        @enderror
                    </label>

                    <label class="block text-sm mb-4">
                        <span class="text-gray-700 dark:text-gray-400">Jenis Kelamin</span>
                        <select name="jenis_kelamin"
                            class="block w-full mt-1 text-sm form-select dark:bg-gray-700 dark:text-gray-300 @error('jenis_kelamin') border-red-500 @enderror">
                            <option value="">-- Pilih --</option>
                            <option value="L" {{ old('jenis_kelamin') == 'L' ? 'selected' : '' }}>Laki-laki
                            </option>
                            <option value="P" {{ old('jenis_kelamin') == 'P' ? 'selected' : '' }}>Perempuan
                            </option>
                        </select>
                        @error('jenis_kelamin')
                            <span class="text-xs text-red-500">{{ $message }}</span>
                        @enderror
                    </label>

                    <label class="block text-sm mb-4">
                        <span class="text-gray-700 dark:text-gray-400">Tanggal Lahir <span
                                class="text-gray-400">(opsional)</span></span>
                        <input type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir') }}"
                            class="block w-full mt-1 text-sm form-input dark:bg-gray-700 dark:text-gray-300" />
                    </label>

                    <label class="block text-sm mb-4">
                        <span class="text-gray-700 dark:text-gray-400">Email Siswa</span>
                        <input type="email" name="email" value="{{ old('email') }}"
                            class="block w-full mt-1 text-sm form-input dark:bg-gray-700 dark:text-gray-300 @error('email') border-red-500 @enderror" />
                        @error('email')
                            <span class="text-xs text-red-500">{{ $message }}</span>
                        @enderror
                    </label>

                    <label class="block text-sm mb-4">
                        <span class="text-gray-700 dark:text-gray-400">Password Siswa</span>
                        <input type="password" name="password"
                            class="block w-full mt-1 text-sm form-input dark:bg-gray-700 dark:text-gray-300 @error('password') border-red-500 @enderror" />
                        @error('password')
                            <span class="text-xs text-red-500">{{ $message }}</span>
                        @enderror
                    </label>
                </div>

                {{-- Data Orang Tua --}}
                <div class="p-6 bg-white rounded-lg shadow-xs dark:bg-gray-800 mb-6">
                    <h3 class="mb-4 text-lg font-semibold text-gray-700 dark:text-gray-200">
                        Data Orang Tua
                    </h3>

                    <div
                        class="mb-3 px-3 py-2 text-xs text-blue-700 bg-blue-50 rounded-lg dark:bg-blue-900/30 dark:text-blue-300">
                        Jika orang tua sudah memiliki akun, cukup masukkan email yang sama. Password bisa dikosongkan.
                    </div>

                    <label class="block text-sm mb-4">
                        <span class="text-gray-700 dark:text-gray-400">Nama Orang Tua</span>
                        <input type="text" name="nama_ortu" value="{{ old('nama_ortu') }}"
                            class="block w-full mt-1 text-sm form-input dark:bg-gray-700 dark:text-gray-300 @error('nama_ortu') border-red-500 @enderror" />
                        @error('nama_ortu')
                            <span class="text-xs text-red-500">{{ $message }}</span>
                        @enderror
                    </label>

                    <label class="block text-sm mb-4">
                        <span class="text-gray-700 dark:text-gray-400">Hubungan</span>
                        <select name="hubungan"
                            class="block w-full mt-1 text-sm form-select dark:bg-gray-700 dark:text-gray-300 @error('hubungan') border-red-500 @enderror">
                            <option value="">-- Pilih --</option>
                            <option value="Ayah" {{ old('hubungan') == 'Ayah' ? 'selected' : '' }}>Ayah</option>
                            <option value="Ibu" {{ old('hubungan') == 'Ibu' ? 'selected' : '' }}>Ibu</option>
                            <option value="Wali" {{ old('hubungan') == 'Wali' ? 'selected' : '' }}>Wali</option>
                        </select>
                        @error('hubungan')
                            <span class="text-xs text-red-500">{{ $message }}</span>
                        @enderror
                    </label>

                    <label class="block text-sm mb-4">
                        <span class="text-gray-700 dark:text-gray-400">No HP <span
                                class="text-gray-400">(opsional)</span></span>
                        <input type="text" name="no_hp_ortu" value="{{ old('no_hp_ortu') }}"
                            class="block w-full mt-1 text-sm form-input dark:bg-gray-700 dark:text-gray-300" />
                    </label>

                    <label class="block text-sm mb-4">
                        <span class="text-gray-700 dark:text-gray-400">Email Orang Tua</span>
                        <input type="email" name="email_ortu" value="{{ old('email_ortu') }}"
                            class="block w-full mt-1 text-sm form-input dark:bg-gray-700 dark:text-gray-300 @error('email_ortu') border-red-500 @enderror" />
                        @error('email_ortu')
                            <span class="text-xs text-red-500">{{ $message }}</span>
                        @enderror
                    </label>

                    <label class="block text-sm mb-4">
                        <span class="text-gray-700 dark:text-gray-400">
                            Password Orang Tua
                            <span class="text-gray-400">(kosongkan jika akun sudah ada)</span>
                        </span>
                        <input type="password" name="password_ortu"
                            class="block w-full mt-1 text-sm form-input dark:bg-gray-700 dark:text-gray-300 @error('password_ortu') border-red-500 @enderror" />
                        @error('password_ortu')
                            <span class="text-xs text-red-500">{{ $message }}</span>
                        @enderror
                    </label>
                </div>

                <button type="submit"
                    class="w-full px-4 py-2 text-sm font-medium text-white bg-purple-600 rounded-lg hover:bg-purple-700">
                    Simpan
                </button>
            </form>
        </div>
    </div>
</x-layouts.admin>
