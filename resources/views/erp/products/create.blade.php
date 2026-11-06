@extends('layouts.erp')

@section('title', 'Add Hardware Product')
@section('page-title', 'Register New Hardware Component')

@section('content')
<div class="card" style="max-width: 860px; margin: 0 auto;">
    <div class="card-header">
        <span class="card-title" style="margin: 0;">Register New Hardware Product</span>
        <a href="{{ route('erp.products.index') }}" class="btn btn-outline btn-sm">&larr; Back to Catalog</a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger" style="margin-top: 16px;">
            {{ $errors->first() }}
        </div>
    @endif

    <form action="{{ route('erp.products.store') }}" method="POST" style="margin-top: 20px;">
        @csrf
        <div class="form-grid">
            <div class="form-group" style="grid-column: 1 / -1;">
                <label class="form-label">Hardware Product Name *</label>
                <input type="text" name="name" class="form-input" value="{{ old('name') }}" placeholder="e.g., AMD Ryzen 7 7800X3D or ASUS ROG STRIX B650-A" required>
            </div>

            <div class="form-group">
                <label class="form-label">Category *</label>
                <select name="category" class="form-input" required>
                    <option value="">-- Select Category --</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}" {{ old('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Brand</label>
                <input type="text" name="brand" class="form-input" value="{{ old('brand') }}" placeholder="e.g., AMD, Intel, ASUS, Corsair">
            </div>

            <div class="form-group">
                <label class="form-label">Primary Supplier</label>
                <select name="supplier_id" class="form-input">
                    <option value="">-- Optional Default Supplier --</option>
                    @foreach($suppliers as $sup)
                        <option value="{{ $sup->id }}" {{ (string) old('supplier_id') === (string) $sup->id ? 'selected' : '' }}>
                            {{ $sup->company_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Warranty Period (Months)</label>
                <input type="number" name="warranty_months" class="form-input" value="{{ old('warranty_months', 36) }}" min="0">
            </div>

            <div class="form-group">
                <label class="form-label">CPU / Motherboard Socket Type</label>
                <input type="text" name="socket_type" list="socket-list" class="form-input" value="{{ old('socket_type') }}" placeholder="e.g., AM5, LGA1700 (if applicable)">
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
                        <option value="{{ $rt }}" {{ old('ram_type') === $rt ? 'selected' : '' }}>{{ $rt }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">TDP / Rated Wattage (W)</label>
                <input type="number" name="tdp_wattage" class="form-input" value="{{ old('tdp_wattage', 0) }}" min="0">
            </div>

            <div class="form-group">
                <label class="form-label">Performance Rating Score (0-100)</label>
                <input type="number" name="performance_score" class="form-input" value="{{ old('performance_score', 0) }}" min="0" max="100" placeholder="For CPU, GPU, RAM, Storage">
            </div>

            <div class="form-group">
                <label class="form-label">Low-Stock Safety Threshold *</label>
                <input type="number" name="low_stock_threshold" class="form-input" value="{{ old('low_stock_threshold', 5) }}" min="1" required>
            </div>

            <div class="form-group">
                <label class="form-label">Default Cost Price (RM) *</label>
                <input type="number" step="0.01" name="cost_price" class="form-input" value="{{ old('cost_price') }}" placeholder="0.00" min="0" required>
            </div>

            <div class="form-group">
                <label class="form-label">Standard Selling Price (RM) *</label>
                <input type="number" step="0.01" name="selling_price" class="form-input" value="{{ old('selling_price') }}" placeholder="0.00" min="0" required>
            </div>

            <div class="form-group" style="grid-column: 1 / -1;">
                <label class="form-label">Compatibility & Technical Specifications</label>
                <textarea name="specs" class="form-input" rows="3" placeholder="e.g., Socket: AM5, Form Factor: ATX, TDP: 120W, PCIe 5.0 x16, RAM: DDR5 6000MHz">{{ old('specs') }}</textarea>
            </div>
        </div>

        <div style="margin-top: 24px; display: flex; justify-content: flex-end; gap: 10px;">
            <a href="{{ route('erp.products.index') }}" class="btn btn-outline">Cancel</a>
            <button type="submit" class="btn btn-primary">Save Product</button>
        </div>
    </form>
</div>
@endsection
