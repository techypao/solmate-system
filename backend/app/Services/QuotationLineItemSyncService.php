<?php

namespace App\Services;

use App\Models\Quotation;
use Illuminate\Support\Facades\DB;

class QuotationLineItemSyncService
{
    public function __construct(
        private QuotationComputationService $quotationComputationService,
        private PromotionDiscountService $promotionDiscountService
    ) {
    }

    public function replaceForQuotation(Quotation $quotation, array $lineItems): Quotation
    {
        return DB::transaction(function () use ($quotation, $lineItems) {
            $quotation->lineItems()->delete();

            foreach ($lineItems as $lineItem) {
                $qty = round((float) $lineItem['qty'], 2);
                $unitAmount = round((float) $lineItem['unit_amount'], 2);
                $totalAmount = round($qty * $unitAmount, 2);

                $quotation->lineItems()->create([
                    'pricing_item_id' => $lineItem['pricing_item_id'] ?? null,
                    'description' => $lineItem['description'],
                    'category' => $lineItem['category'],
                    'qty' => $qty,
                    'unit' => $lineItem['unit'],
                    'unit_amount' => $unitAmount,
                    'total_amount' => $totalAmount,
                ]);
            }

            return $this->updateQuotationTotalsFromLineItems($quotation, $lineItems);
        });
    }

    public function refreshTotalsFromExistingLineItems(Quotation $quotation): Quotation
    {
        $quotation->loadMissing(['lineItems', 'appliedPromo']);

        if ($quotation->lineItems->isEmpty()) {
            return $quotation;
        }

        $lineItems = $quotation->lineItems
            ->map(fn ($lineItem): array => [
                'pricing_item_id' => $lineItem->pricing_item_id,
                'description' => $lineItem->description,
                'category' => $lineItem->category,
                'qty' => $lineItem->qty,
                'unit' => $lineItem->unit,
                'unit_amount' => $lineItem->unit_amount,
            ])
            ->all();

        return DB::transaction(function () use ($quotation, $lineItems) {
            return $this->updateQuotationTotalsFromLineItems($quotation, $lineItems);
        });
    }

    private function updateQuotationTotalsFromLineItems(Quotation $quotation, array $lineItems): Quotation
    {
        $totals = [
            'panel_cost' => 0.00,
            'inverter_cost' => 0.00,
            'battery_cost' => 0.00,
            'bos_cost' => 0.00,
            'materials_subtotal' => 0.00,
        ];

        foreach ($lineItems as $lineItem) {
            $qty = round((float) $lineItem['qty'], 2);
            $unitAmount = round((float) $lineItem['unit_amount'], 2);
            $totalAmount = round($qty * $unitAmount, 2);

            $totals['materials_subtotal'] += $totalAmount;

            switch ($lineItem['category']) {
                case 'panel':
                    $totals['panel_cost'] += $totalAmount;
                    break;
                case 'inverter':
                    $totals['inverter_cost'] += $totalAmount;
                    break;
                case 'battery':
                    $totals['battery_cost'] += $totalAmount;
                    break;
                default:
                    $totals['bos_cost'] += $totalAmount;
                    break;
            }
        }

        $panelCost = round($totals['panel_cost'], 2);
        $inverterCost = round($totals['inverter_cost'], 2);
        $batteryCost = round($totals['battery_cost'], 2);
        $bosCost = round($totals['bos_cost'], 2);
        $materialsSubtotal = round($totals['materials_subtotal'], 2);
        $laborCost = round((float) ($quotation->labor_cost ?? 0), 2);
        $baseProjectCost = round($materialsSubtotal + $laborCost, 2);
        $promoContext = $this->promotionDiscountService->buildContextFromLineItems($lineItems);
        $promoDiscount = $this->promotionDiscountService->compute(
            $quotation->appliedPromo,
            $baseProjectCost,
            $promoContext
        );
        $promoAdjustedProjectCost = round(max(0, $baseProjectCost - (float) ($promoDiscount ?? 0)), 2);
        $adminDiscount = round((float) ($quotation->admin_discount_amount ?? 0), 2);
        $projectCost = round(max(0, $promoAdjustedProjectCost - $adminDiscount), 2);

        $roiValues = $this->quotationComputationService->computeRoi(
            $projectCost,
            (float) ($quotation->monthly_electric_bill ?? 0)
        );

        $quotation->update([
            'panel_cost' => $panelCost,
            'inverter_cost' => $inverterCost,
            'battery_cost' => $batteryCost,
            'bos_cost' => $bosCost,
            'materials_subtotal' => $materialsSubtotal,
            'labor_cost' => $laborCost,
            'promo_discount' => $promoDiscount,
            'project_cost' => $projectCost,
            'estimated_monthly_savings' => $roiValues['estimated_monthly_savings'],
            'estimated_annual_savings' => $roiValues['estimated_annual_savings'],
            'roi_years' => $roiValues['roi_years'],
        ]);

        return $quotation->fresh(['lineItems.pricingItem', 'appliedPromo']);
    }
}
