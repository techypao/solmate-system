@extends('layouts.app', ['title' => 'My Testimonies'])

@section('content')
    <style>
        .testimony-shell {
            display: grid;
            gap: 16px;
        }

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

        .testimony-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 16px;
        }

        .testimony-actions button {
            min-width: 96px;
        }

        .image-grid {
            display: grid;
            gap: 12px;
            grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
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
            height: 130px;
            object-fit: cover;
            background: #e8f1fb;
        }

        .image-tile span {
            display: block;
            padding: 10px 12px;
            color: #334e68;
            font-size: 13px;
        }

        .existing-image-row {
            display: grid;
            gap: 12px;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        }

        .existing-image-card {
            border: 1px solid #d9e2ec;
            border-radius: 12px;
            background: #ffffff;
            overflow: hidden;
        }

        .existing-image-card img {
            display: block;
            width: 100%;
            height: 150px;
            object-fit: cover;
            background: #e8f1fb;
        }

        .existing-image-card label {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px;
            margin: 0;
            font-weight: 500;
        }

        .existing-image-card input {
            width: auto;
            margin: 0;
        }

        .badge-danger {
            background: #fde8e8;
            color: #8a1c1c;
            border-color: #f8b4b4;
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
                <h1 class="page-title">My Testimonies</h1>
                <p class="page-copy">Create, update, and track your own testimonies from the customer website dashboard.</p>
            </div>
            <div class="actions" style="margin-top: 0;">
                <button id="add-testimony-button" type="button">Add Testimony</button>
                <button id="refresh-testimonies-button" type="button" class="secondary">Refresh</button>
            </div>
        </div>

        <div id="testimonies-loading" class="info-box">Loading your testimonies...</div>
        <div id="testimonies-success" class="status" style="display: none;"></div>
        <div id="testimonies-error" class="error-box" style="display: none;"></div>
    </div>

    <div class="testimony-shell">
        <div class="card">
            <div class="section-header">
                <div>
                    <h2 id="form-heading" style="margin: 0 0 6px;">Add testimony</h2>
                    <p id="form-copy" class="page-copy" style="margin-bottom: 0;">Choose a completed service or inspection request, write your testimony, and attach photos if needed.</p>
                </div>
            </div>

            <form id="testimony-form" class="form-grid">
                <div class="form-grid two-columns">
                    <div>
                        <label for="service_request_id">Completed service request</label>
                        <select id="service_request_id" name="service_request_id">
                            <option value="">Select a completed service request</option>
                        </select>
                        <div class="field-error" data-error-for="service_request_id"></div>
                    </div>

                    <div>
                        <label for="inspection_request_id">Completed inspection request</label>
                        <select id="inspection_request_id" name="inspection_request_id">
                            <option value="">Select a completed inspection request</option>
                        </select>
                        <div class="field-error" data-error-for="inspection_request_id"></div>
                    </div>
                </div>

                <div class="form-grid two-columns">
                    <div>
                        <label for="rating">Rating</label>
                        <select id="rating" name="rating" required>
                            <option value="">Select rating</option>
                            @for ($rating = 1; $rating <= 5; $rating++)
                                <option value="{{ $rating }}">{{ $rating }}</option>
                            @endfor
                        </select>
                        <div class="field-error" data-error-for="rating"></div>
                    </div>

                    <div>
                        <label for="title">Title</label>
                        <input id="title" name="title" type="text" maxlength="255" placeholder="Optional title">
                        <div class="field-error" data-error-for="title"></div>
                    </div>
                </div>

                <div>
                    <label for="message">Message</label>
                    <textarea id="message" name="message" rows="6" required placeholder="Share your experience"></textarea>
                    <div class="field-error" data-error-for="message"></div>
                </div>

                <div>
                    <label for="images">Upload images</label>
                    <input id="images" name="images" type="file" accept="image/*" multiple>
                    <div class="muted" style="margin-top: 8px;">You can upload multiple images. Existing images remain unless you choose to remove them while editing.</div>
                    <div class="field-error" data-error-for="images"></div>
                </div>

                <div id="existing-images-section" style="display: none;">
                    <label style="margin-bottom: 12px;">Existing uploaded images</label>
                    <div id="existing-images-list" class="existing-image-row"></div>
                    <div class="field-error" data-error-for="remove_image_ids"></div>
                </div>

                <div class="actions">
                    <button id="save-testimony-button" type="submit">Submit testimony</button>
                    <button id="cancel-edit-button" type="button" class="secondary" style="display: none;">Cancel edit</button>
                </div>
            </form>
        </div>

        <div class="card">
            <div class="section-header">
                <div>
                    <h2 style="margin: 0 0 6px;">Submitted testimonies</h2>
                    <p class="page-copy" style="margin-bottom: 0;">Review your testimony status, see admin notes, and manage your own submissions.</p>
                </div>
            </div>

            <div id="testimonies-empty" class="info-box" style="display: none; margin-bottom: 0;">
                <div class="empty-illustration"></div>
                No testimonies yet. Your completed service or inspection experiences can be submitted here.
            </div>

            <div id="testimonies-list" class="testimony-list" style="display: none;"></div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const loadingBox = document.getElementById('testimonies-loading');
        const successBox = document.getElementById('testimonies-success');
        const errorBox = document.getElementById('testimonies-error');
        const addButton = document.getElementById('add-testimony-button');
        const refreshButton = document.getElementById('refresh-testimonies-button');
        const form = document.getElementById('testimony-form');
        const formHeading = document.getElementById('form-heading');
        const formCopy = document.getElementById('form-copy');
        const saveButton = document.getElementById('save-testimony-button');
        const cancelEditButton = document.getElementById('cancel-edit-button');
        const listContainer = document.getElementById('testimonies-list');
        const emptyState = document.getElementById('testimonies-empty');
        const serviceSelect = document.getElementById('service_request_id');
        const inspectionSelect = document.getElementById('inspection_request_id');
        const ratingSelect = document.getElementById('rating');
        const titleInput = document.getElementById('title');
        const messageInput = document.getElementById('message');
        const imagesInput = document.getElementById('images');
        const existingImagesSection = document.getElementById('existing-images-section');
        const existingImagesList = document.getElementById('existing-images-list');

        const state = {
            testimonies: [],
            serviceRequests: [],
            inspectionRequests: [],
            editingId: null,
            removeImageIds: [],
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

        function clearFieldErrors() {
            document.querySelectorAll('[data-error-for]').forEach((element) => {
                element.textContent = '';
            });
        }

        function applyFieldErrors(errors) {
            Object.entries(errors || {}).forEach(([field, messages]) => {
                const element = document.querySelector(`[data-error-for="${field}"]`);

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

        function formatRequestStatus(status) {
            const normalizedStatus = String(status || '')
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

        function getMessagePreview(message) {
            const trimmed = String(message || '').trim();

            if (!trimmed) {
                return 'No message provided.';
            }

            if (trimmed.length <= 160) {
                return trimmed;
            }

            return `${trimmed.slice(0, 157).trimEnd()}...`;
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
                return `Status: ${formatRequestStatus(testimony.service_request.status)}${testimony.service_request.date_needed ? ` | Preferred date: ${formatDate(testimony.service_request.date_needed)}` : ''}`;
            }

            if (testimony.inspection_request) {
                return `Status: ${formatRequestStatus(testimony.inspection_request.status)}${testimony.inspection_request.date_needed ? ` | Preferred date: ${formatDate(testimony.inspection_request.date_needed)}` : ''}`;
            }

            return 'No linked request metadata available.';
        }

        function getRatingLabel(rating) {
            return `${Number(rating || 0)}/5`;
        }

        function getImageCountLabel(images) {
            const count = Array.isArray(images) ? images.length : 0;
            return `${count} image${count === 1 ? '' : 's'}`;
        }

        function getEditingTestimony() {
            return state.testimonies.find((testimony) => testimony.id === state.editingId) || null;
        }

        function getCompletedServiceRequests() {
            const completedRequests = state.serviceRequests.filter((request) => String(request.status || '').toLowerCase() === 'completed');
            const editingTestimony = getEditingTestimony();
            const linkedRequest = editingTestimony?.service_request;

            if (linkedRequest && !completedRequests.some((request) => request.id === linkedRequest.id)) {
                completedRequests.unshift(linkedRequest);
            }

            return completedRequests;
        }

        function getCompletedInspectionRequests() {
            const completedRequests = state.inspectionRequests.filter((request) => String(request.status || '').toLowerCase() === 'completed');
            const editingTestimony = getEditingTestimony();
            const linkedRequest = editingTestimony?.inspection_request;

            if (linkedRequest && !completedRequests.some((request) => request.id === linkedRequest.id)) {
                completedRequests.unshift(linkedRequest);
            }

            return completedRequests;
        }

        function renderRequestSelectors() {
            const editingTestimony = getEditingTestimony();
            const selectedServiceId = editingTestimony?.service_request_id ? String(editingTestimony.service_request_id) : serviceSelect.value;
            const selectedInspectionId = editingTestimony?.inspection_request_id ? String(editingTestimony.inspection_request_id) : inspectionSelect.value;

            const serviceOptions = [
                '<option value="">Select a completed service request</option>',
                ...getCompletedServiceRequests().map((request) => {
                    const requestType = request.request_type || 'Service';
                    const isSelected = selectedServiceId === String(request.id) ? ' selected' : '';
                    return `<option value="${request.id}"${isSelected}>Service Request #${request.id} - ${escapeHtml(requestType)}</option>`;
                }),
            ];

            const inspectionOptions = [
                '<option value="">Select a completed inspection request</option>',
                ...getCompletedInspectionRequests().map((request) => {
                    const isSelected = selectedInspectionId === String(request.id) ? ' selected' : '';
                    return `<option value="${request.id}"${isSelected}>Inspection Request #${request.id}</option>`;
                }),
            ];

            serviceSelect.innerHTML = serviceOptions.join('');
            inspectionSelect.innerHTML = inspectionOptions.join('');

            if (editingTestimony?.service_request_id) {
                serviceSelect.value = String(editingTestimony.service_request_id);
                inspectionSelect.value = '';
            } else if (editingTestimony?.inspection_request_id) {
                inspectionSelect.value = String(editingTestimony.inspection_request_id);
                serviceSelect.value = '';
            }
        }

        function renderExistingImages() {
            const editingTestimony = getEditingTestimony();
            const images = Array.isArray(editingTestimony?.images) ? editingTestimony.images : [];

            if (!editingTestimony || images.length === 0) {
                existingImagesList.innerHTML = '';
                setVisible(existingImagesSection, false);
                return;
            }

            existingImagesList.innerHTML = images.map((image, index) => {
                const checked = state.removeImageIds.includes(image.id) ? ' checked' : '';
                const imageUrl = image.image_url || '';

                return `
                    <div class="existing-image-card">
                        ${imageUrl
                            ? `<img src="${escapeHtml(imageUrl)}" alt="Existing testimony image ${index + 1}">`
                            : '<div style="height: 150px; background: #e8f1fb;"></div>'
                        }
                        <label>
                            <input type="checkbox" data-remove-image-id="${image.id}"${checked}>
                            <span>Remove this image</span>
                        </label>
                    </div>
                `;
            }).join('');

            setVisible(existingImagesSection, true);
        }

        function renderFormMode() {
            const editingTestimony = getEditingTestimony();

            if (!editingTestimony) {
                formHeading.textContent = 'Add testimony';
                formCopy.textContent = 'Choose a completed service or inspection request, write your testimony, and attach photos if needed.';
                saveButton.textContent = 'Submit testimony';
                setVisible(cancelEditButton, false);
                renderExistingImages();
                return;
            }

            formHeading.textContent = `Edit testimony #${editingTestimony.id}`;
            formCopy.textContent = editingTestimony.status === 'approved'
                ? 'Editing an approved testimony will send it back for admin review.'
                : 'Update your testimony and keep your uploaded images unless you choose to remove them.';
            saveButton.textContent = 'Save changes';
            setVisible(cancelEditButton, true);
            renderExistingImages();
        }

        function resetForm() {
            state.editingId = null;
            state.removeImageIds = [];
            form.reset();
            clearFieldErrors();
            renderRequestSelectors();
            renderFormMode();
        }

        function startEdit(id) {
            const testimony = state.testimonies.find((item) => item.id === id);

            if (!testimony) {
                return;
            }

            state.editingId = id;
            state.removeImageIds = [];
            ratingSelect.value = String(testimony.rating || '');
            titleInput.value = testimony.title || '';
            messageInput.value = testimony.message || '';
            imagesInput.value = '';
            renderRequestSelectors();
            renderFormMode();
            form.scrollIntoView({
                behavior: 'smooth',
                block: 'start',
            });
        }

        function renderList() {
            if (state.testimonies.length === 0) {
                listContainer.innerHTML = '';
                setVisible(listContainer, false);
                setVisible(emptyState, true);
                return;
            }

            listContainer.innerHTML = state.testimonies.map((testimony) => {
                const images = Array.isArray(testimony.images) ? testimony.images : [];
                const imageMarkup = images.length > 0
                    ? `
                        <div class="image-grid" style="margin-top: 14px;">
                            ${images.map((image, index) => {
                                const imageUrl = image.image_url || '';

                                if (!imageUrl) {
                                    return `
                                        <div class="image-tile">
                                            <div style="height: 130px; background: #e8f1fb;"></div>
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
                    : '';

                const adminNoteMarkup = testimony.status === 'rejected' && testimony.admin_note
                    ? `
                        <div class="error-box" style="margin-top: 14px; margin-bottom: 0;">
                            <strong>Admin note:</strong> ${escapeHtml(testimony.admin_note)}
                        </div>
                    `
                    : '';

                return `
                    <div class="testimony-card">
                        <div class="request-header">
                            <div>
                                <div class="request-title">${escapeHtml(testimony.title || 'Untitled testimony')}</div>
                                <div class="muted">${escapeHtml(getLinkedRequestLabel(testimony))}</div>
                            </div>
                            <div class="request-badges">
                                <span class="${getStatusClass(testimony.status)}">${escapeHtml(formatStatusLabel(testimony.status))}</span>
                                <span class="badge badge-primary">${escapeHtml(getRatingLabel(testimony.rating))}</span>
                            </div>
                        </div>

                        <div class="detail-grid">
                            <div class="detail-item">
                                <span class="detail-label">Message</span>
                                <strong>${escapeHtml(getMessagePreview(testimony.message))}</strong>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Request meta</span>
                                <strong>${escapeHtml(getLinkedRequestMeta(testimony))}</strong>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Images</span>
                                <strong>${escapeHtml(getImageCountLabel(testimony.images))}</strong>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Updated</span>
                                <strong>${escapeHtml(formatDate(testimony.updated_at || testimony.created_at))}</strong>
                            </div>
                        </div>

                        ${imageMarkup}
                        ${adminNoteMarkup}

                        <div class="testimony-actions">
                            <button type="button" class="secondary" data-action="edit" data-id="${testimony.id}">Edit</button>
                            <button type="button" class="secondary" data-action="delete" data-id="${testimony.id}">Delete</button>
                        </div>
                    </div>
                `;
            }).join('');

            setVisible(emptyState, false);
            setVisible(listContainer, true);
        }

        async function jsonRequest(endpoint, options = {}) {
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

        async function formRequest(endpoint, formData, method = 'POST') {
            await ensureCsrfCookie();

            const headers = {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-XSRF-TOKEN': getCookie('XSRF-TOKEN') || '',
            };

            const response = await fetch(endpoint, {
                method,
                credentials: 'same-origin',
                headers,
                body: formData,
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

        async function loadPageData(options = {}) {
            if (!options.preserveMessages) {
                clearMessages();
            }

            setVisible(loadingBox, true);

            try {
                const [testimonyResponse, serviceRequests, inspectionRequests] = await Promise.all([
                    jsonRequest('/api/my-testimonies'),
                    jsonRequest('/api/service-requests'),
                    jsonRequest('/api/inspection-requests'),
                ]);

                state.testimonies = Array.isArray(testimonyResponse.data) ? testimonyResponse.data : [];
                state.serviceRequests = Array.isArray(serviceRequests) ? serviceRequests : [];
                state.inspectionRequests = Array.isArray(inspectionRequests) ? inspectionRequests : [];

                if (state.editingId && !state.testimonies.some((testimony) => testimony.id === state.editingId)) {
                    resetForm();
                } else {
                    renderRequestSelectors();
                    renderFormMode();
                }

                renderList();
            } catch (error) {
                showError(error.message || 'Could not load testimonies.');
                listContainer.innerHTML = '';
                setVisible(listContainer, false);
                setVisible(emptyState, false);
            } finally {
                setVisible(loadingBox, false);
            }
        }

        function buildFormData() {
            const formData = new FormData();
            const serviceRequestId = serviceSelect.value;
            const inspectionRequestId = inspectionSelect.value;

            if (state.editingId) {
                formData.append('_method', 'PUT');
            }

            if (serviceRequestId) {
                formData.append('service_request_id', serviceRequestId);
            }

            if (inspectionRequestId) {
                formData.append('inspection_request_id', inspectionRequestId);
            }

            formData.append('rating', ratingSelect.value);
            formData.append('title', titleInput.value || '');
            formData.append('message', messageInput.value || '');

            Array.from(imagesInput.files || []).forEach((file) => {
                formData.append('images[]', file);
            });

            state.removeImageIds.forEach((imageId) => {
                formData.append('remove_image_ids[]', String(imageId));
            });

            return formData;
        }

        async function handleDelete(id) {
            const testimony = state.testimonies.find((item) => item.id === id);

            if (!testimony) {
                return;
            }

            if (!window.confirm(`Delete "${testimony.title || `Testimony #${testimony.id}`}"?`)) {
                return;
            }

            clearMessages();

            try {
                const response = await jsonRequest(`/api/testimonies/${id}`, {
                    method: 'DELETE',
                });

                showSuccess(response.message || 'Testimony deleted successfully.');

                if (state.editingId === id) {
                    resetForm();
                }

                await loadPageData({
                    preserveMessages: true,
                });
            } catch (error) {
                showError(error.message || 'Could not delete testimony.');
            }
        }

        serviceSelect.addEventListener('change', () => {
            if (serviceSelect.value) {
                inspectionSelect.value = '';
            }
        });

        inspectionSelect.addEventListener('change', () => {
            if (inspectionSelect.value) {
                serviceSelect.value = '';
            }
        });

        existingImagesList.addEventListener('change', (event) => {
            const checkbox = event.target.closest('input[data-remove-image-id]');

            if (!checkbox) {
                return;
            }

            const imageId = Number(checkbox.dataset.removeImageId);

            if (!imageId) {
                return;
            }

            if (checkbox.checked) {
                state.removeImageIds = [...new Set([...state.removeImageIds, imageId])];
            } else {
                state.removeImageIds = state.removeImageIds.filter((currentId) => currentId !== imageId);
            }
        });

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

            if (action === 'edit') {
                startEdit(id);
                return;
            }

            if (action === 'delete') {
                handleDelete(id);
            }
        });

        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            clearMessages();
            clearFieldErrors();
            saveButton.disabled = true;
            saveButton.textContent = state.editingId ? 'Saving...' : 'Submitting...';

            try {
                const endpoint = state.editingId
                    ? `/api/testimonies/${state.editingId}`
                    : '/api/testimonies';

                const response = await formRequest(endpoint, buildFormData());
                showSuccess(response.message || (state.editingId ? 'Testimony updated successfully.' : 'Testimony submitted successfully.'));
                resetForm();
                await loadPageData({
                    preserveMessages: true,
                });
            } catch (error) {
                applyFieldErrors(error.errors || {});
                showError(error.message || 'Could not save testimony.');
            } finally {
                saveButton.disabled = false;
                saveButton.textContent = state.editingId ? 'Save changes' : 'Submit testimony';
            }
        });

        addButton.addEventListener('click', () => {
            resetForm();
            form.scrollIntoView({
                behavior: 'smooth',
                block: 'start',
            });
        });

        cancelEditButton.addEventListener('click', resetForm);
        refreshButton.addEventListener('click', loadPageData);

        loadPageData();
    </script>
@endpush
