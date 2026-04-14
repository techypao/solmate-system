<?php

namespace Tests\Feature;

use App\Models\QuotationSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminQuotationSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_quotation_settings(): void
    {
        $admin = $this->createUserWithRole(User::ROLE_ADMIN);

        Sanctum::actingAs($admin);

        $response = $this->getJson('/api/admin/quotation-settings');

        $response->assertOk()
            ->assertJsonPath('data.rate_per_kwh', '14.00')
            ->assertJsonPath('data.days_in_month', 30)
            ->assertJsonPath('data.default_panel_watts', '610.00');

        $this->assertDatabaseCount('quotation_settings', 1);
    }

    public function test_admin_can_update_selected_quotation_settings_fields(): void
    {
        $admin = $this->createUserWithRole(User::ROLE_ADMIN);

        Sanctum::actingAs($admin);

        $response = $this->patchJson('/api/admin/quotation-settings', [
            'rate_per_kwh' => 15.75,
            'sun_hours' => 5.2,
            'labor_percentage' => 12.5,
            'default_bos_cost' => 25000,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.rate_per_kwh', '15.75')
            ->assertJsonPath('data.sun_hours', '5.20')
            ->assertJsonPath('data.labor_percentage', '12.50')
            ->assertJsonPath('data.default_bos_cost', '25000.00')
            ->assertJsonPath('data.days_in_month', 30);

        $this->assertDatabaseHas('quotation_settings', [
            'rate_per_kwh' => 15.75,
            'sun_hours' => 5.20,
            'labor_percentage' => 12.50,
            'default_bos_cost' => 25000.00,
            'days_in_month' => 30,
        ]);
    }

    public function test_non_admin_cannot_access_quotation_settings_routes(): void
    {
        $customer = $this->createUserWithRole(User::ROLE_CUSTOMER);

        Sanctum::actingAs($customer);

        $this->getJson('/api/admin/quotation-settings')
            ->assertForbidden();

        $this->patchJson('/api/admin/quotation-settings', [
            'rate_per_kwh' => 16,
        ])->assertForbidden();
    }

    public function test_update_validation_rejects_invalid_values(): void
    {
        $admin = $this->createUserWithRole(User::ROLE_ADMIN);

        QuotationSetting::query()->create([
            'rate_per_kwh' => 14.00,
            'days_in_month' => 30,
            'sun_hours' => 4.50,
            'pv_safety_factor' => 1.80,
            'battery_factor' => 1.00,
            'battery_voltage' => 51.20,
            'labor_percentage' => 0.00,
            'default_bos_cost' => 0.00,
            'default_misc_cost' => 0.00,
            'default_panel_watts' => 610.00,
        ]);

        Sanctum::actingAs($admin);

        $response = $this->patchJson('/api/admin/quotation-settings', [
            'days_in_month' => 0,
            'sun_hours' => 0,
            'default_panel_watts' => 0,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'days_in_month',
                'sun_hours',
                'default_panel_watts',
            ]);
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
