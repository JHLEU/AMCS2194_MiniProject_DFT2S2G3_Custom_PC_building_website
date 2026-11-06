# Implementation Plan: inventory-supply

## Overview
Build the complete Supply Chain & Inventory Management module (`inventory-supply`) for the ERP portal (`/erp/*`, restricted to `Admin` and `Staff_Warehouse`). This covers the 7 Eloquent domain models and seeders, Product Catalog CRUD, Suppliers & Hardware Mapping (with AJAX lookup), Restock Purchase Orders (pipeline, status workflow, atomic batch SN provisioning, purchasing history), Serial Number tracking (`In-Stock`, `Reserved`, `Sold`, `RMA`, `Lost`), and the Inventory Overview, Low-Stock Alerts, and Batch Cost Valuation views.

## Architecture Decisions
- **Eloquent Scopes & Relationships over Raw SQL**: Encapsulate stock status counts (`in_stock_count`, `reserved_count`, `sold_count`, `rma_count`, `lost_count`), low-stock filtering (`in_stock_count <= low_stock_threshold`), and batch-cost valuation aggregates inside `Product` and `SerialNumber` model methods/scopes so both `inventory-supply` and downstream modules (`compatibility-engine`, `order-checkout`, `finance-cashflow`) share one source of truth.
- **Transactional Restock & Idempotent Auto-SN Generation**: Wrap Restock PO creation and PO status completion in `DB::transaction()`. Guard SN provisioning so that if a PO is already `Completed` (or already has linked `serial_numbers`), re-submitting `Completed` will not generate duplicate serial numbers.
- **Database Compatibility (SQLite + MySQL)**: Use standard Eloquent / Query Builder constructs (`COALESCE`, `CASE WHEN`, `withCount`) compatible with both SQLite (used in automated tests and local quick-start) and MySQL/MariaDB.
- **Zero-Build Blade + Vanilla JS UI**: Extend `resources/views/layouts/erp.blade.php` and `public/css/style.css` / `public/js/app.js` for dynamic PO line-item rows and supplier product auto-fill without npm/Vite steps.

## Dependency Graph
```
Eloquent Models & InventorySupplySeeder (Task 1)
    │
    ├── Product Catalog CRUD + Views (Task 2)
    │       │
    ├── Suppliers CRUD & Hardware Mapping + Views (Task 3)
    │       │
    │       └── Restock PO Pipeline, Auto-SN & History + Views (Task 4)
    │               │
    └── Serial Numbers Registry + Inventory Dashboard, Low-Stock & Costing Views (Task 5)
            │
            └── ERP Navigation Integration & Feature Test Suite (Task 6)
```

## Task List

### Phase 1: Foundation (Models & Seed Data)
- [x] Task 1: Implement Eloquent Models (`Category`, `Product`, `Supplier`, `SupplierHardwareMapping`, `RestockOrder`, `RestockOrderItem`, `SerialNumber`) and `InventorySupplySeeder`

### Checkpoint: Foundation
- [x] `php artisan migrate:fresh --seed` succeeds and populates sample categories, suppliers, products, mappings, restock orders, and serial numbers.

### Phase 2: Core Catalog, Suppliers & Procurement Slices
- [x] Task 2: Product Catalog CRUD (`Erp\ProductController` + `erp/products/*` Blade views)
- [x] Task 3: Suppliers CRUD & Hardware Mapping (`Erp\SupplierController` + `erp/suppliers/*` Blade views)
- [x] Task 4: Restock Purchase Orders Pipeline, Auto-SN Provisioning & History (`Erp\RestockController` + `erp/restock/*` Blade views)

### Checkpoint: Core Features
- [x] Products, Suppliers, Mappings, and Restock POs (including `Pending` → `Approved` → `Completed` with auto-SN generation) work end-to-end.

### Phase 3: Serial Tracking, Valuation Views & Verification
- [x] Task 5: Serial Number Tracking, Inventory Dashboard, Low-Stock Alerts & Costing Views (`Erp\InventoryController` + `erp/inventory/*` & `erp/serials/*` Blade views)
- [x] Task 6: ERP Sidebar Navigation Wiring, `Erp\DashboardController` Low-Stock Refinement & Feature Tests (`tests/Feature/InventorySupplyTest.php`)

### Checkpoint: Complete
- [x] All 6 tasks completed and verified
- [x] `php artisan test` passes 100%

## Risks and Mitigations
| Risk | Impact | Mitigation |
|------|--------|------------|
| SQL `HAVING` on aliased `withCount` columns differs between SQLite and MySQL | Med | Use a subquery or `WHERE (SELECT COUNT(*) FROM serial_numbers ...) <= products.low_stock_threshold` which is 100% standard SQL across SQLite and MySQL. |
| Foreign key constraint errors when deleting a Product or Supplier referenced by Restock Orders | Med | Check for existing child records or catch `QueryException` on delete and return a friendly `error` flash message instead of a 500 error. |
| Duplicate SN generation if `updateStatus('Completed')` is submitted twice | High | Check `$order->status === 'Completed'` or `$order->serialNumbers()->exists()` inside the transaction before generating SNs. |

## Open Questions
- None.
