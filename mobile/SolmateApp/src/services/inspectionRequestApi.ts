import {apiGet, apiPost} from './api';

export type InspectionRequest = {
  id: number;
  user_id?: number;
  details: string;
  date_needed?: string | null;
  status?: string | null;
  created_at?: string;
  updated_at?: string;
};

export type CreateInspectionRequestPayload = {
  details: string;
  date_needed?: string;
};

type CreateInspectionRequestResponse = {
  message: string;
  data: InspectionRequest;
};

export function getInspectionRequests() {
  return apiGet<InspectionRequest[]>('/inspection-requests');
}

export function createInspectionRequest(
  payload: CreateInspectionRequestPayload,
) {
  return apiPost<CreateInspectionRequestResponse>(
    '/inspection-requests',
    payload,
  );
}
