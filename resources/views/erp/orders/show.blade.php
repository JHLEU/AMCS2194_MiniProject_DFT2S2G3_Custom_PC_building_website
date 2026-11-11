@extends('layouts.erp')

@section('title', 'Order ' . $order->order_number)
@section('page-title', 'Order ' . $order->order_number)

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
    <div>
        <div style="display: flex; align-items: center; gap: 10px;">
            <a href="{{ route('erp.orders.index') }}" class="btn btn-outline btn-sm">&larr; Back to Orders Queue</a>
            <span class="badge {{ $order->status_badge_class }}" style="font-size: 0.9rem; padding: 6px 12px;">
                ● {{ $order->order_status }}
            </span>
        </div>
        <h1 style="font-size: 1.6rem; font-weight: 800; color: #fff; margin-top: 6px;">
            Order #{{ $order->order_number }}
        </h1>
        <p style="color: var(--text-muted); font-size: 0.85rem;">
            Placed on {{ $order->created_at->format('F d, Y \a\t H:i') }} &bull; Customer: <strong style="color: #fff;">{{ $order->customer_name }}</strong>
        </p>
    </div>
    <div style="display: flex; gap: 10px;">
        <a href="{{ route('orders.track', ['query' => $order->order_number]) }}" class="btn btn-outline btn-sm" target="_blank">
            View Public Tracker
        </a>
    </div>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px; align-items: start;">
    <!-- Left Column: Items, Serials, Financials -->
    <div style="display: flex; flex-direction: column; gap: 24px;">
        <!-- Ordered Items Table -->
        <div class="card">
            <h3 class="card-title" style="margin-bottom: 16px;">Hardware Components Manifest</h3>

            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Hardware Product</th>
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
                                    <div style="font-weight: 600; color: #fff;">{{ $item->product?->name ?? 'Hardware Part' }}</div>
                                    <div style="font-size: 0.75rem; color: var(--text-muted);">
                                        {{ $item->product?->specs }}
                                    </div>
                                </td>
                                <td><span class="badge badge-info">{{ $item->product?->category }}</span></td>
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
                            <td colspan="4" style="text-align: right; font-weight: 700; color: #fff;">Selling Price Total:</td>
                            <td style="text-align: right; font-weight: 800; font-size: 1.15rem; color: var(--accent);">
                                RM {{ number_format($order->total_selling_amount, 2) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Physical Serial Numbers Allocated -->
        <div class="card">
            <h3 class="card-title" style="margin-bottom: 14px;">Allocated Physical Serial Numbers</h3>
            <p style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 14px;">
                Physical units allocated from warehouse stock. Marked <strong>Reserved</strong> while in production, and automatically updated to <strong>Sold</strong> once order is completed.
            </p>

            @if($order->serialNumbers->isEmpty())
                <div class="alert alert-warning" style="font-size: 0.85rem;">
                    No physical serial numbers currently tied to this order. (They will be automatically allocated upon payment confirmation).
                </div>
            @else
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Serial Number (SN)</th>
                                <th>Hardware Product</th>
                                <th>Status</th>
                                <th style="text-align: right;">Batch Unit Cost</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->serialNumbers as $sn)
                                <tr>
                                    <td style="font-family: monospace; font-weight: 700; color: #fff;">
                                        {{ $sn->sn_code }}
                                    </td>
                                    <td>{{ $sn->product?->name }}</td>
                                    <td>
                                        <span class="badge {{ $sn->status === 'Sold' ? 'badge-success' : 'badge-primary' }}">
                                            {{ $sn->status }}
                                        </span>
                                    </td>
                                    <td style="text-align: right; font-family: monospace;">
                                        RM {{ number_format($sn->batch_cost, 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <!-- Profit & Loss Summary -->
        <div class="card">
            <h3 class="card-title" style="margin-bottom: 14px;">Financials & Margin Breakdown</h3>
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; text-align: center;">
                <div style="background: rgba(255,255,255,0.02); padding: 14px; border-radius: 8px;">
                    <div style="font-size: 0.75rem; color: var(--text-muted);">Total Selling Revenue</div>
                    <div style="font-size: 1.35rem; font-weight: 800; color: var(--accent); margin-top: 4px;">
                        RM {{ number_format($order->total_selling_amount, 2) }}
                    </div>
                </div>

                <div style="background: rgba(255,255,255,0.02); padding: 14px; border-radius: 8px;">
                    <div style="font-size: 0.75rem; color: var(--text-muted);">Allocated Hardware Cost</div>
                    <div style="font-size: 1.35rem; font-weight: 800; color: var(--text-muted); margin-top: 4px;">
                        RM {{ number_format($order->total_cost_amount, 2) }}
                    </div>
                </div>

                <div style="background: rgba(255,255,255,0.02); padding: 14px; border-radius: 8px;">
                    <div style="font-size: 0.75rem; color: var(--text-muted);">Gross Net Margin</div>
                    @php
                        $margin = $order->gross_profit;
                        $pct = $order->total_selling_amount > 0 ? round(($margin / $order->total_selling_amount) * 100, 1) : 0;
                    @endphp
                    <div style="font-size: 1.35rem; font-weight: 800; color: var(--success); margin-top: 4px;">
                        RM {{ number_format($margin, 2) }} ({{ $pct }}%)
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Status Actions, Customer & Logistics -->
    <div style="display: flex; flex-direction: column; gap: 24px;">
        <!-- Workflow Actions Card -->
        <div class="card" style="border: 1px solid rgba(0, 242, 254, 0.25);">
            <h3 class="card-title" style="margin-bottom: 14px;">Order Progression Actions</h3>

            <div style="display: flex; flex-direction: column; gap: 10px;">
                @if($order->order_status === \App\Models\Order::STATUS_PENDING)
                    <form action="{{ route('erp.orders.status', $order) }}" method="POST">
                        @csrf
                        <input type="hidden" name="order_status" value="{{ \App\Models\Order::STATUS_PAID }}">
                        <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">
                            Confirm Payment & Allocate Serials
                        </button>
                    </form>
                @endif

                @if($order->order_status === \App\Models\Order::STATUS_PAID)
                    <form action="{{ route('erp.orders.status', $order) }}" method="POST">
                        @csrf
                        <input type="hidden" name="order_status" value="{{ \App\Models\Order::STATUS_ASSEMBLING }}">
                        <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; background: #fbbf24; color: #000;">
                            Start Bench Assembly & Cable Routing &rarr;
                        </button>
                    </form>
                @endif

                @if($order->order_status === \App\Models\Order::STATUS_ASSEMBLING)
                    <form action="{{ route('erp.orders.status', $order) }}" method="POST">
                        @csrf
                        <input type="hidden" name="order_status" value="{{ \App\Models\Order::STATUS_TESTING }}">
                        <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; background: #c084fc; color: #000;">
                            Move to QC, Thermal & BIOS Testing &rarr;
                        </button>
                    </form>
                @endif

                @if($order->order_status === \App\Models\Order::STATUS_TESTING)
                    <form action="{{ route('erp.orders.status', $order) }}" method="POST">
                        @csrf
                        <input type="hidden" name="order_status" value="{{ \App\Models\Order::STATUS_SHIPPED }}">
                        <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; background: #10b981; color: #fff;">
                            Hand Over to Courier & Dispatch &rarr;
                        </button>
                    </form>
                @endif

                @if($order->order_status === \App\Models\Order::STATUS_SHIPPED)
                    <form action="{{ route('erp.orders.status', $order) }}" method="POST">
                        @csrf
                        <input type="hidden" name="order_status" value="{{ \App\Models\Order::STATUS_COMPLETED }}">
                        <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; background: #34d399; color: #000;">
                            Mark Delivered & Sold &rarr;
                        </button>
                    </form>
                @endif

                @if(!in_array($order->order_status, [\App\Models\Order::STATUS_COMPLETED, \App\Models\Order::STATUS_CANCELLED], true))
                    <form action="{{ route('erp.orders.status', $order) }}" method="POST" onsubmit="return confirm('Cancel order? Any reserved serial numbers will be released back to warehouse stock.');">
                        @csrf
                        <input type="hidden" name="order_status" value="{{ \App\Models\Order::STATUS_CANCELLED }}">
                        <button type="submit" class="btn btn-outline" style="width: 100%; justify-content: center; color: var(--danger); border-color: rgba(255,51,75,0.3); margin-top: 6px;">
                            Cancel Order & Release Stock
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <!-- Logistics & Courier Dispatch Card -->
        <div class="card">
            <h3 class="card-title" style="margin-bottom: 14px;">Courier Consignment</h3>

            <form action="{{ route('erp.orders.shipping', $order) }}" method="POST">
                @csrf
                <div class="form-group" style="margin-bottom: 12px;">
                    <label for="carrier">Courier Carrier</label>
                    <input type="text" name="carrier" id="carrier" class="form-input"
                           value="{{ old('carrier', $order->carrier ?? 'J&T Express Cargo') }}" required>
                </div>

                <div class="form-group" style="margin-bottom: 14px;">
                    <label for="tracking_number">Tracking Consignment No.</label>
                    <input type="text" name="tracking_number" id="tracking_number" class="form-input"
                           value="{{ old('tracking_number', $order->tracking_number ?? '') }}" required>
                </div>

                <button type="submit" class="btn btn-outline btn-sm" style="width: 100%; justify-content: center;">
                    Update Tracking Number
                </button>
            </form>
        </div>

        <!-- Customer & Delivery Card -->
        <div class="card">
            <h3 class="card-title" style="margin-bottom: 14px;">Customer & Address</h3>

            <div style="font-size: 0.85rem; display: flex; flex-direction: column; gap: 8px;">
                <div>
                    <span style="color: var(--text-muted);">Customer Name:</span>
                    <div style="font-weight: 700; color: #fff;">{{ $order->customer_name }}</div>
                </div>
                <div>
                    <span style="color: var(--text-muted);">Phone:</span>
                    <div style="font-weight: 600; color: #fff;">{{ $order->customer_phone }}</div>
                </div>
                <div>
                    <span style="color: var(--text-muted);">Email:</span>
                    <div style="font-weight: 600; color: #fff;">{{ $order->customer_email }}</div>
                </div>
                <div>
                    <span style="color: var(--text-muted);">Shipping Address:</span>
                    <div style="font-weight: 600; color: #fff; margin-top: 2px;">{{ $order->shipping_address }}</div>
                </div>
            </div>
        </div>

        <!-- Payment Record Card -->
        <div class="card">
            <h3 class="card-title" style="margin-bottom: 14px;">Payment Record</h3>

            <div style="font-size: 0.85rem; display: flex; flex-direction: column; gap: 8px;">
                <div>
                    <span style="color: var(--text-muted);">Method:</span>
                    <div style="font-weight: 700; color: #fff;">{{ $order->payment?->payment_method ?? 'Sandbox Instant' }}</div>
                </div>
                <div>
                    <span style="color: var(--text-muted);">Transaction ID:</span>
                    <div style="font-family: monospace; font-weight: 700; color: var(--info);">
                        {{ $order->payment?->transaction_id ?? 'Pending' }}
                    </div>
                </div>
                <div>
                    <span style="color: var(--text-muted);">Status:</span>
                    <div>
                        <span class="badge {{ $order->payment?->payment_status === 'Success' ? 'badge-success' : 'badge-warning' }}">
                            {{ $order->payment?->payment_status ?? 'Pending' }}
                        </span>
                    </div>
                </div>
                <div>
                    <span style="color: var(--text-muted);">Timestamp:</span>
                    <div style="color: var(--text-muted);">
                        {{ $order->payment?->payment_date ? $order->payment->payment_date->format('Y-m-d H:i:s') : 'Pending' }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
