@extends('layouts.app', ['title' => 'Contact Messages'])

@section('content')
    <style>
        .cm-list {
            display: grid;
            gap: 16px;
        }

        .cm-card {
            padding: 20px;
            border: 1px solid #DDE7EE;
            border-radius: 18px;
            background: linear-gradient(180deg, #ffffff 0%, #F8FAFC 100%);
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.04);
        }

        .cm-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 16px;
        }

        .cm-actions button {
            min-width: 96px;
        }

        .cm-detail-panel {
            display: grid;
            gap: 16px;
        }

        .cm-detail-box {
            padding: 16px;
            border: 1px solid #DDE7EE;
            border-radius: 16px;
            background: #F8FAFC;
        }

        .cm-detail-box h3 {
            margin: 0 0 8px;
            color: #123A5A;
        }

        .cm-detail-copy {
            margin: 0;
            color: #243b53;
            white-space: pre-wrap;
            line-height: 1.7;
        }

        .empty-illustration {
            width: 68px;
            height: 68px;
            border-radius: 999px;
            background: #EAF9FD;
            margin: 0 auto 16px;
        }
    </style>

    <div class="admin-page-stack">

    {{-- Hero card --}}
    <div class="card admin-hero-card">
        <div class="section-header">
            <div>
                <p class="admin-page-eyebrow">Inbox</p>
                <h1 class="page-title">Contact Messages</h1>
                <p class="page-copy">View and manage messages submitted through the public contact form.</p>
            </div>
            <div class="actions" style="margin-top: 0;">
                <button id="cm-refresh-btn" type="button" class="secondary">Refresh</button>
            </div>
        </div>

        <div id="cm-loading" class="info-box">Loading messages...</div>
        <div id="cm-success" class="status" style="display: none;"></div>
        <div id="cm-error"   class="error-box" style="display: none;"></div>
        <div id="cm-summary" class="summary-grid" style="display: none;"></div>
    </div>

    {{-- Message list card --}}
    <div class="card admin-section-surface">
        <div class="section-header">
            <div>
                <h2 class="admin-section-title">All messages</h2>
                <p class="page-copy" style="margin-bottom: 0;">Click a message to read it in full or update its status.</p>
            </div>
        </div>

        <div id="cm-empty" class="info-box" style="display: none; margin-bottom: 0;">
            <div class="empty-illustration"></div>
            No contact messages yet.
        </div>

        <div id="cm-list" class="cm-list" style="display: none;"></div>
    </div>

    {{-- Detail card --}}
    <div id="cm-detail-card" class="card admin-section-surface" style="display: none;">
        <div class="section-header">
            <div>
                <h2 id="cm-detail-heading" class="admin-section-title">Message details</h2>
                <p id="cm-detail-subtitle" class="page-copy" style="margin-bottom: 0;">Full message content and contact information.</p>
            </div>
            <div class="actions" style="margin-top: 0;">
                <button id="cm-detail-close" type="button" class="secondary">Close</button>
            </div>
        </div>

        <div id="cm-detail-panel" class="cm-detail-panel"></div>
    </div>

    </div>
@endsection

