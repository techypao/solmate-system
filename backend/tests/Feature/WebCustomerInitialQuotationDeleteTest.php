<?php

namespace Tests\Feature;

use App\Models\PreInspectionQuotationOption;
use App\Models\Quotation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class WebCustomerInitialQuotationDeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_delete_their_own_pre_inspection_estimate_from_web(): void
    {
        $customer = $this->createVerifiedCustomer('customer');
        $quotation = $this->createQuotation($customer, 'initial');

        PreInspectionQuotationOption::query()->create([
            'quotation_id' => $quotation->id,
            'system_type' => 'on-grid',
            'with_battery' => false,
            'project_cost' => 100000,
        ]);

        $this->actingAs($customer)
            ->deleteJson("/customer/quotation/{$quotation->id}")
            ->assertOk()
            ->assertJsonPath('message', 'Pre-inspection estimate deleted successfully.');

        $this->assertDatabaseMissing('quotations', [
            'id' => $quotation->id,
        ]);

        $this->assertDatabaseMissing('pre_inspection_quotation_options', [
            'quotation_id' => $quotation->id,
        ]);
    }

    public function test_customer_cannot_delete_inspection_based_quotation_from_web(): void
    {
        $customer = $this->createVerifiedCustomer('customer');
        $quotation = $this->createQuotation($customer, 'final');

        $this->actingAs($customer)
            ->deleteJson("/customer/quotation/{$quotation->id}")
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Only pre-inspection estimates can be deleted.');

        $this->assertDatabaseHas('quotations', [
            'id' => $quotation->id,
            'quotation_type' => 'final',
        ]);
    }

    public function test_customer_cannot_delete_another_customers_pre_inspection_estimate(): void
    {
        $owner = $this->createVerifiedCustomer('owner');
        $otherCustomer = $this->createVerifiedCustomer('other');
        $quotation = $this->createQuotation($owner, 'initial');

        $this->actingAs($otherCustomer)
            ->deleteJson("/customer/quotation/{$quotation->id}")
            ->assertForbidden();

        $this->assertDatabaseHas('quotations', [
            'id' => $quotation->id,
            'quotation_type' => 'initial',
        ]);
    }

    private function createVerifiedCustomer(string $prefix): User
    {
        $customer = User::query()->create([
            'name' => ucfirst($prefix) . ' Customer',
            'email' => $prefix . '_' . uniqid() . '@example.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_CUSTOMER,
        ]);

        $customer->forceFill(['email_verified_at' => now()])->save();

        return $customer;
    }

    private function createQuotation(User $customer, string $type): Quotation
    {
        return Quotation::query()->create([
            'user_id' => $customer->id,
            'quotation_type' => $type,
            'monthly_electric_bill' => 5000,
            'pv_system_type' => $type === 'final' ? 'hybrid' : 'initial',
            'with_battery' => true,
            'status' => 'pending',
        ]);
    }
}
