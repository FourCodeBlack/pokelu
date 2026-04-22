<!DOCTYPE html>
<html>

<head>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>

<body>
    @yield('content')
    @stack('script')
</body>

</html>