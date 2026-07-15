<div>
    <form method="POST" action="{{ route('profile.destroy') }}">
        @csrf
        @method('DELETE')

        <p class="mb-4 text-sm text-gray-600 dark:text-gray-400">
            Apakah kamu yakin ingin menghapus akun? Semua data akan hilang permanen dan tidak bisa dikembalikan.
        </p>

        @error('password', 'userDeletion')
            <span class="text-xs text-red-500 block mb-2">{{ $message }}</span>
        @enderror

        <button type="button" @click="confirmAction($event.currentTarget.closest('form'),
            'Masukkan password untuk mengkonfirmasi penghapusan akun. Tindakan ini tidak bisa dibatalkan.',
            'Ya, Hapus Akun', 'Hapus Akun', true)"
            class="px-4 py-2 text-sm font-medium text-white transition-colors duration-150 bg-red-600 rounded-lg hover:bg-red-700 focus:outline-none">
            Delete Account
        </button>
    </form>
</div>
