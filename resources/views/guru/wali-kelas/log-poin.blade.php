<x-layouts.admin>
    <x-slot:title>Log Poin - {{ $kelasSaya->nama_kelas }}</x-slot:title>

    <div class="container px-6 mx-auto">
        <div class="my-6">
            <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
                Log Poin Siswa
            </h2>
            <p class="text-gray-500 dark:text-gray-400 mt-1">
                Kelas {{ $kelasSaya->nama_kelas }}
            </p>
        </div>

        @if(session('success'))
            <div class="px-4 py-3 mb-4 text-sm text-green-700 bg-green-100 rounded-lg dark:bg-green-800 dark:text-green-200">
                {{ session('success') }}
            </div>
        @endif

        <div class="w-full overflow-hidden rounded-lg shadow-xs">
            <div class="w-full overflow-x-auto">
                <table class="w-full whitespace-no-wrap">
                    <thead>
                        <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b dark:border-gray-700 bg-gray-50 dark:text-gray-400 dark:bg-gray-800">
                            <th class="px-4 py-3">Tanggal</th>
                            <th class="px-4 py-3">Nama Siswa</th>
                            <th class="px-4 py-3">Pelanggaran</th>
                            <th class="px-4 py-3">Poin</th>
                            <th class="px-4 py-3">Keterangan</th>
                            <th class="px-4 py-3">Input Oleh</th>
                            <th class="px-4 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y dark:divide-gray-700 dark:bg-gray-800">
                        @forelse($logPoin as $log)
                            <tr class="text-gray-700 dark:text-gray-400">
                                <td class="px-4 py-3 text-sm">{{ \Carbon\Carbon::parse($log->tanggal)->locale('id')->isoFormat('D MMM YYYY') }}</td>
                                <td class="px-4 py-3 text-sm font-medium">{{ $log->siswa->nama_siswa ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm">{{ $log->masterPoin->nama_pelanggaran ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-700">
                                        {{ $log->masterPoin->jumlah_poin ?? 0 }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm">{{ $log->keterangan ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm">{{ $log->createdBy->name ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm text-center">
                                    <form method="POST" action="{{ route('guru.wali-kelas.hapus-poin', $log->id_log_poin) }}"
                                        onsubmit="return confirm('Hapus poin ini?')" class="inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800">
                                            <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-sm text-center text-gray-500 dark:text-gray-400">
                                    Belum ada catatan poin.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3 border-t dark:border-gray-700">
                {{ $logPoin->links() }}
            </div>
        </div>
    </div>
</x-layouts.admin>
