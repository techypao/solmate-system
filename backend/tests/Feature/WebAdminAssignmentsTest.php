<?php

namespace Tests\Feature;

use App\Models\InspectionRequest;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebAdminAssignmentsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_request_assignments_page(): void
    {
        $admin = User::query()->create([
            'name' => 'Admin User',
            'email' => 'admin_assignments@example.com',
            'password' => 'password123',
            'role' => User::ROLE_ADMIN,
        ]);

        $technician = User::query()->create([
            'name' => 'Tech User',
            'email' => 'tech_assignments@example.com',
            'password' => 'password123',
            'role' => User::ROLE_TECHNICIAN,
        ]);

        $customer = User::query()->create([
            'name' => 'Customer User',
            'email' => 'customer_assignments@example.com',
            'password' => 'password123',
            'role' => User::ROLE_CUSTOMER,
        ]);

        ServiceRequest::query()->create([
            'user_id' => $customer->id,
            'request_type' => 'Maintenance',
            'details' => 'Check inverter',
            'status' => 'pending',
        ]);

        InspectionRequest::query()->create([
            'user_id' => $customer->id,
            'details' => 'Inspect rooftop setup',
            'status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->get('/admin/request-assignments')
            ->assertOk()
            ->assertSee('Admin Request Assignments')
            ->assertSee('Service Requests')
            ->assertSee('Inspection Requests')
            ->assertSee($technician->email);
    }

    public function test_admin_can_assign_technician_to_service_request_using_existing_api_route(): void
    {
        $admin = User::query()->create([
            'name' => 'Admin User',
            'email' => 'admin_service_assign@example.com',
            'password' => 'password123',
            'role' => User::ROLE_ADMIN,
        ]);

        $technician = User::query()->create([
            'name' => 'Tech User',
            'email' => 'tech_service_assign@example.com',
            'password' => 'password123',
            'role' => User::ROLE_TECHNICIAN,
        ]);

        $customer = User::query()->create([
            'name' => 'Customer User',
            'email' => 'customer_service_assign@example.com',
            'password' => 'password123',
            'role' => User::ROLE_CUSTOMER,
        ]);

        $serviceRequest = ServiceRequest::query()->create([
            'user_id' => $customer->id,
            'request_type' => 'Repair',
            'details' => 'Replace wiring',
            'status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->putJson("/api/service-requests/{$serviceRequest->id}/assign-technician", [
                'technician_id' => $technician->id,
            ])
            ->assertOk()
            ->assertJsonPath('data.technician_id', $technician->id);

        $this->assertDatabaseHas('service_requests', [
            'id' => $serviceRequest->id,
            'technician_id' => $technician->id,
            'status' => 'assigned',
        ]);
    }

    public function test_admin_can_assign_technician_to_inspection_request_using_existing_api_route(): void
    {
        $admin = User::query()->create([
            'name' => 'Admin User',
            'email' => 'admin_inspection_assign@example.com',
            'password' => 'password123',
            'role' => User::ROLE_ADMIN,
        ]);

        $technician = User::query()->create([
            'name' => 'Tech User',
            'email' => 'tech_inspection_assign@example.com',
            'password' => 'password123',
            'role' => User::ROLE_TECHNICIAN,
        ]);

        $customer = User::query()->create([
            'name' => 'Customer User',
            'email' => 'customer_inspection_assign@example.com',
            'password' => 'password123',
            'role' => User::ROLE_CUSTOMER,
        ]);

        $inspectionRequest = InspectionRequest::query()->create([
            'user_id' => $customer->id,
            'details' => 'Inspect installation area',
            'status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->putJson("/api/inspection-requests/{$inspectionRequest->id}/assign-technician", [
                'technician_id' => $technician->id,
            ])
            ->assertOk()
            ->assertJsonPath('inspection_request.technician_id', $technician->id);

        $this->assertDatabaseHas('inspection_requests', [
            'id' => $inspectionRequest->id,
            'technician_id' => $technician->id,
            'status' => 'assigned',
        ]);
    }

    public function test_non_admin_cannot_open_request_assignments_page(): void
    {
        $customer = User::query()->create([
            'name' => 'Customer User',
            'email' => 'customer_no_assignments@example.com',
            'password' => 'password123',
            'role' => User::ROLE_CUSTOMER,
        ]);

        $this->actingAs($customer)
            ->get('/admin/request-assignments')
            ->assertForbidden();
    }
}
