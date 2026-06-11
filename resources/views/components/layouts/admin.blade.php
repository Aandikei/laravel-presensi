<!DOCTYPE html>
<html :class="{ 'dark': dark }" x-data="data()" lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ $title ?? 'Dashboard' }} — Presensi</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('/assets/css/tailwind.output.css') }}" />
    @vite(['resources/css/app.css'])
    <script src="https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.x.x/dist/alpine.min.js" defer></script>
    <script src="{{ asset('/assets/js/init-alpine.js') }}"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.3/Chart.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.3/Chart.min.js" defer></script>
    <script src="{{ asset('/assets/js/charts-lines.js') }}" defer></script>
    <script src="{{ asset('/assets/js/charts-pie.js') }}" defer></script>
    {{-- DataTables --}}
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
</head>

<body>
    <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
        <!-- Desktop sidebar -->
        <x-partials.sidebar-desktop />
        <!-- Mobile sidebar -->
        <!-- Backdrop -->
        <x-partials.sidebar-mobile />
        <div class="md:pl-64 flex flex-col min-h-screen">
            <x-partials.navbar />
            <main class="flex-1 bg-gray-50 dark:bg-gray-900">
                <div class="mx-auto max-w-screen-2xl p-4 md:p-6 2xl:p-10">
                    {{-- Flash Error --}}
                    @if (session('error'))
                        <div
                            class="mb-4 px-4 py-3 text-sm text-red-700 bg-red-100 rounded-lg dark:bg-red-800 dark:text-red-200 flex items-center justify-between">
                            <span>{{ session('error') }}</span>
                            <button onclick="this.parentElement.remove()"
                                class="ml-4 text-red-500 hover:text-red-700">✕</button>
                        </div>
                    @endif

                    {{ $slot }}
                </div>
            </main>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    @stack('scripts')
</body>

</html>
