<x-layouts.admin>
    <x-slot:title>Mutasi Guru</x-slot:title>

    <div class="container px-6 mx-auto">
        <div class="my-6">
            <x-breadcrumb :items="[
                ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
                ['label' => 'Data Guru', 'url' => route('admin.guru.index')],
                ['label' => 'Mutasi Guru'],
            ]" />
            <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
                Mutasi Guru
            </h2>
        </div>

        <div class="max-w-lg p-6 bg-white rounded-lg shadow-xs dark:shadow-none dark:border dark:border-gray-700 dark:bg-gray-800">

            <div class="mb-6 p-4 bg-blue-50 rounded-lg dark:bg-blue-900/20">
                <p class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ $guru->nama_guru }}</p>
                <p class="text-xs text-gray-500">NIP: {{ $guru->nip ?? '-' }}</p>
                <p class="text-xs text-gray-500">Sekolah asal: {{ $instansi->nama_instansi }}</p>
                @if($guru->kelasWali()->exists())
                    <p class="text-xs text-orange-600 mt-1">⚠ Wali kelas akan dilepaskan</p>
                @endif
            </div>

            <form method="POST" action="{{ route('admin.guru.mutasi.proses', $guru) }}">
                @csrf

                <label class="block text-sm mb-4">
                    <span class="text-gray-700 dark:text-gray-400">Sekolah Tujuan</span>
                    <select name="instansi_tujuan"
                        class="block w-full mt-1 text-sm dark:bg-gray-700 dark:text-gray-300 @error('instansi_tujuan') border-red-500 @enderror">
                        <option value="">-- Pilih Sekolah --</option>
                        @foreach($sekolahTujuan as $s)
                            <option value="{{ $s->id_instansi }}" {{ old('instansi_tujuan') == $s->id_instansi ? 'selected' : '' }}>
                                {{ $s->nama_instansi }} ({{ $s->label_jenjang }})
                            </option>
                        @endforeach
                    </select>
                    @error('instansi_tujuan')
                        <span class="text-xs text-red-500">{{ $message }}</span>
                    @enderror
                </label>

                <div class="text-xs text-gray-500 mb-4">
                    Setelah disimpan: akan dibuat token 6 digit. Berikan token ke sekolah tujuan untuk dikonfirmasi.
                    Token berlaku 7 hari. Riwayat absensi & jadwal tetap tersimpan sebagai arsip.
                </div>

                <button type="submit"
                    class="w-full px-4 py-2 text-sm font-medium text-white bg-orange-600 rounded-lg hover:bg-orange-700">
                    Buat Token Mutasi
                </button>
            </form>
        </div>
    </div>
</x-layouts.admin>
