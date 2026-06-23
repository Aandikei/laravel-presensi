<x-layouts.admin>
    <x-slot:title>Poin Siswa - {{ $kelasSaya->nama_kelas }}</x-slot:title>

    <div class="container px-6 mx-auto">
        <div class="my-6">
            <x-breadcrumb :items="[
                ['label' => 'Dashboard', 'url' => route('guru.dashboard')],
                ['label' => 'Wali Kelas', 'url' => route('guru.wali-kelas.siswa-poin')],
                ['label' => 'Poin Siswa'],
            ]" />
            <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
                Poin Siswa
            </h2>
            <p class="text-gray-500 dark:text-gray-400 mt-1">
                Kelas {{ $kelasSaya->nama_kelas }}
            </p>
        </div>

        {{-- Form Tambah Poin --}}
        <div class="p-5 mb-6 bg-white dark:bg-gray-800 rounded-lg shadow-xs dark:shadow-none dark:border dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-4">Tambah Poin Pelanggaran</h3>
            <form method="POST" action="{{ route('guru.wali-kelas.tambah-poin') }}" class="flex flex-wrap items-end gap-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Siswa</label>
                    <select name="siswa_id" required
                        class="w-56 text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                        <option value="">Pilih Siswa</option>
                        @foreach($siswa as $s)
                            <option value="{{ $s->id_siswa }}">{{ $s->nama_siswa }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Pelanggaran</label>
                    <select name="poin_id" required
                        class="w-56 text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                        <option value="">Pilih Pelanggaran</option>
                        @foreach($masterPoin as $p)
                            <option value="{{ $p->id_poin }}">{{ $p->nama_pelanggaran }} ({{ $p->jumlah_poin }} poin)</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Keterangan</label>
                    <input type="text" name="keterangan" placeholder="Opsional..."
                        class="w-48 text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                </div>
                <button type="submit"
                    class="px-4 py-2 text-sm font-medium text-white bg-purple-600 rounded-lg hover:bg-purple-700">
                    Tambah Poin
                </button>
            </form>
        </div>

        {{-- Tabel Siswa --}}
        @if($siswa->isNotEmpty())
            <div class="w-full overflow-hidden rounded-lg shadow-xs">
                <div class="w-full overflow-x-auto bg-white dark:bg-gray-800 p-4">
                    <table id="tabel-poin" class="w-full whitespace-nowrap">
                        <thead>
                            <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b dark:border-gray-700 bg-gray-50 dark:text-gray-400 dark:bg-gray-900/50">
                                <th class="px-4 py-3">No</th>
                                <th class="px-4 py-3">NISN</th>
                                <th class="px-4 py-3">Nama Siswa</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3 text-center">Total Poin</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y dark:divide-gray-700 dark:bg-gray-800">
                            @foreach($siswa as $i => $s)
                                <tr class="text-gray-700 dark:text-gray-400">
                                    <td class="px-4 py-3 text-sm">{{ $i + 1 }}</td>
                                    <td class="px-4 py-3 text-sm">{{ $s->nisn }}</td>
                                    <td class="px-4 py-3 text-sm font-medium">{{ $s->nama_siswa }}</td>
                                    <td class="px-4 py-3">
                                        @if(!$s->isAktif())
                                            <span class="px-2 py-1 text-xs font-semibold text-red-700 bg-red-100 rounded-full dark:bg-red-900/30 dark:text-red-400">{{ $s->status_label }}</span>
                                        @else
                                            <span class="px-2 py-1 text-xs font-semibold text-green-700 bg-green-100 rounded-full dark:bg-green-900/30 dark:text-green-400">Aktif</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm text-center">
                                        @if($s->total_poin > 0)
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full
                                                {{ $s->total_poin >= 20 ? 'bg-red-100 text-red-700' : ($s->total_poin >= 10 ? 'bg-yellow-100 text-yellow-700' : 'bg-blue-100 text-blue-700') }}">
                                                {{ $s->total_poin }}
                                            </span>
                                        @else
                                            <span class="text-gray-400">0</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="w-full p-8 text-sm text-center text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 rounded-lg shadow-xs">
                Belum ada siswa di kelas ini.
            </div>
        @endif
    </div>

    @push('scripts')
    @if($siswa->isNotEmpty())
    <script>
        $(document).ready(function() {
            $('#tabel-poin').DataTable({
                paging: false,
                info: false,
                ordering: true,
                searching: true,
                language: { url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json' }
            });
        });
    </script>
    @endif
    @endpush
</x-layouts.admin>
