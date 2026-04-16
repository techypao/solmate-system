<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebAuthPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(ValidateCsrfToken::class);
    }

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

    public function test_admin_can_log_in_without_remember_me_checked(): void
    {
        $admin = User::query()->create([
            'name' => 'Web Admin No Remember',
            'email' => 'admin_no_remember@example.com',
            'password' => 'password123',
            'role' => User::ROLE_ADMIN,
        ]);

        $this->post('/login', [
            'email' => $admin->email,
            'password' => 'password123',
        ])->assertRedirect(route('admin.quotation-settings'));

        $this->assertAuthenticatedAs($admin);
    }

    public function test_admin_can_log_in_with_remember_me_checked(): void
    {
        $admin = User::query()->create([
            'name' => 'Web Admin Remember',
            'email' => 'admin_remember@example.com',
            'password' => 'password123',
            'role' => User::ROLE_ADMIN,
        ]);

        $this->post('/login', [
            'email' => $admin->email,
            'password' => 'password123',
            'remember' => '1',
        ])->assertRedirect(route('admin.quotation-settings'));

        $this->assertAuthenticatedAs($admin);
    }

    public function test_admin_with_legacy_plain_text_password_can_log_in_and_is_upgraded(): void
    {
        DB::table('users')->insert([
            'name' => 'Legacy Admin',
            'email' => 'legacy_admin@example.com',
            'password' => 'password123',
            'role' => User::ROLE_ADMIN,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->post('/login', [
            'email' => 'legacy_admin@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('admin.quotation-settings'));

        $legacyAdmin = User::query()->where('email', 'legacy_admin@example.com')->firstOrFail();

        $this->assertAuthenticatedAs($legacyAdmin);
        $this->assertNotSame('password123', $legacyAdmin->getAuthPassword());
        $this->assertTrue(Hash::check('password123', $legacyAdmin->getAuthPassword()));
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
