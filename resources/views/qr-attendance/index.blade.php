<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>QR Absensi - {{ config('app.name', 'Laravel') }}</title>

    <style>
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
            --error: #ee0000;
            --error-soft: #f7d4d6;
            --warning-soft: #ffefcf;
            --success-soft: #d3e5ff;
            --radius-sm: 6px;
            --radius-md: 8px;
            --radius-lg: 12px;
            --radius-pill: 100px;
            --shadow-card: 0 1px 1px #00000005, 0 2px 2px #0000000a, inset 0 0 0 1px #00000014;
            --shadow-float: 0 2px 2px #0000000a, 0 8px 16px -4px #0000000a, inset 0 0 0 1px #00000014;
        }

        * {
            box-sizing: border-box;
        }

        ::selection {
            background: var(--primary);
            color: #f2f2f2;
        }

        html {
            background: var(--canvas-soft);
        }

        body {
            margin: 0;
            background: var(--canvas-soft);
            color: var(--ink);
            font-family: Geist, Inter, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            font-size: 16px;
            line-height: 24px;
        }

        button,
        input {
            font: inherit;
        }

        .page {
            min-height: 100vh;
            overflow: hidden;
            position: relative;
        }

        .mesh {
            height: 620px;
            left: 50%;
            opacity: 0.68;
            pointer-events: none;
            position: absolute;
            top: -260px;
            transform: translateX(-50%);
            width: min(1080px, 118vw);
        }

        .mesh::before {
            background:
                radial-gradient(circle at 16% 44%, #007cf0 0, transparent 32%),
                radial-gradient(circle at 38% 42%, #00dfd8 0, transparent 34%),
                radial-gradient(circle at 60% 44%, #7928ca 0, transparent 34%),
                radial-gradient(circle at 78% 40%, #ff0080 0, transparent 32%),
                radial-gradient(circle at 70% 70%, #ff4d4d 0, transparent 30%),
                radial-gradient(circle at 88% 68%, #f9cb28 0, transparent 28%);
            content: "";
            filter: blur(36px);
            inset: 0;
            position: absolute;
        }

        .shell {
            margin: 0 auto;
            max-width: 1200px;
            padding: 16px 24px 64px;
            position: relative;
        }

        .nav {
            align-items: center;
            display: flex;
            height: 64px;
            justify-content: space-between;
        }

        .brand {
            align-items: center;
            color: var(--ink);
            display: inline-flex;
            font-size: 14px;
            font-weight: 500;
            gap: 10px;
            letter-spacing: -0.28px;
            line-height: 20px;
            text-decoration: none;
        }

        .brand-mark {
            align-items: center;
            background: var(--canvas);
            border: 1px solid var(--hairline);
            border-radius: 9999px;
            display: inline-flex;
            height: 30px;
            justify-content: center;
            width: 30px;
        }

        .brand-logo {
            display: block;
            height: 22px;
            object-fit: contain;
            width: 22px;
        }

        .nav-status {
            align-items: center;
            background: var(--canvas);
            border: 1px solid var(--hairline);
            border-radius: 9999px;
            color: var(--body);
            display: inline-flex;
            font-family: "Geist Mono", ui-monospace, SFMono-Regular, Menlo, Monaco, monospace;
            font-size: 12px;
            gap: 8px;
            height: 32px;
            line-height: 16px;
            padding: 0 12px;
        }

        .status-dot {
            background: var(--link);
            border-radius: 9999px;
            height: 8px;
            width: 8px;
        }

        .hero {
            display: grid;
            gap: 32px;
            grid-template-columns: minmax(0, 0.92fr) minmax(400px, 1.08fr);
            padding: 88px 0 64px;
        }

        .eyebrow {
            color: var(--body);
            font-family: "Geist Mono", ui-monospace, SFMono-Regular, Menlo, Monaco, monospace;
            font-size: 12px;
            font-weight: 400;
            line-height: 16px;
            margin: 0 0 16px;
        }

        h1 {
            color: var(--ink);
            font-size: clamp(44px, 7vw, 72px);
            font-weight: 600;
            letter-spacing: -2.4px;
            line-height: 0.96;
            margin: 0;
            max-width: 720px;
        }

        .lead {
            color: var(--body);
            font-size: 18px;
            font-weight: 400;
            line-height: 28px;
            margin: 24px 0 0;
            max-width: 560px;
        }

        .hero-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 32px;
        }

        .inline-badge {
            align-items: center;
            background: #ffffffcc;
            border: 1px solid var(--hairline);
            border-radius: 64px;
            color: var(--ink);
            display: inline-flex;
            font-size: 14px;
            gap: 8px;
            height: 36px;
            letter-spacing: -0.28px;
            line-height: 20px;
            padding: 0 16px;
        }

        .scan-card {
            align-self: start;
            background: var(--canvas);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-float);
            color: var(--ink);
            padding: 32px;
        }

        .scan-card-header {
            align-items: flex-start;
            display: flex;
            gap: 20px;
            justify-content: space-between;
        }

        .scan-title {
            color: var(--ink);
            font-size: 32px;
            font-weight: 600;
            letter-spacing: -1.28px;
            line-height: 40px;
            margin: 0;
        }

        .scan-copy {
            color: var(--body);
            font-size: 14px;
            letter-spacing: -0.28px;
            line-height: 20px;
            margin: 8px 0 0;
        }

        .scanner-glyph {
            align-items: center;
            background: var(--canvas-soft-2);
            border: 1px solid var(--hairline);
            border-radius: 9999px;
            color: var(--ink);
            display: inline-flex;
            flex: 0 0 auto;
            height: 48px;
            justify-content: center;
            width: 48px;
        }

        .scan-form {
            display: grid;
            gap: 12px;
            margin-top: 28px;
        }

        .scan-input {
            background: var(--canvas);
            border: 1px solid var(--hairline);
            border-radius: var(--radius-sm);
            color: var(--ink);
            height: 48px;
            outline: none;
            padding: 0 12px;
            width: 100%;
        }

        .scan-input::placeholder {
            color: var(--mute);
        }

        .scan-input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px #17171714;
        }

        .button-row {
            display: flex;
            gap: 12px;
        }

        .button {
            align-items: center;
            border: 0;
            border-radius: var(--radius-pill);
            cursor: pointer;
            display: inline-flex;
            font-size: 16px;
            font-weight: 500;
            height: 48px;
            justify-content: center;
            line-height: 24px;
            padding: 0 18px;
        }

        .button-primary {
            background: var(--primary);
            color: var(--on-primary);
            flex: 1;
        }

        .button-secondary {
            background: var(--canvas);
            box-shadow: inset 0 0 0 1px var(--hairline);
            color: var(--ink);
        }

        .button:disabled {
            cursor: wait;
            opacity: 0.72;
        }

        .result {
            background: var(--canvas-soft);
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-card);
            display: none;
            margin-top: 24px;
            padding: 20px;
        }

        .result.is-visible {
            display: block;
        }

        .result.is-success {
            background: var(--link-bg-soft);
        }

        .result.is-error {
            background: var(--error-soft);
        }

        .result-heading {
            align-items: center;
            display: flex;
            gap: 12px;
            justify-content: space-between;
        }

        .result-title {
            color: var(--ink);
            font-size: 16px;
            font-weight: 500;
            line-height: 24px;
            margin: 0;
        }

        .result-message {
            color: var(--body);
            font-size: 14px;
            letter-spacing: -0.28px;
            line-height: 20px;
            margin: 8px 0 0;
        }

        .pill {
            background: var(--canvas);
            border-radius: 9999px;
            color: var(--ink);
            display: inline-flex;
            font-family: "Geist Mono", ui-monospace, SFMono-Regular, Menlo, Monaco, monospace;
            font-size: 12px;
            line-height: 16px;
            padding: 4px 8px;
            white-space: nowrap;
        }

        .student-grid {
            display: grid;
            gap: 12px;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            margin-top: 18px;
        }

        .student-field {
            background: #ffffffb8;
            border-radius: var(--radius-md);
            padding: 12px;
        }

        .student-label {
            color: var(--mute);
            display: block;
            font-family: "Geist Mono", ui-monospace, SFMono-Regular, Menlo, Monaco, monospace;
            font-size: 12px;
            line-height: 16px;
            margin-bottom: 4px;
        }

        .student-value {
            color: var(--ink);
            display: block;
            font-size: 14px;
            font-weight: 500;
            line-height: 20px;
            overflow-wrap: anywhere;
        }

        .meta-row {
            border-top: 1px solid #00000014;
            color: var(--body);
            display: flex;
            flex-wrap: wrap;
            font-size: 14px;
            gap: 12px;
            justify-content: space-between;
            letter-spacing: -0.28px;
            margin-top: 18px;
            padding-top: 16px;
        }

        .surface-section {
            background: var(--canvas-soft);
            border-top: 1px solid var(--hairline);
        }

        .info-grid {
            display: grid;
            gap: 16px;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            margin: 0 auto;
            max-width: 1200px;
            padding: 48px 24px 72px;
        }

        .info-card {
            background: var(--canvas);
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-card);
            padding: 24px;
        }

        .info-card .eyebrow {
            color: var(--body);
            margin-bottom: 12px;
        }

        .info-card h2 {
            color: var(--ink);
            font-size: 20px;
            font-weight: 600;
            letter-spacing: -0.6px;
            line-height: 28px;
            margin: 0;
        }

        .info-card p {
            color: var(--body);
            font-size: 14px;
            letter-spacing: -0.28px;
            line-height: 20px;
            margin: 8px 0 0;
        }

        .sr-only {
            clip: rect(0, 0, 0, 0);
            border: 0;
            height: 1px;
            margin: -1px;
            overflow: hidden;
            padding: 0;
            position: absolute;
            white-space: nowrap;
            width: 1px;
        }

        @media (max-width: 920px) {
            .hero {
                grid-template-columns: 1fr;
                padding-top: 56px;
            }

            .info-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 560px) {
            .shell {
                padding: 12px 16px 48px;
            }

            .nav-status {
                display: none;
            }

            .hero {
                padding: 48px 0;
            }

            h1 {
                font-size: 44px;
                letter-spacing: -1.8px;
            }

            .scan-card {
                padding: 24px;
            }

            .scan-card-header,
            .button-row {
                flex-direction: column;
            }

            .button,
            .button-primary,
            .button-secondary {
                width: 100%;
            }

            .student-grid {
                grid-template-columns: 1fr;
            }

            .info-grid {
                padding-left: 16px;
                padding-right: 16px;
            }
        }
    </style>
</head>

<body>
    <div class="page">
        <div class="mesh" aria-hidden="true"></div>

        <main class="shell">
            <nav class="nav" aria-label="QR Absensi">
                <a href="{{ route('qr-attendance.index') }}" class="brand">
                    <span class="brand-mark" aria-hidden="true">
                        <img src="{{ asset('images/bl_logo.png') }}" alt="" class="brand-logo">
                    </span>
                    <span>QR Absensi</span>
                </a>

                <div class="nav-status">
                    <span class="status-dot" aria-hidden="true"></span>
                    <span id="connection-status">Ready</span>
                </div>
            </nav>

            <section class="hero">
                <div>
                    <p class="eyebrow">KB-TK-SD-SMP-SMA-SMK Budi Luhur</p>
                    <h1>Scanner absensi sekolah Budi Luhur.</h1>
                    <p class="lead">
                        Scan QR siswa dari berbagai unit dalam satu layar. Input tetap fokus, proses cepat, dan hasil
                        absensi langsung tampil setelah QR terbaca.
                    </p>

                    <div class="hero-actions" aria-label="Scanner status">
                        <span class="inline-badge">
                            <span class="status-dot" aria-hidden="true"></span>
                            Scanner siap digunakan
                        </span>
                        <span class="inline-badge">Multi-unit sekolah</span>
                    </div>
                </div>

                <section class="scan-card" aria-labelledby="scan-title">
                    <div class="scan-card-header">
                        <div>
                            <h2 id="scan-title" class="scan-title">QR scanner.</h2>
                            <p class="scan-copy">Scan QR siswa, atau tempel token lalu tekan tombol proses.</p>
                        </div>
                        <div class="scanner-glyph" aria-hidden="true">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                                <path
                                    d="M4 4h6v6H4V4Zm2 2v2h2V6H6Zm8-2h6v6h-6V4Zm2 2v2h2V6h-2ZM4 14h6v6H4v-6Zm2 2v2h2v-2H6Zm8-2h2v2h-2v-2Zm4 0h2v2h-2v-2Zm-4 4h6v2h-6v-2Z"
                                    fill="currentColor" />
                            </svg>
                        </div>
                    </div>

                    <form id="scan-form" class="scan-form" action="{{ route('qr-attendance.scan') }}" method="post">
                        @csrf
                        <label class="sr-only" for="token">Token QR</label>
                        <input id="token" class="scan-input" name="token" type="text" autocomplete="off"
                            inputmode="text" placeholder="Scan atau tempel token QR" autofocus required>

                        <div class="button-row">
                            <button id="submit-button" type="submit" class="button button-primary">Proses scan</button>
                            <button id="reset-button" type="button" class="button button-secondary">Reset</button>
                        </div>
                    </form>

                    <section id="result" class="result" aria-live="polite"></section>
                </section>
            </section>
        </main>

        <section class="surface-section">
            <div class="info-grid" aria-label="Informasi scanner">
                <article class="info-card">
                    <p class="eyebrow">01 / Input</p>
                    <h2>Keyboard scanner.</h2>
                    <p>Scanner tipe keyboard wedge mengisi token ke input dan mengirim saat Enter.</p>
                </article>

                <article class="info-card">
                    <p class="eyebrow">02 / Result</p>
                    <h2>Immediate status.</h2>
                    <p>Response berhasil atau gagal tampil langsung, termasuk nama, kelas, tipe, dan waktu.</p>
                </article>

                <article class="info-card">
                    <p class="eyebrow">03 / Unit</p>
                    <h2>Satu layar untuk semua unit.</h2>
                    <p>Absensi siswa KB, TK, SD, SMP, SMA, dan SMK diproses dari halaman scanner yang sama.</p>
                </article>
            </div>
        </section>
    </div>

    <script>
        const form = document.getElementById('scan-form');
        const input = document.getElementById('token');
        const result = document.getElementById('result');
        const submitButton = document.getElementById('submit-button');
        const resetButton = document.getElementById('reset-button');
        const connectionStatus = document.getElementById('connection-status');

        const focusInput = () => {
            window.setTimeout(() => input.focus(), 20);
        };

        const setBusy = (isBusy) => {
            submitButton.disabled = isBusy;
            submitButton.textContent = isBusy ? 'Memproses...' : 'Proses scan';
            connectionStatus.textContent = isBusy ? 'Processing' : 'Ready';
        };

        const renderError = (message) => {
            result.className = 'result is-visible is-error';
            result.innerHTML = `
                <div class="result-heading">
                    <p class="result-title">Scan gagal.</p>
                    <span class="pill">Error</span>
                </div>
                <p class="result-message">${escapeHtml(message)}</p>
            `;
        };

        const renderSuccess = (payload) => {
            const data = payload.data;

            result.className = 'result is-visible is-success';
            result.innerHTML = `
                <div class="result-heading">
                    <p class="result-title">${escapeHtml(payload.message)}</p>
                    <span class="pill">${escapeHtml(data.tipe)}</span>
                </div>
                <div class="student-grid">
                    ${renderField('NIS', data.nis)}
                    ${renderField('Nama', data.nama)}
                    ${renderField('Unit', data.unit ?? '-')}
                    ${renderField('Kelas', data.kelas ?? '-')}
                    ${renderField('Mode QR', data.qr_mode)}
                </div>
                <div class="meta-row">
                    <span>Status: <strong>${escapeHtml(data.status)}</strong></span>
                    <span>Waktu: <strong>${escapeHtml(data.waktu)}</strong></span>
                </div>
            `;
        };

        const renderField = (label, value) => `
            <div class="student-field">
                <span class="student-label">${escapeHtml(label)}</span>
                <span class="student-value">${escapeHtml(String(value ?? '-'))}</span>
            </div>
        `;

        const escapeHtml = (value) => String(value)
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');

        form.addEventListener('submit', async (event) => {
            event.preventDefault();

            const token = input.value.trim();

            if (!token) {
                focusInput();

                return;
            }

            setBusy(true);

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ token }),
                });

                const payload = await response.json();

                if (!response.ok) {
                    renderError(payload.message ?? 'Scan gagal diproses.');
                } else {
                    renderSuccess(payload);
                }
            } catch (error) {
                renderError('Tidak bisa menghubungi server.');
            } finally {
                input.value = '';
                setBusy(false);
                focusInput();
            }
        });

        resetButton.addEventListener('click', () => {
            input.value = '';
            result.className = 'result';
            result.innerHTML = '';
            focusInput();
        });

        document.addEventListener('click', (event) => {
            if (event.target !== input) {
                focusInput();
            }
        });

        focusInput();
    </script>
</body>

</html>
