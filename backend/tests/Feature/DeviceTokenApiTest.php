<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeviceTokenApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_save_their_fcm_token(): void
    {
        $user = User::query()->create([
            'name' => 'Push User',
            'email' => 'push_user@example.com',
            'password' => 'Password123!',
            'role' => User::ROLE_CUSTOMER,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/save-fcm-token', [
                'token' => 'firebase-device-token-123',
            ])
            ->assertOk()
            ->assertJson([
                'message' => 'Device token saved successfully.',
                'user_id' => $user->id,
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'fcm_token' => 'firebase-device-token-123',
        ]);
    }

    public function test_saving_a_token_removes_the_same_token_from_other_users(): void
    {
        $previousUser = User::query()->create([
            'name' => 'Previous Push User',
            'email' => 'previous_push_user@example.com',
            'password' => 'Password123!',
            'role' => User::ROLE_CUSTOMER,
            'fcm_token' => 'shared-firebase-device-token',
        ]);

        $currentUser = User::query()->create([
            'name' => 'Current Push User',
            'email' => 'current_push_user@example.com',
            'password' => 'Password123!',
            'role' => User::ROLE_CUSTOMER,
        ]);

        $token = $currentUser->createToken('auth_token')->plainTextToken;

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/save-fcm-token', [
                'token' => 'shared-firebase-device-token',
            ])
            ->assertOk();

        $this->assertDatabaseHas('users', [
            'id' => $previousUser->id,
            'fcm_token' => null,
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $currentUser->id,
            'fcm_token' => 'shared-firebase-device-token',
        ]);
    }

    public function test_save_fcm_token_ignores_a_spoofed_user_id(): void
    {
        $user = User::query()->create([
            'name' => 'Push User',
            'email' => 'push_user_denied@example.com',
            'password' => 'Password123!',
            'role' => User::ROLE_CUSTOMER,
        ]);

        $otherUser = User::query()->create([
            'name' => 'Other Push User',
            'email' => 'other_push_user@example.com',
            'password' => 'Password123!',
            'role' => User::ROLE_CUSTOMER,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/save-fcm-token', [
                'user_id' => $otherUser->id,
                'token' => 'firebase-device-token-456',
            ])
            ->assertOk()
            ->assertJson([
                'user_id' => $user->id,
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'fcm_token' => 'firebase-device-token-456',
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $otherUser->id,
            'fcm_token' => null,
        ]);
    }

    public function test_authenticated_user_can_remove_their_fcm_token(): void
    {
        $user = User::query()->create([
            'name' => 'Push User',
            'email' => 'push_user_remove@example.com',
            'password' => 'Password123!',
            'role' => User::ROLE_CUSTOMER,
            'fcm_token' => 'firebase-device-token-789',
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/remove-fcm-token')
            ->assertOk()
            ->assertJson([
                'message' => 'Device token removed successfully.',
                'user_id' => $user->id,
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'fcm_token' => null,
        ]);
    }

    public function test_logout_clears_the_authenticated_users_fcm_token(): void
    {
        $user = User::query()->create([
            'name' => 'Push User',
            'email' => 'push_user_logout@example.com',
            'password' => 'Password123!',
            'role' => User::ROLE_CUSTOMER,
            'fcm_token' => 'firebase-device-token-logout',
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/logout')
            ->assertOk();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'fcm_token' => null,
        ]);
    }
}
