import { apiGet, apiPost } from './api';

export type ServiceRequest = {
  id: number;
  user_id?: number;
  quotation_id?: number | null;
  request_type: string;
  details: string;
  date_needed?: string | null;
  status?: string | null;
  created_at?: string;
  updated_at?: string;
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

export function getServiceRequests() {
  return apiGet<ServiceRequest[]>('/service-requests');
}

export function createServiceRequest(payload: CreateServiceRequestPayload) {
  return apiPost<CreateServiceRequestResponse>('/service-requests', payload);
}
