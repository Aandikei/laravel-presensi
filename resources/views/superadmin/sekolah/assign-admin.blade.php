<x-layouts.admin>
    <x-slot:title>Assign Admin - {{ $instansi->nama_instansi }}</x-slot:title>

    <div class="container px-6 mx-auto">
        <div class="my-6">
            <x-breadcrumb :items="[
                ['label' => 'Dashboard', 'url' => route('superadmin.dashboard')],
                ['label' => 'Kelola Sekolah', 'url' => route('superadmin.sekolah.index')],
                ['label' => 'Detail Sekolah', 'url' => route('superadmin.sekolah.show', $instansi->id_instansi)],
                ['label' => 'Admin'],
            ]" />
            <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
                Kelola Admin: {{ $instansi->nama_instansi }}
            </h2>
        </div>

        @if(session('success'))
            <div class="px-4 py-3 mb-4 text-sm text-green-700 bg-green-100 rounded-lg dark:bg-green-800 dark:text-green-200">
                {{ session('success') }}
            </div>
        @endif

        {{-- Daftar Admin --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xs dark:shadow-none dark:border dark:border-gray-700 overflow-hidden mb-6">
            <div class="px-5 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200">Admin Saat Ini</h3>
            </div>
            <div class="p-5">
                @if($currentAdmins->isEmpty())
                    <p class="text-sm text-gray-500 text-center py-4">Belum ada admin.</p>
                @else
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b dark:border-gray-700">
                                <th class="pb-2 px-2">Nama</th>
                                <th class="pb-2 px-2">Email</th>
                                <th class="pb-2 px-2">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y dark:divide-gray-700">
                            @foreach($currentAdmins as $admin)
                                <tr>
                                    <td class="py-2 px-2 text-gray-700 dark:text-gray-200">{{ $admin->name }}</td>
                                    <td class="py-2 px-2 text-gray-500">{{ $admin->email }}</td>
                                    <td class="py-2 px-2">
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

        {{-- Form Tambah Admin --}}
        <div class="max-w-lg p-6 bg-white rounded-lg shadow-xs dark:shadow-none dark:border dark:border-gray-700 dark:bg-gray-800">
            <h3 class="text-md font-semibold text-gray-700 dark:text-gray-200 mb-4">Tambah Admin Baru</h3>
            <form method="POST" action="{{ route('superadmin.sekolah.store-admin', $instansi->id_instansi) }}">
                @csrf

                <label class="block text-sm mb-4">
                    <span class="text-gray-700 dark:text-gray-400">Nama Admin</span>
                    <input type="text" name="name" value="{{ old('name') }}"
                        class="block w-full mt-1 text-sm form-input dark:bg-gray-700 dark:text-gray-300 @error('name') border-red-500 @enderror" />
                    @error('name')
                        <span class="text-xs text-red-500">{{ $message }}</span>
                    @enderror
                </label>

                <label class="block text-sm mb-4">
                    <span class="text-gray-700 dark:text-gray-400">Email Admin</span>
                    <input type="email" name="email" value="{{ old('email') }}"
                        class="block w-full mt-1 text-sm form-input dark:bg-gray-700 dark:text-gray-300 @error('email') border-red-500 @enderror" />
                    @error('email')
                        <span class="text-xs text-red-500">{{ $message }}</span>
                    @enderror
                </label>

                <label class="block text-sm mb-4">
                    <span class="text-gray-700 dark:text-gray-400">Password</span>
                    <input type="password" name="password"
                        class="block w-full mt-1 text-sm form-input dark:bg-gray-700 dark:text-gray-300 @error('password') border-red-500 @enderror" />
                    @error('password')
                        <span class="text-xs text-red-500">{{ $message }}</span>
                    @enderror
                </label>

                <button type="submit"
                    class="w-full px-4 py-2 text-sm font-medium text-white bg-purple-600 rounded-lg hover:bg-purple-700">
                    Tambah Admin
                </button>
            </form>
        </div>
    </div>
</x-layouts.admin>
