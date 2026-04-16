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
                'contact_number' => '+63 917 123 4567',
                'date_needed' => '2026-04-20',
            ])
            ->assertCreated()
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.contact_number', '+63 917 123 4567');

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
            'contact_number' => '+63 917 123 4567',
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

    public function test_admin_can_reschedule_service_request_and_existing_customer_and_technician_views_receive_updated_date(): void
    {
        $customer = User::query()->create([
            'name' => 'Customer User',
            'email' => 'customer_service_reschedule@example.com',
            'password' => 'password123',
            'role' => User::ROLE_CUSTOMER,
        ]);

        $admin = User::query()->create([
            'name' => 'Admin User',
            'email' => 'admin_service_reschedule@example.com',
            'password' => 'password123',
            'role' => User::ROLE_ADMIN,
        ]);

        $technician = User::query()->create([
            'name' => 'Technician User',
            'email' => 'technician_service_reschedule@example.com',
            'password' => 'password123',
            'role' => User::ROLE_TECHNICIAN,
        ]);

        $createResponse = $this->actingAs($customer)
            ->postJson('/api/service-requests', [
                'request_type' => 'Maintenance',
                'details' => 'Inspect inverter and roof wiring',
                'contact_number' => '0917-999-0200',
                'date_needed' => '2026-04-24',
            ])
            ->assertCreated();

        $this->assertStringStartsWith('2026-04-24', (string) $createResponse->json('data.date_needed'));

        $serviceRequestId = $createResponse->json('data.id');

        $this->actingAs($admin)
            ->putJson("/api/service-requests/{$serviceRequestId}/assign-technician", [
                'technician_id' => $technician->id,
            ])
            ->assertOk();

        $updateResponse = $this->actingAs($admin)
            ->putJson("/api/admin/service-requests/{$serviceRequestId}/preferred-date", [
                'date_needed' => '2026-04-30',
            ]);

        $updateResponse->assertOk();
        $this->assertStringStartsWith('2026-04-30', (string) $updateResponse->json('data.date_needed'));

        $this->actingAs($technician)
            ->getJson('/api/technician/service-requests')
            ->assertOk()
            ->assertJsonPath('data.0.id', $serviceRequestId)
            ->assertJsonPath('data.0.date_needed', '2026-04-30T00:00:00.000000Z');

        $customerResponse = $this->actingAs($customer)
            ->getJson('/api/service-requests')
            ->assertOk()
            ->assertJsonPath('0.id', $serviceRequestId);

        $this->assertStringStartsWith('2026-04-30', (string) $customerResponse->json('0.date_needed'));
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
                'contact_number' => '0917-555-0100',
                'date_needed' => '2026-04-21',
            ])
            ->assertCreated()
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.contact_number', '0917-555-0100');

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
            'contact_number' => '0917-555-0100',
            'status' => 'completed',
        ]);
    }

    public function test_admin_can_reschedule_inspection_request_and_existing_customer_and_technician_views_receive_updated_date(): void
    {
        $customer = User::query()->create([
            'name' => 'Customer User',
            'email' => 'customer_inspection_reschedule@example.com',
            'password' => 'password123',
            'role' => User::ROLE_CUSTOMER,
        ]);

        $admin = User::query()->create([
            'name' => 'Admin User',
            'email' => 'admin_inspection_reschedule@example.com',
            'password' => 'password123',
            'role' => User::ROLE_ADMIN,
        ]);

        $technician = User::query()->create([
            'name' => 'Technician User',
            'email' => 'technician_inspection_reschedule@example.com',
            'password' => 'password123',
            'role' => User::ROLE_TECHNICIAN,
        ]);

        $createResponse = $this->actingAs($customer)
            ->postJson('/api/inspection-requests', [
                'details' => 'Inspect roof access and panel layout',
                'contact_number' => '0917-999-0100',
                'date_needed' => '2026-04-22',
            ])
            ->assertCreated()
            ->assertJsonPath('data.date_needed', '2026-04-22');

        $inspectionRequestId = $createResponse->json('data.id');

        $this->actingAs($admin)
            ->putJson("/api/inspection-requests/{$inspectionRequestId}/assign-technician", [
                'technician_id' => $technician->id,
            ])
            ->assertOk();

        $this->actingAs($admin)
            ->putJson("/api/inspection-requests/{$inspectionRequestId}/preferred-date", [
                'date_needed' => '2026-04-26',
            ])
            ->assertOk()
            ->assertJsonPath('inspection_request.date_needed', '2026-04-26');

        $this->actingAs($technician)
            ->getJson('/api/technician/inspection-requests')
            ->assertOk()
            ->assertJsonPath('inspection_requests.0.id', $inspectionRequestId)
            ->assertJsonPath('inspection_requests.0.date_needed', '2026-04-26');

        $this->actingAs($customer)
            ->getJson('/api/inspection-requests')
            ->assertOk()
            ->assertJsonPath('0.id', $inspectionRequestId)
            ->assertJsonPath('0.date_needed', '2026-04-26');
    }

    public function test_contact_number_is_required_for_request_creation(): void
    {
        $customer = User::query()->create([
            'name' => 'Customer User',
            'email' => 'customer_contact_required@example.com',
            'password' => 'password123',
            'role' => User::ROLE_CUSTOMER,
        ]);

        $this->actingAs($customer)
            ->postJson('/api/service-requests', [
                'request_type' => 'Panel Cleaning',
                'details' => 'Clean rooftop solar panels',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['contact_number']);

        $this->actingAs($customer)
            ->postJson('/api/inspection-requests', [
                'details' => 'Inspect rooftop setup',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['contact_number']);
    }
}
