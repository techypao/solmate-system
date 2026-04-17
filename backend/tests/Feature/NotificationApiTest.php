<?php

namespace Tests\Feature;

use App\Models\ServiceRequest;
use App\Models\User;
use App\Notifications\ServiceRequestStatusUpdatedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_notifications_api_supports_listing_counts_and_read_actions(): void
    {
        $customer = $this->createUser(User::ROLE_CUSTOMER, 'customer');
        $adminOne = $this->createUser(User::ROLE_ADMIN, 'admin_one');
        $adminTwo = $this->createUser(User::ROLE_ADMIN, 'admin_two');

        $this->actingAs($customer)
            ->postJson('/api/service-requests', [
                'request_type' => 'Panel Cleaning',
                'details' => 'Clean the rooftop array.',
                'contact_number' => '0917-111-2222',
                'date_needed' => '2026-04-20',
            ])
            ->assertCreated();

        $this->actingAs($customer)
            ->postJson('/api/inspection-requests', [
                'details' => 'Inspect roof access and inverter location.',
                'contact_number' => '0917-222-3333',
                'date_needed' => '2026-04-22',
            ])
            ->assertCreated();

        $this->assertSame(2, $adminOne->fresh()->unreadNotifications()->count());
        $this->assertSame(2, $adminTwo->fresh()->unreadNotifications()->count());

        $listResponse = $this->actingAs($adminOne)
            ->getJson('/api/notifications')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(2, 'data')
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'type',
                        'title',
                        'message',
                        'entity_type',
                        'entity_id',
                        'target_screen',
                        'target_params',
                        'status',
                        'is_read',
                        'read_at',
                        'created_at',
                    ],
                ],
            ]);

        $this->assertEqualsCanonicalizing([
            'admin_new_service_request',
            'admin_new_inspection_request',
        ], collect($listResponse->json('data'))->pluck('type')->all());

        $this->actingAs($adminOne)
            ->getJson('/api/notifications/unread-count')
            ->assertOk()
            ->assertJson([
                'success' => true,
                'unread_count' => 2,
            ]);

        $notificationId = $adminOne->fresh()->notifications()->latest()->firstOrFail()->id;

        $this->actingAs($adminOne)
            ->patchJson("/api/notifications/{$notificationId}/read")
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $notificationId)
            ->assertJsonPath('data.is_read', true);

        $this->actingAs($adminOne)
            ->getJson('/api/notifications/unread-count')
            ->assertOk()
            ->assertJson([
                'success' => true,
                'unread_count' => 1,
            ]);

        $this->actingAs($adminOne)
            ->patchJson('/api/notifications/mark-all-read')
            ->assertOk()
            ->assertJson([
                'success' => true,
                'unread_count' => 0,
            ]);

        $this->assertSame(0, $adminOne->fresh()->unreadNotifications()->count());
        $this->assertSame(2, $adminTwo->fresh()->unreadNotifications()->count());
    }

    public function test_operational_events_notify_the_correct_customer_and_technician_users(): void
    {
        $customer = $this->createUser(User::ROLE_CUSTOMER, 'customer_ops');
        $admin = $this->createUser(User::ROLE_ADMIN, 'admin_ops');
        $technician = $this->createUser(User::ROLE_TECHNICIAN, 'technician_ops');

        $serviceRequestId = $this->actingAs($customer)
            ->postJson('/api/service-requests', [
                'request_type' => 'Maintenance',
                'details' => 'Inspect wiring and inverter.',
                'contact_number' => '0917-333-4444',
                'date_needed' => '2026-04-24',
            ])
            ->assertCreated()
            ->json('data.id');

        $this->actingAs($admin)
            ->putJson("/api/service-requests/{$serviceRequestId}/assign-technician", [
                'technician_id' => $technician->id,
            ])
            ->assertOk();

        $this->actingAs($technician)
            ->putJson("/api/technician/service-requests/{$serviceRequestId}/status", [
                'status' => 'in_progress',
            ])
            ->assertOk();

        $this->actingAs($admin)
            ->putJson("/api/admin/service-requests/{$serviceRequestId}/preferred-date", [
                'date_needed' => '2026-04-30',
            ])
            ->assertOk();

        $this->actingAs($admin)
            ->putJson("/api/admin/service-requests/{$serviceRequestId}/status", [
                'status' => 'completed',
            ])
            ->assertOk();

        $inspectionRequestId = $this->actingAs($customer)
            ->postJson('/api/inspection-requests', [
                'details' => 'Check roof layout and panel placement.',
                'contact_number' => '0917-444-5555',
                'date_needed' => '2026-04-22',
            ])
            ->assertCreated()
            ->json('data.id');

        $this->actingAs($admin)
            ->putJson("/api/inspection-requests/{$inspectionRequestId}/assign-technician", [
                'technician_id' => $technician->id,
            ])
            ->assertOk();

        $this->actingAs($technician)
            ->putJson("/api/technician/inspection-requests/{$inspectionRequestId}/status", [
                'status' => 'in_progress',
            ])
            ->assertOk();

        $this->actingAs($technician)
            ->putJson("/api/technician/inspection-requests/{$inspectionRequestId}/status", [
                'status' => 'completed',
            ])
            ->assertOk();

        $this->actingAs($admin)
            ->putJson("/api/inspection-requests/{$inspectionRequestId}/preferred-date", [
                'date_needed' => '2026-04-26',
            ])
            ->assertOk();

        $this->actingAs($technician)
            ->postJson('/api/technician/final-quotations', [
                'inspection_request_id' => $inspectionRequestId,
                'monthly_electric_bill' => 5500,
                'pv_system_type' => 'hybrid',
                'with_battery' => true,
                'status' => 'pending',
            ])
            ->assertCreated();

        $customerNotifications = $customer->fresh()->notifications()->latest()->get();
        $technicianNotifications = $technician->fresh()->notifications()->latest()->get();
        $adminNotifications = $admin->fresh()->notifications()->latest()->get();

        $this->assertCount(9, $customerNotifications);
        $this->assertCount(4, $technicianNotifications);
        $this->assertCount(2, $adminNotifications);

        $customerTypes = $customerNotifications->map(fn ($notification) => $notification->data['type'])->all();
        $technicianTypes = $technicianNotifications->map(fn ($notification) => $notification->data['type'])->all();

        $this->assertEqualsCanonicalizing([
            'service_request_status_updated',
            'service_request_status_updated',
            'schedule_rescheduled',
            'service_request_status_updated',
            'inspection_request_status_updated',
            'inspection_request_status_updated',
            'inspection_request_status_updated',
            'schedule_rescheduled',
            'final_quotation_available',
        ], $customerTypes);
        $this->assertEqualsCanonicalizing([
            'service_request_assigned',
            'schedule_rescheduled',
            'inspection_request_assigned',
            'schedule_rescheduled',
        ], $technicianTypes);

        $this->actingAs($customer)
            ->getJson('/api/notifications/unread-count')
            ->assertOk()
            ->assertJson([
                'success' => true,
                'unread_count' => 9,
            ]);

        $customerListResponse = $this->actingAs($customer)
            ->getJson('/api/notifications')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(9, 'data');

        $this->assertContains(
            'final_quotation_available',
            collect($customerListResponse->json('data'))->pluck('type')->all()
        );
    }

    public function test_user_cannot_mark_someone_elses_notification_as_read(): void
    {
        $admin = $this->createUser(User::ROLE_ADMIN, 'admin_owner');
        $customer = $this->createUser(User::ROLE_CUSTOMER, 'customer_owner');
        $otherCustomer = $this->createUser(User::ROLE_CUSTOMER, 'customer_other');

        $serviceRequest = ServiceRequest::query()->create([
            'user_id' => $customer->id,
            'request_type' => 'Maintenance',
            'details' => 'Inspect inverter health.',
            'contact_number' => '0917-777-8888',
            'status' => 'assigned',
        ]);

        $customer->notify(new ServiceRequestStatusUpdatedNotification($serviceRequest, $admin->id));

        $notificationId = $customer->fresh()->notifications()->firstOrFail()->id;

        $this->actingAs($otherCustomer)
            ->patchJson("/api/notifications/{$notificationId}/read")
            ->assertNotFound();

        $this->assertNull($customer->fresh()->notifications()->firstOrFail()->read_at);
    }

    private function createUser(string $role, string $prefix): User
    {
        return User::query()->create([
            'name' => ucfirst($prefix) . ' User',
            'email' => "{$prefix}@example.com",
            'password' => 'password123',
            'role' => $role,
        ]);
    }
}