@push('scripts')
<script>
    const cmLoadingBox  = document.getElementById('cm-loading');
    const cmSuccessBox  = document.getElementById('cm-success');
    const cmErrorBox    = document.getElementById('cm-error');
    const cmSummaryGrid = document.getElementById('cm-summary');
    const cmListEl      = document.getElementById('cm-list');
    const cmEmptyEl     = document.getElementById('cm-empty');
    const cmDetailCard  = document.getElementById('cm-detail-card');
    const cmDetailPanel = document.getElementById('cm-detail-panel');
    const cmDetailClose = document.getElementById('cm-detail-close');
    const cmRefreshBtn  = document.getElementById('cm-refresh-btn');

    const cmState = { messages: [], selectedId: null };

    function cmSetVisible(el, visible, displayValue = 'block') {
        el.style.display = visible ? displayValue : 'none';
    }

    function cmClearMessages() {
        cmSuccessBox.textContent = '';
        cmErrorBox.textContent = '';
        cmSetVisible(cmSuccessBox, false);
        cmSetVisible(cmErrorBox, false);
    }

    function cmShowError(msg) {
        cmErrorBox.textContent = msg;
        cmSetVisible(cmErrorBox, true);
    }

    function cmShowSuccess(msg) {
        cmSuccessBox.textContent = msg;
        cmSetVisible(cmSuccessBox, true);
    }

    function cmEscape(val) {
        return String(val ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function cmFormatDate(val) {
        if (!val) return 'Not available';
        const d = new Date(val);
        return Number.isNaN(d.getTime()) ? val : d.toLocaleString();
    }

    function cmStatusBadge(status) {
        const s = String(status || 'unread').toLowerCase();
        if (s === 'resolved') return 'badge badge-success';
        if (s === 'read')     return 'badge badge-primary';
        return 'badge badge-warning';
    }

    function cmStatusLabel(status) {
        const s = String(status || 'unread');
        return s.charAt(0).toUpperCase() + s.slice(1);
    }

    function cmPreview(msg, max = 140) {
        const t = String(msg || '').trim();
        return t.length <= max ? t : t.slice(0, max - 3).trimEnd() + '...';
    }

    function cmGetCookie(name) {
        const prefix = `${name}=`;
        for (const part of document.cookie.split(';')) {
            const trimmed = part.trim();
            if (trimmed.startsWith(prefix)) return decodeURIComponent(trimmed.slice(prefix.length));
        }
        return null;
    }

    async function cmEnsureCsrf() {
        await fetch('/sanctum/csrf-cookie', {
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });
    }

    async function cmApi(endpoint, options = {}) {
        const { method = 'GET', body } = options;
        const headers = { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' };

        if (method !== 'GET') {
            await cmEnsureCsrf();
            headers['Content-Type'] = 'application/json';
            headers['X-XSRF-TOKEN'] = cmGetCookie('XSRF-TOKEN') || '';
        }

        const res = await fetch(endpoint, {
            method,
            credentials: 'same-origin',
            headers,
            body: body === undefined ? undefined : JSON.stringify(body),
        });

        const payload = await res.json().catch(() => ({}));
        if (!res.ok) {
            const err = new Error(payload.message || 'Request failed.');
            err.status = res.status;
            throw err;
        }
        return payload;
    }

    function cmRenderSummary() {
        const total    = cmState.messages.length;
        const unread   = cmState.messages.filter(m => m.status === 'unread').length;
        const read     = cmState.messages.filter(m => m.status === 'read').length;
        const resolved = cmState.messages.filter(m => m.status === 'resolved').length;

        if (total === 0) {
            cmSummaryGrid.innerHTML = '';
            cmSetVisible(cmSummaryGrid, false);
            return;
        }

        cmSummaryGrid.innerHTML = `
            <div class="summary-card"><div class="summary-label">Total</div><div class="summary-value">${total}</div></div>
            <div class="summary-card"><div class="summary-label">Unread</div><div class="summary-value">${unread}</div></div>
            <div class="summary-card"><div class="summary-label">Read</div><div class="summary-value">${read}</div></div>
            <div class="summary-card"><div class="summary-label">Resolved</div><div class="summary-value">${resolved}</div></div>
        `;
        cmSetVisible(cmSummaryGrid, true, 'grid');
    }

    function cmRenderList() {
        if (cmState.messages.length === 0) {
            cmListEl.innerHTML = '';
            cmSetVisible(cmListEl, false);
            cmSetVisible(cmEmptyEl, true);
            return;
        }

        cmListEl.innerHTML = cmState.messages.map(m => `
            <div class="cm-card">
                <div class="request-header">
                    <div>
                        <div class="request-title">${cmEscape(m.full_name)}</div>
                        <div class="muted">${cmEscape(m.email)}${m.phone_number ? ' &bull; ' + cmEscape(m.phone_number) : ''}</div>
                    </div>
                    <div class="request-badges">
                        <span class="${cmStatusBadge(m.status)}">${cmEscape(cmStatusLabel(m.status))}</span>
                    </div>
                </div>

                <div class="detail-grid">
                    <div class="detail-item">
                        <span class="detail-label">Subject</span>
                        <strong>${cmEscape(m.subject)}</strong>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Received</span>
                        <strong>${cmEscape(cmFormatDate(m.created_at))}</strong>
                    </div>
                </div>

                <div class="info-box" style="margin-top: 14px; margin-bottom: 0;">
                    <strong>Preview:</strong> ${cmEscape(cmPreview(m.message))}
                </div>

                <div class="cm-actions">
                    <button type="button" class="secondary" data-cm-action="view"     data-cm-id="${m.id}">View</button>
                    <button type="button"                   data-cm-action="read"     data-cm-id="${m.id}"${m.status === 'read' || m.status === 'resolved' ? ' disabled' : ''}>Mark as Read</button>
                    <button type="button"                   data-cm-action="resolved" data-cm-id="${m.id}"${m.status === 'resolved' ? ' disabled' : ''}>Mark as Resolved</button>
                    <button type="button" class="danger"    data-cm-action="delete"   data-cm-id="${m.id}">Delete</button>
                </div>
            </div>
        `).join('');

        cmSetVisible(cmEmptyEl, false);
        cmSetVisible(cmListEl, true);
    }

    function cmRenderDetail(m) {
        cmDetailPanel.innerHTML = `
            <div class="cm-detail-box">
                <h3>Contact Information</h3>
                <div class="detail-grid">
                    <div class="detail-item"><span class="detail-label">Full Name</span><strong>${cmEscape(m.full_name)}</strong></div>
                    <div class="detail-item"><span class="detail-label">Email</span><strong>${cmEscape(m.email)}</strong></div>
                    <div class="detail-item"><span class="detail-label">Phone</span><strong>${cmEscape(m.phone_number || '—')}</strong></div>
                    <div class="detail-item"><span class="detail-label">Subject</span><strong>${cmEscape(m.subject)}</strong></div>
                    <div class="detail-item"><span class="detail-label">Status</span><strong><span class="${cmStatusBadge(m.status)}">${cmEscape(cmStatusLabel(m.status))}</span></strong></div>
                    <div class="detail-item"><span class="detail-label">Received</span><strong>${cmEscape(cmFormatDate(m.created_at))}</strong></div>
                </div>
            </div>
            <div class="cm-detail-box">
                <h3>Message</h3>
                <p class="cm-detail-copy">${cmEscape(m.message)}</p>
            </div>
            <div class="cm-actions">
                <button type="button"               data-cm-action="read"     data-cm-id="${m.id}"${m.status === 'read' || m.status === 'resolved' ? ' disabled' : ''}>Mark as Read</button>
                <button type="button"               data-cm-action="resolved" data-cm-id="${m.id}"${m.status === 'resolved' ? ' disabled' : ''}>Mark as Resolved</button>
                <button type="button" class="danger" data-cm-action="delete"  data-cm-id="${m.id}">Delete</button>
            </div>
        `;
    }

    async function cmLoad() {
        cmClearMessages();
        cmSetVisible(cmLoadingBox, true);
        cmSetVisible(cmSummaryGrid, false);
        cmSetVisible(cmListEl, false);
        cmSetVisible(cmEmptyEl, false);
        cmSetVisible(cmDetailCard, false);

        try {
            const payload = await cmApi('/api/admin/contact-messages');
            cmState.messages = Array.isArray(payload.data) ? payload.data : [];
            cmSetVisible(cmLoadingBox, false);
            cmRenderSummary();
            cmRenderList();
        } catch (err) {
            cmSetVisible(cmLoadingBox, false);
            cmShowError(err.message || 'Could not load messages.');
        }
    }

    async function cmUpdateStatus(id, status) {
        cmClearMessages();
        try {
            await cmApi(`/api/admin/contact-messages/${id}/status`, {
                method: 'PATCH',
                body: { status },
            });
            const msg = cmState.messages.find(m => m.id === id);
            if (msg) msg.status = status;
            cmRenderSummary();
            cmRenderList();
            if (cmState.selectedId === id) {
                cmRenderDetail(msg);
            }
            cmShowSuccess('Status updated successfully.');
        } catch (err) {
            cmShowError(err.message || 'Could not update status.');
        }
    }

    async function cmDelete(id) {
        if (!confirm('Are you sure you want to delete this message? This cannot be undone.')) return;
        cmClearMessages();
        try {
            await cmApi(`/api/admin/contact-messages/${id}`, { method: 'DELETE' });
            cmState.messages = cmState.messages.filter(m => m.id !== id);
            if (cmState.selectedId === id) {
                cmState.selectedId = null;
                cmSetVisible(cmDetailCard, false);
            }
            cmRenderSummary();
            cmRenderList();
            cmShowSuccess('Message deleted.');
        } catch (err) {
            cmShowError(err.message || 'Could not delete message.');
        }
    }

    document.addEventListener('click', function (e) {
        const btn = e.target.closest('[data-cm-action]');
        if (!btn) return;

        const action = btn.dataset.cmAction;
        const id     = parseInt(btn.dataset.cmId, 10);

        if (action === 'view') {
            const msg = cmState.messages.find(m => m.id === id);
            if (!msg) return;
            cmState.selectedId = id;
            cmDetailCard.querySelector('#cm-detail-heading').textContent = `Message from ${msg.full_name}`;
            cmDetailCard.querySelector('#cm-detail-subtitle').textContent = `Subject: ${msg.subject}`;
            cmRenderDetail(msg);
            cmSetVisible(cmDetailCard, true);
            cmDetailCard.scrollIntoView({ behavior: 'smooth', block: 'start' });
        } else if (action === 'read') {
            cmUpdateStatus(id, 'read');
        } else if (action === 'resolved') {
            cmUpdateStatus(id, 'resolved');
        } else if (action === 'delete') {
            cmDelete(id);
        }
    });

    cmDetailClose.addEventListener('click', function () {
        cmState.selectedId = null;
        cmSetVisible(cmDetailCard, false);
    });

    cmRefreshBtn.addEventListener('click', cmLoad);

    cmLoad();
</script>
@endpush
