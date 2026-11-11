<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Console') | GOPC Operations</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    @yield('styles')
</head>
<body>
    <div class="erp-container">
        <!-- Sidebar Navigation -->
        <aside class="erp-sidebar">
            <div class="erp-brand">
                <span class="gopc-dot"></span>
                <h2>GOPC <span class="badge-tag">CONSOLE</span></h2>
            </div>

            <nav class="erp-nav">
                <div class="erp-nav-group-title">Executive &amp; Operations</div>
                <a href="{{ route('erp.dashboard') }}" class="{{ request()->routeIs('erp.dashboard') ? 'active' : '' }}">
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('erp.compatibility.index') }}" class="{{ request()->routeIs('erp.compatibility.*') ? 'active' : '' }}">
                    <span>Compatibility engine</span>
                </a>

                @if(Auth::user()->isAdmin() || Auth::user()->isWarehouse())
                    <div class="erp-nav-group-title">Supply Chain &amp; Warehouse</div>
                    <a href="{{ route('erp.inventory.index') }}" class="{{ request()->routeIs('erp.inventory.index') ? 'active' : '' }}">
                        <span>Inventory status</span>
                    </a>
                    <a href="{{ route('erp.products.index') }}" class="{{ request()->routeIs('erp.products.*') ? 'active' : '' }}">
                        <span>Hardware catalog</span>
                    </a>
                    <a href="{{ route('erp.inventory.low-stock') }}" class="{{ request()->routeIs('erp.inventory.low-stock') ? 'active' : '' }}">
                        <span>Low stock alerts</span>
                    </a>
                    <a href="{{ route('erp.inventory.costing') }}" class="{{ request()->routeIs('erp.inventory.costing') ? 'active' : '' }}">
                        <span>Costing &amp; valuation</span>
                    </a>
                    <a href="{{ route('erp.suppliers.index') }}" class="{{ request()->routeIs('erp.suppliers.*') ? 'active' : '' }}">
                        <span>Suppliers &amp; mapping</span>
                    </a>
                    <a href="{{ route('erp.restock.index') }}" class="{{ request()->routeIs('erp.restock.*') ? 'active' : '' }}">
                        <span>Restock purchase orders</span>
                    </a>
                    <a href="{{ route('erp.serials.index') }}" class="{{ request()->routeIs('erp.serials.*') ? 'active' : '' }}">
                        <span>Component serials</span>
                    </a>
                @endif

                <div class="erp-nav-group-title">Fulfillment &amp; Finance</div>
                <a href="{{ route('erp.orders.index') }}" class="{{ request()->routeIs('erp.orders.*') ? 'active' : '' }}">
                    <span>Orders queue</span>
                </a>
                @if(Auth::user()->isAdmin())
                    <a href="{{ route('erp.finance.index') }}" class="{{ request()->routeIs('erp.finance.*') ? 'active' : '' }}">
                        <span>Financial ledger</span>
                    </a>
                @endif

                @if(Auth::user()->isAdmin() || Auth::user()->isTech())
                    <div class="erp-nav-group-title">Technical Lab &amp; RMA</div>
                    <a href="{{ route('erp.support.index') }}" class="{{ request()->routeIs('erp.support.index') || request()->routeIs('erp.support.show') ? 'active' : '' }}">
                        <span>Support tickets</span>
                    </a>
                    <a href="{{ route('erp.support.schedules') }}" class="{{ request()->routeIs('erp.support.schedules') ? 'active' : '' }}">
                        <span>Bench dispatch</span>
                    </a>
                    <a href="{{ route('erp.support.warranties') }}" class="{{ request()->routeIs('erp.support.warranties') ? 'active' : '' }}">
                        <span>Warranty registry</span>
                    </a>
                    <a href="{{ route('erp.chat.index') }}" class="{{ request()->routeIs('erp.chat.*') ? 'active' : '' }}">
                        <span>Customer live chat</span>
                    </a>
                @endif

                @if(Auth::user()->isAdmin())
                    <div class="erp-nav-group-title">Administration</div>
                    <a href="{{ route('erp.users.index') }}" class="{{ request()->is('erp/users*') ? 'active' : '' }}">
                        <span>Staff accounts</span>
                    </a>
                @endif
            </nav>

            <div class="erp-sidebar-footer">
                <div class="user-chip">
                    <div class="user-avatar">
                        {{ strtoupper(substr(Auth::user()->name ?? Auth::user()->username, 0, 1)) }}
                    </div>
                    <div class="user-info">
                        <div class="name">{{ Auth::user()->name ?? Auth::user()->username }}</div>
                        <div class="role-badge">● {{ Auth::user()->role->name ?? 'Staff' }}</div>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Workspace -->
        <div class="erp-main">
            <header class="erp-topbar">
                <div style="font-weight: 600; font-size: 0.9375rem; color: var(--text-primary);">
                    @yield('page-title', 'Overview')
                </div>

                <div style="display: flex; align-items: center; gap: 12px;">
                    <a href="{{ route('home') }}" class="btn btn-secondary btn-sm" target="_blank">
                        Storefront &rarr;
                    </a>
                    <form action="{{ route('logout') }}" method="POST" style="display: inline; margin: 0;">
                        @csrf
                        <button type="submit" class="btn btn-outline btn-sm" style="color: var(--danger);">
                            Sign out
                        </button>
                    </form>
                </div>
            </header>

            <div class="erp-content">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                @yield('content')
            </div>
        </div>
    </div>

    <script src="{{ asset('js/vendor/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('js/app.js') }}"></script>
    @yield('scripts')
</body>
</html>
