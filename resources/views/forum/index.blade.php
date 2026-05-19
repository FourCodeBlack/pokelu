@extends('layout.app')
@section('navbar')
    @include('layout.navbar')
@endsection
@push('styles')
<link rel="stylesheet" href="{{ asset('css/forum.css?v=' . time()) }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
@endpush
@section('content')
<div class="forum-header-section">
    <h1>
        <img src="{{ asset('images/pfp.png') }}" alt="Pokeball" class="pokeball-icon">
        Community Forum
    </h1>
</div>

<div class="forum-container">
    @if(session('success'))
        <div style="background: rgba(0,255,0,0.1); border: 1px solid lime; padding: 10px; border-radius: 8px; margin-bottom: 20px; color: lime;">
            {{ session('success') }}
        </div>
    @endif

    @forelse($threads as $thread)
        <div class="thread-item">
            <div class="thread-thumbnail">
                <img src="{{ $thread['thumbnailUrl'] ?? asset('images/logo/default-thumbnail.png') }}" alt="Thumbnail">
            </div>
            <div class="thread-content">
                <h2 class="thread-title">{{ $thread['title'] }}</h2>
                <div class="thread-desc">
                    {{ $thread['excerpt'] ?? Str::limit(strip_tags($thread['body']), 100) }}
                </div>
                
                <div class="thread-footer">
                    <div class="thread-author">
                        @php
                            $pfp = $thread['photoURL'] ?? 'pfp6';
                            $avatarSrc = str_starts_with($pfp, 'http') ? $pfp : asset('images/avatar/' . $pfp . '.png');
                        @endphp
                        <img src="{{ $avatarSrc }}" alt="Avatar" class="author-avatar" onerror="this.src='{{ asset('images/avatar/pfp6.png') }}'">
                        <span class="author-name">Dimulai oleh: {{ '@' . ($thread['username'] ?? 'user') }}</span>
                    </div>

                    <div class="thread-actions">
                        <div class="like-dislike">
                            <form action="{{ route('forum.like', $thread['id']) }}" method="POST">
                                @csrf
                                <button type="submit" title="Like"><i class="fa-solid fa-thumbs-up"></i> {{ $thread['likes_count'] }}</button>
                            </form>
                            <form action="{{ route('forum.dislike', $thread['id']) }}" method="POST">
                                @csrf
                                <button type="submit" title="Dislike"><i class="fa-solid fa-thumbs-down"></i> {{ $thread['dislikes_count'] }}</button>
                            </form>
                        </div>
                        <a href="{{ route('forum.show', $thread['id']) }}" class="btn-enter">Masuk ke diskusi</a>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div style="text-align: center; padding: 50px; background: rgba(255,255,255,0.05); border-radius: 16px;">
            <h3>Belum ada diskusi komunitas.</h3>
            <p style="color: rgba(255,255,255,0.6);">Jadilah yang pertama membuat diskusi!</p>
        </div>
    @endforelse
</div>

<a href="{{ route('forum.create') }}" class="fab" title="Buat Thread Baru">
    <i class="fa-solid fa-plus"></i>
</a>
@endsection
