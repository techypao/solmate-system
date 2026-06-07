<?php

namespace App\Services;

use App\Models\Promotion;

class PromotionDiscountService
{
    public function compute(?Promotion $promo, float $projectCost, array $context = []): ?float
    {
        if (! $promo || $projectCost <= 0) {
            return null;
        }

        $value = (float) ($promo->discount_value ?? 0);

        if ($value <= 0 && $promo->promo_type !== 'free_item') {
            return null;
        }

        return match ($promo->promo_type) {
            'percentage' => round($projectCost * ($value / 100), 2),
            'fixed_amount', 'bundle' => round(min($value, $projectCost), 2),
            'free_item' => $this->computeFreeItemDiscount($promo, $projectCost, $value, $context),
            default => null,
        };
    }

    public function buildContextFromLineItems(iterable $lineItems): array
    {
        $aggregates = [];

        foreach ($lineItems as $lineItem) {
            $category = trim((string) ($lineItem['category'] ?? ''));
            $qty = (float) ($lineItem['qty'] ?? 0);

            if ($category === '' || $qty <= 0) {
                continue;
            }

            $unitAmount = (float) ($lineItem['unit_amount'] ?? 0);
            $totalAmount = isset($lineItem['total_amount'])
                ? (float) $lineItem['total_amount']
                : round($qty * $unitAmount, 2);

            if (! isset($aggregates[$category])) {
                $aggregates[$category] = [
                    'qty' => 0.0,
                    'total' => 0.0,
                ];
            }

            $aggregates[$category]['qty'] += $qty;
            $aggregates[$category]['total'] += $totalAmount;
        }

        $context = [];

        foreach ($aggregates as $category => $aggregate) {
            if ($aggregate['qty'] <= 0) {
                continue;
            }

            $context[$category . '_qty'] = round($aggregate['qty'], 2);
            $context[$category . '_unit_price'] = round($aggregate['total'] / $aggregate['qty'], 2);
        }

        return $context;
    }

    private function computeFreeItemDiscount(Promotion $promo, float $projectCost, float $fallbackValue, array $context): ?float
    {
        $conditions = $promo->conditions ?? [];
        $appliesTo = $conditions['applies_to'] ?? null;
        $minQty = (float) ($conditions['min_qty'] ?? 0);
        $freeQty = (int) ($conditions['free_qty'] ?? 1);

        if ($appliesTo && $minQty > 0) {
            $actualQty = (float) ($context[$appliesTo . '_qty'] ?? 0);
            $unitPrice = (float) ($context[$appliesTo . '_unit_price'] ?? 0);

            if ($actualQty < $minQty || $unitPrice <= 0) {
                return null;
            }

            $promoSetQty = $minQty + $freeQty;
            $eligibleFreeQty = (int) floor($actualQty / $promoSetQty) * $freeQty;

            if ($eligibleFreeQty <= 0) {
                return null;
            }

            return round(min($eligibleFreeQty * $unitPrice, $projectCost), 2);
        }

        return $fallbackValue > 0 ? round(min($fallbackValue, $projectCost), 2) : null;
    }
}
