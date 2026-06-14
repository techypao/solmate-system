<?php

namespace Tests\Feature;

use App\Models\PricingItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PreInspectionQuotationOptionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_initial_quotation_creates_on_grid_and_hybrid_option_snapshots(): void
    {
        $customer = $this->createCustomer();

        $this->createPricingItem('3kW Inverter', 'inverter', 20000);
        $this->createPricingItem('5kW Inverter', 'inverter', 30000);
        $this->createPricingItem('8kW Inverter', 'inverter', 45000);
        $this->createPricingItem('51.2V 100Ah Battery', 'battery', 31500);
        $this->createPricingItem('51.2V 200Ah Battery', 'battery', 75000);
        $this->createPricingItem('51.2V 314Ah Battery', 'battery', 90000);

        Sanctum::actingAs($customer);

        $response = $this->postJson('/api/quotations', [
            'monthly_electric_bill' => 3000,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.quotation_type', 'initial')
            ->assertJsonPath('data.project_cost', 152500)
            ->assertJsonCount(2, 'data.pre_inspection_options')
            ->assertJsonPath('data.pre_inspection_options.0.system_type', 'on-grid')
            ->assertJsonPath('data.pre_inspection_options.0.with_battery', false)
            ->assertJsonPath('data.pre_inspection_options.0.inverter_capacity_kw', '5.00')
            ->assertJsonPath('data.pre_inspection_options.0.battery_cost', '0.00')
            ->assertJsonPath('data.pre_inspection_options.0.project_cost', '182500.00')
            ->assertJsonPath('data.pre_inspection_options.0.roi_years', '5.07')
            ->assertJsonPath('data.pre_inspection_options.1.system_type', 'hybrid')
            ->assertJsonPath('data.pre_inspection_options.1.with_battery', true)
            ->assertJsonPath('data.pre_inspection_options.1.inverter_capacity_kw', '5.00')
            ->assertJsonPath('data.pre_inspection_options.1.battery_capacity_ah', '200.00')
            ->assertJsonPath('data.pre_inspection_options.1.project_cost', '257500.00')
            ->assertJsonPath('data.pre_inspection_options.1.roi_years', '7.15');

        $this->assertDatabaseHas('pre_inspection_quotation_options', [
            'quotation_id' => $response->json('data.id'),
            'system_type' => 'on-grid',
            'with_battery' => false,
            'inverter_capacity_kw' => 5.00,
            'project_cost' => 182500.00,
        ]);

        $this->assertDatabaseHas('pre_inspection_quotation_options', [
            'quotation_id' => $response->json('data.id'),
            'system_type' => 'hybrid',
            'with_battery' => true,
            'inverter_capacity_kw' => 5.00,
            'battery_capacity_ah' => 200.00,
            'project_cost' => 257500.00,
        ]);
    }

    public function test_initial_quotation_flags_options_when_requirement_exceeds_available_components(): void
    {
        $customer = $this->createCustomer();

        $this->createPricingItem('3kW Inverter', 'inverter', 20000);
        $this->createPricingItem('51.2V 100Ah Battery', 'battery', 31500);

        Sanctum::actingAs($customer);

        $response = $this->postJson('/api/quotations', [
            'monthly_electric_bill' => 8000,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.pre_inspection_options.0.inverter_capacity_kw', '6.00')
            ->assertJsonPath('data.pre_inspection_options.0.requires_technician_validation', true)
            ->assertJsonPath('data.pre_inspection_options.1.battery_capacity_ah', '314.00')
            ->assertJsonPath('data.pre_inspection_options.1.requires_technician_validation', true);
    }

    private function createCustomer(): User
    {
        $customer = User::query()->create([
            'name' => 'Customer User',
            'email' => 'customer_' . uniqid() . '@example.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_CUSTOMER,
        ]);

        $customer->forceFill(['email_verified_at' => now()])->save();

        return $customer;
    }

    private function createPricingItem(string $name, string $category, int $price): PricingItem
    {
        return PricingItem::query()->create([
            'name' => $name,
            'category' => $category,
            'unit' => 'pc',
            'default_unit_price' => $price,
            'is_active' => true,
        ]);
    }
}
