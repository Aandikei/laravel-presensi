<x-layouts.admin>
    <x-slot:title>Dashboard</x-slot:title>

    <div class="container px-6 mx-auto">
        <div class="my-6">
            <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">Dashboard Admin</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                {{ now()->locale('id')->isoFormat('dddd, D MMMM Y') }}
            </p>
        </div>

        {{-- Alert Hari Libur --}}
        @if($namaLibur)
            <div class="mb-6 px-4 py-3 text-sm text-blue-700 bg-blue-100 rounded-lg dark:bg-blue-800 dark:text-blue-200 flex items-center gap-2">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Hari ini libur: <strong>{{ $namaLibur }}</strong>
            </div>
        @endif

        {{-- Stats Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">

            {{-- Total Guru --}}
            <div class="p-5 bg-white dark:bg-gray-800 rounded-lg shadow-xs border-l-4 border-blue-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Guru</p>
                        <p class="text-3xl font-bold text-gray-700 dark:text-gray-200 mt-1">{{ $totalGuru }}</p>
                    </div>
                    <div class="p-3 bg-blue-100 dark:bg-blue-900/30 rounded-full">
                        <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Total Siswa --}}
            <div class="p-5 bg-white dark:bg-gray-800 rounded-lg shadow-xs border-l-4 border-green-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Siswa</p>
                        <p class="text-3xl font-bold text-gray-700 dark:text-gray-200 mt-1">{{ $totalSiswa }}</p>
                    </div>
                    <div class="p-3 bg-green-100 dark:bg-green-900/30 rounded-full">
                        <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Total Kelas --}}
            <div class="p-5 bg-white dark:bg-gray-800 rounded-lg shadow-xs border-l-4 border-purple-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Kelas</p>
                        <p class="text-3xl font-bold text-gray-700 dark:text-gray-200 mt-1">{{ $totalKelas }}</p>
                        <p class="text-xs text-gray-400 mt-1">Tahun ajaran aktif</p>
                    </div>
                    <div class="p-3 bg-purple-100 dark:bg-purple-900/30 rounded-full">
                        <svg class="w-6 h-6 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Kehadiran Hari Ini --}}
            <div class="p-5 bg-white dark:bg-gray-800 rounded-lg shadow-xs border-l-4
                {{ $persenHadir >= 75 ? 'border-green-500' : ($persenHadir >= 50 ? 'border-yellow-500' : 'border-red-500') }}">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Kehadiran Hari Ini</p>
                        <p class="text-3xl font-bold text-gray-700 dark:text-gray-200 mt-1">{{ $persenHadir }}%</p>
                        <p class="text-xs text-gray-400 mt-1">{{ $totalHadir }}/{{ $totalAbsensi }} siswa hadir</p>
                    </div>
                    <div class="p-3 rounded-full
                        {{ $persenHadir >= 75 ? 'bg-green-100 dark:bg-green-900/30' : ($persenHadir >= 50 ? 'bg-yellow-100 dark:bg-yellow-900/30' : 'bg-red-100 dark:bg-red-900/30') }}">
                        <svg class="w-6 h-6 {{ $persenHadir >= 75 ? 'text-green-500' : ($persenHadir >= 50 ? 'text-yellow-500' : 'text-red-500') }}"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- Charts Row --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">

            {{-- Line Chart --}}
            <div class="lg:col-span-2 p-5 bg-white dark:bg-gray-800 rounded-lg shadow-xs">
                <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-4">
                    Tren Kehadiran 7 Hari Terakhir
                </h3>
                <canvas id="chartKehadiran" height="120"></canvas>
            </div>

            {{-- Donut Chart --}}
            <div class="p-5 bg-white dark:bg-gray-800 rounded-lg shadow-xs">
                <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-4">
                    Distribusi Status Hari Ini
                </h3>
                @if($totalAbsensi > 0)
                    <canvas id="chartDistribusi" height="200"></canvas>
                    <div class="mt-4 space-y-2">
                        @foreach(['Hadir' => 'green', 'Sakit' => 'blue', 'Izin' => 'yellow', 'Alpa' => 'red', 'Terlambat' => 'orange', 'Bolos' => 'pink'] as $status => $color)
                            @if(($distribusi[$status] ?? 0) > 0)
                                <div class="flex items-center justify-between text-sm">
                                    <div class="flex items-center gap-2">
                                        <span class="w-3 h-3 rounded-full bg-{{ $color }}-500 inline-block"></span>
                                        <span class="text-gray-600 dark:text-gray-400">{{ $status }}</span>
                                    </div>
                                    <span class="font-semibold text-gray-700 dark:text-gray-200">{{ $distribusi[$status] ?? 0 }}</span>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @else
                    <div class="flex items-center justify-center h-40 text-gray-400 dark:text-gray-500">
                        <p class="text-sm">Belum ada data hari ini</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Absensi Bermasalah Hari Ini --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xs overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200">
                        Absensi Bermasalah Hari Ini
                    </h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Alpa, Bolos, Terlambat</p>
                </div>
                <a href="{{ route('admin.absensi.index') }}"
                    class="text-sm text-purple-600 dark:text-purple-400 hover:underline">
                    Lihat semua →
                </a>
            </div>

            @if($absensiTerbaru->isEmpty())
                <div class="px-5 py-8 text-center text-gray-500 dark:text-gray-400">
                    <svg class="w-12 h-12 mx-auto mb-3 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-sm">Tidak ada absensi bermasalah hari ini 🎉</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b dark:border-gray-700 bg-gray-50 dark:bg-gray-800">
                                <th class="px-5 py-3">Siswa</th>
                                <th class="px-5 py-3">Kelas</th>
                                <th class="px-5 py-3">Mata Pelajaran</th>
                                <th class="px-5 py-3">Status</th>
                                <th class="px-5 py-3">Durasi</th>
                                <th class="px-5 py-3">Jam</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y dark:divide-gray-700">
                            @foreach($absensiTerbaru as $absen)
                                @php
                                    $colors = [
                                        'Alpa'      => 'red',
                                        'Bolos'     => 'pink',
                                        'Terlambat' => 'yellow',
                                    ];
                                    $color = $colors[$absen->status] ?? 'gray';
                                @endphp
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                    <td class="px-5 py-3 font-medium text-gray-700 dark:text-gray-200">
                                        {{ $absen->registrasi->siswa->nama_siswa }}
                                    </td>
                                    <td class="px-5 py-3 text-sm text-gray-500 dark:text-gray-400">
                                        {{ $absen->registrasi->kelas->nama_kelas }}
                                    </td>
                                    <td class="px-5 py-3 text-sm text-gray-500 dark:text-gray-400">
                                        {{ $absen->jadwal->kurikulum->mataPelajaran->nama_mapel }}
                                    </td>
                                    <td class="px-5 py-3">
                                        <span class="px-2 py-1 text-xs font-medium rounded-full
                                            text-{{ $color }}-700 bg-{{ $color }}-100
                                            dark:bg-{{ $color }}-800 dark:text-{{ $color }}-200">
                                            {{ $absen->status }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3 text-sm text-gray-500 dark:text-gray-400">
                                        {{ $absen->durasi_terlambat ? $absen->durasi_terlambat . ' mnt' : '-' }}
                                    </td>
                                    <td class="px-5 py-3 text-sm text-gray-500 dark:text-gray-400">
                                        {{ substr($absen->jadwal->jam_mulai, 0, 5) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Chart Kehadiran
        const chartData = @json($chartData);
        new Chart(document.getElementById('chartKehadiran'), {
            type: 'line',
            data: {
                labels: chartData.map(d => d.label),
                datasets: [{
                    label: '% Kehadiran',
                    data: chartData.map(d => d.persen),
                    borderColor: '#7C3AED',
                    backgroundColor: 'rgba(124, 58, 237, 0.08)',
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#7C3AED',
                    pointRadius: 5,
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: { min: 0, max: 100, ticks: { callback: v => v + '%' } }
                },
                plugins: { legend: { display: false } }
            }
        });

        // Chart Distribusi
        @if($totalAbsensi > 0)
        const distribusi = @json($distribusi);
        const statusColors = {
            'Hadir': '#16a34a', 'Sakit': '#2563eb', 'Izin': '#d97706',
            'Alpa': '#dc2626', 'Terlambat': '#ea580c', 'Bolos': '#ec4899'
        };
        const labels = Object.keys(distribusi);
        new Chart(document.getElementById('chartDistribusi'), {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: labels.map(l => distribusi[l]),
                    backgroundColor: labels.map(l => statusColors[l] || '#6b7280'),
                    borderWidth: 0,
                    hoverOffset: 6,
                }]
            },
            options: {
                responsive: true,
                cutout: '65%',
                plugins: { legend: { display: false } }
            }
        });
        @endif
    </script>
    @endpush
</x-layouts.admin>