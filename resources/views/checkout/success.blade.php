@extends('layouts.app')

@section('title', 'Order Placed Successfully')

@section('content')
<div style="max-width: 800px; margin: 40px auto; padding: 0 20px;">
    <!-- Confirmation Banner -->
    <div class="card" style="text-align: center; padding: 36px 24px; margin-bottom: 24px; border: 1px solid rgba(16, 185, 129, 0.4); box-shadow: 0 0 25px rgba(16, 185, 129, 0.15);">
        <div style="font-size: 3.5rem; margin-bottom: 12px;">🎉</div>
        <h1 style="font-size: 1.8rem; font-weight: 800; color: #fff; margin-bottom: 6px;">
            Order Placed & Payment Verified!
        </h1>
        <p style="color: var(--text-muted); font-size: 0.95rem; margin-bottom: 20px;">
            Thank you, <strong style="color: #fff;">{{ $order->customer_name }}</strong>. Your order is confirmed and has been routed to our certified technicians.
        </p>

        <div style="display: inline-flex; align-items: center; gap: 12px; background: rgba(255,255,255,0.04); border: 1px solid var(--border-color); padding: 10px 20px; border-radius: 8px; margin-bottom: 20px;">
            <div>
                <span style="font-size: 0.8rem; color: var(--text-muted);">Order Reference Number:</span>
                <div style="font-size: 1.25rem; font-weight: 800; color: var(--accent); letter-spacing: 0.05em;">
                    {{ $order->order_number }}
                </div>
            </div>
        </div>

        <div style="display: flex; gap: 12px; justify-content: center; flex-wrap: wrap;">
            <a href="{{ route('orders.track', ['query' => $order->order_number]) }}" class="btn btn-primary">
                📍 Track Order Assembly Milestone &rarr;
            </a>
            <a href="{{ route('home') }}" class="btn btn-outline">
                Return to Storefront
            </a>
        </div>
    </div>

    <!-- Receipt Details Card -->
    <div class="card">
        <h3 class="card-title" style="margin-bottom: 16px;">🧾 Official Order Receipt Summary</h3>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 20px; font-size: 0.85rem;">
            <div>
                <span style="color: var(--text-muted);">Payment Method:</span>
                <div style="font-weight: 700; color: #fff;">{{ $order->payment?->payment_method ?? 'Sandbox Instant' }}</div>
            </div>
            <div>
                <span style="color: var(--text-muted);">Transaction ID:</span>
                <div style="font-weight: 700; color: var(--info);">{{ $order->payment?->transaction_id ?? 'TXN-SANDBOX' }}</div>
            </div>
            <div>
                <span style="color: var(--text-muted);">Payment Status:</span>
                <div>
                    <span class="badge badge-success">{{ $order->payment?->payment_status ?? 'Success' }}</span>
                </div>
            </div>
            <div>
                <span style="color: var(--text-muted);">Delivery Carrier:</span>
                <div style="font-weight: 700; color: #fff;">{{ $order->carrier ?? 'J&T Express Cargo' }}</div>
            </div>
        </div>

        <div style="margin-bottom: 20px; font-size: 0.85rem;">
            <span style="color: var(--text-muted);">Shipping Address:</span>
            <div style="font-weight: 600; color: #fff; margin-top: 4px;">{{ $order->shipping_address }}</div>
        </div>

        <!-- Ordered Items Table -->
        <div class="table-responsive" style="margin-bottom: 16px;">
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
                            <td style="font-weight: 600;">{{ $item->product?->name ?? 'Hardware Component' }}</td>
                            <td><span class="badge badge-info">{{ $item->product?->category ?? 'Part' }}</span></td>
                            <td style="text-align: center;">{{ $item->quantity }}</td>
                            <td style="text-align: right;">RM {{ number_format($item->unit_price, 2) }}</td>
                            <td style="text-align: right; font-weight: 700; color: #fff;">
                                RM {{ number_format($item->subtotal, 2) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4" style="text-align: right; font-weight: 700; font-size: 1rem; color: #fff;">
                            Grand Total Settled:
                        </td>
                        <td style="text-align: right; font-weight: 800; font-size: 1.15rem; color: var(--accent);">
                            RM {{ number_format($order->total_selling_amount, 2) }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        @if($order->serialNumbers->isNotEmpty())
            <div style="background: rgba(255,255,255,0.02); border: 1px solid var(--border-color); border-radius: 8px; padding: 12px 16px;">
                <span style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">
                    🏷️ Allocated Warehouse Serial Numbers:
                </span>
                <div style="display: flex; gap: 8px; flex-wrap: wrap; margin-top: 6px;">
                    @foreach($order->serialNumbers as $sn)
                        <span class="badge badge-secondary" style="font-family: monospace;">
                            {{ $sn->sn_code }} ({{ $sn->status }})
                        </span>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
