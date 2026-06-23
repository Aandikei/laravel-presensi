<x-layouts.admin>
    <x-slot:title>Edit Kurikulum</x-slot:title>

    <div class="container px-6 mx-auto">
        <div class="my-6">
            <x-breadcrumb :items="[
                ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
                ['label' => 'Kurikulum', 'url' => route('admin.kurikulum.index')],
                ['label' => 'Edit'],
            ]" />
            <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
                Edit Kurikulum Kelas
            </h2>
        </div>
        <div class="max-w-lg p-6 bg-white rounded-lg shadow-xs dark:shadow-none dark:border dark:border-gray-700 dark:bg-gray-800">
            <form method="POST" action="{{ route('admin.kurikulum.update', $kurikulum) }}">
                @csrf
                @method('PUT')

                <label class="block text-sm mb-4">
                    <span class="text-gray-700 dark:text-gray-400">Kelas</span>
                    <select name="kelas_id"
                        class="block w-full mt-1 text-sm dark:bg-gray-700 dark:text-gray-300">
                        @foreach($kelas as $k)
                            <option value="{{ $k->id_kelas }}"
                                {{ old('kelas_id', $kurikulum->kelas_id) == $k->id_kelas ? 'selected' : '' }}>
                                {{ $k->nama_kelas }} (Tingkat {{ $k->tingkat }})
                            </option>
                        @endforeach
                    </select>
                </label>

                <label class="block text-sm mb-4">
                    <span class="text-gray-700 dark:text-gray-400">Mata Pelajaran</span>
                    <select name="mapel_id"
                        class="block w-full mt-1 text-sm dark:bg-gray-700 dark:text-gray-300 @error('mapel_id') border-red-500 @enderror">
                        @foreach($mapel as $m)
                            <option value="{{ $m->id_mapel }}"
                                {{ old('mapel_id', $kurikulum->mapel_id) == $m->id_mapel ? 'selected' : '' }}>
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
                        class="block w-full mt-1 text-sm dark:bg-gray-700 dark:text-gray-300">
                        @foreach($guru as $g)
                            <option value="{{ $g->id_guru }}"
                                {{ old('guru_id', $kurikulum->guru_id) == $g->id_guru ? 'selected' : '' }}>
                                {{ $g->nama_guru }}
                            </option>
                        @endforeach
                    </select>
                </label>

                <button type="submit"
                    class="w-full px-4 py-2 text-sm font-medium text-white bg-purple-600 rounded-lg hover:bg-purple-700">
                    Update
                </button>
            </form>
        </div>
    </div>
</x-layouts.admin>