<?php

namespace Tests\Feature;

use App\Models\InspectionRequest;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;
use App\Services\PreferredDateLockService;
use Tests\TestCase;

class PreferredDateLockingTest extends TestCase
{
    use RefreshDatabase;

    public function test_service_request_creation_rejects_a_date_locked_by_an_active_inspection_request(): void
    {
        $existingCustomer = $this->createCustomer('existing_inspection_lock@example.com');
        $newCustomer = $this->createCustomer('new_service_request@example.com');

        InspectionRequest::query()->create([
            'user_id' => $existingCustomer->id,
            'details' => 'Existing inspection request',
            'contact_number' => '0917-100-0001',
            'date_needed' => '2026-05-10',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($newCustomer)
            ->postJson('/api/service-requests', [
                'request_type' => 'Maintenance',
                'details' => 'Need inverter checkup',
                'contact_number' => '0917-200-0002',
                'date_needed' => '2026-05-10',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['date_needed']);

        $this->assertSame(
            'Selected date is already reserved. Please choose another date.',
            $response->json('errors.date_needed.0')
        );
    }

    public function test_authenticated_users_can_fetch_unavailable_preferred_dates(): void
    {
        $customer = $this->createCustomer('availability_viewer@example.com');

        InspectionRequest::query()->create([
            'user_id' => $customer->id,
            'details' => 'Active inspection request',
            'contact_number' => '0917-100-0020',
            'date_needed' => '2026-06-01',
            'status' => 'assigned',
        ]);

        ServiceRequest::query()->create([
            'user_id' => $customer->id,
            'request_type' => 'Maintenance',
            'details' => 'Active service request',
            'contact_number' => '0917-100-0021',
            'date_needed' => '2026-06-02',
            'status' => 'in_progress',
        ]);

        ServiceRequest::query()->create([
            'user_id' => $customer->id,
            'request_type' => 'Maintenance',
            'details' => 'Released service request',
            'contact_number' => '0917-100-0022',
            'date_needed' => '2026-06-03',
            'status' => 'completed',
        ]);

        $this->actingAs($customer)
            ->getJson('/api/preferred-date-availability')
            ->assertOk()
            ->assertJson([
                'unavailable_dates' => [
                    '2026-06-01',
                    '2026-06-02',
                ],
            ]);
    }

    public function test_preferred_date_lock_service_rejects_work_when_the_same_date_lock_is_already_held(): void
    {
        $lock = Cache::lock('preferred-date-lock:2026-06-04', 10);

        $this->assertTrue($lock->get());

        try {
            $service = app(PreferredDateLockService::class);

            try {
                $service->withLockedDates(['2026-06-04'], fn () => 'should not run', 0);
                $this->fail('Expected preferred date lock acquisition to fail.');
            } catch (ValidationException $exception) {
                $this->assertSame(
                    'Selected date is already reserved. Please choose another date.',
                    $exception->errors()['date_needed'][0] ?? null
                );
            }
        } finally {
            $lock->release();
        }
    }

    public function test_inspection_request_creation_rejects_a_date_locked_by_an_active_service_request(): void
    {
        $existingCustomer = $this->createCustomer('existing_service_lock@example.com');
        $newCustomer = $this->createCustomer('new_inspection_request@example.com');

        ServiceRequest::query()->create([
            'user_id' => $existingCustomer->id,
            'request_type' => 'Panel Cleaning',
            'details' => 'Existing service request',
            'contact_number' => '0917-300-0003',
            'date_needed' => '2026-05-11',
            'status' => 'in_progress',
        ]);

        $response = $this->actingAs($newCustomer)
            ->postJson('/api/inspection-requests', [
                'details' => 'Need a site inspection',
                'contact_number' => '0917-400-0004',
                'date_needed' => '2026-05-11',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['date_needed']);

        $this->assertSame(
            'Selected date is already reserved. Please choose another date.',
            $response->json('errors.date_needed.0')
        );
    }

    public function test_create_flow_treats_assigned_status_as_an_active_date_lock(): void
    {
        $existingCustomer = $this->createCustomer('assigned_lock_existing@example.com');
        $newCustomer = $this->createCustomer('assigned_lock_new@example.com');

        ServiceRequest::query()->create([
            'user_id' => $existingCustomer->id,
            'request_type' => 'Maintenance',
            'details' => 'Already assigned request should still lock the date',
            'contact_number' => '0917-310-0003',
            'date_needed' => '2026-05-15',
            'status' => 'assigned',
        ]);

        $response = $this->actingAs($newCustomer)
            ->postJson('/api/inspection-requests', [
                'details' => 'New inspection should be blocked by assigned request',
                'contact_number' => '0917-410-0004',
                'date_needed' => '2026-05-15',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['date_needed']);

        $this->assertSame(
            'Selected date is already reserved. Please choose another date.',
            $response->json('errors.date_needed.0')
        );
    }

    public function test_released_statuses_do_not_keep_a_preferred_date_locked(): void
    {
        $existingCustomer = $this->createCustomer('released_status_existing@example.com');
        $newCustomer = $this->createCustomer('released_status_new@example.com');

        foreach (['cancelled', 'declined', 'completed'] as $status) {
            ServiceRequest::query()->create([
                'user_id' => $existingCustomer->id,
                'request_type' => 'Battery Check',
                'details' => "Released service request in {$status} state",
                'contact_number' => '0917-500-0005',
                'date_needed' => '2026-05-12',
                'status' => $status,
            ]);
        }

        $this->actingAs($newCustomer)
            ->postJson('/api/inspection-requests', [
                'details' => 'Date should be reusable now',
                'contact_number' => '0917-600-0006',
                'date_needed' => '2026-05-12',
            ])
            ->assertCreated()
            ->assertJsonPath('data.date_needed', '2026-05-12');
    }

    public function test_service_request_preferred_date_update_allows_the_same_record_to_keep_its_date(): void
    {
        $admin = $this->createAdmin('service_same_date_admin@example.com');
        $serviceCustomer = $this->createCustomer('service_update_owner@example.com');

        $serviceRequest = ServiceRequest::query()->create([
            'user_id' => $serviceCustomer->id,
            'request_type' => 'Maintenance',
            'details' => 'Keep current preferred date',
            'contact_number' => '0917-700-0007',
            'date_needed' => '2026-05-13',
            'status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->putJson("/api/admin/service-requests/{$serviceRequest->id}/preferred-date", [
                'date_needed' => '2026-05-13',
            ])
            ->assertOk()
            ->assertJsonPath('data.id', $serviceRequest->id);
    }

    public function test_inspection_request_preferred_date_update_allows_the_same_record_to_keep_its_date(): void
    {
        $admin = $this->createAdmin('inspection_same_date_admin@example.com');
        $inspectionCustomer = $this->createCustomer('inspection_same_date_owner@example.com');

        $inspectionRequest = InspectionRequest::query()->create([
            'user_id' => $inspectionCustomer->id,
            'details' => 'Keep current inspection preferred date',
            'contact_number' => '0917-800-0008',
            'date_needed' => '2026-05-14',
            'status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->putJson("/api/inspection-requests/{$inspectionRequest->id}/preferred-date", [
                'date_needed' => '2026-05-14',
            ])
            ->assertOk()
            ->assertJsonPath('inspection_request.id', $inspectionRequest->id);
    }

    public function test_service_request_preferred_date_update_rejects_another_active_requests_date(): void
    {
        $admin = $this->createAdmin('service_conflict_admin@example.com');
        $serviceCustomer = $this->createCustomer('service_conflict_owner@example.com');
        $inspectionCustomer = $this->createCustomer('inspection_conflict_lock@example.com');

        $serviceRequest = ServiceRequest::query()->create([
            'user_id' => $serviceCustomer->id,
            'request_type' => 'Maintenance',
            'details' => 'Reschedule service request',
            'contact_number' => '0917-700-0007',
            'date_needed' => '2026-05-13',
            'status' => 'pending',
        ]);

        InspectionRequest::query()->create([
            'user_id' => $inspectionCustomer->id,
            'details' => 'Inspection already using target date',
            'contact_number' => '0917-800-0008',
            'date_needed' => '2026-05-14',
            'status' => 'assigned',
        ]);

        $response = $this->actingAs($admin)
            ->putJson("/api/admin/service-requests/{$serviceRequest->id}/preferred-date", [
                'date_needed' => '2026-05-14',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['date_needed']);

        $this->assertSame(
            'Selected date is already reserved. Please choose another date.',
            $response->json('errors.date_needed.0')
        );
    }

    public function test_inspection_request_preferred_date_update_rejects_another_active_requests_date(): void
    {
        $admin = $this->createAdmin('inspection_conflict_admin@example.com');
        $inspectionCustomer = $this->createCustomer('inspection_conflict_owner@example.com');
        $serviceCustomer = $this->createCustomer('service_conflict_lock@example.com');

        $inspectionRequest = InspectionRequest::query()->create([
            'user_id' => $inspectionCustomer->id,
            'details' => 'Reschedule inspection request',
            'contact_number' => '0917-810-0008',
            'date_needed' => '2026-05-16',
            'status' => 'pending',
        ]);

        ServiceRequest::query()->create([
            'user_id' => $serviceCustomer->id,
            'request_type' => 'Panel Cleaning',
            'details' => 'Service already using target date',
            'contact_number' => '0917-910-0009',
            'date_needed' => '2026-05-17',
            'status' => 'in_progress',
        ]);

        $response = $this->actingAs($admin)
            ->putJson("/api/inspection-requests/{$inspectionRequest->id}/preferred-date", [
                'date_needed' => '2026-05-17',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['date_needed']);

        $this->assertSame(
            'Selected date is already reserved. Please choose another date.',
            $response->json('errors.date_needed.0')
        );
    }

    public function test_service_request_preferred_date_update_succeeds_when_moving_to_a_free_date(): void
    {
        $admin = $this->createAdmin('service_free_date_admin@example.com');
        $serviceCustomer = $this->createCustomer('service_free_date_owner@example.com');

        $serviceRequest = ServiceRequest::query()->create([
            'user_id' => $serviceCustomer->id,
            'request_type' => 'Maintenance',
            'details' => 'Move to a new free date',
            'contact_number' => '0917-720-0010',
            'date_needed' => '2026-05-18',
            'status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->putJson("/api/admin/service-requests/{$serviceRequest->id}/preferred-date", [
                'date_needed' => '2026-05-19',
            ])
            ->assertOk()
            ->assertJsonPath('data.id', $serviceRequest->id)
            ->assertJsonPath('data.date_needed', '2026-05-19T00:00:00.000000Z');
    }

    public function test_inspection_request_preferred_date_update_succeeds_when_moving_to_a_free_date(): void
    {
        $admin = $this->createAdmin('inspection_free_date_admin@example.com');
        $inspectionCustomer = $this->createCustomer('inspection_free_date_owner@example.com');
        $inspectionRequest = InspectionRequest::query()->create([
            'user_id' => $inspectionCustomer->id,
            'details' => 'Move inspection to a new free date',
            'contact_number' => '0917-820-0011',
            'date_needed' => '2026-05-20',
            'status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->putJson("/api/inspection-requests/{$inspectionRequest->id}/preferred-date", [
                'date_needed' => '2026-05-21',
            ])
            ->assertOk()
            ->assertJsonPath('inspection_request.date_needed', '2026-05-21');
    }

    public function test_old_service_request_date_becomes_available_after_successful_reschedule(): void
    {
        $admin = $this->createAdmin('service_old_date_release_admin@example.com');
        $serviceCustomer = $this->createCustomer('service_old_date_owner@example.com');
        $newCustomer = $this->createCustomer('service_old_date_new_customer@example.com');

        $serviceRequest = ServiceRequest::query()->create([
            'user_id' => $serviceCustomer->id,
            'request_type' => 'Maintenance',
            'details' => 'Original service request date should be released',
            'contact_number' => '0917-730-0012',
            'date_needed' => '2026-05-22',
            'status' => 'assigned',
        ]);

        $this->actingAs($admin)
            ->putJson("/api/admin/service-requests/{$serviceRequest->id}/preferred-date", [
                'date_needed' => '2026-05-23',
            ])
            ->assertOk()
            ->assertJsonPath('data.date_needed', '2026-05-23T00:00:00.000000Z');

        $this->actingAs($newCustomer)
            ->postJson('/api/inspection-requests', [
                'details' => 'Old service date should now be available',
                'contact_number' => '0917-830-0013',
                'date_needed' => '2026-05-22',
            ])
            ->assertCreated()
            ->assertJsonPath('data.date_needed', '2026-05-22');
    }

    public function test_old_inspection_request_date_becomes_available_after_successful_reschedule(): void
    {
        $admin = $this->createAdmin('inspection_old_date_release_admin@example.com');
        $inspectionCustomer = $this->createCustomer('inspection_old_date_owner@example.com');
        $newCustomer = $this->createCustomer('inspection_old_date_new_customer@example.com');

        $inspectionRequest = InspectionRequest::query()->create([
            'user_id' => $inspectionCustomer->id,
            'details' => 'Original inspection request date should be released',
            'contact_number' => '0917-840-0014',
            'date_needed' => '2026-05-24',
            'status' => 'assigned',
        ]);

        $this->actingAs($admin)
            ->putJson("/api/inspection-requests/{$inspectionRequest->id}/preferred-date", [
                'date_needed' => '2026-05-25',
            ])
            ->assertOk()
            ->assertJsonPath('inspection_request.date_needed', '2026-05-25');

        $response = $this->actingAs($newCustomer)
            ->postJson('/api/service-requests', [
                'request_type' => 'Panel Cleaning',
                'details' => 'Old inspection date should now be available',
                'contact_number' => '0917-940-0015',
                'date_needed' => '2026-05-24',
            ])
            ->assertCreated();

        $this->assertStringStartsWith('2026-05-24', (string) $response->json('data.date_needed'));
    }

    public function test_inspection_request_date_becomes_reusable_after_status_changes_to_cancelled(): void
    {
        $this->assertInspectionDateReleasedByAdminStatusChange('cancelled', '2026-05-26', 'inspection_cancelled');
    }

    public function test_inspection_request_date_becomes_reusable_after_status_changes_to_declined(): void
    {
        $this->assertInspectionDateReleasedByAdminStatusChange('declined', '2026-05-27', 'inspection_declined');
    }

    public function test_inspection_request_date_becomes_reusable_after_status_changes_to_completed(): void
    {
        $this->assertInspectionDateReleasedByAdminStatusChange('completed', '2026-05-28', 'inspection_completed');
    }

    public function test_service_request_date_becomes_reusable_after_status_changes_to_cancelled(): void
    {
        $this->assertServiceDateReleasedByAdminStatusChange('cancelled', '2026-05-29', 'service_cancelled');
    }

    public function test_service_request_date_becomes_reusable_after_status_changes_to_declined(): void
    {
        $this->assertServiceDateReleasedByAdminStatusChange('declined', '2026-05-30', 'service_declined');
    }

    public function test_service_request_date_becomes_reusable_after_status_changes_to_completed(): void
    {
        $this->assertServiceDateReleasedByAdminStatusChange('completed', '2026-05-31', 'service_completed');
    }

    private function createCustomer(string $email): User
    {
        return User::query()->create([
            'name' => 'Customer User',
            'email' => $email,
            'password' => 'password123',
            'role' => User::ROLE_CUSTOMER,
        ]);
    }

    private function createAdmin(string $email): User
    {
        return User::query()->create([
            'name' => 'Admin User',
            'email' => $email,
            'password' => 'password123',
            'role' => User::ROLE_ADMIN,
        ]);
    }

    private function assertInspectionDateReleasedByAdminStatusChange(
        string $releasedStatus,
        string $dateNeeded,
        string $emailPrefix
    ): void {
        $admin = $this->createAdmin("{$emailPrefix}_admin@example.com");
        $inspectionCustomer = $this->createCustomer("{$emailPrefix}_owner@example.com");
        $newCustomer = $this->createCustomer("{$emailPrefix}_new@example.com");

        $inspectionRequest = InspectionRequest::query()->create([
            'user_id' => $inspectionCustomer->id,
            'details' => "Inspection request transitioning to {$releasedStatus}",
            'contact_number' => '0917-950-0016',
            'date_needed' => $dateNeeded,
            'status' => 'assigned',
        ]);

        $this->actingAs($admin)
            ->putJson("/api/admin/inspection-requests/{$inspectionRequest->id}/status", [
                'status' => $releasedStatus,
            ])
            ->assertOk()
            ->assertJsonPath('inspection_request.status', $releasedStatus);

        $this->actingAs($newCustomer)
            ->postJson('/api/service-requests', [
                'request_type' => 'Battery Check',
                'details' => "Date should be reusable after inspection is {$releasedStatus}",
                'contact_number' => '0917-960-0017',
                'date_needed' => $dateNeeded,
            ])
            ->assertCreated();
    }

    private function assertServiceDateReleasedByAdminStatusChange(
        string $releasedStatus,
        string $dateNeeded,
        string $emailPrefix
    ): void {
        $admin = $this->createAdmin("{$emailPrefix}_admin@example.com");
        $serviceCustomer = $this->createCustomer("{$emailPrefix}_owner@example.com");
        $newCustomer = $this->createCustomer("{$emailPrefix}_new@example.com");

        $serviceRequest = ServiceRequest::query()->create([
            'user_id' => $serviceCustomer->id,
            'request_type' => 'Maintenance',
            'details' => "Service request transitioning to {$releasedStatus}",
            'contact_number' => '0917-970-0018',
            'date_needed' => $dateNeeded,
            'status' => 'assigned',
        ]);

        $this->actingAs($admin)
            ->putJson("/api/admin/service-requests/{$serviceRequest->id}/status", [
                'status' => $releasedStatus,
            ])
            ->assertOk()
            ->assertJsonPath('data.status', $releasedStatus);

        $this->actingAs($newCustomer)
            ->postJson('/api/inspection-requests', [
                'details' => "Date should be reusable after service is {$releasedStatus}",
                'contact_number' => '0917-980-0019',
                'date_needed' => $dateNeeded,
            ])
            ->assertCreated();
    }
}
