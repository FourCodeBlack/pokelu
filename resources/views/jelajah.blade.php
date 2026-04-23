@extends('layout.app')

@push('styles')
    @vite('resources/css/explore.css')
@endpush

@section('navbar')
    @include('layout.navbar')
@endsection

@section('content')
    <main class="content">
        <section class="section">
            <h2 class="section-title">RANDOM TCG</h2>

            <div class="items-grid">
                @foreach ($data as $item)
                    @php
                        $name   = $item['name']   ?? '';
                        $rarity = $item['rarity'] ?? '';

                        if (Str::contains($name, ['VMAX', 'VSTAR', 'EX']) || Str::contains($rarity, ['Rare Secret', 'Amazing Rare', 'LEGEND'])) {
                            $tier = 'secret';
                        } elseif (Str::contains($name, ['V ', 'V', 'GX']) || Str::contains($rarity, ['Rare Ultra', 'Rare Rainbow'])) {
                            $tier = 'ultra';
                        } elseif (Str::contains($rarity, ['Rare Holo'])) {
                            $tier = 'holo';
                        } else {
                            $tier = 'normal';
                        }
                    @endphp

                    {{-- ✅ <a> bukan <div> supaya bisa diklik ke halaman detail --}}
                    <a class="item-card"
                        href="{{ route('card.detail', ['id' => $item['id']]) }}"
                        data-type="{{ $item['type'] ?? '' }}"
                        data-rarity="{{ $rarity }}">

                        <div class="item-thumb tier-{{ $tier }}" data-tier="{{ $tier }}">
                            <div class="glow-spot"></div>
                            <div class="border-glow"></div>
                            @if(in_array($tier, ['secret', 'ultra']))
                                <div class="holo-layer"></div>
                                <div class="shimmer-layer"></div>
                                <div class="sparkle-layer"></div>
                                @if($tier === 'secret')
                                    <div class="gold-layer"></div>
                                @endif
                            @elseif($tier === 'holo')
                                <div class="shimmer-layer"></div>
                            @endif

                            @if (!empty($item['image']))
                                <img src="{{ $item['image'] }}" alt="{{ $name }}" loading="lazy">
                            @else
                                <span class="no-img">?</span>
                            @endif
                        </div>

                        <span class="item-name">{{ $name }}</span>
                    </a>
                @endforeach
            </div>

            {{-- ── PAGINATION ── --}}
            <div class="pagination">
                @if($page > 1)
                    <a href="?{{ http_build_query(array_merge(request()->query(), ['page' => $page - 1])) }}">← Prev</a>
                @endif
                @if($page < ceil($total / $perPage))
                    <a href="?{{ http_build_query(array_merge(request()->query(), ['page' => $page + 1])) }}">Next →</a>
                @endif
            </div>

        </section>
    </main>

    <script>
        function spawnRipple(thumb, x, y) {
            const size = Math.max(thumb.offsetWidth, thumb.offsetHeight) * 0.5;
            const r = document.createElement('div');
            r.className = 'ripple';
            r.style.cssText = `width:${size}px;height:${size}px;left:${x - size/2}px;top:${y - size/2}px`;
            thumb.appendChild(r);
            requestAnimationFrame(() => r.classList.add('go'));
            setTimeout(() => r.remove(), 520);
        }

        function attachHoverEffect(thumb) {
            if (thumb._attached) return;
            thumb._attached = true;

            const tier = thumb.dataset.tier ?? 'normal';
            thumb.closest('.item-card').style.perspective = '800px';
            let entered = false;

            thumb.addEventListener('mouseenter', e => {
                if (entered) return;
                entered = true;
                const r = thumb.getBoundingClientRect();
                spawnRipple(thumb, e.clientX - r.left, e.clientY - r.top);
            });
            thumb.addEventListener('mouseleave', () => { entered = false; });
            thumb.addEventListener('click', e => {
                const r = thumb.getBoundingClientRect();
                spawnRipple(thumb, e.clientX - r.left, e.clientY - r.top);
            });

            thumb.addEventListener('mousemove', e => {
                const r  = thumb.getBoundingClientRect();
                const px = e.clientX - r.left;
                const py = e.clientY - r.top;
                const xp = px / r.width, yp = py / r.height;

                thumb.style.setProperty('--mx', (xp * 100).toFixed(1) + '%');
                thumb.style.setProperty('--my', (yp * 100).toFixed(1) + '%');

                const tilt  = { secret: 26, ultra: 20, holo: 14, normal: 7 }[tier] ?? 7;
                const scale = { secret: 1.05, ultra: 1.035, holo: 1.02, normal: 1.01 }[tier] ?? 1.01;
                const rx = (yp - .5) * tilt, ry = (.5 - xp) * tilt;
                thumb.style.transform = `rotateX(${rx}deg) rotateY(${ry}deg) scale(${scale})`;

                if (tier === 'secret' || tier === 'ultra') {
                    thumb.style.setProperty('--holo-angle', Math.round(xp * 60 + 75) + 'deg');
                }
            });

            thumb.addEventListener('mouseleave', () => {
                thumb.style.transform = '';
                thumb.style.transition = 'transform .5s ease, box-shadow .3s ease';
                setTimeout(() => thumb.style.transition = '', 520);
            });
        }

        document.querySelectorAll('.item-thumb').forEach(attachHoverEffect);

        new MutationObserver(mutations => {
            mutations.forEach(m => m.addedNodes.forEach(node => {
                if (node.nodeType !== 1) return;
                if (node.classList?.contains('item-thumb')) attachHoverEffect(node);
                node.querySelectorAll?.('.item-thumb').forEach(attachHoverEffect);
            }));
        }).observe(document.querySelector('.items-grid') ?? document.body, { childList: true, subtree: true });
    </script>
@endsection

@push('scripts')
    @vite('resources/js/explore-search.js')
@endpush