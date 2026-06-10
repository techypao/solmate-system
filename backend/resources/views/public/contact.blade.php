<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Contact Us &mdash; SolMate</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        :root { font-family: Arial, sans-serif; line-height: 1.5; color: #0F2F4A; }
        body { margin: 0; background: #ffffff; }
        a { text-decoration: none; color: inherit; }

        /* HEADER (shared with welcome) */
        .gst-header { position: sticky; top: 0; z-index: 100; background: linear-gradient(180deg, #ffffff 0%, #F8FAFC 100%); border-bottom: 1px solid rgba(32,167,201,0.12); box-shadow: 0 10px 24px rgba(18,58,90,0.06); }
        .gst-header-inner { max-width: 1200px; margin: 0 auto; padding: 0 28px; height: 68px; display: flex; align-items: center; justify-content: space-between; }
        .gst-brand { display: inline-flex; align-items: center; text-decoration: none; line-height: 0; }
        .gst-logo { display: block; width: auto; max-width: 100%; height: auto; }
        .gst-logo--header { height: 42px; }
        .gst-logo--footer { height: 52px; }
        .gst-nav-links { display: flex; align-items: center; gap: 32px; }
        .gst-nav-link { font-size: 14px; font-weight: 500; color: #5E7288; text-decoration: none; padding: 4px 0; border-bottom: 2px solid transparent; transition: color .15s, border-color .15s; }
        .gst-nav-link:hover { color: #123A5A; border-bottom-color: #20A7C9; text-decoration: none; }
        .gst-nav-link--active { color: #123A5A; border-bottom: 2px solid #F4D000; }
        .gst-header-actions { display: flex; align-items: center; gap: 12px; }
        .gst-btn-login { padding: 8px 20px; font-size: 14px; font-weight: 500; color: #123A5A; background: transparent; border: 1.5px solid #DDE7EE; border-radius: 8px; text-decoration: none; display: inline-flex; align-items: center; transition: border-color .15s, background .15s; }
        .gst-btn-login:hover { border-color: #20A7C9; background: rgba(125,223,242,0.12); text-decoration: none; }
        .gst-btn-register { padding: 8px 20px; font-size: 14px; font-weight: 600; color: #ffffff; background: #123A5A; border: 1.5px solid #123A5A; border-radius: 8px; text-decoration: none; display: inline-flex; align-items: center; transition: background .15s; }
        .gst-btn-register:hover { background: #0F2F4A; text-decoration: none; }

        /* CONTACT PAGE HERO */
        .ctc-hero { background: linear-gradient(135deg, #F8FAFC 0%, #F8FAFC 100%); padding: 72px 32px 68px; text-align: center; border-bottom: 1px solid #DDE7EE; }
        .ctc-hero-eyebrow { display: inline-block; font-size: 12px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: #F4D000; margin-bottom: 14px; }
        .ctc-hero-title { font-size: 46px; font-weight: 700; color: #0F2F4A; line-height: 1.1; margin: 0 0 16px; letter-spacing: -0.5px; }
        .ctc-hero-title span { color: #F4D000; }
        .ctc-hero-sub { font-size: 17px; color: #5E7288; line-height: 1.75; max-width: 540px; margin: 0 auto; }

        /* CONTACT INFO SECTION */
        .ctc-info { background: #f8fafc; padding: 72px 32px; }
        .ctc-info-inner { max-width: 1100px; margin: 0 auto; }
        .ctc-info-heading { text-align: center; margin-bottom: 52px; }
        .ctc-info-eyebrow { display: inline-block; font-size: 12px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: #F4D000; margin-bottom: 10px; }
        .ctc-info-title { font-size: 30px; font-weight: 700; color: #0F2F4A; margin: 0 0 10px; line-height: 1.2; }
        .ctc-info-sub { font-size: 15.5px; color: #5E7288; margin: 0; max-width: 460px; margin: 0 auto; line-height: 1.7; }
        .ctc-info-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; }
        .ctc-info-card { background: #ffffff; border: 1px solid #DDE7EE; border-radius: 16px; padding: 32px 28px; box-shadow: 0 2px 12px rgba(15,23,42,0.05); display: flex; flex-direction: column; align-items: flex-start; gap: 16px; transition: box-shadow .2s, transform .2s; }
        .ctc-info-card:hover { box-shadow: 0 8px 28px rgba(15,23,42,0.10); transform: translateY(-2px); }
        .ctc-info-icon-wrap { width: 52px; height: 52px; background: #EAF9FD; border-radius: 14px; display: flex; align-items: center; justify-content: center; color: #123A5A; flex-shrink: 0; }
        .ctc-info-label { font-size: 11.5px; font-weight: 700; letter-spacing: 0.07em; text-transform: uppercase; color: #F4D000; margin: 0 0 4px; }
        .ctc-info-value { font-size: 16px; font-weight: 600; color: #0F2F4A; margin: 0 0 3px; line-height: 1.45; }
        .ctc-info-note { font-size: 13px; color: #5E7288; margin: 0; line-height: 1.55; }

        /* FORM + MAP ROW */
        .ctc-main { background: #ffffff; padding: 80px 32px; }
        .ctc-main-inner { max-width: 1100px; margin: 0 auto; display: grid; grid-template-columns: 1fr 420px; gap: 56px; align-items: start; }

        /* FORM */
        .ctc-form-card { background: #ffffff; border: 1px solid #DDE7EE; border-radius: 18px; padding: 44px 40px; box-shadow: 0 4px 20px rgba(15,23,42,0.07); }
        .ctc-form-title { font-size: 24px; font-weight: 700; color: #0F2F4A; margin: 0 0 6px; }
        .ctc-form-sub { font-size: 14.5px; color: #5E7288; margin: 0 0 32px; line-height: 1.6; }
        .ctc-form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; margin-bottom: 18px; }
        .ctc-form-group { display: flex; flex-direction: column; gap: 6px; margin-bottom: 18px; }
        .ctc-form-group--half { margin-bottom: 0; }
        .ctc-form-label { font-size: 13px; font-weight: 600; color: #0F2F4A; }
        .ctc-form-label span { color: #ef4444; margin-left: 2px; }
        .ctc-form-input, .ctc-form-select, .ctc-form-textarea { width: 100%; padding: 11px 14px; font-size: 14px; color: #0F2F4A; background: #f8fafc; border: 1.5px solid #DDE7EE; border-radius: 10px; outline: none; font-family: inherit; transition: border-color .15s, box-shadow .15s; -webkit-appearance: none; appearance: none; }
        .ctc-form-input:focus, .ctc-form-select:focus, .ctc-form-textarea:focus { border-color: #20A7C9; box-shadow: 0 0 0 3px rgba(32,167,201,0.14); background: #ffffff; }
        .ctc-form-input::placeholder, .ctc-form-textarea::placeholder { color: #7F92A3; }
        .ctc-form-textarea { resize: vertical; min-height: 130px; line-height: 1.6; }
        .ctc-form-select-wrap { position: relative; }
        .ctc-form-select-wrap::after { content: ""; position: absolute; right: 14px; top: 50%; transform: translateY(-50%); width: 0; height: 0; border-left: 5px solid transparent; border-right: 5px solid transparent; border-top: 6px solid #5E7288; pointer-events: none; }
        .ctc-form-btn { width: 100%; padding: 14px 24px; font-size: 15px; font-weight: 800; color: #0F2F4A; background: linear-gradient(135deg, #F4D000 0%, #E6C200 100%); border: none; border-radius: 10px; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; margin-top: 8px; transition: background .15s, transform .1s; font-family: inherit; }
        .ctc-form-btn:hover { background: linear-gradient(135deg, #E6C200 0%, #F4D000 100%); transform: translateY(-1px); }
        .ctc-form-note { font-size: 12.5px; color: #7F92A3; text-align: center; margin-top: 14px; line-height: 1.5; }

        /* SIDE INFO PANEL */
        .ctc-side { display: flex; flex-direction: column; gap: 24px; }
        .ctc-side-map { background: #ffffff; border: 1px solid #DDE7EE; border-radius: 16px; overflow: hidden; box-shadow: 0 8px 24px rgba(15,23,42,0.06); }
        .ctc-side-map-frame { aspect-ratio: 4/3; background: linear-gradient(135deg, #e8f4ff 0%, #F8FAFC 55%, #F8FAFC 100%); }
        .ctc-side-map-frame iframe { width: 100%; height: 100%; border: 0; display: block; }
        .ctc-side-map-placeholder { width: 100%; height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 14px; color: #123A5A; padding: 24px; text-align: center; }
        .ctc-side-map-icon { width: 56px; height: 56px; background: #EAF9FD; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 2px solid #DDE7EE; }
        .ctc-side-map-body { padding: 22px 24px 24px; border-top: 1px solid #DDE7EE; background: #F8FAFC; display: flex; flex-direction: column; gap: 10px; }
        .ctc-side-map-kicker { font-size: 11px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: #F4D000; margin: 0; }
        .ctc-side-map-label { font-size: 17px; font-weight: 700; color: #123A5A; margin: 0; }
        .ctc-side-map-sub { font-size: 13px; color: #5E7288; margin: 0; line-height: 1.6; }
        .ctc-side-map-btn { width: fit-content; padding: 11px 16px; font-size: 13.5px; font-weight: 700; color: #ffffff; background: #123A5A; border-radius: 10px; display: inline-flex; align-items: center; gap: 8px; transition: background .15s, transform .1s; }
        .ctc-side-map-btn:hover { background: #0F2F4A; color: #ffffff; text-decoration: none; transform: translateY(-1px); }
        .ctc-side-hours { background: #ffffff; border: 1px solid #DDE7EE; border-radius: 16px; padding: 28px; box-shadow: 0 2px 10px rgba(15,23,42,0.04); }
        .ctc-side-hours-title { font-size: 15px; font-weight: 700; color: #0F2F4A; margin: 0 0 16px; display: flex; align-items: center; gap: 8px; }
        .ctc-side-hours-title svg { color: #F4D000; }
        .ctc-side-hours-row { display: flex; justify-content: space-between; align-items: center; padding: 9px 0; border-bottom: 1px solid #f1f5f9; font-size: 13.5px; }
        .ctc-side-hours-row:last-child { border-bottom: none; }
        .ctc-side-hours-day { color: #374151; font-weight: 500; }
        .ctc-side-hours-time { color: #123A5A; font-weight: 600; }
        .ctc-side-hours-closed { color: #7F92A3; font-weight: 400; }

        /* CTA STRIP */
        .ctc-cta { background: #123A5A; padding: 64px 32px; text-align: center; }
        .ctc-cta-inner { max-width: 620px; margin: 0 auto; }
        .ctc-cta-icon { width: 56px; height: 56px; background: rgba(212,160,23,0.15); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; color: #F4D000; }
        .ctc-cta-title { font-size: 30px; font-weight: 700; color: #ffffff; margin: 0 0 12px; line-height: 1.25; }
        .ctc-cta-p { font-size: 15.5px; color: #7F92A3; margin: 0 0 32px; line-height: 1.75; }
        .ctc-cta-actions { display: flex; justify-content: center; gap: 14px; flex-wrap: wrap; }
        .ctc-cta-btn-primary { padding: 13px 28px; font-size: 14.5px; font-weight: 700; color: #123A5A; background: #F4D000; border-radius: 10px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; transition: background .15s; }
        .ctc-cta-btn-primary:hover { background: #E6C200; color: #123A5A; text-decoration: none; }
        .ctc-cta-btn-secondary { padding: 13px 28px; font-size: 14.5px; font-weight: 600; color: #ffffff; background: transparent; border: 2px solid rgba(255,255,255,0.25); border-radius: 10px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; transition: border-color .15s, background .15s; }
        .ctc-cta-btn-secondary:hover { border-color: rgba(255,255,255,0.6); background: rgba(255,255,255,0.06); color: #ffffff; text-decoration: none; }

        /* FOOTER (shared) */
        .solmate-footer { background: linear-gradient(180deg, #3A7EA6 0%, #2A6B92 42%, #1C5476 100%); color: #DDE7EE; font-family: Arial, sans-serif; border-top: 1px solid rgba(125,223,242,0.28); }
        .solmate-footer-inner { max-width: 1200px; margin: 0 auto; padding: 56px 32px 0; }
        .solmate-footer-upper { display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 48px; padding-bottom: 48px; }
        .solmate-footer-brand-link { text-decoration: none; display: inline-flex; align-items: center; margin-bottom: 16px; line-height: 0; }
        .solmate-footer-brand-link:hover { text-decoration: none; }
        .solmate-footer-desc { font-size: 13.5px; line-height: 1.75; color: rgba(255,255,255,0.84); max-width: 300px; margin: 0; }
        .solmate-footer-col-heading { font-size: 13px; font-weight: 700; color: #DDE7EE; letter-spacing: 0.04em; text-transform: uppercase; margin: 0 0 18px; }
        .solmate-footer-links { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 11px; }
        .solmate-footer-links a { font-size: 13.5px; color: rgba(255,255,255,0.88); text-decoration: none; transition: color .15s; }
        .solmate-footer-links a:hover { color: #7DDFF2; text-decoration: none; }
        .solmate-footer-divider { border: none; border-top: 1px solid rgba(255,255,255,0.16); margin: 0; }
        .solmate-footer-bottom { max-width: 1200px; margin: 0 auto; padding: 20px 32px; display: flex; align-items: center; justify-content: space-between; gap: 24px; flex-wrap: wrap; }
        .solmate-footer-copyright { font-size: 12.5px; color: rgba(234,249,253,0.8); margin: 0; line-height: 1.5; }
        .solmate-footer-contact-items { display: flex; align-items: center; gap: 32px; flex-wrap: wrap; }
        .solmate-footer-contact-item { display: flex; align-items: center; gap: 8px; font-size: 12.5px; color: rgba(234,249,253,0.86); }
        .solmate-footer-contact-item svg { flex-shrink: 0; color: #7DDFF2; }

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
@php
    $businessLocationName = 'RDY Solar Installation Inc.';
    $businessLocationDirectionsUrl = 'https://share.google/sUZupKfigerTD2owb';
    $businessLocationEmbedUrl = 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3861.081599521354!2d121.0967439749922!3d14.594425977227985!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3397b9ff305f3d2f%3A0xd4f69df735c4d8c0!2sRDY%20Solar%20Panel%20Installation!5e0!3m2!1sen!2sph!4v1777648847259!5m2!1sen!2sph';
@endphp

{{-- HEADER --}}
<header class="gst-header" aria-label="Site header">
    <div class="gst-header-inner">
        <a href="{{ route('home') }}" class="gst-brand" aria-label="RDY home">
            <img src="{{ asset('images/rdy-logo-transparent.png') }}" alt="RDY logo" class="gst-logo gst-logo--header">
        </a>
        <nav class="gst-nav-links" aria-label="Public navigation">
            <a href="{{ route('home') }}#rdy" class="gst-nav-link">RDY</a>
            <a href="{{ route('home') }}#services" class="gst-nav-link">Services</a>
            <a href="{{ route('home') }}#news" class="gst-nav-link">News</a>
            <a href="{{ route('public.testimonies') }}" class="gst-nav-link">All Reviews</a>
            <a href="{{ route('home') }}#about" class="gst-nav-link">About</a>
            <a href="{{ route('public.contact') }}" class="gst-nav-link gst-nav-link--active">Contact</a>
            <a href="{{ route('home') }}#download-app" class="gst-nav-link">Download App</a>
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
                    <p class="ctc-info-label">Business Location</p>
                    <p class="ctc-info-value">{{ $businessLocationName }}</p>
                    <p class="ctc-info-note">Use Google Maps to open the latest location pin and get directions to our office.</p>
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
                <p id="ctc-success-msg" style="display:none; color:#16a34a; font-size:14px; margin-top:12px; font-weight:600;"></p>
                <p id="ctc-error-msg"   style="display:none; color:#dc2626; font-size:14px; margin-top:12px;"></p>
            </form>
        </div>

        {{-- SIDE PANEL --}}
        <div class="ctc-side">
            <div class="ctc-side-map" aria-label="Business location map">
                <div class="ctc-side-map-frame">
                    @if ($businessLocationEmbedUrl)
                        <iframe
                            src="{{ $businessLocationEmbedUrl }}"
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"
                            allowfullscreen
                            title="RDY Solar Installation Inc. location map"
                        ></iframe>
                    @else
                        <div class="ctc-side-map-placeholder">
                            <div class="ctc-side-map-icon" aria-hidden="true">
                                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                            </div>
                            <p class="ctc-side-map-label">{{ $businessLocationName }}</p>
                            <p class="ctc-side-map-sub">Location map will be available soon.</p>
                        </div>
                    @endif
                </div>
                <div class="ctc-side-map-body">
                    <p class="ctc-side-map-kicker">Business Location Map</p>
                    <p class="ctc-side-map-label">{{ $businessLocationName }}</p>
                    <p class="ctc-side-map-sub">
                        @if ($businessLocationEmbedUrl)
                            View the live map here or open Google Maps in a new tab for directions.
                        @else
                            Open Google Maps in a new tab for directions while the embedded map is being prepared.
                        @endif
                    </p>
                    <a href="{{ $businessLocationDirectionsUrl }}" class="ctc-side-map-btn" target="_blank" rel="noopener noreferrer">
                        Get Directions
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="M12 5l7 7-7 7"/></svg>
                    </a>
                </div>
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
                <a href="{{ route('landing') }}" class="solmate-footer-brand-link" aria-label="RDY home">
                    <img src="{{ asset('images/rdy-logo-transparent.png') }}" alt="RDY logo" class="gst-logo gst-logo--footer">
                </a>
                <p class="solmate-footer-desc">SolMate is a smart solar panel installation management system designed to streamline planning, monitoring, and deployment. We help installers, homeowners, and businesses transition to clean energy with efficiency and confidence.</p>
            </div>
            <div>
                <p class="solmate-footer-col-heading">Quick Links</p>
                <ul class="solmate-footer-links">
                    <li><a href="{{ route('landing') }}">Home</a></li>
                    <li><a href="{{ route('landing') }}#about">About Us</a></li>
                    <li><a href="{{ route('landing') }}#services">Services</a></li>
                    <li><a href="{{ route('landing') }}#testimonials">Testimonials</a></li>
                    <li><a href="{{ route('public.testimonies') }}">All Reviews</a></li>
                    <li><a href="{{ route('public.contact') }}">Contact Us</a></li>
                    <li><a href="{{ route('login') }}">Log In</a></li>
                </ul>
            </div>
            <div>
                <p class="solmate-footer-col-heading">Services</p>
                <ul class="solmate-footer-links">
                    <li><a href="{{ route('landing') }}#services">Solar Installation</a></li>
                    <li><a href="{{ route('landing') }}#services">System Maintenance</a></li>
                    <li><a href="{{ route('landing') }}#services">Site Assessment</a></li>
                    <li><a href="{{ route('landing') }}#services">ROI &amp; Quotation Estimation</a></li>
                    <li><a href="{{ route('public.contact') }}">Consultation</a></li>
                </ul>
            </div>
        </div>
    </div>
    <hr class="solmate-footer-divider">
    <div class="solmate-footer-bottom">
        <p class="solmate-footer-copyright">&copy; {{ date('Y') }} RDY Solar Installation Inc.<br>All Rights Reserved.</p>
        <div class="solmate-footer-contact-items">
            <div class="solmate-footer-contact-item"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg><span>3852 Gumamela, Pasig, 1611 Metro Manila</span></div>
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

    var successMsg = document.getElementById("ctc-success-msg");
    var errorMsg   = document.getElementById("ctc-error-msg");

    form.addEventListener("submit", function (e) {
        e.preventDefault();

        if (successMsg) successMsg.style.display = "none";
        if (errorMsg)   errorMsg.style.display   = "none";

        var name    = form.querySelector("#ctc-name").value.trim();
        var email   = form.querySelector("#ctc-email").value.trim();
        var phone   = form.querySelector("#ctc-phone").value.trim();
        var subject = form.querySelector("#ctc-subject").value;
        var message = form.querySelector("#ctc-message").value.trim();

        if (!name || !email || !subject || !message) {
            if (errorMsg) {
                errorMsg.textContent = "Please fill in all required fields.";
                errorMsg.style.display = "block";
            }
            return;
        }

        var btn = form.querySelector(".ctc-form-btn");
        btn.disabled = true;
        btn.textContent = "Sending…";

        fetch("/api/contact-messages", {
            method: "POST",
            credentials: "same-origin",
            headers: {
                "Content-Type": "application/json",
                "Accept": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({
                full_name:    name,
                email:        email,
                phone_number: phone || null,
                subject:      subject,
                message:      message
            })
        })
        .then(function (res) {
            if (!res.ok) {
                return res.json().then(function (data) {
                    throw new Error(
                        (data.errors ? Object.values(data.errors).flat().join(" ") : null) ||
                        data.message ||
                        "Something went wrong. Please try again."
                    );
                });
            }
            return res.json();
        })
        .then(function () {
            form.reset();
            if (successMsg) {
                successMsg.textContent = "Your message has been sent successfully. Our team will get back to you soon.";
                successMsg.style.display = "block";
            }
            btn.disabled = false;
            btn.innerHTML = 'Send Message <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>';
        })
        .catch(function (err) {
            btn.disabled = false;
            btn.innerHTML = 'Send Message <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>';
            if (errorMsg) {
                errorMsg.textContent = err.message || "Something went wrong. Please try again.";
                errorMsg.style.display = "block";
            }
        });
    });
})();
</script>
</body>
</html>
