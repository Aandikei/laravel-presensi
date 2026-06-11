<x-layouts.admin>
    <x-slot:title>Edit Role</x-slot:title>

    <div class="container px-6 mx-auto">
        <div class="my-6">
            <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
                Edit Role: {{ ucwords(str_replace('_', ' ', $role->name)) }}
            </h2>
            <a href="{{ route('superadmin.roles.index') }}"
                class="text-sm text-purple-600 hover:underline dark:text-purple-400">
                ← Kembali
            </a>
        </div>

        <div class="max-w-2xl p-6 bg-white rounded-lg shadow-xs dark:bg-gray-800">
            <form method="POST" action="{{ route('superadmin.roles.update', $role->id) }}">
                @csrf
                @method('PUT')

                <label class="block text-sm mb-4">
                    <span class="text-gray-700 dark:text-gray-400">Nama Role</span>
                    <input type="text" name="name" value="{{ old('name', $role->name) }}"
                        class="block w-full mt-1 text-sm form-input dark:bg-gray-700 dark:text-gray-300 @error('name') border-red-500 @enderror"
                        {{ in_array($role->name, ['super_admin', 'admin', 'guru', 'wali_kelas', 'siswa', 'orang_tua', 'user']) ? 'readonly' : '' }} />
                    @error('name')
                        <span class="text-xs text-red-500">{{ $message }}</span>
                    @enderror
                </label>

                <h3 class="text-md font-semibold text-gray-700 dark:text-gray-200 mb-4 mt-6 border-b pb-2">Permissions</h3>

                <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                    @foreach($permissions as $perm)
                        <label class="flex items-center gap-2 text-sm p-2 rounded hover:bg-gray-50 dark:hover:bg-gray-700">
                            <input type="checkbox" name="permissions[]" value="{{ $perm->id }}"
                                class="form-checkbox text-purple-600"
                                {{ $role->permissions->contains('id', $perm->id) ? 'checked' : '' }}>
                            <span class="text-gray-700 dark:text-gray-300">{{ $perm->name }}</span>
                        </label>
                    @endforeach
                </div>

                <button type="submit"
                    class="w-full mt-6 px-4 py-2 text-sm font-medium text-white bg-purple-600 rounded-lg hover:bg-purple-700">
                    Simpan Perubahan
                </button>
            </form>
        </div>
    </div>
</x-layouts.admin>
