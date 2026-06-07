import {apiGet} from './api';

export type PromoType = 'percentage' | 'fixed_amount' | 'free_item' | 'bundle';

export type Promotion = {
  id: number;
  title: string;
  description: string | null;
  image_url: string | null;
  start_date: string | null;
  end_date: string | null;
  is_active: boolean;
  promo_type: PromoType | null;
  discount_value: number | null;
  free_item_description: string | null;
  conditions: Record<string, unknown> | null;
  created_at: string | null;
  updated_at: string | null;
};

/** Minimal line-item info needed for condition-based promo evaluation. */
export type PromoLineItemContext = {
  category: string;
  qty: number;
  unit_price: number;
}[];

type PromotionsResponse = {
  message?: string;
  data?: Promotion[];
};

export async function getActivePromotions(): Promise<Promotion[]> {
  const response = await apiGet<PromotionsResponse>('/public/promotions', false);
  return Array.isArray(response?.data) ? response.data : [];
}

/**
 * Returns the condition status for a promo given the current line items.
 * Used to show "requires X panels" hints in the promo selector UI.
 */
export function getPromoConditionStatus(
  promo: Promotion,
  lineItems: PromoLineItemContext,
): {conditionRequired: boolean; conditionMet: boolean; message: string | null} {
  if (promo.promo_type !== 'free_item') {
    return {conditionRequired: false, conditionMet: true, message: null};
  }

  const conditions = promo.conditions;
  const appliesTo = conditions?.applies_to as string | undefined;
  const minQty = Number(conditions?.min_qty ?? 0);
  const freeQty = Number(conditions?.free_qty ?? 1);

  if (!appliesTo || minQty <= 0 || freeQty <= 0) {
    return {conditionRequired: false, conditionMet: true, message: null};
  }

  const totalQty = lineItems
    .filter(i => i.category === appliesTo)
    .reduce((sum, i) => sum + i.qty, 0);
  const promoSetQty = minQty + freeQty;
  const eligibleFreeQty = Math.floor(totalQty / promoSetQty) * freeQty;
  const met = eligibleFreeQty > 0;

  return {
    conditionRequired: true,
    conditionMet: met,
    message: met
      ? `Eligible for ${eligibleFreeQty} free ${appliesTo}${eligibleFreeQty !== 1 ? 's' : ''}`
      : `Requires at least ${promoSetQty} total ${appliesTo}${promoSetQty !== 1 ? 's' : ''} - currently ${totalQty}`,
  };
}

/**
 * Compute the PHP-peso discount amount for a given promo and base project cost.
 * Pass lineItems for item-based free_item conditions (buy N get M free).
 * Returns 0 when the promo has no applicable discount or condition is not met.
 */
export function computePromoDiscount(
  promo: Promotion,
  projectCost: number,
  lineItems: PromoLineItemContext = [],
): number {
  const value = promo.discount_value ?? 0;

  if (value <= 0 && promo.promo_type !== 'free_item') {
    return 0;
  }

  switch (promo.promo_type) {
    case 'percentage':
      return Number((projectCost * (value / 100)).toFixed(2));
    case 'fixed_amount':
    case 'bundle':
      return Number(Math.min(value, projectCost).toFixed(2));
    case 'free_item': {
      const conditions = promo.conditions;
      const appliesTo = conditions?.applies_to as string | undefined;
      const minQty = Number(conditions?.min_qty ?? 0);
      const freeQty = Number(conditions?.free_qty ?? 1);

      if (appliesTo && minQty > 0) {
        const matching = lineItems.filter(i => i.category === appliesTo);
        const totalQty = matching.reduce((sum, i) => sum + i.qty, 0);
        const unitPrice = matching[0]?.unit_price ?? 0;

        if (totalQty < minQty || unitPrice <= 0) {
          return 0; // condition not met
        }

        const promoSetQty = minQty + freeQty;
        const eligibleFreeQty = Math.floor(totalQty / promoSetQty) * freeQty;

        if (eligibleFreeQty <= 0) {
          return 0; // condition not met
        }

        return Number(Math.min(eligibleFreeQty * unitPrice, projectCost).toFixed(2));
      }

      return value > 0 ? Number(Math.min(value, projectCost).toFixed(2)) : 0;
    }
    default:
      return 0;
  }
}

export function getPromoTypeLabel(promoType: PromoType | null): string {
  switch (promoType) {
    case 'percentage':
      return 'Percentage Discount';
    case 'fixed_amount':
      return 'Fixed Discount';
    case 'free_item':
      return 'Free Item Promo';
    case 'bundle':
      return 'Bundle Deal';
    default:
      return 'Promo';
  }
}
