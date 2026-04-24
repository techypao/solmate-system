import {apiGet, apiPost} from './api';

export type UserSummary = {
  id: number;
  name?: string | null;
  email?: string | null;
  role?: string | null;
};

export type InspectionRequestStatus =
  | 'pending'
  | 'assigned'
  | 'in_progress'
  | 'completed'
  | string;

export type InspectionRequest = {
  id: number;
  user_id?: number;
  technician_id?: number | null;
  details: string;
  contact_number?: string | null;
  address?: string | null;
  date_needed?: string | null;
  status?: InspectionRequestStatus | null;
  created_at?: string;
  updated_at?: string;
  customer?: UserSummary | null;
  technician?: UserSummary | null;
};

export type CreateInspectionRequestPayload = {
  details: string;
  contact_number?: string;
  address?: string;
  date_needed?: string;
};

type CreateInspectionRequestResponse = {
  message: string;
  data: InspectionRequest;
};

export function getInspectionRequests() {
  return apiGet<InspectionRequest[]>('/inspection-requests');
}

export async function getInspectionRequestById(id: number) {
  const inspectionRequests = await getInspectionRequests();

  return (
    inspectionRequests.find(inspectionRequest => inspectionRequest.id === id) ||
    null
  );
}

export function createInspectionRequest(
  payload: CreateInspectionRequestPayload,
) {
  return apiPost<CreateInspectionRequestResponse>(
    '/inspection-requests',
    payload,
  );
}
