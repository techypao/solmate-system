<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Contact Us &mdash; SolMate</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        :root { font-family: Arial, sans-serif; line-height: 1.5; color: #1e2937; }
        body { margin: 0; background: #ffffff; }
        a { text-decoration: none; color: inherit; }

        /* HEADER (shared with welcome) */
        .gst-header { position: sticky; top: 0; z-index: 100; background: #f8f4ec; box-shadow: 0 1px 4px rgba(0,0,0,0.06); }
        .gst-header-inner { max-width: 1200px; margin: 0 auto; padding: 0 28px; height: 68px; display: flex; align-items: center; justify-content: space-between; }
        .gst-brand { display: inline-flex; align-items: baseline; text-decoration: none; }
        .gst-brand-sol { font-size: 22px; font-weight: 700; color: #102a43; letter-spacing: -0.3px; }
        .gst-brand-mate { font-size: 22px; font-weight: 700; color: #d4a017; letter-spacing: -0.3px; }
        .gst-nav-links { display: flex; align-items: center; gap: 32px; }
        .gst-nav-link { font-size: 14px; font-weight: 500; color: #4b5563; text-decoration: none; padding: 4px 0; border-bottom: 2px solid transparent; transition: color .15s, border-color .15s; }
        .gst-nav-link:hover { color: #102a43; border-bottom-color: #d4a017; text-decoration: none; }
        .gst-nav-link--active { color: #102a43; border-bottom: 2px solid #d4a017; }
        .gst-header-actions { display: flex; align-items: center; gap: 12px; }
        .gst-btn-login { padding: 8px 20px; font-size: 14px; font-weight: 500; color: #102a43; background: transparent; border: 1.5px solid #d9e2ec; border-radius: 8px; text-decoration: none; display: inline-flex; align-items: center; transition: border-color .15s, background .15s; }
        .gst-btn-login:hover { border-color: #102a43; background: rgba(16,42,67,0.04); text-decoration: none; }
        .gst-btn-register { padding: 8px 20px; font-size: 14px; font-weight: 600; color: #ffffff; background: #102a43; border: 1.5px solid #102a43; border-radius: 8px; text-decoration: none; display: inline-flex; align-items: center; transition: background .15s; }
        .gst-btn-register:hover { background: #0d2237; text-decoration: none; }

        /* CONTACT PAGE HERO */
        .ctc-hero { background: linear-gradient(135deg, #f0f7ff 0%, #f8f4ec 100%); padding: 72px 32px 68px; text-align: center; border-bottom: 1px solid #e2e8f0; }
        .ctc-hero-eyebrow { display: inline-block; font-size: 12px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: #d4a017; margin-bottom: 14px; }
        .ctc-hero-title { font-size: 46px; font-weight: 700; color: #0f172a; line-height: 1.1; margin: 0 0 16px; letter-spacing: -0.5px; }
        .ctc-hero-title span { color: #d4a017; }
        .ctc-hero-sub { font-size: 17px; color: #475569; line-height: 1.75; max-width: 540px; margin: 0 auto; }

        /* CONTACT INFO SECTION */
        .ctc-info { background: #f8fafc; padding: 72px 32px; }
        .ctc-info-inner { max-width: 1100px; margin: 0 auto; }
        .ctc-info-heading { text-align: center; margin-bottom: 52px; }
        .ctc-info-eyebrow { display: inline-block; font-size: 12px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: #d4a017; margin-bottom: 10px; }
        .ctc-info-title { font-size: 30px; font-weight: 700; color: #0f172a; margin: 0 0 10px; line-height: 1.2; }
        .ctc-info-sub { font-size: 15.5px; color: #64748b; margin: 0; max-width: 460px; margin: 0 auto; line-height: 1.7; }
        .ctc-info-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; }
        .ctc-info-card { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 32px 28px; box-shadow: 0 2px 12px rgba(15,23,42,0.05); display: flex; flex-direction: column; align-items: flex-start; gap: 16px; transition: box-shadow .2s, transform .2s; }
        .ctc-info-card:hover { box-shadow: 0 8px 28px rgba(15,23,42,0.10); transform: translateY(-2px); }
        .ctc-info-icon-wrap { width: 52px; height: 52px; background: #eff6ff; border-radius: 14px; display: flex; align-items: center; justify-content: center; color: #102a43; flex-shrink: 0; }
        .ctc-info-label { font-size: 11.5px; font-weight: 700; letter-spacing: 0.07em; text-transform: uppercase; color: #d4a017; margin: 0 0 4px; }
        .ctc-info-value { font-size: 16px; font-weight: 600; color: #0f172a; margin: 0 0 3px; line-height: 1.45; }
        .ctc-info-note { font-size: 13px; color: #64748b; margin: 0; line-height: 1.55; }

        /* FORM + MAP ROW */
        .ctc-main { background: #ffffff; padding: 80px 32px; }
        .ctc-main-inner { max-width: 1100px; margin: 0 auto; display: grid; grid-template-columns: 1fr 420px; gap: 56px; align-items: start; }

        /* FORM */
        .ctc-form-card { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 18px; padding: 44px 40px; box-shadow: 0 4px 20px rgba(15,23,42,0.07); }
        .ctc-form-title { font-size: 24px; font-weight: 700; color: #0f172a; margin: 0 0 6px; }
        .ctc-form-sub { font-size: 14.5px; color: #64748b; margin: 0 0 32px; line-height: 1.6; }
        .ctc-form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; margin-bottom: 18px; }
        .ctc-form-group { display: flex; flex-direction: column; gap: 6px; margin-bottom: 18px; }
        .ctc-form-group--half { margin-bottom: 0; }
        .ctc-form-label { font-size: 13px; font-weight: 600; color: #374151; }
        .ctc-form-label span { color: #ef4444; margin-left: 2px; }
        .ctc-form-input, .ctc-form-select, .ctc-form-textarea { width: 100%; padding: 11px 14px; font-size: 14px; color: #0f172a; background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 10px; outline: none; font-family: inherit; transition: border-color .15s, box-shadow .15s; -webkit-appearance: none; appearance: none; }
        .ctc-form-input:focus, .ctc-form-select:focus, .ctc-form-textarea:focus { border-color: #102a43; box-shadow: 0 0 0 3px rgba(16,42,67,0.08); background: #ffffff; }
        .ctc-form-input::placeholder, .ctc-form-textarea::placeholder { color: #94a3b8; }
        .ctc-form-textarea { resize: vertical; min-height: 130px; line-height: 1.6; }
        .ctc-form-select-wrap { position: relative; }
        .ctc-form-select-wrap::after { content: ""; position: absolute; right: 14px; top: 50%; transform: translateY(-50%); width: 0; height: 0; border-left: 5px solid transparent; border-right: 5px solid transparent; border-top: 6px solid #64748b; pointer-events: none; }
        .ctc-form-btn { width: 100%; padding: 14px 24px; font-size: 15px; font-weight: 700; color: #ffffff; background: #102a43; border: none; border-radius: 10px; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; margin-top: 8px; transition: background .15s, transform .1s; font-family: inherit; }
        .ctc-form-btn:hover { background: #0d2237; transform: translateY(-1px); }
        .ctc-form-note { font-size: 12.5px; color: #94a3b8; text-align: center; margin-top: 14px; line-height: 1.5; }

        /* SIDE INFO PANEL */
        .ctc-side { display: flex; flex-direction: column; gap: 24px; }
        .ctc-side-map { background: #f0f7ff; border: 1px solid #d9e2ec; border-radius: 16px; overflow: hidden; aspect-ratio: 4/3; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 14px; color: #102a43; }
        .ctc-side-map-icon { width: 56px; height: 56px; background: #eff6ff; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 2px solid #d9e2ec; }
        .ctc-side-map-label { font-size: 15px; font-weight: 700; color: #102a43; margin: 0; }
        .ctc-side-map-sub { font-size: 13px; color: #64748b; margin: 0; text-align: center; padding: 0 20px; line-height: 1.6; }
        .ctc-side-hours { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 28px; box-shadow: 0 2px 10px rgba(15,23,42,0.04); }
        .ctc-side-hours-title { font-size: 15px; font-weight: 700; color: #0f172a; margin: 0 0 16px; display: flex; align-items: center; gap: 8px; }
        .ctc-side-hours-title svg { color: #d4a017; }
        .ctc-side-hours-row { display: flex; justify-content: space-between; align-items: center; padding: 9px 0; border-bottom: 1px solid #f1f5f9; font-size: 13.5px; }
        .ctc-side-hours-row:last-child { border-bottom: none; }
        .ctc-side-hours-day { color: #374151; font-weight: 500; }
        .ctc-side-hours-time { color: #102a43; font-weight: 600; }
        .ctc-side-hours-closed { color: #94a3b8; font-weight: 400; }

        /* CTA STRIP */
        .ctc-cta { background: #102a43; padding: 64px 32px; text-align: center; }
        .ctc-cta-inner { max-width: 620px; margin: 0 auto; }
        .ctc-cta-icon { width: 56px; height: 56px; background: rgba(212,160,23,0.15); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; color: #d4a017; }
        .ctc-cta-title { font-size: 30px; font-weight: 700; color: #ffffff; margin: 0 0 12px; line-height: 1.25; }
        .ctc-cta-p { font-size: 15.5px; color: #94a3b8; margin: 0 0 32px; line-height: 1.75; }
        .ctc-cta-actions { display: flex; justify-content: center; gap: 14px; flex-wrap: wrap; }
        .ctc-cta-btn-primary { padding: 13px 28px; font-size: 14.5px; font-weight: 700; color: #102a43; background: #d4a017; border-radius: 10px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; transition: background .15s; }
        .ctc-cta-btn-primary:hover { background: #c49215; color: #102a43; text-decoration: none; }
        .ctc-cta-btn-secondary { padding: 13px 28px; font-size: 14.5px; font-weight: 600; color: #ffffff; background: transparent; border: 2px solid rgba(255,255,255,0.25); border-radius: 10px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; transition: border-color .15s, background .15s; }
        .ctc-cta-btn-secondary:hover { border-color: rgba(255,255,255,0.6); background: rgba(255,255,255,0.06); color: #ffffff; text-decoration: none; }

        /* FOOTER (shared) */
        .solmate-footer { background: #0f1729; color: #cbd5e1; font-family: Arial, sans-serif; }
        .solmate-footer-inner { max-width: 1200px; margin: 0 auto; padding: 56px 32px 0; }
        .solmate-footer-upper { display: grid; grid-template-columns: 2fr 1fr 1fr 0.6fr; gap: 48px; padding-bottom: 48px; }
        .solmate-footer-brand-sol { font-size: 28px; font-weight: 700; color: #7dd3fc; letter-spacing: -0.3px; }
        .solmate-footer-brand-mate { font-size: 28px; font-weight: 700; color: #d4a017; letter-spacing: -0.3px; }
        .solmate-footer-brand-link { text-decoration: none; display: inline-flex; align-items: baseline; margin-bottom: 16px; }
        .solmate-footer-brand-link:hover { text-decoration: none; }
        .solmate-footer-desc { font-size: 13.5px; line-height: 1.75; color: #94a3b8; max-width: 300px; margin: 0; }
        .solmate-footer-col-heading { font-size: 13px; font-weight: 700; color: #e2e8f0; letter-spacing: 0.04em; text-transform: uppercase; margin: 0 0 18px; }
        .solmate-footer-links { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 11px; }
        .solmate-footer-links a { font-size: 13.5px; color: #94a3b8; text-decoration: none; transition: color .15s; }
        .solmate-footer-links a:hover { color: #e2e8f0; text-decoration: none; }
        .solmate-footer-socials { display: flex; flex-direction: column; gap: 12px; }
        .solmate-footer-social-btn { display: flex; align-items: center; justify-content: center; width: 38px; height: 38px; border-radius: 50%; background: rgba(255,255,255,0.07); border: 1px solid rgba(255,255,255,0.10); color: #cbd5e1; text-decoration: none; transition: background .15s, color .15s; line-height: 0; }
        .solmate-footer-social-btn:hover { background: rgba(255,255,255,0.14); color: #ffffff; text-decoration: none; }
        .solmate-footer-divider { border: none; border-top: 1px solid rgba(255,255,255,0.07); margin: 0; }
        .solmate-footer-bottom { max-width: 1200px; margin: 0 auto; padding: 20px 32px; display: flex; align-items: center; justify-content: space-between; gap: 24px; flex-wrap: wrap; }
        .solmate-footer-copyright { font-size: 12.5px; color: #64748b; margin: 0; line-height: 1.5; }
        .solmate-footer-contact-items { display: flex; align-items: center; gap: 32px; flex-wrap: wrap; }
        .solmate-footer-contact-item { display: flex; align-items: center; gap: 8px; font-size: 12.5px; color: #94a3b8; }
        .solmate-footer-contact-item svg { flex-shrink: 0; color: #7dd3fc; }

        /* RESPONSIVE */
        @media (max-width: 960px) {
            .ctc-main-inner { grid-template-columns: 1fr; gap: 36px; }
            .ctc-info-grid { grid-template-columns: repeat(2, 1fr); }
            .solmate-footer-upper { grid-template-columns: 1fr 1fr; gap: 36px; }
        }
        @media (max-width: 640px) {
            .ctc-hero { padding: 52px 20px 48px; }
            .ctc-hero-title { font-size: 32px; }
            .ctc-info { padding: 52px 20px; }
            .ctc-info-grid { grid-template-columns: 1fr; }
            .ctc-main { padding: 52px 20px; }
            .ctc-form-card { padding: 28px 22px; }
            .ctc-form-row { grid-template-columns: 1fr; }
            .ctc-cta { padding: 52px 20px; }
            .ctc-cta-title { font-size: 24px; }
            .gst-header-inner { padding: 0 16px; }
            .gst-nav-links { display: none; }
        }
        @media (max-width: 560px) {
            .solmate-footer-upper { grid-template-columns: 1fr; gap: 28px; }
            .solmate-footer-inner { padding: 40px 20px 0; }
            .solmate-footer-bottom { flex-direction: column; align-items: flex-start; padding: 20px; gap: 14px; }
            .solmate-footer-contact-items { gap: 16px; }
        }
    </style>
</head>
<body>

{{-- HEADER --}}
<header class="gst-header" aria-label="Site header">
    <div class="gst-header-inner">
        <a href="{{ route('home') }}" class="gst-brand" aria-label="SolMate home">
            <span class="gst-brand-sol">Sol</span><span class="gst-brand-mate">Mate</span>
        </a>
        <nav class="gst-nav-links" aria-label="Public navigation">
            <a href="{{ route('home') }}#about" class="gst-nav-link">About</a>
            <a href="{{ route('home') }}#testimonials" class="gst-nav-link">Testimonials</a>
            <a href="{{ route('public.testimonies') }}" class="gst-nav-link">All Reviews</a>
            <a href="{{ route('public.contact') }}" class="gst-nav-link gst-nav-link--active">Contact</a>
        </nav>
        <div class="gst-header-actions">
            <a href="{{ route('login') }}" class="gst-btn-login">Log in</a>
            @if (Route::has('register'))
                <a href="{{ route('register') }}" class="gst-btn-register">Register</a>
            @endif
        </div>
    </div>
</header>

{{-- PAGE HERO --}}
<section class="ctc-hero" aria-label="Contact page header">
    <div>
        <span class="ctc-hero-eyebrow">Get in Touch</span>
        <h1 class="ctc-hero-title">Contact <span>Us</span></h1>
        <p class="ctc-hero-sub">Have a question, interest in solar installation, or need support? We'd love to hear from you. Our team is ready to help.</p>
    </div>
</section>

{{-- CONTACT INFO --}}
<section class="ctc-info" aria-label="Contact information">
    <div class="ctc-info-inner">
        <div class="ctc-info-heading">
            <span class="ctc-info-eyebrow">Reach Us</span>
            <h2 class="ctc-info-title">Our Contact Details</h2>
            <p class="ctc-info-sub">Here are the best ways to connect with RDY Solar Installation Inc.</p>
        </div>
        <div class="ctc-info-grid">
            <div class="ctc-info-card">
                <div class="ctc-info-icon-wrap" aria-hidden="true">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                </div>
                <div>
                    <p class="ctc-info-label">Office Address</p>
                    <p class="ctc-info-value">Address, Philippines</p>
                    <p class="ctc-info-note">Visit us at our main office during business hours.</p>
                </div>
            </div>
            <div class="ctc-info-card">
                <div class="ctc-info-icon-wrap" aria-hidden="true">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                </div>
                <div>
                    <p class="ctc-info-label">Email Address</p>
                    <p class="ctc-info-value">rdysolarpanel@gmail.com</p>
                    <p class="ctc-info-note">We typically respond within 24 business hours.</p>
                </div>
            </div>
            <div class="ctc-info-card">
                <div class="ctc-info-icon-wrap" aria-hidden="true">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12a19.79 19.79 0 0 1-3.07-8.68A2 2 0 0 1 3.62 1h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.6a16 16 0 0 0 6 6l.96-.96a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                </div>
                <div>
                    <p class="ctc-info-label">Phone Number</p>
                    <p class="ctc-info-value">+63 9654326865</p>
                    <p class="ctc-info-note">Available during regular business hours for calls.</p>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- FORM + SIDE PANEL --}}
<section class="ctc-main" aria-label="Contact form">
    <div class="ctc-main-inner">

        {{-- FORM --}}
        <div class="ctc-form-card">
            <h2 class="ctc-form-title">Send Us a Message</h2>
            <p class="ctc-form-sub">Fill in the form below and our team will get back to you as soon as possible.</p>

            {{-- Frontend-only form: no backend submission handler exists yet --}}
            <form id="ctc-contact-form" novalidate>
                <div class="ctc-form-row">
                    <div class="ctc-form-group ctc-form-group--half">
                        <label class="ctc-form-label" for="ctc-name">Full Name <span aria-hidden="true">*</span></label>
                        <input class="ctc-form-input" type="text" id="ctc-name" name="name" placeholder="e.g. Juan dela Cruz" autocomplete="name" required>
                    </div>
                    <div class="ctc-form-group ctc-form-group--half">
                        <label class="ctc-form-label" for="ctc-email">Email Address <span aria-hidden="true">*</span></label>
                        <input class="ctc-form-input" type="email" id="ctc-email" name="email" placeholder="you@example.com" autocomplete="email" required>
                    </div>
                </div>
                <div class="ctc-form-row">
                    <div class="ctc-form-group ctc-form-group--half">
                        <label class="ctc-form-label" for="ctc-phone">Phone Number</label>
                        <input class="ctc-form-input" type="tel" id="ctc-phone" name="phone" placeholder="+63 9XX XXX XXXX" autocomplete="tel">
                    </div>
                    <div class="ctc-form-group ctc-form-group--half">
                        <label class="ctc-form-label" for="ctc-subject">Subject <span aria-hidden="true">*</span></label>
                        <div class="ctc-form-select-wrap">
                            <select class="ctc-form-select" id="ctc-subject" name="subject" required>
                                <option value="" disabled selected>Select a subject</option>
                                <option value="solar-installation">Solar Installation Inquiry</option>
                                <option value="quotation">Request a Quotation</option>
                                <option value="maintenance">System Maintenance</option>
                                <option value="site-assessment">Site Assessment</option>
                                <option value="support">Technical Support</option>
                                <option value="general">General Inquiry</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="ctc-form-group">
                    <label class="ctc-form-label" for="ctc-message">Message <span aria-hidden="true">*</span></label>
                    <textarea class="ctc-form-textarea" id="ctc-message" name="message" placeholder="Tell us about your solar needs or how we can help you..." rows="5" required></textarea>
                </div>
                <button type="submit" class="ctc-form-btn">
                    Send Message
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                </button>
                <p class="ctc-form-note">We respect your privacy. Your information will only be used to respond to your inquiry.</p>
            </form>
        </div>

        {{-- SIDE PANEL --}}
        <div class="ctc-side">
            {{-- Map Placeholder --}}
            <div class="ctc-side-map" aria-label="Office location placeholder">
                <div class="ctc-side-map-icon" aria-hidden="true">
                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                </div>
                <p class="ctc-side-map-label">RDY Solar Installation Inc.</p>
                <p class="ctc-side-map-sub">Address, Philippines</p>
            </div>

            {{-- Business Hours --}}
            <div class="ctc-side-hours">
                <p class="ctc-side-hours-title">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    Business Hours
                </p>
                <div class="ctc-side-hours-row">
                    <span class="ctc-side-hours-day">Monday &ndash; Friday</span>
                    <span class="ctc-side-hours-time">8:00 AM &ndash; 5:00 PM</span>
                </div>
                <div class="ctc-side-hours-row">
                    <span class="ctc-side-hours-day">Saturday</span>
                    <span class="ctc-side-hours-time">9:00 AM &ndash; 3:00 PM</span>
                </div>
                <div class="ctc-side-hours-row">
                    <span class="ctc-side-hours-day">Sunday</span>
                    <span class="ctc-side-hours-closed">Closed</span>
                </div>
            </div>
        </div>

    </div>
</section>

{{-- CTA STRIP --}}
<section class="ctc-cta" aria-label="Get started CTA">
    <div class="ctc-cta-inner">
        <div class="ctc-cta-icon" aria-hidden="true">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
        </div>
        <h2 class="ctc-cta-title">We&rsquo;d Love to Hear From You</h2>
        <p class="ctc-cta-p">Our team is ready to assist you every step of the way &mdash; from initial inquiry and site assessment to full installation and after-service support.</p>
        <div class="ctc-cta-actions">
            <a href="{{ route('login') }}" class="ctc-cta-btn-primary">Log In to Your Account</a>
            @if (Route::has('register'))
                <a href="{{ route('register') }}" class="ctc-cta-btn-secondary">Create a Free Account</a>
            @endif
        </div>
    </div>
</section>

{{-- FOOTER --}}
<footer class="solmate-footer" aria-label="Site footer">
    <div class="solmate-footer-inner">
        <div class="solmate-footer-upper">
            <div>
                <a href="{{ route('home') }}" class="solmate-footer-brand-link" aria-label="SolMate home">
                    <span class="solmate-footer-brand-sol">Sol</span><span class="solmate-footer-brand-mate">Mate</span>
                </a>
                <p class="solmate-footer-desc">SolMate is a smart solar panel installation management system designed to streamline planning, monitoring, and deployment. We help installers, homeowners, and businesses transition to clean energy with efficiency and confidence.</p>
            </div>
            <div>
                <p class="solmate-footer-col-heading">Quick Links</p>
                <ul class="solmate-footer-links">
                    <li><a href="{{ route('home') }}">Home</a></li>
                    <li><a href="{{ route('home') }}#about">About Us</a></li>
                    <li><a href="{{ route('home') }}#testimonials">Testimonials</a></li>
                    <li><a href="{{ route('public.testimonies') }}">All Reviews</a></li>
                    <li><a href="{{ route('public.contact') }}">Contact Us</a></li>
                    <li><a href="{{ route('login') }}">Log In</a></li>
                </ul>
            </div>
            <div>
                <p class="solmate-footer-col-heading">Services</p>
                <ul class="solmate-footer-links">
                    <li><a href="#">Solar Installation</a></li>
                    <li><a href="#">System Maintenance</a></li>
                    <li><a href="#">Site Assessment</a></li>
                    <li><a href="#">ROI &amp; Quotation Estimation</a></li>
                    <li><a href="#">Consultation</a></li>
                </ul>
            </div>
            <div>
                <p class="solmate-footer-col-heading">Socials</p>
                <div class="solmate-footer-socials">
                    <a href="#" class="solmate-footer-social-btn" aria-label="Facebook" target="_blank" rel="noopener noreferrer"><svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg></a>
                    <a href="#" class="solmate-footer-social-btn" aria-label="Instagram" target="_blank" rel="noopener noreferrer"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg></a>
                    <a href="#" class="solmate-footer-social-btn" aria-label="X (Twitter)" target="_blank" rel="noopener noreferrer"><svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg></a>
                    <a href="#" class="solmate-footer-social-btn" aria-label="TikTok" target="_blank" rel="noopener noreferrer"><svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-2.88 2.5 2.89 2.89 0 0 1-2.89-2.89 2.89 2.89 0 0 1 2.89-2.89c.28 0 .54.04.79.1V9.01a6.33 6.33 0 0 0-.79-.05 6.34 6.34 0 0 0-6.34 6.34 6.34 6.34 0 0 0 6.34 6.34 6.34 6.34 0 0 0 6.33-6.34V8.69a8.18 8.18 0 0 0 4.78 1.52V6.76a4.85 4.85 0 0 1-1.01-.07z"/></svg></a>
                </div>
            </div>
        </div>
    </div>
    <hr class="solmate-footer-divider">
    <div class="solmate-footer-bottom">
        <p class="solmate-footer-copyright">&copy; {{ date('Y') }} RDY Solar Installation Inc.<br>All Rights Reserved.</p>
        <div class="solmate-footer-contact-items">
            <div class="solmate-footer-contact-item"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg><span>Address, Philippines</span></div>
            <div class="solmate-footer-contact-item"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg><span>rdysolarpanel@gmail.com</span></div>
            <div class="solmate-footer-contact-item"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12a19.79 19.79 0 0 1-3.07-8.68A2 2 0 0 1 3.62 1h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.6a16 16 0 0 0 6 6l.96-.96a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg><span>+63 9654326865</span></div>
        </div>
    </div>
</footer>

<script>
(function () {
    "use strict";
    var form = document.getElementById("ctc-contact-form");
    if (!form) return;
    form.addEventListener("submit", function (e) {
        e.preventDefault();
        var name = form.querySelector("#ctc-name").value.trim();
        var email = form.querySelector("#ctc-email").value.trim();
        var subject = form.querySelector("#ctc-subject").value;
        var message = form.querySelector("#ctc-message").value.trim();
        if (!name || !email || !subject || !message) {
            alert("Please fill in all required fields.");
            return;
        }
        var btn = form.querySelector(".ctc-form-btn");
        btn.disabled = true;
        btn.textContent = "Message Sent!";
        btn.style.background = "#16a34a";
        setTimeout(function () {
            btn.disabled = false;
            btn.innerHTML = "Send Message <svg width=\"16\" height=\"16\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2.5\" stroke-linecap=\"round\" stroke-linejoin=\"round\" aria-hidden=\"true\"><line x1=\"22\" y1=\"2\" x2=\"11\" y2=\"13\"/><polygon points=\"22 2 15 22 11 13 2 9 22 2\"/></svg>";
            btn.style.background = "";
            form.reset();
        }, 3000);
    });
})();
</script>
</body>
</html>
