<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Pokelu</title>
    <link href="https://fonts.googleapis.com/css2?family=Freckle+Face&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')

    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ asset('images/pfp.png') }}">
</head>

<body>
    @yield('navbar')

    @yield('content')

    {{--
        PENTING — Gunakan Firebase v8, BUKAN v9/v10.
        
        Firebase v9/v10 menggunakan WebSocket yang WAJIB SSL, sehingga
        selalu gagal di localhost HTTP dengan ERR_CERT_COMMON_NAME_INVALID.
        
        Firebase v8 mendukung forceLongPolling() yang bekerja tanpa SSL,
        cocok untuk development di localhost.
        
        Saat production (sudah pakai HTTPS), bisa upgrade ke v10 lagi.
    --}}
    <script src="https://www.gstatic.com/firebasejs/10.12.5/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/10.12.5/firebase-auth-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/10.12.5/firebase-database-compat.js"></script>

    <script>
        if (!firebase.apps.length) {
            firebase.initializeApp({
                apiKey: "AIzaSyDr7r6PrzHWUX6i_5bMnL9I7eZMcx2AS_s",
                authDomain: "pokelu-project.firebaseapp.com",
                databaseURL: "https://pokelu-project-default-rtdb.asia-southeast1.firebasedatabase.app",
                projectId: "pokelu-project",
                storageBucket: "pokelu-project.firebasestorage.app",
                messagingSenderId: "210207641471",
                appId: "1:210207641471:web:aff0df7b7b3acb0ce2d44a",
            });
        }

        async function handleLogout(e) {
            e.preventDefault();
            await firebase.auth().signOut();
            document.getElementById('logoutForm').submit();
        }
    </script>

    {{-- Stack scripts SETELAH Firebase siap --}}
    @stack('scripts')
</body>

</html>