<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'GOPC — Custom PC Engineering')</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/shopping-assistant.css') }}">
    @yield('styles')
    <link rel="stylesheet" href="{{ asset('css/storefront.css') }}">
</head>
<body class="storefront">
    <a class="storefront-skip" href="#storefront-content">Skip to content</a>
    <header class="site-header">
        <div class="storefront-header">
            <a href="{{ route('home') }}" class="gopc-brand-logo" title="GOPC">
                <span class="gopc-text-go">GO</span><span class="gopc-text-pc">PC</span><span class="gopc-dot"></span>
            </a>
        <button type="button" class="btn btn-outline btn-sm storefront-menu" aria-controls="storefront-navigation" aria-expanded="false">Menu</button>

        <nav class="main-nav" id="storefront-navigation" aria-label="Storefront">
            <a href="{{ route('home') }}" class="main-nav-link {{ request()->routeIs('home') ? 'active' : '' }}">Home</a>
            <a href="{{ route('builder.index') }}" class="main-nav-link {{ request()->routeIs('builder.*') ? 'active' : '' }}">PC Builder</a>
            <a href="{{ route('calculator.index') }}" class="main-nav-link {{ request()->routeIs('calculator.*') ? 'active' : '' }}">Performance calculator</a>
            <a href="{{ route('orders.track') }}" class="main-nav-link {{ request()->routeIs('orders.track') ? 'active' : '' }}">Track order</a>
            <a href="{{ route('checkout.index') }}" class="main-nav-link {{ request()->routeIs('checkout.*') ? 'active' : '' }}">Checkout</a>
        </nav>

        <div class="storefront-account">
            @auth
                <!-- Tactical User Profile Dropdown -->
                <div class="user-menu-wrapper" id="userMenuWrapper">
                    <button type="button" class="user-menu-trigger" id="userMenuTrigger" aria-expanded="false" aria-controls="userMenuDropdown">
                        <span class="user-menu-avatar">
                            {{ strtoupper(substr(Auth::user()->name ?? Auth::user()->username ?? 'U', 0, 1)) }}
                        </span>
                        <span class="user-menu-name">
                            {{ Auth::user()->name ?? Auth::user()->username }}
                        </span>
                        <span class="user-menu-arrow">▾</span>
                    </button>

                    <div class="user-menu-dropdown" id="userMenuDropdown">
                        <div class="user-dropdown-header">
                            <div class="user-dropdown-title">{{ Auth::user()->name ?? Auth::user()->username }}</div>
                            <div class="user-dropdown-sub">{{ Auth::user()->email ?? 'Verified Account' }}</div>
                        </div>

                        @if(Auth::user()->isStaff())
                            <a href="{{ route('erp.dashboard') }}" class="user-dropdown-item" style="color: var(--primary);">
                                <span>ERP Console</span>
                                <span class="badge badge-primary">Staff</span>
                            </a>
                            <div class="user-dropdown-sep"></div>
                        @endif

                        <a href="{{ route('customer.orders') }}" class="user-dropdown-item {{ request()->routeIs('customer.orders') ? 'active' : '' }}">
                            <span>My Orders</span>
                        </a>
                        <a href="{{ route('customer.warranties.index') }}" class="user-dropdown-item {{ request()->routeIs('customer.warranties.*') ? 'active' : '' }}">
                            <span>My Warranties</span>
                        </a>
                        <a href="{{ route('customer.support.index') }}" class="user-dropdown-item {{ request()->routeIs('customer.support.*') ? 'active' : '' }}">
                            <span>Technical Support</span>
                        </a>

                        <div class="user-dropdown-sep"></div>

                        <form action="{{ route('logout') }}" method="POST" style="margin: 0;">
                            @csrf
                            <button type="submit" class="user-dropdown-logout-btn">
                                <span>Sign out</span>
                                <span>&rarr;</span>
                            </button>
                        </form>
                    </div>
                </div>
            @else
                <a href="{{ route('login') }}" class="btn btn-outline btn-sm">Sign in</a>
                <a href="{{ route('register') }}" class="btn btn-primary btn-sm">Register</a>
            @endauth
        </div>
        </div>
    </header>

    <main id="storefront-content">
        @if(session('success'))
            <div class="container" style="margin-top: 20px;">
                <div class="alert alert-success">{{ session('success') }}</div>
            </div>
        @endif
        @if(session('error'))
            <div class="container" style="margin-top: 20px;">
                <div class="alert alert-danger">{{ session('error') }}</div>
            </div>
        @endif

        @yield('content')
    </main>

    <footer class="site-footer">
        <div class="site-footer-inner">
            <div class="site-footer-grid">
                <div>
                    <div class="footer-col-title">Plaza Low Yat Workshop</div>
                    <ul class="footer-compact-list">
                        <li>
                            <span style="color: var(--text-muted);">Location:</span>
                            <span>{{ $contactInfo['address'] ?? 'Plaza Low Yat, Bukit Bintang, Kuala Lumpur' }}</span>
                        </li>
                        <li>
                            <span style="color: var(--text-muted);">Hours:</span>
                            <span class="font-mono">{{ $contactInfo['operating_hours'] ?? 'Mon–Sat: 10:00 AM – 8:00 PM' }}</span>
                        </li>
                        <li>
                            <span style="color: var(--text-muted);">Testing Lab:</span>
                            <span>24-Hour Thermal FurMark / Cinebench Burn-in Station</span>
                        </li>
                    </ul>
                </div>

                <div>
                    <div class="footer-col-title">Engineering Inquiries &amp; Support</div>
                    <ul class="footer-compact-list">
                        <li>
                            <span style="color: var(--text-muted);">WhatsApp:</span>
                            <a href="{{ $contactInfo['whatsapp_url'] ?? '#' }}" target="_blank" rel="noopener noreferrer" class="font-mono" style="color: var(--primary);">
                                {{ $contactInfo['whatsapp_phone'] ?? '+60 16-888 2345' }}
                            </a>
                        </li>
                        <li>
                            <span style="color: var(--text-muted);">Workshop Hotline:</span>
                            <a href="tel:{{ str_replace(' ', '', $contactInfo['hotline'] ?? '+60388881234') }}" class="font-mono">
                                {{ $contactInfo['hotline'] ?? '+60 3-8888 1234' }}
                            </a>
                        </li>
                        <li>
                            <span style="color: var(--text-muted);">Support Ticket:</span>
                            <a href="mailto:{{ $contactInfo['support_email'] ?? 'support@custompc.local' }}">
                                {{ $contactInfo['support_email'] ?? 'support@custompc.local' }}
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="footer-bottom-bar">
                <div>
                    &copy; {{ date('Y') }} GOPC Engineering. Precision serialized hardware builds.
                </div>
                <div>
                    @auth
                        @if(Auth::user()->isStaff())
                            <a href="{{ route('erp.dashboard') }}" style="color: var(--primary); font-family: 'Geist Mono', monospace;">
                                ● Staff Console
                            </a>
                        @endif
                    @else
                        <a href="{{ route('login') }}" style="color: var(--text-muted); font-family: 'Geist Mono', monospace;">
                            Staff Portal &rarr;
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </footer>

    <script src="{{ asset('js/vendor/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('js/app.js') }}"></script>
    @include('partials.chat-widget')
    <script src="{{ asset('js/shopping-assistant.js') }}" defer></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const menu = document.querySelector('.storefront-menu');
            const navigation = document.getElementById('storefront-navigation');
            menu.addEventListener('click', function () {
                menu.setAttribute('aria-expanded', String(navigation.classList.toggle('is-open')));
            });

            const userMenuTrigger = document.getElementById('userMenuTrigger');
            const userMenuDropdown = document.getElementById('userMenuDropdown');

            if (userMenuTrigger && userMenuDropdown) {
                userMenuTrigger.addEventListener('click', function (e) {
                    e.stopPropagation();
                    userMenuDropdown.classList.toggle('show');
                    userMenuTrigger.classList.toggle('active');
                    userMenuTrigger.setAttribute('aria-expanded', String(userMenuDropdown.classList.contains('show')));
                });

                document.addEventListener('click', function (e) {
                    if (!userMenuDropdown.contains(e.target) && !userMenuTrigger.contains(e.target)) {
                        userMenuDropdown.classList.remove('show');
                        userMenuTrigger.classList.remove('active');
                        userMenuTrigger.setAttribute('aria-expanded', 'false');
                    }
                });
            }
        });
    </script>
    @yield('scripts')
</body>
</html>
