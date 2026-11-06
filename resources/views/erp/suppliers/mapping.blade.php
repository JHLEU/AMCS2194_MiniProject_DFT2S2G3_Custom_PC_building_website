@extends('layouts.erp')

@section('title', 'Hardware Mapping: ' . $supplier->company_name)
@section('page-title', 'Hardware Supply Mapping')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 12px;">
    <div>
        <h2 style="font-size: 1.25rem; font-weight: 700; color: #fff; margin-bottom: 4px;">
            Hardware Supply Mapping: {{ $supplier->company_name }}
        </h2>
        <p style="font-size: 0.85rem; color: var(--text-muted);">
            Contact: <strong>{{ $supplier->contact_person }}</strong> &bull; {{ $supplier->phone }} &bull; {{ $supplier->email }}
        </p>
    </div>
    <div style="display: flex; gap: 8px;">
        <a href="{{ route('erp.restock.create', ['supplier_id' => $supplier->id]) }}" class="btn btn-primary">+ Create Restock Order</a>
        <a href="{{ route('erp.suppliers.index') }}" class="btn btn-outline">&larr; Back to Suppliers</a>
    </div>
</div>

@if($errors->any())
    <div class="alert alert-danger">
        {{ $errors->first() }}
    </div>
@endif

<div style="display: grid; grid-template-columns: 1fr 360px; gap: 24px; align-items: start;">
    <!-- Active Mappings Table -->
    <div class="card" style="padding: 0; overflow: hidden;">
        <div class="card-header">
            <span class="card-title" style="margin: 0;">📦 Mapped Hardware Components</span>
            <span class="badge badge-info">{{ $mappedProducts->count() }} Products</span>
        </div>

        @if($mappedProducts->isEmpty())
            <div style="padding: 30px; text-align: center; color: var(--text-muted);">
                No hardware products mapped yet. Use the form on the right to assign components this vendor can supply.
            </div>
        @else
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Product Name</th>
                            <th>Category</th>
                            <th>Supplier SKU</th>
                            <th>Agreed Cost</th>
                            <th>Catalog Cost</th>
                            <th>Lead Time</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($mappedProducts as $m)
                            <tr>
                                <td><strong>{{ $m->product->name ?? '—' }}</strong></td>
                                <td><span class="badge badge-neutral">{{ $m->product->category ?? '—' }}</span></td>
                                <td class="font-mono text-muted">{{ $m->supplier_sku ?: '—' }}</td>
                                <td class="font-mono" style="font-weight: 700; color: var(--accent);">
                                    RM {{ number_format((float) $m->agreed_unit_price, 2) }}
                                </td>
                                <td class="font-mono text-muted">
                                    RM {{ number_format((float) ($m->product->cost_price ?? 0), 2) }}
                                </td>
                                <td>{{ $m->lead_time_days }} day(s)</td>
                                <td class="text-right">
                                    <form action="{{ route('erp.suppliers.mapping.remove', [$supplier, $m->product_id]) }}" method="POST" style="display: inline-block;" onsubmit="return confirm('Remove this hardware mapping?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline btn-sm" style="color: var(--danger); border-color: rgba(239,68,68,0.4);">&times; Remove</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <!-- Assign / Update Hardware Form Card -->
    <div class="card">
        <h3 class="card-title">+ Assign / Update Hardware Mapping</h3>
        <form action="{{ route('erp.suppliers.mapping.save', $supplier) }}" method="POST">
            @csrf

            <div class="form-group">
                <label class="form-label">Select Hardware Product *</label>
                <select name="product_id" class="form-input" id="mapping-product-select" required>
                    <option value="">-- Choose Product --</option>
                    @foreach($allProducts as $up)
                        <option value="{{ $up->id }}" data-cost="{{ $up->cost_price }}">
                            {{ $up->name }} ({{ $up->category }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Vendor Specific SKU / Part #</label>
                <input type="text" name="supplier_sku" class="form-input font-mono" placeholder="e.g., TD-CPU-AM5-7800">
            </div>

            <div class="form-group">
                <label class="form-label">Agreed Unit Purchase Price (RM) *</label>
                <input type="number" step="0.01" name="agreed_unit_price" id="mapping-price-input" class="form-input" placeholder="0.00" min="0.01" required>
                <small style="color: var(--text-muted); font-size: 0.78rem;">Contracted unit cost from this supplier.</small>
            </div>

            <div class="form-group">
                <label class="form-label">Estimated Delivery Lead Time (Days) *</label>
                <input type="number" name="lead_time_days" class="form-input" value="3" min="1" required>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 8px;">
                Save Hardware Mapping
            </button>
        </form>
    </div>
</div>
@endsection

