@extends('layouts.app', ['title' => 'My Dashboard'])

@section('content')
<style>
    /* ── Dashboard page styles (dash- prefix) ── */
    .dash-wrapper {
        max-width: 900px;
        margin: 0 auto;
        padding: 0 0 48px;
    }

    /* Profile Banner */
    .dash-banner {
        background: linear-gradient(135deg, #e0f2fe 0%, #dbeafe 50%, #c7d2fe 100%);
        border-radius: 16px;
        padding: 36px 40px;
        display: flex;
        align-items: center;
        gap: 28px;
        margin-bottom: 8px;
        position: relative;
        overflow: hidden;
    }
    .dash-banner::before {
        content: '';
        position: absolute;
        top: -40px;
        right: -40px;
        width: 200px;
        height: 200px;
        border-radius: 50%;
        background: rgba(212, 160, 23, 0.12);
        pointer-events: none;
    }
    .dash-banner::after {
        content: '';
        position: absolute;
        bottom: -60px;
        right: 60px;
        width: 160px;
        height: 160px;
        border-radius: 50%;
        background: rgba(96, 165, 250, 0.15);
        pointer-events: none;
    }
    .dash-avatar {
        width: 88px;
        height: 88px;
        border-radius: 50%;
        background: #102a43;
        border: 4px solid #d4a017;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 32px;
        font-weight: 700;
        color: #fff;
        letter-spacing: 1px;
        flex-shrink: 0;
        z-index: 1;
        overflow: hidden;
        position: relative;
    }
    .dash-avatar-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }
    .dash-avatar-fallback {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .dash-banner-info {
        flex: 1;
        z-index: 1;
    }
    .dash-banner-name {
        font-size: 26px;
        font-weight: 700;
        color: #102a43;
        margin: 0 0 4px;
    }
    .dash-banner-role {
        display: inline-block;
        background: #d4a017;
        color: #fff;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 1px;
        text-transform: uppercase;
        padding: 2px 10px;
        border-radius: 20px;
        margin-bottom: 10px;
    }
    .dash-banner-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 16px;
        margin-top: 4px;
    }
    .dash-banner-meta-item {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
        color: #334155;
    }
    .dash-banner-meta-item svg { color: #64748b; }

    /* Tab Nav */
    .dash-tabs {
        display: flex;
        gap: 0;
        border-bottom: 2px solid #e2e8f0;
        margin-bottom: 28px;
    }
    .dash-tab-btn {
        background: none;
        border: none;
        border-bottom: 3px solid transparent;
        margin-bottom: -2px;
        padding: 14px 22px;
        font-size: 14px;
        font-weight: 600;
        color: #64748b;
        cursor: pointer;
        transition: color .2s, border-color .2s;
        white-space: nowrap;
    }
    .dash-tab-btn:hover { color: #102a43; }
    .dash-tab-btn.active { color: #d4a017; border-bottom-color: #d4a017; }

    /* Tab Panels */
    .dash-tab-panel { display: none; }
    .dash-tab-panel.active { display: block; }

    /* Section heading */
    .dash-section-title {
        font-size: 16px;
        font-weight: 700;
        color: #102a43;
        margin: 0 0 16px;
        padding-bottom: 10px;
        border-bottom: 1px solid #e2e8f0;
    }

    /* Profile Info rows */
    .dash-info-grid { display: grid; gap: 0; }
    .dash-info-row {
        display: grid;
        grid-template-columns: 160px 1fr;
        align-items: center;
        padding: 14px 0;
        border-bottom: 1px solid #f1f5f9;
        gap: 12px;
    }
    .dash-info-row:last-child { border-bottom: none; }
    .dash-info-label {
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .6px;
        color: #94a3b8;
    }
    .dash-info-value { font-size: 15px; color: #1e293b; font-weight: 500; }

    /* Action row */
    .dash-action-row { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 20px; }

    /* Forms */
    .dash-form-card {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 24px;
        margin-top: 28px;
    }
    .dash-form-title { font-size: 15px; font-weight: 700; color: #102a43; margin: 0 0 16px; }
    .dash-form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px; }
    .dash-form-row.single { grid-template-columns: 1fr; }
    .dash-form-group label {
        display: block;
        font-size: 12px;
        font-weight: 600;
        color: #64748b;
        margin-bottom: 6px;
        text-transform: uppercase;
        letter-spacing: .5px;
    }
    .dash-form-group input {
        width: 100%;
        padding: 12px 16px;
        border: 1.5px solid #cbd5e1;
        border-radius: 10px;
        font-size: 14px;
        color: #1e293b;
        background: #fff;
        box-sizing: border-box;
        transition: border-color .2s;
    }
    .dash-form-group input:focus {
        outline: none;
        border-color: #d4a017;
        box-shadow: 0 0 0 3px rgba(212,160,23,.1);
    }
    .dash-form-group .field-error { color: #dc2626; font-size: 12px; margin-top: 4px; }
    .dash-form-actions { display: flex; gap: 10px; margin-top: 16px; }
    .dash-form-helper {
        margin: 8px 0 0;
        font-size: 12px;
        line-height: 1.5;
        color: #64748b;
    }
    .dash-profile-picture-panel {
        display: grid;
        grid-template-columns: auto 1fr;
        gap: 18px;
        align-items: center;
        padding: 18px;
        margin-bottom: 18px;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        background: #ffffff;
    }
    .dash-profile-picture-preview {
        width: 88px;
        height: 88px;
        border-radius: 50%;
        border: 3px solid #d4a017;
        background: #102a43;
        color: #ffffff;
        font-size: 30px;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        flex-shrink: 0;
    }
    .dash-profile-picture-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }
    .dash-profile-picture-meta {
        display: grid;
        gap: 10px;
    }
    .dash-profile-picture-title {
        margin: 0;
        font-size: 14px;
        font-weight: 700;
        color: #102a43;
    }
    .dash-profile-picture-copy {
        margin: 0;
        font-size: 13px;
        line-height: 1.6;
        color: #52606d;
    }

    /* Buttons */
    .dash-btn {
        padding: 10px 22px;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 600;
        border: none;
        cursor: pointer;
        transition: all .2s;
        line-height: 1;
    }
    .dash-btn-gold { background: #d4a017; color: #fff; }
    .dash-btn-gold:hover { background: #b8880f; }
    .dash-btn-outline { background: transparent; border: 1.5px solid #d4a017; color: #d4a017; }
    .dash-btn-outline:hover { background: rgba(212,160,23,.08); }
    .dash-btn-ghost { background: transparent; border: 1.5px solid #cbd5e1; color: #475569; }
    .dash-btn-ghost:hover { background: #f1f5f9; }
    .dash-btn:disabled { opacity: .6; cursor: not-allowed; }

    /* Filter chips */
    .dash-filter-chips { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 20px; }
    .dash-chip {
        padding: 6px 16px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
        background: #f1f5f9;
        color: #475569;
        border: 1.5px solid transparent;
        cursor: pointer;
        transition: all .15s;
    }
    .dash-chip:hover, .dash-chip.active { background: #102a43; color: #fff; border-color: #102a43; }
    .dash-chip.chip-gold.active { background: #d4a017; border-color: #d4a017; color: #fff; }

    /* Quotation Cards */
    .dash-quotation-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 20px 24px;
        margin-bottom: 14px;
        transition: box-shadow .2s;
    }
    .dash-quotation-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,.07); }
    .dash-q-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 14px;
    }
    .dash-q-id { font-size: 15px; font-weight: 700; color: #102a43; }
    .dash-q-meta { font-size: 12px; color: #94a3b8; margin-top: 2px; }
    .dash-q-badges { display: flex; gap: 6px; flex-wrap: wrap; }
    .dash-q-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
        gap: 10px;
        margin-bottom: 16px;
    }
    .dash-q-stat { background: #f8fafc; border-radius: 8px; padding: 10px 12px; }
    .dash-q-stat-label {
        font-size: 11px;
        color: #94a3b8;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .5px;
        margin-bottom: 3px;
    }
    .dash-q-stat-value { font-size: 16px; font-weight: 700; color: #102a43; }
    .dash-q-actions { display: flex; gap: 8px; }

    /* Service / Inspection Request Cards */
    .dash-sr-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 16px; }
    .dash-sr-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 18px 20px;
        display: flex;
        flex-direction: column;
        gap: 10px;
        transition: box-shadow .2s;
    }
    .dash-sr-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,.07); }
    .dash-sr-top { display: flex; align-items: flex-start; justify-content: space-between; gap: 8px; }
    .dash-sr-badges { display: flex; gap: 6px; flex-wrap: wrap; }
    .dash-sr-id { font-size: 14px; font-weight: 700; color: #102a43; }
    .dash-sr-date { font-size: 12px; color: #94a3b8; }
    .dash-sr-notes {
        font-size: 13px;
        color: #475569;
        background: #f8fafc;
        border-radius: 8px;
        padding: 8px 10px;
        line-height: 1.5;
    }

    /* Status Badges */
    .dash-badge {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .5px;
    }
    .dash-badge-type-inspection { background: #f3e8ff; color: #7c3aed; }
    .dash-badge-type-installation { background: #dbeafe; color: #1d4ed8; }
    .dash-badge-type-maintenance { background: #fef3c7; color: #b45309; }
    .dash-badge-type-initial { background: #e0f2fe; color: #0284c7; }
    .dash-badge-type-final { background: #dcfce7; color: #15803d; }
    .dash-badge-status-pending { background: #fef9c3; color: #a16207; }
    .dash-badge-status-confirmed,
    .dash-badge-status-approved { background: #dcfce7; color: #15803d; }
    .dash-badge-status-completed { background: #d1fae5; color: #065f46; }
    .dash-badge-status-in_progress,
    .dash-badge-status-in-progress { background: #dbeafe; color: #1d4ed8; }
    .dash-badge-status-cancelled,
    .dash-badge-status-rejected { background: #fee2e2; color: #dc2626; }
    .dash-badge-status-for_review { background: #fce7f3; color: #be185d; }
    .dash-badge-status-default { background: #f1f5f9; color: #475569; }

    /* Message boxes */
    .dash-msg {
        padding: 12px 16px;
        border-radius: 10px;
        font-size: 14px;
        margin-bottom: 12px;
        display: none;
    }
    .dash-msg.show { display: block; }
    .dash-msg-success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
    .dash-msg-error { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }

    /* Empty state */
    .dash-empty { text-align: center; padding: 48px 24px; color: #94a3b8; }
    .dash-empty-icon { width: 48px; height: 48px; margin: 0 auto 12px; opacity: .5; }
    .dash-empty p { font-size: 14px; margin: 0; }

    /* Loading */
    .dash-loading { text-align: center; padding: 32px; color: #94a3b8; font-size: 14px; display: none; }
    .dash-loading.show { display: block; }

    /* New Quotation Form */
    .dash-new-q-panel {
        background: #fffbeb;
        border: 1.5px solid #fde68a;
        border-radius: 12px;
        padding: 24px;
        margin-bottom: 20px;
        display: none;
    }
    .dash-new-q-panel.show { display: block; }

    /* Admin / Technician dashboard */
    .adm-home {
        display: grid;
        gap: 24px;
    }
    .adm-hero {
        background: linear-gradient(135deg, #f8fbff 0%, #eef6ff 58%, #e3efff 100%);
        border: 1px solid #dbe7f3;
        border-radius: 22px;
        padding: 34px 36px;
        position: relative;
        overflow: hidden;
    }
    .adm-hero::after {
        content: '';
        position: absolute;
        right: -42px;
        top: -46px;
        width: 210px;
        height: 210px;
        border-radius: 50%;
        background: rgba(212,160,23,.12);
    }
    .adm-hero > * { position: relative; z-index: 1; }
    .adm-eyebrow {
        margin: 0 0 8px;
        color: #d4a017;
        font-size: 12px;
        font-weight: 800;
        letter-spacing: .12em;
        text-transform: uppercase;
    }
    .adm-title {
        margin: 0 0 10px;
        color: #102a43;
        font-size: 31px;
        line-height: 1.15;
        font-weight: 800;
        letter-spacing: -.03em;
    }
    .adm-copy {
        margin: 0;
        max-width: 740px;
        font-size: 15px;
        line-height: 1.7;
        color: #52606d;
    }
    .adm-quick-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 14px;
    }
    .adm-link-card {
        display: block;
        padding: 20px;
        border-radius: 18px;
        border: 1px solid #dbe7f3;
        background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
        box-shadow: 0 10px 24px rgba(15,23,42,.05);
        transition: transform .16s, box-shadow .16s, border-color .16s;
        text-decoration: none;
    }
    .adm-link-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 16px 34px rgba(15,23,42,.08);
        border-color: #c5d7ea;
        text-decoration: none;
    }
    .adm-link-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        background: linear-gradient(135deg, #102a43, #1e4068);
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 14px;
    }
    .adm-link-title {
        margin: 0 0 6px;
        color: #102a43;
        font-size: 16px;
        font-weight: 800;
    }
    .adm-link-copy {
        margin: 0;
        color: #52606d;
        font-size: 13px;
        line-height: 1.6;
    }

    @media (max-width: 640px) {
        .dash-banner { flex-direction: column; align-items: flex-start; padding: 24px 20px; }
        .dash-q-grid { grid-template-columns: repeat(2, 1fr); }
        .dash-sr-grid { grid-template-columns: 1fr; }
        .dash-info-row { grid-template-columns: 1fr; gap: 2px; }
        .dash-form-row { grid-template-columns: 1fr; }
        .dash-profile-picture-panel { grid-template-columns: 1fr; justify-items: flex-start; }
        .dash-tab-btn { padding: 12px 14px; font-size: 13px; }
        .adm-hero { padding: 28px 22px; }
        .adm-title { font-size: 25px; }
    }
</style>

@if ($user->role === \App\Models\User::ROLE_CUSTOMER)
<div
    class="dash-wrapper"
    data-user-name="{{ $user->name }}"
    data-profile-picture-path="{{ $user->profile_picture ?? '' }}"
>
    @php
        $dashboardProfilePictureUrl = $user->profile_picture ? asset('storage/' . ltrim($user->profile_picture, '/')) : null;
    @endphp

    {{-- Profile Banner --}}
    <div class="dash-banner">
        <div class="dash-avatar" id="dash-avatar">
            @if ($dashboardProfilePictureUrl)
                <img
                    src="{{ $dashboardProfilePictureUrl }}"
                    alt="{{ $user->name }} profile picture"
                    class="dash-avatar-image"
                    id="dash-avatar-image"
                >
            @endif
            <div
                class="dash-avatar-fallback"
                id="dash-avatar-initials"
                @if ($dashboardProfilePictureUrl) style="display:none;" @endif
            >?</div>
        </div>
        <div class="dash-banner-info">
            <h1 class="dash-banner-name" id="dash-banner-name">{{ $user->name }}</h1>
            <span class="dash-banner-role">Customer</span>
            <div class="dash-banner-meta">
                <span class="dash-banner-meta-item">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="5" width="18" height="14" rx="1"/><path d="M3 9l9 6 9-6"/></svg>
                    <span id="dash-banner-email">{{ $user->email }}</span>
                </span>
            </div>
        </div>
    </div>

    {{-- Tab Navigation --}}
    <div class="dash-tabs">
        <button class="dash-tab-btn active" data-target="tab-profile">Personal Information</button>
        <button class="dash-tab-btn" data-target="tab-quotations">My Quotations</button>
        <button class="dash-tab-btn" data-target="tab-services">My Service Requests</button>
    </div>

    {{-- TAB: Personal Information --}}
    <div class="dash-tab-panel active" id="tab-profile">

        <div class="card" style="margin-bottom: 20px;">
            <h2 class="dash-section-title">Account Details</h2>
            <div id="dash-profile-msg" class="dash-msg"></div>
            <div class="dash-info-grid">
                <div class="dash-info-row">
                    <span class="dash-info-label">Full Name</span>
                    <span class="dash-info-value" id="di-name">{{ $user->name }}</span>
                </div>
                <div class="dash-info-row">
                    <span class="dash-info-label">Email Address</span>
                    <span class="dash-info-value" id="di-email">{{ $user->email }}</span>
                </div>
                <div class="dash-info-row">
                    <span class="dash-info-label">Address</span>
                    <span class="dash-info-value" id="di-address">{{ $user->address ?: 'Not provided' }}</span>
                </div>
                <div class="dash-info-row">
                    <span class="dash-info-label">Contact Number</span>
                    <span class="dash-info-value" id="di-contact-number">{{ $user->contact_number ?: 'Not provided' }}</span>
                </div>
                <div class="dash-info-row">
                    <span class="dash-info-label">Account Role</span>
                    <span class="dash-info-value">Customer</span>
                </div>
            </div>
            <div class="dash-action-row">
                <button class="dash-btn dash-btn-gold" id="toggle-edit-profile-btn">Edit Profile</button>
                <button class="dash-btn dash-btn-ghost" id="toggle-change-pwd-btn">Change Password</button>
            </div>
        </div>

        {{-- Edit Profile Form --}}
        <div class="dash-form-card" id="edit-profile-form-card" style="display:none;">
            <h3 class="dash-form-title">Edit Profile</h3>
            <div class="dash-profile-picture-panel">
                <div class="dash-profile-picture-preview" id="dash-profile-picture-preview">
                    @if ($dashboardProfilePictureUrl)
                        <img
                            src="{{ $dashboardProfilePictureUrl }}"
                            alt="{{ $user->name }} profile picture preview"
                            id="dash-profile-picture-preview-image"
                        >
                    @else
                        <span id="dash-profile-picture-preview-fallback">?</span>
                    @endif
                </div>
                <div class="dash-profile-picture-meta">
                    <div>
                        <p class="dash-profile-picture-title">Profile Picture</p>
                        <p class="dash-profile-picture-copy">Upload a JPG, JPEG, PNG, or WEBP image up to 2 MB. The banner avatar updates right away after a successful upload.</p>
                    </div>
                    <div class="dash-form-group">
                        <label for="ep-profile-picture">Choose Image</label>
                        <input type="file" id="ep-profile-picture" name="profile_picture" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                        <div class="field-error" id="ep-profile_picture-error"></div>
                        <p class="dash-form-helper" id="ep-profile-picture-helper">No file selected.</p>
                    </div>
                    <div class="dash-form-actions" style="margin-top:0;">
                        <button type="button" class="dash-btn dash-btn-outline" id="ep-upload-picture-btn">Upload Picture</button>
                    </div>
                </div>
            </div>
            <form id="edit-profile-form">
                <div class="dash-form-row">
                    <div class="dash-form-group">
                        <label for="ep-name">Full Name</label>
                        <input type="text" id="ep-name" name="name" value="{{ $user->name }}" placeholder="Your full name" required>
                        <div class="field-error" id="ep-name-error"></div>
                    </div>
                    <div class="dash-form-group">
                        <label for="ep-email">Email Address</label>
                        <input type="email" id="ep-email" name="email" value="{{ $user->email }}" placeholder="your@email.com" required>
                        <div class="field-error" id="ep-email-error"></div>
                    </div>
                </div>
                <div class="dash-form-row">
                    <div class="dash-form-group">
                        <label for="ep-address">Address</label>
                        <input type="text" id="ep-address" name="address" value="{{ $user->address }}" placeholder="Your address">
                        <div class="field-error" id="ep-address-error"></div>
                    </div>
                    <div class="dash-form-group">
                        <label for="ep-contact-number">Contact Number</label>
                        <input type="text" id="ep-contact-number" name="contact_number" value="{{ $user->contact_number }}" placeholder="Your contact number">
                        <div class="field-error" id="ep-contact_number-error"></div>
                    </div>
                </div>
                <div class="dash-form-actions">
                    <button type="submit" class="dash-btn dash-btn-gold" id="ep-submit-btn">Save Changes</button>
                    <button type="button" class="dash-btn dash-btn-ghost" id="ep-cancel-btn">Cancel</button>
                </div>
            </form>
        </div>

        {{-- Change Password Form --}}
        <div class="dash-form-card" id="change-pwd-form-card" style="display:none;">
            <h3 class="dash-form-title">Change Password</h3>
            <form id="change-pwd-form">
                <div class="dash-form-row single">
                    <div class="dash-form-group">
                        <label for="cp-current">Current Password</label>
                        <input type="password" id="cp-current" name="current_password" placeholder="Current password" required>
                        <div class="field-error" id="cp-current-error"></div>
                    </div>
                </div>
                <div class="dash-form-row">
                    <div class="dash-form-group">
                        <label for="cp-new">New Password</label>
                        <input type="password" id="cp-new" name="new_password" placeholder="Min 8 characters" required>
                        <div class="field-error" id="cp-new-error"></div>
                    </div>
                    <div class="dash-form-group">
                        <label for="cp-confirm">Confirm New Password</label>
                        <input type="password" id="cp-confirm" name="new_password_confirmation" placeholder="Repeat new password" required>
                        <div class="field-error" id="cp-confirm-error"></div>
                    </div>
                </div>
                <div class="dash-form-actions">
                    <button type="submit" class="dash-btn dash-btn-gold" id="cp-submit-btn">Update Password</button>
                    <button type="button" class="dash-btn dash-btn-ghost" id="cp-cancel-btn">Cancel</button>
                </div>
            </form>
        </div>

    </div>{{-- end tab-profile --}}

    {{-- TAB: My Quotations --}}
    <div class="dash-tab-panel" id="tab-quotations">
        <div class="dash-filter-chips">
            <button class="dash-chip active" data-filter="all">All</button>
            <button class="dash-chip" data-filter="initial">Initial</button>
            <button class="dash-chip" data-filter="final">Final</button>
            <button class="dash-chip" data-filter="completed">Completed</button>
            <button class="dash-chip chip-gold" id="generate-new-chip">+ Generate New</button>
        </div>

        <div class="dash-new-q-panel" id="new-quotation-panel">
            <h3 class="dash-form-title" style="margin-bottom:4px;">Get an Initial Quotation</h3>
            <p style="font-size:13px;color:#92400e;margin:0 0 16px;">Enter your average monthly electricity bill and we will compute a solar system sizing estimate for you.</p>
            <div id="new-q-msg" class="dash-msg"></div>
            <form id="new-quotation-form">
                <div class="dash-form-row" style="max-width:480px;">
                    <div class="dash-form-group">
                        <label for="nq-bill">Monthly Electric Bill (PHP)</label>
                        <input type="number" id="nq-bill" name="monthly_electric_bill" placeholder="e.g. 3500" min="1" required>
                        <div class="field-error" id="nq-bill-error"></div>
                    </div>
                    <div class="dash-form-group">
                        <label for="nq-remarks">Remarks (optional)</label>
                        <input type="text" id="nq-remarks" name="remarks" placeholder="Any notes...">
                    </div>
                </div>
                <div class="dash-form-actions">
                    <button type="submit" class="dash-btn dash-btn-gold" id="nq-submit-btn">Generate Quotation</button>
                    <button type="button" class="dash-btn dash-btn-ghost" id="nq-cancel-btn">Cancel</button>
                </div>
            </form>
        </div>

        <div id="q-loading" class="dash-loading">Loading quotations...</div>
        <div id="q-msg" class="dash-msg"></div>
        <div id="q-list"></div>
        <div id="q-empty" class="dash-empty" style="display:none;">
            <svg class="dash-empty-icon" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5"><path d="M9 12h6M9 16h6M9 8h3M5 4h14a1 1 0 011 1v14a1 1 0 01-1 1H5a1 1 0 01-1-1V5a1 1 0 011-1z"/></svg>
            <p>No quotations found. Generate your first one above.</p>
        </div>
    </div>{{-- end tab-quotations --}}

    {{-- TAB: My Service Requests --}}
    <div class="dash-tab-panel" id="tab-services">
        <div id="sr-loading" class="dash-loading">Loading service requests...</div>
        <div id="sr-msg" class="dash-msg"></div>

        <h2 class="dash-section-title">Service Requests</h2>
        <div id="sr-list" class="dash-sr-grid"></div>
        <div id="sr-empty" class="dash-empty" style="display:none;">
            <svg class="dash-empty-icon" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/></svg>
            <p>No service requests yet.</p>
        </div>

        <h2 class="dash-section-title" style="margin-top:32px;">Inspection Requests</h2>
        <div id="ir-list" class="dash-sr-grid"></div>
        <div id="ir-empty" class="dash-empty" style="display:none;">
            <svg class="dash-empty-icon" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5"><path d="M9 11l3 3L22 4M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg>
            <p>No inspection requests yet.</p>
        </div>
    </div>{{-- end tab-services --}}

</div>{{-- end .dash-wrapper --}}

@elseif ($user->role === \App\Models\User::ROLE_ADMIN)
@php
    $adm_totalCustomers   = \App\Models\User::where('role', \App\Models\User::ROLE_CUSTOMER)->count();
    $adm_totalTechnicians = \App\Models\User::where('role', \App\Models\User::ROLE_TECHNICIAN)->count();
    $adm_pendingInspCount = \App\Models\InspectionRequest::where('status', 'pending')->count();
    $adm_pendingServiceCount = \App\Models\ServiceRequest::where('status', 'pending')->count();
    $adm_initialQuotations = \App\Models\Quotation::where('quotation_type', 'initial')->count();
    $adm_finalQuotations   = \App\Models\Quotation::where('quotation_type', 'final')->count();
    $adm_pendingInspections = \App\Models\InspectionRequest::with(['customer', 'technician'])
        ->where('status', 'pending')->latest()->limit(5)->get();
    $adm_pendingServices = \App\Models\ServiceRequest::with(['customer', 'technician'])
        ->where('status', 'pending')->latest()->limit(5)->get();
    $adm_recentQuotations = \App\Models\Quotation::with('customer')
        ->latest()->limit(5)->get();
@endphp
<style>
    /* ── Admin Dashboard (adm2- prefix) ── */
    .adm2-wrap { display: grid; gap: 22px; width: 100%; min-width: 0; box-sizing: border-box; }

    /* Hero */
    .adm2-hero {
        background: linear-gradient(135deg, #b8d4f0 0%, #cde2f7 55%, #d8ecfc 100%);
        border-radius: 16px;
        padding: 28px 32px 30px;
        position: relative;
        overflow: hidden;
    }
    .adm2-hero::after {
        content: '';
        position: absolute;
        right: -28px; top: -28px;
        width: 160px; height: 160px;
        border-radius: 50%;
        background: rgba(255,255,255,0.25);
        pointer-events: none;
    }
    .adm2-hero-title {
        font-size: 30px;
        font-weight: 800;
        color: #102a43;
        margin: 0 0 8px;
        letter-spacing: -0.03em;
    }
    .adm2-hero-copy {
        color: #334155;
        font-size: 14px;
        line-height: 1.65;
        max-width: 600px;
        margin: 0;
    }

    /* Stats row */
    .adm2-stats {
        display: flex;
        gap: 14px;
        flex-wrap: wrap;
        min-width: 0;
    }
    .adm2-stat-card {
        flex: 1;
        min-width: 0;
        max-width: 100%;
        background: #ffffff;
        border: 1px solid #dde8f4;
        border-radius: 14px;
        padding: 16px 18px;
        box-shadow: 0 3px 10px rgba(15,23,42,0.05);
        box-sizing: border-box;
    }
    .adm2-stat-label {
        font-size: 11px;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 10px;
        min-height: 28px;
    }
    .adm2-stat-value {
        font-size: 28px;
        font-weight: 800;
        color: #102a43;
        line-height: 1;
        margin-bottom: 10px;
    }
    .adm2-stat-value-pair {
        display: flex;
        gap: 18px;
        margin-bottom: 10px;
        align-items: flex-end;
    }
    .adm2-stat-pair-label {
        font-size: 10px;
        color: #94a3b8;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        margin-bottom: 2px;
    }
    .adm2-stat-pair-value {
        font-size: 24px;
        font-weight: 800;
        color: #102a43;
        line-height: 1;
    }
    .adm2-stat-link {
        font-size: 12px;
        font-weight: 600;
        color: #1e6fb5;
        text-decoration: none;
    }
    .adm2-stat-link:hover { text-decoration: underline; }

    /* Two-column rows */
    .adm2-row         { display: grid; gap: 18px; grid-template-columns: 260px 1fr; min-width: 0; }
    .adm2-row-equal   { display: grid; gap: 18px; grid-template-columns: 1fr 1fr; min-width: 0; }

    .adm2-panel {
        background: #ffffff;
        border: 1px solid #dde8f4;
        border-radius: 16px;
        padding: 20px 22px;
        box-shadow: 0 3px 12px rgba(15,23,42,0.05);
        min-width: 0;
        box-sizing: border-box;
        overflow: hidden;
    }
    .adm2-panel-title {
        font-size: 15px;
        font-weight: 700;
        color: #102a43;
        margin: 0 0 16px;
    }

    /* Quick action buttons */
    .adm2-action-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        width: 100%;
        padding: 11px 12px;
        border-radius: 9px;
        border: 1.5px solid #dbe7f3;
        background: #f4f9ff;
        color: #102a43;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        margin-bottom: 9px;
        transition: background 0.15s, border-color 0.15s;
        cursor: pointer;
    }
    .adm2-action-btn:hover {
        background: #e8f3fb;
        border-color: #b0ccdf;
        text-decoration: none;
        color: #102a43;
    }
    .adm2-cta-btn {
        display: block;
        width: 100%;
        padding: 12px;
        margin-top: 14px;
        border-radius: 9px;
        border: none;
        background: #d4a017;
        color: #ffffff;
        font-size: 14px;
        font-weight: 700;
        text-align: center;
        text-decoration: none;
        cursor: pointer;
        transition: background 0.15s;
        box-shadow: 0 5px 16px rgba(212,160,23,0.28);
    }
    .adm2-cta-btn:hover { background: #b8880f; text-decoration: none; color: #fff; }

    /* Panel tables */
    .adm2-table-wrap { overflow-x: auto; }
    .adm2-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 12.5px;
    }
    .adm2-table th {
        text-align: left;
        font-size: 10.5px;
        font-weight: 700;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        color: #64748b;
        padding: 7px 9px;
        border-bottom: 1px solid #e8eff7;
        white-space: nowrap;
        background: #f7fafd;
    }
    .adm2-table td {
        padding: 9px 9px;
        border-bottom: 1px solid #f0f5fb;
        color: #334155;
        vertical-align: middle;
    }
    .adm2-table tbody tr:last-child td { border-bottom: none; }
    .adm2-table tbody tr:hover td { background: #f9fcff; }
    .adm2-table-id { font-weight: 700; color: #102a43; }

    /* Row action buttons */
    .adm2-row-btn {
        display: inline-flex;
        align-items: center;
        padding: 3px 9px;
        border-radius: 5px;
        font-size: 11px;
        font-weight: 600;
        text-decoration: none;
        border: 1px solid transparent;
        transition: background 0.12s;
    }
    .adm2-row-btn-view    { background: #eff6ff; color: #1d4ed8; border-color: #bfdbfe; }
    .adm2-row-btn-view:hover    { background: #dbeafe; text-decoration: none; }
    .adm2-row-btn-approve { background: #dcfce7; color: #15803d; border-color: #bbf7d0; }
    .adm2-row-btn-approve:hover { background: #bbf7d0; text-decoration: none; }

    /* Status badges */
    .adm2-badge {
        display: inline-block;
        padding: 3px 9px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 700;
        white-space: nowrap;
    }
    .adm2-badge-pending      { background: #fef9c3; color: #a16207; }
    .adm2-badge-approved     { background: #dcfce7; color: #15803d; }
    .adm2-badge-in-progress  { background: #dbeafe; color: #1d4ed8; }
    .adm2-badge-completed    { background: #d1fae5; color: #065f46; }
    .adm2-badge-cancelled    { background: #fee2e2; color: #dc2626; }
    .adm2-badge-default      { background: #f1f5f9; color: #475569; }

    /* Panel footer (view all link) */
    .adm2-panel-footer {
        display: flex;
        justify-content: flex-end;
        margin-top: 12px;
        padding-top: 11px;
        border-top: 1px solid #f0f5fb;
    }
    .adm2-view-all {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 6px 14px;
        border-radius: 7px;
        border: 1.5px solid #d4e4f3;
        background: #f4f9ff;
        color: #1e4068;
        font-size: 12px;
        font-weight: 600;
        text-decoration: none;
        transition: background 0.12s;
    }
    .adm2-view-all:hover { background: #e8f3fb; text-decoration: none; }

    /* Empty row */
    .adm2-empty-row td {
        text-align: center;
        color: #94a3b8;
        font-style: italic;
        padding: 20px;
    }

    @media (max-width: 1100px) {
        .adm2-row       { grid-template-columns: 1fr; }
        .adm2-row-equal { grid-template-columns: 1fr; }
    }
    @media (max-width: 640px) {
        .adm2-stats { gap: 10px; }
        .adm2-stat-card { min-width: 0; flex-basis: calc(50% - 5px); }
        .adm2-hero { padding: 20px 18px; }
        .adm2-hero-title { font-size: 22px; }
        .adm2-panel { padding: 16px; }
    }
</style>

<div class="adm2-wrap">

    {{-- HERO --}}
    <section class="adm2-hero" aria-label="Admin Dashboard">
        <h1 class="adm2-hero-title">Admin Dashboard</h1>
        <p class="adm2-hero-copy">Manage quotations, pricing data, testimonies, technician accounts, and request operations from one unified SolMate admin workspace.</p>
    </section>

    {{-- STAT CARDS --}}
    <div class="adm2-stats">
        <div class="adm2-stat-card">
            <div class="adm2-stat-label">Total Customers</div>
            <div class="adm2-stat-value">{{ $adm_totalCustomers }}</div>
            <a href="{{ route('admin.customers') }}" class="adm2-stat-link">View Customers</a>
        </div>
        <div class="adm2-stat-card">
            <div class="adm2-stat-label">Total Technicians</div>
            <div class="adm2-stat-value">{{ $adm_totalTechnicians }}</div>
            <a href="{{ route('admin.technicians.create') }}" class="adm2-stat-link">View Technicians</a>
        </div>
        <div class="adm2-stat-card">
            <div class="adm2-stat-label">Pending Quotation Requests</div>
            <div class="adm2-stat-value">{{ $adm_pendingInspCount }}</div>
            <a href="{{ route('admin.request-assignments') }}#inspection-requests-section" class="adm2-stat-link">View Requests</a>
        </div>
        <div class="adm2-stat-card">
            <div class="adm2-stat-label">Pending Service Requests</div>
            <div class="adm2-stat-value">{{ $adm_pendingServiceCount }}</div>
            <a href="{{ route('admin.request-assignments') }}#service-requests-section" class="adm2-stat-link">View Requests</a>
        </div>
        <div class="adm2-stat-card">
            <div class="adm2-stat-label">Quotations Generated</div>
            <div class="adm2-stat-value-pair">
                <div>
                    <div class="adm2-stat-pair-label">Initial</div>
                    <div class="adm2-stat-pair-value">{{ $adm_initialQuotations }}</div>
                </div>
                <div>
                    <div class="adm2-stat-pair-label">Final</div>
                    <div class="adm2-stat-pair-value">{{ $adm_finalQuotations }}</div>
                </div>
            </div>
            <a href="{{ route('quotations.item-builder') }}" class="adm2-stat-link">View Quotations</a>
        </div>
    </div>

    {{-- ROW: Quick Actions + Pending Inspection Request --}}
    <div class="adm2-row">

        {{-- Quick Actions panel --}}
        <div class="adm2-panel">
            <h2 class="adm2-panel-title">Quick Actions</h2>
            <a href="{{ route('admin.request-assignments') }}" class="adm2-action-btn">Assign Technician</a>
            <a href="{{ route('admin.request-assignments') }}" class="adm2-action-btn">Approve Inspection Request</a>
            <a href="{{ route('admin.request-assignments') }}" class="adm2-action-btn">Approve Service Request</a>
            <a href="{{ route('admin.notifications') }}"      class="adm2-action-btn">View Reports</a>
            <a href="{{ route('admin.quotation-settings') }}" class="adm2-action-btn">Rule Configuration</a>
            <a href="{{ route('admin.quotation-settings') }}" class="adm2-cta-btn">Go to Rule Configurations</a>
        </div>

        {{-- Pending Inspection Request panel --}}
        <div class="adm2-panel">
            <h2 class="adm2-panel-title">Pending Inspection Request</h2>
            <div class="adm2-table-wrap">
                <table class="adm2-table">
                    <thead>
                        <tr>
                            <th>Request ID</th>
                            <th>Customer</th>
                            <th>Preferred Schedule</th>
                            <th>Status</th>
                            <th>Assigned Technician</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($adm_pendingInspections as $insp)
                            <tr>
                                <td class="adm2-table-id">IR-{{ $insp->id }}</td>
                                <td>{{ $insp->customer?->name ?? 'N/A' }}</td>
                                <td style="white-space:nowrap;">{{ $insp->date_needed ? \Carbon\Carbon::parse($insp->date_needed)->format('M d • g:i A') : '—' }}</td>
                                <td>
                                    @php $adm_status = strtolower(str_replace(['_',' '], '-', $insp->status ?? 'pending')); @endphp
                                    <span class="adm2-badge adm2-badge-{{ $adm_status }}">{{ ucfirst(str_replace('_',' ',$insp->status ?? 'pending')) }}</span>
                                </td>
                                <td>{{ $insp->technician?->name ?? 'Unassigned' }}</td>
                                <td style="white-space:nowrap;">
                                    <a href="{{ route('admin.request-assignments') }}" class="adm2-row-btn adm2-row-btn-view">View</a>
                                    <a href="{{ route('admin.request-assignments') }}" class="adm2-row-btn adm2-row-btn-approve">Approve</a>
                                </td>
                            </tr>
                        @empty
                            <tr class="adm2-empty-row"><td colspan="6">No pending inspection requests.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="adm2-panel-footer">
                <a href="{{ route('admin.request-assignments') }}" class="adm2-view-all">View All Inspection</a>
            </div>
        </div>

    </div>{{-- /.adm2-row --}}

    {{-- ROW: Pending Service Request + Recent Quotations --}}
    <div class="adm2-row-equal">

        {{-- Pending Service Request panel --}}
        <div class="adm2-panel">
            <h2 class="adm2-panel-title">Pending Service Request</h2>
            <div class="adm2-table-wrap">
                <table class="adm2-table">
                    <thead>
                        <tr>
                            <th>Request ID</th>
                            <th>Service Type</th>
                            <th>Preferred Schedule</th>
                            <th>Status</th>
                            <th>Assigned Tech</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($adm_pendingServices as $svc)
                            <tr>
                                <td class="adm2-table-id">SR-{{ $svc->id }}</td>
                                <td>{{ ucfirst($svc->request_type ?? 'Service') }}</td>
                                <td style="white-space:nowrap;">{{ $svc->date_needed ? \Carbon\Carbon::parse($svc->date_needed)->format('M d • g:i A') : '—' }}</td>
                                <td>
                                    @php $adm_sv_status = strtolower(str_replace(['_',' '], '-', $svc->status ?? 'pending')); @endphp
                                    <span class="adm2-badge adm2-badge-{{ $adm_sv_status }}">{{ ucfirst(str_replace('_',' ',$svc->status ?? 'pending')) }}</span>
                                </td>
                                <td>{{ $svc->technician?->name ?? 'Unassigned' }}</td>
                                <td style="white-space:nowrap;">
                                    <a href="{{ route('admin.request-assignments') }}" class="adm2-row-btn adm2-row-btn-view">View</a>
                                    <a href="{{ route('admin.request-assignments') }}" class="adm2-row-btn adm2-row-btn-approve">Approve</a>
                                </td>
                            </tr>
                        @empty
                            <tr class="adm2-empty-row"><td colspan="6">No pending service requests.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Recent Quotations panel --}}
        <div class="adm2-panel">
            <h2 class="adm2-panel-title">Recent Quotations</h2>
            <div class="adm2-table-wrap">
                <table class="adm2-table">
                    <thead>
                        <tr>
                            <th>Quote ID</th>
                            <th>Type</th>
                            <th>Customer</th>
                            <th>Generated Date</th>
                            <th>Est. Cost</th>
                            <th>ROI</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($adm_recentQuotations as $q)
                            <tr>
                                <td class="adm2-table-id">Q-{{ str_pad($q->id, 3, '0', STR_PAD_LEFT) }}</td>
                                <td>{{ ucfirst($q->quotation_type ?? 'Initial') }}</td>
                                <td>{{ $q->customer?->name ?? 'N/A' }}</td>
                                <td style="white-space:nowrap;">{{ $q->created_at->format('M d, Y') }}</td>
                                <td style="white-space:nowrap;">{{ $q->project_cost ? '₱'.number_format($q->project_cost) : '—' }}</td>
                                <td>{{ $q->roi_years ? $q->roi_years.' yrs' : '—' }}</td>
                                <td>
                                    @php $adm_q_status = strtolower(str_replace(['_',' '], '-', $q->status ?? 'pending')); @endphp
                                    <span class="adm2-badge adm2-badge-{{ $adm_q_status }}">{{ ucfirst(str_replace('_',' ',$q->status ?? 'pending')) }}</span>
                                </td>
                                <td>
                                    <a href="{{ route('quotations.item-builder') }}" class="adm2-row-btn adm2-row-btn-view">View</a>
                                </td>
                            </tr>
                        @empty
                            <tr class="adm2-empty-row"><td colspan="8">No quotations found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>{{-- /.adm2-row-equal --}}

</div>{{-- /.adm2-wrap --}}

@elseif ($user->role === \App\Models\User::ROLE_TECHNICIAN)
<div class="adm-home">
    <section class="adm-hero" aria-label="Technician dashboard hero">
        <p class="adm-eyebrow">Technician Workspace</p>
        <h1 class="adm-title">Technician Dashboard</h1>
        <p class="adm-copy">Use the SolMate technician workspace to access quotation item editing tools that support your assigned installation and service workflows.</p>
    </section>

    <div class="adm-quick-grid">
        <a class="adm-link-card" href="{{ route('quotations.item-builder') }}">
            <div class="adm-link-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#f4c542" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
            </div>
            <p class="adm-link-title">Quotation Item Builder</p>
            <p class="adm-link-copy">Open the existing quotation item builder to review and edit final quotation line items.</p>
        </a>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
(function () {
    'use strict';

    function qs(s, r) { return (r || document).querySelector(s); }
    function setVisible(el, show) { if (el) el.style.display = show ? '' : 'none'; }
    function showMsg(el, type, text) {
        if (!el) return;
        el.className = 'dash-msg show dash-msg-' + type;
        el.textContent = text;
    }
    function hideMsg(el) { if (el) { el.className = 'dash-msg'; el.textContent = ''; } }

    function fmtDate(str) {
        if (!str) return '-';
        try { return new Date(str).toLocaleDateString('en-PH', { year:'numeric', month:'short', day:'numeric' }); }
        catch(e) { return str; }
    }

    function fmtPeso(val) {
        if (val === null || val === undefined || isNaN(Number(val))) return '-';
        return 'PHP ' + Number(val).toLocaleString('en-PH', { minimumFractionDigits:2, maximumFractionDigits:2 });
    }

    function initials(name) {
        return String(name || '').trim().split(/\s+/).slice(0,2).map(function(w){return w[0]||'';}).join('').toUpperCase() || '?';
    }

    function escHtml(s) {
        return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    function profilePictureUrl(path) {
        var cleanPath = String(path || '').replace(/^\/+/, '');
        return cleanPath ? '/storage/' + cleanPath : '';
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
        var payload = await resp.json().catch(function(){ return {}; });
        if (!resp.ok) {
            var err = new Error(payload.message || 'Request failed.');
            err.status = resp.status;
            err.errors = payload.errors || {};
            throw err;
        }
        return payload;
    }

    async function formDataRequest(endpoint, opts) {
        await ensureCsrf();

        var resp = await fetch(endpoint, {
            method: (opts && opts.method) || 'POST',
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-XSRF-TOKEN': getCookie('XSRF-TOKEN') || '',
            },
            body: opts && opts.body,
        });

        var payload = await resp.json().catch(function(){ return {}; });
        if (!resp.ok) {
            var err = new Error(payload.message || 'Request failed.');
            err.status = resp.status;
            err.errors = payload.errors || {};
            throw err;
        }
        return payload;
    }

    function clearFieldErrors(prefix) {
        document.querySelectorAll('[id^="' + prefix + '"]').forEach(function(el) {
            if (el.classList.contains('field-error')) el.textContent = '';
        });
    }

    function applyFieldErrors(prefix, errors) {
        Object.keys(errors).forEach(function(key) {
            var el = qs('#' + prefix + key.replace(/\./g,'-') + '-error');
            if (el) el.textContent = Array.isArray(errors[key]) ? errors[key][0] : errors[key];
        });
    }

    function typeBadge(type) {
        var map = { inspection:'inspection', installation:'installation', maintenance:'maintenance', initial:'initial', final:'final' };
        var key = String(type||'').toLowerCase();
        var cls = map[key] || 'default';
        var label = String(type||'Unknown').replace(/_/g,' ').replace(/\b\w/g,function(c){return c.toUpperCase();});
        return '<span class="dash-badge dash-badge-type-' + cls + '">' + escHtml(label) + '</span>';
    }

    function statusBadge(status) {
        var raw = String(status||'pending').toLowerCase().replace(/\s+/g,'_');
        var label = raw.replace(/_/g,' ').replace(/\b\w/g,function(c){return c.toUpperCase();});
        return '<span class="dash-badge dash-badge-status-' + raw + '">' + escHtml(label) + '</span>';
    }

    var dashWrapper = qs('.dash-wrapper');

    if (!dashWrapper) {
        return;
    }

    var initialProfilePicturePath = dashWrapper.dataset.profilePicturePath || '';

    function setImageSource(target, src, alt) {
        if (!target) return;
        if (src) {
            target.src = src;
        } else {
            target.removeAttribute('src');
        }
        if (alt) {
            target.alt = alt;
        }
    }

    function syncSharedProfileButtons(imageUrl, userName) {
        document.querySelectorAll('[data-profile-menu-button]').forEach(function(button) {
            button.classList.toggle('has-image', !!imageUrl);
        });

        document.querySelectorAll('[data-profile-menu-image]').forEach(function(imageEl) {
            setVisible(imageEl, !!imageUrl);
            if (imageUrl) {
                setImageSource(imageEl, imageUrl, (userName || 'User') + ' profile picture');
            }
        });

        document.querySelectorAll('[data-profile-menu-icon]').forEach(function(iconEl) {
            setVisible(iconEl, !imageUrl);
        });
    }

    function renderProfilePicture(path, userName) {
        var imageUrl = path ? profilePictureUrl(path) : '';
        var avatar = qs('#dash-avatar');
        var avatarImage = qs('#dash-avatar-image');
        var avatarFallback = qs('#dash-avatar-initials');
        var preview = qs('#dash-profile-picture-preview');
        var previewImage = qs('#dash-profile-picture-preview-image');
        var previewFallback = qs('#dash-profile-picture-preview-fallback');

        if (imageUrl) {
            if (!avatarImage && avatar) {
                avatarImage = document.createElement('img');
                avatarImage.id = 'dash-avatar-image';
                avatarImage.className = 'dash-avatar-image';
                avatar.insertBefore(avatarImage, avatarFallback);
            }
            if (!previewImage && preview) {
                previewImage = document.createElement('img');
                previewImage.id = 'dash-profile-picture-preview-image';
                preview.insertBefore(previewImage, previewFallback);
            }

            setImageSource(avatarImage, imageUrl, (userName || 'User') + ' profile picture');
            setImageSource(previewImage, imageUrl, (userName || 'User') + ' profile picture preview');
            setVisible(avatarImage, true);
            setVisible(previewImage, true);
            setVisible(avatarFallback, false);
            setVisible(previewFallback, false);
        } else {
            if (avatarImage) setVisible(avatarImage, false);
            if (previewImage) setVisible(previewImage, false);
            if (avatarFallback) avatarFallback.textContent = initials(userName);
            if (previewFallback) previewFallback.textContent = initials(userName);
            setVisible(avatarFallback, true);
            setVisible(previewFallback, true);
        }

        syncSharedProfileButtons(imageUrl, userName);
    }

    /* ── Tab switching ── */
    var tabBtns = document.querySelectorAll('.dash-tab-btn');
    var tabPanels = document.querySelectorAll('.dash-tab-panel');
    tabBtns.forEach(function(btn) {
        btn.addEventListener('click', function() {
            var target = btn.dataset.target;
            tabBtns.forEach(function(b){ b.classList.remove('active'); });
            tabPanels.forEach(function(p){ p.classList.remove('active'); });
            btn.classList.add('active');
            var panel = qs('#' + target);
            if (panel) panel.classList.add('active');
            if (target === 'tab-quotations' && !quotationsLoaded) loadQuotations();
            if (target === 'tab-services' && !servicesLoaded) loadServiceRequests();
        });
    });

    /* ── Avatar initials ── */
    (function() {
        var nameEl = qs('#dash-banner-name');
        if (nameEl) {
            renderProfilePicture(initialProfilePicturePath, nameEl.textContent);
        }
    })();

    /* ── Profile toggle ── */
    var editProfileCard = qs('#edit-profile-form-card');
    var changePwdCard   = qs('#change-pwd-form-card');

    qs('#toggle-edit-profile-btn').addEventListener('click', function() {
        var isHidden = editProfileCard.style.display === 'none';
        setVisible(editProfileCard, isHidden);
        if (isHidden) { setVisible(changePwdCard, false); editProfileCard.scrollIntoView({behavior:'smooth',block:'nearest'}); }
    });
    qs('#toggle-change-pwd-btn').addEventListener('click', function() {
        var isHidden = changePwdCard.style.display === 'none';
        setVisible(changePwdCard, isHidden);
        if (isHidden) { setVisible(editProfileCard, false); changePwdCard.scrollIntoView({behavior:'smooth',block:'nearest'}); }
    });
    qs('#ep-cancel-btn').addEventListener('click', function(){ setVisible(editProfileCard, false); });
    qs('#cp-cancel-btn').addEventListener('click', function(){ setVisible(changePwdCard, false); });

    /* ── Edit Profile ── */
    var editProfileForm = qs('#edit-profile-form');
    var epSubmitBtn     = qs('#ep-submit-btn');
    var profileMsg      = qs('#dash-profile-msg');
    var pictureInput    = qs('#ep-profile-picture');
    var pictureHelper   = qs('#ep-profile-picture-helper');
    var pictureUploadBtn = qs('#ep-upload-picture-btn');

    pictureInput.addEventListener('change', function() {
        clearFieldErrors('ep-');
        pictureHelper.textContent = pictureInput.files && pictureInput.files[0]
            ? pictureInput.files[0].name
            : 'No file selected.';
    });

    pictureUploadBtn.addEventListener('click', async function() {
        hideMsg(profileMsg);
        clearFieldErrors('ep-');

        if (!pictureInput.files || !pictureInput.files[0]) {
            applyFieldErrors('ep-', { profile_picture: ['Please choose an image file to upload.'] });
            showMsg(profileMsg, 'error', 'Please choose an image file to upload.');
            return;
        }

        var formData = new FormData();
        formData.append('profile_picture', pictureInput.files[0]);

        pictureUploadBtn.disabled = true;
        pictureUploadBtn.textContent = 'Uploading...';

        try {
            var uploadResp = await formDataRequest('/api/user/profile-picture', {
                method: 'POST',
                body: formData,
            });
            renderProfilePicture(uploadResp.user.profile_picture, uploadResp.user.name || qs('#dash-banner-name').textContent);
            pictureInput.value = '';
            pictureHelper.textContent = 'No file selected.';
            showMsg(profileMsg, 'success', uploadResp.message || 'Profile picture updated successfully.');
        } catch (err) {
            applyFieldErrors('ep-', err.errors || {});
            showMsg(profileMsg, 'error', err.message || 'Could not upload profile picture.');
        } finally {
            pictureUploadBtn.disabled = false;
            pictureUploadBtn.textContent = 'Upload Picture';
        }
    });

    editProfileForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        clearFieldErrors('ep-'); hideMsg(profileMsg);
        epSubmitBtn.disabled = true; epSubmitBtn.textContent = 'Saving...';
        try {
            var resp = await apiRequest('/api/customer/account', {
                method: 'PUT',
                body: {
                    name: qs('#ep-name').value.trim(),
                    email: qs('#ep-email').value.trim(),
                    address: qs('#ep-address').value.trim(),
                    contact_number: qs('#ep-contact-number').value.trim(),
                },
            });
            qs('#di-name').textContent  = resp.user.name;
            qs('#di-email').textContent = resp.user.email;
            qs('#di-address').textContent = resp.user.address || 'Not provided';
            qs('#di-contact-number').textContent = resp.user.contact_number || 'Not provided';
            qs('#dash-banner-name').textContent  = resp.user.name;
            qs('#dash-banner-email').textContent = resp.user.email;
            qs('#ep-address').value = resp.user.address || '';
            qs('#ep-contact-number').value = resp.user.contact_number || '';
            renderProfilePicture(resp.user.profile_picture, resp.user.name);
            setVisible(editProfileCard, false);
            showMsg(profileMsg, 'success', resp.message || 'Profile updated successfully.');
        } catch(err) {
            applyFieldErrors('ep-', err.errors || {});
            showMsg(profileMsg, 'error', err.message || 'Could not update profile.');
        } finally {
            epSubmitBtn.disabled = false; epSubmitBtn.textContent = 'Save Changes';
        }
    });

    /* ── Change Password ── */
    var changePwdForm = qs('#change-pwd-form');
    var cpSubmitBtn   = qs('#cp-submit-btn');

    changePwdForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        clearFieldErrors('cp-'); hideMsg(profileMsg);
        cpSubmitBtn.disabled = true; cpSubmitBtn.textContent = 'Updating...';
        try {
            var resp = await apiRequest('/api/customer/account/password', {
                method: 'PUT',
                body: {
                    current_password: qs('#cp-current').value,
                    new_password: qs('#cp-new').value,
                    new_password_confirmation: qs('#cp-confirm').value,
                },
            });
            changePwdForm.reset();
            setVisible(changePwdCard, false);
            showMsg(profileMsg, 'success', resp.message || 'Password updated successfully.');
        } catch(err) {
            applyFieldErrors('cp-', err.errors || {});
            showMsg(profileMsg, 'error', err.message || 'Could not update password.');
        } finally {
            cpSubmitBtn.disabled = false; cpSubmitBtn.textContent = 'Update Password';
        }
    });

    /* ── Quotations Tab ── */
    var allQuotations  = [];
    var activeFilter   = 'all';
    var quotationsLoaded = false;

    var qLoading = qs('#q-loading');
    var qMsg     = qs('#q-msg');
    var qList    = qs('#q-list');
    var qEmpty   = qs('#q-empty');

    var filterChips     = document.querySelectorAll('.dash-filter-chips .dash-chip:not(#generate-new-chip)');
    var generateNewChip = qs('#generate-new-chip');
    var newQPanel       = qs('#new-quotation-panel');
    var newQForm        = qs('#new-quotation-form');
    var nqSubmitBtn     = qs('#nq-submit-btn');
    var newQMsg         = qs('#new-q-msg');

    filterChips.forEach(function(chip) {
        chip.addEventListener('click', function() {
            filterChips.forEach(function(c){ c.classList.remove('active'); });
            chip.classList.add('active');
            activeFilter = chip.dataset.filter;
            renderQuotations();
        });
    });

    generateNewChip.addEventListener('click', function() {
        newQPanel.classList.toggle('show');
        if (newQPanel.classList.contains('show')) newQPanel.scrollIntoView({behavior:'smooth',block:'nearest'});
    });

    qs('#nq-cancel-btn').addEventListener('click', function() {
        newQPanel.classList.remove('show'); newQForm.reset(); hideMsg(newQMsg); clearFieldErrors('nq-');
    });

    newQForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        clearFieldErrors('nq-'); hideMsg(newQMsg);
        nqSubmitBtn.disabled = true; nqSubmitBtn.textContent = 'Generating...';
        try {
            var body = { monthly_electric_bill: parseFloat(qs('#nq-bill').value) };
            var r = qs('#nq-remarks').value.trim();
            if (r) body.remarks = r;
            var resp = await apiRequest('/api/quotations', { method:'POST', body:body });
            showMsg(newQMsg, 'success', resp.message || 'Quotation generated successfully.');
            newQForm.reset();
            await loadQuotations();
        } catch(err) {
            applyFieldErrors('nq-', err.errors || {});
            showMsg(newQMsg, 'error', err.message || 'Could not generate quotation.');
        } finally {
            nqSubmitBtn.disabled = false; nqSubmitBtn.textContent = 'Generate Quotation';
        }
    });

    function renderQuotationDetails(q) {
        var rows = [
            ['Monthly Bill', fmtPeso(q.monthly_electric_bill)],
            ['Rate / kWh', q.rate_per_kwh ? 'PHP ' + q.rate_per_kwh : '-'],
            ['Panel Quantity', q.panel_quantity || '-'],
            ['Panel Watts', q.panel_watts ? q.panel_watts + ' W' : '-'],
            ['With Battery', q.with_battery ? 'Yes' : 'No'],
            ['Battery Model', q.battery_model || '-'],
            ['System Type', q.pv_system_type || '-'],
            ['Inverter Type', q.inverter_type || '-'],
            ['Materials Cost', fmtPeso(q.materials_subtotal)],
            ['Labor Cost', fmtPeso(q.labor_cost)],
            ['Annual Savings', fmtPeso(q.estimated_annual_savings)],
            ['Remarks', q.remarks || '-'],
        ];
        return '<div class="dash-info-grid">' + rows.map(function(row) {
            return '<div class="dash-info-row" style="padding:8px 0;">'
                + '<span class="dash-info-label">' + escHtml(row[0]) + '</span>'
                + '<span class="dash-info-value">' + escHtml(String(row[1])) + '</span>'
                + '</div>';
        }).join('') + '</div>';
    }

    window.toggleQDetails = function(id) {
        var el = qs('#qd-' + id);
        if (el) setVisible(el, el.style.display === 'none');
    };

    function renderQuotations() {
        var filtered = activeFilter === 'all' ? allQuotations : allQuotations.filter(function(q) {
            var type   = String(q.quotation_type || '').toLowerCase();
            var status = String(q.status || '').toLowerCase();
            if (activeFilter === 'initial')   return type === 'initial';
            if (activeFilter === 'final')     return type === 'final';
            if (activeFilter === 'completed') return status === 'completed';
            return true;
        });

        if (filtered.length === 0) {
            qList.innerHTML = ''; setVisible(qEmpty, true); return;
        }
        setVisible(qEmpty, false);

        qList.innerHTML = filtered.map(function(q) {
            var systemKw = q.system_kw    ? Number(q.system_kw).toFixed(2) + ' kW' : '-';
            var savings  = q.estimated_monthly_savings ? fmtPeso(q.estimated_monthly_savings) + '/mo' : '-';
            var roi      = q.roi_years    ? Number(q.roi_years).toFixed(1) + ' yrs' : '-';

            return '<div class="dash-quotation-card">'
                + '<div class="dash-q-header">'
                +   '<div>'
                +     '<div class="dash-q-id">Quote #' + escHtml(q.id) + '</div>'
                +     '<div class="dash-q-meta">Generated ' + fmtDate(q.created_at) + '</div>'
                +   '</div>'
                +   '<div class="dash-q-badges">' + typeBadge(q.quotation_type||'initial') + statusBadge(q.status||'pending') + '</div>'
                + '</div>'
                + '<div class="dash-q-grid">'
                +   '<div class="dash-q-stat"><div class="dash-q-stat-label">System Size</div><div class="dash-q-stat-value">' + escHtml(systemKw) + '</div></div>'
                +   '<div class="dash-q-stat"><div class="dash-q-stat-label">Project Cost</div><div class="dash-q-stat-value">' + fmtPeso(q.project_cost) + '</div></div>'
                +   '<div class="dash-q-stat"><div class="dash-q-stat-label">Monthly Savings</div><div class="dash-q-stat-value">' + escHtml(savings) + '</div></div>'
                +   '<div class="dash-q-stat"><div class="dash-q-stat-label">ROI Period</div><div class="dash-q-stat-value">' + escHtml(roi) + '</div></div>'
                + '</div>'
                + '<div class="dash-q-actions">'
                +   '<button class="dash-btn dash-btn-ghost" style="font-size:13px;" onclick="toggleQDetails(' + escHtml(q.id) + ')">View Details</button>'
                + '</div>'
                + '<div class="dash-q-details" id="qd-' + escHtml(q.id) + '" style="display:none;margin-top:14px;border-top:1px solid #f1f5f9;padding-top:14px;">'
                +   renderQuotationDetails(q)
                + '</div>'
                + '</div>';
        }).join('');
    }

    async function loadQuotations() {
        setVisible(qLoading, true); hideMsg(qMsg);
        try {
            var data = await apiRequest('/api/quotations');
            allQuotations = Array.isArray(data) ? data : [];
            quotationsLoaded = true;
            renderQuotations();
        } catch(err) {
            showMsg(qMsg, 'error', err.message || 'Could not load quotations.');
        } finally {
            setVisible(qLoading, false);
        }
    }

    /* ── Service Requests Tab ── */
    var servicesLoaded = false;

    var srLoading = qs('#sr-loading');
    var srMsg     = qs('#sr-msg');
    var srList    = qs('#sr-list');
    var srEmpty   = qs('#sr-empty');
    var irList    = qs('#ir-list');
    var irEmpty   = qs('#ir-empty');

    function renderServiceRequests(items) {
        if (!items || items.length === 0) { srList.innerHTML = ''; setVisible(srEmpty, true); return; }
        setVisible(srEmpty, false);
        srList.innerHTML = items.map(function(sr) {
            var notes = String(sr.details||'').slice(0,120) + (String(sr.details||'').length > 120 ? '...' : '');
            return '<div class="dash-sr-card">'
                + '<div class="dash-sr-top"><div class="dash-sr-badges">' + typeBadge(sr.request_type) + statusBadge(sr.status) + '</div></div>'
                + '<div><div class="dash-sr-id">SR #' + escHtml(sr.id) + '</div>'
                + '<div class="dash-sr-date">' + (sr.date_needed ? 'Preferred: ' + fmtDate(sr.date_needed) : 'No preferred date') + '</div></div>'
                + '<div class="dash-sr-notes">' + escHtml(notes||'No details provided.') + '</div>'
                + '</div>';
        }).join('');
    }

    function renderInspectionRequests(items) {
        if (!items || items.length === 0) { irList.innerHTML = ''; setVisible(irEmpty, true); return; }
        setVisible(irEmpty, false);
        irList.innerHTML = items.map(function(ir) {
            var notes = String(ir.details||'').slice(0,120) + (String(ir.details||'').length > 120 ? '...' : '');
            return '<div class="dash-sr-card">'
                + '<div class="dash-sr-top"><div class="dash-sr-badges">' + typeBadge('inspection') + statusBadge(ir.status) + '</div></div>'
                + '<div><div class="dash-sr-id">INS #' + escHtml(ir.id) + '</div>'
                + '<div class="dash-sr-date">' + (ir.date_needed ? 'Preferred: ' + fmtDate(ir.date_needed) : 'No preferred date') + '</div></div>'
                + '<div class="dash-sr-notes">' + escHtml(notes||'No details provided.') + '</div>'
                + '</div>';
        }).join('');
    }

    async function loadServiceRequests() {
        setVisible(srLoading, true); hideMsg(srMsg);
        try {
            var results = await Promise.all([ apiRequest('/api/service-requests'), apiRequest('/api/inspection-requests') ]);
            servicesLoaded = true;
            renderServiceRequests(Array.isArray(results[0]) ? results[0] : []);
            renderInspectionRequests(Array.isArray(results[1]) ? results[1] : []);
        } catch(err) {
            showMsg(srMsg, 'error', err.message || 'Could not load service requests.');
        } finally {
            setVisible(srLoading, false);
        }
    }

})();
</script>
@endpush
