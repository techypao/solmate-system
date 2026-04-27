<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
