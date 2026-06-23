<x-layouts.admin>
    <x-slot:title>Tambah Role</x-slot:title>

    <div class="container px-6 mx-auto">
        <div class="my-6">
            <x-breadcrumb :items="[
                ['label' => 'Dashboard', 'url' => route('superadmin.dashboard')],
                ['label' => 'Roles', 'url' => route('superadmin.roles.index')],
                ['label' => 'Tambah Role'],
            ]" />
            <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
                Tambah Role Baru
            </h2>
        </div>

        <div class="max-w-2xl p-6 bg-white rounded-lg shadow-xs dark:shadow-none dark:border dark:border-gray-700 dark:bg-gray-800">
            <form method="POST" action="{{ route('superadmin.roles.store') }}">
                @csrf

                <label class="block text-sm mb-4">
                    <span class="text-gray-700 dark:text-gray-400">Nama Role</span>
                    <input type="text" name="name" value="{{ old('name') }}"
                        class="block w-full mt-1 text-sm form-input dark:bg-gray-700 dark:text-gray-300 @error('name') border-red-500 @enderror" />
                    @error('name')
                        <span class="text-xs text-red-500">{{ $message }}</span>
                    @enderror
                </label>

                <h3 class="text-md font-semibold text-gray-700 dark:text-gray-200 mb-4 mt-6 border-b pb-2">Permissions</h3>

                <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                    @foreach($permissions as $perm)
                        <label class="flex items-center gap-2 text-sm p-2 rounded hover:bg-gray-50 dark:hover:bg-gray-700/70">
                            <input type="checkbox" name="permissions[]" value="{{ $perm->id }}"
                                class="form-checkbox text-purple-600"
                                {{ in_array($perm->id, old('permissions', [])) ? 'checked' : '' }}>
                            <span class="text-gray-700 dark:text-gray-300">{{ $perm->name }}</span>
                        </label>
                    @endforeach
                </div>

                <button type="submit"
                    class="w-full mt-6 px-4 py-2 text-sm font-medium text-white bg-purple-600 rounded-lg hover:bg-purple-700">
                    Simpan Role
                </button>
            </form>
        </div>
    </div>
</x-layouts.admin>
