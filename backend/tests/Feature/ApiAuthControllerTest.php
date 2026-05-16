<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

        $response = $this->postJson('/api/login', [
            'email' => $customer->email,
            'password' => 'Password123!',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'message' => 'This customer account has been archived. Please contact support for assistance.',
            ]);
    }
}
