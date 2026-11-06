<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\RestockOrder;
use App\Models\SerialNumber;
use App\Models\Supplier;
use App\Models\SupplierHardwareMapping;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventorySupplyTest extends TestCase
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

    public function test_rbac_protects_inventory_routes_from_tech_and_customer(): void
    {
        $routes = [
            route('erp.inventory.index'),
            route('erp.inventory.low-stock'),
            route('erp.inventory.costing'),
            route('erp.products.index'),
            route('erp.suppliers.index'),
            route('erp.restock.index'),
            route('erp.serials.index'),
        ];

        foreach ($routes as $url) {
            $this->actingAs($this->customer)->get($url)->assertStatus(403);
            $this->actingAs($this->techStaff)->get($url)->assertStatus(403);
            $this->actingAs($this->warehouseStaff)->get($url)->assertStatus(200);
            $this->actingAs($this->admin)->get($url)->assertStatus(200);
        }
    }

    public function test_warehouse_staff_can_crud_hardware_product_with_compatibility_specs(): void
    {
        $this->actingAs($this->warehouseStaff);

        // Create
        $response = $this->post(route('erp.products.store'), [
            'name'                => 'MSI MAG B650 TOMAHAWK WIFI',
            'category'            => 'Motherboard',
            'brand'               => 'MSI',
            'socket_type'         => 'AM5',
            'ram_type'            => 'DDR5',
            'tdp_wattage'         => 55,
            'cost_price'          => 175.00,
            'selling_price'       => 219.00,
            'warranty_months'     => 36,
            'low_stock_threshold' => 4,
            'specs'               => 'ATX, AM5, 4x DDR5',
        ]);

        $response->assertRedirect(route('erp.products.index'));

        $product = Product::where('name', 'MSI MAG B650 TOMAHAWK WIFI')->first();
        $this->assertNotNull($product);
        $this->assertSame('AM5', $product->socket_type);
        $this->assertSame('DDR5', $product->ram_type);
        $this->assertSame(55, $product->tdp_wattage);

        // Update
        $updateResponse = $this->put(route('erp.products.update', $product), [
            'name'                => 'MSI MAG B650 TOMAHAWK WIFI V2',
            'category'            => 'Motherboard',
            'brand'               => 'MSI',
            'socket_type'         => 'AM5',
            'ram_type'            => 'DDR5',
            'tdp_wattage'         => 60,
            'cost_price'          => 180.00,
            'selling_price'       => 229.00,
            'warranty_months'     => 36,
            'low_stock_threshold' => 5,
            'specs'               => 'Updated ATX AM5 specs',
        ]);

        $updateResponse->assertRedirect(route('erp.products.index'));
        $this->assertSame('MSI MAG B650 TOMAHAWK WIFI V2', $product->fresh()->name);

        // Delete unreferenced product
        $deleteResponse = $this->delete(route('erp.products.destroy', $product));
        $deleteResponse->assertRedirect(route('erp.products.index'));
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    public function test_supplier_crud_and_hardware_mapping_with_json_lookup(): void
    {
        $this->actingAs($this->warehouseStaff);

        $createResponse = $this->post(route('erp.suppliers.store'), [
            'company_name'   => 'GigaParts MY Distribution',
            'contact_person' => 'Daniel Lim',
            'phone'          => '+60 12-888 9999',
            'email'          => 'sales@gigaparts.my',
            'address'        => 'Petaling Jaya, Selangor',
        ]);

        $supplier = Supplier::where('email', 'sales@gigaparts.my')->firstOrFail();
        $createResponse->assertRedirect(route('erp.suppliers.mapping', $supplier));

        $product = Product::firstOrFail();

        // Assign mapping
        $mapResponse = $this->post(route('erp.suppliers.mapping.save', $supplier), [
            'product_id'        => $product->id,
            'supplier_sku'      => 'GP-SKU-001',
            'agreed_unit_price' => 299.50,
            'lead_time_days'    => 2,
        ]);

        $mapResponse->assertRedirect(route('erp.suppliers.mapping', $supplier));
        $this->assertDatabaseHas('supplier_hardware_mappings', [
            'supplier_id'       => $supplier->id,
            'product_id'        => $product->id,
            'supplier_sku'      => 'GP-SKU-001',
            'agreed_unit_price' => 299.50,
        ]);

        // JSON lookup endpoint for PO builder
        $jsonResponse = $this->getJson(route('erp.restock.supplier-products', ['supplier_id' => $supplier->id]));
        $jsonResponse->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('products.0.product_id', $product->id)
            ->assertJsonPath('products.0.supplier_sku', 'GP-SKU-001');

        // Remove mapping
        $this->delete(route('erp.suppliers.mapping.remove', [$supplier, $product]))
            ->assertRedirect(route('erp.suppliers.mapping', $supplier));

        $this->assertDatabaseMissing('supplier_hardware_mappings', [
            'supplier_id' => $supplier->id,
            'product_id'  => $product->id,
        ]);
    }

    public function test_restock_po_lifecycle_and_idempotent_auto_sn_provisioning(): void
    {
        $this->actingAs($this->warehouseStaff);

        $supplier = Supplier::firstOrFail();
        $product = Product::firstOrFail();
        $initialSnCount = SerialNumber::where('product_id', $product->id)->count();

        // 1. Create PO
        $storeResponse = $this->post(route('erp.restock.store'), [
            'supplier_id'   => $supplier->id,
            'order_date'    => '2026-11-06',
            'expected_date' => '2026-11-09',
            'notes'         => 'Restocking 3 units for test',
            'items'         => [
                [
                    'product_id' => $product->id,
                    'quantity'   => 3,
                    'unit_cost'  => 310.00,
                ],
            ],
        ]);

        $order = RestockOrder::latest('id')->firstOrFail();
        $storeResponse->assertRedirect(route('erp.restock.show', $order));
        $this->assertSame('Pending', $order->status);
        $this->assertEquals(930.00, (float) $order->total_amount);

        // 2. Approve PO
        $this->post(route('erp.restock.status', $order), [
            'status' => 'Approved',
        ])->assertRedirect(route('erp.restock.show', $order));
        $this->assertSame('Approved', $order->fresh()->status);

        // 3. Complete PO with auto_generate_sn = 1
        $this->post(route('erp.restock.status', $order), [
            'status'           => 'Completed',
            'auto_generate_sn' => '1',
        ])->assertRedirect(route('erp.restock.show', $order));

        $order->refresh();
        $this->assertSame('Completed', $order->status);
        $this->assertNotNull($order->completed_at);

        $newSnCount = SerialNumber::where('product_id', $product->id)->count();
        $this->assertSame($initialSnCount + 3, $newSnCount);

        $provisionedSns = SerialNumber::where('restock_order_id', $order->id)->get();
        $this->assertCount(3, $provisionedSns);
        foreach ($provisionedSns as $sn) {
            $this->assertSame(SerialNumber::STATUS_IN_STOCK, $sn->status);
            $this->assertEquals(310.00, (float) $sn->batch_cost);
        }

        // 4. Idempotency check: submitting Completed again must NOT duplicate SNs
        $this->post(route('erp.restock.status', $order), [
            'status'           => 'Completed',
            'auto_generate_sn' => '1',
        ])->assertRedirect(route('erp.restock.show', $order));

        $this->assertCount(3, SerialNumber::where('restock_order_id', $order->id)->get());
    }

    public function test_serial_number_bulk_registration_and_five_state_lifecycle(): void
    {
        $this->actingAs($this->warehouseStaff);

        $product = Product::firstOrFail();

        // Bulk registration (including a duplicate in the same batch)
        $this->post(route('erp.serials.store'), [
            'product_id'    => $product->id,
            'mode'          => 'bulk',
            'batch_cost'    => 295.00,
            'status'        => 'In-Stock',
            'bulk_sn_codes' => "SN-BULK-001\nSN-BULK-002\nSN-BULK-001",
        ])->assertRedirect(route('erp.serials.index'));

        $this->assertDatabaseHas('serial_numbers', ['sn_code' => 'SN-BULK-001', 'batch_cost' => 295.00]);
        $this->assertDatabaseHas('serial_numbers', ['sn_code' => 'SN-BULK-002', 'batch_cost' => 295.00]);
        $this->assertSame(1, SerialNumber::where('sn_code', 'SN-BULK-001')->count());

        $sn = SerialNumber::where('sn_code', 'SN-BULK-001')->firstOrFail();

        // Transition across all 5 states
        foreach (['Reserved', 'Sold', 'RMA', 'Lost', 'In-Stock'] as $nextState) {
            $this->patch(route('erp.serials.status', $sn), [
                'status' => $nextState,
            ])->assertRedirect(route('erp.serials.index'));

            $this->assertSame($nextState, $sn->fresh()->status);
        }

        // Delete SN
        $this->delete(route('erp.serials.destroy', $sn))
            ->assertRedirect(route('erp.serials.index'));
        $this->assertDatabaseMissing('serial_numbers', ['id' => $sn->id]);
    }

    public function test_low_stock_costing_and_purchasing_history_views_render_accurately(): void
    {
        $this->actingAs($this->admin);

        // Seeded data has products under threshold (e.g. Ryzen 7 7800X3D has 3 In-Stock vs threshold 5)
        $this->get(route('erp.inventory.low-stock'))
            ->assertOk()
            ->assertSee('AMD Ryzen 7 7800X3D');

        $this->get(route('erp.inventory.costing'))
            ->assertOk()
            ->assertSee('Total Inventory Asset Value')
            ->assertSee('Category Asset Distribution');

        $this->get(route('erp.restock.history'))
            ->assertOk()
            ->assertSee('RO-20261101-001');
    }

    public function test_cannot_delete_sold_or_order_linked_serial_number(): void
    {
        $this->actingAs($this->warehouseStaff);

        $soldSn = SerialNumber::where('status', SerialNumber::STATUS_SOLD)->firstOrFail();

        $response = $this->delete(route('erp.serials.destroy', $soldSn));
        $response->assertRedirect(route('erp.serials.index'));
        $response->assertSessionHas('error');

        // Confirm database still contains the serial number
        $this->assertDatabaseHas('serial_numbers', ['id' => $soldSn->id]);
    }
}
