<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>FindMe@CCIS - @yield('title')</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    @yield('head')
</head>
<body>

    <header class="navbar">
        <div class="logo-area">
            <img src="{{ asset('img/logo.png') }}" alt="FindMe@CCIS Logo" class="logo-image">
            <div class="logo-text">
                <h1>FindMe@CCIS</h1>
                <h3>Lost and Found Management System</h3>
            </div>
        </div>
        <div class="user-area">
            @yield('navbar-right')
        </div>
    </header>

    <div class="main-content-wrapper">
        @yield('content')
    </div>

    <footer class="footer">
        Lost and Found Management System 2025.
    </footer>

    <script>
        function escapeHtml(value) {
            return String(value ?? '').replace(/[&<>"']/g, char => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            }[char]));
        }
    </script>
    @yield('scripts')
</body>
</html>
