<x-layouts.admin>
    <x-slot:title>Tahun Ajaran</x-slot:title>

    <div class="container px-6 mx-auto">
        <div class="flex items-center justify-between my-6">
            <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
                Tahun Ajaran
            </h2>
            <a href="{{ route('admin.tahun-ajaran.create') }}"
                class="px-4 py-2 text-sm font-medium text-white bg-purple-600 rounded-lg hover:bg-purple-700">
                + Tambah
            </a>
        </div>

        {{-- Alert --}}
        @if (session('success'))
            <div
                class="px-4 py-3 mb-4 text-sm text-green-700 bg-green-100 rounded-lg dark:bg-green-800 dark:text-green-200">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="px-4 py-3 mb-4 text-sm text-red-700 bg-red-100 rounded-lg dark:bg-red-800 dark:text-red-200">
                {{ session('error') }}
            </div>
        @endif

        {{-- Table --}}
        <div class="w-full overflow-hidden rounded-lg shadow-xs">
            <div class="w-full overflow-x-auto bg-white dark:bg-gray-800">
                <table class="w-full whitespace-nowrap">
                    <thead>
                        <tr
                            class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b dark:border-gray-700 bg-gray-50 dark:text-gray-400 dark:bg-gray-900/50">
                            <th class="px-4 py-3">Tahun Ajaran</th>
                            <th class="px-4 py-3">Semester</th>
                            <th class="px-4 py-3">Tanggal Mulai</th>
                            <th class="px-4 py-3">Tanggal Selesai</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y dark:divide-gray-700 dark:bg-gray-800">
                        @forelse($tahunAjaran as $item)
                            <tr class="text-gray-700 dark:text-gray-400">
                                <td class="px-4 py-3 font-medium">{{ $item->nama_tahun }}</td>
                                <td class="px-4 py-3">{{ $item->semester }}</td>
                                <td class="px-4 py-3">{{ $item->tanggal_mulai->format('d M Y') }}</td>
                                <td class="px-4 py-3">{{ $item->tanggal_selesai->format('d M Y') }}</td>
                                <td class="px-4 py-3">
                                    @if ($item->is_aktif)
                                        <span
                                            class="px-2 py-1 text-xs font-medium text-green-700 bg-green-100 rounded-full dark:bg-green-800 dark:text-green-200">
                                            Aktif
                                        </span>
                                    @else
                                        <span
                                            class="px-2 py-1 text-xs font-medium text-gray-700 bg-gray-100 rounded-full dark:bg-gray-700 dark:text-gray-400">
                                            Nonaktif
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        {{-- Aktivasi --}}
                                        @if (!$item->is_aktif)
                                            <form method="POST"
                                                action="{{ route('admin.tahun-ajaran.aktivasi', $item) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit"
                                                    class="px-3 py-1 text-xs font-medium text-green-700 bg-green-100 rounded hover:bg-green-700 focus:outline-none"
                                                    onclick="return confirm('Aktifkan tahun ajaran ini?')">
                                                    Aktifkan
                                                </button>
                                            </form>
                                        @endif

                                        {{-- Edit --}}
                                        <a href="{{ route('admin.tahun-ajaran.edit', $item) }}"
                                            title="Edit"
                                            class="text-blue-600 hover:text-blue-800 mr-2">
                                            <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </a>

                                        {{-- Hapus --}}
                                        @if (!$item->is_aktif)
                                            <form method="POST"
                                                action="{{ route('admin.tahun-ajaran.destroy', $item) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    title="Hapus"
                                                    class="text-red-600 hover:text-red-800"
                                                    onclick="return confirm('Yakin hapus tahun ajaran ini?')">
                                                    <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                    Belum ada tahun ajaran.
                                    <a href="{{ route('admin.tahun-ajaran.create') }}"
                                        class="text-purple-600 hover:underline">Tambah sekarang</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.admin>
