<?php

namespace Tests\Feature;

use App\Models\QuotationSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class InitialQuotationDefaultsTest extends TestCase
{
    use RefreshDatabase;

    public function test_initial_quotation_uses_admin_editable_settings_defaults(): void
    {
        $customer = $this->createCustomer();

        QuotationSetting::query()->update([
            'rate_per_kwh' => 15.00,
            'days_in_month' => 30,
            'sun_hours' => 5.00,
            'pv_safety_factor' => 2.00,
            'battery_factor' => 1.20,
            'battery_voltage' => 50.00,
            'default_panel_watts' => 500.00,
            'initial_price_per_kw' => 55000.00,
        ]);

        Sanctum::actingAs($customer);

        $response = $this->postJson('/api/quotations', [
            'monthly_electric_bill' => 3000,
            'remarks' => 'Test initial quotation',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.quotation_type', 'initial')
            ->assertJsonPath('data.rate_per_kwh', 15)
            ->assertJsonPath('data.days_in_month', 30)
            ->assertJsonPath('data.sun_hours', 5)
            ->assertJsonPath('data.pv_safety_factor', 2)
            ->assertJsonPath('data.battery_factor', 1.2)
            ->assertJsonPath('data.battery_voltage', 50)
            ->assertJsonPath('data.panel_watts', 500)
            ->assertJsonPath('data.monthly_kwh', 200)
            ->assertJsonPath('data.daily_kwh', 6.67)
            ->assertJsonPath('data.pv_kw_raw', 1.33)
            ->assertJsonPath('data.pv_kw_safe', 2.67)
            ->assertJsonPath('data.panel_quantity', 6)
            ->assertJsonPath('data.system_kw', 3)
            ->assertJsonPath('data.battery_required_kwh', 8)
            ->assertJsonPath('data.project_cost', 165000)
            ->assertJsonPath('data.estimated_monthly_savings', 3000)
            ->assertJsonPath('data.estimated_annual_savings', 36000)
            ->assertJsonPath('data.roi_years', 4.58);

        $this->assertEqualsWithDelta(160.08, $response->json('data.battery_required_ah'), 0.1);

        $this->assertDatabaseHas('quotations', [
            'user_id' => $customer->id,
            'quotation_type' => 'initial',
            'pv_system_type' => 'hybrid',
            'rate_per_kwh' => 15.00,
            'days_in_month' => 30,
            'sun_hours' => 5.00,
            'pv_safety_factor' => 2.00,
            'battery_factor' => 1.20,
            'battery_voltage' => 50.00,
            'panel_watts' => 500.00,
            'project_cost' => 165000.00,
            'estimated_monthly_savings' => 3000.00,
            'estimated_annual_savings' => 36000.00,
            'roi_years' => 4.58,
        ]);
    }

    public function test_initial_quotation_falls_back_to_original_defaults_if_settings_row_is_missing(): void
    {
        $customer = $this->createCustomer();

        QuotationSetting::query()->delete();

        Sanctum::actingAs($customer);

        $response = $this->postJson('/api/quotations', [
            'monthly_electric_bill' => 1400,
            'remarks' => 'Fallback defaults test',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.quotation_type', 'initial')
            ->assertJsonPath('data.rate_per_kwh', 14)
            ->assertJsonPath('data.days_in_month', 30)
            ->assertJsonPath('data.sun_hours', 4.5)
            ->assertJsonPath('data.pv_safety_factor', 1.8)
            ->assertJsonPath('data.battery_factor', 1)
            ->assertJsonPath('data.battery_voltage', 51.2)
            ->assertJsonPath('data.panel_watts', 610)
            ->assertJsonPath('data.monthly_kwh', 100)
            ->assertJsonPath('data.daily_kwh', 3.33)
            ->assertJsonPath('data.pv_kw_raw', 0.74)
            ->assertJsonPath('data.pv_kw_safe', 1.33)
            ->assertJsonPath('data.panel_quantity', 3)
            ->assertJsonPath('data.system_kw', 1.83)
            ->assertJsonPath('data.battery_required_kwh', 3.33)
            ->assertJsonPath('data.project_cost', 91500)
            ->assertJsonPath('data.estimated_monthly_savings', 1400)
            ->assertJsonPath('data.estimated_annual_savings', 16800)
            ->assertJsonPath('data.roi_years', 5.45);

        $this->assertEqualsWithDelta(65.04, $response->json('data.battery_required_ah'), 0.1);

        $this->assertDatabaseHas('quotations', [
            'user_id' => $customer->id,
            'quotation_type' => 'initial',
            'pv_system_type' => 'hybrid',
            'rate_per_kwh' => 14.00,
            'days_in_month' => 30,
            'sun_hours' => 4.50,
            'pv_safety_factor' => 1.80,
            'battery_factor' => 1.00,
            'battery_voltage' => 51.20,
            'panel_watts' => 610.00,
            'project_cost' => 91500.00,
            'estimated_monthly_savings' => 1400.00,
            'estimated_annual_savings' => 16800.00,
            'roi_years' => 5.45,
        ]);
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
