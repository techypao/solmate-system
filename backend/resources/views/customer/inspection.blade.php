@extends('layouts.app', ['title' => 'Request Site Inspection'])

@section('content')
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
        color: #d4a017;
        margin: 0 0 8px;
    }
    .insp-hero-title {
        font-size: 30px;
        font-weight: 800;
        color: #102a43;
        margin: 0 0 10px;
        line-height: 1.2;
    }
    .insp-hero-title span { color: #d4a017; }
    .insp-hero-sub {
        font-size: 15px;
        color: #475569;
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
        color: #475569;
    }
    .insp-hero-step-num {
        width: 26px;
        height: 26px;
        border-radius: 50%;
        background: #cbd5e1;
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
    .insp-step-active .insp-hero-step-num { background: #d4a017; }
    .insp-step-active { color: #102a43; font-weight: 700; }
    .insp-step-connector {
        width: 20px;
        height: 2px;
        background: #cbd5e1;
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
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 2px 12px rgba(0,0,0,.04);
        margin-bottom: 20px;
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
        background: linear-gradient(135deg, #102a43, #1e4068);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .insp-card-title {
        font-size: 16px;
        font-weight: 700;
        color: #102a43;
        margin: 0;
    }
    .insp-card-subtitle {
        font-size: 12px;
        color: #94a3b8;
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
        color: #64748b;
        margin-bottom: 8px;
    }
    .insp-select,
    .insp-input,
    .insp-textarea {
        width: 100%;
        padding: 12px 16px;
        border: 1.5px solid #cbd5e1;
        border-radius: 10px;
        font-size: 14px;
        color: #1e293b;
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
        border-color: #d4a017;
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
        color: #94a3b8;
        margin-top: 6px;
    }
    .insp-field-error {
        font-size: 12px;
        color: #dc2626;
        margin-top: 6px;
        display: none;
    }
    .insp-field-error.show { display: block; }

    /* Two-column field row */
    .insp-field-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
        margin-bottom: 20px;
    }
    @media (max-width: 560px) { .insp-field-row { grid-template-columns: 1fr; } }
    .insp-field-row .insp-field { margin-bottom: 0; }

    /* Quote summary box */
    .insp-quote-summary {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
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
        color: #94a3b8;
        margin-bottom: 3px;
    }
    .insp-qs-value {
        font-size: 15px;
        font-weight: 700;
        color: #102a43;
    }
    .insp-qs-value.highlight { color: #d4a017; }
    .insp-qs-note {
        margin-top: 12px;
        padding-top: 12px;
        border-top: 1px solid #e2e8f0;
        font-size: 12px;
        color: #64748b;
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
        margin-top: 8px;
    }
    .insp-submit-btn:hover  { opacity: .92; transform: translateY(-1px); }
    .insp-submit-btn:active { transform: translateY(0); opacity: 1; }
    .insp-submit-btn:disabled { opacity: .6; cursor: not-allowed; transform: none; }

    /* Right panel */
    .insp-panel {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 2px 12px rgba(0,0,0,.04);
    }
    .insp-panel-header {
        padding: 16px 20px;
        background: #102a43;
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
        color: #102a43;
        margin: 0 0 2px;
    }
    .insp-check-desc {
        font-size: 12px;
        color: #64748b;
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
        color: #102a43;
        margin-bottom: 16px;
    }

    .insp-ir-grid { display: grid; gap: 14px; }
    .insp-ir-card {
        background: #fff;
        border: 1px solid #e2e8f0;
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
    .insp-ir-id { font-size: 15px; font-weight: 700; color: #102a43; }
    .insp-ir-date { font-size: 12px; color: #94a3b8; margin-top: 3px; }
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
        color: #94a3b8;
        margin-bottom: 2px;
    }
    .insp-ir-meta-value { font-size: 13px; font-weight: 600; color: #1e293b; }
    .insp-ir-details-row {
        font-size: 13px;
        color: #475569;
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
        color: #94a3b8;
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
    .insp-badge-pending      { background: #fef9c3; color: #a16207; }
    .insp-badge-assigned     { background: #dbeafe; color: #1d4ed8; }
    .insp-badge-in_progress  { background: #ede9fe; color: #6d28d9; }
    .insp-badge-completed    { background: #dcfce7; color: #15803d; }
    .insp-badge-cancelled    { background: #fee2e2; color: #dc2626; }
    .insp-badge-rescheduled  { background: #fef3c7; color: #d97706; }
    .insp-badge-default      { background: #f1f5f9; color: #475569; }

    /* Loading / empty */
    .insp-loading { text-align: center; padding: 32px; color: #94a3b8; font-size: 14px; display: none; }
    .insp-loading.show { display: block; }
    .insp-empty { text-align: center; padding: 48px 24px; color: #94a3b8; display: none; flex-direction: column; align-items: center; gap: 10px; }
    .insp-empty.show { display: flex; }
    .insp-empty svg { opacity: .4; }
    .insp-empty p { font-size: 14px; margin: 0; }
</style>

{{-- ═══ PAGE HERO ═══ --}}
<div class="insp-hero">
    <p class="insp-hero-eyebrow">Step 2 of 3</p>
    <h1 class="insp-hero-title">Request a <span>Site Inspection</span></h1>
    <p class="insp-hero-sub">Our technicians will visit your property to verify installation feasibility and assess the site before finalising your custom solar quotation.</p>
    <div class="insp-hero-steps">
        <div class="insp-hero-step insp-step-done">
            <span class="insp-hero-step-num">
                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5"><path d="M5 13l4 4L19 7"/></svg>
            </span>
            <span>Initial Quotation</span>
        </div>
        <div class="insp-step-connector"></div>
        <div class="insp-hero-step insp-step-active">
            <span class="insp-hero-step-num">2</span>
            <span>Site Inspection</span>
        </div>
        <div class="insp-step-connector"></div>
        <div class="insp-hero-step">
            <span class="insp-hero-step-num">3</span>
            <span>Final Quotation</span>
        </div>
    </div>
</div>

{{-- ═══ TWO-COLUMN LAYOUT ═══ --}}
<div class="insp-layout">

    {{-- ── LEFT: Form sections ── --}}
    <div>

        {{-- CARD 1: Select Quotation --}}
        <div class="insp-card">
            <div class="insp-card-header">
                <div class="insp-card-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#d4a017" stroke-width="2">
                        <path d="M9 12h6M9 16h6M9 8h3M5 4h14a1 1 0 011 1v14a1 1 0 01-1 1H5a1 1 0 01-1-1V5a1 1 0 011-1z"/>
                    </svg>
                </div>
                <div>
                    <p class="insp-card-title">Select Quotation</p>
                    <p class="insp-card-subtitle">Choose the initial quotation this inspection is for</p>
                </div>
            </div>
            <div class="insp-card-body">
                <div class="insp-field">
                    <label class="insp-label" for="insp-quote-select">Your Quotations</label>
                    <select id="insp-quote-select" class="insp-select" aria-label="Select a quotation">
                        <option value="">-- Loading your quotations... --</option>
                    </select>
                    <p class="insp-field-hint">Selecting a quotation will pre-fill the details field with its reference number.</p>
                </div>

                {{-- Summary panel -- shown when a quote is selected --}}
                <div id="insp-quote-summary" class="insp-quote-summary" style="display:none;" aria-live="polite">
                    <div class="insp-qs-grid">
                        <div>
                            <div class="insp-qs-label">Generated</div>
                            <div class="insp-qs-value" id="insp-qs-date">—</div>
                        </div>
                        <div>
                            <div class="insp-qs-label">System Size</div>
                            <div class="insp-qs-value" id="insp-qs-system">—</div>
                        </div>
                        <div>
                            <div class="insp-qs-label">Project Cost</div>
                            <div class="insp-qs-value highlight" id="insp-qs-cost">—</div>
                        </div>
                        <div>
                            <div class="insp-qs-label">ROI Period</div>
                            <div class="insp-qs-value highlight" id="insp-qs-roi">—</div>
                        </div>
                    </div>
                    <p class="insp-qs-note">The selected quote reference will be included in your inspection request details.</p>
                </div>
            </div>
        </div>

        {{-- CARD 2: Preferred Schedule & Details --}}
        <div class="insp-card">
            <div class="insp-card-header">
                <div class="insp-card-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#d4a017" stroke-width="2">
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
                            >
                            <div class="insp-field-error" id="insp-contact-error" role="alert"></div>
                        </div>

                        <div class="insp-field">
                            <label class="insp-label" for="insp-date">
                                Preferred Date <span style="font-weight:400;text-transform:none;letter-spacing:0;color:#94a3b8;">(optional)</span>
                            </label>
                            <input
                                id="insp-date"
                                class="insp-input"
                                type="date"
                                name="date_needed"
                                autocomplete="off"
                            >
                            <div class="insp-field-error" id="insp-date-error" role="alert"></div>
                        </div>
                    </div>

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
                            <p class="insp-check-title">Final Quotation Preparation</p>
                            <p class="insp-check-desc">All findings are compiled into an accurate, itemised final quotation for your approval.</p>
                        </div>
                    </div>

                </div>{{-- /.insp-checklist --}}

                <div class="insp-expect-box">
                    <p class="insp-expect-title">What to Expect</p>
                    <p class="insp-expect-text">The inspection typically takes 1–2 hours. Our team will contact you to confirm the schedule. After the visit, your final detailed quotation will be ready within a few business days.</p>
                </div>
            </div>
        </div>
    </div>{{-- end right column --}}

</div>{{-- /.insp-layout --}}

{{-- ═══ INSPECTION REQUEST HISTORY ═══ --}}
<div class="insp-history-section">
    <h2 class="insp-history-title">My Inspection Requests</h2>
    <div id="insp-history-loading" class="insp-loading">Loading your inspection requests...</div>
    <div id="insp-history-msg" class="insp-msg" role="alert"></div>
    <div id="insp-history-list" class="insp-ir-grid"></div>
    <div id="insp-history-empty" class="insp-empty">
        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5">
            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
            <line x1="16" y1="2" x2="16" y2="6"/>
            <line x1="8" y1="2" x2="8" y2="6"/>
            <line x1="3" y1="10" x2="21" y2="10"/>
        </svg>
        <p>No inspection requests yet. Fill out the form above to get started.</p>
    </div>
</div>

@endsection

@push('scripts')
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
    var quoteSelect  = qs('#insp-quote-select');
    var quoteSummary = qs('#insp-quote-summary');
    var form         = qs('#insp-form');
    var formMsg      = qs('#insp-form-msg');
    var submitBtn    = qs('#insp-submit-btn');
    var submitText   = qs('#insp-submit-text');
    var histLoading  = qs('#insp-history-loading');
    var histMsg      = qs('#insp-history-msg');
    var histList     = qs('#insp-history-list');
    var histEmpty    = qs('#insp-history-empty');

    var allQuotations = [];

    /* Populate quotation dropdown */
    function populateQuoteSelect(quotations) {
        var initialOnly = quotations.filter(function (q) {
            return String(q.quotation_type || 'initial').toLowerCase() === 'initial';
        });
        quoteSelect.innerHTML = '';
        var ph = document.createElement('option');
        ph.value = '';
        ph.textContent = initialOnly.length === 0
            ? '\u2014 No initial quotations found \u2014'
            : '\u2014 Select a quotation (optional) \u2014';
        quoteSelect.appendChild(ph);
        initialOnly.forEach(function (q) {
            var opt = document.createElement('option');
            opt.value = q.id;
            var cost = q.project_cost ? fmtPeso(q.project_cost) : '';
            opt.textContent = 'Quote #' + q.id + (cost ? '  \u00b7  ' + cost : '') + '  \u00b7  ' + fmtDate(q.created_at);
            quoteSelect.appendChild(opt);
        });
    }

    /* Quote select change handler */
    quoteSelect.addEventListener('change', function () {
        var id = quoteSelect.value;
        if (!id) { quoteSummary.style.display = 'none'; return; }
        var q = allQuotations.find(function (x) { return String(x.id) === String(id); });
        if (!q) { quoteSummary.style.display = 'none'; return; }

        qs('#insp-qs-date').textContent   = fmtDate(q.created_at);
        qs('#insp-qs-system').textContent = q.system_kw ? Number(q.system_kw).toFixed(2) + ' kW' : '\u2014';
        qs('#insp-qs-cost').innerHTML     = fmtPeso(q.project_cost);
        qs('#insp-qs-roi').textContent    = q.roi_years ? Number(q.roi_years).toFixed(1) + ' years' : '\u2014';
        quoteSummary.style.display = 'block';

        /* Pre-fill details if empty */
        var detailsEl = qs('#insp-details');
        if (detailsEl && !detailsEl.value.trim()) {
            detailsEl.value = 'Reference: Quote #' + q.id + '. ';
            detailsEl.focus();
            var len = detailsEl.value.length;
            detailsEl.setSelectionRange(len, len);
        }
    });

    /* Field error helpers */
    function clearFieldErrors() {
        document.querySelectorAll('.insp-field-error').forEach(function (el) {
            el.textContent = ''; el.classList.remove('show');
        });
        document.querySelectorAll('.insp-input.has-error, .insp-textarea.has-error, .insp-select.has-error')
            .forEach(function (el) { el.classList.remove('has-error'); });
    }

    var fieldErrorMap = {
        contact_number: 'insp-contact-error',
        date_needed:    'insp-date-error',
        details:        'insp-details-error',
    };

    function applyFieldErrors(errors) {
        Object.keys(errors).forEach(function (key) {
            var elId = fieldErrorMap[key];
            if (elId) {
                var el = qs('#' + elId);
                if (el) {
                    el.textContent = Array.isArray(errors[key]) ? errors[key][0] : errors[key];
                    el.classList.add('show');
                }
            }
        });
    }

    /* Form submit */
    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        clearFieldErrors();
        hideMsg(formMsg);

        var details    = qs('#insp-details').value.trim();
        var contact    = qs('#insp-contact').value.trim();
        var dateNeeded = qs('#insp-date').value;

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
        if (hasError) return;

        submitBtn.disabled = true;
        submitText.textContent = 'Submitting...';

        var body = { details: details, contact_number: contact };
        if (dateNeeded) body.date_needed = dateNeeded;

        try {
            await apiRequest('/api/inspection-requests', { method: 'POST', body: body });
            showMsg(formMsg, 'success', 'Your inspection request has been submitted. Our team will contact you to confirm the schedule.');
            form.reset();
            quoteSelect.value = '';
            quoteSummary.style.display = 'none';
            await loadHistory();
            formMsg.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        } catch (err) {
            applyFieldErrors(err.errors || {});
            showMsg(formMsg, 'error', err.message || 'Could not submit the request. Please try again.');
        } finally {
            submitBtn.disabled = false;
            submitText.textContent = 'Submit Inspection Request';
        }
    });

    /* Status badge */
    function statusBadge(status) {
        var s   = String(status || 'pending').toLowerCase().replace(/ /g, '_');
        var map = {
            pending:     'insp-badge-pending',
            assigned:    'insp-badge-assigned',
            in_progress: 'insp-badge-in_progress',
            completed:   'insp-badge-completed',
            cancelled:   'insp-badge-cancelled',
            rescheduled: 'insp-badge-rescheduled',
        };
        var cls   = map[s] || 'insp-badge-default';
        var label = s.replace(/_/g, ' ');
        label = label.charAt(0).toUpperCase() + label.slice(1);
        return '<span class="insp-badge ' + escHtml(cls) + '">' + escHtml(label) + '</span>';
    }

    /* Render inspection request cards */
    function renderHistory(items) {
        if (!items || items.length === 0) {
            histList.innerHTML = '';
            histEmpty.classList.add('show');
            return;
        }
        histEmpty.classList.remove('show');
        histList.innerHTML = items.map(function (ir) {
            return '<div class="insp-ir-card">'
                + '<div class="insp-ir-card-header">'
                +   '<div>'
                +     '<div class="insp-ir-id">Inspection #' + escHtml(ir.id) + '</div>'
                +     '<div class="insp-ir-date">Submitted ' + fmtDate(ir.created_at) + '</div>'
                +   '</div>'
                +   statusBadge(ir.status)
                + '</div>'
                + '<div class="insp-ir-meta">'
                +   '<div class="insp-ir-meta-item">'
                +     '<div class="insp-ir-meta-label">Contact</div>'
                +     '<div class="insp-ir-meta-value">' + escHtml(ir.contact_number || '\u2014') + '</div>'
                +   '</div>'
                +   '<div class="insp-ir-meta-item">'
                +     '<div class="insp-ir-meta-label">Preferred Date</div>'
                +     '<div class="insp-ir-meta-value">' + fmtDate(ir.date_needed) + '</div>'
                +   '</div>'
                + '</div>'
                + '<div class="insp-ir-details-row">'
                +   '<div class="insp-ir-details-label">Details</div>'
                +   escHtml(ir.details || '\u2014')
                + '</div>'
                + '</div>';
        }).join('');
    }

    /* Load history */
    async function loadHistory() {
        histLoading.classList.add('show');
        hideMsg(histMsg);
        try {
            var data  = await apiRequest('/api/inspection-requests');
            var items = Array.isArray(data) ? data : (data.data || []);
            renderHistory(items);
        } catch (err) {
            showMsg(histMsg, 'error', err.message || 'Could not load inspection requests.');
        } finally {
            histLoading.classList.remove('show');
        }
    }

    /* Load quotations for dropdown */
    async function loadQuotations() {
        try {
            var data = await apiRequest('/api/quotations');
            allQuotations = Array.isArray(data) ? data : (data.data || []);
            populateQuoteSelect(allQuotations);
        } catch (err) {
            quoteSelect.innerHTML = '<option value="">\u2014 Could not load quotations \u2014</option>';
        }
    }

    /* Bootstrap */
    loadQuotations();
    loadHistory();

})();
</script>
@endpush
