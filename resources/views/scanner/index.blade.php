<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Pool Entry Scanner</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.js"></script>
    <style>
        :root {
            --navy:      #0f172a;
            --navy-mid:  #1e293b;
            --navy-soft: #334155;
            --accent:    #38bdf8;
            --success:   #22c55e;
            --danger:    #ef4444;
            --text:      #f1f5f9;
            --text-muted:#94a3b8;
            --border:    #334155;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            background: var(--navy);
            color: var(--text);
            font-family: 'Segoe UI', system-ui, sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 24px;
            gap: 24px;
        }

        /* ── HEADER ── */
        .header {
            text-align: center;
        }

        .header h1 {
            font-size: 22px;
            font-weight: 800;
            color: var(--text);
            letter-spacing: -0.3px;
        }

        .header p {
            color: var(--text-muted);
            font-size: 13px;
            margin-top: 4px;
        }

        /* ── SCANNER BOX ── */
        .scanner-wrap {
            position: relative;
            border-radius: 20px;
            overflow: hidden;
            border: 2px solid var(--navy-soft);
            box-shadow: 0 0 0 1px rgba(56,189,248,0.15),
                        0 20px 60px rgba(0,0,0,0.5);
        }

        .scanner-wrap video {
            display: block;
        }

        /* Corner guides */
        .corner {
            position: absolute;
            width: 28px;
            height: 28px;
            z-index: 2;
        }

        .corner-tl { top: 12px;    left: 12px;
            border-top: 3px solid var(--accent);
            border-left: 3px solid var(--accent);
            border-radius: 4px 0 0 0; }

        .corner-tr { top: 12px;    right: 12px;
            border-top: 3px solid var(--accent);
            border-right: 3px solid var(--accent);
            border-radius: 0 4px 0 0; }

        .corner-bl { bottom: 12px; left: 12px;
            border-bottom: 3px solid var(--accent);
            border-left: 3px solid var(--accent);
            border-radius: 0 0 0 4px; }

        .corner-br { bottom: 12px; right: 12px;
            border-bottom: 3px solid var(--accent);
            border-right: 3px solid var(--accent);
            border-radius: 0 0 4px 0; }

        /* Scan line animation */
        .scan-line {
            position: absolute;
            left: 16px;
            right: 16px;
            height: 2px;
            background: linear-gradient(90deg,
                transparent, var(--accent), transparent);
            border-radius: 999px;
            z-index: 2;
            animation: scanline 2s ease-in-out infinite;
        }

        @keyframes scanline {
            0%   { top: 16px;   opacity: 0; }
            10%  { opacity: 1; }
            90%  { opacity: 1; }
            100% { top: calc(100% - 16px); opacity: 0; }
        }

        /* ── STATUS PILL ── */
        .status-pill {
            background: var(--navy-mid);
            border: 1px solid var(--border);
            border-radius: 999px;
            padding: 8px 20px;
            font-size: 13px;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--accent);
            animation: pulse 1.5s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50%       { opacity: 0.4; transform: scale(0.8); }
        }

        /* ── ADMIN LINK ── */
        .admin-link {
            color: var(--text-muted);
            font-size: 12px;
            text-decoration: none;
            transition: color 0.2s;
        }

        .admin-link:hover { color: var(--accent); }

        /* ── POPUP ── */
        .popup-backdrop {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 50;
            align-items: center;
            justify-content: center;
            padding: 24px;
            background: rgba(0,0,0,0.7);
            backdrop-filter: blur(6px);
        }

        .popup-card {
            width: 100%;
            max-width: 380px;
            border-radius: 24px;
            padding: 40px 32px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 12px;
            box-shadow: 0 32px 80px rgba(0,0,0,0.5);
            animation: popIn 0.2s ease-out;
        }

        @keyframes popIn {
            from { transform: scale(0.92); opacity: 0; }
            to   { transform: scale(1);   opacity: 1; }
        }

        .popup-icon {
            font-size: 64px;
            line-height: 1;
            margin-bottom: 4px;
        }

        .popup-title {
            font-size: 22px;
            font-weight: 800;
            color: #fff;
            letter-spacing: 0.5px;
            text-align: center;
        }

        .popup-name {
            font-size: 20px;
            font-weight: 700;
            color: #fff;
            text-align: center;
        }

        .popup-sub {
            font-size: 14px;
            color: rgba(255,255,255,0.75);
            text-align: center;
            line-height: 1.6;
        }

        .popup-extra {
            background: rgba(255,255,255,0.2);
            border-radius: 999px;
            padding: 8px 22px;
            font-size: 14px;
            font-weight: 600;
            color: #fff;
            margin-top: 4px;
        }

        .popup-btn {
            margin-top: 8px;
            background: rgba(255,255,255,0.2);
            border: 2px solid rgba(255,255,255,0.3);
            color: #fff;
            font-size: 14px;
            font-weight: 700;
            padding: 12px 36px;
            border-radius: 999px;
            cursor: pointer;
            transition: all 0.2s;
            letter-spacing: 0.3px;
        }

        .popup-btn:hover {
            background: rgba(255,255,255,0.3);
            border-color: rgba(255,255,255,0.5);
        }

        /* ── MOBILE ── */
        @media (max-width: 480px) {
            body { padding: 20px 16px; gap: 20px; }
            .header h1 { font-size: 20px; }
            .popup-card { padding: 32px 24px; }
            .popup-title { font-size: 20px; }
            .popup-name  { font-size: 18px; }
        }
    </style>
