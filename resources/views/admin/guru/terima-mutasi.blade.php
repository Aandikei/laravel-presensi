<x-layouts.admin>
    <x-slot:title>Terima Mutasi Guru</x-slot:title>

    <div class="container px-6 mx-auto">
        <div class="my-6">
            <x-breadcrumb :items="[
                ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
                ['label' => 'Terima Mutasi Guru'],
            ]" />
            <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
                Terima Mutasi Guru
            </h2>
            <p class="text-sm text-gray-500 mt-1">Masukkan token dari sekolah asal untuk menerima guru pindahan.</p>
        </div>

        <div class="max-w-lg p-6 bg-white rounded-lg shadow-xs dark:shadow-none dark:border dark:border-gray-700 dark:bg-gray-800">
            <form method="POST" action="{{ route('admin.guru.mutasi.terima.verifikasi') }}">
                @csrf

                <label class="block text-sm mb-4">
                    <span class="text-gray-700 dark:text-gray-400">Token Mutasi</span>
                    <input type="text" name="token" value="{{ old('token') }}" maxlength="6"
                        class="block w-full mt-1 text-sm form-input uppercase tracking-widest text-center text-lg font-bold dark:bg-gray-700 dark:text-gray-300 @error('token') border-red-500 @enderror"
                        placeholder="XXXXXX" />
                    @error('token')
                        <span class="text-xs text-red-500">{{ $message }}</span>
                    @enderror
                </label>

                <button type="submit"
                    class="w-full px-4 py-2 text-sm font-medium text-white bg-orange-600 rounded-lg hover:bg-orange-700">
                    Verifikasi Token
                </button>
            </form>
        </div>
    </div>
</x-layouts.admin>
