@extends('layouts.admin')

@section('content')

<div class="page-header">
    <div>
        <h1 class="page-title">Dashboard</h1>
        <p class="page-subtitle">{{ now()->format('l, d F Y') }} — WIB</p>
    </div>
</div>

{{-- STAT CARDS + KAWASAN --}}
<div class="stat-grid" style="margin-bottom:16px;">

    <div class="stat-card">
        <div class="stat-icon">👥</div>
        <div>
            <div class="stat-label">Total Members</div>
            <div class="stat-value">{{ $totalMembers }}</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon">🚪</div>
        <div>
            <div class="stat-label">Masuk Hari Ini</div>
            <div class="stat-value" style="color:var(--success);">{{ $todayEntryCount }}</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon">⛔</div>
        <div>
            <div class="stat-label">Batas Tercapai</div>
            <div class="stat-value" style="color:var(--danger);">{{ $membersAtLimit }}</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon">🏘️</div>
        <div>
            <div class="stat-label">Total Kawasan</div>
            <div class="stat-value" style="color:var(--accent);">{{ $membersByKawasan->count() }}</div>
        </div>
    </div>

    {{-- Members by Kawasan — fits naturally as another stat card --}}
    @foreach($membersByKawasan as $kawasan => $total)
        <div class="stat-card">
            <div class="stat-icon">🏊</div>
            <div>
                <div class="stat-label">{{ $kawasan }}</div>
                <div class="stat-value" style="font-size:22px;">{{ $total }} <span style="font-size:13px; font-weight:500; color:var(--text-muted);">members</span></div>
            </div>
        </div>
    @endforeach

</div>

{{-- RECENT SCANS — full width below --}}
<div class="card">
    <h2 style="font-size:15px; font-weight:700; color:var(--text);
        margin-bottom:16px;">Aktivitas Scan Terbaru</h2>

    @forelse($recentScans as $scan)
        <div style="display:flex; justify-content:space-between; align-items:center;
            padding:10px 0; border-bottom:1px solid var(--border);">
            <div>
                <p style="color:var(--text); font-weight:500; font-size:14px;">
                    {{ $scan->member->nama ?? 'Unknown' }}
                </p>
                <p style="color:var(--text-muted); font-size:12px;">
                    {{ $scan->scanned_at->format('d M Y, H:i:s') }}
                </p>
            </div>
            <span class="badge {{ $scan->status === 'granted' ? 'badge-green' : 'badge-red' }}">
                {{ strtoupper($scan->status) }}
            </span>
        </div>
    @empty
        <p style="color:var(--text-muted); font-size:13px;">Belum ada aktivitas scan.</p>
    @endforelse

    <a href="{{ route('admin.entry-logs.index') }}"
        style="display:block; text-align:center; color:var(--accent);
            font-size:13px; margin-top:16px; text-decoration:none;">
        Lihat semua log →
    </a>
</div>

@endsection