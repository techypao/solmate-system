<?php

namespace Tests\Feature;

use App\Models\InspectionRequest;
use App\Models\Quotation;
use App\Models\ServiceRequest;
use App\Models\Testimony;
use App\Models\User;
use App\Notifications\AdminCustomerDeleteRequestedNotification;
use App\Services\CustomerActivityLogger;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AdminCustomerListTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_customer_list_shows_latest_registered_customers_first(): void
    {
        $admin = User::query()->create([
            'name' => 'Admin User',
            'email' => 'admin_customers_order@example.com',
            'password' => 'password123',
            'role' => User::ROLE_ADMIN,
        ]);

        User::query()->create([
            'name' => 'Zulu Customer',
            'email' => 'zulu_customer@example.com',
            'password' => 'password123',
            'role' => User::ROLE_CUSTOMER,
        ]);

        User::query()->create([
            'name' => 'Alpha Customer',
            'email' => 'alpha_customer@example.com',
            'password' => 'password123',
            'role' => User::ROLE_CUSTOMER,
        ]);

        User::query()->create([
            'name' => 'Newest Customer',
            'email' => 'newest_customer@example.com',
            'password' => 'password123',
            'role' => User::ROLE_CUSTOMER,
        ]);

        User::query()->where('email', 'zulu_customer@example.com')->update([
            'created_at' => now()->subDays(3),
            'updated_at' => now()->subDays(3),
        ]);

        User::query()->where('email', 'alpha_customer@example.com')->update([
            'created_at' => now()->subDays(2),
            'updated_at' => now()->subDays(2),
        ]);

        User::query()->where('email', 'newest_customer@example.com')->update([
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.customers'));

        $response->assertOk();

        $customers = $response->viewData('customers');

        $this->assertNotNull($customers);
        $this->assertSame([
            'Newest Customer',
            'Alpha Customer',
            'Zulu Customer',
        ], $customers->pluck('name')->all());
    }

    public function test_customer_support_staff_can_view_customers_but_cannot_edit_them(): void
    {
        $support = User::query()->create([
            'name' => 'Support Staff',
            'email' => 'support_customers@example.com',
            'password' => 'password123',
            'role' => User::ROLE_ADMIN,
            'admin_role' => User::ADMIN_ROLE_SUPPORT,
            'email_verified_at' => now(),
        ]);

        $customer = User::query()->create([
            'name' => 'Readonly Customer',
            'email' => 'readonly_customer@example.com',
            'password' => 'password123',
            'role' => User::ROLE_CUSTOMER,
        ]);

        $this->actingAs($support)
            ->get(route('admin.customers'))
            ->assertOk()
            ->assertSee('Readonly Customer')
            ->assertDontSee(route('admin.customers.edit', $customer));

        $this->actingAs($support)
            ->get(route('admin.customers.edit', $customer))
            ->assertRedirect(route('dashboard'));
    }

    public function test_admin_can_archive_customer_and_archived_customer_moves_to_archived_list(): void
    {
        $admin = User::query()->create([
            'name' => 'Admin User',
            'email' => 'admin_archive_customer@example.com',
            'password' => 'password123',
            'role' => User::ROLE_ADMIN,
        ]);

        $customer = User::query()->create([
            'name' => 'Archive Me Customer',
            'email' => 'archive_me_customer@example.com',
            'password' => 'password123',
            'role' => User::ROLE_CUSTOMER,
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.customers.archive', $customer))
            ->assertRedirect(route('admin.customers'))
            ->assertSessionHas('status', 'Customer "Archive Me Customer" was archived successfully.');

        $this->assertDatabaseHas('users', [
            'id' => $customer->id,
            'role' => User::ROLE_CUSTOMER,
        ]);

        $this->assertNotNull($customer->fresh()->archived_at);

        $response = $this->actingAs($admin)
            ->get(route('admin.customers'));

        $response->assertOk();

        $activeCustomers = $response->viewData('customers');
        $archivedCustomers = $response->viewData('archivedCustomers');

        $this->assertNotNull($activeCustomers);
        $this->assertNotNull($archivedCustomers);
        $this->assertSame([], $activeCustomers->pluck('name')->all());
        $this->assertSame(['Archive Me Customer'], $archivedCustomers->pluck('name')->all());
    }

    public function test_admin_can_restore_archived_customer(): void
    {
        $admin = User::query()->create([
            'name' => 'Admin User',
            'email' => 'admin_restore_customer@example.com',
            'password' => 'password123',
            'role' => User::ROLE_ADMIN,
        ]);

        $customer = User::query()->create([
            'name' => 'Restore Me Customer',
            'email' => 'restore_me_customer@example.com',
            'password' => 'password123',
            'role' => User::ROLE_CUSTOMER,
            'is_archived' => true,
            'archived_at' => now()->subDay(),
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.customers.restore', $customer))
            ->assertRedirect(route('admin.customers'))
            ->assertSessionHas('status', 'Customer "Restore Me Customer" was restored successfully.');

        $restoredCustomer = $customer->fresh();

        $this->assertFalse($restoredCustomer->isArchivedCustomer());
        $this->assertNull($restoredCustomer->archived_at);
        $this->assertNotNull($restoredCustomer->last_login_at);
        $this->assertDatabaseHas('customer_archive_audits', [
            'user_id' => $customer->id,
            'action' => 'restored',
        ]);
    }

    public function test_admin_can_open_active_customer_details_with_history(): void
    {
        $admin = User::query()->create([
            'name' => 'Admin User',
            'email' => 'admin_customer_details@example.com',
            'password' => 'password123',
            'role' => User::ROLE_ADMIN,
        ]);

        $customer = User::query()->create([
            'name' => 'History Customer',
            'email' => 'history_customer@example.com',
            'password' => 'password123',
            'role' => User::ROLE_CUSTOMER,
            'contact_number' => '09171234567',
            'address' => 'Sample Address',
        ]);

        Quotation::query()->create([
            'user_id' => $customer->id,
            'quotation_type' => 'initial',
            'monthly_electric_bill' => 3500,
            'project_cost' => 120000,
            'status' => 'pending',
        ]);

        InspectionRequest::query()->create([
            'user_id' => $customer->id,
            'details' => 'Inspect my roof.',
            'status' => 'pending',
        ]);

        ServiceRequest::query()->create([
            'user_id' => $customer->id,
            'request_type' => 'maintenance',
            'details' => 'Check my inverter.',
            'status' => 'pending',
        ]);

        Testimony::query()->create([
            'user_id' => $customer->id,
            'rating' => 5,
            'message' => 'Great service.',
            'status' => Testimony::STATUS_PENDING,
        ]);

        app(CustomerActivityLogger::class)->record(
            customer: $customer,
            eventType: 'test_event',
            title: 'Tracked future action',
            description: 'This came from the activity log.',
            actor: $customer,
            subject: $customer,
        );

        $this->actingAs($admin)
            ->get(route('admin.customers.show', $customer))
            ->assertOk()
            ->assertSee('History Customer')
            ->assertSee('Customer Profile')
            ->assertSee('Activity Timeline')
            ->assertSee('Quotations')
            ->assertSee('Inspection Requests')
            ->assertSee('Service Requests')
            ->assertSee('Feedback')
            ->assertSee('Tracked future action');
    }

    public function test_archived_customer_details_are_not_viewable(): void
    {
        $admin = User::query()->create([
            'name' => 'Admin User',
            'email' => 'admin_archived_details@example.com',
            'password' => 'password123',
            'role' => User::ROLE_ADMIN,
        ]);

        $customer = User::query()->create([
            'name' => 'Archived Details Customer',
            'email' => 'archived_details_customer@example.com',
            'password' => 'password123',
            'role' => User::ROLE_CUSTOMER,
            'is_archived' => true,
            'archived_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get(route('admin.customers.show', $customer))
            ->assertNotFound();
    }

    public function test_customer_can_request_account_deletion_from_website(): void
    {
        Notification::fake();

        $admin = User::query()->create([
            'name' => 'Admin User',
            'email' => 'admin_delete_request@example.com',
            'password' => 'password123',
            'role' => User::ROLE_ADMIN,
        ]);

        $customer = User::query()->create([
            'name' => 'Delete Request Customer',
            'email' => 'delete_request_customer@example.com',
            'password' => 'password123',
            'role' => User::ROLE_CUSTOMER,
        ]);
        $customer->forceFill(['email_verified_at' => now()])->save();

        $reason = 'I no longer need my SolMate customer account.';

        $this->actingAs($customer)
            ->post(route('customer.account.delete-request'), [
                'reason' => $reason,
            ])
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('status', 'Your account deletion request was sent to the admin for review.');

        $customer->refresh();

        $this->assertNotNull($customer->delete_requested_at);
        $this->assertSame($reason, $customer->delete_request_reason);

        Notification::assertSentTo(
            $admin,
            AdminCustomerDeleteRequestedNotification::class,
            function (AdminCustomerDeleteRequestedNotification $notification) use ($admin, $customer, $reason): bool {
                $payload = $notification->toArray($admin);

                return $payload['type'] === 'admin_customer_delete_requested'
                    && $payload['entity_type'] === 'customer'
                    && $payload['entity_id'] === $customer->id
                    && $payload['target_screen'] === 'AdminCustomers'
                    && $payload['delete_request_reason'] === $reason;
            }
        );
    }

    public function test_public_delete_account_page_accepts_optional_reason(): void
    {
        Notification::fake();

        $admin = User::query()->create([
            'name' => 'Admin User',
            'email' => 'admin_public_delete_request@example.com',
            'password' => 'password123',
            'role' => User::ROLE_ADMIN,
        ]);

        $customer = User::query()->create([
            'name' => 'Public Delete Request Customer',
            'email' => 'public_delete_request_customer@example.com',
            'password' => 'password123',
            'role' => User::ROLE_CUSTOMER,
        ]);

        $this->get(route('delete-account'))
            ->assertOk()
            ->assertSee('Request deletion of your SolMate account')
            ->assertSee('Reason for account deletion (optional)')
            ->assertSee('Account deletion is permanent');

        $this->post(route('delete-account.store'), [
            'email' => 'PUBLIC_DELETE_REQUEST_CUSTOMER@example.com',
            'reason' => '',
        ])
            ->assertRedirect(route('delete-account'))
            ->assertSessionHas('status', 'Your account deletion request was successfully submitted. Our admin team will review it and process the request.');

        $customer->refresh();

        $this->assertNotNull($customer->delete_requested_at);
        $this->assertNull($customer->delete_request_reason);

        Notification::assertSentTo($admin, AdminCustomerDeleteRequestedNotification::class);
    }

    public function test_admin_customer_list_shows_account_delete_requests(): void
    {
        $admin = User::query()->create([
            'name' => 'Admin User',
            'email' => 'admin_delete_request_list@example.com',
            'password' => 'password123',
            'role' => User::ROLE_ADMIN,
        ]);

        $customer = User::query()->create([
            'name' => 'Visible Delete Request Customer',
            'email' => 'visible_delete_request_customer@example.com',
            'password' => 'password123',
            'role' => User::ROLE_CUSTOMER,
            'delete_requested_at' => now(),
            'delete_request_reason' => 'Please remove my unused account.',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.customers'))
            ->assertOk()
            ->assertSee('Delete requests')
            ->assertSee('Requested account deletion')
            ->assertSee('Please remove my unused account.')
            ->assertSee('customer-'.$customer->id);
    }
}
