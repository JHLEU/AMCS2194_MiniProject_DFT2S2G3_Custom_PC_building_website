@extends('layouts.app')

@section('title', 'My Hardware Warranties — GOPC')

@section('content')
<div class="container" style="padding-top: 36px; padding-bottom: 60px;">
    <!-- Breadcrumb & Nav -->
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 28px; flex-wrap: wrap; gap: 16px;">
        <div>
            <div style="display: inline-flex; align-items: center; gap: 8px; font-size: 0.8rem; color: var(--primary); font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 6px;">
                <span style="width: 6px; height: 6px; border-radius: 50%; background: var(--primary);"></span>
                Hardware Protection & Warranty Registry
            </div>
            <h1 style="font-size: 2rem; font-weight: 700; color: #fff; margin: 0; letter-spacing: -0.5px;">My Hardware Warranties</h1>
            <p style="color: var(--text-muted); font-size: 0.9rem; margin: 6px 0 0;">
                Official store and manufacturer warranty coverage tied to your purchased hardware serial numbers.
            </p>
        </div>
        <div style="display: flex; gap: 12px;">
            <a href="{{ route('customer.orders') }}" class="btn btn-outline btn-sm" style="border-color: rgba(255,255,255,0.15); padding: 8px 16px;">My Order History</a>
            <a href="{{ route('customer.support.index') }}" class="btn btn-primary btn-sm" style="padding: 8px 18px; font-weight: 600;">Support & Claims</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success" style="margin-bottom: 24px; border-radius: 8px;">
            {{ session('success') }}
        </div>
    @endif

    <div class="card" style="padding: 0; overflow: hidden; border-color: rgba(255,255,255,0.08);">
        <div class="card-header" style="padding: 18px 24px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
            <h3 style="font-size: 1.05rem; font-weight: 700; margin: 0; color: #fff;">Active Hardware Coverage</h3>
            <span class="badge badge-secondary" style="font-family: monospace; font-size: 0.75rem;">{{ $warranties->total() }} Units Registered</span>
        </div>

        @if($warranties->isEmpty())
            <div style="text-align: center; padding: 72px 24px;">
                <div style="width: 56px; height: 56px; margin: 0 auto 16px; border-radius: 12px; background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08); display: flex; align-items: center; justify-content: center; color: var(--text-muted);">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                    </svg>
                </div>
                <h4 style="font-weight: 700; color: #fff; margin-bottom: 8px; font-size: 1.1rem;">No Hardware Warranties Registered Yet</h4>
                <p style="color: var(--text-muted); max-width: 440px; margin: 0 auto 24px; font-size: 0.9rem; line-height: 1.5;">
                    When your custom PC or component orders are fulfilled, your hardware serial numbers and 3-year warranty protections will appear here automatically.
                </p>
                <a href="{{ route('customer.orders') }}" class="btn btn-primary" style="padding: 10px 24px; font-weight: 600;">Track My Orders</a>
            </div>
        @else
            <div class="table-responsive">
                <table class="table" style="margin-bottom: 0;">
                    <thead>
                        <tr>
                            <th style="padding-left: 24px;">Hardware Component</th>
                            <th>Serial Number (SN)</th>
                            <th>Origin Order</th>
                            <th>Coverage Period</th>
                            <th>Remaining Protection</th>
                            <th>Warranty State</th>
                            <th style="text-align: right; padding-right: 24px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($warranties as $warranty)
                            @php
                                $remaining = $warranty->daysRemaining();
                                $product = $warranty->serialNumber->product ?? null;
                            @endphp
                            <tr>
                                <td style="padding-left: 24px;">
                                    <div style="font-weight: 700; color: #fff; font-size: 0.95rem;">{{ $product->name ?? 'Hardware Component' }}</div>
                                    <small style="color: var(--text-muted); font-size: 0.8rem;">{{ $product->category->name ?? ($product->category ?? 'Hardware') }}</small>
                                </td>
                                <td>
                                    <span style="font-family: monospace; background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.1); padding: 4px 10px; border-radius: 6px; font-size: 0.82rem; color: #e2e8f0;">
                                        {{ $warranty->serialNumber->sn_code ?? 'N/A' }}
                                    </span>
                                </td>
                                <td>
                                    @if($warranty->order)
                                        <a href="{{ route('orders.track', ['query' => $warranty->order->order_number]) }}" style="font-family: monospace; color: var(--primary); font-weight: 600; text-decoration: none;">
                                            {{ $warranty->order->order_number }}
                                        </a>
                                    @else
                                        <span style="color: var(--text-muted); font-family: monospace;">—</span>
                                    @endif
                                </td>
                                <td>
                                    <div style="font-family: monospace; font-size: 0.85rem; color: #fff;">{{ $warranty->start_date ? $warranty->start_date->format('Y-m-d') : '—' }}</div>
                                    <small style="color: var(--text-muted); font-family: monospace; font-size: 0.75rem;">to {{ $warranty->end_date ? $warranty->end_date->format('Y-m-d') : '—' }}</small>
                                </td>
                                <td>
                                    @if($warranty->status === 'Active' && $remaining > 0)
                                        <span style="color: var(--primary); font-weight: 700; font-family: monospace; font-size: 0.9rem;">{{ $remaining }} days</span>
                                    @else
                                        <span style="color: var(--text-muted); font-family: monospace;">0 days</span>
                                    @endif
                                </td>
                                <td>
                                    @if($warranty->status === 'Active' && !$warranty->isExpired())
                                        <span class="badge badge-success" style="font-size: 0.75rem; font-family: monospace;">● ACTIVE</span>
                                    @else
                                        <span class="badge badge-secondary" style="font-size: 0.75rem; font-family: monospace;">EXPIRED</span>
                                    @endif
                                </td>
                                <td style="text-align: right; padding-right: 24px;">
                                    @if($warranty->status === 'Active' && !$warranty->isExpired())
                                        <a href="{{ route('customer.support.create', ['warranty_id' => $warranty->id]) }}" class="btn btn-outline btn-sm" style="border-color: rgba(34,197,94,0.3); color: var(--primary); padding: 5px 14px;">
                                            Claim RMA &rarr;
                                        </a>
                                    @else
                                        <a href="{{ route('customer.support.create') }}" class="btn btn-outline btn-sm" style="border-color: rgba(255,255,255,0.15); color: var(--text-muted); padding: 5px 14px;">
                                            Consult Desk
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
