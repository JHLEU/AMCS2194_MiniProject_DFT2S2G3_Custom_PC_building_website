# Spec: order-checkout

## Objective
Implement the **Order Checkout, Mock/Sandbox Payment Gateway, and Assembly Lifecycle** module (`order-checkout`). This module provides end-to-end customer checkout with a dummy/sandbox payment processor (ready for future Stripe integration), automatic serial number reservation and costing, customer-facing order tracking with a visual milestone stepper, and complete ERP Orders Management (`/erp/orders`) for staff to manage order progression (`Pending` → `Paid` → `Assembling` → `Testing` → `Shipped` → `Completed` / `Cancelled`).

Core capabilities:
1. **Dummy / Sandbox Checkout Section**:
   - Interactive checkout page (`/checkout`) displaying order line items, quantity, unit price, subtotal, and grand total.
   - Customer shipping details form (`customer_name`, `customer_phone`, `customer_email`, `shipping_address`, `notes`).
   - Mock/Sandbox payment method selector (`Mock Credit/Debit Card`, `Mock FPX Online Banking`, `Demo Sandbox Instant`, with a clear "Stripe Live API (Coming Soon)" badge/indicator).
   - Checkout submission processing via `App\Services\CheckoutService` wrapped in `DB::transaction()`.
   - Generates unique order number (`ORD-YYYYMMDD-XXXXX`) and payment transaction ID (`TXN-YYYYMMDD-XXXXXX`).
   - Success confirmation / receipt view (`/checkout/success/{order_number}`).
2. **Serial Number Reservation & Cost Allocation**:
   - When an order transitions to `Paid`, available `In-Stock` `SerialNumber` records matching the ordered products are automatically allocated and marked `Reserved` (`order_id = $order->id`).
   - The total hardware batch cost from reserved serial numbers is calculated and stored in `orders.total_cost_amount`, enabling exact gross profit reporting.
   - When an order reaches `Completed`, reserved serial numbers transition from `Reserved` to `Sold`.
   - If an order is `Cancelled`, any reserved serial numbers are released back to `In-Stock` (`order_id = null`).
3. **Public Order Tracking & Customer Order History**:
   - Public tracking portal (`/orders/track`) allowing any customer to look up their build progress by `order_number` or `customer_phone`.
   - Visual 6-stage timeline stepper:
     1. `Pending` (Awaiting Payment)
     2. `Paid` (Payment Verified, Parts Allocated)
     3. `Assembling` (Technician Bench Assembly in Progress)
     4. `Testing` (Stress Testing, BIOS, OS Benchmark)
     5. `Shipped` (Dispatched via Courier with Tracking Number)
     6. `Completed` (Delivered / Handed Over)
   - Displays courier info (`carrier`, `tracking_number`) and full hardware item breakdown.
   - Authenticated customer portal (`/customer/orders`) displaying all past and active orders for the logged-in user.
4. **ERP Orders Management**:
   - Staff orders queue (`/erp/orders`) accessible to all staff (`Admin`, `Staff_Warehouse`, `Staff_Tech`).
   - Filter by status (`Paid`, `Assembling`, `Testing`, `Shipped`, `Completed`, `Cancelled`), keyword search, and summary KPI cards (Total Orders, Pending Assembly, In Testing, Dispatched, Total Revenue).
   - Order detail view (`/erp/orders/{order}`) displaying:
     - Customer contact & delivery address
     - Ordered hardware components with assigned physical serial numbers
     - Payment record details (Method, Transaction ID, Status, Timestamp)
     - Courier dispatch controls (Tracking number & carrier update)
     - 1-click status progression workflow buttons (`Start Assembly`, `Send to Testing`, `Dispatch Order`, `Mark Completed`, `Cancel Order`).
5. **Sample Seed Data**:
   - Seed sample customer orders in `InventorySupplySeeder` across multiple lifecycle states (`Paid`, `Assembling`, `Shipped`, `Completed`) with linked `order_items`, `payments`, and `serial_numbers`.

