@extends('layouts.app')

@section('title', 'My Build Orders — GOPC')

@section('content')
<div class="container" style="max-width: 1140px; padding-top: 36px; padding-bottom: 60px;">
    <!-- Page Header & Action Bar -->
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 28px; flex-wrap: wrap; gap: 16px;">
        <div>
            <div style="display: inline-flex; align-items: center; gap: 8px; font-size: 0.8rem; color: var(--primary); font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 6px;">
                <span style="width: 6px; height: 6px; border-radius: 50%; background: var(--primary);"></span>
                Client Order Management
            </div>
            <h1 style="font-size: 2rem; font-weight: 700; color: #fff; margin: 0; letter-spacing: -0.5px;">
                My Custom PC Orders History
            </h1>
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="{{ route('orders.track') }}" class="btn btn-outline btn-sm" style="border-color: rgba(255,255,255,0.15);">
                Track by Serial / Phone
            </a>
            <a href="{{ route('builder.index') }}" class="btn btn-primary btn-sm" style="box-shadow: 0 4px 12px rgba(34,197,94,0.25);">
                + Start New Build
            </a>
        </div>
    </div>

    @if($orders->isEmpty())
        <div class="card" style="text-align: center; padding: 60px 24px; border: 1px dashed var(--border-color);">
            <div style="width: 52px; height: 52px; border-radius: 50%; background: rgba(255,255,255,0.03); border: 1px solid var(--border-color); display: flex; align-items: center; justify-content: center; margin: 0 auto 16px; color: var(--text-muted); font-size: 1.5rem;">
                0
            </div>
            <h3 style="font-size: 1.25rem; font-weight: 700; color: #fff; margin-bottom: 8px;">No Build Orders Placed Yet</h3>
            <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 24px; max-width: 440px; margin-left: auto; margin-right: auto;">
                You have not checked out any custom builds yet. Ready to design your dream gaming workstation?
            </p>
            <a href="{{ route('builder.index') }}" class="btn btn-primary" style="padding: 12px 28px; font-weight: 600;">
                Launch Custom PC Builder &rarr;
            </a>
        </div>
    @else
        <div class="card" style="padding: 0; overflow: hidden; border-color: rgba(255,255,255,0.08);">
            <div class="table-responsive">
                <table class="table" style="margin-bottom: 0;">
                    <thead>
                        <tr>
                            <th style="padding-left: 24px;">Order Number</th>
                            <th>Placement Date</th>
                            <th>Hardware Items</th>
                            <th>Total Settlement</th>
                            <th>Payment State</th>
                            <th>Assembly Workflow</th>
                            <th>Logistics AWB</th>
                            <th style="text-align: right; padding-right: 24px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orders as $order)
                            <tr>
                                <td style="padding-left: 24px;">
                                    <strong style="color: #fff; font-family: monospace; font-size: 0.95rem;">{{ $order->order_number }}</strong>
                                </td>
                                <td style="font-size: 0.85rem; color: var(--text-muted); font-family: monospace;">
                                    {{ $order->created_at->format('Y-m-d H:i') }}
                                </td>
                                <td>
                                    <span class="badge badge-secondary" style="font-size: 0.75rem;">{{ $order->items->count() }} Component(s)</span>
                                </td>
                                <td style="font-weight: 700; color: var(--primary); font-family: monospace; font-size: 0.95rem;">
                                    RM {{ number_format($order->total_selling_amount, 2) }}
                                </td>
                                <td>
                                    <span class="badge badge-success" style="font-size: 0.75rem;">{{ $order->payment?->payment_status ?? 'Paid' }}</span>
                                </td>
                                <td>
                                    <span class="badge badge-info" style="font-size: 0.75rem; font-family: monospace;">● {{ $order->order_status }}</span>
                                </td>
                                <td style="font-size: 0.85rem; font-family: monospace; color: var(--text-light);">
                                    {{ $order->tracking_number ?? 'In Warehouse' }}
                                </td>
                                <td style="text-align: right; padding-right: 24px;">
                                    <a href="{{ route('orders.track', ['query' => $order->order_number]) }}" class="btn btn-outline btn-sm" style="border-color: rgba(34,197,94,0.3); color: var(--primary); padding: 5px 14px;">
                                        Track Progress &rarr;
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection
