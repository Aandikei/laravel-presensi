<x-layouts.admin>
    <x-slot:title>Edit Guru</x-slot:title>

    <div class="container px-6 mx-auto">
        <div class="my-6">
            <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
                Edit Guru
            </h2>
            <a href="{{ route('admin.guru.index') }}"
                class="text-sm text-purple-600 hover:underline dark:text-purple-400">
                ← Kembali
            </a>
        </div>

        <div class="max-w-lg p-6 bg-white rounded-lg shadow-xs dark:shadow-none dark:border dark:border-gray-700 dark:bg-gray-800">
            <form method="POST" action="{{ route('admin.guru.update', $guru) }}">
                @csrf
                @method('PUT')

                <label class="block text-sm mb-4">
                    <span class="text-gray-700 dark:text-gray-400">Nama Guru</span>
                    <input type="text" name="nama_guru"
                        value="{{ old('nama_guru', $guru->nama_guru) }}"
                        class="block w-full mt-1 text-sm form-input dark:bg-gray-700 dark:text-gray-300 @error('nama_guru') border-red-500 @enderror" />
                    @error('nama_guru')
                        <span class="text-xs text-red-500">{{ $message }}</span>
                    @enderror
                </label>

                <label class="block text-sm mb-4">
                    <span class="text-gray-700 dark:text-gray-400">NIP <span class="text-gray-400">(opsional)</span></span>
                    <input type="text" name="nip"
                        value="{{ old('nip', $guru->nip) }}"
                        class="block w-full mt-1 text-sm form-input dark:bg-gray-700 dark:text-gray-300 @error('nip') border-red-500 @enderror" />
                    @error('nip')
                        <span class="text-xs text-red-500">{{ $message }}</span>
                    @enderror
                </label>

                <label class="block text-sm mb-4">
                    <span class="text-gray-700 dark:text-gray-400">Email</span>
                    <input type="email" name="email"
                        value="{{ old('email', $guru->user->email) }}"
                        class="block w-full mt-1 text-sm form-input dark:bg-gray-700 dark:text-gray-300 @error('email') border-red-500 @enderror" />
                    @error('email')
                        <span class="text-xs text-red-500">{{ $message }}</span>
                    @enderror
                </label>

                <label class="block text-sm mb-4">
                    <span class="text-gray-700 dark:text-gray-400">
                        Password <span class="text-gray-400">(kosongkan jika tidak diubah)</span>
                    </span>
                    <input type="password" name="password"
                        class="block w-full mt-1 text-sm form-input dark:bg-gray-700 dark:text-gray-300 @error('password') border-red-500 @enderror" />
                    @error('password')
                        <span class="text-xs text-red-500">{{ $message }}</span>
                    @enderror
                </label>

                <label class="block text-sm mb-4">
                    <span class="text-gray-700 dark:text-gray-400">Jenis Kelamin</span>
                    <select name="jenis_kelamin"
                        class="block w-full mt-1 text-sm dark:bg-gray-700 dark:text-gray-300">
                        <option value="L" {{ old('jenis_kelamin', $guru->jenis_kelamin) == 'L' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="P" {{ old('jenis_kelamin', $guru->jenis_kelamin) == 'P' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                </label>

                <label class="block text-sm mb-4">
                    <span class="text-gray-700 dark:text-gray-400">No HP <span class="text-gray-400">(opsional)</span></span>
                    <input type="text" name="no_hp"
                        value="{{ old('no_hp', $guru->no_hp) }}"
                        class="block w-full mt-1 text-sm form-input dark:bg-gray-700 dark:text-gray-300" />
                </label>

                @if(count($jabatanTersedia) > 0)
                <label class="block text-sm mb-4">
                    <span class="text-gray-700 dark:text-gray-400">Jabatan <span class="text-gray-400">(opsional)</span></span>
                    <select name="jabatan"
                        class="block w-full mt-1 text-sm dark:bg-gray-700 dark:text-gray-300 @error('jabatan') border-red-500 @enderror">
                        @php
                            $currentJabatan = old('jabatan', $guru->user->hasRole('kepala_sekolah') ? 'kepala_sekolah' : ($guru->user->hasRole('wakil_kepala_sekolah') ? 'wakil_kepala_sekolah' : ''));
                        @endphp
                        <option value="">-- Tidak Ada --</option>
                        @foreach($jabatanTersedia as $value => $label)
                            <option value="{{ $value }}" {{ $currentJabatan == $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('jabatan')
                        <span class="text-xs text-red-500">{{ $message }}</span>
                    @enderror
                </label>
                @endif

                <button type="submit"
                    class="w-full px-4 py-2 text-sm font-medium text-white bg-purple-600 rounded-lg hover:bg-purple-700">
                    Update
                </button>
            </form>
        </div>
    </div>
</x-layouts.admin>