<x-layouts.admin>
    <x-slot:title>Edit Admin - {{ $user->name }}</x-slot:title>

    <div class="container px-6 mx-auto">
        <div class="my-6">
            <x-breadcrumb :items="[
                ['label' => 'Dashboard', 'url' => route('superadmin.dashboard')],
                ['label' => 'Kelola Sekolah', 'url' => route('superadmin.sekolah.index')],
                ['label' => 'Detail Sekolah', 'url' => route('superadmin.sekolah.show', $instansi->id_instansi)],
                ['label' => 'Edit Admin'],
            ]" />
            <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
                Edit Admin: {{ $user->name }}
            </h2>
        </div>

        @if($errors->any())
            <div class="px-4 py-3 mb-4 text-sm text-red-700 bg-red-100 rounded-lg dark:bg-red-800 dark:text-red-200">
                <ul class="list-disc pl-4">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="max-w-lg p-6 bg-white rounded-lg shadow-xs dark:shadow-none dark:border dark:border-gray-700 dark:bg-gray-800">
            <form method="POST" action="{{ route('superadmin.sekolah.update-admin', [$instansi->id_instansi, $user->id]) }}">
                @csrf
                @method('PUT')

                <label class="block text-sm mb-4">
                    <span class="text-gray-700 dark:text-gray-400">Nama Admin</span>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}"
                        class="block w-full mt-1 text-sm form-input dark:bg-gray-700 dark:text-gray-300 @error('name') border-red-500 @enderror" />
                </label>

                <label class="block text-sm mb-4">
                    <span class="text-gray-700 dark:text-gray-400">Email Admin</span>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}"
                        class="block w-full mt-1 text-sm form-input dark:bg-gray-700 dark:text-gray-300 @error('email') border-red-500 @enderror" />
                </label>

                <label class="block text-sm mb-4">
                    <span class="text-gray-700 dark:text-gray-400">
                        Password <span class="text-gray-400">(kosongkan jika tidak diganti)</span>
                    </span>
                    <input type="password" name="password"
                        class="block w-full mt-1 text-sm form-input dark:bg-gray-700 dark:text-gray-300 @error('password') border-red-500 @enderror" />
                </label>

                <div class="flex gap-2">
                    <button type="submit"
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                        Simpan
                    </button>
                    <a href="{{ route('superadmin.sekolah.assign-admin', $instansi->id_instansi) }}"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 dark:bg-gray-600 dark:text-gray-200 dark:hover:bg-gray-500">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-layouts.admin>
