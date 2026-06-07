<?php

namespace Tests\Feature;

use App\Models\Promotion;
use App\Models\Quotation;
use App\Models\QuotationLineItem;
use App\Models\User;
use App\Services\QuotationLineItemSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class QuotationLineItemSyncServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_refreshing_existing_line_items_recalculates_repeating_free_item_promo_discount(): void
    {
        $customer = User::query()->create([
            'name' => 'Customer User',
            'email' => 'customer_' . uniqid() . '@example.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_CUSTOMER,
        ]);

        $promotion = Promotion::query()->create([
            'title' => 'Buy 5 Get 1',
            'description' => 'Buy 5 panels and get 1 free.',
            'is_active' => true,
            'promo_type' => 'free_item',
            'conditions' => [
                'applies_to' => 'panel',
                'min_qty' => 5,
                'free_qty' => 1,
            ],
        ]);

        $quotation = Quotation::query()->create([
            'user_id' => $customer->id,
            'quotation_type' => 'final',
            'monthly_electric_bill' => 3000,
            'labor_cost' => 0,
            'status' => 'pending',
            'applied_promo_id' => $promotion->id,
            'promo_discount' => 100,
            'project_cost' => 1500,
        ]);

        QuotationLineItem::query()->create([
            'quotation_id' => $quotation->id,
            'description' => 'Solar Panel',
            'category' => 'panel',
            'qty' => 16,
            'unit' => 'pc',
            'unit_amount' => 100,
            'total_amount' => 1600,
        ]);

        $refreshed = app(QuotationLineItemSyncService::class)
            ->refreshTotalsFromExistingLineItems($quotation);

        $this->assertSame('200.00', $refreshed->promo_discount);
        $this->assertSame(1400, $refreshed->project_cost);
        $this->assertDatabaseHas('quotations', [
            'id' => $quotation->id,
            'promo_discount' => 200.00,
            'project_cost' => 1400.00,
        ]);
    }
}
