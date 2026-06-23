<!DOCTYPE html>
<html :class="{ 'theme-dark': dark }" x-data="data()" lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Masuk | Presensi</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('assets/css/tailwind.output.css') }}" />
    <script src="https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.x.x/dist/alpine.min.js" defer></script>
    <script src="{{ asset('assets/js/init-alpine.js') }}"></script>
    <style>
        .error-text { color: #ef4444 !important; }
        .dark .error-text { color: #f87171 !important; }
        @keyframes spin { to { transform: rotate(360deg); } }
        .animate-spin { animation: spin 1s linear infinite; }
    </style>
</head>

<body>
    <div class="flex items-center min-h-screen p-6 bg-gray-50 dark:bg-gray-900">
        <div class="relative flex-1 h-full max-w-4xl mx-auto overflow-hidden bg-white rounded-lg shadow-xl dark:shadow-none dark:border dark:border-gray-700 dark:bg-gray-800">

            <div class="flex flex-col overflow-y-auto md:flex-row">

                <div class="h-32 md:h-auto md:w-1/2">
                    <img aria-hidden="true" class="object-cover w-full h-full dark:hidden"
                        src="{{ asset('assets/img/login-office.jpeg') }}" alt="Office" />
                    <img aria-hidden="true" class="hidden object-cover w-full h-full dark:block"
                        src="{{ asset('assets/img/login-office-dark.jpeg') }}" alt="Office" />
                </div>

                {{-- Form --}}
                <div class="flex items-center justify-center p-6 sm:p-12 md:w-1/2">
                    <div class="w-full">

                        {{-- Flash Alert --}}
                        @if (session('status'))
                            <div class="px-4 py-3 mb-4 text-sm text-green-700 bg-green-100 rounded-lg dark:bg-green-800 dark:text-green-200">
                                {{ session('status') }}
                            </div>
                        @endif

                        <h1 class="mb-2 text-xl font-semibold text-gray-700 dark:text-gray-200">
                            Selamat Datang
                        </h1>
                        <p class="mb-6 text-sm text-gray-500 dark:text-gray-400">
                            Silakan masuk menggunakan akun anda
                        </p>

                        <form method="POST" action="{{ route('login') }}" x-data="{ loading: false }" @submit="loading = true">
                            @csrf

                            {{-- Email --}}
                            <label class="block text-sm">
                                <span class="text-gray-700 dark:text-gray-400">Email</span>
                                <input
                                    class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input @error('email') border-red-500 @enderror"
                                    type="email" name="email" value="{{ old('email') }}"
                                    placeholder="Masukkan email anda" required autofocus />
                            </label>
                            @error('email')
                                <span class="error-text text-xs mt-1">{{ $message }}</span>
                            @enderror

                            {{-- Password --}}
                            <label class="block mt-4 text-sm">
                                <span class="text-gray-700 dark:text-gray-400">Kata Sandi</span>
                                <input
                                    class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input @error('password') border-red-500 @enderror"
                                    type="password" name="password" placeholder="Masukkan kata sandi anda" required />
                            </label>
                            @error('password')
                                <span class="error-text text-xs mt-1">{{ $message }}</span>
                            @enderror

                            {{-- Remember Me --}}
                            <label class="flex items-center mt-4 text-sm">
                                <input type="checkbox" name="remember" class="mr-2 rounded border-gray-300 text-purple-600 focus:ring-purple-500" />
                                <span class="text-gray-700 dark:text-gray-400">Ingat Saya</span>
                            </label>

                            {{-- Submit --}}
                            <button type="submit" :disabled="loading"
                                class="flex items-center justify-center w-full px-4 py-2 mt-4 text-sm font-medium leading-5 text-center text-white transition-colors duration-150 bg-purple-600 border border-transparent rounded-lg active:bg-purple-600 hover:bg-purple-700 focus:outline-none focus:shadow-outline-purple disabled:opacity-70">
                                <template x-if="loading">
                                    <svg class="w-4 h-4 mr-2 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </template>
                                <span x-show="!loading">Masuk</span>
                                <span x-show="loading">Memproses...</span>
                            </button>

                            {{-- Lupa Password --}}
                            @if (Route::has('password.request'))
                                <div class="mt-6 text-center">
                                    <a class="text-sm font-medium text-purple-600 dark:text-purple-400 hover:underline"
                                        href="{{ route('password.request') }}">
                                        Lupa kata sandi?
                                    </a>
                                </div>
                            @endif

                        </form>

                        {{-- Footer Copyright --}}
                        <p class="mt-8 text-center text-xs text-gray-400 dark:text-gray-500">
                            &copy; {{ date('Y') }} Sistem Presensi. All rights reserved.
                        </p>

                    </div>
                </div>

            </div>
        </div>
    </div>
</body>

</html>
