<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Cookie;
use Tests\TestCase;

class WebAdminApiSessionAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'app.url' => 'http://127.0.0.1:8000',
        ]);
    }

    public function test_admin_web_session_can_load_notifications_from_localhost_host(): void
    {
        $admin = $this->createAdminWithNotification();
        $sessionCookie = $this->loginThroughWebSession($admin, 'localhost:8000');

        $this->withServerVariables([
            'HTTP_HOST' => 'localhost:8000',
            'HTTP_REFERER' => 'http://localhost:8000/admin/notifications',
        ])->withCookie($sessionCookie->getName(), $sessionCookie->getValue())
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->getJson('/api/notifications')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.type', 'admin_new_service_request');
    }

    public function test_admin_web_session_remains_authenticated_for_api_requests_without_referer_header(): void
    {
        $admin = $this->createAdminWithNotification();
        $sessionCookie = $this->loginThroughWebSession($admin, 'localhost:8000');

        $this->withServerVariables([
            'HTTP_HOST' => 'localhost:8000',
        ])->withCookie($sessionCookie->getName(), $sessionCookie->getValue())
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->getJson('/api/notifications')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data');
    }

    public function test_non_admin_web_session_stays_blocked_from_admin_only_api_routes(): void
    {
        $customer = User::query()->create([
            'name' => 'Customer User',
            'email' => 'web_customer_session@example.com',
            'password' => 'Password123!',
            'role' => User::ROLE_CUSTOMER,
            'address' => 'Customer Street',
            'contact_number' => '09171230000',
        ]);

        $sessionCookie = $this->loginThroughWebSession($customer, 'localhost:8000');

        $this->withServerVariables([
            'HTTP_HOST' => 'localhost:8000',
            'HTTP_REFERER' => 'http://localhost:8000/admin/notifications',
        ])->withCookie($sessionCookie->getName(), $sessionCookie->getValue())
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->getJson('/api/admin-only')
            ->assertForbidden();
    }

    private function createAdminWithNotification(): User
    {
        $admin = User::query()->create([
            'name' => 'Web Admin',
            'email' => 'web_admin_session@example.com',
            'password' => 'Password123!',
            'role' => User::ROLE_ADMIN,
            'address' => 'Admin Street',
            'contact_number' => '09171234567',
        ]);

        DB::table('notifications')->insert([
            'id' => (string) Str::uuid(),
            'type' => 'App\\Notifications\\AdminNewServiceRequestNotification',
            'notifiable_type' => User::class,
            'notifiable_id' => $admin->id,
            'data' => json_encode([
                'type' => 'admin_new_service_request',
                'title' => 'New Service Request',
                'message' => 'A customer submitted service request #101.',
                'entity_type' => 'service_request',
                'entity_id' => 101,
                'target_screen' => 'AdminServiceRequestDetails',
                'target_params' => ['requestId' => 101],
                'status' => 'pending',
                'created_by' => $admin->id,
                'created_at_display' => now()->format('M d, Y h:i A'),
            ], JSON_THROW_ON_ERROR),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $admin;
    }

    private function loginThroughWebSession(User $user, string $host): Cookie
    {
        $token = 'web-session-token';

        $response = $this->withServerVariables([
            'HTTP_HOST' => $host,
        ])->withSession([
            '_token' => $token,
        ])->post('/login', [
            '_token' => $token,
            'email' => $user->email,
            'password' => 'Password123!',
        ]);

        $expectedRedirect = $user->role === User::ROLE_ADMIN ? '/dashboard' : '/';

        $response->assertRedirect($expectedRedirect);

        $sessionCookie = $response->getCookie(config('session.cookie'));

        $this->assertNotNull($sessionCookie, 'The login response did not set a session cookie.');

        return $sessionCookie;
    }
}
