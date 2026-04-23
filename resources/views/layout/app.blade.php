<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pokelu</title>
    <link href="https://fonts.googleapis.com/css2?family=Freckle+Face&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>

<body>

    @yield('navbar')

    @yield('content')

    @stack('scripts')

    
    <script src="https://www.gstatic.com/firebasejs/10.12.0/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/10.12.0/firebase-auth-compat.js"></script>
    <script>
        if (!firebase.apps.length) {
            firebase.initializeApp({
                apiKey: "AIzaSyDr7r6PrzHWUX6i_5bMnL9I7eZMcx2AS_s",
                authDomain: "pokelu-project.firebaseapp.com",
                projectId: "pokelu-project",
                storageBucket: "pokelu-project.firebasestorage.app",
                messagingSenderId: "210207641471",
                appId: "1:210207641471:web:aff0df7b7b3acb0ce2d44a",
            });
        }

        async function handleLogout(e) {
            e.preventDefault();
            await firebase.auth().signOut(); // logout Firebase dulu
            document.getElementById('logoutForm').submit(); // baru Laravel
        }
    </script>
</body>

</html>