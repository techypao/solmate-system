@extends('layouts.app', ['title' => 'Request Site Inspection'])

@section('content')
<link
    rel="stylesheet"
    href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
    integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
    crossorigin=""
>
<style>
    /* ── Customer Inspection Request Page (insp- prefix) ── */

    /* Page hero */
    .insp-hero {
        background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 55%, #d1fae5 100%);
        border-radius: 16px;
        padding: 36px 40px;
        margin-bottom: 32px;
        position: relative;
        overflow: hidden;
    }
    .insp-hero::after {
        content: '';
        position: absolute;
        right: -40px;
        bottom: -40px;
        width: 220px;
        height: 220px;
        border-radius: 50%;
        background: rgba(212,160,23,.09);
        pointer-events: none;
    }
    .insp-hero-eyebrow {
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 1.2px;
        text-transform: uppercase;
        color: #F4D000;
        margin: 0 0 8px;
    }
    .insp-hero-title {
        font-size: 30px;
        font-weight: 800;
        color: #123A5A;
        margin: 0 0 10px;
        line-height: 1.2;
    }
    .insp-hero-title span { color: #F4D000; }
    .insp-hero-sub {
        font-size: 15px;
        color: #5E7288;
        max-width: 560px;
        margin: 0;
        line-height: 1.6;
    }
    .insp-hero-steps {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        margin-top: 24px;
        align-items: center;
    }
    .insp-hero-step {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
        color: #5E7288;
    }
    .insp-hero-step-num {
        width: 26px;
        height: 26px;
        border-radius: 50%;
        background: #DDE7EE;
        color: #fff;
        font-size: 11px;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .insp-step-done .insp-hero-step-num { background: #16a34a; }
    .insp-step-done { color: #166534; font-weight: 600; }
    .insp-step-active .insp-hero-step-num { background: #F4D000; }
    .insp-step-active { color: #123A5A; font-weight: 700; }
    .insp-step-connector {
        width: 20px;
        height: 2px;
        background: #DDE7EE;
        flex-shrink: 0;
    }

    /* Two-column layout */
    .insp-layout {
        display: grid;
        grid-template-columns: 1fr 320px;
        gap: 24px;
        align-items: start;
    }
    @media (max-width: 820px) {
        .insp-layout { grid-template-columns: 1fr; }
        .insp-hero { padding: 24px 20px; }
        .insp-hero-title { font-size: 22px; }
    }

    /* Cards */
    .insp-card {
        background: #fff;
        border: 1px solid #DDE7EE;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 2px 12px rgba(0,0,0,.04);
        margin-bottom: 20px;
    }
    .insp-card-schedule {
        overflow: visible;
    }
    .insp-card:last-child { margin-bottom: 0; }
    .insp-card-header {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 18px 24px;
        border-bottom: 1px solid #f1f5f9;
    }
    .insp-card-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        background: linear-gradient(135deg, #123A5A, #20A7C9);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .insp-card-title {
        font-size: 16px;
        font-weight: 700;
        color: #123A5A;
        margin: 0;
    }
    .insp-card-subtitle {
        font-size: 12px;
        color: #7F92A3;
        margin: 2px 0 0;
    }
    .insp-card-body { padding: 24px; }

    /* Form elements */
    .insp-field { margin-bottom: 20px; }
    .insp-field:last-child { margin-bottom: 0; }
    .insp-label {
        display: block;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .6px;
        color: #5E7288;
        margin-bottom: 8px;
    }
    .insp-select,
    .insp-input,
    .insp-textarea {
        width: 100%;
        padding: 12px 16px;
        border: 1.5px solid #DDE7EE;
        border-radius: 10px;
        font-size: 14px;
        color: #0F2F4A;
        background: #fff;
        box-sizing: border-box;
        font-family: inherit;
        transition: border-color .2s, box-shadow .2s;
        appearance: none;
        -webkit-appearance: none;
    }
    .insp-select {
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 14px center;
        padding-right: 40px;
        cursor: pointer;
    }
    .insp-select:focus,
    .insp-input:focus,
    .insp-textarea:focus {
        outline: none;
        border-color: #F4D000;
        box-shadow: 0 0 0 3px rgba(212,160,23,.12);
    }
    .insp-select.has-error,
    .insp-input.has-error,
    .insp-textarea.has-error { border-color: #ef4444; }
    .insp-textarea {
        resize: vertical;
        min-height: 100px;
    }
    .insp-field-hint {
        font-size: 12px;
        color: #7F92A3;
        margin-top: 6px;
    }
    .insp-address-row {
        display: flex;
        align-items: stretch;
        gap: 12px;
    }
    .insp-address-row .insp-input {
        flex: 1;
    }
    .insp-address-pin-btn {
        flex-shrink: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-height: 48px;
        padding: 0 16px;
        border: 1px solid #e7c35a;
        border-radius: 10px;
        background: linear-gradient(135deg, #fff8dc, #f6e3a1);
        color: #8a6510;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
        transition: transform .15s, box-shadow .2s, border-color .2s;
        white-space: nowrap;
    }
    .insp-address-pin-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(212,160,23,.16);
        border-color: #F4D000;
    }
    .insp-address-pin-btn:focus {
        outline: none;
        border-color: #F4D000;
        box-shadow: 0 0 0 3px rgba(212,160,23,.12);
    }
    .insp-address-pin-btn:active {
        transform: translateY(0);
        box-shadow: none;
    }
    .insp-field-error {
        font-size: 12px;
        color: #dc2626;
        margin-top: 6px;
        display: none;
    }
    .insp-field-error.show { display: block; }
    .insp-field-note {
        display: none;
        margin-top: 8px;
        padding: 10px 12px;
        border-radius: 10px;
        font-size: 12px;
        line-height: 1.5;
    }
    .insp-field-note.show {
        display: block;
    }
    .insp-field-note-info {
        background: #EAF9FD;
        border: 1px solid #bfdbfe;
        color: #123A5A;
    }

    /* Two-column field row */
    .insp-field-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
        margin-bottom: 20px;
    }
    @media (max-width: 560px) { .insp-field-row { grid-template-columns: 1fr; } }
    .insp-field-row .insp-field { margin-bottom: 0; }
    @media (max-width: 640px) {
        .insp-address-row {
            flex-direction: column;
        }
        .insp-address-pin-btn {
            width: 100%;
        }
    }

    /* Map modal */
    .insp-map-modal {
        position: fixed;
        inset: 0;
        z-index: 1200;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 24px 16px;
        background: rgba(15, 23, 42, .58);
        backdrop-filter: blur(4px);
    }
    .insp-map-modal.show {
        display: flex;
    }
    .insp-map-dialog {
        width: min(100%, 760px);
        max-height: min(90vh, 760px);
        background: linear-gradient(180deg, #F8FAFC 0%, #ffffff 28%);
        border: 1px solid #DDE7EE;
        border-radius: 22px;
        box-shadow: 0 24px 60px rgba(15, 23, 42, .24);
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }
    .insp-map-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        padding: 20px 24px 16px;
        border-bottom: 1px solid #DDE7EE;
    }
    .insp-map-title {
        margin: 0;
        font-size: 20px;
        font-weight: 800;
        color: #123A5A;
    }
    .insp-map-subtitle {
        margin: 6px 0 0;
        font-size: 13px;
        color: #5E7288;
        line-height: 1.6;
    }
    .insp-map-close {
        width: 40px;
        height: 40px;
        border: 1px solid #DDE7EE;
        border-radius: 999px;
        background: #ffffff;
        color: #5E7288;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: border-color .2s, color .2s, transform .15s;
    }
    .insp-map-close:hover {
        border-color: #F4D000;
        color: #8a6510;
        transform: translateY(-1px);
    }
    .insp-map-close:focus {
        outline: none;
        border-color: #F4D000;
        box-shadow: 0 0 0 3px rgba(212,160,23,.12);
    }
    .insp-map-body {
        padding: 20px 24px 24px;
        display: grid;
        gap: 16px;
    }
    .insp-map-feedback {
        display: none;
        padding: 12px 14px;
        border-radius: 12px;
        font-size: 13px;
        line-height: 1.5;
    }
    .insp-map-feedback.show {
        display: block;
    }
    .insp-map-feedback-error {
        background: #fff1f2;
        border: 1px solid #fecdd3;
        color: #9f1239;
    }
    .insp-map-feedback-info {
        background: #EAF9FD;
        border: 1px solid #bfdbfe;
        color: #123A5A;
    }
    .insp-map-search {
        display: flex;
        align-items: stretch;
        gap: 12px;
    }
    .insp-map-search-input {
        flex: 1;
    }
    .insp-map-canvas {
        height: 420px;
        border: 1px solid #DDE7EE;
        border-radius: 18px;
        overflow: hidden;
        background: #eaf2fb;
    }
    .insp-map-canvas .leaflet-container {
        width: 100%;
        height: 100%;
        font-family: inherit;
        border-radius: 18px;
    }
    .insp-map-actions {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
    }
    .insp-map-actions-primary {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }
    .insp-map-btn {
        min-height: 44px;
        padding: 10px 16px;
        border-radius: 12px;
        border: 1px solid #DDE7EE;
        background: #ffffff;
        color: #20A7C9;
        font-size: 14px;
        font-weight: 700;
        cursor: pointer;
        transition: transform .15s, box-shadow .2s, border-color .2s;
    }
    .insp-map-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 20px rgba(15, 23, 42, .08);
    }
    .insp-map-btn:focus {
        outline: none;
        box-shadow: 0 0 0 3px rgba(41,82,122,.12);
    }
    .insp-map-btn-gold {
        border-color: #F4D000;
        background: linear-gradient(135deg, #F4D000, #E6C200);
        color: #ffffff;
    }
    .insp-map-btn-gold:focus {
        box-shadow: 0 0 0 3px rgba(212,160,23,.18);
    }
    .insp-map-btn-soft-gold {
        border-color: #e7c35a;
        background: linear-gradient(135deg, #fff8dc, #f6e3a1);
        color: #8a6510;
    }
    body.insp-modal-open {
        overflow: hidden;
    }
    @media (max-width: 640px) {
        .insp-map-modal {
            padding: 12px;
        }
        .insp-map-dialog {
            width: 100%;
            max-height: calc(100vh - 24px);
            border-radius: 18px;
        }
        .insp-map-header,
        .insp-map-body {
            padding-left: 16px;
            padding-right: 16px;
        }
        .insp-map-title {
            font-size: 18px;
        }
        .insp-map-canvas {
            height: 320px;
            border-radius: 16px;
        }
        .insp-map-canvas .leaflet-container {
            border-radius: 16px;
        }
        .insp-map-search {
            flex-direction: column;
        }
        .insp-map-actions,
        .insp-map-actions-primary {
            flex-direction: column;
            align-items: stretch;
        }
        .insp-map-btn {
            width: 100%;
        }
    }

    /* Quote summary box */
    .insp-quote-summary {
        background: #f8fafc;
        border: 1px solid #DDE7EE;
        border-radius: 12px;
        padding: 16px 18px;
        margin-top: 14px;
    }
    .insp-qs-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 12px;
    }
    @media (max-width: 560px) { .insp-qs-grid { grid-template-columns: 1fr; } }
    .insp-qs-label {
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .5px;
        color: #7F92A3;
        margin-bottom: 3px;
    }
    .insp-qs-value {
        font-size: 15px;
        font-weight: 700;
        color: #123A5A;
    }
    .insp-qs-value.highlight { color: #F4D000; }
    .insp-qs-note {
        margin-top: 12px;
        padding-top: 12px;
        border-top: 1px solid #DDE7EE;
        font-size: 12px;
        color: #5E7288;
        line-height: 1.5;
    }

    /* Message boxes */
    .insp-msg {
        padding: 12px 16px;
        border-radius: 10px;
        font-size: 14px;
        margin-bottom: 18px;
        display: none;
        line-height: 1.5;
    }
    .insp-msg.show { display: block; }
    .insp-msg-success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
    .insp-msg-error   { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }

    /* Submit button */
    .insp-submit-btn {
        width: 100%;
        padding: 14px 24px;
        background: linear-gradient(135deg, #F4D000, #E6C200);
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
        margin-top: 8px;
    }
    .insp-submit-btn:hover  { opacity: .92; transform: translateY(-1px); }
    .insp-submit-btn:active { transform: translateY(0); opacity: 1; }
    .insp-submit-btn:disabled { opacity: .6; cursor: not-allowed; transform: none; }

    /* Right panel */
    .insp-panel {
        background: #fff;
        border: 1px solid #DDE7EE;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 2px 12px rgba(0,0,0,.04);
    }
    .insp-panel-header {
        padding: 16px 20px;
        background: #123A5A;
        color: #fff;
    }
    .insp-panel-title {
        font-size: 14px;
        font-weight: 700;
        margin: 0 0 2px;
    }
    .insp-panel-sub {
        font-size: 12px;
        color: rgba(255,255,255,.65);
        margin: 0;
    }
    .insp-panel-body { padding: 20px; }

    /* Checklist */
    .insp-checklist { display: flex; flex-direction: column; gap: 14px; }
    .insp-checklist-item {
        display: flex;
        align-items: flex-start;
        gap: 12px;
    }
    .insp-check-icon {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: #dcfce7;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        margin-top: 2px;
    }
    .insp-check-body { flex: 1; }
    .insp-check-title {
        font-size: 13px;
        font-weight: 700;
        color: #123A5A;
        margin: 0 0 2px;
    }
    .insp-check-desc {
        font-size: 12px;
        color: #5E7288;
        margin: 0;
        line-height: 1.5;
    }

    .insp-expect-box {
        margin-top: 20px;
        padding: 14px 16px;
        background: #fffbeb;
        border: 1px solid #fde68a;
        border-radius: 10px;
    }
    .insp-expect-title {
        font-size: 13px;
        font-weight: 700;
        color: #92400e;
        margin: 0 0 6px;
    }
    .insp-expect-text {
        font-size: 12px;
        color: #78350f;
        margin: 0;
        line-height: 1.6;
    }

    /* History section */
    .insp-history-section { margin-top: 32px; }
    .insp-history-title {
        font-size: 18px;
        font-weight: 700;
        color: #123A5A;
        margin-bottom: 16px;
    }

    .insp-ir-grid { display: grid; gap: 14px; }
    .insp-ir-card {
        background: #fff;
        border: 1px solid #DDE7EE;
        border-radius: 16px;
        padding: 18px 22px;
        transition: box-shadow .2s;
    }
    .insp-ir-card:hover { box-shadow: 0 4px 20px rgba(0,0,0,.07); }
    .insp-ir-card-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 12px;
    }
    .insp-ir-id { font-size: 15px; font-weight: 700; color: #123A5A; }
    .insp-ir-date { font-size: 12px; color: #7F92A3; margin-top: 3px; }
    .insp-ir-meta {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-bottom: 12px;
    }
    .insp-ir-meta-item {
        background: #f8fafc;
        border-radius: 8px;
        padding: 8px 12px;
    }
    .insp-ir-meta-label {
        font-size: 10px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .4px;
        color: #7F92A3;
        margin-bottom: 2px;
    }
    .insp-ir-meta-value { font-size: 13px; font-weight: 600; color: #0F2F4A; }
    .insp-ir-details-row {
        font-size: 13px;
        color: #5E7288;
        background: #f8fafc;
        border-radius: 8px;
        padding: 10px 14px;
        line-height: 1.6;
    }
    .insp-ir-details-label {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .4px;
        color: #7F92A3;
        margin-bottom: 4px;
    }

    /* Status badges */
    .insp-badge {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .4px;
        white-space: nowrap;
    }
    .insp-badge-pending      { background: #FFF7CC; color: #a16207; }
    .insp-badge-assigned     { background: #EAF9FD; color: #1d4ed8; }
    .insp-badge-in_progress  { background: #ede9fe; color: #6d28d9; }
    .insp-badge-completed    { background: #dcfce7; color: #15803d; }
    .insp-badge-cancelled    { background: #fee2e2; color: #dc2626; }
    .insp-badge-rescheduled  { background: #FFF7CC; color: #d97706; }
    .insp-badge-default      { background: #f1f5f9; color: #5E7288; }

    /* Loading / empty */
    .insp-loading { text-align: center; padding: 32px; color: #7F92A3; font-size: 14px; display: none; }
    .insp-loading.show { display: block; }
    .insp-empty { text-align: center; padding: 48px 24px; color: #7F92A3; display: none; flex-direction: column; align-items: center; gap: 10px; }
    .insp-empty.show { display: flex; }
    .insp-empty svg { opacity: .4; }
    .insp-empty p { font-size: 14px; margin: 0; }
</style>
@include('customer.partials.preferred-date-picker-styles')

{{-- ═══ PAGE HERO ═══ --}}
<div class="insp-hero">
    <p class="insp-hero-eyebrow">Step 2 of 3</p>
    <h1 class="insp-hero-title">Request a <span>Site Inspection</span></h1>
    <p class="insp-hero-sub">Our technicians will visit your property to verify installation feasibility and assess the site before preparing the next installation steps.</p>
    <div class="insp-hero-steps">
        <div class="insp-hero-step insp-step-done">
            <span class="insp-hero-step-num">
                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5"><path d="M5 13l4 4L19 7"/></svg>
            </span>
            <span>Request Submitted</span>
        </div>
        <div class="insp-step-connector"></div>
        <div class="insp-hero-step insp-step-active">
            <span class="insp-hero-step-num">2</span>
            <span>Site Inspection</span>
        </div>
        <div class="insp-step-connector"></div>
        <div class="insp-hero-step">
            <span class="insp-hero-step-num">3</span>
            <span>Service Planning</span>
        </div>
    </div>
</div>

{{-- ═══ TWO-COLUMN LAYOUT ═══ --}}
<div class="insp-layout">

    {{-- ── LEFT: Form sections ── --}}
    <div>

        {{-- CARD 1: Preferred Schedule & Details --}}
        <div class="insp-card insp-card-schedule">
            <div class="insp-card-header">
                <div class="insp-card-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#F4D000" stroke-width="2">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                        <line x1="16" y1="2" x2="16" y2="6"/>
                        <line x1="8" y1="2" x2="8" y2="6"/>
                        <line x1="3" y1="10" x2="21" y2="10"/>
                    </svg>
                </div>
                <div>
                    <p class="insp-card-title">Preferred Schedule &amp; Details</p>
                    <p class="insp-card-subtitle">Tell us when and how we should reach you</p>
                </div>
            </div>
            <div class="insp-card-body">
                <div id="insp-form-msg" class="insp-msg" role="alert"></div>

                <form id="insp-form" novalidate>

                    <div class="insp-field-row">
                        <div class="insp-field">
                            <label class="insp-label" for="insp-contact">
                                Contact Number <span style="color:#ef4444;font-weight:700;">*</span>
                            </label>
                            <input
                                id="insp-contact"
                                class="insp-input"
                                type="tel"
                                name="contact_number"
                                placeholder="e.g. 09171234567"
                                maxlength="30"
                                required
                                autocomplete="tel"
                                value="{{ auth()->user()->contact_number ?: (auth()->user()->landline_number ?: '') }}"
                            >
                            <div class="insp-field-error" id="insp-contact-error" role="alert"></div>
                        </div>

                        <div class="insp-field">
                            <label class="insp-label" for="insp-date">
                                Preferred Date <span style="font-weight:400;text-transform:none;letter-spacing:0;color:#7F92A3;">(optional)</span>
                            </label>
                            <input
                                id="insp-date"
                                class="insp-input"
                                type="hidden"
                                name="date_needed"
                                autocomplete="off"
                            >
                            <div id="insp-date-picker" class="sdp-field-host"></div>
                            <div class="insp-field-error" id="insp-date-error" role="alert"></div>
                        </div>
                    </div>

                    <div class="insp-field">
                        <label class="insp-label" for="insp-address">
                            Address <span style="color:#ef4444;font-weight:700;">*</span>
                        </label>
                        <div class="insp-address-row">
                            <input
                                id="insp-address"
                                class="insp-input"
                                type="text"
                                name="address"
                                maxlength="255"
                                required
                                autocomplete="street-address"
                                placeholder="e.g. 123 Rizal Street, Quezon City"
                                value="{{ auth()->user()->address ?? '' }}"
                            >
                            <button type="button" class="insp-address-pin-btn" id="insp-pin-location-btn">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M12 21s-6-4.35-6-10a6 6 0 1 1 12 0c0 5.65-6 10-6 10z"/>
                                    <circle cx="12" cy="11" r="2.5"/>
                                </svg>
                                <span>Pin Location on Map</span>
                            </button>
                        </div>
                        <p class="insp-field-hint">You may type your address manually or pin your exact inspection location on the map.</p>
                        <div class="insp-field-note" id="insp-address-note" role="status" aria-live="polite"></div>
                        <div class="insp-field-error" id="insp-address-error" role="alert"></div>
                    </div>

                    <div class="insp-field">
                        <label class="insp-label" for="insp-address-details">
                            Address Additional Details <span style="font-weight:400;text-transform:none;letter-spacing:0;color:#7F92A3;">(optional)</span>
                        </label>
                        <input
                            id="insp-address-details"
                            class="insp-input"
                            type="text"
                            name="address_details"
                            maxlength="255"
                            placeholder="Unit, floor, landmark, gate code, or nearby reference"
                        >
                        <p class="insp-field-hint">Add landmark or access details to help the team find the exact location faster.</p>
                        <div class="insp-field-error" id="insp-address-details-error" role="alert"></div>
                    </div>

                    <input type="hidden" name="latitude" id="latitude">
                    <input type="hidden" name="longitude" id="longitude">

                    <div class="insp-field">
                        <label class="insp-label" for="insp-details">
                            Details / Notes <span style="color:#ef4444;font-weight:700;">*</span>
                        </label>
                        <textarea
                            id="insp-details"
                            class="insp-textarea"
                            name="details"
                            rows="4"
                            placeholder="Describe your property, any specific concerns, access instructions, or anything else our team should know before the visit..."
                            required
                        ></textarea>
                        <p class="insp-field-hint">Mention your property type, roof condition, or access instructions for our team.</p>
                        <div class="insp-field-error" id="insp-details-error" role="alert"></div>
                    </div>

                    <button type="submit" class="insp-submit-btn" id="insp-submit-btn">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <line x1="22" y1="2" x2="11" y2="13"/>
                            <polygon points="22 2 15 22 11 13 2 9 22 2"/>
                        </svg>
                        <span id="insp-submit-text">Submit Inspection Request</span>
                    </button>

                </form>
            </div>
        </div>

    </div>{{-- end left column --}}

    {{-- ── RIGHT: Site Inspection Checklist panel ── --}}
    <div>
        <div class="insp-panel">
            <div class="insp-panel-header">
                <p class="insp-panel-title">Site Inspection Checklist</p>
                <p class="insp-panel-sub">What our technicians will assess</p>
            </div>
            <div class="insp-panel-body">
                <div class="insp-checklist">

                    <div class="insp-checklist-item">
                        <div class="insp-check-icon">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2.5"><path d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <div class="insp-check-body">
                            <p class="insp-check-title">Roof Assessment</p>
                            <p class="insp-check-desc">Structure integrity, orientation, tilt angle, and available panel space are evaluated.</p>
                        </div>
                    </div>

                    <div class="insp-checklist-item">
                        <div class="insp-check-icon">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2.5"><path d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <div class="insp-check-body">
                            <p class="insp-check-title">Shading Analysis</p>
                            <p class="insp-check-desc">Nearby trees, buildings, and obstructions affecting solar generation are identified.</p>
                        </div>
                    </div>

                    <div class="insp-checklist-item">
                        <div class="insp-check-icon">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2.5"><path d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <div class="insp-check-body">
                            <p class="insp-check-title">Electrical System Review</p>
                            <p class="insp-check-desc">Existing wiring, breaker panels, and grid connection points are checked for compatibility.</p>
                        </div>
                    </div>

                    <div class="insp-checklist-item">
                        <div class="insp-check-icon">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2.5"><path d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <div class="insp-check-body">
                            <p class="insp-check-title">Site Measurements</p>
                            <p class="insp-check-desc">Accurate dimensions are recorded to confirm panel layout and system capacity.</p>
                        </div>
                    </div>

                    <div class="insp-checklist-item">
                        <div class="insp-check-icon">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2.5"><path d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <div class="insp-check-body">
                            <p class="insp-check-title">Mounting Feasibility</p>
                            <p class="insp-check-desc">Suitable mounting options and hardware are determined based on your roof type.</p>
                        </div>
                    </div>

                    <div class="insp-checklist-item">
                        <div class="insp-check-icon">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2.5"><path d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <div class="insp-check-body">
                            <p class="insp-check-title">Site Assessment Review</p>
                            <p class="insp-check-desc">All findings are compiled into a site assessment report so the SolMate team can plan the next installation steps.</p>
                        </div>
                    </div>

                </div>{{-- /.insp-checklist --}}

                <div class="insp-expect-box">
                    <p class="insp-expect-title">What to Expect</p>
                    <p class="insp-expect-text">The inspection typically takes 1–2 hours. Our team will contact you to confirm the schedule. After the visit, we will review the site findings and coordinate the next installation steps with you.</p>
                </div>
            </div>
        </div>
    </div>{{-- end right column --}}

</div>{{-- /.insp-layout --}}

<div class="insp-map-modal" id="insp-map-modal" aria-hidden="true">
    <div class="insp-map-dialog" role="dialog" aria-modal="true" aria-labelledby="insp-map-title">
        <div class="insp-map-header">
            <div>
                <h2 class="insp-map-title" id="insp-map-title">Pin Inspection Location</h2>
                <p class="insp-map-subtitle">Move the marker to your exact inspection spot, then confirm to save the coordinates.</p>
            </div>
            <button type="button" class="insp-map-close" id="insp-map-close-btn" aria-label="Close location picker">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                    <path d="M18 6 6 18"/>
                    <path d="m6 6 12 12"/>
                </svg>
            </button>
        </div>
        <div class="insp-map-body">
            <div class="insp-map-feedback" id="insp-map-feedback" role="status" aria-live="polite"></div>
            <div class="insp-map-search">
                <input
                    type="text"
                    id="insp-map-search-input"
                    class="insp-input insp-map-search-input"
                    placeholder="Search address or landmark"
                    autocomplete="off"
                >
                <button type="button" class="insp-map-btn insp-map-btn-soft-gold" id="insp-map-search-btn">Search</button>
            </div>
            <div class="insp-map-canvas" id="insp-map-canvas"></div>
            <div class="insp-map-actions">
                <button type="button" class="insp-map-btn insp-map-btn-soft-gold" id="insp-use-location-btn">Use Current Location</button>
                <div class="insp-map-actions-primary">
                    <button type="button" class="insp-map-btn" id="insp-map-cancel-btn">Cancel</button>
                    <button type="button" class="insp-map-btn insp-map-btn-gold" id="insp-map-confirm-btn">Confirm Location</button>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script
    src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
    integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
    crossorigin=""
></script>
@include('customer.partials.preferred-date-picker-script')
<script>
(function () {
    'use strict';

    function qs(s, ctx) { return (ctx || document).querySelector(s); }

    function showMsg(el, type, text) {
        if (!el) return;
        el.className = 'insp-msg show insp-msg-' + type;
        el.textContent = text;
    }
    function hideMsg(el) {
        if (el) { el.className = 'insp-msg'; el.textContent = ''; }
    }

    function escHtml(s) {
        return String(s || '')
            .replace(/&/g, '&amp;').replace(/</g, '&lt;')
            .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    function fmtPeso(val) {
        if (val === null || val === undefined || isNaN(Number(val))) return '\u2014';
        return '\u20b1' + Number(val).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function fmtDate(str) {
        if (!str) return '\u2014';
        try {
            return new Date(str).toLocaleDateString('en-PH', { year: 'numeric', month: 'short', day: 'numeric' });
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
        var resp = await fetch(endpoint, {
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

    /* DOM refs */
    var form         = qs('#insp-form');
    var formMsg      = qs('#insp-form-msg');
    var submitBtn    = qs('#insp-submit-btn');
    var submitText   = qs('#insp-submit-text');
    var addressInput = qs('#insp-address');
    var addressNote  = qs('#insp-address-note');
    var latitudeInput = qs('#latitude');
    var longitudeInput = qs('#longitude');
    var mapOpenBtn   = qs('#insp-pin-location-btn');
    var mapModal     = qs('#insp-map-modal');
    var mapCloseBtn  = qs('#insp-map-close-btn');
    var mapCancelBtn = qs('#insp-map-cancel-btn');
    var mapConfirmBtn = qs('#insp-map-confirm-btn');
    var mapSearchInput = qs('#insp-map-search-input');
    var mapSearchBtn = qs('#insp-map-search-btn');
    var useLocationBtn = qs('#insp-use-location-btn');
    var mapFeedback  = qs('#insp-map-feedback');
    var datePicker   = window.createPreferredDatePicker({
        inputId: 'insp-date',
        mountId: 'insp-date-picker',
        endpoint: '/api/preferred-date-availability?type=inspection',
        helperText: 'Booked dates are unavailable and cannot be selected.',
        fetchErrorText: 'Live reserved-date updates could not be loaded right now. The backend will still verify your preferred date when you submit.',
        placeholder: 'Select a preferred date'
    });

    var locationMap = null;
    var locationMarker = null;
    var pendingCoords = null;
    var defaultCoords = { lat: 14.2784, lng: 121.4169, zoom: 10 };
    var activeModalTrigger = null;
    var mapSearchInFlight = false;
    var reverseInFlight = false;

    function parseCoordinate(value) {
        var parsed = parseFloat(value);
        return Number.isFinite(parsed) ? parsed : null;
    }

    function getPreferredLanguage() {
        if (Array.isArray(navigator.languages) && navigator.languages.length > 0) {
            return navigator.languages.join(',');
        }

        if (navigator.language) {
            return navigator.language;
        }

        return document.documentElement.lang || 'en';
    }

    function getStoredCoords() {
        var lat = parseCoordinate(latitudeInput && latitudeInput.value);
        var lng = parseCoordinate(longitudeInput && longitudeInput.value);
        if (lat === null || lng === null) return null;
        return { lat: lat, lng: lng, zoom: 16 };
    }

    function setPendingCoords(latlng) {
        pendingCoords = {
            lat: Number(latlng.lat),
            lng: Number(latlng.lng),
        };
    }

    function clearMapFeedback() {
        if (!mapFeedback) return;
        mapFeedback.className = 'insp-map-feedback';
        mapFeedback.textContent = '';
    }

    function showMapFeedback(message, type) {
        if (!mapFeedback) return;
        mapFeedback.className = 'insp-map-feedback show insp-map-feedback-' + (type || 'info');
        mapFeedback.textContent = message;
    }

    function clearAddressNote() {
        if (!addressNote) return;
        addressNote.className = 'insp-field-note';
        addressNote.textContent = '';
    }

    function showAddressNote(message, type) {
        if (!addressNote) return;
        addressNote.className = 'insp-field-note show insp-field-note-' + (type || 'info');
        addressNote.textContent = message;
    }

    function buildNominatimUrl(path, params) {
        var query = new URLSearchParams(params);
        query.set('format', 'jsonv2');
        query.set('accept-language', getPreferredLanguage());

        return 'https://nominatim.openstreetmap.org/' + path + '?' + query.toString();
    }

    async function nominatimRequest(path, params) {
        var response = await fetch(buildNominatimUrl(path, params), {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
            },
        });

        if (!response.ok) {
            throw new Error('Nominatim request failed.');
        }

        return response.json();
    }

    function syncMarker(latlng, options) {
        if (!locationMap || !locationMarker) return;
        var shouldCenter = !options || options.center !== false;
        var normalized = L.latLng(latlng.lat, latlng.lng);
        locationMarker.setLatLng(normalized);
        setPendingCoords(normalized);
        if (shouldCenter) {
            locationMap.setView(normalized, (options && options.zoom) || locationMap.getZoom(), { animate: false });
        }
    }

    function ensureMap() {
        if (typeof window.L === 'undefined') {
            showMapFeedback('Map could not be loaded right now. Please try again later.', 'error');
            return false;
        }

        if (!locationMap) {
            locationMap = L.map('insp-map-canvas', {
                zoomControl: true,
                attributionControl: true,
            });

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap contributors',
            }).addTo(locationMap);

            locationMarker = L.marker([defaultCoords.lat, defaultCoords.lng], {
                draggable: true,
            }).addTo(locationMap);

            locationMap.on('click', function (event) {
                syncMarker(event.latlng);
            });

            locationMarker.on('dragend', function () {
                syncMarker(locationMarker.getLatLng(), { center: false });
            });
        }

        return true;
    }

    function openMapModal() {
        activeModalTrigger = document.activeElement;
        clearMapFeedback();
        mapModal.classList.add('show');
        mapModal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('insp-modal-open');

        if (!ensureMap()) return;

        var existingCoords = getStoredCoords();
        var initialCoords = existingCoords || defaultCoords;
        syncMarker({ lat: initialCoords.lat, lng: initialCoords.lng }, { zoom: initialCoords.zoom || 16 });

        window.setTimeout(function () {
            if (!locationMap) return;
            locationMap.invalidateSize();
            locationMap.setView([pendingCoords.lat, pendingCoords.lng], initialCoords.zoom || locationMap.getZoom(), { animate: false });
            if (mapSearchInput) {
                mapSearchInput.focus();
            }
        }, 120);
    }

    function closeMapModal() {
        mapModal.classList.remove('show');
        mapModal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('insp-modal-open');
        clearMapFeedback();

        if (activeModalTrigger && typeof activeModalTrigger.focus === 'function') {
            activeModalTrigger.focus();
        }
        activeModalTrigger = null;
    }

    async function applyConfirmedCoords() {
        if (!pendingCoords) return;

        if (reverseInFlight) return;
        reverseInFlight = true;
        mapConfirmBtn.disabled = true;
        mapConfirmBtn.textContent = 'Saving...';

        latitudeInput.value = pendingCoords.lat.toFixed(7);
        longitudeInput.value = pendingCoords.lng.toFixed(7);

        try {
            var result = await nominatimRequest('reverse', {
                lat: pendingCoords.lat,
                lon: pendingCoords.lng,
                zoom: 18,
                addressdetails: 1,
            });

            if (result && result.display_name) {
                addressInput.value = result.display_name;
                clearAddressNote();
            } else {
                throw new Error('Reverse geocoding returned no display name.');
            }
        } catch (error) {
            showAddressNote('Coordinates saved. Please review or type the address manually.', 'info');
        } finally {
            reverseInFlight = false;
            mapConfirmBtn.disabled = false;
            mapConfirmBtn.textContent = 'Confirm Location';
            closeMapModal();
        }
    }

    function useCurrentLocation() {
        clearMapFeedback();

        if (!navigator.geolocation) {
            showMapFeedback('Geolocation is not supported by this browser.', 'error');
            return;
        }

        navigator.geolocation.getCurrentPosition(
            function (position) {
                if (!ensureMap()) return;

                var coords = {
                    lat: position.coords.latitude,
                    lng: position.coords.longitude,
                };

                syncMarker(coords, { zoom: 17 });
                locationMap.invalidateSize();
            },
            function (error) {
                if (error && error.code === error.PERMISSION_DENIED) {
                    showMapFeedback('Location access denied. Please pin manually.', 'error');
                    return;
                }

                showMapFeedback('Location could not be determined. Please pin manually.', 'info');
            },
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0,
            }
        );
    }

    async function searchLocation() {
        if (!mapSearchInput) return;

        var query = mapSearchInput.value.trim();
        clearMapFeedback();

        if (!query) {
            showMapFeedback('Please enter an address or landmark.', 'error');
            mapSearchInput.focus();
            return;
        }

        if (mapSearchInFlight) return;
        mapSearchInFlight = true;
        mapSearchBtn.disabled = true;
        mapSearchBtn.textContent = 'Searching...';

        try {
            if (!ensureMap()) return;

            var results = await nominatimRequest('search', {
                q: query,
                limit: 1,
                addressdetails: 1,
            });

            if (!Array.isArray(results) || results.length === 0) {
                showMapFeedback('No location found. Try a more specific address.', 'info');
                return;
            }

            var firstResult = results[0];
            var lat = parseCoordinate(firstResult.lat);
            var lng = parseCoordinate(firstResult.lon);

            if (lat === null || lng === null) {
                showMapFeedback('No location found. Try a more specific address.', 'info');
                return;
            }

            syncMarker({ lat: lat, lng: lng }, { zoom: 17 });
            locationMap.invalidateSize();
        } catch (error) {
            showMapFeedback('Unable to search location right now. Please try again or pin manually.', 'error');
        } finally {
            mapSearchInFlight = false;
            mapSearchBtn.disabled = false;
            mapSearchBtn.textContent = 'Search';
        }
    }

    /* Field error helpers */
    function clearFieldErrors() {
        document.querySelectorAll('.insp-field-error').forEach(function (el) {
            el.textContent = ''; el.classList.remove('show');
        });
        document.querySelectorAll('.insp-input.has-error, .insp-textarea.has-error, .insp-select.has-error')
            .forEach(function (el) { el.classList.remove('has-error'); });
        document.querySelectorAll('.sdp-field-host.has-error')
            .forEach(function (el) { el.classList.remove('has-error'); });
    }

    var fieldErrorMap = {
        contact_number: 'insp-contact-error',
        address:        'insp-address-error',
        address_details:'insp-address-details-error',
        date_needed:    'insp-date-error',
        details:        'insp-details-error',
    };

    var fieldInputMap = {
        contact_number: 'insp-contact',
        address:        'insp-address',
        address_details:'insp-address-details',
        date_needed:    'insp-date-picker',
        details:        'insp-details',
    };

    function applyFieldErrors(errors) {
        Object.keys(errors).forEach(function (key) {
            var elId = fieldErrorMap[key];
            var inputId = fieldInputMap[key];
            if (elId) {
                var el = qs('#' + elId);
                if (el) {
                    el.textContent = Array.isArray(errors[key]) ? errors[key][0] : errors[key];
                    el.classList.add('show');
                }
            }
            if (inputId) {
                var input = qs('#' + inputId);
                if (input) {
                    input.classList.add('has-error');
                }
            }
        });
    }

    if (mapOpenBtn && mapModal && mapConfirmBtn && mapCancelBtn && mapCloseBtn && useLocationBtn) {
        mapOpenBtn.addEventListener('click', function () {
            openMapModal();
        });

        mapConfirmBtn.addEventListener('click', async function () {
            await applyConfirmedCoords();
        });

        mapCancelBtn.addEventListener('click', function () {
            closeMapModal();
        });

        mapCloseBtn.addEventListener('click', function () {
            closeMapModal();
        });

        useLocationBtn.addEventListener('click', function () {
            useCurrentLocation();
        });

        if (mapSearchBtn && mapSearchInput) {
            mapSearchBtn.addEventListener('click', function () {
                searchLocation();
            });

            mapSearchInput.addEventListener('keydown', function (event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    searchLocation();
                }
            });
        }

        mapModal.addEventListener('click', function (event) {
            if (event.target === mapModal) {
                closeMapModal();
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && mapModal.classList.contains('show')) {
                closeMapModal();
            }
        });
    }

    if (addressInput) {
        addressInput.addEventListener('input', function () {
            clearAddressNote();
        });
    }

    /* Form submit */
    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        clearFieldErrors();
        hideMsg(formMsg);

        var details    = qs('#insp-details').value.trim();
        var contact    = qs('#insp-contact').value.trim();
        var address    = addressInput.value.trim();
        var addressDetailsInput = qs('#insp-address-details');
        var addressDetails = addressDetailsInput ? addressDetailsInput.value.trim() : '';
        await datePicker.refreshAvailability();
        var dateNeeded = datePicker.getValue();

        var hasError = false;
        if (!details) {
            var de = qs('#insp-details-error');
            de.textContent = 'Details are required.';
            de.classList.add('show');
            qs('#insp-details').classList.add('has-error');
            hasError = true;
        }
        if (!contact) {
            var ce = qs('#insp-contact-error');
            ce.textContent = 'Contact number is required.';
            ce.classList.add('show');
            qs('#insp-contact').classList.add('has-error');
            hasError = true;
        }
        if (!address) {
            var ae = qs('#insp-address-error');
            ae.textContent = 'Address is required.';
            ae.classList.add('show');
            qs('#insp-address').classList.add('has-error');
            hasError = true;
        }
        if (datePicker.isSelectedDateUnavailable()) {
            var dte = qs('#insp-date-error');
            dte.textContent = 'Selected date is already reserved. Please choose another date.';
            dte.classList.add('show');
            qs('#insp-date-picker').classList.add('has-error');
            hasError = true;
        }
        if (hasError) return;

        submitBtn.disabled = true;
        submitText.textContent = 'Submitting...';

        var latitude = latitudeInput ? latitudeInput.value.trim() : '';
        var longitude = longitudeInput ? longitudeInput.value.trim() : '';
        var body = {
            details: details,
            contact_number: contact,
            address: address,
            address_details: addressDetails || null,
            latitude: latitude || null,
            longitude: longitude || null
        };
        if (dateNeeded) body.date_needed = dateNeeded;

        try {
            await apiRequest('/api/inspection-requests', { method: 'POST', body: body });
            showMsg(formMsg, 'success', 'Your inspection request has been submitted. Our team will contact you to confirm the schedule.');
            form.reset();
            clearAddressNote();
            datePicker.clear();
            formMsg.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        } catch (err) {
            applyFieldErrors(err.errors || {});
            showMsg(formMsg, 'error', err.message || 'Could not submit the request. Please try again.');
        } finally {
            submitBtn.disabled = false;
            submitText.textContent = 'Submit Inspection Request';
        }
    });

})();
</script>
@endpush
