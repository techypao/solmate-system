@extends('layouts.app', ['title' => 'Download Mobile App'])

@section('content')
<style>
    .appdl-page {
        display: grid;
        gap: 26px;
    }

    .appdl-hero {
        position: relative;
        overflow: hidden;
        padding: 34px 36px;
        border-radius: 22px;
        border: 1px solid #dce8ef;
        background:
            radial-gradient(circle at 15% 0%, rgba(244, 208, 0, 0.2), transparent 45%),
            linear-gradient(135deg, #123A5A 0%, #123A5A 52%, #1f4d76 100%);
        color: #ffffff;
    }

    .appdl-hero::after {
        content: '';
        position: absolute;
        right: -55px;
        bottom: -70px;
        width: 220px;
        height: 220px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.08);
        pointer-events: none;
    }

    .appdl-eyebrow {
        margin: 0 0 8px;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 1.4px;
        text-transform: uppercase;
        color: #F4D000;
    }

    .appdl-title {
        margin: 0 0 10px;
        font-size: 32px;
        line-height: 1.15;
        font-weight: 800;
        letter-spacing: -0.3px;
    }

    .appdl-copy {
        margin: 0;
        max-width: 660px;
        font-size: 15px;
        line-height: 1.7;
        color: rgba(255, 255, 255, 0.86);
    }

    .appdl-grid {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 280px;
        gap: 20px;
        align-items: start;
    }

    .appdl-card,
    .appdl-panel {
        background: #ffffff;
        border: 1px solid #DDE7EE;
        border-radius: 18px;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.04);
    }

    .appdl-card {
        padding: 24px;
    }

    .appdl-card h2 {
        margin: 0 0 10px;
        font-size: 20px;
        font-weight: 800;
        color: #123A5A;
    }

    .appdl-card p {
        margin: 0;
        color: #5E7288;
        font-size: 14px;
        line-height: 1.7;
    }

    .appdl-list {
        margin: 18px 0 0;
        padding: 0;
        list-style: none;
        display: grid;
        gap: 10px;
    }

    .appdl-list li {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        color: #334155;
        font-size: 13px;
        line-height: 1.55;
    }

    .appdl-dot {
        width: 8px;
        height: 8px;
        border-radius: 999px;
        margin-top: 6px;
        flex-shrink: 0;
        background: #F4D000;
    }

    .appdl-panel {
        padding: 20px;
    }

    .appdl-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 6px 11px;
        border-radius: 999px;
        background: #FFF7CC;
        color: #8A6A00;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 0.8px;
        text-transform: uppercase;
    }

    .appdl-panel h3 {
        margin: 12px 0 8px;
        font-size: 17px;
        font-weight: 800;
        color: #123A5A;
    }

    .appdl-panel p {
        margin: 0;
        color: #5E7288;
        font-size: 13px;
        line-height: 1.65;
    }

    .appdl-btn {
        width: 100%;
        margin-top: 16px;
        min-height: 44px;
        border: 1.5px dashed #CBDDE8;
        border-radius: 12px;
        background: #F8FAFC;
        color: #94A3B8;
        font-size: 13px;
        font-weight: 700;
        cursor: not-allowed;
    }

    @media (max-width: 900px) {
        .appdl-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .appdl-hero {
            padding: 28px 22px;
        }

        .appdl-title {
            font-size: 26px;
        }
    }
</style>

<div class="appdl-page">
    <section class="appdl-hero">
        <p class="appdl-eyebrow">Customer Dashboard</p>
        <h1 class="appdl-title">Download the Solmate mobile app</h1>
        <p class="appdl-copy">This page is now ready for your upcoming mobile distribution flow. Once APK publishing is enabled, customers will be able to download and install the latest Android build here.</p>
    </section>

    <section class="appdl-grid" aria-label="Mobile app download information">
        <article class="appdl-card">
            <h2>APK Download Coming Soon</h2>
            <p>The direct APK link and release details are not yet live. This section is prepared so we can plug in versioned downloads as soon as they are available.</p>
            <ul class="appdl-list">
                <li><span class="appdl-dot" aria-hidden="true"></span>Reserved area for latest APK version and file size</li>
                <li><span class="appdl-dot" aria-hidden="true"></span>Reserved area for release notes and update date</li>
                <li><span class="appdl-dot" aria-hidden="true"></span>Reserved area for install and permissions guide</li>
            </ul>
        </article>

        <aside class="appdl-panel" aria-label="Download status">
            <span class="appdl-badge">Planned Feature</span>
            <h3>Distribution Not Enabled</h3>
            <p>When enabled, this panel can show the active release channel and quick install reminders for your customers.</p>
            <button class="appdl-btn" type="button" disabled>APK Download Unavailable</button>
        </aside>
    </section>
</div>
@endsection
