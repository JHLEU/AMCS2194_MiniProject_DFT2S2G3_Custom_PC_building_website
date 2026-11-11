@extends('layouts.erp')

@section('title', 'Orders Management')
@section('page-title', 'Sales & Assembly Orders Queue')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
    <div>
        <h1 style="font-size: 1.5rem; font-weight: 700; color: #fff; margin-bottom: 4px;">
            Custom PC Orders Queue
        </h1>
        <p style="color: var(--text-muted); font-size: 0.9rem;">
            Track customer orders across assembly pipeline: Paid &rarr; Assembling &rarr; Testing &rarr; Shipped &rarr; Completed.
        </p>
    </div>
    <div>
        <a href="{{ route('checkout.index') }}" class="btn btn-outline btn-sm" target="_blank">
            + New Sandbox Order
        </a>
    </div>
</div>

<!-- High-level KPI Cards -->
<div class="stat-grid" style="margin-bottom: 24px;">
    <div class="stat-card">
        <div>
            <div class="stat-label">Total Orders</div>
            <div class="stat-val">{{ number_format($metrics['total']) }}</div>
        </div>
        <div class="stat-icon" style="color: var(--info);"></div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-label">Paid (Awaiting Assembly)</div>
            <div class="stat-val" style="color: var(--accent);">{{ number_format($metrics['paid']) }}</div>
        </div>
        <div class="stat-icon" style="color: var(--accent);"></div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-label">In Bench Assembly</div>
            <div class="stat-val" style="color: var(--warning);">{{ number_format($metrics['assembling']) }}</div>
        </div>
        <div class="stat-icon" style="color: var(--warning);"></div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-label">In Testing / QC</div>
            <div class="stat-val" style="color: #c084fc;">{{ number_format($metrics['testing']) }}</div>
        </div>
        <div class="stat-icon" style="color: #c084fc;"></div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-label">Dispatched / Shipped</div>
            <div class="stat-val" style="color: var(--success);">{{ number_format($metrics['shipped']) }}</div>
        </div>
        <div class="stat-icon" style="color: var(--success);"></div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-label">Total Valid Revenue</div>
            <div class="stat-val" style="color: #34d399; font-size: 1.25rem;">RM {{ number_format($metrics['revenue'], 2) }}</div>
        </div>
        <div class="stat-icon" style="color: #34d399;"></div>
    </div>
</div>

<!-- Filters Bar -->
<div class="card" style="margin-bottom: 20px; padding: 16px;">
    <form method="GET" action="{{ route('erp.orders.index') }}" style="display: flex; gap: 14px; align-items: center; flex-wrap: wrap;">
        <div style="flex: 1; min-width: 240px;">
            <input type="text" name="search" class="form-input"
                   placeholder="Search by Order #, Customer Name, Phone, Email, Tracking..."
                   value="{{ $search }}">
        </div>

        <div style="min-width: 180px;">
            <select name="status" class="form-input" onchange="this.form.submit()">
                <option value="">— All Statuses —</option>
                @foreach(\App\Models\Order::STATUSES as $st)
                    <option value="{{ $st }}" {{ $status === $st ? 'selected' : '' }}>
                        {{ $st }}
                    </option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="btn btn-primary btn-sm">Filter</button>
        @if($status || $search)
            <a href="{{ route('erp.orders.index') }}" class="btn btn-outline btn-sm">Reset</a>
        @endif
    </form>
</div>

<!-- Orders Table -->
<div class="card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Order Ref</th>
                    <th>Date</th>
                    <th>Customer</th>
                    <th>Items</th>
                    <th>Selling Amount</th>
                    <th>Est. Hardware Cost</th>
                    <th>Gross Profit</th>
                    <th>Status</th>
                    <th>Courier Consignment</th>
                    <th style="text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                    <tr>
                        <td>
                            <a href="{{ route('erp.orders.show', $order) }}" style="font-weight: 700; color: #fff; font-family: monospace; text-decoration: none;">
                                {{ $order->order_number }}
                            </a>
                        </td>
                        <td style="font-size: 0.8rem; color: var(--text-muted); white-space: nowrap;">
                            {{ $order->created_at->format('Y-m-d H:i') }}
                        </td>
                        <td>
                            <div style="font-weight: 600; color: #fff;">{{ $order->customer_name }}</div>
                            <div style="font-size: 0.75rem; color: var(--text-muted);">{{ $order->customer_phone }}</div>
                        </td>
                        <td>
                            <span class="badge badge-secondary">{{ $order->items->count() }} Part(s)</span>
                        </td>
                        <td style="font-weight: 700; color: var(--accent);">
                            RM {{ number_format($order->total_selling_amount, 2) }}
                        </td>
                        <td style="font-size: 0.85rem; color: var(--text-muted);">
                            RM {{ number_format($order->total_cost_amount, 2) }}
                        </td>
                        <td>
                            @php
                                $profit = $order->gross_profit;
                            @endphp
                            <span style="font-weight: 700; color: {{ $profit >= 0 ? 'var(--success)' : 'var(--danger)' }};">
                                RM {{ number_format($profit, 2) }}
                            </span>
                        </td>
                        <td>
                            <span class="badge {{ $order->status_badge_class }}">
                                {{ $order->order_status }}
                            </span>
                        </td>
                        <td style="font-size: 0.8rem; font-family: monospace; color: var(--info);">
                            {{ $order->tracking_number ?? '—' }}
                        </td>
                        <td style="text-align: right;">
                            <a href="{{ route('erp.orders.show', $order) }}" class="btn btn-outline btn-sm">
                                View & Process &rarr;
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" style="text-align: center; padding: 32px; color: var(--text-muted);">
                            No orders found matching the filter criteria.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top: 16px;">
        {{ $orders->links() }}
    </div>
</div>
@endsection
