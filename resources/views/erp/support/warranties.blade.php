@extends('layouts.erp')

@section('title', 'Hardware Warranty Registry')

@section('content')
<div>
    <!-- Top Action / Nav -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 12px;">
        <div>
            <h1 style="font-size: 1.6rem; font-weight: 700; margin-bottom: 4px;">Hardware Warranty Registry</h1>
            <p style="color: var(--text-muted); font-size: 0.875rem;">
                Official store & manufacturer serial coverage lookup for warranty verification and RMA validation.
            </p>
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="{{ route('erp.support.index') }}" class="btn btn-secondary btn-sm">Ticket Queue</a>
            <a href="{{ route('erp.support.schedules') }}" class="btn btn-secondary btn-sm">Workshop Schedules</a>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="filter-bar">
        <form action="{{ route('erp.support.warranties') }}" method="GET" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap; width: 100%;">
            <div style="display: flex; gap: 8px; align-items: center;">
                <label style="font-size: 0.85rem; color: var(--text-muted);">Status:</label>
                <select name="status" class="form-control form-control-sm" style="width: 150px;">
                    <option value="">-- All Statuses --</option>
                    <option value="Active" {{ $status === 'Active' ? 'selected' : '' }}>Active</option>
                    <option value="Expired" {{ $status === 'Expired' ? 'selected' : '' }}>Expired</option>
                    <option value="Void" {{ $status === 'Void' ? 'selected' : '' }}>Void</option>
                </select>
            </div>

            <div style="display: flex; gap: 8px; align-items: center; flex: 1; min-width: 260px;">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Search by Serial Number, Order #, or Customer..." value="{{ $search ?? '' }}">
            </div>

            <button type="submit" class="btn btn-primary btn-sm">Search Registry</button>
            @if($search || $status)
                <a href="{{ route('erp.support.warranties') }}" class="btn btn-secondary btn-sm">Reset</a>
            @endif
        </form>
    </div>

    <!-- Warranties Table -->
    <div class="card">
        <div class="card-header">
            <h3 style="font-size: 1.1rem; font-weight: 600;">Registered Hardware Units</h3>
            <span class="badge badge-info">{{ $warranties->total() }} Units</span>
        </div>

        @if($warranties->isEmpty())
            <div style="text-align: center; padding: 50px 20px; color: var(--text-muted);">
                <div style="font-size: 2.5rem; margin-bottom: 8px;"></div>
                <h4 style="font-weight: 600; color: var(--text-light);">No warranties match your query</h4>
                <p style="font-size: 0.875rem;">Try searching with a partial serial number (e.g. "SN-") or order number.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Serial Number</th>
                            <th>Hardware Product</th>
                            <th>Order Number</th>
                            <th>Customer</th>
                            <th>Start Date</th>
                            <th>Expiry Date</th>
                            <th>Remaining</th>
                            <th>Status</th>
                            <th class="text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($warranties as $warranty)
                            @php
                                $product = $warranty->serialNumber->product ?? null;
                                $remaining = $warranty->daysRemaining();
                            @endphp
                            <tr>
                                <td>
                                    <span class="font-mono" style="font-weight: 600; color: #60a5fa; background: rgba(255,255,255,0.06); padding: 3px 8px; border-radius: 4px;">
                                        {{ $warranty->serialNumber->sn_code ?? 'N/A' }}
                                    </span>
                                    <small style="display: block; color: var(--text-muted); margin-top: 4px;">
                                        Unit: {{ $warranty->serialNumber->status ?? 'Sold' }}
                                    </small>
                                </td>
                                <td>
                                    <div style="font-weight: 600;">{{ $product->name ?? 'Hardware' }}</div>
                                    <small style="color: var(--text-muted);">{{ $product->category->name ?? 'Component' }}</small>
                                </td>
                                <td>
                                    @if($warranty->order)
                                        <a href="{{ route('erp.orders.show', $warranty->order) }}" class="font-mono" style="color: #93c5fd; text-decoration: none;">
                                            {{ $warranty->order->order_number }}
                                        </a>
                                    @else
                                        <span style="color: var(--text-muted);">-</span>
                                    @endif
                                </td>
                                <td>
                                    <div>{{ $warranty->order->customer_name ?? ($warranty->order->customer->name ?? 'Customer') }}</div>
                                    <small style="color: var(--text-muted);">{{ $warranty->order->customer_email ?? '' }}</small>
                                </td>
                                <td>{{ $warranty->start_date ? $warranty->start_date->format('M d, Y') : '-' }}</td>
                                <td>{{ $warranty->end_date ? $warranty->end_date->format('M d, Y') : '-' }}</td>
                                <td>
                                    @if($warranty->status === 'Active' && $remaining > 0)
                                        <span style="color: #10b981; font-weight: 600;">{{ $remaining }} days</span>
                                    @else
                                        <span style="color: var(--text-muted);">0 days</span>
                                    @endif
                                </td>
                                <td>
                                    @if($warranty->status === 'Active' && !$warranty->isExpired())
                                        <span class="badge badge-success">Active</span>
                                    @else
                                        <span class="badge badge-secondary">Expired</span>
                                    @endif
                                </td>
                                <td class="text-right">
                                    @if($warranty->order)
                                        <a href="{{ route('erp.orders.show', $warranty->order) }}" class="btn btn-secondary btn-sm">
                                            View Order
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($warranties->hasPages())
                <div style="padding: 16px 24px; border-top: 1px solid var(--border-color);">
                    {{ $warranties->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
