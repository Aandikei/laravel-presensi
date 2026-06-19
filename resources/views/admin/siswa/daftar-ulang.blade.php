<x-layouts.admin>
    <x-slot:title>Daftar Ulang Alumni</x-slot:title>

    <div class="container px-6 mx-auto">
        <div class="my-6">
            <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
                Daftar Ulang Alumni
            </h2>
            <a href="{{ route('admin.siswa.index') }}"
                class="text-sm text-purple-600 hover:underline dark:text-purple-400">
                &larr; Kembali
            </a>
        </div>

        @if (session('info'))
            <div class="px-4 py-3 mb-4 text-sm text-blue-700 bg-blue-100 rounded-lg dark:bg-blue-800 dark:text-blue-200">
                {{ session('info') }}
            </div>
        @endif

        <div class="grid gap-6 md:grid-cols-2">
            <form method="POST" action="{{ route('admin.siswa.proses-daftar-ulang') }}">
                @csrf
                <input type="hidden" name="siswa_id" value="{{ $siswa->id_siswa }}">
                <input type="hidden" name="tahun_id" value="{{ $tahunAktif?->id_tahun }}">

                {{-- Data Siswa (read-only) --}}
                <div class="p-6 bg-white rounded-lg shadow-xs dark:shadow-none dark:border dark:border-gray-700 dark:bg-gray-800 mb-6">
                    <h3 class="mb-4 text-lg font-semibold text-gray-700 dark:text-gray-200">
                        Data Siswa
                    </h3>

                    <label class="block text-sm mb-4">
                        <span class="text-gray-700 dark:text-gray-400">Nama Siswa</span>
                        <input type="text" value="{{ $siswa->nama_siswa }}" readonly disabled
                            class="block w-full mt-1 text-sm form-input bg-gray-100 dark:bg-gray-700 dark:text-gray-400 cursor-not-allowed" />
                    </label>

                    <label class="block text-sm mb-4">
                        <span class="text-gray-700 dark:text-gray-400">NISN</span>
                        <input type="text" value="{{ $siswa->nisn }}" readonly disabled
                            class="block w-full mt-1 text-sm form-input bg-gray-100 dark:bg-gray-700 dark:text-gray-400 cursor-not-allowed" />
                    </label>

                    <label class="block text-sm mb-4">
                        <span class="text-gray-700 dark:text-gray-400">Jenis Kelamin</span>
                        <input type="text" value="{{ $siswa->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}" readonly disabled
                            class="block w-full mt-1 text-sm form-input bg-gray-100 dark:bg-gray-700 dark:text-gray-400 cursor-not-allowed" />
                    </label>

                    <label class="block text-sm mb-4">
                        <span class="text-gray-700 dark:text-gray-400">Tanggal Lahir</span>
                        <input type="text" value="{{ $siswa->tanggal_lahir ? $siswa->tanggal_lahir->format('d F Y') : '-' }}" readonly disabled
                            class="block w-full mt-1 text-sm form-input bg-gray-100 dark:bg-gray-700 dark:text-gray-400 cursor-not-allowed" />
                    </label>

                    <label class="block text-sm mb-4">
                        <span class="text-gray-700 dark:text-gray-400">Email Siswa</span>
                        <input type="email" name="email" value="{{ old('email', $siswa->user->email) }}"
                            class="block w-full mt-1 text-sm form-input dark:bg-gray-700 dark:text-gray-300 @error('email') border-red-500 @enderror" />
                        <span class="text-xs text-gray-400 mt-1 block">Kosongkan jika ingin menggunakan email lama: <strong>{{ $siswa->user->email }}</strong></span>
                        @error('email')
                            <span class="text-xs text-red-500">{{ $message }}</span>
                        @enderror
                    </label>

                    <div class="mb-4 px-3 py-2 text-xs text-blue-700 bg-blue-50 rounded-lg dark:bg-blue-900/30 dark:text-blue-300">
                        Password siswa akan menggunakan NISN secara otomatis.
                    </div>
                </div>

                {{-- Orang Tua --}}
                <div class="p-6 bg-white rounded-lg shadow-xs dark:shadow-none dark:border dark:border-gray-700 dark:bg-gray-800 mb-6">
                    <h3 class="mb-4 text-lg font-semibold text-gray-700 dark:text-gray-200">
                        Orang Tua
                    </h3>

                    <div class="mb-4 px-3 py-2 text-xs text-blue-700 bg-blue-50 rounded-lg dark:bg-blue-900/30 dark:text-blue-300">
                        Siswa ini memiliki data orang tua yang sudah terdaftar. Pilih apakah ingin menggunakan data yang lama atau memasukkan data baru.
                    </div>

                    {{-- Pilihan: Pakai lama --}}
                    <label class="flex items-center gap-3 p-3 border rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 mb-3
                        {{ old('pilihan_ortu', 'lama') == 'lama' ? 'border-purple-500' : 'border-gray-200 dark:border-gray-600' }}" onclick="pilihOrtu('lama')">
                        <input type="radio" name="pilihan_ortu" value="lama" id="pilihan_ortu_lama"
                            {{ old('pilihan_ortu', 'lama') == 'lama' ? 'checked' : '' }}
                            class="w-4 h-4 text-purple-600 border-gray-300 focus:ring-purple-500 focus:ring-2">
                        <div>
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-200">Pakai data orang tua yang lama</span>
                            @foreach ($siswa->orangTua as $ortu)
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $ortu->nama_ortu }} &middot; {{ $ortu->user->email ?? '-' }}
                                    @if ($ortu->pivot->hubungan)
                                        &middot; {{ $ortu->pivot->hubungan }}
                                    @endif
                                </p>
                            @endforeach
                        </div>
                    </label>

                    {{-- Pilihan: Input baru --}}
                    <label class="flex items-center gap-3 p-3 border rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 mb-4
                        {{ old('pilihan_ortu') == 'baru' ? 'border-purple-500' : 'border-gray-200 dark:border-gray-600' }}" onclick="pilihOrtu('baru')">
                        <input type="radio" name="pilihan_ortu" value="baru" id="pilihan_ortu_baru"
                            {{ old('pilihan_ortu') == 'baru' ? 'checked' : '' }}
                            class="w-4 h-4 text-purple-600 border-gray-300 focus:ring-purple-500 focus:ring-2">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-200">Input orang tua baru</span>
                    </label>

                    <div id="form_ortu_baru"
                        class="{{ old('pilihan_ortu', 'lama') == 'baru' ? '' : 'hidden' }}">
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
                                class="block w-full mt-1 text-sm dark:bg-gray-700 dark:text-gray-300 @error('hubungan') border-red-500 @enderror">
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
                            <span class="text-gray-700 dark:text-gray-400">No HP <span class="text-gray-400">(opsional)</span></span>
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

                        <div class="mb-4 px-3 py-2 text-xs text-blue-700 bg-blue-50 rounded-lg dark:bg-blue-900/30 dark:text-blue-300">
                            Password orang tua akan menggunakan NISN siswa secara otomatis.
                        </div>
                    </div>
                </div>

                {{-- Registrasi Kelas --}}
                <div class="p-6 bg-white rounded-lg shadow-xs dark:shadow-none dark:border dark:border-gray-700 dark:bg-gray-800 mb-6">
                    <h3 class="mb-1 text-lg font-semibold text-gray-700 dark:text-gray-200">
                        Daftarkan ke Kelas
                        <span class="text-sm font-normal text-gray-400">(opsional)</span>
                    </h3>
                    <p class="mb-4 text-xs text-gray-500 dark:text-gray-400">
                        Kalau diisi, siswa langsung terdaftar di kelas ini.
                    </p>

                    @if ($tahunAktif)
                        <div class="mb-3 px-3 py-2 text-xs text-blue-700 bg-blue-50 rounded-lg dark:bg-blue-900/30 dark:text-blue-300">
                            Tahun ajaran aktif: <strong>{{ $tahunAktif->nama_tahun }} - {{ $tahunAktif->semester }}</strong>
                        </div>
                    @endif

                    <label class="block text-sm">
                        <span class="text-gray-700 dark:text-gray-400">Pilih Kelas</span>
                        <select name="kelas_id"
                            class="block w-full mt-1 text-sm dark:bg-gray-700 dark:text-gray-300">
                            <option value="">-- Tidak sekarang --</option>
                            @foreach ($kelas as $k)
                                <option value="{{ $k->id_kelas }}" {{ old('kelas_id') == $k->id_kelas ? 'selected' : '' }}>
                                    {{ $k->nama_kelas }}
                                </option>
                            @endforeach
                        </select>
                    </label>
                </div>

                <button type="submit"
                    class="w-full px-4 py-2 text-sm font-medium text-white bg-purple-600 rounded-lg hover:bg-purple-700">
                    Daftar Ulang
                </button>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        function pilihOrtu(pilihan) {
            document.getElementById('pilihan_ortu_' + pilihan).checked = true;
            document.getElementById('form_ortu_baru').classList.toggle('hidden', pilihan !== 'baru');
            
            // Update border styles
            document.querySelectorAll('[onclick^="pilihOrtu"]').forEach(el => {
                el.classList.remove('border-purple-500');
                el.classList.add('border-gray-200', 'dark:border-gray-600');
            });
            document.querySelector('[onclick="pilihOrtu(\'' + pilihan + '\')"]').classList.remove('border-gray-200', 'dark:border-gray-600');
            document.querySelector('[onclick="pilihOrtu(\'' + pilihan + '\')"]').classList.add('border-purple-500');
        }
    </script>
    @endpush
</x-layouts.admin>
