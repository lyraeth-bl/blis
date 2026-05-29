<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'BLIS') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Geist:wght@400;500;600&family=Geist+Mono:wght@400&display=swap"
        rel="stylesheet">
    <script>
        // Apply theme before CSS renders to avoid flash
        (function () {
            const stored = localStorage.getItem('blis-theme');
            if (stored === 'dark' || (!stored && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.setAttribute('data-theme', 'dark');
            }
        })();
    </script>
    @vite(['resources/css/pages/home.css', 'resources/js/pages/home.js'])
</head>

<body>

    <!-- Nav -->
    <header class="site-header">
        <nav class="nav-bar" aria-label="Navigasi utama">
            <div class="nav-inner">
                <a href="{{ route('home') }}" class="nav-brand">
                    <div class="nav-logo-mark">
                        <img src="/images/bl_logo.png" alt="Budi Luhur Logo">
                    </div>
                    <span class="nav-brand-name">BLIS</span>
                </a>

                <div class="nav-links">
                    <button class="nav-search" id="search-open-btn" aria-label="Cari layanan" type="button">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"
                            stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8" />
                            <path d="M21 21l-4.35-4.35" />
                        </svg>
                        <span class="nav-search-label">Cari layanan atau website...</span>
                        <span class="nav-search-kbd">⌘K</span>
                    </button>
                </div>

                <div style="display: flex; align-items: center; gap: 8px;">
                    <button class="theme-toggle" id="theme-toggle" aria-label="Toggle dark mode" type="button">
                        <!-- Sun icon (shown in dark mode) -->
                        <svg class="icon-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"
                            stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="5" />
                            <path
                                d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42" />
                        </svg>
                        <!-- Moon icon (shown in light mode) -->
                        <svg class="icon-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z" />
                        </svg>
                    </button>

                    @auth
                        @if (auth()->user()?->canAccessBackOffice())
                            <a href="/admin" class="nav-cta">Admin Panel</a>
                        @endif

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="nav-logout">Keluar</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="nav-cta">Login</a>
                    @endauth
                </div>
            </div>
        </nav>
    </header>

    <!-- Hero -->
    <section class="hero">
        <div class="hero-gradient" aria-hidden="true"></div>

        <div class="hero-content">
            <div class="hero-eyebrow">
                <span class="hero-eyebrow-dot"></span>
                Sistem aktif
                <span class="hero-eyebrow-mono">v1.0.0</span>
            </div>

            <h1>Budi Luhur<br><span class="gradient-text">Integrated System</span></h1>

            <p class="hero-sub">
                Platform terpadu untuk manajemen absensi, jaringan WiFi, dan administrasi sekolah SMA &amp; SMK Budi
                Luhur.
            </p>

            <div class="hero-actions">
                @if (auth()->user()?->canScanQrAttendance())
                    <a href="{{ route('qr-attendance.index') }}" class="btn-primary">
                        Mulai Absensi
                        <svg width="14" height="14" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M3 8H13M8.5 3.5L13 8L8.5 12.5" stroke="currentColor" stroke-width="1.75"
                                stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </a>
                @endif
                <a href="{{ route('wifi.index') }}" class="btn-secondary">
                    Lihat WiFi
                </a>
            </div>
        </div>
    </section>

    <!-- Cards -->
    <section class="cards-section">
        <p class="section-eyebrow">Layanan</p>
        <h2 class="section-title">Akses cepat</h2>
        <script type="application/json" id="websites-json">@json($websitesJson)</script>

        <div class="unit-filter" aria-label="Filter unit">
            <a href="{{ route('home') }}" class="unit-filter-link {{ $selectedUnitId === null ? 'active' : '' }}">
                Semua unit
            </a>

            @foreach ($units as $unit)
                <a href="{{ route('home', ['unit' => $unit->id]) }}"
                    class="unit-filter-link {{ $selectedUnitId === $unit->id ? 'active' : '' }}">
                    {{ $unit->display_name }}
                </a>
            @endforeach
        </div>

        @guest
            <div class="login-notice">
                <div>
                    <p class="login-notice-title">Login untuk akses lengkap</p>
                    <p class="login-notice-text">Daftar ini hanya menampilkan website publik. Masuk dengan akun Google staff
                        untuk melihat website private sesuai unit.</p>
                </div>
                <a href="{{ route('login') }}" class="login-notice-link">Login Google</a>
            </div>
        @endguest

        <div class="cards-grid">

            @forelse ($websites as $index => $website)
                @php $tint = $index % 5; @endphp
                <a href="{{ $website->url }}" class="app-card card-tint-{{ $tint }}" target="_blank" rel="noopener">
                    <div class="card-icon card-icon-tint-{{ $tint }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"
                            stroke-linecap="round" stroke-linejoin="round" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="12" cy="12" r="10" />
                            <path
                                d="M2 12h20M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z" />
                        </svg>
                    </div>

                    <div class="card-body">
                        <p class="card-title">{{ $website->name }}</p>
                        @if ($website->description)
                            <p class="card-desc">{{ $website->description }}</p>
                        @endif
                        @if ($website->category)
                            <p class="card-desc" style="margin-top: 6px;">
                                <span
                                    style="display: inline-flex; align-items: center; gap: 4px; background: var(--canvas-soft-2); border: 1px solid var(--hairline); border-radius: 9999px; padding: 1px 8px; font-size: 11px; color: var(--mute);">
                                    {{ $website->category }}
                                </span>
                                @if ($website->units->isNotEmpty())
                                    <span
                                        style="display: inline-flex; align-items: center; gap: 4px; background: var(--canvas-soft-2); border: 1px solid var(--hairline); border-radius: 9999px; padding: 1px 8px; font-size: 11px; color: var(--mute);">
                                        {{ $website->units->pluck('display_name')->join(', ') }}
                                    </span>
                                @endif
                                @if ($website->is_private)
                                    <span
                                        style="display: inline-flex; align-items: center; gap: 4px; background: var(--canvas-soft-2); border: 1px solid var(--hairline); border-radius: 9999px; padding: 1px 8px; font-size: 11px; color: var(--mute);">
                                        Private
                                    </span>
                                @endif
                            </p>
                        @endif
                    </div>

                    <div class="card-footer">
                        <span class="card-tag">{{ parse_url($website->url, PHP_URL_HOST) ?: $website->url }}</span>
                        <div class="card-arrow">
                            <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.75"
                                stroke-linecap="round" stroke-linejoin="round" xmlns="http://www.w3.org/2000/svg">
                                <path d="M3 8H13M8.5 3.5L13 8L8.5 12.5" />
                            </svg>
                        </div>
                    </div>
                </a>
            @empty
                {{-- Fallback: hardcoded pages when no websites in DB --}}
                @if (auth()->user()?->canScanQrAttendance())
                    <a href="{{ route('qr-attendance.index') }}" class="app-card card-tint-0">
                        <div class="card-icon card-icon-tint-0">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"
                                stroke-linecap="round" stroke-linejoin="round" xmlns="http://www.w3.org/2000/svg">
                                <rect x="3" y="3" width="7" height="7" rx="1" />
                                <rect x="14" y="3" width="7" height="7" rx="1" />
                                <rect x="3" y="14" width="7" height="7" rx="1" />
                                <path d="M14 14h2v2h-2zM18 14h3M14 18v3M18 18h3v3h-3z" />
                            </svg>
                        </div>
                        <div class="card-body">
                            <p class="card-title">QR Absensi</p>
                            <p class="card-desc">Scan QR code untuk mencatat kehadiran siswa secara real-time dengan validasi
                                otomatis.</p>
                        </div>
                        <div class="card-footer">
                            <span class="card-tag">/qr-absensi</span>
                            <div class="card-arrow">
                                <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.75"
                                    stroke-linecap="round" stroke-linejoin="round" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M3 8H13M8.5 3.5L13 8L8.5 12.5" />
                                </svg>
                            </div>
                        </div>
                    </a>
                @endif

                <a href="{{ route('wifi.index') }}" class="app-card card-tint-1">
                    <div class="card-icon card-icon-tint-1">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"
                            stroke-linecap="round" stroke-linejoin="round" xmlns="http://www.w3.org/2000/svg">
                            <path d="M5 12.55a11 11 0 0 1 14.08 0" />
                            <path d="M1.42 9a16 16 0 0 1 21.16 0" />
                            <path d="M8.53 16.11a6 6 0 0 1 6.95 0" />
                            <circle cx="12" cy="20" r="1" fill="currentColor" stroke="none" />
                        </svg>
                    </div>
                    <div class="card-body">
                        <p class="card-title">Daftar WiFi</p>
                        <p class="card-desc">Temukan SSID, lokasi, dan password WiFi yang tersedia di lingkungan kampus Budi
                            Luhur.</p>
                    </div>
                    <div class="card-footer">
                        <span class="card-tag">/wifi</span>
                        <div class="card-arrow">
                            <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.75"
                                stroke-linecap="round" stroke-linejoin="round" xmlns="http://www.w3.org/2000/svg">
                                <path d="M3 8H13M8.5 3.5L13 8L8.5 12.5" />
                            </svg>
                        </div>
                    </div>
                </a>

                @if (auth()->user()?->canAccessBackOffice())
                    <a href="/admin" class="app-card card-tint-2">
                        <div class="card-icon card-icon-tint-2">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"
                                stroke-linecap="round" stroke-linejoin="round" xmlns="http://www.w3.org/2000/svg">
                                <path d="M12 2L2 7v5c0 5.25 3.75 10.15 9 11.4 1.08-.24 2.08-.62 3-.84" />
                                <circle cx="18" cy="18" r="3" />
                                <path
                                    d="M18 15v-2M18 21v.01M21 18h-2M15 18h.01M20.12 15.88l-1.41 1.41M17.29 20.71l-1.41-1.41M20.12 20.12l-1.41-1.41M17.29 15.29l-1.41 1.41" />
                            </svg>
                        </div>
                        <div class="card-body">
                            <p class="card-title">Admin Panel</p>
                            <p class="card-desc">Kelola data siswa, karyawan, absensi, dan konfigurasi sistem melalui panel
                                administrasi.</p>
                        </div>
                        <div class="card-footer">
                            <span class="card-tag">/admin</span>
                            <div class="card-arrow">
                                <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.75"
                                    stroke-linecap="round" stroke-linejoin="round" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M3 8H13M8.5 3.5L13 8L8.5 12.5" />
                                </svg>
                            </div>
                        </div>
                    </a>
                @endif
            @endforelse

        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="footer-inner">
            <a href="{{ route('home') }}" class="footer-brand">
                <div class="footer-logo-mark">
                    <img src="/images/bl_logo.png" alt="Budi Luhur Logo">
                </div>
                <span class="footer-brand-name">BLIS</span>
            </a>

            <span class="footer-copy">© {{ date('Y') }} Budi Luhur Integrated System</span>

            <div class="footer-links">
                @forelse ($websites as $website)
                    <a href="{{ $website->url }}" class="footer-link" target="_blank"
                        rel="noopener">{{ $website->name }}</a>
                @empty
                    @if (auth()->user()?->canScanQrAttendance())
                        <a href="{{ route('qr-attendance.index') }}" class="footer-link">QR Absensi</a>
                    @endif
                    <a href="{{ route('wifi.index') }}" class="footer-link">WiFi</a>
                    @if (auth()->user()?->canAccessBackOffice())
                        <a href="/admin" class="footer-link">Admin</a>
                    @endif
                @endforelse
            </div>
        </div>
    </footer>

    <!-- Search overlay -->
    <div class="search-overlay" id="search-overlay" role="dialog" aria-modal="true" aria-label="Cari layanan">
        <div class="search-modal" id="search-modal">
            <div class="search-modal-input-wrap">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"
                    stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8" />
                    <path d="M21 21l-4.35-4.35" />
                </svg>
                <input type="text" class="search-modal-input" id="search-input" placeholder="Cari layanan atau website…"
                    autocomplete="off" spellcheck="false">
                <kbd class="search-kbd">ESC</kbd>
            </div>

            <div class="search-results" id="search-results"></div>

            <div class="search-footer">
                <span class="search-footer-hint">
                    <kbd>↑</kbd><kbd>↓</kbd> navigasi
                </span>
                <span class="search-footer-hint">
                    <kbd>↵</kbd> buka
                </span>
                <span class="search-footer-hint">
                    <kbd>ESC</kbd> tutup
                </span>
            </div>
        </div>
    </div>



</body>

</html>