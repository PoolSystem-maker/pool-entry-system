<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Log — {{ $parsedDate->format('d F Y') }}</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 12px;
            color: #1e293b;
            padding: 24px;
            background: white;
        }

        /* Print button — hidden when printing */
        .no-print {
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .btn-print {
            background: #1d4ed8;
            color: white;
            border: none;
            padding: 10px 24px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-back {
            background: #e2e8f0;
            color: #334155;
            border: none;
            padding: 10px 24px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }

        /* Header */
        .header {
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 2px solid #1d4ed8;
        }

        .header h1 {
            font-size: 18px;
            font-weight: 800;
            color: #1e3a8a;
        }

        .header p {
            color: #64748b;
            font-size: 12px;
            margin-top: 4px;
        }

        /* Summary row */
        .summary {
            display: flex;
            gap: 16px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .summary-item {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 10px 16px;
            min-width: 120px;
        }

        .summary-label {
            font-size: 10px;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .summary-value {
            font-size: 20px;
            font-weight: 800;
            color: #1e293b;
            margin-top: 2px;
        }

        .summary-value.green { color: #16a34a; }
        .summary-value.red   { color: #dc2626; }

        /* Table */
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }

        thead {
            background: #1e3a8a;
            color: white;
        }

        thead th {
            padding: 8px 10px;
            text-align: left;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        tbody tr {
            border-bottom: 1px solid #e2e8f0;
        }

        tbody tr:nth-child(even) {
            background: #f8fafc;
        }

        tbody td {
            padding: 7px 10px;
            color: #334155;
            vertical-align: middle;
        }

        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 999px;
            font-size: 10px;
            font-weight: 700;
        }

        .badge-granted {
            background: #dcfce7;
            color: #15803d;
        }

        .badge-denied {
            background: #fee2e2;
            color: #dc2626;
        }

        /* Footer */
        .footer {
            margin-top: 20px;
            padding-top: 12px;
            border-top: 1px solid #e2e8f0;
            font-size: 10px;
            color: #94a3b8;
            display: flex;
            justify-content: space-between;
        }

        @media print {
            .no-print { display: none !important; }
            body { padding: 16px; }
            @page { margin: 10mm; size: A4 landscape; }
        }
    </style>
</head>
<body>

    {{-- Print controls --}}
    <div class="no-print">
        <button class="btn-print" onclick="window.print()">
            🖨️ Print / Save as PDF
        </button>
        <a class="btn-back" onclick="window.history.back()">← Kembali</a>
    </div>

    {{-- Header --}}
    <div class="header">
        <h1>🏊 Pool Entry — Daily Log</h1>
        <p>{{ $parsedDate->translatedFormat('l, d F Y') }}</p>
    </div>

    {{-- Summary --}}
    @php
        $total   = $logs->count();
        $granted = $logs->where('status', 'granted')->count();
        $denied  = $logs->where('status', 'denied')->count();
    @endphp

    <div class="summary">
        <div class="summary-item">
            <div class="summary-label">Total Scan</div>
            <div class="summary-value">{{ $total }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Granted</div>
            <div class="summary-value green">{{ $granted }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Denied</div>
            <div class="summary-value red">{{ $denied }}</div>
        </div>
    </div>

    {{-- Table --}}
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Waktu</th>
                <th>Nama</th>
                <th>Unit</th>
                <th>Cluster</th>
                <th>Kawasan</th>
                <th>Status</th>
                <th>Alasan Ditolak</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $i => $log)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $log->scanned_at->format('H:i:s') }}</td>
                    <td><strong>{{ $log->member->nama ?? '-' }}</strong></td>
                    <td>{{ $log->member->unit ?? '-' }}</td>
                    <td>{{ $log->member->cluster ?? '—' }}</td>
                    <td>{{ $log->member->kawasan ?? '-' }}</td>
                    <td>
                        <span class="badge
                            {{ $log->status === 'granted'
                                ? 'badge-granted' : 'badge-denied' }}">
                            {{ strtoupper($log->status) }}
                        </span>
                    </td>
                    <td>{{ $log->deny_reason ?? '—' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8"
                        style="text-align:center; padding:20px; color:#94a3b8;">
                        Tidak ada data untuk tanggal ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Footer --}}
    <div class="footer">
        <span>Pool Entry System</span>
        <span>Dicetak: {{ now()->format('d M Y, H:i') }} WIB</span>
    </div>

</body>
</html>