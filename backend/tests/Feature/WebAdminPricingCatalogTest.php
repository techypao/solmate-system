<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebAdminPricingCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_pricing_catalog_page(): void
    {
        $admin = User::query()->create([
            'name' => 'Admin Pricing User',
            'email' => 'admin_pricing@example.com',
            'password' => 'password123',
            'role' => User::ROLE_ADMIN,
            'admin_role' => User::ADMIN_ROLE_SUPER_ADMIN,
        ]);
        $admin->forceFill(['email_verified_at' => now()])->save();

        $this->actingAs($admin)
            ->get('/admin/pricing-catalog')
            ->assertOk()
            ->assertSee('Admin Item Catalog')
            ->assertSee('Admin Item Management')
            ->assertSee('Add Item')
            ->assertSee('Item History')
            ->assertSee('History')
            ->assertSee('Search')
            ->assertSee('panel')
            ->assertSee('All categories')
            ->assertSee('All statuses');
    }

    public function test_non_admin_cannot_open_pricing_catalog_page(): void
    {
        $customer = User::query()->create([
            'name' => 'Customer User',
            'email' => 'customer_pricing_page@example.com',
            'password' => 'password123',
            'role' => User::ROLE_CUSTOMER,
        ]);
        $customer->forceFill(['email_verified_at' => now()])->save();

        $this->actingAs($customer)
            ->get('/admin/pricing-catalog')
            ->assertRedirect(route('dashboard'));
    }

    public function test_operations_staff_can_open_pricing_catalog_page(): void
    {
        $staff = User::query()->create([
            'name' => 'Operations Staff',
            'email' => 'operations_pricing@example.com',
            'password' => 'password123',
            'role' => User::ROLE_ADMIN,
            'admin_role' => User::ADMIN_ROLE_OPERATIONS,
        ]);
        $staff->forceFill(['email_verified_at' => now()])->save();

        $this->actingAs($staff)
            ->get('/admin/pricing-catalog')
            ->assertOk()
            ->assertSee('Admin Item Catalog');
    }

    public function test_content_staff_cannot_open_pricing_catalog_page(): void
    {
        $staff = User::query()->create([
            'name' => 'Content Staff',
            'email' => 'content_pricing@example.com',
            'password' => 'password123',
            'role' => User::ROLE_ADMIN,
            'admin_role' => User::ADMIN_ROLE_CONTENT,
        ]);
        $staff->forceFill(['email_verified_at' => now()])->save();

        $this->actingAs($staff)
            ->get('/admin/pricing-catalog')
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('status', 'You do not have permission to access that admin area.');
    }
}
