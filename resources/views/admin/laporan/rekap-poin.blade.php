<x-layouts.admin>
    <x-slot:title>Rekap Poin</x-slot:title>

    <div class="container px-6 mx-auto">
        <div class="flex items-center justify-between my-6">
            <div>
                <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">Rekap Poin Pelanggaran</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    {{ ucfirst($bulanNama) }} {{ $request->tahun }}
                </p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.laporan.export-poin-excel', $request->all()) }}"
                    class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700">
                    Excel
                </a>
                <a href="{{ route('admin.laporan.export-poin-pdf', $request->all()) }}"
                    class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700">
                    PDF
                </a>
                <a href="{{ route('admin.laporan.index') }}"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300">
                    ← Kembali
                </a>
            </div>
        </div>

        <div class="w-full overflow-x-auto bg-white dark:bg-gray-800 rounded-lg shadow-xs dark:shadow-none dark:border dark:border-gray-700">
            <table class="w-full">
                <thead>
                    <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b dark:border-gray-700 bg-gray-50 dark:text-gray-400 dark:bg-gray-900/50">
                        <th class="px-4 py-3">No</th>
                        <th class="px-4 py-3">Nama Siswa</th>
                        <th class="px-4 py-3">NISN</th>
                        <th class="px-4 py-3 text-center">Pelanggaran</th>
                        <th class="px-4 py-3 text-center">Total Poin</th>
                        <th class="px-4 py-3 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y dark:divide-gray-700">
                    @forelse($siswa as $i => $s)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/70 transition-colors">
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ $i + 1 }}</td>
                            <td class="px-4 py-3 font-medium text-gray-700 dark:text-gray-200">{{ $s->nama_siswa }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $s->nisn }}</td>
                            <td class="px-4 py-3 text-center text-gray-700 dark:text-gray-200">{{ $s->jumlah_pelanggaran }}</td>
                            <td class="px-4 py-3 text-center font-bold
                                {{ $s->total_poin >= 100 ? 'text-red-600' : ($s->total_poin >= 50 ? 'text-yellow-600' : 'text-green-600') }}">
                                {{ $s->total_poin }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="px-2 py-1 text-xs font-medium rounded-full
                                    {{ $s->status_poin == 'PERHATIAN' ? 'text-red-700 bg-red-100' :
                                       ($s->status_poin == 'WASPADA' ? 'text-yellow-700 bg-yellow-100' : 'text-green-700 bg-green-100') }}">
                                    {{ $s->status_poin }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                Tidak ada data poin untuk periode ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.admin>