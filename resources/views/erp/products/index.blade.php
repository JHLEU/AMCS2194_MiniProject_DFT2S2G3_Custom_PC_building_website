@extends('layouts.erp')

@section('title', 'Hardware Catalog')
@section('page-title', 'Hardware Catalog & Compatibility Specifications')

@section('content')
<div class="filter-bar">
    <form action="{{ route('erp.products.index') }}" method="GET" style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center; flex: 1;">
        <input type="text" name="search" class="form-input" value="{{ $search }}" placeholder="Search by name, brand, socket, or specs..." style="max-width: 300px;" data-table-search="#products-table">
        <select name="category" class="form-input" style="max-width: 180px;">
            <option value="">All Categories</option>
            @foreach($categories as $cat)
                <option value="{{ $cat }}" {{ $selectedCategory === $cat ? 'selected' : '' }}>{{ $cat }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn btn-outline btn-sm">Filter</button>
        @if($selectedCategory || $search)
            <a href="{{ route('erp.products.index') }}" class="btn btn-outline btn-sm">Reset</a>
        @endif
    </form>

    <div style="display: flex; gap: 8px;">
        <a href="{{ route('erp.products.create') }}" class="btn btn-primary">+ Add New Hardware</a>
    </div>
</div>

<div class="card" style="padding: 0; overflow: hidden;">
    <div class="card-header">
        <span class="card-title" style="margin: 0;">Hardware Catalog & Compatibility Specs</span>
        <span style="font-size: 0.85rem; color: var(--text-muted);">{{ $products->count() }} Total Products</span>
    </div>

    @if($products->isEmpty())
        <div style="padding: 40px; text-align: center; color: var(--text-muted);">
            <h3>No Products Found</h3>
            <p style="margin-top: 6px;">Start by adding hardware components to your catalog.</p>
            <a href="{{ route('erp.products.create') }}" class="btn btn-primary" style="margin-top: 14px;">+ Add Product</a>
        </div>
    @else
        <div class="table-responsive">
            <table class="table" id="products-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Component Name</th>
                        <th>Category</th>
                        <th>Compatibility Metadata</th>
                        <th>Cost Price</th>
                        <th>Selling Price</th>
                        <th>Stock Levels</th>
                        <th>Low-Stock Alert</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $p)
                        <tr>
                            <td class="font-mono text-muted">#{{ $p->id }}</td>
                            <td>
                                <strong>{{ $p->name }}</strong>
                                @if($p->brand)
                                    <div style="font-size: 0.75rem; color: var(--text-muted);">Brand: {{ $p->brand }} &bull; Warranty: {{ $p->warranty_months }}m</div>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-neutral">{{ $p->category }}</span>
                            </td>
                            <td style="max-width: 280px;">
                                <div style="display: flex; gap: 4px; flex-wrap: wrap; margin-bottom: 4px;">
                                    @if($p->socket_type)
                                        <span class="badge badge-info">Socket: {{ $p->socket_type }}</span>
                                    @endif
                                    @if($p->ram_type)
                                        <span class="badge badge-accent">RAM: {{ $p->ram_type }}</span>
                                    @endif
                                    @if($p->performance_score > 0)
                                        <span class="badge badge-success">Score: {{ $p->performance_score }}/100</span>
                                    @endif
                                    @if($p->tdp_wattage > 0)
                                        <span class="badge badge-warning">{{ $p->category === 'PSU' ? 'Cap:' : 'TDP:' }} {{ $p->tdp_wattage }}W</span>
                                    @endif
                                </div>
                                <div style="font-size: 0.78rem; color: var(--text-muted);">{{ $p->specs ?: '—' }}</div>
                            </td>
                            <td class="font-mono">RM {{ number_format((float) $p->cost_price, 2) }}</td>
                            <td class="font-mono">RM {{ number_format((float) $p->selling_price, 2) }}</td>
                            <td>
                                <div style="display: flex; gap: 4px; flex-wrap: wrap;">
                                    <span class="badge badge-instock" title="In-Stock">{{ (int) $p->in_stock_count }} In</span>
                                    @if((int) $p->reserved_count > 0)
                                        <span class="badge badge-info" title="Reserved">{{ (int) $p->reserved_count }} Res</span>
                                    @endif
                                    <span class="badge badge-sold" title="Sold">{{ (int) $p->sold_count }} Sold</span>
                                    @if((int) $p->rma_count > 0)
                                        <span class="badge badge-rma" title="RMA">{{ (int) $p->rma_count }} RMA</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                @if($p->is_low_stock)
                                    <span class="badge badge-lowstock">Low (&le; {{ $p->low_stock_threshold }})</span>
                                @else
                                    <span class="badge badge-instock">OK (Min: {{ $p->low_stock_threshold }})</span>
                                @endif
                            </td>
                            <td class="text-right" style="white-space: nowrap;">
                                <a href="{{ route('erp.products.edit', $p) }}" class="btn btn-outline btn-sm">Edit</a>
                                <a href="{{ route('erp.serials.create', ['product_id' => $p->id]) }}" class="btn btn-outline btn-sm">+ SN</a>
                                <form action="{{ route('erp.products.destroy', $p) }}" method="POST" style="display: inline-block;" onsubmit="return confirm('Delete product {{ addslashes($p->name) }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline btn-sm" style="color: var(--danger); border-color: rgba(239,68,68,0.4);">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
