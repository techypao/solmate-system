@extends('layouts.app', ['title' => 'Manage Promotions'])

@section('content')
    <div class="admin-page-stack">
    <div class="card admin-hero-card">
        <p class="admin-page-eyebrow">Content Management</p>
        <h1 class="page-title">Homepage Promotions</h1>
        <p class="page-copy">Create and manage promotional banners that appear on the public homepage. Set start/end dates to schedule promotions automatically — expired promos are hidden without any manual action needed.</p>
    </div>

    <style>
        .promo-admin-panel {
            padding: 20px;
            background: linear-gradient(180deg, #ffffff 0%, #F8FAFC 100%);
            border: 1px solid #DDE7EE;
            border-radius: 18px;
            box-shadow: 0 8px 22px rgba(15, 23, 42, 0.04);
        }

        .promo-admin-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 16px;
        }

        .promo-admin-card {
            display: flex;
            flex-direction: column;
            border: 1px solid #DDE7EE;
            border-radius: 18px;
            overflow: hidden;
            background: #ffffff;
            box-shadow: 0 8px 18px rgba(15, 23, 42, 0.05);
        }

        .promo-admin-thumb {
            aspect-ratio: 16 / 7;
            background: linear-gradient(135deg, #123A5A 0%, #1f4d76 56%, #20A7C9 100%);
            overflow: hidden;
            position: relative;
        }

        .promo-admin-thumb img {
            width: 100%;
            height: 100%;
            display: block;
            object-fit: cover;
        }

        .promo-admin-thumb-placeholder {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            color: rgba(255,255,255,0.7);
            font-size: 13px;
            text-align: center;
            line-height: 1.6;
        }

        .promo-admin-card-body {
            display: flex;
            flex-direction: column;
            gap: 14px;
            padding: 18px;
            flex: 1;
        }

        .promo-admin-card-title {
            margin: 0;
            color: #123A5A;
            font-size: 17px;
            font-weight: 700;
            line-height: 1.35;
        }

        .promo-admin-card-desc {
            margin: 0;
            color: #5E7288;
            font-size: 13px;
            line-height: 1.7;
        }

        .promo-admin-meta {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .promo-admin-meta-item {
            padding: 12px 14px;
            border: 1px solid #DDE7EE;
            border-radius: 14px;
            background: #F8FAFC;
        }

        .promo-admin-meta-item.full-width {
            grid-column: 1 / -1;
        }

        .promo-admin-meta-label {
            display: block;
            margin-bottom: 4px;
            color: #7F92A3;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .promo-admin-meta-value {
            display: block;
            color: #123A5A;
            font-size: 13px;
            font-weight: 600;
            line-height: 1.6;
            word-break: break-word;
        }

        .promo-admin-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: auto;
        }

        .promo-admin-actions button {
            min-width: 110px;
        }

        .promo-expired-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
            background: #FEE2E2;
            color: #B91C1C;
            border: 1px solid rgba(185,28,28,0.2);
        }

        .promo-modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.55);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .promo-modal-overlay.open {
            display: flex;
        }

        .promo-modal {
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 24px 60px rgba(15, 23, 42, 0.18);
            width: 100%;
            max-width: 560px;
            max-height: 90vh;
            overflow-y: auto;
            padding: 28px;
        }

        .promo-modal-title {
            font-size: 20px;
            font-weight: 800;
            color: #123A5A;
            margin: 0 0 20px;
        }

        .promo-image-preview {
            border: 1px dashed #DDE7EE;
            border-radius: 14px;
            overflow: hidden;
            background: #F8FAFC;
            margin-top: 8px;
        }

        .promo-image-preview img {
            width: 100%;
            aspect-ratio: 16 / 7;
            object-fit: cover;
            display: block;
        }

        .promo-image-preview-empty {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 90px;
            padding: 16px;
            text-align: center;
            color: #5E7288;
            font-size: 13px;
        }

        .promo-free-item-mode-list {
            display: grid;
            gap: 10px;
        }

        .promo-free-item-mode-card {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            width: 100%;
            min-height: 0;
            padding: 14px 16px;
            border: 1.5px solid #DDE7EE;
            border-radius: 12px;
            background: #ffffff;
            cursor: pointer;
            transition: border-color 0.16s ease, background 0.16s ease, box-shadow 0.16s ease;
        }

        .promo-free-item-mode-card:hover {
            border-color: rgba(32, 167, 201, 0.44);
            background: #f9fdff;
        }

        .promo-free-item-mode-card.is-selected {
            border-color: #20A7C9;
            background: #F0F9FF;
            box-shadow: 0 0 0 1px rgba(32, 167, 201, 0.06);
        }

        .promo-free-item-mode-radio {
            width: 18px;
            height: 18px;
            min-width: 18px;
            margin: 2px 0 0;
            padding: 0;
            border: 0;
            border-radius: 50%;
            background: transparent;
            box-shadow: none;
            accent-color: #20A7C9;
            flex-shrink: 0;
            pointer-events: none;
        }

        .solmate-admin-shell .promo-free-item-mode-radio,
        .solmate-admin-shell .promo-free-item-mode-radio:hover,
        .solmate-admin-shell .promo-free-item-mode-radio:focus {
            width: 18px;
            height: 18px;
            padding: 0;
            border: 0;
            border-radius: 50%;
            background: transparent;
            box-shadow: none;
        }

        .promo-free-item-mode-copy {
            min-width: 0;
            flex: 1;
        }

        .promo-free-item-mode-title {
            margin: 0 0 4px;
            color: #0F2F4A;
            font-size: 14px;
            font-weight: 700;
            line-height: 1.35;
        }

        .promo-free-item-mode-description {
            margin: 0;
            color: #5E7288;
            font-size: 12px;
            line-height: 1.55;
        }

        .promo-conditions-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
        }

        @media (max-width: 720px) {
            .promo-free-item-mode-card {
                padding: 12px 14px;
                gap: 10px;
            }

            .promo-conditions-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="card admin-section-surface">
        <div id="promo-loading" class="info-box">Loading promotions...</div>
        <div id="promo-success" class="status" style="display:none;"></div>
        <div id="promo-error" class="error-box" style="display:none;"></div>

        <div id="promo-content" class="stack" style="display:none;">

            {{-- Add / Edit Form Panel --}}
            <div class="promo-admin-panel">
                <div class="actions" style="justify-content: space-between;">
                    <div>
                        <h2 id="promo-form-heading" class="admin-section-title" style="margin: 0 0 6px;">Add Promotion</h2>
                        <div class="muted">Fill in the details below. Only active, in-date promotions appear on the homepage.</div>
                    </div>
                </div>

                <form id="promo-form" class="form-grid two-columns" style="margin-top: 18px;" enctype="multipart/form-data">
                    <input type="hidden" id="promo_edit_id" value="">

                    <div style="grid-column: 1 / -1;">
                        <label for="promo_title">Promo Title <span style="color:#e53e3e;">*</span></label>
                        <input id="promo_title" name="title" type="text" placeholder="e.g. Buy 5 Solar Panels, Get 1 Free">
                        <div class="field-error" data-promo-error-for="title"></div>
                    </div>

                    <div style="grid-column: 1 / -1;">
                        <label for="promo_description">Short Description</label>
                        <textarea id="promo_description" name="description" rows="3" placeholder="Brief description shown on the promo card..."></textarea>
                        <div class="field-error" data-promo-error-for="description"></div>
                    </div>

                    {{-- Quotation discount fields --}}
                    <div style="grid-column: 1 / -1;">
                        <label for="promo_type">Discount Type</label>
                        <select id="promo_type" name="promo_type">
                            <option value="">— No automatic discount —</option>
                            <option value="percentage">Percentage off</option>
                            <option value="fixed_amount">Fixed peso amount off</option>
                            <option value="free_item">Free item / accessory</option>
                            <option value="bundle">Bundle deal</option>
                        </select>
                        <small class="muted" id="promo-type-hint" style="display:block; margin-top:4px;">This determines how the discount is calculated when a technician applies this promo to a quotation. Leave as "No automatic discount" if this promo is for display purposes only.</small>
                        <div class="field-error" data-promo-error-for="promo_type"></div>
                    </div>

                    {{-- Free item: discount method toggle (only shown for free_item type) --}}
                    <div id="promo-free-item-mode-wrap" style="grid-column: 1 / -1; display:none;">
                        <label style="margin-bottom:8px;">How should the discount be computed?</label>
                        <div class="promo-free-item-mode-list">
                            <div id="free-item-mode-condition-row"
                                 class="promo-free-item-mode-card">
                                <input type="radio" id="free_item_mode_condition" name="free_item_mode" value="condition" class="promo-free-item-mode-radio">
                                <div class="promo-free-item-mode-copy">
                                    <p class="promo-free-item-mode-title">Item quantity rule</p>
                                    <p class="promo-free-item-mode-description">Auto-deduct the catalog unit price when a minimum quantity is reached, like buy 5 panels and get 1 free. No manual peso amount needed.</p>
                                </div>
                            </div>
                            <div id="free-item-mode-fixed-row"
                                 class="promo-free-item-mode-card">
                                <input type="radio" id="free_item_mode_fixed" name="free_item_mode" value="fixed" class="promo-free-item-mode-radio">
                                <div class="promo-free-item-mode-copy">
                                    <p class="promo-free-item-mode-title">Fixed peso amount</p>
                                    <p class="promo-free-item-mode-description">Enter a specific peso value to deduct, regardless of which items are selected in the quotation.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Condition fields (free_item condition mode) --}}
                    <div id="promo-conditions-wrap" style="grid-column: 1 / -1; display:none;">
                        <div class="promo-conditions-grid">
                            <div>
                                <label for="promo_conditions_applies_to">Item category</label>
                                <select id="promo_conditions_applies_to" name="conditions[applies_to]">
                                    <option value="">&mdash; Select an item &mdash;</option>
                                    <option value="panel">Solar Panel</option>
                                    <option value="inverter">Inverter</option>
                                    <option value="battery">Battery</option>
                                    <option value="protection">Protection device</option>
                                    <option value="mounting">Mounting hardware</option>
                                    <option value="wiring">Wiring</option>
                                    <option value="grounding">Grounding</option>
                                    <option value="misc">Miscellaneous</option>
                                </select>
                                <small class="muted" style="display:block; margin-top:4px;">Which catalog item this rule applies to.</small>
                            </div>
                            <div id="promo-conditions-qty-wrap">
                                <label for="promo_conditions_min_qty">Min. quantity required</label>
                                <input id="promo_conditions_min_qty" name="conditions[min_qty]" type="number" min="1" step="1" placeholder="e.g. 5">
                                <small class="muted" style="display:block; margin-top:4px;">Promo activates only when at least this many items are in the quotation.</small>
                            </div>
                            <div id="promo-conditions-free-qty-wrap">
                                <label for="promo_conditions_free_qty">Free item count</label>
                                <input id="promo_conditions_free_qty" name="conditions[free_qty]" type="number" min="1" step="1" placeholder="e.g. 1">
                                <small class="muted" style="display:block; margin-top:4px;">Their catalog unit price will be auto-deducted.</small>
                            </div>
                        </div>
                    </div>

                    {{-- Fixed discount value (shown for percentage, fixed_amount, bundle, and free_item fixed mode) --}}
                    <div id="promo-discount-value-wrap" style="display:none;">
                        <label for="promo_discount_value" id="promo-discount-value-label">Discount Value</label>
                        <input id="promo_discount_value" name="discount_value" type="number" min="0" step="0.01" placeholder="">
                        <small class="muted" id="promo-discount-value-hint" style="display:block; margin-top:4px;"></small>
                        <div class="field-error" data-promo-error-for="discount_value"></div>
                    </div>

                    <div id="promo-free-item-wrap" style="grid-column: 1 / -1; display:none;">
                        <label for="promo_free_item_description">Included free / bundle items</label>
                        <input id="promo_free_item_description" name="free_item_description" type="text" placeholder="e.g. Free inverter upgrade, 1 extra solar panel, complimentary monitoring device">
                        <small class="muted" style="display:block; margin-top:4px;">Shown to the technician when they select this promo during quotation.</small>
                        <div class="field-error" data-promo-error-for="free_item_description"></div>
                    </div>
                    <div style="grid-column: 1 / -1;">
                        <label for="promo_image">Banner / Image</label>
                        <input id="promo_image" name="image" type="file" accept="image/jpg,image/jpeg,image/png,image/webp">
                        <div class="field-error" data-promo-error-for="image"></div>
                        <div id="promo-image-preview" class="promo-image-preview" style="display:none;">
                            <img id="promo-image-preview-img" src="" alt="Banner preview">
                        </div>
                    </div>

                    <div>
                        <label for="promo_start_date">Start Date</label>
                        <input id="promo_start_date" name="start_date" type="date">
                        <div class="field-error" data-promo-error-for="start_date"></div>
                    </div>

                    <div>
                        <label for="promo_end_date">End Date</label>
                        <input id="promo_end_date" name="end_date" type="date">
                        <div class="field-error" data-promo-error-for="end_date"></div>
                    </div>

                    <div style="display:flex; align-items:center; gap:10px; grid-column: 1 / -1;">
                        <input id="promo_is_active" name="is_active" type="checkbox" checked style="width:18px; height:18px; flex-shrink:0;">
                        <label for="promo_is_active" style="margin:0; cursor:pointer;">Active (show on homepage)</label>
                    </div>

                    <div class="actions" style="grid-column: 1 / -1; gap: 10px;">
                        <button id="promo-submit" type="submit">Save Promotion</button>
                        <button id="promo-cancel-edit" type="button" class="secondary" style="display:none;">Cancel Edit</button>
                    </div>
                </form>
            </div>

            {{-- Existing Promotions --}}
            <div class="promo-admin-panel">
                <div class="actions" style="justify-content: space-between;">
                    <div>
                        <h2 class="admin-section-title" style="margin: 0 0 6px;">Existing Promotions</h2>
                        <div class="muted">Toggle visibility, edit details, or delete promotions.</div>
                    </div>
                    <button id="promo-refresh" type="button" class="secondary">Refresh</button>
                </div>

                <div id="promo-empty" class="info-box" style="display:none; margin-top: 16px;">No promotions created yet.</div>
                <div id="promo-list" class="promo-admin-grid" style="display:none; margin-top: 16px;"></div>
            </div>

        </div>
    </div>
    </div>
