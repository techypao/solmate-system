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
        ]);

        $this->actingAs($admin)
            ->get('/admin/pricing-catalog')
            ->assertOk()
            ->assertSee('Admin Pricing Catalog')
            ->assertSee('Create or Edit Item')
            ->assertSee('panel')
            ->assertSee('Other Materials / BOS')
            ->assertSee('Pricing Items');
    }

    public function test_non_admin_cannot_open_pricing_catalog_page(): void
    {
        $customer = User::query()->create([
            'name' => 'Customer User',
            'email' => 'customer_pricing_page@example.com',
            'password' => 'password123',
            'role' => User::ROLE_CUSTOMER,
        ]);

        $this->actingAs($customer)
            ->get('/admin/pricing-catalog')
            ->assertForbidden();
    }
}
