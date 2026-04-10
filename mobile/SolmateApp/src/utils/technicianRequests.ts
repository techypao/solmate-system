import {TechnicianServiceRequest} from '../services/technicianApi';

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
    case 'assigned':
      return {
        backgroundColor: '#fef3c7',
        textColor: '#92400e',
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

export function getCustomerName(serviceRequest?: TechnicianServiceRequest | null) {
  return serviceRequest?.customer?.name || 'Customer not provided';
}

export function getCustomerEmail(serviceRequest?: TechnicianServiceRequest | null) {
  return serviceRequest?.customer?.email || 'No email available';
}

export function getTechnicianName(
  serviceRequest?: TechnicianServiceRequest | null,
) {
  return serviceRequest?.technician?.name || 'Assigned technician';
}

export function getTechnicianEmail(
  serviceRequest?: TechnicianServiceRequest | null,
) {
  return serviceRequest?.technician?.email || 'No email available';
}

export function canStartServiceRequest(status?: string | null) {
  return (status || '').toLowerCase() === 'assigned';
}

export function canCompleteServiceRequest(status?: string | null) {
  return (status || '').toLowerCase() === 'in_progress';
}

export function canCreateFinalQuotation(status?: string | null) {
  return (status || '').toLowerCase() === 'completed';
}
