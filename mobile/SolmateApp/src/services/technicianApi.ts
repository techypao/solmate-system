import {apiGet, apiPost, apiPut} from './api';

export type UserSummary = {
  id: number;
  name?: string | null;
  email?: string | null;
  role?: string | null;
};

export type ServiceRequestStatus =
  | 'assigned'
  | 'in_progress'
  | 'completed'
  | string;

export type TechnicianServiceRequest = {
  id: number;
  user_id?: number;
  technician_id?: number | null;
  request_type: string;
  details: string;
  date_needed?: string | null;
  status: ServiceRequestStatus;
  created_at?: string;
  updated_at?: string;
  customer?: UserSummary | null;
  technician?: UserSummary | null;
};

export type TechnicianUpdatableStatus = 'in_progress' | 'completed';

export type CreateFinalQuotationPayload = {
  service_request_id: number;
  preferred_system: string;
  remarks?: string;
};

export type FinalQuotation = {
  id: number;
  user_id?: number;
  service_request_id: number;
  technician_id?: number | null;
  quotation_type?: string | null;
  preferred_system?: string | null;
  remarks?: string | null;
  created_at?: string;
  updated_at?: string;
  customer?: UserSummary | null;
  technician?: UserSummary | null;
  serviceRequest?: TechnicianServiceRequest | null;
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

export async function getAssignedServiceRequests() {
  const response = await apiGet<
    TechnicianServiceRequest[] | ApiEnvelope<TechnicianServiceRequest[]>
  >('/technician/service-requests');

  const data = extractEnvelopeData<TechnicianServiceRequest[]>(response, []);
  return Array.isArray(data) ? data : [];
}

export async function getAssignedServiceRequestById(serviceRequestId: number) {
  const requests = await getAssignedServiceRequests();

  return (
    requests.find(serviceRequest => serviceRequest.id === serviceRequestId) ||
    null
  );
}

export async function updateTechnicianServiceRequestStatus(
  serviceRequestId: number,
  status: TechnicianUpdatableStatus,
) {
  const response = await apiPut<
    TechnicianServiceRequest | ApiEnvelope<TechnicianServiceRequest>
  >(`/technician/service-requests/${serviceRequestId}/status`, {status});

  return extractEnvelopeData<TechnicianServiceRequest>(
    response,
    {} as TechnicianServiceRequest,
  );
}

export async function createFinalQuotation(
  payload: CreateFinalQuotationPayload,
) {
  const response = await apiPost<FinalQuotation | ApiEnvelope<FinalQuotation>>(
    '/technician/final-quotations',
    payload,
  );

  return extractEnvelopeData<FinalQuotation>(response, {} as FinalQuotation);
}
