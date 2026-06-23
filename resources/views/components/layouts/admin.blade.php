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
    {{-- DataTables (styled via app.css) --}}
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
                    {{ $slot }}

                    {{-- Toast Notification --}}
                    <div x-data="toast()" x-init="init()"
                        x-show="visible"
                        x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="translate-x-8 opacity-0"
                        x-transition:enter-end="translate-x-0 opacity-100"
                        x-transition:leave="transition ease-in duration-200"
                        x-transition:leave-start="translate-x-0 opacity-100"
                        x-transition:leave-end="translate-x-8 opacity-0"
                        :class="{
                            'bg-green-500': type === 'success',
                            'bg-red-500': type === 'error',
                            'bg-blue-500': type === 'info'
                        }"
                        class="fixed top-4 right-4 z-[9999] flex items-center gap-3 px-5 py-3 text-sm text-white rounded-lg shadow-lg cursor-pointer max-w-sm"
                        @click="dismiss">
                        <svg x-show="type === 'success'" class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <svg x-show="type === 'error'" class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        <svg x-show="type === 'info'" class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span x-text="message" class="flex-1"></span>
                        <svg class="w-4 h-4 shrink-0 text-white/70 hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </div>

                    <script>
                        function toast() {
                            return {
                                visible: false,
                                type: '',
                                message: '',
                                init() {
                                    @if(session('success'))
                                        this.show('success', {!! json_encode(session('success')) !!});
                                    @elseif(session('error'))
                                        this.show('error', {!! json_encode(session('error')) !!});
                                    @elseif(session('info'))
                                        this.show('info', {!! json_encode(session('info')) !!});
                                    @endif
                                },
                                show(type, message) {
                                    this.type = type;
                                    this.message = message;
                                    this.visible = true;
                                    setTimeout(() => { this.dismiss(); }, 5000);
                                },
                                dismiss() {
                                    this.visible = false;
                                }
                            }
                        }
                    </script>
                </div>
            </main>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    @stack('scripts')
</body>

</html>
