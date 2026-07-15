<x-layouts.admin>
    <x-slot:title>Konfirmasi Mutasi Guru</x-slot:title>

    <div class="container px-6 mx-auto">
        <div class="my-6">
            <x-breadcrumb :items="[
                ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
                ['label' => 'Terima Mutasi Guru', 'url' => route('admin.guru.mutasi.terima')],
                ['label' => 'Konfirmasi'],
            ]" />
            <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
                Konfirmasi Mutasi Guru
            </h2>
        </div>

        <div class="max-w-lg p-6 bg-white rounded-lg shadow-xs dark:shadow-none dark:border dark:border-gray-700 dark:bg-gray-800">
            <div class="mb-6 p-4 bg-blue-50 rounded-lg dark:bg-blue-900/20">
                <p class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ $guru->nama_guru }}</p>
                <p class="text-xs text-gray-500">NIP: {{ $guru->nip ?? '-' }}</p>
                <p class="text-xs text-gray-500">Sekolah asal: {{ $instansiAsal->nama_instansi }} ({{ $instansiAsal->jenjang }})</p>
                <p class="text-xs text-gray-500">Sekolah tujuan: {{ $instansiTujuan->nama_instansi }} ({{ $instansiTujuan->jenjang }})</p>
            </div>

            <form method="POST" action="{{ route('admin.guru.mutasi.terima.proses') }}">
                @csrf
                <input type="hidden" name="guru_id" value="{{ $guru->id_guru }}">
                <input type="hidden" name="token" value="{{ $guru->transfer_token }}">

                <div class="text-xs text-gray-500 mb-4">
                    Setelah dikonfirmasi: guru akan dilepaskan dari jadwal mengajar di sekolah asal.
                    Riwayat absensi tetap tersimpan. Guru akan pindah ke {{ $instansiTujuan->nama_instansi }}.
                </div>

                <button type="button" @click="confirmAction($event.currentTarget.closest('form'),
                    'Yakin menerima mutasi {{ $guru->nama_guru }} ke sekolah ini?',
                    'Ya, Terima')"
                    class="w-full px-4 py-2 text-sm font-medium text-white bg-orange-600 rounded-lg hover:bg-orange-700">
                    Konfirmasi Terima Mutasi
                </button>
            </form>
        </div>
    </div>
</x-layouts.admin>
