<x-layouts.admin>
    <x-slot:title>Tambah Jadwal</x-slot:title>

    <div class="container px-6 mx-auto">
        <div class="my-6">
            <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">Tambah Jadwal</h2>
            <a href="{{ route('admin.jadwal.index') }}"
                class="text-sm text-purple-600 hover:underline dark:text-purple-400">← Kembali</a>
        </div>

        <div class="max-w-lg p-6 bg-white rounded-lg shadow-xs dark:bg-gray-800">
            <form method="POST" action="{{ route('admin.jadwal.store') }}">
                @csrf

                {{-- Pilih Kelas --}}
                <label class="block text-sm mb-4">
                    <span class="text-gray-700 dark:text-gray-400">Kelas</span>
                    <select id="kelas_id" onchange="loadKurikulum(this.value)"
                        class="block w-full mt-1 text-sm form-select dark:bg-gray-700 dark:text-gray-300">
                        <option value="">-- Pilih Kelas --</option>
                        @foreach($kelas as $k)
                            <option value="{{ $k->id_kelas }}">
                                {{ $k->nama_kelas }} ({{ $k->tahunAjaran->nama_tahun }} - {{ $k->tahunAjaran->semester }})
                            </option>
                        @endforeach
                    </select>
                </label>

                {{-- Kurikulum (dynamic) --}}
                <label class="block text-sm mb-4">
                    <span class="text-gray-700 dark:text-gray-400">Mata Pelajaran & Guru</span>
                    <select name="kurikulum_id" id="kurikulum_id"
                        class="block w-full mt-1 text-sm form-select dark:bg-gray-700 dark:text-gray-300 @error('kurikulum_id') border-red-500 @enderror">
                        <option value="">-- Pilih kelas dulu --</option>
                    </select>
                    @error('kurikulum_id')
                        <span class="text-xs text-red-500">{{ $message }}</span>
                    @enderror
                </label>

                {{-- Hari --}}
                <label class="block text-sm mb-4">
                    <span class="text-gray-700 dark:text-gray-400">Hari</span>
                    <select name="hari"
                        class="block w-full mt-1 text-sm form-select dark:bg-gray-700 dark:text-gray-300 @error('hari') border-red-500 @enderror">
                        <option value="">-- Pilih Hari --</option>
                        @foreach(['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'] as $hari)
                            <option value="{{ $hari }}" {{ old('hari') == $hari ? 'selected' : '' }}>{{ $hari }}</option>
                        @endforeach
                    </select>
                    @error('hari')
                        <span class="text-xs text-red-500">{{ $message }}</span>
                    @enderror
                </label>

                {{-- Jam Mulai --}}
                <label class="block text-sm mb-4">
                    <span class="text-gray-700 dark:text-gray-400">Jam Mulai</span>
                    <input type="time" name="jam_mulai" value="{{ old('jam_mulai') }}"
                        class="block w-full mt-1 text-sm form-input dark:bg-gray-700 dark:text-gray-300 @error('jam_mulai') border-red-500 @enderror" />
                    @error('jam_mulai')
                        <span class="text-xs text-red-500">{{ $message }}</span>
                    @enderror
                </label>

                {{-- Jam Selesai --}}
                <label class="block text-sm mb-4">
                    <span class="text-gray-700 dark:text-gray-400">Jam Selesai</span>
                    <input type="time" name="jam_selesai" value="{{ old('jam_selesai') }}"
                        class="block w-full mt-1 text-sm form-input dark:bg-gray-700 dark:text-gray-300 @error('jam_selesai') border-red-500 @enderror" />
                    @error('jam_selesai')
                        <span class="text-xs text-red-500">{{ $message }}</span>
                    @enderror
                </label>

                <button type="submit"
                    class="w-full px-4 py-2 text-sm font-medium text-white bg-purple-600 rounded-lg hover:bg-purple-700">
                    Simpan
                </button>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        function loadKurikulum(kelasId) {
            const select = document.getElementById('kurikulum_id');
            select.innerHTML = '<option value="">Loading...</option>';

            if (!kelasId) {
                select.innerHTML = '<option value="">-- Pilih kelas dulu --</option>';
                return;
            }

            fetch(`/admin/kurikulum-by-kelas/${kelasId}`)
                .then(res => res.json())
                .then(data => {
                    select.innerHTML = '<option value="">-- Pilih Mapel & Guru --</option>';
                    data.forEach(item => {
                        select.innerHTML += `<option value="${item.id_kurikulum}">${item.mata_pelajaran} - ${item.guru}</option>`;
                    });
                });
        }
    </script>
    @endpush
</x-layouts.admin>