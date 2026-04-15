<?php

namespace Tests\Feature;

use App\Models\PricingItem;
use App\Models\Quotation;
use App\Models\QuotationLineItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class QuotationLineItemFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_quotation_can_exist_without_any_line_items(): void
    {
        $customer = $this->createCustomer();

        $quotation = Quotation::query()->create([
            'user_id' => $customer->id,
            'quotation_type' => 'final',
            'monthly_electric_bill' => 3000,
            'rate_per_kwh' => 14,
            'days_in_month' => 30,
            'sun_hours' => 4.5,
            'pv_safety_factor' => 1.8,
            'battery_factor' => 1.0,
            'battery_voltage' => 51.2,
            'pv_system_type' => 'hybrid',
            'with_battery' => true,
            'panel_watts' => 610,
            'status' => 'pending',
        ]);

        $this->assertCount(0, $quotation->lineItems);
    }

    public function test_quotation_line_item_keeps_snapshot_values_even_if_catalog_changes(): void
    {
        $customer = $this->createCustomer();

        $quotation = Quotation::query()->create([
            'user_id' => $customer->id,
            'quotation_type' => 'final',
            'monthly_electric_bill' => 3000,
            'rate_per_kwh' => 14,
            'days_in_month' => 30,
            'sun_hours' => 4.5,
            'pv_safety_factor' => 1.8,
            'battery_factor' => 1.0,
            'battery_voltage' => 51.2,
            'pv_system_type' => 'hybrid',
            'with_battery' => true,
            'panel_watts' => 610,
            'status' => 'pending',
        ]);

        $pricingItem = PricingItem::query()->create([
            'name' => 'Canadian Mono 585W Bifacial',
            'category' => 'panel',
            'unit' => 'pc',
            'default_unit_price' => 10000,
            'brand' => 'Canadian',
            'model' => '585W',
            'specification' => 'Bifacial panel',
            'is_active' => true,
        ]);

        $lineItem = QuotationLineItem::query()->create([
            'quotation_id' => $quotation->id,
            'pricing_item_id' => $pricingItem->id,
            'description' => 'Canadian Mono 585W Bifacial',
            'category' => 'panel',
            'qty' => 10,
            'unit' => 'pc',
            'unit_amount' => 10000,
            'total_amount' => 100000,
        ]);

        $pricingItem->update([
            'name' => 'Updated Catalog Panel Name',
            'default_unit_price' => 12000,
        ]);

        $freshLineItem = $lineItem->fresh();

        $this->assertSame('Canadian Mono 585W Bifacial', $freshLineItem->description);
        $this->assertSame('10000.00', $freshLineItem->unit_amount);
        $this->assertSame('100000.00', $freshLineItem->total_amount);
        $this->assertSame($pricingItem->id, $freshLineItem->pricing_item_id);
    }

    private function createCustomer(): User
    {
        return User::query()->create([
            'name' => 'Customer User',
            'email' => 'customer_' . uniqid() . '@example.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_CUSTOMER,
        ]);
    }
}
