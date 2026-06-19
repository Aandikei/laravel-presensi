<x-layouts.admin>
    <x-slot:title>Edit Siswa</x-slot:title>

    <div class="container px-6 mx-auto">
        <div class="my-6">
            <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
                Edit Siswa
            </h2>
            <a href="{{ route('admin.siswa.index') }}"
                class="text-sm text-purple-600 hover:underline dark:text-purple-400">
                ← Kembali
            </a>
        </div>

        {{-- Status & Transfer Info --}}
        @php
            $statusPindah = $siswa->registrasiAkademik->firstWhere('status', 'Pindah');
        @endphp
        @if($statusPindah || $siswa->transfer_token)
            <div class="max-w-lg mb-4 p-4 rounded-lg border {{ $statusPindah ? 'bg-yellow-50 border-yellow-300 dark:bg-yellow-900/20 dark:border-yellow-700' : 'bg-blue-50 border-blue-300 dark:bg-blue-900/20 dark:border-blue-700' }}">
                <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-200 mb-2">
                    @if($statusPindah)
                        Status: <span class="text-yellow-600 dark:text-yellow-400">Pindah</span>
                        @if($statusPindah->tanggal_mutasi)
                            ({{ $statusPindah->tanggal_mutasi->format('j M Y H:i') }})
                        @endif
                    @else
                        Status: <span class="text-blue-600 dark:text-blue-400">Menunggu diterima</span>
                    @endif
                </h4>
                @if($statusPindah?->alasan_mutasi)
                    <p class="text-xs text-gray-600 dark:text-gray-400 mb-2">
                        Alasan pindah: <strong>{{ $statusPindah->alasan_mutasi }}</strong>
                    </p>
                @endif
                @if($siswa->transfer_token)
                    <p class="text-xs text-gray-600 dark:text-gray-400 mb-2">
                        Kode transfer: <strong class="text-sm text-yellow-700 dark:text-yellow-300">{{ $siswa->transfer_token }}</strong>
                        <button onclick="navigator.clipboard.writeText('{{ $siswa->transfer_token }}')" class="ml-1 text-purple-600 hover:underline text-xs">Salin</button>
                        @if($siswa->transfer_token_expires_at)
                            <span class="block text-xs mt-1">Berlaku sampai {{ $siswa->transfer_token_expires_at->format('j M Y H:i') }}</span>
                        @endif
                    </p>
                @endif
                @if($statusPindah)
                    <form method="POST" action="{{ route('admin.siswa.batal-pindah', $siswa) }}" class="inline"
                        onsubmit="return confirm('Batalkan status pindah? Siswa akan kembali aktif.')">
                        @csrf
                        <button type="submit" class="text-xs px-3 py-1 bg-yellow-600 text-white rounded hover:bg-yellow-700">
                            Batalkan Pindah
                        </button>
                    </form>
                @endif
            </div>
        @endif

        <div class="max-w-lg p-6 bg-white rounded-lg shadow-xs dark:shadow-none dark:border dark:border-gray-700 dark:bg-gray-800">
            <form method="POST" action="{{ route('admin.siswa.update', $siswa) }}">
                @csrf
                @method('PUT')

                <label class="block text-sm mb-4">
                    <span class="text-gray-700 dark:text-gray-400">Nama Siswa</span>
                    <input type="text" name="nama_siswa"
                        value="{{ old('nama_siswa', $siswa->nama_siswa) }}"
                        class="block w-full mt-1 text-sm form-input dark:bg-gray-700 dark:text-gray-300 @error('nama_siswa') border-red-500 @enderror" />
                    @error('nama_siswa')
                        <span class="text-xs text-red-500">{{ $message }}</span>
                    @enderror
                </label>

                <label class="block text-sm mb-4">
                    <span class="text-gray-700 dark:text-gray-400">NISN</span>
                    <input type="text" name="nisn"
                        value="{{ old('nisn', $siswa->nisn) }}"
                        class="block w-full mt-1 text-sm form-input dark:bg-gray-700 dark:text-gray-300 @error('nisn') border-red-500 @enderror" />
                    @error('nisn')
                        <span class="text-xs text-red-500">{{ $message }}</span>
                    @enderror
                </label>

                <label class="block text-sm mb-4">
                    <span class="text-gray-700 dark:text-gray-400">Jenis Kelamin</span>
                    <select name="jenis_kelamin"
                        class="block w-full mt-1 text-sm dark:bg-gray-700 dark:text-gray-300">
                        <option value="L" {{ old('jenis_kelamin', $siswa->jenis_kelamin) == 'L' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="P" {{ old('jenis_kelamin', $siswa->jenis_kelamin) == 'P' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                </label>

                <label class="block text-sm mb-4">
                    <span class="text-gray-700 dark:text-gray-400">Tanggal Lahir</span>
                    <input type="date" name="tanggal_lahir"
                        value="{{ old('tanggal_lahir', $siswa->tanggal_lahir?->format('Y-m-d')) }}"
                        class="block w-full mt-1 text-sm form-input dark:bg-gray-700 dark:text-gray-300" />
                </label>

                <label class="block text-sm mb-4">
                    <span class="text-gray-700 dark:text-gray-400">Email</span>
                    <input type="email" name="email"
                        value="{{ old('email', $siswa->user->email) }}"
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
                        class="block w-full mt-1 text-sm form-input dark:bg-gray-700 dark:text-gray-300" />
                </label>

                <button type="submit"
                    class="w-full px-4 py-2 text-sm font-medium text-white bg-purple-600 rounded-lg hover:bg-purple-700">
                    Update
                </button>
            </form>
        </div>
    </div>
</x-layouts.admin>