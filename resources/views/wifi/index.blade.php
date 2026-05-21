<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar WiFi — BLIS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --primary:          #5e6ad2;
            --primary-hover:    #828fff;
            --primary-focus:    #5e69d1;
            --ink:              #f7f8f8;
            --ink-muted:        #d0d6e0;
            --ink-subtle:       #8a8f98;
            --ink-tertiary:     #62666d;
            --canvas:           #010102;
            --surface-1:        #0f1011;
            --surface-2:        #141516;
            --surface-3:        #18191a;
            --hairline:         #23252a;
            --hairline-strong:  #34343a;
            --semantic-success: #27a644;
        }

        html { scroll-behavior: smooth; }

        body {
            background-color: var(--canvas);
            color: var(--ink);
            font-family: 'Inter', -apple-system, system-ui, 'Segoe UI', Roboto, sans-serif;
            font-size: 16px;
            line-height: 1.5;
            letter-spacing: -0.05px;
            min-height: 100vh;
        }

        /* ── Nav ── */
        nav {
            background-color: var(--canvas);
            border-bottom: 1px solid var(--hairline);
            height: 56px;
            display: flex;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .nav-inner {
            max-width: 1280px;
            width: 100%;
            margin: 0 auto;
            padding: 0 32px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .nav-logo {
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }

        .nav-logo-mark {
            width: 24px;
            height: 24px;
            background: var(--primary);
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .nav-logo-mark svg {
            width: 14px;
            height: 14px;
            fill: #fff;
        }

        .nav-logo-text {
            font-size: 14px;
            font-weight: 600;
            color: var(--ink);
            letter-spacing: -0.3px;
        }

        .nav-divider {
            width: 1px;
            height: 18px;
            background: var(--hairline-strong);
        }

        .nav-label {
            font-size: 14px;
            font-weight: 400;
            color: var(--ink-subtle);
        }

        /* ── Hero ── */
        .hero {
            max-width: 1280px;
            margin: 0 auto;
            padding: 96px 32px 64px;
        }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            font-weight: 500;
            color: var(--primary);
            letter-spacing: 0.4px;
            text-transform: uppercase;
            margin-bottom: 16px;
        }

        .eyebrow-dot {
            width: 6px;
            height: 6px;
            background: var(--semantic-success);
            border-radius: 9999px;
        }

        h1 {
            font-size: 56px;
            font-weight: 600;
            line-height: 1.10;
            letter-spacing: -1.8px;
            color: var(--ink);
            margin-bottom: 16px;
        }

        .hero-sub {
            font-size: 18px;
            font-weight: 400;
            line-height: 1.5;
            letter-spacing: -0.1px;
            color: var(--ink-muted);
            max-width: 520px;
        }

        /* ── Search ── */
        .search-wrap {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 32px 48px;
        }

        .search-input {
            width: 100%;
            max-width: 420px;
            background: var(--surface-1);
            color: var(--ink);
            border: 1px solid var(--hairline);
            border-radius: 8px;
            padding: 8px 12px 8px 36px;
            font-size: 14px;
            font-family: inherit;
            outline: none;
            transition: border-color .15s;
            position: relative;
        }

        .search-input:focus {
            border-color: var(--hairline-strong);
            box-shadow: 0 0 0 2px rgba(94, 106, 210, .25);
        }

        .search-input::placeholder { color: var(--ink-tertiary); }

        .search-group {
            position: relative;
            display: inline-block;
            width: 100%;
            max-width: 420px;
        }

        .search-icon {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--ink-tertiary);
            pointer-events: none;
        }

        /* ── Grid ── */
        .grid-section {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 32px 96px;
        }

        .wifi-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
        }

        /* ── Card ── */
        .wifi-card {
            background: var(--surface-1);
            border: 1px solid var(--hairline);
            border-radius: 12px;
            padding: 24px;
            display: flex;
            flex-direction: column;
            gap: 16px;
            transition: border-color .15s, background .15s;
        }

        .wifi-card:hover {
            background: var(--surface-2);
            border-color: var(--hairline-strong);
        }

        .card-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
        }

        .card-ssid {
            font-size: 22px;
            font-weight: 500;
            line-height: 1.25;
            letter-spacing: -0.4px;
            color: var(--ink);
            word-break: break-all;
        }

        .wifi-icon {
            flex-shrink: 0;
            width: 36px;
            height: 36px;
            background: var(--surface-3);
            border: 1px solid var(--hairline);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .wifi-icon svg {
            width: 18px;
            height: 18px;
            color: var(--primary);
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
            font-size: 12px;
            font-weight: 500;
            color: var(--ink-tertiary);
            letter-spacing: 0.4px;
            text-transform: uppercase;
        }

        .meta-value {
            font-size: 14px;
            color: var(--ink-muted);
            line-height: 1.5;
        }

        .meta-value.mono {
            font-family: 'JetBrains Mono', 'SF Mono', ui-monospace, Menlo, monospace;
            font-size: 13px;
            color: var(--ink);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* ── Password reveal ── */
        .password-wrap {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .password-text {
            font-family: 'JetBrains Mono', 'SF Mono', ui-monospace, Menlo, monospace;
            font-size: 13px;
            color: var(--ink);
            letter-spacing: 0.5px;
            word-break: break-all;
        }

        .password-text.hidden { letter-spacing: 3px; }

        .btn-reveal {
            flex-shrink: 0;
            background: var(--surface-2);
            border: 1px solid var(--hairline);
            border-radius: 6px;
            padding: 3px 8px;
            font-size: 12px;
            font-family: inherit;
            font-weight: 500;
            color: var(--ink-subtle);
            cursor: pointer;
            transition: color .15s, border-color .15s;
            white-space: nowrap;
        }

        .btn-reveal:hover {
            color: var(--ink);
            border-color: var(--hairline-strong);
        }

        /* ── Copy btn ── */
        .btn-copy {
            flex-shrink: 0;
            background: transparent;
            border: 1px solid var(--hairline);
            border-radius: 6px;
            padding: 3px 8px;
            font-size: 12px;
            font-family: inherit;
            font-weight: 500;
            color: var(--ink-subtle);
            cursor: pointer;
            transition: color .15s, border-color .15s, background .15s;
        }

        .btn-copy:hover {
            color: var(--ink);
            border-color: var(--hairline-strong);
            background: var(--surface-2);
        }

        .btn-copy.copied {
            color: var(--semantic-success);
            border-color: var(--semantic-success);
        }

        /* ── Location badge ── */
        .location-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: var(--surface-2);
            border: 1px solid var(--hairline);
            border-radius: 9999px;
            padding: 2px 10px;
            font-size: 12px;
            font-weight: 400;
            color: var(--ink-muted);
            width: fit-content;
        }

        .location-badge svg {
            width: 10px;
            height: 10px;
            color: var(--ink-subtle);
        }

        /* ── Description ── */
        .card-description {
            font-size: 14px;
            color: var(--ink-subtle);
            line-height: 1.5;
            padding-top: 4px;
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
            color: var(--ink-tertiary);
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
            padding: 32px;
            text-align: center;
        }

        footer p {
            font-size: 12px;
            color: var(--ink-tertiary);
        }

        /* ── Responsive ── */
        @media (max-width: 900px) {
            .wifi-grid { grid-template-columns: repeat(2, 1fr); }
            h1 { font-size: 40px; letter-spacing: -1.0px; }
        }

        @media (max-width: 600px) {
            .wifi-grid { grid-template-columns: 1fr; }
            h1 { font-size: 32px; letter-spacing: -0.8px; }
            .hero, .search-wrap, .grid-section { padding-left: 16px; padding-right: 16px; }
            .nav-inner { padding: 0 16px; }
            footer { padding: 24px 16px; }
        }
    </style>
</head>
<body>

    <!-- Nav -->
    <nav>
        <div class="nav-inner">
            <a href="/" class="nav-logo">
                <div class="nav-logo-mark">
                    <svg viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg">
                        <path d="M8 2C4.686 2 2 4.686 2 8s2.686 6 6 6 6-2.686 6-6-2.686-6-6-6zm0 10.5A4.5 4.5 0 1 1 8 3.5a4.5 4.5 0 0 1 0 9z"/>
                    </svg>
                </div>
                <span class="nav-logo-text">BLIS</span>
            </a>
            <div class="nav-divider"></div>
            <span class="nav-label">Daftar WiFi</span>
        </div>
    </nav>

    <!-- Hero -->
    <section class="hero">
        <div class="eyebrow">
            <span class="eyebrow-dot"></span>
            Jaringan Aktif
        </div>
        <h1>Password WiFi</h1>
        <p class="hero-sub">Temukan SSID, lokasi, dan password WiFi yang tersedia di lingkungan SMA dan SMK Budi Luhur.</p>
    </section>

    <!-- Search -->
    <div class="search-wrap">
        <div class="search-group">
            <svg class="search-icon" width="14" height="14" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M7 13A6 6 0 1 0 7 1a6 6 0 0 0 0 12zM15 15l-3.5-3.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
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
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" xmlns="http://www.w3.org/2000/svg">
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
                                    <svg viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" xmlns="http://www.w3.org/2000/svg">
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
                                    <button
                                        class="btn-reveal"
                                        onclick="togglePassword({{ $wifi->id }}, this)"
                                        type="button"
                                    >Tampilkan</button>
                                    <button
                                        class="btn-copy"
                                        onclick="copyPassword({{ $wifi->id }}, this)"
                                        type="button"
                                    >Salin</button>
                                </div>
                            @else
                                <span class="meta-value" style="color: var(--ink-tertiary);">Tidak ada password</span>
                            @endif
                        </div>
                    </div>

                    @if ($wifi->description)
                        <p class="card-description">{{ $wifi->description }}</p>
                    @endif

                </article>
            @empty
                <div class="empty-state">
                    <svg class="empty-icon" viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" xmlns="http://www.w3.org/2000/svg">
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
        <p>© {{ date('Y') }} BLIS — Budi Luhur Integrated System</p>
    </footer>

    <script>
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

        function filterCards(query) {
            const q = query.toLowerCase().trim();
            const cards = document.querySelectorAll('.wifi-card');
            let visible = 0;

            cards.forEach(card => {
                const match = !q
                    || card.dataset.ssid.includes(q)
                    || card.dataset.location.includes(q);
                card.style.display = match ? '' : 'none';
                if (match) visible++;
            });

            // Show/hide empty state
            let emptyEl = document.getElementById('empty-filter');
            if (visible === 0 && q) {
                if (!emptyEl) {
                    emptyEl = document.createElement('div');
                    emptyEl.id = 'empty-filter';
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
