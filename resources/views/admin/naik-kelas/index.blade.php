<x-layouts.admin>
    <x-slot:title>Proses Naik Kelas</x-slot:title>

    <div class="container px-6 mx-auto">
        <div class="my-6">
            <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">Proses Naik Kelas</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                Pindahkan siswa dari tahun ajaran lama ke tahun ajaran baru
            </p>
        </div>

        <div
            class="mb-4 px-3 py-2 text-xs text-blue-700 bg-blue-50 rounded-lg dark:bg-blue-900/30 dark:text-blue-300">
            Pastikan sudah membuat <strong>tahun ajaran baru</strong> sebelum melanjutkan. Kelas bersifat permanen,
            sehingga tidak perlu membuat kelas baru tiap semester.
        </div>

        <div class="grid gap-6 md:grid-cols-2">

            {{-- Naik Kelas --}}
            <div class="p-6 bg-white rounded-lg shadow-xs dark:shadow-none dark:border dark:border-gray-700 dark:bg-gray-800">
                <h3 class="mb-1 text-lg font-semibold text-gray-700 dark:text-gray-200">Naik Kelas</h3>
                <p class="mb-4 text-sm text-gray-500 dark:text-gray-400">
                    Pindahkan siswa ke tingkat kelas berikutnya
                </p>
                <form method="GET" action="{{ route('admin.naik-kelas.preview') }}">

                    <label class="block text-sm mb-3">
                        <span class="text-gray-700 dark:text-gray-400">Tahun Ajaran Asal</span>
                        <select name="tahun_asal_id" id="naik-asal" onchange="updateTujuan('naik')"
                            class="block w-full mt-1 text-sm dark:bg-gray-700 dark:text-gray-300">
                            @foreach ($tahunAjaran as $tahun)
                                <option value="{{ $tahun->id_tahun }}" {{ $tahun->is_aktif ? 'selected' : '' }}>
                                    {{ $tahun->nama_tahun }} - {{ $tahun->semester }}
                                    {{ $tahun->is_aktif ? '(Aktif)' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </label>

                    <label class="block text-sm mb-4">
                        <span class="text-gray-700 dark:text-gray-400">Tahun Ajaran Tujuan</span>
                        <select name="tahun_tujuan_id" id="naik-tujuan"
                            class="block w-full mt-1 text-sm dark:bg-gray-700 dark:text-gray-300">
                            <option value="">-- Pilih tahun asal dulu --</option>
                        </select>
                    </label>

                    <button type="submit" id="btn-naik"
                        class="w-full px-4 py-2 text-sm font-medium text-white bg-purple-600 rounded-lg hover:bg-purple-700 disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                        Lihat Preview →
                    </button>
                </form>
            </div>

            {{-- Ganti Semester --}}
            <div class="p-6 bg-white rounded-lg shadow-xs dark:shadow-none dark:border dark:border-gray-700 dark:bg-gray-800">
                <h3 class="mb-1 text-lg font-semibold text-gray-700 dark:text-gray-200">Ganti Semester</h3>
                <p class="mb-4 text-sm text-gray-500 dark:text-gray-400">
                    Salin siswa ke semester baru dengan kelas yang sama
                </p>
                <form method="POST" action="{{ route('admin.naik-kelas.salin-semester') }}">
                    @csrf

                    <label class="block text-sm mb-3">
                        <span class="text-gray-700 dark:text-gray-400">Semester Asal</span>
                        <select name="tahun_asal_id" id="semester-asal" onchange="updateTujuan('semester')"
                            class="block w-full mt-1 text-sm dark:bg-gray-700 dark:text-gray-300">
                            @foreach ($tahunAjaran as $tahun)
                                <option value="{{ $tahun->id_tahun }}" {{ $tahun->is_aktif ? 'selected' : '' }}>
                                    {{ $tahun->nama_tahun }} - {{ $tahun->semester }}
                                    {{ $tahun->is_aktif ? '(Aktif)' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </label>

                    <label class="block text-sm mb-4">
                        <span class="text-gray-700 dark:text-gray-400">Semester Tujuan</span>
                        <select name="tahun_tujuan_id" id="semester-tujuan"
                            class="block w-full mt-1 text-sm dark:bg-gray-700 dark:text-gray-300">
                            <option value="">-- Pilih semester asal dulu --</option>
                        </select>
                    </label>

                    <button type="submit" id="btn-semester"
                        onclick="return confirm('Salin semua siswa ke semester baru dengan kelas yang sama?')"
                        class="w-full px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                        Salin ke Semester Baru
                    </button>
                </form>
            </div>

        </div>
    </div>
    @push('scripts')
        <script>
            const tahunAjaranData = @json($tahunAjaranData);

            function updateTujuan(prefix) {
                const asalSelect = document.getElementById(`${prefix}-asal`);
                const tujuanSelect = document.getElementById(`${prefix}-tujuan`);
                const btn = document.getElementById(`btn-${prefix}`);
                const asalId = parseInt(asalSelect.value);

                tujuanSelect.innerHTML = '<option value="">-- Pilih --</option>';

                const enableBtn = () => btn.disabled = !(asalSelect.value && tujuanSelect.value);

                if (!asalId) { enableBtn(); return; }

                const asalData = tahunAjaranData.find(t => t.id === asalId);
                if (!asalData) { enableBtn(); return; }

                if (prefix === 'naik') {
                    if (asalData.semester !== 'Genap') {
                        tujuanSelect.innerHTML = '<option value="">Naik kelas hanya dari semester Genap</option>';
                        enableBtn(); return;
                    }
                    const tahunParts = asalData.nama_tahun.split('/');
                    const nextTahun = `${parseInt(tahunParts[0]) + 1}/${parseInt(tahunParts[1]) + 1}`;

                    const tujuan = tahunAjaranData.find(t =>
                        t.nama_tahun === nextTahun && t.semester === 'Ganjil'
                    );

                    if (tujuan) {
                        const opt = document.createElement('option');
                        opt.value = tujuan.id;
                        opt.textContent = tujuan.label;
                        opt.selected = true;
                        tujuanSelect.appendChild(opt);
                    } else {
                        tujuanSelect.innerHTML = '<option value="">Tidak ada tahun ajaran berikutnya</option>';
                    }
                } else if (prefix === 'semester') {
                    if (asalData.semester !== 'Ganjil') {
                        tujuanSelect.innerHTML = '<option value="">Ganti semester hanya dari semester Ganjil</option>';
                        enableBtn(); return;
                    }

                    const tujuan = tahunAjaranData.find(t =>
                        t.nama_tahun === asalData.nama_tahun && t.semester === 'Genap'
                    );

                    if (tujuan) {
                        const opt = document.createElement('option');
                        opt.value = tujuan.id;
                        opt.textContent = tujuan.label;
                        opt.selected = true;
                        tujuanSelect.appendChild(opt);
                    } else {
                        tujuanSelect.innerHTML = '<option value="">Tidak ada semester Genap untuk tahun ini</option>';
                    }
                }
                enableBtn();
            }

            document.addEventListener('DOMContentLoaded', function() {
                updateTujuan('naik');
                updateTujuan('semester');
            });
        </script>
    @endpush
</x-layouts.admin>
