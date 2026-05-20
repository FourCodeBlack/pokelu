@extends('layout.app')

@section('navbar')
    @include('layout.navbar')
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
/* ── ROOT & RESET ── */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

.offers-page {
    min-height: 100vh;
    background: linear-gradient(135deg, #0d0820 0%, #130c2e 50%, #0a0618 100%);
    padding: 40px 24px 80px;
    font-family: 'Inter', sans-serif;
}

/* ── HEADER ── */
.offers-header {
    text-align: center;
    margin-bottom: 48px;
}

.offers-logo {
    font-family: 'Freckle Face', 'Georgia', cursive;
    font-size: clamp(1.8rem, 5vw, 2.8rem);
    background: linear-gradient(135deg, #c084fc, #818cf8, #38bdf8);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    letter-spacing: 2px;
    margin-bottom: 6px;
}

.offers-title {
    font-size: clamp(1rem, 3vw, 1.35rem);
    color: rgba(200, 170, 255, 0.65);
    font-weight: 400;
    letter-spacing: 4px;
    text-transform: uppercase;
    margin-bottom: 4px;
}

.offers-count {
    font-size: 0.82rem;
    color: rgba(155, 130, 210, 0.5);
    letter-spacing: 1px;
}

/* ── GRID ── */
.offers-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 24px;
    max-width: 880px;
    margin: 0 auto 48px;
}

@media (max-width: 620px) {
    .offers-grid { grid-template-columns: 1fr; }
}

/* ── OFFER CARD ── */
.offer-card {
    background: linear-gradient(145deg, rgba(30, 16, 60, 0.85), rgba(18, 9, 42, 0.95));
    border: 1px solid rgba(139, 92, 246, 0.2);
    border-radius: 20px;
    overflow: hidden;
    transition: transform 0.22s ease, border-color 0.22s ease, box-shadow 0.22s ease;
    position: relative;
}

.offer-card:hover {
    transform: translateY(-5px);
    border-color: rgba(139, 92, 246, 0.55);
    box-shadow: 0 16px 48px rgba(139, 92, 246, 0.22), 0 0 0 1px rgba(139, 92, 246, 0.12);
}

/* Card image area */
.offer-card-image {
    position: relative;
    background: linear-gradient(160deg, #1a0a38, #0e0624);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 24px 16px 16px;
    overflow: hidden;
    min-height: 200px;
}

.offer-card-image::before {
    content: '';
    position: absolute;
    inset: 0;
    background: radial-gradient(ellipse at 50% 0%, rgba(139, 92, 246, 0.15) 0%, transparent 70%);
}

.offer-card-image img {
    max-height: 160px;
    max-width: 120px;
    object-fit: contain;
    border-radius: 8px;
    filter: drop-shadow(0 8px 24px rgba(139, 92, 246, 0.4));
    position: relative;
    z-index: 1;
    transition: transform 0.3s ease;
}

.offer-card:hover .offer-card-image img {
    transform: scale(1.06) translateY(-4px);
}

.offer-card-image-placeholder {
    width: 100px;
    height: 140px;
    background: linear-gradient(135deg, rgba(139,92,246,0.15), rgba(99,60,180,0.08));
    border-radius: 10px;
    border: 1px dashed rgba(139,92,246,0.25);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.5rem;
    position: relative;
    z-index: 1;
}

/* Card body */
.offer-card-body {
    padding: 18px 18px 20px;
}

.offer-card-name {
    font-size: 0.95rem;
    font-weight: 700;
    color: #e9d5ff;
    letter-spacing: 0.5px;
    margin-bottom: 10px;
    line-height: 1.3;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.offer-card-meta {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
    margin-bottom: 14px;
}

.offer-price {
    font-size: 1.1rem;
    font-weight: 800;
    color: #a78bfa;
    letter-spacing: 0.5px;
}

.offer-condition {
    font-size: 0.72rem;
    font-weight: 700;
    background: rgba(139, 92, 246, 0.15);
    border: 1px solid rgba(139, 92, 246, 0.3);
    color: #c4b5fd;
    border-radius: 20px;
    padding: 3px 10px;
    text-transform: uppercase;
    letter-spacing: 0.8px;
}

/* User info */
.offer-user {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 16px;
}

.offer-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid rgba(139, 92, 246, 0.4);
    flex-shrink: 0;
}

.offer-user-info {
    flex: 1;
    min-width: 0;
}

.offer-user-name {
    font-size: 0.85rem;
    font-weight: 600;
    color: #ddd6fe;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.offer-user-handle {
    font-size: 0.73rem;
    color: rgba(167, 139, 250, 0.6);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Contact button */
.offer-contact-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    width: 100%;
    padding: 10px 16px;
    background: linear-gradient(135deg, #7c3aed, #6d28d9);
    color: #fff;
    font-size: 0.85rem;
    font-weight: 700;
    letter-spacing: 0.5px;
    border: none;
    border-radius: 10px;
    text-decoration: none;
    cursor: pointer;
    transition: transform 0.15s ease, box-shadow 0.15s ease, background 0.2s ease;
    box-shadow: 0 4px 16px rgba(124, 58, 237, 0.35);
}

.offer-contact-btn:hover {
    background: linear-gradient(135deg, #8b5cf6, #7c3aed);
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(124, 58, 237, 0.5);
    color: #fff;
    text-decoration: none;
}

.offer-contact-btn:active {
    transform: translateY(0);
}

/* ── EMPTY STATE ── */
.offers-empty {
    grid-column: 1 / -1;
    text-align: center;
    padding: 80px 20px;
    color: rgba(167, 139, 250, 0.5);
}

.offers-empty-icon {
    font-size: 3.5rem;
    margin-bottom: 16px;
    display: block;
}

.offers-empty p {
    font-size: 1rem;
    letter-spacing: 1px;
}

/* ── PAGINATION ── */
.offers-pagination {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 16px;
    max-width: 880px;
    margin: 0 auto;
}

.page-info {
    font-size: 0.85rem;
    color: rgba(167, 139, 250, 0.6);
    letter-spacing: 1px;
    min-width: 80px;
    text-align: center;
}

.page-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 24px;
    background: rgba(139, 92, 246, 0.12);
    border: 1px solid rgba(139, 92, 246, 0.3);
    color: #c4b5fd;
    font-size: 0.85rem;
    font-weight: 600;
    border-radius: 10px;
    text-decoration: none;
    letter-spacing: 0.5px;
    transition: background 0.18s, border-color 0.18s, color 0.18s, transform 0.15s;
}

.page-btn:hover {
    background: rgba(139, 92, 246, 0.25);
    border-color: rgba(139, 92, 246, 0.6);
    color: #e9d5ff;
    text-decoration: none;
    transform: translateY(-2px);
}

.page-btn.disabled {
    opacity: 0.3;
    pointer-events: none;
    cursor: default;
}

/* ── DIVIDER ── */
.offers-divider {
    width: 60px;
    height: 3px;
    background: linear-gradient(90deg, #7c3aed, #38bdf8);
    border-radius: 999px;
    margin: 10px auto 0;
}

.offer-actions {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-top: 12px;
}

.offer-contact-btn {
    flex: 1;
}

.offer-delete-btn {
    border: 1px solid rgba(255, 92, 122, 0.45);
    border-radius: 14px;
    padding: 10px 16px;
    background: linear-gradient(135deg, rgba(255, 75, 110, 0.95), rgba(190, 24, 93, 0.95));
    color: #ffffff;
    font-weight: 700;
    font-size: 14px;
    cursor: pointer;
    box-shadow: 0 10px 24px rgba(255, 75, 110, 0.18);
    transition: transform 0.15s ease, box-shadow 0.15s ease, opacity 0.15s ease;
}

.offer-delete-btn:hover {
    transform: translateY(-1px);
    opacity: 0.95;
    box-shadow: 0 14px 30px rgba(255, 75, 110, 0.25);
}

.offer-delete-btn:active {
    transform: translateY(0);
}

.admin-delete-badge {
    display: inline-flex;
    align-items: center;
    padding: 4px 8px;
    border-radius: 999px;
    background: rgba(255, 75, 110, 0.14);
    color: #ff9eb0;
    font-size: 12px;
    font-weight: 700;
    position: absolute;
    top: 12px;
    right: 12px;
    z-index: 5;
}

.alert-success {
    background: rgba(46, 204, 113, 0.15);
    border: 1px solid #2ecc71;
    color: #2ecc71;
    padding: 14px 20px;
    border-radius: 12px;
    margin: 0 auto 24px;
    max-width: 880px;
    text-align: center;
    font-size: 0.9rem;
    font-weight: 600;
}

.alert-error {
    background: rgba(255, 59, 107, 0.15);
    border: 1px solid #ff3b6b;
    color: #ff6f91;
    padding: 14px 20px;
    border-radius: 12px;
    margin: 0 auto 24px;
    max-width: 880px;
    text-align: center;
    font-size: 0.9rem;
    font-weight: 600;
}
</style>
@endpush

@section('content')
<div class="offers-page">

    @if(session('success'))
        <div class="alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert-error">
            {{ session('error') }}
        </div>
    @endif

    {{-- ── HEADER ── --}}
    <header class="offers-header">
        <div class="offers-logo">POKELU</div>
        <div class="offers-divider"></div>
        <h1 class="offers-title" style="margin-top:14px;">Penawaran</h1>
        @if($total > 0)
            <p class="offers-count">{{ $total }} penawaran tersedia</p>
        @endif
    </header>

    {{-- ── GRID ── --}}
    <div class="offers-grid">

        @forelse($offers as $offer)
            @php
                $pfpCode  = $offer['pfp'] ?? 'default';
                $avatarSrc = asset('images/avatar/' . $pfpCode . '.png');
                $fallback  = asset('images/avatar/default.png');
                $cardImage = $offer['cardImage'] ?? null;
                $handle    = $offer['handle']
                    ? (str_starts_with($offer['handle'], '@') ? $offer['handle'] : '@' . $offer['handle'])
                    : '@' . strtolower(str_replace(' ', '', $offer['displayName'] ?? 'user'));

                // Contact link
                $contactHref = '#';
                if (!empty($offer['contact'])) {
                    $c = $offer['contact'];
                    if (str_starts_with($c, 'http') || str_starts_with($c, 'wa.me') || str_starts_with($c, '+')) {
                        $contactHref = str_starts_with($c, 'http') ? $c : 'https://wa.me/' . preg_replace('/\D/', '', $c);
                    } else {
                        $contactHref = route('card.detail', ['id' => $offer['cardId']]);
                    }
                } else {
                    $contactHref = route('card.detail', ['id' => $offer['cardId']]);
                }
            @endphp

            <article class="offer-card">
                @php
                    $isAdmin = ($currentUser['role'] ?? null) === 'admin';
                    $isOwner = ($offer['uid'] ?? null) === ($currentUser['uid'] ?? null);
                    $canDelete = $isAdmin || $isOwner;
                @endphp

                @if ($isAdmin && !$isOwner)
                    <span class="admin-delete-badge">Admin</span>
                @endif

                {{-- Card image --}}
                <div class="offer-card-image">
                    @if($cardImage)
                        <img src="{{ $cardImage }}"
                             alt="{{ $offer['cardName'] ?? 'Kartu' }}"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="offer-card-image-placeholder" style="display:none;">🃏</div>
                    @else
                        <div class="offer-card-image-placeholder">🃏</div>
                    @endif
                </div>

                {{-- Card body --}}
                <div class="offer-card-body">
                    <h2 class="offer-card-name">{{ $offer['cardName'] ?? 'Kartu Pokémon' }}</h2>

                    <div class="offer-card-meta">
                        @if(!empty($offer['price']))
                            <span class="offer-price">${{ number_format($offer['price'], 0) }}</span>
                        @endif
                        @if(!empty($offer['condition']))
                            <span class="offer-condition">{{ $offer['condition'] }}</span>
                        @endif
                    </div>

                    {{-- User info --}}
                    <div class="offer-user">
                        <img src="{{ $avatarSrc }}"
                             onerror="this.src='{{ $fallback }}'"
                             alt="{{ $offer['displayName'] ?? 'User' }}"
                             class="offer-avatar">
                        <div class="offer-user-info">
                            <div class="offer-user-name">{{ $offer['displayName'] ?? 'User' }}</div>
                            <div class="offer-user-handle">{{ $handle }}</div>
                        </div>
                    </div>

                    {{-- Contact & Delete action --}}
                    <div class="offer-actions">
                        <a href="{{ $contactHref }}"
                           class="offer-contact-btn"
                           @if(!empty($offer['contact']) && str_starts_with($contactHref, 'http')) target="_blank" rel="noopener" @endif>
                            <i class="fa-solid fa-message"></i>
                            Contact
                        </a>

                        @if ($canDelete)
                            {{-- DEBUG cardId: {{ $offer['cardId'] ?? 'NULL' }} --}}
                            {{-- DEBUG offerId: {{ $offer['offerId'] ?? 'NULL' }} --}}
                            <form method="POST"
                                  action="{{ route('offers.destroy', ['cardId' => $offer['cardId'], 'offerId' => $offer['offerId']]) }}"
                                  onsubmit="return confirm('Yakin ingin menghapus penawaran ini?')"
                                  class="offer-delete-form"
                                  style="display:inline-block; margin: 0;">
                                @csrf
                                @method('DELETE')

                                <button type="submit" class="offer-delete-btn">
                                    @if ($isAdmin && !$isOwner)
                                        Hapus Admin
                                    @else
                                        Hapus
                                    @endif
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

            </article>

        @empty
            <div class="offers-empty">
                <span class="offers-empty-icon">📭</span>
                <p>Belum ada penawaran.</p>
            </div>
        @endforelse

    </div>

    {{-- ── PAGINATION ── --}}
    @if($total > 0)
        <nav class="offers-pagination" aria-label="Pagination">
            <a href="{{ $page > 1 ? request()->fullUrlWithQuery(['page' => $page - 1]) : '#' }}"
               class="page-btn {{ $page <= 1 ? 'disabled' : '' }}">
                <i class="fa-solid fa-chevron-left"></i> Prev
            </a>

            <span class="page-info">{{ $page }} / {{ $lastPage }}</span>

            <a href="{{ $page < $lastPage ? request()->fullUrlWithQuery(['page' => $page + 1]) : '#' }}"
               class="page-btn {{ $page >= $lastPage ? 'disabled' : '' }}">
                Next <i class="fa-solid fa-chevron-right"></i>
            </a>
        </nav>
    @endif

</div>
@endsection
