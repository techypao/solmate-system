@extends('layouts.app', ['title' => 'Service Request Options'])

@section('content')
    <div class="admin-page-stack">
        <div class="card admin-hero-card">
            <p class="admin-page-eyebrow">Operations Setup</p>
            <h1 class="page-title">Service Request Options</h1>
            <p class="page-copy">Manage the choices customers see when booking installation and maintenance service requests on web and mobile.</p>
        </div>

        <style>
            .service-option-panel {
                padding: 20px;
                background: linear-gradient(180deg, #ffffff 0%, #F8FAFC 100%);
                border: 1px solid #DDE7EE;
                border-radius: 18px;
                box-shadow: 0 8px 22px rgba(15, 23, 42, 0.04);
            }

            .service-option-badge {
                display: inline-flex;
                align-items: center;
                padding: 4px 10px;
                border-radius: 999px;
                font-size: 12px;
                font-weight: 800;
            }

            .service-option-badge.active {
                background: #e3f9e5;
                color: #1f5132;
            }

            .service-option-badge.inactive {
                background: #fde8e8;
                color: #8a1c1c;
            }
        </style>

        <div class="card admin-section-surface">
            <div id="option-loading" class="info-box">Loading service request options...</div>
            <div id="option-success" class="status" style="display: none;"></div>
            <div id="option-error" class="error-box" style="display: none;"></div>

            <div class="stack">
                <div class="service-option-panel">
                    <div class="actions" style="justify-content: space-between;">
                        <div>
                            <h2 class="admin-section-title" style="margin: 0 0 6px;">Create or Edit Option</h2>
                            <div class="muted">These options appear in customer installation and maintenance request forms.</div>
                        </div>
                        <button id="reset-option-form-button" type="button" class="secondary" style="display: none;">Cancel edit</button>
                    </div>

                    <form id="service-option-form" class="form-grid two-columns" style="margin-top: 18px; display: none;">
                        <input id="service_request_option_id" name="service_request_option_id" type="hidden">

                        <div>
                            <label for="category">Category</label>
                            <select id="category" name="category" required>
                                @foreach ($categories as $category)
                                    <option value="{{ $category }}">{{ \Illuminate\Support\Str::headline($category) }}</option>
                                @endforeach
                            </select>
                            <div class="field-error" data-error-for="category"></div>
                        </div>

                        <div>
                            <label for="sort_order">Sort Order</label>
                            <input id="sort_order" name="sort_order" type="number" min="0" step="1">
                            <div class="field-error" data-error-for="sort_order"></div>
                        </div>

                        <div style="grid-column: 1 / -1;">
                            <label for="label">Option Label</label>
                            <input id="label" name="label" type="text" required>
                            <div class="field-error" data-error-for="label"></div>
                        </div>

                        <div style="grid-column: 1 / -1;">
                            <label for="description">Short Description</label>
                            <textarea id="description" name="description" rows="3"></textarea>
                            <div class="field-error" data-error-for="description"></div>
                        </div>

                        <div style="grid-column: 1 / -1;">
                            <label class="checkbox-inline" for="is_active">
                                <input id="is_active" name="is_active" type="checkbox" checked>
                                <span>Option is active</span>
                            </label>
                            <div class="field-error" data-error-for="is_active"></div>
                        </div>

                        <div class="actions" style="grid-column: 1 / -1;">
                            <button id="save-option-button" type="submit">Create option</button>
                            <button id="remove-option-button" type="button" class="danger" style="display: none;">Remove option</button>
                            <span id="option-form-mode-hint" class="muted">New active options are shown to customers after saving.</span>
                        </div>
                    </form>
                </div>

                <div class="service-option-panel">
                    <div class="actions" style="justify-content: space-between;">
                        <div>
                            <h2 class="admin-section-title" style="margin: 0 0 6px;">Current Options</h2>
                            <div class="muted">Edit, reorder, activate, or deactivate the choices customers can pick.</div>
                        </div>
                        <button id="refresh-option-button" type="button" class="secondary">Refresh</button>
                    </div>

                    <div id="service-options-empty" class="info-box" style="display: none; margin-top: 16px;">No service request options found yet.</div>
                    <div id="service-options-list" class="stack" style="margin-top: 16px; display: none;"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script type="application/json" id="__data_service_option_categories">@json($categories)</script>
    <script>
        const categories = JSON.parse(document.getElementById('__data_service_option_categories').textContent);
        const categoryLabels = {
            installation_type: 'Installation Types',
            maintenance_concern: 'Maintenance Concerns',
        };
        const loadingBox = document.getElementById('option-loading');
        const successBox = document.getElementById('option-success');
        const errorBox = document.getElementById('option-error');
        const form = document.getElementById('service-option-form');
        const list = document.getElementById('service-options-list');
        const emptyState = document.getElementById('service-options-empty');
        const refreshButton = document.getElementById('refresh-option-button');
        const saveButton = document.getElementById('save-option-button');
        const resetFormButton = document.getElementById('reset-option-form-button');
        const removeButton = document.getElementById('remove-option-button');
        const formModeHint = document.getElementById('option-form-mode-hint');

        const initialFormState = {
            service_request_option_id: '',
            category: categories[0] || 'installation_type',
            label: '',
            description: '',
            sort_order: 0,
            is_active: true,
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

        function clearFieldErrors() {
            document.querySelectorAll('[data-error-for]').forEach((element) => {
                element.textContent = '';
            });
        }

        function showError(message) {
            errorBox.textContent = message;
            setVisible(errorBox, true);
        }

        function escapeHtml(value) {
            return String(value ?? '')
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
        }

        function getCategoryLabel(category) {
            return categoryLabels[category] || String(category || '').replaceAll('_', ' ');
        }

        function fillForm(item = initialFormState) {
            form.elements.namedItem('service_request_option_id').value = item.id || item.service_request_option_id || '';
            form.elements.namedItem('category').value = item.category || initialFormState.category;
            form.elements.namedItem('label').value = item.label || '';
            form.elements.namedItem('description').value = item.description || '';
            form.elements.namedItem('sort_order').value = Number.isFinite(Number(item.sort_order)) ? Number(item.sort_order) : 0;
            form.elements.namedItem('is_active').checked = item.is_active ?? true;

            const isEditing = Boolean(item.id || item.service_request_option_id);
            saveButton.textContent = isEditing ? 'Update option' : 'Create option';
            formModeHint.textContent = isEditing
                ? 'Editing an existing customer-facing option.'
                : 'New active options are shown to customers after saving.';
            resetFormButton.style.display = isEditing ? 'inline-flex' : 'none';
            removeButton.style.display = isEditing ? 'inline-flex' : 'none';
        }

        function groupOptions(options) {
            return categories.map((category) => ({
                category,
                label: getCategoryLabel(category),
                items: options
                    .filter((option) => option.category === category)
                    .sort((left, right) => {
                        if (left.is_active !== right.is_active) {
                            return Number(right.is_active) - Number(left.is_active);
                        }

                        const orderDiff = Number(left.sort_order || 0) - Number(right.sort_order || 0);
                        return orderDiff || String(left.label || '').localeCompare(String(right.label || ''));
                    }),
            }));
        }

        function renderList(options) {
            list.innerHTML = '';

            if (!options.length) {
                setVisible(emptyState, true);
                setVisible(list, false);
                return;
            }

            setVisible(emptyState, false);
            setVisible(list, true);

            groupOptions(options).forEach((group) => {
                const section = document.createElement('section');
                section.className = 'card';
                section.style.padding = '18px';
                section.style.marginTop = '0';
                section.style.background = '#f8fbfd';

                section.innerHTML = `
                    <div class="actions" style="justify-content: space-between; align-items: flex-start;">
                        <div>
                            <h3 style="margin: 0 0 6px; color: #123A5A;">${escapeHtml(group.label)}</h3>
                            <div class="muted">${escapeHtml(group.items.length)} option${group.items.length === 1 ? '' : 's'}</div>
                        </div>
                    </div>
                `;

                const stack = document.createElement('div');
                stack.className = 'stack';
                stack.style.marginTop = '14px';

                if (!group.items.length) {
                    const empty = document.createElement('div');
                    empty.className = 'info-box';
                    empty.textContent = `No ${group.label.toLowerCase()} configured yet.`;
                    stack.appendChild(empty);
                }

                group.items.forEach((item) => {
                    const row = document.createElement('div');
                    row.className = 'card';
                    row.style.padding = '18px';
                    row.style.marginTop = '0';
                    row.innerHTML = `
                        <div class="actions" style="justify-content: space-between; align-items: flex-start;">
                            <div class="stack" style="gap: 6px;">
                                <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                    <strong>${escapeHtml(item.label)}</strong>
                                    <span class="service-option-badge ${item.is_active ? 'active' : 'inactive'}">${item.is_active ? 'Active' : 'Inactive'}</span>
                                </div>
                                <div class="muted">Sort order: ${escapeHtml(item.sort_order ?? 0)}</div>
                                <div>${escapeHtml(item.description || 'No description provided.')}</div>
                            </div>
                            <div class="actions">
                                <button type="button" class="secondary" data-action="edit">Edit</button>
                                <button type="button" class="${item.is_active ? 'secondary' : ''}" data-action="toggle">
                                    ${item.is_active ? 'Deactivate' : 'Activate'}
                                </button>
                                <button type="button" class="danger" data-action="remove">Remove</button>
                            </div>
                        </div>
                    `;

                    row.querySelector('[data-action="edit"]').addEventListener('click', () => {
                        clearMessages();
                        clearFieldErrors();
                        fillForm(item);
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    });

                    row.querySelector('[data-action="toggle"]').addEventListener('click', async (event) => {
                        const button = event.currentTarget;
                        clearMessages();
                        button.disabled = true;
                        button.textContent = item.is_active ? 'Deactivating...' : 'Activating...';

                        try {
                            await saveOptionPayload(`/api/admin/service-request-options/${item.id}`, 'PATCH', {
                                is_active: !item.is_active,
                            });

                            successBox.textContent = 'Service request option updated successfully.';
                            setVisible(successBox, true);
                            await loadOptions(true);
                        } catch (error) {
                            showError(error.message || 'Could not update service request option.');
                        } finally {
                            button.disabled = false;
                        }
                    });

                    row.querySelector('[data-action="remove"]').addEventListener('click', async () => {
                        await deleteOption(item.id, item.label);
                    });

                    stack.appendChild(row);
                });

                section.appendChild(stack);
                list.appendChild(section);
            });
        }

        async function saveOptionPayload(endpoint, method, payload) {
            await ensureCsrfCookie();

            const response = await fetch(endpoint, {
                method,
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-XSRF-TOKEN': getCookie('XSRF-TOKEN') || '',
                },
                body: JSON.stringify(payload),
            });

            const responseBody = await response.json().catch(() => ({}));

            if (response.status === 422) {
                Object.entries(responseBody.errors || {}).forEach(([field, messages]) => {
                    const errorElement = document.querySelector(`[data-error-for="${field}"]`);
                    if (errorElement && Array.isArray(messages) && messages[0]) {
                        errorElement.textContent = messages[0];
                    }
                });

                throw new Error('Please fix the highlighted fields.');
            }

            if (!response.ok) {
                throw new Error(responseBody.message || 'Request failed.');
            }

            return responseBody;
        }

        async function loadOptions(preserveMessages = false) {
            if (!preserveMessages) {
                clearMessages();
            }

            setVisible(loadingBox, true);
            setVisible(form, false);
            setVisible(list, false);
            setVisible(emptyState, false);

            try {
                const response = await fetch('/api/admin/service-request-options', {
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                const payload = await response.json();

                if (!response.ok) {
                    throw new Error(payload.message || 'Could not load service request options.');
                }

                renderList(payload.data || []);
                fillForm();
                setVisible(form, true, 'grid');
            } catch (error) {
                showError(error.message || 'Could not load service request options.');
            } finally {
                setVisible(loadingBox, false);
            }
        }

        async function saveOption(event) {
            event.preventDefault();
            clearMessages();
            clearFieldErrors();

            const optionId = form.elements.namedItem('service_request_option_id').value;
            const isEditing = Boolean(optionId);
            const endpoint = isEditing ? `/api/admin/service-request-options/${optionId}` : '/api/admin/service-request-options';
            const method = isEditing ? 'PATCH' : 'POST';

            saveButton.disabled = true;
            saveButton.textContent = isEditing ? 'Updating...' : 'Creating...';

            try {
                const payload = {
                    category: form.elements.namedItem('category').value,
                    label: form.elements.namedItem('label').value.trim(),
                    description: form.elements.namedItem('description').value.trim() || null,
                    sort_order: Number(form.elements.namedItem('sort_order').value || 0),
                    is_active: form.elements.namedItem('is_active').checked,
                };

                const responseBody = await saveOptionPayload(endpoint, method, payload);
                fillForm();
                successBox.textContent = responseBody.message || 'Service request option saved successfully.';
                setVisible(successBox, true);
                await loadOptions(true);
            } catch (error) {
                showError(error.message || 'Could not save service request option.');
            } finally {
                saveButton.disabled = false;
                saveButton.textContent = form.elements.namedItem('service_request_option_id').value ? 'Update option' : 'Create option';
            }
        }

        async function deleteOption(optionId, label) {
            if (!confirm(`Remove "${label}"? Existing requests keep their saved label, but this option will be removed from future selections.`)) {
                return;
            }

            clearMessages();
            clearFieldErrors();

            try {
                await ensureCsrfCookie();

                const response = await fetch(`/api/admin/service-request-options/${optionId}`, {
                    method: 'DELETE',
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-XSRF-TOKEN': getCookie('XSRF-TOKEN') || '',
                    },
                });

                const responseBody = await response.json().catch(() => ({}));

                if (!response.ok) {
                    throw new Error(responseBody.message || 'Could not remove service request option.');
                }

                successBox.textContent = responseBody.message || 'Service request option removed successfully.';
                setVisible(successBox, true);
                fillForm();
                await loadOptions(true);
            } catch (error) {
                showError(error.message || 'Could not remove service request option.');
            }
        }

        form.addEventListener('submit', saveOption);
        refreshButton.addEventListener('click', loadOptions);
        resetFormButton.addEventListener('click', () => {
            clearMessages();
            clearFieldErrors();
            fillForm();
        });
        removeButton.addEventListener('click', async () => {
            const optionId = form.elements.namedItem('service_request_option_id').value;
            const label = form.elements.namedItem('label').value;
            if (optionId) {
                await deleteOption(optionId, label);
            }
        });

        loadOptions();
    </script>
@endpush
