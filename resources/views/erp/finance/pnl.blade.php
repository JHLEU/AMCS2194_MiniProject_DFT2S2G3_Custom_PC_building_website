@extends('layouts.erp')

@section('title', 'Profit & Loss Statement')
@section('page-title', 'Profit & Loss (P&L) Statement')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
    <div>
        <div style="display: flex; align-items: center; gap: 10px;">
            <a href="{{ route('erp.finance.index') }}" class="btn btn-outline btn-sm">&larr; Financial Dashboard</a>
        </div>
        <h1 style="font-size: 1.5rem; font-weight: 700; color: #fff; margin-top: 6px;">
            📊 {{ $selectedYear }} Annual & Monthly Profit/Loss Statement
        </h1>
        <p style="color: var(--text-muted); font-size: 0.9rem;">
            Formal 12-month accounting breakdown of Sales Revenue, Cost of Goods Sold (COGS), Gross Profit, and Operating Cashflow.
        </p>
    </div>

    <!-- Year Selector Form -->
    <div>
        <form method="GET" action="{{ route('erp.finance.pnl') }}" style="display: flex; gap: 10px; align-items: center;">
            <label for="year" style="font-size: 0.85rem; color: var(--text-muted); margin: 0;">Fiscal Year:</label>
            <select name="year" id="year" class="form-input" style="width: 120px;" onchange="this.form.submit()">
                @foreach($availableYears as $yr)
                    <option value="{{ $yr }}" {{ (int) $selectedYear === (int) $yr ? 'selected' : '' }}>
                        {{ $yr }}
                    </option>
                @endforeach
            </select>
        </form>
    </div>
</div>

<!-- Annual P&L Summary Cards -->
<div class="stat-grid" style="margin-bottom: 24px;">
    <div class="stat-card">
        <div>
            <div class="stat-label">{{ $selectedYear }} Annual Revenue</div>
            <div class="stat-val" style="color: var(--accent);">
                RM {{ number_format($pnlReport['totals']['revenue'], 2) }}
            </div>
            <div style="font-size: 0.75rem; color: var(--text-muted);">
                {{ $pnlReport['totals']['orders_count'] }} orders fulfilled
            </div>
        </div>
        <div class="stat-icon" style="color: var(--accent);">💵</div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-label">Total Hardware Cost (COGS)</div>
            <div class="stat-val" style="color: var(--text-muted);">
                RM {{ number_format($pnlReport['totals']['cogs'], 2) }}
            </div>
            <div style="font-size: 0.75rem; color: var(--text-muted);">
                Allocated component batch costs
            </div>
        </div>
        <div class="stat-icon" style="color: var(--warning);">📦</div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-label">Annual Gross Profit</div>
            <div class="stat-val" style="color: var(--success);">
                RM {{ number_format($pnlReport['totals']['gross_profit'], 2) }}
            </div>
            <div style="font-size: 0.75rem; color: var(--success); font-weight: 700;">
                ● {{ $pnlReport['totals']['gross_margin_pct'] }}% Net Gross Margin
            </div>
        </div>
        <div class="stat-icon" style="color: var(--success);">📈</div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-label">Procurement Disbursements</div>
            <div class="stat-val" style="color: var(--danger);">
                RM {{ number_format($pnlReport['totals']['procurement'], 2) }}
            </div>
            <div style="font-size: 0.75rem; color: var(--text-muted);">
                Vendor Purchase Orders settled
            </div>
        </div>
        <div class="stat-icon" style="color: var(--danger);">🚚</div>
    </div>
</div>

