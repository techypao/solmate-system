@extends('layouts.app', ['title' => 'Admin Testimonies'])

@section('content')
    <style>
        .testimony-list {
            display: grid;
            gap: 16px;
        }

        .testimony-card {
            padding: 18px;
            border: 1px solid #d9e2ec;
            border-radius: 12px;
            background: #fbfcfe;
        }

        .testimony-preview {
            margin: 0;
            color: #52606d;
            font-size: 14px;
            line-height: 1.6;
        }

        .testimony-title {
            margin: 0 0 6px;
            color: #102a43;
            font-size: 18px;
            font-weight: 700;
        }

        .testimony-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 16px;
        }

        .testimony-actions button {
            min-width: 96px;
        }

        .badge-danger {
            background: #fde8e8;
            color: #8a1c1c;
            border-color: #f8b4b4;
        }

        .detail-panel {
            display: grid;
            gap: 16px;
        }

        .detail-box {
            padding: 16px;
            border: 1px solid #d9e2ec;
            border-radius: 12px;
            background: #fbfcfe;
        }

        .detail-box h3 {
            margin: 0 0 8px;
            color: #102a43;
        }

        .detail-copy {
            margin: 0;
            color: #243b53;
            white-space: pre-wrap;
        }

        .image-grid {
            display: grid;
            gap: 12px;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
        }

        .image-tile {
            display: block;
            border: 1px solid #d9e2ec;
            border-radius: 12px;
            overflow: hidden;
            background: #ffffff;
        }

        .image-tile img {
            display: block;
            width: 100%;
            height: 140px;
            object-fit: cover;
            background: #e8f1fb;
        }

        .image-tile span {
            display: block;
            padding: 10px 12px;
            color: #334e68;
            font-size: 13px;
        }

        .empty-illustration {
            width: 68px;
            height: 68px;
            border-radius: 999px;
            background: #dbeafe;
            margin: 0 auto 16px;
        }
    </style>

    <div class="card">
        <div class="section-header">
            <div>
                <h1 class="page-title">Admin Testimonies</h1>
                <p class="page-copy">Review customer submissions, inspect uploaded images, and moderate what can appear publicly.</p>
            </div>
            <div class="actions" style="margin-top: 0;">
                <button id="refresh-testimonies-button" type="button" class="secondary">Refresh</button>
            </div>
        </div>

        <div id="testimonies-loading" class="info-box">Loading testimonies...</div>
        <div id="testimonies-success" class="status" style="display: none;"></div>
        <div id="testimonies-error" class="error-box" style="display: none;"></div>
        <div id="testimonies-summary" class="summary-grid" style="display: none;"></div>
    </div>

    <div class="card">
        <div class="section-header">
            <div>
                <h2 style="margin: 0 0 6px;">Moderation queue</h2>
                <p class="page-copy" style="margin-bottom: 0;">View, approve, reject, edit, or delete testimonies without leaving the admin workflow.</p>
            </div>
        </div>

        <div id="testimonies-empty" class="info-box" style="display: none; margin-bottom: 0;">
            <div class="empty-illustration"></div>
            No testimonies found yet.
        </div>

        <div id="testimonies-list" class="testimony-list" style="display: none;"></div>
    </div>

    <div id="testimony-detail-card" class="card" style="display: none;">
        <div class="section-header">
            <div>
                <h2 id="detail-heading" style="margin: 0 0 6px;">Testimony details</h2>
                <p id="detail-subtitle" class="page-copy" style="margin-bottom: 0;">Inspect the full submission before moderating it.</p>
            </div>
            <div class="actions" style="margin-top: 0;">
                <button id="detail-close-button" type="button" class="secondary">Close</button>
            </div>
        </div>

        <div id="detail-view-panel" class="detail-panel"></div>

        <form id="testimony-edit-form" class="form-grid" style="display: none;">
            <div class="form-grid two-columns">
                <div>
                    <label for="edit_rating">Rating</label>
                    <select id="edit_rating" name="rating" required>
                        @for ($rating = 1; $rating <= 5; $rating++)
                            <option value="{{ $rating }}">{{ $rating }}</option>
                        @endfor
                    </select>
                    <div class="field-error" data-edit-error-for="rating"></div>
                </div>

                <div>
                    <label for="edit_status">Status</label>
                    <select id="edit_status" name="status">
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                    </select>
                    <div class="field-error" data-edit-error-for="status"></div>
                </div>
            </div>

            <div>
                <label for="edit_title">Title</label>
                <input id="edit_title" name="title" type="text" maxlength="255">
                <div class="field-error" data-edit-error-for="title"></div>
            </div>

            <div>
                <label for="edit_message">Message</label>
                <textarea id="edit_message" name="message" rows="6" required></textarea>
                <div class="field-error" data-edit-error-for="message"></div>
            </div>

            <div>
                <label for="edit_admin_note">Admin note</label>
                <textarea id="edit_admin_note" name="admin_note" rows="4"></textarea>
                <div class="field-error" data-edit-error-for="admin_note"></div>
            </div>

            <div class="info-box" style="margin-bottom: 0;">
                Linked request stays attached to the original completed service or inspection request during admin edits.
            </div>

            <div class="actions">
                <button id="save-edit-button" type="submit">Save changes</button>
                <button id="cancel-edit-button" type="button" class="secondary">Cancel</button>
            </div>
        </form>

        <form id="testimony-reject-form" class="form-grid" style="display: none;">
            <div>
                <label for="reject_admin_note">Optional rejection note</label>
                <textarea id="reject_admin_note" name="admin_note" rows="5" placeholder="Explain why this testimony was rejected if the customer needs guidance."></textarea>
                <div class="field-error" data-reject-error-for="admin_note"></div>
            </div>

            <div class="actions">
                <button id="submit-reject-button" type="submit">Reject testimony</button>
                <button id="cancel-reject-button" type="button" class="secondary">Cancel</button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        const loadingBox = document.getElementById('testimonies-loading');
        const successBox = document.getElementById('testimonies-success');
        const errorBox = document.getElementById('testimonies-error');
        const summaryGrid = document.getElementById('testimonies-summary');
        const listContainer = document.getElementById('testimonies-list');
        const emptyState = document.getElementById('testimonies-empty');
        const detailCard = document.getElementById('testimony-detail-card');
        const detailHeading = document.getElementById('detail-heading');
        const detailSubtitle = document.getElementById('detail-subtitle');
        const detailViewPanel = document.getElementById('detail-view-panel');
        const refreshButton = document.getElementById('refresh-testimonies-button');
        const detailCloseButton = document.getElementById('detail-close-button');
        const editForm = document.getElementById('testimony-edit-form');
        const rejectForm = document.getElementById('testimony-reject-form');
        const saveEditButton = document.getElementById('save-edit-button');
        const submitRejectButton = document.getElementById('submit-reject-button');
        const cancelEditButton = document.getElementById('cancel-edit-button');
        const cancelRejectButton = document.getElementById('cancel-reject-button');

        const state = {
            testimonies: [],
            selectedId: null,
            detailMode: 'view',
        };

        function setVisible(element, visible, displayValue = 'block') {
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

        async function ensureCsrfCookie() {
            await fetch('/sanctum/csrf-cookie', {
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });
        }

        function clearMessages() {
            successBox.textContent = '';
            errorBox.textContent = '';
            setVisible(successBox, false);
            setVisible(errorBox, false);
        }

        function showError(message) {
            errorBox.textContent = message;
            setVisible(errorBox, true);
        }

        function showSuccess(message) {
            successBox.textContent = message;
            setVisible(successBox, true);
        }

        function clearFieldErrors(selector) {
            document.querySelectorAll(selector).forEach((element) => {
                element.textContent = '';
            });
        }

        function applyFieldErrors(errors, attributeName) {
            Object.entries(errors || {}).forEach(([field, messages]) => {
                const element = document.querySelector(`[${attributeName}="${field}"]`);

                if (element && Array.isArray(messages) && messages.length > 0) {
                    element.textContent = messages[0];
                }
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

        function formatDate(value) {
            if (!value) {
                return 'Not available';
            }

            const parsedDate = new Date(value);

            if (Number.isNaN(parsedDate.getTime())) {
                return value;
            }

            return parsedDate.toLocaleString();
        }

        function formatStatusLabel(status) {
            const normalizedStatus = String(status || 'pending').toLowerCase();

            if (normalizedStatus === 'approved') {
                return 'Approved';
            }

            if (normalizedStatus === 'rejected') {
                return 'Rejected';
            }

            return 'Pending';
        }

        function formatRequestStatusLabel(status) {
            const normalizedStatus = String(status || 'pending')
                .replace(/[_-]+/g, ' ')
                .trim();

            if (!normalizedStatus) {
                return 'Not available';
            }

            return normalizedStatus.replace(/\b\w/g, (character) => character.toUpperCase());
        }

        function getStatusClass(status) {
            const normalizedStatus = String(status || 'pending').toLowerCase();

            if (normalizedStatus === 'approved') {
                return 'badge badge-success';
            }

            if (normalizedStatus === 'rejected') {
                return 'badge badge-danger';
            }

            return 'badge badge-warning';
        }

        function getLinkedRequestLabel(testimony) {
            if (testimony.service_request) {
                const requestType = testimony.service_request.request_type || 'Service';
                return `Service Request #${testimony.service_request.id} - ${requestType}`;
            }

            if (testimony.inspection_request) {
                return `Inspection Request #${testimony.inspection_request.id}`;
            }

            return 'Linked request unavailable';
        }

        function getLinkedRequestMeta(testimony) {
            if (testimony.service_request) {
                return `Status: ${formatRequestStatusLabel(testimony.service_request.status)}${testimony.service_request.date_needed ? ` | Preferred date: ${formatDate(testimony.service_request.date_needed)}` : ''}`;
            }

            if (testimony.inspection_request) {
                return `Status: ${formatRequestStatusLabel(testimony.inspection_request.status)}${testimony.inspection_request.date_needed ? ` | Preferred date: ${formatDate(testimony.inspection_request.date_needed)}` : ''}`;
            }

            return 'No linked request metadata available.';
        }

        function getCustomerName(testimony) {
            return testimony.user?.name || 'Unknown customer';
        }

        function getMessagePreview(message) {
            const trimmed = String(message || '').trim();

            if (!trimmed) {
                return 'No message provided.';
            }

            if (trimmed.length <= 140) {
                return trimmed;
            }

            return `${trimmed.slice(0, 137).trimEnd()}...`;
        }

        function getImageCountLabel(images) {
            const count = Array.isArray(images) ? images.length : 0;
            return `${count} image${count === 1 ? '' : 's'}`;
        }

        function getSelectedTestimony() {
            return state.testimonies.find((testimony) => testimony.id === state.selectedId) || null;
        }

        async function apiRequest(endpoint, options = {}) {
            const {
                method = 'GET',
                body,
            } = options;

            const headers = {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            };

            if (method !== 'GET') {
                await ensureCsrfCookie();
                headers['Content-Type'] = 'application/json';
                headers['X-XSRF-TOKEN'] = getCookie('XSRF-TOKEN') || '';
            }

            const response = await fetch(endpoint, {
                method,
                credentials: 'same-origin',
                headers,
                body: body === undefined ? undefined : JSON.stringify(body),
            });

            const payload = await response.json().catch(() => ({}));

            if (!response.ok) {
                const error = new Error(payload.message || 'Request failed.');
                error.status = response.status;
                error.errors = payload.errors || {};
                throw error;
            }

            return payload;
        }

        function renderSummary() {
            if (state.testimonies.length === 0) {
                summaryGrid.innerHTML = '';
                setVisible(summaryGrid, false);
                return;
            }

            const pendingCount = state.testimonies.filter((testimony) => testimony.status === 'pending').length;
            const approvedCount = state.testimonies.filter((testimony) => testimony.status === 'approved').length;
            const rejectedCount = state.testimonies.filter((testimony) => testimony.status === 'rejected').length;

            summaryGrid.innerHTML = `
                <div class="summary-card">
                    <div class="summary-label">Total testimonies</div>
                    <div class="summary-value">${state.testimonies.length}</div>
                </div>
                <div class="summary-card">
                    <div class="summary-label">Pending review</div>
                    <div class="summary-value">${pendingCount}</div>
                </div>
                <div class="summary-card">
                    <div class="summary-label">Approved</div>
                    <div class="summary-value">${approvedCount}</div>
                </div>
                <div class="summary-card">
                    <div class="summary-label">Rejected</div>
                    <div class="summary-value">${rejectedCount}</div>
                </div>
            `;

            setVisible(summaryGrid, true, 'grid');
        }

        function renderList() {
            if (state.testimonies.length === 0) {
                listContainer.innerHTML = '';
                setVisible(listContainer, false);
                setVisible(emptyState, true);
                return;
            }

            listContainer.innerHTML = state.testimonies.map((testimony) => {
                const firstImage = Array.isArray(testimony.images) ? testimony.images[0] : null;

                return `
                    <div class="testimony-card">
                        <div class="request-header">
                            <div>
                                <div class="request-title">${escapeHtml(testimony.title || 'Untitled testimony')}</div>
                                <div class="muted">Customer: ${escapeHtml(getCustomerName(testimony))}</div>
                            </div>
                            <div class="request-badges">
                                <span class="${getStatusClass(testimony.status)}">${escapeHtml(formatStatusLabel(testimony.status))}</span>
                                <span class="badge badge-primary">${escapeHtml(`${Number(testimony.rating || 0)}/5`)}</span>
                            </div>
                        </div>

                        <div class="detail-grid">
                            <div class="detail-item">
                                <span class="detail-label">Linked Request</span>
                                <strong>${escapeHtml(getLinkedRequestLabel(testimony))}</strong>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Created</span>
                                <strong>${escapeHtml(formatDate(testimony.created_at))}</strong>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Images</span>
                                <strong>${escapeHtml(getImageCountLabel(testimony.images))}</strong>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Preview Image</span>
                                <strong>${firstImage?.image_url ? 'Available' : 'None'}</strong>
                            </div>
                        </div>

                        <div class="info-box" style="margin-top: 14px; margin-bottom: 0;">
                            <strong>Message preview:</strong> ${escapeHtml(getMessagePreview(testimony.message))}
                        </div>

                        <div class="testimony-actions">
                            <button type="button" class="secondary" data-action="view" data-id="${testimony.id}">View</button>
                            <button type="button" data-action="approve" data-id="${testimony.id}">Approve</button>
                            <button type="button" class="secondary" data-action="reject" data-id="${testimony.id}">Reject</button>
                            <button type="button" class="secondary" data-action="edit" data-id="${testimony.id}">Edit</button>
                            <button type="button" class="secondary" data-action="delete" data-id="${testimony.id}">Delete</button>
                        </div>
                    </div>
                `;
            }).join('');

            setVisible(emptyState, false);
            setVisible(listContainer, true);
        }

        function populateEditForm(testimony) {
            editForm.elements.namedItem('rating').value = String(testimony.rating || 1);
            editForm.elements.namedItem('status').value = testimony.status || 'pending';
            editForm.elements.namedItem('title').value = testimony.title || '';
            editForm.elements.namedItem('message').value = testimony.message || '';
            editForm.elements.namedItem('admin_note').value = testimony.admin_note || '';
        }

        function populateRejectForm(testimony) {
            rejectForm.elements.namedItem('admin_note').value = testimony.admin_note || '';
        }

        function renderDetailView(testimony) {
            const images = Array.isArray(testimony.images) ? testimony.images : [];
            const imageMarkup = images.length > 0
                ? `
                    <div class="image-grid">
                        ${images.map((image, index) => {
                            const imageUrl = image.image_url || '';

                            if (!imageUrl) {
                                return `
                                    <div class="image-tile">
                                        <div style="height: 140px; background: #e8f1fb;"></div>
                                        <span>Image ${index + 1} unavailable</span>
                                    </div>
                                `;
                            }

                            return `
                                <a class="image-tile" href="${escapeHtml(imageUrl)}" target="_blank" rel="noreferrer">
                                    <img src="${escapeHtml(imageUrl)}" alt="Testimony image ${index + 1}">
                                    <span>Image ${index + 1}</span>
                                </a>
                            `;
                        }).join('')}
                    </div>
                `
                : '<div class="info-box" style="margin-bottom: 0;">No uploaded images for this testimony.</div>';

            const adminNoteMarkup = testimony.admin_note
                ? `
                    <div class="detail-box">
                        <h3>Admin note</h3>
                        <p class="detail-copy">${escapeHtml(testimony.admin_note)}</p>
                    </div>
                `
                : '';

            detailViewPanel.innerHTML = `
                <div class="detail-grid">
                    <div class="detail-item">
                        <span class="detail-label">Customer</span>
                        <strong>${escapeHtml(getCustomerName(testimony))}</strong>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Linked Request</span>
                        <strong>${escapeHtml(getLinkedRequestLabel(testimony))}</strong>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Request Meta</span>
                        <strong>${escapeHtml(getLinkedRequestMeta(testimony))}</strong>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Created</span>
                        <strong>${escapeHtml(formatDate(testimony.created_at))}</strong>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Updated</span>
                        <strong>${escapeHtml(formatDate(testimony.updated_at))}</strong>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Status</span>
                        <strong>${escapeHtml(formatStatusLabel(testimony.status))}</strong>
                    </div>
                </div>

                <div class="detail-box">
                    <h3>Full message</h3>
                    <p class="detail-copy">${escapeHtml(testimony.message || '')}</p>
                </div>

                <div class="detail-box">
                    <h3>Uploaded images</h3>
                    ${imageMarkup}
                </div>

                ${adminNoteMarkup}

                <div class="actions" style="margin-top: 0;">
                    <button id="detail-approve-action" type="button">Approve</button>
                    <button id="detail-reject-action" type="button" class="secondary">Reject</button>
                    <button id="detail-edit-action" type="button" class="secondary">Edit</button>
                    <button id="detail-delete-action" type="button" class="secondary">Delete</button>
                </div>
            `;

            const approveButton = document.getElementById('detail-approve-action');
            const rejectButton = document.getElementById('detail-reject-action');
            const editButton = document.getElementById('detail-edit-action');
            const deleteButton = document.getElementById('detail-delete-action');

            approveButton?.addEventListener('click', () => moderateTestimony(testimony.id, 'approve'));
            rejectButton?.addEventListener('click', () => openDetail(testimony.id, 'reject'));
            editButton?.addEventListener('click', () => openDetail(testimony.id, 'edit'));
            deleteButton?.addEventListener('click', () => handleDelete(testimony.id));
        }

        function renderDetail() {
            const testimony = getSelectedTestimony();

            if (!testimony) {
                detailViewPanel.innerHTML = '';
                setVisible(detailCard, false);
                return;
            }

            detailHeading.textContent = testimony.title || `Testimony #${testimony.id}`;
            detailSubtitle.textContent = `Customer: ${getCustomerName(testimony)} | Linked request: ${getLinkedRequestLabel(testimony)}`;

            renderDetailView(testimony);
            populateEditForm(testimony);
            populateRejectForm(testimony);
            clearFieldErrors('[data-edit-error-for]');
            clearFieldErrors('[data-reject-error-for]');

            setVisible(detailViewPanel, state.detailMode === 'view');
            setVisible(editForm, state.detailMode === 'edit');
            setVisible(rejectForm, state.detailMode === 'reject');
            setVisible(detailCard, true);
        }

        function openDetail(id, mode = 'view') {
            state.selectedId = id;
            state.detailMode = mode;
            renderDetail();
            detailCard.scrollIntoView({
                behavior: 'smooth',
                block: 'start',
            });
        }

        function closeDetail() {
            state.selectedId = null;
            state.detailMode = 'view';
            renderDetail();
        }

        async function loadTestimonies() {
            clearMessages();
            setVisible(loadingBox, true);

            try {
                const response = await apiRequest('/api/admin/testimonies');
                const testimonies = Array.isArray(response.data) ? response.data : [];

                state.testimonies = testimonies;

                if (state.selectedId && !state.testimonies.some((testimony) => testimony.id === state.selectedId)) {
                    state.selectedId = null;
                    state.detailMode = 'view';
                }

                renderSummary();
                renderList();
                renderDetail();
            } catch (error) {
                showError(error.message || 'Could not load testimonies.');
                summaryGrid.innerHTML = '';
                listContainer.innerHTML = '';
                setVisible(summaryGrid, false);
                setVisible(listContainer, false);
                setVisible(emptyState, false);
                closeDetail();
            } finally {
                setVisible(loadingBox, false);
            }
        }

        async function moderateTestimony(id, action, adminNote = null) {
            clearMessages();

            try {
                const response = await apiRequest(`/api/admin/testimonies/${id}/${action}`, {
                    method: 'PATCH',
                    body: {
                        admin_note: adminNote,
                    },
                });

                showSuccess(response.message || 'Testimony updated successfully.');
                await loadTestimonies();

                if (state.selectedId === id) {
                    openDetail(id, 'view');
                }
            } catch (error) {
                showError(error.message || 'Could not update testimony.');
            }
        }

        async function handleDelete(id) {
            const selectedTestimony = state.testimonies.find((testimony) => testimony.id === id);

            if (!selectedTestimony) {
                return;
            }

            if (!window.confirm(`Delete "${selectedTestimony.title || `Testimony #${selectedTestimony.id}`}"? This also removes its uploaded image records.`)) {
                return;
            }

            clearMessages();

            try {
                const response = await apiRequest(`/api/admin/testimonies/${id}`, {
                    method: 'DELETE',
                });

                showSuccess(response.message || 'Testimony deleted successfully.');

                if (state.selectedId === id) {
                    closeDetail();
                }

                await loadTestimonies();
            } catch (error) {
                showError(error.message || 'Could not delete testimony.');
            }
        }

        listContainer.addEventListener('click', (event) => {
            const button = event.target.closest('button[data-action]');

            if (!button) {
                return;
            }

            const action = button.dataset.action;
            const id = Number(button.dataset.id);

            if (!id) {
                return;
            }

            if (action === 'view') {
                openDetail(id, 'view');
                return;
            }

            if (action === 'edit') {
                openDetail(id, 'edit');
                return;
            }

            if (action === 'reject') {
                openDetail(id, 'reject');
                return;
            }

            if (action === 'approve') {
                moderateTestimony(id, 'approve');
                return;
            }

            if (action === 'delete') {
                handleDelete(id);
            }
        });

        editForm.addEventListener('submit', async (event) => {
            event.preventDefault();

            const testimony = getSelectedTestimony();

            if (!testimony) {
                return;
            }

            clearMessages();
            clearFieldErrors('[data-edit-error-for]');
            saveEditButton.disabled = true;
            saveEditButton.textContent = 'Saving...';

            try {
                const payload = {
                    rating: Number(editForm.elements.namedItem('rating').value),
                    title: editForm.elements.namedItem('title').value || null,
                    message: editForm.elements.namedItem('message').value,
                    status: editForm.elements.namedItem('status').value || null,
                    admin_note: editForm.elements.namedItem('admin_note').value || null,
                };

                const response = await apiRequest(`/api/admin/testimonies/${testimony.id}`, {
                    method: 'PUT',
                    body: payload,
                });

                showSuccess(response.message || 'Testimony updated successfully.');
                state.detailMode = 'view';
                await loadTestimonies();
                openDetail(testimony.id, 'view');
            } catch (error) {
                applyFieldErrors(error.errors || {}, 'data-edit-error-for');
                showError(error.message || 'Could not update testimony.');
            } finally {
                saveEditButton.disabled = false;
                saveEditButton.textContent = 'Save changes';
            }
        });

        rejectForm.addEventListener('submit', async (event) => {
            event.preventDefault();

            const testimony = getSelectedTestimony();

            if (!testimony) {
                return;
            }

            clearMessages();
            clearFieldErrors('[data-reject-error-for]');
            submitRejectButton.disabled = true;
            submitRejectButton.textContent = 'Rejecting...';

            try {
                const adminNote = rejectForm.elements.namedItem('admin_note').value || null;
                const response = await apiRequest(`/api/admin/testimonies/${testimony.id}/reject`, {
                    method: 'PATCH',
                    body: {
                        admin_note: adminNote,
                    },
                });

                showSuccess(response.message || 'Testimony rejected successfully.');
                state.detailMode = 'view';
                await loadTestimonies();
                openDetail(testimony.id, 'view');
            } catch (error) {
                applyFieldErrors(error.errors || {}, 'data-reject-error-for');
                showError(error.message || 'Could not reject testimony.');
            } finally {
                submitRejectButton.disabled = false;
                submitRejectButton.textContent = 'Reject testimony';
            }
        });

        refreshButton.addEventListener('click', loadTestimonies);
        detailCloseButton.addEventListener('click', closeDetail);
        cancelEditButton.addEventListener('click', () => {
            const testimony = getSelectedTestimony();

            if (testimony) {
                openDetail(testimony.id, 'view');
            }
        });
        cancelRejectButton.addEventListener('click', () => {
            const testimony = getSelectedTestimony();

            if (testimony) {
                openDetail(testimony.id, 'view');
            }
        });

        loadTestimonies();
    </script>
@endpush
