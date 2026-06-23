<x-layouts.admin>
    <x-slot:title>Terima Siswa Pindahan</x-slot:title>

    <div class="container px-6 mx-auto">
        <div class="my-6">
            <x-breadcrumb :items="[
                ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
                ['label' => 'Terima Pindahan'],
            ]" />
            <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
                Terima Siswa Pindahan
            </h2>
        </div>

        @if(session('info'))
            <div class="px-4 py-3 mb-4 text-sm text-blue-700 bg-blue-100 rounded-lg dark:bg-blue-800 dark:text-blue-200">
                {{ session('info') }}
            </div>
        @endif

        <div class="max-w-lg p-6 bg-white rounded-lg shadow-xs dark:shadow-none dark:border dark:border-gray-700 dark:bg-gray-800">
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
                Masukkan NISN dan kode transfer dari sekolah asal untuk memindahkan siswa ke sekolah ini.
            </p>

            <form method="POST" action="{{ route('admin.siswa.pindah.verifikasi') }}">
                @csrf

                <label class="block text-sm mb-4">
                    <span class="text-gray-700 dark:text-gray-400">NISN Siswa</span>
                    <input type="text" name="nisn" value="{{ old('nisn', request('nisn')) }}" required
                        class="block w-full mt-1 text-sm form-input dark:bg-gray-700 dark:text-gray-300 @error('nisn') border-red-500 @enderror"
                        placeholder="Masukkan NISN" />
                    @error('nisn')
                        <span class="text-xs text-red-500">{{ $message }}</span>
                    @enderror
                </label>

                <label class="block text-sm mb-4">
                    <span class="text-gray-700 dark:text-gray-400">Kode Transfer</span>
                    <input type="text" name="token" value="{{ old('token') }}" required maxlength="6"
                        class="block w-full mt-1 text-sm form-input uppercase dark:bg-gray-700 dark:text-gray-300 @error('token') border-red-500 @enderror tracking-widest text-center font-bold"
                        placeholder="XXXXXX" />
                    @error('token')
                        <span class="text-xs text-red-500">{{ $message }}</span>
                    @enderror
                </label>

                <div class="mb-4 px-3 py-2 text-xs text-blue-700 bg-blue-50 rounded-lg dark:bg-blue-900/30 dark:text-blue-300">
                    Kode transfer diperoleh dari sekolah asal siswa. Hubungi admin sekolah asal jika belum punya kode.
                </div>

                <button type="submit"
                    class="w-full px-4 py-2 text-sm font-medium text-white bg-purple-600 rounded-lg hover:bg-purple-700">
                    Verifikasi & Lanjutkan
                </button>
            </form>
        </div>
    </div>
</x-layouts.admin>
