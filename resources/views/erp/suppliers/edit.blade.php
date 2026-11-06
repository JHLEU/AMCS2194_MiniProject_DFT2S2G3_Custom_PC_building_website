@extends('layouts.erp')

@section('title', 'Edit Supplier: ' . $supplier->company_name)
@section('page-title', 'Edit Supplier Profile')

@section('content')
<div class="card" style="max-width: 750px; margin: 0 auto;">
    <div class="card-header">
        <span class="card-title" style="margin: 0;">Edit Supplier: {{ $supplier->company_name }}</span>
        <a href="{{ route('erp.suppliers.index') }}" class="btn btn-outline btn-sm">&larr; Back to Suppliers</a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger" style="margin-top: 16px;">
            {{ $errors->first() }}
        </div>
    @endif

    <form action="{{ route('erp.suppliers.update', $supplier) }}" method="POST" style="margin-top: 20px;">
        @csrf
        @method('PUT')

        <div class="form-grid">
            <div class="form-group" style="grid-column: 1 / -1;">
                <label class="form-label">Company Name *</label>
                <input type="text" name="company_name" class="form-input" value="{{ old('company_name', $supplier->company_name) }}" required>
            </div>

            <div class="form-group">
                <label class="form-label">Contact Person Name *</label>
                <input type="text" name="contact_person" class="form-input" value="{{ old('contact_person', $supplier->contact_person) }}" required>
            </div>

            <div class="form-group">
                <label class="form-label">Phone Number *</label>
                <input type="text" name="phone" class="form-input" value="{{ old('phone', $supplier->phone) }}" required>
            </div>

            <div class="form-group" style="grid-column: 1 / -1;">
                <label class="form-label">Email Address *</label>
                <input type="email" name="email" class="form-input" value="{{ old('email', $supplier->email) }}" required>
            </div>

            <div class="form-group" style="grid-column: 1 / -1;">
                <label class="form-label">Physical Warehouse / Office Address</label>
                <textarea name="address" class="form-input" rows="3">{{ old('address', $supplier->address) }}</textarea>
            </div>
        </div>

        <div style="margin-top: 24px; display: flex; justify-content: flex-end; gap: 10px;">
            <a href="{{ route('erp.suppliers.mapping', $supplier) }}" class="btn btn-outline">Manage Hardware Mapping</a>
            <a href="{{ route('erp.suppliers.index') }}" class="btn btn-outline">Cancel</a>
            <button type="submit" class="btn btn-primary">Update Profile</button>
        </div>
    </form>
</div>
@endsection
