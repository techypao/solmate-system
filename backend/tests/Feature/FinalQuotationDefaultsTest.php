<?php

namespace Tests\Feature;

use App\Models\InspectionRequest;
use App\Models\QuotationSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class FinalQuotationDefaultsTest extends TestCase
{
    use RefreshDatabase;

    public function test_final_quotation_uses_settings_defaults_when_technician_omits_optional_inputs(): void
    {
        $customer = $this->createUser(User::ROLE_CUSTOMER, 'customer');
        $technician = $this->createUser(User::ROLE_TECHNICIAN, 'technician');
        $inspectionRequest = $this->createCompletedInspectionRequest($customer->id, $technician->id);

        QuotationSetting::query()->update([
            'rate_per_kwh' => 15.00,
            'days_in_month' => 30,
            'sun_hours' => 5.00,
            'pv_safety_factor' => 2.00,
            'battery_factor' => 1.20,
            'battery_voltage' => 50.00,
            'default_panel_watts' => 500.00,
        ]);

        Sanctum::actingAs($technician);

        $response = $this->postJson('/api/technician/final-quotations', [
            'inspection_request_id' => $inspectionRequest->id,
            'monthly_electric_bill' => 3000,
            'pv_system_type' => 'hybrid',
            'with_battery' => true,
            'remarks' => 'Defaults from settings',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.quotation_type', 'final')
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
            ->assertJsonPath('data.battery_required_kwh', 8);

        $this->assertEqualsWithDelta(160.08, $response->json('data.battery_required_ah'), 0.1);

        $this->assertDatabaseHas('quotations', [
            'inspection_request_id' => $inspectionRequest->id,
            'quotation_type' => 'final',
            'rate_per_kwh' => 15.00,
            'days_in_month' => 30,
            'sun_hours' => 5.00,
            'pv_safety_factor' => 2.00,
            'battery_factor' => 1.20,
            'battery_voltage' => 50.00,
            'panel_watts' => 500.00,
        ]);
    }

    public function test_final_quotation_preserves_technician_supplied_values_over_settings_defaults(): void
    {
        $customer = $this->createUser(User::ROLE_CUSTOMER, 'customer');
        $technician = $this->createUser(User::ROLE_TECHNICIAN, 'technician');
        $inspectionRequest = $this->createCompletedInspectionRequest($customer->id, $technician->id);

        QuotationSetting::query()->update([
            'rate_per_kwh' => 99.00,
            'days_in_month' => 31,
            'sun_hours' => 9.00,
            'pv_safety_factor' => 3.50,
            'battery_factor' => 2.50,
            'battery_voltage' => 60.00,
            'default_panel_watts' => 700.00,
        ]);

        Sanctum::actingAs($technician);

        $response = $this->postJson('/api/technician/final-quotations', [
            'inspection_request_id' => $inspectionRequest->id,
            'monthly_electric_bill' => 2400,
            'rate_per_kwh' => 20,
            'days_in_month' => 25,
            'sun_hours' => 4,
            'pv_safety_factor' => 1.5,
            'battery_factor' => 1.1,
            'battery_voltage' => 48,
            'panel_watts' => 600,
            'pv_system_type' => 'hybrid',
            'with_battery' => true,
            'remarks' => 'Technician overrides',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.quotation_type', 'final')
            ->assertJsonPath('data.rate_per_kwh', 20)
            ->assertJsonPath('data.days_in_month', 25)
            ->assertJsonPath('data.sun_hours', 4)
            ->assertJsonPath('data.pv_safety_factor', 1.5)
            ->assertJsonPath('data.battery_factor', 1.1)
            ->assertJsonPath('data.battery_voltage', 48)
            ->assertJsonPath('data.panel_watts', 600)
            ->assertJsonPath('data.monthly_kwh', 120)
            ->assertJsonPath('data.daily_kwh', 4.8)
            ->assertJsonPath('data.pv_kw_raw', 1.2)
            ->assertJsonPath('data.pv_kw_safe', 1.8)
            ->assertJsonPath('data.panel_quantity', 3)
            ->assertJsonPath('data.system_kw', 1.8)
            ->assertJsonPath('data.battery_required_kwh', 5.28);

        $this->assertEqualsWithDelta(110, $response->json('data.battery_required_ah'), 0.1);

        $this->assertDatabaseHas('quotations', [
            'inspection_request_id' => $inspectionRequest->id,
            'quotation_type' => 'final',
            'rate_per_kwh' => 20.00,
            'days_in_month' => 25,
            'sun_hours' => 4.00,
            'pv_safety_factor' => 1.50,
            'battery_factor' => 1.10,
            'battery_voltage' => 48.00,
            'panel_watts' => 600.00,
        ]);
    }

    public function test_final_quotation_falls_back_to_original_defaults_if_settings_row_is_missing(): void
    {
        $customer = $this->createUser(User::ROLE_CUSTOMER, 'customer');
        $technician = $this->createUser(User::ROLE_TECHNICIAN, 'technician');
        $inspectionRequest = $this->createCompletedInspectionRequest($customer->id, $technician->id);

        QuotationSetting::query()->delete();

        Sanctum::actingAs($technician);

        $response = $this->postJson('/api/technician/final-quotations', [
            'inspection_request_id' => $inspectionRequest->id,
            'monthly_electric_bill' => 1400,
            'pv_system_type' => 'hybrid',
            'with_battery' => true,
            'remarks' => 'Fallback defaults',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.quotation_type', 'final')
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
            ->assertJsonPath('data.battery_required_kwh', 3.33);

        $this->assertEqualsWithDelta(65.04, $response->json('data.battery_required_ah'), 0.1);
    }

    private function createCompletedInspectionRequest(int $customerId, int $technicianId): InspectionRequest
    {
        return InspectionRequest::query()->create([
            'user_id' => $customerId,
            'technician_id' => $technicianId,
            'details' => 'Completed site visit',
            'status' => 'completed',
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
