@extends('layouts.erp')

@section('title', 'Inventory Costing & Valuation')
@section('page-title', 'Inventory Costing & Valuation Analysis')

@section('content')
<div class="stat-grid">
    <div class="stat-card">
        <div>
            <div class="stat-label">Total Inventory Asset Value</div>
            <div class="stat-val" style="color: var(--primary);">
                RM {{ number_format((float) ($costingData['overall']['total_valuation'] ?? 0), 2) }}
            </div>
        </div>
        <div class="stat-icon"></div>
    </div>
    <div class="stat-card">
        <div>
            <div class="stat-label">In-Stock Physical Units</div>
            <div class="stat-val" style="color: var(--success);">
                {{ (int) ($costingData['overall']['total_units'] ?? 0) }}
            </div>
        </div>
        <div class="stat-icon"></div>
    </div>
    <div class="stat-card">
        <div>
            <div class="stat-label">Average Unit Cost</div>
            <div class="stat-val" style="color: var(--accent);">
                RM {{ number_format((float) ($costingData['overall']['avg_unit_cost'] ?? 0), 2) }}
            </div>
        </div>
        <div class="stat-icon"></div>
    </div>
</div>

<!-- Category Breakdown -->
<div class="card" style="padding: 0; overflow: hidden;">
    <div class="card-header">
        <span class="card-title" style="margin: 0;">Category Asset Distribution</span>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Hardware Category</th>
                    <th>In-Stock Units</th>
                    <th>% of Total Valuation</th>
                    <th class="text-right">Total Category Value</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $totalVal = (float) ($costingData['overall']['total_valuation'] ?? 0);
                    $divisor = $totalVal > 0 ? $totalVal : 1.0;
                @endphp
                @forelse($costingData['by_category'] as $cat)
                    @php
                        $pct = round(((float) $cat['category_valuation'] / $divisor) * 100, 1);
                    @endphp
                    <tr>
                        <td><strong>{{ $cat['category'] }}</strong></td>
                        <td>{{ (int) $cat['in_stock_count'] }} units</td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div style="background: rgba(148, 163, 184, 0.2); width: 130px; height: 8px; border-radius: 4px; overflow: hidden;">
                                    <div style="background: var(--primary); width: {{ min(100, $pct) }}%; height: 100%;"></div>
                                </div>
                                <span style="font-size: 0.8rem; color: var(--text-muted);">{{ $pct }}%</span>
                            </div>
                        </td>
                        <td class="text-right font-mono" style="font-weight: 700;">
                            RM {{ number_format((float) $cat['category_valuation'], 2) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" style="text-align: center; padding: 24px; color: var(--text-muted);">No in-stock components found to calculate valuation.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Product-Level Cost Breakdown -->
<div class="card" style="padding: 0; overflow: hidden;">
    <div class="card-header">
        <span class="card-title" style="margin: 0;">Granular Hardware Item Cost Breakdown</span>
        <input type="text" data-table-search="#costing-table" class="form-input" placeholder="Filter items..." style="max-width: 240px; padding: 6px 12px; font-size: 0.82rem;">
    </div>
    <div class="table-responsive">
        <table class="table" id="costing-table">
            <thead>
                <tr>
                    <th>Product Item</th>
                    <th>Category</th>
                    <th>In-Stock Units</th>
                    <th>Min Batch Cost</th>
                    <th>Max Batch Cost</th>
                    <th>Avg Batch Cost</th>
                    <th class="text-right">Total Item Valuation</th>
                </tr>
            </thead>
            <tbody>
                @forelse($costingData['by_product'] as $prod)
                    <tr>
                        <td><strong>{{ $prod['product_name'] }}</strong></td>
                        <td><span class="badge badge-neutral">{{ $prod['category'] }}</span></td>
                        <td><span class="badge badge-instock">{{ (int) $prod['in_stock_count'] }} In-Stock</span></td>
                        <td class="font-mono text-muted">RM {{ number_format((float) $prod['min_batch_cost'], 2) }}</td>
                        <td class="font-mono text-muted">RM {{ number_format((float) $prod['max_batch_cost'], 2) }}</td>
                        <td class="font-mono" style="font-weight: 600;">RM {{ number_format((float) $prod['avg_batch_cost'], 2) }}</td>
                        <td class="text-right font-mono" style="font-weight: 700; color: var(--accent);">
                            RM {{ number_format((float) $prod['total_product_valuation'], 2) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 24px; color: var(--text-muted);">No inventory data available.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