@endsection

@push('scripts')
<script>
(() => {
    const loadingBox = document.getElementById('promo-loading');
    const successBox = document.getElementById('promo-success');
    const errorBox   = document.getElementById('promo-error');
    const content    = document.getElementById('promo-content');
    const form       = document.getElementById('promo-form');
    const submitBtn  = document.getElementById('promo-submit');
    const cancelBtn  = document.getElementById('promo-cancel-edit');
    const refreshBtn = document.getElementById('promo-refresh');
    const emptyState = document.getElementById('promo-empty');
    const list       = document.getElementById('promo-list');
    const formHeading = document.getElementById('promo-form-heading');
    const editIdInput = document.getElementById('promo_edit_id');
    const imageInput  = document.getElementById('promo_image');
    const imagePreview    = document.getElementById('promo-image-preview');
    const imagePreviewImg = document.getElementById('promo-image-preview-img');

    let promotions = [];

    /* ── helpers ─────────────────────────────────────────────── */

    function setVisible(el, visible, displayValue = 'block') {
        el.style.display = visible ? displayValue : 'none';
    }

    function escapeHtml(value) {
        return String(value == null ? '' : value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function formatDate(value) {
        if (!value) return '—';
        const d = new Date(value);
        return Number.isNaN(d.getTime()) ? value : d.toLocaleDateString();
    }

    function isExpired(promo) {
        if (!promo.end_date) return false;
        return new Date(promo.end_date) < new Date(new Date().toDateString());
    }

    function getCookie(name) {
        const prefix = `${name}=`;
        for (const part of document.cookie.split(';')) {
            const trimmed = part.trim();
            if (trimmed.startsWith(prefix)) {
                return decodeURIComponent(trimmed.slice(prefix.length));
            }
        }
        return null;
    }

    async function ensureCsrf() {
        await fetch('/sanctum/csrf-cookie', {
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });
    }

    async function apiRequest(endpoint, options = {}) {
        const method = (options.method || 'GET').toUpperCase();
        const isMultipart = options.isMultipart === true;
        const headers = {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        };

        if (method !== 'GET') {
            await ensureCsrf();
            if (!isMultipart) {
                headers['Content-Type'] = 'application/json';
            }
            headers['X-XSRF-TOKEN'] = getCookie('XSRF-TOKEN') || '';
        }

        const response = await fetch(endpoint, {
            method,
            credentials: 'same-origin',
            headers,
            body: options.body || undefined,
        });

        const payload = await response.json().catch(() => ({}));

        if (!response.ok) {
            const err = new Error(payload.message || 'Request failed.');
            err.status = response.status;
            err.payload = payload;
            throw err;
        }

        return payload;
    }

    function clearFeedback() {
        successBox.textContent = '';
        errorBox.textContent = '';
        setVisible(successBox, false);
        setVisible(errorBox, false);
        document.querySelectorAll('[data-promo-error-for]').forEach((el) => {
            el.textContent = '';
        });
    }

    function showError(msg) { errorBox.textContent = msg; setVisible(errorBox, true); }
    function showSuccess(msg) { successBox.textContent = msg; setVisible(successBox, true); }

    /* ── promo type UI ───────────────────────────────────────── */

    function syncFreeItemMode(mode) {
        const discountWrap = document.getElementById('promo-discount-value-wrap');
        const conditionsWrap = document.getElementById('promo-conditions-wrap');
        const rowCondition = document.getElementById('free-item-mode-condition-row');
        const rowFixed = document.getElementById('free-item-mode-fixed-row');
        const radioCondition = document.getElementById('free_item_mode_condition');
        const radioFixed = document.getElementById('free_item_mode_fixed');

        if (radioCondition) radioCondition.checked = mode === 'condition';
        if (radioFixed) radioFixed.checked = mode === 'fixed';

        setVisible(conditionsWrap, mode === 'condition');
        setVisible(discountWrap, mode === 'fixed');

        rowCondition?.classList.toggle('is-selected', mode === 'condition');
        rowFixed?.classList.toggle('is-selected', mode === 'fixed');

        if (mode === 'fixed') {
            // Clear condition fields so they don't get submitted
            document.getElementById('promo_conditions_applies_to').value = '';
            document.getElementById('promo_conditions_min_qty').value = '';
            document.getElementById('promo_conditions_free_qty').value = '';
            const label = document.getElementById('promo-discount-value-label');
            const input = document.getElementById('promo_discount_value');
            const hint  = document.getElementById('promo-discount-value-hint');
            if (label) label.textContent = 'Free item value (₱) — optional';
            if (input) input.placeholder = 'e.g. 2500  →  ₱2,500 deducted from total (leave blank if display only)';
            if (hint)  hint.textContent  = 'Optional. Enter a peso amount to deduct. Leave blank if this promo is purely informational.';
        } else {
            // Clear discount_value so it doesn’t get submitted
            document.getElementById('promo_discount_value').value = '';
        }
    }

    function syncConditionsUI(appliesTo) {
        // qty/free-qty fields are always visible in condition mode now — nothing to toggle
    }

    function syncPromoTypeUI(promoType) {
        const discountValueWrap  = document.getElementById('promo-discount-value-wrap');
        const freeItemWrap       = document.getElementById('promo-free-item-wrap');
        const freeItemModeWrap   = document.getElementById('promo-free-item-mode-wrap');
        const conditionsWrap     = document.getElementById('promo-conditions-wrap');
        const rowCondition       = document.getElementById('free-item-mode-condition-row');
        const rowFixed           = document.getElementById('free-item-mode-fixed-row');
        const label              = document.getElementById('promo-discount-value-label');
        const input              = document.getElementById('promo_discount_value');
        const hint               = document.getElementById('promo-discount-value-hint');

        const showFreeItem = promoType === 'free_item' || promoType === 'bundle';
        setVisible(freeItemWrap, showFreeItem);
        setVisible(freeItemModeWrap, promoType === 'free_item');

        if (promoType === 'percentage') {
            setVisible(discountValueWrap, true);
            setVisible(conditionsWrap, false);
            if (label) label.textContent = 'Percentage off (%)';
            if (input) input.placeholder = 'e.g. 10  →  10% off total';
            if (hint)  hint.textContent  = 'Enter a number from 1–100. Example: 15 gives 15% off the project cost.';
        } else if (promoType === 'fixed_amount') {
            setVisible(discountValueWrap, true);
            setVisible(conditionsWrap, false);
            if (label) label.textContent = 'Peso amount off (₱)';
            if (input) input.placeholder = 'e.g. 5000  →  ₱5,000 off total';
            if (hint)  hint.textContent  = 'Enter the exact peso amount to deduct. Example: 5000 deducts ₱5,000 from the project cost.';
        } else if (promoType === 'bundle') {
            setVisible(discountValueWrap, true);
            setVisible(conditionsWrap, false);
            if (label) label.textContent = 'Bundle savings (₱)';
            if (input) input.placeholder = 'e.g. 3000  →  ₱3,000 off for this bundle';
            if (hint)  hint.textContent  = 'Enter the fixed peso discount for this bundle deal.';
        } else if (promoType === 'free_item') {
            // Determine which mode to activate based on existing data.
            // Default to 'condition' for new promos; use 'fixed' only when a
            // discount_value is already set and no condition applies_to is set.
            const hasCondition   = !!document.getElementById('promo_conditions_applies_to').value;
            const hasFixedAmount = !!document.getElementById('promo_discount_value').value;
            const checkedRadio   = document.querySelector('input[name="free_item_mode"]:checked');
            const mode = checkedRadio?.value ?? (hasCondition || !hasFixedAmount ? 'condition' : 'fixed');
            document.querySelector(`input[name="free_item_mode"][value="${mode}"]`).checked = true;
            syncFreeItemMode(mode);
        } else {
            setVisible(discountValueWrap, false);
            setVisible(conditionsWrap, false);
            rowCondition?.classList.remove('is-selected');
            rowFixed?.classList.remove('is-selected');
            if (label) label.textContent = 'Discount Value';
            if (input) input.placeholder = '';
            if (hint)  hint.textContent  = '';
        }
    }

    document.getElementById('promo_type').addEventListener('change', (e) => {
        syncPromoTypeUI(e.target.value);
    });

    document.getElementById('free-item-mode-condition-row')?.addEventListener('click', () => {
        syncFreeItemMode('condition');
    });

    document.getElementById('free-item-mode-fixed-row')?.addEventListener('click', () => {
        syncFreeItemMode('fixed');
    });

    document.querySelectorAll('input[name="free_item_mode"]').forEach((radio) => {
        radio.addEventListener('change', (e) => {
            if (e.target.checked) {
                syncFreeItemMode(e.target.value);
            }
        });
    });

    document.getElementById('promo_conditions_applies_to').addEventListener('change', (e) => {
        syncConditionsUI(e.target.value);
    });

    // Initialise on page load
    syncPromoTypeUI(document.getElementById('promo_type').value);

    /* ── image preview ───────────────────────────────────────── */

    imageInput.addEventListener('change', () => {
        const file = imageInput.files[0];
        if (file) {
            const url = URL.createObjectURL(file);
            imagePreviewImg.src = url;
            setVisible(imagePreview, true);
        } else {
            setVisible(imagePreview, false);
        }
    });

    /* ── rendering ───────────────────────────────────────────── */

    function renderList() {
        if (!Array.isArray(promotions) || promotions.length === 0) {
            list.innerHTML = '';
            setVisible(list, false);
            setVisible(emptyState, true);
            return;
        }

        const promoTypeLabels = {
            percentage: 'Percentage',
            fixed_amount: 'Fixed Amount',
            free_item: 'Free Item',
            bundle: 'Bundle Deal',
        };

        list.innerHTML = promotions.map((promo) => {
            const expired = isExpired(promo);
            const statusClass = promo.is_active ? 'badge badge-success' : 'badge badge-warning';
            const statusLabel = promo.is_active ? 'Active' : 'Inactive';
            const mediaHtml = promo.image_url
                ? `<img src="${escapeHtml(promo.image_url)}" alt="${escapeHtml(promo.title)} banner">`
                : `<div class="promo-admin-thumb-placeholder">No banner uploaded yet.</div>`;

            let discountHtml = '';
            if (promo.promo_type) {
                const typeLabel = promoTypeLabels[promo.promo_type] || promo.promo_type;
                let valueText = '';
                if (promo.promo_type === 'percentage' && promo.discount_value != null) {
                    valueText = ` — ${escapeHtml(String(promo.discount_value))}% off`;
                } else if ((promo.promo_type === 'fixed_amount' || promo.promo_type === 'bundle') && promo.discount_value != null) {
                    valueText = ` — ₱${escapeHtml(Number(promo.discount_value).toLocaleString())} off`;
                } else if (promo.promo_type === 'free_item' && promo.free_item_description) {
                    valueText = `: ${escapeHtml(promo.free_item_description)}`;
                }
                discountHtml = `<div class="promo-admin-meta-item">
                    <span class="promo-admin-meta-label">Discount</span>
                    <span class="promo-admin-meta-value">${escapeHtml(typeLabel)}${valueText}</span>
                </div>`;
            }

            return `
                <article class="promo-admin-card">
                    <div class="promo-admin-thumb">${mediaHtml}</div>
                    <div class="promo-admin-card-body">
                        <div class="actions" style="justify-content: space-between; align-items: flex-start; margin: 0; gap: 8px; flex-wrap: wrap;">
                            <span class="${statusClass}">${statusLabel}</span>
                            ${expired ? '<span class="promo-expired-badge">Expired</span>' : ''}
                        </div>

                        <h3 class="promo-admin-card-title">${escapeHtml(promo.title)}</h3>
                        ${promo.description ? `<p class="promo-admin-card-desc">${escapeHtml(promo.description)}</p>` : ''}

                        <div class="promo-admin-meta">
                            <div class="promo-admin-meta-item">
                                <span class="promo-admin-meta-label">Start Date</span>
                                <span class="promo-admin-meta-value">${escapeHtml(formatDate(promo.start_date))}</span>
                            </div>
                            <div class="promo-admin-meta-item">
                                <span class="promo-admin-meta-label">End Date</span>
                                <span class="promo-admin-meta-value">${escapeHtml(formatDate(promo.end_date))}</span>
                            </div>
                            ${discountHtml}
                        </div>

                        <div class="promo-admin-actions">
                            <button type="button" data-promo-action="toggle" data-promo-id="${promo.id}">${promo.is_active ? 'Set Inactive' : 'Set Active'}</button>
                            <button type="button" class="secondary" data-promo-action="edit" data-promo-id="${promo.id}">Edit</button>
                            <button type="button" class="danger" data-promo-action="delete" data-promo-id="${promo.id}">Delete</button>
                        </div>
                    </div>
                </article>
            `;
        }).join('');

        setVisible(emptyState, false);
        setVisible(list, true, 'grid');
    }

    /* ── load ────────────────────────────────────────────────── */

    async function loadPromotions() {
        clearFeedback();
        setVisible(loadingBox, true);
        setVisible(content, false);

        try {
            const payload = await apiRequest('/api/admin/promotions');
            promotions = Array.isArray(payload.data) ? payload.data : [];
            renderList();
            setVisible(content, true);
        } catch (error) {
            showError(error.message || 'Could not load promotions.');
        } finally {
            setVisible(loadingBox, false);
        }
    }

    /* ── reset form ──────────────────────────────────────────── */

    function resetForm() {
        form.reset();
        editIdInput.value = '';
        document.getElementById('promo_is_active').checked = true;
        setVisible(imagePreview, false);
        formHeading.textContent = 'Add Promotion';
        submitBtn.textContent = 'Save Promotion';
        setVisible(cancelBtn, false);
        syncPromoTypeUI('');
    }

    /* ── populate form for editing ───────────────────────────── */

    function populateForm(promo) {
        editIdInput.value = promo.id;
        document.getElementById('promo_title').value = promo.title ?? '';
        document.getElementById('promo_description').value = promo.description ?? '';
        document.getElementById('promo_start_date').value = promo.start_date ? promo.start_date.slice(0, 10) : '';
        document.getElementById('promo_end_date').value = promo.end_date ? promo.end_date.slice(0, 10) : '';
        document.getElementById('promo_is_active').checked = !!promo.is_active;
        document.getElementById('promo_type').value = promo.promo_type ?? '';
        document.getElementById('promo_discount_value').value = promo.discount_value != null ? promo.discount_value : '';
        document.getElementById('promo_free_item_description').value = promo.free_item_description ?? '';
        document.getElementById('promo_conditions_applies_to').value = promo.conditions?.applies_to ?? '';
        document.getElementById('promo_conditions_min_qty').value = promo.conditions?.min_qty != null ? promo.conditions.min_qty : '';
        document.getElementById('promo_conditions_free_qty').value = promo.conditions?.free_qty != null ? promo.conditions.free_qty : '';

        // Pre-select the correct radio mode for free_item promos
        if (promo.promo_type === 'free_item') {
            const mode = promo.conditions?.applies_to ? 'condition' : 'fixed';
            const radio = document.querySelector(`input[name="free_item_mode"][value="${mode}"]`);
            if (radio) radio.checked = true;
        }

        syncPromoTypeUI(promo.promo_type ?? '');

        if (promo.image_url) {
            imagePreviewImg.src = promo.image_url;
            setVisible(imagePreview, true);
        } else {
            setVisible(imagePreview, false);
        }

        formHeading.textContent = 'Edit Promotion';
        submitBtn.textContent = 'Update Promotion';
        setVisible(cancelBtn, true);

        form.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    /* ── submit ──────────────────────────────────────────────── */

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        clearFeedback();

        const editId = editIdInput.value;
        const isEdit = !!editId;
        const endpoint = isEdit ? `/api/admin/promotions/${editId}` : '/api/admin/promotions';

        submitBtn.disabled = true;
        submitBtn.textContent = isEdit ? 'Updating...' : 'Saving...';

        try {
            const formData = new FormData(form);
            // FormData checkbox: only present when checked
            formData.set('is_active', document.getElementById('promo_is_active').checked ? '1' : '0');

            const payload = await apiRequest(endpoint, {
                method: 'POST',
                isMultipart: true,
                body: formData,
            });

            resetForm();
            showSuccess(payload.message || 'Promotion saved successfully.');
            await loadPromotions();
        } catch (error) {
            if (error.status === 422 && error.payload?.errors) {
                Object.entries(error.payload.errors).forEach(([field, messages]) => {
                    const target = document.querySelector(`[data-promo-error-for="${field}"]`);
                    if (target && Array.isArray(messages) && messages[0]) {
                        target.textContent = messages[0];
                    }
                });
            }
            showError(error.message || 'Could not save the promotion.');
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = isEdit ? 'Update Promotion' : 'Save Promotion';
        }
    });

    /* ── cancel edit ─────────────────────────────────────────── */

    cancelBtn.addEventListener('click', resetForm);

    /* ── list actions ────────────────────────────────────────── */

    async function togglePromo(id) {
        clearFeedback();
        try {
            const payload = await apiRequest(`/api/admin/promotions/${id}/toggle`, { method: 'PATCH' });
            showSuccess(payload.message || 'Promotion updated.');
            await loadPromotions();
        } catch (error) {
            showError(error.message || 'Could not toggle the promotion.');
        }
    }

    function editPromo(id) {
        const promo = promotions.find((p) => p.id === id);
        if (promo) populateForm(promo);
    }

    async function deletePromo(id) {
        if (!window.confirm('Delete this promotion? This cannot be undone.')) return;
        clearFeedback();
        try {
            const payload = await apiRequest(`/api/admin/promotions/${id}`, { method: 'DELETE' });
            showSuccess(payload.message || 'Promotion deleted.');
            if (editIdInput.value === String(id)) resetForm();
            await loadPromotions();
        } catch (error) {
            showError(error.message || 'Could not delete the promotion.');
        }
    }

    refreshBtn.addEventListener('click', loadPromotions);

    list.addEventListener('click', (event) => {
        const btn = event.target.closest('[data-promo-action]');
        if (!btn) return;

        const id = Number(btn.dataset.promoId);
        if (!Number.isFinite(id) || id <= 0) return;

        if (btn.dataset.promoAction === 'toggle') { togglePromo(id); return; }
        if (btn.dataset.promoAction === 'edit')   { editPromo(id);   return; }
        if (btn.dataset.promoAction === 'delete') { deletePromo(id); }
    });

    loadPromotions();
})();
</script>
@endpush
