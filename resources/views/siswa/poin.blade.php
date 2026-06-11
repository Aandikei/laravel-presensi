<x-layouts.admin>
    <x-slot:title>Riwayat Poin</x-slot:title>

    <div class="container px-6 mx-auto">
        <div class="my-6">
            <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">Riwayat Poin Pelanggaran</h2>
        </div>

        {{-- Filter --}}
        <form method="GET" action="{{ route('siswa.poin') }}"
            class="mb-4 p-4 bg-white dark:bg-gray-800 rounded-lg shadow-xs dark:shadow-none dark:border dark:border-gray-700 flex flex-wrap gap-4 items-end">
            <label class="block text-sm">
                <span class="text-gray-700 dark:text-gray-400">Bulan</span>
                <select name="bulan" class="block mt-1 text-sm dark:bg-gray-700 dark:text-gray-300">
                    @for($i = 1; $i <= 12; $i++)
                        <option value="{{ $i }}" {{ $bulan == $i ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create()->month($i)->locale('id')->monthName }}
                        </option>
                    @endfor
                </select>
            </label>
            <label class="block text-sm">
                <span class="text-gray-700 dark:text-gray-400">Tahun</span>
                <select name="tahun" class="block mt-1 text-sm dark:bg-gray-700 dark:text-gray-300">
                    @for($y = now()->year; $y >= 2020; $y--)
                        <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </label>
            <button type="submit"
                class="px-4 py-2 text-sm font-medium text-white bg-purple-600 rounded-lg hover:bg-purple-700">
                Filter
            </button>
        </form>

        {{-- Total Poin --}}
        <div class="mb-4 p-5 bg-white dark:bg-gray-800 rounded-lg shadow-xs dark:shadow-none dark:border dark:border-gray-700 text-center">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Poin {{ ucfirst($bulanNama) }} {{ $tahun }}</p>
            <p class="text-5xl font-bold mt-2 {{ $totalPoin >= 100 ? 'text-red-600' : ($totalPoin >= 50 ? 'text-yellow-600' : 'text-green-600') }}">
                {{ $totalPoin }}
            </p>
            <p class="text-sm mt-2 {{ $totalPoin >= 100 ? 'text-red-600' : ($totalPoin >= 50 ? 'text-yellow-600' : 'text-green-600') }}">
                {{ $totalPoin >= 100 ? '⚠️ Perhatian! Poin sudah melebihi batas' : ($totalPoin >= 50 ? '⚠️ Waspada! Poin mendekati batas' : '✅ Poin aman') }}
            </p>
        </div>

        {{-- Table --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xs dark:shadow-none dark:border dark:border-gray-700 overflow-hidden">
            <table class="w-full">
                <thead>
                    <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50">
                        <th class="px-5 py-3">Tanggal</th>
                        <th class="px-5 py-3">Pelanggaran</th>
                        <th class="px-5 py-3">Poin</th>
                        <th class="px-5 py-3">Keterangan</th>
                        <th class="px-5 py-3">Dicatat Oleh</th>
                    </tr>
                </thead>
                <tbody class="divide-y dark:divide-gray-700">
                    @forelse($logPoin as $log)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/70">
                            <td class="px-5 py-3 text-sm text-gray-600 dark:text-gray-400">
                                {{ \Carbon\Carbon::parse($log->tanggal)->format('d M Y') }}
                            </td>
                            <td class="px-5 py-3 text-sm font-medium text-gray-700 dark:text-gray-200">
                                {{ $log->masterPoin->nama_pelanggaran }}
                            </td>
                            <td class="px-5 py-3">
                                <span class="px-2 py-1 text-xs font-bold text-red-700 bg-red-100 rounded-full">
                                    +{{ $log->masterPoin->jumlah_poin }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-sm text-gray-500">{{ $log->keterangan ?? '-' }}</td>
                            <td class="px-5 py-3 text-sm text-gray-500">{{ $log->createdBy->name ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-8 text-center text-gray-500">
                                Tidak ada pelanggaran bulan ini 🎉
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.admin>