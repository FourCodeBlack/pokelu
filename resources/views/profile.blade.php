@extends('layout.app')
@push('styles')
    @vite('resources/css/profile.css')
@endpush
@section('navbar')
    @include('layout.navbar')
@endsection
@section('content')


    @php
        use App\Models\userLogin;
        use App\Http\Controllers\userProfile;
        $data = userProfile::getUser(userLogin::get('uid'));
        $pfp = asset('images/avatar/' . $data['pfp'] . '.png');

    @endphp

    <header class="header">
        <div class="header-inner">
            <div class="avatar-wrap">
                <div class="avatar">
                    <img src="{{ $pfp }}" alt="">
                </div>
                <img src="{{ asset('images/pen.png') }}" alt="" class="edit">
            </div>
            <div class="user-info">
                <h1 class="user-name">{{ $data['name'] }}</h1>
                <p class="user-email">{{$data['email']}}</p>
            </div>

        </div>
    </header>

    <body>
        <section class="liked-tcg">
            <h1 class="font-family fs-3 text-light">Wishlist TCG</h1>
            @php
                use App\Models\FirebaseHelper;
                $uid = session('user.uid');
                $path = "users/$uid/wishlist";

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
    </body>
@endsection