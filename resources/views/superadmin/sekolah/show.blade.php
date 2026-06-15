<x-layouts.admin>
    <x-slot:title>Detail Sekolah</x-slot:title>

    <div class="container px-6 mx-auto">
        <div class="my-6">
            <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
                Detail Sekolah
            </h2>
            <a href="{{ route('superadmin.dashboard') }}"
                class="text-sm text-purple-600 hover:underline dark:text-purple-400">
                ← Kembali ke Dashboard
            </a>
        </div>

        {{-- Info Sekolah --}}
        <div class="grid gap-4 md:grid-cols-3 mb-6">
            <div class="flex items-center p-4 bg-white rounded-lg shadow-xs dark:shadow-none dark:border dark:border-gray-700 dark:bg-gray-800">
                <div class="p-3 mr-4 text-green-500 bg-green-100 rounded-full dark:text-green-100 dark:bg-green-500">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Guru</p>
                    <p class="text-lg font-semibold text-gray-700 dark:text-gray-200">{{ $instansi->guru_count }}</p>
                </div>
            </div>
            <div class="flex items-center p-4 bg-white rounded-lg shadow-xs dark:shadow-none dark:border dark:border-gray-700 dark:bg-gray-800">
                <div class="p-3 mr-4 text-orange-500 bg-orange-100 rounded-full dark:text-orange-100 dark:bg-orange-500">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838l-2.727 1.17 1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3zM3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762zM9.3 16.573A9.026 9.026 0 007 14.935v-3.957l1.818.78a3 3 0 002.364 0l5.508-2.361a11.026 11.026 0 01.25 3.762 1 1 0 01-.89.89 8.968 8.968 0 00-5.35 2.524 1 1 0 01-1.4 0zM6 18a1 1 0 001-1v-2.065a8.935 8.935 0 00-2-.712V17a1 1 0 001 1z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Siswa</p>
                    <p class="text-lg font-semibold text-gray-700 dark:text-gray-200">{{ $instansi->siswa_count }}</p>
                </div>
            </div>
            <div class="flex items-center p-4 bg-white rounded-lg shadow-xs dark:shadow-none dark:border dark:border-gray-700 dark:bg-gray-800">
                <div class="p-3 mr-4 text-blue-500 bg-blue-100 rounded-full dark:text-blue-100 dark:bg-blue-500">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 4.804A7.968 7.968 0 005.5 4c-1.255 0-2.443.29-3.5.804v10A7.969 7.969 0 015.5 14c1.669 0 3.218.51 4.5 1.385A7.962 7.962 0 0114.5 14c1.255 0 2.443.29 3.5.804v-10A7.968 7.968 0 0014.5 4c-1.255 0-2.443.29-3.5.804V12a1 1 0 11-2 0V4.804z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Kelas</p>
                    <p class="text-lg font-semibold text-gray-700 dark:text-gray-200">{{ $instansi->kelas_count }}</p>
                </div>
            </div>
        </div>

        {{-- Detail Info --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xs dark:shadow-none dark:border dark:border-gray-700 overflow-hidden mb-6">
            <div class="px-5 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200">Informasi Sekolah</h3>
            </div>
            <div class="p-5">
                <table class="w-full text-sm">
                    <tr class="border-b dark:border-gray-700">
                        <td class="py-3 font-medium text-gray-600 dark:text-gray-400 w-1/4">Nama</td>
                        <td class="py-3 text-gray-800 dark:text-gray-200">{{ $instansi->nama_instansi }}</td>
                    </tr>
                    <tr class="border-b dark:border-gray-700">
                        <td class="py-3 font-medium text-gray-600 dark:text-gray-400">Jenjang</td>
                        <td class="py-3">
                            <span class="px-2 py-1 text-xs font-medium text-purple-700 bg-purple-100 rounded-full">{{ $instansi->jenjang }}</span>
                        </td>
                    </tr>
                    <tr class="border-b dark:border-gray-700">
                        <td class="py-3 font-medium text-gray-600 dark:text-gray-400">NPSN</td>
                        <td class="py-3 text-gray-800 dark:text-gray-200">{{ $instansi->npsn }}</td>
                    </tr>
                    <tr class="border-b dark:border-gray-700">
                        <td class="py-3 font-medium text-gray-600 dark:text-gray-400">Alamat</td>
                        <td class="py-3 text-gray-800 dark:text-gray-200">{{ $instansi->alamat ?? '-' }}</td>
                    </tr>
                    <tr class="border-b dark:border-gray-700">
                        <td class="py-3 font-medium text-gray-600 dark:text-gray-400">Telepon</td>
                        <td class="py-3 text-gray-800 dark:text-gray-200">{{ $instansi->telepon ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="py-3 font-medium text-gray-600 dark:text-gray-400">Email</td>
                        <td class="py-3 text-gray-800 dark:text-gray-200">{{ $instansi->email ?? '-' }}</td>
                    </tr>
                </table>
            </div>
        </div>

        {{-- Admin Sekolah --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xs dark:shadow-none dark:border dark:border-gray-700 overflow-hidden mb-6">
            <div class="px-5 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200">Admin Sekolah</h3>
                <a href="{{ route('superadmin.sekolah.assign-admin', $instansi->id_instansi) }}"
                    class="px-3 py-1 text-xs font-medium text-white bg-purple-600 rounded hover:bg-purple-700">
                    + Tambah Admin
                </a>
            </div>
            <div class="p-5">
                @if($adminUsers->isEmpty())
                    <p class="text-sm text-gray-500 text-center py-4">Belum ada admin untuk sekolah ini.</p>
                @else
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b dark:border-gray-700">
                                <th class="pb-2">Nama</th>
                                <th class="pb-2">Email</th>
                                <th class="pb-2">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y dark:divide-gray-700">
                            @foreach($adminUsers as $admin)
                                <tr>
                                    <td class="py-2 text-gray-700 dark:text-gray-200">{{ $admin->name }}</td>
                                    <td class="py-2 text-gray-500">{{ $admin->email }}</td>
                                    <td class="py-2">
                                        <a href="{{ route('superadmin.sekolah.edit-admin', [$instansi->id_instansi, $admin->id]) }}"
                                            class="text-blue-600 hover:text-blue-800 mr-2" title="Edit">
                                            <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </a>
                                        <form method="POST" action="{{ route('superadmin.sekolah.delete-admin', [$instansi->id_instansi, $admin->id]) }}"
                                            class="inline"
                                            onsubmit="return confirm('Yakin hapus admin {{ $admin->name }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-800" title="Hapus">
                                                <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>

        {{-- Aksi --}}
        <div class="flex gap-2 mb-6">
            <a href="{{ route('superadmin.sekolah.edit', $instansi->id_instansi) }}"
                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                Edit Sekolah
            </a>
            <form method="POST" action="{{ route('superadmin.sekolah.destroy', $instansi->id_instansi) }}" class="inline"
                onsubmit="return confirm('YAKIN HAPUS PERMANEN?\n\nSemua data terkait sekolah ' + 'ini (siswa, guru, kelas, absensi, poin, laporan) akan ikut TERHAPUS PERMANEN.\nTindakan ini tidak bisa dibatalkan.')">
                @csrf
                @method('DELETE')
                <button type="submit"
                    class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700">
                    Hapus Sekolah
                </button>
            </form>
        </div>
    </div>
</x-layouts.admin>
