<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebQuotationItemBuilderTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_and_technician_can_open_quotation_item_builder_page(): void
    {
        $admin = User::query()->create([
            'name' => 'Admin Builder User',
            'email' => 'admin_builder@example.com',
            'password' => 'password123',
            'role' => User::ROLE_ADMIN,
        ]);

        $technician = User::query()->create([
            'name' => 'Technician Builder User',
            'email' => 'technician_builder@example.com',
            'password' => 'password123',
            'role' => User::ROLE_TECHNICIAN,
        ]);

        $this->actingAs($admin)
            ->get('/quotations/item-builder')
            ->assertOk()
            ->assertSee('Quotation Item Builder')
            ->assertSee('Quotation ID')
            ->assertSee('Save line items');

        $this->actingAs($technician)
            ->get('/quotations/item-builder')
            ->assertOk()
            ->assertSee('Quotation Item Builder')
            ->assertSee('Load quotation');
    }

    public function test_customer_cannot_open_quotation_item_builder_page(): void
    {
        $customer = User::query()->create([
            'name' => 'Customer Builder User',
            'email' => 'customer_builder@example.com',
            'password' => 'password123',
            'role' => User::ROLE_CUSTOMER,
        ]);

        $this->actingAs($customer)
            ->get('/quotations/item-builder')
            ->assertForbidden();
    }
}
