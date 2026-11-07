<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Custom PC Building Platform')</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    @yield('styles')
</head>
<body>
    <header style="background-color: var(--bg-darker); border-bottom: 1px solid var(--border-color); padding: 16px 32px; display: flex; align-items: center; justify-content: space-between; position: relative;">
        <div style="display: flex; align-items: center; gap: 20px;">
            <a href="{{ route('home') }}" style="font-weight: 700; font-size: 1.2rem; color: #fff; display: flex; align-items: center; gap: 8px;">
                ⚡ Custom PC Builder
            </a>
        </div>

        <nav style="display: flex; gap: 20px; font-size: 0.95rem; align-items: center; justify-content: center; flex-wrap: wrap;">
            <a href="{{ route('home') }}" style="color: {{ request()->routeIs('home') ? '#fff' : 'var(--text-light)' }}; font-weight: {{ request()->routeIs('home') ? '600' : 'normal' }};">Home</a>
            <a href="{{ route('builder.index') }}" style="color: {{ request()->routeIs('builder.*') ? 'var(--primary-hover)' : 'var(--text-light)' }}; font-weight: {{ request()->routeIs('builder.*') ? '600' : 'normal' }};">⚡ PC Builder</a>
            <a href="{{ route('calculator.index') }}" style="color: {{ request()->routeIs('calculator.*') ? 'var(--primary-hover)' : 'var(--text-light)' }}; font-weight: {{ request()->routeIs('calculator.*') ? '600' : 'normal' }};">🎯 Performance Calc</a>
            <a href="{{ route('orders.track') }}" style="color: {{ request()->routeIs('orders.track') ? 'var(--info)' : 'var(--text-muted)' }};">Track Order</a>
            <a href="{{ route('checkout.index') }}" style="color: {{ request()->routeIs('checkout.*') ? 'var(--accent)' : 'var(--text-muted)' }};">🛒 Checkout</a>
            @auth
                <a href="{{ route('customer.orders') }}" style="color: {{ request()->routeIs('customer.orders') ? '#fff' : 'var(--text-muted)' }};">My Orders</a>
                <a href="{{ route('customer.warranties.index') }}" style="color: {{ request()->routeIs('customer.warranties.*') ? '#fff' : 'var(--text-muted)' }};">My Warranties</a>
                <a href="{{ route('customer.support.index') }}" style="color: {{ request()->routeIs('customer.support.*') ? '#fff' : 'var(--text-muted)' }};">Support</a>
            @endauth
        </nav>

        <div style="display: flex; align-items: center; gap: 12px;">
            @auth
                @if(Auth::user()->isStaff())
                    <a href="{{ route('erp.dashboard') }}" class="btn btn-outline btn-sm">ERP Dashboard</a>
                @endif
                <span style="font-size: 0.85rem; color: var(--text-light);">{{ Auth::user()->name ?? Auth::user()->username }}</span>
                <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-outline btn-sm">Logout</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="btn btn-outline btn-sm">Staff & Customer Login</a>
                <a href="{{ route('register') }}" class="btn btn-primary btn-sm">Register</a>
            @endauth
        </div>
    </header>

    <main>
        @if(session('success'))
            <div style="max-width: 1200px; margin: 20px auto; padding: 0 20px;">
                <div class="alert alert-success alert-dismissible">{{ session('success') }}</div>
            </div>
        @endif
        @if(session('error'))
            <div style="max-width: 1200px; margin: 20px auto; padding: 0 20px;">
                <div class="alert alert-danger alert-dismissible">{{ session('error') }}</div>
            </div>
        @endif

        @yield('content')
    </main>

    <footer class="site-footer">
        <div class="site-footer-inner">
            <div class="site-footer-grid">
                <!-- 1. About -->
                <div>
                    <a href="{{ route('home') }}" class="footer-brand-logo">
                        <span>⚡</span>
                        <span>{{ $contactInfo['store_name'] ?? 'Custom PC Enterprise & Workshop' }}</span>
                    </a>
                    <p class="footer-desc">
                        Professional custom gaming PCs and workstation solutions. Every rig is precision-built, bench-tested, and covered by comprehensive warranty protection.
                    </p>
                </div>

                <!-- 2. Address & Hours -->
                <div>
                    <div class="footer-col-title">Workshop & Address</div>
                    <ul class="footer-compact-list">
                        <li>
                            <span class="icon">📍</span>
                            <span>{{ $contactInfo['address'] ?? 'Plaza Low Yat, Kuala Lumpur' }}</span>
                        </li>
                        <li>
                            <span class="icon">🕒</span>
                            <span style="color: #10b981;">{{ $contactInfo['operating_hours'] ?? 'Mon–Sat: 10:00 AM – 8:00 PM' }}</span>
                        </li>
                    </ul>
                </div>

                <!-- 3. Contact Info -->
                <div>
                    <div class="footer-col-title">Contact Us</div>
                    <ul class="footer-compact-list">
                        <li>
                            <span class="icon">💬</span>
                            <a href="{{ $contactInfo['whatsapp_url'] ?? '#' }}" target="_blank" rel="noopener noreferrer" style="color: #25D366; font-weight: 500;">
                                WhatsApp: {{ $contactInfo['whatsapp_phone'] ?? '+60 16-888 2345' }}
                            </a>
                        </li>
                        <li>
                            <span class="icon">📞</span>
                            <a href="tel:{{ str_replace(' ', '', $contactInfo['hotline'] ?? '+60388881234') }}">
                                Tel: {{ $contactInfo['hotline'] ?? '+60 3-8888 1234' }}
                            </a>
                        </li>
                        <li>
                            <span class="icon">✉️</span>
                            <a href="mailto:{{ $contactInfo['support_email'] ?? 'support@custompc.local' }}">
                                {{ $contactInfo['support_email'] ?? 'support@custompc.local' }}
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Footer Bottom -->
            <div class="footer-bottom-bar">
                <div>
                    &copy; {{ date('Y') }} {{ $contactInfo['store_name'] ?? 'Custom PC Enterprise & Workshop' }}. All rights reserved.
                </div>
                <div class="footer-bottom-nav">
                    @auth
                        @if(Auth::user()->isStaff())
                            <a href="{{ route('erp.dashboard') }}" class="footer-bottom-link staff-link">ERP Dashboard</a>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="footer-bottom-link staff-link">Staff Portal</a>
                    @endauth
                </div>
            </div>
        </div>
    </footer>

    <script src="{{ asset('js/vendor/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('js/app.js') }}"></script>
    @include('partials.chat-widget')
    <script src="{{ asset('js/chat-widget.js') }}"></script>
    @yield('scripts')
</body>
</html>
