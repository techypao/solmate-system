@extends('layouts.app', ['title' => 'Admin Pricing Catalog'])

@section('content')
    <div class="admin-page-stack">
    <div class="card admin-hero-card">
        <p class="admin-page-eyebrow">Admin Catalog</p>
        <h1 class="page-title">Admin Pricing Catalog</h1>
        <p class="page-copy">Manage pricing items for future final quotation itemization. This page uses the existing admin pricing API.</p>
    </div>

    <style>
        .pricing-panel {
            padding: 20px;
            background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
            border: 1px solid #dbe7f3;
            border-radius: 18px;
            box-shadow: 0 8px 22px rgba(15, 23, 42, 0.04);
        }
    </style>

    <div class="card admin-section-surface">

        <div id="catalog-loading" class="info-box">Loading pricing catalog...</div>
        <div id="catalog-success" class="status" style="display: none;"></div>
        <div id="catalog-error" class="error-box" style="display: none;"></div>

        <div class="stack">
            <div class="pricing-panel">
                <div class="actions" style="justify-content: space-between;">
                    <div>
                        <h2 class="admin-section-title" style="margin: 0 0 6px;">Create or Edit Item</h2>
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
                        <select id="category" name="category" required>
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
                        <textarea id="specification" name="specification" rows="3"></textarea>
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

            <div class="pricing-panel">
                <div class="actions" style="justify-content: space-between;">
                    <div>
                        <h2 class="admin-section-title" style="margin: 0 0 6px;">Pricing Items</h2>
                        <div class="muted">Activate, deactivate, or edit existing catalog entries.</div>
                        <div class="muted">Grouped into Panels, Inverters, Batteries, Other Materials / BOS, and Labor / Installation when available.</div>
                    </div>
                    <button id="refresh-button" type="button" class="secondary">Refresh</button>
                </div>

                <div id="pricing-items-empty" class="info-box" style="display: none; margin-top: 16px;">No pricing items found yet.</div>
                <div id="pricing-group-summary" class="form-grid two-columns" style="margin-top: 16px; display: none;"></div>

                <div id="pricing-items-list" class="stack" style="margin-top: 16px; display: none;"></div>
            </div>
        </div>
    </div>
    </div>
@endsection

