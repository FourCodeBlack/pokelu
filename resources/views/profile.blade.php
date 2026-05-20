@extends('layout.app')
@push('styles')
    @vite('resources/css/profile.css')
    <style>
        .profile-offers-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 18px;
            padding-top: 15px;
        }

        .profile-offer-card {
            border-radius: 22px;
            background: linear-gradient(180deg, rgba(42, 20, 72, 0.9), rgba(27, 12, 47, 0.95));
            border: 1px solid rgba(168, 85, 247, 0.22);
            overflow: hidden;
            transition: transform 0.15s ease, border-color 0.15s ease, box-shadow 0.15s ease;
        }

        .profile-offer-card:hover {
            transform: translateY(-2px);
            border-color: rgba(216, 180, 254, 0.5);
            box-shadow: 0 16px 36px rgba(124, 58, 237, 0.2);
        }

        .profile-offer-image-wrap {
            height: 150px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: radial-gradient(circle at center, rgba(124, 58, 237, 0.28), rgba(13, 6, 28, 0.1));
        }

        .profile-offer-image {
            max-height: 130px;
            max-width: 100px;
            object-fit: contain;
            border-radius: 8px;
        }

        .profile-offer-placeholder {
            color: #8b7bb0;
            font-weight: 700;
        }

        .profile-offer-body {
            padding: 16px;
        }

        .profile-offer-body h3 {
            margin: 0 0 8px;
            color: #ffffff;
            font-size: 17px;
            font-weight: 900;
        }

        .profile-offer-meta {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 14px;
        }

        .profile-offer-meta strong {
            color: #a78bfa;
            font-size: 18px;
            font-weight: 900;
        }

        .profile-offer-meta span {
            padding: 4px 10px;
            border-radius: 999px;
            background: rgba(124, 58, 237, 0.35);
            color: #ddd6fe;
            font-size: 11px;
            font-weight: 900;
            text-transform: uppercase;
        }

        .profile-offer-actions {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .profile-offer-detail-btn,
        .profile-offer-delete-btn {
            height: 38px;
            border: none;
            border-radius: 12px;
            padding: 0 14px;
            color: white;
            font-weight: 900;
            font-size: 13px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .profile-offer-detail-btn {
            background: linear-gradient(135deg, #8b5cf6, #7c3aed);
        }

        .profile-offer-delete-btn {
            background: linear-gradient(135deg, #ff4b6e, #e11d48);
        }

        @media (max-width: 768px) {
            .profile-active-offers {
                padding: 0 18px 18px;
            }
        }
    </style>
@endpush
@section('navbar')
    @include('layout.navbar')
@endsection
@section('content')


    @php
        $profileUid = $profileUser['uid'] ?? session('user.uid');
        if (empty($profileUser) && $profileUid) {
            $profileUser = \App\Models\FirebaseHelper::baca("users/{$profileUid}");
        }
        $pfpName = $profileUser['pfp'] ?? 'default';
        $pfp = asset('images/avatar/' . $pfpName . '.png');
        $username = $profileUser['username'] ?? ($profileUser['name'] ?? 'User');
        $email = $profileUser['email'] ?? '';
    @endphp

    <header class="header">
        <div class="header-inner">
            <div class="avatar-wrap">
                <div class="avatar">
                    <img src="{{ $pfp }}" alt="">
                </div>
                @if(($currentUser['uid'] ?? session('user.uid')) === $profileUid)
                    <a href="{{ route('profile.edit') }}">
                        <img src="{{ asset('images/pen.png') }}" alt="" class="edit">
                    </a>
                @endif
            </div>
            <div class="user-info">
                <h1 class="user-name">{{ $username }}</h1>
                <p class="user-email">{{ $email }}</p>
            </div>

        </div>
    </header>

    <body>
        <section class="liked-tcg">
            <h1 class="font-family fs-3 text-light">Wishlist TCG</h1>
            @php
                use App\Models\FirebaseHelper;
                $path = "users/$profileUid/wishlist";

                $hasWislist = FirebaseHelper::adakah($path);
                $dataWishlist = FirebaseHelper::baca($path);
            @endphp

            @if ($hasWislist)
                <div class="overflow-x-auto" style="scrollbar-width: none;">
                    <div style="display: flex; gap: 12px; padding: 0.5rem 0.25rem 1rem; scroll-snap-type: x mandatory;">
                        @foreach ($dataWishlist as $id => $item)
                            <a href="{{ route('card.detail', ['id' => $id]) }}" class="pe-auto text-decoration-none">
                                <div style="flex: 0 0 auto; width: 90px; scroll-snap-align: start;">
                                    <div style="border-radius: 8px; overflow: hidden; border: 0.5px solid #e5e5e5; aspect-ratio: 2/3; transition: transform 0.2s ease;"
                                        onmouseover="this.style.transform='translateY(-4px) scale(1.04)'"
                                        onmouseout="this.style.transform='none'">
                                        <img src="{{ $item['image'] }}"
                                            style="width: 100%; height: 100%; object-fit: cover; display: block;" loading="lazy">
                                    </div>
                                    <p class="text-light text-center">{{ $item['name'] }}</p>

                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>

            @else
                <p class="fs-6 text-light">Doesn't have a wishlist</p>
            @endif
        </section>

        {{-- ── SECTION PENAWARAN AKTIF ── --}}
        <section class="liked-tcg" style="padding-top: 20px;">
            <h1 class="font-family fs-3 text-light">
                Penawaran Aktif
                <span class="fs-6 text-light opacity-50 font-normal" style="font-family: 'Fredoka', cursive; font-weight: normal; margin-left: 8px;">
                    ({{ count($activeOffers ?? []) }} aktif)
                </span>
            </h1>

            @if (empty($activeOffers))
                <p class="fs-6 text-light">Belum ada penawaran aktif</p>
            @else
                <div class="profile-offers-grid">
                    @foreach ($activeOffers as $offer)
                        @php
                            $isAdmin = ($currentUser['role'] ?? null) === 'admin';
                            $isOwner = ($offer['uid'] ?? null) === ($currentUser['uid'] ?? null);
                            $canDelete = $isAdmin || $isOwner;
                        @endphp

                        <div class="profile-offer-card">
                            <div class="profile-offer-image-wrap">
                                @if (!empty($offer['cardImage']))
                                    <img src="{{ $offer['cardImage'] }}"
                                         alt="{{ $offer['cardName'] }}"
                                         class="profile-offer-image">
                                @else
                                    <div class="profile-offer-placeholder">No Image</div>
                                @endif
                            </div>

                            <div class="profile-offer-body">
                                <h3>{{ $offer['cardName'] ?? 'Nama kartu' }}</h3>

                                <div class="profile-offer-meta">
                                    <strong>
                                        {{ !empty($offer['price']) ? '$' . $offer['price'] : 'Harga belum diisi' }}
                                    </strong>

                                    @if (!empty($offer['condition']))
                                        <span>{{ $offer['condition'] }}</span>
                                    @endif
                                </div>

                                <div class="profile-offer-actions">
                                    <a href="{{ route('card.detail', ['id' => $offer['cardId']]) }}"
                                       class="profile-offer-detail-btn">
                                        Lihat Kartu
                                    </a>

                                    @if ($canDelete)
                                        <form method="POST"
                                              action="{{ route('offers.destroy', ['cardId' => $offer['cardId'], 'offerId' => $offer['offerId']]) }}"
                                              onsubmit="return confirm('Yakin ingin menghapus penawaran ini?')"
                                              style="display: inline-block; margin: 0;">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit" class="profile-offer-delete-btn">
                                                {{ $isAdmin && !$isOwner ? 'Hapus Admin' : 'Hapus' }}
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>
    </body>
@endsection