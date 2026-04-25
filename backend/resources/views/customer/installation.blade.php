@extends('layouts.app', ['title' => 'Book Installation Service'])

@section('content')
<style>
    /* Installation request page */
    .inst-page {
        display: flex;
        flex-direction: column;
        gap: 32px;
    }

    .inst-hero {
        background: linear-gradient(135deg, #f8fbff 0%, #eef6ff 56%, #e0efff 100%);
        border: 1px solid #dbeafe;
        border-radius: 20px;
        padding: 36px 40px;
        position: relative;
        overflow: hidden;
    }
    .inst-hero::after {
        content: '';
        position: absolute;
        right: -48px;
        top: -48px;
        width: 220px;
        height: 220px;
        border-radius: 50%;
        background: rgba(212, 160, 23, 0.10);
        pointer-events: none;
    }
    .inst-hero-inner {
        position: relative;
        z-index: 1;
        display: grid;
        grid-template-columns: minmax(0, 1fr) 260px;
        gap: 28px;
        align-items: center;
    }
    .inst-hero-eyebrow {
        margin: 0 0 8px;
        font-size: 12px;
        font-weight: 800;
        letter-spacing: 1.1px;
        text-transform: uppercase;
        color: #d4a017;
    }
    .inst-hero-title {
        margin: 0 0 10px;
        font-size: 32px;
        line-height: 1.15;
        font-weight: 800;
        color: #102a43;
    }
    .inst-hero-title span { color: #d4a017; }
    .inst-hero-sub {
        margin: 0;
        max-width: 620px;
        font-size: 15px;
        line-height: 1.7;
        color: #475569;
    }
    .inst-hero-steps {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-top: 22px;
    }
    .inst-hero-step {
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
    .inst-hero-step-num {
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
    .inst-hero-art {
        display: flex;
        justify-content: center;
    }

    .inst-layout {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 320px;
        gap: 24px;
        align-items: start;
    }

    .inst-card,
    .inst-panel,
    .inst-history-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.04);
    }
    .inst-card + .inst-card { margin-top: 20px; }

    .inst-card-header {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 20px 24px;
        border-bottom: 1px solid #f1f5f9;
    }
    .inst-card-icon {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        background: linear-gradient(135deg, #102a43, #1e4068);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .inst-card-title {
        margin: 0;
        font-size: 16px;
        font-weight: 800;
        color: #102a43;
    }
    .inst-card-subtitle {
        margin: 3px 0 0;
        font-size: 12px;
        color: #94a3b8;
    }
    .inst-card-body { padding: 24px; }

    .inst-field { margin-bottom: 20px; }
    .inst-field:last-child { margin-bottom: 0; }
    .inst-field-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
        margin-bottom: 20px;
    }
    .inst-field-row .inst-field { margin-bottom: 0; }
    .inst-label {
        display: block;
        margin-bottom: 8px;
        font-size: 12px;
        font-weight: 800;
        letter-spacing: 0.6px;
        text-transform: uppercase;
        color: #64748b;
    }
    .inst-input,
    .inst-select,
    .inst-textarea {
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
    .inst-select {
        appearance: none;
        -webkit-appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 14px center;
        padding-right: 40px;
    }
    .inst-textarea {
        min-height: 110px;
        resize: vertical;
    }
    .inst-input:focus,
    .inst-select:focus,
    .inst-textarea:focus {
        outline: none;
        border-color: #d4a017;
        box-shadow: 0 0 0 3px rgba(212, 160, 23, 0.12);
    }
    .inst-input.has-error,
    .inst-select.has-error,
    .inst-textarea.has-error {
        border-color: #ef4444;
    }
    .inst-field-hint {
        margin: 7px 0 0;
        font-size: 12px;
        line-height: 1.5;
        color: #94a3b8;
    }
    .inst-field-error {
        display: none;
        margin-top: 6px;
        font-size: 12px;
        color: #dc2626;
    }
    .inst-field-error.show { display: block; }

    .inst-choice-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }
    .inst-choice {
        position: relative;
        border: 1.5px solid #dbeafe;
        border-radius: 16px;
        padding: 16px;
        background: #f8fbff;
        cursor: pointer;
        transition: border-color 0.2s, box-shadow 0.2s, transform 0.15s;
    }
    .inst-choice:hover {
        border-color: #bfdbfe;
        box-shadow: 0 10px 22px rgba(59, 130, 246, 0.08);
        transform: translateY(-1px);
    }
    .inst-choice.is-selected {
        border-color: #d4a017;
        background: #fffbeb;
        box-shadow: 0 12px 26px rgba(212, 160, 23, 0.10);
    }
    .inst-choice input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }
    .inst-choice-title {
        margin: 0 0 5px;
        font-size: 14px;
        font-weight: 800;
        color: #102a43;
    }
    .inst-choice-desc {
        margin: 0;
        font-size: 12px;
        line-height: 1.55;
        color: #64748b;
    }
    .inst-choice-tag {
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
    .inst-choice.is-selected .inst-choice-tag {
        background: #d4a017;
        color: #ffffff;
    }

    .inst-quote-summary {
        display: none;
        margin-top: 14px;
        padding: 16px 18px;
        border-radius: 14px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
    }
    .inst-quote-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
    }
    .inst-quote-label {
        margin-bottom: 4px;
        font-size: 10px;
        font-weight: 800;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        color: #94a3b8;
    }
    .inst-quote-value {
        font-size: 14px;
        font-weight: 800;
        color: #102a43;
    }
    .inst-quote-value.highlight { color: #d4a017; }
    .inst-quote-note {
        margin: 12px 0 0;
        padding-top: 12px;
        border-top: 1px solid #e2e8f0;
        font-size: 12px;
        line-height: 1.5;
        color: #64748b;
    }

    .inst-msg {
        display: none;
        margin-bottom: 18px;
        padding: 12px 16px;
        border-radius: 12px;
        font-size: 14px;
        line-height: 1.5;
    }
    .inst-msg.show { display: block; }
    .inst-msg-success {
        background: #dcfce7;
        border: 1px solid #bbf7d0;
        color: #166534;
    }
    .inst-msg-error {
        background: #fee2e2;
        border: 1px solid #fecaca;
        color: #991b1b;
    }

    .inst-submit-btn {
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
    .inst-submit-btn:hover {
        opacity: 0.95;
        transform: translateY(-1px);
    }
    .inst-submit-btn:disabled {
        opacity: 0.65;
        cursor: not-allowed;
        transform: none;
    }

    .inst-panel {
        overflow: hidden;
        position: sticky;
        top: 24px;
    }
    .inst-panel-header {
        background: #102a43;
        padding: 18px 20px;
        color: #ffffff;
    }
    .inst-panel-title {
        margin: 0 0 4px;
        font-size: 14px;
        font-weight: 800;
    }
    .inst-panel-sub {
        margin: 0;
        font-size: 12px;
        color: rgba(255, 255, 255, 0.70);
    }
    .inst-panel-body { padding: 20px; }
    .inst-checklist {
        display: flex;
        flex-direction: column;
        gap: 14px;
    }
    .inst-check {
        display: flex;
        align-items: flex-start;
        gap: 12px;
    }
    .inst-check-icon {
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
    .inst-check-title {
        margin: 0 0 3px;
        font-size: 13px;
        font-weight: 800;
        color: #102a43;
    }
    .inst-check-desc {
        margin: 0;
        font-size: 12px;
        line-height: 1.55;
        color: #64748b;
    }
    .inst-expect {
        margin-top: 20px;
        padding: 14px 16px;
        border-radius: 12px;
        background: #fffbeb;
        border: 1px solid #fde68a;
    }
    .inst-expect-title {
        margin: 0 0 6px;
        font-size: 13px;
        font-weight: 800;
        color: #92400e;
    }
    .inst-expect-text {
        margin: 0;
        font-size: 12px;
        line-height: 1.6;
        color: #78350f;
    }

    .inst-history-title {
        margin: 0 0 14px;
        font-size: 18px;
        font-weight: 800;
        color: #102a43;
    }
    .inst-loading {
        display: none;
        text-align: center;
        padding: 28px;
        color: #94a3b8;
        font-size: 14px;
    }
    .inst-loading.show { display: block; }
    .inst-empty {
        display: none;
        text-align: center;
        padding: 40px 22px;
        color: #94a3b8;
        font-size: 14px;
    }
    .inst-empty.show { display: block; }
    .inst-history-list {
        display: grid;
        gap: 14px;
    }
    .inst-history-card {
        padding: 20px 22px;
    }
    .inst-history-top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 12px;
    }
    .inst-history-id {
        margin: 0;
        font-size: 15px;
        font-weight: 800;
        color: #102a43;
    }
    .inst-history-date {
        margin: 4px 0 0;
        font-size: 12px;
        color: #94a3b8;
    }
    .inst-badge {
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
    .inst-badge-pending { background: #fef3c7; color: #a16207; }
    .inst-badge-approved,
    .inst-badge-scheduled,
    .inst-badge-assigned { background: #dbeafe; color: #1d4ed8; }
    .inst-badge-in_progress { background: #ede9fe; color: #6d28d9; }
    .inst-badge-completed { background: #dcfce7; color: #15803d; }
    .inst-badge-cancelled,
    .inst-badge-declined { background: #fee2e2; color: #dc2626; }
    .inst-badge-default { background: #f1f5f9; color: #475569; }

    .inst-history-meta {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 10px;
        margin-bottom: 12px;
    }
    .inst-history-chip {
        padding: 10px 12px;
        border-radius: 12px;
        background: #f8fafc;
    }
    .inst-history-chip-label {
        margin-bottom: 3px;
        font-size: 10px;
        font-weight: 800;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        color: #94a3b8;
    }
    .inst-history-chip-value {
        font-size: 13px;
        font-weight: 700;
        color: #1e293b;
    }
    .inst-history-details {
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
        .inst-layout,
        .inst-hero-inner {
            grid-template-columns: 1fr;
        }
        .inst-panel {
            position: static;
        }
        .inst-hero-art {
            display: none;
        }
    }
    @media (max-width: 680px) {
        .inst-hero {
            padding: 28px 22px;
        }
        .inst-hero-title {
            font-size: 25px;
        }
        .inst-card-body,
        .inst-card-header {
            padding-left: 18px;
            padding-right: 18px;
        }
        .inst-field-row,
        .inst-choice-grid,
        .inst-history-meta,
        .inst-quote-grid {
            grid-template-columns: 1fr;
        }
    }
@include('customer.partials.preferred-date-picker-styles')
</style>

<div class="inst-page">
    <section class="inst-hero" aria-label="Installation request hero">
        <div class="inst-hero-inner">
            <div>
                <p class="inst-hero-eyebrow">Customer Service Booking</p>
                <h1 class="inst-hero-title">Book <span>Installation Service</span></h1>
                <p class="inst-hero-sub">Schedule an installation appointment for your solar setup. Add site notes, choose your preferred schedule, and send the request to the SolMate team.</p>
                <div class="inst-hero-steps" aria-label="Installation booking steps">
                    <span class="inst-hero-step"><span class="inst-hero-step-num">1</span>Choose quotation reference</span>
                    <span class="inst-hero-step"><span class="inst-hero-step-num">2</span>Add installation details</span>
                    <span class="inst-hero-step"><span class="inst-hero-step-num">3</span>Choose schedule</span>
                </div>
            </div>
            <div class="inst-hero-art" aria-hidden="true">
                <svg width="210" height="170" viewBox="0 0 210 170" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect x="27" y="52" width="156" height="86" rx="12" fill="#ffffff" stroke="#bfdbfe" stroke-width="2"/>
                    <rect x="39" y="64" width="39" height="24" rx="5" fill="#e0efff"/>
                    <rect x="85" y="64" width="39" height="24" rx="5" fill="#dbeafe"/>
                    <rect x="131" y="64" width="39" height="24" rx="5" fill="#e0efff"/>
                    <rect x="39" y="94" width="39" height="24" rx="5" fill="#dbeafe"/>
                    <rect x="85" y="94" width="39" height="24" rx="5" fill="#ffefb0"/>
                    <rect x="131" y="94" width="39" height="24" rx="5" fill="#dbeafe"/>
                    <path d="M57 138L73 153H137L153 138" stroke="#94a3b8" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
                    <circle cx="163" cy="35" r="24" fill="#fffbeb" stroke="#fde68a" stroke-width="2"/>
                    <path d="M163 22V35L171 43" stroke="#d4a017" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
        </div>
    </section>

    <div class="inst-layout">
        <div>
            <div class="inst-card">
                <div class="inst-card-header">
                    <div class="inst-card-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#f4c542" stroke-width="2">
                            <path d="M9 12h6M9 16h6M9 8h3M5 4h14a1 1 0 011 1v14a1 1 0 01-1 1H5a1 1 0 01-1-1V5a1 1 0 011-1z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="inst-card-title">Quotation Reference</p>
                        <p class="inst-card-subtitle">Connect this request to your quotation or installation coordination needs</p>
                    </div>
                </div>
                <div class="inst-card-body">
                    <div class="inst-field">
                        <label class="inst-label" for="inst-quote-select">Quotation Reference</label>
                        <select id="inst-quote-select" class="inst-select" aria-label="Select a quotation">
                            <option value="">Loading your quotations...</option>
                        </select>
                        <p class="inst-field-hint">If you already have an approved quotation, select it so the request includes its reference.</p>
                    </div>

                    <div id="inst-quote-summary" class="inst-quote-summary" aria-live="polite">
                        <div class="inst-quote-grid">
                            <div>
                                <div class="inst-quote-label">Generated</div>
                                <div class="inst-quote-value" id="inst-qs-date">-</div>
                            </div>
                            <div>
                                <div class="inst-quote-label">System Size</div>
                                <div class="inst-quote-value" id="inst-qs-system">-</div>
                            </div>
                            <div>
                                <div class="inst-quote-label">Project Cost</div>
                                <div class="inst-quote-value highlight" id="inst-qs-cost">-</div>
                            </div>
                            <div>
                                <div class="inst-quote-label">ROI Period</div>
                                <div class="inst-quote-value highlight" id="inst-qs-roi">-</div>
                            </div>
                        </div>
                        <p class="inst-quote-note">The selected quotation number will be included in your installation request details.</p>
                    </div>
                </div>
            </div>

            <div class="inst-card">
                <div class="inst-card-header">
                    <div class="inst-card-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#f4c542" stroke-width="2">
                            <rect x="2" y="7" width="20" height="14" rx="2"/>
                            <path d="M16 21V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v16"/>
                        </svg>
                    </div>
                    <div>
                        <p class="inst-card-title">Installation Request Details</p>
                        <p class="inst-card-subtitle">Let us know what kind of installation support you need</p>
                    </div>
                </div>
                <div class="inst-card-body">
                    <div class="inst-field">
                        <label class="inst-label">Installation Type</label>
                        <div class="inst-choice-grid" id="inst-type-grid">
                            <label class="inst-choice">
                                <input type="radio" name="installation_type" value="Residential rooftop installation">
                                <span class="inst-choice-tag">A</span>
                                <p class="inst-choice-title">Residential Rooftop</p>
                                <p class="inst-choice-desc">For standard rooftop panel installation and on-site setup.</p>
                            </label>
                            <label class="inst-choice">
                                <input type="radio" name="installation_type" value="Ground-mounted solar setup">
                                <span class="inst-choice-tag">B</span>
                                <p class="inst-choice-title">Ground-Mounted Setup</p>
                                <p class="inst-choice-desc">For properties using a ground structure instead of a roof mount.</p>
                            </label>
                            <label class="inst-choice">
                                <input type="radio" name="installation_type" value="System expansion or additional panels">
                                <span class="inst-choice-tag">C</span>
                                <p class="inst-choice-title">System Expansion</p>
                                <p class="inst-choice-desc">For adding panels or expanding an existing approved system.</p>
                            </label>
                            <label class="inst-choice">
                                <input type="radio" name="installation_type" value="Installation schedule coordination">
                                <span class="inst-choice-tag">D</span>
                                <p class="inst-choice-title">Schedule Coordination</p>
                                <p class="inst-choice-desc">For customers ready to coordinate the installation appointment and site access.</p>
                            </label>
                        </div>
                        <div class="inst-field-error" id="inst-type-error" role="alert"></div>
                    </div>

                    <div class="inst-field">
                        <label class="inst-label" for="inst-details">Short Note or Installation Instructions</label>
                        <textarea id="inst-details" class="inst-textarea" placeholder="Add any installation notes, site access reminders, property instructions, or concerns our team should review before scheduling."></textarea>
                        <p class="inst-field-hint">Examples: gate access details, roof entry reminders, preferred coordination contact, or installation preparation notes.</p>
                        <div class="inst-field-error" id="inst-details-error" role="alert"></div>
                    </div>
                </div>
            </div>

            <div class="inst-card">
                <div class="inst-card-header">
                    <div class="inst-card-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#f4c542" stroke-width="2">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                            <line x1="16" y1="2" x2="16" y2="6"/>
                            <line x1="8" y1="2" x2="8" y2="6"/>
                            <line x1="3" y1="10" x2="21" y2="10"/>
                        </svg>
                    </div>
                    <div>
                        <p class="inst-card-title">Preferred Schedule</p>
                        <p class="inst-card-subtitle">Choose your preferred appointment date, time, and contact details</p>
                    </div>
                </div>
                <div class="inst-card-body">
                    <div id="inst-form-msg" class="inst-msg" role="alert"></div>

                    <form id="inst-form" novalidate>
                        <div class="inst-field-row">
                            <div class="inst-field">
                                <label class="inst-label" for="inst-contact">Contact Number</label>
                                <input id="inst-contact" class="inst-input" type="tel" maxlength="30" autocomplete="tel" placeholder="e.g. 09171234567">
                                <div class="inst-field-error" id="inst-contact-error" role="alert"></div>
                            </div>
                            <div class="inst-field">
                                <label class="inst-label" for="inst-date">Preferred Date</label>
                                <input id="inst-date" class="inst-input" type="hidden" autocomplete="off">
                                <div id="inst-date-picker" class="sdp-field-host"></div>
                                <div class="inst-field-error" id="inst-date-error" role="alert"></div>
                            </div>
                        </div>

                        <div class="inst-field">
                            <label class="inst-label" for="inst-address">Address</label>
                            <input id="inst-address" class="inst-input" type="text" maxlength="255" autocomplete="street-address" placeholder="e.g. 123 Rizal Street, Quezon City" value="{{ auth()->user()->address ?? '' }}">
                            <div class="inst-field-error" id="inst-address-error" role="alert"></div>
                        </div>

                        <div class="inst-field-row">
                            <div class="inst-field">
                                <label class="inst-label" for="inst-time">Preferred Time</label>
                                <select id="inst-time" class="inst-select">
                                    <option value="">Select preferred time</option>
                                    <option value="Morning (8:00 AM - 11:00 AM)">Morning (8:00 AM - 11:00 AM)</option>
                                    <option value="Midday (11:00 AM - 1:00 PM)">Midday (11:00 AM - 1:00 PM)</option>
                                    <option value="Afternoon (1:00 PM - 4:00 PM)">Afternoon (1:00 PM - 4:00 PM)</option>
                                </select>
                                <div class="inst-field-error" id="inst-time-error" role="alert"></div>
                            </div>
                            <div class="inst-field">
                                <label class="inst-label" for="inst-extra">Additional Notes</label>
                                <input id="inst-extra" class="inst-input" type="text" placeholder="Optional scheduling or access note">
                            </div>
                        </div>

                        <p class="inst-field-hint" style="margin-top:-2px;margin-bottom:16px;">Preferred time and extra notes are included in your request details for scheduling review.</p>

                        <button type="submit" class="inst-submit-btn" id="inst-submit-btn">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <line x1="22" y1="2" x2="11" y2="13"/>
                                <polygon points="22 2 15 22 11 13 2 9 22 2"/>
                            </svg>
                            <span id="inst-submit-text">Submit Installation Request</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <aside class="inst-panel" aria-label="Installation request checklist">
            <div class="inst-panel-header">
                <p class="inst-panel-title">Installation Request Checklist</p>
                <p class="inst-panel-sub">What to prepare before you book</p>
            </div>
            <div class="inst-panel-body">
                <div class="inst-checklist">
                    <div class="inst-check">
                        <div class="inst-check-icon">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <div>
                            <p class="inst-check-title">Confirm your quotation</p>
                            <p class="inst-check-desc">Select your quotation reference if your installation request is tied to an approved or final quotation.</p>
                        </div>
                    </div>
                    <div class="inst-check">
                        <div class="inst-check-icon">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <div>
                            <p class="inst-check-title">Choose your preferred schedule</p>
                            <p class="inst-check-desc">Pick your best date and time window so our team can coordinate the installation appointment.</p>
                        </div>
                    </div>
                    <div class="inst-check">
                        <div class="inst-check-icon">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <div>
                            <p class="inst-check-title">Prepare site access details</p>
                            <p class="inst-check-desc">Add notes about roof access, gate entry, or any property instructions that may affect the visit.</p>
                        </div>
                    </div>
                    <div class="inst-check">
                        <div class="inst-check-icon">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <div>
                            <p class="inst-check-title">Submit special instructions</p>
                            <p class="inst-check-desc">Use the request notes to highlight coordination concerns, access limits, or preparation reminders.</p>
                        </div>
                    </div>
                    <div class="inst-check">
                        <div class="inst-check-icon">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <div>
                            <p class="inst-check-title">Wait for confirmation</p>
                            <p class="inst-check-desc">After submission, SolMate will review the request and confirm the installation schedule with you.</p>
                        </div>
                    </div>
                </div>

                <div class="inst-expect">
                    <p class="inst-expect-title">What to Expect</p>
                    <p class="inst-expect-text">Your request is submitted as a customer service booking. Once reviewed, the SolMate team will coordinate the confirmed installation schedule and next steps.</p>
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

    function fmtPeso(value) {
        if (value === null || value === undefined || isNaN(Number(value))) return '-';
        return 'PHP ' + Number(value).toLocaleString('en-PH', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
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
        el.className = 'inst-msg show inst-msg-' + type;
        el.textContent = text;
    }

    function hideMsg(el) {
        if (!el) return;
        el.className = 'inst-msg';
        el.textContent = '';
    }

    function clearFieldErrors() {
        qsa('.inst-field-error').forEach(function (el) {
            el.textContent = '';
            el.classList.remove('show');
        });
        qsa('.inst-input, .inst-select, .inst-textarea').forEach(function (el) {
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

    var quoteSelect = qs('#inst-quote-select');
    var quoteSummary = qs('#inst-quote-summary');
    var form = qs('#inst-form');
    var formMsg = qs('#inst-form-msg');
    var submitBtn = qs('#inst-submit-btn');
    var submitText = qs('#inst-submit-text');
    var datePicker = window.createPreferredDatePicker({
        inputId: 'inst-date',
        mountId: 'inst-date-picker',
        helperText: 'Booked dates are unavailable and cannot be selected.',
        fetchErrorText: 'Schedule availability could not be refreshed right now. The backend will still verify your preferred date when you submit.',
        placeholder: 'Select a preferred date'
    });
    var allQuotations = [];

    qsa('.inst-choice').forEach(function (choice) {
        var radio = qs('input[type="radio"]', choice);
        choice.addEventListener('click', function () {
            if (radio) radio.checked = true;
            qsa('.inst-choice').forEach(function (item) {
                item.classList.remove('is-selected');
            });
            choice.classList.add('is-selected');
            var err = qs('#inst-type-error');
            if (err) {
                err.textContent = '';
                err.classList.remove('show');
            }
        });
    });

    function populateQuotationSelect(quotations) {
        var options = quotations.filter(function (quotation) {
            return ['initial', 'final'].indexOf(String(quotation.quotation_type || 'initial').toLowerCase()) !== -1;
        });

        quoteSelect.innerHTML = '';
        var placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.textContent = options.length ? 'Select a quotation (optional)' : 'No quotations available';
        quoteSelect.appendChild(placeholder);

        options.forEach(function (quotation) {
            var opt = document.createElement('option');
            opt.value = quotation.id;
            opt.textContent = 'Quote #' + quotation.id + ' · ' + fmtPeso(quotation.project_cost) + ' · ' + fmtDate(quotation.created_at);
            quoteSelect.appendChild(opt);
        });
    }

    quoteSelect.addEventListener('change', function () {
        var selectedId = quoteSelect.value;
        if (!selectedId) {
            quoteSummary.style.display = 'none';
            return;
        }

        var quotation = allQuotations.find(function (item) {
            return String(item.id) === String(selectedId);
        });

        if (!quotation) {
            quoteSummary.style.display = 'none';
            return;
        }

        qs('#inst-qs-date').textContent = fmtDate(quotation.created_at);
        qs('#inst-qs-system').textContent = quotation.system_kw ? Number(quotation.system_kw).toFixed(2) + ' kW' : '-';
        qs('#inst-qs-cost').textContent = fmtPeso(quotation.project_cost);
        qs('#inst-qs-roi').textContent = quotation.roi_years ? Number(quotation.roi_years).toFixed(1) + ' years' : '-';
        quoteSummary.style.display = 'block';
    });

    async function loadQuotations() {
        try {
            var response = await apiRequest('/api/quotations');
            allQuotations = Array.isArray(response) ? response : (response.data || []);
            populateQuotationSelect(allQuotations);
        } catch (error) {
            quoteSelect.innerHTML = '<option value="">Could not load quotations</option>';
        }
    }

    form.addEventListener('submit', async function (event) {
        event.preventDefault();
        clearFieldErrors();
        hideMsg(formMsg);

        var selectedTypeInput = qs('input[name="installation_type"]:checked');
        var selectedType = selectedTypeInput ? selectedTypeInput.value : '';
        var basis = 'Installation coordination request';
        var contact = qs('#inst-contact').value.trim();
        await datePicker.refreshAvailability();
        var dateNeeded = datePicker.getValue();
        var time = qs('#inst-time').value.trim();
        var detailsText = qs('#inst-details').value.trim();
        var extra = qs('#inst-extra').value.trim();
        var quotationId = quoteSelect.value;
        var address = qs('#inst-address').value.trim();

        var hasError = false;
        if (!selectedType) {
            showFieldError('inst-type-grid', 'inst-type-error', 'Please choose the installation type.');
            hasError = true;
        }
        if (!detailsText) {
            showFieldError('inst-details', 'inst-details-error', 'Please add installation notes or instructions.');
            hasError = true;
        }
        if (!contact) {
            showFieldError('inst-contact', 'inst-contact-error', 'Contact number is required.');
            hasError = true;
        }
        if (!dateNeeded) {
            showFieldError('inst-date-picker', 'inst-date-error', 'Preferred date is required.');
            hasError = true;
        }
        if (datePicker.isSelectedDateUnavailable()) {
            showFieldError('inst-date-picker', 'inst-date-error', 'Selected date is already reserved. Please choose another date.');
            hasError = true;
        }
        if (!time) {
            showFieldError('inst-time', 'inst-time-error', 'Please choose a preferred time.');
            hasError = true;
        }
        if (hasError) return;

        var detailLines = [];
        if (quotationId) detailLines.push('Quotation Reference: Quote #' + quotationId);
        detailLines.push('Installation Request Basis: ' + basis);
        detailLines.push('Installation Type: ' + selectedType);
        detailLines.push('Preferred Time: ' + time);
        detailLines.push('Installation Notes: ' + detailsText);
        if (extra) detailLines.push('Additional Notes: ' + extra);

        submitBtn.disabled = true;
        submitText.textContent = 'Submitting...';

        try {
            await apiRequest('/api/service-requests', {
                method: 'POST',
                body: {
                    request_type: 'installation',
                    contact_number: contact,
                    date_needed: dateNeeded,
                    details: detailLines.join('\n'),
                    address: address || undefined
                }
            });

            showMsg(formMsg, 'success', 'Your installation request has been submitted. SolMate will review your preferred schedule and confirm the appointment.');
            form.reset();
            datePicker.clear();
            quoteSelect.value = '';
            quoteSummary.style.display = 'none';
            qsa('.inst-choice').forEach(function (item) {
                item.classList.remove('is-selected');
            });
            formMsg.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        } catch (error) {
            if (error.errors && error.errors.contact_number) {
                showFieldError('inst-contact', 'inst-contact-error', error.errors.contact_number[0]);
            }
            if (error.errors && error.errors.date_needed) {
                showFieldError('inst-date-picker', 'inst-date-error', error.errors.date_needed[0]);
            }
            if (error.errors && error.errors.details) {
                showFieldError('inst-details', 'inst-details-error', error.errors.details[0]);
            }
            showMsg(formMsg, 'error', error.message || 'Could not submit the installation request.');
        } finally {
            submitBtn.disabled = false;
            submitText.textContent = 'Submit Installation Request';
        }
    });

    loadQuotations();
})();
</script>
@endpush
