# Spec: finance-cashflow

## Objective
Implement the **Financial Reporting, Cashflow & Profit/Loss (P&L)** module (`finance-cashflow`) in the ERP portal (`/erp/finance/*`). This module aggregates financial data across customer sales (`order-checkout`) and procurement expenses (`inventory-supply`), calculating exact Cost of Goods Sold (COGS) from unit-level batch serial numbers, Gross Margin percentages, procurement capital expenditure, current asset inventory valuation, and monthly/annual cashflow trends.

Core capabilities:
1. **Executive Financial Dashboard (`/erp/finance`)**:
   - High-level KPIs:
     - **Gross Revenue**: Sum of valid customer orders / verified payment amounts.
     - **Cost of Goods Sold (COGS)**: Sum of actual batch costs (`orders.total_cost_amount` & sold serial number costs).
     - **Gross Profit & Margin**: `Revenue - COGS` and `(Gross Profit / Revenue) * 100`.
     - **Procurement Outflows**: Capital spent on completed restock purchase orders (`restock_orders.total_amount`).
     - **Inventory Asset Valuation**: Total capital held in warehouse stock (`serial_numbers` where `status = 'In-Stock'`).
     - **Net Cashflow**: `Total Payments Inflow - Total Procurement Outflow`.
   - Monthly revenue & margin overview breakdown.
   - Category performance ranking (Revenue, Cost, Profit, and Margin % per hardware category).
2. **Profit & Loss (P&L) Statement (`/erp/finance/pnl`)**:
   - Month-by-month financial ledger for any chosen calendar year.
   - Displays Revenue, COGS, Gross Profit, Operating/Procurement expenses, and Net Operating Margin for all 12 months with annual totals.
3. **Cashflow Ledger & Transaction Stream (`/erp/finance/cashflow`)**:
   - Unified audit stream of financial movements:
     - **Inflows (+)**: Verified customer order payments (`payments` with `payment_status = 'Success'`).
     - **Outflows (-)**: Completed supplier restock orders (`restock_orders` with `status = 'Completed'`).
   - Filtering by transaction type (`All`, `Inflow`, `Outflow`) and date range.
4. **Role-Based Access Control**:
   - Accessible to `Admin` (executive oversight) with safe RBAC guards blocking unauthenticated users, `Customer`, and `Staff_Tech`.

## Tech Stack
- **Framework**: Laravel 12.x (PHP 8.2+)
- **Database**: SQLite (local/testing) & MySQL/MariaDB compatible
- **Architecture**: Service Layer (`App\Services\FinancialReportService`), Controller (`App\Http\Controllers\Erp\FinanceController`), Blade Views extending `layouts/erp.blade.php`, Plain CSS (`public/css/style.css`), jQuery 3.7.1
- **Authorization**: `auth` and `role:Admin` on all `/erp/finance/*` routes

## Project Structure
```
app/
├── Services/
│   └── FinancialReportService.php               → P&L calculations, monthly aggregation, category breakdown, unified cashflow ledger
├── Http/
│   └── Controllers/
│       └── Erp/
│           └── FinanceController.php            → Financial dashboard, P&L statement, and cashflow ledger views
resources/
└── views/
    ├── layouts/
    │   └── erp.blade.php                        → Active Financials & Cashflow navigation links
    └── erp/
        └── finance/
            ├── index.blade.php                  → Financial Dashboard & Category Margin ranking
            ├── pnl.blade.php                    → Monthly & Annual Profit & Loss (P&L) Statement
            └── cashflow.blade.php               → Unified Inflow/Outflow Cashflow Ledger
routes/
└── web.php                                      → Registers `/erp/finance`, `/erp/finance/pnl`, `/erp/finance/cashflow`
tests/
└── Feature/
    └── FinanceCashflowTest.php                  → Feature tests for financial calculations, P&L aggregation, ledger stream, and RBAC
```

## Testing Strategy
- **Framework**: PHPUnit (`RefreshDatabase` with in-memory SQLite)
- **Test File**: `tests/Feature/FinanceCashflowTest.php`
- **Key Test Cases**:
  1. **Financial Overview Metrics**: Accurate computation of Gross Revenue, COGS, Gross Profit, Margin %, Procurement Outflow, and Inventory Valuation against seeded database.
  2. **Monthly P&L Aggregation**: Verifies that monthly buckets correctly group customer orders and restock disbursements for a selected year.
  3. **Category Profit Breakdown**: Verifies that sales and costs are accurately mapped by component category (`CPU`, `GPU`, etc.) with correct margin percentages.
  4. **Cashflow Ledger Inflow/Outflow**: Verifies customer payments appear as positive inflows and completed restock POs appear as negative outflows.
  5. **RBAC Protection**: `Admin` receives `200 OK` on all finance routes; `Staff_Tech` and `Customer` receive `403 Forbidden`.
