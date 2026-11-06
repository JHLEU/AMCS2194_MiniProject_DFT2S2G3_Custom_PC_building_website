@extends('layouts.erp')

@section('title', 'Purchasing History')
@section('page-title', 'Purchasing History & Transaction Archive')

@section('content')
<div class="stat-grid">
    <div class="stat-card">
        <div>
            <div class="stat-label">Total Procurement Spend</div>
            <div class="stat-val" style="color: var(--primary);">
                RM {{ number_format((float) ($metrics['total_spent'] ?? 0), 2) }}
            </div>
        </div>
        <div class="stat-icon">💵</div>
    </div>
    <div class="stat-card">
        <div>
            <div class="stat-label">Completed Restock Orders</div>
            <div class="stat-val" style="color: var(--success);">
                {{ (int) ($metrics['total_completed_orders'] ?? 0) }}
            </div>
        </div>
        <div class="stat-icon">✅</div>
    </div>
    <div class="stat-card">
        <div>
            <div class="stat-label">Total Hardware Procured</div>
            <div class="stat-val" style="color: var(--accent);">
                {{ (int) ($metrics['total_items_procured'] ?? 0) }}
            </div>
        </div>
        <div class="stat-icon">📦</div>
    </div>
</div>

<div class="filter-bar">
    <form action="{{ route('erp.restock.history') }}" method="GET" style="display: flex; flex-wrap: wrap; gap: 10px; align-items: center; flex: 1;">
        <input type="text" name="search" class="form-input" placeholder="Search order code or supplier..." value="{{ $search }}" style="max-width: 240px;">

        <select name="supplier_id" class="form-input" style="max-width: 220px;">
            <option value="">All Suppliers</option>
            @foreach($suppliers as $s)
                <option value="{{ $s->id }}" {{ $selectedSupplier === (int) $s->id ? 'selected' : '' }}>
                    {{ $s->company_name }}
                </option>
            @endforeach
        </select>

        <div style="display: flex; align-items: center; gap: 6px;">
            <span style="font-size: 0.8rem; color: var(--text-muted);">From:</span>
            <input type="date" name="date_from" class="form-input" value="{{ $dateFrom }}" style="max-width: 155px;">
        </div>

        <div style="display: flex; align-items: center; gap: 6px;">
            <span style="font-size: 0.8rem; color: var(--text-muted);">To:</span>
            <input type="date" name="date_to" class="form-input" value="{{ $dateTo }}" style="max-width: 155px;">
        </div>

        <button type="submit" class="btn btn-outline btn-sm">Apply Filter</button>
        @if($search || $selectedSupplier || $dateFrom || $dateTo)
            <a href="{{ route('erp.restock.history') }}" class="btn btn-outline btn-sm">Reset</a>
        @endif
    </form>

    <div>
        <a href="{{ route('erp.restock.index') }}" class="btn btn-outline">&larr; Active Orders Pipeline</a>
    </div>
</div>

<div class="card" style="padding: 0; overflow: hidden;">
    <div class="card-header">
        <span class="card-title" style="margin: 0;">📜 Fulfilled Purchasing Archive</span>
        <span style="font-size: 0.85rem; color: var(--text-muted);">{{ $history->count() }} Completed Records</span>
    </div>

    @if($history->isEmpty())
        <div style="padding: 40px; text-align: center; color: var(--text-muted);">
            <h3>No Completed Transactions</h3>
            <p style="margin-top: 6px;">When restock orders are finalized and marked as Completed, they will be archived here for financial tracking and auditing.</p>
        </div>
    @else
        <div class="table-responsive">
            <table class="table" id="history-table">
                <thead>
                    <tr>
                        <th>Order Code</th>
                        <th>Supplier</th>
                        <th>Order Date</th>
                        <th>Fulfilled / Received Date</th>
                        <th>Lines / Units</th>
                        <th class="text-right">Transaction Total</th>
                        <th class="text-right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($history as $h)
                        <tr>
                            <td>
                                <strong class="font-mono" style="color: var(--accent);">{{ $h->order_code }}</strong>
                            </td>
                            <td>
                                <strong>{{ $h->supplier->company_name ?? '—' }}</strong>
                                <div style="font-size: 0.75rem; color: var(--text-muted);">
                                    {{ $h->supplier->contact_person ?? '' }} ({{ $h->supplier->email ?? '' }})
                                </div>
                            </td>
                            <td>{{ $h->order_date?->format('Y-m-d') }}</td>
                            <td>
                                <span class="badge badge-instock">
                                    ✓ {{ $h->completed_at ? $h->completed_at->format('M d, Y H:i') : 'Completed' }}
                                </span>
                            </td>
                            <td>
                                {{ (int) $h->total_line_items }} line(s) &bull; <strong>{{ (int) $h->total_units_received }} units</strong>
                            </td>
                            <td class="text-right font-mono" style="font-weight: 700;">
                                RM {{ number_format((float) $h->total_amount, 2) }}
                            </td>
                            <td class="text-right">
                                <a href="{{ route('erp.restock.show', $h) }}" class="btn btn-outline btn-sm">
                                    View Slip
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
