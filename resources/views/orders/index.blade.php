@extends('layouts.app')

@section('title', 'My PC Orders History')

@section('content')
<div style="max-width: 1100px; margin: 30px auto; padding: 0 20px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 14px;">
        <div>
            <h1 style="font-size: 1.6rem; font-weight: 800; color: #fff;">
                📦 My Custom PC Orders History
            </h1>
            <p style="color: var(--text-muted); font-size: 0.85rem; margin-top: 4px;">
                Review past and active system builds, invoice receipts, and live courier tracking numbers.
            </p>
        </div>
        <div>
            <a href="{{ route('orders.track') }}" class="btn btn-outline btn-sm">
                🔍 Live Tracking Portal
            </a>
            <a href="{{ route('checkout.index') }}" class="btn btn-primary btn-sm">
                + New Order Checkout
            </a>
        </div>
    </div>

    @if($orders->isEmpty())
        <div class="card" style="text-align: center; padding: 48px 20px;">
            <div style="font-size: 3rem; margin-bottom: 12px;">🛒</div>
            <h3 style="font-size: 1.2rem; color: #fff; margin-bottom: 8px;">No Orders Placed Yet</h3>
            <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 20px;">
                You haven't checked out any custom builds yet. Ready to assemble your dream PC?
            </p>
            <a href="{{ route('checkout.index') }}" class="btn btn-primary">
                Explore Hardware Checkout &rarr;
            </a>
        </div>
    @else
        <div class="card">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Order No.</th>
                            <th>Date</th>
                            <th>Items</th>
                            <th>Total Amount</th>
                            <th>Payment</th>
                            <th>Status</th>
                            <th>Tracking</th>
                            <th style="text-align: right;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orders as $order)
                            <tr>
                                <td>
                                    <strong style="color: #fff; font-family: monospace;">{{ $order->order_number }}</strong>
                                </td>
                                <td style="font-size: 0.85rem; color: var(--text-muted);">
                                    {{ $order->created_at->format('Y-m-d H:i') }}
                                </td>
                                <td>
                                    <span class="badge badge-secondary">{{ $order->items->count() }} Part(s)</span>
                                </td>
                                <td style="font-weight: 700; color: var(--accent);">
                                    RM {{ number_format($order->total_selling_amount, 2) }}
                                </td>
                                <td>
                                    <span class="badge badge-success">{{ $order->payment?->payment_status ?? 'Pending' }}</span>
                                </td>
                                <td>
                                    <span class="badge badge-info">{{ $order->order_status }}</span>
                                </td>
                                <td style="font-size: 0.85rem; font-family: monospace; color: var(--info);">
                                    {{ $order->tracking_number ?? '—' }}
                                </td>
                                <td style="text-align: right;">
                                    <a href="{{ route('orders.track', ['query' => $order->order_number]) }}" class="btn btn-outline btn-sm">
                                        Track &rarr;
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div style="margin-top: 16px;">
                {{ $orders->links() }}
            </div>
        </div>
    @endif
</div>
@endsection
