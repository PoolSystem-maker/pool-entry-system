@extends('layouts.admin')

@section('content')

<div class="page-header">
    <div>
        <h1 class="page-title">Monthly Logs</h1>
        <p class="page-subtitle">Riwayat akses granted per bulan</p>
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

{{-- SUMMARY STAT --}}
@php $totalGranted = collect($logs)->flatten()->count(); @endphp
<div style="display:flex; gap:12px; margin-bottom:16px; flex-wrap:wrap;">
    <div class="card" style="display:flex; align-items:center; gap:12px;
        padding:14px 20px; flex:1; min-width:160px;">
        <div style="font-size:28px;">📅</div>
        <div>
            <div style="color:var(--text-muted); font-size:12px;">Total Hari Aktif</div>
            <div style="color:var(--text); font-size:22px; font-weight:800;">
                {{ count($logs) }}
            </div>
        </div>
    </div>
    <div class="card" style="display:flex; align-items:center; gap:12px;
        padding:14px 20px; flex:1; min-width:160px;">
        <div style="font-size:28px;">🚪</div>
        <div>
            <div style="color:var(--text-muted); font-size:12px;">Total Akses Bulan Ini</div>
            <div style="color:var(--success); font-size:22px; font-weight:800;">
                {{ $totalGranted }}
            </div>
        </div>
    </div>
</div>

{{-- LOGS GROUPED BY DAY --}}
@forelse($logs as $date => $dayLogs)
    <div class="table-wrap" style="margin-bottom:12px;">

        {{-- Day header --}}
        <div style="background:var(--navy-soft); padding:12px 16px;
            display:flex; justify-content:space-between; align-items:center;">
            <h2 style="font-size:14px; font-weight:700; color:var(--text);">
                {{ \Carbon\Carbon::parse($date)->translatedFormat('l, d F Y') }}
            </h2>
            <span class="badge badge-blue">{{ $dayLogs->count() }} akses</span>
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
                        <th>Kawasan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($dayLogs as $i => $log)
                        <tr>
                            <td style="color:var(--text-muted);">{{ $i + 1 }}</td>
                            <td style="color:var(--text-muted); font-size:13px;">
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
                                {{ $log->member->kawasan ?? '-' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Mobile list --}}
        <div class="mobile-cards" style="display:none;">
            @foreach($dayLogs as $i => $log)
                <div style="padding:12px 16px; border-bottom:1px solid var(--border);
                    display:flex; justify-content:space-between; align-items:center;">
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
                        <p style="color:var(--text-muted); font-size:12px; margin-top:2px;">
                            {{ $log->member->unit ?? '-' }} —
                            {{ $log->member->kawasan ?? '-' }}
                        </p>
                    </div>
                    <span style="color:var(--text-muted); font-size:12px;">
                        {{ $log->scanned_at->format('H:i:s') }}
                    </span>
                </div>
            @endforeach
        </div>

    </div>
@empty
    <div class="card" style="text-align:center; padding:48px; color:var(--text-muted);">
        Tidak ada data akses untuk bulan ini.
    </div>
@endforelse

<style>
    @media (max-width: 768px) {
        .desktop-table { display: none !important; }
        .mobile-cards  { display: block !important; }
    }
</style>

@endsection