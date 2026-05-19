@extends('layout.app')

@section('navbar')
    @include('layout.navbar')
@endsection

@push('styles')
    @vite('resources/css/home.css')
@endpush

@section('content')
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