@extends('layouts.erp')

@section('title', 'Supply Chain & Inventory Dashboard')
@section('page-title', 'Supply Chain & Inventory Management Dashboard')

@section('content')
<div class="stat-grid">
    <div class="stat-card">
        <div>
            <div class="stat-label">Total Inventory Valuation</div>
            <div class="stat-val" style="color: var(--accent);">
                RM {{ number_format((float) ($costing['overall']['total_valuation'] ?? 0), 2) }}
            </div>
        </div>
        <div class="stat-icon"></div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-label">In-Stock Physical Units</div>
            <div class="stat-val" style="color: var(--success);">
                {{ (int) ($costing['overall']['total_units'] ?? 0) }}
            </div>
        </div>
        <div class="stat-icon"></div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-label">Low Stock Alerts</div>
            <div class="stat-val" style="color: {{ $lowStockProducts->isNotEmpty() ? 'var(--warning)' : 'var(--success)' }};">
                {{ $lowStockProducts->count() }}
            </div>
        </div>
        <div class="stat-icon"></div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-label">Active Suppliers</div>
            <div class="stat-val" style="color: var(--primary);">
                {{ (int) $totalSuppliers }}
            </div>
            <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 2px;">{{ (int) $totalProducts }} catalog SKUs</div>
        </div>
        <div class="stat-icon"></div>
    </div>
</div>

<!-- Quick Actions Bar -->
<div style="display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 24px;">
    <a href="{{ route('erp.products.index') }}" class="btn btn-primary btn-sm">Hardware Catalog</a>
    <a href="{{ route('erp.serials.index') }}" class="btn btn-outline btn-sm">Serial Number Ledger</a>
    <a href="{{ route('erp.restock.index') }}" class="btn btn-outline btn-sm">Restock Purchase Orders</a>
    <a href="{{ route('erp.suppliers.index') }}" class="btn btn-outline btn-sm">Suppliers & Mapping</a>
    <a href="{{ route('erp.inventory.costing') }}" class="btn btn-outline btn-sm">Batch Costing & Valuation</a>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px; align-items: start;">
    <!-- Low Stock Alert Card -->
    <div class="card" style="padding: 0; overflow: hidden;">
        <div class="card-header">
            <span class="card-title" style="margin: 0;">Critical Low-Stock Alerts</span>
            <a href="{{ route('erp.inventory.low-stock') }}" class="btn btn-outline btn-sm">View All &rarr;</a>
        </div>

        @if($lowStockProducts->isEmpty())
            <div style="padding: 30px; text-align: center; color: var(--text-muted);">
                All hardware catalog items are above their safety thresholds.
            </div>
        @else
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Category</th>
                            <th>In-Stock</th>
                            <th>Threshold</th>
                            <th>Deficit</th>
                            <th class="text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($lowStockProducts->take(6) as $item)
                            <tr>
                                <td><strong>{{ $item->name }}</strong></td>
                                <td><span class="badge badge-neutral">{{ $item->category }}</span></td>
                                <td><span class="badge badge-lowstock">{{ (int) $item->in_stock_count }} units</span></td>
                                <td>{{ (int) $item->low_stock_threshold }}</td>
                                <td class="font-mono" style="color: var(--danger); font-weight: 700;">
                                    -{{ $item->deficit }}
                                </td>
                                <td class="text-right">
                                    <a href="{{ route('erp.restock.create', ['product_id' => $item->id]) }}" class="btn btn-primary btn-sm">
                                        Restock
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <!-- Category Valuation Card -->
    <div class="card" style="padding: 0; overflow: hidden;">
        <div class="card-header">
            <span class="card-title" style="margin: 0;">Inventory by Category</span>
            <a href="{{ route('erp.inventory.costing') }}" class="btn btn-outline btn-sm">Cost Report</a>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Category</th>
                        <th>Units</th>
                        <th class="text-right">Valuation</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($costing['by_category'] as $cat)
                        <tr>
                            <td><strong>{{ $cat['category'] }}</strong></td>
                            <td>{{ (int) $cat['in_stock_count'] }}</td>
                            <td class="text-right font-mono" style="font-weight: 600;">
                                RM {{ number_format((float) $cat['category_valuation'], 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" style="text-align: center; padding: 20px; color: var(--text-muted);">No in-stock items found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
