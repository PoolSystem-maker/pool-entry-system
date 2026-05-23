@extends('layouts.admin')

@section('content')

{{-- IMPORT SKIP REPORT --}}
@if(session('import_skipped') && count(session('import_skipped')) > 0)
    <div id="skip-report" style="background:rgba(245,158,11,0.1);
        border:1px solid rgba(245,158,11,0.3);
        border-radius:12px; padding:16px; margin-bottom:16px;
        position:relative;">

        {{-- Close button --}}
        <button onclick="closeSkipReport()"
            style="position:absolute; top:12px; right:12px; background:none;
                border:none; color:var(--text-muted); font-size:18px;
                cursor:pointer; line-height:1; padding:4px 8px; border-radius:4px;"
            title="Tutup">
            ✕
        </button>

        <h3 style="color:#fcd34d; font-size:14px; font-weight:700;
            margin-bottom:12px; padding-right:32px;">
            ⚠️ {{ count(session('import_skipped')) }} baris dilewati saat import:
        </h3>

        <div style="overflow-x:auto;">
            <table style="width:100%; font-size:13px; border-collapse:collapse;">
                <thead>
                    <tr>
                        <th style="text-align:left; padding:6px 12px;
                            color:var(--text-muted);">Nama</th>
                        <th style="text-align:left; padding:6px 12px;
                            color:var(--text-muted);">No KTP</th>
                        <th style="text-align:left; padding:6px 12px;
                            color:var(--text-muted);">Unit</th>
                        <th style="text-align:left; padding:6px 12px;
                            color:var(--text-muted);">Kawasan</th>
                        <th style="text-align:left; padding:6px 12px;
                            color:var(--text-muted);">Alasan Dilewati</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach(session('import_skipped') as $row)
                        <tr style="border-top:1px solid var(--border);">
                            <td style="padding:8px 12px; color:var(--text);">
                                {{ $row['nama'] }}
                            </td>
                            <td style="padding:8px 12px; color:var(--text-muted);">
                                {{ $row['no_ktp'] }}
                            </td>
                            <td style="padding:8px 12px; color:var(--text-muted);">
                                {{ $row['unit'] }}
                            </td>
                            <td style="padding:8px 12px; color:var(--text-muted);">
                                {{ $row['kawasan'] }}
                            </td>
                            <td style="padding:8px 12px; color:#fcd34d; font-size:12px;">
                                {{ $row['reason'] }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    </div>

    <script>
        function closeSkipReport() {
            document.getElementById('skip-report').style.display = 'none';
            // Also clear from server session so it doesn't come back on refresh
            fetch('{{ route("admin.members.clearImportSession") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                }
            });
        }
    </script>
@endif

<div class="page-header">
    <div>
        <h1 class="page-title">Members</h1>
        <p class="page-subtitle">{{ $members->total() }} member terdaftar</p>
    </div>
    <div style="display:flex; gap:10px; flex-wrap:wrap;">
        <a href="{{ route('admin.members.downloadAllCards') }}"
            class="btn" style="background:#7c3aed; color:#fff;">
            ⬇ Download All Cards (ZIP)
        </a>
        <a href="{{ route('admin.members.create') }}" class="btn btn-primary">
            + Tambah Member
        </a>
    </div>
</div>

{{-- SEARCH --}}
<div class="card" style="margin-bottom:16px;">
    <form method="GET" action="{{ route('admin.members.index') }}"
        style="display:flex; flex-wrap:wrap; gap:10px; align-items:flex-end;">
        <div style="flex:1; min-width:200px;">
            <label>Cari nama, unit, atau kawasan</label>
            <input type="text" name="search" value="{{ request('search') }}"
                placeholder="Cari...">
        </div>
        <button type="submit" class="btn btn-primary">Cari</button>
        @if(request('search'))
            <a href="{{ route('admin.members.index') }}" class="btn btn-ghost">Reset</a>
        @endif
    </form>
</div>

{{-- IMPORT / EXPORT --}}
<div style="display:flex; flex-wrap:wrap; gap:10px; margin-bottom:16px;">

    <a href="{{ route('admin.members.export') }}" class="btn btn-success">
        ⬇ Export Excel
    </a>

    <a href="{{ route('admin.members.sampleTemplate') }}" class="btn btn-ghost">
        📄 Download Template
    </a>

    <form method="POST" action="{{ route('admin.members.import') }}"
        enctype="multipart/form-data"
        style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
        @csrf
        <input type="file" name="file" accept=".xlsx,.xls"
            style="width:auto; padding:7px 10px; font-size:13px;">
        <button type="submit" class="btn btn-warning">⬆ Import Excel</button>
    </form>

</div>

