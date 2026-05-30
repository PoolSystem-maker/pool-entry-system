@extends('layouts.admin')

@section('content')

<div class="page-header">
    <div>
        <h1 class="page-title">Monthly Logs</h1>
        <p class="page-subtitle">Riwayat akses per bulan</p>
    </div>
    <a href="{{ route('admin.monthly-logs.export', ['month' => $month, 'year' => $year]) }}"
        class="btn btn-success">
        ⬇ Export Excel
    </a>
</div>

{{-- FILTER --}}
<div class="card" style="margin-bottom:16px;">
    <form method="GET" action="{{ route('admin.monthly-logs.index') }}"
        style="display:flex; flex-wrap:wrap; gap:10px; align-items:flex-end;">

        <div>
            <label>Bulan</label>
            <select name="month" style="width:auto;">
                @foreach(range(1, 12) as $m)
                    <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                        {{ DateTime::createFromFormat('!m', $m)->format('F') }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label>Tahun</label>
            <select name="year" style="width:auto;">
                @foreach($years as $y)
                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>
                        {{ $y }}
                    </option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Tampilkan</button>

    </form>
</div>

{{-- SUMMARY --}}
@php
    $totalEntries  = collect($logs)->flatten()->count();
    $totalGranted  = collect($logs)->flatten()->where('status', 'granted')->count();
    $totalDenied   = collect($logs)->flatten()->where('status', 'denied')->count();
@endphp

<div style="display:flex; gap:12px; margin-bottom:16px; flex-wrap:wrap;">
    <div class="card" style="display:flex; align-items:center; gap:12px;
        padding:14px 20px; flex:1; min-width:140px;">
        <div style="font-size:28px;">📅</div>
        <div>
            <div style="color:var(--text-muted); font-size:12px;">Total Hari Aktif</div>
            <div style="color:var(--text); font-size:22px; font-weight:800;">
                {{ count($logs) }}
            </div>
        </div>
    </div>
    <div class="card" style="display:flex; align-items:center; gap:12px;
        padding:14px 20px; flex:1; min-width:140px;">
        <div style="font-size:28px;">🚪</div>
        <div>
            <div style="color:var(--text-muted); font-size:12px;">Total Scan</div>
            <div style="color:var(--text); font-size:22px; font-weight:800;">
                {{ $totalEntries }}
            </div>
        </div>
    </div>
    <div class="card" style="display:flex; align-items:center; gap:12px;
        padding:14px 20px; flex:1; min-width:140px;">
        <div style="font-size:28px;">✅</div>
        <div>
            <div style="color:var(--text-muted); font-size:12px;">Granted</div>
            <div style="color:var(--success); font-size:22px; font-weight:800;">
                {{ $totalGranted }}
            </div>
        </div>
    </div>
    <div class="card" style="display:flex; align-items:center; gap:12px;
        padding:14px 20px; flex:1; min-width:140px;">
        <div style="font-size:28px;">❌</div>
        <div>
            <div style="color:var(--text-muted); font-size:12px;">Denied</div>
            <div style="color:var(--danger); font-size:22px; font-weight:800;">
                {{ $totalDenied }}
            </div>
        </div>
    </div>
</div>

{{-- LOGS GROUPED BY DAY --}}
@forelse($logs as $date => $dayLogs)
    <div class="table-wrap" style="margin-bottom:12px;">

        {{-- Day header --}}
        <div style="background:var(--navy-soft); padding:12px 16px;
            display:flex; justify-content:space-between; align-items:center;
            flex-wrap:wrap; gap:8px;">
            <h2 style="font-size:14px; font-weight:700; color:var(--text);">
                {{ \Carbon\Carbon::parse($date)->translatedFormat('l, d F Y') }}
            </h2>
            <div style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
                <span class="badge badge-green">
                    {{ $dayLogs->where('status','granted')->count() }} granted
                </span>
                @if($dayLogs->where('status','denied')->count() > 0)
                    <span class="badge badge-red">
                        {{ $dayLogs->where('status','denied')->count() }} denied
                    </span>
                @endif
                <a href="{{ route('admin.monthly-logs.dailyExport', ['date' => $date]) }}"
                    target="_blank"
                    style="background:#1d4ed8; color:white; padding:4px 12px;
                        border-radius:6px; font-size:11px; font-weight:600;
                        text-decoration:none;">
                    🖨️ Export Harian
                </a>
            </div>
        </div>

        {{-- Desktop table --}}
        <div class="desktop-table">
            <table>
                <thead>
                    <tr>
                        <th style="width:40px;">No</th>
                        <th>Waktu</th>
                        <th>Nama</th>
                        <th>Unit</th>
                        <th>Cluster</th>
                        <th>Kawasan</th>
                        <th style="text-align:center;">Status</th>
                        <th>Alasan Ditolak</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($dayLogs as $i => $log)
                        <tr>
                            <td style="color:var(--text-muted);">{{ $i + 1 }}</td>
                            <td style="color:var(--text-muted); font-size:13px;
                                white-space:nowrap;">
                                {{ $log->scanned_at->format('H:i:s') }}
                            </td>
                            <td>
                                @if($log->member)
                                    <a href="{{ route('admin.members.show', $log->member) }}"
                                        style="color:var(--accent); text-decoration:none;
                                            font-weight:600;">
                                        {{ $log->member->nama }}
                                    </a>
                                @else
                                    <span style="color:var(--text-muted);">Unknown</span>
                                @endif
                            </td>
                            <td style="color:var(--text-muted);">
                                {{ $log->member->unit ?? '-' }}
                            </td>
                            <td style="color:var(--text-muted);">
                                {{ $log->member->cluster ?? '—' }}
                            </td>
                            <td style="color:var(--text-muted);">
                                {{ $log->member->kawasan ?? '-' }}
                            </td>
                            <td style="text-align:center;">
                                <span class="badge
                                    {{ $log->status === 'granted'
                                        ? 'badge-green' : 'badge-red' }}">
                                    {{ strtoupper($log->status) }}
                                </span>
                            </td>
                            <td style="color:var(--text-muted); font-size:12px;">
                                {{ $log->deny_reason ?? '—' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Mobile list --}}
        <div class="mobile-cards" style="display:none;">
            @foreach($dayLogs as $i => $log)
                <div style="padding:12px 16px; border-bottom:1px solid var(--border);">
                    <div style="display:flex; justify-content:space-between;
                        align-items:flex-start; margin-bottom:4px;">
                        <div>
                            @if($log->member)
                                <a href="{{ route('admin.members.show', $log->member) }}"
                                    style="color:var(--accent); font-weight:600;
                                        font-size:14px; text-decoration:none;">
                                    {{ $log->member->nama }}
                                </a>
                            @else
                                <span style="color:var(--text-muted);">Unknown</span>
                            @endif
                            <p style="color:var(--text-muted); font-size:12px;
                                margin-top:2px;">
                                {{ $log->member->unit ?? '-' }}
                                @if($log->member?->cluster)
                                    ({{ $log->member->cluster }})
                                @endif
                                — {{ $log->member->kawasan ?? '-' }}
                            </p>
                        </div>
                        <div style="display:flex; flex-direction:column;
                            align-items:flex-end; gap:4px;">
                            <span class="badge
                                {{ $log->status === 'granted'
                                    ? 'badge-green' : 'badge-red' }}">
                                {{ strtoupper($log->status) }}
                            </span>
                            <span style="color:var(--text-muted); font-size:12px;">
                                {{ $log->scanned_at->format('H:i:s') }}
                            </span>
                        </div>
                    </div>
                    @if($log->deny_reason)
                        <p style="color:var(--danger); font-size:12px; margin-top:4px;">
                            {{ $log->deny_reason }}
                        </p>
                    @endif
                </div>
            @endforeach
        </div>

    </div>
@empty
    <div class="card" style="text-align:center; padding:48px;
        color:var(--text-muted);">
        Tidak ada data untuk bulan ini.
    </div>
@endforelse

<style>
    @media (max-width: 768px) {
        .desktop-table { display: none !important; }
        .mobile-cards  { display: block !important; }
    }
</style>

@endsection