@extends('layouts.admin')

@section('content')

<div class="page-header">
    <div>
        <a href="{{ route('admin.members.index') }}"
            style="color:var(--text-muted); text-decoration:none; font-size:13px;">
            ← Kembali ke Members
        </a>
        <h1 class="page-title" style="margin-top:4px;">Tambah Member Baru</h1>
    </div>
</div>

<div class="card" style="max-width:560px;">
    <form method="POST" action="{{ route('admin.members.store') }}">
        @csrf

        <div class="form-group">
            <label>Nama Pemilik <span style="color:var(--danger);">*</span></label>
            <input type="text" name="nama" value="{{ old('nama') }}"
                placeholder="Contoh: HALBERG">
            @error('nama')
                <p class="form-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <label>No KTP <span style="color:var(--danger);">*</span></label>
            <input type="text" name="no_ktp" value="{{ old('no_ktp') }}"
                placeholder="Contoh: 123456789">
            @error('no_ktp')
                <p class="form-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <label>No Telepon <span style="color:var(--danger);">*</span></label>
            <input type="text" name="no_telp" value="{{ old('no_telp') }}"
                placeholder="Contoh: 08123456789">
            @error('no_telp')
                <p class="form-error">{{ $message }}</p>
            @enderror
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
            <div class="form-group">
                <label>Unit <span style="color:var(--danger);">*</span></label>
                <input type="text" name="unit" value="{{ old('unit') }}"
                    placeholder="Contoh: A-8">
                @error('unit')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group">
                <label>
                    Accluster
                    <span style="color:var(--text-muted); font-weight:400;">
                        (opsional)
                    </span>
                </label>
                <input type="text" name="cluster" value="{{ old('cluster') }}"
                    placeholder="Contoh: TRILIAN">
                <p class="form-hint">Kosongkan jika tidak ada cluster.</p>
                @error('cluster')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="form-group">
            <label>Kawasan <span style="color:var(--danger);">*</span></label>
            <select name="kawasan">
                <option value="">-- Pilih Kawasan --</option>
                <option value="Diamond Palace"
                    {{ old('kawasan') === 'Diamond Palace' ? 'selected' : '' }}>
                    Diamond Palace
                </option>
                <option value="Diamond Pavilion"
                    {{ old('kawasan') === 'Diamond Pavilion' ? 'selected' : '' }}>
                    Diamond Pavilion
                </option>
            </select>
            @error('kawasan')
                <p class="form-error">{{ $message }}</p>
            @enderror
        </div>

        <div style="background:rgba(56,189,248,0.08); border:1px solid rgba(56,189,248,0.2);
            border-radius:8px; padding:12px 16px; margin-bottom:20px;">
            <p style="color:var(--accent); font-size:13px;">
                🔑 QR code akan digenerate otomatis setelah member disimpan.
            </p>
        </div>

        <div style="display:flex; gap:10px;">
            <button type="submit" class="btn btn-primary">Simpan Member</button>
            <a href="{{ route('admin.members.index') }}" class="btn btn-ghost">Batal</a>
        </div>

    </form>
</div>

@endsection