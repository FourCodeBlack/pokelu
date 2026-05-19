@extends('layouts.app')

@section('title', 'Jelajahi Kartu')

@push('styles')
<style>
    .search-bar {
        display: flex;
        gap: 10px;
        margin-bottom: 28px;
    }
    .search-input {
        flex: 1;
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: 12px;
        color: var(--text-main);
        padding: 10px 18px;
        font-family: var(--font-body);
        font-size: .95rem;
        outline: none;
        transition: border-color .2s;
    }
    .search-input:focus { border-color: var(--accent-purple); }
    .sets-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 18px;
    }
    .set-card {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 20px;
        text-decoration: none;
        color: inherit;
        transition: all .2s;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 12px;
        text-align: center;
    }
    .set-card:hover {
        border-color: var(--accent-purple);
        box-shadow: 0 0 20px rgba(155,89,182,.25);
        transform: translateY(-4px);
    }
    .set-logo {
        height: 60px;
        object-fit: contain;
        filter: drop-shadow(0 2px 8px rgba(155,89,182,.4));
    }
    .set-name {
        font-weight: 800;
        font-size: .95rem;
        color: var(--text-main);
    }
    .set-meta { color: var(--text-muted); font-size: .8rem; }
</style>
@endpush

@section('content')
<h1 class="section-title">Jelajahi Kartu TCG</h1>

<form method="GET" action="{{ route('cards.index') }}" class="search-bar">
    <input type="text" name="q" class="search-input"
           value="{{ $search ?? '' }}"
           placeholder="Cari nama set atau seri...">
    <button type="submit" class="btn-pink">Cari</button>
    @if($search)
        <a href="{{ route('cards.index') }}" class="btn-outline">Reset</a>
    @endif
</form>

<div class="sets-grid">
    @forelse($sets as $set)
        <a href="{{ route('cards.show', $set['id'].'-001') }}" class="set-card">
            @if($set['logo'])
                <img src="{{ $set['logo'] }}" alt="{{ $set['name'] }}" class="set-logo"
                     onerror="this.style.display='none'">
            @else
                <div style="font-size:2.5rem;">🃏</div>
            @endif
            <div class="set-name">{{ $set['name'] }}</div>
            <div class="set-meta">
                {{ $set['series'] }}<br>
                {{ $set['total'] }} kartu · {{ $set['releaseDate'] }}
            </div>
        </a>
    @empty
        <div style="grid-column:1/-1;text-align:center;color:var(--text-muted);padding:60px;">
            Tidak ada set ditemukan.
        </div>
    @endforelse
</div>
@endsection
