# Spec: compatibility-engine

## Objective
Implement the **Hardware Compatibility & Power Estimation Service** (`compatibility-engine`). This module provides the domain rules, calculation services, JSON validation endpoints, and ERP Compatibility Test Bench needed to validate custom PC builds across socket types, memory standards, thermal design power (TDP), PSU wattage headroom, and CPU/GPU bottleneck estimation. It serves as the core validation engine consumed by both ERP staff (assembly/tech verification) and the upcoming `order-checkout` and `frontend-storefront` (PC Builder & CPU/GPU Calculator) modules.

Core capabilities:
1. **Socket Compatibility Validation (`CPU` ↔ `Motherboard`)**:
   - Validates that a selected CPU and Motherboard share the exact same normalized `socket_type` (e.g., `AM5`, `AM4`, `LGA1700`, `LGA1851`, `LGA1200`).
   - Flags an explicit `socket_mismatch` error if both are selected and their `socket_type` values differ.
   - Emits a warning if either component has a missing/null `socket_type`.
2. **Memory Generation Compatibility Validation (`Motherboard` ↔ `RAM` & `CPU` ↔ `RAM`)**:
   - Validates that selected RAM (`ram_type`, e.g., `DDR4`, `DDR5`) matches the Motherboard's `ram_type`.
   - Validates that selected RAM matches the CPU's supported `ram_type` (if specified on the CPU).
   - Flags an explicit `ram_mismatch` error when a DDR4 component is paired with a DDR5 motherboard/CPU (or vice versa).
3. **System TDP Wattage Calculation & PSU Headroom Checks**:
   - Calculates **Component TDP Sum** across all non-PSU components (`CPU`, `GPU`, `Motherboard`, `RAM`, `Storage`, `Cooling`, `Case`, `Accessories`) plus a **Base System Overhead** (`60W` for fans, RGB, VRM losses, USB peripherals).
   - Convention for `PSU` products: `tdp_wattage` on a `PSU` category product represents its **rated capacity in watts** (e.g., `850` = 850W PSU), or falls back to parsing `<number>W` from `name`/`specs` if `tdp_wattage` is `0`.
   - Calculates **Recommended PSU Wattage** using a **1.25x safety headroom multiplier** (`ceil((total_wattage * 1.25) / 50) * 50`, minimum `550W`, mapped to standard tiers `550W`, `650W`, `750W`, `850W`, `1000W`, `1200W`).
   - Evaluates the selected `PSU` against `total_wattage`:
     - **Error (`psu_insufficient`)**: Selected PSU capacity `< total_wattage` (overload risk).
     - **Warning (`psu_tight_headroom`)**: Selected PSU capacity `>= total_wattage` but `< ceil(total_wattage * 1.15)` (less than 15% transient spike headroom).
     - **Optimal (`psu_ok`)**: Selected PSU capacity `>= ceil(total_wattage * 1.15)`.
   - Provides **Cooling Recommendation** based on CPU `tdp_wattage` (`>= 200W` → 360mm AIO Liquid Cooler, `120W–199W` → Dual-Tower Air Cooler or 240mm+ AIO, `< 120W` → Standard Air Cooler) and warns if a high-TDP CPU (`>= 200W`) is paired with a low-rated cooler (`tdp_wattage < 25`).
4. **Compatible Component Filtering & Build Pricing**:
   - Given a partial selection (e.g., a selected `CPU` or `Motherboard`), filters or annotates candidate components from the `products` catalog with compatibility status (`compatible`, `incompatible`, `reason`) and live `in_stock_count`.
   - Calculates total estimated build retail price (`selling_price` sum) and total stock availability status (`all_in_stock`).
5. **CPU/GPU Bottleneck & Synergy Estimator**:
   - Computes performance synergy, bottleneck percentage, resolution-adjusted balance (`1080p`, `1440p`, `2160p`), cooling advice, and estimated game FPS metrics for any CPU + GPU pair (used by both the ERP Test Bench and the Storefront CPU Calculator).
