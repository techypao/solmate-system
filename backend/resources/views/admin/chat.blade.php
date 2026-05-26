@extends('layouts.app', ['title' => 'Support Chat'])

@section('content')
    <style>
        .support-chat-layout {
            display: grid;
            grid-template-columns: minmax(280px, 340px) minmax(0, 1fr);
            gap: 20px;
            min-height: min(78vh, 880px);
            height: calc(100vh - 240px);
            max-height: 880px;
            align-items: stretch;
        }

        .support-chat-panel {
            background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
            border: 1px solid #dde7ee;
            border-radius: 20px;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.04);
            overflow: hidden;
            min-height: 0;
        }

        .support-chat-panel-header {
            padding: 18px 20px;
            border-bottom: 1px solid #dde7ee;
            display: flex;
            justify-content: space-between;
            gap: 12px;
            align-items: center;
        }

        .support-chat-list {
            min-height: 0;
            overflow-y: auto;
            display: grid;
        }

        .support-chat-thread-button {
            width: 100%;
            display: grid;
            gap: 8px;
            text-align: left;
            border: 0;
            border-bottom: 1px solid #edf2f7;
            background: transparent;
            padding: 16px 18px;
            cursor: pointer;
        }

        .support-chat-thread-button.active {
            background: #eff6ff;
        }

        .support-chat-thread-button.waiting {
            background: #fff7ed;
        }

        .support-chat-thread-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12px;
            min-width: 0;
        }

        .support-chat-thread-name {
            color: #123a5a;
            font-weight: 800;
            font-size: 18px;
            line-height: 1.15;
            flex: 1;
            min-width: 0;
            overflow-wrap: anywhere;
        }

        .support-chat-thread-meta,
        .support-chat-thread-preview {
            color: #5e7288;
            font-size: 13px;
            line-height: 1.5;
            min-width: 0;
            overflow-wrap: anywhere;
        }

        .support-chat-thread-preview {
            color: #41566d;
        }

        .support-chat-badge {
            flex-shrink: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 4px 8px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .support-chat-badge.waiting {
            background: #fed7aa;
            color: #9a3412;
        }

        .support-chat-badge.admin {
            background: #bbf7d0;
            color: #166534;
        }

        .support-chat-badge.bot {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .support-chat-main {
            display: grid;
            grid-template-rows: auto auto minmax(0, 1fr) auto;
            min-height: 0;
            height: 100%;
        }

        .support-chat-banner {
            margin: 16px 20px 0;
            padding: 14px 16px;
            border-radius: 14px;
            display: none;
        }

        .support-chat-banner.waiting {
            background: #fff7ed;
            border: 1px solid #fdba74;
            color: #9a3412;
        }

        .support-chat-banner.admin {
            background: #ecfdf3;
            border: 1px solid #86efac;
            color: #166534;
        }

        .support-chat-message-list {
            min-height: 0;
            overflow-y: auto;
            padding: 18px 20px;
            display: grid;
            gap: 12px;
        }

        .support-chat-empty {
            padding: 32px 20px;
            text-align: center;
            color: #5e7288;
        }

        .support-chat-row {
            display: flex;
        }

        .support-chat-row.admin {
            justify-content: flex-end;
        }

        .support-chat-row.system {
            justify-content: center;
        }

        .support-chat-bubble {
            max-width: min(78%, 720px);
            border-radius: 18px;
            padding: 12px 14px;
            background: #f4f7fb;
            border: 1px solid #e4eaf3;
        }

        .support-chat-bubble.admin {
            background: #123a5a;
            border-color: #123a5a;
            color: #ffffff;
        }

        .support-chat-bubble.bot {
            background: #fef3c7;
            border-color: #fde68a;
            color: #92400e;
        }

        .support-chat-bubble.system {
            background: #eef2ff;
            border-color: #c7d2fe;
            color: #3730a3;
            text-align: center;
        }

        .support-chat-meta {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 8px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .support-chat-body {
            margin: 0;
            white-space: pre-wrap;
            line-height: 1.6;
        }

        .support-chat-controls {
            display: grid;
            gap: 12px;
            padding: 16px 20px 12px;
            border-top: 1px solid #dde7ee;
            background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
        }

        .support-chat-actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            align-items: center;
        }

        .support-chat-controls-note {
            color: #5e7288;
            font-size: 13px;
            line-height: 1.5;
        }

        .support-chat-composer {
            padding: 0 20px 20px;
            display: grid;
            gap: 12px;
        }

        .support-chat-composer-row {
            display: flex;
            gap: 12px;
            align-items: stretch;
        }

        .support-chat-textarea {
            flex: 1;
            width: 100%;
            display: block;
            min-height: 96px;
            resize: vertical;
            border-radius: 16px;
            border: 1px solid #d8e3f0;
            padding: 14px 16px;
            font: inherit;
            color: #123a5a;
            background: #ffffff;
        }

        @media (max-width: 980px) {
            .support-chat-layout {
                grid-template-columns: 1fr;
                height: auto;
                max-height: none;
                min-height: 0;
            }

            .support-chat-composer-row {
                flex-direction: column;
            }

            .support-chat-composer-row button {
                width: 100%;
            }
        }
    </style>

    <div class="admin-page-stack">
        <div class="card admin-hero-card">
            <div class="section-header">
                <div>
                    <p class="admin-page-eyebrow">Customer Support</p>
                    <h1 class="page-title">Support Chat</h1>
                    <p class="page-copy">Take over SolBot conversations when escalation is triggered. Admin replies stay in the same thread and SolBot remains paused while admin control is active.</p>
                </div>
                <button id="admin-chat-refresh-button" type="button" class="secondary">Refresh</button>
            </div>
        </div>

        <div class="support-chat-layout">
            <div class="support-chat-panel">
                <div class="support-chat-panel-header">
                    <div>
                        <h2 class="admin-section-title" style="margin-bottom: 4px;">Conversations</h2>
                        <p class="page-copy" style="margin-bottom: 0;">Waiting chats appear first.</p>
                    </div>
                </div>
                <div id="admin-chat-thread-list" class="support-chat-list">
                    <div class="support-chat-empty">Loading conversations...</div>
                </div>
            </div>

            <div class="support-chat-panel support-chat-main">
                <div class="support-chat-panel-header">
                    <div>
                        <h2 id="admin-chat-title" class="admin-section-title" style="margin-bottom: 4px;">Select a conversation</h2>
                        <p id="admin-chat-subtitle" class="page-copy" style="margin-bottom: 0;">Choose a customer thread to review messages and take over when needed.</p>
                    </div>
                </div>

                <div id="admin-chat-banner" class="support-chat-banner waiting"></div>
                <div id="admin-chat-error" class="error-box" style="display:none; margin: 16px 20px 0;"></div>

                <div id="admin-chat-message-list" class="support-chat-message-list">
                    <div class="support-chat-empty">No conversation selected yet.</div>
                </div>

                <div class="support-chat-controls">
                    <div class="support-chat-actions">
                        <button id="admin-chat-takeover-button" type="button" class="secondary" disabled>Take over chat</button>
                        <button id="admin-chat-return-button" type="button" class="secondary" disabled>Switch to chatbot</button>
                    </div>
                    <div class="support-chat-controls-note">Customer and admin messages stay in the same thread.</div>
                </div>

                <div class="support-chat-composer">
                    <div class="support-chat-composer-row">
                        <textarea id="admin-chat-message-input" class="support-chat-textarea" placeholder="Reply as admin..." disabled></textarea>
                        <button id="admin-chat-send-button" type="button" class="primary" disabled>Send</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const adminThreadList = document.getElementById('admin-chat-thread-list');
        const adminMessageList = document.getElementById('admin-chat-message-list');
        const adminChatTitle = document.getElementById('admin-chat-title');
        const adminChatSubtitle = document.getElementById('admin-chat-subtitle');
        const adminChatBanner = document.getElementById('admin-chat-banner');
        const adminChatError = document.getElementById('admin-chat-error');
        const adminChatMessageInput = document.getElementById('admin-chat-message-input');
        const adminChatSendButton = document.getElementById('admin-chat-send-button');
        const adminChatTakeoverButton = document.getElementById('admin-chat-takeover-button');
        const adminChatReturnButton = document.getElementById('admin-chat-return-button');
        const adminChatRefreshButton = document.getElementById('admin-chat-refresh-button');
        let adminChatState = {conversations: [], selectedConversation: null, selectedConversationId: null};
        let adminSubmitting = false;

        function setVisible(element, visible, displayValue = 'block') {
            if (!element) {
                return;
            }

            element.style.display = visible ? displayValue : 'none';
        }

        function escapeHtml(value) {
            return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function getCookie(name) {
            const prefix = `${name}=`;

            for (const part of document.cookie.split(';')) {
                const trimmed = part.trim();

                if (trimmed.startsWith(prefix)) {
                    return decodeURIComponent(trimmed.substring(prefix.length));
                }
            }

            return null;
        }

        async function ensureCsrfCookie() {
            await fetch('/sanctum/csrf-cookie', {
                credentials: 'same-origin',
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });
        }

        function formatTime(value) {
            return value
                ? new Date(value).toLocaleTimeString([], {hour: 'numeric', minute: '2-digit'})
                : '';
        }

        function renderThreadList() {
            const conversations = Array.isArray(adminChatState.conversations) ? adminChatState.conversations : [];

            if (conversations.length === 0) {
                adminThreadList.innerHTML = '<div class="support-chat-empty">No chat conversations yet.</div>';
                return;
            }

            adminThreadList.innerHTML = conversations.map((conversation) => {
                const active = conversation.id === adminChatState.selectedConversationId;
                const waiting = conversation.is_awaiting_admin;
                const badgeClass = waiting ? 'waiting' : conversation.status === 'admin' ? 'admin' : 'bot';
                const badgeLabel = waiting ? 'Waiting' : conversation.status === 'admin' ? 'Admin' : 'Bot';

                return `<button type="button" class="support-chat-thread-button ${active ? 'active' : ''} ${waiting ? 'waiting' : ''}" data-conversation-id="${conversation.id}">
                    <div class="support-chat-thread-top">
                        <span class="support-chat-thread-name">${escapeHtml(conversation.customer?.name || 'Customer')}</span>
                        <span class="support-chat-badge ${badgeClass}">${badgeLabel}</span>
                    </div>
                    <div class="support-chat-thread-meta">${escapeHtml(conversation.customer?.email || '')}</div>
                    <div class="support-chat-thread-preview">${escapeHtml(conversation.latest_message || 'No messages yet.')}</div>
                </button>`;
            }).join('');

            adminThreadList.querySelectorAll('[data-conversation-id]').forEach((button) => {
                button.addEventListener('click', () => {
                    adminChatState.selectedConversationId = Number(button.getAttribute('data-conversation-id'));
                    loadSelectedConversation();
                    renderThreadList();
                });
            });
        }

        function renderSelectedConversation() {
            const payload = adminChatState.selectedConversation;
            const conversation = payload?.conversation;
            const messages = Array.isArray(payload?.messages) ? payload.messages : [];

            if (!conversation) {
                adminChatTitle.textContent = 'Select a conversation';
                adminChatSubtitle.textContent = 'Choose a customer thread to review messages and take over when needed.';
                adminMessageList.innerHTML = '<div class="support-chat-empty">No conversation selected yet.</div>';
                adminChatTakeoverButton.disabled = true;
                adminChatReturnButton.disabled = true;
                adminChatMessageInput.disabled = true;
                adminChatSendButton.disabled = true;
                setVisible(adminChatBanner, false);
                return;
            }

            adminChatTitle.textContent = conversation.customer?.name || 'Customer conversation';
            adminChatSubtitle.textContent = conversation.customer?.email || 'Support chat thread';

            if (conversation.is_awaiting_admin) {
                adminChatBanner.className = 'support-chat-banner waiting';
                adminChatBanner.textContent = 'This conversation has been escalated and is waiting for an admin to take over. SolBot is already paused.';
                setVisible(adminChatBanner, true);
            } else if (conversation.admin) {
                adminChatBanner.className = 'support-chat-banner admin';
                adminChatBanner.textContent = `${conversation.admin.name} is currently assigned to this conversation.`;
                setVisible(adminChatBanner, true);
            } else {
                setVisible(adminChatBanner, false);
            }

            adminChatTakeoverButton.disabled = adminSubmitting || !conversation.is_awaiting_admin;
            adminChatReturnButton.disabled = adminSubmitting || !conversation.is_admin_active;
            adminChatMessageInput.disabled = false;
            adminChatSendButton.disabled = adminSubmitting;

            adminMessageList.innerHTML = messages.map((message) => {
                const rowClass = message.sender_type === 'admin'
                    ? 'admin'
                    : message.sender_type === 'system'
                        ? 'system'
                        : message.sender_type === 'bot'
                            ? 'bot'
                            : 'user';
                const senderName = message.sender_type === 'admin'
                    ? (message.sender_name || 'Admin')
                    : message.sender_type === 'bot'
                        ? 'SolBot'
                        : message.sender_type === 'system'
                            ? 'System'
                            : (message.sender_name || 'Customer');

                return `<div class="support-chat-row ${rowClass}">
                    <div class="support-chat-bubble ${rowClass}">
                        <div class="support-chat-meta">
                            <span>${escapeHtml(senderName)}</span>
                            <span>${escapeHtml(formatTime(message.created_at))}</span>
                        </div>
                        <p class="support-chat-body">${escapeHtml(message.body)}</p>
                    </div>
                </div>`;
            }).join('') || '<div class="support-chat-empty">No messages yet.</div>';

            adminMessageList.scrollTop = adminMessageList.scrollHeight;
        }

        async function fetchConversations() {
            const response = await fetch('/api/admin/chat/conversations', {
                credentials: 'same-origin',
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (!response.ok) {
                throw new Error('Could not load chat conversations.');
            }

            return response.json();
        }

        async function fetchConversation(conversationId) {
            const response = await fetch(`/api/admin/chat/conversations/${conversationId}`, {
                credentials: 'same-origin',
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (!response.ok) {
                throw new Error('Could not load the selected conversation.');
            }

            return response.json();
        }

        async function postJson(url, payload) {
            await ensureCsrfCookie();
            const csrfToken = getCookie('XSRF-TOKEN');
            const response = await fetch(url, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    ...(csrfToken ? {'X-XSRF-TOKEN': csrfToken} : {}),
                },
                body: JSON.stringify(payload || {}),
            });
            const data = await response.json().catch(() => ({}));

            if (!response.ok) {
                throw new Error(data?.message || 'The request could not be completed.');
            }

            return data;
        }

        async function loadConversationList() {
            const payload = await fetchConversations();
            const conversations = Array.isArray(payload.conversations) ? payload.conversations : [];
            adminChatState.conversations = conversations.sort((left, right) => {
                if (left.is_awaiting_admin !== right.is_awaiting_admin) {
                    return left.is_awaiting_admin ? -1 : 1;
                }

                return String(right.last_message_at || '').localeCompare(String(left.last_message_at || ''));
            });

            if (!adminChatState.selectedConversationId && adminChatState.conversations.length > 0) {
                adminChatState.selectedConversationId = adminChatState.conversations[0].id;
            }

            renderThreadList();
        }

        async function loadSelectedConversation() {
            if (!adminChatState.selectedConversationId) {
                adminChatState.selectedConversation = null;
                renderSelectedConversation();
                return;
            }

            adminChatState.selectedConversation = await fetchConversation(adminChatState.selectedConversationId);
            renderSelectedConversation();
        }

        async function refreshAdminChat() {
            try {
                adminChatError.textContent = '';
                setVisible(adminChatError, false);
                await loadConversationList();
                await loadSelectedConversation();
            } catch (error) {
                adminChatError.textContent = error?.message || 'Could not load support chats.';
                setVisible(adminChatError, true);
            }
        }

        async function takeOverConversation() {
            if (!adminChatState.selectedConversationId || adminSubmitting) {
                return;
            }

            try {
                adminSubmitting = true;
                adminChatState.selectedConversation = await postJson(`/api/admin/chat/conversations/${adminChatState.selectedConversationId}/takeover`);
                await loadConversationList();
                renderSelectedConversation();
            } catch (error) {
                adminChatError.textContent = error?.message || 'Could not take over the conversation.';
                setVisible(adminChatError, true);
            } finally {
                adminSubmitting = false;
                renderSelectedConversation();
            }
        }

        async function returnConversationToBot() {
            if (!adminChatState.selectedConversationId || adminSubmitting) {
                return;
            }

            try {
                adminSubmitting = true;
                adminChatState.selectedConversation = await postJson(`/api/admin/chat/conversations/${adminChatState.selectedConversationId}/return-to-bot`);
                await loadConversationList();
                renderSelectedConversation();
            } catch (error) {
                adminChatError.textContent = error?.message || 'Could not switch the conversation back to the chatbot.';
                setVisible(adminChatError, true);
            } finally {
                adminSubmitting = false;
                renderSelectedConversation();
            }
        }

        async function sendAdminMessage() {
            const message = adminChatMessageInput.value.trim();

            if (!message || !adminChatState.selectedConversationId || adminSubmitting) {
                return;
            }

            try {
                adminSubmitting = true;
                renderSelectedConversation();
                adminChatState.selectedConversation = await postJson(`/api/admin/chat/conversations/${adminChatState.selectedConversationId}/messages`, {message});
                adminChatMessageInput.value = '';
                await loadConversationList();
                renderSelectedConversation();
            } catch (error) {
                adminChatError.textContent = error?.message || 'Could not send the admin reply.';
                setVisible(adminChatError, true);
            } finally {
                adminSubmitting = false;
                renderSelectedConversation();
            }
        }

        adminChatRefreshButton.addEventListener('click', refreshAdminChat);
        adminChatTakeoverButton.addEventListener('click', takeOverConversation);
        adminChatReturnButton.addEventListener('click', returnConversationToBot);
        adminChatSendButton.addEventListener('click', sendAdminMessage);
        adminChatMessageInput.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' && !event.shiftKey) {
                event.preventDefault();
                sendAdminMessage();
            }
        });

        refreshAdminChat();
        window.setInterval(refreshAdminChat, 5000);
    </script>
@endpush