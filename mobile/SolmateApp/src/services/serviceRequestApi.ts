import { apiGet, apiPost, apiPostForm, apiPut } from './api';
import {CompletionReport, CompletionReportPayload, ServiceCompletionReportPayload} from './completionReportApi';

export type UserSummary = {
  id: number;
  name?: string | null;
  email?: string | null;
  role?: string | null;
};

export type ServiceRequest = {
  id: number;
  user_id?: number;
  customer_name?: string | null;
  customer_email?: string | null;
  technician_id?: number | null;
  quotation_id?: number | null;
  request_type: string;
  details: string;
  cancellation_note?: string | null;
  contact_number?: string | null;
  address?: string | null;
  address_details?: string | null;
  latitude?: number | null;
  longitude?: number | null;
  date_needed?: string | null;
  status: string;
  technician_marked_done_at?: string | null;
  completion_report?: CompletionReport | null;
  created_at?: string;
  updated_at?: string;
  customer?: UserSummary | null;
  technician?: UserSummary | null;
};

export type CreateServiceRequestPayload = {
  request_type: string;
  details: string;
  contact_number?: string;
  address?: string;
  address_details?: string | null;
  latitude?: number | null;
  longitude?: number | null;
  date_needed?: string;
};

export type TechnicianServiceRequestStatus =
  | 'assigned'
  | 'in_progress';

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

export async function submitTechnicianServiceCompletionReport(
  id: number,
  payload: ServiceCompletionReportPayload,
) {
  const formData = new FormData();
  formData.append('report_text', payload.report_text);
  formData.append('completed_at', payload.completed_at);

  payload.completion_photos.forEach((photo, index) => {
    formData.append('completion_photos[]', {
      uri: photo.uri,
      type: photo.type || 'image/jpeg',
      name: photo.name || `completion-photo-${Date.now()}-${index}.jpg`,
    } as any);
  });

  const response = await apiPostForm<{message?: string; data?: ServiceRequest}>(
    `/technician/service-requests/${id}/completion-report`,
    formData,
  );

  return response?.data ?? ({} as ServiceRequest);
}

export async function cancelServiceRequestByCustomer(
  id: number,
  cancellationNote: string,
) {
  const response = await apiPut<{
    message?: string;
    data?: ServiceRequest;
    cancellation_count?: number;
    account_archived?: boolean;
  }>(
    `/service-requests/${id}/cancel`,
    {cancellation_note: cancellationNote},
  );

  return {
    ...(response?.data ?? ({} as ServiceRequest)),
    cancellation_count: response?.cancellation_count,
    account_archived: response?.account_archived,
  };
}
