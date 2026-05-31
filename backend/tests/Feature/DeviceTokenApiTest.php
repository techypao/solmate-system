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
            ->postJson('/api/save-device-token', [
                'fcm_token' => 'firebase-device-token-123',
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

    public function test_non_admin_user_cannot_save_another_users_fcm_token(): void
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
            ->postJson('/api/save-device-token', [
                'user_id' => $otherUser->id,
                'fcm_token' => 'firebase-device-token-456',
            ])
            ->assertStatus(403);

        $this->assertDatabaseHas('users', [
            'id' => $otherUser->id,
            'fcm_token' => null,
        ]);
    }
}