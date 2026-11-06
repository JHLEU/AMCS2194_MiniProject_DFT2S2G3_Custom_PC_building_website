# Tasks: warranty-techsupport

## Task 1: Models & Core Service Layer (`Warranty`, `SupportTicket`, `TechSchedule`, `WarrantyService`, `SupportTicketService`)
**Description:**
- Implement Eloquent models: `App\Models\Warranty`, `App\Models\SupportTicket`, `App\Models\TechSchedule` with proper relationships and status constants.
- Implement `App\Services\WarrantyService` for auto-registering warranties on order completion (`registerWarrantiesForOrder`), checking expiry, and searching by serial number.
- Connect `registerWarrantiesForOrder()` in `CheckoutService::completeOrder()`.
- Implement `App\Services\SupportTicketService` for ticket creation, status progression, and technician workshop dispatch scheduling.

**Acceptance criteria:**
- [x] Models defined with casting, fillable fields, and relationships (`belongsTo`, `hasMany`).
- [x] `WarrantyService::registerWarrantiesForOrder($order)` creates 24-month active warranties for order serials.
- [x] `SupportTicketService::createTicket()` validates customer ownership and creates initial `Open` ticket.
- [x] `SupportTicketService::assignTechnician()` creates/updates `TechSchedule` records.

**Verification:**
- [x] Unit / service test verification.

**Dependencies:** None

**Files likely touched:**
- `app/Models/Warranty.php`
- `app/Models/SupportTicket.php`
- `app/Models/TechSchedule.php`
- `app/Models/Order.php`
- `app/Models/SerialNumber.php`
- `app/Services/WarrantyService.php`
- `app/Services/SupportTicketService.php`
- `app/Services/CheckoutService.php`

**Estimated scope:** Medium (8 files)

---

## Task 2: Customer Portal Warranty & Ticket System
**Description:**
- Implement `Customer\WarrantyController` (`index`) and `Customer\SupportTicketController` (`index`, `create`, `store`, `show`).
- Implement Blade views in `resources/views/customer/`:
  - `warranties/index.blade.php`: Customer's active/expired hardware warranties with expiry days and "File RMA Claim" action.
  - `support/index.blade.php`: Customer's tickets list with status badges.
  - `support/create.blade.php`: Ticket submission form with option to attach an active warranty/order.
  - `support/show.blade.php`: Ticket status tracking, diagnostic description, and technician dispatch info.
- Register routes in `routes/web.php` under `middleware: ['auth']`.

**Acceptance criteria:**
- [x] `GET /customer/warranties` lists only the logged-in customer's hardware warranties.
- [x] `GET /customer/support` lists tickets; `POST /customer/support` submits new ticket.
- [x] Customer cannot view tickets belonging to another user (403/404).

**Verification:**
- [x] Routes registered and verified via browser/feature tests.

**Dependencies:** Task 1

**Files likely touched:**
- `app/Http/Controllers/Customer/WarrantyController.php`
- `app/Http/Controllers/Customer/SupportTicketController.php`
- `resources/views/customer/warranties/index.blade.php`
- `resources/views/customer/support/index.blade.php`
- `resources/views/customer/support/create.blade.php`
- `resources/views/customer/support/show.blade.php`
- `routes/web.php`

**Estimated scope:** Medium (7 files)

---

## Task 3: ERP Technician Support Workbench & Workshop Dispatch
**Description:**
- Implement `Erp\SupportTicketController` (`index`, `show`, `updateStatus`, `assignSchedule`, `warranties`) and `Erp\TechScheduleController` (`index`).
- Implement Blade views in `resources/views/erp/support/`:
  - `index.blade.php`: Ticket triage dashboard with KPI count cards (`Open`, `Diagnosing`, `WaitingForParts`, `Resolved`, `Closed`) and filterable ticket queue.
  - `show.blade.php`: Detailed ticket workbench showing customer details, serial number, warranty badge, status transition buttons, and appointment dispatch form.
  - `schedules.blade.php`: Workshop timeline calendar / dispatch schedule for technicians.
  - `warranties.blade.php`: Searchable warranty registry across all sold serials.
- Register ERP routes in `routes/web.php` under `middleware: ['auth', 'role:staff', 'role:Admin,Staff_Tech']`.
- Update navigation links in `resources/views/layouts/erp.blade.php`.

**Acceptance criteria:**
- [x] `GET /erp/support` renders ticket triage dashboard with correct status counts.
- [x] `POST /erp/support/tickets/{ticket}/status` transitions status (`Open` -> `Diagnosing` -> `Resolved` -> `Closed`).
- [x] `POST /erp/support/tickets/{ticket}/assign` schedules inspection slot with technician.
- [x] `GET /erp/support/schedules` lists workshop appointments.
- [x] `Staff_Warehouse` and `Customer` are blocked with 403 Forbidden.

**Verification:**
- [x] Routes registered and verified via feature test assertions.

**Dependencies:** Task 1, Task 2

**Files likely touched:**
- `app/Http/Controllers/Erp/SupportTicketController.php`
- `app/Http/Controllers/Erp/TechScheduleController.php`
- `resources/views/erp/support/index.blade.php`
- `resources/views/erp/support/show.blade.php`
- `resources/views/erp/support/schedules.blade.php`
- `resources/views/erp/support/warranties.blade.php`
- `resources/views/layouts/erp.blade.php`
- `routes/web.php`

**Estimated scope:** Large (8 files)

---

## Task 4: Automated Feature Tests (`WarrantyTechSupportTest`) & Seeding
**Description:**
- Update `Database\Seeders\InventorySupplySeeder.php` to seed initial warranties, support tickets, and technician schedules for realistic test data.
- Implement `tests/Feature/WarrantyTechSupportTest.php` testing:
  - Automatic warranty provisioning on order completion.
  - Customer warranty list and ticket submission.
  - Customer ticket isolation (no viewing others' tickets).
  - Technician/Admin triage workflow, status updates, and dispatch scheduling.
  - RBAC verification (`Staff_Tech` and `Admin` allowed; `Staff_Warehouse` and `Customer` blocked from `/erp/support`).
- Run `php artisan test` and verify all tests pass 100%.

**Acceptance criteria:**
- [x] All warranty & tech support tests pass.
- [x] Zero regressions across existing test suites (`php artisan test`).
- [x] `php artisan migrate:fresh --seed` runs cleanly.

**Verification:**
- [x] `php artisan test` completes with 0 errors.

**Dependencies:** Task 1, Task 2, Task 3

**Files likely touched:**
- `database/seeders/InventorySupplySeeder.php`
- `tests/Feature/WarrantyTechSupportTest.php`

**Estimated scope:** Medium (2 files)

---

## Checkpoint: Complete (`warranty-techsupport`)
- [x] All acceptance criteria in `SPEC-warranty-techsupport.md` met
- [x] `php artisan migrate:fresh --seed` and `php artisan test` pass 100%
- [x] Ready for human review