</head>
<body>

    {{-- HEADER --}}
    <div class="header">
        <h1>🏊 Pool Entry</h1>
        <p>Arahkan kamera ke QR code member</p>
    </div>

    {{-- SCANNER --}}
    <div class="scanner-wrap">
        <div class="corner corner-tl"></div>
        <div class="corner corner-tr"></div>
        <div class="corner corner-bl"></div>
        <div class="corner corner-br"></div>
        <div class="scan-line"></div>

        <video id="camera" autoplay muted playsinline
            style="width:288px; height:288px; object-fit:cover; display:block;">
        </video>
        <canvas id="canvas" style="display:none;"></canvas>
    </div>

    {{-- STATUS --}}
    <div class="status-pill">
        <div class="status-dot"></div>
        <span id="status-text">Memulai kamera...</span>
    </div>

    {{-- ADMIN LINK --}}
    <a href="{{ route('admin.dashboard') }}" class="admin-link">
        Admin Panel →
    </a>

    {{-- POPUP --}}
    <div id="popup" class="popup-backdrop">
        <div id="popup-card" class="popup-card">
            <div id="popup-icon"  class="popup-icon"></div>
            <div id="popup-title" class="popup-title"></div>
            <div id="popup-name"  class="popup-name"></div>
            <div id="popup-sub"   class="popup-sub"></div>
            <div id="popup-extra" class="popup-extra" style="display:none;"></div>
            <button class="popup-btn" onclick="closePopup()">
                OK — Lanjutkan Scan
            </button>
        </div>
    </div>

    <script>
        const video      = document.getElementById('camera');
        const canvas     = document.getElementById('canvas');
        const ctx        = canvas.getContext('2d');
        const statusText = document.getElementById('status-text');
        const popup      = document.getElementById('popup');
        const popupCard  = document.getElementById('popup-card');

        let scanning = true;

        // Start camera
        navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } })
            .then(stream => {
                video.srcObject = stream;
                video.play();
                statusText.textContent = 'Siap memindai...';
                requestAnimationFrame(scanFrame);
            })
            .catch(err => {
                statusText.textContent = 'Kamera tidak dapat diakses.';
                console.error(err);
            });

        // Scan each frame
        function scanFrame() {
            if (video.readyState === video.HAVE_ENOUGH_DATA && scanning) {
                canvas.width  = video.videoWidth;
                canvas.height = video.videoHeight;
                ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

                const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
                const code = jsQR(imageData.data, imageData.width, imageData.height);

                if (code) {
                    scanning = false;
                    statusText.textContent = 'QR terdeteksi...';
                    sendToken(code.data);
                }
            }
            requestAnimationFrame(scanFrame);
        }

        // Send token to server
        function sendToken(token) {
            fetch('{{ route("scanner.scan") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ token }),
            })
            .then(res => res.json())
            .then(data => showPopup(data))
            .catch(() => {
                statusText.textContent = 'Error, coba lagi.';
                scanning = true;
            });
        }

        // Show popup
        function showPopup(data) {
            const isGranted = data.status === 'granted';

            popupCard.style.background = isGranted
                ? 'linear-gradient(135deg, #15803d, #16a34a)'
                : 'linear-gradient(135deg, #b91c1c, #dc2626)';

            document.getElementById('popup-icon').textContent  = isGranted ? '✅' : '❌';
            document.getElementById('popup-title').textContent = isGranted
                ? 'AKSES DIBERIKAN' : 'AKSES DITOLAK';

            if (isGranted) {
                document.getElementById('popup-name').textContent = data.member.nama;
                document.getElementById('popup-sub').textContent  =
                    'Unit ' + data.member.unit + ' · ' + data.member.kawasan;
                const extra = document.getElementById('popup-extra');
                extra.textContent = 'Sisa akses hari ini: ' + data.remaining;
                extra.style.display = 'block';
            } else {
                document.getElementById('popup-name').textContent = data.reason;
                document.getElementById('popup-sub').textContent  = data.member
                    ? 'Unit ' + data.member.unit + ' · ' + data.member.kawasan
                    : '';
                document.getElementById('popup-extra').style.display = 'none';
            }

            popup.style.display = 'flex';
            statusText.textContent = 'Menunggu konfirmasi...';
        }

        // Close popup
        function closePopup() {
            popup.style.display = 'none';
            scanning = true;
            statusText.textContent = 'Siap memindai...';
        }
    </script>

</body>
</html>