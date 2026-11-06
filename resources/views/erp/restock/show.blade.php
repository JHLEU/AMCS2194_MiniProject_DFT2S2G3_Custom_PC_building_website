@extends('layouts.erp')

@section('title', 'Restock Order: ' . $order->order_code)
@section('page-title', 'Restock Purchase Order Details')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 12px;">
    <div>
        <h2 style="font-size: 1.35rem; font-weight: 700; color: #fff; margin-bottom: 4px;">
            Restock Order: <span class="font-mono" style="color: var(--accent);">{{ $order->order_code }}</span>
        </h2>
        <div style="font-size: 0.85rem; color: var(--text-muted);">
            Ordered on {{ $order->order_date?->format('Y-m-d') }} &bull; Supplier: <strong>{{ $order->supplier->company_name ?? '—' }}</strong>
        </div>
    </div>
    <div style="display: flex; gap: 8px;">
        <a href="{{ route('erp.restock.index') }}" class="btn btn-outline">&larr; Back to Orders</a>
    </div>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px; align-items: start;">
    <!-- Order Line Items & Provisioned Serials -->
    <div>
        <div class="card" style="padding: 0; overflow: hidden;">
            <div class="card-header">
                <span class="card-title" style="margin: 0;">📦 Order Line Items & Received Components</span>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Hardware Product</th>
                            <th>Category</th>
                            <th>Supplier SKU</th>
                            <th>Ordered Qty</th>
                            <th>Unit Cost</th>
                            <th class="text-right">Line Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $item)
                            <tr>
                                <td><strong>{{ $item->product->name ?? '—' }}</strong></td>
                                <td><span class="badge badge-neutral">{{ $item->product->category ?? '—' }}</span></td>
                                <td class="font-mono text-muted">{{ $skuMap[$item->product_id] ?? '—' }}</td>
                                <td><strong>{{ (int) $item->quantity }}</strong> units</td>
                                <td class="font-mono">RM {{ number_format((float) $item->unit_cost, 2) }}</td>
                                <td class="text-right font-mono" style="font-weight: 700;">
                                    RM {{ number_format((float) $item->subtotal, 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr style="background: rgba(15, 23, 42, 0.6); font-weight: 700;">
                            <td colspan="5" class="text-right" style="padding: 14px;">Total Purchase Valuation:</td>
                            <td class="text-right font-mono" style="font-size: 1.15rem; color: var(--accent); padding: 14px;">
                                RM {{ number_format((float) $order->total_amount, 2) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        @if($order->serialNumbers->isNotEmpty())
            <div class="card" style="padding: 0; overflow: hidden;">
                <div class="card-header">
                    <span class="card-title" style="margin: 0;">🏷️ Provisioned Serial Numbers ({{ $order->serialNumbers->count() }} Units)</span>
                    <a href="{{ route('erp.serials.index') }}" class="btn btn-outline btn-sm">View All Serials</a>
                </div>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Serial Code</th>
                                <th>Product</th>
                                <th>Batch Cost</th>
                                <th>Lifecycle Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->serialNumbers as $sn)
                                <tr>
                                    <td class="font-mono"><strong>{{ $sn->sn_code }}</strong></td>
                                    <td>{{ $sn->product->name ?? '—' }}</td>
                                    <td class="font-mono">RM {{ number_format((float) $sn->batch_cost, 2) }}</td>
                                    <td><span class="badge badge-instock">{{ $sn->status }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>

    <!-- Status & Supplier Summary Card -->
    <div>
        <div class="card">
            <h3 class="card-title">Approval & Status Workflow</h3>
            <div style="margin-bottom: 16px;">
                <div style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 6px;">Current Status</div>
                @php
                    $statusClass = 'badge-warning';
                    if ($order->status === 'Approved')  $statusClass = 'badge-info';
                    if ($order->status === 'Completed') $statusClass = 'badge-instock';
                    if ($order->status === 'Cancelled') $statusClass = 'badge-neutral';
                @endphp
                <span class="badge {{ $statusClass }}" style="font-size: 0.9rem; padding: 6px 12px;">
                    {{ $order->status }}
                </span>
                @if($order->completed_at)
                    <div style="font-size: 0.78rem; color: var(--text-muted); margin-top: 8px;">
                        Completed: {{ $order->completed_at->format('M d, Y H:i') }}
                    </div>
                @endif
            </div>

            @if($order->status === 'Pending')
                <form action="{{ route('erp.restock.status', $order) }}" method="POST" style="margin-bottom: 10px;">
                    @csrf
                    <input type="hidden" name="status" value="Approved">
                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                        ✓ Approve Purchase Order
                    </button>
                </form>
                <form action="{{ route('erp.restock.status', $order) }}" method="POST" onsubmit="return confirm('Cancel this purchase order?');">
                    @csrf
                    <input type="hidden" name="status" value="Cancelled">
                    <button type="submit" class="btn btn-outline" style="width: 100%; color: var(--danger); border-color: rgba(239,68,68,0.4);">
                        Cancel Order
                    </button>
                </form>
            @elseif($order->status === 'Approved')
                <form action="{{ route('erp.restock.status', $order) }}" method="POST">
                    @csrf
                    <input type="hidden" name="status" value="Completed">

                    <div style="background: rgba(15, 23, 42, 0.7); border: 1px solid var(--border-color); padding: 12px; border-radius: 6px; margin-bottom: 14px; font-size: 0.82rem;">
                        <label style="display: flex; align-items: flex-start; gap: 8px; cursor: pointer;">
                            <input type="checkbox" name="auto_generate_sn" value="1" checked style="margin-top: 3px;">
                            <span><strong>Auto-provision Serial Numbers:</strong> Automatically register physical <code>In-Stock</code> serial numbers for all line items at their PO unit costs.</span>
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%; margin-bottom: 10px; background: var(--success);">
                        ✓ Receive Stock & Complete Order
                    </button>
                </form>
                <form action="{{ route('erp.restock.status', $order) }}" method="POST" onsubmit="return confirm('Cancel this approved order?');">
                    @csrf
                    <input type="hidden" name="status" value="Cancelled">
                    <button type="submit" class="btn btn-outline btn-sm" style="width: 100%; color: var(--danger);">
                        Cancel Order
                    </button>
                </form>
            @elseif($order->status === 'Completed')
                <div class="alert alert-success" style="margin-bottom: 0;">
                    ✅ This transaction is finalized. Stock has been received into inventory.
                </div>
            @else
                <div style="background: rgba(148, 163, 184, 0.15); color: var(--text-muted); padding: 12px; border-radius: 6px; font-size: 0.85rem;">
                    This order has been cancelled.
                </div>
            @endif
        </div>

        <div class="card">
            <h3 class="card-title">Supplier Information</h3>
            <div style="font-size: 0.85rem; line-height: 1.7;">
                <p><strong>Company:</strong> {{ $order->supplier->company_name ?? '—' }}</p>
                <p><strong>Contact:</strong> {{ $order->supplier->contact_person ?? '—' }}</p>
                <p><strong>Phone:</strong> {{ $order->supplier->phone ?? '—' }}</p>
                <p><strong>Email:</strong> <a href="mailto:{{ $order->supplier->email ?? '' }}">{{ $order->supplier->email ?? '—' }}</a></p>
                @if($order->supplier?->address)
                    <p style="margin-top: 6px; color: var(--text-muted);">{{ $order->supplier->address }}</p>
                @endif

                @if($order->notes)
                    <div style="margin-top: 12px; padding-top: 12px; border-top: 1px solid var(--border-color);">
                        <strong>Order Notes:</strong>
                        <div style="color: var(--text-muted); margin-top: 2px;">{{ $order->notes }}</div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
