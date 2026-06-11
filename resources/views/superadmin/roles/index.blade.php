<x-layouts.admin>
    <x-slot:title>Manage Roles</x-slot:title>

    <div class="container px-6 mx-auto">
        <div class="flex items-center justify-between my-6">
            <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
                Manage Roles & Permissions
            </h2>
            <a href="{{ route('superadmin.roles.create') }}"
                class="px-4 py-2 text-sm font-medium text-white bg-purple-600 rounded-lg hover:bg-purple-700">
                + Tambah Role
            </a>
        </div>

        @if(session('success'))
            <div class="px-4 py-3 mb-4 text-sm text-green-700 bg-green-100 rounded-lg dark:bg-green-800 dark:text-green-200">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="px-4 py-3 mb-4 text-sm text-red-700 bg-red-100 rounded-lg dark:bg-red-800 dark:text-red-200">
                {{ session('error') }}
            </div>
        @endif

        <div class="grid gap-4">
            @foreach($roles as $role)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xs overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200">
                                {{ ucwords(str_replace('_', ' ', $role->name)) }}
                            </h3>
                            <span class="text-sm text-gray-500">{{ $role->users_count ?? 0 }} user</span>
                        </div>
                        <div class="flex gap-2">
                            <a href="{{ route('superadmin.roles.edit', $role->id) }}"
                                class="px-3 py-1 text-xs font-medium text-white bg-blue-600 rounded hover:bg-blue-700">
                                Edit
                            </a>
                            @if(!in_array($role->name, ['super_admin', 'admin', 'guru', 'wali_kelas', 'siswa', 'orang_tua', 'user']))
                                <form method="POST" action="{{ route('superadmin.roles.destroy', $role->id) }}" class="inline"
                                    onsubmit="return confirm('Yakin hapus role ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="px-3 py-1 text-xs font-medium text-white bg-red-600 rounded hover:bg-red-700">
                                        Hapus
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                    <div class="p-5">
                        <div class="flex flex-wrap gap-2">
                            @forelse($role->permissions as $perm)
                                <span class="px-2 py-1 text-xs font-medium text-purple-700 bg-purple-100 rounded-full dark:bg-purple-800 dark:text-purple-200">
                                    {{ $perm->name }}
                                </span>
                            @empty
                                <span class="text-sm text-gray-400">Tidak ada permission</span>
                            @endforelse
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-layouts.admin>
