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
    <style>
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

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
            --link: #0070f3;
            --link-bg-soft: #d3e5ff;
            --cyan: #50e3c2;
            --violet: #7928ca;
            --highlight-pink: #ff0080;
            --grad-dev-start: #007cf0;
            --grad-dev-end: #00dfd8;
            --grad-prev-start: #7928ca;
            --grad-prev-end: #ff0080;
            --grad-ship-start: #ff4d4d;
            --grad-ship-end: #f9cb28;
            --shadow-card: 0 1px 1px #00000005, 0 2px 2px #0000000a, inset 0 0 0 1px #00000014;
            --shadow-card-hover: 0 1px 1px #0000000a, 0 4px 8px #00000010, inset 0 0 0 1px #00000018;
        }

        /* ── Dark mode tokens ── */
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

        [data-theme="dark"] .hero-gradient {
            opacity: 0.45;
        }

        ::selection {
            background: #171717;
            color: #f2f2f2;
        }

        html {
            background: var(--canvas-soft);
        }

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

        /* ── Theme toggle button ── */
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
            flex-shrink: 0;
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

        .theme-toggle .icon-sun  { display: none; }
        .theme-toggle .icon-moon { display: block; }

        [data-theme="dark"] .theme-toggle .icon-sun  { display: block; }
        [data-theme="dark"] .theme-toggle .icon-moon { display: none; }

        /* ── Search button ── */
        .search-btn {
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
        }

        .search-btn:hover {
            border-color: var(--hairline-strong);
            color: var(--ink);
            background: var(--canvas-soft-2);
        }

        .search-btn svg { width: 16px; height: 16px; }

        /* ── Search overlay ── */
        .search-overlay {
            position: fixed;
            inset: 0;
            z-index: 200;
            background: rgba(0, 0, 0, 0.4);
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
            display: flex;
            align-items: flex-start;
            justify-content: center;
            padding-top: 80px;
            opacity: 0;
            pointer-events: none;
            transition: opacity .2s;
        }

        .search-overlay.open {
            opacity: 1;
            pointer-events: all;
        }

        .search-modal {
            background: var(--canvas);
            border: 1px solid var(--hairline);
            border-radius: 16px;
            box-shadow: 0 8px 32px #00000020, 0 2px 8px #00000010;
            width: 100%;
            max-width: 560px;
            margin: 0 16px;
            overflow: hidden;
            transform: translateY(-8px);
            transition: transform .2s;
        }

        .search-overlay.open .search-modal {
            transform: translateY(0);
        }

        .search-modal-input-wrap {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 16px 20px;
            border-bottom: 1px solid var(--hairline);
        }

        .search-modal-input-wrap svg {
            width: 18px;
            height: 18px;
            color: var(--mute);
            flex-shrink: 0;
        }

        .search-modal-input {
            flex: 1;
            border: none;
            outline: none;
            background: transparent;
            font-family: 'Geist', inherit;
            font-size: 16px;
            color: var(--ink);
        }

        .search-modal-input::placeholder { color: var(--mute); }

        .search-kbd {
            font-family: 'Geist Mono', ui-monospace, monospace;
            font-size: 11px;
            color: var(--mute);
            background: var(--canvas-soft-2);
            border: 1px solid var(--hairline);
            border-radius: 4px;
            padding: 2px 6px;
            flex-shrink: 0;
        }

        .search-results {
            max-height: 400px;
            overflow-y: auto;
            padding: 8px;
        }

        .search-result-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 12px;
            border-radius: 8px;
            text-decoration: none;
            color: var(--ink);
            transition: background .1s;
            cursor: pointer;
        }

        .search-result-item:hover,
        .search-result-item.active {
            background: var(--canvas-soft-2);
        }

        .search-result-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 14px;
        }

        .search-result-icon svg { width: 16px; height: 16px; }

        .search-result-body { flex: 1; min-width: 0; }

        .search-result-name {
            font-size: 14px;
            font-weight: 500;
            color: var(--ink);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .search-result-meta {
            font-family: 'Geist Mono', ui-monospace, monospace;
            font-size: 11px;
            color: var(--mute);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .search-result-badge {
            font-size: 11px;
            color: var(--mute);
            background: var(--canvas-soft);
            border: 1px solid var(--hairline);
            border-radius: 9999px;
            padding: 1px 8px;
            flex-shrink: 0;
        }

        .search-empty {
            padding: 40px 20px;
            text-align: center;
            font-size: 14px;
            color: var(--mute);
        }

        .search-footer {
            padding: 10px 20px;
            border-top: 1px solid var(--hairline);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .search-footer-hint {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 12px;
            color: var(--mute);
        }

        .search-footer-hint kbd {
            font-family: 'Geist Mono', ui-monospace, monospace;
            font-size: 11px;
            background: var(--canvas-soft-2);
            border: 1px solid var(--hairline);
            border-radius: 4px;
            padding: 1px 5px;
        }

        @media (max-width: 640px) {
            .search-overlay { padding-top: 16px; align-items: flex-start; }
            .search-modal   { border-radius: 12px; margin: 0 12px; }
            .search-results { max-height: 55vh; }
            .search-footer  { display: none; }
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
            justify-content: space-between;
        }

        .nav-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
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

        .nav-links {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .nav-link {
            font-size: 14px;
            font-weight: 400;
            color: var(--body);
            text-decoration: none;
            padding: 8px 12px;
            border-radius: 9999px;
            transition: color .15s, background .15s;
        }

        .nav-link:hover {
            color: var(--ink);
            background: var(--canvas-soft-2);
        }

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

        .nav-cta:hover {
            opacity: 0.85;
        }

        /* ── Hero ── */
        .hero {
            max-width: 1120px;
            width: 100%;
            margin: 0 auto;
            padding: 96px 32px 80px;
            position: relative;
        }

        /* Mesh gradient orb */
        .hero-gradient {
            position: absolute;
            top: -60px;
            right: -80px;
            width: 700px;
            height: 560px;
            background: radial-gradient(ellipse at 60% 40%, #007cf060 0%, transparent 55%),
                radial-gradient(ellipse at 30% 70%, #7928ca40 0%, transparent 50%),
                radial-gradient(ellipse at 80% 70%, #ff008030 0%, transparent 45%),
                radial-gradient(ellipse at 20% 30%, #00dfd830 0%, transparent 50%),
                radial-gradient(ellipse at 70% 20%, #f9cb2820 0%, transparent 45%);
            pointer-events: none;
            filter: blur(48px);
            opacity: 0.65;
            z-index: 0;
        }

        .hero-content {
            position: relative;
            z-index: 1;
            max-width: 680px;
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
            margin-bottom: 24px;
            box-shadow: var(--shadow-card);
        }

        .hero-eyebrow-dot {
            width: 6px;
            height: 6px;
            background: #27a644;
            border-radius: 9999px;
            flex-shrink: 0;
        }

        .hero-eyebrow-mono {
            font-family: 'Geist Mono', ui-monospace, monospace;
            font-size: 11px;
            color: var(--mute);
        }

        h1 {
            font-size: 56px;
            font-weight: 600;
            line-height: 1.05;
            letter-spacing: -2.4px;
            color: var(--ink);
            margin-bottom: 20px;
        }

        h1 .gradient-text {
            background: linear-gradient(90deg, var(--grad-dev-start), var(--grad-prev-end), var(--grad-ship-start));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero-sub {
            font-size: 18px;
            font-weight: 400;
            line-height: 1.6;
            color: var(--body);
            max-width: 540px;
            margin-bottom: 40px;
        }

        .hero-actions {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .btn-primary {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            height: 44px;
            padding: 0 20px;
            background: var(--primary);
            color: var(--on-primary);
            font-size: 15px;
            font-weight: 500;
            font-family: inherit;
            text-decoration: none;
            border-radius: 9999px;
            border: none;
            cursor: pointer;
            transition: opacity .15s;
        }

        .btn-primary:hover {
            opacity: 0.85;
        }

        .btn-secondary {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            height: 44px;
            padding: 0 20px;
            background: var(--canvas);
            color: var(--ink);
            font-size: 15px;
            font-weight: 500;
            font-family: inherit;
            text-decoration: none;
            border-radius: 9999px;
            border: 1px solid var(--hairline);
            cursor: pointer;
            box-shadow: var(--shadow-card);
            transition: border-color .15s, box-shadow .15s;
        }

        .btn-secondary:hover {
            border-color: var(--hairline-strong);
            box-shadow: var(--shadow-card-hover);
        }

        /* ── Cards section ── */
        .cards-section {
            max-width: 1120px;
            width: 100%;
            margin: 0 auto;
            padding: 0 32px 96px;
        }

        .section-eyebrow {
            font-family: 'Geist Mono', ui-monospace, monospace;
            font-size: 12px;
            font-weight: 400;
            color: var(--mute);
            letter-spacing: 0.5px;
            text-transform: uppercase;
            margin-bottom: 12px;
        }

        .section-title {
            font-size: 32px;
            font-weight: 600;
            line-height: 1.25;
            letter-spacing: -1.28px;
            color: var(--ink);
            margin-bottom: 40px;
        }

        .cards-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
        }

        /* ── Card ── */
        .app-card {
            background: var(--canvas);
            border-radius: 12px;
            padding: 28px;
            display: flex;
            flex-direction: column;
            gap: 20px;
            text-decoration: none;
            box-shadow: var(--shadow-card);
            transition: box-shadow .2s, transform .2s;
            position: relative;
            overflow: hidden;
        }

        .app-card::before {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--card-grad-start, transparent) 0%, var(--card-grad-end, transparent) 100%);
            opacity: 0;
            transition: opacity .25s;
            pointer-events: none;
        }

        .app-card:hover {
            box-shadow: var(--shadow-card-hover);
            transform: translateY(-2px);
        }

        .app-card:hover::before {
            opacity: 1;
        }

        /* Per-card gradient tints (cycling by index mod 5) */
        .card-tint-0 {
            --card-grad-start: #007cf008;
            --card-grad-end: #00dfd808;
        }

        .card-tint-1 {
            --card-grad-start: #7928ca08;
            --card-grad-end: #ff008008;
        }

        .card-tint-2 {
            --card-grad-start: #ff4d4d08;
            --card-grad-end: #f9cb2808;
        }

        .card-tint-3 {
            --card-grad-start: #0070f308;
            --card-grad-end: #50e3c208;
        }

        .card-tint-4 {
            --card-grad-start: #f5a62308;
            --card-grad-end: #ff4d4d08;
        }

        .card-icon-tint-0 {
            background: linear-gradient(135deg, #007cf015, #00dfd820);
            color: #007cf0;
        }

        .card-icon-tint-1 {
            background: linear-gradient(135deg, #7928ca15, #ff008020);
            color: #7928ca;
        }

        .card-icon-tint-2 {
            background: linear-gradient(135deg, #ff4d4d15, #f9cb2820);
            color: #ff4d4d;
        }

        .card-icon-tint-3 {
            background: linear-gradient(135deg, #0070f315, #50e3c220);
            color: #0070f3;
        }

        .card-icon-tint-4 {
            background: linear-gradient(135deg, #f5a62315, #ff4d4d20);
            color: #f5a623;
        }

        .card-icon {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .card-icon svg {
            width: 22px;
            height: 22px;
        }

        .card-icon-qr {
            background: linear-gradient(135deg, #007cf015, #00dfd820);
            color: #007cf0;
        }

        .card-icon-wifi {
            background: linear-gradient(135deg, #7928ca15, #ff008020);
            color: #7928ca;
        }

        .card-icon-admin {
            background: linear-gradient(135deg, #ff4d4d15, #f9cb2820);
            color: #ff4d4d;
        }

        .card-body {
            flex: 1;
        }

        .card-title {
            font-size: 17px;
            font-weight: 600;
            color: var(--ink);
            letter-spacing: -0.4px;
            margin-bottom: 6px;
        }

        .card-desc {
            font-size: 14px;
            color: var(--body);
            line-height: 1.55;
        }

        .card-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-top: 16px;
            border-top: 1px solid var(--hairline);
        }

        .card-tag {
            font-family: 'Geist Mono', ui-monospace, monospace;
            font-size: 11px;
            color: var(--mute);
            letter-spacing: 0.3px;
        }

        .card-arrow {
            width: 28px;
            height: 28px;
            border-radius: 9999px;
            background: var(--canvas-soft);
            border: 1px solid var(--hairline);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--body);
            transition: background .15s, border-color .15s, color .15s, transform .2s;
        }

        .app-card:hover .card-arrow {
            background: var(--primary);
            border-color: var(--primary);
            color: var(--on-primary);
            transform: translateX(2px);
        }

        .card-arrow svg {
            width: 14px;
            height: 14px;
        }

        /* ── Stats band ── */
        .stats-section {
            background: var(--canvas);
            border-top: 1px solid var(--hairline);
            border-bottom: 1px solid var(--hairline);
            padding: 48px 32px;
            margin-bottom: 96px;
        }

        .stats-inner {
            max-width: 1120px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 0;
        }

        .stat-item {
            padding: 0 40px;
            border-right: 1px solid var(--hairline);
        }

        .stat-item:first-child {
            padding-left: 0;
        }

        .stat-item:last-child {
            border-right: none;
        }

        .stat-number {
            font-size: 36px;
            font-weight: 600;
            letter-spacing: -1.5px;
            color: var(--ink);
            margin-bottom: 4px;
        }

        .stat-number span {
            background: linear-gradient(90deg, var(--grad-dev-start), var(--grad-prev-end));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .stat-label {
            font-size: 14px;
            color: var(--body);
        }

        /* ── Footer ── */
        footer {
            margin-top: auto;
            border-top: 1px solid var(--hairline);
            padding: 32px;
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
            width: 24px;
            height: 24px;
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

        .footer-links {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .footer-link {
            font-size: 12px;
            color: var(--body);
            text-decoration: none;
            transition: color .15s;
        }

        .footer-link:hover {
            color: var(--ink);
        }

        /* ── Responsive ── */
        /* ── Tablet ── */
        @media (max-width: 960px) {
            .cards-grid { grid-template-columns: repeat(2, 1fr); }
            .stats-inner { grid-template-columns: repeat(3, 1fr); }

            h1 {
                font-size: 44px;
                letter-spacing: -1.8px;
            }
        }

        /* ── Mobile ── */
        @media (max-width: 640px) {
            /* Nav */
            .nav-inner { padding: 0 16px; }
            .nav-links  { display: none; }

            .nav-cta {
                font-size: 13px;
                padding: 0 10px;
                height: 30px;
            }

            .theme-toggle,
            .search-btn {
                width: 32px;
                height: 32px;
            }

            /* Hero */
            .hero {
                padding: 48px 16px 40px;
            }

            .hero-gradient {
                width: 100%;
                right: 0;
                top: -20px;
                opacity: 0.5;
            }

            h1 {
                font-size: 34px;
                letter-spacing: -1.4px;
            }

            .hero-sub {
                font-size: 15px;
                margin-bottom: 28px;
            }

            .btn-primary,
            .btn-secondary {
                height: 40px;
                font-size: 14px;
                padding: 0 16px;
            }

            /* Cards */
            .cards-section {
                padding: 0 16px 56px;
            }

            .section-title {
                font-size: 24px;
                letter-spacing: -0.8px;
                margin-bottom: 20px;
            }

            .cards-grid {
                grid-template-columns: 1fr;
                gap: 12px;
            }

            .app-card {
                padding: 20px;
                gap: 14px;
            }

            .card-icon {
                width: 36px;
                height: 36px;
            }

            .card-icon svg { width: 18px; height: 18px; }

            .card-title { font-size: 15px; }
            .card-desc  { font-size: 13px; }

            /* Stats */
            .stats-section {
                padding: 32px 16px;
                margin-bottom: 0;
            }

            .stats-inner {
                grid-template-columns: repeat(3, 1fr);
                gap: 0;
            }

            .stat-item {
                padding: 0 16px;
            }

            .stat-item:first-child { padding-left: 0; }
            .stat-item:last-child  { border-right: none; }

            .stat-number {
                font-size: 24px;
                letter-spacing: -1px;
            }

            .stat-label { font-size: 12px; }

            /* Footer */
            footer { padding: 20px 16px; }

            .footer-inner {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }

            .footer-links {
                flex-wrap: wrap;
                gap: 12px 16px;
            }
        }

        /* ── Very small (≤ 380px) ── */
        @media (max-width: 380px) {
            h1 { font-size: 28px; letter-spacing: -1px; }

            .stats-inner { grid-template-columns: 1fr; gap: 16px; }

            .stat-item {
                padding: 0;
                border-right: none;
                border-bottom: 1px solid var(--hairline);
                padding-bottom: 16px;
            }

            .stat-item:last-child { border-bottom: none; padding-bottom: 0; }
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

            <div class="nav-links">
                <a href="/qr-absensi" class="nav-link">QR Absensi</a>
                <a href="/wifi" class="nav-link">WiFi</a>
            </div>

            <div style="display: flex; align-items: center; gap: 8px;">
                <button class="search-btn" id="search-open-btn" aria-label="Cari layanan" type="button">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"/>
                        <path d="M21 21l-4.35-4.35"/>
                    </svg>
                </button>

                <button class="theme-toggle" id="theme-toggle" aria-label="Toggle dark mode" type="button">
                    <!-- Sun icon (shown in dark mode) -->
                    <svg class="icon-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="5"/>
                        <path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/>
                    </svg>
                    <!-- Moon icon (shown in light mode) -->
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
                <a href="/qr-absensi" class="btn-primary">
                    Mulai Absensi
                    <svg width="14" height="14" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M3 8H13M8.5 3.5L13 8L8.5 12.5" stroke="currentColor" stroke-width="1.75"
                            stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </a>
                <a href="/wifi" class="btn-secondary">
                    Lihat WiFi
                </a>
            </div>
        </div>
    </section>

    <!-- Cards -->
    <section class="cards-section">
        <p class="section-eyebrow">Layanan</p>
        <h2 class="section-title">Akses cepat</h2>

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
                <a href="/qr-absensi" class="app-card card-tint-0">
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

                <a href="/wifi" class="app-card card-tint-1">
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
            @endforelse

        </div>
    </section>

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

            <div class="footer-links">
                @forelse ($websites as $website)
                    <a href="{{ $website->url }}" class="footer-link" target="_blank"
                        rel="noopener">{{ $website->name }}</a>
                @empty
                    <a href="/qr-absensi" class="footer-link">QR Absensi</a>
                    <a href="/wifi" class="footer-link">WiFi</a>
                    <a href="/admin" class="footer-link">Admin</a>
                @endforelse
            </div>
        </div>
    </footer>

    <!-- Search overlay -->
    <div class="search-overlay" id="search-overlay" role="dialog" aria-modal="true" aria-label="Cari layanan">
        <div class="search-modal" id="search-modal">
            <div class="search-modal-input-wrap">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"/>
                    <path d="M21 21l-4.35-4.35"/>
                </svg>
                <input
                    type="text"
                    class="search-modal-input"
                    id="search-input"
                    placeholder="Cari layanan atau website…"
                    autocomplete="off"
                    spellcheck="false"
                >
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

    <script>
        // ── Dark mode ──
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

        // ── Search ──
        (function () {
            const websites = @json($websitesJson);

            // Fallback when table is empty
            const fallback = [
                { name: 'QR Absensi', url: '/qr-absensi', category: 'Absensi & Kehadiran', host: '/qr-absensi' },
                { name: 'Daftar WiFi', url: '/wifi',       category: 'Jaringan & Infrastruktur', host: '/wifi' },
                { name: 'Admin Panel', url: '/admin',      category: 'Sistem Internal', host: '/admin' },
            ];

            const items = websites.length ? websites : fallback;

            const overlay   = document.getElementById('search-overlay');
            const modal     = document.getElementById('search-modal');
            const input     = document.getElementById('search-input');
            const results   = document.getElementById('search-results');
            const openBtn   = document.getElementById('search-open-btn');
            let activeIdx   = -1;

            function tintClass(idx) {
                return 'card-icon-tint-' + (idx % 5);
            }

            function globeIcon() {
                return `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/>
                    <path d="M2 12h20M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
                </svg>`;
            }

            function highlight(text, query) {
                if (!query) { return text; }
                const escaped = query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
                return text.replace(new RegExp('(' + escaped + ')', 'gi'), '<mark style="background:transparent;color:var(--ink);font-weight:600;">$1</mark>');
            }

            function render(query) {
                const q = query.trim().toLowerCase();
                const filtered = q
                    ? items.filter(w =>
                        w.name.toLowerCase().includes(q) ||
                        (w.category || '').toLowerCase().includes(q) ||
                        w.host.toLowerCase().includes(q)
                    )
                    : items;

                activeIdx = filtered.length ? 0 : -1;

                if (!filtered.length) {
                    results.innerHTML = '<div class="search-empty">Tidak ada hasil untuk "<strong>' + query + '</strong>"</div>';
                    return;
                }

                results.innerHTML = filtered.map((w, i) => `
                    <a href="${w.url}" class="search-result-item${i === 0 ? ' active' : ''}" data-idx="${i}" target="${w.url.startsWith('http') ? '_blank' : '_self'}" rel="noopener">
                        <div class="search-result-icon ${tintClass(i)}">${globeIcon()}</div>
                        <div class="search-result-body">
                            <div class="search-result-name">${highlight(w.name, query)}</div>
                            <div class="search-result-meta">${w.host}</div>
                        </div>
                        ${w.category ? `<span class="search-result-badge">${w.category}</span>` : ''}
                    </a>
                `).join('');

                results.querySelectorAll('.search-result-item').forEach(el => {
                    el.addEventListener('mouseenter', function () {
                        setActive(parseInt(this.dataset.idx));
                    });
                });
            }

            function setActive(idx) {
                activeIdx = idx;
                results.querySelectorAll('.search-result-item').forEach((el, i) => {
                    el.classList.toggle('active', i === idx);
                    if (i === idx) { el.scrollIntoView({ block: 'nearest' }); }
                });
            }

            function open() {
                overlay.classList.add('open');
                document.body.style.overflow = 'hidden';
                input.value = '';
                render('');
                setTimeout(() => input.focus(), 50);
            }

            function close() {
                overlay.classList.remove('open');
                document.body.style.overflow = '';
            }

            openBtn.addEventListener('click', open);

            overlay.addEventListener('click', function (e) {
                if (!modal.contains(e.target)) { close(); }
            });

            input.addEventListener('input', function () {
                render(this.value);
            });

            document.addEventListener('keydown', function (e) {
                // Cmd/Ctrl+K to open
                if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
                    e.preventDefault();
                    overlay.classList.contains('open') ? close() : open();
                    return;
                }

                if (!overlay.classList.contains('open')) { return; }

                const rows = results.querySelectorAll('.search-result-item');

                if (e.key === 'Escape') { close(); }
                else if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    setActive(Math.min(activeIdx + 1, rows.length - 1));
                }
                else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    setActive(Math.max(activeIdx - 1, 0));
                }
                else if (e.key === 'Enter' && activeIdx >= 0) {
                    rows[activeIdx]?.click();
                }
            });
        })();
    </script>

</body>

</html>