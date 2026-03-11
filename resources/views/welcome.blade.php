<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Rajin Auth') }} — Restricted Access</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=share-tech-mono:400&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            background-color: #000;
            color: #ff0000;
            font-family: 'Share Tech Mono', monospace;
            min-height: 100vh;
            overflow-x: hidden;
        }

        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background: repeating-linear-gradient(
                0deg,
                transparent,
                transparent 2px,
                rgba(0, 0, 0, 0.15) 2px,
                rgba(0, 0, 0, 0.15) 4px
            );
            pointer-events: none;
            z-index: 100;
        }

        @keyframes flicker {
            0%, 95%, 100% { opacity: 1; }
            96% { opacity: 0.4; }
            97% { opacity: 1; }
            98% { opacity: 0.2; }
            99% { opacity: 1; }
        }

        @keyframes blink {
            0%, 49% { opacity: 1; }
            50%, 100% { opacity: 0; }
        }

        @keyframes scan {
            0% { top: -10%; }
            100% { top: 110%; }
        }

        @keyframes pulse-red {
            0%, 100% { box-shadow: 0 0 10px #ff0000, 0 0 30px #ff000055; }
            50% { box-shadow: 0 0 25px #ff0000, 0 0 60px #ff000088; }
        }

        @keyframes glitch {
            0% { transform: translate(0); }
            20% { transform: translate(-2px, 2px); }
            40% { transform: translate(2px, -2px); }
            60% { transform: translate(-1px, 1px); }
            80% { transform: translate(1px, -1px); }
            100% { transform: translate(0); }
        }

        @keyframes rotate-slow {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .scanline {
            position: fixed;
            left: 0;
            width: 100%;
            height: 4px;
            background: rgba(255, 0, 0, 0.07);
            animation: scan 6s linear infinite;
            pointer-events: none;
            z-index: 99;
        }

        .container {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            animation: flicker 8s infinite;
        }

        .warning-badge {
            border: 2px solid #ff0000;
            padding: 0.25rem 1rem;
            font-size: 0.7rem;
            letter-spacing: 0.3em;
            color: #ff0000;
            margin-bottom: 2.5rem;
            animation: pulse-red 2s ease-in-out infinite;
        }

        .radiation {
            width: 120px;
            height: 120px;
            position: relative;
            margin-bottom: 2rem;
            animation: rotate-slow 12s linear infinite;
            filter: drop-shadow(0 0 12px #ff0000);
        }

        .title {
            font-size: clamp(1.8rem, 6vw, 3.5rem);
            letter-spacing: 0.15em;
            text-align: center;
            color: #ff0000;
            text-shadow: 0 0 10px #ff000088;
            animation: glitch 5s infinite;
            margin-bottom: 0.75rem;
        }

        .subtitle {
            font-size: 0.75rem;
            letter-spacing: 0.4em;
            color: #ff000099;
            text-align: center;
            margin-bottom: 3rem;
        }

        .terminal {
            width: 100%;
            max-width: 520px;
            border: 1px solid #ff000055;
            background: rgba(255, 0, 0, 0.04);
            padding: 1.5rem;
            margin-bottom: 3rem;
            font-size: 0.75rem;
            line-height: 2;
            color: #ff000099;
        }

        .terminal .line::before {
            content: '> ';
            color: #ff0000;
        }

        .cursor::after {
            content: '█';
            animation: blink 1s infinite;
        }

        .footer {
            font-size: 0.65rem;
            letter-spacing: 0.2em;
            color: #ff000044;
            text-align: center;
        }

        .grid-overlay {
            position: fixed;
            inset: 0;
            background-image:
                linear-gradient(rgba(255,0,0,0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,0,0,0.03) 1px, transparent 1px);
            background-size: 40px 40px;
            pointer-events: none;
            z-index: 0;
        }

        .content { position: relative; z-index: 1; display: flex; flex-direction: column; align-items: center; }
    </style>
</head>
<body>
    <div class="scanline"></div>
    <div class="grid-overlay"></div>

    <div class="container">
        <div class="content">

            <div class="warning-badge">⚠ CLASSIFIED SYSTEM ⚠</div>

            <svg class="radiation" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="50" cy="50" r="48" stroke="#ff0000" stroke-width="1.5" stroke-dasharray="4 4"/>
                <circle cx="50" cy="50" r="7" fill="#ff0000"/>
                <path d="M50 43 L44 20 A30 30 0 0 1 56 20 Z" fill="#ff0000" opacity="0.9"/>
                <path d="M50 43 L44 20 A30 30 0 0 1 56 20 Z" fill="#ff0000" opacity="0.9" transform="rotate(120 50 50)"/>
                <path d="M50 43 L44 20 A30 30 0 0 1 56 20 Z" fill="#ff0000" opacity="0.9" transform="rotate(240 50 50)"/>
                <circle cx="50" cy="50" r="12" fill="#000"/>
                <circle cx="50" cy="50" r="5" fill="#ff0000"/>
            </svg>

            <h1 class="title">RESTRICTED ACCESS</h1>
            <p class="subtitle">AUTHORIZATION REQUIRED — LEVEL 5 CLEARANCE</p>

            <div class="terminal">
                <div class="line">SYSTEM: {{ config('app.name', 'Rajin Auth') }} Identity Server</div>
                <div class="line">LOCATION: CLASSIFIED</div>
                <div class="line">STATUS: <span style="color:#ff0000;">ACTIVE — MONITORING</span></div>
                <div class="line">INTRUSION DETECTION: <span style="color:#ff0000;">ENABLED</span></div>
                <div class="line">UNAUTHORIZED ACCESS: <span style="color:#ff0000;">PROSECUTED</span></div>
                <div class="line cursor">AWAITING CREDENTIALS</div>
            </div>

            <div class="footer">
                <div>ALL ACTIVITY IS LOGGED AND MONITORED</div>
                <div style="margin-top:0.5rem;">{{ config('app.name', 'Rajin Auth') }} · {{ date('Y') }} · CLASSIFIED</div>
            </div>

        </div>
    </div>

    <script>
        const title = document.querySelector('.title');
        setInterval(() => {
            if (Math.random() > 0.85) {
                title.style.transform = `translate(${(Math.random()-0.5)*6}px, ${(Math.random()-0.5)*3}px)`;
                setTimeout(() => title.style.transform = '', 80);
            }
        }, 500);

        const cursor = document.querySelector('.cursor');
        const messages = [
            'AWAITING CREDENTIALS',
            'SCANNING BIOMETRICS...',
            'VERIFYING CLEARANCE...',
            'ACCESS POINT SECURED',
        ];
        let i = 0;
        setInterval(() => {
            i = (i + 1) % messages.length;
            cursor.childNodes[0].textContent = messages[i] + ' ';
        }, 3000);
    </script>
</body>
</html>
