<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RoleAndUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndUserSeeder::class);
    }

    public function test_guest_is_redirected_to_login_when_accessing_erp(): void
    {
        $response = $this->get(route('erp.dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_customer_cannot_access_erp_dashboard(): void
    {
        $customer = User::where('email', 'customer@custompc.local')->first();
        $this->actingAs($customer);

        $response = $this->get(route('erp.dashboard'));
        $response->assertStatus(403);
    }

    public function test_admin_can_access_erp_dashboard_and_users(): void
    {
        $admin = User::where('email', 'admin@custompc.local')->first();
        $this->actingAs($admin);

        $response = $this->get(route('erp.dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Enterprise Operations Dashboard');

        $usersResponse = $this->get(route('erp.users.index'));
        $usersResponse->assertStatus(200);
    }

    public function test_warehouse_staff_can_access_inventory_but_not_user_management(): void
    {
        $warehouse = User::where('email', 'warehouse@custompc.local')->first();
        $this->actingAs($warehouse);

        $response = $this->get(route('erp.dashboard'));
        $response->assertStatus(200);

        $inventoryResponse = $this->get(route('erp.inventory.index'));
        $inventoryResponse->assertStatus(200);

        $usersResponse = $this->get(route('erp.users.index'));
        $usersResponse->assertStatus(403);
    }

    public function test_tech_staff_can_access_support_but_not_user_management(): void
    {
        $tech = User::where('email', 'tech@custompc.local')->first();
        $this->actingAs($tech);

        $response = $this->get(route('erp.dashboard'));
        $response->assertStatus(200);

        $supportResponse = $this->get(route('erp.support.index'));
        $supportResponse->assertStatus(200);

        $usersResponse = $this->get(route('erp.users.index'));
        $usersResponse->assertStatus(403);
    }
}
