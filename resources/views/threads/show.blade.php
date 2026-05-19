@extends('layouts.app')

@section('title', $thread['title'])

@push('styles')
<style>
    .thread-detail-card {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 28px;
        margin-bottom: 28px;
    }
    .thread-detail-title {
        font-family: var(--font-title);
        font-size: 2rem;
        letter-spacing: 1.5px;
        margin-bottom: 12px;
    }
    .thread-detail-meta {
        display: flex;
        align-items: center;
        gap: 12px;
        color: var(--text-muted);
        font-size: .85rem;
        margin-bottom: 18px;
        flex-wrap: wrap;
    }
    .thread-detail-desc {
        color: var(--text-muted);
        line-height: 1.8;
        font-size: .95rem;
        border-left: 3px solid var(--accent-purple);
        padding-left: 16px;
    }
    .thread-info-row {
        display: flex;
        gap: 16px;
        margin-top: 18px;
        flex-wrap: wrap;
    }
    .info-pill {
        background: var(--bg-section);
        border: 1px solid var(--border);
        border-radius: 10px;
        padding: 8px 16px;
        font-size: .85rem;
    }
    .info-pill strong { color: var(--accent-cyan); }

    /* Replies */
    .replies-section h3 {
        font-family: var(--font-title);
        font-size: 1.3rem;
        margin-bottom: 16px;
        color: var(--text-main);
    }
    .reply-card {
        background: var(--bg-section);
        border: 1px solid var(--border);
        border-radius: 10px;
        padding: 14px 16px;
        margin-bottom: 12px;
        display: flex;
        gap: 12px;
    }
    .reply-avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        border: 2px solid var(--accent-purple);
        flex-shrink: 0;
        object-fit: cover;
    }
    .reply-body { flex: 1; }
    .reply-header {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 4px;
    }
    .reply-name { font-weight: 800; font-size: .88rem; }
    .reply-time { color: var(--text-muted); font-size: .75rem; }
    .reply-text { color: var(--text-muted); font-size: .9rem; line-height: 1.6; }

    /* Reply form */
    .reply-form-wrap {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 20px;
        margin-top: 24px;
    }
    .reply-form-wrap h4 {
        font-family: var(--font-title);
        font-size: 1.1rem;
        margin-bottom: 12px;
    }
    textarea.form-control {
        width: 100%;
        background: var(--bg-section);
        border: 1px solid var(--border);
        border-radius: 10px;
        color: var(--text-main);
        padding: 12px 16px;
        font-family: var(--font-body);
        font-size: .9rem;
        resize: vertical;
        min-height: 100px;
        outline: none;
        transition: border-color .2s;
    }
    textarea.form-control:focus { border-color: var(--accent-purple); }
</style>
@endpush

@section('content')
<div style="margin-bottom:16px;">
    <a href="{{ route('threads.index') }}" style="color:var(--text-muted);text-decoration:none;font-size:.88rem;">
        ← Kembali ke Utasan
    </a>
</div>

{{-- Thread Detail --}}
<div class="thread-detail-card">
    <h1 class="thread-detail-title">{{ $thread['title'] }}</h1>

    <div class="thread-detail-meta">
        <img src="{{ $thread['authorPhoto'] ?? 'https://ui-avatars.com/api/?name='.urlencode($thread['authorName']).'&background=9b59b6&color=fff&size=36' }}"
             style="width:28px;height:28px;border-radius:50%;border:2px solid var(--accent-purple);">
        <span>{{ '@'.$thread['authorName'] }}</span>
        @if($thread['createdAt'])
            <span>· {{ \Carbon\Carbon::createFromTimestampMs($thread['createdAt'])->isoFormat('D MMM YYYY') }}</span>
        @endif
    </div>

    <p class="thread-detail-desc">{{ $thread['description'] ?: 'Tidak ada deskripsi.' }}</p>

    <div class="thread-info-row">
        @if($thread['condition'])
        <div class="info-pill">Kondisi: <strong>{{ $thread['condition'] }}</strong></div>
        @endif
        @if($thread['price'])
        <div class="info-pill">Harga: <strong>Rp {{ number_format($thread['price'],0,',','.') }}</strong></div>
        @endif
        <div class="info-pill">Kartu: <strong>{{ $cardId }}</strong></div>
    </div>

    {{-- Vote row --}}
    <div style="display:flex;align-items:center;gap:10px;margin-top:20px;">
        <form method="POST" action="{{ route('threads.vote', $thread['id']) }}" style="display:inline;">
            @csrf
            <input type="hidden" name="vote" value="up">
            <button type="submit" class="vote-btn" style="background:rgba(233,30,140,.1);border:1px solid var(--accent-pink);border-radius:8px;color:var(--accent-pink);padding:6px 14px;font-weight:700;cursor:pointer;">
                👍 Suka
            </button>
        </form>
        <span style="font-size:1.1rem;font-weight:800;">{{ $thread['votes'] }}</span>
        <form method="POST" action="{{ route('threads.vote', $thread['id']) }}" style="display:inline;">
            @csrf
            <input type="hidden" name="vote" value="down">
            <button type="submit" class="vote-btn" style="background:var(--bg-section);border:1px solid var(--border);border-radius:8px;color:var(--text-muted);padding:6px 14px;font-weight:700;cursor:pointer;">
                👎
            </button>
        </form>
    </div>
</div>

{{-- Replies --}}
<div class="replies-section">
    <h3>{{ count($replies) }} Balasan</h3>

    @forelse($replies as $replyId => $reply)
        <div class="reply-card">
            <img class="reply-avatar"
                 src="{{ $reply['photoURL'] ?? 'https://ui-avatars.com/api/?name='.urlencode($reply['displayName'] ?? 'U').'&background=231540&color=9b59b6&size=36' }}"
                 alt="{{ $reply['displayName'] ?? 'User' }}">
            <div class="reply-body">
                <div class="reply-header">
                    <span class="reply-name">{{ $reply['displayName'] ?? 'Anonymous' }}</span>
                    @if(!empty($reply['createdAt']))
                        <span class="reply-time">{{ \Carbon\Carbon::createFromTimestampMs($reply['createdAt'])->diffForHumans() }}</span>
                    @endif
                </div>
                <p class="reply-text">{{ $reply['text'] }}</p>
            </div>
        </div>
    @empty
        <p style="color:var(--text-muted);text-align:center;padding:24px 0;">Belum ada balasan. Jadilah yang pertama!</p>
    @endforelse
</div>

{{-- Reply Form --}}
@if(session('firebase_uid'))
    <div class="reply-form-wrap">
        <h4>Tulis Balasan</h4>
        <form method="POST" action="{{ route('threads.reply', $thread['id']) }}">
            @csrf
            <textarea name="text" class="form-control" placeholder="Tulis balasanmu di sini...">{{ old('text') }}</textarea>
            @error('text')
                <p style="color:var(--accent-pink);font-size:.8rem;margin-top:4px;">{{ $message }}</p>
            @enderror
            <div style="margin-top:12px;">
                <button type="submit" class="btn-pink">Kirim Balasan</button>
            </div>
        </form>
    </div>
@else
    <div style="text-align:center;padding:24px;background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius);margin-top:20px;">
        <a href="{{ route('login') }}" class="btn-pink">Masuk untuk membalas</a>
    </div>
@endif
@endsection
