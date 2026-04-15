@extends('layouts.app', ['title' => 'Admin Pricing Catalog'])

@section('content')
    <div class="card">
        <h1 class="page-title">Admin Pricing Catalog</h1>
        <p class="page-copy">Manage pricing items for future final quotation itemization. This page uses the existing admin pricing API.</p>

        <div id="catalog-loading" class="info-box">Loading pricing catalog...</div>
        <div id="catalog-success" class="status" style="display: none;"></div>
        <div id="catalog-error" class="error-box" style="display: none;"></div>

        <div class="stack">
            <div class="card" style="padding: 18px; background: #f8fbfd;">
                <div class="actions" style="justify-content: space-between;">
                    <div>
                        <h2 style="margin: 0 0 6px;">Create or Edit Item</h2>
                        <div class="muted">Use the form to add a pricing item or update an existing one.</div>
                    </div>
                    <button id="reset-form-button" type="button" class="secondary" style="display: none;">Cancel edit</button>
                </div>

                <form id="pricing-item-form" class="form-grid two-columns" style="margin-top: 18px; display: none;">
                    <input id="pricing_item_id" name="pricing_item_id" type="hidden">

                    <div>
                        <label for="name">Name</label>
                        <input id="name" name="name" type="text" required>
                        <div class="field-error" data-error-for="name"></div>
                    </div>

                    <div>
                        <label for="category">Category</label>
                        <select
                            id="category"
                            name="category"
                            required
                            style="width: 100%; padding: 10px 12px; border: 1px solid #bcccdc; border-radius: 8px; background: #fff;"
                        >
                            @foreach ($categories as $category)
                                <option value="{{ $category }}">{{ ucfirst($category) }}</option>
                            @endforeach
                        </select>
                        <div class="field-error" data-error-for="category"></div>
                    </div>

                    <div>
                        <label for="unit">Unit</label>
                        <input id="unit" name="unit" type="text" required>
                        <div class="field-error" data-error-for="unit"></div>
                    </div>

                    <div>
                        <label for="default_unit_price">Default Unit Price</label>
                        <input id="default_unit_price" name="default_unit_price" type="number" min="0" step="0.01" required>
                        <div class="field-error" data-error-for="default_unit_price"></div>
                    </div>

                    <div>
                        <label for="brand">Brand</label>
                        <input id="brand" name="brand" type="text">
                        <div class="field-error" data-error-for="brand"></div>
                    </div>

                    <div>
                        <label for="model">Model</label>
                        <input id="model" name="model" type="text">
                        <div class="field-error" data-error-for="model"></div>
                    </div>

                    <div style="grid-column: 1 / -1;">
                        <label for="specification">Specification</label>
                        <textarea
                            id="specification"
                            name="specification"
                            rows="3"
                            style="width: 100%; padding: 10px 12px; border: 1px solid #bcccdc; border-radius: 8px; background: #fff;"
                        ></textarea>
                        <div class="field-error" data-error-for="specification"></div>
                    </div>

                    <div style="grid-column: 1 / -1;">
                        <label class="checkbox-inline" for="is_active">
                            <input id="is_active" name="is_active" type="checkbox" checked>
                            <span>Item is active</span>
                        </label>
                        <div class="field-error" data-error-for="is_active"></div>
                    </div>

                    <div class="actions" style="grid-column: 1 / -1;">
                        <button id="save-item-button" type="submit">Create item</button>
                        <span id="form-mode-hint" class="muted">New items are saved through the existing admin API.</span>
                    </div>
                </form>
            </div>

            <div class="card" style="padding: 18px;">
                <div class="actions" style="justify-content: space-between;">
                    <div>
                        <h2 style="margin: 0 0 6px;">Pricing Items</h2>
                        <div class="muted">Activate, deactivate, or edit existing catalog entries.</div>
                    </div>
                    <button id="refresh-button" type="button" class="secondary">Refresh</button>
                </div>

                <div id="pricing-items-empty" class="info-box" style="display: none; margin-top: 16px;">No pricing items found yet.</div>

                <div id="pricing-items-list" class="stack" style="margin-top: 16px; display: none;"></div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const categories = @json($categories);
        const loadingBox = document.getElementById('catalog-loading');
        const successBox = document.getElementById('catalog-success');
        const errorBox = document.getElementById('catalog-error');
        const form = document.getElementById('pricing-item-form');
        const list = document.getElementById('pricing-items-list');
        const emptyState = document.getElementById('pricing-items-empty');
        const refreshButton = document.getElementById('refresh-button');
        const saveButton = document.getElementById('save-item-button');
        const resetFormButton = document.getElementById('reset-form-button');
        const formModeHint = document.getElementById('form-mode-hint');
        const initialFormState = {
            pricing_item_id: '',
            name: '',
            category: categories[0] || 'panel',
            unit: '',
            default_unit_price: '',
            brand: '',
            model: '',
            specification: '',
            is_active: true,
        };

        function setVisible(element, visible) {
            element.style.display = visible ? 'block' : 'none';
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

        function formatCurrency(value) {
            const number = Number(value || 0);
            return number.toFixed(2);
        }

        function escapeHtml(value) {
            return String(value ?? '')
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
        }

        function fillForm(item = initialFormState) {
            form.elements.namedItem('pricing_item_id').value = item.id || item.pricing_item_id || '';
            form.elements.namedItem('name').value = item.name || '';
            form.elements.namedItem('category').value = item.category || initialFormState.category;
            form.elements.namedItem('unit').value = item.unit || '';
            form.elements.namedItem('default_unit_price').value = item.default_unit_price ? Number(item.default_unit_price) : '';
            form.elements.namedItem('brand').value = item.brand || '';
            form.elements.namedItem('model').value = item.model || '';
            form.elements.namedItem('specification').value = item.specification || '';
            form.elements.namedItem('is_active').checked = item.is_active ?? true;

            const isEditing = Boolean(item.id || item.pricing_item_id);
            saveButton.textContent = isEditing ? 'Update item' : 'Create item';
            formModeHint.textContent = isEditing
                ? 'Editing an existing pricing item through the existing admin API.'
                : 'New items are saved through the existing admin API.';
            resetFormButton.style.display = isEditing ? 'inline-flex' : 'none';
        }

        function renderList(items) {
            list.innerHTML = '';

            if (!items.length) {
                setVisible(emptyState, true);
                setVisible(list, false);
                return;
            }

            setVisible(emptyState, false);
            setVisible(list, true);

            items.forEach((item) => {
                const row = document.createElement('div');
                row.className = 'card';
                row.style.padding = '18px';
                row.innerHTML = `
                    <div class="actions" style="justify-content: space-between; align-items: flex-start;">
                        <div class="stack" style="gap: 6px;">
                            <div><strong>${escapeHtml(item.name)}</strong></div>
                            <div class="muted">Category: ${escapeHtml(item.category)} | Unit: ${escapeHtml(item.unit)} | Price: ${formatCurrency(item.default_unit_price)}</div>
                            <div class="muted">Brand: ${escapeHtml(item.brand || 'N/A')} | Model: ${escapeHtml(item.model || 'N/A')}</div>
                            <div class="muted">Status: <span data-status-label>${item.is_active ? 'Active' : 'Inactive'}</span></div>
                            <div>${escapeHtml(item.specification || 'No specification provided.')}</div>
                        </div>
                        <div class="actions">
                            <button type="button" class="secondary" data-action="edit">Edit</button>
                            <button type="button" class="${item.is_active ? 'secondary' : ''}" data-action="toggle">
                                ${item.is_active ? 'Deactivate' : 'Activate'}
                            </button>
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
                        await ensureCsrfCookie();

                        const response = await fetch(`/api/admin/pricing-items/${item.id}`, {
                            method: 'PATCH',
                            credentials: 'same-origin',
                            headers: {
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-XSRF-TOKEN': getCookie('XSRF-TOKEN') || '',
                            },
                            body: JSON.stringify({
                                is_active: !item.is_active,
                            }),
                        });

                        const responseBody = await response.json();

                        if (!response.ok) {
                            throw new Error(responseBody.message || 'Could not update pricing item status.');
                        }

                        successBox.textContent = responseBody.message || 'Pricing item updated successfully.';
                        setVisible(successBox, true);
                        await loadPricingItems(true);
                    } catch (error) {
                        showError(error.message || 'Could not update pricing item status.');
                    } finally {
                        button.disabled = false;
                    }
                });

                list.appendChild(row);
            });
        }

        async function loadPricingItems(preserveMessages = false) {
            if (!preserveMessages) {
                clearMessages();
            }

            setVisible(loadingBox, true);
            setVisible(form, false);
            setVisible(list, false);
            setVisible(emptyState, false);

            try {
                const response = await fetch('/api/admin/pricing-items', {
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (!response.ok) {
                    throw new Error('Could not load pricing items.');
                }

                const payload = await response.json();
                const items = payload.data || [];
                renderList(items);
                fillForm();
                setVisible(form, true);
            } catch (error) {
                showError(error.message || 'Could not load pricing items.');
            } finally {
                setVisible(loadingBox, false);
            }
        }

        async function savePricingItem(event) {
            event.preventDefault();
            clearMessages();
            clearFieldErrors();

            const itemId = form.elements.namedItem('pricing_item_id').value;
            const isEditing = Boolean(itemId);
            const endpoint = isEditing ? `/api/admin/pricing-items/${itemId}` : '/api/admin/pricing-items';
            const method = isEditing ? 'PATCH' : 'POST';

            saveButton.disabled = true;
            saveButton.textContent = isEditing ? 'Updating...' : 'Creating...';

            try {
                await ensureCsrfCookie();

                const payload = {
                    name: form.elements.namedItem('name').value.trim(),
                    category: form.elements.namedItem('category').value,
                    unit: form.elements.namedItem('unit').value.trim(),
                    default_unit_price: Number(form.elements.namedItem('default_unit_price').value),
                    brand: form.elements.namedItem('brand').value.trim() || null,
                    model: form.elements.namedItem('model').value.trim() || null,
                    specification: form.elements.namedItem('specification').value.trim() || null,
                    is_active: form.elements.namedItem('is_active').checked,
                };

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

                const responseBody = await response.json();

                if (response.status === 422) {
                    const errors = responseBody.errors || {};

                    Object.entries(errors).forEach(([field, messages]) => {
                        const fieldName = field.replace(/^line_items\.\d+\./, '');
                        const errorElement = document.querySelector(`[data-error-for="${fieldName}"]`);

                        if (errorElement) {
                            errorElement.textContent = messages[0];
                        }
                    });

                    throw new Error('Please fix the highlighted fields.');
                }

                if (!response.ok) {
                    throw new Error(responseBody.message || 'Could not save pricing item.');
                }

                fillForm();
                successBox.textContent = responseBody.message || 'Pricing item saved successfully.';
                setVisible(successBox, true);
                await loadPricingItems(true);
            } catch (error) {
                showError(error.message || 'Could not save pricing item.');
            } finally {
                saveButton.disabled = false;
                saveButton.textContent = form.elements.namedItem('pricing_item_id').value ? 'Update item' : 'Create item';
            }
        }

        form.addEventListener('submit', savePricingItem);
        refreshButton.addEventListener('click', loadPricingItems);
        resetFormButton.addEventListener('click', () => {
            clearMessages();
            clearFieldErrors();
            fillForm();
        });

        loadPricingItems();
    </script>
@endpush
