import {
  getInspectionRequests,
  type InspectionRequest,
} from './inspectionRequestApi';
import {getServiceRequests, type ServiceRequest} from './serviceRequestApi';

const TERMINAL_REQUEST_STATUSES = new Set([
  'completed',
  'cancelled',
  'declined',
]);

export const ONGOING_REQUEST_BLOCK_MESSAGE =
  'You already have an ongoing inspection, installation, or maintenance request. Please wait until it is completed, cancelled, or declined before submitting another request.';

function isTerminalRequestStatus(status?: string | null) {
  return TERMINAL_REQUEST_STATUSES.has((status || '').trim().toLowerCase());
}

function hasOngoingInspectionRequest(requests: InspectionRequest[]) {
  return requests.some(request => !isTerminalRequestStatus(request.status));
}

function hasOngoingServiceRequest(requests: ServiceRequest[]) {
  return requests.some(request => !isTerminalRequestStatus(request.status));
}

export async function getCustomerRequestBlockMessage() {
  const [inspectionRequests, serviceRequests] = await Promise.all([
    getInspectionRequests(),
    getServiceRequests(),
  ]);

  if (
    hasOngoingInspectionRequest(inspectionRequests) ||
    hasOngoingServiceRequest(serviceRequests)
  ) {
    return ONGOING_REQUEST_BLOCK_MESSAGE;
  }

  return null;
}