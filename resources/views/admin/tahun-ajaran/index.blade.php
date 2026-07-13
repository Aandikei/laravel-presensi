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

        {{-- Table --}}
        <div class="w-full overflow-hidden rounded-lg shadow-xs">
            <div class="w-full overflow-x-auto bg-white dark:bg-gray-800 p-4">
                <table id="tabel-tahun-ajaran" class="w-full whitespace-nowrap">
                    <thead>
                        <tr
                            class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b dark:border-gray-700 bg-gray-50 dark:text-gray-400 dark:bg-gray-900/50">
                            <th class="px-6 py-4">Tahun Ajaran</th>
                            <th class="px-6 py-4">Semester</th>
                            <th class="px-6 py-4">Tanggal Mulai</th>
                            <th class="px-6 py-4">Tanggal Selesai</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y dark:divide-gray-700 dark:bg-gray-800">
                        @foreach($tahunAjaran as $item)
                            <tr class="text-gray-700 dark:text-gray-400">
                                <td class="px-6 py-4 font-medium">{{ $item->nama_tahun }}</td>
                                <td class="px-6 py-4">
                                    @if ($item->semester == 'Ganjil')
                                        <span
                                            class="px-2 py-1 text-xs font-medium text-blue-700 bg-blue-100 rounded-full dark:bg-blue-800 dark:text-blue-200">Ganjil</span>
                                    @else
                                        <span
                                            class="px-2 py-1 text-xs font-medium text-orange-700 bg-orange-100 rounded-full dark:bg-orange-800 dark:text-orange-200">Genap</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">{{ $item->tanggal_mulai->format('d M Y') }}</td>
                                <td class="px-6 py-4">{{ $item->tanggal_selesai->format('d M Y') }}</td>
                                <td class="px-6 py-4">
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
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        @if (!$item->is_aktif && $item->can_activate)
                                            <form method="POST"
                                                action="{{ route('admin.tahun-ajaran.aktivasi', $item) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit"
                                                    title="Aktifkan"
                                                    class="text-green-600 hover:text-green-800"
                                                    onclick="return confirm('Aktifkan tahun ajaran ini?')">
                                                    <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                </button>
                                            </form>
                                        @endif

                                        <a href="{{ route('admin.tahun-ajaran.edit', $item) }}"
                                            title="Edit"
                                            class="text-blue-600 hover:text-blue-800">
                                            <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </a>

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
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                $('#tabel-tahun-ajaran').DataTable({
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
                    },
                    order: []
                });
            });
        </script>
    @endpush
</x-layouts.admin>
