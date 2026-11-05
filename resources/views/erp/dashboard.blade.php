@extends('layouts.erp')

@section('title', 'ERP Overview')
@section('page-title', 'Enterprise Operations Dashboard')

@section('content')
<div style="margin-bottom: 24px;">
    <h1 style="font-size: 1.5rem; font-weight: 700; color: #fff;">
        Welcome, {{ $user->name ?? $user->username }}
    </h1>
    <p style="color: var(--text-muted); font-size: 0.9rem;">
        Role: <strong style="color: var(--accent);">{{ $user->role->name ?? 'Staff' }}</strong> &bull; System Status: <span style="color: var(--success); font-weight: 600;">● Online (Operational)</span>
    </p>
</div>

<!-- High-level Metric Stat Cards -->
<div class="stat-grid">
    <div class="stat-card">
        <div>
            <div class="stat-label">Active Products</div>
            <div class="stat-val">{{ number_format($totalProducts) }}</div>
        </div>
        <div class="stat-icon" style="color: var(--primary);">📦</div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-label">Low Stock Alerts</div>
            <div class="stat-val" style="color: var(--warning);">{{ number_format($lowStockCount) }}</div>
        </div>
        <div class="stat-icon" style="color: var(--warning);">⚠️</div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-label">Pending Restocks</div>
            <div class="stat-val">{{ number_format($pendingRestocks) }}</div>
        </div>
        <div class="stat-icon" style="color: var(--info);">🚚</div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-label">Active Orders</div>
            <div class="stat-val" style="color: var(--success);">{{ number_format($activeOrders) }}</div>
        </div>
        <div class="stat-icon" style="color: var(--success);">🛒</div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-label">Total Revenue</div>
            <div class="stat-val">RM {{ number_format($totalRevenue, 2) }}</div>
        </div>
        <div class="stat-icon" style="color: #a855f7;">💵</div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-label">Support Tickets</div>
            <div class="stat-val" style="color: var(--danger);">{{ number_format($openTickets) }}</div>
        </div>
        <div class="stat-icon" style="color: var(--danger);">🛠️</div>
    </div>
</div>

<!-- Operational Modules Grid -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 24px;">
    <!-- Supply Chain Card -->
    @if(Auth::user()->isAdmin() || Auth::user()->isWarehouse())
    <div class="card">
        <h3 class="card-title">📦 Supply Chain & Inventory</h3>
        <p style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 16px;">
            Hardware specifications, warehouse physical serial numbers, cost pricing, and restock purchase orders.
        </p>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <a href="{{ route('erp.inventory.index') }}" class="btn btn-primary btn-sm">Stock Catalog</a>
            <a href="#" class="btn btn-outline btn-sm">Serial Tracking</a>
            <a href="#" class="btn btn-outline btn-sm">Restock Orders</a>
        </div>
    </div>
    @endif

    <!-- Fulfillment Card -->
    <div class="card">
        <h3 class="card-title">🚀 Orders & Assembly Workflow</h3>
        <p style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 16px;">
            Order lifecycle progression: Pending &rarr; Paid &rarr; Assembling &rarr; Testing &rarr; Shipped &rarr; Completed.
        </p>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <a href="#" class="btn btn-primary btn-sm">Orders Queue</a>
            <a href="#" class="btn btn-outline btn-sm">Shipping & Tracking</a>
        </div>
    </div>

    <!-- After-sales Card -->
    @if(Auth::user()->isAdmin() || Auth::user()->isTech())
    <div class="card">
        <h3 class="card-title">🛠️ Technical Support & RMA</h3>
        <p style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 16px;">
            Warranty periods, customer repair tickets, and technician on-site / workshop scheduling calendar.
        </p>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <a href="{{ route('erp.support.index') }}" class="btn btn-primary btn-sm">Ticket Management</a>
            <a href="#" class="btn btn-outline btn-sm">Tech Schedule</a>
        </div>
    </div>
    @endif
</div>
@endsection
