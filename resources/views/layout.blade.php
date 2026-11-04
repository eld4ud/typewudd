<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Typewud') · Type Faster. Type Smarter.</title>

    <link rel="stylesheet" href="{{ asset('css/typewud.css') }}?v=6">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Cormorant+Garamond:wght@500;600;700&display=swap" rel="stylesheet">

    <script type="module" src="https://unpkg.com/@google/model-viewer/dist/model-viewer.min.js"></script>
</head>
<body>
    <header class="site-nav">
        <div class="site-nav-inner">
            <a href="/" class="site-logo">Typewud</a>

            <nav class="site-menu">
                <a href="/typing-test">Start Type</a>
                <a href="/leaderboard">Leaderboard</a>
                <a href="/duel">Duel</a>
                <a href="/dashboard">Docs</a>
            </nav>

            <div class="site-actions">
                @auth
                    <a href="/dashboard" class="nav-link-btn">Dashboard</a>

                    <form method="post" action="/logout" class="inline">
                        @csrf
                        <button type="submit" class="nav-pill ghost">Log Out</button>
                    </form>
                @else
                    <a href="/login" class="nav-link-btn">Log In</a>
                    <a href="/register" class="nav-pill">Get Started</a>
                @endauth
            </div>
        </div>
    </header>

    <main class="main-shell">
        @yield('content')
    </main>

    @yield('scripts')
</body>
</html>