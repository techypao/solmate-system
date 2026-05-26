@extends('layouts.app', ['title' => 'Support Chat'])

@section('content')
    <style>
        .chat-shell {
            display: grid;
            gap: 20px;
        }

        .chat-card {
            background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
            border: 1px solid #dde7ee;
            border-radius: 20px;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.04);
        }

        .chat-head {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            align-items: center;
            padding: 20px 22px 16px;
            border-bottom: 1px solid #dde7ee;
        }

        .chat-status-banner {
            margin: 16px 22px 0;
            padding: 14px 16px;
            border-radius: 14px;
            font-size: 14px;
            line-height: 1.5;
        }

        .chat-status-banner.waiting {
            background: #fff7ed;
            border: 1px solid #fdba74;
            color: #9a3412;
        }

        .chat-status-banner.admin {
            background: #ecfdf3;
            border: 1px solid #86efac;
            color: #166534;
        }

        .chat-message-list {
            min-height: 420px;
            max-height: 60vh;
            overflow-y: auto;
            padding: 18px 22px 10px;
            display: grid;
            gap: 12px;
        }

        .chat-empty {
            padding: 48px 20px;
            text-align: center;
            color: #5e7288;
        }

        .chat-row {
            display: flex;
        }

        .chat-row.user {
            justify-content: flex-end;
        }

        .chat-row.system {
            justify-content: center;
        }

        .chat-bubble {
            max-width: min(75%, 680px);
            border-radius: 18px;
            padding: 12px 14px;
            border: 1px solid #e4eaf3;
            background: #f4f7fb;
        }

        .chat-bubble.user {
            background: #123a5a;
            border-color: #123a5a;
            color: #ffffff;
        }

        .chat-bubble.admin {
            background: #fef3c7;
            border-color: #fde68a;
            color: #92400e;
        }

        .chat-bubble.system {
            background: #eef2ff;
            border-color: #c7d2fe;
            color: #3730a3;
            text-align: center;
        }

        .chat-meta {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 8px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .chat-body {
            margin: 0;
            white-space: pre-wrap;
            line-height: 1.6;
        }

        .chat-suggestions {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 12px;
        }

        .chat-suggestion {
            padding: 8px 12px;
            border-radius: 999px;
            border: 1px solid #d8e3f0;
            background: #ffffff;
            color: #123a5a;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
        }

        .chat-composer {
            display: grid;
            gap: 12px;
            padding: 18px 22px 22px;
            border-top: 1px solid #dde7ee;
        }

        .chat-composer-actions {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }

        .chat-input-row {
            display: flex;
            gap: 10px;
            align-items: flex-end;
        }

        .chat-textarea {
            flex: 1;
            min-height: 96px;
            resize: vertical;
            border-radius: 16px;
            border: 1px solid #d8e3f0;
            padding: 14px 16px;
            font: inherit;
            color: #123a5a;
            background: #ffffff;
        }

        .chat-footnote {
            color: #5e7288;
            font-size: 13px;
        }

        @media (max-width: 768px) {
            .chat-bubble {
                max-width: 100%;
            }

            .chat-input-row {
                flex-direction: column;
            }

            .chat-input-row button {
                width: 100%;
            }
        }
    </style>

    <div class="admin-page-stack chat-shell">
        <div class="card admin-hero-card">
            <div class="section-header">
                <div>
                    <p class="admin-page-eyebrow">Customer Support</p>
                    <h1 class="page-title">Support Chat</h1>
                    <p class="page-copy">SolBot handles your questions by default. If the conversation needs a real person, an admin can take over in the same thread.</p>
                </div>
            </div>
        </div>

        <div class="chat-card">
            <div class="chat-head">
                <div>
                    <h2 class="admin-section-title" style="margin-bottom: 4px;">SolBot Conversation</h2>
                    <p class="page-copy" style="margin-bottom: 0;">The chat stays in one thread whether you are talking to SolBot or a real admin.</p>
                </div>
                <button id="chat-refresh-button" type="button" class="secondary">Refresh</button>
            </div>

            <div id="chat-status-banner" class="chat-status-banner waiting" style="display: none;"></div>
            <div id="chat-error" class="error-box" style="display:none; margin: 16px 22px 0;"></div>
            <div id="chat-message-list" class="chat-message-list">
                <div class="chat-empty">Loading your conversation...</div>
            </div>

            <div class="chat-composer">
                <div class="chat-composer-actions">
                    <button id="chat-escalate-button" type="button" class="secondary">Talk to a real admin</button>
                    <div class="chat-footnote">SolBot pauses automatically when an admin takes over.</div>
                </div>

                <div class="chat-input-row">
                    <textarea id="chat-message-input" class="chat-textarea" placeholder="Type your message..."></textarea>
                    <button id="chat-send-button" type="button" class="primary">Send</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const chatMessageList = document.getElementById('chat-message-list');
        const chatError = document.getElementById('chat-error');
        const chatStatusBanner = document.getElementById('chat-status-banner');
        const chatMessageInput = document.getElementById('chat-message-input');
        const chatSendButton = document.getElementById('chat-send-button');
        const chatEscalateButton = document.getElementById('chat-escalate-button');
        const chatRefreshButton = document.getElementById('chat-refresh-button');
        let chatState = {conversation: null, messages: []};
        let submittingChat = false;

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

        function formatChatTime(value) {
            return value
                ? new Date(value).toLocaleTimeString([], {hour: 'numeric', minute: '2-digit'})
                : '';
        }

        function senderLabel(message) {
            if (message.sender_type === 'user') {
                return 'You';
            }

            if (message.sender_type === 'admin') {
                return message.sender_name || 'Admin';
            }

            if (message.sender_type === 'system') {
                return 'System';
            }

            return 'SolBot';
        }

        function renderStatusBanner() {
            const conversation = chatState.conversation;

            if (!conversation) {
                setVisible(chatStatusBanner, false);
                return;
            }

            if (conversation.is_admin_active && conversation.admin) {
                chatStatusBanner.className = 'chat-status-banner admin';
                chatStatusBanner.textContent = `You are now chatting with ${conversation.admin.name}. SolBot is paused while admin support is active.`;
                setVisible(chatStatusBanner, true);
                return;
            }

            if (conversation.is_awaiting_admin) {
                chatStatusBanner.className = 'chat-status-banner waiting';
                chatStatusBanner.textContent = 'A real admin has been requested. SolBot is paused until an admin joins this conversation.';
                setVisible(chatStatusBanner, true);
                return;
            }

            setVisible(chatStatusBanner, false);
        }

        function renderMessages() {
            const messages = Array.isArray(chatState.messages) ? chatState.messages : [];

            if (messages.length === 0) {
                chatMessageList.innerHTML = '<div class="chat-empty">Start the conversation with SolBot. An admin can take over here if needed.</div>';
                return;
            }

            chatMessageList.innerHTML = messages.map((message) => {
                const bubbleClass = message.sender_type === 'user'
                    ? 'user'
                    : message.sender_type === 'admin'
                        ? 'admin'
                        : message.sender_type === 'system'
                            ? 'system'
                            : 'bot';
                const suggestions = Array.isArray(message.metadata?.suggestions)
                    ? message.metadata.suggestions
                    : [];
                const suggestionMarkup = bubbleClass === 'bot' && suggestions.length > 0 && !chatState.conversation?.is_admin_active
                    ? `<div class="chat-suggestions">${suggestions.map((suggestion) => `<button type="button" class="chat-suggestion" data-suggestion="${escapeHtml(suggestion)}">${escapeHtml(suggestion)}</button>`).join('')}</div>`
                    : '';

                return `<div class="chat-row ${bubbleClass}">
                    <div class="chat-bubble ${bubbleClass}">
                        <div class="chat-meta">
                            <span>${escapeHtml(senderLabel(message))}</span>
                            <span>${escapeHtml(formatChatTime(message.created_at))}</span>
                        </div>
                        <p class="chat-body">${escapeHtml(message.body)}</p>
                        ${suggestionMarkup}
                    </div>
                </div>`;
            }).join('');

            chatMessageList.scrollTop = chatMessageList.scrollHeight;
            chatMessageList.querySelectorAll('[data-suggestion]').forEach((button) => {
                button.addEventListener('click', () => {
                    chatMessageInput.value = button.getAttribute('data-suggestion') || '';
                    sendMessage();
                });
            });
        }

        function syncComposerState() {
            const isAdminActive = !!chatState.conversation?.is_admin_active;
            chatEscalateButton.disabled = submittingChat || isAdminActive;
            chatSendButton.disabled = submittingChat;
        }

        async function fetchConversation() {
            const response = await fetch('/api/chat/conversation', {
                credentials: 'same-origin',
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (!response.ok) {
                throw new Error('Could not load the support conversation.');
            }

            return response.json();
        }

        async function loadConversation() {
            try {
                chatState = await fetchConversation();
                chatError.textContent = '';
                setVisible(chatError, false);
                renderStatusBanner();
                renderMessages();
                syncComposerState();
            } catch (error) {
                chatError.textContent = error?.message || 'Could not load the support conversation.';
                setVisible(chatError, true);
            }
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

        async function sendMessage() {
            const message = chatMessageInput.value.trim();

            if (!message || submittingChat) {
                return;
            }

            try {
                submittingChat = true;
                syncComposerState();
                chatState = await postJson('/api/chat/conversation/messages', {message});
                chatMessageInput.value = '';
                renderStatusBanner();
                renderMessages();
            } catch (error) {
                chatError.textContent = error?.message || 'Could not send your message.';
                setVisible(chatError, true);
            } finally {
                submittingChat = false;
                syncComposerState();
            }
        }

        async function escalateConversation() {
            if (submittingChat || chatState.conversation?.is_admin_active) {
                return;
            }

            try {
                submittingChat = true;
                syncComposerState();
                chatState = await postJson('/api/chat/conversation/escalate');
                renderStatusBanner();
                renderMessages();
            } catch (error) {
                chatError.textContent = error?.message || 'Could not request admin takeover.';
                setVisible(chatError, true);
            } finally {
                submittingChat = false;
                syncComposerState();
            }
        }

        chatSendButton.addEventListener('click', sendMessage);
        chatEscalateButton.addEventListener('click', escalateConversation);
        chatRefreshButton.addEventListener('click', loadConversation);
        chatMessageInput.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' && !event.shiftKey) {
                event.preventDefault();
                sendMessage();
            }
        });

        loadConversation();
        window.setInterval(loadConversation, 5000);
    </script>
@endpush