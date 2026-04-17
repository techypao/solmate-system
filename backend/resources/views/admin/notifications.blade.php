@extends('layouts.app', ['title' => 'Admin Notifications'])

@section('content')
    <style>
        .notification-summary-card {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
            padding: 18px;
            border: 1px solid #d9e2ec;
            border-radius: 12px;
            background: #f8fbff;
        }

        .notification-summary-label {
            color: #52606d;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.04em;
            margin-bottom: 6px;
            text-transform: uppercase;
        }

        .notification-summary-value {
            color: #102a43;
            font-size: 32px;
            font-weight: 700;
            line-height: 1;
        }

        .notification-list {
            display: grid;
            gap: 16px;
        }

        .notification-item {
            display: block;
            width: 100%;
            text-align: left;
            padding: 18px;
            border: 1px solid #d9e2ec;
            border-radius: 12px;
            background: #ffffff;
            color: inherit;
            cursor: pointer;
            appearance: none;
        }

        .notification-item:hover {
            border-color: #9dc8f3;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06);
        }

        .notification-item.unread {
            border-color: #9dc8f3;
            background: #f8fbff;
        }

        .notification-item-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 16px;
            margin-bottom: 12px;
        }

        .notification-item-title {
            margin: 0 0 4px;
            color: #102a43;
            font-size: 18px;
            font-weight: 700;
        }

        .notification-item-date {
            color: #7b8794;
            font-size: 13px;
        }

        .notification-item-message {
            margin: 0 0 12px;
            color: #334e68;
            font-size: 14px;
            line-height: 1.6;
        }

        .notification-item-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .notification-state {
            font-size: 12px;
            font-weight: 700;
        }

        .notification-state.read {
            color: #7b8794;
        }

        .notification-state.unread {
            color: #0f5f9c;
        }

        .notification-type {
            color: #7b8794;
            font-size: 12px;
            font-weight: 700;
        }

        .notification-unread-dot {
            width: 10px;
            height: 10px;
            border-radius: 999px;
            background: #0f5f9c;
            flex-shrink: 0;
            margin-top: 6px;
        }

        .notification-empty-state {
            text-align: center;
            padding: 28px;
            border: 1px solid #d9e2ec;
            border-radius: 12px;
            background: #fbfcfe;
        }

        .notification-empty-state strong {
            display: block;
            color: #102a43;
            font-size: 18px;
            margin-bottom: 8px;
        }
    </style>

    <div class="card">
        <div class="section-header">
            <div>
                <h1 class="page-title">Admin Notifications</h1>
                <p class="page-copy">Review new customer request alerts and jump straight into the admin request assignment workflow.</p>
            </div>
            <div class="actions" style="margin-top: 0;">
                <button id="refresh-notifications-button" type="button" class="secondary">Refresh</button>
            </div>
        </div>

        <div class="notification-summary-card">
            <div>
                <div class="notification-summary-label">Unread notifications</div>
                <div id="notification-unread-count" class="notification-summary-value">0</div>
            </div>

            <button id="mark-all-read-button" type="button" class="secondary">Mark all as read</button>
        </div>

        <div id="notifications-loading" class="info-box" style="margin-top: 16px;">Loading notifications...</div>
        <div id="notifications-success" class="status" style="display: none; margin-top: 16px;"></div>
        <div id="notifications-error" class="error-box" style="display: none; margin-top: 16px;"></div>
    </div>

    <div class="card">
        <div class="section-header">
            <div>
                <h2 style="margin: 0 0 6px;">Latest notifications</h2>
                <p class="page-copy" style="margin-bottom: 0;">Click a notification to mark it as read and open the related admin request assignment record.</p>
            </div>
        </div>

        <div id="notifications-empty" class="notification-empty-state" style="display: none;">
            <strong>No notifications yet</strong>
            Customer-created service and inspection request alerts will appear here.
        </div>

        <div id="notifications-list" class="notification-list" style="display: none;"></div>
    </div>
@endsection

