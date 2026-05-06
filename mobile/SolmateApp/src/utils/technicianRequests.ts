import {TechnicianInspectionRequest} from '../services/technicianApi';
import {getSolmateStatusColors} from '../theme/colors';

export function formatDate(value?: string | null, fallback = 'Not specified') {
  if (!value) {
    return fallback;
  }

  const parsedDate = new Date(value);

  if (Number.isNaN(parsedDate.getTime())) {
    return value;
  }

  return parsedDate.toLocaleDateString();
}

export function formatDateTime(
  value?: string | null,
  fallback = 'Not available',
) {
  if (!value) {
    return fallback;
  }

  const parsedDate = new Date(value);

  if (Number.isNaN(parsedDate.getTime())) {
    return value;
  }

  return parsedDate.toLocaleString();
}

export function formatServiceRequestStatus(status?: string | null) {
  switch ((status || '').toLowerCase()) {
    case 'pending':
      return 'Pending';
    case 'assigned':
      return 'Assigned';
    case 'in_progress':
      return 'In Progress';
    case 'completed':
      return 'Completed';
    default:
      return 'Pending';
  }
}

export function getServiceRequestStatusColors(status?: string | null) {
  return getSolmateStatusColors(status);
}

export function getCustomerName(
  inspectionRequest?: TechnicianInspectionRequest | null,
) {
  return inspectionRequest?.customer?.name || 'Customer not provided';
}

export function getCustomerEmail(
  inspectionRequest?: TechnicianInspectionRequest | null,
) {
  return inspectionRequest?.customer?.email || 'No email available';
}

export function getTechnicianName(
  inspectionRequest?: TechnicianInspectionRequest | null,
) {
  return inspectionRequest?.technician?.name || 'Assigned technician';
}

export function getTechnicianEmail(
  inspectionRequest?: TechnicianInspectionRequest | null,
) {
  return inspectionRequest?.technician?.email || 'No email available';
}

function normalizeInspectionStatus(status?: string | null) {
  return (status || '')
    .trim()
    .toLowerCase()
    .replace(/[\s-]+/g, '_');
}

export function canCreateFinalQuotation(status?: string | null) {
  return normalizeInspectionStatus(status) === 'in_progress';
}
