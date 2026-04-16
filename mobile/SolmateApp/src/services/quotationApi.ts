import {apiGet, apiPost, apiPut} from './api';
import {InspectionRequest, UserSummary} from './inspectionRequestApi';

export type PvSystemType = 'hybrid' | 'on-grid' | 'off-grid';
export type QuotationType = 'initial' | 'final';
export type QuotationStatus =
  | 'pending'
  | 'approved'
  | 'rejected'
  | 'completed'
  | string;

export type PricingItemSummary = {
  id: number;
  name?: string | null;
  category?: string | null;
  unit?: string | null;
  default_unit_price?: number | null;
  brand?: string | null;
  model?: string | null;
  specification?: string | null;
  is_active?: boolean | null;
};

export type QuotationLineItem = {
  id: number;
  pricing_item_id?: number | null;
  description?: string | null;
  category?: string | null;
  qty?: number | null;
  unit?: string | null;
  unit_amount?: number | null;
  total_amount?: number | null;
  pricing_item?: PricingItemSummary | null;
};

export type Quotation = {
  id: number;
  user_id?: number;
  inspection_request_id?: number | null;
  quotation_type: QuotationType;
  status: QuotationStatus;
  monthly_electric_bill?: number | null;
  rate_per_kwh?: number | null;
  days_in_month?: number | null;
  sun_hours?: number | null;
  pv_safety_factor?: number | null;
  battery_factor?: number | null;
  battery_voltage?: number | null;
  pv_system_type?: PvSystemType | string | null;
  with_battery?: boolean | null;
  inverter_type?: string | null;
  battery_model?: string | null;
  battery_capacity_ah?: number | null;
  panel_watts?: number | null;
  monthly_kwh?: number | null;
  daily_kwh?: number | null;
  pv_kw_raw?: number | null;
  pv_kw_safe?: number | null;
  panel_quantity?: number | null;
  system_kw?: number | null;
  battery_required_kwh?: number | null;
  battery_required_ah?: number | null;
  panel_cost?: number | null;
  inverter_cost?: number | null;
  battery_cost?: number | null;
  bos_cost?: number | null;
  materials_subtotal?: number | null;
  labor_cost?: number | null;
  project_cost?: number | null;
  estimated_monthly_savings?: number | null;
  estimated_annual_savings?: number | null;
  roi_years?: number | null;
  remarks?: string | null;
  created_at?: string;
  updated_at?: string;
  customer?: UserSummary | null;
  inspectionRequest?: InspectionRequest | null;
  line_items?: QuotationLineItem[];
};

export type CreateQuotationPayload = {
  monthly_electric_bill: number;
  remarks?: string;
};

export type UpdateQuotationPayload = {
  quotation_type?: QuotationType;
  panel_watts?: number;
  inverter_type?: string;
  battery_model?: string;
  battery_capacity_ah?: number;
  status?: QuotationStatus;
  panel_cost?: number;
  inverter_cost?: number;
  battery_cost?: number;
  bos_cost?: number;
  materials_subtotal?: number;
  labor_cost?: number;
  project_cost?: number;
  remarks?: string;
};

export type SubmitFinalQuotationPayload = {
  inspection_request_id: number;
  monthly_electric_bill: number;
  rate_per_kwh?: number;
  days_in_month?: number;
  sun_hours?: number;
  pv_safety_factor?: number;
  battery_factor?: number;
  battery_voltage?: number;
  pv_system_type: PvSystemType;
  with_battery: boolean;
  inverter_type?: string;
  battery_model?: string;
  battery_capacity_ah?: number;
  panel_watts?: number;
  panel_cost?: number;
  inverter_cost?: number;
  battery_cost?: number;
  bos_cost?: number;
  materials_subtotal?: number;
  labor_cost?: number;
  project_cost?: number;
  status?: QuotationStatus;
  remarks?: string;
};

export type FinalQuotationOption<T extends string | number = string | number> = {
  label: string;
  value: T;
  battery_voltage?: number;
  battery_capacity_ah?: number;
};

export type FinalQuotationComputationDefaults = {
  rate_per_kwh: number;
  days_in_month: number;
  sun_hours: number;
  pv_safety_factor: number;
  battery_factor: number;
  battery_voltage: number;
  default_panel_watts: number;
};

export type ReplaceQuotationLineItemsPayload = {
  line_items: Array<{
    pricing_item_id?: number | null;
    description: string;
    category: string;
    qty: number;
    unit: string;
    unit_amount: number;
    total_amount?: number;
  }>;
};

export type FinalQuotationOptions = {
  system_types: FinalQuotationOption<string>[];
  panel_options: FinalQuotationOption<number>[];
  battery_options: FinalQuotationOption<string>[];
  inverter_options: FinalQuotationOption<string>[];
  computation_defaults: FinalQuotationComputationDefaults;
};

type QuotationResponse = {
  message: string;
  data: Quotation;
};

type ApiEnvelope<T> = {
  message?: string;
  data?: T;
};

function extractEnvelopeData<T>(response: T | ApiEnvelope<T>, fallback: T): T {
  if (
    response &&
    typeof response === 'object' &&
    'data' in response &&
    response.data !== undefined
  ) {
    return response.data;
  }

  return (response ?? fallback) as T;
}

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

export async function submitFinalQuotation(payload: SubmitFinalQuotationPayload) {
  const response = await apiPost<Quotation | ApiEnvelope<Quotation>>(
    '/technician/final-quotations',
    payload,
  );

  return extractEnvelopeData<Quotation>(response, {} as Quotation);
}

export async function getFinalQuotationOptions() {
  const response = await apiGet<
    FinalQuotationOptions | ApiEnvelope<FinalQuotationOptions>
  >('/technician/final-quotation-options');

  return extractEnvelopeData<FinalQuotationOptions>(response, {
    system_types: [],
    panel_options: [],
    battery_options: [],
    inverter_options: [],
    computation_defaults: {
      rate_per_kwh: 14,
      days_in_month: 30,
      sun_hours: 4.5,
      pv_safety_factor: 1.8,
      battery_factor: 1,
      battery_voltage: 51.2,
      default_panel_watts: 610,
    },
  });
}

export async function getPricingCatalog() {
  const response = await apiGet<PricingItemSummary[] | ApiEnvelope<PricingItemSummary[]>>(
    '/pricing-items',
  );

  return extractEnvelopeData<PricingItemSummary[]>(response, []);
}

export async function getCustomerFinalQuotation(inspectionRequestId: number) {
  const response = await apiGet<Quotation | ApiEnvelope<Quotation>>(
    `/customer/final-quotations/${inspectionRequestId}`,
  );

  return extractEnvelopeData<Quotation>(response, {} as Quotation);
}

export async function replaceQuotationLineItems(
  quotationId: number,
  payload: ReplaceQuotationLineItemsPayload,
) {
  const response = await apiPut<Quotation | ApiEnvelope<Quotation>>(
    `/quotations/${quotationId}/line-items`,
    payload,
  );

  return extractEnvelopeData<Quotation>(response, {} as Quotation);
}
