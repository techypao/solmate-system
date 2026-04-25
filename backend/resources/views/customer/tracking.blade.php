@extends('layouts.app', ['title' => 'Service Tracking'])

@section('content')
<style>
    /* ── Customer Service Tracking Page (trk- prefix) ── */

    /* Hero */
    .trk-hero {
        background: linear-gradient(135deg, #f5f3ff 0%, #ede9fe 55%, #ddd6fe 100%);
        border-radius: 16px;
        padding: 36px 40px;
        margin-bottom: 32px;
        position: relative;
        overflow: hidden;
    }
    .trk-hero::after {
        content: '';
        position: absolute;
        right: -40px;
        bottom: -40px;
        width: 220px;
        height: 220px;
        border-radius: 50%;
        background: rgba(212,160,23,.08);
        pointer-events: none;
    }
    .trk-hero-eyebrow {
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 1.2px;
        text-transform: uppercase;
        color: #d4a017;
        margin: 0 0 8px;
    }
    .trk-hero-title {
        font-size: 30px;
        font-weight: 800;
        color: #102a43;
        margin: 0 0 10px;
        line-height: 1.2;
    }
    .trk-hero-title span { color: #d4a017; }
    .trk-hero-sub {
        font-size: 15px;
        color: #475569;
        max-width: 540px;
        margin: 0;
        line-height: 1.6;
    }
    @media (max-width: 768px) {
        .trk-hero { padding: 24px 20px; }
        .trk-hero-title { font-size: 22px; }
    }

    /* ── Shared card base ── */
    .trk-panel {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        padding: 22px;
        box-shadow: 0 2px 12px rgba(0,0,0,.04);
    }
    .trk-tabs-wrap {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 22px;
        flex-wrap: wrap;
    }
    .trk-tabs {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 6px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 999px;
        overflow-x: auto;
        max-width: 100%;
        -webkit-overflow-scrolling: touch;
    }
    .trk-tab-btn {
        appearance: none;
        border: 0;
        background: transparent;
        color: #475569;
        font-size: 14px;
        font-weight: 700;
        padding: 10px 18px;
        border-radius: 999px;
        cursor: pointer;
        white-space: nowrap;
        transition: background .2s ease, color .2s ease, box-shadow .2s ease;
    }
    .trk-tab-btn:hover {
        color: #102a43;
        background: rgba(255,255,255,.75);
    }
    .trk-tab-btn.active {
        background: linear-gradient(135deg, #102a43, #1e4068);
        color: #fff;
        box-shadow: 0 10px 24px rgba(16,42,67,.18);
    }
    .trk-tab-count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 22px;
        height: 22px;
        padding: 0 7px;
        margin-left: 8px;
        border-radius: 999px;
        background: rgba(255,255,255,.16);
        font-size: 12px;
        font-weight: 800;
    }
    .trk-tab-btn:not(.active) .trk-tab-count {
        background: #e2e8f0;
        color: #475569;
    }
    .trk-panel-note {
        font-size: 13px;
        color: #64748b;
        margin: 0;
        line-height: 1.5;
    }
    .trk-section-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }
    .trk-section-kicker {
        font-size: 11px;
        font-weight: 800;
        letter-spacing: .9px;
        text-transform: uppercase;
        color: #d4a017;
        margin: 0 0 6px;
    }
    .trk-section-title {
        font-size: 22px;
        font-weight: 800;
        color: #102a43;
        margin: 0 0 4px;
    }
    .trk-section-sub {
        font-size: 14px;
        color: #64748b;
        margin: 0;
        line-height: 1.6;
    }
    .trk-section-pill {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 14px;
        border-radius: 999px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        color: #334155;
        font-size: 13px;
        font-weight: 700;
        white-space: nowrap;
    }
    .trk-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 2px 12px rgba(0,0,0,.04);
        margin-bottom: 16px;
    }
    .trk-card:last-child { margin-bottom: 0; }
    .trk-card-header {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 18px 24px;
        border-bottom: 1px solid #f1f5f9;
    }
    .trk-card-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        background: linear-gradient(135deg, #102a43, #1e4068);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .trk-card-title {
        font-size: 16px;
        font-weight: 700;
        color: #102a43;
        margin: 0;
    }
    .trk-card-subtitle {
        font-size: 12px;
        color: #94a3b8;
        margin: 2px 0 0;
    }
    .trk-card-body { padding: 24px; }

    /* ── Request group wrapper ── */
    .trk-request-group {
        margin-bottom: 40px;
    }
    .trk-request-group:last-child { margin-bottom: 0; }

    /* ── Request header band ── */
    .trk-req-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 16px;
        flex-wrap: wrap;
    }
    .trk-req-id {
        font-size: 17px;
        font-weight: 800;
        color: #102a43;
    }
    .trk-req-type {
        font-size: 13px;
        font-weight: 500;
        color: #64748b;
        margin-top: 2px;
    }
    .trk-req-date {
        font-size: 12px;
        color: #94a3b8;
        margin-top: 2px;
    }

    /* ── Status badge ── */
    .trk-status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .4px;
        white-space: nowrap;
    }
    .trk-status-badge::before {
        content: '';
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: currentColor;
        opacity: .7;
        flex-shrink: 0;
    }
    .trk-s-pending      { background: #fef9c3; color: #a16207; }
    .trk-s-approved     { background: #dbeafe; color: #1d4ed8; }
    .trk-s-scheduled    { background: #e0f2fe; color: #0284c7; }
    .trk-s-assigned     { background: #ede9fe; color: #6d28d9; }
    .trk-s-in_progress  { background: #fef3c7; color: #d97706; }
    .trk-s-completed    { background: #dcfce7; color: #15803d; }
    .trk-s-cancelled    { background: #fee2e2; color: #dc2626; }
    .trk-s-declined     { background: #ffe4e6; color: #be123c; }
    .trk-s-default      { background: #f1f5f9; color: #475569; }

    /* ── Progress stepper ── */
    .trk-stepper {
        display: flex;
        align-items: flex-start;
        gap: 0;
        margin: 4px 0 0;
        padding: 4px 0;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    .trk-step {
        display: flex;
        flex-direction: column;
        align-items: center;
        flex: 1;
        min-width: 64px;
        position: relative;
    }
    /* connector line between steps */
    .trk-step:not(:last-child)::after {
        content: '';
        position: absolute;
        top: 14px;
        left: calc(50% + 14px);
        right: calc(-50% + 14px);
        height: 2px;
        background: #e2e8f0;
        z-index: 0;
    }
    .trk-step.done:not(:last-child)::after  { background: #16a34a; }
    .trk-step.active:not(:last-child)::after { background: #e2e8f0; }
    .trk-step.cancelled-step:not(:last-child)::after { background: #fca5a5; }

    .trk-step-circle {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1;
        position: relative;
        flex-shrink: 0;
        transition: background .2s;
    }
    .trk-step.done   .trk-step-circle { background: #16a34a; }
    .trk-step.active .trk-step-circle { background: #d4a017; box-shadow: 0 0 0 4px rgba(212,160,23,.2); }
    .trk-step.cancelled-step .trk-step-circle { background: #ef4444; }

    .trk-step-label {
        font-size: 11px;
        font-weight: 600;
        color: #94a3b8;
        text-align: center;
        margin-top: 6px;
        line-height: 1.3;
        max-width: 72px;
    }
    .trk-step.done   .trk-step-label  { color: #15803d; }
    .trk-step.active .trk-step-label  { color: #92400e; font-weight: 700; }
    .trk-step.cancelled-step .trk-step-label { color: #dc2626; }

    /* ── Technician card ── */
    .trk-tech-row {
        display: flex;
        align-items: center;
        gap: 16px;
    }
    .trk-tech-avatar {
        width: 52px;
        height: 52px;
        border-radius: 50%;
        background: linear-gradient(135deg, #102a43, #1e4068);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        font-weight: 800;
        color: #d4a017;
        flex-shrink: 0;
        text-transform: uppercase;
        letter-spacing: -1px;
    }
    .trk-tech-name {
        font-size: 16px;
        font-weight: 700;
        color: #102a43;
        margin: 0 0 2px;
    }
    .trk-tech-role {
        font-size: 12px;
        font-weight: 600;
        color: #d4a017;
        text-transform: uppercase;
        letter-spacing: .5px;
        margin: 0 0 6px;
    }
    .trk-tech-meta {
        display: flex;
        gap: 14px;
        flex-wrap: wrap;
    }
    .trk-tech-meta-item {
        display: flex;
        align-items: center;
        gap: 5px;
        font-size: 13px;
        color: #475569;
    }
    .trk-tech-meta-item svg { flex-shrink: 0; color: #94a3b8; }

    .trk-unassigned {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 16px;
        background: #f8fafc;
        border-radius: 10px;
        font-size: 13px;
        color: #94a3b8;
    }
    .trk-unassigned-icon {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        background: #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    /* ── Notes card ── */
    .trk-notes-text {
        font-size: 14px;
        color: #334155;
        line-height: 1.7;
        white-space: pre-wrap;
        background: #f8fafc;
        border-radius: 10px;
        padding: 14px 16px;
        border: 1px solid #f1f5f9;
        margin: 0;
    }
    .trk-notes-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        margin-top: 14px;
    }
    .trk-notes-meta-item {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 8px 14px;
    }
    .trk-notes-meta-label {
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .5px;
        color: #94a3b8;
        margin-bottom: 2px;
    }
    .trk-notes-meta-value {
        font-size: 13px;
        font-weight: 600;
        color: #1e293b;
    }

    /* ── Cancelled/declined banner ── */
    .trk-terminal-banner {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 14px 18px;
        border-radius: 10px;
        font-size: 14px;
        margin-bottom: 16px;
        line-height: 1.5;
    }
    .trk-terminal-cancelled { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; }
    .trk-terminal-declined  { background: #fff1f2; border: 1px solid #fecdd3; color: #9f1239; }
    .trk-terminal-banner svg { flex-shrink: 0; margin-top: 2px; }
    .trk-terminal-banner-body { flex: 1; }
    .trk-terminal-banner-title { font-weight: 700; margin-bottom: 2px; }

    /* ── Divider between requests ── */
    .trk-group-divider {
        display: flex;
        align-items: center;
        gap: 12px;
        margin: 32px 0;
        color: #94a3b8;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .6px;
    }
    .trk-group-divider::before, .trk-group-divider::after {
        content: '';
        flex: 1;
        height: 1px;
        background: #e2e8f0;
    }

    .trk-list[aria-busy="true"] {
        opacity: .55;
        pointer-events: none;
    }

    /* ── Loading / empty ── */
    .trk-msg {
        padding: 12px 16px;
        border-radius: 10px;
        font-size: 14px;
        margin-bottom: 18px;
        display: none;
        line-height: 1.5;
    }
    .trk-msg.show { display: block; }
    .trk-msg-error { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }

    .trk-loading {
        text-align: center;
        padding: 56px 24px;
        color: #94a3b8;
        font-size: 14px;
        display: none;
    }
    .trk-loading.show { display: block; }

    .trk-empty {
        text-align: center;
        padding: 64px 24px;
        display: none;
        flex-direction: column;
        align-items: center;
        gap: 12px;
    }
    .trk-empty.show { display: flex; }
    .trk-empty svg { opacity: .35; }
    .trk-empty-title { font-size: 16px; font-weight: 700; color: #334155; margin: 0; }
    .trk-empty-sub { font-size: 14px; color: #94a3b8; margin: 0; max-width: 340px; line-height: 1.6; }
    .trk-empty-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-top: 4px;
        padding: 10px 20px;
        background: linear-gradient(135deg, #d4a017, #b8880f);
        color: #fff;
        font-size: 14px;
        font-weight: 700;
        border-radius: 10px;
        text-decoration: none;
        transition: opacity .2s;
    }
    .trk-empty-link:hover { opacity: .88; color: #fff; }

    /* ── Content wrapper max-width ── */
    .trk-content { max-width: 740px; margin: 0 auto; }

    @media (max-width: 768px) {
        .trk-panel { padding: 18px; }
        .trk-tabs-wrap { margin-bottom: 18px; }
        .trk-tabs {
            width: 100%;
            justify-content: flex-start;
        }
        .trk-tab-btn {
            padding: 10px 14px;
            font-size: 13px;
        }
        .trk-section-title { font-size: 19px; }
        .trk-section-pill { width: 100%; justify-content: center; }
    }
</style>

{{-- ═══ PAGE HERO ═══ --}}
<div class="trk-hero">
    <p class="trk-hero-eyebrow">Your Service Requests</p>
    <h1 class="trk-hero-title">Service <span>Tracking</span></h1>
    <p class="trk-hero-sub">Monitor the real-time progress of your service requests — from scheduling through to job completion.</p>
</div>

{{-- ═══ CONTENT ═══ --}}
<div class="trk-content">

    <div class="trk-panel">
        <div class="trk-tabs-wrap">
            <div class="trk-tabs" role="tablist" aria-label="Tracking request categories">
                <button type="button" class="trk-tab-btn active" data-trk-tab="inspection" role="tab" aria-selected="true">
                    Inspection <span class="trk-tab-count" id="trk-count-inspection">0</span>
                </button>
                <button type="button" class="trk-tab-btn" data-trk-tab="installation" role="tab" aria-selected="false">
                    Installation <span class="trk-tab-count" id="trk-count-installation">0</span>
                </button>
                <button type="button" class="trk-tab-btn" data-trk-tab="maintenance" role="tab" aria-selected="false">
                    Maintenance <span class="trk-tab-count" id="trk-count-maintenance">0</span>
                </button>
            </div>
            <p class="trk-panel-note">Switch between your inspection, installation, and maintenance requests without leaving the page.</p>
        </div>

        <div class="trk-section-head">
            <div>
                <p id="trk-section-kicker" class="trk-section-kicker">Inspection Requests</p>
                <h2 id="trk-section-title" class="trk-section-title">Track your inspection schedule and progress</h2>
                <p id="trk-section-sub" class="trk-section-sub">Review the latest status, assigned technician details, and next steps for your inspection bookings.</p>
            </div>
            <div id="trk-section-pill" class="trk-section-pill">0 requests in this tab</div>
        </div>

        <div id="trk-loading" class="trk-loading">Loading your tracking requests...</div>
        <div id="trk-msg" class="trk-msg" role="alert"></div>

        <div id="trk-list" class="trk-list" aria-live="polite"></div>

        <div id="trk-empty" class="trk-empty">
            <svg width="52" height="52" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.3">
                <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/>
                <rect x="9" y="3" width="6" height="4" rx="1"/>
                <path d="M9 12h6M9 16h4"/>
            </svg>
            <p id="trk-empty-title" class="trk-empty-title">No inspection requests yet.</p>
            <p id="trk-empty-sub" class="trk-empty-sub">Start with an inspection request so the SolMate team can assess your property and guide your next step.</p>
            <a id="trk-empty-link" href="{{ route('customer.inspection') }}" class="trk-empty-link">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>
                <span id="trk-empty-link-text">Request an Inspection</span>
            </a>
        </div>
    </div>

</div>{{-- /.trk-content --}}

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

    function fmtDate(str) {
        if (!str) return '\u2014';
        try {
            return new Date(str).toLocaleDateString('en-PH', { year: 'numeric', month: 'short', day: 'numeric' });
        } catch (e) { return str; }
    }

    function fmtDateTime(str) {
        if (!str) return '\u2014';
        try {
            return new Date(str).toLocaleString('en-PH', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
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

    async function apiGet(endpoint) {
        var headers = { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' };
        var resp = await fetch(endpoint, { method: 'GET', credentials: 'same-origin', headers: headers });
        var payload = await resp.json().catch(function () { return {}; });
        if (!resp.ok) {
            var err    = new Error(payload.message || 'Request failed.');
            err.status = resp.status;
            throw err;
        }
        return payload;
    }

    /* ── Status config ── */
    var STATUS_ORDER = ['pending', 'approved', 'scheduled', 'assigned', 'in_progress', 'completed'];
    var TERMINAL     = { cancelled: true, declined: true };

    /* Steps shown in the progress indicator */
    var STEPS = [
        { key: 'submitted',   label: 'Submitted' },
        { key: 'approved',    label: 'Approved' },
        { key: 'scheduled',   label: 'Scheduled' },
        { key: 'in_progress', label: 'In Progress' },
        { key: 'completed',   label: 'Completed' },
    ];

    /* Map a raw status to which STEPS are "done" */
    function getStepState(status) {
        var s = String(status || 'pending').toLowerCase();
        if (TERMINAL[s]) return 'terminal';

        /* Index of each step in the linear flow */
        var flowPos = {
            pending:     0, // submitted only
            approved:    1,
            scheduled:   2,
            assigned:    2, // treat assigned same as scheduled
            in_progress: 3,
            completed:   4,
        };
        return flowPos[s] !== undefined ? flowPos[s] : 0;
    }

    function statusBadgeClass(status) {
        var s = String(status || '').toLowerCase();
        var map = {
            pending:     'trk-s-pending',
            approved:    'trk-s-approved',
            scheduled:   'trk-s-scheduled',
            assigned:    'trk-s-assigned',
            in_progress: 'trk-s-in_progress',
            completed:   'trk-s-completed',
            cancelled:   'trk-s-cancelled',
            declined:    'trk-s-declined',
        };
        return map[s] || 'trk-s-default';
    }

    function statusLabel(status) {
        var s = String(status || 'pending').toLowerCase().replace(/_/g, ' ');
        return s.charAt(0).toUpperCase() + s.slice(1);
    }

    function headline(text) {
        var cleaned = String(text || '')
            .replace(/[_-]+/g, ' ')
            .replace(/\s+/g, ' ')
            .trim();

        if (!cleaned) return 'Service Request';

        return cleaned.split(' ').map(function (word) {
            return word.charAt(0).toUpperCase() + word.slice(1).toLowerCase();
        }).join(' ');
    }

    function normalizeServiceCategory(requestType) {
        var value = String(requestType || '')
            .toLowerCase()
            .replace(/[_-]+/g, ' ')
            .trim();

        if (value.indexOf('installation') !== -1) return 'installation';
        return 'maintenance';
    }

    function normalizeInspectionRequest(request) {
        var normalized = Object.assign({}, request);
        normalized.request_type = 'inspection';
        normalized.tracking_category = 'inspection';
        normalized.tracking_title = 'Inspection Request #' + request.id;
        return normalized;
    }

    function normalizeServiceRequest(request) {
        var normalized = Object.assign({}, request);
        normalized.tracking_category = normalizeServiceCategory(request.request_type);
        normalized.tracking_title = (normalized.tracking_category === 'installation'
            ? 'Installation Request #'
            : 'Maintenance Request #') + request.id;
        return normalized;
    }

    function buildRequestTypeLabel(request) {
        if (request.tracking_category === 'inspection') return 'Inspection';
        return headline(request.request_type || request.tracking_category || 'Service Request');
    }

    /* ── HTML builders ── */
    function buildStepper(status) {
        var s         = String(status || 'pending').toLowerCase();
        var isCancel  = TERMINAL[s];
        var stepPos   = isCancel ? -1 : getStepState(s);

        var html = '<div class="trk-stepper" role="list" aria-label="Request progress">';
        for (var i = 0; i < STEPS.length; i++) {
            var step     = STEPS[i];
            var isDone   = !isCancel && i <= stepPos;
            var isActive = !isCancel && i === stepPos + 1;
            // step 0 (Submitted) is always done unless terminal
            if (i === 0) { isDone = !isCancel; isActive = false; }

            var stepClass = 'trk-step';
            if (isCancel) stepClass += ' cancelled-step';
            else if (isDone)   stepClass += ' done';
            else if (isActive) stepClass += ' active';

            var icon;
            if (isCancel) {
                icon = '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5"><path d="M18 6L6 18M6 6l12 12"/></svg>';
            } else if (isDone) {
                icon = '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="3"><path d="M5 13l4 4L19 7"/></svg>';
            } else if (isActive) {
                icon = '<svg width="10" height="10" viewBox="0 0 24 24" fill="#fff"><circle cx="12" cy="12" r="5"/></svg>';
            } else {
                icon = '<svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="2"><circle cx="12" cy="12" r="4"/></svg>';
            }

            html += '<div class="' + stepClass + '" role="listitem">'
                  + '<div class="trk-step-circle">' + icon + '</div>'
                  + '<span class="trk-step-label">' + escHtml(step.label) + '</span>'
                  + '</div>';
        }
        html += '</div>';
        return html;
    }

    function buildTerminalBanner(status) {
        var s   = String(status || '').toLowerCase();
        var cls = (s === 'cancelled') ? 'trk-terminal-cancelled' : 'trk-terminal-declined';
        var msg = (s === 'cancelled')
            ? 'This service request has been <strong>cancelled</strong>.'
            : 'This service request was <strong>declined</strong>. Please contact us if you have questions.';
        return '<div class="trk-terminal-banner ' + cls + '">'
            + '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>'
            + '<div class="trk-terminal-banner-body"><div class="trk-terminal-banner-title">'
            + statusLabel(status) + '</div><div>' + msg + '</div></div>'
            + '</div>';
    }

    function buildRequestCard(sr) {
        var status = String(sr.status || 'pending').toLowerCase();
        var isTerm = !!TERMINAL[status];
        var reqType = buildRequestTypeLabel(sr);
        var title = sr.tracking_title || ('Request #' + sr.id);

        var html = '<div class="trk-request-group">';

        /* ── Request header band ── */
        html += '<div class="trk-req-header">'
            + '<div>'
            + '<div class="trk-req-id">' + escHtml(title) + '</div>'
            + '<div class="trk-req-type">' + escHtml(reqType) + '</div>'
            + '<div class="trk-req-date">Submitted ' + fmtDate(sr.created_at) + '</div>'
            + '</div>'
            + '<span class="trk-status-badge ' + statusBadgeClass(status) + '">' + escHtml(statusLabel(status)) + '</span>'
            + '</div>';

        /* Terminal banner */
        if (isTerm) html += buildTerminalBanner(status);

        /* ── Card 1: Request Status ── */
        html += '<div class="trk-card">'
            + '<div class="trk-card-header">'
            + '<div class="trk-card-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#d4a017" stroke-width="2"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/><path d="M9 12h6M9 16h4"/></svg></div>'
            + '<div><p class="trk-card-title">Request Status</p><p class="trk-card-subtitle">Live progress of your service request</p></div>'
            + '</div>'
            + '<div class="trk-card-body">' + buildStepper(status);

        /* Preferred date if set */
        if (sr.date_needed) {
            html += '<div style="margin-top:18px;padding:12px 16px;background:#f8fafc;border-radius:10px;display:flex;align-items:center;gap:10px;">'
                  + '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#d4a017" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>'
                  + '<span style="font-size:13px;color:#475569;"><strong>Preferred date:</strong> ' + escHtml(fmtDate(sr.date_needed)) + '</span>'
                  + '</div>';
        }

        /* Technician completed banner */
        if (sr.technician_marked_done_at) {
            html += '<div style="margin-top:14px;padding:10px 14px;background:#dcfce7;border:1px solid #bbf7d0;border-radius:8px;font-size:13px;color:#166534;">'
                  + '<strong>Technician marked as done</strong> on ' + escHtml(fmtDateTime(sr.technician_marked_done_at))
                  + '</div>';
        }

        html += '</div></div>'; /* end card 1 */

        /* ── Card 2: Technician Assigned ── */
        html += '<div class="trk-card">'
            + '<div class="trk-card-header">'
            + '<div class="trk-card-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#d4a017" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></div>'
            + '<div><p class="trk-card-title">Technician Assigned</p><p class="trk-card-subtitle">Your assigned field technician</p></div>'
            + '</div>'
            + '<div class="trk-card-body">';

        if (sr.technician) {
            var tech     = sr.technician;
            var initials = String(tech.name || '?').split(' ').map(function (w) { return w[0]; }).slice(0, 2).join('');
            var techRole = String(tech.role || 'Technician').replace(/_/g, ' ');
            techRole = techRole.charAt(0).toUpperCase() + techRole.slice(1);

            html += '<div class="trk-tech-row">'
                + '<div class="trk-tech-avatar">' + escHtml(initials) + '</div>'
                + '<div>'
                + '<p class="trk-tech-name">' + escHtml(tech.name) + '</p>'
                + '<p class="trk-tech-role">' + escHtml(techRole) + '</p>'
                + '<div class="trk-tech-meta">'
                + '<div class="trk-tech-meta-item"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>' + escHtml(tech.email) + '</div>'
                + '</div>'
                + '</div></div>';
        } else if (sr.technician_id) {
            html += '<div class="trk-unassigned">'
                + '<div class="trk-unassigned-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></div>'
                + '<span>A technician has been assigned to this request. Full technician details will appear here once they are available.</span>'
                + '</div>';
        } else {
            html += '<div class="trk-unassigned">'
                + '<div class="trk-unassigned-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></div>'
                + '<span>No technician assigned yet. You will be notified once a technician is assigned to your request.</span>'
                + '</div>';
        }

        html += '</div></div>'; /* end card 2 */

        /* ── Card 3: Resolution Notes ── */
        html += '<div class="trk-card">'
            + '<div class="trk-card-header">'
            + '<div class="trk-card-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#d4a017" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg></div>'
            + '<div><p class="trk-card-title">Request Details &amp; Notes</p><p class="trk-card-subtitle">Your submitted request notes</p></div>'
            + '</div>'
            + '<div class="trk-card-body">'
            + '<pre class="trk-notes-text">' + escHtml(sr.details || 'No details provided.') + '</pre>'
            + '<div class="trk-notes-meta">'
            + '<div class="trk-notes-meta-item"><div class="trk-notes-meta-label">Contact Number</div><div class="trk-notes-meta-value">' + escHtml(sr.contact_number || '\u2014') + '</div></div>'
            + '<div class="trk-notes-meta-item"><div class="trk-notes-meta-label">Preferred Date</div><div class="trk-notes-meta-value">' + escHtml(fmtDate(sr.date_needed)) + '</div></div>'
            + '<div class="trk-notes-meta-item"><div class="trk-notes-meta-label">Submitted</div><div class="trk-notes-meta-value">' + escHtml(fmtDate(sr.created_at)) + '</div></div>'
            + '</div>'
            + '</div></div>'; /* end card 3 */

        html += '</div>'; /* end request group */
        return html;
    }

    /* ── Load & render ── */
    var trkLoading = qs('#trk-loading');
    var trkMsg     = qs('#trk-msg');
    var trkList    = qs('#trk-list');
    var trkEmpty   = qs('#trk-empty');
    var trkEmptyTitle = qs('#trk-empty-title');
    var trkEmptySub = qs('#trk-empty-sub');
    var trkEmptyLink = qs('#trk-empty-link');
    var trkEmptyLinkText = qs('#trk-empty-link-text');
    var trkSectionKicker = qs('#trk-section-kicker');
    var trkSectionTitle = qs('#trk-section-title');
    var trkSectionSub = qs('#trk-section-sub');
    var trkSectionPill = qs('#trk-section-pill');
    var trkTabButtons = Array.prototype.slice.call(document.querySelectorAll('[data-trk-tab]'));

    var TAB_CONFIG = {
        inspection: {
            kicker: 'Inspection Requests',
            title: 'Track your inspection schedule and progress',
            subtitle: 'Review the latest status, assigned technician details, and next steps for your inspection bookings.',
            emptyTitle: 'No inspection requests yet.',
            emptySubtitle: 'Start with an inspection request so the SolMate team can assess your property and guide your next step.',
            emptyHref: '{{ route('customer.inspection') }}',
            emptyCta: 'Request an Inspection'
        },
        installation: {
            kicker: 'Installation Requests',
            title: 'Track your installation bookings and site coordination',
            subtitle: 'See which installation requests are approved, scheduled, or already moving toward completion.',
            emptyTitle: 'No installation requests yet.',
            emptySubtitle: 'Book an installation request when you are ready for site coordination or solar setup scheduling.',
            emptyHref: '{{ route('customer.installation') }}',
            emptyCta: 'Request Installation'
        },
        maintenance: {
            kicker: 'Maintenance Requests',
            title: 'Track ongoing maintenance and service support',
            subtitle: 'Follow maintenance progress, technician assignment, and the latest service updates in one place.',
            emptyTitle: 'No maintenance requests yet.',
            emptySubtitle: 'Submit a maintenance request whenever your system needs servicing, support, or follow-up work.',
            emptyHref: '{{ route('customer.maintenance') }}',
            emptyCta: 'Request Maintenance'
        }
    };

    var state = {
        activeTab: 'inspection',
        requests: []
    };

    function showMsg(type, text) {
        trkMsg.className = 'trk-msg show trk-msg-' + type;
        trkMsg.textContent = text;
    }

    function requestsForTab(tabKey) {
        return state.requests.filter(function (request) {
            return request.tracking_category === tabKey;
        });
    }

    function updateTabButtons() {
        trkTabButtons.forEach(function (button) {
            var tabKey = button.getAttribute('data-trk-tab');
            var isActive = tabKey === state.activeTab;
            button.classList.toggle('active', isActive);
            button.setAttribute('aria-selected', isActive ? 'true' : 'false');
        });
    }

    function updateTabCounts() {
        Object.keys(TAB_CONFIG).forEach(function (tabKey) {
            var countEl = qs('#trk-count-' + tabKey);
            if (countEl) countEl.textContent = String(requestsForTab(tabKey).length);
        });
    }

    function updateSectionCopy(items) {
        var config = TAB_CONFIG[state.activeTab];
        var count = items.length;
        trkSectionKicker.textContent = config.kicker;
        trkSectionTitle.textContent = config.title;
        trkSectionSub.textContent = config.subtitle;
        trkSectionPill.textContent = count + ' request' + (count === 1 ? '' : 's') + ' in this tab';
    }

    function updateEmptyState() {
        var config = TAB_CONFIG[state.activeTab];
        trkEmptyTitle.textContent = config.emptyTitle;
        trkEmptySub.textContent = config.emptySubtitle;
        trkEmptyLink.setAttribute('href', config.emptyHref);
        trkEmptyLinkText.textContent = config.emptyCta;
    }

    function renderRequests() {
        var items = requestsForTab(state.activeTab);
        updateTabButtons();
        updateTabCounts();
        updateSectionCopy(items);
        updateEmptyState();

        if (items.length === 0) {
            trkList.innerHTML = '';
            trkEmpty.classList.add('show');
            return;
        }

        trkEmpty.classList.remove('show');

        var html = '';
        items.forEach(function (sr, idx) {
            if (idx > 0) {
                html += '<div class="trk-group-divider">Request ' + (idx + 1) + '</div>';
            }
            html += buildRequestCard(sr);
        });
        trkList.innerHTML = html;
    }

    async function loadRequests() {
        trkLoading.classList.add('show');
        trkList.setAttribute('aria-busy', 'true');
        trkMsg.className = 'trk-msg';
        trkMsg.textContent = '';
        try {
            var results = await Promise.all([
                apiGet('/api/inspection-requests'),
                apiGet('/api/service-requests')
            ]);
            var inspectionItems = Array.isArray(results[0]) ? results[0] : (results[0].data || []);
            var serviceItems = Array.isArray(results[1]) ? results[1] : (results[1].data || []);

            state.requests = inspectionItems.map(normalizeInspectionRequest)
                .concat(serviceItems.map(normalizeServiceRequest));
            renderRequests();
        } catch (err) {
            showMsg('error', err.message || 'Could not load tracking requests. Please refresh the page.');
        } finally {
            trkLoading.classList.remove('show');
            trkList.removeAttribute('aria-busy');
        }
    }

    trkTabButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            var nextTab = button.getAttribute('data-trk-tab');
            if (!TAB_CONFIG[nextTab] || nextTab === state.activeTab) return;
            state.activeTab = nextTab;
            renderRequests();
        });
    });

    loadRequests();

})();
</script>
@endpush
