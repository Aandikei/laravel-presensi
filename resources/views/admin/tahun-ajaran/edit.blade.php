<x-layouts.admin>
    <x-slot:title>Edit Tahun Ajaran</x-slot:title>

    <div class="container px-6 mx-auto">
        <div class="my-6">
            <x-breadcrumb :items="[
                ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
                ['label' => 'Tahun Ajaran', 'url' => route('admin.tahun-ajaran.index')],
                ['label' => 'Edit'],
            ]" />
            <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
                Edit Tahun Ajaran
            </h2>
        </div>
        @php
            $editTahun1 = (int) explode('/', $tahunAjaran->nama_tahun)[0];
        @endphp
        <div class="max-w-lg p-6 bg-white rounded-lg shadow-xs dark:shadow-none dark:border dark:border-gray-700 dark:bg-gray-800">
            <form method="POST" action="{{ route('admin.tahun-ajaran.update', $tahunAjaran) }}" id="form-tahun-ajaran">
                @csrf
                @method('PUT')

                {{-- Tahun Awal --}}
                <label class="block text-sm mb-4">
                    <span class="text-gray-700 dark:text-gray-400">Tahun Awal</span>
                    <input type="number" id="tahun-awal" min="1900"
                        value="{{ $editTahun1 }}" readonly disabled
                        class="block w-full mt-1 text-sm form-input dark:bg-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-600 cursor-not-allowed" />
                </label>

                {{-- Tahun Akhir --}}
                <label class="block text-sm mb-4">
                    <span class="text-gray-700 dark:text-gray-400">Tahun Akhir</span>
                    <input type="text" id="tahun-akhir" readonly disabled
                        class="block w-full mt-1 text-sm form-input dark:bg-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-600 cursor-not-allowed" />
                </label>

                {{-- Hidden nama_tahun --}}
                <input type="hidden" name="nama_tahun" id="nama-tahun" value="{{ old('nama_tahun', $tahunAjaran->nama_tahun) }}">

                {{-- Semester --}}
                <label class="block text-sm mb-4">
                    <span class="text-gray-700 dark:text-gray-400">Semester</span>
                    <input type="text" id="semester-display" readonly disabled
                        value="{{ old('semester', $tahunAjaran->semester) }}"
                        class="block w-full mt-1 text-sm form-input dark:bg-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-600 cursor-not-allowed" />
                    <input type="hidden" name="semester" id="semester" value="{{ old('semester', $tahunAjaran->semester) }}">
                    @error('semester')
                        <span class="text-xs text-red-500">{{ $message }}</span>
                    @enderror
                </label>

                {{-- Tanggal Mulai --}}
                <label class="block text-sm mb-4">
                    <span class="text-gray-700 dark:text-gray-400">Tanggal Mulai</span>
                    <input type="date" name="tanggal_mulai" id="tanggal-mulai"
                        value="{{ old('tanggal_mulai', $tahunAjaran->tanggal_mulai->format('Y-m-d')) }}"
                        class="block w-full mt-1 text-sm form-input dark:bg-gray-700 dark:text-gray-300 @error('tanggal_mulai') border-red-500 @enderror" />
                    @error('tanggal_mulai')
                        <span class="text-xs text-red-500">{{ $message }}</span>
                    @enderror
                </label>

                {{-- Tanggal Selesai --}}
                <label class="block text-sm mb-4">
                    <span class="text-gray-700 dark:text-gray-400">Tanggal Selesai</span>
                    <input type="date" name="tanggal_selesai" id="tanggal-selesai"
                        value="{{ old('tanggal_selesai', $tahunAjaran->tanggal_selesai->format('Y-m-d')) }}"
                        class="block w-full mt-1 text-sm form-input dark:bg-gray-700 dark:text-gray-300 @error('tanggal_selesai') border-red-500 @enderror" />
                    @error('tanggal_selesai')
                        <span class="text-xs text-red-500">{{ $message }}</span>
                    @enderror
                </label>

                <button type="submit" id="btn-submit"
                    class="w-full px-4 py-2 text-sm font-medium text-white bg-purple-600 rounded-lg hover:bg-purple-700">
                    Update
                </button>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        (function() {
            function setMinMax(tahun1, semester) {
                var mulai = document.getElementById('tanggal-mulai');
                var selesai = document.getElementById('tanggal-selesai');

                if (semester === 'Ganjil') {
                    mulai.min = tahun1 + '-01-01';
                    mulai.max = tahun1 + '-12-31';
                    selesai.min = tahun1 + '-01-01';
                    selesai.max = tahun1 + '-12-31';
                } else if (semester === 'Genap') {
                    var t2 = tahun1 + 1;
                    mulai.min = t2 + '-01-01';
                    mulai.max = t2 + '-12-31';
                    selesai.min = t2 + '-01-01';
                    selesai.max = t2 + '-12-31';
                }
            }

            document.addEventListener('DOMContentLoaded', function() {
                var tahunAwal = document.getElementById('tahun-awal');
                var t1 = parseInt(tahunAwal.value, 10);

                if (t1 && t1 >= 1900) {
                    document.getElementById('tahun-akhir').value = t1 + 1;
                    var semester = document.getElementById('semester').value;
                    if (semester) setMinMax(t1, semester);
                }
            });
        })();
    </script>
    @endpush
</x-layouts.admin>
