@extends('layouts.erp')

@section('title', 'Edit Product: ' . $product->name)
@section('page-title', 'Edit Hardware Component')

@section('content')
<div class="card" style="max-width: 860px; margin: 0 auto;">
    <div class="card-header">
        <span class="card-title" style="margin: 0;">Edit Product: {{ $product->name }}</span>
        <a href="{{ route('erp.products.index') }}" class="btn btn-outline btn-sm">&larr; Back to Catalog</a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger" style="margin-top: 16px;">
            {{ $errors->first() }}
        </div>
    @endif

    <form action="{{ route('erp.products.update', $product) }}" method="POST" style="margin-top: 20px;">
        @csrf
        @method('PUT')

        <div class="form-grid">
            <div class="form-group" style="grid-column: 1 / -1;">
                <label class="form-label">Hardware Product Name *</label>
                <input type="text" name="name" class="form-input" value="{{ old('name', $product->name) }}" required>
            </div>

            <div class="form-group">
                <label class="form-label">Category *</label>
                <select name="category" class="form-input" required>
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}" {{ old('category', $product->category) === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Brand</label>
                <input type="text" name="brand" class="form-input" value="{{ old('brand', $product->brand) }}">
            </div>

            <div class="form-group">
                <label class="form-label">Primary Supplier</label>
                <select name="supplier_id" class="form-input">
                    <option value="">-- Optional Default Supplier --</option>
                    @foreach($suppliers as $sup)
                        <option value="{{ $sup->id }}" {{ (string) old('supplier_id', $product->supplier_id) === (string) $sup->id ? 'selected' : '' }}>
                            {{ $sup->company_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Warranty Period (Months)</label>
                <input type="number" name="warranty_months" class="form-input" value="{{ old('warranty_months', $product->warranty_months) }}" min="0">
            </div>

            <div class="form-group">
                <label class="form-label">CPU / Motherboard Socket Type</label>
                <input type="text" name="socket_type" list="socket-list" class="form-input" value="{{ old('socket_type', $product->socket_type) }}">
                <datalist id="socket-list">
                    @foreach($socketTypes as $st)
                        <option value="{{ $st }}">
                    @endforeach
                </datalist>
            </div>

            <div class="form-group">
                <label class="form-label">RAM Generation Standard</label>
                <select name="ram_type" class="form-input">
                    <option value="">-- N/A --</option>
                    @foreach($ramTypes as $rt)
                        <option value="{{ $rt }}" {{ old('ram_type', $product->ram_type) === $rt ? 'selected' : '' }}>{{ $rt }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">TDP / Rated Wattage (W)</label>
                <input type="number" name="tdp_wattage" class="form-input" value="{{ old('tdp_wattage', $product->tdp_wattage) }}" min="0">
            </div>

            <div class="form-group">
                <label class="form-label">Performance Rating Score (0-100)</label>
                <input type="number" name="performance_score" class="form-input" value="{{ old('performance_score', $product->performance_score) }}" min="0" max="100" placeholder="For CPU, GPU, RAM, Storage">
            </div>

            <div class="form-group">
                <label class="form-label">Low-Stock Safety Threshold *</label>
                <input type="number" name="low_stock_threshold" class="form-input" value="{{ old('low_stock_threshold', $product->low_stock_threshold) }}" min="1" required>
                <small style="color: var(--text-muted); font-size: 0.78rem;">Current In-Stock SN Count: <strong>{{ (int) $product->in_stock_count }}</strong></small>
            </div>

            <div class="form-group">
                <label class="form-label">Default Cost Price (RM) *</label>
                <input type="number" step="0.01" name="cost_price" class="form-input" value="{{ old('cost_price', $product->cost_price) }}" min="0" required>
            </div>

            <div class="form-group">
                <label class="form-label">Standard Selling Price (RM) *</label>
                <input type="number" step="0.01" name="selling_price" class="form-input" value="{{ old('selling_price', $product->selling_price) }}" min="0" required>
            </div>

            <div class="form-group" style="grid-column: 1 / -1;">
                <label class="form-label">Compatibility & Technical Specifications</label>
                <textarea name="specs" class="form-input" rows="3">{{ old('specs', $product->specs) }}</textarea>
            </div>
        </div>

        <div style="margin-top: 24px; display: flex; justify-content: flex-end; gap: 10px;">
            <a href="{{ route('erp.products.index') }}" class="btn btn-outline">Cancel</a>
            <button type="submit" class="btn btn-primary">Update Product</button>
        </div>
    </form>
</div>

<div class="card" style="max-width: 860px; margin: 24px auto 0; padding: 0; overflow: hidden;">
    <div class="card-header">
        <span class="card-title" style="margin: 0;">🏢 Qualified Suppliers for this Component</span>
        <a href="{{ route('erp.suppliers.index') }}" class="btn btn-outline btn-sm">Manage Suppliers</a>
    </div>

    @if($product->supplierMappings->isEmpty())
        <div style="padding: 24px; text-align: center; color: var(--text-muted);">
            No suppliers have been mapped to this hardware product yet.
        </div>
    @else
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Supplier Company</th>
                        <th>Contact</th>
                        <th>Supplier SKU</th>
                        <th>Agreed Cost</th>
                        <th>Lead Time</th>
                        <th class="text-right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($product->supplierMappings as $m)
                        <tr>
                            <td><strong>{{ $m->supplier->company_name ?? '—' }}</strong></td>
                            <td>{{ $m->supplier->contact_person ?? '—' }} ({{ $m->supplier->phone ?? '—' }})</td>
                            <td class="font-mono">{{ $m->supplier_sku ?: '—' }}</td>
                            <td class="font-mono">RM {{ number_format((float) $m->agreed_unit_price, 2) }}</td>
                            <td>{{ $m->lead_time_days }} days</td>
                            <td class="text-right">
                                <a href="{{ route('erp.restock.create', ['supplier_id' => $m->supplier_id, 'product_id' => $product->id]) }}" class="btn btn-primary btn-sm">
                                    Draft Restock PO
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
