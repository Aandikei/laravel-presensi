{{-- <section class="space-y-6">
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Delete Account') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}
        </p>
    </header>

    <x-danger-button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
    >{{ __('Delete Account') }}</x-danger-button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            <h2 class="text-lg font-medium text-gray-900">
                {{ __('Are you sure you want to delete your account?') }}
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}
            </p>

            <div class="mt-6">
                <x-input-label for="password" value="{{ __('Password') }}" class="sr-only" />

                <x-text-input
                    id="password"
                    name="password"
                    type="password"
                    class="mt-1 block w-3/4"
                    placeholder="{{ __('Password') }}"
                />

                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div class="mt-6 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">
                    {{ __('Cancel') }}
                </x-secondary-button>

                <x-danger-button class="ms-3">
                    {{ __('Delete Account') }}
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</section> --}}


<div x-data="{ open: false }">

    {{-- Trigger button --}}
    <button @click="open = true"
        class="px-4 py-2 text-sm font-medium text-white transition-colors duration-150 bg-red-600 rounded-lg hover:bg-red-700 focus:outline-none">
        Delete Account
    </button>

    {{-- Modal konfirmasi --}}
    <div x-show="open"
        class="fixed inset-0 z-30 flex items-end bg-black bg-opacity-50 sm:items-center sm:justify-center">
        <div
            class="w-full px-6 py-4 overflow-hidden bg-white rounded-t-lg dark:bg-gray-800 sm:rounded-lg sm:m-4 sm:max-w-xl">

            <h3 class="mb-2 text-lg font-semibold text-gray-700 dark:text-gray-200">
                Hapus Akun
            </h3>
            <p class="mb-6 text-sm text-gray-600 dark:text-gray-400">
                Apakah kamu yakin ingin menghapus akun? Semua data akan hilang permanen dan tidak bisa dikembalikan.
            </p>

            <form method="POST" action="{{ route('profile.destroy') }}">
                @csrf
                @method('DELETE')

                {{-- Password konfirmasi --}}
                <label class="block text-sm">
                    <span class="text-gray-700 dark:text-gray-400">Masukkan password untuk konfirmasi</span>
                    <input
                        class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 form-input @error('password') border-red-500 @enderror"
                        type="password" name="password" placeholder="***************" />
                    @error('password', 'userDeletion')
                        <span class="text-xs text-red-500 mt-1">{{ $message }}</span>
                    @enderror
                </label>

                <div class="flex mt-6 gap-3">
                    <button type="submit"
                        class="px-4 py-2 text-sm font-medium text-white transition-colors duration-150 bg-red-600 rounded-lg hover:bg-red-700 focus:outline-none">
                        Ya, Hapus Akun
                    </button>
                    <button type="button" @click="open = false"
                        class="px-4 py-2 text-sm font-medium text-gray-700 transition-colors duration-150 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-400 focus:outline-none">
                        Batal
                    </button>
                </div>

            </form>
        </div>
    </div>

</div>
