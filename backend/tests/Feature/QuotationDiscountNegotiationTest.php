<?php

namespace Tests\Feature;

use App\Models\InspectionRequest;
use App\Models\Quotation;
use App\Models\User;
use App\Notifications\AdminQuotationDiscountRequestedNotification;
use App\Notifications\QuotationDiscountUpdatedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class QuotationDiscountNegotiationTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_request_discount_for_their_inspection_based_quotation(): void
    {
        Notification::fake();

        $admin = $this->createUser(User::ROLE_ADMIN, 'Admin User');
        $customer = $this->createUser(User::ROLE_CUSTOMER, 'Customer User');
        $technician = $this->createUser(User::ROLE_TECHNICIAN, 'Technician User');
        $inspectionRequest = $this->createInspectionRequest($customer, $technician);
        $quotation = $this->createFinalQuotation($customer, $inspectionRequest, [
            'project_cost' => 120000,
        ]);

        Sanctum::actingAs($customer);

        $response = $this->postJson("/api/customer/final-quotations/{$inspectionRequest->id}/discount-request", [
            'message' => 'Can we lower this a little?',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.id', $quotation->id)
            ->assertJsonPath('data.discount_request_status', 'requested')
            ->assertJsonPath('data.discount_request_message', 'Can we lower this a little?');

        $this->assertDatabaseHas('quotations', [
            'id' => $quotation->id,
            'discount_request_status' => 'requested',
            'discount_request_message' => 'Can we lower this a little?',
        ]);

        Notification::assertSentTo(
            $admin,
            AdminQuotationDiscountRequestedNotification::class,
            function (AdminQuotationDiscountRequestedNotification $notification) use ($admin, $quotation): bool {
                $payload = $notification->toArray($admin);

                return $payload['entity_type'] === 'quotation'
                    && $payload['entity_id'] === $quotation->id
                    && $payload['target_screen'] === 'QuotationItemBuilder'
                    && $payload['target_params']['quotationId'] === $quotation->id;
            }
        );
    }

    public function test_admin_can_apply_discount_and_customer_gets_updated_total(): void
    {
        Notification::fake();

        $admin = $this->createUser(User::ROLE_ADMIN, 'Admin User');
        $customer = $this->createUser(User::ROLE_CUSTOMER, 'Customer User');
        $technician = $this->createUser(User::ROLE_TECHNICIAN, 'Technician User');
        $inspectionRequest = $this->createInspectionRequest($customer, $technician);
        $quotation = $this->createFinalQuotation($customer, $inspectionRequest, [
            'materials_subtotal' => 100000,
            'labor_cost' => 20000,
            'promo_discount' => 5000,
            'project_cost' => 115000,
            'discount_request_status' => 'requested',
        ]);

        Sanctum::actingAs($admin);

        $response = $this->patchJson("/api/admin/quotations/{$quotation->id}/discount", [
            'admin_discount_amount' => 15000,
            'admin_discount_reason' => 'Approved negotiated discount.',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.discount_request_status', 'applied')
            ->assertJsonPath('data.admin_discount_amount', '15000.00')
            ->assertJsonPath('data.admin_discount_reason', 'Approved negotiated discount.')
            ->assertJsonPath('data.project_cost', 100000)
            ->assertJsonPath('data.admin_discount_base_total', 115000);

        $this->assertDatabaseHas('quotations', [
            'id' => $quotation->id,
            'discount_request_status' => 'applied',
            'admin_discount_amount' => 15000.00,
            'project_cost' => 100000.00,
            'admin_discount_applied_by' => $admin->id,
        ]);

        Notification::assertSentTo($customer, QuotationDiscountUpdatedNotification::class);
    }

    public function test_line_item_recalculation_keeps_admin_discount_applied(): void
    {
        $admin = $this->createUser(User::ROLE_ADMIN, 'Admin User');
        $customer = $this->createUser(User::ROLE_CUSTOMER, 'Customer User');
        $technician = $this->createUser(User::ROLE_TECHNICIAN, 'Technician User');
        $inspectionRequest = $this->createInspectionRequest($customer, $technician);
        $quotation = $this->createFinalQuotation($customer, $inspectionRequest, [
            'labor_cost' => 5000,
            'admin_discount_amount' => 10000,
            'project_cost' => 95000,
        ]);

        Sanctum::actingAs($admin);

        $response = $this->putJson("/api/quotations/{$quotation->id}/line-items", [
            'line_items' => [
                [
                    'description' => 'Solar Panel Set',
                    'category' => 'panel',
                    'qty' => 12,
                    'unit' => 'pc',
                    'unit_amount' => 10000,
                ],
            ],
        ]);

        $response->assertOk()
            ->assertJsonPath('data.materials_subtotal', 120000)
            ->assertJsonPath('data.labor_cost', 5000)
            ->assertJsonPath('data.admin_discount_amount', '10000.00')
            ->assertJsonPath('data.project_cost', 115000);
    }

    public function test_admin_notifications_page_contains_quotation_workspace_target_url(): void
    {
        $admin = $this->createUser(User::ROLE_ADMIN, 'Admin User');

        $this->actingAs($admin)
            ->get('/admin/notifications')
            ->assertOk()
            ->assertSee('__data_quotationsUrl')
            ->assertSee('/quotations/item-builder');
    }

    private function createUser(string $role, string $name): User
    {
        $user = User::query()->create([
            'name' => $name,
            'email' => $role . '_' . uniqid() . '@example.com',
            'password' => Hash::make('password'),
            'role' => $role,
        ]);

        $user->forceFill(['email_verified_at' => now()])->save();

        return $user;
    }

    private function createInspectionRequest(User $customer, User $technician): InspectionRequest
    {
        return InspectionRequest::query()->create([
            'user_id' => $customer->id,
            'technician_id' => $technician->id,
            'details' => 'Completed site inspection',
            'status' => 'completed',
        ]);
    }

    private function createFinalQuotation(User $customer, InspectionRequest $inspectionRequest, array $overrides = []): Quotation
    {
        return Quotation::query()->create(array_merge([
            'user_id' => $customer->id,
            'inspection_request_id' => $inspectionRequest->id,
            'quotation_type' => 'final',
            'monthly_electric_bill' => 5000,
            'status' => 'pending',
        ], $overrides));
    }
}
