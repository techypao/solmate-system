<?php

namespace Tests\Unit;

use App\Models\Promotion;
use App\Services\PromotionDiscountService;
use PHPUnit\Framework\TestCase;

class PromotionDiscountServiceTest extends TestCase
{
    public function test_free_item_discount_repeats_for_each_complete_promo_set(): void
    {
        $promotion = new Promotion([
            'promo_type' => 'free_item',
            'conditions' => [
                'applies_to' => 'panel',
                'min_qty' => 5,
                'free_qty' => 1,
            ],
        ]);

        $discount = (new PromotionDiscountService())->compute($promotion, 1600, [
            'panel_qty' => 16,
            'panel_unit_price' => 100,
        ]);

        $this->assertSame(200.0, $discount);
    }
}
