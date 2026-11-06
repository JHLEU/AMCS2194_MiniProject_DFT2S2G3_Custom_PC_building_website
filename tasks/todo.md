# Tasks: finance-cashflow

## Task 1: Financial Report Service Layer (`FinancialReportService`)
**Description:** Implement `App\Services\FinancialReportService` providing business logic for overall financial KPI metrics (Revenue, COGS from batch costs, Gross Profit, Gross Margin %, Procurement Expenditure, Inventory Valuation, Net Cashflow), 12-month calendar P&L statement aggregation, hardware category margin breakdown, and a unified chronological cashflow transaction stream (Inflows from customer payments & Outflows from restock purchase orders).

**Acceptance criteria:**
- [x] `getOverviewMetrics()` computes Revenue, COGS, Gross Profit, Gross Margin %, Procurement Outflow, Inventory Valuation, and Net Cashflow.
- [x] `getMonthlyPnL(int $year)` returns monthly financial figures for all 12 months with annual summary totals.
- [x] `getCategoryBreakdown()` groups item sales and batch costs by hardware category with gross margin %.
- [x] `getTransactionsLedger()` unifies customer payments (Inflows) and completed restock PO disbursements (Outflows) sorted by date.

**Verification:**
- [x] Tested via service methods and automated feature tests.

**Dependencies:** None

**Files likely touched:**
- `app/Services/FinancialReportService.php`

**Estimated scope:** Medium (1 file)

---

## Task 2: ERP Finance Controller & Blade Views
**Description:** Implement `App\Http\Controllers\Erp\FinanceController` (`index`, `pnl`, `cashflow`) and views in `resources/views/erp/finance/`:
- `index.blade.php`: Financial Dashboard with KPI cards, category margin breakdown, and recent cashflow stream.
- `pnl.blade.php`: Profit & Loss (P&L) Statement with year selector and monthly table.
- `cashflow.blade.php`: Cashflow Ledger with Inflow/Outflow filtering and date search.
Wire sidebar links in `resources/views/layouts/erp.blade.php` and register routes in `routes/web.php` under `role:Admin`.

**Acceptance criteria:**
- [x] `GET /erp/finance` renders Financial Dashboard with correct KPI summaries and category margins.
- [x] `GET /erp/finance/pnl` displays 12-month P&L table with revenue, COGS, and gross profit.
- [x] `GET /erp/finance/cashflow` displays unified transaction stream with filter by type (`Inflow` / `Outflow`).
- [x] Sidebar links in `layouts/erp.blade.php` point to active financial routes.

**Verification:**
- [x] `php artisan route:list --path=erp/finance` shows all financial routes.

**Dependencies:** Task 1

**Files likely touched:**
- `app/Http/Controllers/Erp/FinanceController.php`
- `resources/views/erp/finance/index.blade.php`
- `resources/views/erp/finance/pnl.blade.php`
- `resources/views/erp/finance/cashflow.blade.php`
- `resources/views/layouts/erp.blade.php`
- `routes/web.php`

**Estimated scope:** Large (6 files)

---

## Task 3: Automated Feature Tests (`FinanceCashflowTest`)
**Description:** Create `tests/Feature/FinanceCashflowTest.php` covering calculations for overview metrics, monthly P&L structure, category margin breakdown, cashflow ledger inflow/outflow balance, and strict RBAC enforcement (`Admin` allowed, `Staff_Tech` / `Customer` blocked).

**Acceptance criteria:**
- [x] All financial feature tests pass with 100% success rate.
- [x] Zero regressions across the full test suite (`php artisan test`).

**Verification:**
- [x] `php artisan test` completes with 0 errors.

**Dependencies:** Task 1, Task 2

**Files likely touched:**
- `tests/Feature/FinanceCashflowTest.php`

**Estimated scope:** Small (1 file)

---

## Checkpoint: Complete (`finance-cashflow`)
- [x] All acceptance criteria in `SPEC-finance-cashflow.md` met
- [x] `php artisan migrate:fresh --seed` and `php artisan test` pass 100%
- [x] Ready for human review
