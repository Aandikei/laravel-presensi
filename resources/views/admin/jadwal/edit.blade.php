<x-layouts.admin>
    <x-slot:title>Edit Jadwal</x-slot:title>

    <div class="container px-6 mx-auto">
        <div class="my-6">
            <x-breadcrumb :items="[
                ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
                ['label' => 'Jadwal', 'url' => route('admin.jadwal.index')],
                ['label' => 'Edit'],
            ]" />
            <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">Edit Jadwal</h2>
        </div>
        <div class="max-w-lg p-6 bg-white rounded-lg shadow-xs dark:shadow-none dark:border dark:border-gray-700 dark:bg-gray-800">
            <form method="POST" action="{{ route('admin.jadwal.update', $jadwal) }}">
                @csrf
                @method('PUT')

                {{-- Info kelas & mapel saat ini --}}
                <div class="mb-4 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg text-sm text-gray-600 dark:text-gray-300">
                    <p><span class="font-medium">Kelas:</span> {{ $jadwal->kurikulum->kelas->nama_kelas }}</p>
                    <p><span class="font-medium">Mapel:</span> {{ $jadwal->kurikulum->mataPelajaran->nama_mapel }}</p>
                    <p><span class="font-medium">Guru:</span>
                        @if($guru = $jadwal->kurikulum?->guru)
                            {{ $guru->nama_guru }}
                            @if($guru->instansi_id !== auth()->user()->instansi_id)
                                <span class="px-2 py-1 text-xs font-medium text-orange-700 bg-orange-100 rounded-full">Mutasi</span>
                            @elseif($guru->status === 'Keluar')
                                <span class="px-2 py-1 text-xs font-medium text-red-700 bg-red-100 rounded-full">Keluar</span>
                            @elseif($guru->status === 'Pensiun')
                                <span class="px-2 py-1 text-xs font-medium text-gray-700 bg-gray-200 rounded-full">Pensiun</span>
                            @endif
                        @else
                            -
                        @endif
                    </p>
                </div>

                <input type="hidden" name="kurikulum_id" value="{{ $jadwal->kurikulum_id }}">

                <label class="block text-sm mb-4">
                    <span class="text-gray-700 dark:text-gray-400">Hari</span>
                    <select name="hari"
                        class="block w-full mt-1 text-sm dark:bg-gray-700 dark:text-gray-300">
                        @foreach(['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'] as $hari)
                            <option value="{{ $hari }}" {{ old('hari', $jadwal->hari) == $hari ? 'selected' : '' }}>{{ $hari }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="block text-sm mb-4">
                    <span class="text-gray-700 dark:text-gray-400">Jam Mulai</span>
                    <input type="time" name="jam_mulai"
                        value="{{ old('jam_mulai', substr($jadwal->jam_mulai, 0, 5)) }}"
                        class="block w-full mt-1 text-sm form-input dark:bg-gray-700 dark:text-gray-300 @error('jam_mulai') border-red-500 @enderror" />
                    @error('jam_mulai')
                        <span class="text-xs text-red-500">{{ $message }}</span>
                    @enderror
                </label>

                <label class="block text-sm mb-4">
                    <span class="text-gray-700 dark:text-gray-400">Jam Selesai</span>
                    <input type="time" name="jam_selesai"
                        value="{{ old('jam_selesai', substr($jadwal->jam_selesai, 0, 5)) }}"
                        class="block w-full mt-1 text-sm form-input dark:bg-gray-700 dark:text-gray-300 @error('jam_selesai') border-red-500 @enderror" />
                    @error('jam_selesai')
                        <span class="text-xs text-red-500">{{ $message }}</span>
                    @enderror
                </label>

                <button type="submit"
                    class="w-full px-4 py-2 text-sm font-medium text-white bg-purple-600 rounded-lg hover:bg-purple-700">
                    Update
                </button>
            </form>
        </div>
    </div>
</x-layouts.admin>