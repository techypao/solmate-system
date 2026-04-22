@extends('layouts.app', ['title' => 'Quotation Item Builder'])

@section('content')
    <div class="admin-page-stack">
    <div class="card admin-hero-card">
        <p class="admin-page-eyebrow">Quotation Workspace</p>
        <h1 class="page-title">Quotation Item Builder</h1>
        <p class="page-copy">Load an existing final quotation, edit its itemized line items, and save them through the existing backend sync endpoint.</p>

        <div id="builder-success" class="status" style="display: none;"></div>
        <div id="builder-error" class="error-box" style="display: none;"></div>

        <form id="quotation-loader-form" class="form-grid two-columns">
            <div>
                <label for="quotation_id">Quotation ID</label>
                <input id="quotation_id" name="quotation_id" type="number" min="1" value="{{ $initialQuotationId }}" required>
                <div class="field-error" data-loader-error></div>
            </div>

            <div class="actions" style="align-self: end;">
                <button id="load-quotation-button" type="submit">Load quotation</button>
            </div>
        </form>
    </div>

    <div id="builder-loading" class="info-box" style="display: none; margin-top: 16px;">Loading quotation builder data...</div>

    <div id="builder-locked" class="card admin-section-surface" style="display: none; margin-top: 16px;">
        <h2 class="admin-section-title" style="margin-top: 0;">Editing Unavailable</h2>
        <p id="builder-locked-message" class="page-copy" style="margin-bottom: 0;"></p>
    </div>

        <div id="builder-content" style="display: none;">
        <div class="card admin-section-surface" style="margin-top: 16px;">
            <h2 class="admin-section-title" style="margin-top: 0;">Quotation Summary</h2>
            <div id="quotation-summary" class="stack"></div>
        </div>

        <div class="card admin-section-surface" style="margin-top: 16px;">
            <div class="actions" style="justify-content: space-between;">
                <div>
                    <h2 class="admin-section-title" style="margin: 0 0 6px;">Line Items</h2>
                    <div class="muted">Choose from the active catalog or enter custom snapshot values manually.</div>
                </div>
                <button id="add-line-item-button" type="button" class="secondary">Add line item</button>
            </div>

            <div id="line-item-errors" class="error-box" style="display: none; margin-top: 16px;"></div>
            <div id="line-items-empty" class="info-box" style="display: none; margin-top: 16px;">No line items yet. Add at least one item or save an empty set to clear existing rows.</div>
            <div id="line-items-list" class="stack" style="margin-top: 16px;"></div>
        </div>

        <div class="card admin-section-surface" style="margin-top: 16px;">
            <h2 class="admin-section-title" style="margin-top: 0;">Subtotal Preview</h2>
            <div id="totals-preview" class="stack"></div>

            <div class="actions" style="margin-top: 20px;">
                <button id="save-line-items-button" type="button">Save line items</button>
                <span class="muted">This replaces the quotation's current line-item set using the existing sync API.</span>
            </div>
        </div>
    </div>
    </div>
@endsection

