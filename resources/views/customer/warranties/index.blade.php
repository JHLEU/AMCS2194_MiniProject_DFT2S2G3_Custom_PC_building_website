@extends('layouts.app')

@section('title', 'My Hardware Warranties')

@section('content')
<div class="container" style="padding-top: 40px; padding-bottom: 60px;">
    <!-- Breadcrumb & Nav -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 12px;">
        <div>
            <h1 style="font-size: 1.8rem; font-weight: 700; margin-bottom: 6px;">🛡️ My Hardware Warranties</h1>
            <p style="color: var(--text-muted); font-size: 0.95rem;">
                Official store & manufacturer warranty coverage registered to your purchased hardware.
            </p>
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="{{ route('customer.orders') }}" class="btn btn-secondary">📦 My Orders</a>
            <a href="{{ route('customer.support.index') }}" class="btn btn-primary">🛠️ Support Tickets</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success" style="margin-bottom: 20px;">
            {{ session('success') }}
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h3 style="font-size: 1.1rem; font-weight: 600;">Active Hardware Coverage</h3>
            <span class="badge badge-info">{{ $warranties->total() }} Units Registered</span>
        </div>

        @if($warranties->isEmpty())
            <div style="text-align: center; padding: 60px 20px;">
                <div style="font-size: 3rem; margin-bottom: 12px;">🛡️</div>
                <h4 style="font-weight: 600; margin-bottom: 8px;">No hardware warranties registered yet</h4>
                <p style="color: var(--text-muted); max-width: 450px; margin: 0 auto 20px;">
                    When your custom PC or component orders are fulfilled, your hardware serial numbers and 2-year warranty protections will appear here automatically.
                </p>
                <a href="{{ route('customer.orders') }}" class="btn btn-primary">Track My Orders</a>
            </div>
        @else
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Hardware Product</th>
                            <th>Serial Number</th>
                            <th>Order #</th>
                            <th>Coverage Period</th>
                            <th>Remaining</th>
                            <th>Status</th>
                            <th class="text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($warranties as $warranty)
                            @php
                                $remaining = $warranty->daysRemaining();
                                $product = $warranty->serialNumber->product ?? null;
                            @endphp
                            <tr>
                                <td>
                                    <div style="font-weight: 600;">{{ $product->name ?? 'Hardware Component' }}</div>
                                    <small style="color: var(--text-muted);">{{ $product->category->name ?? 'Hardware' }}</small>
                                </td>
                                <td>
                                    <span class="font-mono" style="background: rgba(255,255,255,0.06); padding: 3px 8px; border-radius: 4px;">
                                        {{ $warranty->serialNumber->serial_number ?? 'N/A' }}
                                    </span>
                                </td>
                                <td>
                                    @if($warranty->order)
                                        <span class="font-mono">{{ $warranty->order->order_number }}</span>
                                    @else
                                        <span style="color: var(--text-muted);">-</span>
                                    @endif
                                </td>
                                <td>
                                    <div>{{ $warranty->start_date ? $warranty->start_date->format('M d, Y') : '-' }}</div>
                                    <small style="color: var(--text-muted);">to {{ $warranty->end_date ? $warranty->end_date->format('M d, Y') : '-' }}</small>
                                </td>
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
                                    @if($warranty->status === 'Active' && !$warranty->isExpired())
                                        <a href="{{ route('customer.support.create', ['warranty_id' => $warranty->id]) }}" class="btn btn-secondary btn-sm" style="color: #60a5fa;">
                                            Claim RMA
                                        </a>
                                    @else
                                        <a href="{{ route('customer.support.create') }}" class="btn btn-secondary btn-sm">
                                            Request Help
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
