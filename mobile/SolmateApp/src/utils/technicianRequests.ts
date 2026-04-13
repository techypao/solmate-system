import {TechnicianInspectionRequest} from '../services/technicianApi';

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
  switch ((status || '').toLowerCase()) {
    case 'pending':
      return {
        backgroundColor: '#fef3c7',
        textColor: '#92400e',
      };
    case 'assigned':
      return {
        backgroundColor: '#ede9fe',
        textColor: '#6d28d9',
      };
    case 'in_progress':
      return {
        backgroundColor: '#dbeafe',
        textColor: '#1d4ed8',
      };
    case 'completed':
      return {
        backgroundColor: '#dcfce7',
        textColor: '#166534',
      };
    default:
      return {
        backgroundColor: '#e2e8f0',
        textColor: '#475569',
      };
  }
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

export function canCreateFinalQuotation(status?: string | null) {
  return (status || '').toLowerCase() === 'completed';
}
