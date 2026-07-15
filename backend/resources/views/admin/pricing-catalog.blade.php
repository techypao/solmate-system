@extends('layouts.app', ['title' => 'Admin Item Management'])

@section('content')
    <div class="admin-page-stack">
        <div class="card admin-hero-card">
            <p class="admin-page-eyebrow">Admin Item Catalog</p>
            <h1 class="page-title">Admin Item Management</h1>
            <p class="page-copy">Manage the item catalog used by inspection-based quotations.</p>
        </div>

        <style>
            .pricing-toolbar {
                display: grid;
                grid-template-columns: minmax(220px, 1fr) minmax(160px, 220px) minmax(140px, 180px) auto auto;
                gap: 12px;
                align-items: end;
            }

            .pricing-toolbar label {
                display: block;
                margin-bottom: 6px;
            }

            .pricing-table-section {
                margin-top: 18px;
                border: 1px solid #DDE7EE;
                border-radius: 8px;
                background: #ffffff;
                overflow: hidden;
            }

            .pricing-table-header {
                display: flex;
                justify-content: space-between;
                gap: 12px;
                padding: 16px 18px;
                background: #F8FAFC;
                border-bottom: 1px solid #DDE7EE;
            }

            .pricing-table-title {
                margin: 0;
                color: #123A5A;
                font-size: 17px;
            }

            .pricing-table-wrap {
                overflow-x: auto;
            }

            .pricing-table {
                width: 100%;
                min-width: 1060px;
                border-collapse: collapse;
            }

            .pricing-table th,
            .pricing-table td {
                padding: 12px 14px;
                border-bottom: 1px solid #E6EEF5;
                text-align: left;
                vertical-align: top;
            }

            .pricing-table th {
                background: #FBFDFF;
                color: #496275;
                font-size: 12px;
                font-weight: 800;
                text-transform: uppercase;
            }

            .pricing-table tbody tr:last-child td {
                border-bottom: none;
            }

            .pricing-table tbody tr:hover td {
                background: #F9FCFF;
            }

            .pricing-item-name {
                color: #123A5A;
                font-weight: 800;
            }

            .pricing-spec {
                max-width: 260px;
                color: #496275;
                font-size: 13px;
                line-height: 1.45;
            }

            .pricing-status-pill {
                display: inline-flex;
                align-items: center;
                min-height: 24px;
                padding: 4px 9px;
                border-radius: 999px;
                font-size: 12px;
                font-weight: 800;
            }

            .pricing-status-pill.active {
                background: #E3F9E5;
                color: #1F5132;
            }

            .pricing-status-pill.inactive {
                background: #FDE8E8;
                color: #8A1C1C;
            }

            .pricing-row-actions {
                display: flex;
                flex-wrap: wrap;
                gap: 8px;
                justify-content: flex-end;
            }

            .pricing-modal-overlay {
                position: fixed;
                inset: 0;
                z-index: 1000;
                display: none;
                align-items: center;
                justify-content: center;
                padding: 22px;
                background: rgba(15, 23, 42, 0.48);
            }

            .pricing-modal-overlay.open {
                display: flex;
            }

            .pricing-modal {
                width: min(760px, 100%);
                max-height: calc(100vh - 44px);
                overflow-y: auto;
                border-radius: 8px;
                background: #ffffff;
                box-shadow: 0 24px 70px rgba(15, 23, 42, 0.28);
            }

            .pricing-modal-header {
                display: flex;
                justify-content: space-between;
                gap: 16px;
                padding: 20px 22px;
                border-bottom: 1px solid #E6EEF5;
            }

            .pricing-modal-title {
                margin: 0;
                color: #123A5A;
                font-size: 20px;
            }

            .pricing-modal-body {
                padding: 22px;
            }

            .pricing-modal-footer {
                display: flex;
                flex-wrap: wrap;
                gap: 10px;
                justify-content: flex-end;
                padding: 0 22px 22px;
            }

            .pricing-close-button {
                min-width: 40px;
                width: 40px;
                height: 40px;
                padding: 0;
                justify-content: center;
            }

            .pricing-history-list {
                display: grid;
                gap: 14px;
            }

            .pricing-history-item {
                border: 1px solid #DDE7EE;
                border-radius: 8px;
                overflow: hidden;
            }

            .pricing-history-meta {
                display: flex;
                justify-content: space-between;
                gap: 12px;
                padding: 12px 14px;
                background: #F8FAFC;
                border-bottom: 1px solid #DDE7EE;
            }

            .pricing-history-action {
                color: #123A5A;
                font-weight: 800;
            }

            .pricing-history-values {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 12px;
                padding: 14px;
            }

            .pricing-history-panel {
                min-width: 0;
                padding: 12px;
                border: 1px solid #E6EEF5;
                border-radius: 8px;
                background: #FBFDFF;
            }

            .pricing-history-panel-title {
                margin-bottom: 8px;
                color: #496275;
                font-size: 12px;
                font-weight: 800;
                text-transform: uppercase;
            }

            .pricing-history-field {
                display: grid;
                grid-template-columns: minmax(92px, 34%) 1fr;
                gap: 8px;
                padding: 6px 0;
                border-top: 1px solid #E6EEF5;
                font-size: 13px;
            }

            .pricing-history-field:first-of-type {
                border-top: none;
            }

            .pricing-history-label {
                color: #496275;
                font-weight: 700;
            }

            @media (max-width: 960px) {
                .pricing-toolbar {
                    grid-template-columns: 1fr 1fr;
                }
            }

            @media (max-width: 640px) {
                .pricing-toolbar {
                    grid-template-columns: 1fr;
                }

                .pricing-table-header,
                .pricing-modal-header,
                .pricing-modal-footer,
                .pricing-history-meta {
                    flex-direction: column;
                    align-items: stretch;
                }

                .pricing-history-values {
                    grid-template-columns: 1fr;
                }

                .pricing-row-actions {
                    justify-content: flex-start;
                }
            }
        </style>

        <div class="card admin-section-surface">
            <div id="catalog-loading" class="info-box">Loading item management data...</div>
            <div id="catalog-success" class="status" style="display: none;"></div>
            <div id="catalog-error" class="error-box" style="display: none;"></div>

            <div class="pricing-toolbar" style="margin-top: 16px;">
                <div>
                    <label for="pricing-search">Search</label>
                    <input id="pricing-search" type="search" placeholder="Item, brand, model, unit">
                </div>

                <div>
                    <label for="pricing-category-filter">Category</label>
                    <select id="pricing-category-filter">
                        <option value="">All categories</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category }}">{{ ucfirst($category) }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="pricing-status-filter">Status</label>
                    <select id="pricing-status-filter">
                        <option value="">All statuses</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>

                <button id="reset-filter-button" type="button" class="secondary">Reset</button>
                <button id="add-item-button" type="button">Add Item</button>
            </div>

            <div class="actions" style="justify-content: space-between; margin-top: 18px;">
                <div class="muted" id="pricing-result-count">0 items</div>
                <button id="refresh-button" type="button" class="secondary">Refresh</button>
            </div>

            <div id="pricing-items-empty" class="info-box" style="display: none; margin-top: 16px;">No pricing items found.</div>
            <div id="pricing-items-list" style="display: none;"></div>
        </div>
    </div>

    <div id="pricing-item-modal" class="pricing-modal-overlay" aria-hidden="true">
        <div class="pricing-modal" role="dialog" aria-modal="true" aria-labelledby="pricing-modal-title">
            <div class="pricing-modal-header">
                <div>
                    <h2 id="pricing-modal-title" class="pricing-modal-title">Add Item</h2>
                    <div id="form-mode-hint" class="muted" style="margin-top: 5px;">New item</div>
                </div>
                <button id="close-modal-button" type="button" class="secondary pricing-close-button" aria-label="Close">x</button>
            </div>

            <form id="pricing-item-form">
                <div class="pricing-modal-body">
                    <div id="modal-error" class="error-box" style="display: none; margin-bottom: 16px;"></div>
                    <input id="pricing_item_id" name="pricing_item_id" type="hidden">

                    <div class="form-grid two-columns">
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
                                <span>Active</span>
                            </label>
                            <div class="field-error" data-error-for="is_active"></div>
                        </div>
                    </div>
                </div>

                <div class="pricing-modal-footer">
                    <button id="cancel-modal-button" type="button" class="secondary">Cancel</button>
                    <button id="save-item-button" type="submit">Create item</button>
                </div>
            </form>
        </div>
    </div>

    <div id="pricing-history-modal" class="pricing-modal-overlay" aria-hidden="true">
        <div class="pricing-modal" role="dialog" aria-modal="true" aria-labelledby="pricing-history-title">
            <div class="pricing-modal-header">
                <div>
                    <h2 id="pricing-history-title" class="pricing-modal-title">Item History</h2>
                    <div id="pricing-history-subtitle" class="muted" style="margin-top: 5px;">Previous values and edits</div>
                </div>
                <button id="close-history-button" type="button" class="secondary pricing-close-button" aria-label="Close">x</button>
            </div>

            <div class="pricing-modal-body">
                <div id="pricing-history-content"></div>
            </div>

            <div class="pricing-modal-footer">
                <button id="done-history-button" type="button" class="secondary">Done</button>
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
        const refreshButton = document.getElementById('refresh-button');
        const saveButton = document.getElementById('save-item-button');
        const addItemButton = document.getElementById('add-item-button');
        const closeModalButton = document.getElementById('close-modal-button');
        const cancelModalButton = document.getElementById('cancel-modal-button');
        const modal = document.getElementById('pricing-item-modal');
        const modalErrorBox = document.getElementById('modal-error');
        const modalTitle = document.getElementById('pricing-modal-title');
        const historyModal = document.getElementById('pricing-history-modal');
        const historyTitle = document.getElementById('pricing-history-title');
        const historySubtitle = document.getElementById('pricing-history-subtitle');
        const historyContent = document.getElementById('pricing-history-content');
        const closeHistoryButton = document.getElementById('close-history-button');
        const doneHistoryButton = document.getElementById('done-history-button');
        const formModeHint = document.getElementById('form-mode-hint');
        const searchInput = document.getElementById('pricing-search');
        const categoryFilter = document.getElementById('pricing-category-filter');
        const statusFilter = document.getElementById('pricing-status-filter');
        const resetFilterButton = document.getElementById('reset-filter-button');
        const resultCount = document.getElementById('pricing-result-count');
        const categoryMetadata = {
            panel: 'Panel',
            inverter: 'Inverter',
            battery: 'Battery',
            protection: 'Protection',
            mounting: 'Mounting',
            wiring: 'Wiring',
            grounding: 'Grounding',
            misc: 'Misc',
        };
        const adminRoleLabels = {
            super_admin: 'Super Admin',
            operations_staff: 'Operations Staff',
            customer_support_staff: 'Customer Support Staff',
            content_staff: 'Content Staff',
        };
        const historyFieldLabels = {
            name: 'Name',
            category: 'Category',
            unit: 'Unit',
            default_unit_price: 'Price',
            brand: 'Brand',
            model: 'Model',
            specification: 'Spec',
            is_active: 'Status',
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
        let pricingItems = [];
        let activeModalTrigger = null;

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
            modalErrorBox.textContent = '';
            setVisible(successBox, false);
            setVisible(errorBox, false);
            setVisible(modalErrorBox, false);
        }

        function clearFieldErrors() {
            document.querySelectorAll('[data-error-for]').forEach((element) => {
                element.textContent = '';
            });
        }

        function showError(message) {
            if (modal.classList.contains('open')) {
                modalErrorBox.textContent = message;
                setVisible(modalErrorBox, true);
                return;
            }

            errorBox.textContent = message;
            setVisible(errorBox, true);
        }

        function normalizeBoolean(value) {
            return value === true || value === 1 || value === '1';
        }

        function escapeHtml(value) {
            return String(value ?? '')
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
        }

        function humanizeLabel(value) {
            return String(value || '')
                .replace(/[_-]+/g, ' ')
                .replace(/\b\w/g, (character) => character.toUpperCase());
        }

        function getCategoryLabel(category) {
            return categoryMetadata[String(category || '').toLowerCase()] || humanizeLabel(category || 'Uncategorized');
        }

        function formatCurrency(value) {
            const number = Number(value || 0);
            return number.toLocaleString('en-PH', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            });
        }

        function formatDateTime(value) {
            if (!value) {
                return 'N/A';
            }

            const date = new Date(value);

            if (Number.isNaN(date.getTime())) {
                return 'N/A';
            }

            return new Intl.DateTimeFormat('en-PH', {
                dateStyle: 'medium',
                timeStyle: 'short',
            }).format(date);
        }

        function formatHistoryValue(field, value) {
            if (value === null || value === undefined || value === '') {
                return 'N/A';
            }

            if (field === 'is_active') {
                return normalizeBoolean(value) ? 'Active' : 'Inactive';
            }

            if (field === 'default_unit_price') {
                return formatCurrency(value);
            }

            if (field === 'category') {
                return getCategoryLabel(value);
            }

            return String(value);
        }

        function formatActionLabel(action) {
            return humanizeLabel(action || 'updated');
        }

        function historyPerformerName(history) {
            return history.performed_by_snapshot?.name || history.performed_by?.name || 'System';
        }

        function historyPerformerRole(history) {
            if (history.performed_by_snapshot?.admin_role_label) {
                return history.performed_by_snapshot.admin_role_label;
            }

            if (history.performed_by?.admin_role) {
                return adminRoleLabels[history.performed_by.admin_role] || 'Admin Staff';
            }

            return 'Admin Staff';
        }

        function formatCountLabel(count) {
            return `${count} ${count === 1 ? 'item' : 'items'}`;
        }

        function fillForm(item = initialFormState) {
            form.elements.namedItem('pricing_item_id').value = item.id || item.pricing_item_id || '';
            form.elements.namedItem('name').value = item.name || '';
            form.elements.namedItem('category').value = item.category || initialFormState.category;
            form.elements.namedItem('unit').value = item.unit || '';
            form.elements.namedItem('default_unit_price').value = item.default_unit_price ?? '';
            form.elements.namedItem('brand').value = item.brand || '';
            form.elements.namedItem('model').value = item.model || '';
            form.elements.namedItem('specification').value = item.specification || '';
            form.elements.namedItem('is_active').checked = item.is_active ?? true;

            const isEditing = Boolean(item.id || item.pricing_item_id);
            modalTitle.textContent = isEditing ? 'Edit Item' : 'Add Item';
            saveButton.textContent = isEditing ? 'Update item' : 'Create item';
            formModeHint.textContent = isEditing ? `Item #${item.id || item.pricing_item_id}` : 'New item';
        }

        function openModal(item = initialFormState, trigger = null) {
            activeModalTrigger = trigger || document.activeElement;
            clearMessages();
            clearFieldErrors();
            fillForm(item);
            modal.classList.add('open');
            modal.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
            window.setTimeout(() => form.elements.namedItem('name').focus(), 0);
        }

        function closeModal() {
            modal.classList.remove('open');
            modal.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
            clearFieldErrors();

            if (activeModalTrigger && typeof activeModalTrigger.focus === 'function') {
                activeModalTrigger.focus();
            }

            activeModalTrigger = null;
        }

        function changedHistoryFields(history) {
            const oldValues = history.old_values || {};
            const newValues = history.new_values || {};
            const fields = Object.keys(historyFieldLabels);

            if (!history.old_values || !history.new_values) {
                return fields.filter((field) => oldValues[field] !== undefined || newValues[field] !== undefined);
            }

            return fields.filter((field) => String(oldValues[field] ?? '') !== String(newValues[field] ?? ''));
        }

        function renderHistoryPanel(title, values, fields) {
            const rows = fields.length
                ? fields.map((field) => `
                    <div class="pricing-history-field">
                        <span class="pricing-history-label">${escapeHtml(historyFieldLabels[field] || humanizeLabel(field))}</span>
                        <span>${escapeHtml(formatHistoryValue(field, values?.[field]))}</span>
                    </div>
                `).join('')
                : '<div class="muted">No values recorded.</div>';

            return `
                <div class="pricing-history-panel">
                    <div class="pricing-history-panel-title">${escapeHtml(title)}</div>
                    ${rows}
                </div>
            `;
        }

        function openHistoryModal(item, trigger = null) {
            activeModalTrigger = trigger || document.activeElement;
            const histories = item.histories || [];
            historyTitle.textContent = 'Item History';
            historySubtitle.textContent = item.name || `Item #${item.id}`;

            if (!histories.length) {
                historyContent.innerHTML = '<div class="info-box" style="margin-bottom:0;">No history has been recorded for this item yet.</div>';
            } else {
                historyContent.innerHTML = `
                    <div class="pricing-history-list">
                        ${histories.map((history) => {
                            const fields = changedHistoryFields(history);
                            const performerName = historyPerformerName(history);
                            const performerRole = historyPerformerRole(history);

                            return `
                                <article class="pricing-history-item">
                                    <div class="pricing-history-meta">
                                        <div>
                                            <div class="pricing-history-action">${escapeHtml(formatActionLabel(history.action))}</div>
                                            <div class="muted" style="margin-top:4px;">Changed by: ${escapeHtml(performerName)} | ${escapeHtml(performerRole)}</div>
                                        </div>
                                        <div class="muted">${escapeHtml(formatDateTime(history.created_at))}</div>
                                    </div>
                                    <div class="pricing-history-values">
                                        ${renderHistoryPanel('Before', history.old_values, fields)}
                                        ${renderHistoryPanel('After', history.new_values, fields)}
                                    </div>
                                </article>
                            `;
                        }).join('')}
                    </div>
                `;
            }

            historyModal.classList.add('open');
            historyModal.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
            window.setTimeout(() => closeHistoryButton.focus(), 0);
        }

        function closeHistoryModal() {
            historyModal.classList.remove('open');
            historyModal.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
            historyContent.innerHTML = '';

            if (activeModalTrigger && typeof activeModalTrigger.focus === 'function') {
                activeModalTrigger.focus();
            }

            activeModalTrigger = null;
        }

        function getFilteredItems() {
            const query = searchInput.value.trim().toLowerCase();
            const category = categoryFilter.value;
            const status = statusFilter.value;

            return pricingItems.filter((item) => {
                const itemStatus = item.is_active ? 'active' : 'inactive';
                const searchable = [
                    item.name,
                    item.category,
                    item.unit,
                    item.default_unit_price,
                    item.brand,
                    item.model,
                    item.specification,
                ].join(' ').toLowerCase();

                if (category && item.category !== category) {
                    return false;
                }

                if (status && itemStatus !== status) {
                    return false;
                }

                return !query || searchable.includes(query);
            });
        }

        function groupItemsByCategory(items) {
            const groups = new Map();

            categories.forEach((category) => {
                groups.set(category, []);
            });

            items.forEach((item) => {
                if (!groups.has(item.category)) {
                    groups.set(item.category || 'uncategorized', []);
                }

                groups.get(item.category || 'uncategorized').push(item);
            });

            groups.forEach((groupItems) => {
                groupItems.sort((left, right) => {
                    if (left.is_active !== right.is_active) {
                        return Number(right.is_active) - Number(left.is_active);
                    }

                    return String(left.name || '').localeCompare(String(right.name || ''));
                });
            });

            return Array.from(groups.entries())
                .map(([category, groupItems]) => ({
                    category,
                    label: getCategoryLabel(category),
                    items: groupItems,
                }))
                .filter((group) => group.items.length > 0);
        }

        function renderList() {
            list.innerHTML = '';

            const filteredItems = getFilteredItems();
            const groups = groupItemsByCategory(filteredItems);
            resultCount.textContent = formatCountLabel(filteredItems.length);

            if (!filteredItems.length) {
                setVisible(emptyState, true);
                setVisible(list, false);
                return;
            }

            setVisible(emptyState, false);
            setVisible(list, true);

            groups.forEach((group) => {
                const activeCount = group.items.filter((item) => item.is_active).length;
                const section = document.createElement('section');
                section.className = 'pricing-table-section';

                section.innerHTML = `
                    <div class="pricing-table-header">
                        <div>
                            <h2 class="pricing-table-title">${escapeHtml(group.label)}</h2>
                            <div class="muted" style="margin-top: 4px;">${escapeHtml(formatCountLabel(group.items.length))}</div>
                        </div>
                        <div class="muted">${escapeHtml(activeCount)} active</div>
                    </div>
                    <div class="pricing-table-wrap">
                        <table class="pricing-table">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Brand</th>
                                    <th>Model</th>
                                    <th>Unit</th>
                                    <th>Default Price</th>
                                    <th>Status</th>
                                    <th>Created At</th>
                                    <th>Updated At</th>
                                    <th>Specification</th>
                                    <th style="text-align: right;">Actions</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                `;

                const tbody = section.querySelector('tbody');

                group.items.forEach((item) => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td><div class="pricing-item-name">${escapeHtml(item.name)}</div></td>
                        <td>${escapeHtml(item.brand || 'N/A')}</td>
                        <td>${escapeHtml(item.model || 'N/A')}</td>
                        <td>${escapeHtml(item.unit)}</td>
                        <td>${escapeHtml(formatCurrency(item.default_unit_price))}</td>
                        <td>
                            <span class="pricing-status-pill ${item.is_active ? 'active' : 'inactive'}">
                                ${item.is_active ? 'Active' : 'Inactive'}
                            </span>
                        </td>
                        <td>${escapeHtml(formatDateTime(item.created_at))}</td>
                        <td>${escapeHtml(formatDateTime(item.updated_at))}</td>
                        <td><div class="pricing-spec">${escapeHtml(item.specification || 'N/A')}</div></td>
                        <td>
                            <div class="pricing-row-actions">
                                <button type="button" class="secondary" data-action="history">History</button>
                                <button type="button" class="secondary" data-action="edit">Edit</button>
                                <button type="button" class="${item.is_active ? 'secondary' : ''}" data-action="toggle">
                                    ${item.is_active ? 'Deactivate' : 'Activate'}
                                </button>
                            </div>
                        </td>
                    `;

                    row.querySelector('[data-action="edit"]').addEventListener('click', (event) => {
                        openModal(item, event.currentTarget);
                    });

                    row.querySelector('[data-action="history"]').addEventListener('click', (event) => {
                        openHistoryModal(item, event.currentTarget);
                    });

                    row.querySelector('[data-action="toggle"]').addEventListener('click', async (event) => {
                        await toggleItemStatus(item, event.currentTarget);
                    });

                    tbody.appendChild(row);
                });

                list.appendChild(section);
            });
        }

        async function loadPricingItems(preserveMessages = false) {
            if (!preserveMessages) {
                clearMessages();
            }

            setVisible(loadingBox, true);
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
                pricingItems = payload.data || [];
                renderList();
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
                        const errorElement = document.querySelector(`[data-error-for="${field}"]`);

                        if (errorElement) {
                            errorElement.textContent = messages[0];
                        }
                    });

                    throw new Error('Please fix the highlighted fields.');
                }

                if (!response.ok) {
                    throw new Error(responseBody.message || 'Could not save pricing item.');
                }

                closeModal();
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

        async function toggleItemStatus(item, button) {
            clearMessages();
            const originalLabel = item.is_active ? 'Deactivate' : 'Activate';
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
                button.textContent = originalLabel;
            }
        }

        form.addEventListener('submit', savePricingItem);
        refreshButton.addEventListener('click', () => loadPricingItems());
        addItemButton.addEventListener('click', (event) => openModal(initialFormState, event.currentTarget));
        closeModalButton.addEventListener('click', closeModal);
        cancelModalButton.addEventListener('click', closeModal);
        closeHistoryButton.addEventListener('click', closeHistoryModal);
        doneHistoryButton.addEventListener('click', closeHistoryModal);
        searchInput.addEventListener('input', renderList);
        categoryFilter.addEventListener('change', renderList);
        statusFilter.addEventListener('change', renderList);
        resetFilterButton.addEventListener('click', () => {
            searchInput.value = '';
            categoryFilter.value = '';
            statusFilter.value = '';
            renderList();
        });

        modal.addEventListener('click', (event) => {
            if (event.target === modal) {
                closeModal();
            }
        });

        historyModal.addEventListener('click', (event) => {
            if (event.target === historyModal) {
                closeHistoryModal();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && modal.classList.contains('open')) {
                closeModal();
            }

            if (event.key === 'Escape' && historyModal.classList.contains('open')) {
                closeHistoryModal();
            }
        });

        loadPricingItems();
    </script>
@endpush
