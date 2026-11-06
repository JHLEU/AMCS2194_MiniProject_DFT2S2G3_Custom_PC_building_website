<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\SerialNumber;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderCheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $warehouseStaff;
    protected User $techStaff;
    protected User $customer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);

        $this->admin = User::where('email', 'admin@custompc.local')->firstOrFail();
        $this->warehouseStaff = User::where('email', 'warehouse@custompc.local')->firstOrFail();
        $this->techStaff = User::where('email', 'tech@custompc.local')->firstOrFail();
        $this->customer = User::where('email', 'customer@custompc.local')->firstOrFail();
    }

    public function test_customer_can_view_checkout_screen(): void
    {
        $response = $this->get(route('checkout.index'));

        $response->assertOk()
            ->assertSee('Secure Cashier & Payment Gateway', false)
            ->assertSee('Instant Sandbox (1-Click Test)')
            ->assertSee('Stripe Live API Gateway')
            ->assertSee('Coming Soon');
    }

    public function test_customer_can_place_order_via_dummy_checkout_with_auto_sn_reservation(): void
    {
        $cpu = Product::where('name', 'AMD Ryzen 7 7800X3D')->firstOrFail();
        $gpu = Product::where('name', 'NVIDIA GeForce RTX 4070 Super 12GB')->firstOrFail();

        // Count initial In-Stock serial numbers
        $initialCpuInStock = SerialNumber::where('product_id', $cpu->id)->where('status', SerialNumber::STATUS_IN_STOCK)->count();
        $this->assertGreaterThan(0, $initialCpuInStock);

        $payload = [
            'customer_name'    => 'Marcus Sterling',
            'customer_phone'   => '+60 12-998 8776',
            'customer_email'   => 'marcus@example.com',
            'shipping_address' => 'Penthouse 22-A, Pavilion Suites, Bukit Bintang, 55100 Kuala Lumpur',
            'payment_method'   => Payment::METHOD_DUMMY_SANDBOX,
            'items'            => [
                ['product_id' => $cpu->id, 'quantity' => 1, 'unit_price' => 399.00],
                ['product_id' => $gpu->id, 'quantity' => 1, 'unit_price' => 599.00],
            ],
        ];

        // 1. Submit via form POST
        $response = $this->post(route('checkout.process'), $payload);

        $order = Order::where('customer_email', 'marcus@example.com')->first();
        $this->assertNotNull($order);
        $this->assertSame(Order::STATUS_PAID, $order->order_status);
        $this->assertSame('998.00', (string) $order->total_selling_amount);
        $this->assertNotNull($order->tracking_number);

        // Payment check
        $this->assertNotNull($order->payment);
        $this->assertSame(Payment::STATUS_SUCCESS, $order->payment->payment_status);
        $this->assertSame(Payment::METHOD_DUMMY_SANDBOX, $order->payment->payment_method);
        $this->assertStringStartsWith('TXN-', $order->payment->transaction_id);

        // Serial numbers should have been reserved
        $reservedSerials = SerialNumber::where('order_id', $order->id)->get();
        $this->assertNotEmpty($reservedSerials);
        foreach ($reservedSerials as $sn) {
            $this->assertSame(SerialNumber::STATUS_RESERVED, $sn->status);
        }

        // Redirects to success receipt
        $response->assertRedirect(route('checkout.success', $order->order_number));

        // 2. View receipt view
        $receiptResponse = $this->get(route('checkout.success', $order->order_number));
        $receiptResponse->assertOk()
            ->assertSee($order->order_number)
            ->assertSee('Order Placed & Payment Verified!', false)
            ->assertSee('Marcus Sterling');
    }

    public function test_ajax_checkout_submission_returns_json_receipt(): void
    {
        $cpu = Product::where('name', 'AMD Ryzen 7 7800X3D')->firstOrFail();

        $response = $this->postJson(route('checkout.process'), [
            'customer_name'    => 'AJAX Customer',
            'customer_phone'   => '+60 11-2233 4455',
            'customer_email'   => 'ajax.customer@example.com',
            'shipping_address' => 'Cyberjaya Tech Park, 63000 Cyberjaya',
            'payment_method'   => Payment::METHOD_DUMMY_CARD,
            'items'            => [
                ['product_id' => $cpu->id, 'quantity' => 1, 'unit_price' => 399.00],
            ],
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'order_number',
                'transaction_id',
                'tracking_url',
                'receipt_url',
                'message',
            ]);
    }

    public function test_order_lifecycle_transitions_from_paid_to_completed_marks_serials_sold(): void
    {
        $order = Order::where('order_number', 'ORD-20261105-002')->firstOrFail();
        $this->assertSame(Order::STATUS_ASSEMBLING, $order->order_status);

        $this->actingAs($this->techStaff);

        // Transition: Assembling -> Testing
        $this->post(route('erp.orders.status', $order), [
            'order_status' => Order::STATUS_TESTING,
        ])->assertRedirect(route('erp.orders.show', $order));

        $this->assertSame(Order::STATUS_TESTING, $order->fresh()->order_status);

        // Transition: Testing -> Shipped
        $this->post(route('erp.orders.status', $order), [
            'order_status'    => Order::STATUS_SHIPPED,
            'carrier'         => 'DHL Express MY',
            'tracking_number' => 'DHL-99887766MY',
        ])->assertRedirect(route('erp.orders.show', $order));

        $refreshed = $order->fresh();
        $this->assertSame(Order::STATUS_SHIPPED, $refreshed->order_status);
        $this->assertSame('DHL Express MY', $refreshed->carrier);
        $this->assertSame('DHL-99887766MY', $refreshed->tracking_number);

        // Transition: Shipped -> Completed (Reserved SNs become Sold)
        $this->post(route('erp.orders.status', $order), [
            'order_status' => Order::STATUS_COMPLETED,
        ])->assertRedirect(route('erp.orders.show', $order));

        $completedOrder = $order->fresh();
        $this->assertSame(Order::STATUS_COMPLETED, $completedOrder->order_status);

        $linkedSerials = SerialNumber::where('order_id', $order->id)->get();
        $this->assertNotEmpty($linkedSerials);
        foreach ($linkedSerials as $sn) {
            $this->assertSame(SerialNumber::STATUS_SOLD, $sn->status);
        }
    }

    public function test_cancelling_order_releases_reserved_serial_numbers_back_to_in_stock(): void
    {
        $order = Order::where('order_number', 'ORD-20261106-003')->firstOrFail();
        $this->assertSame(Order::STATUS_SHIPPED, $order->order_status);

        // Identify serials reserved for this order
        $serialsBefore = SerialNumber::where('order_id', $order->id)->get();
        $this->assertNotEmpty($serialsBefore);
        $snIds = $serialsBefore->pluck('id')->all();

        $this->actingAs($this->admin);

        // Cancel order
        $this->post(route('erp.orders.status', $order), [
            'order_status' => Order::STATUS_CANCELLED,
        ])->assertRedirect(route('erp.orders.show', $order));

        $this->assertSame(Order::STATUS_CANCELLED, $order->fresh()->order_status);

        // Serial numbers must now be In-Stock and order_id must be null
        $releasedSerials = SerialNumber::whereIn('id', $snIds)->get();
        foreach ($releasedSerials as $sn) {
            $this->assertSame(SerialNumber::STATUS_IN_STOCK, $sn->status);
            $this->assertNull($sn->order_id);
        }
    }

    public function test_public_order_tracking_lookup_by_order_number_and_phone(): void
    {
        // 1. Search by exact order number
        $respByCode = $this->get(route('orders.track', ['query' => 'ORD-20261102-001']));
        $respByCode->assertOk()
            ->assertSee('ORD-20261102-001')
            ->assertSee('Bryan Tan (Gamer)')
            ->assertSee('Delivered');

        // 2. Search by phone number
        $respByPhone = $this->get(route('orders.track', ['query' => '+60 16-888 2345']));
        $respByPhone->assertOk()
            ->assertSee('ORD-20261105-002')
            ->assertSee('Darren Lim')
            ->assertSee('Bench Assembly');

        // 3. Search non-existent
        $respNotFound = $this->get(route('orders.track', ['query' => 'NON-EXISTENT-CODE']));
        $respNotFound->assertOk()
            ->assertSee('No active or archived order found matching');
    }

    public function test_customer_can_view_order_history_and_guest_is_redirected(): void
    {
        // Guest redirected to login
        $this->get(route('customer.orders'))->assertRedirect(route('login'));

        // Customer sees orders
        $response = $this->actingAs($this->customer)->get(route('customer.orders'));
        $response->assertOk()
            ->assertSee('My Custom PC Orders History')
            ->assertSee('ORD-20261102-001');
    }

    public function test_erp_orders_management_accessible_to_staff_and_protected_from_customers(): void
    {
        $order = Order::where('order_number', 'ORD-20261102-001')->firstOrFail();

        // Customer receives 403
        $this->actingAs($this->customer)->get(route('erp.orders.index'))->assertStatus(403);
        $this->actingAs($this->customer)->get(route('erp.orders.show', $order))->assertStatus(403);

        // Staff can access
        $this->actingAs($this->admin)->get(route('erp.orders.index'))->assertOk()->assertSee('Orders Queue');
        $this->actingAs($this->warehouseStaff)->get(route('erp.orders.index'))->assertOk();
        $this->actingAs($this->techStaff)->get(route('erp.orders.show', $order))->assertOk()->assertSee($order->order_number);
    }

    public function test_erp_staff_can_update_order_shipping_consignment(): void
    {
        $order = Order::where('order_number', 'ORD-20261105-002')->firstOrFail();

        $this->actingAs($this->warehouseStaff);

        $response = $this->post(route('erp.orders.shipping', $order), [
            'carrier'         => 'Ninja Van Cargo',
            'tracking_number' => 'NINJA-887766MY',
        ]);

        $response->assertRedirect(route('erp.orders.show', $order));

        $updated = $order->fresh();
        $this->assertSame('Ninja Van Cargo', $updated->carrier);
        $this->assertSame('NINJA-887766MY', $updated->tracking_number);
    }

    public function test_checkout_ignores_client_side_price_tampering_and_uses_catalog_price(): void
    {
        $product = Product::firstOrFail();
        $this->assertGreaterThan(1.00, (float) $product->selling_price);

        $payload = [
            'customer_name'    => 'Security Tester',
            'customer_phone'   => '+60 12-345 6789',
            'customer_email'   => 'tester@security.org',
            'shipping_address' => '404 Pentest Lane, Cyberjaya',
            'payment_method'   => Payment::METHOD_DUMMY_SANDBOX,
            'items'            => [
                [
                    'product_id' => $product->id,
                    'quantity'   => 2,
                    'unit_price' => 0.01, // Malicious price tampering attempt
                ],
            ],
        ];

        $response = $this->post(route('checkout.process'), $payload);
        $response->assertRedirect();

        $order = Order::where('customer_email', 'tester@security.org')->latest()->first();
        $this->assertNotNull($order);
        // Selling price should be trusted catalog price * 2, not 0.02
        $expectedTotal = (float) $product->selling_price * 2;
        $this->assertSame($expectedTotal, (float) $order->total_selling_amount);
    }

    public function test_public_tracking_prevents_wildcard_leakage_for_short_queries(): void
    {
        // Short queries with < 8 digits must not match arbitrary phone numbers
        $respSingleDigit = $this->get(route('orders.track', ['query' => '0']));
        $respSingleDigit->assertOk()->assertSee('No active or archived order found matching');

        $respCountryCode = $this->get(route('orders.track', ['query' => '60']));
        $respCountryCode->assertOk()->assertSee('No active or archived order found matching');
    }

    public function test_reserve_serial_numbers_is_idempotent(): void
    {
        $order = Order::where('order_number', 'ORD-20261105-002')->firstOrFail();
        $initialSerialsCount = SerialNumber::where('order_id', $order->id)->count();

        $checkoutService = app(\App\Services\CheckoutService::class);
        $checkoutService->reserveSerialNumbers($order);
        $checkoutService->reserveSerialNumbers($order);

        $afterSerialsCount = SerialNumber::where('order_id', $order->id)->count();
        $this->assertSame($initialSerialsCount, $afterSerialsCount);
    }
}
