<?php

namespace Tests\Feature;

use App\Models\PricingItem;
use App\Models\Quotation;
use App\Models\QuotationLineItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class QuotationLineItemApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_or_technician_can_replace_line_items_on_final_quotation_and_rollups_are_updated(): void
    {
        $technician = $this->createUser(User::ROLE_TECHNICIAN, 'technician');
        $customer = $this->createUser(User::ROLE_CUSTOMER, 'customer');
        $pricingItem = $this->createPricingItem();

        $quotation = Quotation::query()->create([
            'user_id' => $customer->id,
            'quotation_type' => 'final',
            'monthly_electric_bill' => 3000,
            'labor_cost' => 5000,
            'status' => 'pending',
        ]);

        QuotationLineItem::query()->create([
            'quotation_id' => $quotation->id,
            'pricing_item_id' => null,
            'description' => 'Old item to replace',
            'category' => 'misc',
            'qty' => 1,
            'unit' => 'pc',
            'unit_amount' => 1,
            'total_amount' => 1,
        ]);

        Sanctum::actingAs($technician);

        $response = $this->putJson("/api/quotations/{$quotation->id}/line-items", [
            'line_items' => [
                [
                    'pricing_item_id' => $pricingItem->id,
                    'description' => 'Canadian Mono 585W Bifacial',
                    'category' => 'panel',
                    'qty' => 10,
                    'unit' => 'pc',
                    'unit_amount' => 10000,
                    'total_amount' => 1,
                ],
                [
                    'description' => 'MC4',
                    'category' => 'wiring',
                    'qty' => 20,
                    'unit' => 'pair',
                    'unit_amount' => 150,
                ],
            ],
        ]);

        $response->assertOk()
            ->assertJsonPath('data.line_items.0.description', 'Canadian Mono 585W Bifacial')
            ->assertJsonPath('data.line_items.0.total_amount', '100000.00')
            ->assertJsonPath('data.line_items.1.description', 'MC4')
            ->assertJsonPath('data.line_items.1.total_amount', '3000.00')
            ->assertJsonPath('data.panel_cost', 100000)
            ->assertJsonPath('data.bos_cost', 3000)
            ->assertJsonPath('data.materials_subtotal', 103000)
            ->assertJsonPath('data.project_cost', 108000)
            ->assertJsonPath('data.estimated_monthly_savings', 3000)
            ->assertJsonPath('data.estimated_annual_savings', 36000)
            ->assertJsonPath('data.roi_years', 3);

        $this->assertDatabaseCount('quotation_line_items', 2);
        $this->assertDatabaseMissing('quotation_line_items', [
            'quotation_id' => $quotation->id,
            'description' => 'Old item to replace',
        ]);
        $this->assertDatabaseHas('quotations', [
            'id' => $quotation->id,
            'panel_cost' => 100000.00,
            'inverter_cost' => 0.00,
            'battery_cost' => 0.00,
            'bos_cost' => 3000.00,
            'materials_subtotal' => 103000.00,
            'project_cost' => 108000.00,
        ]);
    }

    public function test_attaching_line_items_to_initial_quotation_is_rejected(): void
    {
        $admin = $this->createUser(User::ROLE_ADMIN, 'admin');
        $customer = $this->createUser(User::ROLE_CUSTOMER, 'customer');

        $quotation = Quotation::query()->create([
            'user_id' => $customer->id,
            'quotation_type' => 'initial',
            'monthly_electric_bill' => 2000,
            'status' => 'pending',
        ]);

        Sanctum::actingAs($admin);

        $response = $this->putJson("/api/quotations/{$quotation->id}/line-items", [
            'line_items' => [
                [
                    'description' => 'Should fail',
                    'category' => 'misc',
                    'qty' => 1,
                    'unit' => 'pc',
                    'unit_amount' => 100,
                ],
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('message', 'Line items can only be attached to final quotations.');

        $this->assertDatabaseCount('quotation_line_items', 0);
    }

    public function test_line_item_snapshots_remain_unchanged_after_catalog_item_is_updated(): void
    {
        $admin = $this->createUser(User::ROLE_ADMIN, 'admin');
        $customer = $this->createUser(User::ROLE_CUSTOMER, 'customer');
        $pricingItem = $this->createPricingItem();

        $quotation = Quotation::query()->create([
            'user_id' => $customer->id,
            'quotation_type' => 'final',
            'monthly_electric_bill' => 2500,
            'status' => 'pending',
        ]);

        Sanctum::actingAs($admin);

        $this->putJson("/api/quotations/{$quotation->id}/line-items", [
            'line_items' => [
                [
                    'pricing_item_id' => $pricingItem->id,
                    'description' => 'Canadian Mono 585W Bifacial',
                    'category' => 'panel',
                    'qty' => 8,
                    'unit' => 'pc',
                    'unit_amount' => 10000,
                ],
            ],
        ])->assertOk();

        $pricingItem->update([
            'name' => 'Renamed Catalog Item',
            'default_unit_price' => 12000,
        ]);

        $lineItem = QuotationLineItem::query()->where('quotation_id', $quotation->id)->firstOrFail();

        $this->assertSame('Canadian Mono 585W Bifacial', $lineItem->description);
        $this->assertSame('10000.00', $lineItem->unit_amount);
        $this->assertSame('80000.00', $lineItem->total_amount);
        $this->assertSame($pricingItem->id, $lineItem->pricing_item_id);
    }

    public function test_validation_rejects_invalid_line_item_payload(): void
    {
        $admin = $this->createUser(User::ROLE_ADMIN, 'admin');
        $customer = $this->createUser(User::ROLE_CUSTOMER, 'customer');

        $quotation = Quotation::query()->create([
            'user_id' => $customer->id,
            'quotation_type' => 'final',
            'monthly_electric_bill' => 1800,
            'status' => 'pending',
        ]);

        Sanctum::actingAs($admin);

        $response = $this->putJson("/api/quotations/{$quotation->id}/line-items", [
            'line_items' => [
                [
                    'description' => '',
                    'category' => 'invalid-category',
                    'qty' => 0,
                    'unit' => '',
                    'unit_amount' => -1,
                ],
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'line_items.0.description',
                'line_items.0.category',
                'line_items.0.qty',
                'line_items.0.unit',
                'line_items.0.unit_amount',
            ]);
    }

    public function test_customer_cannot_attach_line_items_to_quotation(): void
    {
        $customer = $this->createUser(User::ROLE_CUSTOMER, 'customer');

        $quotation = Quotation::query()->create([
            'user_id' => $customer->id,
            'quotation_type' => 'final',
            'monthly_electric_bill' => 1800,
            'status' => 'pending',
        ]);

        Sanctum::actingAs($customer);

        $this->putJson("/api/quotations/{$quotation->id}/line-items", [
            'line_items' => [
                [
                    'description' => 'Blocked item',
                    'category' => 'misc',
                    'qty' => 1,
                    'unit' => 'pc',
                    'unit_amount' => 100,
                ],
            ],
        ])->assertForbidden();
    }

    private function createPricingItem(): PricingItem
    {
        return PricingItem::query()->create([
            'name' => 'Canadian Mono 585W Bifacial',
            'category' => 'panel',
            'unit' => 'pc',
            'default_unit_price' => 10000,
            'brand' => 'Canadian',
            'model' => '585W',
            'specification' => 'Bifacial panel',
            'is_active' => true,
        ]);
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
