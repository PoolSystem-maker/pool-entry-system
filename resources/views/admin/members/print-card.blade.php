<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Card — {{ $member->nama }}</title>
    <style>
        /* Reset */
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            background: #f3f4f6;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            font-family: 'Segoe UI', sans-serif;
        }

        /* Print button — hidden when printing */
        .print-btn {
            margin-bottom: 24px;
            display: flex;
            gap: 12px;
        }

        .print-btn button, .print-btn a {
            padding: 10px 24px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
        }

        .btn-print {
            background: #2563eb;
            color: white;
        }

        .btn-back {
            background: #e5e7eb;
            color: #374151;
        }

        /* CR80 card size: 85.6mm × 54mm */
        .card {
            width: 85.6mm;
            height: 54mm;
            background: linear-gradient(135deg, #1e3a8a 0%, #1d4ed8 60%, #0ea5e9 100%);
            border-radius: 4mm;
            padding: 5mm;
            display: flex;
            flex-direction: row;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 8px 32px rgba(0,0,0,0.18);
            color: white;
            position: relative;
            overflow: hidden;
        }

        /* Decorative circle in background */
        .card::before {
            content: '';
            position: absolute;
            top: -15mm;
            right: -10mm;
            width: 40mm;
            height: 40mm;
            background: rgba(255,255,255,0.07);
            border-radius: 50%;
        }

        .card::after {
            content: '';
            position: absolute;
            bottom: -10mm;
            left: 20mm;
            width: 25mm;
            height: 25mm;
            background: rgba(255,255,255,0.05);
            border-radius: 50%;
        }

        /* Left side: member info */
        .card-info {
            flex: 1;
            z-index: 1;
        }

        .card-title {
            font-size: 6pt;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: rgba(255,255,255,0.7);
            margin-bottom: 2mm;
        }

        .card-name {
            font-size: 11pt;
            font-weight: 700;
            margin-bottom: 1mm;
            line-height: 1.2;
        }

        .card-unit {
            font-size: 9pt;
            font-weight: 600;
            color: #bfdbfe;
            margin-bottom: 1mm;
        }

        .card-kawasan {
            font-size: 7pt;
            color: rgba(255,255,255,0.7);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 3mm;
        }

        .card-id {
            font-size: 6pt;
            color: rgba(255,255,255,0.5);
            font-family: monospace;
        }

        /* Right side: QR code */
        .card-qr {
            z-index: 1;
            background: white;
            padding: 2mm;
            border-radius: 2mm;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-left: 4mm;
        }

        .card-qr svg {
            width: 28mm !important;
            height: 28mm !important;
        }

        /* Pool label at top */
        .card-header {
            position: absolute;
            top: 3mm;
            right: 5mm;
            font-size: 5.5pt;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: rgba(255,255,255,0.5);
            z-index: 1;
        }

        /* Print styles */
        @media print {
            body {
                background: white;
                justify-content: flex-start;
                padding: 0;
            }

            .print-btn {
                display: none !important;
            }

            .card {
                box-shadow: none;
                margin: 0;
            }
        }
    </style>
</head>
<body>

    {{-- Print & Back buttons (hidden when printing) --}}
    <div class="print-btn">
        <button class="btn-print" onclick="window.print()">🖨️ Print Card</button>
        <a class="btn-back" href="{{ route('admin.members.show', $member) }}">← Kembali</a>
    </div>

    {{-- CR80 Card --}}
    <div class="card">

        {{-- Top label --}}
        <div class="card-header">Pool Entry Pass</div>

        {{-- Member Info --}}
        <div class="card-info">
            <p class="card-title">Pool Entry Pass</p>
            <p class="card-name">{{ $member->nama }}</p>
            <p class="card-unit">Unit {{ $member->unit }}</p>
            <p class="card-kawasan">{{ $member->kawasan }}</p>
            <p class="card-id">ID #{{ $member->id }}</p>
        </div>

        {{-- QR Code --}}
        <div class="card-qr">
            {!! $qrCode !!}
        </div>

    </div>

</body>
</html>