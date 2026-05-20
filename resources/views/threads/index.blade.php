@extends('layouts.app')

@section('title', 'Utasan Komunitas')

@push('styles')
<style>
    .page-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 28px;
    }
    .thread-grid {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }
    .thread-card {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 20px;
        display: flex;
        gap: 20px;
        align-items: flex-start;
        transition: border-color .2s, box-shadow .2s;
    }
    .thread-card:hover {
        border-color: var(--accent-purple);
        box-shadow: 0 0 24px rgba(155,89,182,.2);
    }
    .thread-thumb {
        flex: 0 0 120px;
        height: 120px;
        border-radius: 10px;
        background: var(--bg-section);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.5rem;
        overflow: hidden;
    }
    .thread-thumb img { width:100%; height:100%; object-fit:cover; }
    .thread-body { flex: 1; }
    .thread-title {
        font-family: var(--font-title);
        font-size: 1.4rem;
        letter-spacing: 1px;
        color: var(--text-main);
        text-decoration: none;
        display: block;
        margin-bottom: 6px;
    }
    .thread-title:hover { color: var(--accent-cyan); }
    .thread-desc {
        color: var(--text-muted);
        font-size: .88rem;
        line-height: 1.6;
        margin-bottom: 12px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .thread-meta {
        display: flex;
        align-items: center;
        gap: 14px;
        font-size: .8rem;
        color: var(--text-muted);
        flex-wrap: wrap;
    }
    .badge {
        background: rgba(155,89,182,.2);
        color: var(--accent-purple);
        border: 1px solid var(--accent-purple);
        border-radius: 20px;
        padding: 2px 10px;
        font-size: .75rem;
        font-weight: 700;
    }
    .empty-state {
        text-align: center;
        padding: 80px 20px;
        color: var(--text-muted);
    }
    .empty-state .icon { font-size: 4rem; margin-bottom: 16px; }
</style>
@endpush

@section('content')
<div class="page-header">
    <h1 class="section-title font text-light" style="margin:0;">Utasan Komunitas</h1>
    @if(session('firebase_uid'))
        <a href="{{ route('threads.create') }}" class="btn-pink">+</a>
    @endif
</div>

<div class="thread-grid">
    @forelse($threads as $thread)
        <div class="thread-card">
            <div class="thread-thumb">
                @if(!empty($thread['image']))
                    <img src="{{ $thread['image'] }}" alt="">
                @else
                    🃏
                @endif
            </div>
            <div class="thread-body">
                <a href="{{ route('threads.show', $thread['id']) }}" class="thread-title">
                    {{ $thread['title'] }}
                </a>
                <p class="thread-desc">{{ $thread['description'] }}</p>
                <div class="thread-meta">
                    <span><i class="fas fa-user" style="margin-right:4px;"></i>{{ '@'.$thread['authorName'] }}</span>
                    @if($thread['condition'])
                        <span class="badge">{{ $thread['condition'] }}</span>
                    @endif
                    @if($thread['price'])
                        <span style="color:var(--accent-cyan);font-weight:700;">
                            Rp {{ number_format($thread['price'], 0, ',', '.') }}
                        </span>
                    @endif
                    <span><i class="fas fa-reply" style="margin-right:4px;"></i>{{ $thread['votes'] }} balasan</span>
                    @if($thread['createdAt'])
                        <span>{{ \Carbon\Carbon::createFromTimestampMs($thread['createdAt'])->diffForHumans() }}</span>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div class="empty-state">
            <div class="icon">📭</div>
            <p>Belum ada utasan komunitas.</p>
            @if(session('firebase_uid'))
                <a href="{{ route('threads.create') }}" class="btn-pink" style="margin-top:16px;">Buat Utasan Pertama</a>
            @endif
        </div>
    @endforelse
</div>
@endsection
