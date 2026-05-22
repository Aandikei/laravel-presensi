<x-layouts.admin>
    <x-slot:title>Tambah Kelas</x-slot:title>

    <div class="container px-6 mx-auto">
        <div class="my-6">
            <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
                Tambah Kelas
            </h2>
            <a href="{{ route('admin.kelas.index') }}"
                class="text-sm text-purple-600 hover:underline dark:text-purple-400">
                ← Kembali
            </a>
        </div>

        <div class="max-w-lg p-6 bg-white rounded-lg shadow-xs dark:bg-gray-800">
            <form method="POST" action="{{ route('admin.kelas.store') }}">
                @csrf

                <label class="block text-sm mb-4">
                    <span class="text-gray-700 dark:text-gray-400">Nama Kelas</span>
                    <input type="text" name="nama_kelas" value="{{ old('nama_kelas') }}"
                        placeholder="contoh: X IPA 1"
                        class="block w-full mt-1 text-sm form-input dark:bg-gray-700 dark:text-gray-300 @error('nama_kelas') border-red-500 @enderror" />
                    @error('nama_kelas')
                        <span class="text-xs text-red-500">{{ $message }}</span>
                    @enderror
                </label>

                <label class="block text-sm mb-4">
                    <span class="text-gray-700 dark:text-gray-400">Tingkat</span>
                    <input type="number" name="tingkat" value="{{ old('tingkat') }}"
                        placeholder="contoh: 10"
                        min="1" max="12"
                        class="block w-full mt-1 text-sm form-input dark:bg-gray-700 dark:text-gray-300 @error('tingkat') border-red-500 @enderror" />
                    @error('tingkat')
                        <span class="text-xs text-red-500">{{ $message }}</span>
                    @enderror
                </label>

                <label class="block text-sm mb-4">
                    <span class="text-gray-700 dark:text-gray-400">Tahun Ajaran</span>
                    <select name="tahun_id"
                        class="block w-full mt-1 text-sm form-select dark:bg-gray-700 dark:text-gray-300 @error('tahun_id') border-red-500 @enderror">
                        <option value="">-- Pilih Tahun Ajaran --</option>
                        @foreach($tahunAjaran as $tahun)
                            <option value="{{ $tahun->id_tahun }}"
                                {{ old('tahun_id') == $tahun->id_tahun ? 'selected' : '' }}
                                {{ $tahun->is_aktif ? 'selected' : '' }}>
                                {{ $tahun->nama_tahun }} - {{ $tahun->semester }}
                                {{ $tahun->is_aktif ? '(Aktif)' : '' }}
                            </option>
                        @endforeach
                    </select>
                    @error('tahun_id')
                        <span class="text-xs text-red-500">{{ $message }}</span>
                    @enderror
                </label>

                <label class="block text-sm mb-4">
                    <span class="text-gray-700 dark:text-gray-400">Wali Kelas <span class="text-gray-400">(opsional)</span></span>
                    <select name="guru_wali_id"
                        class="block w-full mt-1 text-sm form-select dark:bg-gray-700 dark:text-gray-300">
                        <option value="">-- Pilih Wali Kelas --</option>
                        @foreach($guru as $g)
                            <option value="{{ $g->id_guru }}"
                                {{ old('guru_wali_id') == $g->id_guru ? 'selected' : '' }}>
                                {{ $g->nama_guru }}
                            </option>
                        @endforeach
                    </select>
                </label>

                <button type="submit"
                    class="w-full px-4 py-2 text-sm font-medium text-white bg-purple-600 rounded-lg hover:bg-purple-700">
                    Simpan
                </button>
            </form>
        </div>
    </div>
</x-layouts.admin>