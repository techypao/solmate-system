<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class InitialQuotationEstimateOutputTest extends TestCase
{
    use RefreshDatabase;

    public function test_initial_quotation_show_returns_estimate_output_without_line_items_or_breakdown(): void
    {
        $customer = $this->createCustomer();

        Sanctum::actingAs($customer);

        $createdResponse = $this->postJson('/api/quotations', [
            'monthly_electric_bill' => 5000,
            'remarks' => 'Need quick estimate',
        ]);

        $createdResponse->assertCreated();

        $quotationId = $createdResponse->json('data.id');

        $showResponse = $this->getJson("/api/quotations/{$quotationId}");

        $showResponse->assertOk()
            ->assertJsonPath('quotation_type', 'initial')
            ->assertJsonPath('pv_system_type', 'hybrid')
            ->assertJsonPath('project_cost', 244000)
            ->assertJsonPath('estimated_monthly_savings', 5000)
            ->assertJsonPath('estimated_annual_savings', 60000)
            ->assertJsonPath('roi_years', 4.07)
            ->assertJsonPath('panel_cost', null)
            ->assertJsonPath('inverter_cost', null)
            ->assertJsonPath('battery_cost', null)
            ->assertJsonPath('bos_cost', null)
            ->assertJsonPath('materials_subtotal', null)
            ->assertJsonPath('labor_cost', null)
            ->assertJsonMissingPath('line_items');
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
