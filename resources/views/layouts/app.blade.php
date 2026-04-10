<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Sistem Antrian Infinix Roxy Mas' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-main: #f7f8fb;
            --text-main: #15202e;
            --text-muted: #526172;
            --surface: rgba(255, 255, 255, 0.86);
            --surface-solid: #ffffff;
            --line-soft: rgba(21, 32, 46, 0.1);
            --brand: #e6492d;
            --brand-dark: #c22b11;
            --chip: #fce8e4;
            --chip-text: #8e2a1a;
        }

        * { box-sizing: border-box; }

        body {
            font-family: 'Outfit', sans-serif;
            margin: 0;
            color: var(--text-main);
            background:
                radial-gradient(circle at 12% 18%, rgba(255, 198, 111, 0.28), transparent 46%),
                radial-gradient(circle at 84% 78%, rgba(85, 158, 245, 0.2), transparent 42%),
                linear-gradient(180deg, #f6f8fc 0%, #f0f4fb 100%);
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
        }

        body::before,
        body::after {
            content: '';
            position: fixed;
            inset: 0;
            pointer-events: none;
            z-index: -2;
        }

        body::before {
            background-image:
                linear-gradient(rgba(255, 255, 255, 0.75), rgba(255, 255, 255, 0.84)),
                url('https://images.unsplash.com/photo-1604719312566-8912e9c8a213?auto=format&fit=crop&w=1600&q=80');
            background-size: cover;
            background-position: center;
            opacity: 0.36;
        }

        body.theme-admin::before {
            background-image:
                linear-gradient(rgba(255, 255, 255, 0.76), rgba(255, 255, 255, 0.87)),
                url('https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&w=1600&q=80');
        }

        body.theme-public::before {
            background-image:
                linear-gradient(rgba(255, 255, 255, 0.68), rgba(255, 255, 255, 0.84)),
                url('https://images.unsplash.com/photo-1556740749-887f6717d7e4?auto=format&fit=crop&w=1600&q=80');
        }

        body::after {
            background-image: linear-gradient(90deg, rgba(255, 255, 255, 0.35) 1px, transparent 1px), linear-gradient(0deg, rgba(255, 255, 255, 0.35) 1px, transparent 1px);
            background-size: 48px 48px;
            opacity: 0.35;
            z-index: -1;
        }

        .container {
            max-width: 1120px;
            margin: 24px auto;
            padding: 0 16px 26px;
        }

        .top-banner {
            background: linear-gradient(90deg, rgba(230, 73, 45, 0.95), rgba(251, 129, 24, 0.92));
            color: #fff;
            border-radius: 18px;
            padding: 16px 18px;
            margin-bottom: 16px;
            box-shadow: 0 12px 24px rgba(180, 63, 32, 0.25);
            animation: reveal 0.45s ease;
        }

        .top-banner h2 {
            font-family: 'Space Grotesk', sans-serif;
            margin: 0 0 4px;
            font-size: 20px;
            letter-spacing: 0.2px;
        }

        .top-banner p {
            margin: 0;
            font-size: 14px;
            color: rgba(255, 255, 255, 0.92);
        }

        .card {
            background: var(--surface);
            backdrop-filter: blur(7px);
            border-radius: 18px;
            padding: 22px;
            border: 1px solid rgba(255, 255, 255, 0.8);
            box-shadow: 0 16px 35px rgba(12, 26, 44, 0.09);
            animation: reveal 0.4s ease;
        }

        .title {
            margin: 0 0 12px;
            font-size: 28px;
            font-family: 'Space Grotesk', sans-serif;
            letter-spacing: 0.2px;
        }

        .subtitle {
            margin: 0 0 18px;
            color: var(--text-muted);
            font-size: 15px;
            line-height: 1.45;
        }

        .row { display: flex; gap: 14px; flex-wrap: wrap; }
        .col { flex: 1 1 240px; }

        label {
            display: block;
            margin-bottom: 6px;
            font-size: 13px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            color: #304355;
        }

        input, textarea, button, select {
            width: 100%;
            padding: 11px 12px;
            border-radius: 10px;
            border: 1px solid #cad4df;
            background: var(--surface-solid);
            box-sizing: border-box;
            font: inherit;
        }

        input:focus,
        textarea:focus,
        select:focus {
            outline: none;
            border-color: #6b9ef5;
            box-shadow: 0 0 0 3px rgba(107, 158, 245, 0.2);
        }

        textarea { min-height: 118px; resize: vertical; }

        button {
            cursor: pointer;
            border: none;
            background: linear-gradient(90deg, var(--brand) 0%, #f97316 100%);
            color: #fff;
            font-weight: 700;
            letter-spacing: 0.2px;
            transition: transform 0.15s ease, box-shadow 0.2s ease;
        }

        button:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 18px rgba(201, 63, 38, 0.28);
        }

        button.secondary {
            background: linear-gradient(90deg, #6b7280 0%, #4b5563 100%);
        }

        .quick-link {
            color: #114ea7;
            text-decoration: none;
            font-weight: 600;
        }

        .quick-link:hover { text-decoration: underline; }

        .mt-12 { margin-top: 12px; }
        .mt-20 { margin-top: 20px; }

        .stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 12px;
        }

        .stat-box {
            background: #fff;
            border: 1px solid var(--line-soft);
            border-radius: 12px;
            padding: 12px;
        }

        .stat-label {
            font-size: 13px;
            color: #5e6b79;
        }

        .stat-value {
            font-size: 26px;
            font-family: 'Space Grotesk', sans-serif;
            margin-top: 5px;
        }

        .alert { padding: 10px 12px; border-radius: 10px; margin-bottom: 12px; }
        .alert-success { background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; }
        .alert-error { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-top: 10px;
            border: 1px solid var(--line-soft);
            border-radius: 12px;
            overflow: hidden;
            background: #fff;
        }

        th, td {
            border-bottom: 1px solid #ecf0f3;
            padding: 11px 10px;
            text-align: left;
            vertical-align: top;
            font-size: 14px;
        }

        tr:last-child td { border-bottom: none; }

        th {
            background: #f8fafc;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.35px;
            color: #4b5563;
        }

        .badge {
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }

        .b-waiting { background: #eff6ff; color: #1d4ed8; }
        .b-called { background: #fffbeb; color: #92400e; }
        .b-in-service { background: #ecfeff; color: #155e75; }
        .b-done { background: #ecfdf5; color: #065f46; }
        .b-cancelled { background: #f3f4f6; color: #374151; }

        .actions form { display: inline-block; margin: 2px; }
        .actions button { width: auto; padding: 7px 11px; font-size: 12px; }

        .live-clock {
            position: fixed;
            top: 14px;
            right: 16px;
            background: linear-gradient(90deg, rgba(230, 73, 45, 0.9), rgba(251, 129, 24, 0.87));
            color: #fff;
            padding: 8px 14px;
            border-radius: 10px;
            font-family: 'Space Grotesk', monospace;
            font-size: 15px;
            font-weight: 700;
            letter-spacing: 0.5px;
            box-shadow: 0 8px 20px rgba(180, 63, 32, 0.22);
            z-index: 9999;
            min-width: 240px;
            text-align: center;
        }

        @keyframes reveal {
            from { opacity: 0; transform: translateY(8px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 768px) {
            .container { margin: 14px auto; padding: 0 12px 16px; }
            .card { padding: 16px; border-radius: 14px; }
            .title { font-size: 24px; }
            th, td { font-size: 13px; padding: 9px 8px; }
        }
    </style>
</head>
<body class="{{ $pageClass ?? 'theme-public' }}">
    <div class="live-clock" id="live-clock">03/04/2026 00:00:00</div>
    <div class="container">
        <div class="top-banner">
            <h2>Roxy Service Queue Hub</h2>
            <p>Nuansa pusat perbelanjaan modern untuk admin dan pelanggan Service Center Infinix Roxy Mas.</p>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-error">{{ session('error') }}</div>
        @endif
        @yield('content')
    </div>

    <script>
        (() => {
            const clockEl = document.getElementById('live-clock');

            const updateClock = () => {
                const now = new window['Date']();
                const day = String(now.getDate()).padStart(2, '0');
                const month = String(now.getMonth() + 1).padStart(2, '0');
                const year = now.getFullYear();
                const hours = String(now.getHours()).padStart(2, '0');
                const minutes = String(now.getMinutes()).padStart(2, '0');
                const seconds = String(now.getSeconds()).padStart(2, '0');
                clockEl.textContent = `${day}/${month}/${year} ${hours}:${minutes}:${seconds}`;
            };

            updateClock();
            setInterval(updateClock, 1000);
        })();
    </script>
</body>
</html>
