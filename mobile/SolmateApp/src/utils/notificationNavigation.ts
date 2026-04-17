import {AppNotification} from '../services/notificationApi';

type NotificationNavigationTarget = {
  routeName: string;
  params?: Record<string, unknown>;
};

export const CUSTOMER_NOTIFICATION_ROUTE_NAMES = [
  'Home',
  'CustomerNotifications',
  'ServiceRequestList',
  'ServiceRequestDetail',
  'InspectionRequestList',
  'InspectionRequestDetail',
  'FinalQuotationView',
  'QuotationList',
  'QuotationDetail',
] as const;

export const TECHNICIAN_NOTIFICATION_ROUTE_NAMES = [
  'TechnicianDashboard',
  'TechnicianNotifications',
  'AssignedInspectionRequests',
  'InspectionDetails',
  'TechnicianServiceRequests',
  'TechnicianServiceRequestDetail',
  'TechnicianQuotationDetail',
] as const;

function toNullableString(value: unknown) {
  if (typeof value !== 'string') {
    return null;
  }

  const trimmedValue = value.trim();

  return trimmedValue.length > 0 ? trimmedValue : null;
}

function toPositiveNumber(value: unknown) {
  const parsedValue = Number(value);

  if (!Number.isFinite(parsedValue) || parsedValue <= 0) {
    return null;
  }

  return parsedValue;
}

function getTargetParams(notification: AppNotification) {
  if (
    notification.target_params &&
    typeof notification.target_params === 'object' &&
    !Array.isArray(notification.target_params)
  ) {
    return notification.target_params;
  }

  return {};
}

export function getCustomerNotificationNavigationTarget(
  notification: AppNotification,
): NotificationNavigationTarget | null {
  const targetScreen = toNullableString(notification.target_screen) || '';
  const targetParams = getTargetParams(notification);
  const entityId = toPositiveNumber(notification.entity_id);
  const requestId =
    toPositiveNumber(targetParams.requestId) ??
    toPositiveNumber(targetParams.serviceRequestId) ??
    entityId;
  const inspectionRequestId =
    toPositiveNumber(targetParams.inspectionRequestId) ?? entityId;
  const quotationId = toPositiveNumber(targetParams.quotationId) ?? entityId;

  switch (targetScreen) {
    case 'CustomerRequestDetails':
      return requestId
        ? {
            routeName: 'ServiceRequestDetail',
            params: {
              serviceRequestId: requestId,
              mode: 'customer',
            },
          }
        : {routeName: 'ServiceRequestList'};
    case 'CustomerInspectionRequestDetails':
      return inspectionRequestId
        ? {
            routeName: 'InspectionRequestDetail',
            params: {inspectionRequestId},
          }
        : {routeName: 'InspectionRequestList'};
    case 'CustomerFinalQuotationDetails':
      return inspectionRequestId
        ? {
            routeName: 'FinalQuotationView',
            params: {inspectionRequestId},
          }
        : {routeName: 'QuotationList'};
    default:
      break;
  }

  switch (notification.entity_type) {
    case 'service_request':
      return requestId
        ? {
            routeName: 'ServiceRequestDetail',
            params: {
              serviceRequestId: requestId,
              mode: 'customer',
            },
          }
        : {routeName: 'ServiceRequestList'};
    case 'inspection_request':
      return inspectionRequestId
        ? {
            routeName: 'InspectionRequestDetail',
            params: {inspectionRequestId},
          }
        : {routeName: 'InspectionRequestList'};
    case 'quotation':
      return inspectionRequestId
        ? {
            routeName: 'FinalQuotationView',
            params: {inspectionRequestId},
          }
        : quotationId
          ? {
              routeName: 'QuotationDetail',
              params: {quotationId},
            }
          : {routeName: 'QuotationList'};
    default:
      return {routeName: 'Home'};
  }
}

export function getTechnicianNotificationNavigationTarget(
  notification: AppNotification,
): NotificationNavigationTarget | null {
  const targetScreen = toNullableString(notification.target_screen) || '';
  const targetParams = getTargetParams(notification);
  const entityId = toPositiveNumber(notification.entity_id);
  const requestId =
    toPositiveNumber(targetParams.requestId) ??
    toPositiveNumber(targetParams.serviceRequestId) ??
    entityId;
  const inspectionRequestId =
    toPositiveNumber(targetParams.inspectionRequestId) ?? entityId;
  const quotationId = toPositiveNumber(targetParams.quotationId) ?? entityId;

  switch (targetScreen) {
    case 'TechnicianServiceRequestDetails':
      return requestId
        ? {
            routeName: 'TechnicianServiceRequestDetail',
            params: {
              serviceRequestId: requestId,
              mode: 'technician',
            },
          }
        : {routeName: 'TechnicianServiceRequests'};
    case 'TechnicianInspectionRequestDetails':
      return inspectionRequestId
        ? {
            routeName: 'InspectionDetails',
            params: {inspectionRequestId},
          }
        : {routeName: 'AssignedInspectionRequests'};
    default:
      break;
  }

  switch (notification.entity_type) {
    case 'service_request':
      return requestId
        ? {
            routeName: 'TechnicianServiceRequestDetail',
            params: {
              serviceRequestId: requestId,
              mode: 'technician',
            },
          }
        : {routeName: 'TechnicianServiceRequests'};
    case 'inspection_request':
      return inspectionRequestId
        ? {
            routeName: 'InspectionDetails',
            params: {inspectionRequestId},
          }
        : {routeName: 'AssignedInspectionRequests'};
    case 'quotation':
      return quotationId
        ? {
            routeName: 'TechnicianQuotationDetail',
            params: {quotationId},
          }
        : {routeName: 'TechnicianDashboard'};
    default:
      return {routeName: 'TechnicianDashboard'};
  }
}
