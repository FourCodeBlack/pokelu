<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pokelu</title>
    <link href="https://fonts.googleapis.com/css2?family=Freckle+Face&display=swap" rel="stylesheet">
</head>

<body>
    @extends('layout.app')
    @push('styles')
        @vite('resources/css/home.css')
    @endpush
    @section('content')


        @include('layout.navbar')

        <!-- Hero Section -->
        <div class="hero">
            <img class="bg" src="{{ asset('images/background_home.webp') }}" alt="Background">

            <div class="hero-content">
                <h1>CARI KARTUMU SEKARANG!</h1>
                <p>Data TCG Pokemon Terlengkap</p>
                <a href="explore" class="btn-jelajahi">Jelajahi ></a>
            </div>
        </div>
    @endsection

</body>

</html>