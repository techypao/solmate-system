import {TechnicianInspectionRequest} from '../services/technicianApi';
import {getSolmateStatusColors} from '../theme/colors';

const DISPLAY_VALUE_KEYS = [
  'formatted_address',
  'full_address',
  'address',
  'address_line',
  'name',
  'label',
  'title',
  'value',
  'text',
  'description',
  'message',
];

function isRecord(value: unknown): value is Record<string, unknown> {
  return !!value && typeof value === 'object' && !Array.isArray(value);
}

export function formatDisplayValue(
  value: unknown,
  fallback = 'Not available',
): string {
  if (value === null || value === undefined) {
    return fallback;
  }

  if (typeof value === 'string') {
    const trimmedValue = value.trim();
    return trimmedValue || fallback;
  }

  if (typeof value === 'number' || typeof value === 'boolean') {
    return String(value);
  }

  if (value instanceof Date) {
    return Number.isNaN(value.getTime()) ? fallback : value.toLocaleString();
  }

  if (Array.isArray(value)) {
    const formattedItems = value
      .map(item => formatDisplayValue(item, ''))
      .filter(Boolean);

    return formattedItems.length > 0 ? formattedItems.join(', ') : fallback;
  }

  if (isRecord(value)) {
    for (const key of DISPLAY_VALUE_KEYS) {
      if (key in value) {
        const formattedValue = formatDisplayValue(value[key], '');

        if (formattedValue) {
          return formattedValue;
        }
      }
    }
  }

  return fallback;
}

export function formatDate(value?: unknown, fallback = 'Not specified') {
  const dateValue = formatDisplayValue(value, '');

  if (!dateValue) {
    return fallback;
  }

  const parsedDate = new Date(dateValue);

  if (Number.isNaN(parsedDate.getTime())) {
    return dateValue;
  }

  return parsedDate.toLocaleDateString();
}

export function formatDateTime(
  value?: unknown,
  fallback = 'Not available',
) {
  const dateValue = formatDisplayValue(value, '');

  if (!dateValue) {
    return fallback;
  }

  const parsedDate = new Date(dateValue);

  if (Number.isNaN(parsedDate.getTime())) {
    return dateValue;
  }

  return parsedDate.toLocaleString();
}

export function normalizeRequestStatus(status?: unknown) {
  return formatDisplayValue(status, '')
    .trim()
    .toLowerCase()
    .replace(/[\s-]+/g, '_');
}

export function formatServiceRequestStatus(status?: unknown) {
  switch (normalizeRequestStatus(status)) {
    case 'pending':
      return 'Pending';
    case 'assigned':
      return 'Assigned';
    case 'in_progress':
      return 'In Progress';
    case 'completed':
      return 'Completed';
    case 'cancelled':
      return 'Cancelled';
    case 'declined':
      return 'Declined';
    default:
      return 'Pending';
  }
}

export function getServiceRequestStatusColors(status?: unknown) {
  return getSolmateStatusColors(normalizeRequestStatus(status));
}

export function getCustomerName(
  inspectionRequest?: TechnicianInspectionRequest | null,
) {
  return formatDisplayValue(
    inspectionRequest?.customer?.name || inspectionRequest?.customer_name,
    'Customer not provided',
  );
}

export function getCustomerEmail(
  inspectionRequest?: TechnicianInspectionRequest | null,
) {
  return formatDisplayValue(
    inspectionRequest?.customer?.email || inspectionRequest?.customer_email,
    'No email available',
  );
}

export function getTechnicianName(
  inspectionRequest?: TechnicianInspectionRequest | null,
) {
  return formatDisplayValue(
    inspectionRequest?.technician?.name,
    'Assigned technician',
  );
}

export function getTechnicianEmail(
  inspectionRequest?: TechnicianInspectionRequest | null,
) {
  return formatDisplayValue(
    inspectionRequest?.technician?.email,
    'No email available',
  );
}

export function canCreateFinalQuotation(status?: unknown) {
  return normalizeRequestStatus(status) === 'in_progress';
}
