import {apiGet, apiPostForm, apiPut} from './api';
import {InspectionCompletionReportPayload} from './completionReportApi';
import {InspectionRequest} from './inspectionRequestApi';

export type TechnicianInspectionRequest = InspectionRequest;
export type TechnicianUpdatableStatus =
  | 'assigned'
  | 'in_progress';

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

export async function submitInspectionCompletionReport(
  inspectionRequestId: number,
  payload: InspectionCompletionReportPayload,
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

  const response = await apiPostForm<UpdatedInspectionRequestResponse>(
    `/technician/inspection-requests/${inspectionRequestId}/completion-report`,
    formData,
  );

  return response?.inspection_request ?? ({} as TechnicianInspectionRequest);
}
