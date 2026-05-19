@extends('layouts.app')

@section('title', 'Buat Utasan Baru')

@push('styles')
<style>
    .form-card {
        max-width: 680px;
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 32px;
    }
    .form-group { margin-bottom: 20px; }
    .form-label {
        display: block;
        font-weight: 700;
        font-size: .9rem;
        margin-bottom: 6px;
        color: var(--text-main);
    }
    .form-control {
        width: 100%;
        background: var(--bg-section);
        border: 1px solid var(--border);
        border-radius: 10px;
        color: var(--text-main);
        padding: 10px 14px;
        font-family: var(--font-body);
        font-size: .9rem;
        outline: none;
        transition: border-color .2s;
    }
    .form-control:focus { border-color: var(--accent-purple); }
    select.form-control option { background: var(--bg-section); }
    .form-error { color: var(--accent-pink); font-size: .8rem; margin-top: 4px; }
</style>
@endpush

@section('content')
<div style="margin-bottom:20px;">
    <a href="{{ route('threads.index') }}" style="color:var(--text-muted);text-decoration:none;font-size:.88rem;">← Kembali</a>
</div>

<div class="form-card">
    <h2 class="section-title" style="margin-bottom:24px;">Buat Utasan Baru</h2>

    <form method="POST" action="{{ route('threads.store') }}">
        @csrf

        <div class="form-group">
            <label class="form-label">Kartu (dari Wishlist)</label>
            <select name="card_id" class="form-control" required>
                <option value="">-- Pilih Kartu --</option>
                @foreach($wishlist as $cardId => $card)
                    <option value="{{ $cardId }}" {{ old('card_id') == $cardId ? 'selected' : '' }}>
                        {{ $card['name'] ?? $cardId }}
                    </option>
                @endforeach
            </select>
            @error('card_id')<p class="form-error">{{ $message }}</p>@enderror
            <p style="color:var(--text-muted);font-size:.78rem;margin-top:4px;">
                Hanya kartu dari wishlist kamu yang muncul. <a href="{{ route('cards.index') }}" style="color:var(--accent-cyan);">Tambah ke wishlist</a> dulu.
            </p>
        </div>

        <div class="form-group">
            <label class="form-label">Kondisi Kartu</label>
            <select name="condition" class="form-control" required>
                <option value="">-- Pilih Kondisi --</option>
                <option value="Mint" {{ old('condition') == 'Mint' ? 'selected' : '' }}>Mint</option>
                <option value="Near Mint" {{ old('condition') == 'Near Mint' ? 'selected' : '' }}>Near Mint</option>
                <option value="Good" {{ old('condition') == 'Good' ? 'selected' : '' }}>Good</option>
                <option value="Played" {{ old('condition') == 'Played' ? 'selected' : '' }}>Played</option>
                <option value="Damaged" {{ old('condition') == 'Damaged' ? 'selected' : '' }}>Damaged</option>
            </select>
            @error('condition')<p class="form-error">{{ $message }}</p>@enderror
        </div>

        <div class="form-group">
            <label class="form-label">Harga (Rp) <span style="color:var(--text-muted);font-weight:400;">opsional</span></label>
            <input type="number" name="price" class="form-control"
                   value="{{ old('price') }}" placeholder="0" min="0">
            @error('price')<p class="form-error">{{ $message }}</p>@enderror
        </div>

        <div class="form-group">
            <label class="form-label">Deskripsi</label>
            <textarea name="desc" class="form-control" rows="5"
                      placeholder="Jelaskan kondisi, tawaran, atau diskusi tentang kartu ini...">{{ old('desc') }}</textarea>
            @error('desc')<p class="form-error">{{ $message }}</p>@enderror
        </div>

        <div style="display:flex;gap:12px;">
            <button type="submit" class="btn-pink">Buat Utasan</button>
            <a href="{{ route('threads.index') }}" class="btn-outline">Batal</a>
        </div>
    </form>
</div>
@endsection