@push('scripts')
    <script type="application/json" id="__data_categories">@json($categories)</script>
    <script>
        const categories = JSON.parse(document.getElementById('__data_categories').textContent);
        const loadingBox = document.getElementById('catalog-loading');
        const successBox = document.getElementById('catalog-success');
        const errorBox = document.getElementById('catalog-error');
        const form = document.getElementById('pricing-item-form');
        const list = document.getElementById('pricing-items-list');
        const emptyState = document.getElementById('pricing-items-empty');
        const groupSummary = document.getElementById('pricing-group-summary');
        const refreshButton = document.getElementById('refresh-button');
        const saveButton = document.getElementById('save-item-button');
        const resetFormButton = document.getElementById('reset-form-button');
        const formModeHint = document.getElementById('form-mode-hint');
        const pricingGroups = [
            {
                key: 'panels',
                label: 'Panels',
                description: 'Solar panel items',
            },
            {
                key: 'inverters',
                label: 'Inverters',
                description: 'Inverter units and related pricing',
            },
            {
                key: 'batteries',
                label: 'Batteries',
                description: 'Battery units and storage items',
            },
            {
                key: 'other-materials',
                label: 'Other Materials / BOS',
                description: 'Protection, mounting, wiring, grounding, and other balance-of-system items',
            },
            {
                key: 'labor-installation',
                label: 'Labor / Installation',
                description: 'Service, labor, and installation pricing',
            },
        ];
        const categoryMetadata = {
            panel: {
                label: 'Panel',
                group: 'panels',
            },
            inverter: {
                label: 'Inverter',
                group: 'inverters',
            },
            battery: {
                label: 'Battery',
                group: 'batteries',
            },
            protection: {
                label: 'Protection',
                group: 'other-materials',
            },
            mounting: {
                label: 'Mounting',
                group: 'other-materials',
            },
            wiring: {
                label: 'Wiring',
                group: 'other-materials',
            },
            grounding: {
                label: 'Grounding',
                group: 'other-materials',
            },
            misc: {
                label: 'Misc',
                group: 'other-materials',
            },
        };
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

        function formatCurrency(value) {
            const number = Number(value || 0);
            return number.toFixed(2);
        }

        function formatCountLabel(count, singularLabel, pluralLabel = `${singularLabel}s`) {
            return `${count} ${count === 1 ? singularLabel : pluralLabel}`;
        }

        function humanizeLabel(value) {
            return String(value || '')
                .replace(/[_-]+/g, ' ')
                .replace(/\b\w/g, (character) => character.toUpperCase());
        }

        function normalizeCategory(category) {
            return String(category || '').trim().toLowerCase();
        }

        function getCategoryLabel(category) {
            const normalizedCategory = normalizeCategory(category);
            return categoryMetadata[normalizedCategory]?.label || humanizeLabel(normalizedCategory || 'uncategorized');
        }

        function resolveGroupKey(category) {
            const normalizedCategory = normalizeCategory(category);

            if (categoryMetadata[normalizedCategory]) {
                return categoryMetadata[normalizedCategory].group;
            }

            if (normalizedCategory.includes('labor') || normalizedCategory.includes('install')) {
                return 'labor-installation';
            }

            return 'other-materials';
        }

        function groupPricingItems(items) {
            const groupsByKey = new Map(
                pricingGroups.map((group) => [
                    group.key,
                    {
                        ...group,
                        items: [],
                    },
                ])
            );

            items.forEach((item) => {
                const groupKey = resolveGroupKey(item.category);
                const group = groupsByKey.get(groupKey) || groupsByKey.get('other-materials');

                group.items.push(item);
            });

            groupsByKey.forEach((group) => {
                group.items.sort((left, right) => {
                    if (left.is_active !== right.is_active) {
                        return Number(right.is_active) - Number(left.is_active);
                    }

                    return String(left.name || '').localeCompare(String(right.name || ''));
                });
            });

            return pricingGroups
                .map((group) => groupsByKey.get(group.key))
                .filter((group) => group.items.length > 0);
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

        function renderGroupSummary(groups) {
            groupSummary.innerHTML = '';

            if (!groups.length) {
                setVisible(groupSummary, false);
                return;
            }

            groups.forEach((group) => {
                const activeItems = group.items.filter((item) => item.is_active).length;
                const summaryCard = document.createElement('div');
                summaryCard.className = 'card';
                summaryCard.style.padding = '16px';
                summaryCard.style.marginTop = '0';
                summaryCard.style.background = '#f8fbfd';
                summaryCard.innerHTML = `
                    <div style="font-weight: 700; color: #102a43;">${escapeHtml(group.label)}</div>
                    <div class="muted" style="margin-top: 4px;">${escapeHtml(group.description)}</div>
                    <div class="muted" style="margin-top: 10px;">
                        ${escapeHtml(formatCountLabel(group.items.length, 'item'))} | ${escapeHtml(formatCountLabel(activeItems, 'active item'))}
                    </div>
                `;

                groupSummary.appendChild(summaryCard);
            });

            setVisible(groupSummary, true, 'grid');
        }

        function renderList(items) {
            list.innerHTML = '';

            if (!items.length) {
                setVisible(emptyState, true);
                setVisible(list, false);
                setVisible(groupSummary, false);
                return;
            }

            setVisible(emptyState, false);
            setVisible(list, true, 'grid');

            const groups = groupPricingItems(items);
            renderGroupSummary(groups);

            groups.forEach((group) => {
                const section = document.createElement('section');
                section.className = 'card';
                section.style.padding = '18px';
                section.style.marginTop = '0';
                section.style.background = '#f8fbfd';

                const sectionHeader = document.createElement('div');
                sectionHeader.className = 'actions';
                sectionHeader.style.justifyContent = 'space-between';
                sectionHeader.style.alignItems = 'flex-start';
                sectionHeader.innerHTML = `
                    <div>
                        <h3 style="margin: 0 0 6px; color: #102a43;">${escapeHtml(group.label)}</h3>
                        <div class="muted">${escapeHtml(group.description)}</div>
                    </div>
                    <div class="muted">${escapeHtml(formatCountLabel(group.items.length, 'item'))}</div>
                `;

                const itemStack = document.createElement('div');
                itemStack.className = 'stack';
                itemStack.style.marginTop = '14px';

                group.items.forEach((item) => {
                    const row = document.createElement('div');
                    row.className = 'card';
                    row.style.padding = '18px';
                    row.style.marginTop = '0';
                    row.innerHTML = `
                        <div class="actions" style="justify-content: space-between; align-items: flex-start;">
                            <div class="stack" style="gap: 6px;">
                                <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                    <strong>${escapeHtml(item.name)}</strong>
                                    <span style="padding: 4px 8px; border-radius: 999px; background: ${item.is_active ? '#e3f9e5' : '#fde8e8'}; color: ${item.is_active ? '#1f5132' : '#8a1c1c'}; font-size: 12px; font-weight: 700;">
                                        ${item.is_active ? 'Active' : 'Inactive'}
                                    </span>
                                </div>
                                <div class="muted">Category: ${escapeHtml(getCategoryLabel(item.category))} | Unit: ${escapeHtml(item.unit)} | Price: ${formatCurrency(item.default_unit_price)}</div>
                                <div class="muted">Brand: ${escapeHtml(item.brand || 'N/A')} | Model: ${escapeHtml(item.model || 'N/A')}</div>
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

                    itemStack.appendChild(row);
                });

                section.appendChild(sectionHeader);
                section.appendChild(itemStack);
                list.appendChild(section);
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
            setVisible(groupSummary, false);

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
                setVisible(form, true, 'grid');
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
