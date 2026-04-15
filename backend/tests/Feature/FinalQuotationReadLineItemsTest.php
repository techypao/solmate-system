<?php

namespace Tests\Feature;

use App\Models\InspectionRequest;
use App\Models\PricingItem;
use App\Models\Quotation;
use App\Models\QuotationLineItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class FinalQuotationReadLineItemsTest extends TestCase
{
    use RefreshDatabase;

    public function test_show_returns_line_items_for_final_quotation_when_present(): void
    {
        $admin = $this->createUser(User::ROLE_ADMIN, 'admin');
        $customer = $this->createUser(User::ROLE_CUSTOMER, 'customer');
        $pricingItem = $this->createPricingItem();

        $quotation = Quotation::query()->create([
            'user_id' => $customer->id,
            'quotation_type' => 'final',
            'monthly_electric_bill' => 3000,
            'status' => 'pending',
        ]);

        QuotationLineItem::query()->create([
            'quotation_id' => $quotation->id,
            'pricing_item_id' => $pricingItem->id,
            'description' => 'Canadian Mono 585W Bifacial',
            'category' => 'panel',
            'qty' => 10,
            'unit' => 'pc',
            'unit_amount' => 10000,
            'total_amount' => 100000,
        ]);

        Sanctum::actingAs($admin);

        $response = $this->getJson("/api/quotations/{$quotation->id}");

        $response->assertOk()
            ->assertJsonPath('quotation_type', 'final')
            ->assertJsonPath('line_items.0.description', 'Canadian Mono 585W Bifacial')
            ->assertJsonPath('line_items.0.category', 'panel')
            ->assertJsonPath('line_items.0.total_amount', '100000.00')
            ->assertJsonPath('line_items.0.pricing_item.id', $pricingItem->id)
            ->assertJsonPath('line_items.0.pricing_item.name', 'Canadian Mono 585W Bifacial');
    }

    public function test_show_returns_empty_line_items_for_final_quotation_with_zero_line_items(): void
    {
        $technician = $this->createUser(User::ROLE_TECHNICIAN, 'technician');
        $customer = $this->createUser(User::ROLE_CUSTOMER, 'customer');

        $quotation = Quotation::query()->create([
            'user_id' => $customer->id,
            'quotation_type' => 'final',
            'monthly_electric_bill' => 2200,
            'status' => 'pending',
        ]);

        Sanctum::actingAs($technician);

        $response = $this->getJson("/api/quotations/{$quotation->id}");

        $response->assertOk()
            ->assertJsonPath('quotation_type', 'final')
            ->assertJsonPath('line_items', []);
    }

    public function test_show_keeps_initial_quotation_response_simplified(): void
    {
        $customer = $this->createUser(User::ROLE_CUSTOMER, 'customer');

        $quotation = Quotation::query()->create([
            'user_id' => $customer->id,
            'quotation_type' => 'initial',
            'monthly_electric_bill' => 1800,
            'status' => 'pending',
        ]);

        Sanctum::actingAs($customer);

        $response = $this->getJson("/api/quotations/{$quotation->id}");

        $response->assertOk()
            ->assertJsonPath('quotation_type', 'initial')
            ->assertJsonMissingPath('line_items');
    }

    public function test_customer_final_quotation_read_includes_line_items_when_present(): void
    {
        $customer = $this->createUser(User::ROLE_CUSTOMER, 'customer');
        $technician = $this->createUser(User::ROLE_TECHNICIAN, 'technician');
        $pricingItem = $this->createPricingItem();
        $inspectionRequest = InspectionRequest::query()->create([
            'user_id' => $customer->id,
            'technician_id' => $technician->id,
            'details' => 'Completed site visit',
            'status' => 'completed',
        ]);

        $quotation = Quotation::query()->create([
            'user_id' => $customer->id,
            'inspection_request_id' => $inspectionRequest->id,
            'quotation_type' => 'final',
            'monthly_electric_bill' => 2600,
            'status' => 'pending',
        ]);

        QuotationLineItem::query()->create([
            'quotation_id' => $quotation->id,
            'pricing_item_id' => $pricingItem->id,
            'description' => 'SRNE 6kW Hybrid',
            'category' => 'inverter',
            'qty' => 1,
            'unit' => 'set',
            'unit_amount' => 85000,
            'total_amount' => 85000,
        ]);

        Sanctum::actingAs($customer);

        $response = $this->getJson("/api/customer/final-quotations/{$inspectionRequest->id}");

        $response->assertOk()
            ->assertJsonPath('data.quotation_type', 'final')
            ->assertJsonPath('data.line_items.0.description', 'SRNE 6kW Hybrid')
            ->assertJsonPath('data.line_items.0.category', 'inverter')
            ->assertJsonPath('data.line_items.0.pricing_item.id', $pricingItem->id);
    }

    private function createPricingItem(): PricingItem
    {
        return PricingItem::query()->create([
            'name' => 'Canadian Mono 585W Bifacial',
            'category' => 'panel',
            'unit' => 'pc',
            'default_unit_price' => 10000,
            'brand' => 'Canadian',
            'model' => '585W',
            'specification' => 'Bifacial panel',
            'is_active' => true,
        ]);
    }

    private function createUser(string $role, string $prefix): User
    {
        return User::query()->create([
            'name' => ucfirst($role) . ' User',
            'email' => $prefix . '_' . uniqid() . '@example.com',
            'password' => Hash::make('password'),
            'role' => $role,
        ]);
    }
}
