<?php

namespace Tests\Feature;

use App\Models\PricingItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PricingItemCatalogReadTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_and_technician_can_read_active_pricing_catalog(): void
    {
        $admin = $this->createUser(User::ROLE_ADMIN, 'admin');
        $technician = $this->createUser(User::ROLE_TECHNICIAN, 'technician');

        PricingItem::query()->create([
            'name' => 'Active Panel',
            'category' => 'panel',
            'unit' => 'pc',
            'default_unit_price' => 10000,
            'is_active' => true,
        ]);

        PricingItem::query()->create([
            'name' => 'Inactive Panel',
            'category' => 'panel',
            'unit' => 'pc',
            'default_unit_price' => 9000,
            'is_active' => false,
        ]);

        Sanctum::actingAs($admin);
        $this->getJson('/api/pricing-items')
            ->assertOk()
            ->assertJsonPath('data.0.name', 'Active Panel')
            ->assertJsonMissing(['name' => 'Inactive Panel']);

        Sanctum::actingAs($technician);
        $this->getJson('/api/pricing-items')
            ->assertOk()
            ->assertJsonPath('data.0.name', 'Active Panel')
            ->assertJsonMissing(['name' => 'Inactive Panel']);
    }

    public function test_customer_cannot_read_pricing_catalog(): void
    {
        $customer = $this->createUser(User::ROLE_CUSTOMER, 'customer');

        Sanctum::actingAs($customer);

        $this->getJson('/api/pricing-items')->assertForbidden();
    }

    private function createUser(string $role, string $prefix): User
    {
        return User::query()->create([
            'name' => ucfirst($role) . ' User',
            'email' => $prefix . '_' . uniqid() . '@example.com',
            'password' => Hash::make('password'),
            'role' => $role,
        ]);
    }
}
