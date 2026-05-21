<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Hidden input: auto-focus, menangkap input dari scanner USB --}}
        <input id="qr-input" type="text" wire:model="token" autocomplete="off" class="sr-only"
            aria-label="QR Scanner Input" />

        {{-- Status Scanner --}}
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <div class="flex items-center gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-primary-100 dark:bg-primary-900">
                    <x-heroicon-o-qr-code class="h-6 w-6 text-primary-600 dark:text-primary-400" />
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Status Scanner</p>
                    <p class="text-lg font-semibold text-gray-900 dark:text-white" id="scanner-status">
                        Siap Scan
                    </p>
                </div>
                <div class="ml-auto">
                    <span
                        class="inline-flex items-center gap-1.5 rounded-full bg-success-100 px-3 py-1 text-sm font-medium text-success-700 dark:bg-success-900 dark:text-success-300">
                        <span class="h-2 w-2 rounded-full bg-success-500 animate-pulse"></span>
                        Aktif
                    </span>
                </div>
            </div>
            <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">
                Arahkan scanner ke QR Code siswa. Input otomatis terdeteksi.
            </p>
        </div>

        {{-- Hasil Scan Terakhir --}}
        @if ($lastScan)
            <div
                class="rounded-xl border border-success-200 bg-success-50 p-6 shadow-sm dark:border-success-700 dark:bg-success-950">
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-3">
                        <x-heroicon-o-check-circle class="h-6 w-6 text-success-600 dark:text-success-400" />
                        <p class="font-semibold text-success-800 dark:text-success-200">Absensi Berhasil</p>
                    </div>
                    <span class="rounded-full px-3 py-1 text-sm font-medium
                                        {{ $lastScan['tipe'] === 'Check In'
            ? 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300'
            : 'bg-orange-100 text-orange-700 dark:bg-orange-900 dark:text-orange-300' }}">
                        {{ $lastScan['tipe'] }}
                    </span>
                </div>

                <div class="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-4">
                    <div>
                        <p class="text-xs text-success-600 dark:text-success-400">NIS</p>
                        <p class="font-semibold text-success-900 dark:text-success-100">{{ $lastScan['nis'] }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-success-600 dark:text-success-400">Nama</p>
                        <p class="font-semibold text-success-900 dark:text-success-100">{{ $lastScan['nama'] }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-success-600 dark:text-success-400">Kelas</p>
                        <p class="font-semibold text-success-900 dark:text-success-100">{{ $lastScan['kelas'] }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-success-600 dark:text-success-400">Status</p>
                        <p class="font-semibold text-success-900 dark:text-success-100">{{ $lastScan['status'] }}</p>
                    </div>
                </div>

                <p class="mt-3 text-xs text-success-600 dark:text-success-400">
                    Dicatat pukul {{ $lastScan['waktu'] }}
                </p>
            </div>
        @endif

        {{-- Error --}}
        @if ($lastError)
            <div
                class="rounded-xl border border-danger-200 bg-danger-50 p-6 shadow-sm dark:border-danger-700 dark:bg-danger-950">
                <div class="flex items-center gap-3">
                    <x-heroicon-o-x-circle class="h-6 w-6 text-danger-600 dark:text-danger-400" />
                    <div>
                        <p class="font-semibold text-danger-800 dark:text-danger-200">Scan Gagal</p>
                        <p class="text-sm text-danger-700 dark:text-danger-300">{{ $lastError }}</p>
                    </div>
                </div>
            </div>
        @endif

    </div>

    @script
    <script>
        requestAnimationFrame(() => {
            const input = document.getElementById('qr-input');

            if (!input) {
                return;
            }

            // Auto-focus input saat halaman load
            focusInput();

            // Re-focus setiap kali halaman diklik
            // (biar fokus tidak pindah ke elemen lain)
            document.addEventListener('click', (e) => {
                if (e.target.id !== 'qr-input') focusInput();
            });

            // Re-focus setelah Livewire reset input
            Livewire.on('reset-input', () => {
                input.value = '';
                focusInput();
            });

            // Deteksi Enter dari scanner → dispatch ke Livewire
            input.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const token = input.value.trim();
                    if (token) {
                        Livewire.dispatch('qr-scanned', { token });
                    }
                }
            });

            function focusInput() {
                input.focus();
            }
        });
    </script>
    @endscript
</x-filament-panels::page>