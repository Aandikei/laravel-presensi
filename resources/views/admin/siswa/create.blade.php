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

                <input type="hidden" name="tahun_id" value="{{ $tahunAktif?->id_tahun }}">

                {{-- Data Siswa --}}
                <div class="p-6 bg-white rounded-lg shadow-xs dark:shadow-none dark:border dark:border-gray-700 dark:bg-gray-800 mb-6">
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
                        <span class="text-xs text-gray-400 mt-1 block">Jika siswa pindahan dari sekolah lain, gunakan menu <a href="{{ route('admin.siswa.pindah.form-masuk') }}" class="text-purple-600 hover:underline">Terima Pindahan</a>. Alumni dari sekolah lain akan otomatis terdeteksi.</span>
                    </label>

                    <label class="block text-sm mb-4">
                        <span class="text-gray-700 dark:text-gray-400">Jenis Kelamin</span>
                        <select name="jenis_kelamin"
                            class="block w-full mt-1 text-sm dark:bg-gray-700 dark:text-gray-300 @error('jenis_kelamin') border-red-500 @enderror">
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
                </div>

                {{-- Data Orang Tua --}}
                <div class="p-6 bg-white rounded-lg shadow-xs dark:shadow-none dark:border dark:border-gray-700 dark:bg-gray-800 mb-6">
                    <h3 class="mb-4 text-lg font-semibold text-gray-700 dark:text-gray-200">
                        Data Orang Tua
                    </h3>

                    {{-- Email orang tua — satu-satunya yang tampil di awal --}}
                    <label class="block text-sm mb-4">
                        <span class="text-gray-700 dark:text-gray-400">Email Orang Tua</span>
                        <input type="email" name="email_ortu" id="email_ortu" value="{{ old('email_ortu') }}"
                            class="block w-full mt-1 text-sm form-input dark:bg-gray-700 dark:text-gray-300 @error('email_ortu') border-red-500 @enderror" />
                        @error('email_ortu')
                            <span class="text-xs text-red-500">{{ $message }}</span>
                        @enderror
                    </label>

                    <div
                        class="mb-3 px-3 py-2 text-xs text-blue-700 bg-blue-50 rounded-lg dark:bg-blue-900/30 dark:text-blue-300">
                        Password default orang tua = <strong>NISN</strong>.
                        Jika lupa, gunakan fitur <strong>Lupa Password</strong> di halaman login.
                    </div>

                    {{-- Info email sudah terdaftar (muncul setelah cek) --}}
                    <div id="ortu_lama_info" class="hidden mb-3 px-3 py-2 text-xs text-green-700 bg-green-50 rounded-lg dark:bg-green-900/30 dark:text-green-300">
                        Email sudah terdaftar atas nama: <strong id="ortu_lama_nama"></strong>.
                        Cukup isi <strong>Hubungan</strong> saja.
                    </div>

                    {{-- Field yang muncul setelah cek email (sembunyi di awal) --}}
                    <div id="ortu_fields_after_check" class="hidden">
                        {{-- Nama & No HP untuk ortu baru --}}
                        <div id="ortu_baru_fields">
                            <label class="block text-sm mb-4">
                                <span class="text-gray-700 dark:text-gray-400">Nama Orang Tua</span>
                                <input type="text" name="nama_ortu" value="{{ old('nama_ortu') }}"
                                    class="block w-full mt-1 text-sm form-input dark:bg-gray-700 dark:text-gray-300 @error('nama_ortu') border-red-500 @enderror" />
                                @error('nama_ortu')
                                    <span class="text-xs text-red-500">{{ $message }}</span>
                                @enderror
                            </label>

                            <label class="block text-sm mb-4">
                                <span class="text-gray-700 dark:text-gray-400">No HP <span
                                        class="text-gray-400">(opsional)</span></span>
                                <input type="text" name="no_hp_ortu" value="{{ old('no_hp_ortu') }}"
                                    class="block w-full mt-1 text-sm form-input dark:bg-gray-700 dark:text-gray-300" />
                            </label>
                        </div>

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
                    </div>
                </div>

                {{-- Registrasi Kelas (Opsional) --}}
                <div class="p-6 bg-white rounded-lg shadow-xs dark:shadow-none dark:border dark:border-gray-700 dark:bg-gray-800 mb-6">
                    <h3 class="mb-1 text-lg font-semibold text-gray-700 dark:text-gray-200">
                        Daftarkan ke Kelas
                        <span class="text-sm font-normal text-gray-400">(opsional)</span>
                    </h3>
                    <p class="mb-4 text-xs text-gray-500 dark:text-gray-400">
                        Kalau diisi, siswa langsung terdaftar di kelas ini. Bisa juga didaftarkan nanti lewat menu
                        Registrasi.
                    </p>

                    @if ($tahunAktif)
                        <div
                            class="mb-3 px-3 py-2 text-xs text-blue-700 bg-blue-50 rounded-lg dark:bg-blue-900/30 dark:text-blue-300">
                            Tahun ajaran aktif: <strong>{{ $tahunAktif->nama_tahun }} -
                                {{ $tahunAktif->semester }}</strong>
                        </div>
                    @endif

                    <label class="block text-sm">
                        <span class="text-gray-700 dark:text-gray-400">Pilih Kelas</span>
                        <select name="kelas_id"
                            class="block w-full mt-1 text-sm dark:bg-gray-700 dark:text-gray-300">
                            <option value="">-- Tidak sekarang --</option>
                            @foreach ($kelas as $k)
                                <option value="{{ $k->id_kelas }}"
                                    {{ old('kelas_id') == $k->id_kelas ? 'selected' : '' }}>
                                    {{ $k->nama_kelas }}
                                </option>
                            @endforeach
                        </select>
                    </label>
                </div>

                <button type="submit"
                    class="w-full px-4 py-2 text-sm font-medium text-white bg-purple-600 rounded-lg hover:bg-purple-700">
                    Simpan
                </button>
            </form>
        </div>
    </div>
    @push('scripts')
    <script>
        const emailInput = document.getElementById('email_ortu');
        const afterCheck = document.getElementById('ortu_fields_after_check');
        const ortuBaruFields = document.getElementById('ortu_baru_fields');
        const ortuLamaInfo = document.getElementById('ortu_lama_info');
        const ortuLamaNama = document.getElementById('ortu_lama_nama');
        let debounceTimer;

        emailInput.addEventListener('input', function () {
            clearTimeout(debounceTimer);
            const email = this.value.trim();

            if (!email || !email.includes('@')) {
                afterCheck.classList.add('hidden');
                ortuLamaInfo.classList.add('hidden');
                return;
            }

            debounceTimer = setTimeout(() => {
                fetch('{{ route("admin.siswa.cek-email-ortu") }}?email=' + encodeURIComponent(email))
                    .then(res => res.json())
                    .then(data => {
                        afterCheck.classList.remove('hidden');

                        if (data.exists) {
                            ortuBaruFields.classList.add('hidden');
                            ortuLamaInfo.classList.remove('hidden');
                            ortuLamaNama.textContent = data.nama_ortu;
                        } else {
                            ortuBaruFields.classList.remove('hidden');
                            ortuLamaInfo.classList.add('hidden');
                        }
                    });
            }, 500);
        });
    </script>
    @endpush
</x-layouts.admin>
