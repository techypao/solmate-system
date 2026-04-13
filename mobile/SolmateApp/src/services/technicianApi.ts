import {apiGet, apiPut} from './api';
import {InspectionRequest} from './inspectionRequestApi';

export type TechnicianInspectionRequest = InspectionRequest;
export type TechnicianUpdatableStatus =
  | 'assigned'
  | 'in_progress'
  | 'completed';

type AssignedInspectionRequestsResponse = {
  inspection_requests?: TechnicianInspectionRequest[];
};

type UpdatedInspectionRequestResponse = {
  message?: string;
  inspection_request?: TechnicianInspectionRequest;
};

export async function getAssignedInspectionRequests() {
  const response = await apiGet<AssignedInspectionRequestsResponse>(
    '/technician/inspection-requests',
  );

  return Array.isArray(response?.inspection_requests)
    ? response.inspection_requests
    : [];
}

export async function getAssignedInspectionRequestById(
  inspectionRequestId: number,
) {
  const requests = await getAssignedInspectionRequests();

  return (
    requests.find(
      inspectionRequest => inspectionRequest.id === inspectionRequestId,
    ) || null
  );
}

export async function updateInspectionRequestStatus(
  inspectionRequestId: number,
  status: TechnicianUpdatableStatus,
) {
  const response = await apiPut<UpdatedInspectionRequestResponse>(
    `/technician/inspection-requests/${inspectionRequestId}/status`,
    {status},
  );

  return response?.inspection_request ?? ({} as TechnicianInspectionRequest);
}
