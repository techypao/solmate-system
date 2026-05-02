@extends('layouts.app', ['title' => 'Quotation'])

@section('content')
<style>
    .qh-hero {
        position: relative;
        overflow: hidden;
        margin-bottom: 28px;
        padding: 38px 40px;
        border-radius: 22px;
        background:
            radial-gradient(circle at top right, rgba(212, 160, 23, 0.18), transparent 32%),
            linear-gradient(135deg, #102a43 0%, #163a5f 58%, #1f4d76 100%);
        color: #fff;
    }
    .qh-hero::after {
        content: '';
        position: absolute;
        inset: auto -60px -70px auto;
        width: 220px;
        height: 220px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.06);
        pointer-events: none;
    }
    .qh-eyebrow {
        margin: 0 0 10px;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 1.2px;
        text-transform: uppercase;
        color: #facc15;
    }
    .qh-title {
        margin: 0 0 12px;
        font-size: 34px;
        font-weight: 800;
        line-height: 1.15;
    }
    .qh-sub {
        max-width: 640px;
        margin: 0;
        font-size: 15px;
        line-height: 1.7;
        color: rgba(255, 255, 255, 0.82);
    }
    .qh-steps {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        margin-top: 22px;
    }
    .qh-step {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 14px;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.14);
        font-size: 12px;
        font-weight: 600;
        color: #f8fafc;
    }
    .qh-step-num {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 22px;
        height: 22px;
        border-radius: 50%;
        background: #d4a017;
        color: #102a43;
        font-size: 11px;
        font-weight: 800;
    }

    .qh-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 22px;
    }
    .qh-card {
        display: flex;
        flex-direction: column;
        min-height: 100%;
        padding: 26px;
        border-radius: 22px;
        background: #fff;
        border: 1px solid #e2e8f0;
        box-shadow: 0 18px 40px rgba(15, 23, 42, 0.08);
    }
    .qh-card-accent {
        width: 54px;
        height: 54px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 16px;
        margin-bottom: 18px;
        background: linear-gradient(135deg, rgba(212, 160, 23, 0.18), rgba(16, 42, 67, 0.14));
        color: #102a43;
    }
    .qh-card-title {
        margin: 0 0 10px;
        font-size: 22px;
        font-weight: 800;
        color: #102a43;
    }
    .qh-card-desc {
        margin: 0 0 18px;
        color: #475569;
        font-size: 14px;
        line-height: 1.7;
    }
    .qh-card-points {
        display: grid;
        gap: 10px;
        margin: 0 0 22px;
        padding: 0;
        list-style: none;
    }
    .qh-card-points li {
        display: flex;
        align-items: center;
        gap: 10px;
        color: #334155;
        font-size: 13px;
        line-height: 1.5;
    }
    .qh-card-points svg {
        flex-shrink: 0;
        color: #d4a017;
    }
    .qh-card-footer {
        margin-top: auto;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
    }
    .qh-card-note {
        font-size: 12px;
        color: #64748b;
    }
    .qh-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-height: 46px;
        padding: 0 18px;
        border-radius: 12px;
        border: none;
        text-decoration: none;
        font-size: 14px;
        font-weight: 700;
        transition: transform .15s ease, box-shadow .15s ease, opacity .15s ease;
    }
    .qh-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.12);
    }
    .qh-btn-primary {
        background: linear-gradient(135deg, #d4a017, #b8880f);
        color: #fff;
    }
    .qh-btn-secondary {
        background: #102a43;
        color: #fff;
    }
    .qh-tip {
        margin-top: 24px;
        padding: 18px 20px;
        border-radius: 18px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        color: #475569;
        font-size: 14px;
        line-height: 1.7;
    }
    .qh-tip strong {
        color: #102a43;
    }

    @media (max-width: 900px) {
        .qh-grid {
            grid-template-columns: 1fr;
        }
    }
    @media (max-width: 768px) {
        .qh-hero {
            padding: 28px 22px;
        }
        .qh-title {
            font-size: 28px;
        }
    }
</style>

<section class="qh-hero">
    <p class="qh-eyebrow">Quotation Center</p>
    <h1 class="qh-title">Choose how you want to manage your quotation</h1>
    <p class="qh-sub">Start with a pre-inspection estimate or review the quotations already prepared for your account, including technician-prepared inspection-based quotations after inspection.</p>
    <div class="qh-steps">
        <span class="qh-step"><span class="qh-step-num">1</span>Estimate your system</span>
        <span class="qh-step"><span class="qh-step-num">2</span>Review your submissions</span>
        <span class="qh-step"><span class="qh-step-num">3</span>Track your next step</span>
    </div>
</section>

<section class="qh-grid" aria-label="Quotation options">
    <article class="qh-card">
        <div class="qh-card-accent" aria-hidden="true">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                <path d="M13 2L3 14h8l-1 8 11-14h-8l1-6z"/>
            </svg>
        </div>
        <h2 class="qh-card-title">Create Pre-Inspection Estimate</h2>
        <p class="qh-card-desc">Estimate your solar system size, projected cost, and ROI based on your monthly electric bill.</p>
        <ul class="qh-card-points">
            <li>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M20 6L9 17l-5-5"/></svg>
                Instant system sizing and estimated package cost
            </li>
            <li>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M20 6L9 17l-5-5"/></svg>
                Monthly and annual savings plus ROI preview
            </li>
            <li>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M20 6L9 17l-5-5"/></svg>
                Keeps the existing pre-inspection estimate flow
            </li>
        </ul>
        <div class="qh-card-footer">
            <span class="qh-card-note">Best if you are starting a new request.</span>
            <a href="{{ route('customer.quotation.create') }}" class="qh-btn qh-btn-primary">Create Quotation</a>
        </div>
    </article>

    <article class="qh-card">
        <div class="qh-card-accent" aria-hidden="true">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                <path d="M14 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/>
                <path d="M14 3v6h6"/>
                <path d="M8 13h8"/>
                <path d="M8 17h6"/>
            </svg>
        </div>
        <h2 class="qh-card-title">View My Quotations</h2>
        <p class="qh-card-desc">View your submitted pre-inspection estimates and technician-prepared inspection-based quotations.</p>
        <ul class="qh-card-points">
            <li>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M20 6L9 17l-5-5"/></svg>
                See both quotation types in one place
            </li>
            <li>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M20 6L9 17l-5-5"/></svg>
                Review the latest quotation status and submission date
            </li>
            <li>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M20 6L9 17l-5-5"/></svg>
                Open inspection-based quotations prepared after inspection
            </li>
        </ul>
        <div class="qh-card-footer">
            <span class="qh-card-note">Best if you want to review existing quotations.</span>
            <a href="{{ route('customer.quotation.index') }}" class="qh-btn qh-btn-secondary">View Quotations</a>
        </div>
    </article>
</section>

<div class="qh-tip">
    <strong>Tip:</strong> The pre-inspection estimate is only a guide and may change after the technician's actual inspection.
</div>
@endsection
