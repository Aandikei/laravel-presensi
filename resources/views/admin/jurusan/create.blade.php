<x-layouts.admin>
    <x-slot:title>Tambah Jurusan</x-slot:title>

    <div class="container px-6 mx-auto">
        <div class="my-6">
            <x-breadcrumb :items="[
                ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
                ['label' => 'Jurusan', 'url' => route('admin.jurusan.index')],
                ['label' => 'Tambah Jurusan'],
            ]" />
            <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
                Tambah Jurusan
            </h2>
        </div>
        <div class="max-w-lg p-6 bg-white rounded-lg shadow-xs dark:shadow-none dark:border dark:border-gray-700 dark:bg-gray-800">
            <form method="POST" action="{{ route('admin.jurusan.store') }}">
                @csrf

                <label class="block text-sm mb-4">
                    <span class="text-gray-700 dark:text-gray-400">Kode Jurusan</span>
                    <input type="text" name="kode_jurusan" value="{{ old('kode_jurusan') }}"
                        placeholder="contoh: IPA"
                        class="block w-full mt-1 text-sm form-input dark:bg-gray-700 dark:text-gray-300 @error('kode_jurusan') border-red-500 @enderror" />
                    @error('kode_jurusan')
                        <span class="text-xs text-red-500">{{ $message }}</span>
                    @enderror
                </label>

                <label class="block text-sm mb-4">
                    <span class="text-gray-700 dark:text-gray-400">Nama Jurusan</span>
                    <input type="text" name="nama_jurusan" value="{{ old('nama_jurusan') }}"
                        placeholder="contoh: Ilmu Pengetahuan Alam"
                        class="block w-full mt-1 text-sm form-input dark:bg-gray-700 dark:text-gray-300 @error('nama_jurusan') border-red-500 @enderror" />
                    @error('nama_jurusan')
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
