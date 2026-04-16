<?php

namespace Tests\Feature;

use App\Models\QuotationSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class FinalQuotationOptionsApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_technician_final_quotation_options_include_current_computation_defaults(): void
    {
        $technician = $this->createUser(User::ROLE_TECHNICIAN, 'technician');

        QuotationSetting::query()->update([
            'rate_per_kwh' => 15.50,
            'days_in_month' => 28,
            'sun_hours' => 5.20,
            'pv_safety_factor' => 1.90,
            'battery_factor' => 1.15,
            'battery_voltage' => 48.00,
            'default_panel_watts' => 650.00,
        ]);

        Sanctum::actingAs($technician);

        $response = $this->getJson('/api/technician/final-quotation-options');

        $response->assertOk()
            ->assertJsonPath('data.computation_defaults.rate_per_kwh', 15.5)
            ->assertJsonPath('data.computation_defaults.days_in_month', 28)
            ->assertJsonPath('data.computation_defaults.sun_hours', 5.2)
            ->assertJsonPath('data.computation_defaults.pv_safety_factor', 1.9)
            ->assertJsonPath('data.computation_defaults.battery_factor', 1.15)
            ->assertJsonPath('data.computation_defaults.battery_voltage', 48)
            ->assertJsonPath('data.computation_defaults.default_panel_watts', 650);
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
