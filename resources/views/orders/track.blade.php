@extends('layouts.app')

@section('title', 'Live Order & Assembly Tracking')

@section('content')
<div style="max-width: 1100px; margin: 30px auto; padding: 0 20px;">
    <!-- Search Section -->
    <div class="card" style="margin-bottom: 24px;">
        <h1 style="font-size: 1.5rem; font-weight: 800; color: #fff; margin-bottom: 6px;">
            🚚 Real-Time Custom PC Assembly & Shipment Tracking
        </h1>
        <p style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 18px;">
            Enter your official Order Number (e.g. <code>ORD-20261102-001</code>) or contact phone to view hardware staging, stress testing logs, and courier progress.
        </p>

        <form action="{{ route('orders.track') }}" method="GET" style="display: flex; gap: 12px; flex-wrap: wrap;">
            <input type="text" name="query" class="form-input" style="flex: 1; min-width: 260px;"
                   placeholder="Enter Order Number or Phone Number..."
                   value="{{ $query }}" required>
            <button type="submit" class="btn btn-primary" style="padding: 10px 24px; font-weight: 700;">
                🔍 Track Order
            </button>
        </form>
    </div>

    @if($searched && !$trackedOrder)
        <div class="alert alert-danger" style="margin-bottom: 24px;">
            ❌ No active or archived order found matching "<strong>{{ $query }}</strong>". Please double check your order number or phone number.
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
        <div class="card" style="margin-bottom: 24px; border: 1px solid rgba(0, 242, 254, 0.35); box-shadow: 0 10px 30px rgba(0,0,0,0.5);">
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-color); padding-bottom: 14px; margin-bottom: 24px; flex-wrap: wrap; gap: 12px;">
                <div>
                    <span style="font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase;">Tracking Reference:</span>
                    <h2 style="font-size: 1.4rem; font-weight: 800; color: #fff; margin-top: 2px;">
                        {{ $trackedOrder->order_number }}
                    </h2>
                </div>
                <div style="text-align: right;">
                    <span style="font-size: 0.8rem; color: var(--text-muted); display: block;">Current Workflow Stage:</span>
                    @if($isCancelled)
                        <span class="badge badge-danger" style="font-size: 0.95rem; padding: 6px 14px;">Cancelled</span>
                    @else
                        <span class="badge badge-info" style="font-size: 0.95rem; padding: 6px 14px; background: rgba(0, 242, 254, 0.15); color: #00f2fe; border: 1px solid #00f2fe;">
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
                            <div style="width: 44px; height: 44px; border-radius: 50%; margin: 0 auto 8px; display: flex; align-items: center; justify-content: center; font-size: 1.1rem;
                                        background: {{ $isCurrent ? 'var(--primary)' : ($isDone ? 'rgba(16, 185, 129, 0.2)' : 'rgba(255,255,255,0.05)') }};
                                        border: 2px solid {{ $isCurrent ? '#ff334b' : ($isDone ? '#10b981' : 'var(--border-color)') }};
                                        color: {{ $isDone || $isCurrent ? '#fff' : 'var(--text-muted)' }};
                                        box-shadow: {{ $isCurrent ? '0 0 15px rgba(255,51,75,0.6)' : 'none' }};">
                                {{ $step['icon'] }}
                            </div>
                            <div style="font-size: 0.75rem; font-weight: 700; color: {{ $isCurrent ? 'var(--accent)' : ($isDone ? '#fff' : 'var(--text-muted)') }};">
                                {{ $step['title'] }}
                            </div>
                            <div style="font-size: 0.65rem; color: var(--text-muted); margin-top: 2px;">
                                {{ $isDone ? ($isCurrent ? '● IN PROGRESS' : '✔ COMPLETED') : 'Pending' }}
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <!-- Shipping & Logistics Card -->
            <div style="background: rgba(255,255,255,0.02); border: 1px solid var(--border-color); border-radius: 8px; padding: 18px; margin-bottom: 24px;">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; font-size: 0.85rem;">
                    <div>
                        <span style="color: var(--text-muted);">Customer:</span>
                        <div style="font-weight: 700; color: #fff;">{{ $trackedOrder->customer_name }}</div>
                    </div>
                    <div>
                        <span style="color: var(--text-muted);">Contact Phone:</span>
                        <div style="font-weight: 700; color: #fff;">{{ $trackedOrder->customer_phone }}</div>
                    </div>
                    <div>
                        <span style="color: var(--text-muted);">Courier Carrier:</span>
                        <div style="font-weight: 700; color: var(--info);">{{ $trackedOrder->carrier ?? 'J&T Express Cargo' }}</div>
                    </div>
                    <div>
                        <span style="color: var(--text-muted);">Tracking Consignment No.:</span>
                        <div style="font-weight: 700; font-family: monospace; color: var(--accent);">
                            {{ $trackedOrder->tracking_number ?? 'Pending Allocation' }}
                        </div>
                    </div>
                </div>

                <div style="margin-top: 12px; font-size: 0.85rem; border-top: 1px dashed rgba(255,255,255,0.06); padding-top: 10px;">
                    <span style="color: var(--text-muted);">Delivery Address:</span>
                    <span style="color: #fff; margin-left: 6px;">{{ $trackedOrder->shipping_address }}</span>
                </div>
            </div>

            <!-- Hardware Item Spec List -->
            <h3 style="font-size: 1rem; font-weight: 700; color: #fff; margin-bottom: 12px;">
                📋 Ordered Hardware Build Manifest
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
                            <td colspan="4" style="text-align: right; font-weight: 700; color: #fff;">Total Settled:</td>
                            <td style="text-align: right; font-weight: 800; font-size: 1.15rem; color: var(--accent);">
                                RM {{ number_format($trackedOrder->total_selling_amount, 2) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            @if($trackedOrder->serialNumbers->isNotEmpty())
                <div style="margin-top: 16px; background: rgba(255,255,255,0.02); border: 1px solid var(--border-color); border-radius: 8px; padding: 12px 16px;">
                    <span style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">
                        🏷️ Verified Physical Serial Numbers on Build:
                    </span>
                    <div style="display: flex; gap: 8px; flex-wrap: wrap; margin-top: 6px;">
                        @foreach($trackedOrder->serialNumbers as $sn)
                            <span class="badge badge-secondary" style="font-family: monospace;">
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
