import {apiDelete, apiGet, apiPatch} from './api';

export type AppNotification = {
  id: string;
  type?: string | null;
  title?: string | null;
  message?: string | null;
  entity_type?: string | null;
  entity_id?: number | null;
  target_screen?: string | null;
  target_params?: Record<string, unknown> | null;
  status?: string | null;
  created_by?: number | null;
  created_at_display?: string | null;
  is_read: boolean;
  read_at?: string | null;
  created_at?: string | null;
};

type NotificationsResponse = {
  success: boolean;
  data?: AppNotification[];
};

type UnreadCountResponse = {
  success: boolean;
  unread_count?: number;
};

type MarkReadResponse = {
  success: boolean;
  message?: string;
  data?: AppNotification;
};

type MarkAllReadResponse = {
  success: boolean;
  message?: string;
  unread_count?: number;
};

type DeleteNotificationResponse = {
  message?: string;
};

function toBoolean(value: unknown) {
  if (typeof value === 'boolean') {
    return value;
  }

  if (typeof value === 'string') {
    const normalizedValue = value.trim().toLowerCase();

    if (normalizedValue === 'true' || normalizedValue === '1') {
      return true;
    }

    if (normalizedValue === 'false' || normalizedValue === '0') {
      return false;
    }
  }

  if (typeof value === 'number') {
    return value === 1;
  }

  return false;
}

function normalizeNotification(value: any): AppNotification | null {
  if (!value || typeof value !== 'object') {
    return null;
  }

  const rawTargetParams =
    value.target_params &&
    typeof value.target_params === 'object' &&
    !Array.isArray(value.target_params)
      ? value.target_params
      : {};
  const readAt = typeof value.read_at === 'string' ? value.read_at : null;
  const isRead = toBoolean(value.is_read) || readAt !== null;

  return {
    id: String(value.id ?? ''),
    type: typeof value.type === 'string' ? value.type : null,
    title: typeof value.title === 'string' ? value.title : null,
    message: typeof value.message === 'string' ? value.message : null,
    entity_type:
      typeof value.entity_type === 'string' ? value.entity_type : null,
    entity_id:
      typeof value.entity_id === 'number' ? value.entity_id : Number(value.entity_id) || null,
    target_screen:
      typeof value.target_screen === 'string' ? value.target_screen : null,
    target_params: rawTargetParams,
    status: typeof value.status === 'string' ? value.status : null,
    created_by:
      typeof value.created_by === 'number'
        ? value.created_by
        : Number(value.created_by) || null,
    created_at_display:
      typeof value.created_at_display === 'string'
        ? value.created_at_display
        : null,
    is_read: isRead,
    read_at: readAt,
    created_at: typeof value.created_at === 'string' ? value.created_at : null,
  };
}

function extractNotifications(response: NotificationsResponse | AppNotification[]) {
  const rawItems = Array.isArray(response)
    ? response
    : Array.isArray(response?.data)
      ? response.data
      : [];

  return rawItems
    .map(normalizeNotification)
    .filter((item): item is AppNotification => !!item && item.id.length > 0);
}

export async function getNotifications() {
  const response = await apiGet<NotificationsResponse | AppNotification[]>(
    '/notifications',
  );

  return extractNotifications(response);
}

export async function getUnreadNotificationCount() {
  const response = await apiGet<UnreadCountResponse>('/notifications/unread-count');

  return typeof response?.unread_count === 'number' ? response.unread_count : 0;
}

export async function markNotificationAsRead(id: string) {
  const response = await apiPatch<MarkReadResponse>(
    `/notifications/${id}/read`,
    {},
  );

  return normalizeNotification(response?.data);
}

export async function markAllNotificationsAsRead() {
  const response = await apiPatch<MarkAllReadResponse>(
    '/notifications/mark-all-read',
    {},
  );

  return typeof response?.unread_count === 'number' ? response.unread_count : 0;
}

export async function deleteNotification(id: string) {
  const response = await apiDelete<DeleteNotificationResponse>(
    `/notifications/${id}`,
  );

  return response?.message || 'Notification deleted successfully.';
}

export async function deleteAllNotifications() {
  const response = await apiDelete<DeleteNotificationResponse>(
    '/notifications',
  );

  return response?.message || 'All notifications deleted successfully.';
}
