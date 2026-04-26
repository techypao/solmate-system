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
            linear-gradient(135deg, #eff6ff 0%, #f8fafc 55%, #fefce8 100%);
        border: 1px solid #e2e8f0;
    }
    .cql-eyebrow {
        margin: 0 0 10px;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 1.2px;
        text-transform: uppercase;
        color: #d4a017;
    }
    .cql-title {
        margin: 0 0 10px;
        font-size: 32px;
        font-weight: 800;
        color: #102a43;
        line-height: 1.15;
    }
    .cql-sub {
        max-width: 650px;
        margin: 0;
        color: #475569;
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
        background: linear-gradient(135deg, #d4a017, #b8880f);
        color: #fff;
    }
    .cql-btn-secondary {
        background: #102a43;
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
        color: #64748b;
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
        border: 1px solid #cbd5e1;
        border-radius: 999px;
        background: #fff;
        color: #475569;
        font-size: 12px;
        font-weight: 700;
        cursor: pointer;
        transition: all .15s ease;
    }
    .cql-chip.active,
    .cql-chip:hover {
        background: #102a43;
        border-color: #102a43;
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
    .cql-loading {
        display: none;
        padding: 30px 18px;
        text-align: center;
        color: #64748b;
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
        border: 1px dashed #cbd5e1;
        background: #fff;
        color: #64748b;
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
        border: 1px solid #e2e8f0;
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
        color: #102a43;
        font-size: 20px;
        font-weight: 800;
    }
    .cql-quote-date {
        margin-top: 4px;
        color: #64748b;
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
    .cql-badge-initial { background: #e0f2fe; color: #0284c7; }
    .cql-badge-final { background: #dcfce7; color: #15803d; }
    .cql-badge-pending { background: #fef9c3; color: #a16207; }
    .cql-badge-approved { background: #dcfce7; color: #15803d; }
    .cql-badge-completed { background: #d1fae5; color: #065f46; }
    .cql-badge-rejected { background: #fee2e2; color: #dc2626; }
    .cql-badge-default { background: #e2e8f0; color: #475569; }

    .cql-meta-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 12px;
    }
    .cql-meta {
        padding: 14px 14px 12px;
        border-radius: 14px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
    }
    .cql-meta-label {
        margin: 0 0 6px;
        color: #94a3b8;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .45px;
        text-transform: uppercase;
    }
    .cql-meta-value {
        margin: 0;
        color: #102a43;
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
    .cql-card-note {
        color: #64748b;
        font-size: 13px;
    }
    .cql-detail-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 42px;
        padding: 0 16px;
        border: 1px solid #cbd5e1;
        border-radius: 12px;
        background: #fff;
        color: #102a43;
        font-size: 13px;
        font-weight: 700;
        text-decoration: none;
        cursor: pointer;
        transition: all .15s ease;
    }
    .cql-detail-btn:hover {
        border-color: #102a43;
        background: #102a43;
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
        border: 1px solid #e2e8f0;
    }
    .cql-initial-card-feature {
        background: linear-gradient(135deg, #102a43 0%, #163a5f 100%);
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
        color: #94a3b8;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .45px;
        text-transform: uppercase;
    }
    .cql-initial-card-value {
        margin: 0;
        color: #102a43;
        font-size: 16px;
        font-weight: 800;
        line-height: 1.35;
        word-break: break-word;
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
        border: 1px solid #e2e8f0;
    }
    .cql-detail-label {
        margin: 0 0 6px;
        color: #94a3b8;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .45px;
    }
    .cql-detail-value {
        margin: 0;
        color: #1e293b;
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
        <p class="cql-sub">Review your submitted initial quotations and any final quotations prepared by our technicians. The latest quotations appear first so you can quickly pick up where you left off.</p>
    </div>
    <div class="cql-actions">
        <a href="{{ route('customer.quotation.create') }}" class="cql-btn cql-btn-primary">Create Quotation</a>
        <a href="{{ route('customer.quotation') }}" class="cql-btn cql-btn-secondary">Quotation Hub</a>
    </div>
</section>

<section aria-label="Quotation list">
    <div class="cql-toolbar">
        <div class="cql-toolbar-copy">Initial and final quotations are shown together and sorted by the most recent creation date.</div>
        <div class="cql-filters">
            <button type="button" class="cql-chip active" data-filter="all">All</button>
            <button type="button" class="cql-chip" data-filter="initial">Initial</button>
            <button type="button" class="cql-chip" data-filter="final">Final</button>
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

    async function apiRequest(endpoint) {
        var response = await fetch(endpoint, {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        var payload = await response.json().catch(function () { return {}; });

        if (!response.ok) {
            var err = new Error(payload.message || 'Request failed.');
            err.status = response.status;
            throw err;
        }

        return payload;
    }

    function showMsg(text) {
        var el = qs('#cql-msg');
        el.className = 'cql-msg show cql-msg-error';
        el.textContent = text;
    }

    function hideMsg() {
        var el = qs('#cql-msg');
        el.className = 'cql-msg';
        el.textContent = '';
    }

    function typeBadge(type) {
        var normalized = String(type || 'initial').toLowerCase();
        var label = normalized === 'final' ? 'Final' : 'Initial';
        var badgeClass = normalized === 'final' ? 'cql-badge-final' : 'cql-badge-initial';
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

    function renderInitialDetail(quotation) {
        var summaryCards = [
            ['Quotation ID', '#' + quotation.id, ''],
            ['Quotation Type', 'Initial', ''],
            ['Submitted Date', fmtDate(quotation.created_at), ''],
            ['Monthly Electric Bill', fmtPeso(quotation.monthly_electric_bill), ''],
            ['Estimated System Size', quotation.system_kw ? Number(quotation.system_kw).toFixed(2) + ' kW' : '-', ''],
            ['Projected Cost', fmtPeso(quotation.project_cost), 'cql-initial-card-feature'],
            ['Estimated Monthly Savings', fmtPeso(quotation.estimated_monthly_savings), ''],
            ['Estimated Annual Savings', fmtPeso(quotation.estimated_annual_savings), ''],
            ['ROI', quotation.roi_years ? Number(quotation.roi_years).toFixed(1) + ' years' : '-', '']
        ];

        var remarks = quotation.remarks
            ? '<div class="cql-initial-remarks">'
                + '<p class="cql-initial-remarks-title">Remarks</p>'
                + '<p class="cql-initial-remarks-copy">' + escHtml(quotation.remarks) + '</p>'
              + '</div>'
            : '';

        return '<div class="cql-initial-detail">'
            + '<div class="cql-initial-summary">'
            + summaryCards.map(function (item) {
                return renderInitialSummaryCard(item[0], item[1], item[2]);
            }).join('')
            + '</div>'
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
            ['Projected Cost', fmtPeso(quotation.project_cost)],
            ['Estimated Monthly Savings', fmtPeso(quotation.estimated_monthly_savings)],
            ['Estimated Annual Savings', fmtPeso(quotation.estimated_annual_savings)],
            ['ROI', quotation.roi_years ? Number(quotation.roi_years).toFixed(1) + ' years' : '-'],
            ['System Type', titleCase(quotation.pv_system_type)],
            ['Panel Quantity', quotation.panel_quantity || '-'],
            ['Panel Watts', quotation.panel_watts ? quotation.panel_watts + ' W' : '-'],
            ['With Battery', quotation.with_battery ? 'Yes' : 'No'],
            ['Related Request', relatedRequestLabel(quotation)],
            ['Remarks', quotation.remarks || '-']
        ];

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
            return '<a class="cql-detail-btn" href="{{ url('/customer/final-quotation') }}/' + encodeURIComponent(quotation.inspection_request_id) + '">View Details</a>';
        }

        return '<button type="button" class="cql-detail-btn" data-toggle-id="' + escHtml(quotation.id) + '">View Details</button>';
    }

    function renderCard(quotation) {
        var statusText = quotation.status ? titleCase(quotation.status) : '-';
        var createdDate = fmtDate(quotation.created_at);
        var projectedCost = fmtPeso(quotation.project_cost);
        var relatedRequest = relatedRequestLabel(quotation);
        var initial = isInitialQuotation(quotation);
        var typeText = initial ? 'Initial' : 'Final';
        var detailWrapperClass = initial ? 'cql-detail-content-full' : 'cql-detail-grid';
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
            + '<div class="cql-badges">' + typeBadge(typeText) + (initial ? '' : statusBadge(quotation.status)) + '</div>'
            + '</div>'
            + '<div class="cql-meta-grid">'
            + metaItems.map(function (item) {
                return '<div class="cql-meta"><p class="cql-meta-label">' + escHtml(item[0]) + '</p><p class="cql-meta-value">' + escHtml(item[1]) + '</p></div>';
            }).join('')
            + '</div>'
            + '<div class="cql-actions-row">'
            + '<div class="cql-card-note">Projected cost: <strong>' + projectedCost + '</strong></div>'
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
