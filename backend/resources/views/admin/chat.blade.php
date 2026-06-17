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
            align-content: start;
            gap: 12px;
            padding: 14px;
            background: linear-gradient(180deg, #f8fbff 0%, #f3f7fb 100%);
        }

        .support-chat-thread-button {
            width: 100%;
            display: grid;
            gap: 10px;
            text-align: left;
            border: 1px solid #e2eaf3;
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.96);
            box-shadow: 0 10px 20px rgba(18, 58, 90, 0.04);
            padding: 18px 18px 20px;
            cursor: pointer;
            transition: border-color 0.2s ease, background 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
        }

        .support-chat-thread-button:hover {
            border-color: #c9d9ea;
            box-shadow: 0 14px 28px rgba(18, 58, 90, 0.08);
            transform: translateY(-1px);
        }

        .support-chat-thread-button.active {
            background: linear-gradient(180deg, #eef6ff 0%, #e5f0fb 100%);
            border-color: #bed3e6;
            box-shadow: 0 16px 30px rgba(49, 111, 173, 0.12);
        }

        .support-chat-thread-button.waiting {
            background: linear-gradient(180deg, #fff9ef 0%, #fff2dc 100%);
            border-color: #f3d3a2;
            box-shadow: 0 16px 30px rgba(191, 124, 40, 0.1);
        }

        .support-chat-thread-button.active.waiting {
            background: linear-gradient(180deg, #fff6e8 0%, #ffeccf 100%);
            border-color: #efc783;
        }

        .support-chat-thread-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            min-width: 0;
        }

        .support-chat-thread-name {
            color: #123a5a;
            font-weight: 800;
            font-size: 17px;
            line-height: 1.15;
            flex: 1;
            min-width: 0;
            overflow-wrap: anywhere;
        }

        .support-chat-thread-meta,
        .support-chat-thread-preview {
            color: #61768d;
            font-size: 13px;
            line-height: 1.5;
            min-width: 0;
            overflow-wrap: anywhere;
        }

        .support-chat-thread-meta {
            font-weight: 600;
        }

        .support-chat-thread-reason,
        .support-chat-banner-reason {
            color: #9a3412;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 0.02em;
            text-transform: uppercase;
        }

        .support-chat-thread-preview {
            color: #425972;
            font-size: 14px;
            font-weight: 700;
            display: -webkit-box;
            line-clamp: 2;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
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

        @media (max-width: 980px) {
            .support-chat-list {
                padding: 12px;
            }
        }

        .support-chat-main {
            display: flex;
            flex-direction: column;
            min-height: 0;
            height: 100%;
            overflow: hidden;
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
            flex: 1 1 auto;
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

        .support-chat-actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            align-items: center;
            justify-content: flex-start;
        }

        .support-chat-actions button {
            flex: 0 1 auto;
            min-width: 180px;
            min-height: 44px;
            padding-inline: 16px;
            white-space: nowrap;
            border-radius: 14px;
        }

        .support-chat-composer {
            flex: 0 0 auto;
            padding: 14px 20px 20px;
            display: grid;
            gap: 10px;
            border-top: 1px solid #e7eef6;
            background: linear-gradient(180deg, #f8fbff 0%, #ffffff 100%);
        }

        .support-chat-composer-shell {
            display: grid;
            gap: 14px;
            padding: 16px;
            border: 1px solid #d8e3f0;
            border-radius: 20px;
            background: #ffffff;
            box-shadow: 0 8px 20px rgba(18, 58, 90, 0.05), inset 0 1px 0 rgba(255, 255, 255, 0.8);
        }

        .support-chat-composer-row {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 116px;
            gap: 12px;
            align-items: center;
        }

        .support-chat-textarea {
            width: 100%;
            display: block;
            min-width: 0;
            height: 56px;
            min-height: 56px;
            max-height: 144px;
            resize: none;
            overflow-y: auto;
            border-radius: 16px;
            border: 1px solid #d8e3f0;
            padding: 16px 18px;
            font: inherit;
            color: #123a5a;
            background: #ffffff;
            box-sizing: border-box;
            line-height: 1.5;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .support-chat-textarea:focus {
            outline: none;
            border-color: #8bb8d9;
            box-shadow: 0 0 0 4px rgba(136, 196, 230, 0.18);
        }

        .support-chat-send-button {
            width: 116px;
            min-height: 56px;
            align-self: stretch;
            border-radius: 16px;
        }

        @media (max-width: 980px) {
            .support-chat-layout {
                grid-template-columns: 1fr;
                height: auto;
                max-height: none;
                min-height: 0;
            }

            .support-chat-actions {
                flex-direction: column;
                align-items: stretch;
            }

            .support-chat-actions button {
                width: 100%;
                min-width: 0;
            }
        }

        @media (max-width: 680px) {
            .support-chat-composer-row {
                grid-template-columns: 1fr;
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

                <div class="support-chat-composer">
                    <div class="support-chat-composer-shell">
                        <div class="support-chat-actions">
                            <button id="admin-chat-takeover-button" type="button" class="secondary" disabled>Take over chat</button>
                            <button id="admin-chat-return-button" type="button" class="secondary" disabled>Switch to chatbot</button>
                        </div>
                        <div class="support-chat-composer-row">
                            <textarea id="admin-chat-message-input" class="support-chat-textarea" placeholder="Reply as admin..." disabled></textarea>
                            <button id="admin-chat-send-button" type="button" class="primary support-chat-send-button" disabled>Send</button>
                        </div>
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

        function syncAdminComposerHeight() {
            if (!adminChatMessageInput) {
                return;
            }

            adminChatMessageInput.style.height = '56px';
            adminChatMessageInput.style.height = `${Math.min(adminChatMessageInput.scrollHeight, 144)}px`;
        }

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
                const reasonLabel = conversation.escalation_reason_label;

                return `<button type="button" class="support-chat-thread-button ${active ? 'active' : ''} ${waiting ? 'waiting' : ''}" data-conversation-id="${conversation.id}">
                    <div class="support-chat-thread-top">
                        <span class="support-chat-thread-name">${escapeHtml(conversation.customer?.name || 'Customer')}</span>
                        <span class="support-chat-badge ${badgeClass}">${badgeLabel}</span>
                    </div>
                    <div class="support-chat-thread-meta">${escapeHtml(conversation.customer?.email || '')}</div>
                    ${reasonLabel ? `<div class="support-chat-thread-reason">${escapeHtml(reasonLabel)}</div>` : ''}
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
                adminChatMessageInput.value = '';
                syncAdminComposerHeight();
                setVisible(adminChatBanner, false);
                return;
            }

            adminChatTitle.textContent = conversation.customer?.name || 'Customer conversation';
            adminChatSubtitle.textContent = conversation.customer?.email || 'Support chat thread';

            if (conversation.is_awaiting_admin) {
                adminChatBanner.className = 'support-chat-banner waiting';
                adminChatBanner.innerHTML = `${conversation.escalation_reason_label ? `<div class="support-chat-banner-reason">${escapeHtml(conversation.escalation_reason_label)}</div>` : ''}<div>This conversation has been escalated and is waiting for an admin to take over. SolBot is already paused.</div>`;
                setVisible(adminChatBanner, true);
            } else if (conversation.admin) {
                adminChatBanner.className = 'support-chat-banner admin';
                adminChatBanner.innerHTML = `${conversation.escalation_reason_label ? `<div class="support-chat-banner-reason">${escapeHtml(conversation.escalation_reason_label)}</div>` : ''}<div>${escapeHtml(conversation.admin.name)} is currently assigned to this conversation.</div>`;
                setVisible(adminChatBanner, true);
            } else {
                setVisible(adminChatBanner, false);
            }

            adminChatTakeoverButton.disabled = adminSubmitting || !conversation.is_awaiting_admin;
            adminChatReturnButton.disabled = adminSubmitting || !conversation.is_admin_active;
            adminChatMessageInput.disabled = false;
            adminChatSendButton.disabled = adminSubmitting;
            syncAdminComposerHeight();

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
                syncAdminComposerHeight();
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
        adminChatMessageInput.addEventListener('input', syncAdminComposerHeight);

        syncAdminComposerHeight();
        refreshAdminChat();
        window.setInterval(refreshAdminChat, 5000);
    </script>
@endpush
