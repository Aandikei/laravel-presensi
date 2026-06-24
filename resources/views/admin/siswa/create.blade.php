<x-layouts.admin>
    <x-slot:title>Tambah Siswa</x-slot:title>

    <div class="container px-6 mx-auto">
        <div class="my-6">
            <x-breadcrumb :items="[
                ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
                ['label' => 'Data Siswa', 'url' => route('admin.siswa.index')],
                ['label' => 'Tambah Siswa'],
            ]" />
            <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
                Tambah Siswa
            </h2>
        </div>

        {{-- Step 1: Cek NISN --}}
        <div id="wrapper-cek-nisn" class="p-6 bg-white rounded-lg shadow-xs dark:shadow-none dark:border dark:border-gray-700 dark:bg-gray-800 mb-6">
            <h3 class="mb-1 text-lg font-semibold text-gray-700 dark:text-gray-200">Cek NISN</h3>
            <p class="mb-4 text-xs text-gray-500 dark:text-gray-400">Masukkan NISN untuk mengecek status siswa.</p>

            <label class="block text-sm">
                <span class="text-gray-700 dark:text-gray-400">NISN</span>
                <input type="text" id="nisn" value="{{ old('nisn') }}"
                    class="block w-full mt-1 text-sm form-input dark:bg-gray-700 dark:text-gray-300 @error('nisn') border-red-500 @enderror" autocomplete="off" />
                <span id="nisn-loading" class="hidden text-xs text-blue-600 dark:text-blue-400 mt-1">Mengecek...</span>
                @error('nisn')
                    <span class="text-xs text-red-500">{{ $message }}</span>
                @enderror
            </label>
        </div>

        {{-- Hasil cek NISN --}}
        <div id="nisn-result" class="hidden mb-6">
            <div id="result-same" class="hidden p-6 bg-white rounded-lg shadow-xs dark:shadow-none dark:border dark:border-gray-700 dark:bg-gray-800">
                <div id="same-active" class="px-4 py-3 text-sm text-yellow-700 bg-yellow-100 rounded-lg dark:bg-yellow-900/30 dark:text-yellow-300">
                    NISN <strong id="same-nama"></strong> sudah terdaftar di sekolah ini.
                </div>
                <div id="same-keluar" class="hidden px-4 py-3 text-sm text-red-700 bg-red-100 rounded-lg dark:bg-red-900/30 dark:text-red-300">
                    Siswa <strong id="same-keluar-nama"></strong> sudah terdaftar dengan status <strong>Keluar</strong>.
                    <a href="{{ route('admin.siswa.index') }}" class="block mt-2 text-purple-600 hover:underline font-medium">Batalkan Tandai Keluar →</a>
                </div>
                <div id="same-pindah" class="hidden px-4 py-3 text-sm text-yellow-700 bg-yellow-100 rounded-lg dark:bg-yellow-900/30 dark:text-yellow-300">
                    Siswa <strong id="same-pindah-nama"></strong> sudah terdaftar dengan status <strong>Pindah</strong>.
                    <a href="{{ route('admin.siswa.index') }}" class="block mt-2 text-purple-600 hover:underline font-medium">Ke Data Siswa →</a>
                </div>
            </div>

            <div id="result-daftar-ulang" class="hidden p-6 bg-white rounded-lg shadow-xs dark:shadow-none dark:border dark:border-gray-700 dark:bg-gray-800">
                <div class="px-4 py-3 text-sm text-blue-700 bg-blue-50 rounded-lg dark:bg-blue-900/30 dark:text-blue-300">
                    Siswa <strong id="du-nama"></strong> sudah <span id="du-status"></span> dari <strong id="du-instansi"></strong>.
                    <a id="du-link" href="#" class="block mt-2 text-purple-600 hover:underline font-medium">Daftarkan Ulang →</a>
                </div>
            </div>

            <div id="result-blocked" class="hidden p-6 bg-white rounded-lg shadow-xs dark:shadow-none dark:border dark:border-gray-700 dark:bg-gray-800">
                <div class="px-4 py-3 text-sm text-red-700 bg-red-100 rounded-lg dark:bg-red-900/30 dark:text-red-300">
                    <strong id="blocked-nama"></strong>
                    <span id="blocked-message" class="block mt-1"></span>
                </div>
            </div>

            <div id="result-pindah" class="hidden p-6 bg-white rounded-lg shadow-xs dark:shadow-none dark:border dark:border-gray-700 dark:bg-gray-800">
                <div class="px-4 py-3 text-sm text-yellow-700 bg-yellow-100 rounded-lg dark:bg-yellow-900/30 dark:text-yellow-300">
                    Siswa <strong id="pindah-nama"></strong> masih terdaftar di <strong id="pindah-instansi"></strong>.
                    <a id="pindah-link" href="{{ route('admin.siswa.pindah.form-masuk') }}" class="block mt-2 text-purple-600 hover:underline font-medium">Terima Pindahan →</a>
                </div>
            </div>
        </div>

        {{-- Step 2: Form lengkap --}}
        <div id="step-form" class="hidden">
            <form method="POST" action="{{ route('admin.siswa.store') }}">
                @csrf
                <input type="hidden" name="tahun_id" value="{{ $tahunAktif?->id_tahun }}">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Card Kiri: Data Siswa --}}
                    <div class="p-6 bg-white rounded-lg shadow-xs dark:shadow-none dark:border dark:border-gray-700 dark:bg-gray-800">
                        <h3 class="mb-3 text-lg font-semibold text-gray-700 dark:text-gray-200">Data Siswa</h3>
                        <hr class="mb-4 border-gray-200 dark:border-gray-700">

                        <div>
                            <label class="block text-sm mb-4">
                                <span class="text-gray-700 dark:text-gray-400">Nama Siswa</span>
                                <input type="text" name="nama_siswa" value="{{ old('nama_siswa') }}"
                                    class="block w-full mt-1 text-sm form-input dark:bg-gray-700 dark:text-gray-300 @error('nama_siswa') border-red-500 @enderror" />
                                @error('nama_siswa')
                                    <span class="text-xs text-red-500">{{ $message }}</span>
                                @enderror
                            </label>

                            <label class="block text-sm mb-4">
                                <span class="text-gray-700 dark:text-gray-400">Jenis Kelamin</span>
                                <select name="jenis_kelamin"
                                    class="block w-full mt-1 text-sm dark:bg-gray-700 dark:text-gray-300 @error('jenis_kelamin') border-red-500 @enderror">
                                    <option value="">-- Pilih --</option>
                                    <option value="L" {{ old('jenis_kelamin') == 'L' ? 'selected' : '' }}>Laki-laki</option>
                                    <option value="P" {{ old('jenis_kelamin') == 'P' ? 'selected' : '' }}>Perempuan</option>
                                </select>
                                @error('jenis_kelamin')
                                    <span class="text-xs text-red-500">{{ $message }}</span>
                                @enderror
                            </label>

                            <label class="block text-sm mb-4">
                                <span class="text-gray-700 dark:text-gray-400">NISN</span>
                                <input type="text" name="nisn" id="form-nisn" value="{{ old('nisn') }}"
                                    class="block w-full mt-1 text-sm form-input dark:bg-gray-700 dark:text-gray-300 @error('nisn') border-red-500 @enderror" readonly />
                                @error('nisn')
                                    <span class="text-xs text-red-500">{{ $message }}</span>
                                @enderror
                            </label>

                            <label class="block text-sm mb-4">
                                <span class="text-gray-700 dark:text-gray-400">Tanggal Lahir <span class="text-gray-400">(opsional)</span></span>
                                <input type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir') }}"
                                    class="block w-full mt-1 text-sm form-input dark:bg-gray-700 dark:text-gray-300" />
                            </label>

                            <label class="block text-sm mb-0">
                                <span class="text-gray-700 dark:text-gray-400">Email Siswa</span>
                                <input type="email" name="email" value="{{ old('email') }}"
                                    class="block w-full mt-1 text-sm form-input dark:bg-gray-700 dark:text-gray-300 @error('email') border-red-500 @enderror" />
                                @error('email')
                                    <span class="text-xs text-red-500">{{ $message }}</span>
                                @enderror
                            </label>
                        </div>
                    </div>

                    {{-- Card Kanan: Data Orang Tua + Daftarkan Kelas --}}
                    <div class="space-y-6">
                        <div class="p-6 bg-white rounded-lg shadow-xs dark:shadow-none dark:border dark:border-gray-700 dark:bg-gray-800">
                            <h3 class="mb-3 text-lg font-semibold text-gray-700 dark:text-gray-200">Data Orang Tua</h3>
                            <hr class="mb-4 border-gray-200 dark:border-gray-700">

                            <div class="mb-3 px-3 py-2 text-xs text-blue-700 bg-blue-50 rounded-lg dark:bg-blue-900/30 dark:text-blue-300">
                                Password default orang tua = <strong>NISN</strong>.
                                Jika lupa, gunakan fitur <strong>Lupa Password</strong> di halaman login.
                            </div>

                            <label class="block text-sm mb-4">
                                <span class="text-gray-700 dark:text-gray-400">Email Orang Tua</span>
                                <input type="email" name="email_ortu" id="email_ortu" value="{{ old('email_ortu') }}"
                                    class="block w-full mt-1 text-sm form-input dark:bg-gray-700 dark:text-gray-300 @error('email_ortu') border-red-500 @enderror" />
                                @error('email_ortu')
                                    <span class="text-xs text-red-500">{{ $message }}</span>
                                @enderror
                            </label>

                            <div id="ortu_baru_fields" class="hidden">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-0">
                                    <label class="block text-sm mb-4">
                                        <span class="text-gray-700 dark:text-gray-400">Nama Orang Tua</span>
                                        <input type="text" name="nama_ortu" value="{{ old('nama_ortu') }}"
                                            class="block w-full mt-1 text-sm form-input dark:bg-gray-700 dark:text-gray-300 @error('nama_ortu') border-red-500 @enderror" />
                                        @error('nama_ortu')
                                            <span class="text-xs text-red-500">{{ $message }}</span>
                                        @enderror
                                    </label>
                                    <label class="block text-sm mb-4">
                                        <span class="text-gray-700 dark:text-gray-400">No HP <span class="text-gray-400">(opsional)</span></span>
                                        <input type="text" name="no_hp_ortu" value="{{ old('no_hp_ortu') }}"
                                            class="block w-full mt-1 text-sm form-input dark:bg-gray-700 dark:text-gray-300" />
                                    </label>
                                </div>
                            </div>

                            <div id="ortu_lama_info" class="hidden mb-4 px-3 py-2 text-xs text-green-700 bg-green-50 rounded-lg dark:bg-green-900/30 dark:text-green-300">
                                Email sudah terdaftar atas nama: <strong id="ortu_lama_nama"></strong>.
                                Cukup isi <strong>Hubungan</strong> saja.
                            </div>

                            <div id="ortu_hubungan_wrapper" class="hidden">
                                <label class="block text-sm mb-0">
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

                        <div class="p-6 bg-white rounded-lg shadow-xs dark:shadow-none dark:border dark:border-gray-700 dark:bg-gray-800">
                            <h3 class="mb-3 text-lg font-semibold text-gray-700 dark:text-gray-200">
                                Daftarkan ke Kelas <span class="text-sm font-normal text-gray-400">(opsional)</span>
                            </h3>
                            <hr class="mb-4 border-gray-200 dark:border-gray-700">

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
                    </div>
                </div>

                <button type="submit"
                    class="w-full px-4 py-2 mt-6 text-sm font-medium text-white bg-purple-600 rounded-lg hover:bg-purple-700">
                    Simpan
                </button>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        const nisnInput = document.getElementById('nisn');
        const nisnLoading = document.getElementById('nisn-loading');
        const nisnResult = document.getElementById('nisn-result');
        const resultSame = document.getElementById('result-same');
        const resultDaftarUlang = document.getElementById('result-daftar-ulang');
        const resultPindah = document.getElementById('result-pindah');
        const resultBlocked = document.getElementById('result-blocked');
        const stepForm = document.getElementById('step-form');
        let debounceTimer;

        function resetResults() {
            nisnResult.classList.add('hidden');
            resultSame.classList.add('hidden');
            resultDaftarUlang.classList.add('hidden');
            resultPindah.classList.add('hidden');
            resultBlocked.classList.add('hidden');
            stepForm.classList.add('hidden');
        }

        nisnInput.addEventListener('input', function () {
            clearTimeout(debounceTimer);
            const nisn = this.value.trim();

            if (!nisn) {
                resetResults();
                return;
            }

            nisnLoading.classList.remove('hidden');

            debounceTimer = setTimeout(() => {
                fetch('{{ route("admin.siswa.cek-nisn") }}?nisn=' + encodeURIComponent(nisn))
                    .then(res => res.json())
                    .then(data => {
                        nisnLoading.classList.add('hidden');
                        resetResults();

                        if (!data.found) {
                            stepForm.classList.remove('hidden');
                            document.getElementById('form-nisn').value = nisn;
                            return;
                        }

                        nisnResult.classList.remove('hidden');

                        if (data.same_instansi) {
                            document.getElementById('same-active').classList.add('hidden');
                            document.getElementById('same-keluar').classList.add('hidden');
                            document.getElementById('same-pindah').classList.add('hidden');

                            if (data.status === 'Keluar') {
                                document.getElementById('same-keluar-nama').textContent = data.nama;
                                document.getElementById('same-keluar').classList.remove('hidden');
                            } else if (data.status === 'Pindah') {
                                document.getElementById('same-pindah-nama').textContent = data.nama;
                                document.getElementById('same-pindah').classList.remove('hidden');
                            } else {
                                document.getElementById('same-nama').textContent = data.nama;
                                document.getElementById('same-active').classList.remove('hidden');
                            }
                            nisnResult.classList.remove('hidden');
                            resultSame.classList.remove('hidden');
                            return;
                        }

                        if (data.blocked) {
                            document.getElementById('blocked-nama').textContent = data.nama;
                            document.getElementById('blocked-message').textContent = data.message;
                            nisnResult.classList.remove('hidden');
                            resultBlocked.classList.remove('hidden');
                            return;
                        }

                        if (data.action === 'daftar-ulang') {
                            document.getElementById('du-nama').textContent = data.nama;
                            document.getElementById('du-status').textContent = data.status === 'Keluar' ? 'ditandai Keluar' : 'lulus (Alumni)';
                            document.getElementById('du-instansi').textContent = data.instansi;
                            document.getElementById('du-link').href = '{{ route("admin.siswa.daftar-ulang", "__ID__") }}'.replace('__ID__', data.siswa_id);
                            resultDaftarUlang.classList.remove('hidden');
                            return;
                        }

                        if (data.action === 'pindah') {
                            document.getElementById('pindah-nama').textContent = data.nama;
                            document.getElementById('pindah-instansi').textContent = data.instansi;
                            document.getElementById('pindah-link').href = '{{ route("admin.siswa.pindah.form-masuk") }}?nisn=' + encodeURIComponent(nisn);
                            resultPindah.classList.remove('hidden');
                        }
                    });
            }, 500);
        });

        const emailInput = document.getElementById('email_ortu');
        const ortuBaruFields = document.getElementById('ortu_baru_fields');
        const ortuLamaInfo = document.getElementById('ortu_lama_info');
        const ortuLamaNama = document.getElementById('ortu_lama_nama');
        const ortuHubunganWrapper = document.getElementById('ortu_hubungan_wrapper');
        let debounceOrtuTimer;

        emailInput.addEventListener('input', function () {
            clearTimeout(debounceOrtuTimer);
            const email = this.value.trim();

            if (!email || !email.includes('@')) {
                ortuBaruFields.classList.add('hidden');
                ortuLamaInfo.classList.add('hidden');
                ortuHubunganWrapper.classList.add('hidden');
                return;
            }

            debounceOrtuTimer = setTimeout(() => {
                fetch('{{ route("admin.siswa.cek-email-ortu") }}?email=' + encodeURIComponent(email))
                    .then(res => res.json())
                    .then(data => {
                        if (data.exists) {
                            ortuBaruFields.classList.add('hidden');
                            ortuLamaInfo.classList.remove('hidden');
                            ortuLamaNama.textContent = data.nama;
                        } else {
                            ortuBaruFields.classList.remove('hidden');
                            ortuLamaInfo.classList.add('hidden');
                        }
                        ortuHubunganWrapper.classList.remove('hidden');
                    });
            }, 500);
        });
    </script>
    @endpush
</x-layouts.admin>
