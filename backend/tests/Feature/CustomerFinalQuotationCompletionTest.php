<?php

namespace Tests\Feature;

use App\Models\InspectionRequest;
use App\Models\Quotation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CustomerFinalQuotationCompletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_mark_their_own_final_quotation_as_completed(): void
    {
        $customer = $this->createUser(User::ROLE_CUSTOMER, 'customer');
        $technician = $this->createUser(User::ROLE_TECHNICIAN, 'technician');

        $inspectionRequest = InspectionRequest::query()->create([
            'user_id' => $customer->id,
            'technician_id' => $technician->id,
            'details' => 'Completed site inspection',
            'status' => 'completed',
        ]);

        $quotation = Quotation::query()->create([
            'user_id' => $customer->id,
            'inspection_request_id' => $inspectionRequest->id,
            'quotation_type' => 'final',
            'monthly_electric_bill' => 3500,
            'status' => 'pending',
        ]);

        Sanctum::actingAs($customer);

        $response = $this->postJson("/api/quotations/{$quotation->id}/complete");

        $response->assertOk()
            ->assertJsonPath('message', 'Inspection-based quotation marked as completed.')
            ->assertJsonPath('data.id', $quotation->id)
            ->assertJsonPath('data.status', 'completed')
            ->assertJsonPath('data.quotation_type', 'final');

        $this->assertDatabaseHas('quotations', [
            'id' => $quotation->id,
            'status' => 'completed',
        ]);
    }

    public function test_customer_cannot_mark_another_customers_final_quotation_as_completed(): void
    {
        $owner = $this->createUser(User::ROLE_CUSTOMER, 'owner');
        $otherCustomer = $this->createUser(User::ROLE_CUSTOMER, 'other_customer');
        $technician = $this->createUser(User::ROLE_TECHNICIAN, 'technician');

        $inspectionRequest = InspectionRequest::query()->create([
            'user_id' => $owner->id,
            'technician_id' => $technician->id,
            'details' => 'Completed site inspection',
            'status' => 'completed',
        ]);

        $quotation = Quotation::query()->create([
            'user_id' => $owner->id,
            'inspection_request_id' => $inspectionRequest->id,
            'quotation_type' => 'final',
            'monthly_electric_bill' => 2800,
            'status' => 'pending',
        ]);

        Sanctum::actingAs($otherCustomer);

        $response = $this->postJson("/api/quotations/{$quotation->id}/complete");

        $response->assertForbidden()
            ->assertJsonPath('message', 'You are not allowed to complete this quotation.');

        $this->assertDatabaseHas('quotations', [
            'id' => $quotation->id,
            'status' => 'pending',
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
