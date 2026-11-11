@extends('layouts.erp')

@section('title', 'Serial Number Tracking')
@section('page-title', 'Physical Serial Number (SN) Tracking')

@section('content')
<div class="filter-bar">
    <form action="{{ route('erp.serials.index') }}" method="GET" style="display: flex; flex-wrap: wrap; gap: 10px; align-items: center; flex: 1;">
        <input type="text" name="search" class="form-input" placeholder="Search by SN code or Product..." value="{{ $search }}" style="max-width: 260px;">

        <select name="status" class="form-input" style="max-width: 165px;" onchange="this.form.submit()">
            <option value="">All Statuses</option>
            @foreach($statuses as $st)
                <option value="{{ $st }}" {{ $selectedStatus === $st ? 'selected' : '' }}>{{ $st }}</option>
            @endforeach
        </select>

        <select name="product_id" class="form-input" style="max-width: 250px;" onchange="this.form.submit()">
            <option value="">All Hardware Products</option>
            @foreach($products as $p)
                <option value="{{ $p->id }}" {{ $selectedProduct === (int) $p->id ? 'selected' : '' }}>
                    {{ $p->name }} ({{ $p->category }})
                </option>
            @endforeach
        </select>

        <button type="submit" class="btn btn-outline btn-sm">Filter</button>
        @if($search || $selectedStatus || $selectedProduct)
            <a href="{{ route('erp.serials.index') }}" class="btn btn-outline btn-sm">Clear</a>
        @endif
    </form>

    <div>
        <a href="{{ route('erp.serials.create') }}" class="btn btn-primary">+ Register Serial Numbers</a>
    </div>
</div>

<div class="card" style="padding: 0; overflow: hidden;">
    <div class="card-header">
        <span class="card-title" style="margin: 0;">Physical Item Serial Number Ledger</span>
        <span style="font-size: 0.85rem; color: var(--text-muted);">{{ $serials->count() }} Records</span>
    </div>

    @if($serials->isEmpty())
        <div style="padding: 40px; text-align: center; color: var(--text-muted);">
            <h3>No Serial Numbers Found</h3>
            <p style="margin-top: 6px;">Register new physical units to begin tracking serial numbers and calculating batch costing.</p>
            <a href="{{ route('erp.serials.create') }}" class="btn btn-primary" style="margin-top: 14px;">+ Register Serial Numbers</a>
        </div>
    @else
        <div class="table-responsive">
            <table class="table" id="serials-table">
                <thead>
                    <tr>
                        <th>Serial Number (SN)</th>
                        <th>Hardware Product</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Batch Unit Cost</th>
                        <th>Restock Ref</th>
                        <th>Registered Date</th>
                        <th class="text-right">Change Status / Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($serials as $sn)
                        <tr>
                            <td>
                                <strong class="font-mono" style="font-size: 0.92rem; color: #fff;">{{ $sn->sn_code }}</strong>
                            </td>
                            <td>
                                <a href="{{ route('erp.products.edit', $sn->product_id) }}" style="font-weight: 600;">
                                    {{ $sn->product->name ?? '—' }}
                                </a>
                            </td>
                            <td>
                                <span class="badge badge-neutral">{{ $sn->product->category ?? '—' }}</span>
                            </td>
                            <td>
                                @php
                                    $statusClass = 'badge-instock';
                                    if ($sn->status === 'Reserved') $statusClass = 'badge-info';
                                    if ($sn->status === 'Sold')     $statusClass = 'badge-sold';
                                    if ($sn->status === 'RMA')      $statusClass = 'badge-rma';
                                    if ($sn->status === 'Lost')     $statusClass = 'badge-lowstock';
                                @endphp
                                <span class="badge {{ $statusClass }}">{{ $sn->status }}</span>
                            </td>
                            <td class="font-mono">RM {{ number_format((float) $sn->batch_cost, 2) }}</td>
                            <td>
                                @if($sn->restockOrder)
                                    <a href="{{ route('erp.restock.show', $sn->restockOrder) }}" class="font-mono" style="font-size: 0.8rem;">
                                        {{ $sn->restockOrder->order_code }}
                                    </a>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td style="font-size: 0.8rem; color: var(--text-muted);">
                                {{ $sn->created_at?->format('M d, Y H:i') }}
                            </td>
                            <td class="text-right" style="white-space: nowrap;">
                                <form action="{{ route('erp.serials.status', $sn) }}" method="POST" style="display: inline-flex; align-items: center; gap: 4px;">
                                    @csrf
                                    @method('PATCH')
                                    <select name="status" class="form-input" style="padding: 4px 8px; font-size: 0.75rem; width: 115px;" onchange="this.form.submit()">
                                        @foreach($statuses as $st)
                                            <option value="{{ $st }}" {{ $sn->status === $st ? 'selected' : '' }}>{{ $st }}</option>
                                        @endforeach
                                    </select>
                                </form>

                                <form action="{{ route('erp.serials.destroy', $sn) }}" method="POST" style="display: inline-block;" onsubmit="return confirm('Delete serial number {{ addslashes($sn->sn_code) }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline btn-sm" style="color: var(--danger); border-color: rgba(239,68,68,0.4); padding: 4px 8px;">&times;</button>
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
