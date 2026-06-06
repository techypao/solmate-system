<?php

namespace Tests\Feature;

use App\Notifications\CustomerArchiveWarningNotification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ApiAuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_archived_customer_cannot_log_in_via_api(): void
    {
        $customer = User::query()->create([
            'name' => 'Archived API Customer',
            'email' => 'archived_api_customer@example.com',
            'password' => 'Password123!',
            'role' => User::ROLE_CUSTOMER,
            'archived_at' => now(),
        ]);
        $customer->forceFill(['email_verified_at' => now()])->save();

        $response = $this->postJson('/api/login', [
            'email' => $customer->email,
            'password' => 'Password123!',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'message' => User::archivedAccountMessage(),
            ]);
    }

    public function test_api_login_records_last_login_at_for_active_customer(): void
    {
        $customer = User::query()->create([
            'name' => 'API Login Customer',
            'email' => 'api_login_customer@example.com',
            'password' => 'Password123!',
            'role' => User::ROLE_CUSTOMER,
        ]);
        $customer->forceFill(['email_verified_at' => now()])->save();

        $this->postJson('/api/login', [
            'email' => $customer->email,
            'password' => 'Password123!',
        ])->assertOk();

        $this->assertNotNull($customer->fresh()->last_login_at);
    }

    public function test_unverified_customer_cannot_log_in_via_api(): void
    {
        $customer = User::query()->create([
            'name' => 'Unverified API Customer',
            'email' => 'unverified_api_customer@example.com',
            'password' => 'Password123!',
            'role' => User::ROLE_CUSTOMER,
            'email_verified_at' => null,
        ]);

        $this->postJson('/api/login', [
            'email' => $customer->email,
            'password' => 'Password123!',
        ])
            ->assertStatus(403)
            ->assertJson([
                'message' => 'EMAIL_NOT_VERIFIED',
                'error' => 'Please verify your email before logging in.',
            ]);
    }

    public function test_archived_customer_with_existing_token_is_blocked_from_authenticated_api_routes(): void
    {
        $customer = User::query()->create([
            'name' => 'Archived Token Customer',
            'email' => 'archived_token_customer@example.com',
            'password' => 'Password123!',
            'role' => User::ROLE_CUSTOMER,
            'is_archived' => true,
            'archived_at' => now(),
        ]);

        $token = $customer->createToken('auth_token')->plainTextToken;

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/user')
            ->assertStatus(403)
            ->assertJson([
                'message' => User::archivedAccountMessage(),
            ]);
    }

    public function test_inactive_customer_archival_command_sends_warning_and_archives_due_accounts(): void
    {
        Notification::fake();

        $warningCustomer = User::query()->create([
            'name' => 'Warning Customer',
            'email' => 'warning_customer@example.com',
            'password' => 'Password123!',
            'role' => User::ROLE_CUSTOMER,
            'last_login_at' => now()->subDays(53),
        ]);

        $archivedCustomer = User::query()->create([
            'name' => 'Archive Customer',
            'email' => 'archive_customer@example.com',
            'password' => 'Password123!',
            'role' => User::ROLE_CUSTOMER,
            'last_login_at' => now()->subDays(61),
        ]);

        $archivedCustomer->createToken('auth_token');

        $admin = User::query()->create([
            'name' => 'Admin User',
            'email' => 'archive_command_admin@example.com',
            'password' => 'Password123!',
            'role' => User::ROLE_ADMIN,
            'last_login_at' => now()->subDays(120),
        ]);

        Artisan::call('customers:archive-inactive');

        Notification::assertSentTo($warningCustomer, CustomerArchiveWarningNotification::class);

        $this->assertNotNull($warningCustomer->fresh()->archive_warning_sent_at);
        $this->assertTrue($archivedCustomer->fresh()->isArchivedCustomer());
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $archivedCustomer->id,
            'tokenable_type' => User::class,
        ]);
        $this->assertDatabaseHas('customer_archive_audits', [
            'user_id' => $warningCustomer->id,
            'action' => 'warning_sent',
        ]);
        $this->assertDatabaseHas('customer_archive_audits', [
            'user_id' => $archivedCustomer->id,
            'action' => 'archived',
        ]);
        $this->assertDatabaseMissing('customer_archive_audits', [
            'user_id' => $admin->id,
            'action' => 'archived',
        ]);
    }
}
