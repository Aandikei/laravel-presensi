<x-layouts.admin>
    <x-slot:title>Tambah Kurikulum</x-slot:title>

    <div class="container px-6 mx-auto">
        <div class="my-6">
            <x-breadcrumb :items="[
                ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
                ['label' => 'Kurikulum', 'url' => route('admin.kurikulum.index')],
                ['label' => 'Tambah'],
            ]" />
            <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
                Tambah Kurikulum Kelas
            </h2>
        </div>
        <div class="max-w-lg p-6 bg-white rounded-lg shadow-xs dark:shadow-none dark:border dark:border-gray-700 dark:bg-gray-800">
            <form method="POST" action="{{ route('admin.kurikulum.store') }}">
                @csrf

                <label class="block text-sm mb-4">
                    <span class="text-gray-700 dark:text-gray-400">Kelas</span>
                    <select name="kelas_id" id="kelas_id"
                        class="block w-full mt-1 text-sm dark:bg-gray-700 dark:text-gray-300 @error('kelas_id') border-red-500 @enderror">
                        <option value="">-- Pilih Kelas --</option>
                        @foreach($kelas as $k)
                            <option value="{{ $k->id_kelas }}"
                                {{ old('kelas_id') == $k->id_kelas ? 'selected' : '' }}>
                                {{ $k->nama_kelas }} (Tingkat {{ $k->tingkat }})
                            </option>
                        @endforeach
                    </select>
                    @error('kelas_id')
                        <span class="text-xs text-red-500">{{ $message }}</span>
                    @enderror
                </label>

                <label class="block text-sm mb-4">
                    <span class="text-gray-700 dark:text-gray-400">Mata Pelajaran</span>
                    <select name="mapel_id"
                        class="block w-full mt-1 text-sm dark:bg-gray-700 dark:text-gray-300 @error('mapel_id') border-red-500 @enderror">
                        <option value="">-- Pilih Mata Pelajaran --</option>
                        @foreach($mapel as $m)
                            <option value="{{ $m->id_mapel }}"
                                {{ old('mapel_id') == $m->id_mapel ? 'selected' : '' }}>
                                {{ $m->nama_mapel }}
                                @if($m->kode_mapel) ({{ $m->kode_mapel }}) @endif
                            </option>
                        @endforeach
                    </select>
                    @error('mapel_id')
                        <span class="text-xs text-red-500">{{ $message }}</span>
                    @enderror
                </label>

                <label class="block text-sm mb-4">
                    <span class="text-gray-700 dark:text-gray-400">Guru Pengampu</span>
                    <select name="guru_id"
                        class="block w-full mt-1 text-sm dark:bg-gray-700 dark:text-gray-300 @error('guru_id') border-red-500 @enderror">
                        <option value="">-- Pilih Guru --</option>
                        @foreach($guru as $g)
                            <option value="{{ $g->id_guru }}"
                                {{ old('guru_id') == $g->id_guru ? 'selected' : '' }}>
                                {{ $g->nama_guru }}
                            </option>
                        @endforeach
                    </select>
                    @error('guru_id')
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
</x-layouts.admin>