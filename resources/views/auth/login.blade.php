<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login — {{ config('app.name', 'BLIS') }}</title>
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
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --primary: #171717;
            --on-primary: #ffffff;
            --ink: #171717;
            --body: #4d4d4d;
            --mute: #888888;
            --hairline: #ebebeb;
            --hairline-strong: #a1a1a1;
            --canvas: #ffffff;
            --canvas-soft: #fafafa;
            --canvas-soft-2: #f5f5f5;
            --error: #ee0000;
            --error-soft: #f7d4d6;
            --grad-dev-start: #007cf0;
            --grad-dev-end: #00dfd8;
            --grad-prev-start: #7928ca;
            --grad-prev-end: #ff0080;
            --grad-ship-start: #ff4d4d;
            --grad-ship-end: #f9cb28;
            --shadow-card: 0 1px 1px #00000005, 0 2px 2px #0000000a, inset 0 0 0 1px #00000014;
        }

        [data-theme="dark"] {
            --primary: #ffffff;
            --on-primary: #171717;
            --ink: #ededed;
            --body: #a1a1a1;
            --mute: #666666;
            --hairline: #2a2a2a;
            --hairline-strong: #3d3d3d;
            --canvas: #111111;
            --canvas-soft: #0a0a0a;
            --canvas-soft-2: #161616;
            --error-soft: #4a1d1d;
            --shadow-card: 0 1px 1px #00000030, 0 2px 2px #00000040, inset 0 0 0 1px #ffffff0a;
        }

        ::selection { background: #171717; color: #f2f2f2; }

        html { background: var(--canvas-soft); }

        @view-transition {
            navigation: auto;
        }

        ::view-transition-old(root) {
            animation: page-out .18s ease both;
        }

        ::view-transition-new(root) {
            animation: page-in .32s cubic-bezier(.16, 1, .3, 1) both;
        }

        body {
            min-height: 100vh;
            font-family: 'Geist', Inter, system-ui, -apple-system, sans-serif;
            color: var(--ink);
            background: var(--canvas-soft);
            -webkit-font-smoothing: antialiased;
            transition: background .2s, color .2s;
        }

        @keyframes fade-up {
            from {
                opacity: 0;
                transform: translateY(18px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes page-in {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes page-out {
            to {
                opacity: 0;
                transform: translateY(-6px);
            }
        }

        @keyframes mesh-drift {
            0%, 100% { transform: translate3d(0, 0, 0) scale(1); }
            50% { transform: translate3d(-18px, -14px, 0) scale(1.04); }
        }

        @keyframes status-pulse {
            0%, 100% {
                box-shadow: 0 0 0 0 #00dfd855;
                transform: scale(1);
            }

            50% {
                box-shadow: 0 0 0 6px #00dfd800;
                transform: scale(1.08);
            }
        }

        .page {
            position: relative;
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 32px 16px;
            overflow: hidden;
        }

        .mesh {
            position: absolute;
            inset: auto -160px -180px auto;
            width: 720px;
            height: 520px;
            background:
                radial-gradient(ellipse at 55% 35%, #007cf055 0%, transparent 54%),
                radial-gradient(ellipse at 25% 70%, #7928ca35 0%, transparent 50%),
                radial-gradient(ellipse at 82% 68%, #ff00802f 0%, transparent 46%),
                radial-gradient(ellipse at 18% 30%, #00dfd82d 0%, transparent 50%),
                radial-gradient(ellipse at 72% 20%, #f9cb2822 0%, transparent 45%);
            filter: blur(48px);
            pointer-events: none;
            opacity: .65;
            animation: mesh-drift 12s ease-in-out infinite;
        }

        [data-theme="dark"] .mesh { opacity: .38; }

        .shell {
            position: relative;
            z-index: 1;
            width: min(100%, 420px);
        }

        .brand {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-bottom: 28px;
            text-decoration: none;
            color: var(--ink);
            opacity: 0;
            animation: fade-up .65s cubic-bezier(.16, 1, .3, 1) .08s both;
        }

        .brand img {
            width: 34px;
            height: 34px;
            object-fit: contain;
        }

        .brand span {
            font-size: 15px;
            font-weight: 600;
            letter-spacing: 0;
        }

        .panel {
            background: var(--canvas);
            border: 1px solid var(--hairline);
            border-radius: 8px;
            box-shadow: var(--shadow-card);
            padding: 28px;
            opacity: 0;
            animation: fade-up .72s cubic-bezier(.16, 1, .3, 1) .18s both;
        }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: 1px solid var(--hairline);
            border-radius: 9999px;
            padding: 4px 10px;
            margin-bottom: 20px;
            color: var(--body);
            font-size: 12px;
            font-weight: 500;
        }

        .eyebrow::before {
            content: '';
            width: 6px;
            height: 6px;
            border-radius: 9999px;
            background: linear-gradient(90deg, var(--grad-dev-start), var(--grad-dev-end));
            animation: status-pulse 2.4s ease-in-out infinite;
        }

        h1 {
            font-size: 32px;
            line-height: 40px;
            font-weight: 600;
            letter-spacing: 0;
            margin-bottom: 10px;
        }

        .lede {
            color: var(--body);
            font-size: 14px;
            line-height: 22px;
            margin-bottom: 24px;
        }

        .error {
            border: 1px solid var(--error-soft);
            border-radius: 6px;
            background: #fff8f8;
            color: var(--error);
            font-size: 13px;
            line-height: 20px;
            padding: 10px 12px;
            margin-bottom: 16px;
        }

        .google-button {
            width: 100%;
            height: 44px;
            border-radius: 9999px;
            font: inherit;
            font-size: 15px;
            font-weight: 500;
            cursor: pointer;
            transition: opacity .15s, transform .18s ease;
        }

        .google-button:hover {
            opacity: .86;
            transform: translateY(-1px);
        }

        .google-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            background: var(--primary);
            color: var(--on-primary);
            border: 1px solid var(--primary);
            gap: 10px;
            box-shadow: var(--shadow-card);
        }

        .google-button svg {
            width: 18px;
            height: 18px;
            flex-shrink: 0;
        }

        .home-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            margin-top: 14px;
            color: var(--body);
            font-size: 13px;
            font-weight: 500;
            text-decoration: none;
            transition: color .15s, transform .18s ease;
        }

        .home-link:hover {
            color: var(--ink);
            transform: translateY(-1px);
        }

        .footnote {
            margin-top: 18px;
            color: var(--mute);
            font-size: 12px;
            line-height: 18px;
            text-align: center;
            opacity: 0;
            animation: fade-up .65s cubic-bezier(.16, 1, .3, 1) .3s both;
        }

        .theme-toggle {
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
            margin: 18px auto 0;
            opacity: 0;
            animation: fade-up .65s cubic-bezier(.16, 1, .3, 1) .38s both;
        }

        .theme-toggle:hover {
            border-color: var(--hairline-strong);
            color: var(--ink);
            background: var(--canvas-soft-2);
        }

        .theme-toggle svg {
            width: 16px;
            height: 16px;
        }

        .theme-toggle .icon-sun { display: none; }
        .theme-toggle .icon-moon { display: block; }
        [data-theme="dark"] .theme-toggle .icon-sun { display: block; }
        [data-theme="dark"] .theme-toggle .icon-moon { display: none; }

        @media (prefers-reduced-motion: reduce) {
            *,
            *::before,
            *::after {
                animation-duration: .01ms !important;
                animation-iteration-count: 1 !important;
                scroll-behavior: auto !important;
                transition-duration: .01ms !important;
            }

            .brand,
            .panel,
            .footnote,
            .theme-toggle {
                opacity: 1;
                transform: none;
            }
        }

        @media (max-width: 480px) {
            .panel { padding: 22px; }
            h1 { font-size: 28px; line-height: 34px; }
        }
    </style>
</head>

<body>
    <main class="page">
        <div class="mesh" aria-hidden="true"></div>

        <div class="shell">
            <a href="{{ route('home') }}" class="brand" aria-label="Kembali ke beranda BLIS">
                <img src="/images/bl_logo.png" alt="Budi Luhur Logo">
                <span>BLIS</span>
            </a>

            <section class="panel" aria-labelledby="login-title">
                <div class="eyebrow">Staff portal</div>

                <h1 id="login-title">Masuk ke BLIS</h1>
                <p class="lede">Gunakan akun Google guru atau staff yang sudah terdaftar di sistem kepegawaian.</p>

                @if ($errors->any())
                    <div class="error">
                        {{ $errors->first() }}
                    </div>
                @endif

                <a href="{{ route('login.google') }}" class="google-button" aria-label="Masuk dengan Google">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path fill="#4285F4" d="M21.6 12.23c0-.78-.07-1.53-.2-2.23H12v4.22h5.38a4.6 4.6 0 0 1-2 3.02v2.51h3.24c1.9-1.75 2.98-4.32 2.98-7.52z"/>
                        <path fill="#34A853" d="M12 22c2.7 0 4.96-.9 6.62-2.25l-3.24-2.51c-.9.6-2.05.96-3.38.96-2.6 0-4.8-1.76-5.59-4.12H3.07v2.59A10 10 0 0 0 12 22z"/>
                        <path fill="#FBBC05" d="M6.41 14.08A6 6 0 0 1 6.1 12c0-.72.11-1.42.31-2.08V7.33H3.07A10 10 0 0 0 2 12c0 1.61.39 3.14 1.07 4.67l3.34-2.59z"/>
                        <path fill="#EA4335" d="M12 5.8c1.47 0 2.8.51 3.84 1.5l2.87-2.87A9.62 9.62 0 0 0 12 2a10 10 0 0 0-8.93 5.33l3.34 2.59C7.2 7.56 9.4 5.8 12 5.8z"/>
                    </svg>
                    Masuk dengan Google
                </a>

                <a href="{{ route('home') }}" class="home-link">Kembali ke Beranda</a>
            </section>

            <p class="footnote">Akses siswa dengan prefix blsma atau blsmk tidak diizinkan.</p>

            <button class="theme-toggle" id="theme-toggle" aria-label="Toggle dark mode" type="button">
                <svg class="icon-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="5"/>
                    <path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/>
                </svg>
                <svg class="icon-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
                </svg>
            </button>
        </div>
    </main>
    <script>
        (function () {
            const root = document.documentElement;
            const btn = document.getElementById('theme-toggle');

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
    </script>
</body>

</html>
