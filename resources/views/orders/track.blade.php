@extends('layouts.app')

@section('title', 'Live Assembly & Delivery Tracking — GOPC')

@section('content')
<div class="container" style="max-width: 1040px; padding-top: 36px; padding-bottom: 60px;">
    <!-- Search Section -->
    <div class="card" style="margin-bottom: 24px; border: 1px solid rgba(34, 197, 94, 0.25); box-shadow: 0 10px 30px rgba(0,0,0,0.4);">
        <div style="display: inline-flex; align-items: center; gap: 8px; font-size: 0.8rem; color: var(--primary); font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 6px;">
            <span style="width: 6px; height: 6px; border-radius: 50%; background: var(--primary);"></span>
            Hardware Workshop Telemetry
        </div>
        <h1 style="font-size: 1.8rem; font-weight: 700; color: #fff; margin: 0 0 8px; letter-spacing: -0.5px;">
            Live Build Assembly & Logistics Staging
        </h1>
        <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 20px;">
            Enter your official Order Number (e.g. <code>ORD-20261102-001</code>) or phone number to view hardware staging, 24-hr stress test telemetry, and courier dispatch status.
        </p>

        <form action="{{ route('orders.track') }}" method="GET" style="display: flex; gap: 12px; flex-wrap: wrap;">
            <input type="text" name="query" class="form-input" style="flex: 1; min-width: 260px; padding: 12px 16px; font-family: monospace;"
                   placeholder="Enter Order Reference Number or Contact Phone..."
                   value="{{ $query }}" required>
            <button type="submit" class="btn btn-primary" style="padding: 12px 28px; font-weight: 600; box-shadow: 0 4px 14px rgba(34,197,94,0.3);">
                Query Telemetry &rarr;
            </button>
        </form>
    </div>

    @if($searched && !$trackedOrder)
        <div class="alert alert-danger" style="margin-bottom: 24px;">
            No active or archived build order matches "<strong>{{ $query }}</strong>". Please double check your order number or phone number format.
        </div>
    @endif

    @if($trackedOrder)
        @php
            $statuses = [
                \App\Models\Order::STATUS_PENDING,
                \App\Models\Order::STATUS_PAID,
                \App\Models\Order::STATUS_ASSEMBLING,
                \App\Models\Order::STATUS_TESTING,
                \App\Models\Order::STATUS_SHIPPED,
                \App\Models\Order::STATUS_COMPLETED,
            ];
            $currentIdx = array_search($trackedOrder->order_status, $statuses, true);
            if ($currentIdx === false && $trackedOrder->order_status === \App\Models\Order::STATUS_CANCELLED) {
                $isCancelled = true;
            } else {
                $isCancelled = false;
            }
        @endphp

        <!-- Tracked Order Main Card -->
        <div class="card" style="margin-bottom: 24px; border: 1px solid rgba(34, 197, 94, 0.3); box-shadow: 0 10px 40px rgba(0,0,0,0.6);">
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-color); padding-bottom: 14px; margin-bottom: 24px; flex-wrap: wrap; gap: 12px;">
                <div>
                    <span style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Order Tracking Reference:</span>
                    <h2 style="font-size: 1.5rem; font-weight: 700; color: #fff; margin-top: 2px; font-family: monospace;">
                        {{ $trackedOrder->order_number }}
                    </h2>
                </div>
                <div style="text-align: right;">
                    <span style="font-size: 0.75rem; color: var(--text-muted); display: block; margin-bottom: 4px; text-transform: uppercase; letter-spacing: 0.05em;">Current Milestone:</span>
                    @if($isCancelled)
                        <span class="badge badge-danger" style="font-size: 0.85rem; padding: 6px 14px;">Cancelled</span>
                    @else
                        <span class="badge badge-success" style="font-size: 0.85rem; padding: 6px 14px; font-weight: 600; font-family: monospace;">
                            ● {{ $trackedOrder->order_status }}
                        </span>
                    @endif
                </div>
            </div>

            <!-- 6-Stage Visual Milestone Stepper -->
            @if(!$isCancelled)
                <div style="display: grid; grid-template-columns: repeat(6, 1fr); gap: 12px; margin-bottom: 30px;" class="tracking-stepper">
                    @foreach($milestoneSteps as $key => $step)
                        @php
                            $stepIdx = array_search($key, $statuses, true);
                            $isDone = ($stepIdx !== false && $stepIdx <= $currentIdx);
                            $isCurrent = ($stepIdx === $currentIdx);
                        @endphp
                        <div style="text-align: center; position: relative;">
                            <div style="width: 38px; height: 38px; border-radius: 50%; margin: 0 auto 8px; display: flex; align-items: center; justify-content: center; font-size: 0.85rem; font-weight: 700; transition: all 0.3s;
                                        background: {{ $isDone ? 'rgba(34,197,94,0.15)' : 'rgba(255,255,255,0.03)' }};
                                        border: 2px solid {{ $isDone ? 'var(--primary)' : 'var(--border-color)' }};
                                        color: {{ $isDone ? 'var(--primary)' : 'var(--text-muted)' }};
                                        box-shadow: {{ $isCurrent ? '0 0 16px rgba(34,197,94,0.4)' : 'none' }};">
                                {{ $loop->iteration }}
                            </div>
                            <div style="font-size: 0.8rem; font-weight: {{ $isCurrent ? '700' : '500' }}; color: {{ $isDone ? '#fff' : 'var(--text-muted)' }}; line-height: 1.3;">
                                {{ $step['label'] }}
                            </div>
                            <small style="font-size: 0.7rem; color: var(--text-muted); display: block; margin-top: 2px;">
                                {{ $step['desc'] }}
                            </small>
                        </div>
                    @endforeach
                </div>
            @endif

            <!-- Customer & Shipping Summary Grid -->
            <div style="background: rgba(255,255,255,0.02); border: 1px solid var(--border-color); border-radius: 10px; padding: 18px; margin-bottom: 24px;">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; font-size: 0.85rem;">
                    <div>
                        <span style="color: var(--text-muted); display: block; margin-bottom: 2px;">Customer Recipient:</span>
                        <div style="font-weight: 600; color: #fff;">{{ $trackedOrder->customer_name }}</div>
                    </div>
                    <div>
                        <span style="color: var(--text-muted); display: block; margin-bottom: 2px;">Contact Phone:</span>
                        <div style="font-weight: 600; color: #fff; font-family: monospace;">{{ $maskedPhone }}</div>
                    </div>
                    <div>
                        <span style="color: var(--text-muted); display: block; margin-bottom: 2px;">Courier Carrier:</span>
                        <div style="font-weight: 600; color: #fff;">{{ $trackedOrder->carrier ?? 'J&T Express Cargo' }}</div>
                    </div>
                    <div>
                        <span style="color: var(--text-muted); display: block; margin-bottom: 2px;">Air Waybill Consignment:</span>
                        <div style="font-weight: 600; font-family: monospace; color: var(--primary);">
                            {{ $trackedOrder->tracking_number ?? 'Pending Allocation' }}
                        </div>
                    </div>
                </div>

                <div style="margin-top: 14px; font-size: 0.85rem; border-top: 1px dashed rgba(255,255,255,0.08); padding-top: 12px;">
                    <span style="color: var(--text-muted);">Delivery Destination:</span>
                    <span style="color: #fff; margin-left: 6px;">{{ $maskedAddress }}</span>
                </div>
            </div>

            <!-- Hardware Item Spec List -->
            <h3 style="font-size: 1.05rem; font-weight: 700; color: #fff; margin-bottom: 14px;">
                Ordered Hardware Build Manifest
            </h3>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Hardware Item</th>
                            <th>Category</th>
                            <th style="text-align: center;">Qty</th>
                            <th style="text-align: right;">Unit Price</th>
                            <th style="text-align: right;">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($trackedOrder->items as $item)
                            <tr>
                                <td>
                                    <strong style="color: #fff; display: block;">{{ $item->product?->name ?? 'Hardware Component' }}</strong>
                                    <small style="color: var(--text-muted); font-size: 0.75rem;">SKU #{{ $item->product_id }}</small>
                                </td>
                                <td><span class="badge badge-secondary" style="font-size: 0.75rem;">{{ $item->product?->category ?? 'Part' }}</span></td>
                                <td style="text-align: center; font-family: monospace;">{{ $item->quantity }}</td>
                                <td style="text-align: right; font-family: monospace; color: var(--text-light);">RM {{ number_format($item->unit_price, 2) }}</td>
                                <td style="text-align: right; font-weight: 600; font-family: monospace; color: #fff;">
                                    RM {{ number_format($item->subtotal, 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4" style="text-align: right; font-weight: 600; color: #fff; padding-top: 14px;">Total Settled:</td>
                            <td style="text-align: right; font-weight: 800; font-size: 1.25rem; color: var(--primary); font-family: monospace; padding-top: 14px;">
                                RM {{ number_format($trackedOrder->total_selling_amount, 2) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            @if($trackedOrder->serialNumbers->isNotEmpty())
                <div style="margin-top: 18px; background: rgba(255,255,255,0.02); border: 1px solid var(--border-color); border-radius: 8px; padding: 14px 18px;">
                    <span style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700; letter-spacing: 0.05em;">
                        Verified Physical Serial Numbers on Build:
                    </span>
                    <div style="display: flex; gap: 8px; flex-wrap: wrap; margin-top: 8px;">
                        @foreach($trackedOrder->serialNumbers as $sn)
                            <span class="badge badge-secondary" style="font-family: monospace; font-size: 0.75rem; border-color: rgba(34,197,94,0.3); color: var(--primary);">
                                {{ $sn->sn_code }} ({{ $sn->status }})
                            </span>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    @endif
</div>
@endsection
