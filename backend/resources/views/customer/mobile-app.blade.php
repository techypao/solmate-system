@extends('layouts.app', ['title' => 'Download Mobile App'])

@section('content')
<style>
    .appdl-page {
        display: grid;
        gap: 22px;
        max-width: 1040px;
        margin: 0 auto;
    }

    .appdl-hero {
        position: relative;
        overflow: hidden;
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 24px;
        align-items: center;
        padding: 32px 34px;
        border: 1px solid #DDE7EE;
        border-radius: 22px;
        background:
            radial-gradient(circle at 94% 8%, rgba(244,208,0,0.2), transparent 30%),
            linear-gradient(135deg, #F8FAFC 0%, #EAF9FD 58%, #fff8e7 100%);
        box-shadow: 0 14px 34px rgba(15,23,42,0.06);
    }

    .appdl-hero::after {
        content: '';
        position: absolute;
        right: -58px;
        bottom: -70px;
        width: 190px;
        height: 190px;
        border-radius: 999px;
        background: rgba(18,58,90,0.08);
        pointer-events: none;
    }

    .appdl-hero > * {
        position: relative;
        z-index: 1;
    }

    .appdl-eyebrow {
        margin: 0 0 9px;
        color: #F4D000;
        font-size: 12px;
        font-weight: 800;
        letter-spacing: 0.12em;
        text-transform: uppercase;
    }

    .appdl-title {
        margin: 0 0 10px;
        color: #123A5A;
        font-size: 34px;
        line-height: 1.12;
        font-weight: 800;
        letter-spacing: -0.03em;
    }

    .appdl-copy {
        margin: 0;
        max-width: 650px;
        color: #5E7288;
        font-size: 15px;
        line-height: 1.75;
    }

    .appdl-hero-mark {
        width: 86px;
        height: 86px;
        border-radius: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #F4D000;
        background: #123A5A;
        box-shadow: 0 14px 26px rgba(18,58,90,0.22);
    }

    .appdl-methods {
        display: grid;
        grid-template-columns: minmax(0, 0.95fr) minmax(0, 1.05fr);
        gap: 20px;
        align-items: stretch;
    }

    .appdl-card {
        position: relative;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        padding: 24px;
        border: 1px solid #DDE7EE;
        border-radius: 20px;
        background: #ffffff;
        box-shadow: 0 12px 30px rgba(15,23,42,0.06);
    }

    .appdl-card-dark {
        background:
            radial-gradient(circle at 100% 100%, rgba(255,255,255,0.1), transparent 34%),
            linear-gradient(135deg, #123A5A 0%, #164766 100%);
        color: #ffffff;
        border-color: rgba(18,58,90,0.12);
    }

    .appdl-badge {
        width: fit-content;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 6px 11px;
        border-radius: 999px;
        background: #FFF7CC;
        color: #92400e;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .appdl-card-dark .appdl-badge {
        background: rgba(255,255,255,0.12);
        border: 1px solid rgba(255,255,255,0.18);
        color: #f8d774;
    }

    .appdl-card-title {
        margin: 16px 0 8px;
        color: #123A5A;
        font-size: 24px;
        font-weight: 800;
        line-height: 1.18;
    }

    .appdl-card-dark .appdl-card-title {
        color: #ffffff;
    }

    .appdl-card-copy {
        margin: 0;
        color: #5E7288;
        font-size: 14px;
        line-height: 1.75;
    }

    .appdl-card-dark .appdl-card-copy {
        color: rgba(255,255,255,0.84);
    }

    .appdl-qr-frame {
        width: min(192px, 100%);
        margin: 24px auto 0;
        padding: 12px;
        border-radius: 20px;
        background: #ffffff;
        border: 1px solid rgba(221,231,238,0.95);
        box-shadow: 0 14px 26px rgba(15,23,42,0.18);
    }

    .appdl-qr-frame img {
        display: block;
        width: 100%;
        height: auto;
        border-radius: 12px;
    }

    .appdl-note {
        margin: 20px 0 0;
        padding: 14px 16px;
        border-radius: 14px;
        border: 1px dashed #DDE7EE;
        background: #F8FAFC;
        color: #5E7288;
        font-size: 13px;
        line-height: 1.65;
    }

    .appdl-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 20px;
    }

    .appdl-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 46px;
        padding: 12px 22px;
        border-radius: 10px;
        background: #123A5A;
        color: #ffffff;
        font-size: 14px;
        font-weight: 800;
        text-decoration: none;
        box-shadow: 0 8px 20px rgba(18,58,90,0.18);
        transition: background .15s, transform .1s;
    }

    .appdl-btn:hover {
        background: #0F2F4A;
        color: #ffffff;
        text-decoration: none;
        transform: translateY(-1px);
    }

    .appdl-steps {
        display: grid;
        gap: 12px;
        margin-top: 22px;
    }

    .appdl-step {
        display: flex;
        gap: 12px;
        align-items: flex-start;
        padding: 14px 15px;
        border-radius: 14px;
        background: #F8FAFC;
        border: 1px solid #DDE7EE;
    }

    .appdl-step-number {
        width: 24px;
        height: 24px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        background: #F4D000;
        color: #123A5A;
        font-size: 12px;
        font-weight: 800;
    }

    .appdl-step p {
        margin: 0;
        color: #334155;
        font-size: 13px;
        line-height: 1.6;
    }

    @media (max-width: 900px) {
        .appdl-hero,
        .appdl-methods {
            grid-template-columns: 1fr;
        }

        .appdl-hero-mark {
            display: none;
        }
    }

    @media (max-width: 640px) {
        .appdl-hero,
        .appdl-card {
            padding: 22px;
            border-radius: 18px;
        }

        .appdl-title {
            font-size: 28px;
        }

        .appdl-card-title {
            font-size: 21px;
        }

        .appdl-actions,
        .appdl-btn {
            width: 100%;
        }
    }
