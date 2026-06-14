@extends('layouts.app', ['title' => 'My Quotations'])

@section('content')
<style>
    .cql-hero {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 20px;
        flex-wrap: wrap;
        margin-bottom: 26px;
        padding: 34px 38px;
        border-radius: 22px;
        background:
            radial-gradient(circle at right top, rgba(212, 160, 23, 0.18), transparent 34%),
            linear-gradient(135deg, #EAF9FD 0%, #f8fafc 55%, #fefce8 100%);
        border: 1px solid #DDE7EE;
    }
    .cql-eyebrow {
        margin: 0 0 10px;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 1.2px;
        text-transform: uppercase;
        color: #F4D000;
    }
    .cql-title {
        margin: 0 0 10px;
        font-size: 32px;
        font-weight: 800;
        color: #123A5A;
        line-height: 1.15;
    }
    .cql-sub {
        max-width: 650px;
        margin: 0;
        color: #5E7288;
        font-size: 15px;
        line-height: 1.7;
    }
    .cql-actions {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }
    .cql-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-height: 46px;
        padding: 0 18px;
        border-radius: 12px;
        font-size: 14px;
        font-weight: 700;
        text-decoration: none;
        transition: transform .15s ease, opacity .15s ease, box-shadow .15s ease;
    }
    .cql-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.12);
    }
    .cql-btn-primary {
        background: linear-gradient(135deg, #F4D000, #E6C200);
        color: #fff;
    }
    .cql-btn-secondary {
        background: #123A5A;
        color: #fff;
    }

    .cql-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
        margin-bottom: 18px;
    }
    .cql-toolbar-copy {
        color: #5E7288;
        font-size: 14px;
    }
    .cql-filters {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }
    .cql-chip {
        min-height: 36px;
        padding: 0 14px;
        border: 1px solid #DDE7EE;
        border-radius: 999px;
        background: #fff;
        color: #5E7288;
        font-size: 12px;
        font-weight: 700;
        cursor: pointer;
        transition: all .15s ease;
    }
    .cql-chip.active,
    .cql-chip:hover {
        background: #123A5A;
        border-color: #123A5A;
        color: #fff;
    }

    .cql-msg {
        display: none;
        margin-bottom: 16px;
        padding: 12px 14px;
        border-radius: 12px;
        font-size: 14px;
        line-height: 1.5;
    }
    .cql-msg.show {
        display: block;
    }
    .cql-msg-error {
        background: #fee2e2;
        border: 1px solid #fecaca;
        color: #991b1b;
    }
    .cql-msg-success {
        background: #dcfce7;
        border: 1px solid #bbf7d0;
        color: #166534;
    }
    .cql-loading {
        display: none;
        padding: 30px 18px;
        text-align: center;
        color: #5E7288;
        font-size: 14px;
    }
    .cql-loading.show {
        display: block;
    }
    .cql-empty {
        display: none;
        align-items: center;
        justify-content: center;
        min-height: 240px;
        padding: 32px 22px;
        border-radius: 22px;
        border: 1px dashed #DDE7EE;
        background: #fff;
        color: #5E7288;
        text-align: center;
        font-size: 18px;
        font-weight: 700;
    }
    .cql-empty.show {
        display: flex;
    }

    .cql-list {
        display: grid;
        gap: 16px;
    }
    .cql-card {
        background: #fff;
        border: 1px solid #DDE7EE;
        border-radius: 20px;
        box-shadow: 0 12px 32px rgba(15, 23, 42, 0.06);
        overflow: hidden;
    }
    .cql-card-main {
        padding: 22px 24px;
    }
    .cql-card-top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 16px;
        flex-wrap: wrap;
    }
    .cql-quote-id {
        margin: 0;
        color: #123A5A;
        font-size: 20px;
        font-weight: 800;
    }
    .cql-quote-date {
        margin-top: 4px;
        color: #5E7288;
        font-size: 13px;
    }
    .cql-badges {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }
    .cql-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        min-height: 28px;
        padding: 0 12px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: .4px;
        text-transform: uppercase;
    }
    .cql-badge::before {
        content: '';
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: currentColor;
        opacity: .78;
    }
    .cql-badge-initial { background: #e0f2fe; color: #20A7C9; }
    .cql-badge-final { background: #dcfce7; color: #15803d; }
    .cql-badge-pending { background: #FFF7CC; color: #a16207; }
    .cql-badge-approved { background: #dcfce7; color: #15803d; }
    .cql-badge-completed { background: #d1fae5; color: #065f46; }
    .cql-badge-rejected { background: #fee2e2; color: #dc2626; }
    .cql-badge-default { background: #DDE7EE; color: #5E7288; }

    .cql-meta-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 12px;
    }
    .cql-meta {
        padding: 14px 14px 12px;
        border-radius: 14px;
        background: #f8fafc;
        border: 1px solid #DDE7EE;
    }
    .cql-meta-label {
        margin: 0 0 6px;
        color: #7F92A3;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .45px;
        text-transform: uppercase;
    }
    .cql-meta-value {
        margin: 0;
        color: #123A5A;
        font-size: 15px;
        font-weight: 700;
        line-height: 1.4;
        word-break: break-word;
    }
    .cql-actions-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
        margin-top: 18px;
    }
    .cql-action-group {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }
    .cql-card-note {
        color: #5E7288;
        font-size: 13px;
    }
    .cql-detail-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 42px;
        padding: 0 16px;
        border: 1px solid #DDE7EE;
        border-radius: 12px;
        background: #fff;
        color: #123A5A;
        font-size: 13px;
        font-weight: 700;
        text-decoration: none;
        cursor: pointer;
        transition: all .15s ease;
    }
    .cql-detail-btn:hover {
        border-color: #123A5A;
        background: #123A5A;
        color: #fff;
    }
    .cql-detail-btn-download {
        background: #123A5A;
        border-color: #123A5A;
        color: #fff;
    }
    .cql-detail-btn-download:hover {
        background: #123A5A;
        border-color: #123A5A;
        color: #fff;
    }
    .cql-detail-btn-danger {
        border-color: #fecaca;
        color: #b91c1c;
    }
    .cql-detail-btn-danger:hover {
        background: #b91c1c;
        border-color: #b91c1c;
        color: #fff;
    }
    .cql-detail {
        display: none;
        padding: 0 24px 22px;
    }
    .cql-detail.show {
        display: block;
    }
    .cql-detail-content-full {
        width: 100%;
    }
    .cql-detail-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
    }
    .cql-initial-detail {
        display: grid;
        gap: 14px;
    }
    .cql-initial-summary {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 10px;
    }
    .cql-initial-card {
        padding: 14px 15px;
        border-radius: 16px;
        background: linear-gradient(135deg, #f8fafc 0%, #fffdf6 100%);
        border: 1px solid #DDE7EE;
    }
    .cql-initial-card-feature {
        background: linear-gradient(135deg, #123A5A 0%, #123A5A 100%);
        border-color: rgba(16, 42, 67, 0.18);
        box-shadow: 0 10px 26px rgba(16, 42, 67, 0.14);
    }
    .cql-initial-card-feature .cql-initial-card-label,
    .cql-initial-card-feature .cql-initial-card-value {
        color: #fff;
    }
    .cql-initial-card-feature .cql-initial-card-label {
        color: rgba(255, 255, 255, 0.72);
    }
    .cql-initial-card-label {
        margin: 0 0 6px;
        color: #7F92A3;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .45px;
        text-transform: uppercase;
    }
    .cql-initial-card-value {
        margin: 0;
        color: #123A5A;
        font-size: 16px;
        font-weight: 800;
        line-height: 1.35;
        word-break: break-word;
    }
    .cql-option-list {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
    }
    .cql-option-card {
        padding: 16px;
        border-radius: 16px;
        background: #fff;
        border: 1px solid #DDE7EE;
    }
    .cql-option-title {
        margin: 0 0 10px;
        color: #123A5A;
        font-size: 15px;
        font-weight: 900;
    }
    .cql-option-note {
        margin: 10px 0 0;
        color: #92400e;
        font-size: 12px;
        line-height: 1.5;
    }
    .cql-estimate-disclaimer {
        padding: 14px 16px;
        border-radius: 14px;
        background: #fff7e0;
        border: 1px solid #f2d48a;
        color: #6f5a1a;
        font-size: 13px;
        line-height: 1.6;
    }
    .cql-initial-remarks {
        padding: 16px 18px;
        border-radius: 16px;
        background: #fffaf0;
        border: 1px solid #f6e2a6;
    }
    .cql-initial-remarks-title {
        margin: 0 0 8px;
        color: #a16207;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: .45px;
        text-transform: uppercase;
    }
    .cql-initial-remarks-copy {
        margin: 0;
        color: #78350f;
        font-size: 14px;
        line-height: 1.7;
        word-break: break-word;
    }
    .cql-detail-item {
        padding: 14px;
        border-radius: 14px;
        background: #f8fafc;
        border: 1px solid #DDE7EE;
    }
    .cql-detail-label {
        margin: 0 0 6px;
        color: #7F92A3;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .45px;
    }
    .cql-detail-value {
        margin: 0;
        color: #0F2F4A;
        font-size: 14px;
        font-weight: 600;
        line-height: 1.5;
        word-break: break-word;
    }

    @media (max-width: 1100px) {
        .cql-initial-summary {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .cql-meta-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
    @media (max-width: 768px) {
        .cql-hero {
            padding: 28px 22px;
        }
        .cql-title {
            font-size: 27px;
        }
        .cql-card-main,
        .cql-detail {
            padding-left: 18px;
            padding-right: 18px;
        }
        .cql-initial-summary,
        .cql-option-list,
        .cql-detail-grid,
        .cql-meta-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<section class="cql-hero">
    <div>
        <p class="cql-eyebrow">Quotation History</p>
        <h1 class="cql-title">View all your quotations in one place</h1>
        <p class="cql-sub">Review your submitted pre-inspection estimates and any inspection-based quotations prepared by our technicians. The latest quotations appear first so you can quickly pick up where you left off.</p>
    </div>
    <div class="cql-actions">
        <a href="{{ route('customer.quotation.create') }}" class="cql-btn cql-btn-primary">Create Quotation</a>
        <a href="{{ route('customer.quotation') }}" class="cql-btn cql-btn-secondary">Quotation Hub</a>
    </div>
</section>

<section aria-label="Quotation list">
    <div class="cql-toolbar">
        <div class="cql-toolbar-copy">Pre-inspection estimates and inspection-based quotations are shown together and sorted by the most recent creation date.</div>
        <div class="cql-filters">
            <button type="button" class="cql-chip active" data-filter="all">All</button>
            <button type="button" class="cql-chip" data-filter="initial">Pre-Inspection Estimate</button>
            <button type="button" class="cql-chip" data-filter="final">Inspection-Based Quotation</button>
        </div>
    </div>

    <div id="cql-msg" class="cql-msg"></div>
    <div id="cql-loading" class="cql-loading">Loading quotations...</div>
    <div id="cql-empty" class="cql-empty">No quotations yet.</div>
    <div id="cql-list" class="cql-list"></div>
</section>
@endsection

@push('scripts')
<script>
(function () {
    'use strict';

    function qs(selector, root) {
        return (root || document).querySelector(selector);
    }

    function qsa(selector, root) {
        return Array.prototype.slice.call((root || document).querySelectorAll(selector));
    }

    function escHtml(value) {
        return String(value == null ? '' : value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function fmtPeso(value) {
        if (value === null || value === undefined || value === '' || isNaN(Number(value))) {
            return '-';
        }
        return 'PHP ' + Number(value).toLocaleString('en-PH', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    function fmtDate(value) {
        if (!value) {
            return '-';
        }

        try {
            return new Date(value).toLocaleDateString('en-PH', {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            });
        } catch (error) {
            return value;
        }
    }

    function titleCase(value) {
        var text = String(value || '').replace(/[_-]+/g, ' ').trim();
        if (!text) {
            return '-';
        }

        return text.replace(/\b\w/g, function (match) {
            return match.toUpperCase();
        });
    }

    function getAppliedPromo(quotation) {
        return (quotation && (quotation.applied_promo || quotation.appliedPromo)) || null;
    }

    function hasAppliedPromo(quotation) {
        var discount = quotation ? Number(quotation.promo_discount || 0) : 0;
        return !!(getAppliedPromo(quotation) || (quotation && quotation.applied_promo_id) || discount > 0);
    }

    function getCookie(name) {
        var match = document.cookie.match(new RegExp('(?:^|; )' + name.replace(/([.*+?^=!:${}()|[\]\/\\])/g, '\\$1') + '=([^;]*)'));
        return match ? decodeURIComponent(match[1]) : null;
    }

    async function ensureCsrf() {
        if (!getCookie('XSRF-TOKEN')) {
            await fetch('/sanctum/csrf-cookie', { credentials: 'same-origin' });
        }
    }

    function formatPromoRule(quotation) {
        var promo = getAppliedPromo(quotation);
        if (!promo) {
            return quotation && quotation.applied_promo_id ? 'Promo #' + quotation.applied_promo_id : '-';
        }

        var title = promo.title || promo.free_item_description || ('Promo #' + promo.id);
        var conditions = promo.conditions || {};
        var appliesTo = conditions.applies_to ? titleCase(conditions.applies_to) : '';
        var minQty = Number(conditions.min_qty || 0);
        var freeQty = Number(conditions.free_qty || 1);

        if (promo.promo_type === 'free_item' && appliesTo && minQty > 0) {
            return title + ' (' + appliesTo + ': buy ' + minQty + ', get ' + freeQty + ' free)';
        }

        if (promo.promo_type === 'free_item' && promo.free_item_description) {
            return title + ' (' + promo.free_item_description + ')';
        }

        return title;
    }

    async function apiRequest(endpoint, options) {
        var method = (options && options.method) || 'GET';
        var headers = {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        };

        if (method !== 'GET') {
            await ensureCsrf();
            headers['Content-Type'] = 'application/json';
            headers['X-XSRF-TOKEN'] = getCookie('XSRF-TOKEN') || '';
        }

        var response = await fetch(endpoint, {
            method: method,
            credentials: 'same-origin',
            headers: headers,
            body: options && options.body !== undefined ? JSON.stringify(options.body) : undefined
        });

        var payload = await response.json().catch(function () { return {}; });

        if (!response.ok) {
            var err = new Error(payload.message || 'Request failed.');
            err.status = response.status;
            throw err;
        }

        return payload;
    }

    function showMsg(text, type) {
        var el = qs('#cql-msg');
        el.className = 'cql-msg show cql-msg-' + (type || 'error');
        el.textContent = text;
    }

    function hideMsg() {
        var el = qs('#cql-msg');
        el.className = 'cql-msg';
        el.textContent = '';
    }

    function typeBadge(type) {
        var normalized = String(type || 'initial').toLowerCase();
        var isFinal = normalized === 'final' || normalized.indexOf('inspection-based') !== -1;
        var label = isFinal ? 'Inspection-Based Quotation' : 'Pre-Inspection Estimate';
        var badgeClass = isFinal ? 'cql-badge-final' : 'cql-badge-initial';
        return '<span class="cql-badge ' + badgeClass + '">' + escHtml(label) + '</span>';
    }

    function statusBadge(status) {
        if (!status) {
            return '';
        }

        var normalized = String(status).toLowerCase();
        var classMap = {
            pending: 'cql-badge-pending',
            approved: 'cql-badge-approved',
            completed: 'cql-badge-completed',
            rejected: 'cql-badge-rejected'
        };

        return '<span class="cql-badge ' + (classMap[normalized] || 'cql-badge-default') + '">' + escHtml(titleCase(normalized)) + '</span>';
    }

    function relatedRequestLabel(quotation) {
        if (quotation.inspection_request_id) {
            return 'Inspection Request #' + quotation.inspection_request_id;
        }
        if (quotation.service_request_id) {
            return 'Service Request #' + quotation.service_request_id;
        }
        return '-';
    }

    function isInitialQuotation(quotation) {
        return String(quotation.quotation_type || 'initial').toLowerCase() === 'initial';
    }

    function renderInitialSummaryCard(label, value, featureClass) {
        return '<div class="cql-initial-card' + (featureClass ? ' ' + featureClass : '') + '">'
            + '<p class="cql-initial-card-label">' + escHtml(label) + '</p>'
            + '<p class="cql-initial-card-value">' + escHtml(value) + '</p>'
            + '</div>';
    }

    function initialOptionTitle(option) {
        return String(option.system_type || '').toLowerCase() === 'hybrid'
            ? 'Hybrid'
            : 'On-Grid';
    }

    function fmtCapacity(value, suffix) {
        if (value === null || value === undefined || value === '' || isNaN(Number(value))) {
            return '-';
        }

        return Number(value).toFixed(2) + suffix;
    }

    function renderInitialOption(option) {
        var battery = option.with_battery
            ? fmtCapacity(option.battery_capacity_ah, ' Ah')
            : 'Not included';
        var note = option.validation_note
            ? '<p class="cql-option-note">' + escHtml(option.validation_note) + '</p>'
            : '';

        return '<article class="cql-option-card">'
            + '<p class="cql-option-title">' + escHtml(initialOptionTitle(option)) + '</p>'
            + '<div class="cql-initial-summary">'
            + renderInitialSummaryCard('System Size', fmtCapacity(option.system_kw, ' kW'), '')
            + renderInitialSummaryCard('Inverter', fmtCapacity(option.inverter_capacity_kw, ' kW'), '')
            + renderInitialSummaryCard('Battery', battery, '')
            + renderInitialSummaryCard('Total Estimate', fmtPeso(option.project_cost), 'cql-initial-card-feature')
            + renderInitialSummaryCard('Monthly Savings', fmtPeso(option.estimated_monthly_savings), '')
            + renderInitialSummaryCard('ROI', option.roi_years ? Number(option.roi_years).toFixed(1) + ' years' : '-', '')
            + '</div>'
            + note
            + '</article>';
    }

    function renderInitialDetail(quotation) {
        var remarks = quotation.remarks
            ? '<div class="cql-initial-remarks">'
                + '<p class="cql-initial-remarks-title">Remarks</p>'
                + '<p class="cql-initial-remarks-copy">' + escHtml(quotation.remarks) + '</p>'
              + '</div>'
            : '';
        var options = Array.isArray(quotation.pre_inspection_options) && quotation.pre_inspection_options.length
            ? '<div class="cql-option-list">' + quotation.pre_inspection_options.map(renderInitialOption).join('') + '</div>'
            : '';

        return '<div class="cql-initial-detail">'
            + '<div class="cql-estimate-disclaimer">This pre-inspection estimate only accounts for solar panels, inverter, and battery. It does not include labor, wiring, mounting materials, protection devices, permits, and other installation costs.</div>'
            + options
            + remarks
            + '</div>';
    }

    function renderDetailGrid(quotation) {
        if (isInitialQuotation(quotation)) {
            return renderInitialDetail(quotation);
        }

        var detailItems = [
            ['Monthly Electric Bill', fmtPeso(quotation.monthly_electric_bill)],
            ['Estimated System Size', quotation.system_kw ? Number(quotation.system_kw).toFixed(2) + ' kW' : '-'],
            ['Panel Cost', fmtPeso(quotation.panel_cost)],
            ['Inverter Cost', fmtPeso(quotation.inverter_cost)],
            ['Battery Cost', fmtPeso(quotation.battery_cost)],
            ['BOS Cost', fmtPeso(quotation.bos_cost)],
            ['Materials Subtotal', fmtPeso(quotation.materials_subtotal)],
            ['Labor Cost', fmtPeso(quotation.labor_cost || 0)],
            ['Total Project Cost', fmtPeso(quotation.project_cost)],
            ['Est. Monthly Savings', fmtPeso(quotation.estimated_monthly_savings)],
            ['Est. Annual Savings', fmtPeso(quotation.estimated_annual_savings)],
            ['ROI / Payback Period', quotation.roi_years ? Number(quotation.roi_years).toFixed(1) + ' years' : '-'],
            ['System Type', titleCase(quotation.pv_system_type)],
            ['Panel Quantity', quotation.panel_quantity || '-'],
            ['Panel Watts', quotation.panel_watts ? quotation.panel_watts + ' W' : '-'],
            ['With Battery', quotation.with_battery ? 'Yes' : 'No'],
            ['Related Request', relatedRequestLabel(quotation)],
            ['Remarks', quotation.remarks || '-']
        ];

        if (hasAppliedPromo(quotation)) {
            detailItems.splice(8, 0, ['Applied Promo', formatPromoRule(quotation)]);
            if (Number(quotation.promo_discount || 0) > 0) {
                detailItems.splice(9, 0, ['Promo Discount', '-' + fmtPeso(quotation.promo_discount)]);
            }
        }

        return detailItems.map(function (item) {
            return '<div class="cql-detail-item">'
                + '<p class="cql-detail-label">' + escHtml(item[0]) + '</p>'
                + '<p class="cql-detail-value">' + escHtml(item[1]) + '</p>'
                + '</div>';
        }).join('');
    }

    function buildAction(quotation) {
        var isFinal = !isInitialQuotation(quotation);
        if (isFinal && quotation.inspection_request_id) {
            return '<div class="cql-action-group">'
                + '<a class="cql-detail-btn" href="{{ url("/customer/final-quotation") }}/' + encodeURIComponent(quotation.inspection_request_id) + '">View Details</a>'
                + '<a class="cql-detail-btn cql-detail-btn-download" href="{{ url("/customer/final-quotation") }}/' + encodeURIComponent(quotation.inspection_request_id) + '/download-pdf">Download PDF</a>'
                + '</div>';
        }

        return '<div class="cql-action-group">'
            + '<button type="button" class="cql-detail-btn" data-toggle-id="' + escHtml(quotation.id) + '">View Details</button>'
            + '<button type="button" class="cql-detail-btn cql-detail-btn-danger" data-delete-id="' + escHtml(quotation.id) + '">Delete</button>'
            + '</div>';
    }

    function renderCard(quotation) {
        var statusText = quotation.status ? titleCase(quotation.status) : '-';
        var createdDate = fmtDate(quotation.created_at);
        var projectedCost = fmtPeso(quotation.project_cost);
        var relatedRequest = relatedRequestLabel(quotation);
        var initial = isInitialQuotation(quotation);
        var typeText = initial ? 'Pre-Inspection Estimate' : 'Inspection-Based Quotation';
        var detailWrapperClass = initial ? 'cql-detail-content-full' : 'cql-detail-grid';
        var cardNote = initial
            ? 'Estimate options: <strong>On-Grid and Hybrid</strong>'
            : 'Projected cost: <strong>' + projectedCost + '</strong>';
        var metaItems = [
            ['Quotation ID', '#' + quotation.id],
            ['Quotation Type', typeText],
            ['Created Date', createdDate]
        ];

        if (!initial) {
            metaItems.push(['Status', statusText]);
            metaItems.push(['Related Request', relatedRequest]);
        }

        return '<article class="cql-card">'
            + '<div class="cql-card-main">'
            + '<div class="cql-card-top">'
            + '<div>'
            + '<h2 class="cql-quote-id">Quotation #' + escHtml(quotation.id) + '</h2>'
            + '<div class="cql-quote-date">Submitted ' + escHtml(createdDate) + '</div>'
            + '</div>'
            + '<div class="cql-badges">' + typeBadge(quotation.quotation_type) + (initial ? '' : statusBadge(quotation.status)) + '</div>'
            + '</div>'
            + '<div class="cql-meta-grid">'
            + metaItems.map(function (item) {
                return '<div class="cql-meta"><p class="cql-meta-label">' + escHtml(item[0]) + '</p><p class="cql-meta-value">' + escHtml(item[1]) + '</p></div>';
            }).join('')
            + '</div>'
            + '<div class="cql-actions-row">'
            + '<div class="cql-card-note">' + cardNote + '</div>'
            + buildAction(quotation)
            + '</div>'
            + '</div>'
            + '<div class="cql-detail" id="cql-detail-' + escHtml(quotation.id) + '">'
            + '<div class="' + detailWrapperClass + '">' + renderDetailGrid(quotation) + '</div>'
            + '</div>'
            + '</article>';
    }

    var quotations = [];
    var activeFilter = 'all';

    function sortLatestFirst(items) {
        return items.slice().sort(function (a, b) {
            var aTime = a && a.created_at ? new Date(a.created_at).getTime() : 0;
            var bTime = b && b.created_at ? new Date(b.created_at).getTime() : 0;

            if (aTime !== bTime) {
                return bTime - aTime;
            }

            return Number(b.id || 0) - Number(a.id || 0);
        });
    }

    function filteredQuotations() {
        if (activeFilter === 'all') {
            return sortLatestFirst(quotations);
        }

        return sortLatestFirst(quotations.filter(function (quotation) {
            return String(quotation.quotation_type || 'initial').toLowerCase() === activeFilter;
        }));
    }

    function renderList() {
        var list = qs('#cql-list');
        var empty = qs('#cql-empty');
        var results = filteredQuotations();

        list.innerHTML = '';
        empty.classList.remove('show');

        if (!results.length) {
            empty.classList.add('show');
            return;
        }

        list.innerHTML = results.map(renderCard).join('');

        qsa('[data-toggle-id]', list).forEach(function (button) {
            button.addEventListener('click', function () {
                var id = button.getAttribute('data-toggle-id');
                var detail = qs('#cql-detail-' + id);
                if (!detail) {
                    return;
                }

                var isOpen = detail.classList.contains('show');
                detail.classList.toggle('show', !isOpen);
                button.textContent = isOpen ? 'View Details' : 'Hide Details';
            });
        });

        qsa('[data-delete-id]', list).forEach(function (button) {
            button.addEventListener('click', async function () {
                var id = button.getAttribute('data-delete-id');

                if (!id || !window.confirm('Delete this pre-inspection estimate? This action cannot be undone.')) {
                    return;
                }

                button.disabled = true;
                button.textContent = 'Deleting...';

                try {
                    var response = await apiRequest('{{ url("/customer/quotation") }}/' + encodeURIComponent(id), {
                        method: 'DELETE'
                    });
                    quotations = quotations.filter(function (quotation) {
                        return String(quotation.id) !== String(id);
                    });
                    renderList();
                    showMsg(response.message || 'Pre-inspection estimate deleted successfully.', 'success');
                } catch (error) {
                    button.disabled = false;
                    button.textContent = 'Delete';
                    showMsg(error.message || 'Could not delete the pre-inspection estimate.');
                }
            });
        });
    }

    async function loadQuotations() {
        var loading = qs('#cql-loading');
        loading.classList.add('show');
        hideMsg();

        try {
            var payload = await apiRequest('/api/quotations');
            quotations = Array.isArray(payload) ? payload : (Array.isArray(payload.data) ? payload.data : []);
            renderList();
        } catch (error) {
            showMsg(error.message || 'Could not load quotations.');
        } finally {
            loading.classList.remove('show');
        }
    }

    qsa('.cql-chip').forEach(function (chip) {
        chip.addEventListener('click', function () {
            qsa('.cql-chip').forEach(function (item) {
                item.classList.remove('active');
            });
            chip.classList.add('active');
            activeFilter = chip.getAttribute('data-filter') || 'all';
            renderList();
        });
    });

    loadQuotations();
})();
</script>
@endpush
