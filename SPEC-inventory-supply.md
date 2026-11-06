# Spec: inventory-supply

## Objective
Implement the **Supply Chain & Inventory Management** module (`inventory-supply`) inside the ERP portal (`/erp/*`, accessible to `Admin` and `Staff_Warehouse`). This module provides full lifecycle control over PC hardware inventory, vendor relationships, procurement, and unit-level serial number tracking, serving as the authoritative data foundation for the downstream Compatibility Engine (`compatibility-engine`), Customer Checkout (`order-checkout`), Financial Reporting (`finance-cashflow`), and Warranty/RMA (`warranty-techsupport`) modules.

Core capabilities:
1. **Product Catalog CRUD**: Create, list, filter, edit, and delete hardware components (`CPU`, `GPU`, `Motherboard`, `RAM`, `Storage`, `PSU`, `Case`, `Cooling`, `Accessories`) including compatibility metadata (`socket_type`, `ram_type`, `tdp_wattage`, `brand`, `specs`), pricing (`cost_price`, `selling_price`), `warranty_months`, and `low_stock_threshold`, with real-time counts of `In-Stock`, `Reserved`, `Sold`, `RMA`, and `Lost` units.
2. **Suppliers & Hardware Mapping**: Manage supplier profiles (`company_name`, `contact_person`, `phone`, `email`, `address`) and maintain many-to-many supplier hardware mappings (`supplier_sku`, `agreed_unit_price`, `lead_time_days`) with an AJAX endpoint (`/erp/restock/supplier-products`) to auto-populate agreed unit costs during PO creation.
3. **Restock Purchase Orders (POs) & Purchasing History**: Create multi-item restock purchase orders (`RO-YYYYMMDD-XXXXX`), manage status transitions (`Pending` → `Approved` → `Completed` / `Cancelled`), automatically provision `In-Stock` serial numbers with the PO line item's `unit_cost` as `batch_cost` upon completion, and provide a searchable Purchasing History archive for completed POs.
4. **Serial Numbers (SN) Tracking**: Single and bulk registration of physical serial numbers (`sn_code`), filtering by status/product/search query, and lifecycle status transitions across all 5 states (`In-Stock`, `Reserved`, `Sold`, `RMA`, `Lost`).
5. **Inventory Dashboard, Costing & Low-Stock Alerts**:
   - Supply Chain overview dashboard with total valuation, active SKUs, low-stock count, and supplier count.
   - Real-time Low-Stock Alerts view showing products where `in_stock_count <= low_stock_threshold`, sorted by deficit, with 1-click shortcut to draft a Restock PO.
   - Inventory Costing & Valuation view aggregating `batch_cost` of `In-Stock` serial numbers overall, by category, and by product (min/max/avg/total valuation).
   - Update `Erp\DashboardController` to compute the real `lowStockCount` using live `In-Stock` serial number counts.

## Tech Stack
- **Framework**: Laravel 12.x (PHP 8.2+)
- **Database**: SQLite (local/testing) & MySQL/MariaDB compatible via Eloquent ORM / Query Builder
- **Frontend**: Standard Laravel Blade templates extending `resources/views/layouts/erp.blade.php`, Plain CSS (`public/css/style.css`), and Plain Vanilla JS (`public/js/app.js`)
- **Authorization**: `auth` and `role:Admin,Staff_Warehouse` middleware on all `/erp/inventory/*`, `/erp/products/*`, `/erp/suppliers/*`, and `/erp/restock/*` routes

## Commands
- **Run Migrations & Seeders**: `php artisan migrate:fresh --seed`
- **Run Full Test Suite**: `php artisan test`
- **Run Inventory Module Tests**: `php artisan test --filter=InventorySupplyTest`
- **Serve Application**: `php artisan serve --port=8000`

