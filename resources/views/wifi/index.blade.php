<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar WiFi — BLIS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Geist:wght@400;500;600&family=Geist+Mono:wght@400&display=swap" rel="stylesheet">
    <script>
        (function () {
            const stored = localStorage.getItem('blis-theme');
            if (stored === 'dark' || (!stored && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.setAttribute('data-theme', 'dark');
            }
        })();
    </script>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --primary:           #171717;
            --on-primary:        #ffffff;
            --ink:               #171717;
            --body:              #4d4d4d;
            --mute:              #888888;
            --hairline:          #ebebeb;
            --hairline-strong:   #a1a1a1;
            --canvas:            #ffffff;
            --canvas-soft:       #fafafa;
            --canvas-soft-2:     #f5f5f5;
            --success:           #27a644;
            --link:              #0070f3;
            --shadow-card:       0 1px 1px #00000005, 0 2px 2px #0000000a, inset 0 0 0 1px #00000014;
            --shadow-card-hover: 0 1px 1px #0000000a, 0 4px 8px #00000010, inset 0 0 0 1px #00000018;
        }

        [data-theme="dark"] {
            --primary:           #ffffff;
            --on-primary:        #171717;
            --ink:               #ededed;
            --body:              #a1a1a1;
            --mute:              #666666;
            --hairline:          #2a2a2a;
            --hairline-strong:   #3d3d3d;
            --canvas:            #111111;
            --canvas-soft:       #0a0a0a;
            --canvas-soft-2:     #161616;
            --shadow-card:       0 1px 1px #00000030, 0 2px 2px #00000040, inset 0 0 0 1px #ffffff0a;
            --shadow-card-hover: 0 1px 1px #00000050, 0 4px 8px #00000060, inset 0 0 0 1px #ffffff12;
        }

        [data-theme="dark"] nav {
            background: rgba(10, 10, 10, 0.85);
        }

        ::selection { background: #171717; color: #f2f2f2; }

        html { background: var(--canvas-soft); scroll-behavior: smooth; }

        body {
            font-family: 'Geist', Inter, system-ui, -apple-system, sans-serif;
            color: var(--ink);
            background: var(--canvas-soft);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            -webkit-font-smoothing: antialiased;
            transition: background .2s, color .2s;
        }

        /* ── Nav ── */
        nav {
            position: sticky;
            top: 0;
            z-index: 100;
            height: 64px;
            background: rgba(250, 250, 250, 0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--hairline);
            display: flex;
            align-items: center;
        }

        .nav-inner {
            max-width: 1120px;
            width: 100%;
            margin: 0 auto;
            padding: 0 32px;
            display: flex;
            align-items: center;
            gap: 0;
        }

        .nav-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            flex-shrink: 0;
        }

        .nav-logo-mark {
            width: 32px;
            height: 32px;
            flex-shrink: 0;
        }

        .nav-logo-mark img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .nav-brand-name {
            font-size: 15px;
            font-weight: 600;
            color: var(--ink);
            letter-spacing: -0.3px;
        }

        .nav-divider {
            width: 1px;
            height: 18px;
            background: var(--hairline-strong);
            margin: 0 14px;
            flex-shrink: 0;
        }

        .nav-page-label {
            font-size: 14px;
            font-weight: 400;
            color: var(--body);
            flex: 1;
        }

        .nav-actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .nav-icon-btn {
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 9999px;
            border: 1px solid var(--hairline);
            background: var(--canvas);
            color: var(--body);
            cursor: pointer;
            transition: border-color .15s, color .15s, background .15s;
            flex-shrink: 0;
            text-decoration: none;
        }

        .nav-icon-btn:hover {
            border-color: var(--hairline-strong);
            color: var(--ink);
            background: var(--canvas-soft-2);
        }

        .nav-icon-btn svg { width: 16px; height: 16px; }

        .icon-sun  { display: none; }
        .icon-moon { display: block; }

        [data-theme="dark"] .icon-sun  { display: block; }
        [data-theme="dark"] .icon-moon { display: none; }

        .nav-cta {
            font-size: 14px;
            font-weight: 500;
            color: var(--on-primary);
            background: var(--primary);
            text-decoration: none;
            padding: 0 12px;
            height: 32px;
            display: inline-flex;
            align-items: center;
            border-radius: 6px;
            transition: opacity .15s;
        }

        .nav-cta:hover { opacity: 0.85; }

        /* ── Hero ── */
        .hero {
            max-width: 1120px;
            margin: 0 auto;
            padding: 80px 32px 56px;
        }

        .hero-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--canvas);
            border: 1px solid var(--hairline);
            border-radius: 9999px;
            padding: 4px 12px;
            font-size: 12px;
            font-weight: 500;
            color: var(--body);
            letter-spacing: 0.2px;
            margin-bottom: 20px;
            box-shadow: var(--shadow-card);
        }

        .hero-eyebrow-dot {
            width: 6px;
            height: 6px;
            background: var(--success);
            border-radius: 9999px;
            flex-shrink: 0;
        }

        h1 {
            font-size: 48px;
            font-weight: 600;
            line-height: 1.05;
            letter-spacing: -2.4px;
            color: var(--ink);
            margin-bottom: 14px;
        }

        .hero-sub {
            font-size: 17px;
            font-weight: 400;
            line-height: 1.6;
            color: var(--body);
            max-width: 520px;
        }

        /* ── Search bar ── */
        .search-wrap {
            max-width: 1120px;
            margin: 0 auto;
            padding: 0 32px 40px;
        }

        .search-group {
            position: relative;
            width: 100%;
            max-width: 400px;
        }

        .search-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--mute);
            pointer-events: none;
            width: 15px;
            height: 15px;
        }

        .search-input {
            width: 100%;
            height: 40px;
            background: var(--canvas);
            color: var(--ink);
            border: 1px solid var(--hairline);
            border-radius: 8px;
            padding: 0 12px 0 36px;
            font-size: 14px;
            font-family: inherit;
            outline: none;
            box-shadow: var(--shadow-card);
            transition: border-color .15s, box-shadow .15s;
        }

        .search-input:focus {
            border-color: var(--hairline-strong);
            box-shadow: var(--shadow-card-hover);
        }

        .search-input::placeholder { color: var(--mute); }

        /* ── Grid ── */
        .grid-section {
            max-width: 1120px;
            margin: 0 auto;
            padding: 0 32px 96px;
            flex: 1;
        }

        .wifi-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
        }

        /* ── Card ── */
        .wifi-card {
            background: var(--canvas);
            border-radius: 12px;
            padding: 24px;
            display: flex;
            flex-direction: column;
            gap: 16px;
            box-shadow: var(--shadow-card);
            transition: box-shadow .2s, transform .2s;
        }

        .wifi-card:hover {
            box-shadow: var(--shadow-card-hover);
            transform: translateY(-2px);
        }

        .card-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
        }

        .card-ssid {
            font-size: 20px;
            font-weight: 600;
            line-height: 1.25;
            letter-spacing: -0.6px;
            color: var(--ink);
            word-break: break-all;
        }

        .wifi-icon {
            flex-shrink: 0;
            width: 36px;
            height: 36px;
            background: var(--canvas-soft-2);
            border: 1px solid var(--hairline);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .wifi-icon svg {
            width: 18px;
            height: 18px;
            color: var(--link);
        }

        .card-divider {
            height: 1px;
            background: var(--hairline);
        }

        .card-meta {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .meta-row {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .meta-label {
            font-family: 'Geist Mono', ui-monospace, monospace;
            font-size: 11px;
            font-weight: 400;
            color: var(--mute);
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .meta-value {
            font-size: 14px;
            color: var(--body);
            line-height: 1.5;
        }

        /* ── Password ── */
        .password-wrap {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
        }

        .password-text {
            font-family: 'Geist Mono', ui-monospace, monospace;
            font-size: 13px;
            color: var(--ink);
            letter-spacing: 0.5px;
            word-break: break-all;
            flex: 1;
        }

        .password-text.hidden { letter-spacing: 4px; }

        .btn-sm {
            flex-shrink: 0;
            background: var(--canvas-soft);
            border: 1px solid var(--hairline);
            border-radius: 6px;
            padding: 3px 8px;
            font-size: 12px;
            font-family: inherit;
            font-weight: 500;
            color: var(--body);
            cursor: pointer;
            transition: color .15s, border-color .15s, background .15s;
            white-space: nowrap;
        }

        .btn-sm:hover {
            color: var(--ink);
            border-color: var(--hairline-strong);
            background: var(--canvas-soft-2);
        }

        .btn-sm.copied {
            color: var(--success);
            border-color: var(--success);
        }

        /* ── Location badge ── */
        .location-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: var(--canvas-soft);
            border: 1px solid var(--hairline);
            border-radius: 9999px;
            padding: 2px 10px;
            font-size: 12px;
            font-weight: 400;
            color: var(--body);
            width: fit-content;
        }

        .location-badge svg {
            width: 10px;
            height: 10px;
            color: var(--mute);
        }

        /* ── Description ── */
        .card-description {
            font-size: 13px;
            color: var(--body);
            line-height: 1.55;
            padding-top: 12px;
            border-top: 1px solid var(--hairline);
        }

        /* ── Empty state ── */
        .empty-state {
            grid-column: 1 / -1;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 12px;
            padding: 80px 24px;
            color: var(--mute);
            font-size: 14px;
            text-align: center;
        }

        .empty-icon {
            width: 48px;
            height: 48px;
            opacity: .3;
        }

        /* ── Footer ── */
        footer {
            border-top: 1px solid var(--hairline);
            padding: 28px 32px;
        }

        .footer-inner {
            max-width: 1120px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
        }

        .footer-brand {
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }

        .footer-logo-mark {
            width: 22px;
            height: 22px;
        }

        .footer-logo-mark img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .footer-brand-name {
            font-size: 13px;
            font-weight: 600;
            color: var(--ink);
            letter-spacing: -0.2px;
        }

        .footer-copy {
            font-size: 12px;
            color: var(--mute);
        }

        /* ── Responsive ── */
        @media (max-width: 960px) {
            .wifi-grid { grid-template-columns: repeat(2, 1fr); }
            h1 { font-size: 40px; letter-spacing: -1.8px; }
        }

        @media (max-width: 640px) {
            .nav-inner   { padding: 0 16px; }
            .nav-cta     { font-size: 13px; padding: 0 10px; height: 30px; }
            .nav-icon-btn { width: 32px; height: 32px; }

            .hero        { padding: 48px 16px 36px; }
            h1           { font-size: 32px; letter-spacing: -1.2px; }
            .hero-sub    { font-size: 15px; }

            .search-wrap  { padding: 0 16px 28px; }
            .search-group { max-width: 100%; }

            .grid-section { padding: 0 16px 56px; }
            .wifi-grid    { grid-template-columns: 1fr; gap: 12px; }
            .wifi-card    { padding: 18px; }
            .card-ssid    { font-size: 17px; }

            footer        { padding: 20px 16px; }
            .footer-inner { flex-direction: column; align-items: flex-start; gap: 8px; }
        }
    </style>
</head>
<body>

    <!-- Nav -->
    <nav>
        <div class="nav-inner">
            <a href="/" class="nav-brand">
                <div class="nav-logo-mark">
                    <img src="/images/bl_logo.png" alt="Budi Luhur Logo">
                </div>
                <span class="nav-brand-name">BLIS</span>
            </a>

            <div class="nav-divider"></div>
            <span class="nav-page-label">Daftar WiFi</span>

            <div class="nav-actions">
                <button class="nav-icon-btn" id="theme-toggle" aria-label="Toggle dark mode" type="button">
                    <svg class="icon-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="5"/>
                        <path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/>
                    </svg>
                    <svg class="icon-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
                    </svg>
                </button>

                <a href="/admin" class="nav-cta">Admin Panel</a>
            </div>
        </div>
    </nav>

    <!-- Hero -->
    <section class="hero">
        <div class="hero-eyebrow">
            <span class="hero-eyebrow-dot"></span>
            Jaringan aktif
        </div>
        <h1>Password WiFi</h1>
        <p class="hero-sub">Temukan SSID, lokasi, dan password WiFi yang tersedia di lingkungan SMA dan SMK Budi Luhur.</p>
    </section>

    <!-- Search -->
    <div class="search-wrap">
        <div class="search-group">
            <svg class="search-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round">
                <circle cx="6.5" cy="6.5" r="5.5"/>
                <path d="M14 14l-3-3"/>
            </svg>
            <input
                type="text"
                class="search-input"
                id="search"
                placeholder="Cari SSID atau lokasi…"
                autocomplete="off"
                oninput="filterCards(this.value)"
            >
        </div>
    </div>

    <!-- Grid -->
    <div class="grid-section">
        <div class="wifi-grid" id="wifi-grid">
            @forelse ($wifis as $wifi)
                <article class="wifi-card" data-ssid="{{ strtolower($wifi->ssid) }}" data-location="{{ strtolower($wifi->location) }}">

                    <div class="card-header">
                        <span class="card-ssid">{{ $wifi->ssid }}</span>
                        <div class="wifi-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M5 12.55a11 11 0 0 1 14.08 0"/>
                                <path d="M1.42 9a16 16 0 0 1 21.16 0"/>
                                <path d="M8.53 16.11a6 6 0 0 1 6.95 0"/>
                                <circle cx="12" cy="20" r="1" fill="currentColor" stroke="none"/>
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
                                    <svg viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M6 1a3.5 3.5 0 0 1 3.5 3.5C9.5 7.5 6 11 6 11S2.5 7.5 2.5 4.5A3.5 3.5 0 0 1 6 1z"/>
                                        <circle cx="6" cy="4.5" r="1" fill="currentColor" stroke="none"/>
                                    </svg>
                                    {{ $wifi->location }}
                                </span>
                            </div>
                        </div>

                        <!-- Password -->
                        <div class="meta-row">
                            <span class="meta-label">Password</span>
                            @if ($wifi->password)
                                <div class="password-wrap">
                                    <span
                                        class="password-text hidden"
                                        id="pwd-{{ $wifi->id }}"
                                        data-password="{{ $wifi->password }}"
                                    >••••••••••</span>
                                    <button class="btn-sm" onclick="togglePassword({{ $wifi->id }}, this)" type="button">Tampilkan</button>
                                    <button class="btn-sm" onclick="copyPassword({{ $wifi->id }}, this)" type="button">Salin</button>
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
                    <svg class="empty-icon" viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M10 24.55a20 20 0 0 1 28 0"/>
                        <path d="M2 17a30 30 0 0 1 44 0"/>
                        <path d="M17 32a10 10 0 0 1 14 0"/>
                        <circle cx="24" cy="40" r="2" fill="currentColor" stroke="none"/>
                    </svg>
                    <span>Belum ada data WiFi tersedia.</span>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <div class="footer-inner">
            <a href="/" class="footer-brand">
                <div class="footer-logo-mark">
                    <img src="/images/bl_logo.png" alt="Budi Luhur Logo">
                </div>
                <span class="footer-brand-name">BLIS</span>
            </a>
            <span class="footer-copy">© {{ date('Y') }} Budi Luhur Integrated System</span>
        </div>
    </footer>

    <script>
        // ── Dark mode ──
        (function () {
            const root = document.documentElement;
            const btn  = document.getElementById('theme-toggle');

            btn.addEventListener('click', function () {
                const isDark = root.getAttribute('data-theme') === 'dark';
                if (isDark) {
                    root.removeAttribute('data-theme');
                    localStorage.setItem('blis-theme', 'light');
                } else {
                    root.setAttribute('data-theme', 'dark');
                    localStorage.setItem('blis-theme', 'dark');
                }
            });
        })();

        // ── Password toggle ──
        function togglePassword(id, btn) {
            const el = document.getElementById('pwd-' + id);
            if (el.classList.contains('hidden')) {
                el.textContent = el.dataset.password;
                el.classList.remove('hidden');
                btn.textContent = 'Sembunyikan';
            } else {
                el.textContent = '••••••••••';
                el.classList.add('hidden');
                btn.textContent = 'Tampilkan';
            }
        }

        // ── Copy password ──
        function copyPassword(id, btn) {
            const el = document.getElementById('pwd-' + id);
            navigator.clipboard.writeText(el.dataset.password).then(() => {
                btn.textContent = 'Tersalin!';
                btn.classList.add('copied');
                setTimeout(() => {
                    btn.textContent = 'Salin';
                    btn.classList.remove('copied');
                }, 2000);
            });
        }

        // ── Filter cards ──
        function filterCards(query) {
            const q     = query.toLowerCase().trim();
            const cards = document.querySelectorAll('.wifi-card');
            let visible = 0;

            cards.forEach(card => {
                const match = !q || card.dataset.ssid.includes(q) || card.dataset.location.includes(q);
                card.style.display = match ? '' : 'none';
                if (match) { visible++; }
            });

            let emptyEl = document.getElementById('empty-filter');
            if (visible === 0 && q) {
                if (!emptyEl) {
                    emptyEl = document.createElement('div');
                    emptyEl.id        = 'empty-filter';
                    emptyEl.className = 'empty-state';
                    emptyEl.style.gridColumn = '1 / -1';
                    emptyEl.innerHTML = '<span>Tidak ada hasil untuk "<strong>' + query + '</strong>".</span>';
                    document.getElementById('wifi-grid').appendChild(emptyEl);
                }
            } else if (emptyEl) {
                emptyEl.remove();
            }
        }
    </script>

</body>
</html>
