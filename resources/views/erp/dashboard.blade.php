@extends('layouts.erp')

@section('title', 'Operations Dashboard')
@section('page-title', 'Operations Overview')

@section('content')
<div style="margin-bottom: 24px; display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 12px;">
    <div>
        <h1 style="font-size: 1.375rem; font-weight: 600; color: var(--text-primary); letter-spacing: -0.01em; margin: 0 0 4px 0;">
            Operations metrics
        </h1>
        <p style="color: var(--text-muted); font-size: 0.8125rem; margin: 0;">
            Logged in as <strong style="color: var(--text-primary);">{{ $user->name ?? $user->username }}</strong> ({{ $user->role->name ?? 'Staff' }})
        </p>
    </div>
    <div style="display: flex; align-items: center; gap: 8px;">
        <span style="display: inline-block; width: 6px; height: 6px; border-radius: 50%; background: var(--primary);"></span>
        <span class="font-mono" style="font-size: 0.75rem; color: var(--text-secondary);">Core system operational</span>
    </div>
</div>

<!-- High-level Metric Stat Cards -->
<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-label">Active hardware models</div>
        <div class="stat-val">{{ number_format($totalProducts) }}</div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Low inventory alerts</div>
        <div class="stat-val" style="color: var(--warning);">{{ number_format($lowStockCount) }}</div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Restock orders pending</div>
        <div class="stat-val">{{ number_format($pendingRestocks) }}</div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Active assembly orders</div>
        <div class="stat-val" style="color: var(--primary);">{{ number_format($activeOrders) }}</div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Gross revenue</div>
        <div class="stat-val">RM {{ number_format($totalRevenue, 2) }}</div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Open tech support tickets</div>
        <div class="stat-val" style="color: var(--danger);">{{ number_format($openTickets) }}</div>
    </div>
</div>

<!-- Operational Modules Grid -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px;">
    <!-- Supply Chain Card -->
    @if(Auth::user()->isAdmin() || Auth::user()->isWarehouse())
    <div class="card" style="margin-bottom: 0;">
        <h3 class="card-title">Warehouse &amp; Supply Chain</h3>
        <p style="color: var(--text-secondary); font-size: 0.8125rem; margin-bottom: 16px; line-height: 1.5;">
            Manage component specifications, supplier cost price tiers, serialized warehouse tracking, and incoming purchase restocks.
        </p>
        <div style="display: flex; gap: 8px; flex-wrap: wrap;">
            <a href="{{ route('erp.products.index') }}" class="btn btn-secondary btn-sm">Hardware catalog</a>
            <a href="{{ route('erp.serials.index') }}" class="btn btn-outline btn-sm">Serialized stock</a>
            <a href="{{ route('erp.restock.index') }}" class="btn btn-outline btn-sm">Restock orders</a>
            <a href="{{ route('erp.inventory.low-stock') }}" class="btn btn-outline btn-sm">Low stock</a>
        </div>
    </div>
    @endif

    <!-- Fulfillment Card -->
    <div class="card" style="margin-bottom: 0;">
        <h3 class="card-title">Order Processing &amp; Assembly</h3>
        <p style="color: var(--text-secondary); font-size: 0.8125rem; margin-bottom: 16px; line-height: 1.5;">
            Track the end-to-end assembly pipeline: component staging, bench stress-testing, packaging, and courier tracking updates.
        </p>
        <div style="display: flex; gap: 8px; flex-wrap: wrap;">
            <a href="{{ route('erp.orders.index') }}" class="btn btn-primary btn-sm">Assembly queue</a>
            <a href="{{ route('erp.compatibility.index') }}" class="btn btn-outline btn-sm">Compatibility evaluator</a>
        </div>
    </div>

    <!-- After-sales Card -->
    @if(Auth::user()->isAdmin() || Auth::user()->isTech())
    <div class="card" style="margin-bottom: 0;">
        <h3 class="card-title">Workshop Support &amp; RMA</h3>
        <p style="color: var(--text-secondary); font-size: 0.8125rem; margin-bottom: 16px; line-height: 1.5;">
            Distributor warranty validation, customer repair intake, on-site technician scheduling, and diagnostic reporting.
        </p>
        <div style="display: flex; gap: 8px; flex-wrap: wrap;">
            <a href="{{ route('erp.support.index') }}" class="btn btn-secondary btn-sm">RMA tickets</a>
            <a href="{{ route('erp.support.schedules') }}" class="btn btn-outline btn-sm">Bench schedule</a>
        </div>
    </div>
    @endif
</div>
@endsection
