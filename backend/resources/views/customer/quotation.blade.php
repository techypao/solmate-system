@extends('layouts.app', ['title' => 'Generate Quotation & ROI'])

@section('content')
<style>
    /* ── Customer Quotation Page (cq- prefix) ── */

    /* Page hero */
    .cq-hero {
        background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 60%, #dbeafe 100%);
        border-radius: 16px;
        padding: 36px 40px;
        margin-bottom: 32px;
        position: relative;
        overflow: hidden;
    }
    .cq-hero::after {
        content: '';
        position: absolute;
        right: -40px;
        bottom: -40px;
        width: 220px;
        height: 220px;
        border-radius: 50%;
        background: rgba(212, 160, 23, 0.1);
        pointer-events: none;
    }
    .cq-hero-eyebrow {
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 1.2px;
        text-transform: uppercase;
        color: #d4a017;
        margin: 0 0 8px;
    }
    .cq-hero-title {
        font-size: 30px;
        font-weight: 800;
        color: #102a43;
        margin: 0 0 10px;
        line-height: 1.2;
    }
    .cq-hero-title span { color: #d4a017; }
    .cq-hero-sub {
        font-size: 15px;
        color: #475569;
        max-width: 520px;
        margin: 0;
        line-height: 1.6;
    }
    .cq-hero-steps {
        display: flex;
        gap: 24px;
        flex-wrap: wrap;
        margin-top: 24px;
    }
    .cq-hero-step {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
        color: #334155;
    }
    .cq-hero-step-num {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background: #d4a017;
        color: #fff;
        font-size: 12px;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    /* Two-column layout */
    .cq-layout {
        display: grid;
        grid-template-columns: 1fr 340px;
        gap: 24px;
        align-items: start;
    }
    @media (max-width: 768px) {
        .cq-layout { grid-template-columns: 1fr; }
        .cq-hero { padding: 24px 20px; }
        .cq-hero-title { font-size: 22px; }
    }

    /* Main quotation card */
    .cq-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 2px 12px rgba(0,0,0,.04);
    }
    .cq-card-header {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 18px 24px;
        border-bottom: 1px solid #f1f5f9;
    }
    .cq-card-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        background: linear-gradient(135deg, #102a43, #1e4068);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .cq-card-icon svg { color: #d4a017; }
    .cq-card-title {
        font-size: 16px;
        font-weight: 700;
        color: #102a43;
        margin: 0;
    }
    .cq-card-subtitle {
        font-size: 12px;
        color: #94a3b8;
        margin: 2px 0 0;
    }
    .cq-card-body { padding: 24px; }

    /* Form elements */
    .cq-field { margin-bottom: 20px; }
    .cq-label {
        display: block;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .6px;
        color: #64748b;
        margin-bottom: 8px;
    }
    .cq-input-wrap {
        position: relative;
    }
    .cq-input-prefix {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 15px;
        font-weight: 600;
        color: #64748b;
        pointer-events: none;
        user-select: none;
    }
    .cq-input {
        width: 100%;
        padding: 13px 16px 13px 36px;
        border: 1.5px solid #cbd5e1;
        border-radius: 10px;
        font-size: 16px;
        font-weight: 600;
        color: #1e293b;
        background: #fff;
        box-sizing: border-box;
        transition: border-color .2s, box-shadow .2s;
        -moz-appearance: textfield;
        appearance: textfield;
    }
    .cq-input::-webkit-outer-spin-button,
    .cq-input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
    .cq-input:focus {
        outline: none;
        border-color: #d4a017;
        box-shadow: 0 0 0 3px rgba(212,160,23,.12);
    }
    .cq-input.has-error { border-color: #ef4444; }
    .cq-field-hint { font-size: 12px; color: #94a3b8; margin-top: 6px; }
    .cq-field-error { font-size: 12px; color: #dc2626; margin-top: 6px; display: none; }
    .cq-field-error.show { display: block; }

    .cq-textarea {
        width: 100%;
        padding: 12px 16px;
        border: 1.5px solid #cbd5e1;
        border-radius: 10px;
        font-size: 14px;
        color: #1e293b;
        background: #fff;
        box-sizing: border-box;
        resize: vertical;
        min-height: 80px;
        font-family: inherit;
        transition: border-color .2s, box-shadow .2s;
    }
    .cq-textarea:focus {
        outline: none;
        border-color: #d4a017;
        box-shadow: 0 0 0 3px rgba(212,160,23,.12);
    }

    /* Submit button */
    .cq-submit-btn {
        width: 100%;
        padding: 14px 24px;
        background: linear-gradient(135deg, #d4a017, #b8880f);
        color: #fff;
        font-size: 15px;
        font-weight: 700;
        border: none;
        border-radius: 10px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: opacity .2s, transform .15s;
        letter-spacing: .3px;
        margin-top: 4px;
    }
    .cq-submit-btn:hover { opacity: .92; transform: translateY(-1px); }
    .cq-submit-btn:active { transform: translateY(0); opacity: 1; }
    .cq-submit-btn:disabled { opacity: .6; cursor: not-allowed; transform: none; }

    /* Message boxes */
    .cq-msg {
        padding: 12px 16px;
        border-radius: 10px;
        font-size: 14px;
        margin-bottom: 18px;
        display: none;
        line-height: 1.5;
    }
    .cq-msg.show { display: block; }
    .cq-msg-success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
    .cq-msg-error { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }

    /* Info divider */
    .cq-divider {
        display: flex;
        align-items: center;
        gap: 12px;
        margin: 20px 0;
        color: #94a3b8;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .6px;
    }
    .cq-divider::before, .cq-divider::after {
        content: '';
        flex: 1;
        height: 1px;
        background: #e2e8f0;
    }

    /* Computed preview */
    .cq-preview-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
        margin-top: 16px;
    }
    @media (max-width: 480px) { .cq-preview-grid { grid-template-columns: 1fr; } }
    .cq-preview-item {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 14px 16px;
    }
    .cq-preview-label {
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .5px;
        color: #94a3b8;
        margin-bottom: 4px;
    }
    .cq-preview-value {
        font-size: 18px;
        font-weight: 700;
        color: #102a43;
    }
    .cq-preview-value.highlight { color: #d4a017; }

    /* Right panel */
    .cq-panel {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 2px 12px rgba(0,0,0,.04);
    }
    .cq-panel-header {
        padding: 16px 20px;
        background: #102a43;
        color: #fff;
    }
    .cq-panel-title {
        font-size: 14px;
        font-weight: 700;
        margin: 0 0 2px;
    }
    .cq-panel-sub {
        font-size: 12px;
        color: rgba(255,255,255,.65);
        margin: 0;
    }
    .cq-panel-body { padding: 20px; }
    .cq-info-row {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 10px 0;
        border-bottom: 1px solid #f1f5f9;
    }
    .cq-info-row:last-child { border-bottom: none; }
    .cq-info-icon {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        margin-top: 2px;
    }
    .cq-info-icon-gold { background: #fef3c7; }
    .cq-info-icon-blue { background: #dbeafe; }
    .cq-info-icon-green { background: #dcfce7; }
    .cq-info-body { flex: 1; }
    .cq-info-label {
        font-size: 13px;
        font-weight: 600;
        color: #102a43;
        margin: 0 0 3px;
    }
    .cq-info-desc {
        font-size: 12px;
        color: #64748b;
        margin: 0;
        line-height: 1.5;
    }

    /* Quotation history section */
    .cq-history-section { margin-top: 32px; }
    .cq-history-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 16px;
    }
    .cq-history-title {
        font-size: 18px;
        font-weight: 700;
        color: #102a43;
    }
    .cq-filter-chips { display: flex; gap: 8px; flex-wrap: wrap; }
    .cq-chip {
        padding: 5px 14px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        background: #f1f5f9;
        color: #475569;
        border: 1.5px solid transparent;
        cursor: pointer;
        transition: all .15s;
    }
    .cq-chip:hover, .cq-chip.active { background: #102a43; color: #fff; border-color: #102a43; }

    /* Quotation cards */
    .cq-q-grid { display: grid; gap: 14px; }
    .cq-q-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 18px 22px;
        transition: box-shadow .2s;
    }
    .cq-q-card:hover { box-shadow: 0 4px 20px rgba(0,0,0,.07); }
    .cq-q-card-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 14px;
    }
    .cq-q-id { font-size: 15px; font-weight: 700; color: #102a43; }
    .cq-q-date { font-size: 12px; color: #94a3b8; margin-top: 2px; }
    .cq-q-badges { display: flex; gap: 6px; flex-wrap: wrap; }
    .cq-badge {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .4px;
    }
    .cq-badge-initial { background: #e0f2fe; color: #0284c7; }
    .cq-badge-final { background: #dcfce7; color: #15803d; }
    .cq-badge-pending { background: #fef9c3; color: #a16207; }
    .cq-badge-approved { background: #dcfce7; color: #15803d; }
    .cq-badge-completed { background: #d1fae5; color: #065f46; }
    .cq-badge-rejected { background: #fee2e2; color: #dc2626; }
    .cq-badge-default { background: #f1f5f9; color: #475569; }

    .cq-q-stats {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
        gap: 10px;
        margin-bottom: 14px;
    }
    .cq-q-stat { background: #f8fafc; border-radius: 8px; padding: 10px 12px; }
    .cq-q-stat-label {
        font-size: 10px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .5px;
        color: #94a3b8;
        margin-bottom: 3px;
    }
    .cq-q-stat-val { font-size: 15px; font-weight: 700; color: #102a43; }
    .cq-q-actions { display: flex; gap: 8px; align-items: center; }
    .cq-btn-ghost {
        padding: 8px 16px;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 600;
        background: transparent;
        border: 1.5px solid #cbd5e1;
        color: #475569;
        cursor: pointer;
        transition: all .15s;
    }
    .cq-btn-ghost:hover { background: #f1f5f9; }
    .cq-q-detail {
        margin-top: 14px;
        border-top: 1px solid #f1f5f9;
        padding-top: 14px;
        display: none;
    }
    .cq-detail-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0; }
    @media (max-width: 480px) { .cq-detail-grid { grid-template-columns: 1fr; } }
    .cq-detail-row {
        display: grid;
        grid-template-columns: 150px 1fr;
        padding: 7px 0;
        border-bottom: 1px solid #f8fafc;
        gap: 8px;
    }
    .cq-detail-row:last-child { border-bottom: none; }
    .cq-detail-label { font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .4px; color: #94a3b8; }
    .cq-detail-value { font-size: 13px; color: #1e293b; font-weight: 500; }

    /* Loading/empty states */
    .cq-loading { text-align: center; padding: 32px; color: #94a3b8; font-size: 14px; display: none; }
    .cq-loading.show { display: block; }
    .cq-empty { text-align: center; padding: 48px 24px; color: #94a3b8; display: none; }
    .cq-empty.show { display: flex; flex-direction: column; align-items: center; gap: 10px; }
    .cq-empty svg { opacity: .4; }
    .cq-empty p { font-size: 14px; margin: 0; }
</style>

{{-- ═══ PAGE HERO ═══ --}}
<div class="cq-hero">
    <p class="cq-hero-eyebrow">Solar System Sizing</p>
    <h1 class="cq-hero-title">Generate Quotation <span>&amp;</span> ROI</h1>
    <p class="cq-hero-sub">Enter your average monthly electricity bill to instantly receive a solar system sizing estimate, projected cost, and return on investment analysis.</p>
    <div class="cq-hero-steps">
        <div class="cq-hero-step">
            <span class="cq-hero-step-num">1</span>
            <span>Enter your monthly bill</span>
        </div>
        <div class="cq-hero-step">
            <span class="cq-hero-step-num">2</span>
            <span>Get instant sizing estimate</span>
        </div>
        <div class="cq-hero-step">
            <span class="cq-hero-step-num">3</span>
            <span>Request site inspection</span>
        </div>
    </div>
</div>

{{-- ═══ TWO-COLUMN LAYOUT ═══ --}}
<div class="cq-layout">

    {{-- ── LEFT: Main quotation card ── --}}
    <div>
        <div class="cq-card">
            <div class="cq-card-header">
                <div class="cq-card-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" color="#d4a017">
                        <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
                    </svg>
                </div>
                <div>
                    <p class="cq-card-title">Initial Quotation</p>
                    <p class="cq-card-subtitle">Automated solar sizing &amp; ROI estimate</p>
                </div>
            </div>

            <div class="cq-card-body">
                <div id="cq-form-msg" class="cq-msg"></div>

                <form id="cq-quotation-form">
                    <div class="cq-field">
                        <label class="cq-label" for="cq-bill-input">Monthly Electric Bill</label>
                        <div class="cq-input-wrap">
                            <span class="cq-input-prefix">&#8369;</span>
                            <input
                                id="cq-bill-input"
                                class="cq-input"
                                type="number"
                                name="monthly_electric_bill"
                                min="1"
                                step="0.01"
                                placeholder="0.00"
                                required
                                autocomplete="off"
                            >
                        </div>
                        <p class="cq-field-hint">Enter your average monthly electricity bill in Philippine Peso.</p>
                        <div class="cq-field-error" id="cq-bill-error"></div>
                    </div>

                    <div class="cq-field">
                        <label class="cq-label" for="cq-remarks-input">Remarks <span style="font-weight:400;text-transform:none;letter-spacing:0;color:#94a3b8;">(optional)</span></label>
                        <textarea
                            id="cq-remarks-input"
                            class="cq-textarea"
                            name="remarks"
                            placeholder="Any additional notes, questions, or special requirements..."
                            rows="3"
                        ></textarea>
                        <div class="cq-field-error" id="cq-remarks-error"></div>
                    </div>

                    <button type="submit" class="cq-submit-btn" id="cq-generate-btn">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/>
                        </svg>
                        <span id="cq-btn-text">Generate My Quotation</span>
                    </button>
                </form>

                {{-- Result preview (shown after successful generation) --}}
                <div id="cq-result-panel" style="display:none;">
                    <div class="cq-divider">Your estimate</div>
                    <div class="cq-preview-grid" id="cq-preview-grid"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── RIGHT: Info panel ── --}}
    <div>
        <div class="cq-panel">
            <div class="cq-panel-header">
                <p class="cq-panel-title">How It Works</p>
                <p class="cq-panel-sub">Your solar journey in 3 steps</p>
            </div>
            <div class="cq-panel-body">
                <div class="cq-info-row">
                    <div class="cq-info-icon cq-info-icon-gold">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#d4a017" stroke-width="2"><path d="M9 12h6M9 16h6M9 8h3M5 4h14a1 1 0 011 1v14a1 1 0 01-1 1H5a1 1 0 01-1-1V5a1 1 0 011-1z"/></svg>
                    </div>
                    <div class="cq-info-body">
                        <p class="cq-info-label">Initial Quotation</p>
                        <p class="cq-info-desc">We compute your estimated system size, total project cost, and monthly / annual savings based on your electricity bill.</p>
                    </div>
                </div>
                <div class="cq-info-row">
                    <div class="cq-info-icon cq-info-icon-blue">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="M21 21l-3.5-3.5"/></svg>
                    </div>
                    <div class="cq-info-body">
                        <p class="cq-info-label">Site Inspection</p>
                        <p class="cq-info-desc">Our technicians visit your property to assess the site and verify the initial estimate before preparing a final proposal.</p>
                    </div>
                </div>
                <div class="cq-info-row">
                    <div class="cq-info-icon cq-info-icon-green">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2"><path d="M9 11l3 3L22 4M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg>
                    </div>
                    <div class="cq-info-body">
                        <p class="cq-info-label">Final Quotation</p>
                        <p class="cq-info-desc">After inspection, a detailed final quotation with itemized components is provided for your approval before installation.</p>
                    </div>
                </div>

                <div style="margin-top: 20px; padding: 16px; background: #fffbeb; border: 1px solid #fde68a; border-radius: 10px;">
                    <p style="font-size:13px;font-weight:700;color:#92400e;margin:0 0 6px;">What is ROI?</p>
                    <p style="font-size:12px;color:#78350f;margin:0;line-height:1.6;">Return on Investment (ROI) estimates how many years it takes to recover your solar installation cost through electricity savings.</p>
                </div>
            </div>
        </div>
    </div>

</div>{{-- end .cq-layout --}}

{{-- ═══ QUOTATION HISTORY ═══ --}}
<div class="cq-history-section">
    <div class="cq-history-header">
        <h2 class="cq-history-title">My Quotations</h2>
        <div class="cq-filter-chips">
            <button class="cq-chip active" data-filter="all">All</button>
            <button class="cq-chip" data-filter="initial">Initial</button>
            <button class="cq-chip" data-filter="final">Final</button>
        </div>
    </div>

    <div id="cq-history-loading" class="cq-loading">Loading your quotations...</div>
    <div id="cq-history-msg" class="cq-msg"></div>
    <div id="cq-history-list" class="cq-q-grid"></div>
    <div id="cq-history-empty" class="cq-empty">
        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5"><path d="M9 12h6M9 16h6M9 8h3M5 4h14a1 1 0 011 1v14a1 1 0 01-1 1H5a1 1 0 01-1-1V5a1 1 0 011-1z"/></svg>
        <p>No quotations yet. Generate your first one above.</p>
    </div>
</div>

@endsection

@push('scripts')
<script>
(function () {
    'use strict';

    /* ── Utilities ── */
    function qs(s, r) { return (r || document).querySelector(s); }
    function setVisible(el, show) { if (el) el.style.display = show ? '' : 'none'; }

    function showMsg(el, type, text) {
        if (!el) return;
        el.className = 'cq-msg show cq-msg-' + type;
        el.textContent = text;
    }
    function hideMsg(el) { if (el) { el.className = 'cq-msg'; el.textContent = ''; } }

    function escHtml(s) {
        return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    function fmtPeso(val) {
        if (val === null || val === undefined || isNaN(Number(val))) return '-';
        return '&#8369;' + Number(val).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function fmtDate(str) {
        if (!str) return '-';
        try { return new Date(str).toLocaleDateString('en-PH', { year: 'numeric', month: 'short', day: 'numeric' }); }
        catch(e) { return str; }
    }

    function getCookie(name) {
        var m = document.cookie.match(new RegExp('(?:^|; )' + name.replace(/([.*+?^=!:${}()|[\]\/\\])/g,'\\$1') + '=([^;]*)'));
        return m ? decodeURIComponent(m[1]) : null;
    }

    async function ensureCsrf() {
        if (!getCookie('XSRF-TOKEN')) {
            await fetch('/sanctum/csrf-cookie', { credentials: 'same-origin' });
        }
    }

    async function apiRequest(endpoint, opts) {
        var method = (opts && opts.method) || 'GET';
        var headers = { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' };
        if (method !== 'GET') {
            await ensureCsrf();
            headers['Content-Type'] = 'application/json';
            headers['X-XSRF-TOKEN'] = getCookie('XSRF-TOKEN') || '';
        }
        var resp = await fetch(endpoint, {
            method: method,
            credentials: 'same-origin',
            headers: headers,
            body: (opts && opts.body !== undefined) ? JSON.stringify(opts.body) : undefined,
        });
        var payload = await resp.json().catch(function () { return {}; });
        if (!resp.ok) {
            var err = new Error(payload.message || 'Request failed.');
            err.status = resp.status;
            err.errors = payload.errors || {};
            throw err;
        }
        return payload;
    }

    function clearFieldErrors() {
        var errEls = document.querySelectorAll('.cq-field-error');
        errEls.forEach(function (el) { el.textContent = ''; el.classList.remove('show'); });
        var inputs = document.querySelectorAll('.cq-input.has-error, .cq-textarea.has-error');
        inputs.forEach(function (el) { el.classList.remove('has-error'); });
    }

    function applyFieldErrors(errors) {
        var map = { monthly_electric_bill: 'cq-bill-error', remarks: 'cq-remarks-error' };
        Object.keys(errors).forEach(function (key) {
            var elId = map[key];
            if (elId) {
                var el = qs('#' + elId);
                if (el) { el.textContent = Array.isArray(errors[key]) ? errors[key][0] : errors[key]; el.classList.add('show'); }
            }
        });
    }

    /* ── Generate Quotation Form ── */
    var form       = qs('#cq-quotation-form');
    var formMsg    = qs('#cq-form-msg');
    var billInput  = qs('#cq-bill-input');
    var remarksInput = qs('#cq-remarks-input');
    var generateBtn = qs('#cq-generate-btn');
    var btnText    = qs('#cq-btn-text');
    var resultPanel = qs('#cq-result-panel');
    var previewGrid = qs('#cq-preview-grid');

    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        clearFieldErrors();
        hideMsg(formMsg);
        generateBtn.disabled = true;
        btnText.textContent = 'Generating...';

        var body = { monthly_electric_bill: parseFloat(billInput.value) };
        var remarks = (remarksInput.value || '').trim();
        if (remarks) body.remarks = remarks;

        try {
            var resp = await apiRequest('/api/quotations', { method: 'POST', body: body });
            var q = resp.data || resp;

            showMsg(formMsg, 'success', 'Your initial quotation has been generated successfully.');

            /* show preview */
            var systemKw  = q.system_kw   ? Number(q.system_kw).toFixed(2) + ' kW' : '-';
            var cost      = fmtPeso(q.project_cost);
            var savings   = fmtPeso(q.estimated_monthly_savings);
            var roi       = q.roi_years   ? Number(q.roi_years).toFixed(1) + ' years' : '-';

            previewGrid.innerHTML =
                cqPreviewItem('System Size', systemKw, false) +
                cqPreviewItem('Project Cost', cost, true) +
                cqPreviewItem('Monthly Savings', savings, false) +
                cqPreviewItem('ROI Period', roi, true);

            setVisible(resultPanel, true);
            form.reset();
            resultPanel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });

            await loadHistory();
        } catch (err) {
            applyFieldErrors(err.errors || {});
            showMsg(formMsg, 'error', err.message || 'Could not generate quotation. Please try again.');
        } finally {
            generateBtn.disabled = false;
            btnText.textContent = 'Generate My Quotation';
        }
    });

    function cqPreviewItem(label, value, highlight) {
        return '<div class="cq-preview-item">'
            + '<div class="cq-preview-label">' + escHtml(label) + '</div>'
            + '<div class="cq-preview-value' + (highlight ? ' highlight' : '') + '">' + value + '</div>'
            + '</div>';
    }

    /* ── Quotation History ── */
    var allQuotations = [];
    var activeFilter  = 'all';

    var historyLoading = qs('#cq-history-loading');
    var historyMsg     = qs('#cq-history-msg');
    var historyList    = qs('#cq-history-list');
    var historyEmpty   = qs('#cq-history-empty');

    var chips = document.querySelectorAll('.cq-chip');
    chips.forEach(function (chip) {
        chip.addEventListener('click', function () {
            chips.forEach(function (c) { c.classList.remove('active'); });
            chip.classList.add('active');
            activeFilter = chip.dataset.filter;
            renderHistory();
        });
    });

    function typeBadge(type) {
        var t = String(type || 'initial').toLowerCase();
        var cls = (t === 'final') ? 'cq-badge-final' : 'cq-badge-initial';
        var label = t.charAt(0).toUpperCase() + t.slice(1);
        return '<span class="cq-badge ' + cls + '">' + escHtml(label) + '</span>';
    }

    function statusBadge(status) {
        var s = String(status || 'pending').toLowerCase();
        var classMap = { pending:'cq-badge-pending', approved:'cq-badge-approved', completed:'cq-badge-completed', rejected:'cq-badge-rejected' };
        var cls = classMap[s] || 'cq-badge-default';
        var label = s.charAt(0).toUpperCase() + s.slice(1);
        return '<span class="cq-badge ' + cls + '">' + escHtml(label) + '</span>';
    }

    function renderQuotationDetail(q) {
        var rows = [
            ['Monthly Bill', fmtPeso(q.monthly_electric_bill)],
            ['Rate / kWh', q.rate_per_kwh ? '&#8369;' + q.rate_per_kwh : '-'],
            ['Panel Qty', q.panel_quantity || '-'],
            ['Panel Watts', q.panel_watts ? q.panel_watts + ' W' : '-'],
            ['With Battery', q.with_battery ? 'Yes' : 'No'],
            ['System Type', q.pv_system_type || '-'],
            ['Inverter Type', q.inverter_type || '-'],
            ['Materials Cost', fmtPeso(q.materials_subtotal)],
            ['Labor Cost', fmtPeso(q.labor_cost)],
            ['Annual Savings', fmtPeso(q.estimated_annual_savings)],
            ['Remarks', q.remarks || '-'],
        ];
        return '<div class="cq-detail-grid">' + rows.map(function (r) {
            return '<div class="cq-detail-row">'
                + '<span class="cq-detail-label">' + escHtml(r[0]) + '</span>'
                + '<span class="cq-detail-value">' + r[1] + '</span>'
                + '</div>';
        }).join('') + '</div>';
    }

    window.cqToggleDetail = function (id) {
        var el = qs('#cq-detail-' + id);
        if (!el) return;
        var hidden = el.style.display === 'none' || !el.style.display;
        el.style.display = hidden ? 'block' : 'none';
        var btn = qs('[data-toggle-id="' + id + '"]');
        if (btn) btn.textContent = hidden ? 'Hide Details' : 'View Details';
    };

    function renderHistory() {
        var filtered = activeFilter === 'all' ? allQuotations : allQuotations.filter(function (q) {
            var type = String(q.quotation_type || '').toLowerCase();
            return type === activeFilter;
        });

        if (filtered.length === 0) {
            historyList.innerHTML = '';
            historyEmpty.classList.add('show');
            return;
        }
        historyEmpty.classList.remove('show');

        historyList.innerHTML = filtered.map(function (q) {
            var systemKw = q.system_kw ? Number(q.system_kw).toFixed(2) + ' kW' : '-';
            var cost     = fmtPeso(q.project_cost);
            var savings  = fmtPeso(q.estimated_monthly_savings);
            var roi      = q.roi_years ? Number(q.roi_years).toFixed(1) + ' yrs' : '-';

            return '<div class="cq-q-card">'
                + '<div class="cq-q-card-header">'
                +   '<div>'
                +     '<div class="cq-q-id">Quote #' + escHtml(q.id) + '</div>'
                +     '<div class="cq-q-date">Generated ' + fmtDate(q.created_at) + '</div>'
                +   '</div>'
                +   '<div class="cq-q-badges">' + typeBadge(q.quotation_type) + statusBadge(q.status) + '</div>'
                + '</div>'
                + '<div class="cq-q-stats">'
                +   '<div class="cq-q-stat"><div class="cq-q-stat-label">System Size</div><div class="cq-q-stat-val">' + escHtml(systemKw) + '</div></div>'
                +   '<div class="cq-q-stat"><div class="cq-q-stat-label">Project Cost</div><div class="cq-q-stat-val">' + cost + '</div></div>'
                +   '<div class="cq-q-stat"><div class="cq-q-stat-label">Monthly Savings</div><div class="cq-q-stat-val">' + savings + '</div></div>'
                +   '<div class="cq-q-stat"><div class="cq-q-stat-label">ROI Period</div><div class="cq-q-stat-val">' + escHtml(roi) + '</div></div>'
                + '</div>'
                + '<div class="cq-q-actions">'
                +   '<button class="cq-btn-ghost" data-toggle-id="' + escHtml(q.id) + '" onclick="cqToggleDetail(' + escHtml(q.id) + ')">View Details</button>'
                + '</div>'
                + '<div class="cq-q-detail" id="cq-detail-' + escHtml(q.id) + '">'
                +   renderQuotationDetail(q)
                + '</div>'
                + '</div>';
        }).join('');
    }

    async function loadHistory() {
        historyLoading.classList.add('show');
        hideMsg(historyMsg);
        try {
            var data = await apiRequest('/api/quotations');
            allQuotations = Array.isArray(data) ? data : [];
            renderHistory();
        } catch (err) {
            showMsg(historyMsg, 'error', err.message || 'Could not load quotation history.');
        } finally {
            historyLoading.classList.remove('show');
        }
    }

    loadHistory();
})();
</script>
@endpush
