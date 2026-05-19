@extends('layouts.admin')

@section('content')

<div class="page-header">
    <div>
        <a href="{{ route('admin.members.show', $member) }}"
            style="color:var(--text-muted); text-decoration:none; font-size:13px;">
            ← Kembali ke Detail Member
        </a>
        <h1 class="page-title" style="margin-top:4px;">Edit Member</h1>
    </div>
</div>

<div class="card" style="max-width:560px;">
    <form method="POST" action="{{ route('admin.members.update', $member) }}">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label>Nama Pemilik <span style="color:var(--danger);">*</span></label>
            <input type="text" name="nama"
                value="{{ old('nama', $member->nama) }}"
                placeholder="Contoh: HALBERG">
            @error('nama')
                <p class="form-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <label>No KTP <span style="color:var(--danger);">*</span></label>
            <input type="text" name="no_ktp"
                value="{{ old('no_ktp', $member->no_ktp) }}"
                placeholder="Contoh: 123456789">
            @error('no_ktp')
                <p class="form-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <label>No Telepon <span style="color:var(--danger);">*</span></label>
            <input type="text" name="no_telp"
                value="{{ old('no_telp', $member->no_telp) }}"
                placeholder="Contoh: 08123456789">
            @error('no_telp')
                <p class="form-error">{{ $message }}</p>
            @enderror
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
            <div class="form-group">
                <label>Unit <span style="color:var(--danger);">*</span></label>
                <input type="text" name="unit"
                    value="{{ old('unit', $member->unit) }}"
                    placeholder="Contoh: A-8">
                @error('unit')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group">
                <label>Kawasan <span style="color:var(--danger);">*</span></label>
                <input type="text" name="kawasan"
                    value="{{ old('kawasan', $member->kawasan) }}"
                    placeholder="Contoh: PALACE">
                @error('kawasan')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="form-group">
            <label>Batas Akses Harian (Default)</label>
            <input type="number" name="daily_limit" min="1" max="100"
                value="{{ old('daily_limit', $member->daily_limit) }}">
            <p class="form-hint">
                Ini adalah batas permanen per hari. Untuk override hari ini saja,
                gunakan fitur "Set Limit Hari Ini" di halaman detail member.
            </p>
            @error('daily_limit')
                <p class="form-error">{{ $message }}</p>
            @enderror
        </div>

        {{-- Is Active --}}
        <div class="form-group">
            <label>Status Member</label>
            <div style="display:flex; align-items:center; gap:10px;
                background:var(--navy); border:1px solid var(--border);
                border-radius:8px; padding:12px 14px;">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" id="is_active"
                    {{ old('is_active', $member->is_active) ? 'checked' : '' }}
                    style="width:18px; height:18px; cursor:pointer; accent-color:var(--accent2);">
                <label for="is_active"
                    style="color:var(--text); font-size:14px;
                        font-weight:500; cursor:pointer; margin:0;">
                    Member aktif — boleh masuk kolam renang
                </label>
            </div>
        </div>

        {{-- Member info strip --}}
        <div style="background:var(--navy); border:1px solid var(--border);
            border-radius:8px; padding:12px 16px; margin-bottom:20px;">
            <p style="color:var(--text-muted); font-size:12px; margin-bottom:4px;">
                Member ID
            </p>
            <p style="color:var(--text); font-size:13px; font-family:monospace;">
                #{{ $member->id }} — Terdaftar {{ $member->created_at->format('d M Y') }}
            </p>
        </div>

        <div style="display:flex; gap:10px;">
            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            <a href="{{ route('admin.members.show', $member) }}" class="btn btn-ghost">
                Batal
            </a>
        </div>

    </form>
</div>

@endsection