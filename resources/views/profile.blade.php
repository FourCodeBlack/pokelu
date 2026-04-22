<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <link href="https://fonts.googleapis.com/css2?family=Freckle+Face&display=swap" rel="stylesheet">
</head>

<body>
    @extends('layout.app')
    @push('styles')
        @vite('resources/css/profile.css')
    @endpush
    @section('content')

        <header class="header">
            <div class="header-inner">
                <div class="user-info">
                    <h1 class="user-name">Username</h1>
                    <p class="user-email">email</p>
                </div>
                <div class="avatar-wrap">
                    <div class="avatar">
                        <!-- default person icon -->
                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="12" cy="8" r="4" fill="#888" />
                            <path d="M4 20c0-4 3.582-7 8-7s8 3 8 7" stroke="#888" stroke-width="2" stroke-linecap="round" />
                        </svg>
                    </div>
                </div>
            </div>
        </header>
    @endsection


</body>

</html>