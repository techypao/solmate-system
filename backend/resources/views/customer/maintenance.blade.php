@extends('layouts.app', ['title' => 'Book Maintenance Service'])

@section('content')
<style>
    /* Maintenance request page */
    .mnt-page {
        display: flex;
        flex-direction: column;
        gap: 32px;
    }

    .mnt-hero {
        background: linear-gradient(135deg, #f8fbff 0%, #eef6ff 54%, #e6f0ff 100%);
        border: 1px solid #dbeafe;
        border-radius: 20px;
        padding: 36px 40px;
        position: relative;
        overflow: hidden;
    }
    .mnt-hero::after {
        content: '';
        position: absolute;
        left: -48px;
        bottom: -52px;
        width: 230px;
        height: 230px;
        border-radius: 50%;
        background: rgba(59, 130, 246, 0.10);
        pointer-events: none;
    }
    .mnt-hero-inner {
        position: relative;
        z-index: 1;
        display: grid;
        grid-template-columns: minmax(0, 1fr) 250px;
        gap: 28px;
        align-items: center;
    }
    .mnt-hero-eyebrow {
        margin: 0 0 8px;
        font-size: 12px;
        font-weight: 800;
        letter-spacing: 1.1px;
        text-transform: uppercase;
        color: #d4a017;
    }
    .mnt-hero-title {
        margin: 0 0 10px;
        font-size: 32px;
        line-height: 1.15;
        font-weight: 800;
        color: #102a43;
    }
    .mnt-hero-title span { color: #d4a017; }
    .mnt-hero-sub {
        margin: 0;
        max-width: 630px;
        font-size: 15px;
        line-height: 1.7;
        color: #475569;
    }
    .mnt-hero-steps {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-top: 22px;
    }
    .mnt-hero-step {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 12px;
        border-radius: 999px;
        background: #ffffff;
        border: 1px solid #dbeafe;
        font-size: 12px;
        font-weight: 700;
        color: #334155;
    }
    .mnt-hero-step-num {
        width: 22px;
        height: 22px;
        border-radius: 50%;
        background: #102a43;
        color: #ffffff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        font-weight: 800;
    }
    .mnt-hero-art {
        display: flex;
        justify-content: center;
    }

    .mnt-layout {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 320px;
        gap: 24px;
        align-items: start;
    }

    .mnt-card,
    .mnt-panel,
    .mnt-history-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.04);
    }
    .mnt-card + .mnt-card { margin-top: 20px; }

    .mnt-card-header {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 20px 24px;
        border-bottom: 1px solid #f1f5f9;
    }
    .mnt-card-icon {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        background: linear-gradient(135deg, #102a43, #1e4068);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .mnt-card-title {
        margin: 0;
        font-size: 16px;
        font-weight: 800;
        color: #102a43;
    }
    .mnt-card-subtitle {
        margin: 3px 0 0;
        font-size: 12px;
        color: #94a3b8;
    }
    .mnt-card-body { padding: 24px; }

    .mnt-field { margin-bottom: 20px; }
    .mnt-field:last-child { margin-bottom: 0; }
    .mnt-field-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
        margin-bottom: 20px;
    }
    .mnt-field-row .mnt-field { margin-bottom: 0; }
    .mnt-label {
        display: block;
        margin-bottom: 8px;
        font-size: 12px;
        font-weight: 800;
        letter-spacing: 0.6px;
        text-transform: uppercase;
        color: #64748b;
    }
    .mnt-input,
    .mnt-select,
    .mnt-textarea {
        width: 100%;
        box-sizing: border-box;
        border: 1.5px solid #cbd5e1;
        border-radius: 12px;
        background: #ffffff;
        color: #1e293b;
        font: inherit;
        padding: 12px 15px;
        transition: border-color 0.2s, box-shadow 0.2s;
    }
    .mnt-select {
        appearance: none;
        -webkit-appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 14px center;
        padding-right: 40px;
    }
    .mnt-textarea {
        min-height: 110px;
        resize: vertical;
    }
    .mnt-input:focus,
    .mnt-select:focus,
    .mnt-textarea:focus {
        outline: none;
        border-color: #d4a017;
        box-shadow: 0 0 0 3px rgba(212, 160, 23, 0.12);
    }
    .mnt-input.has-error,
    .mnt-select.has-error,
    .mnt-textarea.has-error {
        border-color: #ef4444;
    }
    .mnt-field-hint {
        margin: 7px 0 0;
        font-size: 12px;
        line-height: 1.5;
        color: #94a3b8;
    }
    .mnt-field-error {
        display: none;
        margin-top: 6px;
        font-size: 12px;
        color: #dc2626;
    }
    .mnt-field-error.show { display: block; }

    .mnt-choice-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }
    .mnt-choice {
        position: relative;
        border: 1.5px solid #dbeafe;
        border-radius: 16px;
        padding: 16px;
        background: #f8fbff;
        cursor: pointer;
        transition: border-color 0.2s, box-shadow 0.2s, transform 0.15s;
    }
    .mnt-choice:hover {
        border-color: #bfdbfe;
        box-shadow: 0 10px 22px rgba(59, 130, 246, 0.08);
        transform: translateY(-1px);
    }
    .mnt-choice.is-selected {
        border-color: #d4a017;
        background: #fffbeb;
        box-shadow: 0 12px 26px rgba(212, 160, 23, 0.10);
    }
    .mnt-choice input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }
    .mnt-choice-title {
        margin: 0 0 5px;
        font-size: 14px;
        font-weight: 800;
        color: #102a43;
    }
    .mnt-choice-desc {
        margin: 0;
        font-size: 12px;
        line-height: 1.55;
        color: #64748b;
    }
    .mnt-choice-tag {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 28px;
        height: 28px;
        border-radius: 999px;
        background: #dbeafe;
        color: #1d4ed8;
        font-size: 11px;
        font-weight: 800;
        margin-bottom: 10px;
    }
    .mnt-choice.is-selected .mnt-choice-tag {
        background: #d4a017;
        color: #ffffff;
    }

    .mnt-msg {
        display: none;
        margin-bottom: 18px;
        padding: 12px 16px;
        border-radius: 12px;
        font-size: 14px;
        line-height: 1.5;
    }
    .mnt-msg.show { display: block; }
    .mnt-msg-success {
        background: #dcfce7;
        border: 1px solid #bbf7d0;
        color: #166534;
    }
    .mnt-msg-error {
        background: #fee2e2;
        border: 1px solid #fecaca;
        color: #991b1b;
    }

    .mnt-submit-btn {
        width: 100%;
        border: none;
        border-radius: 12px;
        padding: 15px 20px;
        background: linear-gradient(135deg, #f4c542, #d4a017);
        color: #ffffff;
        font-size: 15px;
        font-weight: 800;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        cursor: pointer;
        transition: transform 0.15s, opacity 0.2s;
        margin-top: 8px;
    }
    .mnt-submit-btn:hover {
        opacity: 0.95;
        transform: translateY(-1px);
    }
    .mnt-submit-btn:disabled {
        opacity: 0.65;
        cursor: not-allowed;
        transform: none;
    }

    .mnt-panel {
        overflow: hidden;
        position: sticky;
        top: 24px;
    }
    .mnt-panel-header {
        background: #102a43;
        padding: 18px 20px;
        color: #ffffff;
    }
    .mnt-panel-title {
        margin: 0 0 4px;
        font-size: 14px;
        font-weight: 800;
    }
    .mnt-panel-sub {
        margin: 0;
        font-size: 12px;
        color: rgba(255, 255, 255, 0.70);
    }
    .mnt-panel-body { padding: 20px; }
    .mnt-checklist {
        display: flex;
        flex-direction: column;
        gap: 14px;
    }
    .mnt-check {
        display: flex;
        align-items: flex-start;
        gap: 12px;
    }
    .mnt-check-icon {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: #eff6ff;
        color: #1d4ed8;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        margin-top: 2px;
    }
    .mnt-check-title {
        margin: 0 0 3px;
        font-size: 13px;
        font-weight: 800;
        color: #102a43;
    }
    .mnt-check-desc {
        margin: 0;
        font-size: 12px;
        line-height: 1.55;
        color: #64748b;
    }
    .mnt-expect {
        margin-top: 20px;
        padding: 14px 16px;
        border-radius: 12px;
        background: #fffbeb;
        border: 1px solid #fde68a;
    }
    .mnt-expect-title {
        margin: 0 0 6px;
        font-size: 13px;
        font-weight: 800;
        color: #92400e;
    }
    .mnt-expect-text {
        margin: 0;
        font-size: 12px;
        line-height: 1.6;
        color: #78350f;
    }

    .mnt-history-title {
        margin: 0 0 14px;
        font-size: 18px;
        font-weight: 800;
        color: #102a43;
    }
    .mnt-loading {
        display: none;
        text-align: center;
        padding: 28px;
        color: #94a3b8;
        font-size: 14px;
    }
    .mnt-loading.show { display: block; }
    .mnt-empty {
        display: none;
        text-align: center;
        padding: 40px 22px;
        color: #94a3b8;
        font-size: 14px;
    }
    .mnt-empty.show { display: block; }
    .mnt-history-list {
        display: grid;
        gap: 14px;
    }
    .mnt-history-card {
        padding: 20px 22px;
    }
    .mnt-history-top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 12px;
    }
    .mnt-history-id {
        margin: 0;
        font-size: 15px;
        font-weight: 800;
        color: #102a43;
    }
    .mnt-history-date {
        margin: 4px 0 0;
        font-size: 12px;
        color: #94a3b8;
    }
    .mnt-badge {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        padding: 4px 10px;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 0.4px;
        text-transform: uppercase;
        white-space: nowrap;
    }
    .mnt-badge-pending { background: #fef3c7; color: #a16207; }
    .mnt-badge-approved,
    .mnt-badge-scheduled,
    .mnt-badge-assigned { background: #dbeafe; color: #1d4ed8; }
    .mnt-badge-in_progress { background: #ede9fe; color: #6d28d9; }
    .mnt-badge-completed { background: #dcfce7; color: #15803d; }
    .mnt-badge-cancelled,
    .mnt-badge-declined { background: #fee2e2; color: #dc2626; }
    .mnt-badge-default { background: #f1f5f9; color: #475569; }

    .mnt-history-meta {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 10px;
        margin-bottom: 12px;
    }
    .mnt-history-chip {
        padding: 10px 12px;
        border-radius: 12px;
        background: #f8fafc;
    }
    .mnt-history-chip-label {
        margin-bottom: 3px;
        font-size: 10px;
        font-weight: 800;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        color: #94a3b8;
    }
    .mnt-history-chip-value {
        font-size: 13px;
        font-weight: 700;
        color: #1e293b;
    }
    .mnt-history-details {
        margin: 0;
        padding: 12px 14px;
        border-radius: 12px;
        background: #f8fafc;
        color: #475569;
        font-size: 13px;
        line-height: 1.65;
        white-space: pre-wrap;
    }

    @media (max-width: 940px) {
        .mnt-layout,
        .mnt-hero-inner {
            grid-template-columns: 1fr;
        }
        .mnt-panel {
            position: static;
        }
        .mnt-hero-art {
            display: none;
        }
    }
    @media (max-width: 680px) {
        .mnt-hero {
            padding: 28px 22px;
        }
        .mnt-hero-title {
            font-size: 25px;
        }
        .mnt-card-body,
        .mnt-card-header {
            padding-left: 18px;
            padding-right: 18px;
        }
        .mnt-field-row,
        .mnt-choice-grid,
        .mnt-history-meta {
            grid-template-columns: 1fr;
        }
    }
@include('customer.partials.preferred-date-picker-styles')
</style>

<div class="mnt-page">
    <section class="mnt-hero" aria-label="Maintenance request hero">
        <div class="mnt-hero-inner">
            <div>
                <p class="mnt-hero-eyebrow">Customer Service Booking</p>
                <h1 class="mnt-hero-title">Book <span>Maintenance Service</span></h1>
                <p class="mnt-hero-sub">Schedule a maintenance appointment for your solar system concerns and routine check-ups. Select the concern type, describe what needs attention, and choose your preferred appointment schedule.</p>
                <div class="mnt-hero-steps" aria-label="Maintenance booking steps">
                    <span class="mnt-hero-step"><span class="mnt-hero-step-num">1</span>Select concern</span>
                    <span class="mnt-hero-step"><span class="mnt-hero-step-num">2</span>Describe request</span>
                    <span class="mnt-hero-step"><span class="mnt-hero-step-num">3</span>Choose schedule</span>
                </div>
            </div>
            <div class="mnt-hero-art" aria-hidden="true">
                <svg width="210" height="170" viewBox="0 0 210 170" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect x="28" y="54" width="116" height="74" rx="12" fill="#ffffff" stroke="#bfdbfe" stroke-width="2"/>
                    <rect x="40" y="66" width="24" height="20" rx="4" fill="#dbeafe"/>
                    <rect x="68" y="66" width="24" height="20" rx="4" fill="#e0efff"/>
                    <rect x="96" y="66" width="24" height="20" rx="4" fill="#dbeafe"/>
                    <rect x="40" y="90" width="24" height="20" rx="4" fill="#e0efff"/>
                    <rect x="68" y="90" width="24" height="20" rx="4" fill="#ffefb0"/>
                    <rect x="96" y="90" width="24" height="20" rx="4" fill="#e0efff"/>
                    <path d="M152 48L177 23C181 19 188 19 192 23C196 27 196 34 192 38L167 63" stroke="#d4a017" stroke-width="8" stroke-linecap="round"/>
                    <circle cx="156" cy="99" r="20" fill="#eff6ff" stroke="#bfdbfe" stroke-width="2"/>
                    <path d="M156 88V99L163 106" stroke="#1d4ed8" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
        </div>
    </section>

    <div class="mnt-layout">
        <div>
            <div class="mnt-card">
                <div class="mnt-card-header">
                    <div class="mnt-card-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#f4c542" stroke-width="2">
                            <circle cx="12" cy="12" r="3"/>
                            <path d="M19.07 4.93l-1.41 1.41M4.93 4.93l1.41 1.41M19.07 19.07l-1.41-1.41M4.93 19.07l1.41-1.41M12 2v2M12 20v2M2 12h2M20 12h2"/>
                        </svg>
                    </div>
                    <div>
                        <p class="mnt-card-title">Maintenance Service Type</p>
                        <p class="mnt-card-subtitle">Choose the maintenance concern you want to request</p>
                    </div>
                </div>
                <div class="mnt-card-body">
                    <div class="mnt-field">
                        <label class="mnt-label">Maintenance Concern</label>
                        <div class="mnt-choice-grid" id="mnt-type-grid">
                            <label class="mnt-choice">
                                <input type="radio" name="maintenance_type" value="Battery check-up">
                                <span class="mnt-choice-tag">A</span>
                                <p class="mnt-choice-title">Battery Check-Up</p>
                                <p class="mnt-choice-desc">For battery health review, charging issues, or preventive battery service.</p>
                            </label>
                            <label class="mnt-choice">
                                <input type="radio" name="maintenance_type" value="Panel cleaning">
                                <span class="mnt-choice-tag">B</span>
                                <p class="mnt-choice-title">Panel Cleaning</p>
                                <p class="mnt-choice-desc">For dirt buildup, output drops, or scheduled solar panel cleaning.</p>
                            </label>
                            <label class="mnt-choice">
                                <input type="radio" name="maintenance_type" value="Inverter check">
                                <span class="mnt-choice-tag">C</span>
                                <p class="mnt-choice-title">Inverter Check</p>
                                <p class="mnt-choice-desc">For inverter alerts, unusual readings, or operational checks.</p>
                            </label>
                            <label class="mnt-choice">
                                <input type="radio" name="maintenance_type" value="Wiring inspection">
                                <span class="mnt-choice-tag">D</span>
                                <p class="mnt-choice-title">Wiring Inspection</p>
                                <p class="mnt-choice-desc">For electrical connection review, cable concerns, or safety checks.</p>
                            </label>
                            <label class="mnt-choice">
                                <input type="radio" name="maintenance_type" value="General system check">
                                <span class="mnt-choice-tag">E</span>
                                <p class="mnt-choice-title">General System Check</p>
                                <p class="mnt-choice-desc">For regular maintenance, performance review, or overall system inspection.</p>
                            </label>
                            <label class="mnt-choice">
                                <input type="radio" name="maintenance_type" value="Other custom concern">
                                <span class="mnt-choice-tag">F</span>
                                <p class="mnt-choice-title">Other or Custom Concern</p>
                                <p class="mnt-choice-desc">For anything else you want the technician to review during the visit.</p>
                            </label>
                        </div>
                        <div class="mnt-field-error" id="mnt-type-error" role="alert"></div>
                    </div>
                </div>
            </div>

            <div class="mnt-card">
                <div class="mnt-card-header">
                    <div class="mnt-card-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#f4c542" stroke-width="2">
                            <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
                            <polyline points="14 2 14 8 20 8"/>
                            <line x1="16" y1="13" x2="8" y2="13"/>
                            <line x1="16" y1="17" x2="8" y2="17"/>
                        </svg>
                    </div>
                    <div>
                        <p class="mnt-card-title">Maintenance Concern Details</p>
                        <p class="mnt-card-subtitle">Describe the issue or the maintenance work you want scheduled</p>
                    </div>
                </div>
                <div class="mnt-card-body">
                    <div class="mnt-field">
                        <label class="mnt-label" for="mnt-description">Concern Description</label>
                        <textarea id="mnt-description" class="mnt-textarea" placeholder="Describe the concern here. Example: battery check-up needed, reduced output, unusual inverter issue, or periodic maintenance request."></textarea>
                        <p class="mnt-field-hint">Share system symptoms, performance concerns, or the exact maintenance work you want the technician to perform.</p>
                        <div class="mnt-field-error" id="mnt-description-error" role="alert"></div>
                    </div>

                    <div class="mnt-field">
                        <label class="mnt-label" for="mnt-extra-concern">Additional Notes</label>
                        <textarea id="mnt-extra-concern" class="mnt-textarea" style="min-height:92px;" placeholder="Optional: add error code details, recent observations, site reminders, or custom maintenance instructions."></textarea>
                    </div>
                </div>
            </div>

            <div class="mnt-card">
                <div class="mnt-card-header">
                    <div class="mnt-card-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#f4c542" stroke-width="2">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                            <line x1="16" y1="2" x2="16" y2="6"/>
                            <line x1="8" y1="2" x2="8" y2="6"/>
                            <line x1="3" y1="10" x2="21" y2="10"/>
                        </svg>
                    </div>
                    <div>
                        <p class="mnt-card-title">Preferred Schedule</p>
                        <p class="mnt-card-subtitle">Pick the best date, time, and contact details for your maintenance visit</p>
                    </div>
                </div>
                <div class="mnt-card-body">
                    <div id="mnt-form-msg" class="mnt-msg" role="alert"></div>

                    <form id="mnt-form" novalidate>
                        <div class="mnt-field-row">
                            <div class="mnt-field">
                                <label class="mnt-label" for="mnt-contact">Contact Number</label>
                                <input id="mnt-contact" class="mnt-input" type="tel" maxlength="30" autocomplete="tel" placeholder="e.g. 09171234567">
                                <div class="mnt-field-error" id="mnt-contact-error" role="alert"></div>
                            </div>
                            <div class="mnt-field">
                                <label class="mnt-label" for="mnt-date">Preferred Date</label>
                                <input id="mnt-date" class="mnt-input" type="hidden" autocomplete="off">
                                <div id="mnt-date-picker" class="sdp-field-host"></div>
                                <div class="mnt-field-error" id="mnt-date-error" role="alert"></div>
                            </div>
                        </div>

                        <div class="mnt-field">
                            <label class="mnt-label" for="mnt-address">Address</label>
                            <input id="mnt-address" class="mnt-input" type="text" maxlength="255" autocomplete="street-address" placeholder="e.g. 123 Rizal Street, Quezon City" value="{{ auth()->user()->address ?? '' }}">
                            <div class="mnt-field-error" id="mnt-address-error" role="alert"></div>
                        </div>

                        <div class="mnt-field-row">
                            <div class="mnt-field">
                                <label class="mnt-label" for="mnt-time">Preferred Time</label>
                                <select id="mnt-time" class="mnt-select">
                                    <option value="">Select preferred time</option>
                                    <option value="Morning (8:00 AM - 11:00 AM)">Morning (8:00 AM - 11:00 AM)</option>
                                    <option value="Midday (11:00 AM - 1:00 PM)">Midday (11:00 AM - 1:00 PM)</option>
                                    <option value="Afternoon (1:00 PM - 4:00 PM)">Afternoon (1:00 PM - 4:00 PM)</option>
                                </select>
                                <div class="mnt-field-error" id="mnt-time-error" role="alert"></div>
                            </div>
                            <div class="mnt-field">
                                <label class="mnt-label" for="mnt-visit-note">Visit Note</label>
                                <input id="mnt-visit-note" class="mnt-input" type="text" placeholder="Optional schedule or access note">
                            </div>
                        </div>

                        <p class="mnt-field-hint" style="margin-top:-2px;margin-bottom:16px;">Preferred time and visit notes are included in the request details so the technician can review them before confirmation.</p>

                        <button type="submit" class="mnt-submit-btn" id="mnt-submit-btn">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <line x1="22" y1="2" x2="11" y2="13"/>
                                <polygon points="22 2 15 22 11 13 2 9 22 2"/>
                            </svg>
                            <span id="mnt-submit-text">Submit Maintenance Request</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <aside class="mnt-panel" aria-label="Maintenance request checklist">
            <div class="mnt-panel-header">
                <p class="mnt-panel-title">Maintenance Request Checklist</p>
                <p class="mnt-panel-sub">What to expect before confirmation</p>
            </div>
            <div class="mnt-panel-body">
                <div class="mnt-checklist">
                    <div class="mnt-check">
                        <div class="mnt-check-icon">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <div>
                            <p class="mnt-check-title">Select the maintenance concern</p>
                            <p class="mnt-check-desc">Choose the closest concern type such as battery check-up, panel cleaning, inverter check, or a custom issue.</p>
                        </div>
                    </div>
                    <div class="mnt-check">
                        <div class="mnt-check-icon">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <div>
                            <p class="mnt-check-title">Add important system notes</p>
                            <p class="mnt-check-desc">Describe symptoms, reduced performance, inverter behavior, or any maintenance work you want prioritized.</p>
                        </div>
                    </div>
                    <div class="mnt-check">
                        <div class="mnt-check-icon">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <div>
                            <p class="mnt-check-title">Choose your preferred schedule</p>
                            <p class="mnt-check-desc">Select the date and time window that works best so the visit can be coordinated around your availability.</p>
                        </div>
                    </div>
                    <div class="mnt-check">
                        <div class="mnt-check-icon">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <div>
                            <p class="mnt-check-title">Technician review follows</p>
                            <p class="mnt-check-desc">The request details are reviewed first so the right technician can prepare for the concern type you selected.</p>
                        </div>
                    </div>
                    <div class="mnt-check">
                        <div class="mnt-check-icon">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <div>
                            <p class="mnt-check-title">Appointment confirmation</p>
                            <p class="mnt-check-desc">SolMate will contact you to confirm the maintenance appointment and any follow-up preparation needed.</p>
                        </div>
                    </div>
                </div>

                <div class="mnt-expect">
                    <p class="mnt-expect-title">What to Expect</p>
                    <p class="mnt-expect-text">Your maintenance request is submitted as a customer service booking. After review, the team will confirm the schedule and provide the next service update.</p>
                </div>
            </div>
        </aside>
    </div>

</div>
@endsection

@push('scripts')
@include('customer.partials.preferred-date-picker-script')
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
        return String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function fmtDate(value) {
        if (!value) return '-';
        try {
            return new Date(value).toLocaleDateString('en-PH', {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            });
        } catch (e) {
            return value;
        }
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
            body: (options && options.body !== undefined) ? JSON.stringify(options.body) : undefined
        });

        var payload = await response.json().catch(function () { return {}; });
        if (!response.ok) {
            var error = new Error(payload.message || 'Request failed.');
            error.status = response.status;
            error.errors = payload.errors || {};
            throw error;
        }
        return payload;
    }

    function showMsg(el, type, text) {
        if (!el) return;
        el.className = 'mnt-msg show mnt-msg-' + type;
        el.textContent = text;
    }

    function hideMsg(el) {
        if (!el) return;
        el.className = 'mnt-msg';
        el.textContent = '';
    }

    function clearFieldErrors() {
        qsa('.mnt-field-error').forEach(function (el) {
            el.textContent = '';
            el.classList.remove('show');
        });
        qsa('.mnt-input, .mnt-select, .mnt-textarea').forEach(function (el) {
            el.classList.remove('has-error');
        });
        qsa('.sdp-field-host').forEach(function (el) {
            el.classList.remove('has-error');
        });
    }

    function showFieldError(inputId, errorId, message) {
        var input = qs('#' + inputId);
        var error = qs('#' + errorId);
        if (input) input.classList.add('has-error');
        if (error) {
            error.textContent = message;
            error.classList.add('show');
        }
    }

    var form = qs('#mnt-form');
    var formMsg = qs('#mnt-form-msg');
    var submitBtn = qs('#mnt-submit-btn');
    var submitText = qs('#mnt-submit-text');
    var datePicker = window.createPreferredDatePicker({
        inputId: 'mnt-date',
        mountId: 'mnt-date-picker',
        helperText: 'Booked dates are unavailable and cannot be selected.',
        fetchErrorText: 'Schedule availability could not be refreshed right now. The backend will still verify your preferred date when you submit.',
        placeholder: 'Select a preferred date'
    });

    qsa('.mnt-choice').forEach(function (choice) {
        var radio = qs('input[type="radio"]', choice);
        choice.addEventListener('click', function () {
            if (radio) radio.checked = true;
            qsa('.mnt-choice').forEach(function (item) {
                item.classList.remove('is-selected');
            });
            choice.classList.add('is-selected');
            var err = qs('#mnt-type-error');
            if (err) {
                err.textContent = '';
                err.classList.remove('show');
            }
        });
    });

    form.addEventListener('submit', async function (event) {
        event.preventDefault();
        clearFieldErrors();
        hideMsg(formMsg);

        var selectedTypeInput = qs('input[name="maintenance_type"]:checked');
        var selectedType = selectedTypeInput ? selectedTypeInput.value : '';
        var description = qs('#mnt-description').value.trim();
        var extraConcern = qs('#mnt-extra-concern').value.trim();
        var contact = qs('#mnt-contact').value.trim();
        await datePicker.refreshAvailability();
        var dateNeeded = datePicker.getValue();
        var time = qs('#mnt-time').value.trim();
        var visitNote = qs('#mnt-visit-note').value.trim();
        var address = qs('#mnt-address').value.trim();

        var hasError = false;
        if (!selectedType) {
            showFieldError('mnt-description', 'mnt-type-error', 'Please select a maintenance concern.');
            hasError = true;
        }
        if (!description) {
            showFieldError('mnt-description', 'mnt-description-error', 'Please describe the maintenance request.');
            hasError = true;
        }
        if (!contact) {
            showFieldError('mnt-contact', 'mnt-contact-error', 'Contact number is required.');
            hasError = true;
        }
        if (!dateNeeded) {
            showFieldError('mnt-date-picker', 'mnt-date-error', 'Preferred date is required.');
            hasError = true;
        }
        if (datePicker.isSelectedDateUnavailable()) {
            showFieldError('mnt-date-picker', 'mnt-date-error', 'Selected date is already reserved. Please choose another date.');
            hasError = true;
        }
        if (!time) {
            showFieldError('mnt-time', 'mnt-time-error', 'Please choose a preferred time.');
            hasError = true;
        }
        if (hasError) return;

        var detailLines = [
            'Maintenance Concern: ' + selectedType,
            'Preferred Time: ' + time,
            'Concern Description: ' + description
        ];
        if (extraConcern) detailLines.push('Additional Notes: ' + extraConcern);
        if (visitNote) detailLines.push('Visit Note: ' + visitNote);

        submitBtn.disabled = true;
        submitText.textContent = 'Submitting...';

        try {
            await apiRequest('/api/service-requests', {
                method: 'POST',
                body: {
                    request_type: 'maintenance',
                    contact_number: contact,
                    date_needed: dateNeeded,
                    details: detailLines.join('\n'),
                    address: address || undefined
                }
            });

            showMsg(formMsg, 'success', 'Your maintenance request has been submitted. SolMate will review the concern and confirm your appointment.');
            form.reset();
            datePicker.clear();
            qsa('.mnt-choice').forEach(function (item) {
                item.classList.remove('is-selected');
            });
            formMsg.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        } catch (error) {
            if (error.errors && error.errors.contact_number) {
                showFieldError('mnt-contact', 'mnt-contact-error', error.errors.contact_number[0]);
            }
            if (error.errors && error.errors.date_needed) {
                showFieldError('mnt-date-picker', 'mnt-date-error', error.errors.date_needed[0]);
            }
            if (error.errors && error.errors.details) {
                showFieldError('mnt-description', 'mnt-description-error', error.errors.details[0]);
            }
            showMsg(formMsg, 'error', error.message || 'Could not submit the maintenance request.');
        } finally {
            submitBtn.disabled = false;
            submitText.textContent = 'Submit Maintenance Request';
        }
    });

})();
</script>
@endpush