6. **JSON API & ERP Compatibility Simulator**:
   - Public/shared JSON endpoints (`POST /api/compatibility/evaluate`, `POST /api/compatibility/bottleneck`, `GET /api/compatibility/compatible-parts`) so the interactive PC Builder and ERP tools can validate builds in real time.
   - ERP Compatibility Test Bench (`/erp/compatibility`, accessible to all staff roles: `Admin`, `Staff_Warehouse`, `Staff_Tech`) to interactively test combinations of catalog products, inspect compatibility rules, power headroom, and bottleneck diagnostics.
   - Expand `InventorySupplySeeder` with a few additional catalog items (e.g., an LGA1700 Motherboard, DDR4 RAM, 650W PSU, CPU Cooler, Case) so both compatible and incompatible combinations can be thoroughly tested out of the box.

## Tech Stack
- **Framework**: Laravel 12.x (PHP 8.2+)
- **Database**: SQLite (local/testing) & MySQL/MariaDB compatible via Eloquent ORM
- **Domain Service**: `App\Services\CompatibilityService` (pure, stateless domain service injectable into any controller)
- **Frontend**: Blade templates (`resources/views/erp/compatibility/index.blade.php`), Plain CSS (`public/css/style.css`), jQuery 3.7.1 (served locally from `public/js/vendor/jquery-3.7.1.min.js`) + `public/js/app.js` for live AJAX re-evaluation

## Commands
- **Run Migrations & Seeders**: `php artisan migrate:fresh --seed`
- **Run Full Test Suite**: `php artisan test`
- **Run Compatibility Module Tests**: `php artisan test --filter=CompatibilityEngineTest`

