<x-layouts.admin>
    <x-slot:title>Tambah Kelas</x-slot:title>

    <div class="container px-6 mx-auto">
        <div class="my-6">
            <x-breadcrumb :items="[
                ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
                ['label' => 'Kelas', 'url' => route('admin.kelas.index')],
                ['label' => 'Tambah Kelas'],
            ]" />
            <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
                Tambah Kelas
            </h2>
        </div>
        <div class="max-w-lg p-6 bg-white rounded-lg shadow-xs dark:shadow-none dark:border dark:border-gray-700 dark:bg-gray-800">
            <form method="POST" action="{{ route('admin.kelas.store') }}">
                @csrf

                <label class="block text-sm mb-4">
                    <span class="text-gray-700 dark:text-gray-400">Tingkat</span>
                    <select name="tingkat" id="tingkat"
                        class="block w-full mt-1 text-sm dark:bg-gray-700 dark:text-gray-300 @error('tingkat') border-red-500 @enderror">
                        <option value="">-- Pilih Tingkat --</option>
                        @foreach($daftarTingkat as $t)
                            <option value="{{ $t }}" {{ old('tingkat') == $t ? 'selected' : '' }}>{{ $t }}</option>
                        @endforeach
                    </select>
                    @error('tingkat')
                        <span class="text-xs text-red-500">{{ $message }}</span>
                    @enderror
                </label>

                @if($instansi->jenjang === 'SMA')
                <label class="block text-sm mb-4">
                    <span class="text-gray-700 dark:text-gray-400">Jurusan</span>
                    <select name="jurusan_id" id="jurusan"
                        class="block w-full mt-1 text-sm dark:bg-gray-700 dark:text-gray-300 @error('jurusan_id') border-red-500 @enderror">
                        <option value="">-- Pilih Jurusan --</option>
                        @foreach($jurusanList as $j)
                            <option value="{{ $j->id_jurusan }}" {{ old('jurusan_id') == $j->id_jurusan ? 'selected' : '' }}>
                                {{ $j->kode_jurusan }} ({{ $j->nama_jurusan }})
                            </option>
                        @endforeach
                    </select>
                    @error('jurusan_id')
                        <span class="text-xs text-red-500">{{ $message }}</span>
                    @enderror
                </label>
                @endif

                <label class="block text-sm mb-4">
                    <span class="text-gray-700 dark:text-gray-400">Nomor Kelas</span>
                    <input type="text" name="nomor_kelas" id="nomor_kelas" value="{{ old('nomor_kelas') }}"
                        placeholder="{{ $instansi->jenjang === 'SMA' ? 'contoh: 1, 2, 3' : 'contoh: A, B, C' }}"
                        class="block w-full mt-1 text-sm form-input dark:bg-gray-700 dark:text-gray-300 @error('nomor_kelas') border-red-500 @enderror" />
                    <p class="text-xs text-gray-400 mt-1">
                        Kosongkan jika hanya ada 1 kelas di tingkat
                        @if($instansi->jenjang === 'SMA') dan jurusan @endif ini.
                        Akan terisi otomatis jika sudah ada kelas lain.
                    </p>
                    @error('nomor_kelas')
                        <span class="text-xs text-red-500">{{ $message }}</span>
                    @enderror
                </label>

                <label class="block text-sm mb-4">
                    <span class="text-gray-700 dark:text-gray-400">Wali Kelas <span class="text-gray-400">(opsional)</span></span>
                    <select name="guru_wali_id"
                        class="block w-full mt-1 text-sm dark:bg-gray-700 dark:text-gray-300">
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

    @push('scripts')
    <script>
        $(document).ready(function() {
            function updateNomorKelas() {
                var tingkat = $('#tingkat').val();
                var jurusanId = $('#jurusan').val() || '';
                if (!tingkat) return;

                $.get('{{ route('admin.kelas.next-nomor') }}', {
                    tingkat: tingkat,
                    jurusan_id: jurusanId
                }, function(data) {
                    var oldVal = $('#nomor_kelas').val();
                    if (oldVal) return;
                    if (data.next_nomor) {
                        $('#nomor_kelas').val(data.next_nomor).prop('readonly', true);
                    } else {
                        $('#nomor_kelas').prop('readonly', false);
                    }
                });
            }

            $('#tingkat, #jurusan').on('change', function() {
                $('#nomor_kelas').val('').prop('readonly', false);
                updateNomorKelas();
            });
        });
    </script>
    @endpush
</x-layouts.admin>
