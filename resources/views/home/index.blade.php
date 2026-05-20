@extends('layout.app')

@push('styles')
    @vite('resources/css/home-page.css')
@endpush

@section('navbar')
    @include('layout.navbar')
@endsection

@section('content')
<div class="home-page">

    {{-- ── Section: TCG Populer ── --}}
    <section>

        <div class="popular-header">
            <h2 class="popular-title">TCG populer di pokelu</h2>
        </div>

        @if(count($popularCards) > 0)

            <div class="popular-track" id="popularTrack">
                @foreach($popularCards as $card)
                    <a
                        class="pop-card"
                        href="{{ route('card.detail', ['id' => $card['id']]) }}"
                        title="{{ $card['name'] }}"
                    >
                        <div class="pop-thumb">

                            {{-- Gambar kartu --}}
                            @if($card['image'])
                                <img
                                    src="{{ $card['image'] }}"
                                    alt="{{ $card['name'] }}"
                                    loading="lazy"
                                >
                            @else
                                <div class="no-img">?</div>
                            @endif

                            {{-- Badge jumlah offer --}}
                            <span class="offer-badge">{{ $card['offer_count'] }} offer</span>

                        </div>

                        {{-- Nama kartu --}}
                        <span class="pop-card-name">{{ $card['name'] }}</span>
                    </a>
                @endforeach
            </div>

        @else

            {{-- Kosong — belum ada offer --}}
            <div class="empty-state">
                <span class="empty-icon">🃏</span>
                <p>Belum ada kartu populer.<br>Jadilah yang pertama buat offer!</p>
            </div>

        @endif

        {{-- ── Tombol Discovery (kanan bawah) ── --}}
        <div class="home-bottom">
            <a href="{{ route('jelajah') }}" class="btn-discovery">
                Discovery →
            </a>
        </div>

    </section>

    {{-- ── Section: Forum Populer ── --}}
    <section class="popular-forum-section">

        <div class="popular-header">
            <h2 class="popular-title">Forum populer</h2>
        </div>

        @if($popularForum)
            <div class="popular-forum-card">
                {{-- Thumbnail --}}
                <div class="pforum-thumb">
                    <img
                        src="{{ $popularForum['thumbnailUrl'] ?? asset('images/pfp.png') }}"
                        alt="{{ $popularForum['title'] }}"
                        onerror="this.src='{{ asset('images/pfp.png') }}'"
                    >
                </div>

                {{-- Konten --}}
                <div class="pforum-content">
                    <h3 class="pforum-title">{{ $popularForum['title'] }}</h3>
                    <p class="pforum-excerpt">{{ $popularForum['excerpt'] }}</p>

                    <div class="pforum-meta">
                        {{-- Tombol masuk --}}
                        <a href="{{ route('forum.show', $popularForum['id']) }}" class="btn-masuk">
                            Masuk ke diskusi
                        </a>

                        {{-- Author --}}
                        <div class="pforum-author">
                            <img
                                src="{{ $popularForum['avatarSrc'] }}"
                                alt="{{ $popularForum['displayName'] }}"
                                class="pforum-avatar"
                                onerror="this.src='{{ asset('images/avatar/pfp6.png') }}'"
                            >
                            <span>Dimulai oleh: <strong>{{'@'}}{{ $popularForum['username'] }}</strong></span>
                        </div>

                        {{-- Like/Dislike --}}
                        <div class="pforum-reactions">
                            <span class="reaction-item">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M1 21h4V9H1v12zm22-11c0-1.1-.9-2-2-2h-6.31l.95-4.57.03-.32c0-.41-.17-.79-.44-1.06L14.17 1 7.59 7.59C7.22 7.95 7 8.45 7 9v10c0 1.1.9 2 2 2h9c.83 0 1.54-.5 1.84-1.22l3.02-7.05c.09-.23.14-.47.14-.73v-2z"/></svg>
                                {{ $popularForum['like_count'] }}
                            </span>
                            <span class="reaction-item">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M15 3H6c-.83 0-1.54.5-1.84 1.22l-3.02 7.05c-.09.23-.14.47-.14.73v2c0 1.1.9 2 2 2h6.31l-.95 4.57-.03.32c0 .41.17.79.44 1.06L9.83 23l6.59-6.59c.36-.36.58-.86.58-1.41V5c0-1.1-.9-2-2-2zm4 0v12h4V3h-4z"/></svg>
                                {{ $popularForum['dislike_count'] }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

        @else
            <div class="empty-state">
                <span class="empty-icon">💬</span>
                <p>Belum ada forum populer.<br>Jadilah yang pertama membuat diskusi!</p>
            </div>
        @endif

        {{-- Tombol ke semua forum --}}
        <a href="{{ route('forum.index') }}" class="btn-forum-all">
            forum komunitas lainnya
        </a>

    </section>

    {{-- ── Section: Discovery Harian ── --}}
    <section class="daily-discovery-section">
        <div class="popular-header">
            <h2 class="popular-title">Discovery Harian</h2>
        </div>

        <div class="daily-discovery-row">
            @foreach ($discoveryCards ?? [] as $card)
                <a href="{{ route('card.detail', ['id' => $card['id']]) }}"
                   class="daily-discovery-card">
                    <div class="daily-discovery-image-wrap">
                        @if (!empty($card['image']))
                            <img src="{{ $card['image'] }}"
                                 alt="{{ $card['name'] }}"
                                 class="daily-discovery-image"
                                 draggable="false"
                                 loading="lazy">
                        @else
                            <div class="daily-discovery-placeholder">?</div>
                        @endif

                        @if (($card['offer_count'] ?? 0) > 0)
                            <span class="daily-discovery-badge">
                                {{ $card['offer_count'] }} offer
                            </span>
                        @endif
                    </div>

                    <div class="daily-discovery-name">
                        {{ $card['name'] }}
                    </div>
                </a>
            @endforeach

            <a href="{{ route('jelajah') }}"
               class="daily-discovery-card daily-discovery-more-card">
                <div class="daily-discovery-more-box">
                    <span>Lainnya</span>
                    <small>Jelajahi kartu lain</small>
                </div>
                <div class="daily-discovery-name">&nbsp;</div>
            </a>
        </div>
    </section>

</div>
@endsection

@push('scripts')
<script>
    // Drag-to-scroll horizontal track
    (function () {
        const track = document.getElementById('popularTrack');
        if (!track) return;

        let isDragging = false, startX = 0, scrollLeft = 0;

        track.addEventListener('mousedown', e => {
            isDragging  = true;
            startX      = e.pageX - track.offsetLeft;
            scrollLeft  = track.scrollLeft;
            track.style.cursor = 'grabbing';
        });

        document.addEventListener('mouseup', () => {
            isDragging = false;
            track.style.cursor = '';
        });

        track.addEventListener('mousemove', e => {
            if (!isDragging) return;
            e.preventDefault();
            const x    = e.pageX - track.offsetLeft;
            const walk = (x - startX) * 1.4;
            track.scrollLeft = scrollLeft - walk;
        });
    })();

    // Drag & Wheel horizontal scroll untuk Discovery Harian
    document.addEventListener('DOMContentLoaded', function () {
        const slider = document.querySelector('.daily-discovery-row');

        if (!slider) return;

        let isDown = false;
        let startX = 0;
        let scrollLeft = 0;
        let moved = false;

        slider.addEventListener('mousedown', function (e) {
            isDown = true;
            moved = false;
            startX = e.pageX - slider.offsetLeft;
            scrollLeft = slider.scrollLeft;
            slider.classList.add('is-dragging');
        });

        slider.addEventListener('mouseleave', function () {
            isDown = false;
            slider.classList.remove('is-dragging');
        });

        slider.addEventListener('mouseup', function () {
            isDown = false;
            setTimeout(function () {
                slider.classList.remove('is-dragging');
            }, 0);
        });

        slider.addEventListener('mousemove', function (e) {
            if (!isDown) return;

            const x = e.pageX - slider.offsetLeft;
            const walk = (x - startX) * 1.3;

            if (Math.abs(walk) > 6) {
                moved = true;
                e.preventDefault();
            }

            slider.scrollLeft = scrollLeft - walk;
        });

        slider.addEventListener('click', function (e) {
            if (moved) {
                e.preventDefault();
                e.stopPropagation();
                moved = false;
            }
        }, true);

        slider.addEventListener('wheel', function (e) {
            const canScrollHorizontally = slider.scrollWidth > slider.clientWidth;

            if (!canScrollHorizontally) return;

            const delta = Math.abs(e.deltaY) > Math.abs(e.deltaX)
                ? e.deltaY
                : e.deltaX;

            const atStart = slider.scrollLeft <= 0;
            const atEnd = Math.ceil(slider.scrollLeft + slider.clientWidth) >= slider.scrollWidth;

            const scrollingLeft = delta < 0;
            const scrollingRight = delta > 0;

            if ((scrollingLeft && atStart) || (scrollingRight && atEnd)) {
                return;
            }

            e.preventDefault();
            slider.scrollLeft += delta * 1.2;
        }, { passive: false });
    });
</script>
@endpush
