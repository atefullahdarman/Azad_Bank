<!doctype html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0a0b0d">
    <title>@yield('title', 'Bank Management') · Luxury Bank</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    @vite(['resources/css/app.css', 'resources/css/luxury.css', 'resources/js/app.js'])
</head>
<body class="public-body">
    <header class="public-header">
        <a class="brand-lockup" href="{{ route('index') }}">
            <span class="brand-emblem"><x-icon name="bank" size="25" /></span>
            <span class="brand-copy"><strong>Luxury Bank</strong><small>Management System</small></span>
        </a>
        <button class="icon-button public-menu-button" type="button" data-public-menu-toggle aria-label="Main menu" aria-controls="public-navigation" aria-expanded="false">
            <x-icon name="menu" />
        </button>
        <nav class="public-nav" id="public-navigation" data-public-nav aria-label="Main navigation">
            <a href="{{ route('about_me') }}" @if(request()->routeIs('about_me')) aria-current="page" @endif>About</a>
            <a href="{{ route('contact_as') }}" @if(request()->routeIs('contact_as')) aria-current="page" @endif>Contact</a>
            @auth
                <a class="button button-primary public-nav-login" href="{{ route('dashboard') }}">Dashboard</a>
            @else
                <a class="button button-primary public-nav-login" href="{{ route('login') }}">Sign In</a>
            @endauth
        </nav>
    </header>
    <main>@yield('content')</main>
    <footer class="public-footer">
        <div><span class="brand-emblem brand-emblem-small"><x-icon name="bank" size="20" /></span><span>Luxury Bank</span></div>
        <span>© {{ now()->year }} Luxury Bank</span>
    </footer>
</body>
</html>
