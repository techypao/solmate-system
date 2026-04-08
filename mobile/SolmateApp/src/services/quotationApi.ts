import {apiGet, apiPost, apiPut} from './api';

export type Quotation = {
  id: number;
  quotation_type: 'initial' | 'final';
  status: 'pending' | 'approved' | 'rejected' | 'completed';
  monthly_electric_bill?: number | null;
  panel_watts?: number | null;
  inverter_type?: string | null;
  battery_model?: string | null;
  battery_capacity_ah?: number | null;
  panel_cost?: number | null;
  inverter_cost?: number | null;
  battery_cost?: number | null;
  bos_cost?: number | null;
  materials_subtotal?: number | null;
  labor_cost?: number | null;
  project_cost?: number | null;
  remarks?: string | null;
  created_at?: string;
};

export type CreateQuotationPayload = {
  quotation_type: 'initial' | 'final';
  monthly_electric_bill?: number;
  rate_per_kwh?: number;
  days_in_month?: number;
  sun_hours?: number;
  pv_safety_factor?: number;
  battery_factor?: number;
  battery_voltage?: number;
  pv_system_type?: 'hybrid' | 'on-grid' | 'off-grid';
  with_battery?: boolean;
  inverter_type?: string;
  battery_model?: string;
  battery_capacity_ah?: number;
  panel_watts?: number;
  remarks?: string;
};

export type UpdateQuotationPayload = {
  quotation_type?: 'initial' | 'final';
  panel_watts?: number;
  inverter_type?: string;
  battery_model?: string;
  battery_capacity_ah?: number;
  status?: 'pending' | 'approved' | 'rejected' | 'completed';
  panel_cost?: number;
  inverter_cost?: number;
  battery_cost?: number;
  bos_cost?: number;
  materials_subtotal?: number;
  labor_cost?: number;
  project_cost?: number;
  remarks?: string;
};

type QuotationResponse = {
  message: string;
  data: Quotation;
};

export function getQuotations() {
  return apiGet<Quotation[]>('/quotations');
}

export function getQuotationById(id: number) {
  return apiGet<Quotation>(`/quotations/${id}`);
}

export function createQuotation(payload: CreateQuotationPayload) {
  return apiPost<QuotationResponse>('/quotations', payload);
}

export function updateQuotation(
  id: number,
  payload: UpdateQuotationPayload,
) {
  return apiPut<QuotationResponse>(`/quotations/${id}`, payload);
}
