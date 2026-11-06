@extends('layouts.erp')

@section('title', 'Register Supplier')
@section('page-title', 'Register New Vendor / Supplier')

@section('content')
<div class="card" style="max-width: 750px; margin: 0 auto;">
    <div class="card-header">
        <span class="card-title" style="margin: 0;">Register New Vendor / Supplier Profile</span>
        <a href="{{ route('erp.suppliers.index') }}" class="btn btn-outline btn-sm">&larr; Back to Suppliers</a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger" style="margin-top: 16px;">
            {{ $errors->first() }}
        </div>
    @endif

    <form action="{{ route('erp.suppliers.store') }}" method="POST" style="margin-top: 20px;">
        @csrf
        <div class="form-grid">
            <div class="form-group" style="grid-column: 1 / -1;">
                <label class="form-label">Company Name *</label>
                <input type="text" name="company_name" class="form-input" value="{{ old('company_name') }}" placeholder="e.g., TechData Distribution or ASUS APAC Direct" required>
            </div>

            <div class="form-group">
                <label class="form-label">Contact Person Name *</label>
                <input type="text" name="contact_person" class="form-input" value="{{ old('contact_person') }}" placeholder="e.g., Sarah Tan" required>
            </div>

            <div class="form-group">
                <label class="form-label">Phone Number *</label>
                <input type="text" name="phone" class="form-input" value="{{ old('phone') }}" placeholder="e.g., +60 12-345 6789" required>
            </div>

            <div class="form-group" style="grid-column: 1 / -1;">
                <label class="form-label">Email Address *</label>
                <input type="email" name="email" class="form-input" value="{{ old('email') }}" placeholder="orders@supplier-domain.com" required>
            </div>

            <div class="form-group" style="grid-column: 1 / -1;">
                <label class="form-label">Physical Warehouse / Office Address</label>
                <textarea name="address" class="form-input" rows="3" placeholder="Full address, city, state, postal code">{{ old('address') }}</textarea>
            </div>
        </div>

        <div style="margin-top: 24px; display: flex; justify-content: flex-end; gap: 10px;">
            <a href="{{ route('erp.suppliers.index') }}" class="btn btn-outline">Cancel</a>
            <button type="submit" class="btn btn-primary">Save Supplier Profile</button>
        </div>
    </form>
</div>
@endsection
