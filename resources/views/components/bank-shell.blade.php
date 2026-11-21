@props(['title' => 'Dashboard', 'subtitle' => ''])

@php
    $user = auth()->user();
    $staff = $user?->isBankStaff();
    $searchValue = request()->routeIs('bank.search') ? request()->query('q') : null;
    $links = $staff ? [
        ['dashboard', 'dashboard', 'home', 'Dashboard'],
        ['accounts.index', 'accounts.*', 'wallet', 'Accounts'],
        ['transactions.index', 'transactions.*', 'transfer', 'Transactions'],
        ['customers.index', 'customers.*', 'users', 'Customers'],
        ['loans.index', 'loans.*', 'coins', 'Loans'],
        ['reports', 'reports', 'chart', 'Reports'],
    ] : [['dashboard', 'dashboard', 'home', 'My Account']];
    if ($user?->role === 'admin' && Route::has('users.index')) {
        $links[] = ['users.index', 'users.*', 'user', 'Users'];
    }
    $links[] = ['profile.edit', 'profile.*', 'settings', 'Settings'];
    $roleLabel = match ($user?->role) {
        'admin' => 'Administrator',
        'employee' => 'Employee',
        default => 'Customer',
    };
@endphp

<!doctype html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#080807">
    <title>{{ $title }} · Luxury Bank</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    @vite(['resources/css/app.css', 'resources/css/luxury.css', 'resources/js/app.js'])
</head>
<body class="lux-app luxury-workspace">
    <a class="skip-to-content" href="#main-content">Skip to content</a>
    <div class="app-frame">
        <aside class="app-sidebar" id="app-sidebar" aria-label="Main navigation">
            <a class="brand-lockup" href="{{ route('dashboard') }}">
                <span class="brand-emblem"><x-icon name="bank" size="48" /></span>
                <span class="brand-copy"><strong>Luxury Bank</strong><small>Management System</small></span>
            </a>
            <nav class="side-nav" aria-label="Workspace">
                @foreach($links as [$route, $pattern, $icon, $label])
                    <a class="side-link {{ request()->routeIs($pattern) ? 'is-active' : '' }}" href="{{ route($route) }}"
                       @if(request()->routeIs($pattern)) aria-current="page" @endif>
                        <x-icon :name="$icon" size="24" /><span>{{ $label }}</span>
                    </a>
                @endforeach
            </nav>
            <div class="sidebar-spacer"></div>
            <form method="POST" action="{{ route('logout') }}" class="logout-form">
                @csrf
                <button class="side-link side-link-button" type="submit"><x-icon name="logout" size="23" /><span>Logout</span></button>
            </form>
        </aside>

        <button class="sidebar-scrim" type="button" data-sidebar-close aria-label="Close menu"></button>
        <div class="app-main">
            <header class="topbar">
                <button class="icon-button mobile-menu-button" type="button" data-menu-toggle aria-label="Toggle menu" aria-controls="app-sidebar" aria-expanded="false">
                    <x-icon name="menu" size="29" />
                </button>
                @if($staff)
                    <form class="topbar-search" method="GET" action="{{ route('bank.search') }}" role="search">
                        <button type="submit" aria-label="Search records"><x-icon name="search" size="22" /></button>
                        <input name="q" type="search" value="{{ is_string($searchValue) ? mb_substr(trim($searchValue), 0, 100) : '' }}"
                               placeholder="Search accounts, customers, transactions..." aria-label="Search bank records" maxlength="100" required>
                    </form>
                @endif
                <div class="topbar-actions">
                    @if($staff)
                        <details class="topbar-menu">
                            <summary class="topbar-notification" aria-label="Operations notifications">
                                <x-icon name="bell" size="25" />
                                @if(($pendingTransactions ?? 0) + ($pendingLoans ?? 0) > 0)<i aria-hidden="true"></i>@endif
                            </summary>
                            <div class="topbar-dropdown">
                                <strong>Notifications</strong>
                                <a href="{{ route('transactions.index', ['status' => 'pending']) }}">Pending transactions <b>{{ $pendingTransactions ?? 0 }}</b></a>
                                <a href="{{ route('loans.index', ['status' => 'pending']) }}">Pending loan requests <b>{{ $pendingLoans ?? 0 }}</b></a>
                            </div>
                        </details>
                    @endif
                    <details class="topbar-menu">
                        <summary class="topbar-profile" aria-label="Account menu">
                            <span class="avatar-mark avatar-mark-small"><x-icon name="user" size="25" /></span>
                            <span class="topbar-identity"><strong>{{ $user?->name }}</strong><small>{{ $roleLabel }}</small></span>
                            <x-icon name="chevron-down" size="18" />
                        </summary>
                        <div class="topbar-dropdown">
                            <a href="{{ route('profile.edit') }}"><x-icon name="settings" size="17" /> Settings</a>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit"><x-icon name="logout" size="17" /> Logout</button>
                            </form>
                        </div>
                    </details>
                </div>
            </header>

            <div class="page-container">
                <main id="main-content" tabindex="-1">
                    @if($title !== 'Dashboard')
                        <div class="page-heading">
                            <div>
                                <h1>{{ $title }}</h1>
                                @if($subtitle !== '')<p class="page-subtitle">{{ $subtitle }}</p>@endif
                            </div>
                            <time class="current-date" datetime="{{ today()->toDateString() }}">{{ today()->format('M j, Y') }}</time>
                        </div>
                    @endif
                    @if(session('success'))
                        <div class="notice notice-success" role="status"><x-icon name="check" /><span>{{ session('success') }}</span></div>
                    @endif
                    @if(session('error'))
                        <div class="notice notice-error" role="alert"><x-icon name="close" /><span>{{ session('error') }}</span></div>
                    @endif
                    <div class="page-content">{{ $slot }}</div>
                </main>
                <footer class="app-footer"><span>© {{ today()->year }} Luxury Bank</span><span>Bank Management System</span></footer>
            </div>
        </div>
    </div>
</body>
</html>
