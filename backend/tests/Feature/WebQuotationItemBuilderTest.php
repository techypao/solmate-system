<?php

namespace Tests\Feature;

use App\Models\InspectionRequest;
use App\Models\Quotation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
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

    public function test_builder_page_displays_completed_when_linked_inspection_is_completed_even_if_quotation_is_still_pending(): void
    {
        $admin = User::query()->create([
            'name' => 'Admin Builder User',
            'email' => 'admin_builder_status@example.com',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_ADMIN,
        ]);

        $customer = User::query()->create([
            'name' => 'Jose Rizal',
            'email' => 'jose_rizal_builder@example.com',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_CUSTOMER,
        ]);

        $technician = User::query()->create([
            'name' => 'Technician Builder User',
            'email' => 'technician_builder_status@example.com',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_TECHNICIAN,
        ]);

        $inspectionRequest = InspectionRequest::query()->create([
            'user_id' => $customer->id,
            'technician_id' => $technician->id,
            'details' => 'Completed site inspection',
            'status' => 'completed',
        ]);

        $quotation = Quotation::query()->create([
            'user_id' => $customer->id,
            'inspection_request_id' => $inspectionRequest->id,
            'quotation_type' => 'final',
            'monthly_electric_bill' => 4200,
            'status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->get('/quotations/item-builder')
            ->assertOk()
            ->assertSee("#{$quotation->id}")
            ->assertSee('Back to available quotation IDs')
            ->assertSee('Completed')
            ->assertDontSee('Pending');
    }
}
