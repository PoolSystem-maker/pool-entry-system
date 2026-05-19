@extends('layouts.admin')

@section('content')

<div class="page-header">
    <div>
        <h1 class="page-title">Entry Logs</h1>
        <p class="page-subtitle">Semua riwayat scan masuk dan ditolak</p>
    </div>
</div>

{{-- FILTERS --}}
<div class="card" style="margin-bottom:16px;">
    <form method="GET" action="{{ route('admin.entry-logs.index') }}"
        style="display:flex; flex-wrap:wrap; gap:10px; align-items:flex-end;">

        <div>
            <label>Tanggal</label>
            <input type="date" name="date" value="{{ request('date') }}"
                style="width:auto;">
        </div>

        <div style="flex:1; min-width:160px;">
            <label>Nama Member</label>
            <input type="text" name="member_name"
                value="{{ request('member_name') }}"
                placeholder="Cari nama...">
        </div>

        <div>
            <label>Status</label>
            <select name="status" style="width:auto;">
                <option value="">Semua</option>
                <option value="granted"
                    {{ request('status') === 'granted' ? 'selected' : '' }}>
                    Granted
                </option>
                <option value="denied"
                    {{ request('status') === 'denied' ? 'selected' : '' }}>
                    Denied
                </option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Filter</button>

        @if(request('date') || request('member_name') || request('status'))
            <a href="{{ route('admin.entry-logs.index') }}" class="btn btn-ghost">
                Reset
            </a>
        @endif

    </form>
</div>

{{-- DESKTOP TABLE --}}
<div class="table-wrap desktop-table">
    <table>
        <thead>
            <tr>
                <th>Waktu</th>
                <th>Nama Member</th>
                <th>Unit</th>
                <th>Kawasan</th>
                <th>Gate</th>
                <th style="text-align:center;">Status</th>
                <th>Alasan Ditolak</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $log)
                <tr>
                    <td style="white-space:nowrap; color:var(--text-muted); font-size:13px;">
                        {{ $log->scanned_at->format('d M Y, H:i:s') }}
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
                    <td style="color:var(--text-muted);">
                        {{ $log->gate_name }}
                    </td>
                    <td style="text-align:center;">
                        <span class="badge
                            {{ $log->status === 'granted' ? 'badge-green' : 'badge-red' }}">
                            {{ strtoupper($log->status) }}
                        </span>
                    </td>
                    <td style="color:var(--text-muted); font-size:13px;">
                        {{ $log->deny_reason ?? '-' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7"
                        style="text-align:center; padding:40px; color:var(--text-muted);">
                        Belum ada log scan.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if($logs->hasPages())
        <div class="pagination-wrap">{{ $logs->links() }}</div>
    @endif
</div>

{{-- MOBILE CARDS --}}
<div class="mobile-cards" style="display:none;">
    <div class="table-wrap">
        @forelse($logs as $log)
            <div style="padding:14px 16px; border-bottom:1px solid var(--border);">

                <div style="display:flex; justify-content:space-between;
                    align-items:flex-start; margin-bottom:6px;">
                    <div>
                        @if($log->member)
                            <a href="{{ route('admin.members.show', $log->member) }}"
                                style="color:var(--accent); font-weight:700;
                                    font-size:15px; text-decoration:none;">
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
                    <span class="badge
                        {{ $log->status === 'granted' ? 'badge-green' : 'badge-red' }}">
                        {{ strtoupper($log->status) }}
                    </span>
                </div>

                <div style="display:flex; justify-content:space-between;
                    align-items:center;">
                    <span style="color:var(--text-muted); font-size:12px;">
                        {{ $log->scanned_at->format('d M Y, H:i:s') }}
                    </span>
                    @if($log->deny_reason)
                        <span style="color:var(--danger); font-size:12px;
                            max-width:180px; text-align:right;">
                            {{ $log->deny_reason }}
                        </span>
                    @endif
                </div>

            </div>
        @empty
            <div style="padding:40px; text-align:center; color:var(--text-muted);">
                Belum ada log scan.
            </div>
        @endforelse

        @if($logs->hasPages())
            <div class="pagination-wrap">{{ $logs->links() }}</div>
        @endif
    </div>
</div>

<style>
    @media (max-width: 768px) {
        .desktop-table { display: none !important; }
        .mobile-cards  { display: block !important; }
    }
</style>

@endsection