@extends('layouts.app', ['title' => 'Create Pre-Inspection Estimate'])

@section('content')
<style>
    .cqc-hero {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 20px;
        flex-wrap: wrap;
        margin-bottom: 28px;
        padding: 34px 38px;
        border-radius: 22px;
        background:
            radial-gradient(circle at top right, rgba(212, 160, 23, 0.18), transparent 34%),
            linear-gradient(135deg, #f0f9ff 0%, #eff6ff 58%, #f8fafc 100%);
        border: 1px solid #dbeafe;
    }
    .cqc-eyebrow {
        margin: 0 0 10px;
        color: #d4a017;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 1.2px;
        text-transform: uppercase;
    }
    .cqc-title {
        margin: 0 0 10px;
        color: #102a43;
        font-size: 32px;
        font-weight: 800;
        line-height: 1.15;
    }
    .cqc-sub {
        max-width: 650px;
        margin: 0;
        color: #475569;
        font-size: 15px;
        line-height: 1.7;
    }
    .cqc-actions {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }
    .cqc-link-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-height: 46px;
        padding: 0 18px;
        border-radius: 12px;
        text-decoration: none;
        font-size: 14px;
        font-weight: 700;
        transition: transform .15s ease, box-shadow .15s ease;
    }
    .cqc-link-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.12);
    }
    .cqc-link-btn-primary {
        background: #102a43;
        color: #fff;
    }
    .cqc-link-btn-secondary {
        background: linear-gradient(135deg, #d4a017, #b8880f);
        color: #fff;
    }

    .cqc-layout {
        display: grid;
        grid-template-columns: minmax(0, 1.2fr) minmax(280px, .8fr);
        gap: 22px;
        align-items: start;
    }
    .cqc-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 20px;
        box-shadow: 0 16px 36px rgba(15, 23, 42, 0.06);
        overflow: hidden;
    }
    .cqc-card-header {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 20px 24px;
        border-bottom: 1px solid #f1f5f9;
    }
    .cqc-card-icon {
        width: 44px;
        height: 44px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 14px;
        background: linear-gradient(135deg, rgba(16, 42, 67, 0.12), rgba(212, 160, 23, 0.18));
        color: #102a43;
        flex-shrink: 0;
    }
    .cqc-card-title {
        margin: 0;
        color: #102a43;
        font-size: 18px;
        font-weight: 800;
    }
    .cqc-card-subtitle {
        margin: 4px 0 0;
        color: #64748b;
        font-size: 13px;
    }
    .cqc-card-body {
        padding: 24px;
    }

    .cqc-msg {
        display: none;
        margin-bottom: 18px;
        padding: 12px 14px;
        border-radius: 12px;
        font-size: 14px;
        line-height: 1.5;
    }
    .cqc-msg.show {
        display: block;
    }
    .cqc-msg-success {
        background: #dcfce7;
        border: 1px solid #bbf7d0;
        color: #166534;
    }
    .cqc-msg-error {
        background: #fee2e2;
        border: 1px solid #fecaca;
        color: #991b1b;
    }

    .cqc-field {
        margin-bottom: 20px;
    }
    .cqc-label {
        display: block;
        margin-bottom: 8px;
        color: #475569;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: .6px;
        text-transform: uppercase;
    }
    .cqc-input-wrap {
        position: relative;
    }
    .cqc-input-prefix {
        position: absolute;
        top: 50%;
        left: 14px;
        transform: translateY(-50%);
        color: #64748b;
        font-size: 15px;
        font-weight: 700;
        pointer-events: none;
    }
    .cqc-input,
    .cqc-textarea {
        width: 100%;
        border: 1.5px solid #cbd5e1;
        border-radius: 12px;
        box-sizing: border-box;
        background: #fff;
        color: #102a43;
        transition: border-color .15s ease, box-shadow .15s ease;
    }
    .cqc-input {
        padding: 14px 16px 14px 38px;
        font-size: 16px;
        font-weight: 700;
        -moz-appearance: textfield;
        appearance: textfield;
    }
    .cqc-input::-webkit-outer-spin-button,
    .cqc-input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
    .cqc-textarea {
        min-height: 108px;
        padding: 13px 14px;
        resize: vertical;
        font-size: 14px;
        line-height: 1.6;
        font-family: inherit;
    }
    .cqc-input:focus,
    .cqc-textarea:focus {
        outline: none;
        border-color: #d4a017;
        box-shadow: 0 0 0 3px rgba(212, 160, 23, 0.12);
    }
    .cqc-input.has-error,
    .cqc-textarea.has-error {
        border-color: #ef4444;
    }
    .cqc-field-hint {
        margin-top: 7px;
        color: #94a3b8;
        font-size: 12px;
        line-height: 1.5;
    }
    .cqc-field-error {
        display: none;
        margin-top: 7px;
        color: #dc2626;
        font-size: 12px;
        line-height: 1.5;
    }
    .cqc-field-error.show {
        display: block;
    }

    .cqc-disclaimer {
        margin: 0 0 20px;
        padding: 12px 14px;
        border-radius: 12px;
        background: #f8fafc;
        border: 1px solid #dbeafe;
        color: #475569;
        font-size: 13px;
        line-height: 1.6;
    }
    .cqc-disclaimer strong {
        color: #102a43;
    }

    .cqc-submit {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        width: 100%;
        min-height: 50px;
        border: none;
        border-radius: 12px;
        background: linear-gradient(135deg, #d4a017, #b8880f);
        color: #fff;
        font-size: 15px;
        font-weight: 800;
        cursor: pointer;
        transition: transform .15s ease, opacity .15s ease;
    }
    .cqc-submit:hover {
        transform: translateY(-1px);
        opacity: .95;
    }
    .cqc-submit:disabled {
        opacity: .7;
        cursor: not-allowed;
        transform: none;
    }

    .cqc-result {
        display: none;
        margin-top: 22px;
        padding: 18px;
        border-radius: 18px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
    }
    .cqc-result.show {
        display: block;
    }
    .cqc-result-title {
        margin: 0 0 14px;
        color: #102a43;
        font-size: 16px;
        font-weight: 800;
    }
    .cqc-result-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
    }
    .cqc-result-item {
        padding: 14px;
        border-radius: 14px;
        background: #fff;
        border: 1px solid #e2e8f0;
    }
    .cqc-result-label {
        margin: 0 0 6px;
        color: #94a3b8;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .45px;
        text-transform: uppercase;
    }
    .cqc-result-value {
        margin: 0;
        color: #102a43;
        font-size: 18px;
        font-weight: 800;
    }
    .cqc-result-actions {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        margin-top: 16px;
    }

    .cqc-panel {
        display: grid;
        gap: 16px;
    }
    .cqc-info-card {
        padding: 22px;
    }
    .cqc-info-list {
        display: grid;
        gap: 14px;
    }
    .cqc-info-row {
        display: flex;
        gap: 12px;
        align-items: flex-start;
    }
    .cqc-info-icon {
        width: 36px;
        height: 36px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        flex-shrink: 0;
    }
    .cqc-info-title {
        margin: 0 0 4px;
        color: #102a43;
        font-size: 14px;
        font-weight: 700;
    }
    .cqc-info-copy {
        margin: 0;
        color: #64748b;
        font-size: 13px;
        line-height: 1.6;
    }
    .cqc-note {
        padding: 16px;
        border-radius: 16px;
        background: #fffbeb;
        border: 1px solid #fde68a;
        color: #78350f;
        font-size: 13px;
        line-height: 1.7;
    }
    .cqc-note strong {
        color: #92400e;
    }

    @media (max-width: 980px) {
        .cqc-layout {
            grid-template-columns: 1fr;
        }
    }
    @media (max-width: 768px) {
        .cqc-hero {
            padding: 28px 22px;
        }
        .cqc-title {
            font-size: 27px;
        }
        .cqc-card-body,
        .cqc-card-header {
            padding-left: 18px;
            padding-right: 18px;
        }
        .cqc-result-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<section class="cqc-hero">
    <div>
        <p class="cqc-eyebrow">Create Pre-Inspection Estimate</p>
        <h1 class="cqc-title">Estimate your solar system size, cost, and ROI</h1>
        <p class="cqc-sub">Enter your average monthly electric bill and we’ll generate a pre-inspection estimate, including projected savings and return on investment.</p>
    </div>
    <div class="cqc-actions">
        <a href="{{ route('customer.quotation.index') }}" class="cqc-link-btn cqc-link-btn-primary">View My Quotations</a>
        <a href="{{ route('customer.quotation') }}" class="cqc-link-btn cqc-link-btn-secondary">Quotation Hub</a>
    </div>
</section>

<div class="cqc-layout">
    <section class="cqc-card" aria-label="Pre-inspection estimate">
        <div class="cqc-card-header">
            <div class="cqc-card-icon" aria-hidden="true">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                    <path d="M13 2L3 14h8l-1 8 11-14h-8l1-6z"/>
                </svg>
            </div>
            <div>
                <h2 class="cqc-card-title">Pre-Inspection Estimate</h2>
                <p class="cqc-card-subtitle">Your existing automated sizing and ROI computation stays the same.</p>
            </div>
        </div>

        <div class="cqc-card-body">
            <div id="cqc-form-msg" class="cqc-msg"></div>

            <form id="cqc-form">
                <div class="cqc-field">
                    <label class="cqc-label" for="cqc-bill-input">Monthly Electric Bill</label>
                    <div class="cqc-input-wrap">
                        <span class="cqc-input-prefix">&#8369;</span>
                        <input
                            id="cqc-bill-input"
                            class="cqc-input"
                            type="number"
                            name="monthly_electric_bill"
                            min="1"
                            step="0.01"
                            placeholder="0.00"
                            autocomplete="off"
                            required
                        >
                    </div>
                    <p class="cqc-field-hint">Estimate your current average monthly bill in Philippine Peso.</p>
                    <div id="cqc-bill-error" class="cqc-field-error"></div>
                </div>

                <div class="cqc-disclaimer">
                    <strong>Disclaimer:</strong> Tax is not yet included in the estimate.
                </div>

                <button type="submit" class="cqc-submit" id="cqc-submit-btn">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4">
                        <path d="M13 2L3 14h8l-1 8 11-14h-8l1-6z"/>
                    </svg>
                    <span id="cqc-submit-text">Generate My Quotation</span>
                </button>
            </form>

            <div id="cqc-result" class="cqc-result">
                <h3 class="cqc-result-title">Your estimate</h3>
                <div id="cqc-result-grid" class="cqc-result-grid"></div>
                <div class="cqc-result-actions">
                    <a href="{{ route('customer.quotation.index') }}" class="cqc-link-btn cqc-link-btn-primary">View My Quotations</a>
                    <a href="{{ route('customer.inspection') }}" class="cqc-link-btn cqc-link-btn-secondary">Request Inspection</a>
                </div>
            </div>
        </div>
    </section>

    <aside class="cqc-panel">
        <section class="cqc-card cqc-info-card">
            <div class="cqc-info-list">
                <div class="cqc-info-row">
                    <div class="cqc-info-icon" aria-hidden="true">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#d4a017" stroke-width="2">
                            <path d="M20 6L9 17l-5-5"/>
                        </svg>
                    </div>
                    <div>
                        <p class="cqc-info-title">Instant sizing estimate</p>
                        <p class="cqc-info-copy">We calculate a recommended solar system size based on your bill and the configured quotation rules.</p>
                    </div>
                </div>

                <div class="cqc-info-row">
                    <div class="cqc-info-icon" aria-hidden="true">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2">
                            <path d="M12 20V10"/>
                            <path d="M18 20V4"/>
                            <path d="M6 20v-6"/>
                        </svg>
                    </div>
                    <div>
                        <p class="cqc-info-title">Projected savings and ROI</p>
                        <p class="cqc-info-copy">The estimate includes projected cost, expected monthly savings, and an ROI period so you can compare options quickly.</p>
                    </div>
                </div>

                <div class="cqc-info-row">
                    <div class="cqc-info-icon" aria-hidden="true">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2">
                            <path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0 1 18 0z"/>
                            <circle cx="12" cy="10" r="3"/>
                        </svg>
                    </div>
                    <div>
                        <p class="cqc-info-title">Inspection-based quotation later</p>
                        <p class="cqc-info-copy">After a site inspection, our technicians can prepare an inspection-based quotation for your approval.</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="cqc-note">
            <strong>Note:</strong> The pre-inspection estimate is only a guide and may change after the technician's actual inspection.
        </section>

        <section class="cqc-note">
            <strong>ROI guide:</strong> Return on investment estimates how long it may take for your solar savings to recover the project cost.
        </section>
    </aside>
</div>
@endsection

@push('scripts')
<script>
(function () {
    'use strict';

    function qs(selector, root) {
        return (root || document).querySelector(selector);
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

    function showMsg(el, type, text) {
        el.className = 'cqc-msg show cqc-msg-' + type;
        el.textContent = text;
    }

    function hideMsg(el) {
        el.className = 'cqc-msg';
        el.textContent = '';
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
            err.errors = payload.errors || {};
            throw err;
        }

        return payload;
    }

    function clearErrors() {
        ['#cqc-bill-error'].forEach(function (selector) {
            var el = qs(selector);
            el.textContent = '';
            el.classList.remove('show');
        });

        ['#cqc-bill-input'].forEach(function (selector) {
            qs(selector).classList.remove('has-error');
        });
    }

    function applyErrors(errors) {
        if (errors.monthly_electric_bill) {
            qs('#cqc-bill-error').textContent = Array.isArray(errors.monthly_electric_bill) ? errors.monthly_electric_bill[0] : errors.monthly_electric_bill;
            qs('#cqc-bill-error').classList.add('show');
            qs('#cqc-bill-input').classList.add('has-error');
        }

    }

    function renderResultItem(label, value) {
        return '<div class="cqc-result-item">'
            + '<p class="cqc-result-label">' + escHtml(label) + '</p>'
            + '<p class="cqc-result-value">' + value + '</p>'
            + '</div>';
    }

    var form = qs('#cqc-form');
    var formMsg = qs('#cqc-form-msg');
    var billInput = qs('#cqc-bill-input');
    var submitBtn = qs('#cqc-submit-btn');
    var submitText = qs('#cqc-submit-text');
    var resultBox = qs('#cqc-result');
    var resultGrid = qs('#cqc-result-grid');

    form.addEventListener('submit', async function (event) {
        event.preventDefault();
        clearErrors();
        hideMsg(formMsg);
        submitBtn.disabled = true;
        submitText.textContent = 'Generating...';

        var body = {
            monthly_electric_bill: parseFloat(billInput.value)
        };

        try {
            var response = await apiRequest('/api/quotations', {
                method: 'POST',
                body: body
            });
            var quotation = response.data || response;

            resultGrid.innerHTML =
                renderResultItem('System Size', quotation.system_kw ? Number(quotation.system_kw).toFixed(2) + ' kW' : '-') +
                renderResultItem('Projected Cost', fmtPeso(quotation.project_cost)) +
                renderResultItem('Monthly Savings', fmtPeso(quotation.estimated_monthly_savings)) +
                renderResultItem('ROI', quotation.roi_years ? Number(quotation.roi_years).toFixed(1) + ' years' : '-');

            resultBox.classList.add('show');
            showMsg(formMsg, 'success', 'Your pre-inspection estimate has been generated successfully.');
            form.reset();
            resultBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        } catch (error) {
            applyErrors(error.errors || {});
            showMsg(formMsg, 'error', error.message || 'Could not generate quotation. Please try again.');
        } finally {
            submitBtn.disabled = false;
            submitText.textContent = 'Generate My Quotation';
        }
    });
})();
</script>
@endpush
