<?php

namespace Tests\Feature;

use App\Models\NewsArticle;
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
        $this->get('/')
            ->assertOk()
            ->assertSee('Our Services')
            ->assertSee('Inspection')
            ->assertSee('Installation')
            ->assertSee('Maintenance')
            ->assertSee('SolMate by RDY')
            ->assertSee('Loading visual highlights...')
            ->assertSee('Latest News')
            ->assertSee('No news articles available yet.')
            ->assertSee('/api/public/visual-highlights', false)
            ->assertDontSee('/api/public/testimonies', false);

        $this->get('/login')
            ->assertOk()
            ->assertSee('Login');

        $this->get('/register')
            ->assertOk()
            ->assertSee('Register');

        $this->get('/testimonies')
            ->assertOk()
            ->assertSee('Customer Reviews')
            ->assertSee('Loading approved reviews...');

        $this->get('/contact')
            ->assertOk()
            ->assertSee('RDY Solar Installation Inc.')
            ->assertSee('Get Directions')
            ->assertSee('https://share.google/sUZupKfigerTD2owb', false)
            ->assertSee('https://www.google.com/maps/embed?pb=', false);
    }

    public function test_guest_homepage_shows_only_active_news_articles(): void
    {
        NewsArticle::query()->create([
            'article_url' => 'https://example.com/active-news',
            'title' => 'Active News Article',
            'description' => 'Active description',
            'thumbnail_url' => 'https://example.com/active.jpg',
            'source_name' => 'example.com',
            'is_active' => true,
        ]);

        NewsArticle::query()->create([
            'article_url' => 'https://example.com/inactive-news',
            'title' => 'Inactive News Article',
            'description' => 'Inactive description',
            'thumbnail_url' => 'https://example.com/inactive.jpg',
            'source_name' => 'example.com',
            'is_active' => false,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Active News Article')
            ->assertDontSee('Inactive News Article')
            ->assertDontSee('No news articles available yet.');
    }

    public function test_register_creates_customer_user_and_redirects_back_with_success_flash(): void
    {
        $response = $this->post('/register', [
            'first_name' => 'Web',
            'last_name' => 'Customer',
            'email' => 'web_customer@example.com',
            'address' => '123 Solar Street',
            'contact_number' => '09123456789',
            'landline_number' => '',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertRedirect(route('register'));
        $response->assertSessionHas('registration_success', 'Account successfully created! Redirecting to login page...');
        $this->assertGuest();
        $this->assertDatabaseHas('users', [
            'email' => 'web_customer@example.com',
            'role' => User::ROLE_CUSTOMER,
            'first_name' => 'Web',
            'last_name' => 'Customer',
            'contact_number' => '09123456789',
            'landline_number' => null,
        ]);
    }

    public function test_register_requires_a_stronger_password(): void
    {
        $this->from('/register')
            ->post('/register', [
                'first_name' => 'Weak',
                'last_name' => 'Password Customer',
                'email' => 'weak_password_customer@example.com',
                'address' => '123 Solar Street',
                'contact_number' => '09123456789',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ])
            ->assertRedirect('/register')
            ->assertSessionHasErrors([
                'password' => 'Password must contain at least one uppercase letter.',
            ]);
    }

    public function test_customer_can_open_customer_testimonies_page(): void
    {
        $customer = User::query()->create([
            'name' => 'Web Customer Testimonies',
            'email' => 'web_customer_testimonies@example.com',
            'password' => 'password123',
            'role' => User::ROLE_CUSTOMER,
        ]);

        $this->actingAs($customer)
            ->get('/customer/testimonies')
            ->assertOk()
            ->assertSee('My Testimonies')
            ->assertSee('Submitted testimonies');
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

        $this->get('/admin/testimonies')
            ->assertOk()
            ->assertSee('Admin Testimonies')
            ->assertSee('Moderation queue');

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

    public function test_technician_cannot_log_in_to_the_website(): void
    {
        $technician = User::query()->create([
            'name' => 'Web Technician',
            'email' => 'web_technician@example.com',
            'password' => 'password123',
            'role' => User::ROLE_TECHNICIAN,
        ]);

        $this->from('/login')
            ->post('/login', [
                'email' => $technician->email,
                'password' => 'password123',
            ])
            ->assertRedirect('/login')
            ->assertSessionHasErrors([
                'email' => 'Technician accounts can only sign in through the SolMate mobile app.',
            ]);

        $this->assertGuest();
    }

    public function test_archived_customer_cannot_log_in_to_the_website(): void
    {
        $customer = User::query()->create([
            'name' => 'Archived Web Customer',
            'email' => 'archived_web_customer@example.com',
            'password' => 'password123',
            'role' => User::ROLE_CUSTOMER,
            'archived_at' => now(),
        ]);

        $this->from('/login')
            ->post('/login', [
                'email' => $customer->email,
                'password' => 'password123',
            ])
            ->assertRedirect('/login')
            ->assertSessionHasErrors([
                'email' => 'This customer account has been archived. Please contact support for assistance.',
            ]);

        $this->assertGuest();
    }

    public function test_authenticated_user_can_log_out_and_is_redirected_with_success_flash(): void
    {
        $customer = User::query()->create([
            'name' => 'Web Logout Customer',
            'email' => 'web_logout_customer@example.com',
            'password' => 'password123',
            'role' => User::ROLE_CUSTOMER,
        ]);

        $response = $this->actingAs($customer)->post('/logout');

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('logout_success', 'Logged out successfully.');
        $this->assertGuest();
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

        $this->actingAs($customer)
            ->get('/admin/testimonies')
            ->assertForbidden();
    }

    public function test_non_customer_cannot_open_customer_testimonies_page(): void
    {
        $admin = User::query()->create([
            'name' => 'Web Admin Blocked',
            'email' => 'web_admin_blocked@example.com',
            'password' => 'password123',
            'role' => User::ROLE_ADMIN,
        ]);

        $this->actingAs($admin)
            ->get('/customer/testimonies')
            ->assertForbidden();
    }
}
