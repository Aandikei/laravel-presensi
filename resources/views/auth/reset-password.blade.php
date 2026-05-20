<!DOCTYPE html>
<html :class="{ 'theme-dark': dark }" x-data="data()" lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Reset Password</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('assets/css/tailwind.output.css') }}" />
    <script src="https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.x.x/dist/alpine.min.js" defer></script>
    <script src="{{ asset('assets/js/init-alpine.js') }}"></script>
</head>

<body>
    <div class="flex items-center justify-center min-h-screen p-6 bg-gray-50 dark:bg-gray-900">

        <div class="w-full max-w-md bg-white rounded-lg shadow-xl dark:bg-gray-800">
            <div class="p-6 sm:p-12">

                <h1 class="mb-2 text-xl font-semibold text-gray-700 dark:text-gray-200">
                    Reset Password
                </h1>
                <p class="mb-6 text-sm text-gray-600 dark:text-gray-400">
                    Masukkan password baru kamu di bawah ini.
                </p>

                <form method="POST" action="{{ route('password.store') }}">
                    @csrf

                    {{-- Token (hidden) --}}
                    <input type="hidden" name="token" value="{{ $request->route('token') }}" />

                    {{-- Email --}}
                    <label class="block text-sm">
                        <span class="text-gray-700 dark:text-gray-400">Email</span>
                        <input
                            class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input @error('email') border-red-500 @enderror"
                            type="email" name="email" value="{{ old('email', $request->email) }}"
                            placeholder="jane@doe.com" required autofocus />
                        @error('email')
                            <span class="text-xs text-red-500 mt-1">{{ $message }}</span>
                        @enderror
                    </label>

                    {{-- Password --}}
                    <label class="block mt-4 text-sm">
                        <span class="text-gray-700 dark:text-gray-400">Password Baru</span>
                        <input
                            class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input @error('password') border-red-500 @enderror"
                            type="password" name="password" placeholder="***************" required />
                        @error('password')
                            <span class="text-xs text-red-500 mt-1">{{ $message }}</span>
                        @enderror
                    </label>

                    {{-- Confirm Password --}}
                    <label class="block mt-4 text-sm">
                        <span class="text-gray-700 dark:text-gray-400">Konfirmasi Password</span>
                        <input
                            class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input"
                            type="password" name="password_confirmation" placeholder="***************" required />
                    </label>

                    {{-- Submit --}}
                    <button type="submit"
                        class="block w-full px-4 py-2 mt-6 text-sm font-medium leading-5 text-center text-white transition-colors duration-150 bg-purple-600 border border-transparent rounded-lg active:bg-purple-600 hover:bg-purple-700 focus:outline-none focus:shadow-outline-purple">
                        Reset Password
                    </button>

                    <p class="mt-4">
                        <a class="text-sm font-medium text-purple-600 dark:text-purple-400 hover:underline"
                            href="{{ route('login') }}">
                            Back to login
                        </a>
                    </p>

                </form>
            </div>
        </div>

    </div>
</body>

</html>
