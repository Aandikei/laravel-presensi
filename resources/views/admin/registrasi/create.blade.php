<x-layouts.admin>
    <x-slot:title>Daftarkan Siswa</x-slot:title>

    <div class="container px-6 mx-auto">
        <div class="my-6">
            <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
                Daftarkan Siswa ke Kelas
            </h2>
            <a href="{{ route('admin.registrasi.index') }}"
                class="text-sm text-purple-600 hover:underline dark:text-purple-400">← Kembali</a>
        </div>

        @if ($tahunAktif)
            <div class="mb-4 px-4 py-3 text-sm text-blue-700 bg-blue-100 rounded-lg dark:bg-blue-800 dark:text-blue-200">
                Tahun ajaran aktif: <strong>{{ $tahunAktif->nama_tahun }} - {{ $tahunAktif->semester }}</strong>
            </div>
        @else
            <div
                class="mb-4 px-4 py-3 text-sm text-yellow-700 bg-yellow-100 rounded-lg dark:bg-yellow-800 dark:text-yellow-200">
                Belum ada tahun ajaran aktif! Aktifkan tahun ajaran terlebih dahulu.
            </div>
        @endif

        <div class="p-6 bg-white rounded-lg shadow-xs dark:bg-gray-800">
            <form method="POST" action="{{ route('admin.registrasi.store') }}">
                @csrf

                <div class="grid gap-4 md:grid-cols-2 mb-6">
                    {{-- Tahun Ajaran --}}
                    <label class="block text-sm">
                        <span class="text-gray-700 dark:text-gray-400">Tahun Ajaran</span>
                        <select name="tahun_id" id="tahun_id" onchange="loadKelasByTahun(this.value)"
                            class="block w-full mt-1 text-sm form-select dark:bg-gray-700 dark:text-gray-300 @error('tahun_id') border-red-500 @enderror">
                            <option value="">-- Pilih Tahun Ajaran --</option>
                            @foreach ($tahunAjaran as $tahun)
                                <option value="{{ $tahun->id_tahun }}" {{ $tahun->is_aktif ? 'selected' : '' }}>
                                    {{ $tahun->nama_tahun }} - {{ $tahun->semester }}
                                    {{ $tahun->is_aktif ? '(Aktif)' : '' }}
                                </option>
                            @endforeach
                        </select>
                        @error('tahun_id')
                            <span class="text-xs text-red-500">{{ $message }}</span>
                        @enderror
                    </label>

                    {{-- Kelas (dynamic) --}}
                    <label class="block text-sm">
                        <span class="text-gray-700 dark:text-gray-400">Kelas Tujuan</span>
                        <select name="kelas_id" id="kelas_id"
                            class="block w-full mt-1 text-sm form-select dark:bg-gray-700 dark:text-gray-300 @error('kelas_id') border-red-500 @enderror">
                            <option value="">-- Pilih tahun ajaran dulu --</option>
                        </select>
                        @error('kelas_id')
                            <span class="text-xs text-red-500">{{ $message }}</span>
                        @enderror
                    </label>
                </div>

                {{-- Pilih Siswa --}}
                <div class="mb-4">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm text-gray-700 dark:text-gray-400 font-medium">
                            Pilih Siswa
                            <span class="text-xs text-gray-400">(belum terdaftar di tahun ajaran aktif)</span>
                        </span>
                        <div class="flex gap-2">
                            <button type="button" onclick="selectAll()"
                                class="text-xs px-3 py-1 bg-purple-100 text-purple-700 rounded hover:bg-purple-200">
                                Pilih Semua
                            </button>
                            <button type="button" onclick="deselectAll()"
                                class="text-xs px-3 py-1 bg-gray-100 text-gray-700 rounded hover:bg-gray-200">
                                Batal Semua
                            </button>
                        </div>
                    </div>

                    @if ($siswa->isEmpty())
                        <div
                            class="text-center py-8 text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            Semua siswa sudah terdaftar di tahun ajaran aktif.
                        </div>
                    @else
                        {{-- Search filter --}}
                        <input type="text" id="search-siswa" placeholder="Cari nama atau NISN..."
                            class="block w-full mb-3 text-sm form-input dark:bg-gray-700 dark:text-gray-300"
                            onkeyup="filterSiswa(this.value)" />

                        <div id="siswa-list"
                            class="max-h-72 overflow-y-auto border border-gray-200 dark:border-gray-700 rounded-lg divide-y divide-gray-100 dark:divide-gray-700">
                            @foreach ($siswa as $s)
                                <label
                                    class="siswa-item flex items-center gap-3 px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer"
                                    data-name="{{ strtolower($s->nama_siswa) }}" data-nisn="{{ $s->nisn }}">
                                    <input type="checkbox" name="siswa_id[]" value="{{ $s->id_siswa }}"
                                        class="rounded border-gray-300 text-purple-600 focus:ring-purple-500" />
                                    <div>
                                        <p class="text-sm font-medium text-gray-700 dark:text-gray-200">
                                            {{ $s->nama_siswa }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">NISN: {{ $s->nisn }}
                                        </p>
                                    </div>
                                </label>
                            @endforeach
                        </div>

                        @error('siswa_id')
                            <span class="text-xs text-red-500">{{ $message }}</span>
                        @enderror
                    @endif
                </div>

                <button type="submit"
                    class="w-full px-4 py-2 text-sm font-medium text-white bg-purple-600 rounded-lg hover:bg-purple-700"
                    {{ $siswa->isEmpty() ? 'disabled' : '' }}>
                    Daftarkan Siswa
                </button>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            function selectAll() {
                document.querySelectorAll('#siswa-list input[type="checkbox"]').forEach(cb => cb.checked = true);
            }

            function deselectAll() {
                document.querySelectorAll('#siswa-list input[type="checkbox"]').forEach(cb => cb.checked = false);
            }

            function filterSiswa(query) {
                const q = query.toLowerCase();
                document.querySelectorAll('.siswa-item').forEach(item => {
                    const name = item.dataset.name;
                    const nisn = item.dataset.nisn;
                    item.style.display = (name.includes(q) || nisn.includes(q)) ? '' : 'none';
                });
            }
            // Auto load kelas sesuai tahun ajaran aktif saat halaman load
            document.addEventListener('DOMContentLoaded', function() {
                const tahunSelect = document.getElementById('tahun_id');
                if (tahunSelect.value) {
                    loadKelasByTahun(tahunSelect.value);
                }
            });

            function loadKelasByTahun(tahunId) {
                const kelasSelect = document.getElementById('kelas_id');
                kelasSelect.innerHTML = '<option value="">Loading...</option>';

                if (!tahunId) {
                    kelasSelect.innerHTML = '<option value="">-- Pilih tahun ajaran dulu --</option>';
                    return;
                }

                fetch(`/admin/kelas-by-tahun/${tahunId}`)
                    .then(res => res.json())
                    .then(data => {
                        kelasSelect.innerHTML = '<option value="">-- Pilih Kelas --</option>';
                        if (data.length === 0) {
                            kelasSelect.innerHTML = '<option value="">Belum ada kelas di tahun ajaran ini</option>';
                            return;
                        }
                        data.forEach(item => {
                            kelasSelect.innerHTML += `<option value="${item.id_kelas}">${item.nama_kelas}</option>`;
                        });
                    });
            }
        </script>
    @endpush
</x-layouts.admin>
