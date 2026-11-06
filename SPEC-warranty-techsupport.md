# Specification: Module 6 — `warranty-techsupport`

## 1. Objective
Implement the warranty lifecycle, customer RMA / support ticketing system, and technician workshop dispatch scheduling. This enables automated warranty coverage upon hardware purchase, self-service customer RMA claim filing, and ERP triage / schedule coordination for certified technicians (`Staff_Tech` and `Admin`).

---

## 2. Architecture & Database Integration

Existing migrations (`2026_11_06_000004_create_warranties_and_tickets_table.php`):
1. **`warranties`**:
   - `id`: Primary key
   - `serial_number_id`: FK → `serial_numbers.id` (1:1 with hardware unit)
   - `order_id`: FK → `orders.id`
   - `start_date`: Date (order completion/sale date)
   - `end_date`: Date (`start_date` + warranty duration, default 2 years)
   - `status`: Enum (`Active`, `Expired`, `Void`)
2. **`support_tickets`**:
   - `id`: Primary key
   - `customer_id`: FK → `users.id`
   - `warranty_id`: Nullable FK → `warranties.id` (RMA hardware claim vs general technical consultation)
   - `subject`: String(200)
   - `issue_description`: Text
   - `status`: Enum (`Open`, `Diagnosing`, `WaitingForParts`, `Resolved`, `Closed`)
3. **`tech_schedules`**:
   - `id`: Primary key
   - `support_ticket_id`: FK → `support_tickets.id`
   - `technician_id`: FK → `users.id` (must have `Staff_Tech` or `Admin` role)
   - `scheduled_datetime`: DateTime
   - `estimated_duration_hours`: Integer (default 1)
   - `notes`: Text (nullable diagnostic or repair work notes)

---

## 3. Core Business Services

### 3.1 `WarrantyService` (`App\Services\WarrantyService`)
- `registerWarrantiesForOrder(Order $order, int $warrantyYears = 2): Collection`
  - Iterates through completed order items with assigned serial numbers.
  - Idempotently creates `Warranty` records if not already registered.
  - Sets `start_date = today()`, `end_date = today()->addYears($warrantyYears)`, `status = 'Active'`.
- `checkAndUpdateExpiry(): int`
  - Utility checking expired active warranties (`end_date < today()`) and flipping them to `'Expired'`.
- `lookupBySerialNumber(string $serialNumberCode): ?Warranty`
  - Fast lookup for staff triage desk.

### 3.2 `SupportTicketService` (`App\Services\SupportTicketService`)
- `createTicket(User $customer, array $data): SupportTicket`
  - Validates ownership if `warranty_id` is supplied.
  - Creates ticket with initial status `'Open'`.
- `updateStatus(SupportTicket $ticket, string $status, ?string $adminNote = null): SupportTicket`
  - Transitions ticket state and optionally marks serial number as `RMA` when waiting for parts or defective.
- `assignTechnician(SupportTicket $ticket, int $technicianId, array $scheduleData): TechSchedule`
  - Assigns ticket to certified technician, schedules inspection time, logs estimated duration and workbench notes.

---

## 4. Web Endpoints & UI Routing

### 4.1 Customer Portal (`middleware: ['auth']`)
- `GET /customer/warranties` (`Customer\WarrantyController@index`): List customer's hardware warranties with expiry countdown and "File RMA" action.
- `GET /customer/support` (`Customer\SupportTicketController@index`): List customer's support tickets with status tags.
- `GET /customer/support/create` (`Customer\SupportTicketController@create`): Ticket submission form (select warranty or general issue).
- `POST /customer/support` (`Customer\SupportTicketController@store`): Store ticket.
- `GET /customer/support/{ticket}` (`Customer\SupportTicketController@show`): Ticket details, repair notes, and appointment date.

### 4.2 ERP Staff & Admin Support Desk (`middleware: ['auth', 'role:staff', 'role:Admin,Staff_Tech']`)
- `GET /erp/support` (`Erp\SupportTicketController@index`): Support ticket triage dashboard with status counter cards and filterable ticket queue.
- `GET /erp/support/tickets/{ticket}` (`Erp\SupportTicketController@show`): Full ticket inspection: customer details, linked hardware & warranty validity, triage status transition buttons, technician appointment booking.
- `POST /erp/support/tickets/{ticket}/status` (`Erp\SupportTicketController@updateStatus`): Update status (`Diagnosing`, `WaitingForParts`, `Resolved`, `Closed`).
- `POST /erp/support/tickets/{ticket}/assign` (`Erp\SupportTicketController@assignSchedule`): Dispatch ticket to technician, set datetime and workbench notes.
- `GET /erp/support/schedules` (`Erp\TechScheduleController@index`): Workshop timeline & dispatch calendar listing all scheduled technician appointments.
- `GET /erp/support/warranties` (`Erp\SupportTicketController@warranties`): Staff warranty registry with serial number search.

---

## 5. Security & RBAC Guardrails
- **Customer Isolation**: Customer can only view/file tickets for their own user ID and claim warranties attached to their purchased orders.
- **ERP Staff Authorization**:
  - `/erp/support/*` routes strictly require `Admin` or `Staff_Tech`.
  - `Staff_Warehouse` and `Customer` are blocked with HTTP 403 Forbidden.

---

## 6. Verification Criteria
1. `tests/Feature/WarrantyTechSupportTest.php` covers:
   - Auto warranty registration on order completion with 2-year duration.
   - Customer viewing warranties and submitting support ticket.
   - Customer cannot view or modify tickets belonging to other customers.
   - Technician / Admin triaging tickets, updating status, and assigning workshop schedules.
   - Role enforcement (Staff_Tech & Admin allowed; Staff_Warehouse & Customer blocked from `/erp/support`).
2. `php artisan test` passes with 0 failures across all test suites.