@push('scripts')
    <script type="application/json" id="__ib-categories">{!! json_encode($categories, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}</script>
    <script type="application/json" id="__ib-quotation-id">{!! json_encode($initialQuotationId, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}</script>
    <script>
        const categories = JSON.parse(document.getElementById('__ib-categories').textContent);
        const initialQuotationId = JSON.parse(document.getElementById('__ib-quotation-id').textContent);
        const quotationLoaderForm = document.getElementById('quotation-loader-form');
        const quotationIdInput = document.getElementById('quotation_id');
        const loaderError = document.querySelector('[data-loader-error]');
        const loadQuotationButton = document.getElementById('load-quotation-button');
        const builderLoading = document.getElementById('builder-loading');
        const builderContent = document.getElementById('builder-content');
        const builderLocked = document.getElementById('builder-locked');
        const builderLockedMessage = document.getElementById('builder-locked-message');
        const builderSuccess = document.getElementById('builder-success');
        const builderError = document.getElementById('builder-error');
        const quotationSummary = document.getElementById('quotation-summary');
        const lineItemsList = document.getElementById('line-items-list');
        const lineItemsEmpty = document.getElementById('line-items-empty');
        const lineItemErrors = document.getElementById('line-item-errors');
        const totalsPreview = document.getElementById('totals-preview');
        const addLineItemButton = document.getElementById('add-line-item-button');
        const saveLineItemsButton = document.getElementById('save-line-items-button');

        let quotationState = null;
        let pricingCatalog = [];
        let lineItemsState = [];

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
            builderSuccess.textContent = '';
            builderError.textContent = '';
            lineItemErrors.innerHTML = '';
            setVisible(builderSuccess, false);
            setVisible(builderError, false);
            setVisible(lineItemErrors, false);
            loaderError.textContent = '';
        }

        function showError(message) {
            builderError.textContent = message;
            setVisible(builderError, true);
        }

        function showValidationErrors(errors) {
            const messages = [];

            Object.values(errors || {}).forEach((fieldMessages) => {
                if (Array.isArray(fieldMessages)) {
                    messages.push(...fieldMessages);
                }
            });

            if (!messages.length) {
                return;
            }

            lineItemErrors.innerHTML = `<strong>Please review the line items.</strong><ul>${messages.map((message) => `<li>${escapeHtml(message)}</li>`).join('')}</ul>`;
            setVisible(lineItemErrors, true);
        }

        function escapeHtml(value) {
            return String(value ?? '')
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
        }

        function formatMoney(value) {
            return Number(value || 0).toFixed(2);
        }

        function updateUrl(quotationId) {
            const url = new URL(window.location.href);

            if (quotationId) {
                url.searchParams.set('quotation_id', quotationId);
            } else {
                url.searchParams.delete('quotation_id');
            }

            window.history.replaceState({}, '', url.toString());
        }

        function categoryOptions(selectedValue) {
            return categories.map((category) => {
                const selected = category === selectedValue ? 'selected' : '';
                return `<option value="${escapeHtml(category)}" ${selected}>${escapeHtml(category)}</option>`;
            }).join('');
        }

        function pricingOptions(selectedValue) {
            const options = ['<option value="">Custom item</option>'];

            pricingCatalog.forEach((item) => {
                const selected = Number(selectedValue) === Number(item.id) ? 'selected' : '';
                options.push(`<option value="${item.id}" ${selected}>${escapeHtml(item.name)} (${escapeHtml(item.category)})</option>`);
            });

            return options.join('');
        }

        function createBlankLineItem() {
            return {
                pricing_item_id: null,
                description: '',
                category: categories[0] || 'panel',
                qty: 1,
                unit: '',
                unit_amount: 0,
                total_amount: 0,
            };
        }

        function normalizeLineItem(item = {}) {
            return {
                pricing_item_id: item.pricing_item_id ? Number(item.pricing_item_id) : null,
                description: item.description || '',
                category: item.category || categories[0] || 'panel',
                qty: Number(item.qty ?? 1),
                unit: item.unit || '',
                unit_amount: Number(item.unit_amount ?? 0),
                total_amount: Number(item.total_amount ?? 0),
            };
        }

        function calculateRowTotal(item) {
            return Number(item.qty || 0) * Number(item.unit_amount || 0);
        }

        function updateLineItem(index, field, value) {
            const item = lineItemsState[index];

            if (!item) {
                return;
            }

            if (field === 'pricing_item_id') {
                item.pricing_item_id = value ? Number(value) : null;

                const selectedItem = pricingCatalog.find((catalogItem) => Number(catalogItem.id) === Number(value));

                if (selectedItem) {
                    item.description = selectedItem.name;
                    item.category = selectedItem.category;
                    item.unit = selectedItem.unit;
                    item.unit_amount = Number(selectedItem.default_unit_price || 0);
                }
            } else if (field === 'qty' || field === 'unit_amount') {
                item[field] = Number(value || 0);
            } else {
                item[field] = value;
            }

            item.total_amount = calculateRowTotal(item);
            renderLineItems();
            renderTotals();
        }

        function removeLineItem(index) {
            lineItemsState.splice(index, 1);
            renderLineItems();
            renderTotals();
        }

        function addLineItem(item = createBlankLineItem()) {
            lineItemsState.push(normalizeLineItem(item));
            renderLineItems();
            renderTotals();
        }

        function renderSummary() {
            if (!quotationState) {
                quotationSummary.innerHTML = '';
                return;
            }

            quotationSummary.innerHTML = `
                <div><strong>Quotation ID:</strong> ${quotationState.id}</div>
                <div><strong>Type:</strong> ${escapeHtml(quotationState.quotation_type)}</div>
                <div><strong>Status:</strong> ${escapeHtml(quotationState.status || 'pending')}</div>
                <div><strong>Monthly electric bill:</strong> ${formatMoney(quotationState.monthly_electric_bill)}</div>
                <div><strong>Saved materials subtotal:</strong> ${formatMoney(quotationState.materials_subtotal)}</div>
                <div><strong>Saved labor cost:</strong> ${formatMoney(quotationState.labor_cost)}</div>
                <div><strong>Saved project cost:</strong> ${formatMoney(quotationState.project_cost)}</div>
                <div><strong>Remarks:</strong> ${escapeHtml(quotationState.remarks || 'No remarks')}</div>
            `;
        }

        function renderLineItems() {
            lineItemsList.innerHTML = '';

            if (!lineItemsState.length) {
                setVisible(lineItemsEmpty, true);
                return;
            }

            setVisible(lineItemsEmpty, false);

            lineItemsState.forEach((item, index) => {
                const row = document.createElement('div');
                row.className = 'card';
                row.style.padding = '18px';
                row.innerHTML = `
                    <div class="actions" style="justify-content: space-between; align-items: center;">
                        <strong>Line Item ${index + 1}</strong>
                        <button type="button" class="secondary" data-action="remove">Remove</button>
                    </div>
                    <div class="form-grid two-columns" style="margin-top: 16px;">
                        <div style="grid-column: 1 / -1;">
                            <label>Catalog Item</label>
                            <select data-field="pricing_item_id">
                                ${pricingOptions(item.pricing_item_id)}
                            </select>
                        </div>

                        <div style="grid-column: 1 / -1;">
                            <label>Description</label>
                            <input data-field="description" type="text" value="${escapeHtml(item.description)}">
                        </div>

                        <div>
                            <label>Category</label>
                            <select data-field="category">
                                ${categoryOptions(item.category)}
                            </select>
                        </div>

                        <div>
                            <label>Unit</label>
                            <input data-field="unit" type="text" value="${escapeHtml(item.unit)}">
                        </div>

                        <div>
                            <label>Qty</label>
                            <input data-field="qty" type="number" min="0" step="0.01" value="${escapeHtml(item.qty)}">
                        </div>

                        <div>
                            <label>Unit Amount</label>
                            <input data-field="unit_amount" type="number" min="0" step="0.01" value="${escapeHtml(item.unit_amount)}">
                        </div>

                        <div>
                            <label>Total Amount</label>
                            <input type="text" value="${escapeHtml(formatMoney(item.total_amount))}" readonly style="background: #f8fbfd;">
                        </div>
                    </div>
                `;

                row.querySelector('[data-action="remove"]').addEventListener('click', () => {
                    clearMessages();
                    removeLineItem(index);
                });

                row.querySelectorAll('[data-field]').forEach((input) => {
                    input.addEventListener('change', (event) => {
                        updateLineItem(index, event.target.dataset.field, event.target.value);
                    });

                    if (input.tagName === 'INPUT') {
                        input.addEventListener('input', (event) => {
                            updateLineItem(index, event.target.dataset.field, event.target.value);
                        });
                    }
                });

                lineItemsList.appendChild(row);
            });
        }

        function renderTotals() {
            const subtotal = lineItemsState.reduce((sum, item) => sum + calculateRowTotal(item), 0);
            const laborCost = Number(quotationState?.labor_cost || 0);
            const projectTotal = subtotal + laborCost;

            totalsPreview.innerHTML = `
                <div><strong>Line-item subtotal:</strong> ${formatMoney(subtotal)}</div>
                <div><strong>Labor cost:</strong> ${formatMoney(laborCost)}</div>
                <div><strong>Projected total:</strong> ${formatMoney(projectTotal)}</div>
            `;
        }

        async function loadPricingCatalog() {
            const response = await fetch('/api/pricing-items', {
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            const responseBody = await response.json();

            if (!response.ok) {
                throw new Error(responseBody.message || 'Could not load pricing catalog.');
            }

            pricingCatalog = responseBody.data || [];
        }

        async function loadQuotation(quotationId) {
            const response = await fetch(`/api/quotations/${quotationId}`, {
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            const responseBody = await response.json();

            if (!response.ok) {
                throw new Error(responseBody.message || 'Could not load quotation.');
            }

            quotationState = responseBody;
            updateUrl(quotationId);

            if (quotationState.quotation_type !== 'final') {
                setVisible(builderContent, false);
                setVisible(builderLocked, true);
                builderLockedMessage.textContent = 'Only final quotations can be edited in the item builder. This quotation is not editable here.';
                return;
            }

            setVisible(builderLocked, false);

            lineItemsState = (quotationState.line_items || []).map((item) => normalizeLineItem(item));

            renderSummary();
            renderLineItems();
            renderTotals();
            setVisible(builderContent, true);
        }

        async function loadBuilder(quotationId) {
            clearMessages();
            setVisible(builderLoading, true);
            setVisible(builderContent, false);
            setVisible(builderLocked, false);

            try {
                await loadPricingCatalog();
                await loadQuotation(quotationId);
            } catch (error) {
                quotationState = null;
                lineItemsState = [];
                setVisible(builderContent, false);
                setVisible(builderLocked, false);
                showError(error.message || 'Could not load quotation builder data.');
            } finally {
                setVisible(builderLoading, false);
            }
        }

        async function saveLineItems() {
            if (!quotationState || quotationState.quotation_type !== 'final') {
                showError('Only final quotations can be saved in the item builder.');
                return;
            }

            clearMessages();
            saveLineItemsButton.disabled = true;
            saveLineItemsButton.textContent = 'Saving...';

            try {
                await ensureCsrfCookie();

                const payload = {
                    line_items: lineItemsState.map((item) => ({
                        pricing_item_id: item.pricing_item_id || null,
                        description: item.description,
                        category: item.category,
                        qty: Number(item.qty || 0),
                        unit: item.unit,
                        unit_amount: Number(item.unit_amount || 0),
                        total_amount: Number(calculateRowTotal(item) || 0),
                    })),
                };

                const response = await fetch(`/api/quotations/${quotationState.id}/line-items`, {
                    method: 'PUT',
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
                    showValidationErrors(responseBody.errors || {});

                    if (!responseBody.errors) {
                        throw new Error(responseBody.message || 'Please review the line items.');
                    }

                    throw new Error('Please review the line items.');
                }

                if (!response.ok) {
                    throw new Error(responseBody.message || 'Could not save line items.');
                }

                quotationState = responseBody.data;
                lineItemsState = (quotationState.line_items || []).map((item) => normalizeLineItem(item));

                renderSummary();
                renderLineItems();
                renderTotals();

                builderSuccess.textContent = responseBody.message || 'Quotation line items updated successfully.';
                setVisible(builderSuccess, true);
            } catch (error) {
                if (!lineItemErrors.innerHTML) {
                    showError(error.message || 'Could not save line items.');
                }
            } finally {
                saveLineItemsButton.disabled = false;
                saveLineItemsButton.textContent = 'Save line items';
            }
        }

        quotationLoaderForm.addEventListener('submit', async (event) => {
            event.preventDefault();

            const quotationId = Number(quotationIdInput.value);

            if (!quotationId) {
                loaderError.textContent = 'Enter a valid quotation ID.';
                return;
            }

            await loadBuilder(quotationId);
        });

        addLineItemButton.addEventListener('click', () => {
            clearMessages();
            addLineItem();
        });

        saveLineItemsButton.addEventListener('click', saveLineItems);

        if (initialQuotationId) {
            loadBuilder(initialQuotationId);
        }
    </script>
@endpush
