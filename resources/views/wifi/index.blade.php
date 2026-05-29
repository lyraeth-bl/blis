<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar WiFi — BLIS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Geist:wght@400;500;600&family=Geist+Mono:wght@400&display=swap"
        rel="stylesheet">
    <script>
        (function () {
            const stored = localStorage.getItem('blis-theme');
            if (stored === 'dark' || (!stored && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.setAttribute('data-theme', 'dark');
            }
        })();
    </script>
    @vite(['resources/css/pages/wifi.css', 'resources/js/pages/wifi.js'])
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

                <div class="nav-divider"></div>
                <span class="nav-page-label">Daftar WiFi</span>

                <div class="nav-actions">
                    <button class="nav-icon-btn" id="theme-toggle" aria-label="Toggle dark mode" type="button">
                        <svg class="icon-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"
                            stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="5" />
                            <path
                                d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42" />
                        </svg>
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
                Jaringan aktif
                <span class="hero-eyebrow-mono">WiFi</span>
            </div>

            <h1>Password WiFi<br><span class="gradient-text">Budi Luhur</span></h1>

            <p class="hero-sub">Temukan SSID, lokasi, dan password WiFi yang tersedia di lingkungan SMA dan SMK Budi
                Luhur.</p>
        </div>
    </section>

    <!-- Search -->
    <div class="search-wrap">
        <div class="search-group">
            <svg class="search-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"
                stroke-linecap="round">
                <circle cx="6.5" cy="6.5" r="5.5" />
                <path d="M14 14l-3-3" />
            </svg>
            <input type="text" class="search-input" id="search" placeholder="Cari SSID atau lokasi…" autocomplete="off">
        </div>
    </div>

    <div class="unit-filter" aria-label="Filter unit">
        <a href="{{ route('wifi.index') }}" class="unit-filter-link {{ $selectedUnitId === null ? 'active' : '' }}">
            Semua unit
        </a>

        @foreach ($units as $unit)
            <a href="{{ route('wifi.index', ['unit' => $unit->id]) }}"
                class="unit-filter-link {{ $selectedUnitId === $unit->id ? 'active' : '' }}">
                {{ $unit->display_name }}
            </a>
        @endforeach
    </div>

    @guest
        <div class="login-notice">
            <div>
                <p class="login-notice-title">Login untuk akses lengkap</p>
                <p class="login-notice-text">Daftar ini hanya menampilkan WiFi publik. Masuk dengan akun Google staff untuk
                    melihat WiFi private sesuai unit.</p>
            </div>
            <a href="{{ route('login') }}" class="login-notice-link">Login Google</a>
        </div>
    @endguest

    <!-- Grid -->
    <div class="grid-section">
        <div class="wifi-grid" id="wifi-grid">
            @forelse ($wifis as $wifi)
                <article class="wifi-card" data-ssid="{{ strtolower($wifi->ssid) }}"
                    data-location="{{ strtolower($wifi->location) }}">

                    <div class="card-header">
                        <span class="card-ssid">{{ $wifi->ssid }}</span>
                        <div class="wifi-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"
                                stroke-linecap="round" stroke-linejoin="round">
                                <path d="M5 12.55a11 11 0 0 1 14.08 0" />
                                <path d="M1.42 9a16 16 0 0 1 21.16 0" />
                                <path d="M8.53 16.11a6 6 0 0 1 6.95 0" />
                                <circle cx="12" cy="20" r="1" fill="currentColor" stroke="none" />
                            </svg>
                        </div>
                    </div>

                    <div class="card-divider"></div>

                    <div class="card-meta">
                        <!-- Lokasi -->
                        <div class="meta-row">
                            <span class="meta-label">Lokasi</span>
                            <div>
                                <span class="location-badge">
                                    <svg viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.5"
                                        stroke-linecap="round" stroke-linejoin="round">
                                        <path
                                            d="M6 1a3.5 3.5 0 0 1 3.5 3.5C9.5 7.5 6 11 6 11S2.5 7.5 2.5 4.5A3.5 3.5 0 0 1 6 1z" />
                                        <circle cx="6" cy="4.5" r="1" fill="currentColor" stroke="none" />
                                    </svg>
                                    {{ $wifi->location }}
                                </span>
                                @if ($wifi->units->isNotEmpty())
                                    <span class="location-badge">
                                        {{ $wifi->units->pluck('display_name')->join(', ') }}
                                    </span>
                                @endif
                                @if ($wifi->is_private)
                                    <span class="location-badge">
                                        Private
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- Password -->
                        <div class="meta-row">
                            <span class="meta-label">Password</span>
                            @if ($wifi->password)
                                <div class="password-wrap">
                                    <span class="password-text hidden" id="pwd-{{ $wifi->id }}"
                                        data-password="{{ $wifi->password }}">••••••••••</span>
                                    <button class="btn-sm" data-password-action="toggle" data-wifi-id="{{ $wifi->id }}"
                                        type="button">Tampilkan</button>
                                    <button class="btn-sm" data-password-action="copy" data-wifi-id="{{ $wifi->id }}"
                                        type="button">Salin</button>
                                </div>
                            @else
                                <span class="meta-value" style="color:var(--mute);">Tidak ada password</span>
                            @endif
                        </div>
                    </div>

                    @if ($wifi->description)
                        <p class="card-description">{{ $wifi->description }}</p>
                    @endif

                </article>
            @empty
                <div class="empty-state">
                    <svg class="empty-icon" viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="1.5"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M10 24.55a20 20 0 0 1 28 0" />
                        <path d="M2 17a30 30 0 0 1 44 0" />
                        <path d="M17 32a10 10 0 0 1 14 0" />
                        <circle cx="24" cy="40" r="2" fill="currentColor" stroke="none" />
                    </svg>
                    <span>Belum ada data WiFi tersedia.</span>
                </div>
            @endforelse
        </div>
    </div>

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
        </div>
    </footer>



</body>

</html>