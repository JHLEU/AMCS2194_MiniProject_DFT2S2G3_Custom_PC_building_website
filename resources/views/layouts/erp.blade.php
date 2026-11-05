<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'ERP Dashboard') | Custom PC Enterprise</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    @yield('styles')
</head>
<body>
    <div class="erp-container">
        <!-- Sidebar Navigation -->
        <aside class="erp-sidebar">
            <div class="erp-brand">
                <span style="font-size: 1.4rem;">🖥️</span>
                <h2>CUSTOM PC <span class="badge-tag">ERP</span></h2>
            </div>

            <nav class="erp-nav">
                <div class="erp-nav-group-title">Executive & Operations</div>
                <a href="{{ route('erp.dashboard') }}" class="{{ request()->routeIs('erp.dashboard') ? 'active' : '' }}">
                    <span>📊</span> Dashboard
                </a>

                @if(Auth::user()->isAdmin() || Auth::user()->isWarehouse())
                    <div class="erp-nav-group-title">Supply Chain & Warehouse</div>
                    <a href="{{ route('erp.inventory.index') }}" class="{{ request()->is('erp/inventory*') ? 'active' : '' }}">
                        <span>📦</span> Inventory & Stock
                    </a>
                    <a href="#" style="color: var(--text-muted);">
                        <span>⚠️</span> Low Stock Alerts
                    </a>
                    <a href="#" style="color: var(--text-muted);">
                        <span>🏭</span> Suppliers & POs
                    </a>
                    <a href="#" style="color: var(--text-muted);">
                        <span>🏷️</span> Serial Numbers
                    </a>
                @endif

                <div class="erp-nav-group-title">Sales & Fulfillment</div>
                <a href="#" style="color: var(--text-muted);">
                    <span>🛒</span> Orders Management
                </a>
                <a href="#" style="color: var(--text-muted);">
                    <span>💵</span> Financials & Cashflow
                </a>

                @if(Auth::user()->isAdmin() || Auth::user()->isTech())
                    <div class="erp-nav-group-title">Support & Service</div>
                    <a href="{{ route('erp.support.index') }}" class="{{ request()->is('erp/support*') ? 'active' : '' }}">
                        <span>🛠️</span> Tech Support & RMA
                    </a>
                    <a href="#" style="color: var(--text-muted);">
                        <span>📅</span> Technician Dispatch
                    </a>
                    <a href="#" style="color: var(--text-muted);">
                        <span>💬</span> Customer Chat Logs
                    </a>
                @endif

                @if(Auth::user()->isAdmin())
                    <div class="erp-nav-group-title">Administration</div>
                    <a href="{{ route('erp.users.index') }}" class="{{ request()->is('erp/users*') ? 'active' : '' }}">
                        <span>👥</span> User & Role Management
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
                <div style="font-weight: 600; font-size: 1rem; color: #fff;">
                    @yield('page-title', 'Overview')
                </div>

                <div style="display: flex; align-items: center; gap: 16px;">
                    <a href="{{ route('home') }}" class="btn btn-outline btn-sm" target="_blank">
                        🌐 View Storefront
                    </a>
                    <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn btn-outline btn-sm" style="color: #f87171;">
                            Logout
                        </button>
                    </form>
                </div>
            </header>

            <div class="erp-content">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible">{{ session('success') }}</div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible">{{ session('error') }}</div>
                @endif

                @yield('content')
            </div>
        </div>
    </div>

    <script src="{{ asset('js/app.js') }}"></script>
    @yield('scripts')
</body>
</html>
