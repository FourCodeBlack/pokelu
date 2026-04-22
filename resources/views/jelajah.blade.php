{{-- @php
use Illuminate\Support\Facades\Http;

$response = Http::get(url('/api/pokeTcg/data'));
$data = $response->json();
@endphp --}}

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Jelajah - Pokelu</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Freckle+Face&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/navbar_jelajah.css') }}">
</head>

<body>
    @extends('layout.app')
 
    @push('styles')
        @vite('resources/css/explore.css')
    @endpush
 
    @section('content')
        <main class="content">
            <section class="section">
                <h2 class="section-title">RANDOM TCG</h2>
    
                <div class="items-grid">
                    @foreach ($data as $item)
                        <div class="item-card">
                            <div class="item-thumb">
                                @if (!empty($item['image']))
                                    <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" loading="lazy">
                                @else
                                    <span class="no-img">?</span>
                                @endif
                            </div>
                            <span class="item-name">{{ $item['name'] }}</span>
                        </div>
                    @endforeach
                </div>
    
                {{-- Pagination --}}
                <div class="pagination">
                    @if ($page > 1)
                        <a class="btn-page" href="?page={{ $page - 1 }}">← Prev</a>
                    @else
                        <span class="btn-page disabled">← Prev</span>
                    @endif
    
                    <span class="page-info">Page {{ $page }} / {{ ceil($total / $perPage) }}</span>
    
                    @if ($page * $perPage < $total)
                        <a class="btn-page" href="?page={{ $page + 1 }}">Next →</a>
                    @else
                        <span class="btn-page disabled">Next →</span>
                    @endif
                </div>
    
            </section>
        </main>
    @endsection
</body>

</html>