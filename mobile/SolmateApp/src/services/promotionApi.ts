import {apiGet} from './api';

export type Promotion = {
  id: number;
  title: string;
  description: string | null;
  image_url: string | null;
  start_date: string | null;
  end_date: string | null;
  is_active: boolean;
  created_at: string | null;
  updated_at: string | null;
};

type PromotionsResponse = {
  message?: string;
  data?: Promotion[];
};

export async function getActivePromotions(): Promise<Promotion[]> {
  const response = await apiGet<PromotionsResponse>('/public/promotions', false);
  return Array.isArray(response?.data) ? response.data : [];
}
