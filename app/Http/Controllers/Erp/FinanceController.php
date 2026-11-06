<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Services\FinancialReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FinanceController extends Controller
{
    public function __construct(
        private readonly FinancialReportService $financialService
    ) {}

    /**
     * Executive Financial Dashboard overview.
     */
    public function index(): View
    {
        $metrics = $this->financialService->getOverviewMetrics();
        $categories = $this->financialService->getCategoryBreakdown();
        $recentTransactions = $this->financialService->getTransactionsLedger(10);
        $currentYearPnL = $this->financialService->getMonthlyPnL((int) date('Y'));

        return view('erp.finance.index', [
            'metrics'            => $metrics,
            'categories'         => $categories,
            'recentTransactions' => $recentTransactions,
            'currentYearPnL'     => $currentYearPnL,
        ]);
    }

    /**
     * Monthly & Annual Profit & Loss Statement (P&L).
     */
    public function pnl(Request $request): View
    {
        $year = (int) ($request->query('year') ?? date('Y'));
        $pnlReport = $this->financialService->getMonthlyPnL($year);

        $availableYears = range((int) date('Y') - 2, (int) date('Y') + 1);

        return view('erp.finance.pnl', [
            'pnlReport'      => $pnlReport,
            'selectedYear'   => $year,
            'availableYears' => $availableYears,
        ]);
    }

    /**
     * Cashflow Ledger showing real-time Inflows & Outflows.
     */
    public function cashflow(Request $request): View
    {
        $typeFilter = $request->query('type');
        if (! in_array($typeFilter, ['Inflow', 'Outflow'], true)) {
            $typeFilter = null;
        }

        $ledger = $this->financialService->getTransactionsLedger(50, $typeFilter);
        $metrics = $this->financialService->getOverviewMetrics();

        return view('erp.finance.cashflow', [
            'ledger'     => $ledger,
            'typeFilter' => $typeFilter,
            'metrics'    => $metrics,
        ]);
    }
}
