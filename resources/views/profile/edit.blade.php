@extends('layout.app')

@section('navbar')
    @include('layout.navbar')
@endsection
@push('styles')
    @vite('resources/css/edit-profile.css')
@endpush

@section('content')
    <div class="container py-5 text-light">

        <a href="{{ route('profile') }}" class="back-btn">
            ← Back
        </a>

        <h1>Edit Profile</h1>

        <form action="{{ route('profile.update') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label>Name</label>
            <input type="text" name="name" class="form-control" value="{{ old('name', $data['name'] ?? '') }}">
        </div>

        <div class="mb-3">
            <label>Avatar</label>

            <div class="d-flex gap-3 flex-wrap mt-2">
                @foreach (['pfp1', 'pfp2', 'pfp3','pfp4','pfp5','pfp6'] as $avatar)
                    <label class="avatar-option">
                        <input type="radio" name="pfp" value="{{ $avatar }}" class="d-none" {{ ($data['pfp'] ?? 'default') == $avatar ? 'checked' : '' }}>

                        <img src="{{ asset('images/avatar/' . $avatar . '.png') }}" width="80" style=" border-radius:50%;">
                    </label>
                @endforeach
            </div>
        </div>

        <button class="btn btn-primary" type="submit">
            Save
        </button>
        </form>
    </div>
@endsection