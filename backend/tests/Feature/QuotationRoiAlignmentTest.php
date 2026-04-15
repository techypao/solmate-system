<?php

namespace Tests\Feature;

use App\Models\InspectionRequest;
use App\Models\Quotation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class QuotationRoiAlignmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_final_quotation_creation_uses_client_approved_roi_method(): void
    {
        $customer = $this->createUser(User::ROLE_CUSTOMER, 'customer');
        $technician = $this->createUser(User::ROLE_TECHNICIAN, 'technician');
        $inspectionRequest = InspectionRequest::query()->create([
            'user_id' => $customer->id,
            'technician_id' => $technician->id,
            'details' => 'Completed site visit',
            'status' => 'completed',
        ]);

        Sanctum::actingAs($technician);

        $response = $this->postJson('/api/technician/final-quotations', [
            'inspection_request_id' => $inspectionRequest->id,
            'monthly_electric_bill' => 6000,
            'project_cost' => 360000,
            'pv_system_type' => 'hybrid',
            'with_battery' => true,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.estimated_monthly_savings', 6000)
            ->assertJsonPath('data.estimated_annual_savings', 72000)
            ->assertJsonPath('data.roi_years', 5);
    }

    public function test_initial_quotation_update_uses_client_approved_roi_method(): void
    {
        $admin = $this->createUser(User::ROLE_ADMIN, 'admin');
        $customer = $this->createUser(User::ROLE_CUSTOMER, 'customer');

        $quotation = Quotation::query()->create([
            'user_id' => $customer->id,
            'quotation_type' => 'initial',
            'monthly_electric_bill' => 5000,
            'pv_system_type' => 'hybrid',
            'with_battery' => true,
            'status' => 'pending',
        ]);

        Sanctum::actingAs($admin);

        $response = $this->putJson("/api/quotations/{$quotation->id}", [
            'project_cost' => 300000,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.quotation_type', 'initial')
            ->assertJsonPath('data.estimated_monthly_savings', 5000)
            ->assertJsonPath('data.estimated_annual_savings', 60000)
            ->assertJsonPath('data.roi_years', 5);

        $this->assertDatabaseHas('quotations', [
            'id' => $quotation->id,
            'quotation_type' => 'initial',
            'project_cost' => 300000.00,
            'estimated_monthly_savings' => 5000.00,
            'estimated_annual_savings' => 60000.00,
            'roi_years' => 5.00,
        ]);
    }

    public function test_initial_quotation_creation_uses_package_estimate_to_populate_roi_fields(): void
    {
        $customer = $this->createUser(User::ROLE_CUSTOMER, 'customer');

        Sanctum::actingAs($customer);

        $response = $this->postJson('/api/quotations', [
            'monthly_electric_bill' => 4200,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.project_cost', 213500)
            ->assertJsonPath('data.estimated_monthly_savings', 4200)
            ->assertJsonPath('data.estimated_annual_savings', 50400)
            ->assertJsonPath('data.roi_years', 4.24);
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
