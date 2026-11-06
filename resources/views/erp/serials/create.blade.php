@extends('layouts.erp')

@section('title', 'Register Serial Numbers')
@section('page-title', 'Register Physical Serial Numbers')

@section('content')
<div class="card" style="max-width: 750px; margin: 0 auto;">
    <div class="card-header">
        <span class="card-title" style="margin: 0;">Register Physical Serial Numbers</span>
        <a href="{{ route('erp.serials.index') }}" class="btn btn-outline btn-sm">&larr; Back to SN Ledger</a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger" style="margin-top: 16px;">
            {{ $errors->first() }}
        </div>
    @endif

    <form action="{{ route('erp.serials.store') }}" method="POST" style="margin-top: 20px;">
        @csrf
        <div class="form-group">
            <label class="form-label">Target Hardware Component *</label>
            <select name="product_id" class="form-input" id="serial-product-select" required>
                <option value="">-- Choose Product to Bind Serial Numbers --</option>
                @foreach($products as $p)
                    <option value="{{ $p->id }}" data-cost="{{ $p->cost_price }}" {{ (int) old('product_id', $preselectedProduct) === (int) $p->id ? 'selected' : '' }}>
                        {{ $p->name }} ({{ $p->category }}) - Current In-Stock: {{ (int) $p->in_stock_count }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-grid">
            <div class="form-group">
                <label class="form-label">Batch Cost Price per Unit (RM)</label>
                <input type="number" step="0.01" name="batch_cost" id="batch-cost-input" class="form-input" value="{{ old('batch_cost') }}" placeholder="Defaults to catalog cost price">
                <small style="color: var(--text-muted); font-size: 0.78rem;">Used for exact batch inventory valuation.</small>
            </div>

            <div class="form-group">
                <label class="form-label">Initial Status *</label>
                <select name="status" class="form-input" required>
                    @foreach($statuses as $st)
                        <option value="{{ $st }}" {{ old('status', 'In-Stock') === $st ? 'selected' : '' }}>{{ $st }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="form-group" style="margin-top: 8px;">
            <label class="form-label">Entry Mode</label>
            <div style="display: flex; gap: 20px; align-items: center; padding: 6px 0;">
                <label style="cursor: pointer; display: flex; align-items: center; gap: 6px;">
                    <input type="radio" name="mode" value="single" {{ old('mode', 'single') === 'single' ? 'checked' : '' }}> Single Serial Number
                </label>
                <label style="cursor: pointer; display: flex; align-items: center; gap: 6px;">
                    <input type="radio" name="mode" value="bulk" {{ old('mode') === 'bulk' ? 'checked' : '' }}> Bulk Batch Entry (Multi-line)
                </label>
            </div>
        </div>

        <!-- Single SN Section -->
        <div id="single-sn-section" class="form-group" style="{{ old('mode', 'single') === 'bulk' ? 'display: none;' : '' }}">
            <label class="form-label">Serial Number Code (SN) *</label>
            <input type="text" name="sn_code" class="form-input font-mono" value="{{ old('sn_code') }}" placeholder="e.g., SN-7800X3D-98234 or barcode scan">
        </div>

        <!-- Bulk SN Section -->
        <div id="bulk-sn-section" class="form-group" style="{{ old('mode') === 'bulk' ? 'display: block;' : 'display: none;' }}">
            <label class="form-label">Batch Serial Numbers (Paste one code per line or comma-separated) *</label>
            <textarea name="bulk_sn_codes" class="form-input font-mono" rows="6" placeholder="SN-7800X3D-1001&#10;SN-7800X3D-1002&#10;SN-7800X3D-1003">{{ old('bulk_sn_codes') }}</textarea>
            <small style="color: var(--text-muted); font-size: 0.78rem;">Separate multiple serial numbers with line breaks or commas. Duplicate codes will be skipped.</small>
        </div>

        <div style="margin-top: 24px; display: flex; justify-content: flex-end; gap: 10px;">
            <a href="{{ route('erp.serials.index') }}" class="btn btn-outline">Cancel</a>
            <button type="submit" class="btn btn-primary">Save Serial Number(s)</button>
        </div>
    </form>
</div>
@endsection