@push('scripts')
    <script>
        const notificationsLoading = document.getElementById('notifications-loading');
        const notificationsSuccess = document.getElementById('notifications-success');
        const notificationsError = document.getElementById('notifications-error');
        const notificationsEmpty = document.getElementById('notifications-empty');
        const notificationsList = document.getElementById('notifications-list');
        const unreadCountValue = document.getElementById('notification-unread-count');
        const refreshButton = document.getElementById('refresh-notifications-button');
        const markAllReadButton = document.getElementById('mark-all-read-button');
        const requestAssignmentsUrl = @json($requestAssignmentsUrl);
        let notificationsState = [];

        function setVisible(element, visible, displayValue = 'block') {
            if (!element) {
                return;
            }

            element.style.display = visible ? displayValue : 'none';
        }

        function getCookie(name) {
            const prefix = `${name}=`;
            const parts = document.cookie.split(';');

            for (const part of parts) {
                const trimmed = part.trim();

                if (trimmed.startsWith(prefix)) {
                    return decodeURIComponent(trimmed.substring(prefix.length));
                }
            }

            return null;
        }

        function clearMessages() {
            notificationsSuccess.textContent = '';
            notificationsError.textContent = '';
            setVisible(notificationsSuccess, false);
            setVisible(notificationsError, false);
        }

        function showTopError(message) {
            notificationsError.textContent = message;
            setVisible(notificationsError, true);
        }

        async function ensureCsrfCookie() {
            await fetch('/sanctum/csrf-cookie', {
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });
        }

        function escapeHtml(value) {
            return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function normalizeNotification(value) {
            if (!value || typeof value !== 'object') {
                return null;
            }

            const targetParams = value.target_params && typeof value.target_params === 'object' && !Array.isArray(value.target_params)
                ? value.target_params
                : {};
            const readAt = typeof value.read_at === 'string' ? value.read_at : null;
            const isRead = value.is_read === true || !!readAt;
            const entityId = Number(value.entity_id);

            return {
                id: String(value.id ?? ''),
                type: typeof value.type === 'string' ? value.type : null,
                title: typeof value.title === 'string' ? value.title : 'Notification',
                message: typeof value.message === 'string' ? value.message : 'Open this notification to review the related admin record.',
                entity_type: typeof value.entity_type === 'string' ? value.entity_type : null,
                entity_id: Number.isFinite(entityId) && entityId > 0 ? entityId : null,
                target_screen: typeof value.target_screen === 'string' ? value.target_screen : null,
                target_params: targetParams,
                status: typeof value.status === 'string' ? value.status : null,
                is_read: isRead,
                read_at: readAt,
                created_at: typeof value.created_at === 'string' ? value.created_at : null,
                created_at_display: typeof value.created_at_display === 'string' ? value.created_at_display : null,
            };
        }

        function isNotificationRead(notification) {
            return notification?.is_read === true || !!notification?.read_at;
        }

        function formatNotificationDate(notification) {
            if (notification?.created_at_display) {
                return notification.created_at_display;
            }

            if (!notification?.created_at) {
                return 'Just now';
            }

            const parsedDate = new Date(notification.created_at);

            if (Number.isNaN(parsedDate.getTime())) {
                return notification.created_at;
            }

            return parsedDate.toLocaleString();
        }

        function getUnreadCount(notifications) {
            if (!Array.isArray(notifications)) {
                return 0;
            }

            return notifications.filter((notification) => !isNotificationRead(notification)).length;
        }

        function syncUnreadCount(notifications) {
            const unreadCount = getUnreadCount(notifications);
            unreadCountValue.textContent = String(unreadCount);

            if (window.adminNotifications && typeof window.adminNotifications.setBadgeCount === 'function') {
                window.adminNotifications.setBadgeCount(unreadCount);
            }

            markAllReadButton.disabled = unreadCount === 0;
        }

        function getNotificationTargetUrl(notification) {
            const targetParams = notification?.target_params || {};
            const requestId = Number(targetParams.requestId ?? targetParams.serviceRequestId ?? notification?.entity_id);
            const inspectionRequestId = Number(targetParams.inspectionRequestId ?? notification?.entity_id);

            if (notification?.target_screen === 'AdminServiceRequestDetails' || notification?.entity_type === 'service_request') {
                return Number.isFinite(requestId) && requestId > 0
                    ? `${requestAssignmentsUrl}#service-request-${requestId}`
                    : requestAssignmentsUrl;
            }

            if (notification?.target_screen === 'AdminInspectionRequestDetails' || notification?.entity_type === 'inspection_request') {
                return Number.isFinite(inspectionRequestId) && inspectionRequestId > 0
                    ? `${requestAssignmentsUrl}#inspection-request-${inspectionRequestId}`
                    : requestAssignmentsUrl;
            }

            return requestAssignmentsUrl;
        }

        function renderNotifications() {
            const notifications = Array.isArray(notificationsState) ? notificationsState : [];
            notificationsList.innerHTML = '';

            if (notifications.length === 0) {
                setVisible(notificationsEmpty, true);
                setVisible(notificationsList, false);
                syncUnreadCount([]);
                return;
            }

            setVisible(notificationsEmpty, false);
            setVisible(notificationsList, true, 'grid');

            notifications.forEach((notification) => {
                const isRead = isNotificationRead(notification);
                const button = document.createElement('button');
                button.type = 'button';
                button.className = `notification-item${isRead ? '' : ' unread'}`;
                button.innerHTML = `
                    <div class="notification-item-header">
                        <div>
                            <div class="notification-item-title">${escapeHtml(notification.title || 'Notification')}</div>
                            <div class="notification-item-date">${escapeHtml(formatNotificationDate(notification))}</div>
                        </div>
                        ${isRead ? '' : '<span class="notification-unread-dot"></span>'}
                    </div>
                    <p class="notification-item-message">${escapeHtml(notification.message || 'Open this notification to review the related admin record.')}</p>
                    <div class="notification-item-footer">
                        <span class="notification-state ${isRead ? 'read' : 'unread'}">${isRead ? 'Read' : 'Unread'}</span>
                        <span class="notification-type">${escapeHtml(String(notification.type || 'general').replace(/_/g, ' ').replace(/\b\w/g, (character) => character.toUpperCase()))}</span>
                    </div>
                `;

                button.addEventListener('click', () => handleNotificationClick(notification));
                notificationsList.appendChild(button);
            });

            syncUnreadCount(notifications);
        }

        function getFriendlyErrorMessage(error, fallbackMessage) {
            if (error instanceof Error && error.message) {
                return error.message;
            }

            return fallbackMessage;
        }

        async function fetchNotifications() {
            const response = await fetch('/api/notifications', {
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (!response.ok) {
                throw new Error('Could not load notifications.');
            }

            const payload = await response.json();
            const rawItems = Array.isArray(payload?.data) ? payload.data : [];

            return rawItems
                .map(normalizeNotification)
                .filter((notification) => notification && notification.id);
        }

        async function patchNotification(endpoint) {
            await ensureCsrfCookie();

            const response = await fetch(endpoint, {
                method: 'PATCH',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-XSRF-TOKEN': getCookie('XSRF-TOKEN') || '',
                },
                body: JSON.stringify({}),
            });

            const payload = await response.json();

            if (!response.ok) {
                throw new Error(payload?.message || 'Notification request failed.');
            }

            return payload;
        }

        async function loadNotifications(showLoadingState = false) {
            try {
                if (showLoadingState) {
                    setVisible(notificationsLoading, true);
                }

                clearMessages();
                notificationsState = await fetchNotifications();
                renderNotifications();
            } catch (error) {
                notificationsState = [];
                renderNotifications();
                showTopError(getFriendlyErrorMessage(error, 'Could not load notifications.'));
            } finally {
                setVisible(notificationsLoading, false);
            }
        }

        async function handleNotificationClick(notification) {
            try {
                let nextNotification = notification;

                if (!isNotificationRead(notification)) {
                    const payload = await patchNotification(`/api/notifications/${notification.id}/read`);
                    const updatedNotification = normalizeNotification(payload?.data);

                    if (!updatedNotification) {
                        throw new Error('The server did not return the updated notification.');
                    }

                    nextNotification = {
                        ...notification,
                        ...updatedNotification,
                        is_read: true,
                        read_at: updatedNotification.read_at || notification.read_at || new Date().toISOString(),
                    };

                    notificationsState = notificationsState.map((currentNotification) =>
                        currentNotification.id === notification.id ? nextNotification : currentNotification
                    );
                    renderNotifications();
                }

                window.location.href = getNotificationTargetUrl(nextNotification);
            } catch (error) {
                showTopError(getFriendlyErrorMessage(error, 'Could not open notification.'));
            }
        }

        async function handleMarkAllRead() {
            try {
                clearMessages();
                markAllReadButton.disabled = true;
                markAllReadButton.textContent = 'Marking...';

                await patchNotification('/api/notifications/mark-all-read');
                await loadNotifications(false);
                notificationsSuccess.textContent = 'All notifications marked as read.';
                setVisible(notificationsSuccess, true);
            } catch (error) {
                showTopError(getFriendlyErrorMessage(error, 'Could not mark all notifications as read.'));
            } finally {
                markAllReadButton.textContent = 'Mark all as read';
                markAllReadButton.disabled = getUnreadCount(notificationsState) === 0;
            }
        }

        refreshButton.addEventListener('click', () => loadNotifications(true));
        markAllReadButton.addEventListener('click', handleMarkAllRead);

        loadNotifications(true);
        window.addEventListener('focus', () => loadNotifications(false));
        document.addEventListener('visibilitychange', () => {
            if (document.visibilityState === 'visible') {
                loadNotifications(false);
            }
        });
    </script>
@endpush
