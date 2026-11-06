<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Models\RestockOrder;
use App\Models\SerialNumber;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class FinancialReportService
{
    /**
     * Compute high-level executive financial metrics.
     *
     * @return array<string, mixed>
     */
    public function getOverviewMetrics(): array
    {
        $ordersQuery = Order::where('order_status', '!=', Order::STATUS_CANCELLED);

        $totalRevenue = (float) ((clone $ordersQuery)->sum('total_selling_amount') ?? 0.0);
        $totalCogs = (float) ((clone $ordersQuery)->sum('total_cost_amount') ?? 0.0);
        $grossProfit = $totalRevenue - $totalCogs;
        $grossMarginPct = $totalRevenue > 0 ? round(($grossProfit / $totalRevenue) * 100, 1) : 0.0;

        $totalOrdersCount = (int) (clone $ordersQuery)->count();
        $completedOrdersCount = (int) Order::where('order_status', Order::STATUS_COMPLETED)->count();
        $averageOrderValue = $totalOrdersCount > 0 ? round($totalRevenue / $totalOrdersCount, 2) : 0.0;

        $paymentsCollected = (float) (Payment::where('payment_status', Payment::STATUS_SUCCESS)->sum('amount') ?? 0.0);
        $procurementOutflow = (float) (RestockOrder::where('status', RestockOrder::STATUS_COMPLETED)->sum('total_amount') ?? 0.0);
        $inventoryValuation = (float) (SerialNumber::where('status', SerialNumber::STATUS_IN_STOCK)->sum('batch_cost') ?? 0.0);

        $netCashflow = $paymentsCollected - $procurementOutflow;

        return [
            'gross_revenue'          => $totalRevenue,
            'cost_of_goods_sold'     => $totalCogs,
            'gross_profit'           => $grossProfit,
            'gross_margin_pct'       => $grossMarginPct,
            'procurement_outflow'    => $procurementOutflow,
            'payments_collected'     => $paymentsCollected,
            'inventory_valuation'    => $inventoryValuation,
            'net_cashflow'           => $netCashflow,
            'total_orders_count'     => $totalOrdersCount,
            'completed_orders_count' => $completedOrdersCount,
            'average_order_value'    => $averageOrderValue,
        ];
    }

    /**
     * Generate 12-month Profit and Loss (P&L) breakdown for a specific calendar year.
     *
     * @return array{year: int, months: array<int, array<string, mixed>>, totals: array<string, mixed>}
     */
    public function getMonthlyPnL(int $year): array
    {
        $months = [];
        $annualRevenue = 0.0;
        $annualCogs = 0.0;
        $annualGrossProfit = 0.0;
        $annualProcurement = 0.0;
        $annualNetCashflow = 0.0;
        $annualOrdersCount = 0;

        for ($m = 1; $m <= 12; $m++) {
            $startDate = Carbon::create($year, $m, 1)->startOfMonth();
            $endDate = (clone $startDate)->endOfMonth();

            $monthOrders = Order::where('order_status', '!=', Order::STATUS_CANCELLED)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->get();

            $rev = (float) $monthOrders->sum('total_selling_amount');
            $cogs = (float) $monthOrders->sum('total_cost_amount');
            $profit = $rev - $cogs;
            $marginPct = $rev > 0 ? round(($profit / $rev) * 100, 1) : 0.0;
            $ordersCount = $monthOrders->count();

            $procurement = (float) RestockOrder::where('status', RestockOrder::STATUS_COMPLETED)
                ->whereBetween('order_date', [$startDate->toDateString(), $endDate->toDateString()])
                ->sum('total_amount');

            $netCash = $rev - $procurement;

            $months[$m] = [
                'month_number'     => $m,
                'month_name'       => $startDate->format('M'),
                'full_month_name'  => $startDate->format('F'),
                'orders_count'     => $ordersCount,
                'revenue'          => $rev,
                'cogs'             => $cogs,
                'gross_profit'     => $profit,
                'gross_margin_pct' => $marginPct,
                'procurement'      => $procurement,
                'net_cashflow'     => $netCash,
            ];

            $annualRevenue += $rev;
            $annualCogs += $cogs;
            $annualGrossProfit += $profit;
            $annualProcurement += $procurement;
            $annualNetCashflow += $netCash;
            $annualOrdersCount += $ordersCount;
        }

        $annualMarginPct = $annualRevenue > 0 ? round(($annualGrossProfit / $annualRevenue) * 100, 1) : 0.0;

        return [
            'year'   => $year,
            'months' => $months,
            'totals' => [
                'orders_count'     => $annualOrdersCount,
                'revenue'          => $annualRevenue,
                'cogs'             => $annualCogs,
                'gross_profit'     => $annualGrossProfit,
                'gross_margin_pct' => $annualMarginPct,
                'procurement'      => $annualProcurement,
                'net_cashflow'     => $annualNetCashflow,
            ],
        ];
    }

    /**
     * Category revenue and margin ranking breakdown.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function getCategoryBreakdown(): Collection
    {
        $rows = DB::table('order_items as oi')
            ->join('products as p', 'oi.product_id', '=', 'p.id')
            ->join('orders as o', 'oi.order_id', '=', 'o.id')
            ->where('o.order_status', '!=', Order::STATUS_CANCELLED)
            ->select(
                'p.category',
                DB::raw('COUNT(oi.id) as line_items_count'),
                DB::raw('SUM(oi.quantity) as total_units_sold'),
                DB::raw('SUM(oi.quantity * oi.unit_price) as category_revenue'),
                DB::raw('SUM(oi.quantity * p.cost_price) as category_cost')
            )
            ->groupBy('p.category')
            ->orderByDesc('category_revenue')
            ->get();

        return $rows->map(function ($row) {
            $revenue = (float) ($row->category_revenue ?? 0.0);
            $cost = (float) ($row->category_cost ?? 0.0);
            $profit = $revenue - $cost;
            $marginPct = $revenue > 0 ? round(($profit / $revenue) * 100, 1) : 0.0;

            return [
                'category'          => $row->category,
                'total_units_sold'  => (int) ($row->total_units_sold ?? 0),
                'category_revenue'  => $revenue,
                'category_cost'     => $cost,
                'gross_profit'      => $profit,
                'gross_margin_pct'  => $marginPct,
            ];
        });
    }

    /**
     * Unified cashflow ledger showing chronologically ordered Inflows (Payments) and Outflows (Restock POs).
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function getTransactionsLedger(int $limit = 50, ?string $typeFilter = null): Collection
    {
        $transactions = collect();

        // 1. Inflows from verified customer payments
        if ($typeFilter === null || $typeFilter === 'Inflow') {
            $payments = Payment::with(['order.customer'])
                ->where('payment_status', Payment::STATUS_SUCCESS)
                ->latest('payment_date')
                ->limit($limit)
                ->get();

            foreach ($payments as $pay) {
                $transactions->push([
                    'type'           => 'Inflow',
                    'reference'      => $pay->order?->order_number ?? 'Order Payment',
                    'description'    => 'Customer Order Payment: ' . ($pay->order?->customer_name ?? 'Customer'),
                    'method'         => $pay->payment_method ?? 'Sandbox',
                    'txn_id'         => $pay->transaction_id ?? 'TXN-UNKNOWN',
                    'amount'         => (float) $pay->amount,
                    'timestamp'      => $pay->payment_date ?? $pay->created_at,
                    'badge_class'    => 'badge-success',
                ]);
            }
        }

        // 2. Outflows from completed restock purchase orders
        if ($typeFilter === null || $typeFilter === 'Outflow') {
            $restocks = RestockOrder::with('supplier')
                ->where('status', RestockOrder::STATUS_COMPLETED)
                ->latest('completed_at')
                ->limit($limit)
                ->get();

            foreach ($restocks as $ro) {
                $transactions->push([
                    'type'           => 'Outflow',
                    'reference'      => $ro->order_code,
                    'description'    => 'Restock Procurement: ' . ($ro->supplier?->company_name ?? 'Supplier'),
                    'method'         => 'Supplier PO Disbursement',
                    'txn_id'         => $ro->order_code,
                    'amount'         => (float) $ro->total_amount,
                    'timestamp'      => $ro->completed_at ? Carbon::parse($ro->completed_at) : Carbon::parse($ro->order_date),
                    'badge_class'    => 'badge-danger',
                ]);
            }
        }

        // Sort descending by timestamp
        return $transactions->sortByDesc('timestamp')->values()->take($limit);
    }
}
