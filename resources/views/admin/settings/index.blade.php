<x-layouts.admin>
    <x-slot:title>Pengaturan Sekolah</x-slot:title>

    <div class="container px-6 mx-auto">
        <div class="my-6">
            <x-breadcrumb :items="[
                ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
                ['label' => 'Pengaturan'],
            ]" />
            <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">Pengaturan Sekolah</h2>
        </div>

        <div class="max-w-2xl p-6 bg-white rounded-lg shadow-xs dark:shadow-none dark:border dark:border-gray-700 dark:bg-gray-800">

            <div class="mb-6">
                <p class="font-semibold text-gray-700 dark:text-gray-200 text-lg">{{ $instansi->nama_instansi }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $instansi->label_jenjang }} • NPSN: {{ $instansi->npsn }}</p>
            </div>

            <form method="POST" action="{{ route('admin.settings.update') }}">
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
                        <select name="jenjang" id="jenjang"
                            class="block w-full mt-1 text-sm dark:bg-gray-700 dark:text-gray-300">
                            @foreach(['SD','SMP','SMA'] as $j)
                                <option value="{{ $j }}" {{ old('jenjang', $instansi->jenjang) == $j ? 'selected' : '' }}>
                                    {{ $j }}
                                </option>
                            @endforeach
                        </select>
                    </label>

                    <label class="block text-sm">
                        <span class="text-gray-700 dark:text-gray-400">Label Jenjang <span class="text-gray-400">(opsional)</span></span>
                        <select name="label_jenjang" id="label_jenjang"
                            class="block w-full mt-1 text-sm dark:bg-gray-700 dark:text-gray-300">
                            <option value="">— {{ $instansi->label_jenjang }} —</option>
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

                </div>

                @push('scripts')
                <script>
                    const sJenjang = document.getElementById('jenjang');
                    const sLabel = document.getElementById('label_jenjang');
                    const sLabelMap = { SD: ['SD', 'MI'], SMP: ['SMP', 'MTs'], SMA: ['SMA', 'MA', 'SMK'] };

                    function updateSLabel() {
                        const labels = sLabelMap[sJenjang.value] || [];
                        const oldVal = sLabel.value;
                        sLabel.innerHTML = '<option value="">— ' + sJenjang.value + ' —</option>';
                        labels.forEach(function(l) {
                            const opt = document.createElement('option');
                            opt.value = l; opt.textContent = l;
                            sLabel.appendChild(opt);
                        });
                        if (oldVal) sLabel.value = oldVal;
                        else sLabel.value = '{{ $instansi->label_jenjang }}';
                    }
                    sJenjang.addEventListener('change', updateSLabel);
                    updateSLabel();
                </script>
                @endpush

                <button type="submit"
                    class="mt-6 w-full px-4 py-2 text-sm font-medium text-white bg-purple-600 rounded-lg hover:bg-purple-700">
                    Simpan Pengaturan
                </button>
            </form>
        </div>
    </div>
</x-layouts.admin>