@extends('layouts.erp')

@section('title', 'Cashflow Ledger & Transactions')
@section('page-title', 'Unified Cashflow Ledger')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
    <div>
        <div style="display: flex; align-items: center; gap: 10px;">
            <a href="{{ route('erp.finance.index') }}" class="btn btn-outline btn-sm">&larr; Financial Dashboard</a>
        </div>
        <h1 style="font-size: 1.5rem; font-weight: 700; color: #fff; margin-top: 6px;">
            📋 Unified Cash Inflow & Outflow Ledger
        </h1>
        <p style="color: var(--text-muted); font-size: 0.9rem;">
            Chronological audit stream of customer order payments (+) and supplier restock disbursements (-).
        </p>
    </div>
    <div style="display: flex; gap: 10px;">
        <a href="{{ route('erp.finance.pnl') }}" class="btn btn-outline btn-sm">
            📊 P&L Statement
        </a>
    </div>
</div>

<!-- Cash Balance Summary -->
<div class="stat-grid" style="margin-bottom: 24px;">
    <div class="stat-card">
        <div>
            <div class="stat-label">Total Verified Inflows</div>
            <div class="stat-val" style="color: var(--success);">
                + RM {{ number_format($metrics['payments_collected'], 2) }}
            </div>
            <div style="font-size: 0.75rem; color: var(--text-muted);">
                Customer sales & payment settlements
            </div>
        </div>
        <div class="stat-icon" style="color: var(--success);">📥</div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-label">Total Procurement Outflows</div>
            <div class="stat-val" style="color: var(--danger);">
                - RM {{ number_format($metrics['procurement_outflow'], 2) }}
            </div>
            <div style="font-size: 0.75rem; color: var(--text-muted);">
                Completed vendor purchase orders
            </div>
        </div>
        <div class="stat-icon" style="color: var(--danger);">📤</div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-label">Net Operating Position</div>
            <div class="stat-val" style="color: {{ $metrics['net_cashflow'] >= 0 ? '#34d399' : '#f87171' }};">
                RM {{ number_format($metrics['net_cashflow'], 2) }}
            </div>
            <div style="font-size: 0.75rem; color: var(--text-muted);">
                Net liquid cash balance
            </div>
        </div>
        <div class="stat-icon" style="color: #34d399;">⚖️</div>
    </div>
</div>

<!-- Filter Tabs -->
<div style="display: flex; gap: 10px; margin-bottom: 20px;">
    <a href="{{ route('erp.finance.cashflow') }}"
       class="btn btn-sm {{ $typeFilter === null ? 'btn-primary' : 'btn-outline' }}">
        All Transactions ({{ $ledger->count() }})
    </a>
    <a href="{{ route('erp.finance.cashflow', ['type' => 'Inflow']) }}"
       class="btn btn-sm {{ $typeFilter === 'Inflow' ? 'btn-primary' : 'btn-outline' }}"
       style="{{ $typeFilter === 'Inflow' ? 'background: #10b981; color: #fff;' : '' }}">
        📥 Customer Inflows (+)
    </a>
    <a href="{{ route('erp.finance.cashflow', ['type' => 'Outflow']) }}"
       class="btn btn-sm {{ $typeFilter === 'Outflow' ? 'btn-primary' : 'btn-outline' }}"
       style="{{ $typeFilter === 'Outflow' ? 'background: #ff334b; color: #fff;' : '' }}">
        📤 Supplier Outflows (-)
    </a>
</div>

<!-- Ledger Table -->
<div class="card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Timestamp</th>
                    <th>Direction</th>
                    <th>Reference</th>
                    <th>Description</th>
                    <th>Payment / Channel</th>
                    <th style="text-align: right;">Amount (MYR)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($ledger as $tx)
                    <tr>
                        <td style="font-size: 0.85rem; color: var(--text-muted); white-space: nowrap;">
                            {{ $tx['timestamp']->format('Y-m-d H:i') }}
                        </td>
                        <td>
                            <span class="badge {{ $tx['badge_class'] }}">
                                {{ $tx['type'] === 'Inflow' ? '+ Inflow' : '- Outflow' }}
                            </span>
                        </td>
                        <td style="font-family: monospace; font-weight: 700; color: #fff;">
                            {{ $tx['reference'] }}
                        </td>
                        <td style="font-size: 0.9rem;">
                            {{ $tx['description'] }}
                        </td>
                        <td style="font-size: 0.85rem; color: var(--text-muted);">
                            {{ $tx['method'] }}
                        </td>
                        <td style="text-align: right; font-weight: 800; font-size: 1rem; color: {{ $tx['type'] === 'Inflow' ? 'var(--success)' : 'var(--danger)' }};">
                            {{ $tx['type'] === 'Inflow' ? '+' : '-' }} RM {{ number_format($tx['amount'], 2) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 32px;">
                            No financial transactions found for the selected filter.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
