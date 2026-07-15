<x-layouts.admin>
    <x-slot:title>Dashboard Wakil Kepala Sekolah</x-slot:title>

    <div class="container px-6 mx-auto">
        <div class="my-6">
            <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
                Selamat datang, {{ $guru->nama_guru }}!
            </h2>
            <p class="text-gray-500 dark:text-gray-400 mt-1">
                {{ $instansi->nama_instansi }} — {{ $instansi->label_jenjang }}
            </p>
        </div>

        <div class="grid gap-6 mb-8 md:grid-cols-2 xl:grid-cols-4">
            <div class="flex items-center p-5 bg-white dark:bg-gray-800 rounded-lg shadow-xs dark:shadow-none dark:border dark:border-gray-700">
                <div class="p-3 mr-4 text-blue-500 bg-blue-100 rounded-full dark:text-blue-100 dark:bg-blue-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <div>
                    <p class="mb-2 text-sm font-medium text-gray-600 dark:text-gray-400">Total Guru</p>
                    <p class="text-2xl font-bold text-gray-700 dark:text-gray-200">{{ $totalGuru }}</p>
                </div>
            </div>

            <div class="flex items-center p-5 bg-white dark:bg-gray-800 rounded-lg shadow-xs dark:shadow-none dark:border dark:border-gray-700">
                <div class="p-3 mr-4 text-green-500 bg-green-100 rounded-full dark:text-green-100 dark:bg-green-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </div>
                <div>
                    <p class="mb-2 text-sm font-medium text-gray-600 dark:text-gray-400">Siswa Aktif</p>
                    <p class="text-2xl font-bold text-gray-700 dark:text-gray-200">{{ $totalSiswaAktif }}</p>
                </div>
            </div>

            <div class="flex items-center p-5 bg-white dark:bg-gray-800 rounded-lg shadow-xs dark:shadow-none dark:border dark:border-gray-700">
                <div class="p-3 mr-4 text-purple-500 bg-purple-100 rounded-full dark:text-purple-100 dark:bg-purple-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </div>
                <div>
                    <p class="mb-2 text-sm font-medium text-gray-600 dark:text-gray-400">Total Kelas</p>
                    <p class="text-2xl font-bold text-gray-700 dark:text-gray-200">{{ $totalKelas }}</p>
                </div>
            </div>

            <div class="flex items-center p-5 bg-white dark:bg-gray-800 rounded-lg shadow-xs dark:shadow-none dark:border dark:border-gray-700">
                <div class="p-3 mr-4 text-yellow-500 bg-yellow-100 rounded-full dark:text-yellow-100 dark:bg-yellow-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                </div>
                <div>
                    <p class="mb-2 text-sm font-medium text-gray-600 dark:text-gray-400">Absensi Hari Ini</p>
                    <p class="text-2xl font-bold text-gray-700 dark:text-gray-200">{{ $absensiHariIni }}</p>
                </div>
            </div>
        </div>

        <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-4">Data Sekolah</h3>
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4 mb-6">
            <a href="{{ route('admin.guru.index') }}"
                class="flex items-center gap-4 p-5 bg-white dark:bg-gray-800 rounded-lg shadow-xs dark:shadow-none dark:border dark:border-gray-700 hover:bg-blue-50 dark:hover:bg-gray-700 transition-colors">
                <div class="p-3 text-blue-500 bg-blue-100 rounded-full">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <div>
                    <p class="font-semibold text-gray-700 dark:text-gray-200">Data Guru</p>
                    <p class="text-sm text-gray-500">Lihat daftar guru</p>
                </div>
            </a>

            <a href="{{ route('admin.siswa.index') }}"
                class="flex items-center gap-4 p-5 bg-white dark:bg-gray-800 rounded-lg shadow-xs dark:shadow-none dark:border dark:border-gray-700 hover:bg-green-50 dark:hover:bg-gray-700 transition-colors">
                <div class="p-3 text-green-500 bg-green-100 rounded-full">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </div>
                <div>
                    <p class="font-semibold text-gray-700 dark:text-gray-200">Data Siswa</p>
                    <p class="text-sm text-gray-500">Lihat daftar siswa</p>
                </div>
            </a>

            <a href="{{ route('admin.kelas.index') }}"
                class="flex items-center gap-4 p-5 bg-white dark:bg-gray-800 rounded-lg shadow-xs dark:shadow-none dark:border dark:border-gray-700 hover:bg-purple-50 dark:hover:bg-gray-700 transition-colors">
                <div class="p-3 text-purple-500 bg-purple-100 rounded-full">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </div>
                <div>
                    <p class="font-semibold text-gray-700 dark:text-gray-200">Data Kelas</p>
                    <p class="text-sm text-gray-500">Lihat daftar kelas</p>
                </div>
            </a>

            <a href="{{ route('guru.absensi.rekap') }}"
                class="flex items-center gap-4 p-5 bg-white dark:bg-gray-800 rounded-lg shadow-xs dark:shadow-none dark:border dark:border-gray-700 hover:bg-yellow-50 dark:hover:bg-gray-700 transition-colors">
                <div class="p-3 text-yellow-500 bg-yellow-100 rounded-full">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                </div>
                <div>
                    <p class="font-semibold text-gray-700 dark:text-gray-200">Rekap Absensi</p>
                    <p class="text-sm text-gray-500">Lihat laporan kehadiran</p>
                </div>
            </a>
        </div>

    </div>
</x-layouts.admin>
