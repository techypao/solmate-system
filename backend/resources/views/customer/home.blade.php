@extends('layouts.app', ['title' => 'Home · SolMate'])

@section('content')
<style>
    /* ── Customer Home Page (ch- prefix) ─────────────────────────── */

    /* ─── Hero ─── */
    .ch-hero {
        background: linear-gradient(140deg, #102a43 0%, #1e3a5f 58%, #0f3460 100%);
        border-radius: 20px;
        padding: 56px 52px;
        display: grid;
        grid-template-columns: 1fr 200px;
        gap: 40px;
        align-items: center;
        margin-bottom: 40px;
        position: relative;
        overflow: hidden;
    }
    .ch-hero::before {
        content: '';
        position: absolute;
        top: -60px;
        right: 200px;
        width: 280px;
        height: 280px;
        border-radius: 50%;
        background: rgba(212,160,23,.07);
        pointer-events: none;
    }
    .ch-hero::after {
        content: '';
        position: absolute;
        bottom: -80px;
        right: -40px;
        width: 320px;
        height: 320px;
        border-radius: 50%;
        background: rgba(255,255,255,.03);
        pointer-events: none;
    }
    .ch-hero-content { position: relative; z-index: 1; }
    .ch-hero-eyebrow {
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 1.6px;
        text-transform: uppercase;
        color: #f4c542;
        margin: 0 0 12px;
        opacity: .9;
    }
    .ch-hero-title {
        font-size: 38px;
        font-weight: 900;
        color: #fff;
        margin: 0 0 16px;
        line-height: 1.15;
        letter-spacing: -0.5px;
    }
    .ch-hero-title span { color: #f4c542; }
    .ch-hero-sub {
        font-size: 15px;
        color: rgba(255,255,255,.68);
        max-width: 480px;
        line-height: 1.7;
        margin: 0 0 30px;
    }
    .ch-hero-actions { display: flex; gap: 12px; flex-wrap: wrap; align-items: center; }
    .ch-btn-hero-primary {
        padding: 13px 28px;
        background: linear-gradient(135deg, #d4a017, #b8880f);
        color: #fff;
        font-size: 14px;
        font-weight: 700;
        border: none;
        border-radius: 10px;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: opacity .2s, transform .15s;
        letter-spacing: .2px;
    }
    .ch-btn-hero-primary:hover { opacity: .9; transform: translateY(-1px); color: #fff; text-decoration: none; }
    .ch-btn-hero-ghost {
        padding: 12px 24px;
        background: rgba(255,255,255,.1);
        color: rgba(255,255,255,.9);
        font-size: 14px;
        font-weight: 600;
        border: 1.5px solid rgba(255,255,255,.22);
        border-radius: 10px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: background .2s, border-color .2s;
    }
    .ch-btn-hero-ghost:hover { background: rgba(255,255,255,.16); border-color: rgba(255,255,255,.4); color: #fff; text-decoration: none; }
    .ch-hero-deco {
        position: relative;
        z-index: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 200px;
        height: 180px;
    }
    @media (max-width: 768px) {
        .ch-hero { grid-template-columns: 1fr; padding: 32px 24px; gap: 20px; }
        .ch-hero-deco { display: none; }
        .ch-hero-title { font-size: 28px; }
    }

    /* ─── Section scaffold ─── */
    .ch-section { margin-bottom: 44px; }
    .ch-section-header { margin-bottom: 22px; }
    .ch-section-eyebrow {
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 1.3px;
        text-transform: uppercase;
        color: #d4a017;
        margin: 0 0 6px;
    }
    .ch-section-title {
        font-size: 22px;
        font-weight: 800;
        color: #102a43;
        margin: 0;
        line-height: 1.2;
    }
    .ch-section-sub {
        font-size: 14px;
        color: #64748b;
        margin: 6px 0 0;
        line-height: 1.6;
    }

    /* ─── Quick Action Cards ─── */
    .ch-actions-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 14px;
    }
    @media (max-width: 560px) { .ch-actions-grid { grid-template-columns: repeat(2, 1fr); } }
    .ch-action-card {
        background: #fff;
        border: 1.5px solid #e2e8f0;
        border-radius: 16px;
        padding: 22px 18px 20px;
        text-decoration: none;
        display: flex;
        flex-direction: column;
        gap: 10px;
        transition: border-color .2s, box-shadow .25s, transform .18s;
        position: relative;
        overflow: hidden;
    }
    .ch-action-card::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, #d4a017, #f4c542);
        transform: scaleX(0);
        transform-origin: left;
        transition: transform .25s ease;
    }
    .ch-action-card:hover {
        border-color: #fbbf24;
        box-shadow: 0 8px 28px rgba(212,160,23,.14);
        transform: translateY(-3px);
        text-decoration: none;
    }
    .ch-action-card:hover::after { transform: scaleX(1); }
    .ch-action-icon {
        width: 46px;
        height: 46px;
        border-radius: 12px;
        background: linear-gradient(135deg, #102a43, #1e4068);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .ch-action-title {
        font-size: 14px;
        font-weight: 700;
        color: #102a43;
        margin: 0;
        line-height: 1.3;
    }
    .ch-action-desc {
        font-size: 12px;
        color: #64748b;
        margin: 0;
        line-height: 1.5;
        flex: 1;
    }
    .ch-action-cta {
        display: flex;
        align-items: center;
        gap: 4px;
        font-size: 12px;
        font-weight: 700;
        color: #d4a017;
        margin-top: 4px;
    }

    /* ─── Recent Activity ─── */
    .ch-activity-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }
    @media (max-width: 680px) { .ch-activity-grid { grid-template-columns: 1fr; } }
    .ch-act-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 2px 12px rgba(0,0,0,.04);
    }
    .ch-act-card-header {
        display: flex;
        align-items: center;
        gap: 11px;
        padding: 16px 20px;
        border-bottom: 1px solid #f1f5f9;
    }
    .ch-act-card-icon {
        width: 34px;
        height: 34px;
        border-radius: 9px;
        background: linear-gradient(135deg, #102a43, #1e4068);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .ch-act-card-title {
        font-size: 14px;
        font-weight: 700;
        color: #102a43;
        margin: 0;
    }
    .ch-act-card-sub {
        font-size: 11px;
        color: #94a3b8;
        margin: 1px 0 0;
    }
    .ch-act-card-body { padding: 16px 20px 20px; }
    .ch-act-item {
        display: flex;
        align-items: center;
        gap: 11px;
        padding: 9px 0;
        border-bottom: 1px solid #f8fafc;
    }
    .ch-act-item:last-child { border-bottom: none; padding-bottom: 0; }
    .ch-act-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #e2e8f0;
        flex-shrink: 0;
    }
    .ch-act-dot-gold  { background: #d4a017; }
    .ch-act-dot-green { background: #22c55e; }
    .ch-act-dot-blue  { background: #3b82f6; }
    .ch-act-dot-amber { background: #f59e0b; }
    .ch-act-dot-red   { background: #ef4444; }
    .ch-act-info { flex: 1; min-width: 0; }
    .ch-act-label {
        font-size: 13px;
        font-weight: 600;
        color: #1e293b;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        margin: 0;
    }
    .ch-act-meta { font-size: 11px; color: #94a3b8; margin: 1px 0 0; }
    .ch-act-badge {
        display: inline-flex;
        padding: 2px 8px;
        border-radius: 20px;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .3px;
        white-space: nowrap;
        flex-shrink: 0;
    }
    .ch-act-badge-pending     { background: #fef9c3; color: #a16207; }
    .ch-act-badge-approved,
    .ch-act-badge-completed   { background: #dcfce7; color: #15803d; }
    .ch-act-badge-in_progress { background: #dbeafe; color: #1d4ed8; }
    .ch-act-badge-cancelled   { background: #fee2e2; color: #dc2626; }
    .ch-act-badge-default     { background: #f1f5f9; color: #475569; }
    .ch-act-empty {
        text-align: center;
        padding: 28px 16px;
        color: #94a3b8;
        font-size: 13px;
        display: none;
    }
    .ch-act-empty.show { display: block; }
    .ch-act-loading {
        padding: 24px 16px;
        text-align: center;
        color: #94a3b8;
        font-size: 13px;
    }
    .ch-act-view-link {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        margin-top: 14px;
        font-size: 12px;
        font-weight: 700;
        color: #d4a017;
        text-decoration: none;
    }
    .ch-act-view-link:hover { color: #b8880f; text-decoration: none; }

    /* ─── Help strip ─── */
    .ch-help {
        background: linear-gradient(135deg, #fefce8 0%, #fef9c3 100%);
        border: 1px solid #fde68a;
        border-radius: 16px;
        padding: 32px 36px;
        display: flex;
        align-items: center;
        gap: 28px;
        flex-wrap: wrap;
        margin-bottom: 8px;
    }
    @media (max-width: 640px) { .ch-help { padding: 24px 20px; } }
    .ch-help-content { flex: 1; min-width: 220px; }
    .ch-help-title {
        font-size: 17px;
        font-weight: 800;
        color: #92400e;
        margin: 0 0 6px;
    }
    .ch-help-sub {
        font-size: 14px;
        color: #78350f;
        margin: 0;
        line-height: 1.55;
    }
    .ch-help-actions { display: flex; gap: 10px; flex-wrap: wrap; }
    .ch-help-btn {
        padding: 11px 22px;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 700;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 7px;
        transition: all .15s;
        white-space: nowrap;
    }
    .ch-help-btn-gold { background: #d4a017; color: #fff; border: none; }
    .ch-help-btn-gold:hover { background: #b8880f; color: #fff; text-decoration: none; }
    .ch-help-btn-outline {
        background: transparent;
        color: #92400e;
        border: 1.5px solid #d4a017;
    }
    .ch-help-btn-outline:hover { background: rgba(212,160,23,.1); color: #78350f; text-decoration: none; }
</style>

{{-- ═══ HERO ═══ --}}
<section class="ch-hero" aria-label="Welcome banner">
    <div class="ch-hero-content">
        <p class="ch-hero-eyebrow">Your Solar Journey</p>
        <h1 class="ch-hero-title">
            Welcome back,<br>
            <span>{{ auth()->user()->name }}</span>
        </h1>
        <p class="ch-hero-sub">Manage your solar installation from quotation to completion — track requests, view quotes, and stay updated on every step.</p>
        <div class="ch-hero-actions">
            <a href="{{ route('customer.quotation') }}" class="ch-btn-hero-primary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                Get a Quotation
            </a>
            <a href="{{ route('customer.tracking') }}" class="ch-btn-hero-ghost">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                Track Services
            </a>
        </div>
    </div>

    {{-- Decorative solar illustration --}}
    <div class="ch-hero-deco" aria-hidden="true">
        <svg width="180" height="180" viewBox="0 0 180 180" fill="none" xmlns="http://www.w3.org/2000/svg">
            <!-- Outer glow ring -->
            <circle cx="90" cy="90" r="80" stroke="rgba(212,160,23,.12)" stroke-width="1.5"/>
            <circle cx="90" cy="90" r="64" stroke="rgba(212,160,23,.18)" stroke-width="1.5"/>
            <!-- Sun core -->
            <circle cx="90" cy="90" r="28" fill="rgba(212,160,23,.18)" stroke="#d4a017" stroke-width="1.5"/>
            <circle cx="90" cy="90" r="16" fill="#d4a017" opacity=".85"/>
            <!-- Ray lines -->
            <line x1="90" y1="52" x2="90" y2="44" stroke="#f4c542" stroke-width="2.5" stroke-linecap="round"/>
            <line x1="90" y1="136" x2="90" y2="128" stroke="#f4c542" stroke-width="2.5" stroke-linecap="round"/>
            <line x1="52" y1="90" x2="44" y2="90" stroke="#f4c542" stroke-width="2.5" stroke-linecap="round"/>
            <line x1="136" y1="90" x2="128" y2="90" stroke="#f4c542" stroke-width="2.5" stroke-linecap="round"/>
            <line x1="62.5" y1="62.5" x2="57" y2="57" stroke="#f4c542" stroke-width="2" stroke-linecap="round"/>
            <line x1="123" y1="123" x2="117.5" y2="117.5" stroke="#f4c542" stroke-width="2" stroke-linecap="round"/>
            <line x1="117.5" y1="62.5" x2="123" y2="57" stroke="#f4c542" stroke-width="2" stroke-linecap="round"/>
            <line x1="57" y1="123" x2="62.5" y2="117.5" stroke="#f4c542" stroke-width="2" stroke-linecap="round"/>
            <!-- Small solar panel grid (decorative) -->
            <rect x="134" y="124" width="32" height="22" rx="3" fill="none" stroke="rgba(255,255,255,.2)" stroke-width="1.2"/>
            <line x1="144" y1="124" x2="144" y2="146" stroke="rgba(255,255,255,.15)" stroke-width="1"/>
            <line x1="154" y1="124" x2="154" y2="146" stroke="rgba(255,255,255,.15)" stroke-width="1"/>
            <line x1="134" y1="132" x2="166" y2="132" stroke="rgba(255,255,255,.15)" stroke-width="1"/>
            <line x1="134" y1="139" x2="166" y2="139" stroke="rgba(255,255,255,.15)" stroke-width="1"/>
            <!-- Orbit dots -->
            <circle cx="90" cy="26" r="3" fill="#d4a017" opacity=".7"/>
            <circle cx="154" cy="90" r="3" fill="#d4a017" opacity=".5"/>
            <circle cx="26" cy="90" r="3" fill="#d4a017" opacity=".4"/>
        </svg>
    </div>
</section>

{{-- ═══ QUICK ACTIONS ═══ --}}
<section class="ch-section">
    <div class="ch-section-header">
        <p class="ch-section-eyebrow">Get Started</p>
        <h2 class="ch-section-title">Quick Actions</h2>
        <p class="ch-section-sub">Everything you need to manage your solar journey in one place</p>
    </div>

    <div class="ch-actions-grid">

        {{-- 1. Solar Quotation --}}
        <a href="{{ route('customer.quotation') }}" class="ch-action-card">
            <div class="ch-action-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#d4a017" stroke-width="2" aria-hidden="true">
                    <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>
                </svg>
            </div>
            <p class="ch-action-title">Solar Quotation</p>
            <p class="ch-action-desc">Get instant solar system sizing and ROI estimate based on your bill</p>
            <span class="ch-action-cta">
                Open
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </span>
        </a>

        {{-- 2. Site Inspection --}}
        <a href="{{ route('customer.inspection') }}" class="ch-action-card">
            <div class="ch-action-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#d4a017" stroke-width="2" aria-hidden="true">
                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                    <circle cx="12" cy="10" r="3"/>
                </svg>
            </div>
            <p class="ch-action-title">Site Inspection</p>
            <p class="ch-action-desc">Request a technician visit to assess your property for installation</p>
            <span class="ch-action-cta">
                Request
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </span>
        </a>

        {{-- 3. Final Quotation --}}
        <a href="{{ route('customer.tracking') }}" class="ch-action-card">
            <div class="ch-action-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#d4a017" stroke-width="2" aria-hidden="true">
                    <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
                    <polyline points="14 2 14 8 20 8"/>
                    <line x1="16" y1="13" x2="8" y2="13"/>
                    <line x1="16" y1="17" x2="8" y2="17"/>
                    <polyline points="10 9 9 9 8 9"/>
                </svg>
            </div>
            <p class="ch-action-title">Final Quotation</p>
            <p class="ch-action-desc">View your custom solar quotation after site inspection is complete</p>
            <span class="ch-action-cta">
                View
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </span>
        </a>

        {{-- 4. Job Tracking --}}
        <a href="{{ route('customer.tracking') }}" class="ch-action-card">
            <div class="ch-action-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#d4a017" stroke-width="2" aria-hidden="true">
                    <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
                </svg>
            </div>
            <p class="ch-action-title">Job Tracking</p>
            <p class="ch-action-desc">Monitor real-time progress of your service requests and installations</p>
            <span class="ch-action-cta">
                Track
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </span>
        </a>

        {{-- 5. My Dashboard --}}
        <a href="{{ route('dashboard') }}" class="ch-action-card">
            <div class="ch-action-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#d4a017" stroke-width="2" aria-hidden="true">
                    <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/>
                    <circle cx="12" cy="7" r="4"/>
                </svg>
            </div>
            <p class="ch-action-title">My Dashboard</p>
            <p class="ch-action-desc">Manage your profile, review quotation history, and update account details</p>
            <span class="ch-action-cta">
                Open
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </span>
        </a>

        {{-- 6. My Feedback --}}
        <a href="{{ route('customer.testimonies') }}" class="ch-action-card">
            <div class="ch-action-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#d4a017" stroke-width="2" aria-hidden="true">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                </svg>
            </div>
            <p class="ch-action-title">My Feedback</p>
            <p class="ch-action-desc">Share your experience, rate our service quality, and help us improve</p>
            <span class="ch-action-cta">
                Write
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </span>
        </a>

    </div>
</section>

{{-- ═══ RECENT ACTIVITY ═══ --}}
<section class="ch-section">
    <div class="ch-section-header">
        <p class="ch-section-eyebrow">Overview</p>
        <h2 class="ch-section-title">Recent Activity</h2>
        <p class="ch-section-sub">Your latest quotations and inspection requests at a glance</p>
    </div>

    <div class="ch-activity-grid">

        {{-- Recent Quotations --}}
        <div class="ch-act-card">
            <div class="ch-act-card-header">
                <div class="ch-act-card-icon">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#d4a017" stroke-width="2" aria-hidden="true">
                        <line x1="12" y1="1" x2="12" y2="23"/>
                        <path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/>
                    </svg>
                </div>
                <div>
                    <p class="ch-act-card-title">My Quotations</p>
                    <p class="ch-act-card-sub">Latest solar quotation requests</p>
                </div>
            </div>
            <div class="ch-act-card-body">
                <div id="ch-qt-loading" class="ch-act-loading">Loading quotations&hellip;</div>
                <div id="ch-qt-list"></div>
                <div id="ch-qt-empty" class="ch-act-empty">No quotations yet. <a href="{{ route('customer.quotation') }}" style="color:#d4a017;font-weight:700;">Generate one now.</a></div>
                <a href="{{ route('customer.quotation') }}" class="ch-act-view-link">
                    View all quotations
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                </a>
            </div>
        </div>

        {{-- Recent Inspection Requests --}}
        <div class="ch-act-card">
            <div class="ch-act-card-header">
                <div class="ch-act-card-icon">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#d4a017" stroke-width="2" aria-hidden="true">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                        <line x1="16" y1="2" x2="16" y2="6"/>
                        <line x1="8" y1="2" x2="8" y2="6"/>
                        <line x1="3" y1="10" x2="21" y2="10"/>
                    </svg>
                </div>
                <div>
                    <p class="ch-act-card-title">Inspection Requests</p>
                    <p class="ch-act-card-sub">Latest site inspection activity</p>
                </div>
            </div>
            <div class="ch-act-card-body">
                <div id="ch-insp-loading" class="ch-act-loading">Loading requests&hellip;</div>
                <div id="ch-insp-list"></div>
                <div id="ch-insp-empty" class="ch-act-empty">No inspection requests yet. <a href="{{ route('customer.inspection') }}" style="color:#d4a017;font-weight:700;">Schedule one now.</a></div>
                <a href="{{ route('customer.inspection') }}" class="ch-act-view-link">
                    View all requests
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                </a>
            </div>
        </div>

    </div>
</section>

{{-- ═══ HELP STRIP ═══ --}}
<div class="ch-help">
    <div class="ch-help-content">
        <h3 class="ch-help-title">Need help getting started?</h3>
        <p class="ch-help-sub">Our team is ready to assist you. Schedule a site inspection or reach out through our contact page.</p>
    </div>
    <div class="ch-help-actions">
        <a href="{{ route('customer.inspection') }}" class="ch-help-btn ch-help-btn-gold">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            Schedule Inspection
        </a>
        <a href="{{ route('public.contact') }}" class="ch-help-btn ch-help-btn-outline">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
            Contact Support
        </a>
    </div>
</div>

@endsection

@push('scripts')
<script>
(function () {
    'use strict';

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

    async function apiRequest(endpoint) {
        var resp = await fetch(endpoint, {
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });
        if (!resp.ok) throw new Error('Request failed');
        return resp.json();
    }

    function dotClass(status) {
        var s = String(status || '').toLowerCase();
        if (s === 'pending') return 'ch-act-dot-amber';
        if (s === 'approved' || s === 'completed') return 'ch-act-dot-green';
        if (s === 'in_progress' || s === 'in-progress' || s === 'scheduled') return 'ch-act-dot-blue';
        if (s === 'cancelled' || s === 'declined' || s === 'rejected') return 'ch-act-dot-red';
        return 'ch-act-dot-gold';
    }

    function badgeClass(status) {
        var s = String(status || '').toLowerCase();
        var map = {
            pending: 'ch-act-badge-pending',
            approved: 'ch-act-badge-approved',
            completed: 'ch-act-badge-completed',
            in_progress: 'ch-act-badge-in_progress',
            cancelled: 'ch-act-badge-cancelled',
            rejected: 'ch-act-badge-cancelled',
        };
        return map[s] || 'ch-act-badge-default';
    }

    function renderItems(listId, loadId, emptyId, items, labelFn) {
        var loadEl  = document.getElementById(loadId);
        var listEl  = document.getElementById(listId);
        var emptyEl = document.getElementById(emptyId);

        if (loadEl)  loadEl.style.display = 'none';
        if (!items || items.length === 0) {
            if (emptyEl) emptyEl.classList.add('show');
            return;
        }

        var html = items.slice(0, 3).map(function (item) {
            var label  = labelFn(item);
            var meta   = fmtDate(item.created_at);
            var status = String(item.status || 'pending');
            var statusLabel = status.replace(/_/g, ' ').replace(/\b\w/g, function (c) { return c.toUpperCase(); });
            var dc = dotClass(status);
            var bc = badgeClass(status);
            return '<div class="ch-act-item">'
                + '<div class="ch-act-dot ' + escHtml(dc) + '"></div>'
                + '<div class="ch-act-info">'
                +   '<p class="ch-act-label">' + escHtml(label) + '</p>'
                +   '<p class="ch-act-meta">' + escHtml(meta) + '</p>'
                + '</div>'
                + '<span class="ch-act-badge ' + escHtml(bc) + '">' + escHtml(statusLabel) + '</span>'
                + '</div>';
        }).join('');

        if (listEl) listEl.innerHTML = html;
    }

    /* ── Load latest quotations ── */
    (async function loadQuotations() {
        try {
            var resp  = await apiRequest('/api/customer/quotations');
            var items = resp.data || resp;
            if (!Array.isArray(items)) items = [];
            renderItems('ch-qt-list', 'ch-qt-loading', 'ch-qt-empty', items, function (q) {
                var type = q.quotation_type ? (q.quotation_type.charAt(0).toUpperCase() + q.quotation_type.slice(1)) : 'Quotation';
                return type + ' Quotation #' + (q.id || '');
            });
        } catch (e) {
            var el = document.getElementById('ch-qt-loading');
            if (el) { el.textContent = 'Could not load quotations.'; el.style.color = '#94a3b8'; }
        }
    })();

    /* ── Load latest inspection requests ── */
    (async function loadInspections() {
        try {
            var resp  = await apiRequest('/api/customer/inspection-requests');
            var items = resp.data || resp;
            if (!Array.isArray(items)) items = [];
            renderItems('ch-insp-list', 'ch-insp-loading', 'ch-insp-empty', items, function (r) {
                return 'Inspection Request #' + (r.id || '');
            });
        } catch (e) {
            var el = document.getElementById('ch-insp-loading');
            if (el) { el.textContent = 'Could not load requests.'; el.style.color = '#94a3b8'; }
        }
    })();

})();
</script>
@endpush