</style>

<div class="appdl-page">
    <section class="appdl-hero" aria-label="Download mobile app">
        <div>
            <p class="appdl-eyebrow">Download the App</p>
            <h1 class="appdl-title">Download Our Mobile App</h1>
            <p class="appdl-copy">Install the SolMate Android app using one of the methods below. Use the QR code when viewing on desktop, or download the APK directly from your phone.</p>
        </div>
        <div class="appdl-hero-mark" aria-hidden="true">
            <svg width="38" height="38" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="7" y="2" width="10" height="20" rx="2"/>
                <path d="M11 18h2"/>
            </svg>
        </div>
    </section>

    <section class="appdl-methods" aria-label="Mobile app download methods">
        <article class="appdl-card appdl-card-dark">
            <span class="appdl-badge">Method 1</span>
            <h2 class="appdl-card-title">Scan the QR Code</h2>
            <p class="appdl-card-copy">Scan this QR code using your phone to download the app.</p>
            <div class="appdl-qr-frame">
                <img src="{{ asset('images/app-qr.png') }}" alt="QR code to download the SolMate mobile app APK">
            </div>
        </article>

        <article class="appdl-card">
            <span class="appdl-badge">Method 2</span>
            <h2 class="appdl-card-title">Direct Download</h2>
            <p class="appdl-card-copy">If you are already on your Android phone, tap the button below to open the APK download file.</p>
            <div class="appdl-actions">
                <a href="https://drive.google.com/file/d/1GJZ8vnPeRArP8Z-NIpB74woOYsEq3qA6/view?usp=sharing" class="appdl-btn" target="_blank" rel="noopener">Download APK</a>
            </div>
            <div class="appdl-note">After downloading, open the APK file on your phone and follow the Android install prompts.</div>
            <div class="appdl-steps" aria-label="Installation reminders">
                <div class="appdl-step">
                    <span class="appdl-step-number">1</span>
                    <p>Download the APK from Google Drive.</p>
                </div>
                <div class="appdl-step">
                    <span class="appdl-step-number">2</span>
                    <p>Open the file from your browser downloads or file manager.</p>
                </div>
                <div class="appdl-step">
                    <span class="appdl-step-number">3</span>
                    <p>Allow installation when Android asks, then finish installing SolMate.</p>
                </div>
            </div>
        </article>
    </section>
</div>
@endsection
