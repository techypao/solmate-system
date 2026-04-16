<?php

namespace Tests\Feature;

use App\Models\InspectionRequest;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RequestWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_service_request_flow_supports_admin_reviewed_completion(): void
    {
        $customer = User::query()->create([
            'name' => 'Customer User',
            'email' => 'customer_service_flow@example.com',
            'password' => 'password123',
            'role' => User::ROLE_CUSTOMER,
        ]);

        $admin = User::query()->create([
            'name' => 'Admin User',
            'email' => 'admin_service_flow@example.com',
            'password' => 'password123',
            'role' => User::ROLE_ADMIN,
        ]);

        $technician = User::query()->create([
            'name' => 'Technician User',
            'email' => 'technician_service_flow@example.com',
            'password' => 'password123',
            'role' => User::ROLE_TECHNICIAN,
        ]);

        $createResponse = $this->actingAs($customer)
            ->postJson('/api/service-requests', [
                'request_type' => 'Panel Cleaning',
                'details' => 'Clean rooftop solar panels',
                'date_needed' => '2026-04-20',
            ])
            ->assertCreated()
            ->assertJsonPath('data.status', 'pending');

        $serviceRequestId = $createResponse->json('data.id');

        $this->actingAs($admin)
            ->putJson("/api/service-requests/{$serviceRequestId}/assign-technician", [
                'technician_id' => $technician->id,
            ])
            ->assertOk()
            ->assertJsonPath('data.status', 'assigned');

        $this->actingAs($technician)
            ->getJson('/api/technician/service-requests')
            ->assertOk()
            ->assertJsonPath('data.0.id', $serviceRequestId);

        $this->actingAs($technician)
            ->putJson("/api/technician/service-requests/{$serviceRequestId}/status", [
                'status' => 'in_progress',
            ])
            ->assertOk()
            ->assertJsonPath('data.status', 'in_progress');

        $completionResponse = $this->actingAs($technician)
            ->postJson("/api/technician/service-requests/{$serviceRequestId}/completion-request")
            ->assertOk()
            ->assertJsonPath('data.status', 'in_progress');

        $this->assertNotNull($completionResponse->json('data.technician_marked_done_at'));

        $this->actingAs($admin)
            ->putJson("/api/admin/service-requests/{$serviceRequestId}/status", [
                'status' => 'completed',
            ])
            ->assertOk()
            ->assertJsonPath('data.status', 'completed');

        $this->actingAs($customer)
            ->getJson('/api/service-requests')
            ->assertOk()
            ->assertJsonPath('0.id', $serviceRequestId)
            ->assertJsonPath('0.status', 'completed')
            ->assertJsonPath('0.technician.id', $technician->id);

        $this->assertDatabaseHas('service_requests', [
            'id' => $serviceRequestId,
            'user_id' => $customer->id,
            'technician_id' => $technician->id,
            'status' => 'completed',
        ]);
    }

    public function test_service_completion_request_is_limited_to_assigned_technician_and_admin_controls_completion(): void
    {
        $customer = User::query()->create([
            'name' => 'Customer User',
            'email' => 'customer_service_restrictions@example.com',
            'password' => 'password123',
            'role' => User::ROLE_CUSTOMER,
        ]);

        $admin = User::query()->create([
            'name' => 'Admin User',
            'email' => 'admin_service_restrictions@example.com',
            'password' => 'password123',
            'role' => User::ROLE_ADMIN,
        ]);

        $assignedTechnician = User::query()->create([
            'name' => 'Assigned Technician',
            'email' => 'assigned_technician@example.com',
            'password' => 'password123',
            'role' => User::ROLE_TECHNICIAN,
        ]);

        $otherTechnician = User::query()->create([
            'name' => 'Other Technician',
            'email' => 'other_technician@example.com',
            'password' => 'password123',
            'role' => User::ROLE_TECHNICIAN,
        ]);

        $serviceRequest = ServiceRequest::query()->create([
            'user_id' => $customer->id,
            'technician_id' => $assignedTechnician->id,
            'request_type' => 'Battery Check',
            'details' => 'Inspect the battery bank',
            'status' => 'assigned',
        ]);

        $this->actingAs($assignedTechnician)
            ->putJson("/api/technician/service-requests/{$serviceRequest->id}/status", [
                'status' => 'completed',
            ])
            ->assertUnprocessable();

        $this->actingAs($assignedTechnician)
            ->postJson("/api/technician/service-requests/{$serviceRequest->id}/completion-request")
            ->assertUnprocessable();

        $this->actingAs($customer)
            ->postJson("/api/technician/service-requests/{$serviceRequest->id}/completion-request")
            ->assertForbidden();

        $this->actingAs($otherTechnician)
            ->postJson("/api/technician/service-requests/{$serviceRequest->id}/completion-request")
            ->assertForbidden();

        $this->actingAs($admin)
            ->putJson("/api/admin/service-requests/{$serviceRequest->id}/status", [
                'status' => 'completed',
            ])
            ->assertOk()
            ->assertJsonPath('data.status', 'completed');
    }

    public function test_inspection_request_flow_still_allows_customer_creation_admin_assignment_and_technician_completion(): void
    {
        $customer = User::query()->create([
            'name' => 'Customer User',
            'email' => 'customer_inspection_flow@example.com',
            'password' => 'password123',
            'role' => User::ROLE_CUSTOMER,
        ]);

        $admin = User::query()->create([
            'name' => 'Admin User',
            'email' => 'admin_inspection_flow@example.com',
            'password' => 'password123',
            'role' => User::ROLE_ADMIN,
        ]);

        $technician = User::query()->create([
            'name' => 'Technician User',
            'email' => 'technician_inspection_flow@example.com',
            'password' => 'password123',
            'role' => User::ROLE_TECHNICIAN,
        ]);

        $createResponse = $this->actingAs($customer)
            ->postJson('/api/inspection-requests', [
                'details' => 'Inspect the roof and inverter placement',
                'date_needed' => '2026-04-21',
            ])
            ->assertCreated()
            ->assertJsonPath('data.status', 'pending');

        $inspectionRequestId = $createResponse->json('data.id');

        $this->actingAs($admin)
            ->putJson("/api/inspection-requests/{$inspectionRequestId}/assign-technician", [
                'technician_id' => $technician->id,
            ])
            ->assertOk()
            ->assertJsonPath('inspection_request.status', 'assigned');

        $this->actingAs($technician)
            ->getJson('/api/technician/inspection-requests')
            ->assertOk()
            ->assertJsonPath('inspection_requests.0.id', $inspectionRequestId);

        $this->actingAs($technician)
            ->putJson("/api/technician/inspection-requests/{$inspectionRequestId}/status", [
                'status' => 'in_progress',
            ])
            ->assertOk()
            ->assertJsonPath('inspection_request.status', 'in_progress');

        $this->actingAs($technician)
            ->putJson("/api/technician/inspection-requests/{$inspectionRequestId}/status", [
                'status' => 'completed',
            ])
            ->assertOk()
            ->assertJsonPath('inspection_request.status', 'completed');

        $this->assertDatabaseHas('inspection_requests', [
            'id' => $inspectionRequestId,
            'user_id' => $customer->id,
            'technician_id' => $technician->id,
            'status' => 'completed',
        ]);
    }
}
