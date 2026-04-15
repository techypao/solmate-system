<?php

namespace Tests\Feature;

use App\Models\PricingItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminPricingItemApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_list_pricing_items(): void
    {
        $admin = $this->createUserWithRole(User::ROLE_ADMIN);

        PricingItem::query()->create([
            'name' => 'SRNE 6kW Hybrid',
            'category' => 'inverter',
            'unit' => 'set',
            'default_unit_price' => 85000,
            'brand' => 'SRNE',
            'model' => '6kW Hybrid',
            'specification' => 'Hybrid inverter',
            'is_active' => true,
        ]);

        Sanctum::actingAs($admin);

        $response = $this->getJson('/api/admin/pricing-items');

        $response->assertOk()
            ->assertJsonPath('data.0.name', 'SRNE 6kW Hybrid')
            ->assertJsonPath('data.0.category', 'inverter')
            ->assertJsonPath('data.0.default_unit_price', '85000.00')
            ->assertJsonPath('data.0.is_active', true);
    }

    public function test_admin_can_create_pricing_item(): void
    {
        $admin = $this->createUserWithRole(User::ROLE_ADMIN);

        Sanctum::actingAs($admin);

        $response = $this->postJson('/api/admin/pricing-items', [
            'name' => 'Canadian Mono 585W Bifacial',
            'category' => 'panel',
            'unit' => 'pc',
            'default_unit_price' => 10000,
            'brand' => 'Canadian',
            'model' => '585W',
            'specification' => 'Bifacial panel',
            'is_active' => true,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.name', 'Canadian Mono 585W Bifacial')
            ->assertJsonPath('data.category', 'panel')
            ->assertJsonPath('data.unit', 'pc')
            ->assertJsonPath('data.default_unit_price', '10000.00')
            ->assertJsonPath('data.is_active', true);

        $this->assertDatabaseHas('pricing_items', [
            'name' => 'Canadian Mono 585W Bifacial',
            'category' => 'panel',
            'unit' => 'pc',
            'default_unit_price' => 10000.00,
            'brand' => 'Canadian',
            'model' => '585W',
            'is_active' => true,
        ]);
    }

    public function test_admin_can_update_pricing_item_and_toggle_activation(): void
    {
        $admin = $this->createUserWithRole(User::ROLE_ADMIN);

        $pricingItem = PricingItem::query()->create([
            'name' => 'Grounding Rod',
            'category' => 'grounding',
            'unit' => 'pc',
            'default_unit_price' => 500,
            'brand' => null,
            'model' => null,
            'specification' => null,
            'is_active' => true,
        ]);

        Sanctum::actingAs($admin);

        $response = $this->patchJson("/api/admin/pricing-items/{$pricingItem->id}", [
            'default_unit_price' => 650,
            'specification' => 'Copper grounding rod',
            'is_active' => false,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.name', 'Grounding Rod')
            ->assertJsonPath('data.category', 'grounding')
            ->assertJsonPath('data.default_unit_price', '650.00')
            ->assertJsonPath('data.specification', 'Copper grounding rod')
            ->assertJsonPath('data.is_active', false);

        $this->assertDatabaseHas('pricing_items', [
            'id' => $pricingItem->id,
            'default_unit_price' => 650.00,
            'specification' => 'Copper grounding rod',
            'is_active' => false,
        ]);
    }

    public function test_validation_rejects_invalid_pricing_item_payload(): void
    {
        $admin = $this->createUserWithRole(User::ROLE_ADMIN);

        Sanctum::actingAs($admin);

        $response = $this->postJson('/api/admin/pricing-items', [
            'name' => '',
            'category' => 'invalid-category',
            'unit' => '',
            'default_unit_price' => -1,
            'is_active' => 'not-a-boolean',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'name',
                'category',
                'unit',
                'default_unit_price',
                'is_active',
            ]);
    }

    public function test_non_admin_users_cannot_access_pricing_item_admin_routes(): void
    {
        $customer = $this->createUserWithRole(User::ROLE_CUSTOMER);
        $technician = $this->createUserWithRole(User::ROLE_TECHNICIAN);
        $pricingItem = PricingItem::query()->create([
            'name' => 'Protected Item',
            'category' => 'misc',
            'unit' => 'pc',
            'default_unit_price' => 100,
            'is_active' => true,
        ]);

        Sanctum::actingAs($customer);

        $this->getJson('/api/admin/pricing-items')->assertForbidden();
        $this->postJson('/api/admin/pricing-items', [
            'name' => 'Blocked Item',
            'category' => 'misc',
            'unit' => 'pc',
            'default_unit_price' => 1,
        ])->assertForbidden();

        Sanctum::actingAs($technician);

        $this->patchJson("/api/admin/pricing-items/{$pricingItem->id}", [
            'is_active' => false,
        ])->assertForbidden();
    }

    private function createUserWithRole(string $role): User
    {
        return User::query()->create([
            'name' => ucfirst($role) . ' User',
            'email' => $role . '_' . uniqid() . '@example.com',
            'password' => Hash::make('password'),
            'role' => $role,
        ]);
    }
}
