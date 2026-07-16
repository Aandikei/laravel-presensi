<x-layouts.admin>
    <x-slot:title>Tambah Tahun Ajaran</x-slot:title>

    <div class="container px-6 mx-auto">
        <div class="my-6">
            <x-breadcrumb :items="[
                ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
                ['label' => 'Tahun Ajaran', 'url' => route('admin.tahun-ajaran.index')],
                ['label' => 'Tambah'],
            ]" />
            <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
                Tambah Tahun Ajaran
            </h2>
        </div>
        <div class="max-w-lg p-6 bg-white rounded-lg shadow-xs dark:shadow-none dark:border dark:border-gray-700 dark:bg-gray-800">
            <form method="POST" action="{{ route('admin.tahun-ajaran.store') }}" id="form-tahun-ajaran">
                @csrf

                {{-- Tahun Awal --}}
                <label class="block text-sm mb-4">
                    <span class="text-gray-700 dark:text-gray-400">Tahun Awal</span>
                    <input type="number" id="tahun-awal" min="1900"
                        value="{{ old('tahun_awal', $tahunMulai) }}"
                        class="block w-full mt-1 text-sm form-input dark:bg-gray-700 dark:text-gray-300 @error('nama_tahun') border-red-500 @enderror" />
                    @error('nama_tahun')
                        <span class="text-xs text-red-500">{{ $message }}</span>
                    @enderror
                </label>

                {{-- Tahun Akhir --}}
                <label class="block text-sm mb-4">
                    <span class="text-gray-700 dark:text-gray-400">Tahun Akhir</span>
                    <input type="text" id="tahun-akhir" readonly disabled
                        class="block w-full mt-1 text-sm form-input dark:bg-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-600 cursor-not-allowed" />
                </label>

                {{-- Hidden nama_tahun --}}
                <input type="hidden" name="nama_tahun" id="nama-tahun" value="{{ old('nama_tahun') }}">

                {{-- Semester --}}
                <label class="block text-sm mb-4">
                    <span class="text-gray-700 dark:text-gray-400">Semester</span>
                    <input type="text" id="semester-display" readonly disabled
                        class="block w-full mt-1 text-sm form-input dark:bg-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-600 cursor-not-allowed" />
                    <input type="hidden" name="semester" id="semester" value="{{ old('semester') }}">
                    @error('semester')
                        <span class="text-xs text-red-500">{{ $message }}</span>
                    @enderror
                </label>

                {{-- Tanggal Mulai --}}
                <label class="block text-sm mb-4">
                    <span class="text-gray-700 dark:text-gray-400">Tanggal Mulai</span>
                    <input type="date" name="tanggal_mulai" id="tanggal-mulai"
                        value="{{ old('tanggal_mulai') }}"
                        class="block w-full mt-1 text-sm form-input dark:bg-gray-700 dark:text-gray-300 @error('tanggal_mulai') border-red-500 @enderror" />
                    @error('tanggal_mulai')
                        <span class="text-xs text-red-500">{{ $message }}</span>
                    @enderror
                </label>

                {{-- Tanggal Selesai --}}
                <label class="block text-sm mb-4">
                    <span class="text-gray-700 dark:text-gray-400">Tanggal Selesai</span>
                    <input type="date" name="tanggal_selesai" id="tanggal-selesai"
                        value="{{ old('tanggal_selesai') }}"
                        class="block w-full mt-1 text-sm form-input dark:bg-gray-700 dark:text-gray-300 @error('tanggal_selesai') border-red-500 @enderror" />
                    @error('tanggal_selesai')
                        <span class="text-xs text-red-500">{{ $message }}</span>
                    @enderror
                </label>

                <div id="semester-warning" class="hidden mb-4 text-sm text-yellow-700 bg-yellow-100 rounded-lg p-3">
                    Semester Ganjil dan Genap untuk tahun ini sudah ada.
                </div>

                <button type="submit" id="btn-submit"
                    class="w-full px-4 py-2 text-sm font-medium text-white bg-purple-600 rounded-lg hover:bg-purple-700">
                    Simpan
                </button>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        (function() {
            const existingData = @json($existingData);

            function updateSemester(tahun1) {
                if (!tahun1 || tahun1 < 1900) return null;

                const tahun2 = tahun1 + 1;
                const namaTahun = tahun1 + '/' + tahun2;

                document.getElementById('tahun-akhir').value = tahun2;
                document.getElementById('nama-tahun').value = namaTahun;

                const hasGanjil = existingData.some(function(d) {
                    return d.nama_tahun === namaTahun && d.semester === 'Ganjil';
                });
                const hasGenap = existingData.some(function(d) {
                    return d.nama_tahun === namaTahun && d.semester === 'Genap';
                });

                var semesterDisplay = document.getElementById('semester-display');
                var semesterInput = document.getElementById('semester');
                var warning = document.getElementById('semester-warning');
                var submitBtn = document.getElementById('btn-submit');

                if (!hasGanjil) {
                    semesterDisplay.value = 'Ganjil';
                    semesterInput.value = 'Ganjil';
                    warning.classList.add('hidden');
                    submitBtn.disabled = false;
                    return 'Ganjil';
                }

                if (!hasGenap) {
                    semesterDisplay.value = 'Genap';
                    semesterInput.value = 'Genap';
                    warning.classList.add('hidden');
                    submitBtn.disabled = false;
                    return 'Genap';
                }

                semesterDisplay.value = '';
                semesterInput.value = '';
                warning.classList.remove('hidden');
                submitBtn.disabled = true;
                return null;
            }

            function setDates(tahun1, semester) {
                var mulai = document.getElementById('tanggal-mulai');
                var selesai = document.getElementById('tanggal-selesai');

                if (semester === 'Ganjil') {
                    mulai.value = tahun1 + '-07-01';
                    selesai.value = tahun1 + '-12-31';
                    mulai.min = tahun1 + '-01-01';
                    mulai.max = tahun1 + '-12-31';
                    selesai.min = tahun1 + '-01-01';
                    selesai.max = tahun1 + '-12-31';
                } else if (semester === 'Genap') {
                    var tahun2 = tahun1 + 1;
                    mulai.value = tahun2 + '-01-01';
                    selesai.value = tahun2 + '-06-30';
                    mulai.min = tahun2 + '-01-01';
                    mulai.max = tahun2 + '-12-31';
                    selesai.min = tahun2 + '-01-01';
                    selesai.max = tahun2 + '-12-31';
                }
            }

            function findAvailableYear(startTahun) {
                for (var i = 0; i < 20; i++) {
                    var t = startTahun + i;
                    var nama = t + '/' + (t + 1);
                    var hasGanjil = existingData.some(function(d) {
                        return d.nama_tahun === nama && d.semester === 'Ganjil';
                    });
                    var hasGenap = existingData.some(function(d) {
                        return d.nama_tahun === nama && d.semester === 'Genap';
                    });
                    if (!hasGanjil || !hasGenap) {
                        return t;
                    }
                }
                return null;
            }

            function clearForm() {
                document.getElementById('tahun-akhir').value = '';
                document.getElementById('nama-tahun').value = '';
                document.getElementById('semester-display').value = '';
                document.getElementById('semester').value = '';
                document.getElementById('tanggal-mulai').value = '';
                document.getElementById('tanggal-mulai').min = '';
                document.getElementById('tanggal-mulai').max = '';
                document.getElementById('tanggal-selesai').value = '';
                document.getElementById('tanggal-selesai').min = '';
                document.getElementById('tanggal-selesai').max = '';
                document.getElementById('semester-warning').classList.add('hidden');
                document.getElementById('btn-submit').disabled = false;
            }

            document.addEventListener('DOMContentLoaded', function() {
                var tahunAwal = document.getElementById('tahun-awal');
                var namaTahunInput = document.getElementById('nama-tahun');
                var hasOld = namaTahunInput.value !== '';

                if (hasOld) {
                    var parts = namaTahunInput.value.split('/');
                    if (parts.length === 2) {
                        tahunAwal.value = parseInt(parts[0], 10);
                        var t1 = parseInt(tahunAwal.value, 10);
                        if (t1 && t1 >= 1900) {
                            var semester = updateSemester(t1);
                            if (semester) setDates(t1, semester);
                        }
                    }
                    return;
                }

                // Auto-increment if initial year is complete
                var t1 = parseInt(tahunAwal.value, 10);
                if (t1 && t1 >= 1900) {
                    var available = findAvailableYear(t1);
                    if (available !== null && available !== t1) {
                        tahunAwal.value = available;
                        t1 = available;
                    }
                    var semester = updateSemester(t1);
                    if (semester) setDates(t1, semester);
                }

                tahunAwal.addEventListener('input', function() {
                    var t1 = parseInt(this.value, 10);
                    if (!this.value || isNaN(t1) || t1 < 1900) {
                        clearForm();
                        return;
                    }
                    var semester = updateSemester(t1);
                    if (semester) setDates(t1, semester);
                });
            });
        })();
    </script>
    @endpush
</x-layouts.admin>