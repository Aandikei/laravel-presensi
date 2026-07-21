<x-layouts.admin>
    <x-slot:title>Atur Kurikulum Guru Kelas</x-slot:title>

    <div class="container px-6 mx-auto">
        <div class="my-6">
            <x-breadcrumb :items="[
                ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
                ['label' => 'Kurikulum', 'url' => route('admin.kurikulum.index')],
                ['label' => 'Atur Guru Kelas'],
            ]" />
            <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
                Atur Kurikulum Guru Kelas
            </h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Pilih kelas dan centang mapel yang diampu oleh guru kelas. Kurikulum akan otomatis dibuat dengan guru kelas sebagai pengampu.
            </p>
        </div>

        <div class="max-w-2xl p-6 bg-white rounded-lg shadow-xs dark:shadow-none dark:border dark:border-gray-700 dark:bg-gray-800">
            <form method="POST" action="{{ route('admin.kurikulum.batch-guru-kelas.store') }}">
                @csrf

                <label class="block text-sm mb-6">
                    <span class="text-gray-700 dark:text-gray-400">Kelas</span>
                    <select name="kelas_id" id="kelas-select"
                        class="block w-full mt-1 text-sm dark:bg-gray-700 dark:text-gray-300 @error('kelas_id') border-red-500 @enderror">
                        <option value="">-- Pilih Kelas --</option>
                        @foreach($kelas as $k)
                            <option value="{{ $k->id_kelas }}" {{ old('kelas_id') == $k->id_kelas ? 'selected' : '' }}
                                data-wali="{{ $k->waliKelas?->nama_guru ?? '-' }}">
                                {{ $k->nama_kelas }} (Tingkat {{ $k->tingkat }})
                            </option>
                        @endforeach
                    </select>
                    @error('kelas_id')
                        <span class="text-xs text-red-500">{{ $message }}</span>
                    @enderror
                </label>

                <div id="wali-info" class="hidden mb-6 p-3 bg-purple-50 dark:bg-purple-900/20 rounded-lg">
                    <p class="text-sm text-purple-700 dark:text-purple-300">
                        Guru Kelas: <strong id="wali-nama">-</strong>
                    </p>
                </div>

                <label class="block text-sm mb-4">
                    <span class="text-gray-700 dark:text-gray-400">Mapel yang Diampu Guru Kelas</span>
                    <div class="mt-2 space-y-2 max-h-96 overflow-y-auto border dark:border-gray-700 rounded-lg p-3">
                        @foreach($mapel as $m)
                            <label class="flex items-center gap-3 p-2 hover:bg-gray-50 dark:hover:bg-gray-700/50 rounded cursor-pointer">
                                <input type="checkbox" name="mapel_ids[]" value="{{ $m->id_mapel }}"
                                    class="rounded border-gray-300 text-purple-600 focus:ring-purple-500"
                                    {{ in_array($m->id_mapel, old('mapel_ids', [])) ? 'checked' : '' }}>
                                <span class="text-sm text-gray-700 dark:text-gray-200">{{ $m->nama_mapel }}</span>
                                <span class="text-xs text-gray-400">({{ $m->kode_mapel }})</span>
                            </label>
                        @endforeach
                    </div>
                    @error('mapel_ids')
                        <span class="text-xs text-red-500">{{ $message }}</span>
                    @enderror
                    @error('mapel_ids.*')
                        <span class="text-xs text-red-500">{{ $message }}</span>
                    @enderror
                </label>

                <button type="submit"
                    class="w-full px-4 py-2 text-sm font-medium text-white bg-purple-600 rounded-lg hover:bg-purple-700 disabled:opacity-50 disabled:cursor-not-allowed"
                    id="btn-submit" disabled>
                    Simpan Kurikulum Guru Kelas
                </button>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        var existingGuruKelas = @json($existingGuruKelas);

        $(document).ready(function() {
            function updateMapelCheckboxes() {
                var kelasId = $('#kelas-select').val();
                var existing = (existingGuruKelas[kelasId] || []).map(String);
                var hasNew = false;

                $('input[name="mapel_ids[]"]').each(function() {
                    var mapelId = $(this).val();
                    if (existing.indexOf(mapelId) !== -1) {
                        $(this).prop('checked', true).prop('disabled', true);
                        $(this).closest('label').addClass('opacity-60 cursor-not-allowed');
                    } else {
                        $(this).prop('checked', false).prop('disabled', false);
                        $(this).closest('label').removeClass('opacity-60 cursor-not-allowed');
                        hasNew = true;
                    }
                });

                if (!hasNew) {
                    $('#btn-submit').prop('disabled', true);
                }
            }

            $('#kelas-select').on('change', function() {
                var selected = $(this).find('option:selected');
                var wali = selected.data('wali');
                if (this.value && wali && wali !== '-') {
                    $('#wali-nama').text(wali);
                    $('#wali-info').removeClass('hidden');
                    $('#btn-submit').prop('disabled', false);
                } else {
                    $('#wali-info').addClass('hidden');
                    $('#btn-submit').prop('disabled', true);
                }
                updateMapelCheckboxes();
            }).trigger('change');
        });
    </script>
    @endpush
</x-layouts.admin>
