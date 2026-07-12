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
            ->assertSee('Staff Accounts')
            ->assertSee('Add Staff Account')
            ->assertSee('Existing Staff Accounts');
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
                'admin_role' => User::ADMIN_ROLE_CONTENT,
                'password' => 'Password123!',
                'password_confirmation' => 'Password123!',
            ]);

        $response->assertRedirect(route('admin.admins'));
        $response->assertSessionHas('status', 'Staff account created successfully.');

        $createdAdmin = User::query()
            ->where('email', 'new_admin@example.com')
            ->firstOrFail();

        $this->assertSame(User::ROLE_ADMIN, $createdAdmin->role);
        $this->assertSame(User::ADMIN_ROLE_CONTENT, $createdAdmin->admin_role);
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
            ->assertSessionHas('status', 'Staff account "Target Admin" was deleted successfully.');

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

    public function test_non_super_admin_cannot_manage_staff_accounts(): void
    {
        $staff = $this->createAdmin(
            'operations_staff_accounts@example.com',
            'Operations Staff',
            User::ADMIN_ROLE_OPERATIONS,
        );

        $this->actingAs($staff)
            ->get(route('admin.admins'))
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('status', 'You do not have permission to access that admin area.');
    }

    public function test_last_super_admin_cannot_be_deleted(): void
    {
        $superAdmin = $this->createAdmin('only_super_admin@example.com');
        $staff = $this->createAdmin(
            'content_staff_delete_target@example.com',
            'Content Staff',
            User::ADMIN_ROLE_CONTENT,
        );

        $this->actingAs($superAdmin)
            ->delete(route('admin.admins.destroy', $staff))
            ->assertRedirect(route('admin.admins'));

        $this->actingAs($superAdmin)
            ->delete(route('admin.admins.destroy', $superAdmin->fresh()))
            ->assertRedirect(route('admin.admins'))
            ->assertSessionHasErrors('admin');
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

    private function createAdmin(
        string $email,
        string $name = 'Admin User',
        string $adminRole = User::ADMIN_ROLE_SUPER_ADMIN,
    ): User
    {
        return User::query()->create([
            'name' => $name,
            'email' => $email,
            'password' => 'password123',
            'role' => User::ROLE_ADMIN,
            'admin_role' => $adminRole,
            'email_verified_at' => now(),
        ]);
    }
}
