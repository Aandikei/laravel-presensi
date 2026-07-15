<!DOCTYPE html>
<html :class="{ 'dark': dark }" x-data="data()" lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ $title ?? 'Dashboard' }} — Presensi</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('/assets/css/tailwind.output.css') }}" />
    @vite(['resources/css/app.css'])
    <style>[x-cloak] { display: none !important; }</style>
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
                                    window.addEventListener('app-toast', (e) => {
                                        this.show(e.detail.type, e.detail.message);
                                    });
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
    {{-- Confirm Modal --}}
    <div x-data="confirmModal()" x-init="init()" x-cloak>
            <div x-show="open"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 z-30 flex items-end bg-black bg-opacity-50 sm:items-center sm:justify-center"
                @click.self="open = false"
                @keydown.escape.window="open = false">
                <div x-show="open"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="w-full px-6 py-4 overflow-hidden bg-white rounded-t-lg dark:bg-gray-800 sm:rounded-lg sm:m-4 sm:max-w-xl"
                    @keydown.enter.prevent="confirm">
                <h3 class="mb-2 text-lg font-semibold text-gray-700 dark:text-gray-200" x-text="title"></h3>
                <p class="mb-4 text-sm text-gray-600 dark:text-gray-400" x-text="message"></p>
                <div x-show="requirePassword" class="mb-4">
                    <label class="block text-sm">
                        <span class="text-gray-700 dark:text-gray-400">Masukkan password untuk konfirmasi</span>
                        <input type="password" x-model="password" placeholder="***************"
                            class="block w-full mt-1 text-sm form-input dark:bg-gray-700 dark:text-gray-300"
                            x-bind:class="{ 'border-red-500': passwordError }" />
                            <span x-show="passwordError" class="text-xs text-red-500 mt-1" x-text="passwordError"></span>
                    </label>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" @click="open = false"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-400">
                        Batal
                    </button>
                    <button type="button" @click="confirm" id="confirm-btn"
                        class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700"
                        x-text="confirmText">
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function confirmAction(formEl, message, confirmText = 'Ya, Hapus', title = 'Konfirmasi', requirePassword = false) {
            if (!formEl) return;
            window.dispatchEvent(new CustomEvent('confirm-action', {
                detail: { title, message, confirmText, formEl, requirePassword }
            }));
        }

        function confirmModal() {
            return {
                open: false,
                title: 'Konfirmasi',
                message: '',
                confirmText: 'Ya, Hapus',
                requirePassword: false,
                password: '',
                passwordError: '',
                sourceForm: null,
                init() {
                    window.addEventListener('confirm-action', (e) => {
                        this.title = e.detail.title || 'Konfirmasi';
                        this.message = e.detail.message;
                        this.confirmText = e.detail.confirmText || 'Ya, Hapus';
                        this.requirePassword = e.detail.requirePassword || false;
                        this.password = '';
                        this.passwordError = '';
                        this.sourceForm = e.detail.formEl;
                        this.open = true;
                        this.$nextTick(() => {
                            if (this.requirePassword) {
                                this.$el.querySelector('input[type="password"]')?.focus();
                            } else {
                                document.getElementById('confirm-btn')?.focus();
                            }
                        });
                    });
                },
                confirm() {
                    if (this.requirePassword && !this.password) {
                        this.passwordError = 'Password wajib diisi.';
                        return;
                    }
                    this.open = false;
                    if (this.sourceForm) {
                        if (this.requirePassword) {
                            let pwField = this.sourceForm.querySelector('input[name="password"]');
                            if (!pwField) {
                                pwField = document.createElement('input');
                                pwField.type = 'hidden';
                                pwField.name = 'password';
                                this.sourceForm.appendChild(pwField);
                            }
                            pwField.value = this.password;
                        }
                        this.sourceForm.submit();
                    }
                }
            };
        }
    </script>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    @stack('scripts')

    <script>
        document.addEventListener('submit', function(e) {
            const btn = e.target.querySelector('button[type="submit"]');
            if (btn) btn.disabled = true;
        });
    </script>
</body>

</html>
