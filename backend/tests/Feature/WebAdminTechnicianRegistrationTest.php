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
            ->assertSee('Register Technician')
            ->assertSee('Create technician accounts directly from the admin website');
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
                'name' => 'New Technician',
                'email' => 'new_technician@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]);

        $response->assertRedirect(route('admin.technicians.create'));
        $response->assertSessionHas('status', 'Technician account created successfully.');

        $this->assertDatabaseHas('users', [
            'name' => 'New Technician',
            'email' => 'new_technician@example.com',
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
                'name' => 'Another Technician',
                'email' => 'duplicate_technician@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ])
            ->assertRedirect(route('admin.technicians.create'))
            ->assertSessionHasErrors('email');
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
