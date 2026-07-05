<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebAdminAccountManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(ValidateCsrfToken::class);
    }

    public function test_admin_can_open_admin_management_page(): void
    {
        $admin = $this->createAdmin('admin_accounts_page@example.com');

        $this->actingAs($admin)
            ->get(route('admin.admins'))
            ->assertOk()
            ->assertSee('Manage Admins')
            ->assertSee('Add New Admin')
            ->assertSee('Existing Admins');
    }

    public function test_admin_can_create_another_admin_without_email_verification(): void
    {
        $admin = $this->createAdmin('admin_create_admin@example.com');

        $response = $this->actingAs($admin)
            ->post(route('admin.admins.store'), [
                'first_name' => 'New',
                'last_name' => 'Admin',
                'email' => 'new_admin@example.com',
                'contact_number' => '09123456789',
                'password' => 'Password123!',
                'password_confirmation' => 'Password123!',
            ]);

        $response->assertRedirect(route('admin.admins'));
        $response->assertSessionHas('status', 'Admin account created successfully.');

        $createdAdmin = User::query()
            ->where('email', 'new_admin@example.com')
            ->firstOrFail();

        $this->assertSame(User::ROLE_ADMIN, $createdAdmin->role);
        $this->assertSame('New Admin', $createdAdmin->name);
        $this->assertNotNull($createdAdmin->email_verified_at);
    }

    public function test_admin_can_delete_another_admin(): void
    {
        $admin = $this->createAdmin('admin_delete_actor@example.com');
        $otherAdmin = $this->createAdmin('admin_delete_target@example.com', 'Target Admin');

        $this->actingAs($admin)
            ->delete(route('admin.admins.destroy', $otherAdmin))
            ->assertRedirect(route('admin.admins'))
            ->assertSessionHas('status', 'Admin "Target Admin" was deleted successfully.');

        $this->assertDatabaseMissing('users', [
            'id' => $otherAdmin->id,
        ]);
    }

    public function test_admin_cannot_delete_their_own_account(): void
    {
        $admin = $this->createAdmin('admin_delete_self@example.com');

        $this->actingAs($admin)
            ->delete(route('admin.admins.destroy', $admin))
            ->assertRedirect(route('admin.admins'))
            ->assertSessionHasErrors('admin');

        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
        ]);
    }

    public function test_public_api_registration_ignores_requested_admin_role(): void
    {
        $this->postJson('/api/register', [
            'first_name' => 'Public',
            'last_name' => 'Registrant',
            'email' => 'public_requested_admin@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'address' => '123 Solar Street',
            'contact_number' => '09123456789',
            'role' => User::ROLE_ADMIN,
        ])->assertCreated();

        $this->assertDatabaseHas('users', [
            'email' => 'public_requested_admin@example.com',
            'role' => User::ROLE_CUSTOMER,
        ]);
    }

    private function createAdmin(string $email, string $name = 'Admin User'): User
    {
        return User::query()->create([
            'name' => $name,
            'email' => $email,
            'password' => 'password123',
            'role' => User::ROLE_ADMIN,
            'email_verified_at' => now(),
        ]);
    }
}
