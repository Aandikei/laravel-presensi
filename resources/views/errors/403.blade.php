<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Akses Ditolak</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('assets/css/tailwind.output.css') }}"/>
</head>
<body class="bg-gray-50 dark:bg-gray-900 min-h-screen flex items-center justify-center p-6">
    <div class="text-center max-w-md">
        <div class="w-20 h-20 bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg class="w-10 h-10 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
        </div>

        <h1 class="text-4xl font-bold text-gray-800 dark:text-white mb-2">403</h1>
        <h2 class="text-xl font-semibold text-gray-700 dark:text-gray-200 mb-3">Akses Ditolak</h2>
        <p class="text-gray-500 dark:text-gray-400 mb-8">
            Kamu tidak memiliki akses ke halaman ini.
        </p>

        <div class="flex flex-col sm:flex-row gap-3 justify-center">
            {{-- Tombol ke dashboard sesuai role --}}
            @auth
                @php
                    $user = auth()->user();
                    if ($user->hasRole('super_admin')) $dashRoute = route('superadmin.dashboard');
                    elseif ($user->hasRole('admin')) $dashRoute = route('admin.dashboard');
                    elseif ($user->hasRole('guru') || $user->hasRole('wali_kelas')) $dashRoute = route('guru.dashboard');
                    elseif ($user->hasRole('siswa')) $dashRoute = route('siswa.dashboard');
                    elseif ($user->hasRole('orang_tua')) $dashRoute = route('orangtua.dashboard');
                    else $dashRoute = route('login');
                @endphp
                <a href="{{ $dashRoute }}"
                    class="px-6 py-2.5 text-sm font-medium text-white bg-purple-600 rounded-lg hover:bg-purple-700">
                    Ke Dashboard Saya
                </a>

                {{-- Logout --}}
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        class="w-full px-6 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600">
                        Logout
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}"
                    class="px-6 py-2.5 text-sm font-medium text-white bg-purple-600 rounded-lg hover:bg-purple-700">
                    Login
                </a>
            @endauth
        </div>

        @auth
            <p class="mt-4 text-xs text-gray-400">
                Login sebagai: <strong>{{ auth()->user()->name }}</strong>
                ({{ auth()->user()->getRoleNames()->first() }})
            </p>
        @endauth
    </div>
</body>
</html>