@extends('layouts.app', ['title' => 'Final Quotation Summary'])

@section('content')
<style>
    /* ── Customer Final Quotation Summary Page (fq- prefix) ── */

    /* Hero */
    .fq-hero {
        background: linear-gradient(135deg, #fefce8 0%, #fef9c3 55%, #fef08a 100%);
        border-radius: 16px;
        padding: 36px 40px;
        margin-bottom: 32px;
        position: relative;
        overflow: hidden;
    }
    .fq-hero::after {
        content: '';
        position: absolute;
        right: -40px;
        bottom: -40px;
        width: 200px;
        height: 200px;
        border-radius: 50%;
        background: rgba(16,42,67,.05);
        pointer-events: none;
    }
    .fq-hero-eyebrow {
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 1.2px;
        text-transform: uppercase;
        color: #d4a017;
        margin: 0 0 8px;
    }
    .fq-hero-title {
        font-size: 30px;
        font-weight: 800;
        color: #102a43;
        margin: 0 0 10px;
        line-height: 1.2;
    }
    .fq-hero-title span { color: #d4a017; }
    .fq-hero-sub {
        font-size: 15px;
        color: #475569;
        max-width: 520px;
        margin: 0 0 16px;
        line-height: 1.6;
    }
    .fq-hero-meta {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        align-items: center;
    }
    .fq-hero-meta-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 12px;
        background: rgba(255,255,255,.7);
        border: 1px solid #e2e8f0;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        color: #475569;
    }
    @media (max-width: 768px) {
        .fq-hero { padding: 24px 20px; }
        .fq-hero-title { font-size: 22px; }
    }

    /* Status badges */
    .fq-status-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .4px;
        white-space: nowrap;
    }
    .fq-status-badge::before {
        content: '';
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: currentColor;
        opacity: .7;
    }
    .fq-s-pending   { background: #fef9c3; color: #a16207; }
    .fq-s-approved  { background: #dcfce7; color: #15803d; }
    .fq-s-completed { background: #d1fae5; color: #065f46; }
    .fq-s-rejected  { background: #fee2e2; color: #dc2626; }
    .fq-s-default   { background: #f1f5f9; color: #475569; }

    /* Card base */
    .fq-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 2px 12px rgba(0,0,0,.04);
        margin-bottom: 16px;
    }
    .fq-card:last-child { margin-bottom: 0; }
    .fq-card-header {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 20px 24px;
        border-bottom: 1px solid #f1f5f9;
    }
    .fq-card-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        background: linear-gradient(135deg, #102a43, #1e4068);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .fq-card-title {
        font-size: 16px;
        font-weight: 700;
        color: #102a43;
        margin: 0;
    }
    .fq-card-subtitle {
        font-size: 12px;
        color: #94a3b8;
        margin: 2px 0 0;
    }
    .fq-card-body { padding: 24px; }

    /* Spec grid (Card 1) */
    .fq-spec-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 12px;
    }
    .fq-spec-item {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 14px 16px;
    }
    .fq-spec-label {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .5px;
        color: #94a3b8;
        margin-bottom: 5px;
    }
    .fq-spec-value {
        font-size: 17px;
        font-weight: 800;
        color: #102a43;
        line-height: 1.2;
    }
    .fq-spec-value.accent { color: #d4a017; }
    .fq-spec-unit {
        font-size: 12px;
        font-weight: 500;
        color: #64748b;
        margin-top: 2px;
    }

    /* Tags row */
    .fq-tags {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        margin-top: 16px;
    }
    .fq-tag {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 5px 12px;
        background: #f0f9ff;
        border: 1px solid #bae6fd;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 600;
        color: #0369a1;
    }
    .fq-tag svg { flex-shrink: 0; }

    /* Cost breakdown (Card 2) */
    .fq-cost-table { width: 100%; border-collapse: collapse; }
    .fq-cost-row td {
        padding: 9px 0;
        border-bottom: 1px solid #f1f5f9;
        font-size: 14px;
        vertical-align: middle;
    }
    .fq-cost-row:last-child td { border-bottom: none; }
    .fq-cost-label { color: #475569; }
    .fq-cost-value { text-align: right; font-weight: 600; color: #1e293b; white-space: nowrap; }
    .fq-cost-subtotal td {
        padding: 11px 0;
        border-top: 1.5px solid #e2e8f0;
        border-bottom: 1.5px solid #e2e8f0;
        font-size: 14px;
        font-weight: 700;
        color: #102a43;
    }

    /* Final price highlight band */
    .fq-final-price {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
        margin-top: 18px;
        padding: 18px 20px;
        background: linear-gradient(135deg, #102a43 0%, #1e3a5f 100%);
        border-radius: 12px;
        color: #fff;
    }
    .fq-final-price-label {
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .7px;
        color: rgba(255,255,255,.7);
        margin-bottom: 4px;
    }
    .fq-final-price-amount {
        font-size: 28px;
        font-weight: 900;
        color: #f4c542;
        line-height: 1;
        letter-spacing: -1px;
    }
    .fq-final-price-note {
        font-size: 12px;
        color: rgba(255,255,255,.55);
        margin-top: 4px;
    }

    /* Line items section */
    .fq-li-section {
        margin-top: 22px;
    }
    .fq-li-section-title {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .6px;
        color: #94a3b8;
        margin: 0 0 10px;
    }
    .fq-li-table { width: 100%; border-collapse: collapse; }
    .fq-li-table thead th {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .5px;
        color: #94a3b8;
        padding: 6px 8px;
        text-align: left;
        border-bottom: 1.5px solid #e2e8f0;
    }
    .fq-li-table thead th:last-child { text-align: right; }
    .fq-li-table tbody td {
        padding: 9px 8px;
        border-bottom: 1px solid #f1f5f9;
        font-size: 13px;
        color: #1e293b;
        vertical-align: middle;
    }
    .fq-li-table tbody tr:last-child td { border-bottom: none; }
    .fq-li-table tbody td:last-child { text-align: right; font-weight: 600; white-space: nowrap; }
    .fq-li-qty { color: #64748b; font-size: 12px; }
    .fq-li-cat-badge {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 6px;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .3px;
        background: #f1f5f9;
        color: #475569;
    }

    /* ROI card (Card 3) */
    .fq-roi-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 12px;
        margin-bottom: 18px;
    }
    @media (max-width: 600px) { .fq-roi-grid { grid-template-columns: 1fr; } }
    .fq-roi-item {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 16px;
        text-align: center;
    }
    .fq-roi-item.featured {
        background: linear-gradient(135deg, #fefce8, #fef9c3);
        border-color: #fde68a;
    }
    .fq-roi-label {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .5px;
        color: #94a3b8;
        margin-bottom: 8px;
    }
    .fq-roi-item.featured .fq-roi-label { color: #92400e; }
    .fq-roi-value {
        font-size: 22px;
        font-weight: 900;
        color: #102a43;
        line-height: 1;
        margin-bottom: 4px;
    }
    .fq-roi-item.featured .fq-roi-value { color: #d4a017; }
    .fq-roi-unit {
        font-size: 12px;
        color: #94a3b8;
    }

    .fq-roi-bar-wrap {
        margin-top: 4px;
        background: #e2e8f0;
        border-radius: 6px;
        height: 8px;
        overflow: hidden;
    }
    .fq-roi-bar {
        height: 100%;
        border-radius: 6px;
        background: linear-gradient(90deg, #d4a017, #f4c542);
        max-width: 100%;
    }
    .fq-roi-note {
        font-size: 13px;
        color: #64748b;
        line-height: 1.6;
        padding: 14px 16px;
        background: #fffbeb;
        border: 1px solid #fde68a;
        border-radius: 10px;
        margin-top: 4px;
    }

    /* Remarks card */
    .fq-remarks-text {
        font-size: 14px;
        color: #334155;
        line-height: 1.7;
        background: #f8fafc;
        border-radius: 10px;
        padding: 14px 16px;
        border: 1px solid #f1f5f9;
        white-space: pre-wrap;
        margin: 0;
    }

    /* Action buttons */
    .fq-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-top: 24px;
    }
    .fq-btn-primary {
        flex: 1;
        min-width: 160px;
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
        text-decoration: none;
    }
    .fq-btn-primary:hover  { opacity: .9; transform: translateY(-1px); color: #fff; }
    .fq-btn-primary:active { transform: translateY(0); }
    .fq-btn-primary:disabled { opacity: .6; cursor: not-allowed; transform: none; }
    .fq-btn-secondary {
        padding: 13px 20px;
        background: transparent;
        border: 1.5px solid #cbd5e1;
        color: #475569;
        font-size: 14px;
        font-weight: 600;
        border-radius: 10px;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 7px;
        transition: all .15s;
        text-decoration: none;
    }
    .fq-btn-secondary:hover { background: #f1f5f9; color: #102a43; }

    /* Message boxes */
    .fq-msg {
        padding: 12px 16px;
        border-radius: 10px;
        font-size: 14px;
        margin-bottom: 18px;
        display: none;
        line-height: 1.5;
    }
    .fq-msg.show { display: block; }
    .fq-msg-success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
    .fq-msg-error   { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }

    /* Loading / error / not-found */
    .fq-loading {
        text-align: center;
        padding: 64px 24px;
        color: #94a3b8;
        font-size: 15px;
        display: none;
    }
    .fq-loading.show { display: block; }
    .fq-empty {
        text-align: center;
        padding: 64px 24px;
        flex-direction: column;
        align-items: center;
        gap: 14px;
        display: none;
    }
    .fq-empty.show { display: flex; }
    .fq-empty svg { opacity: .35; }
    .fq-empty-title { font-size: 16px; font-weight: 700; color: #334155; margin: 0; }
    .fq-empty-sub { font-size: 14px; color: #94a3b8; margin: 0; max-width: 360px; line-height: 1.6; }

    /* Divider */
    .fq-divider {
        display: flex;
        align-items: center;
        gap: 12px;
        margin: 22px 0;
        color: #94a3b8;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .6px;
    }
    .fq-divider::before, .fq-divider::after {
        content: '';
        flex: 1;
        height: 1px;
        background: #e2e8f0;
    }

    /* Content max-width */
    .fq-content { max-width: 780px; margin: 0 auto; }
</style>

{{-- ═══ PAGE HERO ═══ --}}
<div class="fq-hero">
    <p class="fq-hero-eyebrow">Solar System Proposal</p>
    <h1 class="fq-hero-title">Final Quotation <span>Summary</span></h1>
    <p class="fq-hero-sub">Review your custom solar system configuration, itemised cost breakdown, and projected return on investment before confirming.</p>
    <div class="fq-hero-meta">
        <span class="fq-hero-meta-chip" id="fq-chip-id" style="display:none;">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/></svg>
            <span id="fq-chip-id-text"></span>
        </span>
        <span id="fq-status-badge"></span>
    </div>
</div>

{{-- ═══ CONTENT ═══ --}}
<div class="fq-content">

    <div id="fq-loading" class="fq-loading">Loading your final quotation...</div>
    <div id="fq-page-msg" class="fq-msg" role="alert"></div>

    {{-- Empty / not-found state --}}
    <div id="fq-empty" class="fq-empty">
        <svg width="52" height="52" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.3">
            <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/>
            <rect x="9" y="3" width="6" height="4" rx="1"/>
            <path d="M9 12h6M9 16h4"/>
        </svg>
        <p class="fq-empty-title">Final Quotation Not Available</p>
        <p class="fq-empty-sub">The final quotation for this inspection has not been prepared yet. You will be notified once it is ready for your review.</p>
    </div>

    {{-- Main quotation content (hidden until loaded) --}}
    <div id="fq-main" style="display:none;">

        {{-- Action message --}}
        <div id="fq-action-msg" class="fq-msg" role="alert"></div>

        {{-- ── CARD 1: System Configuration ── --}}
        <div class="fq-card">
            <div class="fq-card-header">
                <div class="fq-card-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#d4a017" stroke-width="2">
                        <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
                    </svg>
                </div>
                <div>
                    <p class="fq-card-title">System Configuration</p>
                    <p class="fq-card-subtitle">Designed solar system specifications for your property</p>
                </div>
            </div>
            <div class="fq-card-body">
                <div class="fq-spec-grid" id="fq-spec-grid"></div>
                <div class="fq-tags" id="fq-tags-row"></div>
            </div>
        </div>

        {{-- ── CARD 2: Final Price ── --}}
        <div class="fq-card">
            <div class="fq-card-header">
                <div class="fq-card-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#d4a017" stroke-width="2">
                        <line x1="12" y1="1" x2="12" y2="23"/>
                        <path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/>
                    </svg>
                </div>
                <div>
                    <p class="fq-card-title">Final Price</p>
                    <p class="fq-card-subtitle">Itemised cost breakdown before your confirmation</p>
                </div>
            </div>
            <div class="fq-card-body">
                <table class="fq-cost-table" id="fq-cost-table"></table>
                <div class="fq-final-price" id="fq-final-price-band">
                    <div>
                        <div class="fq-final-price-label">Total Project Cost</div>
                        <div class="fq-final-price-amount" id="fq-total-cost">—</div>
                        <div class="fq-final-price-note">Inclusive of materials and labour</div>
                    </div>
                    <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#f4c542" stroke-width="1.5" opacity=".5">
                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                    </svg>
                </div>

                {{-- Line items section (only shown if line items exist) --}}
                <div class="fq-li-section" id="fq-li-section" style="display:none;">
                    <div class="fq-divider">Itemised Components</div>
                    <p class="fq-li-section-title">All installed components</p>
                    <table class="fq-li-table">
                        <thead>
                            <tr>
                                <th>Description</th>
                                <th>Category</th>
                                <th>Qty</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody id="fq-li-tbody"></tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ── CARD 3: Estimated ROI ── --}}
        <div class="fq-card">
            <div class="fq-card-header">
                <div class="fq-card-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#d4a017" stroke-width="2">
                        <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/>
                        <polyline points="17 6 23 6 23 12"/>
                    </svg>
                </div>
                <div>
                    <p class="fq-card-title">Estimated Return on Investment</p>
                    <p class="fq-card-subtitle">Projected savings based on your monthly electricity usage</p>
                </div>
            </div>
            <div class="fq-card-body">
                <div class="fq-roi-grid" id="fq-roi-grid"></div>
                <p class="fq-roi-note">
                    ROI is estimated based on your current electricity rate and average local solar irradiance. Actual savings may vary depending on weather conditions and energy consumption patterns.
                </p>
            </div>
        </div>

        {{-- ── Remarks (shown only if present) ── --}}
        <div class="fq-card" id="fq-remarks-card" style="display:none;">
            <div class="fq-card-header">
                <div class="fq-card-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#d4a017" stroke-width="2">
                        <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/>
                    </svg>
                </div>
                <div>
                    <p class="fq-card-title">Technician Remarks</p>
                    <p class="fq-card-subtitle">Additional notes from the site assessment</p>
                </div>
            </div>
            <div class="fq-card-body">
                <pre class="fq-remarks-text" id="fq-remarks-text"></pre>
            </div>
        </div>

        {{-- ── ACTION BUTTONS ── --}}
        <div class="fq-actions" id="fq-actions" style="display:none;">
            <button type="button" class="fq-btn-primary" id="fq-confirm-btn">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 13l4 4L19 7"/></svg>
                <span id="fq-confirm-text">Confirm Quotation</span>
            </button>
            <button type="button" class="fq-btn-secondary" id="fq-back-btn">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
                Back
            </button>
        </div>

    </div>{{-- /#fq-main --}}

</div>{{-- /.fq-content --}}

@endsection

@push('scripts')
<script>
(function () {
    'use strict';

    /* ── Utilities ── */
    function qs(s, ctx) { return (ctx || document).querySelector(s); }

    function escHtml(s) {
        return String(s || '')
            .replace(/&/g, '&amp;').replace(/</g, '&lt;')
            .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    function fmtPeso(val) {
        if (val === null || val === undefined || val === '' || isNaN(Number(val))) return '\u2014';
        return '\u20b1' + Number(val).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function fmtDate(str) {
        if (!str) return '\u2014';
        try {
            return new Date(str).toLocaleDateString('en-PH', { year: 'numeric', month: 'long', day: 'numeric' });
        } catch (e) { return str; }
    }

    function getCookie(name) {
        var m = document.cookie.match(new RegExp('(?:^|; )' + name.replace(/([.*+?^=!:${}()|[\]\/\\])/g, '\\$1') + '=([^;]*)'));
        return m ? decodeURIComponent(m[1]) : null;
    }

    async function ensureCsrf() {
        if (!getCookie('XSRF-TOKEN')) {
            await fetch('/sanctum/csrf-cookie', { credentials: 'same-origin' });
        }
    }

    async function apiRequest(endpoint, opts) {
        var method  = (opts && opts.method) || 'GET';
        var headers = { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' };
        if (method !== 'GET') {
            await ensureCsrf();
            headers['Content-Type']  = 'application/json';
            headers['X-XSRF-TOKEN'] = getCookie('XSRF-TOKEN') || '';
        }
        var resp    = await fetch(endpoint, {
            method:      method,
            credentials: 'same-origin',
            headers:     headers,
            body:        (opts && opts.body !== undefined) ? JSON.stringify(opts.body) : undefined,
        });
        var payload = await resp.json().catch(function () { return {}; });
        if (!resp.ok) {
            var err    = new Error(payload.message || 'Request failed.');
            err.status = resp.status;
            err.errors = payload.errors || {};
            throw err;
        }
        return payload;
    }

    /* ── DOM refs ── */
    var loadingEl    = qs('#fq-loading');
    var pageMsg      = qs('#fq-page-msg');
    var emptyEl      = qs('#fq-empty');
    var mainEl       = qs('#fq-main');
    var actionMsg    = qs('#fq-action-msg');
    var specGrid     = qs('#fq-spec-grid');
    var tagsRow      = qs('#fq-tags-row');
    var costTable    = qs('#fq-cost-table');
    var totalCostEl  = qs('#fq-total-cost');
    var liSection    = qs('#fq-li-section');
    var liTbody      = qs('#fq-li-tbody');
    var roiGrid      = qs('#fq-roi-grid');
    var remarksCard  = qs('#fq-remarks-card');
    var remarksText  = qs('#fq-remarks-text');
    var actionsEl    = qs('#fq-actions');
    var confirmBtn   = qs('#fq-confirm-btn');
    var confirmText  = qs('#fq-confirm-text');
    var backBtn      = qs('#fq-back-btn');
    var chipId       = qs('#fq-chip-id');
    var chipIdText   = qs('#fq-chip-id-text');
    var statusBadge  = qs('#fq-status-badge');

    /* ── Extract inspection_request_id from URL ── */
    var pathParts    = window.location.pathname.split('/');
    var inspectionId = pathParts[pathParts.length - 1] || null;

    /* ── Message helpers ── */
    function showMsg(el, type, text) {
        if (!el) return;
        el.className   = 'fq-msg show fq-msg-' + type;
        el.textContent = text;
    }
    function hideMsg(el) {
        if (el) { el.className = 'fq-msg'; el.textContent = ''; }
    }

    /* ── Status badge ── */
    function buildStatusBadge(status) {
        var s   = String(status || 'pending').toLowerCase();
        var map = { pending: 'fq-s-pending', approved: 'fq-s-approved', completed: 'fq-s-completed', rejected: 'fq-s-rejected' };
        var cls = map[s] || 'fq-s-default';
        var lbl = s.charAt(0).toUpperCase() + s.slice(1);
        return '<span class="fq-status-badge ' + cls + '">' + escHtml(lbl) + '</span>';
    }

    /* ── Spec grid ── */
    function buildSpecItem(label, value, unit, accent) {
        return '<div class="fq-spec-item">'
            + '<div class="fq-spec-label">' + escHtml(label) + '</div>'
            + '<div class="fq-spec-value' + (accent ? ' accent' : '') + '">' + value + '</div>'
            + (unit ? '<div class="fq-spec-unit">' + escHtml(unit) + '</div>' : '')
            + '</div>';
    }

    function renderSpecGrid(q) {
        var items = '';
        if (q.system_kw)    items += buildSpecItem('System Size',    Number(q.system_kw).toFixed(2), 'kilowatts (kW)', true);
        if (q.panel_quantity) items += buildSpecItem('Solar Panels', q.panel_quantity, q.panel_watts ? q.panel_watts + 'W per panel' : 'panels');
        if (q.panel_watts)  items += buildSpecItem('Panel Wattage',  q.panel_watts, 'watts per panel');
        if (q.daily_kwh)    items += buildSpecItem('Daily Usage',    Number(q.daily_kwh).toFixed(2), 'kWh/day');
        if (q.monthly_kwh)  items += buildSpecItem('Monthly Usage',  Number(q.monthly_kwh).toFixed(2), 'kWh/month');
        if (q.monthly_electric_bill) items += buildSpecItem('Monthly Bill', fmtPeso(q.monthly_electric_bill), 'based on your input');
        specGrid.innerHTML = items || '<p style="color:#94a3b8;font-size:13px;">System details not available.</p>';

        /* Tags row */
        var tags = '';
        if (q.pv_system_type) tags += '<span class="fq-tag"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>' + escHtml(q.pv_system_type.replace(/_/g,' ')) + '</span>';
        if (q.inverter_type)  tags += '<span class="fq-tag"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v16"/></svg>' + escHtml(q.inverter_type.replace(/_/g,' ')) + '</span>';
        if (q.with_battery)   tags += '<span class="fq-tag"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="18" height="10" rx="2"/><line x1="22" y1="11" x2="22" y2="13"/></svg>Battery Storage' + (q.battery_model ? ': ' + escHtml(q.battery_model) : '') + '</span>';
        tagsRow.innerHTML = tags;
    }

    /* ── Cost breakdown table ── */
    function costRow(label, value, isSub) {
        var cls = isSub ? 'fq-cost-subtotal' : 'fq-cost-row';
        return '<tr class="' + cls + '"><td class="fq-cost-label">' + escHtml(label) + '</td><td class="fq-cost-value">' + value + '</td></tr>';
    }

    function renderCostTable(q) {
        var html = '';
        if (q.panel_cost)        html += costRow('Solar Panels',        fmtPeso(q.panel_cost));
        if (q.inverter_cost)     html += costRow('Inverter',            fmtPeso(q.inverter_cost));
        if (q.battery_cost && q.with_battery) html += costRow('Battery Storage', fmtPeso(q.battery_cost));
        if (q.bos_cost)          html += costRow('Balance of System (BOS)', fmtPeso(q.bos_cost));
        if (q.materials_subtotal) html += costRow('Materials Subtotal', fmtPeso(q.materials_subtotal), true);
        if (q.labor_cost)        html += costRow('Labour & Installation', fmtPeso(q.labor_cost));
        costTable.innerHTML = html || '<tr class="fq-cost-row"><td colspan="2" style="color:#94a3b8;font-size:13px;">Cost breakdown not available.</td></tr>';
        totalCostEl.innerHTML = fmtPeso(q.project_cost);
    }

    /* ── Line items ── */
    function renderLineItems(lineItems) {
        if (!lineItems || lineItems.length === 0) { liSection.style.display = 'none'; return; }
        liSection.style.display = 'block';
        liTbody.innerHTML = lineItems.map(function (li) {
            var catLabel = String(li.category || '').replace(/_/g, ' ');
            catLabel = catLabel.charAt(0).toUpperCase() + catLabel.slice(1);
            return '<tr>'
                + '<td>' + escHtml(li.description || (li.pricingItem && li.pricingItem.name) || '\u2014') + '</td>'
                + '<td>' + (catLabel ? '<span class="fq-li-cat-badge">' + escHtml(catLabel) + '</span>' : '') + '</td>'
                + '<td class="fq-li-qty">' + escHtml(String(li.qty || '1')) + ' ' + escHtml(li.unit || '') + '</td>'
                + '<td>' + fmtPeso(li.total_amount) + '</td>'
                + '</tr>';
        }).join('');
    }

    /* ── ROI grid ── */
    function roiItem(label, value, unit, featured) {
        var barPct = 0;
        if (featured && typeof value === 'number' && !isNaN(value)) {
            barPct = Math.min(100, (value / 25) * 100); // scale: 25 years = 100%
        }
        return '<div class="fq-roi-item' + (featured ? ' featured' : '') + '">'
            + '<div class="fq-roi-label">' + escHtml(label) + '</div>'
            + '<div class="fq-roi-value">' + value + '</div>'
            + '<div class="fq-roi-unit">' + escHtml(unit) + '</div>'
            + (featured && barPct > 0
                ? '<div class="fq-roi-bar-wrap" style="margin-top:10px;"><div class="fq-roi-bar" style="width:' + barPct + '%;"></div></div>'
                : '')
            + '</div>';
    }

    function renderRoi(q) {
        var roiYears    = q.roi_years   ? Number(q.roi_years).toFixed(1)   : null;
        var monthlySav  = q.estimated_monthly_savings  ? fmtPeso(q.estimated_monthly_savings)  : '\u2014';
        var annualSav   = q.estimated_annual_savings   ? fmtPeso(q.estimated_annual_savings)   : '\u2014';
        var html = '';
        html += roiItem('Payback Period', roiYears ? roiYears + ' yrs' : '\u2014', 'years to recoup investment', true);
        html += roiItem('Monthly Savings', monthlySav, 'estimated per month', false);
        html += roiItem('Annual Savings',  annualSav,  'estimated per year',  false);
        roiGrid.innerHTML = html;
    }

    /* ── Render full quotation ── */
    function renderQuotation(q) {
        /* Hero meta */
        chipIdText.textContent = 'Quotation #' + q.id;
        chipId.style.display   = '';
        statusBadge.innerHTML  = buildStatusBadge(q.status);

        renderSpecGrid(q);
        renderCostTable(q);
        renderLineItems(q.line_items || q.lineItems || []);
        renderRoi(q);

        /* Remarks */
        if (q.remarks && q.remarks.trim()) {
            remarksText.textContent   = q.remarks;
            remarksCard.style.display = '';
        }

        /* Action buttons — shown when status is 'pending' (awaiting customer confirmation) */
        var status = String(q.status || 'pending').toLowerCase();
        if (status === 'pending') {
            actionsEl.style.display = '';
            confirmBtn.addEventListener('click', function () { handleConfirm(q.id); });
        } else {
            actionsEl.style.display = 'none';
        }

        mainEl.style.display = '';
    }

    /* ── Confirm action ── */
    async function handleConfirm(quotationId) {
        hideMsg(actionMsg);
        confirmBtn.disabled     = true;
        confirmText.textContent = 'Confirming...';
        try {
            /* Endpoint: customers confirm by approving — adjust if a dedicated approve route exists */
            await apiRequest('/api/quotations/' + quotationId + '/approve', { method: 'POST', body: {} });
            showMsg(actionMsg, 'success', 'Quotation confirmed successfully. Our team will be in touch to schedule your installation.');
            confirmBtn.style.display = 'none';
        } catch (err) {
            showMsg(actionMsg, 'error', err.message || 'Could not confirm. Please try again.');
            confirmBtn.disabled     = false;
            confirmText.textContent = 'Confirm Quotation';
        }
    }

    /* ── Back button ── */
    backBtn.addEventListener('click', function () { window.history.back(); });

    /* ── Load quotation ── */
    async function load() {
        if (!inspectionId || isNaN(Number(inspectionId))) {
            emptyEl.classList.add('show');
            return;
        }
        loadingEl.classList.add('show');
        try {
            var resp = await apiRequest('/api/customer/final-quotations/' + inspectionId);
            var q    = resp.data || resp;
            renderQuotation(q);
        } catch (err) {
            if (err.status === 404) {
                emptyEl.classList.add('show');
            } else {
                showMsg(pageMsg, 'error', err.message || 'Could not load final quotation. Please refresh.');
            }
        } finally {
            loadingEl.classList.remove('show');
        }
    }

    load();

})();
</script>
@endpush