{{-- TABLE — desktop --}}
<div class="table-wrap">

    {{-- Desktop table --}}
    <div class="desktop-table">
        <table>
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Unit</th>
                    <th>Kawasan</th>
                    <th>Cluster</th>
                    <th style="text-align:center;">Status</th>
                    <th style="text-align:center;">Akses Hari Ini</th>
                    <th style="text-align:center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($members as $member)
                    @php
                        $used  = $member->todayGrantedCount();
                        $limit = $member->todayLimit();
                    @endphp
                    <tr>
                        <td>
                            <a href="{{ route('admin.members.show', $member) }}"
                                style="color:var(--accent); text-decoration:none;
                                    font-weight:600;">
                                {{ $member->nama }}
                            </a>
                        </td>
                        <td style="color:var(--text-muted);">{{ $member->unit }}</td>
                        <td style="color:var(--text-muted);">{{ $member->kawasan }}</td>
                        <td style="color:var(--text-muted);">{{ $member->cluster ?? '—' }}</td>
                        <td style="text-align:center;">
                            <span class="badge
                                {{ $member->is_active ? 'badge-green' : 'badge-red' }}">
                                {{ $member->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td style="text-align:center;">
                            <span style="font-weight:700;
                                color:{{ $used >= $limit
                                    ? 'var(--danger)' : 'var(--success)' }};">
                                {{ $used }}/{{ $limit }}
                            </span>
                        </td>
                        <td style="text-align:center;">
                            <div style="display:flex; gap:6px;
                                justify-content:center; flex-wrap:wrap;">
                                <a href="{{ route('admin.members.show', $member) }}"
                                    class="btn btn-sm btn-ghost">Lihat</a>
                                <a href="{{ route('admin.members.edit', $member) }}"
                                    class="btn btn-sm btn-warning">Edit</a>
                                <form method="POST"
                                    action="{{ route('admin.members.toggle', $member) }}">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="btn btn-sm btn-ghost">
                                        {{ $member->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                    </button>
                                </form>
                                <form method="POST"
                                    action="{{ route('admin.members.destroy', $member) }}"
                                    onsubmit="return confirm('Yakin hapus member ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                        class="btn btn-sm btn-danger">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7"
                            style="text-align:center; padding:40px;
                                color:var(--text-muted);">
                            Belum ada member.
                            <a href="{{ route('admin.members.create') }}"
                                style="color:var(--accent);">Tambah sekarang</a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Mobile cards --}}
    <div class="mobile-cards" style="display:none;">
        @forelse($members as $member)
            @php
                $used  = $member->todayGrantedCount();
                $limit = $member->todayLimit();
            @endphp
            <div style="padding:16px; border-bottom:1px solid var(--border);">
                <div style="display:flex; justify-content:space-between;
                    align-items:flex-start; margin-bottom:10px;">
                    <div>
                        <a href="{{ route('admin.members.show', $member) }}"
                            style="color:var(--accent); font-weight:700;
                                font-size:15px; text-decoration:none;">
                            {{ $member->nama }}
                        </a>
                        <p style="color:var(--text-muted); font-size:13px;
                            margin-top:2px;">
                            {{ $member->unit }} — {{ $member->kawasan }}
                        </p>
                    </div>
                    <div style="display:flex; flex-direction:column;
                        align-items:flex-end; gap:6px;">
                        <span class="badge
                            {{ $member->is_active ? 'badge-green' : 'badge-red' }}">
                            {{ $member->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                        <span style="font-size:13px; font-weight:700;
                            color:{{ $used >= $limit
                                ? 'var(--danger)' : 'var(--success)' }};">
                            {{ $used }}/{{ $limit }} akses
                        </span>
                    </div>
                </div>
                <div style="display:flex; gap:8px; flex-wrap:wrap;">
                    <a href="{{ route('admin.members.show', $member) }}"
                        class="btn btn-sm btn-ghost">Lihat</a>
                    <a href="{{ route('admin.members.edit', $member) }}"
                        class="btn btn-sm btn-warning">Edit</a>
                    <form method="POST"
                        action="{{ route('admin.members.toggle', $member) }}">
                        @csrf @method('PATCH')
                        <button type="submit" class="btn btn-sm btn-ghost">
                            {{ $member->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                        </button>
                    </form>
                    <form method="POST"
                        action="{{ route('admin.members.destroy', $member) }}"
                        onsubmit="return confirm('Yakin hapus member ini?')">
                        @csrf @method('DELETE')
                        <button type="submit"
                            class="btn btn-sm btn-danger">Hapus</button>
                    </form>
                </div>
            </div>
        @empty
            <div style="padding:40px; text-align:center; color:var(--text-muted);">
                Belum ada member.
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($members->hasPages())
        <div class="pagination-wrap">
            {{ $members->links() }}
        </div>
    @endif

</div>

<style>
    @media (max-width: 768px) {
        .desktop-table { display: none !important; }
        .mobile-cards  { display: block !important; }
    }
</style>

@endsection