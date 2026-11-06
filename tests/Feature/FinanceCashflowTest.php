<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use App\Services\FinancialReportService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class FinanceCashflowTest extends TestCase
{
    use RefreshDatabase;

    protected FinancialReportService $service;
    protected User $admin;
    protected User $warehouseStaff;
    protected User $techStaff;
    protected User $customer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);

        $this->service = app(FinancialReportService::class);
        $this->admin = User::where('email', 'admin@custompc.local')->firstOrFail();
        $this->warehouseStaff = User::where('email', 'warehouse@custompc.local')->firstOrFail();
        $this->techStaff = User::where('email', 'tech@custompc.local')->firstOrFail();
        $this->customer = User::where('email', 'customer@custompc.local')->firstOrFail();
    }

    public function test_financial_overview_metrics_calculation(): void
    {
        $metrics = $this->service->getOverviewMetrics();

        // 3 customer orders seeded: 1565.00 + 887.00 + 577.00 = 3029.00
        $this->assertSame(3029.00, $metrics['gross_revenue']);

        // Hardware cost: 1225.00 + 710.00 + 443.00 = 2378.00
        $this->assertSame(2378.00, $metrics['cost_of_goods_sold']);

        // Gross Profit: 3029.00 - 2378.00 = 651.00
        $this->assertSame(651.00, $metrics['gross_profit']);
        $this->assertGreaterThan(20.0, $metrics['gross_margin_pct']);

        // Procurement Outflow: 1 completed restock order (RO-20261101-001) = 2680.00
        $this->assertSame(2680.00, $metrics['procurement_outflow']);

        // Inventory Valuation: In-Stock serial numbers sum
        $this->assertGreaterThan(1000.00, $metrics['inventory_valuation']);

        // Order counters
        $this->assertSame(3, $metrics['total_orders_count']);
        $this->assertSame(1, $metrics['completed_orders_count']);
        $this->assertGreaterThan(900.00, $metrics['average_order_value']);
    }

    public function test_monthly_pnl_aggregation(): void
    {
        $currentYear = (int) date('Y');
        $report = $this->service->getMonthlyPnL($currentYear);

        $this->assertSame($currentYear, $report['year']);
        $this->assertCount(12, $report['months']);

        // Month 11 (November) where our sample orders are placed
        $nov = $report['months'][11];
        $this->assertSame('Nov', $nov['month_name']);
        $this->assertGreaterThan(0, $nov['orders_count']);
        $this->assertGreaterThan(0, $nov['revenue']);
        $this->assertGreaterThan(0, $nov['cogs']);
        $this->assertGreaterThan(0, $nov['gross_profit']);

        // Totals match annual aggregates
        $this->assertSame(3029.00, $report['totals']['revenue']);
        $this->assertSame(2378.00, $report['totals']['cogs']);
        $this->assertSame(651.00, $report['totals']['gross_profit']);
    }

    public function test_category_breakdown_metrics(): void
    {
        $categories = $this->service->getCategoryBreakdown();

        $this->assertNotEmpty($categories);

        $catNames = $categories->pluck('category')->all();
        $this->assertContains('CPU', $catNames);
        $this->assertContains('GPU', $catNames);

        $cpuRow = $categories->firstWhere('category', 'CPU');
        $this->assertNotNull($cpuRow);
        $this->assertGreaterThanOrEqual(1, $cpuRow['total_units_sold']);
        $this->assertGreaterThan(0, $cpuRow['category_revenue']);
        $this->assertGreaterThan(0, $cpuRow['category_cost']);
        $this->assertGreaterThan(0, $cpuRow['gross_profit']);
    }

    public function test_cashflow_ledger_inflows_and_outflows(): void
    {
        // 1. All transactions
        $all = $this->service->getTransactionsLedger(50);
        $this->assertNotEmpty($all);

        $directions = $all->pluck('type')->unique()->all();
        $this->assertContains('Inflow', $directions);
        $this->assertContains('Outflow', $directions);

        // 2. Filter Inflows only
        $inflows = $this->service->getTransactionsLedger(50, 'Inflow');
        $this->assertNotEmpty($inflows);
        foreach ($inflows as $tx) {
            $this->assertSame('Inflow', $tx['type']);
            $this->assertStringStartsWith('ORD-', $tx['reference']);
        }

        // 3. Filter Outflows only
        $outflows = $this->service->getTransactionsLedger(50, 'Outflow');
        $this->assertNotEmpty($outflows);
        foreach ($outflows as $tx) {
            $this->assertSame('Outflow', $tx['type']);
            $this->assertStringStartsWith('RO-', $tx['reference']);
        }
    }

    public function test_erp_financial_views_accessible_to_admin_and_blocked_for_others(): void
    {
        $routes = [
            route('erp.finance.index'),
            route('erp.finance.pnl'),
            route('erp.finance.cashflow'),
        ];

        foreach ($routes as $url) {
            // Guest redirected
            Auth::logout();
            $this->get($url)->assertRedirect(route('login'));

            // Customer forbidden
            $this->actingAs($this->customer)->get($url)->assertStatus(403);

            // Tech staff forbidden
            $this->actingAs($this->techStaff)->get($url)->assertStatus(403);

            // Warehouse staff forbidden from executive P&L
            $this->actingAs($this->warehouseStaff)->get($url)->assertStatus(403);

            // Admin granted
            $this->actingAs($this->admin)->get($url)->assertOk();
        }
    }
}