## Project Structure
```
app/
├── Services/
│   └── CompatibilityService.php                 → Core compatibility, wattage, PSU headroom, compatible filtering & bottleneck service
├── Http/
│   └── Controllers/
│       ├── Api/
│       │   └── CompatibilityController.php      → JSON endpoints for build evaluation, compatible parts & bottleneck calculation
│       └── Erp/
│           └── CompatibilityController.php      → ERP Compatibility & Power Test Bench UI
database/
└── seeders/
    └── InventorySupplySeeder.php                → Expanded with LGA1700 MB, DDR4 RAM, 650W PSU, Cooler & Case for cross-compatibility testing
resources/
└── views/
    ├── layouts/
    │   └── erp.blade.php                        → Sidebar link to Compatibility Engine Test Bench
    └── erp/
        └── compatibility/
            └── index.blade.php                  → Interactive ERP Hardware Compatibility & Power Simulator view
routes/
└── web.php                                      → Registers `/api/compatibility/*` and `/erp/compatibility` routes
tests/
└── Feature/
    └── CompatibilityEngineTest.php              → Unit & Feature tests for socket checks, RAM checks, PSU headroom, bottleneck calc, and API/ERP endpoints
```

## Code Style & Service Contract
`App\Services\CompatibilityService` exposes clean, deterministic array/DTO structures:

```php
namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;

class CompatibilityService
{
    public const BASE_SYSTEM_WATTAGE = 60;
    public const PSU_RECOMMENDED_MULTIPLIER = 1.25;
    public const PSU_MIN_SAFE_MULTIPLIER = 1.15;

    /**
     * Evaluate a set of selected components (Collection<Product> or array of Product IDs / Models).
     *
     * @param  iterable<Product|int>  $components
     * @return array{
     *   compatible: bool,
     *   status: string, // 'compatible' | 'warning' | 'incompatible'
     *   errors: array<int, array{code: string, category: string, message: string}>,
     *   warnings: array<int, array{code: string, category: string, message: string}>,
     *   power: array{
     *     component_tdp_wattage: int,
     *     base_overhead_wattage: int,
     *     estimated_total_wattage: int,
     *     recommended_psu_wattage: int,
     *     selected_psu_wattage: int|null,
     *     psu_headroom_watts: int|null,
     *     psu_headroom_percent: float|null,
     *     psu_load_percent: float|null,
     *     psu_status: string // 'none' | 'ok' | 'tight' | 'insufficient'
     *   },
     *   cooling_advice: string,
     *   pricing: array{
     *     estimated_total_price: float,
     *     component_count: int,
     *     all_in_stock: bool
     *   },
     *   summary: array<string, mixed>
     * }
     */
    public function evaluateBuild(iterable $components): array;
}
```

## Testing Strategy
- **Framework**: PHPUnit (`RefreshDatabase` with in-memory SQLite)
- **Test File**: `tests/Feature/CompatibilityEngineTest.php`
- **Key Test Cases**:
  1. **Compatible AM5 + DDR5 Build**: Ryzen 7 7800X3D (`AM5`, `DDR5`) + ASUS B650-A (`AM5`, `DDR5`) + Corsair 32GB (`DDR5`) + RTX 4070 Super (`220W`) + Corsair RM850x (`850W`) returns `compatible = true`, `errors = []`, `psu_status = 'ok'`.
  2. **Socket Mismatch Detection**: Pairing Intel Core i7-14700K (`LGA1700`) with ASUS ROG STRIX B650-A (`AM5`) returns `compatible = false` and an error with `code = 'socket_mismatch'`.
  3. **RAM Type Mismatch Detection**: Pairing an AM5/LGA1700 `DDR5` Motherboard/CPU with `DDR4` RAM returns `compatible = false` and an error with `code = 'ram_mismatch'`.
  4. **TDP Calculation & PSU Insufficient / Tight Headroom Detection**:
     - Verifies `estimated_total_wattage` = sum of non-PSU component TDPs + `60W` base overhead.
     - Verifies `psu_insufficient` error when selected PSU wattage `< estimated_total_wattage`.
     - Verifies `psu_tight_headroom` warning when selected PSU wattage is between `1.0x` and `1.15x` of `estimated_total_wattage`.
  5. **Compatible Parts Filtering**: Calling `GET /api/compatibility/compatible-parts?cpu_id={am5_cpu}` marks `AM5` motherboards as `compatible = true` and `LGA1700` motherboards as `compatible = false` with reason.
  6. **Bottleneck & Synergy Calculator**: Calling `POST /api/compatibility/bottleneck` with CPU, GPU, and resolution (`1080`, `1440`, `2160`) returns bottleneck percentage, tier advice, recommended PSU wattage, cooling recommendation, and estimated game FPS list.
  7. **ERP Compatibility Test Bench Access**: Authenticated staff (`Admin`, `Staff_Warehouse`, `Staff_Tech`) can access `GET /erp/compatibility` (`200 OK`), while `Customer` receives `403 Forbidden`.

## Boundaries
- **Always**:
  - Exclude `PSU` category products from the consumed TDP sum (`PSU.tdp_wattage` is power capacity supplied, not consumed).
  - Normalize socket strings (`strtoupper(trim($socket))`) and RAM strings (`strtoupper(trim($ram))`) before comparison so case/whitespace differences do not cause false mismatches.
  - Keep `CompatibilityService` stateless and free of session/request dependencies so it can be called from CLI, tests, API controllers, and Blade controllers alike.
- **Ask First**:
  - Adding new database tables or schema migrations (none are required for `compatibility-engine`).
- **Never**:
  - Modify or delete files inside `legacy/`.
  - Hardcode product IDs in compatibility rules — always inspect product attributes (`category`, `socket_type`, `ram_type`, `tdp_wattage`, `specs`).

## Success Criteria
1. `App\Services\CompatibilityService` accurately validates CPU/Motherboard sockets, Motherboard/CPU/RAM memory types, total system TDP wattage (excluding PSU capacity from consumed wattage), PSU headroom (`ok`, `tight`, `insufficient`), cooling advice, and CPU/GPU bottleneck metrics.
2. JSON endpoints (`/api/compatibility/evaluate`, `/api/compatibility/compatible-parts`, `/api/compatibility/bottleneck`) return structured validation payloads ready for the PC Builder and checkout flows.
3. ERP staff can open `/erp/compatibility` from the ERP sidebar to interactively test any hardware combination in the catalog.
4. All existing and new tests in `tests/Feature/CompatibilityEngineTest.php` pass 100%.
