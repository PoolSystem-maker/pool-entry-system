<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print All Member Cards</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            background: #f3f4f6;
            font-family: 'Segoe UI', sans-serif;
            padding: 24px;
        }

        /* Print controls — hidden when printing */
        .controls {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 24px;
            flex-wrap: wrap;
        }

        .controls h1 {
            font-size: 20px;
            font-weight: 700;
            color: #1e3a8a;
            flex: 1;
        }

        .btn {
            padding: 10px 24px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
        }

        .btn-print { background: #2563eb; color: white; }
        .btn-back  { background: #e5e7eb; color: #374151; }

        .card-count {
            font-size: 13px;
            color: #6b7280;
        }

        /* Grid of cards */
        .cards-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            justify-content: flex-start;
        }

        /* CR80 card: 85.6mm × 54mm */
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
            color: white;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            page-break-inside: avoid;
            break-inside: avoid;
        }

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

        /* Inactive card style */
        .card.inactive {
            background: linear-gradient(135deg, #4b5563 0%, #6b7280 100%);
            opacity: 0.7;
        }

        /* Print styles */
        @media print {
            body {
                background: white;
                padding: 8mm;
            }

            .controls {
                display: none !important;
            }

            .cards-grid {
                gap: 6mm;
            }

            .card {
                box-shadow: none;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>

    {{-- Controls (hidden when printing) --}}
    <div class="controls">
        <h1>🖨️ Print All Member Cards</h1>
        <span class="card-count">{{ $members->count() }} cards total</span>
        <a class="btn btn-back" href="{{ route('admin.members.index') }}">← Kembali</a>
        <button class="btn btn-print" onclick="window.print()">🖨️ Print / Save as PDF</button>
    </div>

    {{-- Cards Grid --}}
    <div class="cards-grid">
        @foreach($members as $member)
            <div class="card {{ !$member->is_active ? 'inactive' : '' }}">

                <div class="card-header">Pool Entry Pass</div>

                <div class="card-info">
                    <p class="card-title">Pool Entry Pass</p>
                    <p class="card-name">{{ $member->nama }}</p>
                    <p class="card-unit">Unit {{ $member->unit }}</p>
                    <p class="card-kawasan">{{ $member->kawasan }}</p>
                    <p class="card-id">ID #{{ $member->id }}</p>
                </div>

                <div class="card-qr">
                    {!! $qrCodes[$member->id] !!}
                </div>

            </div>
        @endforeach
    </div>

</body>
</html>