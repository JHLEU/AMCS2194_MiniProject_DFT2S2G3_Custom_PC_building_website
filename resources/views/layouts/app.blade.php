<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Custom PC Building Platform')</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    @yield('styles')
</head>
<body>
    <header style="background-color: var(--bg-darker); border-bottom: 1px solid var(--border-color); padding: 16px 32px; display: flex; align-items: center; justify-content: space-between;">
        <div style="display: flex; align-items: center; gap: 20px;">
            <a href="{{ route('home') }}" style="font-weight: 700; font-size: 1.2rem; color: #fff; display: flex; align-items: center; gap: 8px;">
                ⚡ Custom PC Builder
            </a>
            <nav style="display: flex; gap: 16px; font-size: 0.9rem;">
                <a href="{{ route('home') }}" style="color: var(--text-light);">Home</a>
                <a href="#" style="color: var(--text-muted);">PC Builder</a>
                <a href="#" style="color: var(--text-muted);">CPU Calculator</a>
                <a href="#" style="color: var(--text-muted);">Reviews</a>
                <a href="#" style="color: var(--text-muted);">Track Order</a>
            </nav>
        </div>

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

    <footer style="background-color: var(--bg-darker); border-top: 1px solid var(--border-color); padding: 32px; text-align: center; font-size: 0.85rem; color: var(--text-muted); margin-top: 60px;">
        <p>&copy; {{ date('Y') }} Custom PC Building & ERP Supply Chain Platform. All rights reserved.</p>
    </footer>

    <script src="{{ asset('js/app.js') }}"></script>
    @yield('scripts')
</body>
</html>
