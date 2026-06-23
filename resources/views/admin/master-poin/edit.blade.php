<x-layouts.admin>
    <x-slot:title>Edit Pelanggaran</x-slot:title>

    <div class="container px-6 mx-auto">
        <div class="my-6">
            <x-breadcrumb :items="[
                ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
                ['label' => 'Master Poin', 'url' => route('admin.master-poin.index')],
                ['label' => 'Edit'],
            ]" />
            <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">Edit Pelanggaran</h2>
        </div>
        <div class="max-w-lg p-6 bg-white rounded-lg shadow-xs dark:shadow-none dark:border dark:border-gray-700 dark:bg-gray-800">
            <form method="POST" action="{{ route('admin.master-poin.update', $masterPoin) }}">
                @csrf
                @method('PUT')

                <label class="block text-sm mb-4">
                    <span class="text-gray-700 dark:text-gray-400">Nama Pelanggaran</span>
                    <input type="text" name="nama_pelanggaran"
                        value="{{ old('nama_pelanggaran', $masterPoin->nama_pelanggaran) }}"
                        class="block w-full mt-1 text-sm form-input dark:bg-gray-700 dark:text-gray-300 @error('nama_pelanggaran') border-red-500 @enderror" />
                    @error('nama_pelanggaran')
                        <span class="text-xs text-red-500">{{ $message }}</span>
                    @enderror
                </label>

                <label class="block text-sm mb-4">
                    <span class="text-gray-700 dark:text-gray-400">Deskripsi <span class="text-gray-400">(opsional)</span></span>
                    <textarea name="deskripsi" rows="3" placeholder="contoh: Tidak membawa buku pelajaran"
                        class="block w-full mt-1 text-sm form-textarea dark:bg-gray-700 dark:text-gray-300 @error('deskripsi') border-red-500 @enderror">{{ old('deskripsi', $masterPoin->deskripsi) }}</textarea>
                    @error('deskripsi')
                        <span class="text-xs text-red-500">{{ $message }}</span>
                    @enderror
                </label>

                <label class="block text-sm mb-4">
                    <span class="text-gray-700 dark:text-gray-400">Jumlah Poin</span>
                    <input type="number" name="jumlah_poin"
                        value="{{ old('jumlah_poin', $masterPoin->jumlah_poin) }}"
                        min="1" max="100"
                        class="block w-full mt-1 text-sm form-input dark:bg-gray-700 dark:text-gray-300 @error('jumlah_poin') border-red-500 @enderror" />
                    @error('jumlah_poin')
                        <span class="text-xs text-red-500">{{ $message }}</span>
                    @enderror
                </label>

                <button type="submit"
                    class="w-full px-4 py-2 text-sm font-medium text-white bg-purple-600 rounded-lg hover:bg-purple-700">
                    Update
                </button>
            </form>
        </div>
    </div>
</x-layouts.admin>