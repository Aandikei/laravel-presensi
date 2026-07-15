<x-layouts.admin>
    <x-slot:title>Edit Sekolah</x-slot:title>

    <div class="container px-6 mx-auto">
        <div class="my-6">
            <x-breadcrumb :items="[
                ['label' => 'Dashboard', 'url' => route('superadmin.dashboard')],
                ['label' => 'Kelola Sekolah', 'url' => route('superadmin.sekolah.index')],
                ['label' => 'Edit Sekolah'],
            ]" />
            <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
                Edit Sekolah
            </h2>
        </div>

        <div class="max-w-2xl p-6 bg-white rounded-lg shadow-xs dark:shadow-none dark:border dark:border-gray-700 dark:bg-gray-800">
            <form method="POST" action="{{ route('superadmin.sekolah.update', $instansi->id_instansi) }}">
                @csrf
                @method('PUT')

                @php
                    $jenjangPrefixes = ['SD', 'SMP', 'SMA', 'MI', 'MTs', 'MA', 'SMK'];
                    $namaInstansi = old('nama_instansi', $instansi->nama_instansi);
                    $extractedJenjang = old('jenjang', $instansi->jenjang);
                    $namaSekolah = '';
                    if ($namaInstansi) {
                        $found = false;
                        foreach ($jenjangPrefixes as $p) {
                            if (str_starts_with($namaInstansi, $p . ' ') || $namaInstansi === $p) {
                                $namaSekolah = trim(substr($namaInstansi, strlen($p)));
                                $found = true;
                                break;
                            }
                        }
                        if (!$found) {
                            $namaSekolah = $namaInstansi;
                        }
                    }
                @endphp

                <div class="grid grid-cols-2 gap-4">
                    <label class="block text-sm mb-4">
                        <span class="text-gray-700 dark:text-gray-400">Jenjang</span>
                        <select name="jenjang" id="jenjang"
                            class="block w-full mt-1 text-sm dark:bg-gray-700 dark:text-gray-300 @error('jenjang') border-red-500 @enderror">
                            @foreach (['SD', 'SMP', 'SMA'] as $j)
                                <option value="{{ $j }}" {{ $extractedJenjang == $j ? 'selected' : '' }}>{{ $j }}</option>
                            @endforeach
                        </select>
                        @error('jenjang')
                            <span class="text-xs text-red-500">{{ $message }}</span>
                        @enderror
                    </label>

                    <label class="block text-sm mb-4">
                        <span class="text-gray-700 dark:text-gray-400">Label Jenjang <span class="text-gray-400">(opsional)</span></span>
                        <select name="label_jenjang" id="label_jenjang"
                            class="block w-full mt-1 text-sm dark:bg-gray-700 dark:text-gray-300">
                            <option value="">— {{ $extractedJenjang }} —</option>
                        </select>
                        @error('label_jenjang')
                            <span class="text-xs text-red-500">{{ $message }}</span>
                        @enderror
                    </label>

                </div>

                <input type="hidden" name="nama_instansi" id="nama_instansi" value="{{ $namaInstansi }}" />

                <label class="block text-sm mb-4">
                    <span class="text-gray-700 dark:text-gray-400">Nama Sekolah</span>
                    <div class="flex mt-1">
                        <span id="jenjang-prefix"
                            class="inline-flex items-center px-3 text-sm rounded-l-lg border border-r-0 bg-gray-100 dark:bg-gray-600 text-gray-700 dark:text-gray-300 {{ $extractedJenjang ? '' : 'hidden' }}">{{ $extractedJenjang }}</span>
                        <input type="text" id="nama_sekolah" value="{{ old('nama_sekolah', $namaSekolah) }}"
                            class="flex-1 min-w-0 text-sm form-input dark:bg-gray-700 dark:text-gray-300 @error('nama_instansi') border-red-500 @enderror {{ $extractedJenjang ? 'rounded-r-lg' : 'rounded-lg' }}"
                            placeholder="{{ $extractedJenjang ? 'Nama sekolah...' : 'Pilih jenjang terlebih dahulu' }}" />
                    </div>
                    @error('nama_instansi')
                        <span class="text-xs text-red-500">{{ $message }}</span>
                    @enderror
                </label>

                <label class="block text-sm mb-4">
                    <span class="text-gray-700 dark:text-gray-400">NPSN</span>
                    <input type="text" name="npsn" value="{{ old('npsn', $instansi->npsn) }}"
                        class="block w-full mt-1 text-sm form-input dark:bg-gray-700 dark:text-gray-300 @error('npsn') border-red-500 @enderror" />
                    @error('npsn')
                        <span class="text-xs text-red-500">{{ $message }}</span>
                    @enderror
                </label>

                <div class="grid grid-cols-2 gap-4">
                    <label class="block text-sm mb-4">
                        <span class="text-gray-700 dark:text-gray-400">Telepon</span>
                        <input type="text" name="telepon" value="{{ old('telepon', $instansi->telepon) }}"
                            class="block w-full mt-1 text-sm form-input dark:bg-gray-700 dark:text-gray-300" />
                    </label>

                    <label class="block text-sm mb-4">
                        <span class="text-gray-700 dark:text-gray-400">Email</span>
                        <input type="email" name="email" value="{{ old('email', $instansi->email) }}"
                            class="block w-full mt-1 text-sm form-input dark:bg-gray-700 dark:text-gray-300" />
                    </label>
                </div>

                <label class="block text-sm mb-4">
                    <span class="text-gray-700 dark:text-gray-400">Alamat</span>
                    <textarea name="alamat" rows="2"
                        class="block w-full mt-1 text-sm form-textarea dark:bg-gray-700 dark:text-gray-300">{{ old('alamat', $instansi->alamat) }}</textarea>
                </label>

                <button type="submit"
                    class="w-full px-4 py-2 text-sm font-medium text-white bg-purple-600 rounded-lg hover:bg-purple-700">
                    Simpan Perubahan
                </button>
            </form>
            @push('scripts')
            <script>
                const jenjangEl = document.getElementById('jenjang');
                const prefixEl = document.getElementById('jenjang-prefix');
                const namaEl = document.getElementById('nama_sekolah');
                const hiddenEl = document.getElementById('nama_instansi');
                const labelEl = document.getElementById('label_jenjang');

                const labelMap = {
                    SD:  ['SD', 'MI'],
                    SMP: ['SMP', 'MTs'],
                    SMA: ['SMA', 'MA', 'SMK'],
                };

                function updateLabelOptions() {
                    const labels = labelMap[jenjangEl.value] || [];
                    const oldVal = labelEl.value;
                    labelEl.innerHTML = '<option value="">— ' + (jenjangEl.value || 'Pilih jenjang dulu') + ' —</option>';
                    labels.forEach(function(l) {
                        const opt = document.createElement('option');
                        opt.value = l;
                        opt.textContent = l;
                        labelEl.appendChild(opt);
                    });
                    if (oldVal) labelEl.value = oldVal;
                    else labelEl.value = '{{ $instansi->label_jenjang }}';
                }

                function getPrefix() { return labelEl.value || jenjangEl.value; }

                function updateNama() {
                    if (jenjangEl.value) {
                        prefixEl.textContent = getPrefix();
                        prefixEl.classList.remove('hidden');
                        namaEl.classList.remove('rounded-lg');
                        namaEl.classList.add('rounded-r-lg');
                        namaEl.placeholder = 'Nama sekolah...';
                    } else {
                        prefixEl.classList.add('hidden');
                        namaEl.classList.remove('rounded-r-lg');
                        namaEl.classList.add('rounded-lg');
                        namaEl.placeholder = 'Pilih jenjang terlebih dahulu';
                    }
                    var p = getPrefix();
                    hiddenEl.value = p ? p + ' ' + namaEl.value : namaEl.value;
                }

                jenjangEl.addEventListener('change', function() {
                    updateLabelOptions();
                    updateNama();
                });
                labelEl.addEventListener('change', updateNama);
                namaEl.addEventListener('input', updateNama);

                updateLabelOptions();
            </script>
            @endpush
        </div>
    </div>
</x-layouts.admin>
