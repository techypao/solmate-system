<?php

namespace Tests\Feature;

use App\Models\InspectionRequest;
use App\Models\ServiceRequest;
use App\Models\Testimony;
use App\Models\TestimonyImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TestimonyApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_submit_testimony_for_completed_service_request_and_public_endpoint_only_returns_approved_items(): void
    {
        Storage::fake(TestimonyImage::PUBLIC_DISK);

        $customer = User::query()->create([
            'name' => 'Customer User',
            'email' => 'customer_testimony_create@example.com',
            'password' => 'password123',
            'role' => User::ROLE_CUSTOMER,
        ]);

        $completedServiceRequest = ServiceRequest::query()->create([
            'user_id' => $customer->id,
            'request_type' => 'Solar Maintenance',
            'details' => 'Completed maintenance job',
            'status' => 'completed',
        ]);

        $createResponse = $this->actingAs($customer)
            ->postJson('/api/testimonies', [
                'service_request_id' => $completedServiceRequest->id,
                'rating' => 5,
                'title' => 'Great work',
                'message' => 'The team finished the service well.',
            ])
            ->assertCreated()
            ->assertJsonPath('data.user_id', $customer->id)
            ->assertJsonPath('data.service_request_id', $completedServiceRequest->id)
            ->assertJsonPath('data.status', Testimony::STATUS_PENDING);

        $pendingTestimonyId = $createResponse->json('data.id');

        Testimony::query()->whereKey($pendingTestimonyId)->update([
            'status' => Testimony::STATUS_APPROVED,
        ]);

        Testimony::query()->create([
            'user_id' => $customer->id,
            'service_request_id' => $completedServiceRequest->id,
            'rating' => 4,
            'title' => 'Still pending',
            'message' => 'Pending moderation',
            'status' => Testimony::STATUS_PENDING,
        ]);

        $this->getJson('/api/public/testimonies')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $pendingTestimonyId)
            ->assertJsonPath('data.0.status', Testimony::STATUS_APPROVED)
            ->assertJsonPath('data.0.user.name', 'Customer User');
    }

    public function test_testimony_creation_is_restricted_to_customers_with_their_own_completed_requests(): void
    {
        Storage::fake(TestimonyImage::PUBLIC_DISK);

        $customer = User::query()->create([
            'name' => 'Customer User',
            'email' => 'customer_testimony_restrictions@example.com',
            'password' => 'password123',
            'role' => User::ROLE_CUSTOMER,
        ]);

        $otherCustomer = User::query()->create([
            'name' => 'Other Customer',
            'email' => 'other_customer_testimony_restrictions@example.com',
            'password' => 'password123',
            'role' => User::ROLE_CUSTOMER,
        ]);

        $technician = User::query()->create([
            'name' => 'Technician User',
            'email' => 'technician_testimony_restrictions@example.com',
            'password' => 'password123',
            'role' => User::ROLE_TECHNICIAN,
        ]);

        $admin = User::query()->create([
            'name' => 'Admin User',
            'email' => 'admin_testimony_restrictions@example.com',
            'password' => 'password123',
            'role' => User::ROLE_ADMIN,
        ]);

        $pendingServiceRequest = ServiceRequest::query()->create([
            'user_id' => $customer->id,
            'request_type' => 'Panel Check',
            'details' => 'Not completed yet',
            'status' => 'pending',
        ]);

        $otherCustomerInspectionRequest = InspectionRequest::query()->create([
            'user_id' => $otherCustomer->id,
            'details' => 'Completed inspection for someone else',
            'status' => 'completed',
        ]);

        $this->actingAs($customer)
            ->postJson('/api/testimonies', [
                'service_request_id' => $pendingServiceRequest->id,
                'rating' => 5,
                'message' => 'This should fail.',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('service_request_id');

        $this->actingAs($customer)
            ->postJson('/api/testimonies', [
                'inspection_request_id' => $otherCustomerInspectionRequest->id,
                'rating' => 4,
                'message' => 'This should also fail.',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('inspection_request_id');

        $this->actingAs($customer)
            ->postJson('/api/testimonies', [
                'rating' => 4,
                'message' => 'Missing linked request.',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('service_request_id');

        $this->actingAs($technician)
            ->postJson('/api/testimonies', [
                'rating' => 5,
                'message' => 'Technicians cannot submit this.',
            ])
            ->assertForbidden();

        $this->actingAs($admin)
            ->postJson('/api/testimonies', [
                'rating' => 5,
                'message' => 'Admins cannot submit this.',
            ])
            ->assertForbidden();
    }

    public function test_customer_can_only_manage_their_own_testimonies_and_approved_edits_reset_to_pending(): void
    {
        Storage::fake(TestimonyImage::PUBLIC_DISK);

        $customer = User::query()->create([
            'name' => 'Customer User',
            'email' => 'customer_testimony_update@example.com',
            'password' => 'password123',
            'role' => User::ROLE_CUSTOMER,
        ]);

        $otherCustomer = User::query()->create([
            'name' => 'Other Customer',
            'email' => 'other_customer_testimony_update@example.com',
            'password' => 'password123',
            'role' => User::ROLE_CUSTOMER,
        ]);

        $inspectionRequest = InspectionRequest::query()->create([
            'user_id' => $customer->id,
            'details' => 'Completed inspection',
            'status' => 'completed',
        ]);

        $testimony = Testimony::query()->create([
            'user_id' => $customer->id,
            'inspection_request_id' => $inspectionRequest->id,
            'rating' => 5,
            'title' => 'Originally approved',
            'message' => 'Original approved testimony.',
            'status' => Testimony::STATUS_APPROVED,
            'admin_note' => 'Approved previously.',
        ]);

        $this->actingAs($customer)
            ->getJson('/api/my-testimonies')
            ->assertOk()
            ->assertJsonPath('data.0.id', $testimony->id);

        $this->actingAs($customer)
            ->putJson("/api/testimonies/{$testimony->id}", [
                'rating' => 4,
                'title' => 'Updated review',
                'message' => 'Updated after approval.',
            ])
            ->assertOk()
            ->assertJsonPath('data.status', Testimony::STATUS_PENDING)
            ->assertJsonPath('data.admin_note', null)
            ->assertJsonPath('data.inspection_request_id', $inspectionRequest->id);

        $this->actingAs($otherCustomer)
            ->putJson("/api/testimonies/{$testimony->id}", [
                'rating' => 3,
                'message' => 'Not allowed.',
            ])
            ->assertNotFound();

        $this->actingAs($otherCustomer)
            ->deleteJson("/api/testimonies/{$testimony->id}")
            ->assertNotFound();

        $this->actingAs($customer)
            ->deleteJson("/api/testimonies/{$testimony->id}")
            ->assertOk();

        $this->assertDatabaseMissing('testimonies', [
            'id' => $testimony->id,
        ]);
    }

    public function test_admin_can_list_approve_reject_update_and_delete_testimonies(): void
    {
        Storage::fake(TestimonyImage::PUBLIC_DISK);

        $admin = User::query()->create([
            'name' => 'Admin User',
            'email' => 'admin_testimony_moderation@example.com',
            'password' => 'password123',
            'role' => User::ROLE_ADMIN,
        ]);

        $customer = User::query()->create([
            'name' => 'Customer User',
            'email' => 'customer_testimony_moderation@example.com',
            'password' => 'password123',
            'role' => User::ROLE_CUSTOMER,
        ]);

        $serviceRequest = ServiceRequest::query()->create([
            'user_id' => $customer->id,
            'request_type' => 'Installation',
            'details' => 'Completed installation',
            'status' => 'completed',
        ]);

        $testimony = Testimony::query()->create([
            'user_id' => $customer->id,
            'service_request_id' => $serviceRequest->id,
            'rating' => 5,
            'title' => 'Needs review',
            'message' => 'Pending admin review.',
            'status' => Testimony::STATUS_PENDING,
        ]);

        $this->actingAs($admin)
            ->getJson('/api/admin/testimonies')
            ->assertOk()
            ->assertJsonPath('data.0.id', $testimony->id);

        $this->actingAs($admin)
            ->patchJson("/api/admin/testimonies/{$testimony->id}/approve", [
                'admin_note' => 'Approved for public display.',
            ])
            ->assertOk()
            ->assertJsonPath('data.status', Testimony::STATUS_APPROVED)
            ->assertJsonPath('data.admin_note', 'Approved for public display.');

        $this->actingAs($admin)
            ->patchJson("/api/admin/testimonies/{$testimony->id}/reject", [
                'admin_note' => 'Please revise the wording.',
            ])
            ->assertOk()
            ->assertJsonPath('data.status', Testimony::STATUS_REJECTED)
            ->assertJsonPath('data.admin_note', 'Please revise the wording.');

        $this->actingAs($admin)
            ->putJson("/api/admin/testimonies/{$testimony->id}", [
                'service_request_id' => $serviceRequest->id,
                'rating' => 4,
                'title' => 'Admin updated title',
                'message' => 'Admin updated the testimony content.',
                'status' => Testimony::STATUS_APPROVED,
                'admin_note' => 'Approved after admin edit.',
            ])
            ->assertOk()
            ->assertJsonPath('data.rating', 4)
            ->assertJsonPath('data.status', Testimony::STATUS_APPROVED)
            ->assertJsonPath('data.admin_note', 'Approved after admin edit.');

        $this->actingAs($admin)
            ->deleteJson("/api/admin/testimonies/{$testimony->id}")
            ->assertOk();

        $this->assertDatabaseMissing('testimonies', [
            'id' => $testimony->id,
        ]);
    }

    public function test_customer_can_create_and_update_testimony_images_and_public_and_admin_responses_include_them(): void
    {
        Storage::fake(TestimonyImage::PUBLIC_DISK);

        $admin = User::query()->create([
            'name' => 'Admin User',
            'email' => 'admin_testimony_images@example.com',
            'password' => 'password123',
            'role' => User::ROLE_ADMIN,
        ]);

        $customer = User::query()->create([
            'name' => 'Customer User',
            'email' => 'customer_testimony_images@example.com',
            'password' => 'password123',
            'role' => User::ROLE_CUSTOMER,
        ]);

        $serviceRequest = ServiceRequest::query()->create([
            'user_id' => $customer->id,
            'request_type' => 'Cleaning',
            'details' => 'Completed cleaning',
            'status' => 'completed',
        ]);

        $createResponse = $this->actingAs($customer)
            ->post('/api/testimonies', [
                'service_request_id' => $serviceRequest->id,
                'rating' => 5,
                'title' => 'With photos',
                'message' => 'Uploading images with the testimony.',
                'images' => [
                    UploadedFile::fake()->image('before.jpg'),
                    UploadedFile::fake()->image('after.png'),
                ],
            ], ['Accept' => 'application/json'])
            ->assertCreated()
            ->assertJsonCount(2, 'data.images');

        $testimonyId = $createResponse->json('data.id');
        $firstImageId = $createResponse->json('data.images.0.id');
        $firstImagePath = $createResponse->json('data.images.0.image_path');
        $secondImagePath = $createResponse->json('data.images.1.image_path');

        Storage::disk(TestimonyImage::PUBLIC_DISK)->assertExists($firstImagePath);
        Storage::disk(TestimonyImage::PUBLIC_DISK)->assertExists($secondImagePath);

        $updateResponse = $this->actingAs($customer)
            ->post("/api/testimonies/{$testimonyId}", [
                '_method' => 'PUT',
                'service_request_id' => $serviceRequest->id,
                'rating' => 4,
                'title' => 'Updated with one removed',
                'message' => 'Keeping one old image and adding one new image.',
                'remove_image_ids' => [$firstImageId],
                'images' => [
                    UploadedFile::fake()->image('new.webp'),
                ],
            ], ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJsonCount(2, 'data.images');

        Storage::disk(TestimonyImage::PUBLIC_DISK)->assertMissing($firstImagePath);

        $remainingPaths = collect($updateResponse->json('data.images'))->pluck('image_path')->all();
        $this->assertContains($secondImagePath, $remainingPaths);

        Testimony::query()->whereKey($testimonyId)->update([
            'status' => Testimony::STATUS_APPROVED,
        ]);

        $publicResponse = $this->getJson('/api/public/testimonies')
            ->assertOk();

        $this->assertIsString($publicResponse->json('data.0.images.0.image_url'));
        $this->assertStringContainsString('/storage/testimonies/', $publicResponse->json('data.0.images.0.image_url'));

        $this->actingAs($admin)
            ->getJson('/api/admin/testimonies')
            ->assertOk()
            ->assertJsonCount(2, 'data.0.images');
    }

    public function test_testimony_image_validation_limits_and_cleanup_on_delete_work(): void
    {
        Storage::fake(TestimonyImage::PUBLIC_DISK);

        $customer = User::query()->create([
            'name' => 'Customer User',
            'email' => 'customer_testimony_image_validation@example.com',
            'password' => 'password123',
            'role' => User::ROLE_CUSTOMER,
        ]);

        $serviceRequest = ServiceRequest::query()->create([
            'user_id' => $customer->id,
            'request_type' => 'Inspection Follow-up',
            'details' => 'Completed job',
            'status' => 'completed',
        ]);

        $this->actingAs($customer)
            ->post('/api/testimonies', [
                'service_request_id' => $serviceRequest->id,
                'rating' => 5,
                'message' => 'Too many files.',
                'images' => [
                    UploadedFile::fake()->image('1.jpg'),
                    UploadedFile::fake()->image('2.jpg'),
                    UploadedFile::fake()->image('3.jpg'),
                    UploadedFile::fake()->image('4.jpg'),
                    UploadedFile::fake()->image('5.jpg'),
                    UploadedFile::fake()->image('6.jpg'),
                ],
            ], ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('images');

        $this->actingAs($customer)
            ->post('/api/testimonies', [
                'service_request_id' => $serviceRequest->id,
                'rating' => 5,
                'message' => 'Invalid file type.',
                'images' => [
                    UploadedFile::fake()->create('notes.pdf', 100, 'application/pdf'),
                ],
            ], ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('images.0');

        $createResponse = $this->actingAs($customer)
            ->post('/api/testimonies', [
                'service_request_id' => $serviceRequest->id,
                'rating' => 5,
                'message' => 'Valid upload.',
                'images' => [
                    UploadedFile::fake()->image('cleanup.jpg'),
                ],
            ], ['Accept' => 'application/json'])
            ->assertCreated();

        $testimonyId = $createResponse->json('data.id');
        $imagePath = $createResponse->json('data.images.0.image_path');

        Storage::disk(TestimonyImage::PUBLIC_DISK)->assertExists($imagePath);

        $this->actingAs($customer)
            ->deleteJson("/api/testimonies/{$testimonyId}")
            ->assertOk();

        Storage::disk(TestimonyImage::PUBLIC_DISK)->assertMissing($imagePath);
        $this->assertDatabaseMissing('testimony_images', [
            'testimony_id' => $testimonyId,
        ]);
    }
}
