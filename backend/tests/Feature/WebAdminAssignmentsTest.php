<?php

namespace Tests\Feature;

use App\Models\InspectionRequest;
use App\Models\ServiceRequest;
use App\Models\User;
use Carbon\Carbon;
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
            'contact_number' => '0917-222-1000',
            'status' => 'pending',
        ]);

        InspectionRequest::query()->create([
            'user_id' => $customer->id,
            'details' => 'Inspect rooftop setup',
            'contact_number' => '0917-333-2000',
            'status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->get('/admin/request-assignments')
            ->assertOk()
            ->assertSee('Admin Request Assignments')
            ->assertSee('Service Requests')
            ->assertSee('Inspection Requests')
            ->assertSee($technician->email)
            ->assertSee('0917-222-1000')
            ->assertSee('0917-333-2000');
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

    public function test_admin_can_update_service_request_preferred_date(): void
    {
        $admin = User::query()->create([
            'name' => 'Admin User',
            'email' => 'admin_service_date@example.com',
            'password' => 'password123',
            'role' => User::ROLE_ADMIN,
        ]);

        $customer = User::query()->create([
            'name' => 'Customer User',
            'email' => 'customer_service_date@example.com',
            'password' => 'password123',
            'role' => User::ROLE_CUSTOMER,
        ]);

        $serviceRequest = ServiceRequest::query()->create([
            'user_id' => $customer->id,
            'request_type' => 'Maintenance',
            'details' => 'Inspect inverter and wiring',
            'contact_number' => '0917-444-1100',
            'date_needed' => '2026-04-23',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)
            ->putJson("/api/admin/service-requests/{$serviceRequest->id}/preferred-date", [
                'date_needed' => '2026-04-27',
            ]);

        $response->assertOk()
            ->assertJsonPath('data.id', $serviceRequest->id);

        $this->assertStringStartsWith('2026-04-27', (string) $response->json('data.date_needed'));

        $this->assertDatabaseHas('service_requests', [
            'id' => $serviceRequest->id,
            'date_needed' => '2026-04-27 00:00:00',
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

    public function test_admin_can_update_inspection_request_preferred_date(): void
    {
        $admin = User::query()->create([
            'name' => 'Admin User',
            'email' => 'admin_inspection_date@example.com',
            'password' => 'password123',
            'role' => User::ROLE_ADMIN,
        ]);

        $customer = User::query()->create([
            'name' => 'Customer User',
            'email' => 'customer_inspection_date@example.com',
            'password' => 'password123',
            'role' => User::ROLE_CUSTOMER,
        ]);

        $inspectionRequest = InspectionRequest::query()->create([
            'user_id' => $customer->id,
            'details' => 'Inspect the site layout',
            'contact_number' => '0917-444-1000',
            'date_needed' => '2026-04-21',
            'status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->putJson("/api/inspection-requests/{$inspectionRequest->id}/preferred-date", [
                'date_needed' => '2026-04-24',
            ])
            ->assertOk()
            ->assertJsonPath('inspection_request.id', $inspectionRequest->id)
            ->assertJsonPath('inspection_request.date_needed', '2026-04-24');

        $this->assertDatabaseHas('inspection_requests', [
            'id' => $inspectionRequest->id,
            'date_needed' => '2026-04-24',
        ]);
    }

    public function test_non_admin_cannot_update_inspection_request_preferred_date(): void
    {
        $customer = User::query()->create([
            'name' => 'Customer User',
            'email' => 'customer_inspection_date_blocked@example.com',
            'password' => 'password123',
            'role' => User::ROLE_CUSTOMER,
        ]);

        $inspectionRequest = InspectionRequest::query()->create([
            'user_id' => $customer->id,
            'details' => 'Inspect the meter area',
            'date_needed' => '2026-04-21',
            'status' => 'pending',
        ]);

        $this->actingAs($customer)
            ->putJson("/api/inspection-requests/{$inspectionRequest->id}/preferred-date", [
                'date_needed' => '2026-04-25',
            ])
            ->assertForbidden();
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

    public function test_non_admin_cannot_assign_technician_to_service_request(): void
    {
        $customer = User::query()->create([
            'name' => 'Customer User',
            'email' => 'customer_service_assign_blocked@example.com',
            'password' => 'password123',
            'role' => User::ROLE_CUSTOMER,
        ]);

        $technician = User::query()->create([
            'name' => 'Tech User',
            'email' => 'tech_service_assign_blocked@example.com',
            'password' => 'password123',
            'role' => User::ROLE_TECHNICIAN,
        ]);

        $serviceRequest = ServiceRequest::query()->create([
            'user_id' => $customer->id,
            'request_type' => 'Repair',
            'details' => 'Replace wiring',
            'status' => 'pending',
        ]);

        $this->actingAs($customer)
            ->putJson("/api/service-requests/{$serviceRequest->id}/assign-technician", [
                'technician_id' => $technician->id,
            ])
            ->assertForbidden();
    }

    public function test_non_admin_cannot_update_service_request_preferred_date(): void
    {
        $customer = User::query()->create([
            'name' => 'Customer User',
            'email' => 'customer_service_date_blocked@example.com',
            'password' => 'password123',
            'role' => User::ROLE_CUSTOMER,
        ]);

        $serviceRequest = ServiceRequest::query()->create([
            'user_id' => $customer->id,
            'request_type' => 'Repair',
            'details' => 'Replace damaged wiring',
            'date_needed' => '2026-04-23',
            'status' => 'pending',
        ]);

        $this->actingAs($customer)
            ->putJson("/api/admin/service-requests/{$serviceRequest->id}/preferred-date", [
                'date_needed' => '2026-04-29',
            ])
            ->assertForbidden();
    }

    public function test_admin_request_assignments_page_shows_service_completion_review_controls(): void
    {
        $admin = User::query()->create([
            'name' => 'Admin User',
            'email' => 'admin_service_review_page@example.com',
            'password' => 'password123',
            'role' => User::ROLE_ADMIN,
        ]);

        $technician = User::query()->create([
            'name' => 'Tech User',
            'email' => 'tech_service_review_page@example.com',
            'password' => 'password123',
            'role' => User::ROLE_TECHNICIAN,
        ]);

        $customer = User::query()->create([
            'name' => 'Customer User',
            'email' => 'customer_service_review_page@example.com',
            'password' => 'password123',
            'role' => User::ROLE_CUSTOMER,
        ]);

        ServiceRequest::query()->create([
            'user_id' => $customer->id,
            'technician_id' => $technician->id,
            'request_type' => 'Maintenance',
            'details' => 'Check rooftop wiring',
            'status' => 'in_progress',
            'technician_marked_done_at' => Carbon::parse('2026-04-19 10:30:00'),
        ]);

        $this->actingAs($admin)
            ->get('/admin/request-assignments')
            ->assertOk()
            ->assertSee('Awaiting admin review')
            ->assertSee('Official service status')
            ->assertSee('Save official status');
    }

    public function test_admin_request_assignments_page_shows_service_preferred_date_controls(): void
    {
        $admin = User::query()->create([
            'name' => 'Admin User',
            'email' => 'admin_service_page_date@example.com',
            'password' => 'password123',
            'role' => User::ROLE_ADMIN,
        ]);

        $customer = User::query()->create([
            'name' => 'Customer User',
            'email' => 'customer_service_page_date@example.com',
            'password' => 'password123',
            'role' => User::ROLE_CUSTOMER,
        ]);

        ServiceRequest::query()->create([
            'user_id' => $customer->id,
            'request_type' => 'Maintenance',
            'details' => 'Check solar panel connections',
            'contact_number' => '0917-888-2100',
            'date_needed' => '2026-04-27',
            'status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->get('/admin/request-assignments')
            ->assertOk()
            ->assertSee('Preferred Date')
            ->assertSee('Official preferred date')
            ->assertSee('Save preferred date')
            ->assertSee("Adjust this when the customer's requested service date needs to move for technician availability.", false)
            ->assertSee('Apr 27, 2026');
    }

    public function test_admin_request_assignments_page_shows_inspection_preferred_date_controls(): void
    {
        $admin = User::query()->create([
            'name' => 'Admin User',
            'email' => 'admin_inspection_page_date@example.com',
            'password' => 'password123',
            'role' => User::ROLE_ADMIN,
        ]);

        $customer = User::query()->create([
            'name' => 'Customer User',
            'email' => 'customer_inspection_page_date@example.com',
            'password' => 'password123',
            'role' => User::ROLE_CUSTOMER,
        ]);

        InspectionRequest::query()->create([
            'user_id' => $customer->id,
            'details' => 'Inspect rooftop setup',
            'contact_number' => '0917-888-2000',
            'date_needed' => '2026-04-28',
            'status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->get('/admin/request-assignments')
            ->assertOk()
            ->assertSee('Preferred Date')
            ->assertSee('Official preferred date')
            ->assertSee('Save preferred date')
            ->assertSee('Apr 28, 2026');
    }
}