<!-- 12-Month Detailed P&L Table -->
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
        <h3 class="card-title" style="margin: 0;">📋 Calendar Month-by-Month Statement ({{ $selectedYear }})</h3>
        <span style="font-size: 0.75rem; color: var(--text-muted);">Amounts in MYR (Ringgit Malaysia)</span>
    </div>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Month</th>
                    <th style="text-align: center;">Orders</th>
                    <th style="text-align: right;">Gross Revenue</th>
                    <th style="text-align: right;">Cost of Goods (COGS)</th>
                    <th style="text-align: right;">Gross Profit</th>
                    <th style="text-align: right;">Margin %</th>
                    <th style="text-align: right;">Procurement Outflow</th>
                    <th style="text-align: right;">Net Operating Cash</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pnlReport['months'] as $m)
                    <tr>
                        <td style="font-weight: 700; color: #fff;">
                            {{ $m['full_month_name'] }}
                        </td>
                        <td style="text-align: center;">
                            @if($m['orders_count'] > 0)
                                <span class="badge badge-secondary">{{ $m['orders_count'] }}</span>
                            @else
                                <span style="color: var(--text-muted);">—</span>
                            @endif
                        </td>
                        <td style="text-align: right; font-weight: 600; color: {{ $m['revenue'] > 0 ? 'var(--accent)' : 'var(--text-muted)' }};">
                            RM {{ number_format($m['revenue'], 2) }}
                        </td>
                        <td style="text-align: right; color: var(--text-muted);">
                            RM {{ number_format($m['cogs'], 2) }}
                        </td>
                        <td style="text-align: right; font-weight: 700; color: {{ $m['gross_profit'] > 0 ? 'var(--success)' : ($m['gross_profit'] < 0 ? 'var(--danger)' : 'var(--text-muted)') }};">
                            RM {{ number_format($m['gross_profit'], 2) }}
                        </td>
                        <td style="text-align: right; font-weight: 700; color: {{ $m['gross_margin_pct'] > 0 ? 'var(--info)' : 'var(--text-muted)' }};">
                            {{ $m['gross_margin_pct'] }}%
                        </td>
                        <td style="text-align: right; color: {{ $m['procurement'] > 0 ? 'var(--danger)' : 'var(--text-muted)' }};">
                            RM {{ number_format($m['procurement'], 2) }}
                        </td>
                        <td style="text-align: right; font-weight: 700; color: {{ $m['net_cashflow'] > 0 ? 'var(--success)' : ($m['net_cashflow'] < 0 ? 'var(--danger)' : 'var(--text-muted)') }};">
                            RM {{ number_format($m['net_cashflow'], 2) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="border-top: 2px solid var(--border-color); background: rgba(255,255,255,0.03);">
                    <td style="font-weight: 800; font-size: 1rem; color: #fff;">
                        Annual Totals ({{ $selectedYear }}):
                    </td>
                    <td style="text-align: center; font-weight: 800;">
                        {{ $pnlReport['totals']['orders_count'] }}
                    </td>
                    <td style="text-align: right; font-weight: 800; font-size: 1.05rem; color: var(--accent);">
                        RM {{ number_format($pnlReport['totals']['revenue'], 2) }}
                    </td>
                    <td style="text-align: right; font-weight: 700; color: var(--text-muted);">
                        RM {{ number_format($pnlReport['totals']['cogs'], 2) }}
                    </td>
                    <td style="text-align: right; font-weight: 800; font-size: 1.05rem; color: var(--success);">
                        RM {{ number_format($pnlReport['totals']['gross_profit'], 2) }}
                    </td>
                    <td style="text-align: right; font-weight: 800; color: var(--info);">
                        {{ $pnlReport['totals']['gross_margin_pct'] }}%
                    </td>
                    <td style="text-align: right; font-weight: 700; color: var(--danger);">
                        RM {{ number_format($pnlReport['totals']['procurement'], 2) }}
                    </td>
                    <td style="text-align: right; font-weight: 800; font-size: 1.05rem; color: {{ $pnlReport['totals']['net_cashflow'] >= 0 ? 'var(--success)' : 'var(--danger)' }};">
                        RM {{ number_format($pnlReport['totals']['net_cashflow'], 2) }}
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection
