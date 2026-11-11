@extends('layouts.app')

@section('title', 'Order Confirmed — GOPC')

@section('content')
<div class="container" style="max-width: 880px; padding-top: 40px; padding-bottom: 70px;">
    <!-- Confirmation Banner -->
    <div class="card" style="text-align: center; padding: 40px 28px; margin-bottom: 24px; border: 1px solid rgba(34, 197, 94, 0.4); box-shadow: 0 10px 40px rgba(34, 197, 94, 0.12); position: relative; overflow: hidden;">
        <div style="width: 56px; height: 56px; border-radius: 50%; background: rgba(34, 197, 94, 0.12); border: 2px solid var(--primary); color: var(--primary); font-size: 1.8rem; font-weight: 800; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px;">
            ✓
        </div>
        <h1 style="font-size: 2rem; font-weight: 700; color: #fff; margin-bottom: 8px; letter-spacing: -0.5px;">
            Order Placed & Payment Verified
        </h1>
        <p style="color: var(--text-muted); font-size: 0.95rem; margin-bottom: 24px; max-width: 540px; margin-left: auto; margin-right: auto;">
            Thank you, <strong style="color: #fff;">{{ $order->customer_name }}</strong>. Your custom rig order has been securely logged and assigned to our assembly workshop.
        </p>

        <div style="display: inline-flex; flex-direction: column; align-items: center; background: rgba(255,255,255,0.03); border: 1px solid var(--border-color); padding: 12px 28px; border-radius: 10px; margin-bottom: 28px;">
            <span style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Order Tracking Reference</span>
            <div style="font-size: 1.4rem; font-weight: 700; color: var(--primary); font-family: monospace; letter-spacing: 0.05em; margin-top: 2px;">
                {{ $order->order_number }}
            </div>
        </div>

        <div style="display: flex; gap: 14px; justify-content: center; flex-wrap: wrap;">
            <a href="{{ route('orders.track', ['query' => $order->order_number]) }}" class="btn btn-primary" style="padding: 12px 28px; font-weight: 600; box-shadow: 0 4px 14px rgba(34,197,94,0.3);">
                Track Assembly Staging &rarr;
            </a>
            <a href="{{ route('home') }}" class="btn btn-outline" style="padding: 12px 24px; border-color: rgba(255,255,255,0.15);">
                Return to Storefront
            </a>
        </div>
    </div>

    <!-- Receipt Details Card -->
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; border-bottom: 1px solid var(--border-color); padding-bottom: 12px;">
            <h3 style="font-size: 1.15rem; font-weight: 700; color: #fff; margin: 0;">Order Manifest & Invoice Receipt</h3>
            <span class="badge badge-success" style="font-family: monospace;">{{ $order->payment?->payment_status ?? 'Paid' }}</span>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px; margin-bottom: 24px; font-size: 0.85rem; background: rgba(255,255,255,0.02); padding: 16px; border-radius: 8px; border: 1px solid var(--border-color);">
            <div>
                <span style="color: var(--text-muted); display: block; margin-bottom: 2px;">Payment Gateway:</span>
                <div style="font-weight: 600; color: #fff;">{{ $order->payment?->payment_method ?? 'Sandbox Instant' }}</div>
            </div>
            <div>
                <span style="color: var(--text-muted); display: block; margin-bottom: 2px;">Transaction Reference:</span>
                <div style="font-weight: 600; color: var(--info); font-family: monospace;">{{ $order->payment?->transaction_id ?? 'TXN-SANDBOX' }}</div>
            </div>
            <div>
                <span style="color: var(--text-muted); display: block; margin-bottom: 2px;">Logistics Carrier:</span>
                <div style="font-weight: 600; color: #fff;">{{ $order->carrier ?? 'J&T Express Cargo' }}</div>
            </div>
            <div>
                <span style="color: var(--text-muted); display: block; margin-bottom: 2px;">Delivery Destination:</span>
                <div style="font-weight: 500; color: #fff;">{{ $order->shipping_address }}</div>
            </div>
        </div>

        <!-- Ordered Items Table -->
        <div class="table-responsive" style="margin-bottom: 20px;">
            <table class="table">
                <thead>
                    <tr>
                        <th>Hardware Component</th>
                        <th>Category</th>
                        <th style="text-align: center;">Qty</th>
                        <th style="text-align: right;">Unit Price</th>
                        <th style="text-align: right;">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                        <tr>
                            <td>
                                <strong style="color: #fff; display: block;">{{ $item->product?->name ?? 'Hardware Component' }}</strong>
                                <small style="color: var(--text-muted); font-size: 0.75rem;">Part #{{ $item->product_id }}</small>
                            </td>
                            <td>
                                <span class="badge badge-secondary" style="font-size: 0.75rem;">{{ $item->product?->category ?? 'Part' }}</span>
                            </td>
                            <td style="text-align: center; font-family: monospace;">
                                {{ $item->quantity }}
                            </td>
                            <td style="text-align: right; font-family: monospace; color: var(--text-light);">
                                RM {{ number_format($item->unit_price, 2) }}
                            </td>
                            <td style="text-align: right; font-weight: 600; font-family: monospace; color: #fff;">
                                RM {{ number_format($item->subtotal, 2) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4" style="text-align: right; font-weight: 600; font-size: 0.95rem; color: #fff; padding-top: 14px;">
                            Grand Total Settled:
                        </td>
                        <td style="text-align: right; font-weight: 800; font-size: 1.35rem; color: var(--primary); font-family: monospace; padding-top: 14px;">
                            RM {{ number_format($order->total_selling_amount, 2) }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        @if($order->serialNumbers->isNotEmpty())
            <div style="background: rgba(255,255,255,0.02); border: 1px solid var(--border-color); border-radius: 8px; padding: 14px 18px;">
                <span style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700; letter-spacing: 0.05em;">
                    Allocated Hardware Serial Numbers:
                </span>
                <div style="display: flex; gap: 8px; flex-wrap: wrap; margin-top: 8px;">
                    @foreach($order->serialNumbers as $sn)
                        <span class="badge badge-secondary" style="font-family: monospace; font-size: 0.75rem; border-color: rgba(34,197,94,0.3); color: var(--primary);">
                            {{ $sn->sn_code }} ({{ $sn->status }})
                        </span>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
