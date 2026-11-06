@extends('layouts.erp')

@section('title', 'Supplier Directory')
@section('page-title', 'Supplier Directory & Vendor Profiles')

@section('content')
<div class="filter-bar">
    <input type="text" data-table-search="#suppliers-table" class="form-input" placeholder="Search suppliers by company name, contact person, or email..." style="max-width: 380px;">

    <div style="margin-left: auto;">
        <a href="{{ route('erp.suppliers.create') }}" class="btn btn-primary">+ Register New Supplier</a>
    </div>
</div>

<div class="card" style="padding: 0; overflow: hidden;">
    <div class="card-header">
        <span class="card-title" style="margin: 0;">🏢 Supplier Directory & Verified Vendors</span>
        <span style="font-size: 0.85rem; color: var(--text-muted);">{{ $suppliers->count() }} Registered Suppliers</span>
    </div>

    @if($suppliers->isEmpty())
        <div style="padding: 40px; text-align: center; color: var(--text-muted);">
            <h3>No Suppliers Registered</h3>
            <p style="margin-top: 6px;">Register suppliers to establish hardware mappings and issue restock purchase orders.</p>
            <a href="{{ route('erp.suppliers.create') }}" class="btn btn-primary" style="margin-top: 14px;">+ Add Supplier</a>
        </div>
    @else
        <div class="table-responsive">
            <table class="table" id="suppliers-table">
                <thead>
                    <tr>
                        <th>Company Name</th>
                        <th>Contact Person</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Address / Location</th>
                        <th>Mapped Hardware</th>
                        <th>Total Orders</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($suppliers as $s)
                        <tr>
                            <td><strong>{{ $s->company_name }}</strong></td>
                            <td>{{ $s->contact_person ?: '—' }}</td>
                            <td class="font-mono">{{ $s->phone ?: '—' }}</td>
                            <td><a href="mailto:{{ $s->email }}">{{ $s->email }}</a></td>
                            <td style="max-width: 220px; font-size: 0.8rem; color: var(--text-muted);">
                                {{ $s->address ?: '—' }}
                            </td>
                            <td>
                                <a href="{{ route('erp.suppliers.mapping', $s) }}" class="badge badge-info">
                                    {{ (int) $s->mapped_products_count }} products &rarr;
                                </a>
                            </td>
                            <td>
                                <span class="badge badge-neutral">
                                    {{ (int) $s->total_orders_count }} orders
                                </span>
                            </td>
                            <td class="text-right" style="white-space: nowrap;">
                                <a href="{{ route('erp.suppliers.mapping', $s) }}" class="btn btn-outline btn-sm">Hardware</a>
                                <a href="{{ route('erp.restock.create', ['supplier_id' => $s->id]) }}" class="btn btn-primary btn-sm">+ Order</a>
                                <a href="{{ route('erp.suppliers.edit', $s) }}" class="btn btn-outline btn-sm">Edit</a>
                                <form action="{{ route('erp.suppliers.destroy', $s) }}" method="POST" style="display: inline-block;" onsubmit="return confirm('Delete supplier {{ addslashes($s->company_name) }}?');">
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
