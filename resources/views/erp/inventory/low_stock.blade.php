@extends('layouts.erp')

@section('title', 'Low-Stock Real-Time Alerts')
@section('page-title', 'Low-Stock Real-Time Alerts')

@section('content')
<div class="filter-bar">
    <input type="text" data-table-search="#low-stock-table" class="form-input" placeholder="Search low stock hardware by name, category, or specs..." style="max-width: 400px;">
    <div style="margin-left: auto; color: var(--text-muted); font-size: 0.85rem;">
        Total Low-Stock Items: <strong style="color: var(--danger);">{{ $lowStockItems->count() }}</strong>
    </div>
</div>

<div class="card" style="padding: 0; overflow: hidden;">
    <div class="card-header">
        <span class="card-title" style="margin: 0;">⚠️ Hardware Items Falling Below Safety Thresholds</span>
        <a href="{{ route('erp.restock.create') }}" class="btn btn-primary btn-sm">+ Draft Restock Order</a>
    </div>

    @if($lowStockItems->isEmpty())
        <div style="padding: 40px; text-align: center; color: var(--text-muted);">
            <div style="font-size: 2.5rem; margin-bottom: 8px;">✅</div>
            <h3>Stock Levels Healthy</h3>
            <p style="margin-top: 6px;">No products are currently under their configured low-stock thresholds.</p>
        </div>
    @else
        <div class="table-responsive">
            <table class="table" id="low-stock-table">
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>Category</th>
                        <th>Hardware Specs</th>
                        <th>In-Stock Units</th>
                        <th>Min Threshold</th>
                        <th>Deficit Units</th>
                        <th>Est. Unit Cost</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($lowStockItems as $item)
                        <tr>
                            <td><strong>{{ $item->name }}</strong></td>
                            <td><span class="badge badge-neutral">{{ $item->category }}</span></td>
                            <td style="max-width: 280px; font-size: 0.8rem; color: var(--text-muted);">
                                {{ $item->specs ?: 'No specs listed' }}
                            </td>
                            <td>
                                <span class="badge badge-lowstock">{{ (int) $item->in_stock_count }} In-Stock</span>
                            </td>
                            <td><strong>{{ (int) $item->low_stock_threshold }}</strong></td>
                            <td>
                                <span class="font-mono" style="color: var(--danger); font-weight: 700;">
                                    -{{ $item->deficit }} units
                                </span>
                            </td>
                            <td class="font-mono">RM {{ number_format((float) $item->cost_price, 2) }}</td>
                            <td class="text-right" style="white-space: nowrap;">
                                <a href="{{ route('erp.restock.create', ['product_id' => $item->id, 'supplier_id' => $item->supplier_id]) }}" class="btn btn-primary btn-sm">
                                    Order Restock
                                </a>
                                <a href="{{ route('erp.serials.create', ['product_id' => $item->id]) }}" class="btn btn-outline btn-sm">
                                    + Add SN
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
