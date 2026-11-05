<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleAndUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndUserSeeder::class);
    }

    public function test_can_view_login_page(): void
    {
        $response = $this->get(route('login'));
        $response->assertStatus(200);
        $response->assertSee('Account Login');
    }

    public function test_can_view_register_page(): void
    {
        $response = $this->get(route('register'));
        $response->assertStatus(200);
        $response->assertSee('Create Customer Account');
    }

    public function test_customer_can_register(): void
    {
        $response = $this->post(route('register.post'), [
            'name' => 'John Doe',
            'username' => 'johndoe',
            'email' => 'john@example.com',
            'phone' => '+6011-22334455',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertAuthenticated();

        $user = User::where('email', 'john@example.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->isCustomer());
    }

    public function test_admin_can_login_with_email_and_redirects_to_erp(): void
    {
        $response = $this->post(route('login.post'), [
            'login' => 'admin@custompc.local',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('erp.dashboard'));
        $this->assertAuthenticated();
    }

    public function test_staff_can_login_with_username_and_redirects_to_erp(): void
    {
        $response = $this->post(route('login.post'), [
            'login' => 'warehouse_staff',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('erp.dashboard'));
        $this->assertAuthenticated();
    }

    public function test_customer_login_redirects_to_home(): void
    {
        $response = $this->post(route('login.post'), [
            'login' => 'customer@custompc.local',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertAuthenticated();
    }

    public function test_login_with_remember_me_sets_recaller_cookie(): void
    {
        $response = $this->post(route('login.post'), [
            'login' => 'customer@custompc.local',
            'password' => 'password123',
            'remember' => '1',
        ]);

        $response->assertRedirect(route('home'));
        $response->assertCookie(\Illuminate\Support\Facades\Auth::guard()->getRecallerName());
        $this->assertNotNull(User::where('email', 'customer@custompc.local')->first()->remember_token);
    }

    public function test_user_factory_creates_valid_user_with_default_role(): void
    {
        $user = User::factory()->create();

        $this->assertNotNull($user->id);
        $this->assertNotEmpty($user->username);
        $this->assertTrue($user->isCustomer());
    }

    public function test_invalid_password_fails_authentication(): void
    {
        $response = $this->post(route('login.post'), [
            'login' => 'admin@custompc.local',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors('login');
        $this->assertGuest();
    }

    public function test_user_can_logout(): void
    {
        $user = User::where('email', 'customer@custompc.local')->first();
        $this->actingAs($user);

        $response = $this->post(route('logout'));
        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }
}
