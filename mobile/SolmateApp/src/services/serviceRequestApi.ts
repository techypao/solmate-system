import { apiGet, apiPost } from './api';

export type UserSummary = {
  id: number;
  name?: string | null;
  email?: string | null;
  role?: string | null;
};

export type ServiceRequest = {
  id: number;
  user_id?: number;
  technician_id?: number | null;
  quotation_id?: number | null;
  request_type: string;
  details: string;
  date_needed?: string | null;
  status: string;
  created_at?: string;
  updated_at?: string;
  customer?: UserSummary | null;
  technician?: UserSummary | null;
};

export type CreateServiceRequestPayload = {
  request_type: string;
  details: string;
  date_needed?: string;
};

type CreateServiceRequestResponse = {
  message: string;
  data: ServiceRequest;
};

type TechnicianServiceRequestResponse = {
  message?: string;
  data?: ServiceRequest[];
};

export function getServiceRequests() {
  return apiGet<ServiceRequest[]>('/service-requests');
}

export async function getTechnicianServiceRequests() {
  const response = await apiGet<TechnicianServiceRequestResponse>(
    '/technician/service-requests',
  );

  return Array.isArray(response?.data) ? response.data : [];
}

export function createServiceRequest(payload: CreateServiceRequestPayload) {
  return apiPost<CreateServiceRequestResponse>('/service-requests', payload);
}
