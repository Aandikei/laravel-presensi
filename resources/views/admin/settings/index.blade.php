<x-layouts.admin>
    <x-slot:title>Pengaturan Sekolah</x-slot:title>

    <div class="container px-6 mx-auto">
        <div class="my-6">
            <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">Pengaturan Sekolah</h2>
        </div>

        @if(session('success'))
            <div class="px-4 py-3 mb-4 text-sm text-green-700 bg-green-100 rounded-lg dark:bg-green-800 dark:text-green-200">
                {{ session('success') }}
            </div>
        @endif

        <div class="max-w-2xl p-6 bg-white rounded-lg shadow-xs dark:shadow-none dark:border dark:border-gray-700 dark:bg-gray-800">

            {{-- Preview Logo --}}
            <div class="mb-6 flex items-center gap-4">
                @if($instansi->logo)
                    <img src="{{ Storage::url($instansi->logo) }}"
                        alt="Logo" class="w-20 h-20 object-contain rounded-lg border border-gray-200 dark:border-gray-700">
                @else
                    <div class="w-20 h-20 rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600 flex items-center justify-center">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                @endif
                <div>
                    <p class="font-semibold text-gray-700 dark:text-gray-200">{{ $instansi->nama_instansi }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $instansi->jenjang }} • NPSN: {{ $instansi->npsn }}</p>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data">
                @csrf
                @method('PATCH')

                <div class="grid gap-4 md:grid-cols-2">
                    <label class="block text-sm md:col-span-2">
                        <span class="text-gray-700 dark:text-gray-400">Nama Sekolah</span>
                        <input type="text" name="nama_instansi"
                            value="{{ old('nama_instansi', $instansi->nama_instansi) }}"
                            class="block w-full mt-1 text-sm form-input dark:bg-gray-700 dark:text-gray-300 @error('nama_instansi') border-red-500 @enderror" />
                        @error('nama_instansi')
                            <span class="text-xs text-red-500">{{ $message }}</span>
                        @enderror
                    </label>

                    <label class="block text-sm">
                        <span class="text-gray-700 dark:text-gray-400">NPSN</span>
                        <input type="text" name="npsn"
                            value="{{ old('npsn', $instansi->npsn) }}"
                            class="block w-full mt-1 text-sm form-input dark:bg-gray-700 dark:text-gray-300 @error('npsn') border-red-500 @enderror" />
                        @error('npsn')
                            <span class="text-xs text-red-500">{{ $message }}</span>
                        @enderror
                    </label>

                    <label class="block text-sm">
                        <span class="text-gray-700 dark:text-gray-400">Jenjang</span>
                        <select name="jenjang"
                            class="block w-full mt-1 text-sm dark:bg-gray-700 dark:text-gray-300">
                            @foreach(['SD','SMP','SMA','SMK'] as $j)
                                <option value="{{ $j }}" {{ old('jenjang', $instansi->jenjang) == $j ? 'selected' : '' }}>
                                    {{ $j }}
                                </option>
                            @endforeach
                        </select>
                    </label>

                    <label class="block text-sm md:col-span-2">
                        <span class="text-gray-700 dark:text-gray-400">Alamat</span>
                        <textarea name="alamat" rows="2"
                            class="block w-full mt-1 text-sm form-textarea dark:bg-gray-700 dark:text-gray-300">{{ old('alamat', $instansi->alamat) }}</textarea>
                    </label>

                    <label class="block text-sm">
                        <span class="text-gray-700 dark:text-gray-400">Telepon</span>
                        <input type="text" name="telepon"
                            value="{{ old('telepon', $instansi->telepon) }}"
                            class="block w-full mt-1 text-sm form-input dark:bg-gray-700 dark:text-gray-300" />
                    </label>

                    <label class="block text-sm">
                        <span class="text-gray-700 dark:text-gray-400">Email Sekolah</span>
                        <input type="email" name="email"
                            value="{{ old('email', $instansi->email) }}"
                            class="block w-full mt-1 text-sm form-input dark:bg-gray-700 dark:text-gray-300" />
                    </label>

                    <label class="block text-sm md:col-span-2">
                        <span class="text-gray-700 dark:text-gray-400">
                            Logo Sekolah
                            <span class="text-gray-400">(PNG/JPG, maks 2MB)</span>
                        </span>
                        <div class="relative mt-1">
                            <input type="file" name="logo" accept=".png,.jpg,.jpeg"
                                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" />
                            <div class="flex items-center justify-between px-3 py-2 text-sm border border-gray-300 rounded-lg cursor-pointer dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                <span class="truncate text-gray-400">Pilih file...</span>
                                <svg class="w-5 h-5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                        </div>
                        @error('logo')
                            <span class="text-xs text-red-500">{{ $message }}</span>
                        @enderror
                    </label>
                </div>

                <button type="submit"
                    class="mt-6 w-full px-4 py-2 text-sm font-medium text-white bg-purple-600 rounded-lg hover:bg-purple-700">
                    Simpan Pengaturan
                </button>
            </form>
        </div>
    </div>
</x-layouts.admin>