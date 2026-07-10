<?php

namespace Tests\Feature;

use App\Models\CompletionReport;
use App\Models\InspectionRequest;
use App\Models\Quotation;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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
            ->postJson("/api/technician/service-requests/{$serviceRequestId}/completion-report", [
                'report_text' => 'Cleaned the system, inspected the mounting, and confirmed normal output.',
                'findings' => 'No damaged panels found.',
                'recommendations' => 'Schedule another preventive maintenance visit in six months.',
                'completed_at' => '2026-04-20 15:30:00',
            ])
            ->assertCreated()
            ->assertJsonPath('data.status', 'in_progress');

        $this->assertNotNull($completionResponse->json('data.technician_marked_done_at'));
        $this->assertSame('pending', $completionResponse->json('data.completion_report.status'));

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
        $this->assertDatabaseHas('completion_reports', [
            'service_request_id' => $serviceRequestId,
            'status' => CompletionReport::STATUS_APPROVED,
        ]);
    }

    public function test_admin_can_create_manual_inspection_request_for_non_registered_customer(): void
    {
        $admin = User::query()->create([
            'name' => 'Admin User',
            'email' => 'admin_manual_inspection@example.com',
            'password' => 'password123',
            'role' => User::ROLE_ADMIN,
        ]);

        $technician = User::query()->create([
            'name' => 'Technician User',
            'email' => 'technician_manual_inspection@example.com',
            'password' => 'password123',
            'role' => User::ROLE_TECHNICIAN,
        ]);
        $technician->forceFill(['email_verified_at' => now()])->save();

        $createResponse = $this->actingAs($admin)
            ->postJson('/api/admin/manual-inspection-requests', [
                'customer_name' => 'Prospect Caller',
                'customer_email' => 'prospect@example.com',
                'contact_number' => '0917-555-0101',
                'address_details' => '45 Sample Street, Calamba City',
                'date_needed' => '2026-04-20',
                'details' => 'Caller wants a roof inspection before deciding on solar.',
            ])
            ->assertCreated()
            ->assertJsonPath('data.user_id', null)
            ->assertJsonPath('data.customer_name', 'Prospect Caller')
            ->assertJsonPath('data.customer_email', 'prospect@example.com')
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
            ->assertJsonPath('inspection_requests.0.id', $inspectionRequestId)
            ->assertJsonPath('inspection_requests.0.user_id', null)
            ->assertJsonPath('inspection_requests.0.customer_name', 'Prospect Caller');

        $this->actingAs($technician)
            ->putJson("/api/technician/inspection-requests/{$inspectionRequestId}/status", [
                'status' => 'in_progress',
            ])
            ->assertOk()
            ->assertJsonPath('inspection_request.status', 'in_progress');

        $this->actingAs($technician)
            ->postJson('/api/technician/final-quotations', [
                'inspection_request_id' => $inspectionRequestId,
                'monthly_electric_bill' => 4200,
                'pv_system_type' => 'hybrid',
                'with_battery' => true,
                'remarks' => 'Inspection-based quotation for walk-in prospect.',
            ])
            ->assertCreated()
            ->assertJsonPath('data.user_id', null)
            ->assertJsonPath('data.inspection_request_id', $inspectionRequestId)
            ->assertJsonPath('data.quotation_type', 'final');

        $this->assertDatabaseHas('inspection_requests', [
            'id' => $inspectionRequestId,
            'user_id' => null,
            'customer_name' => 'Prospect Caller',
            'customer_email' => 'prospect@example.com',
        ]);

        $this->assertDatabaseHas('quotations', [
            'inspection_request_id' => $inspectionRequestId,
            'user_id' => null,
            'quotation_type' => 'final',
        ]);
    }

    public function test_admin_can_delete_manual_inspection_requests_only(): void
    {
        $admin = User::query()->create([
            'name' => 'Admin User',
            'email' => 'admin_delete_manual_inspection@example.com',
            'password' => 'password123',
            'role' => User::ROLE_ADMIN,
        ]);

        $customer = User::query()->create([
            'name' => 'Registered Customer',
            'email' => 'registered_delete_guard@example.com',
            'password' => 'password123',
            'role' => User::ROLE_CUSTOMER,
        ]);

        $manualInspection = InspectionRequest::query()->create([
            'user_id' => null,
            'customer_name' => 'Walkin Delete',
            'customer_email' => 'walkin-delete@example.com',
            'details' => 'Manual request to delete.',
            'contact_number' => '0917-000-0000',
            'address_details' => 'Temporary address',
            'date_needed' => '2026-04-25',
            'status' => 'pending',
        ]);

        $registeredInspection = InspectionRequest::query()->create([
            'user_id' => $customer->id,
            'details' => 'Registered customer inspection.',
            'contact_number' => '0917-111-1111',
            'address' => 'Registered address',
            'date_needed' => '2026-04-26',
            'status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->deleteJson("/api/admin/manual-inspection-requests/{$registeredInspection->id}")
            ->assertStatus(422)
            ->assertJsonPath('message', 'Only manual inspection requests can be deleted from this action.');

        $this->assertDatabaseHas('inspection_requests', [
            'id' => $registeredInspection->id,
        ]);

        $this->actingAs($admin)
            ->deleteJson("/api/admin/manual-inspection-requests/{$manualInspection->id}")
            ->assertOk()
            ->assertJsonPath('message', 'Manual inspection request deleted successfully.');

        $this->assertDatabaseMissing('inspection_requests', [
            'id' => $manualInspection->id,
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
            ->postJson("/api/technician/service-requests/{$serviceRequest->id}/completion-report", [
                'report_text' => 'Attempted to submit before work started.',
                'completed_at' => '2026-04-20 11:00:00',
            ])
            ->assertUnprocessable();

        $this->actingAs($customer)
            ->postJson("/api/technician/service-requests/{$serviceRequest->id}/completion-report", [
                'report_text' => 'Customer should not be able to submit this.',
                'completed_at' => '2026-04-20 11:00:00',
            ])
            ->assertForbidden();

        $this->actingAs($otherTechnician)
            ->postJson("/api/technician/service-requests/{$serviceRequest->id}/completion-report", [
                'report_text' => 'Other technician should not be able to submit this.',
                'completed_at' => '2026-04-20 11:00:00',
            ])
            ->assertForbidden();

        $this->actingAs($admin)
            ->putJson("/api/admin/service-requests/{$serviceRequest->id}/status", [
                'status' => 'completed',
            ])
            ->assertUnprocessable();
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
                'address' => '222 Summit Street, Teresa, Rizal',
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

    public function test_installation_service_request_can_store_optional_map_coordinates(): void
    {
        $customer = User::query()->create([
            'name' => 'Installation Coordinate Customer',
            'email' => 'service_coordinates@example.com',
            'password' => 'password123',
            'role' => User::ROLE_CUSTOMER,
            'address' => '12 Helios Street, Antipolo City',
        ]);

        $response = $this->actingAs($customer)
            ->postJson('/api/service-requests', [
                'request_type' => 'installation',
                'details' => 'Coordinate rooftop installation access',
                'contact_number' => '0917-123-4567',
                'address' => '12 Helios Street, Antipolo City',
                'address_details' => 'Gate 2, beside the covered basketball court',
                'latitude' => 14.5878500,
                'longitude' => 121.1764500,
                'date_needed' => '2026-04-25',
            ])
            ->assertCreated()
            ->assertJsonPath('data.address', '12 Helios Street, Antipolo City')
            ->assertJsonPath('data.address_details', 'Gate 2, beside the covered basketball court');

        $this->assertDatabaseHas('service_requests', [
            'id' => $response->json('data.id'),
            'address_details' => 'Gate 2, beside the covered basketball court',
            'latitude' => '14.5878500',
            'longitude' => '121.1764500',
        ]);
    }

    public function test_installation_service_request_coordinates_remain_optional_with_manual_address(): void
    {
        $customer = User::query()->create([
            'name' => 'Installation Manual Address Customer',
            'email' => 'service_coordinates_optional@example.com',
            'password' => 'password123',
            'role' => User::ROLE_CUSTOMER,
            'address' => '45 Suncrest Avenue, Cainta, Rizal',
        ]);

        $response = $this->actingAs($customer)
            ->postJson('/api/service-requests', [
                'request_type' => 'installation',
                'details' => 'Schedule installation coordination',
                'contact_number' => '0917-765-4321',
                'address' => '45 Suncrest Avenue, Cainta, Rizal',
                'date_needed' => '2026-04-26',
            ])
            ->assertCreated();

        $this->assertDatabaseHas('service_requests', [
            'id' => $response->json('data.id'),
            'latitude' => null,
            'longitude' => null,
        ]);
    }

    public function test_maintenance_service_request_can_store_optional_map_coordinates(): void
    {
        $customer = User::query()->create([
            'name' => 'Maintenance Coordinate Customer',
            'email' => 'maintenance_coordinates@example.com',
            'password' => 'password123',
            'role' => User::ROLE_CUSTOMER,
            'address' => '77 Aurora Lane, Taytay, Rizal',
        ]);

        $response = $this->actingAs($customer)
            ->postJson('/api/service-requests', [
                'request_type' => 'maintenance',
                'details' => 'Need inverter and panel maintenance review',
                'contact_number' => '0917-888-1010',
                'address' => '77 Aurora Lane, Taytay, Rizal',
                'latitude' => 14.5692000,
                'longitude' => 121.1324000,
                'date_needed' => '2026-04-27',
            ])
            ->assertCreated()
            ->assertJsonPath('data.address', '77 Aurora Lane, Taytay, Rizal');

        $this->assertDatabaseHas('service_requests', [
            'id' => $response->json('data.id'),
            'latitude' => '14.5692000',
            'longitude' => '121.1324000',
        ]);
    }

    public function test_maintenance_service_request_coordinates_remain_optional_with_manual_address(): void
    {
        $customer = User::query()->create([
            'name' => 'Maintenance Manual Address Customer',
            'email' => 'maintenance_coordinates_optional@example.com',
            'password' => 'password123',
            'role' => User::ROLE_CUSTOMER,
            'address' => '88 Ridgeview Street, Angono, Rizal',
        ]);

        $response = $this->actingAs($customer)
            ->postJson('/api/service-requests', [
                'request_type' => 'maintenance',
                'details' => 'Routine maintenance visit request',
                'contact_number' => '0917-222-9090',
                'address' => '88 Ridgeview Street, Angono, Rizal',
                'date_needed' => '2026-04-28',
            ])
            ->assertCreated();

        $this->assertDatabaseHas('service_requests', [
            'id' => $response->json('data.id'),
            'latitude' => null,
            'longitude' => null,
        ]);
    }

    public function test_inspection_request_flow_still_allows_customer_creation_admin_assignment_report_submission_and_admin_completion(): void
    {
        Storage::fake('public');

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
                'address' => '100 Solar Drive, Antipolo City',
                'date_needed' => '2026-04-21',
            ])
            ->assertCreated()
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.contact_number', '0917-555-0100')
            ->assertJsonPath('data.address', '100 Solar Drive, Antipolo City');

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
            ->assertJsonPath('inspection_requests.0.id', $inspectionRequestId)
            ->assertJsonPath('inspection_requests.0.has_final_quotation', false);

        $this->actingAs($technician)
            ->putJson("/api/technician/inspection-requests/{$inspectionRequestId}/status", [
                'status' => 'in_progress',
            ])
            ->assertOk()
            ->assertJsonPath('inspection_request.status', 'in_progress');

        $this->actingAs($technician)
            ->post("/api/technician/inspection-requests/{$inspectionRequestId}/completion-report", [
                'report_text' => 'Inspected the roof, captured measurements, and verified placement constraints.',
                'completed_at' => '2026-04-21 16:30:00',
                'completion_photos' => [
                    UploadedFile::fake()->image('inspection-proof-before-quote.jpg'),
                ],
            ], ['Accept' => 'application/json'])
            ->assertStatus(422)
            ->assertJsonPath('message', 'Create the inspection-based quotation before notifying admin that this inspection is done.');

        Quotation::query()->create([
            'user_id' => $customer->id,
            'inspection_request_id' => $inspectionRequestId,
            'quotation_type' => 'final',
            'monthly_electric_bill' => 5000,
            'status' => 'pending',
        ]);

        $this->actingAs($technician)
            ->post("/api/technician/inspection-requests/{$inspectionRequestId}/completion-report", [
                'report_text' => 'Inspected the roof, captured measurements, and verified placement constraints.',
                'completed_at' => '2026-04-21 16:30:00',
                'completion_photos' => [
                    UploadedFile::fake()->image('inspection-proof.jpg'),
                ],
            ], ['Accept' => 'application/json'])
            ->assertCreated()
            ->assertJsonPath('inspection_request.status', 'in_progress')
            ->assertJsonPath('inspection_request.completion_report.status', 'pending')
            ->assertJsonCount(1, 'inspection_request.completion_report.photos');

        $this->actingAs($admin)
            ->putJson("/api/admin/inspection-requests/{$inspectionRequestId}/status", [
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
        $this->assertDatabaseHas('completion_reports', [
            'inspection_request_id' => $inspectionRequestId,
            'status' => CompletionReport::STATUS_APPROVED,
        ]);
        $this->assertDatabaseCount('completion_report_photos', 1);
    }

    public function test_inspection_request_can_store_optional_map_coordinates(): void
    {
        $customer = User::query()->create([
            'name' => 'Coordinate Customer',
            'email' => 'inspection_coordinates@example.com',
            'password' => 'password123',
            'role' => User::ROLE_CUSTOMER,
            'address' => '123 Solar Street, Quezon City',
        ]);

        $response = $this->actingAs($customer)
            ->postJson('/api/inspection-requests', [
                'details' => 'Inspect the shaded roof area',
                'contact_number' => '0917-555-0111',
                'address' => '123 Solar Street, Quezon City',
                'address_details' => 'Blue gate, 2nd floor office entrance',
                'latitude' => 14.6760413,
                'longitude' => 121.0437003,
                'date_needed' => '2026-04-27',
            ])
            ->assertCreated()
            ->assertJsonPath('data.address', '123 Solar Street, Quezon City')
            ->assertJsonPath('data.address_details', 'Blue gate, 2nd floor office entrance');

        $this->assertDatabaseHas('inspection_requests', [
            'id' => $response->json('data.id'),
            'address_details' => 'Blue gate, 2nd floor office entrance',
            'latitude' => '14.6760413',
            'longitude' => '121.0437003',
        ]);
    }

    public function test_inspection_request_coordinates_remain_optional(): void
    {
        $customer = User::query()->create([
            'name' => 'Optional Coordinate Customer',
            'email' => 'inspection_coordinates_optional@example.com',
            'password' => 'password123',
            'role' => User::ROLE_CUSTOMER,
            'address' => '456 Sunbeam Avenue, Pasig City',
        ]);

        $response = $this->actingAs($customer)
            ->postJson('/api/inspection-requests', [
                'details' => 'Inspect the garage roof',
                'contact_number' => '0917-555-0222',
                'address' => '456 Sunbeam Avenue, Pasig City',
                'date_needed' => '2026-04-28',
            ])
            ->assertCreated();

        $this->assertDatabaseHas('inspection_requests', [
            'id' => $response->json('data.id'),
            'latitude' => null,
            'longitude' => null,
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
                'address' => '300 Ridge Road, Binangonan, Rizal',
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

    public function test_admin_can_only_mark_cancel_requested_inspection_as_cancelled(): void
    {
        $customer = User::query()->create([
            'name' => 'Customer User',
            'email' => 'customer_inspection_cancel_review@example.com',
            'password' => 'password123',
            'role' => User::ROLE_CUSTOMER,
            'address' => '77 Solar Avenue, Makati City',
        ]);
        $customer->forceFill(['email_verified_at' => now()])->save();

        $admin = User::query()->create([
            'name' => 'Admin User',
            'email' => 'admin_inspection_cancel_review@example.com',
            'password' => 'password123',
            'role' => User::ROLE_ADMIN,
        ]);
        $admin->forceFill(['email_verified_at' => now()])->save();

        $inspectionRequestId = $this->actingAs($customer)
            ->postJson('/api/inspection-requests', [
                'details' => 'Inspect rooftop setup before installation.',
                'contact_number' => '0917-777-2000',
                'address' => '77 Solar Avenue, Makati City',
                'date_needed' => '2026-04-27',
            ])
            ->assertCreated()
            ->assertJsonPath('data.status', 'pending')
            ->json('data.id');

        $cancellationNote = 'Schedule changed. Please cancel this inspection request.';

        $this->actingAs($customer)
            ->putJson("/api/inspection-requests/{$inspectionRequestId}/cancel", [
                'cancellation_note' => $cancellationNote,
            ])
            ->assertOk()
            ->assertJsonPath('inspection_request.status', 'pending')
            ->assertJsonPath('inspection_request.cancellation_note', $cancellationNote);

        $this->actingAs($admin)
            ->putJson("/api/admin/inspection-requests/{$inspectionRequestId}/status", [
                'status' => 'approved',
            ])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'This inspection has a customer cancellation request and can only be marked as cancelled.');

        $this->assertDatabaseHas('inspection_requests', [
            'id' => $inspectionRequestId,
            'status' => 'pending',
            'cancellation_note' => $cancellationNote,
        ]);

        $this->actingAs($admin)
            ->putJson("/api/admin/inspection-requests/{$inspectionRequestId}/status", [
                'status' => 'cancelled',
            ])
            ->assertOk()
            ->assertJsonPath('inspection_request.status', 'cancelled');

        $this->assertDatabaseHas('inspection_requests', [
            'id' => $inspectionRequestId,
            'status' => 'cancelled',
            'cancellation_note' => $cancellationNote,
        ]);
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
                'address' => '500 Mercury Avenue, Pasig City',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['contact_number']);
    }

    public function test_customer_cannot_create_another_request_while_an_existing_request_is_ongoing(): void
    {
        $customer = User::query()->create([
            'name' => 'Customer User',
            'email' => 'customer_existing_request_guard@example.com',
            'password' => 'password123',
            'role' => User::ROLE_CUSTOMER,
            'address' => '88 Aurora Boulevard, Quezon City',
        ]);

        InspectionRequest::query()->create([
            'user_id' => $customer->id,
            'details' => 'Initial inspection request',
            'contact_number' => '0917-111-2222',
            'address' => '88 Aurora Boulevard, Quezon City',
            'date_needed' => '2026-05-31',
            'status' => 'pending',
        ]);

        $expectedMessage = 'You already have an ongoing inspection, installation, or maintenance request. Please wait until it is completed, cancelled, or declined before submitting another request.';

        $this->actingAs($customer)
            ->postJson('/api/service-requests', [
                'request_type' => 'Maintenance',
                'details' => 'Follow-up maintenance request',
                'contact_number' => '0917-111-2222',
                'address' => '88 Aurora Boulevard, Quezon City',
                'date_needed' => '2026-06-02',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['request'])
            ->assertJsonPath('errors.request.0', $expectedMessage);

        $this->actingAs($customer)
            ->postJson('/api/inspection-requests', [
                'details' => 'Second inspection request should be blocked',
                'contact_number' => '0917-111-2222',
                'address' => '88 Aurora Boulevard, Quezon City',
                'date_needed' => '2026-06-03',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['request'])
            ->assertJsonPath('errors.request.0', $expectedMessage);

        $this->assertDatabaseCount('inspection_requests', 1);
        $this->assertDatabaseCount('service_requests', 0);
    }
}
