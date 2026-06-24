<x-layouts.admin>
    <x-slot:title>Edit Guru</x-slot:title>

    <div class="container px-6 mx-auto">
        <div class="my-6">
            <x-breadcrumb :items="[
                ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
                ['label' => 'Data Guru', 'url' => route('admin.guru.index')],
                ['label' => 'Edit Guru'],
            ]" />
            <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
                Edit Guru
            </h2>
        </div>

        {{-- Status & Transfer Info --}}
        @php
            $sedangMutasi = $guru->transfer_token && !$guru->isTransferTokenExpired();
        @endphp
        @if($sedangMutasi || !$guru->isAktif())
            <div class="max-w-lg mb-4 p-4 rounded-lg border {{ $sedangMutasi ? 'bg-yellow-50 border-yellow-300 dark:bg-yellow-900/20 dark:border-yellow-700' : 'bg-red-50 border-red-300 dark:bg-red-900/20 dark:border-red-700' }}">
                <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-200 mb-2">
                    @if($sedangMutasi)
                        Status: <span class="text-yellow-600 dark:text-yellow-400">Mutasi ke {{ $guru->instansiTujuan->nama_instansi ?? '?' }}</span>
                    @else
                        Status: <span class="text-red-600 dark:text-red-400">{{ $guru->status_label ?? 'Tidak Aktif' }}</span>
                    @endif
                </h4>
                @if($sedangMutasi && $guru->transfer_token)
                    <p class="text-xs text-gray-600 dark:text-gray-400 mb-2">
                        Kode transfer: <strong class="text-sm text-yellow-700 dark:text-yellow-300">{{ $guru->transfer_token }}</strong>
                        <button onclick="navigator.clipboard.writeText('{{ $guru->transfer_token }}').then(() => window.dispatchEvent(new CustomEvent('app-toast', { detail: { type: 'success', message: 'Kode berhasil disalin!' } })))" class="ml-1 text-purple-600 hover:underline text-xs">Salin</button>
                        @if($guru->transfer_token_expires_at)
                            <span class="block text-xs mt-1">Berlaku sampai {{ $guru->transfer_token_expires_at->format('j M Y H:i') }}</span>
                        @endif
                    </p>
                @endif
                @if($sedangMutasi)
                    <form method="POST" action="{{ route('admin.guru.mutasi.batal', $guru) }}" class="inline"
                        onsubmit="return confirm('Batalkan mutasi? Guru akan kembali aktif.')">
                        @csrf
                        <button type="submit" class="text-xs px-3 py-1 bg-yellow-600 text-white rounded hover:bg-yellow-700">
                            Batalkan Mutasi
                        </button>
                    </form>
                @endif
            </div>
        @endif

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