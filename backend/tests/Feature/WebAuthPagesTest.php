<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebAuthPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_view_login_and_register_pages(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('Login');

        $this->get('/register')
            ->assertOk()
            ->assertSee('Register');
    }

    public function test_register_creates_customer_user_and_redirects_to_dashboard(): void
    {
        $response = $this->post('/register', [
            'name' => 'Web Customer',
            'email' => 'web_customer@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'web_customer@example.com',
            'role' => User::ROLE_CUSTOMER,
        ]);
    }

    public function test_admin_can_log_in_and_open_admin_quotation_settings_page(): void
    {
        $admin = User::query()->create([
            'name' => 'Web Admin',
            'email' => 'admin@example.com',
            'password' => 'password123',
            'role' => User::ROLE_ADMIN,
        ]);

        $response = $this->post('/login', [
            'email' => $admin->email,
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('admin.quotation-settings'));

        $this->get('/admin/quotation-settings')
            ->assertOk()
            ->assertSee('Admin Quotation Settings')
            ->assertSee('rate_per_kwh');

        $this->getJson('/api/admin/quotation-settings')
            ->assertOk()
            ->assertJsonPath('data.rate_per_kwh', '14.00');
    }

    public function test_non_admin_cannot_open_admin_quotation_settings_page(): void
    {
        $customer = User::query()->create([
            'name' => 'Web Customer',
            'email' => 'customer@example.com',
            'password' => 'password123',
            'role' => User::ROLE_CUSTOMER,
        ]);

        $this->actingAs($customer)
            ->get('/admin/quotation-settings')
            ->assertForbidden();
    }
}