## Project Structure
```
app/
├── Http/
│   └── Controllers/
│       └── Erp/
│           ├── DashboardController.php      → Updated with real low-stock query
│           ├── InventoryController.php      → Inventory dashboard, low-stock, costing, serials CRUD
│           ├── ProductController.php        → Hardware catalog CRUD
│           ├── SupplierController.php       → Supplier directory CRUD & hardware mapping
│           └── RestockController.php        → Restock PO pipeline, status transitions, auto-SN, history
├── Models/
│   ├── Category.php                         → Hardware categories
│   ├── Product.php                          → Hardware catalog & stock count scopes/helpers
│   ├── Supplier.php                         → Vendor profiles
│   ├── SupplierHardwareMapping.php          → Supplier-to-product SKU, agreed cost & lead time
│   ├── RestockOrder.php                     → Purchase orders header
│   ├── RestockOrderItem.php                 → Purchase order line items
│   └── SerialNumber.php                     → Individual unit SN tracking & batch costing
database/
└── seeders/
    ├── DatabaseSeeder.php                   → Calls RoleAndUserSeeder + InventorySupplySeeder
    └── InventorySupplySeeder.php            → Seeds initial categories, suppliers, products, mappings, POs, and SNs from database.sql
resources/
└── views/
    ├── layouts/
    │   └── erp.blade.php                    → Updated sidebar navigation links to active inventory routes
    └── erp/
        ├── inventory/
        │   ├── dashboard.blade.php          → Supply chain overview & quick metrics
        │   ├── low_stock.blade.php          → Real-time low-stock alert table & restock shortcuts
        │   └── costing.blade.php            → Batch cost valuation breakdown (overall, category, product)
        ├── products/
        │   ├── index.blade.php              → Hardware catalog table with compatibility badges & stock counts
        │   ├── create.blade.php             → Create component form (socket, RAM type, TDP, prices)
        │   └── edit.blade.php               → Edit component form + mapped suppliers list
        ├── suppliers/
        │   ├── index.blade.php              → Supplier directory
        │   ├── create.blade.php             → Register supplier form
        │   ├── edit.blade.php               → Edit supplier form
        │   └── mapping.blade.php            → Assign/remove products, SKU, agreed price, lead time
        ├── restock/
        │   ├── index.blade.php              → Active PO pipeline with status/supplier filters
        │   ├── create.blade.php             → Dynamic multi-item PO builder with supplier price auto-fill
        │   ├── show.blade.php               → PO detail view & status transition controls (with auto-SN toggle)
        │   └── history.blade.php            → Completed PO archive with date/supplier/search filters
        └── serials/
            ├── index.blade.php              → Serial number registry with status/product/search filters & inline status update
            └── create.blade.php             → Single & bulk SN registration form
tests/
└── Feature/
    └── InventorySupplyTest.php              → Feature tests covering Products, Suppliers, Mappings, Restock POs, Auto-SN, and Low-Stock/Costing
```

