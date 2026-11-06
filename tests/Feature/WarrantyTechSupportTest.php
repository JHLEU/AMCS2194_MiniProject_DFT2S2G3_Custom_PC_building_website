<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\SerialNumber;
use App\Models\SupportTicket;
use App\Models\TechSchedule;
use App\Models\User;
use App\Models\Warranty;
use App\Services\CheckoutService;
use App\Services\SupportTicketService;
use App\Services\WarrantyService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class WarrantyTechSupportTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $techStaff;
    protected User $warehouseStaff;
    protected User $customer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);

        $this->admin = User::where('email', 'admin@custompc.local')->firstOrFail();
        $this->techStaff = User::where('email', 'tech@custompc.local')->firstOrFail();
        $this->warehouseStaff = User::where('email', 'warehouse@custompc.local')->firstOrFail();
        $this->customer = User::where('email', 'customer@custompc.local')->firstOrFail();
    }

    public function test_warranties_auto_provisioned_on_order_completion(): void
    {
        $product = Product::first();
        $serial = SerialNumber::where('status', SerialNumber::STATUS_IN_STOCK)->firstOrFail();

        $order = Order::create([
            'order_number'         => 'ORD-TEST-WARRANTY',
            'customer_id'          => $this->customer->id,
            'customer_name'        => $this->customer->name,
            'customer_email'       => $this->customer->email,
            'customer_phone'       => '0123456789',
            'shipping_address'     => '123 Test Street',
            'total_cost_amount'    => 100.00,
            'total_selling_amount' => 150.00,
            'order_status'         => Order::STATUS_PAID,
        ]);

        $item = OrderItem::create([
            'order_id'         => $order->id,
            'product_id'       => $product->id,
            'quantity'         => 1,
            'unit_price'       => 150.00,
        ]);

        $serial->update([
            'order_id' => $order->id,
            'status'   => SerialNumber::STATUS_RESERVED,
        ]);

        // Mark serial numbers sold via CheckoutService
        $checkoutService = app(CheckoutService::class);
        $checkoutService->markSerialNumbersSold($order);

        // Verify serial number marked Sold
        $serial->refresh();
        $this->assertSame(SerialNumber::STATUS_SOLD, $serial->status);

        // Verify Warranty record created
        $warranty = Warranty::where('serial_number_id', $serial->id)->first();
        $this->assertNotNull($warranty);
        $this->assertSame($order->id, $warranty->order_id);
        $this->assertSame(Warranty::STATUS_ACTIVE, $warranty->status);
        $this->assertGreaterThan(700, $warranty->daysRemaining());
    }

    public function test_customer_can_view_own_warranties_and_open_rma_ticket(): void
    {
        // Customer visits warranties page
        $response = $this->actingAs($this->customer)->get(route('customer.warranties.index'));
        $response->assertOk();
        $response->assertSee('My Hardware Warranties');

        $warranty = Warranty::whereHas('order', function ($q) {
            $q->where('customer_id', $this->customer->id);
        })->firstOrFail();

        // Customer visits ticket creation form
        $formResponse = $this->actingAs($this->customer)->get(route('customer.support.create', ['warranty_id' => $warranty->id]));
        $formResponse->assertOk();
        $formResponse->assertSee('Submit Support & RMA Claim', false);

        // Customer posts ticket
        $postResponse = $this->actingAs($this->customer)->post(route('customer.support.store'), [
            'warranty_id'       => $warranty->id,
            'subject'           => 'Overheating and throttling under load',
            'issue_description' => 'CPU temperatures reach 98 degrees Celsius immediately upon launching Cinebench R23, causing thermal throttling.',
        ]);

        $ticket = SupportTicket::where('subject', 'Overheating and throttling under load')->first();
        $this->assertNotNull($ticket);
        $this->assertSame(SupportTicket::STATUS_OPEN, $ticket->status);
        $this->assertSame($this->customer->id, $ticket->customer_id);

        $postResponse->assertRedirect(route('customer.support.show', $ticket));

        // Customer can view ticket detail
        $showResponse = $this->actingAs($this->customer)->get(route('customer.support.show', $ticket));
        $showResponse->assertOk();
        $showResponse->assertSee('Overheating and throttling under load');
        $showResponse->assertSee('Triage in Progress');
    }

    public function test_customer_cannot_view_or_claim_another_customers_ticket_or_warranty(): void
    {
        $otherUser = User::factory()->create();

        $warranty = Warranty::firstOrFail();
        $ticket = SupportTicket::firstOrFail();

        // Acting as another user trying to view the ticket
        $response = $this->actingAs($otherUser)->get(route('customer.support.show', $ticket));
        $response->assertStatus(403);

        // Acting as another user trying to claim someone else's warranty
        $postResponse = $this->actingAs($otherUser)->post(route('customer.support.store'), [
            'warranty_id'       => $warranty->id,
            'subject'           => 'Unauthorized warranty claim attempt',
            'issue_description' => 'Trying to file a claim on hardware I did not purchase from the store.',
        ]);

        $postResponse->assertSessionHasErrors('warranty_id');
    }

    public function test_erp_support_desk_ticket_lifecycle_and_technician_dispatch(): void
    {
        $ticket = SupportTicket::firstOrFail();

        // Tech staff views ticket workbench
        $workbenchResponse = $this->actingAs($this->techStaff)->get(route('erp.support.show', $ticket));
        $workbenchResponse->assertOk();
        $workbenchResponse->assertSee($ticket->subject);

        // Update status to WaitingForParts (RMA)
        $statusResponse = $this->actingAs($this->techStaff)->post(route('erp.support.status', $ticket), [
            'status' => SupportTicket::STATUS_WAITING_FOR_PARTS,
        ]);
        $statusResponse->assertRedirect();

        $ticket->refresh();
        $this->assertSame(SupportTicket::STATUS_WAITING_FOR_PARTS, $ticket->status);
        if ($ticket->warranty && $ticket->warranty->serialNumber) {
            $this->assertSame(SerialNumber::STATUS_RMA, $ticket->warranty->serialNumber->status);
        }

        // Assign technician and schedule workshop appointment
        $assignResponse = $this->actingAs($this->techStaff)->post(route('erp.support.assign', $ticket), [
            'technician_id'            => $this->techStaff->id,
            'scheduled_datetime'       => now()->addDays(2)->format('Y-m-d H:i:s'),
            'estimated_duration_hours' => 3,
            'notes'                    => 'Prepare thermal paste repasting and bench test.',
        ]);
        $assignResponse->assertRedirect();

        $schedule = TechSchedule::where('support_ticket_id', $ticket->id)->latest('id')->first();
        $this->assertNotNull($schedule);
        $this->assertSame($this->techStaff->id, $schedule->technician_id);
        $this->assertSame(3, $schedule->estimated_duration_hours);

        // View dispatch schedules calendar
        $schedResponse = $this->actingAs($this->techStaff)->get(route('erp.support.schedules'));
        $schedResponse->assertOk();
        $schedResponse->assertSee('Technician Dispatch & Workshop Schedules', false);

        // View warranties registry
        $warrantiesResponse = $this->actingAs($this->techStaff)->get(route('erp.support.warranties'));
        $warrantiesResponse->assertOk();
        $warrantiesResponse->assertSee('Hardware Warranty Registry', false);
    }

    public function test_erp_support_desk_rbac_protection(): void
    {
        $routes = [
            route('erp.support.index'),
            route('erp.support.schedules'),
            route('erp.support.warranties'),
        ];

        foreach ($routes as $url) {
            // Guest redirected
            Auth::logout();
            $this->get($url)->assertRedirect(route('login'));

            // Customer forbidden
            $this->actingAs($this->customer)->get($url)->assertStatus(403);

            // Warehouse staff forbidden from tech support desk
            $this->actingAs($this->warehouseStaff)->get($url)->assertStatus(403);

            // Tech staff granted
            $this->actingAs($this->techStaff)->get($url)->assertOk();

            // Admin granted
            $this->actingAs($this->admin)->get($url)->assertOk();
        }
    }
}
