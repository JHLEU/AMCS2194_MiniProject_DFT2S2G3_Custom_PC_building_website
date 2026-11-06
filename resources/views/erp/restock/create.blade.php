@extends('layouts.erp')

@section('title', 'Draft Restock Purchase Order')
@section('page-title', 'Draft Restock Purchase Order')

@section('content')
<!-- Hidden template select for fallback -->
<select id="global-products-template" style="display: none;">
    <option value="">-- Choose Hardware Product --</option>
    @foreach($allProducts as $p)
        <option value="{{ $p->id }}" data-price="{{ $p->cost_price }}" {{ $preselectedProduct === (int) $p->id ? 'selected' : '' }}>
            {{ $p->name }} ({{ $p->category }}) - Catalog: RM {{ number_format((float) $p->cost_price, 2) }}
        </option>
    @endforeach
</select>

<div class="card" style="max-width: 980px; margin: 0 auto;">
    <div class="card-header">
        <span class="card-title" style="margin: 0;">Draft New Restock Purchase Order</span>
        <a href="{{ route('erp.restock.index') }}" class="btn btn-outline btn-sm">&larr; Back to Orders</a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger" style="margin-top: 16px;">
            {{ $errors->first() }}
        </div>
    @endif

    <form action="{{ route('erp.restock.store') }}" method="POST" id="restock-order-form" style="margin-top: 20px;" data-supplier-products-url="{{ route('erp.restock.supplier-products') }}" data-preselected-product="{{ $preselectedProduct }}">
        @csrf
        <div class="form-grid">
            <div class="form-group">
                <label class="form-label">Vendor / Supplier *</label>
                <select name="supplier_id" id="restock-supplier-select" class="form-input" required>
                    <option value="">-- Choose Supplier --</option>
                    @foreach($suppliers as $s)
                        <option value="{{ $s->id }}" {{ (int) old('supplier_id', $preselectedSupplier) === (int) $s->id ? 'selected' : '' }}>
                            {{ $s->company_name }} ({{ (int) $s->mapped_products_count }} mapped products)
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Order Date *</label>
                <input type="date" name="order_date" class="form-input" value="{{ old('order_date', date('Y-m-d')) }}" required>
            </div>

            <div class="form-group">
                <label class="form-label">Expected Delivery Date</label>
                <input type="date" name="expected_date" class="form-input" value="{{ old('expected_date', date('Y-m-d', strtotime('+3 days'))) }}">
            </div>

            <div class="form-group">
                <label class="form-label">Order Notes / Purchase Instructions</label>
                <input type="text" name="notes" class="form-input" value="{{ old('notes') }}" placeholder="e.g., Urgent restock for weekend custom builds">
            </div>
        </div>

        <!-- Line Items Table -->
        <div style="margin-top: 24px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                <h3 style="font-size: 1rem; font-weight: 700; color: #fff;">Purchase Order Line Items</h3>
                <button type="button" id="add-restock-item-btn" class="btn btn-outline btn-sm">+ Add Item Row</button>
            </div>

            <div class="table-responsive" style="border: 1px solid var(--border-color); border-radius: var(--radius-md);">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Hardware Product</th>
                            <th style="width: 130px;">Quantity</th>
                            <th style="width: 170px;">Unit Cost (RM)</th>
                            <th style="width: 170px;">Line Subtotal</th>
                            <th style="width: 50px;"></th>
                        </tr>
                    </thead>
                    <tbody id="restock-items-table-body">
                        <!-- Populated dynamically via JS -->
                    </tbody>
                    <tfoot>
                        <tr style="background: rgba(15, 23, 42, 0.6); font-weight: 700;">
                            <td colspan="3" class="text-right" style="padding: 14px;">Grand Total Purchase Cost:</td>
                            <td class="font-mono" style="font-size: 1.1rem; color: var(--accent); padding: 14px;">
                                RM <span id="restock-grand-total">0.00</span>
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div style="margin-top: 24px; display: flex; justify-content: flex-end; gap: 10px;">
            <a href="{{ route('erp.restock.index') }}" class="btn btn-outline">Cancel</a>
            <button type="submit" class="btn btn-primary">Create Purchase Order</button>
        </div>
    </form>
</div>
@endsection
