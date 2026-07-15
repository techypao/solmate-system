@extends('layouts.app', ['title' => 'Quotation Item Builder'])

@section('content')
    <style>
        .ib-available-card {
            margin-top: 18px;
            padding: 18px;
            border: 1px solid #DDE7EE;
            border-radius: 18px;
            background: linear-gradient(180deg, #ffffff 0%, #F8FAFC 100%);
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.05);
        }

        .ib-available-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 14px;
        }

        .ib-available-title {
            margin: 0;
            color: #123A5A;
            font-size: 18px;
            font-weight: 800;
            letter-spacing: -0.02em;
        }

        .ib-available-copy {
            margin: 6px 0 0;
            color: #5E7288;
            font-size: 13px;
            line-height: 1.6;
        }

        .ib-available-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 34px;
            padding: 0 12px;
            border-radius: 999px;
            background: #EAF9FD;
            color: #20A7C9;
            font-size: 12px;
            font-weight: 800;
            white-space: nowrap;
        }

        .ib-helper-text {
            margin: 8px 0 0;
            color: #5E7288;
            font-size: 12px;
            line-height: 1.5;
        }

        .ib-loader-form {
            margin-top: 8px;
        }

        .ib-loader-field {
            display: grid;
            gap: 8px;
        }

        .ib-loader-row {
            display: grid;
            grid-template-columns: 1fr;
            gap: 12px;
            align-items: start;
        }

        .ib-loader-input {
            width: 100%;
            height: 48px;
            min-height: 48px;
            margin: 0;
            box-sizing: border-box;
        }

        .ib-loader-actions {
            display: flex;
            align-items: flex-start;
        }

        .ib-loader-submit {
            display: flex;
            align-items: center;
            justify-content: center;
            width: auto;
            height: 48px;
            min-width: 180px;
            min-height: 48px;
            padding: 0 18px;
            font-size: 13px;
            font-weight: 800;
            line-height: 1;
            white-space: nowrap;
            box-sizing: border-box;
        }

        .ib-select-wrap {
            margin-top: 14px;
        }

        .ib-select-wrap label {
            display: block;
            margin-bottom: 6px;
            color: #123A5A;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .ib-select-wrap select {
            width: 100%;
        }
        .ib-table-wrap {
            margin-top: 16px;
            overflow-x: auto;
        }

        .ib-table {
            width: 100%;
            border-collapse: collapse;
        }

        .ib-table th,
        .ib-table td {
            padding: 12px 14px;
            border-bottom: 1px solid #e8eff7;
            text-align: left;
            vertical-align: middle;
        }

        .ib-table th {
            background: #f7fafd;
            color: #5E7288;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .ib-table td {
            color: #334155;
            font-size: 13px;
        }

        .ib-table tbody tr:last-child td {
            border-bottom: none;
        }

        .ib-table-id {
            color: #123A5A;
            font-weight: 800;
        }

        .ib-meta-stack {
            display: grid;
            gap: 4px;
        }

        .ib-inline-note {
            color: #5E7288;
            font-size: 12px;
        }

        .ib-use-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 34px;
            padding: 0 12px;
            border-radius: 10px;
            border: 1px solid #DDE7EE;
            background: #f4f9ff;
            color: #20A7C9;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.15s ease;
        }

        .ib-use-btn:hover,
        .ib-use-btn:focus-visible {
            border-color: #F4D000;
            background: #fff7e0;
            color: #123A5A;
            outline: none;
        }

        .ib-empty-state {
            margin-top: 14px;
            padding: 18px;
            border: 1px dashed #DDE7EE;
            border-radius: 14px;
            background: #F8FAFC;
            color: #5E7288;
            font-size: 13px;
            line-height: 1.7;
            text-align: center;
        }

        .ib-empty-state p {
            margin: 0;
        }

        .ib-negotiation-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(280px, 420px);
            gap: 18px;
            align-items: start;
        }

        .ib-negotiation-status {
            display: grid;
            gap: 10px;
        }

        .ib-negotiation-pill {
            display: inline-flex;
            align-items: center;
            width: fit-content;
            min-height: 30px;
            padding: 0 11px;
            border-radius: 999px;
            background: #EAF9FD;
            color: #20A7C9;
            font-size: 12px;
            font-weight: 800;
            text-transform: capitalize;
        }

        .ib-negotiation-message {
            padding: 14px 16px;
            border: 1px solid #DDE7EE;
            border-radius: 14px;
            background: #F8FAFC;
            color: #334155;
            font-size: 13px;
            line-height: 1.7;
            white-space: pre-wrap;
        }

        .ib-negotiation-form {
            display: grid;
            gap: 14px;
        }

        @media (max-width: 720px) {
            .ib-available-card {
                padding: 16px;
            }

            .ib-loader-form {
                margin-top: 6px;
            }

            .ib-loader-actions {
                width: 100%;
            }

            .ib-loader-submit {
                width: 100%;
                min-width: 0;
            }

            .ib-table th,
            .ib-table td {
                padding: 10px 12px;
            }

            .ib-negotiation-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="admin-page-stack">
    <div class="card admin-hero-card">
        <p class="admin-page-eyebrow">Quotation Workspace</p>
        <h1 class="page-title">Quotation Item Builder</h1>
        <p class="page-copy">Load an existing inspection-based quotation, edit its itemized line items, and save them through the existing backend sync endpoint.</p>

        <div id="builder-success" class="status" style="display: none;"></div>
        <div id="builder-error" class="error-box" style="display: none;"></div>

        <form id="quotation-loader-form" class="ib-loader-form">
            <div class="ib-loader-field">
                <label for="quotation_id">Quotation ID</label>
                <div class="ib-loader-row">
                    <input
                        id="quotation_id"
                        name="quotation_id"
                        class="ib-loader-input"
                        type="number"
                        min="1"
                        value="{{ $initialQuotationId }}"
                        required
                    >
                    <div class="actions ib-loader-actions">
                        <button id="load-quotation-button" class="ib-loader-submit" type="submit">Load quotation</button>
                    </div>
                </div>
                <p class="ib-helper-text">Select or enter an existing quotation ID from the list below.</p>
                <div class="field-error" data-loader-error></div>
            </div>
        </form>

        <section class="ib-available-card" aria-label="Available quotation IDs">
            <div class="ib-available-head">
                <div>
                    <h2 class="ib-available-title">Available Quotation IDs</h2>
                    <p class="ib-available-copy">Choose from the latest inspection-based quotations to auto-fill the input, then click <strong>Load quotation</strong>.</p>
                </div>
                <span class="ib-available-pill">Latest {{ $availableQuotations->count() }}</span>
            </div>

            @if ($availableQuotations->isNotEmpty())
                <div class="ib-select-wrap">
                    <label for="available_quotation_id">Quick Select</label>
                    <select id="available_quotation_id" name="available_quotation_id">
                        <option value="">Select a quotation ID</option>
                        @foreach ($availableQuotations as $availableQuotation)
                            @php
                                $isInspectionBasedQuotation = strtolower((string) ($availableQuotation->quotation_type ?? 'initial')) === 'final';
                                $displayStatus = $availableQuotation->status ?? 'pending';

                                if (
                                    $isInspectionBasedQuotation
                                    && strtolower((string) $displayStatus) === 'pending'
                                    && filled($availableQuotation->inspectionRequest?->status)
                                ) {
                                    $displayStatus = $availableQuotation->inspectionRequest->status;
                                }
                            @endphp
                            <option value="{{ $availableQuotation->id }}">
                                #{{ $availableQuotation->id }}
                                @if ($availableQuotation->customer?->name)
                                    · {{ $availableQuotation->customer->name }}
                                @endif
                                · {{ $isInspectionBasedQuotation ? 'Inspection-Based Quotation' : 'Pre-Inspection Estimate' }}
                                · {{ \Illuminate\Support\Str::headline($displayStatus) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="actions" id="available-quotation-back-row" style="display: none; justify-content: flex-start; margin-top: 12px;">
                    <button type="button" id="show-available-quotations-button" class="ib-use-btn">Back to available quotation IDs</button>
                </div>

                <div id="available-quotation-table" class="ib-table-wrap">
                    <table class="ib-table">
                        <thead>
                            <tr>
                                <th>Quotation ID</th>
                                <th>Customer</th>
                                <th>Type / Status</th>
                                <th>Created</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($availableQuotations as $availableQuotation)
                                @php
                                    $isInspectionBasedQuotation = strtolower((string) ($availableQuotation->quotation_type ?? 'initial')) === 'final';
                                    $displayStatus = $availableQuotation->status ?? 'pending';

                                    if (
                                        $isInspectionBasedQuotation
                                        && strtolower((string) $displayStatus) === 'pending'
                                        && filled($availableQuotation->inspectionRequest?->status)
                                    ) {
                                        $displayStatus = $availableQuotation->inspectionRequest->status;
                                    }
                                @endphp
                                <tr>
                                    <td class="ib-table-id">#{{ $availableQuotation->id }}</td>
                                    <td>{{ $availableQuotation->customer?->name ?? '—' }}</td>
                                    <td>
                                        <div class="ib-meta-stack">
	                                            <span>{{ $isInspectionBasedQuotation ? 'Inspection-Based Quotation' : 'Pre-Inspection Estimate' }}</span>
	                                            <span class="ib-inline-note">{{ \Illuminate\Support\Str::headline($displayStatus) }}</span>
	                                        </div>
                                    </td>
                                    <td>{{ optional($availableQuotation->created_at)->format('M d, Y h:i A') ?? '—' }}</td>
                                    <td>
                                        <button
                                            type="button"
                                            class="ib-use-btn"
                                            data-quotation-fill="{{ $availableQuotation->id }}"
                                        >
                                            Use ID
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="ib-empty-state">
                    <p>No inspection-based quotations available yet.</p>
                </div>
            @endif
        </section>
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

            @if (auth()->user()?->role === \App\Models\User::ROLE_ADMIN)
                <div id="discount-negotiation-panel" class="card admin-section-surface" style="margin-top: 16px; display: none;">
                    <div class="actions" style="justify-content: space-between; align-items: flex-start;">
                        <div>
                            <h2 class="admin-section-title" style="margin: 0 0 6px;">Customer Discount Negotiation</h2>
                            <div class="muted">Review customer discount requests and apply an admin-only peso discount to the customer-facing quotation total.</div>
                        </div>
                    </div>

                    <div class="ib-negotiation-grid" style="margin-top: 16px;">
                        <div class="ib-negotiation-status">
                            <span id="discount-status-pill" class="ib-negotiation-pill">None</span>
                            <div id="discount-request-message" class="ib-negotiation-message">No customer discount request yet.</div>
                            <div id="discount-breakdown" class="stack"></div>
                        </div>

                        <div class="ib-negotiation-form">
                            <div>
                                <label for="admin_discount_amount">Admin Discount Amount</label>
                                <input id="admin_discount_amount" type="number" min="0" step="0.01" placeholder="0.00">
                                <div class="field-error" data-discount-error-for="admin_discount_amount"></div>
                            </div>

                            <div>
                                <label for="admin_discount_reason">Admin Note</label>
                                <textarea id="admin_discount_reason" rows="4" maxlength="1000" placeholder="Optional note for this discount or review decision"></textarea>
                                <div class="field-error" data-discount-error-for="admin_discount_reason"></div>
                            </div>

                            <div class="actions" style="margin-top: 0;">
                                <button id="apply-discount-button" type="button">Apply discount</button>
                                <button id="reject-discount-button" type="button" class="secondary">Reject request</button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

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
        const isAdminUser = @json(auth()->user()?->role === \App\Models\User::ROLE_ADMIN);
        const quotationLoaderForm = document.getElementById('quotation-loader-form');
        const quotationIdInput = document.getElementById('quotation_id');
        const availableQuotationSelect = document.getElementById('available_quotation_id');
        const availableQuotationTable = document.getElementById('available-quotation-table');
        const availableQuotationBackRow = document.getElementById('available-quotation-back-row');
        const showAvailableQuotationsButton = document.getElementById('show-available-quotations-button');
        const quotationFillButtons = document.querySelectorAll('[data-quotation-fill]');
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
        const discountNegotiationPanel = document.getElementById('discount-negotiation-panel');
        const discountStatusPill = document.getElementById('discount-status-pill');
        const discountRequestMessage = document.getElementById('discount-request-message');
        const discountBreakdown = document.getElementById('discount-breakdown');
        const adminDiscountAmountInput = document.getElementById('admin_discount_amount');
        const adminDiscountReasonInput = document.getElementById('admin_discount_reason');
        const applyDiscountButton = document.getElementById('apply-discount-button');
        const rejectDiscountButton = document.getElementById('reject-discount-button');

        let quotationState = null;
        let pricingCatalog = [];
        let lineItemsState = [];

        function setVisible(element, visible) {
            element.style.display = visible ? 'block' : 'none';
        }

        function setAvailableQuotationTableVisible(visible) {
            if (availableQuotationTable) {
                setVisible(availableQuotationTable, visible);
            }

            if (availableQuotationBackRow) {
                availableQuotationBackRow.style.display = visible ? 'none' : 'flex';
            }
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
            document.querySelectorAll('[data-discount-error-for]').forEach((element) => {
                element.textContent = '';
            });
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

        function formatQuotationTypeLabel(type) {
            const normalized = String(type || 'initial').trim().toLowerCase();

            return normalized === 'final'
                ? 'Inspection-Based Quotation'
                : 'Pre-Inspection Estimate';
        }

        function formatPromoTypeLabel(type) {
            switch (String(type || '').trim().toLowerCase()) {
                case 'percentage':
                    return 'Percentage Discount';
                case 'fixed_amount':
                    return 'Fixed Discount';
                case 'free_item':
                    return 'Free Item Promo';
                case 'bundle':
                    return 'Bundle Deal';
                default:
                    return 'Promo';
            }
        }

        function getAppliedPromo() {
            return quotationState?.applied_promo || null;
        }

        function buildPromoLineItemContext() {
            const aggregates = {};

            lineItemsState.forEach((item) => {
                const category = String(item.category || '').trim();
                const qty = Number(item.qty || 0);
                const totalAmount = Number(calculateRowTotal(item) || 0);

                if (!category || qty <= 0) {
                    return;
                }

                if (!aggregates[category]) {
                    aggregates[category] = {qty: 0, total: 0};
                }

                aggregates[category].qty += qty;
                aggregates[category].total += totalAmount;
            });

            return Object.entries(aggregates).reduce((context, [category, aggregate]) => {
                if (aggregate.qty <= 0) {
                    return context;
                }

                context[`${category}_qty`] = aggregate.qty;
                context[`${category}_unit_price`] = aggregate.total / aggregate.qty;
                return context;
            }, {});
        }

        function computePromoDiscountPreview(baseTotal) {
            const promo = getAppliedPromo();

            if (!promo || !promo.promo_type || baseTotal <= 0) {
                return 0;
            }

            const value = Number(promo.discount_value || 0);

            switch (promo.promo_type) {
                case 'percentage':
                    return Number((baseTotal * (value / 100)).toFixed(2));
                case 'fixed_amount':
                case 'bundle':
                    return Number(Math.min(value, baseTotal).toFixed(2));
                case 'free_item': {
                    const context = buildPromoLineItemContext();
                    const conditions = promo.conditions || {};
                    const appliesTo = conditions.applies_to;
                    const minQty = Number(conditions.min_qty || 0);
                    const freeQty = Number(conditions.free_qty || 1);

                    if (appliesTo && minQty > 0) {
                        const actualQty = Number(context[`${appliesTo}_qty`] || 0);
                        const unitPrice = Number(context[`${appliesTo}_unit_price`] || 0);

                        if (actualQty < minQty || unitPrice <= 0) {
                            return 0;
                        }

                        const promoSetQty = minQty + freeQty;
                        const eligibleFreeQty = Math.floor(actualQty / promoSetQty) * freeQty;

                        if (eligibleFreeQty <= 0) {
                            return 0;
                        }

                        return Number(Math.min(eligibleFreeQty * unitPrice, baseTotal).toFixed(2));
                    }

                    return value > 0 ? Number(Math.min(value, baseTotal).toFixed(2)) : 0;
                }
                default:
                    return 0;
            }
        }

        function formatAppliedPromoLabel(promo) {
            if (!promo) {
                return 'No promo applied';
            }

            return `${promo.title} (${formatPromoTypeLabel(promo.promo_type)})`;
        }

        function getAdminDiscountAmount() {
            return Number(quotationState?.admin_discount_amount || 0);
        }

        function getAdminDiscountBaseTotal() {
            if (quotationState?.admin_discount_base_total !== null && quotationState?.admin_discount_base_total !== undefined) {
                return Number(quotationState.admin_discount_base_total || 0);
            }

            return Number(quotationState?.project_cost || 0) + getAdminDiscountAmount();
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

        function applyQuotationId(quotationId) {
            const normalizedId = Number(quotationId);

            if (!normalizedId) {
                setAvailableQuotationTableVisible(true);
                return;
            }

            quotationIdInput.value = normalizedId;

            if (availableQuotationSelect) {
                availableQuotationSelect.value = String(normalizedId);
            }

            loaderError.textContent = '';
            setAvailableQuotationTableVisible(false);
        }

        function resetBuilderView() {
            quotationState = null;
            lineItemsState = [];
            quotationSummary.innerHTML = '';
            lineItemsList.innerHTML = '';
            totalsPreview.innerHTML = '';
            setVisible(lineItemsEmpty, true);
            setVisible(builderLoading, false);
            setVisible(builderContent, false);
            setVisible(builderLocked, false);
            clearMessages();
            updateUrl(null);
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

            const appliedPromo = getAppliedPromo();
            const adminDiscount = getAdminDiscountAmount();

            quotationSummary.innerHTML = `
                <div><strong>Quotation ID:</strong> ${quotationState.id}</div>
                <div><strong>Type:</strong> ${escapeHtml(formatQuotationTypeLabel(quotationState.quotation_type))}</div>
                <div><strong>Status:</strong> ${escapeHtml(quotationState.status || 'pending')}</div>
                <div><strong>Monthly electric bill:</strong> ${formatMoney(quotationState.monthly_electric_bill)}</div>
                <div><strong>Saved materials subtotal:</strong> ${formatMoney(quotationState.materials_subtotal)}</div>
                <div><strong>Saved labor cost:</strong> ${formatMoney(quotationState.labor_cost)}</div>
                <div><strong>Applied promo:</strong> ${escapeHtml(formatAppliedPromoLabel(appliedPromo))}</div>
                <div><strong>Saved promo discount:</strong> ${formatMoney(quotationState.promo_discount)}</div>
                <div><strong>Admin discount:</strong> ${formatMoney(adminDiscount)}</div>
                <div><strong>Saved final project cost:</strong> ${formatMoney(quotationState.project_cost)}</div>
                <div><strong>Remarks:</strong> ${escapeHtml(quotationState.remarks || 'No remarks')}</div>
            `;
        }

        function renderDiscountNegotiation() {
            if (!isAdminUser || !discountNegotiationPanel || !quotationState) {
                return;
            }

            const status = String(quotationState.discount_request_status || 'none').replace(/_/g, ' ');
            const requestMessage = quotationState.discount_request_message
                ? quotationState.discount_request_message
                : 'No customer discount request yet.';
            const adminDiscount = getAdminDiscountAmount();
            const baseTotal = getAdminDiscountBaseTotal();
            const updatedTotal = Number(quotationState.project_cost || 0);

            discountStatusPill.textContent = status;
            discountRequestMessage.textContent = requestMessage;
            discountBreakdown.innerHTML = `
                <div><strong>Original total before admin discount:</strong> ${formatMoney(baseTotal)}</div>
                <div><strong>Current admin discount:</strong> ${formatMoney(adminDiscount)}</div>
                <div><strong>Updated customer-facing total:</strong> ${formatMoney(updatedTotal)}</div>
            `;

            adminDiscountAmountInput.value = adminDiscount > 0 ? adminDiscount.toFixed(2) : '';
            adminDiscountAmountInput.max = baseTotal > 0 ? String(baseTotal) : '';
            adminDiscountReasonInput.value = quotationState.admin_discount_reason || '';
            setVisible(discountNegotiationPanel, true);
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
            const baseTotal = subtotal + laborCost;
            const promoDiscount = computePromoDiscountPreview(baseTotal);
            const appliedPromo = getAppliedPromo();
            const adminDiscount = getAdminDiscountAmount();
            const totalBeforeAdminDiscount = Math.max(0, baseTotal - promoDiscount);
            const finalTotal = Math.max(0, totalBeforeAdminDiscount - adminDiscount);

            totalsPreview.innerHTML = `
                <div><strong>Line-item subtotal:</strong> ${formatMoney(subtotal)}</div>
                <div><strong>Labor cost:</strong> ${formatMoney(laborCost)}</div>
                <div><strong>Base total:</strong> ${formatMoney(baseTotal)}</div>
                <div><strong>Applied promo:</strong> ${escapeHtml(formatAppliedPromoLabel(appliedPromo))}</div>
                <div><strong>Promo discount preview:</strong> ${formatMoney(promoDiscount)}</div>
                <div><strong>Admin discount:</strong> ${formatMoney(adminDiscount)}</div>
                <div><strong>Projected final total:</strong> ${formatMoney(finalTotal)}</div>
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
                throw new Error(responseBody.message || 'Could not load item management data.');
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
                builderLockedMessage.textContent = 'Only inspection-based quotations can be edited in the item builder. This quotation is not editable here.';
                return;
            }

            setVisible(builderLocked, false);

            lineItemsState = (quotationState.line_items || []).map((item) => normalizeLineItem(item));

	            renderSummary();
                renderDiscountNegotiation();
	            renderLineItems();
	            renderTotals();
	            setVisible(builderContent, true);
	        }

        async function submitDiscountReview(action) {
            if (!quotationState || quotationState.quotation_type !== 'final') {
                showError('Load an inspection-based quotation before reviewing a discount request.');
                return;
            }

            clearMessages();

            const button = action === 'apply' ? applyDiscountButton : rejectDiscountButton;
            const originalLabel = button ? button.textContent : '';

            if (button) {
                button.disabled = true;
                button.textContent = action === 'apply' ? 'Applying...' : 'Rejecting...';
            }

            try {
                await ensureCsrfCookie();

                const payload = {
                    admin_discount_reason: adminDiscountReasonInput?.value?.trim() || null,
                };

                if (action === 'apply') {
                    payload.admin_discount_amount = Number(adminDiscountAmountInput?.value || 0);
                }

                const endpoint = action === 'apply'
                    ? `/api/admin/quotations/${quotationState.id}/discount`
                    : `/api/admin/quotations/${quotationState.id}/discount/reject`;

                const response = await fetch(endpoint, {
                    method: 'PATCH',
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
                    Object.entries(responseBody.errors || {}).forEach(([field, messages]) => {
                        const errorElement = document.querySelector(`[data-discount-error-for="${field}"]`);
                        if (errorElement) {
                            errorElement.textContent = messages[0];
                        }
                    });

                    throw new Error(responseBody.message || 'Please review the discount fields.');
                }

                if (!response.ok) {
                    throw new Error(responseBody.message || 'Could not review discount request.');
                }

                quotationState = responseBody.data;
                lineItemsState = (quotationState.line_items || []).map((item) => normalizeLineItem(item));

                renderSummary();
                renderDiscountNegotiation();
                renderLineItems();
                renderTotals();

                builderSuccess.textContent = responseBody.message || 'Discount review saved successfully.';
                setVisible(builderSuccess, true);
            } catch (error) {
                showError(error.message || 'Could not review discount request.');
            } finally {
                if (button) {
                    button.disabled = false;
                    button.textContent = originalLabel;
                }
            }
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
                showError('Only inspection-based quotations can be saved in the item builder.');
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
                    renderDiscountNegotiation();
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

        if (availableQuotationSelect) {
            availableQuotationSelect.addEventListener('change', (event) => {
                applyQuotationId(event.target.value);
            });
        }

        quotationFillButtons.forEach((button) => {
            button.addEventListener('click', () => {
                applyQuotationId(button.dataset.quotationFill);
                quotationIdInput.focus();
            });
        });

        if (showAvailableQuotationsButton) {
            showAvailableQuotationsButton.addEventListener('click', () => {
                resetBuilderView();
                setAvailableQuotationTableVisible(true);
                quotationIdInput.value = '';

                if (availableQuotationSelect) {
                    availableQuotationSelect.value = '';
                }

                if (availableQuotationSelect) {
                    availableQuotationSelect.focus();
                } else {
                    quotationIdInput.focus();
                }
            });
        }

        addLineItemButton.addEventListener('click', () => {
            clearMessages();
            addLineItem();
        });

        saveLineItemsButton.addEventListener('click', saveLineItems);

        if (applyDiscountButton) {
            applyDiscountButton.addEventListener('click', () => submitDiscountReview('apply'));
        }

        if (rejectDiscountButton) {
            rejectDiscountButton.addEventListener('click', () => submitDiscountReview('reject'));
        }

        if (initialQuotationId) {
            applyQuotationId(initialQuotationId);
            loadBuilder(initialQuotationId);
        }
    </script>
@endpush
