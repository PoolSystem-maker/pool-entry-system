@extends('layouts.admin')

@section('content')

<div class="page-header">
    <div>
        <a href="{{ route('admin.members.index') }}"
            style="color:var(--text-muted); text-decoration:none; font-size:13px;">
            ← Kembali ke Members
        </a>
        <h1 class="page-title" style="margin-top:4px;">{{ $member->nama }}</h1>
    </div>
    <div style="display:flex; gap:10px; flex-wrap:wrap;">
        <a href="{{ route('admin.members.edit', $member) }}" class="btn btn-warning">
            ✏️ Edit
        </a>
        <a href="{{ route('admin.members.printCard', $member) }}"
            class="btn" style="background:#7c3aed; color:#fff;">
            ⬇ Download Card
        </a>
    </div>
</div>

<div class="show-grid">

    {{-- LEFT COLUMN --}}
    <div style="display:flex; flex-direction:column; gap:16px;">

        {{-- Info Card --}}
        <div class="card">
            <div style="display:flex; justify-content:space-between;
                align-items:center; margin-bottom:16px;">
                <h2 style="font-size:15px; font-weight:700; color:var(--text);">
                    Informasi Member
                </h2>
                <span class="badge {{ $member->is_active ? 'badge-green' : 'badge-red' }}">
                    {{ $member->is_active ? 'Aktif' : 'Nonaktif' }}
                </span>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                <div>
                    <p style="color:var(--text-muted); font-size:12px;">Nama Pemilik</p>
                    <p style="color:var(--text); font-weight:600; font-size:16px;
                        margin-top:2px;">{{ $member->nama }}</p>
                </div>
                <div>
                    <p style="color:var(--text-muted); font-size:12px;">No KTP</p>
                    <p style="color:var(--text); font-weight:500;
                        margin-top:2px;">{{ $member->no_ktp }}</p>
                </div>
                <div>
                    <p style="color:var(--text-muted); font-size:12px;">No Telepon</p>
                    <p style="color:var(--text); font-weight:500;
                        margin-top:2px;">{{ $member->no_telp }}</p>
                </div>
                <div>
                    <p style="color:var(--text-muted); font-size:12px;">Unit</p>
                    <p style="color:var(--text); font-weight:500;
                        margin-top:2px;">{{ $member->unit }}</p>
                </div>
                <div>
                    <p style="color:var(--text-muted); font-size:12px;">Cluster</p>
                    <p style="color:var(--text); font-weight:500;
                        margin-top:2px;">{{ $member->cluster ?? '—' }}</p>
                </div>
                <div>
                    <p style="color:var(--text-muted); font-size:12px;">Kawasan</p>
                    <p style="color:var(--text); font-weight:500;
                        margin-top:2px;">{{ $member->kawasan }}</p>
                </div>
                <div>
                    <p style="color:var(--text-muted); font-size:12px;">Batas Harian Default</p>
                    <p style="color:var(--text); font-weight:500;
                        margin-top:2px;">{{ $member->daily_limit }}x per hari</p>
                </div>
                <div>
                    <p style="color:var(--text-muted); font-size:12px;">Member ID</p>
                    <p style="color:var(--text); font-weight:500;
                        margin-top:2px;">#{{ $member->id }}</p>
                </div>
                <div>
                    <p style="color:var(--text-muted); font-size:12px;">Terdaftar Sejak</p>
                    <p style="color:var(--text); font-weight:500;
                        margin-top:2px;">{{ $member->created_at->format('d M Y') }}</p>
                </div>
            </div>
        </div>

        {{-- Today's Access --}}
        <div class="card">
            <h2 style="font-size:15px; font-weight:700; color:var(--text);
                margin-bottom:16px;">Akses Hari Ini</h2>

            @php
                $used      = $member->todayGrantedCount();
                $limit     = $member->todayLimit();
                $remaining = $member->remainingEntriesToday();
                $percent   = $limit > 0 ? ($used / $limit) * 100 : 0;
                $isOverride = $member->dailyLimitOverrides()
                    ->whereDate('date', today())->exists();
            @endphp

            <div style="display:flex; justify-content:space-between;
                align-items:center; margin-bottom:10px; flex-wrap:wrap; gap:8px;">
                <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                    <span style="color:var(--text); font-size:14px;">
                        {{ $used }} dari {{ $limit }} akses digunakan
                    </span>
                    @if($isOverride)
                        <span class="badge badge-yellow">Override Aktif</span>
                    @endif
                </div>
                <span style="font-weight:700; font-size:14px;
                    color:{{ $remaining === 0 ? 'var(--danger)' : 'var(--success)' }};">
                    Sisa: {{ $remaining }}
                </span>
            </div>

            <div class="progress-track" style="margin-bottom:20px;">
                <div class="progress-fill"
                    style="width:{{ min($percent, 100) }}%;
                        background:{{ $percent >= 100
                            ? 'var(--danger)'
                            : ($percent >= 75 ? 'var(--warning)' : 'var(--success)') }};">
                </div>
            </div>

            {{-- Override Form --}}
            <form method="POST"
                action="{{ route('admin.members.overrideLimit', $member) }}">
                @csrf
                <label>Set batas akses untuk hari ini saja</label>
                <div style="display:flex; gap:10px; align-items:center;
                    margin-top:6px; flex-wrap:wrap;">
                    <input type="number" name="override_limit" min="1" max="100"
                        value="{{ $limit }}"
                        style="width:100px;">
                    <button type="submit" class="btn"
                        style="background:var(--warning); color:#fff;">
                        Set Limit Hari Ini
                    </button>
                </div>
                <p class="form-hint" style="margin-top:8px;">
                    Besok otomatis kembali ke default ({{ $member->daily_limit }}x/hari).
                </p>
            </form>
        </div>

        {{-- Actions --}}
        <div class="card">
            <h2 style="font-size:15px; font-weight:700; color:var(--text);
                margin-bottom:16px;">Aksi Member</h2>

            <div style="display:flex; flex-wrap:wrap; gap:10px;">

                <form method="POST"
                    action="{{ route('admin.members.toggle', $member) }}">
                    @csrf @method('PATCH')
                    <button type="submit" class="btn
                        {{ $member->is_active ? 'btn-danger' : 'btn-success' }}">
                        {{ $member->is_active ? '🚫 Nonaktifkan' : '✅ Aktifkan' }}
                    </button>
                </form>

                <form method="POST"
                    action="{{ route('admin.members.regenerateQr', $member) }}"
                    onsubmit="return confirm('QR lama akan tidak berlaku. Lanjutkan?')">
                    @csrf @method('PATCH')
                    <button type="submit" class="btn btn-ghost">
                        🔄 Regenerate QR
                    </button>
                </form>

                <a href="{{ route('admin.members.downloadQr', $member) }}"
                    class="btn btn-ghost">
                    ⬇️ Download QR
                </a>

                <form method="POST"
                    action="{{ route('admin.members.destroy', $member) }}"
                    onsubmit="return confirm('Yakin hapus member ini? Semua log ikut terhapus.')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        🗑️ Hapus Member
                    </button>
                </form>

            </div>
        </div>

    </div>

    {{-- RIGHT COLUMN --}}
    <div class="show-right">

        {{-- QR Code --}}
        <div class="card" style="display:flex; flex-direction:column;
            align-items:center; margin-bottom:16px;">
            <h2 style="font-size:15px; font-weight:700; color:var(--text);
                margin-bottom:16px; align-self:flex-start;">QR Code</h2>

            <div style="background:#fff; padding:16px; border-radius:12px;
                max-width:220px; width:100%; display:flex;
                align-items:center; justify-content:center;">
                {!! $qrCode !!}
            </div>

            <p style="color:var(--text-muted); font-size:11px; margin-top:12px;
                text-align:center; word-break:break-all; line-height:1.5;">
                {{ $member->qr_token }}
            </p>
        </div>

        {{-- Recent Scans --}}
        <div class="card">
            <h2 style="font-size:15px; font-weight:700; color:var(--text);
                margin-bottom:16px;">Scan Terakhir</h2>

            @php
                $recentLogs = $member->entryLogs()
                    ->orderBy('scanned_at', 'desc')
                    ->limit($member->todayLimit() + 5)
                    ->get();
            @endphp

            @forelse($recentLogs as $log)
                <div style="display:flex; justify-content:space-between;
                    align-items:center; padding:8px 0;
                    border-bottom:1px solid var(--border);">
                    <span style="color:var(--text-muted); font-size:12px;">
                        {{ $log->scanned_at->format('d M, H:i') }}
                    </span>
                    <span class="badge
                        {{ $log->status === 'granted' ? 'badge-green' : 'badge-red' }}">
                        {{ strtoupper($log->status) }}
                    </span>
                </div>
            @empty
                <p style="color:var(--text-muted); font-size:13px;">
                    Belum ada riwayat scan.
                </p>
            @endforelse
        </div>

    </div>

</div>

<style>
    .show-grid {
        display: grid;
        grid-template-columns: 1fr 340px;
        gap: 16px;
    }

    @media (max-width: 768px) {
        .show-grid {
            grid-template-columns: 1fr !important;
        }

        /* Show QR and scan history ABOVE member info on mobile */
        .show-right {
            order: -1;
        }
    }
</style>

@endsection