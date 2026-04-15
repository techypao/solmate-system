import { apiGet, apiPost, apiPut } from './api';

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

export type TechnicianServiceRequestStatus =
  | 'assigned'
  | 'in_progress'
  | 'completed';

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

export async function getServiceRequestById(id: number) {
  const serviceRequests = await getServiceRequests();

  return (
    serviceRequests.find(serviceRequest => serviceRequest.id === id) || null
  );
}

export async function getTechnicianServiceRequestById(id: number) {
  const serviceRequests = await getTechnicianServiceRequests();

  return (
    serviceRequests.find(serviceRequest => serviceRequest.id === id) || null
  );
}

export function createServiceRequest(payload: CreateServiceRequestPayload) {
  return apiPost<CreateServiceRequestResponse>('/service-requests', payload);
}

export async function updateTechnicianServiceRequestStatus(
  id: number,
  status: TechnicianServiceRequestStatus,
) {
  const response = await apiPut<{message?: string; data?: ServiceRequest}>(
    `/technician/service-requests/${id}/status`,
    {status},
  );

  return response?.data ?? ({} as ServiceRequest);
}
