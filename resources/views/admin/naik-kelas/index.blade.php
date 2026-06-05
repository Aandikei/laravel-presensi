<x-layouts.admin>
    <x-slot:title>Proses Naik Kelas</x-slot:title>

    <div class="container px-6 mx-auto">
        <div class="my-6">
            <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">Proses Naik Kelas</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                Pindahkan siswa dari tahun ajaran lama ke tahun ajaran baru
            </p>
        </div>

        @if (session('success'))
            <div
                class="px-4 py-3 mb-4 text-sm text-green-700 bg-green-100 rounded-lg dark:bg-green-800 dark:text-green-200">
                {{ session('success') }}
            </div>
        @endif

        <div class="max-w-lg p-6 bg-white rounded-lg shadow-xs dark:bg-gray-800">
            <div
                class="mb-4 px-3 py-2 text-xs text-blue-700 bg-blue-50 rounded-lg dark:bg-blue-900/30 dark:text-blue-300">
                Pastikan sudah membuat <strong>tahun ajaran baru</strong> sebelum melanjutkan. Kelas bersifat permanen,
                sehingga tidak perlu membuat kelas baru tiap semester.
            </div>

            {{-- Naik Kelas --}}
            <form method="GET" action="{{ route('admin.naik-kelas.preview') }}">

                <label class="block text-sm mb-3">
                    <span class="text-gray-700 dark:text-gray-400">Tahun Ajaran Asal</span>
                    <select name="tahun_asal_id" id="naik-asal" onchange="updateTujuan('naik')"
                        class="block w-full mt-1 text-sm form-select dark:bg-gray-700 dark:text-gray-300">
                        <option value="">-- Pilih --</option>
                        @foreach ($tahunAjaran as $tahun)
                            <option value="{{ $tahun->id_tahun }}" data-index="{{ $loop->index }}">
                                {{ $tahun->nama_tahun }} - {{ $tahun->semester }}
                                {{ $tahun->is_aktif ? '(Aktif)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </label>

                <label class="block text-sm mb-4">
                    <span class="text-gray-700 dark:text-gray-400">Tahun Ajaran Tujuan</span>
                    <select name="tahun_tujuan_id" id="naik-tujuan"
                        class="block w-full mt-1 text-sm form-select dark:bg-gray-700 dark:text-gray-300">
                        <option value="">-- Pilih tahun asal dulu --</option>
                    </select>
                </label>

                <button type="submit"
                    class="w-full px-4 py-2 text-sm font-medium text-white bg-purple-600 rounded-lg hover:bg-purple-700">
                    Lihat Preview →
                </button>
            </form>

            {{-- Ganti Semester --}}
            <form method="POST" action="{{ route('admin.naik-kelas.salin-semester') }}">
                @csrf

                <label class="block text-sm mb-3">
                    <span class="text-gray-700 dark:text-gray-400">Semester Asal</span>
                    <select name="tahun_asal_id" id="semester-asal" onchange="updateTujuan('semester')"
                        class="block w-full mt-1 text-sm form-select dark:bg-gray-700 dark:text-gray-300">
                        <option value="">-- Pilih --</option>
                        @foreach ($tahunAjaran as $tahun)
                            <option value="{{ $tahun->id_tahun }}" data-index="{{ $loop->index }}">
                                {{ $tahun->nama_tahun }} - {{ $tahun->semester }}
                                {{ $tahun->is_aktif ? '(Aktif)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </label>

                <label class="block text-sm mb-4">
                    <span class="text-gray-700 dark:text-gray-400">Semester Tujuan</span>
                    <select name="tahun_tujuan_id" id="semester-tujuan"
                        class="block w-full mt-1 text-sm form-select dark:bg-gray-700 dark:text-gray-300">
                        <option value="">-- Pilih semester asal dulu --</option>
                    </select>
                </label>

                <button type="submit"
                    onclick="return confirm('Salin semua siswa ke semester baru dengan kelas yang sama?')"
                    class="w-full px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                    Salin ke Semester Baru
                </button>
            </form>
        </div>
    </div>
    @push('scripts')
        <script>
            // Data tahun ajaran dari server (urut dari terbaru)
            const tahunAjaranData = @json(
                $tahunAjaran->map(fn($t) => [
                            'id' => $t->id_tahun,
                            'label' => $t->nama_tahun . ' - ' . $t->semester . ($t->is_aktif ? ' (Aktif)' : ''),
                            'is_aktif' => $t->is_aktif])->values()->toArray());

            function updateTujuan(prefix) {
                const asalSelect = document.getElementById(`${prefix}-asal`);
                const tujuanSelect = document.getElementById(`${prefix}-tujuan`);
                const asalId = parseInt(asalSelect.value);

                tujuanSelect.innerHTML = '<option value="">-- Pilih --</option>';

                if (!asalId) return;

                // Tampilkan hanya tahun ajaran yang id-nya LEBIH BESAR dari asal
                // (karena diurut DESC, index lebih kecil = id lebih besar = lebih baru)
                const asalIndex = tahunAjaranData.findIndex(t => t.id === asalId);

                tahunAjaranData.forEach((tahun, index) => {
                    // Hanya tampilkan yang lebih baru (index lebih kecil dari asal)
                    if (index < asalIndex && tahun.id !== asalId) {
                        const opt = document.createElement('option');
                        opt.value = tahun.id;
                        opt.textContent = tahun.label;
                        tujuanSelect.appendChild(opt);
                    }
                });

                if (tujuanSelect.options.length === 1) {
                    tujuanSelect.innerHTML = '<option value="">Tidak ada tahun ajaran yang lebih baru</option>';
                }
            }
        </script>
    @endpush
</x-layouts.admin>