## Code Style
Follow PSR-12 and Laravel 12 conventions: Eloquent models with typed relationships, database transactions (`DB::transaction()`) for multi-step writes (such as PO creation and PO completion with batch SN provisioning), and standard Blade views using the dark ERP theme variables in `public/css/style.css`.

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    public const CATEGORIES = [
        'CPU', 'GPU', 'Motherboard', 'RAM', 'Storage', 'PSU', 'Case', 'Cooling', 'Accessories',
    ];

    protected $fillable = [
        'name', 'category', 'category_id', 'supplier_id', 'brand', 'specs',
        'image_url', 'tdp_wattage', 'socket_type', 'ram_type',
        'cost_price', 'selling_price', 'warranty_months', 'low_stock_threshold',
    ];

    public function serialNumbers(): HasMany
    {
        return $this->hasMany(SerialNumber::class);
    }

    public function scopeWithStockCounts(Builder $query): Builder
    {
        return $query->withCount([
            'serialNumbers as in_stock_count' => fn (Builder $q) => $q->where('status', SerialNumber::STATUS_IN_STOCK),
            'serialNumbers as reserved_count' => fn (Builder $q) => $q->where('status', SerialNumber::STATUS_RESERVED),
            'serialNumbers as sold_count'     => fn (Builder $q) => $q->where('status', SerialNumber::STATUS_SOLD),
            'serialNumbers as rma_count'      => fn (Builder $q) => $q->where('status', SerialNumber::STATUS_RMA),
            'serialNumbers as lost_count'     => fn (Builder $q) => $q->where('status', SerialNumber::STATUS_LOST),
        ]);
    }
}
```

## Testing Strategy
- **Framework**: PHPUnit (`RefreshDatabase` with in-memory SQLite)
- **Test File**: `tests/Feature/InventorySupplyTest.php`
- **Key Test Cases**:
  1. **RBAC Enforcement**: `Staff_Tech` and `Customer` receive `403 Forbidden` on `/erp/products`, `/erp/suppliers`, `/erp/restock`, and `/erp/inventory`; `Admin` and `Staff_Warehouse` receive `200 OK`.
  2. **Product Catalog CRUD**: Creating, updating, viewing, and deleting a hardware product with `socket_type`, `ram_type`, `tdp_wattage`, `cost_price`, `selling_price`, and `low_stock_threshold`.
  3. **Supplier & Hardware Mapping**: Creating a supplier, assigning a product mapping (`supplier_sku`, `agreed_unit_price`, `lead_time_days`), updating existing mapping via upsert, and querying the JSON endpoint for supplier-mapped products.
  4. **Restock PO Lifecycle & Auto-SN Provisioning**: Creating a multi-item `Pending` PO, transitioning `Pending` → `Approved` → `Completed` with `auto_generate_sn = 1`, and verifying that the exact quantity of `In-Stock` `SerialNumber` records are created with `batch_cost` matching the PO line item `unit_cost` and linked `restock_order_id`.
  5. **Idempotency Guard on Completed POs**: Re-submitting status `Completed` on an already-completed PO does not duplicate serial numbers.
  6. **Serial Number Lifecycle & Bulk Registration**: Registering bulk SNs (skipping duplicates) and transitioning an SN across `In-Stock`, `Reserved`, `Sold`, `RMA`, and `Lost`.
  7. **Low-Stock Detection & Costing Valuation**: Verifying that products with `in_stock_count <= low_stock_threshold` appear in the low-stock view and that `In-Stock` `batch_cost` sums match the costing summary.

## Boundaries
- **Always**:
  - Protect all `inventory-supply` ERP routes with `['auth', 'role:Admin,Staff_Warehouse']`.
  - Wrap PO creation and PO completion + SN provisioning in `DB::transaction()`.
  - Prevent duplicate SN generation if a `Completed` PO is submitted to `updateStatus` again.
  - Preserve existing migrations and schema column names from `database/migrations/2026_11_06_000001_*`, `000002_*`, and `000003_*`.
  - Use Plain CSS (`public/css/style.css`) and jQuery 3.7.1 (local copy in `public/js/vendor/`, loaded before `public/js/app.js`) without npm/Vite build steps.
- **Ask First**:
  - Adding any new database migration or altering existing columns on `products`, `suppliers`, `supplier_hardware_mappings`, `restock_orders`, `restock_order_items`, or `serial_numbers`.
  - Installing any new Composer packages.
- **Never**:
  - Modify or delete backup files inside `legacy/`.
  - Allow deleting a `Product` or `Supplier` that has restricted foreign key references (`restock_order_items` or `restock_orders`) without catching the constraint violation and returning a user-friendly flash error message.
  - Expose ERP inventory routes to unauthenticated users, `Customer`, or `Staff_Tech`.

## Success Criteria
1. All 7 Eloquent models (`Category`, `Product`, `Supplier`, `SupplierHardwareMapping`, `RestockOrder`, `RestockOrderItem`, `SerialNumber`) and `InventorySupplySeeder` are implemented and `php artisan migrate:fresh --seed` populates sample categories, suppliers, products (with sockets/RAM types/TDP), mappings, restock orders, and serial numbers.
2. `Admin` and `Staff_Warehouse` can perform full CRUD on Products and Suppliers, manage Supplier Hardware Mappings, create and transition Restock POs (`Pending` → `Approved` → `Completed` / `Cancelled`), and manage Serial Numbers (`In-Stock`, `Reserved`, `Sold`, `RMA`, `Lost`).
3. Completing a Restock PO with auto-SN enabled automatically generates the exact number of `In-Stock` serial numbers with `batch_cost` equal to the PO line item's `unit_cost`.
4. All ERP sidebar links under "Supply Chain & Warehouse" in `layouts/erp.blade.php` and quick links in `erp/dashboard.blade.php` resolve to working pages (`Inventory & Stock`, `Hardware Catalog`, `Low Stock Alerts`, `Inventory Costing`, `Suppliers & Mapping`, `Restock POs`, `Purchasing History`, `Serial Numbers`).
5. All existing and new automated feature tests pass (`php artisan test`).

## Open Questions
None — the database schema in `database/migrations/` and the domain workflows in `legacy/` fully specify the required fields, status enums, and calculations.
