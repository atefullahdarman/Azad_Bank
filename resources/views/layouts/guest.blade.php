<!doctype html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0a0b0d">
    <title>Secure Access · Luxury Bank</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    @vite(['resources/css/app.css', 'resources/css/luxury.css', 'resources/js/app.js'])
</head>
<body class="auth-body">
    <div class="auth-layout">
        <aside class="auth-visual" aria-label="Luxury Bank">
            <img class="auth-visual-image" src="{{ asset('images/bank-hero.jpg') }}" alt="Modern bank building" fetchpriority="high">
            <a class="brand-lockup auth-visual-brand" href="{{ route('index') }}">
                <span class="brand-emblem"><x-icon name="bank" size="25" /></span>
                <span class="brand-copy"><strong>Luxury Bank</strong><small>Management System</small></span>
            </a>
            <div class="auth-visual-copy">
                <h2>Banking operations in one secure workspace.</h2>
                <div class="auth-visual-note"><x-icon name="shield" size="17" /> Secure account access</div>
            </div>
        </aside>
        <main class="auth-panel">
            <div class="auth-panel-inner">
                {{ $slot }}
                <p class="auth-footer">© {{ now()->year }} Luxury Bank</p>
            </div>
        </main>
    </div>
</body>
</html>
