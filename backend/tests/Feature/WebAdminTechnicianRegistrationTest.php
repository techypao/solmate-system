<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebAdminTechnicianRegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(ValidateCsrfToken::class);
    }

    public function test_admin_can_open_technician_registration_page(): void
    {
        $admin = User::query()->create([
            'name' => 'Admin User',
            'email' => 'admin_technician_page@example.com',
            'password' => 'password123',
            'role' => User::ROLE_ADMIN,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.technicians.create'))
            ->assertOk()
            ->assertSee('Manage Technicians')
            ->assertSee('Add New Technician')
            ->assertSee('Fill in the fields to create a new technician login account.');
    }

    public function test_admin_can_create_technician_from_admin_page(): void
    {
        $admin = User::query()->create([
            'name' => 'Admin User',
            'email' => 'admin_create_technician@example.com',
            'password' => 'password123',
            'role' => User::ROLE_ADMIN,
        ]);

        $response = $this->actingAs($admin)
            ->post(route('admin.technicians.store'), [
                'first_name' => 'New',
                'last_name' => 'Technician',
                'email' => 'new_technician@example.com',
                'contact_number' => '09123456789',
                'password' => 'Password123!',
                'password_confirmation' => 'Password123!',
            ]);

        $response->assertRedirect(route('admin.technicians.create'));
        $response->assertSessionHas('status', 'Technician account created successfully.');

        $this->assertDatabaseHas('users', [
            'name' => 'New Technician',
            'first_name' => 'New',
            'last_name' => 'Technician',
            'email' => 'new_technician@example.com',
            'contact_number' => '09123456789',
            'role' => User::ROLE_TECHNICIAN,
        ]);
    }

    public function test_admin_cannot_create_technician_with_duplicate_email(): void
    {
        $admin = User::query()->create([
            'name' => 'Admin User',
            'email' => 'admin_duplicate_technician@example.com',
            'password' => 'password123',
            'role' => User::ROLE_ADMIN,
        ]);

        User::query()->create([
            'name' => 'Existing Technician',
            'email' => 'duplicate_technician@example.com',
            'password' => 'password123',
            'role' => User::ROLE_TECHNICIAN,
        ]);

        $this->actingAs($admin)
            ->from(route('admin.technicians.create'))
            ->post(route('admin.technicians.store'), [
                'first_name' => 'Another',
                'last_name' => 'Technician',
                'email' => 'duplicate_technician@example.com',
                'contact_number' => '09123456789',
                'password' => 'Password123!',
                'password_confirmation' => 'Password123!',
            ])
            ->assertRedirect(route('admin.technicians.create'))
            ->assertSessionHasErrors('email');
    }

    public function test_admin_cannot_create_technician_with_weak_password(): void
    {
        $admin = User::query()->create([
            'name' => 'Admin User',
            'email' => 'admin_weak_technician@example.com',
            'password' => 'password123',
            'role' => User::ROLE_ADMIN,
        ]);

        $this->actingAs($admin)
            ->from(route('admin.technicians.create'))
            ->post(route('admin.technicians.store'), [
                'first_name' => 'Weak',
                'last_name' => 'Technician',
                'email' => 'weak_technician@example.com',
                'contact_number' => '09123456789',
                'password' => 'Password123',
                'password_confirmation' => 'Password123',
            ])
            ->assertRedirect(route('admin.technicians.create'))
            ->assertSessionHasErrors([
                'password' => 'Password must contain at least one special character.',
            ]);
    }

    public function test_admin_cannot_create_technician_with_invalid_contact_number(): void
    {
        $admin = User::query()->create([
            'name' => 'Admin User',
            'email' => 'admin_invalid_contact_technician@example.com',
            'password' => 'password123',
            'role' => User::ROLE_ADMIN,
        ]);

        $this->actingAs($admin)
            ->from(route('admin.technicians.create'))
            ->post(route('admin.technicians.store'), [
                'first_name' => 'Invalid',
                'last_name' => 'Contact',
                'email' => 'invalid_contact_technician@example.com',
                'contact_number' => '09123abc789',
                'password' => 'Password123!',
                'password_confirmation' => 'Password123!',
            ])
            ->assertRedirect(route('admin.technicians.create'))
            ->assertSessionHasErrors([
                'contact_number' => 'Contact number must be exactly 11 digits.',
            ]);
    }

    public function test_admin_can_update_technician_profile_fields(): void
    {
        $admin = User::query()->create([
            'name' => 'Admin User',
            'email' => 'admin_update_technician@example.com',
            'password' => 'password123',
            'role' => User::ROLE_ADMIN,
        ]);

        $technician = User::query()->create([
            'name' => 'Tech Old',
            'first_name' => 'Tech',
            'last_name' => 'Old',
            'email' => 'tech_old@example.com',
            'contact_number' => '09123456789',
            'landline_number' => null,
            'password' => 'Password123!',
            'role' => User::ROLE_TECHNICIAN,
        ]);

        $this->actingAs($admin)
            ->put(route('admin.technicians.update', $technician), [
                'first_name' => 'Tech',
                'last_name' => 'Updated',
                'email' => 'tech_updated@example.com',
                'contact_number' => '09987654321',
                'password' => '',
                'password_confirmation' => '',
            ])
            ->assertRedirect(route('admin.technicians.create'))
            ->assertSessionHas('status', 'Technician account updated successfully.');

        $this->assertDatabaseHas('users', [
            'id' => $technician->id,
            'name' => 'Tech Updated',
            'first_name' => 'Tech',
            'last_name' => 'Updated',
            'email' => 'tech_updated@example.com',
            'contact_number' => '09987654321',
        ]);
    }

    public function test_non_admin_cannot_open_technician_registration_page(): void
    {
        $customer = User::query()->create([
            'name' => 'Customer User',
            'email' => 'customer_technician_page@example.com',
            'password' => 'password123',
            'role' => User::ROLE_CUSTOMER,
        ]);

        $this->actingAs($customer)
            ->get(route('admin.technicians.create'))
            ->assertForbidden();
    }
}