## Tech Stack
- **Framework**: Laravel 12.x (PHP 8.2+)
- **Database**: SQLite (local/testing) & MySQL/MariaDB compatible
- **Architecture**: Eloquent Models (`Order`, `OrderItem`, `Payment`), Service Layer (`App\Services\CheckoutService`), Controllers (`CheckoutController`, `OrderTrackingController`, `Erp\OrderController`)
- **Frontend**: Blade templates extending `layouts/app.blade.php` and `layouts/erp.blade.php`, Plain CSS (`public/css/style.css`), jQuery 3.7.1 (`public/js/app.js`)
- **Payment Gateway**: Extensible Sandbox / Dummy Mock Driver with future Stripe API compatibility hooks.

## Project Structure
```
app/
├── Models/
│   ├── Order.php                                → Customer order model, statuses, relationships, scopes
│   ├── OrderItem.php                            → Line item model (product, quantity, price)
│   ├── Payment.php                              → Payment transaction record (method, transaction_id, status)
│   ├── Product.php                              → Updated with orderItems relationship
│   ├── SerialNumber.php                         → Updated with order relationship
│   └── User.php                                 → Updated with orders relationship
├── Services/
│   └── CheckoutService.php                      → Order creation, dummy payment execution, SN reservation/release, status workflow
├── Http/
│   └── Controllers/
│       ├── CheckoutController.php               → Customer checkout & success receipt views
│       ├── OrderTrackingController.php          → Public order tracking & customer order history
│       └── Erp/
│           ├── OrderController.php              → ERP staff orders queue, detail view, status progression, shipping update
│           └── DashboardController.php          → Updated with real activeOrders & totalRevenue queries
resources/
└── views/
    ├── checkout/
    │   ├── index.blade.php                      → Customer checkout & dummy payment form
    │   └── success.blade.php                    → Order receipt & confirmation with tracking link
    ├── orders/
    │   ├── track.blade.php                      → Public order tracking & 6-stage milestone timeline
    │   └── index.blade.php                      → Customer order history list
    ├── erp/
    │   └── orders/
    │       ├── index.blade.php                  → ERP orders queue with status filter & metrics
    │       └── show.blade.php                   → ERP order detail view, assigned serials & lifecycle actions
    └── layouts/
        ├── app.blade.php                        → Updated nav links (Track Order, My Orders)
        └── erp.blade.php                        → Active Orders Management sidebar link
routes/
└── web.php                                      → Customer checkout, tracking, and ERP order routes
tests/
└── Feature/
    └── OrderCheckoutTest.php                    → Comprehensive tests for checkout, dummy payment, SN lifecycle, tracking, and ERP RBAC
```

## Testing Strategy
- **Framework**: PHPUnit (`RefreshDatabase` with in-memory SQLite)
- **Test File**: `tests/Feature/OrderCheckoutTest.php`
- **Key Test Cases**:
  1. **Dummy Checkout Flow**: Submitting valid customer data and items creates `Order`, `OrderItem`s, and a `Success` `Payment`, returning order number and redirecting/responding with success.
  2. **Serial Number Reservation**: Placing a `Paid` order automatically reserves available `In-Stock` serial numbers for each item (`status = 'Reserved'`, `order_id = $order->id`) and updates `total_cost_amount`.
  3. **Order Lifecycle Transitions**:
     - `Paid` → `Assembling` → `Testing` → `Shipped` (assigns tracking number & carrier) → `Completed` (transitions reserved serial numbers to `Sold`).
     - Cancelling an order releases all reserved serial numbers back to `In-Stock` with `order_id = null`.
  4. **Public Order Tracking**: Searching by `order_number` or `customer_phone` on `/orders/track` renders the order details and current milestone status.
  5. **ERP Orders Management RBAC**: Staff (`Admin`, `Staff_Warehouse`, `Staff_Tech`) can view and update order status (`200 OK`); unauthenticated users and `Customer` receive `403 Forbidden` on `/erp/orders`.
