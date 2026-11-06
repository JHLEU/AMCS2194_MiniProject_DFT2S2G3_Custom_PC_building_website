@extends('layouts.erp')

@section('title', 'Restock Purchase Orders')
@section('page-title', 'Restock Purchase Orders Pipeline')

@section('content')
<div class="filter-bar">
    <form action="{{ route('erp.restock.index') }}" method="GET" style="display: flex; flex-wrap: wrap; gap: 10px; align-items: center; flex: 1;">
        <select name="status" class="form-input" style="max-width: 170px;" onchange="this.form.submit()">
            <option value="">All Statuses</option>
            @foreach(['Pending', 'Approved', 'Completed', 'Cancelled'] as $st)
                <option value="{{ $st }}" {{ $selectedStatus === $st ? 'selected' : '' }}>{{ $st }}</option>
            @endforeach
        </select>

        <select name="supplier_id" class="form-input" style="max-width: 250px;" onchange="this.form.submit()">
            <option value="">All Suppliers</option>
            @foreach($suppliers as $s)
                <option value="{{ $s->id }}" {{ $selectedSupplier === (int) $s->id ? 'selected' : '' }}>
                    {{ $s->company_name }}
                </option>
            @endforeach
        </select>

        <button type="submit" class="btn btn-outline btn-sm">Filter</button>
        @if($selectedStatus || $selectedSupplier)
            <a href="{{ route('erp.restock.index') }}" class="btn btn-outline btn-sm">Clear</a>
        @endif
    </form>

    <div style="display: flex; gap: 8px;">
        <a href="{{ route('erp.restock.history') }}" class="btn btn-outline">📜 Purchasing History</a>
        <a href="{{ route('erp.restock.create') }}" class="btn btn-primary">+ Create Restock PO</a>
    </div>
</div>

<div class="card" style="padding: 0; overflow: hidden;">
    <div class="card-header">
        <span class="card-title" style="margin: 0;">📦 Restock Purchase Orders & Approval Pipeline</span>
        <span style="font-size: 0.85rem; color: var(--text-muted);">{{ $orders->count() }} Total Orders</span>
    </div>

    @if($orders->isEmpty())
        <div style="padding: 40px; text-align: center; color: var(--text-muted);">
            <h3>No Purchase Orders Found</h3>
            <p style="margin-top: 6px;">Create a restock request to replenish depleted inventory from authorized suppliers.</p>
            <a href="{{ route('erp.restock.create') }}" class="btn btn-primary" style="margin-top: 14px;">+ Create Order</a>
        </div>
    @else
        <div class="table-responsive">
            <table class="table" id="restock-orders-table">
                <thead>
                    <tr>
                        <th>Order Code</th>
                        <th>Supplier</th>
                        <th>Order Date</th>
                        <th>Items / Units</th>
                        <th>Total Amount</th>
                        <th>Approval Status</th>
                        <th>Completed Date</th>
                        <th class="text-right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($orders as $o)
                        <tr>
                            <td>
                                <a href="{{ route('erp.restock.show', $o) }}" class="font-mono" style="font-weight: 700;">
                                    {{ $o->order_code }}
                                </a>
                            </td>
                            <td>
                                <strong>{{ $o->supplier->company_name ?? '—' }}</strong>
                                <div style="font-size: 0.75rem; color: var(--text-muted);">{{ $o->supplier->contact_person ?? '' }}</div>
                            </td>
                            <td>{{ $o->order_date?->format('Y-m-d') }}</td>
                            <td>
                                {{ (int) $o->total_line_items }} item(s) &bull; <strong>{{ (int) $o->total_units_ordered }}</strong> units
                            </td>
                            <td class="font-mono" style="font-weight: 700;">
                                RM {{ number_format((float) $o->total_amount, 2) }}
                            </td>
                            <td>
                                @php
                                    $statusClass = 'badge-warning';
                                    if ($o->status === 'Approved')  $statusClass = 'badge-info';
                                    if ($o->status === 'Completed') $statusClass = 'badge-instock';
                                    if ($o->status === 'Cancelled') $statusClass = 'badge-neutral';
                                @endphp
                                <span class="badge {{ $statusClass }}">{{ $o->status }}</span>
                            </td>
                            <td style="font-size: 0.8rem; color: var(--text-muted);">
                                {{ $o->completed_at ? $o->completed_at->format('M d, Y H:i') : '—' }}
                            </td>
                            <td class="text-right">
                                <a href="{{ route('erp.restock.show', $o) }}" class="btn btn-outline btn-sm">
                                    View / Manage &rarr;
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
