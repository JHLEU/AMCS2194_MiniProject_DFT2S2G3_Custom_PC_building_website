@extends('layouts.erp')

@section('title', 'Financial Overview & Cashflow')
@section('page-title', 'Financial Analytics & Profit/Loss')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
    <div>
        <h1 style="font-size: 1.5rem; font-weight: 700; color: #fff; margin-bottom: 4px;">
            Financial Health & Cashflow Dashboard
        </h1>
        <p style="color: var(--text-muted); font-size: 0.9rem;">
            Real-time Profit & Loss accounting, gross margins tracked from physical batch costs, and capital expenditure.
        </p>
    </div>
    <div style="display: flex; gap: 10px;">
        <a href="{{ route('erp.finance.pnl') }}" class="btn btn-outline btn-sm">
            Monthly P&L Statement
        </a>
        <a href="{{ route('erp.finance.cashflow') }}" class="btn btn-primary btn-sm">
            Cashflow Ledger Stream
        </a>
    </div>
</div>

<!-- Primary Financial KPIs -->
<div class="stat-grid" style="margin-bottom: 24px;">
    <div class="stat-card">
        <div>
            <div class="stat-label">Gross Revenue</div>
            <div class="stat-val" style="color: var(--accent);">
                RM {{ number_format($metrics['gross_revenue'], 2) }}
            </div>
            <div style="font-size: 0.75rem; color: var(--text-muted);">
                {{ $metrics['total_orders_count'] }} orders (Avg: RM {{ number_format($metrics['average_order_value'], 2) }})
            </div>
        </div>
        <div class="stat-icon" style="color: var(--accent);"></div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-label">Cost of Goods Sold (COGS)</div>
            <div class="stat-val" style="color: var(--text-muted);">
                RM {{ number_format($metrics['cost_of_goods_sold'], 2) }}
            </div>
            <div style="font-size: 0.75rem; color: var(--text-muted);">
                Hardware batch unit allocation
            </div>
        </div>
        <div class="stat-icon" style="color: var(--warning);"></div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-label">Gross Profit (Margin)</div>
            <div class="stat-val" style="color: var(--success);">
                RM {{ number_format($metrics['gross_profit'], 2) }}
            </div>
            <div style="font-size: 0.75rem; color: var(--success); font-weight: 700;">
                ● {{ $metrics['gross_margin_pct'] }}% Gross Margin
            </div>
        </div>
        <div class="stat-icon" style="color: var(--success);"></div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-label">Procurement Outflows</div>
            <div class="stat-val" style="color: var(--danger);">
                RM {{ number_format($metrics['procurement_outflow'], 2) }}
            </div>
            <div style="font-size: 0.75rem; color: var(--text-muted);">
                Completed Restock Orders
            </div>
        </div>
        <div class="stat-icon" style="color: var(--danger);"></div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-label">In-Stock Asset Valuation</div>
            <div class="stat-val" style="color: var(--info);">
                RM {{ number_format($metrics['inventory_valuation'], 2) }}
            </div>
            <div style="font-size: 0.75rem; color: var(--text-muted);">
                Warehouse Physical Inventory
            </div>
        </div>
        <div class="stat-icon" style="color: var(--info);"></div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-label">Net Operating Cashflow</div>
            <div class="stat-val" style="color: {{ $metrics['net_cashflow'] >= 0 ? '#34d399' : '#f87171' }}; font-size: 1.25rem;">
                RM {{ number_format($metrics['net_cashflow'], 2) }}
            </div>
            <div style="font-size: 0.75rem; color: var(--text-muted);">
                Inflow (RM {{ number_format($metrics['payments_collected'], 0) }}) - Outflow
            </div>
        </div>
        <div class="stat-icon" style="color: #34d399;"></div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1.6fr 1.4fr; gap: 24px; align-items: start;">
    <!-- Left Column: Category Performance & Gross Margin Ranking -->
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <h3 class="card-title" style="margin: 0;">Category Revenue & Margin Ranking</h3>
            <span style="font-size: 0.75rem; color: var(--text-muted);">Ranked by gross sales volume</span>
        </div>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Hardware Category</th>
                        <th style="text-align: center;">Units</th>
                        <th style="text-align: right;">Revenue</th>
                        <th style="text-align: right;">Cost (COGS)</th>
                        <th style="text-align: right;">Profit</th>
                        <th style="text-align: right;">Margin %</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $cat)
                        <tr>
                            <td>
                                <span class="badge badge-info">{{ $cat['category'] }}</span>
                            </td>
                            <td style="text-align: center;">{{ $cat['total_units_sold'] }}</td>
                            <td style="text-align: right; font-weight: 700; color: #fff;">
                                RM {{ number_format($cat['category_revenue'], 2) }}
                            </td>
                            <td style="text-align: right; color: var(--text-muted);">
                                RM {{ number_format($cat['category_cost'], 2) }}
                            </td>
                            <td style="text-align: right; font-weight: 700; color: {{ $cat['gross_profit'] >= 0 ? 'var(--success)' : 'var(--danger)' }};">
                                RM {{ number_format($cat['gross_profit'], 2) }}
                            </td>
                            <td style="text-align: right;">
                                <span style="font-weight: 700; color: {{ $cat['gross_margin_pct'] >= 20 ? 'var(--success)' : 'var(--warning)' }};">
                                    {{ $cat['gross_margin_pct'] }}%
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 24px;">
                                No category sales data recorded yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Right Column: Recent Financial Transactions & Cashflow Activity -->
    <div style="display: flex; flex-direction: column; gap: 24px;">
        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <h3 class="card-title" style="margin: 0;">Recent Cash Movements</h3>
                <a href="{{ route('erp.finance.cashflow') }}" style="font-size: 0.8rem; color: var(--info); text-decoration: none;">
                    View All &rarr;
                </a>
            </div>

            <div style="display: flex; flex-direction: column; gap: 10px;">
                @forelse($recentTransactions as $tx)
                    <div style="display: flex; justify-content: space-between; align-items: center; background: rgba(255,255,255,0.02); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px 14px; font-size: 0.85rem;">
                        <div>
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <span class="badge {{ $tx['badge_class'] }}" style="font-size: 0.7rem; padding: 2px 8px;">
                                    {{ $tx['type'] === 'Inflow' ? '+ INFLOW' : '- OUTFLOW' }}
                                </span>
                                <strong style="color: #fff; font-family: monospace;">{{ $tx['reference'] }}</strong>
                            </div>
                            <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 3px;">
                                {{ $tx['description'] }} &bull; {{ $tx['timestamp']->format('M d, H:i') }}
                            </div>
                        </div>
                        <div style="text-align: right;">
                            <div style="font-weight: 800; font-size: 0.95rem; color: {{ $tx['type'] === 'Inflow' ? 'var(--success)' : 'var(--danger)' }};">
                                {{ $tx['type'] === 'Inflow' ? '+' : '-' }} RM {{ number_format($tx['amount'], 2) }}
                            </div>
                            <div style="font-size: 0.7rem; color: var(--text-muted);">
                                {{ $tx['method'] }}
                            </div>
                        </div>
                    </div>
                @empty
                    <div style="text-align: center; color: var(--text-muted); padding: 20px;">
                        No recent transactions recorded.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Annual Summary Teaser Card -->
        <div class="card" style="border: 1px solid rgba(0, 242, 254, 0.25);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                <h3 class="card-title" style="margin: 0;">{{ $currentYearPnL['year'] }} Fiscal Year Summary</h3>
                <a href="{{ route('erp.finance.pnl', ['year' => $currentYearPnL['year']]) }}" class="btn btn-outline btn-sm">
                    Detailed P&L
                </a>
            </div>

            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; font-size: 0.85rem;">
                <div style="background: rgba(255,255,255,0.02); padding: 12px; border-radius: 8px;">
                    <span style="color: var(--text-muted); display: block; font-size: 0.75rem;">YTD Revenue</span>
                    <strong style="font-size: 1.1rem; color: var(--accent);">
                        RM {{ number_format($currentYearPnL['totals']['revenue'], 2) }}
                    </strong>
                </div>
                <div style="background: rgba(255,255,255,0.02); padding: 12px; border-radius: 8px;">
                    <span style="color: var(--text-muted); display: block; font-size: 0.75rem;">YTD Gross Profit</span>
                    <strong style="font-size: 1.1rem; color: var(--success);">
                        RM {{ number_format($currentYearPnL['totals']['gross_profit'], 2) }}
                    </strong>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
