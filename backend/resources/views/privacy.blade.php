<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Privacy Policy - SolMate by RDY</title>
    <style>
        html { scroll-behavior: smooth; }
        *, *::before, *::after { box-sizing: border-box; }
        :root { font-family: Arial, sans-serif; line-height: 1.5; color: #0F2F4A; }
        body { margin: 0; background: linear-gradient(180deg, #ffffff 0%, #F8FAFC 100%); }
        a { text-decoration: none; color: inherit; }

        .gst-header { position: sticky; top: 0; z-index: 100; background: linear-gradient(180deg, #ffffff 0%, #F8FAFC 100%); border-bottom: 1px solid rgba(32,167,201,0.12); box-shadow: 0 10px 24px rgba(18,58,90,0.06); }
        .gst-header-inner { max-width: 1200px; margin: 0 auto; padding: 0 28px; height: 68px; display: flex; align-items: center; justify-content: space-between; }
        .gst-brand { display: inline-flex; align-items: center; text-decoration: none; line-height: 0; }
        .gst-logo { display: block; width: auto; max-width: 100%; height: auto; }
        .gst-logo--header { height: 42px; }
        .gst-nav-links { display: flex; align-items: center; gap: 32px; }
        .gst-nav-link,
        .nav-link { font-size: 14px; font-weight: 500; color: #5E7288; text-decoration: none; padding: 4px 0; border-bottom: 2px solid transparent; transition: color .15s, border-color .15s; }
        .gst-nav-link:hover,
        .nav-link:hover { color: #123A5A; border-bottom-color: #20A7C9; text-decoration: none; }
        .gst-header-actions { display: flex; align-items: center; gap: 12px; }
        .gst-btn-login { padding: 8px 20px; font-size: 14px; font-weight: 500; color: #123A5A; background: transparent; border: 1.5px solid #DDE7EE; border-radius: 8px; text-decoration: none; display: inline-flex; align-items: center; transition: border-color .15s, background .15s; }
        .gst-btn-login:hover { border-color: #20A7C9; background: rgba(125,223,242,0.12); text-decoration: none; }
        .gst-btn-register { padding: 8px 20px; font-size: 14px; font-weight: 600; color: #ffffff; background: #123A5A; border: 1.5px solid #123A5A; border-radius: 8px; text-decoration: none; display: inline-flex; align-items: center; transition: background .15s; }
        .gst-btn-register:hover { background: #0F2F4A; text-decoration: none; }

        .privacy-page { padding: 80px 32px; }
        .privacy-page-inner { max-width: 1080px; margin: 0 auto; }
        .gst-section-heading { text-align: center; margin-bottom: 52px; }
        .gst-section-eyebrow { display: inline-block; font-size: 12px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: #F4D000; margin-bottom: 10px; }
        .gst-section-title { font-size: 34px; font-weight: 700; color: #0F2F4A; margin: 0 0 12px; line-height: 1.2; }
        .gst-section-sub { font-size: 16px; color: #5E7288; max-width: 560px; margin: 0 auto; line-height: 1.7; }
        .privacy-card { overflow: hidden; display: grid; grid-template-columns: minmax(280px, 0.88fr) minmax(0, 1.12fr); background: #ffffff; border: 1px solid #DDE7EE; border-radius: 22px; box-shadow: 0 18px 42px rgba(15,23,42,0.08); }
        .privacy-summary { position: relative; padding: 34px; background: linear-gradient(135deg, #123A5A 0%, #1f4d76 64%, #20A7C9 100%); color: #ffffff; }
        .privacy-summary::after { content: ""; position: absolute; right: -70px; bottom: -82px; width: 210px; height: 210px; border-radius: 50%; background: rgba(244,208,0,0.16); pointer-events: none; }
        .privacy-badge { position: relative; z-index: 1; display: inline-flex; align-items: center; gap: 8px; padding: 7px 12px; border-radius: 999px; background: rgba(255,255,255,0.12); border: 1px solid rgba(255,255,255,0.18); color: #f8d774; font-size: 11px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; }
        .privacy-badge::before { content: ""; width: 8px; height: 8px; border-radius: 50%; background: #F4D000; }
        .privacy-title { position: relative; z-index: 1; margin: 18px 0 12px; font-size: 32px; font-weight: 700; line-height: 1.16; color: #ffffff; }
        .privacy-lead { position: relative; z-index: 1; margin: 0; color: rgba(255,255,255,0.84); font-size: 14.5px; line-height: 1.75; }
        .privacy-contact { position: relative; z-index: 1; margin-top: 28px; padding: 16px 18px; border-radius: 16px; background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.15); }
        .privacy-contact span { display: block; margin-bottom: 5px; color: #f8d774; font-size: 11px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; }
        .privacy-contact a { color: #ffffff; font-size: 14px; font-weight: 700; word-break: break-word; }
        .privacy-content { padding: 34px; }
        .privacy-updated { display: inline-flex; align-items: center; width: fit-content; margin: 0 0 20px; padding: 6px 12px; border-radius: 999px; background: #FFF7CC; color: #92400e; font-size: 12px; font-weight: 700; }
        .privacy-url { display: inline-flex; align-items: center; margin: 2px 0 22px; color: #123A5A; font-size: 14px; font-weight: 700; text-decoration: underline; text-decoration-color: rgba(32,167,201,0.45); text-underline-offset: 4px; word-break: break-word; }
        .privacy-url:hover { color: #20A7C9; text-decoration-color: #20A7C9; }
        .privacy-content h4 { margin: 28px 0 10px; font-size: 16px; color: #123A5A; }
        .privacy-content h4:first-of-type { margin-top: 0; }
        .privacy-content p { margin: 0 0 16px; color: #5E7288; line-height: 1.75; }
        .privacy-content ul { margin: 0 0 18px; padding-left: 0; list-style: none; color: #5E7288; line-height: 1.75; }
        .privacy-content li { position: relative; margin-bottom: 8px; padding-left: 20px; }
        .privacy-content li::before { content: ""; position: absolute; left: 0; top: 0.72em; width: 7px; height: 7px; border-radius: 50%; background: #20A7C9; }

        @media (max-width: 1000px) {
            .privacy-card { grid-template-columns: 1fr; }
        }
        @media (max-width: 720px) {
            .gst-header-inner { padding: 0 16px; }
            .gst-nav-links { display: none; }
            .privacy-page { padding: 56px 20px; }
            .gst-section-title { font-size: 26px; }
        }
        @media (max-width: 560px) {
            .privacy-summary, .privacy-content { padding: 28px 22px; }
            .privacy-title { font-size: 26px; }
        }
    </style>
</head>
<body>
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
            <a href="{{ route('public.contact') }}" class="gst-nav-link">Contact</a>
            <a href="{{ route('home') }}#download-app" class="gst-nav-link">Download App</a>
            <a href="{{ url('/privacy-policy') }}" class="nav-link">Privacy Policy</a>
        </nav>
        <div class="gst-header-actions">
            <a href="{{ route('login') }}" class="gst-btn-login">Log in</a>
            @if (Route::has('register'))
                <a href="{{ route('register') }}" class="gst-btn-register">Register</a>
            @endif
        </div>
    </div>
</header>

<main class="privacy-page">
    <div class="privacy-page-inner">
        <div class="gst-section-heading">
            <span class="gst-section-eyebrow">Privacy</span>
            <h1 class="gst-section-title">Privacy Policy</h1>
            <p class="gst-section-sub">A simple overview of how SolMate handles account, usage, and app information.</p>
        </div>

        <section class="privacy-card" aria-label="Privacy Policy">
            <div class="privacy-summary">
                <span class="privacy-badge">SolMate by RDY</span>
                <h2 class="privacy-title">Your data stays part of a cleaner, clearer solar experience.</h2>
                <p class="privacy-lead">SolMate values your privacy and explains here how information is collected, used, and protected when you use our mobile and web application.</p>

                <div class="privacy-contact">
                    <span>Contact Us</span>
                    <a href="mailto:solmate.innovit@gmail.com">solmate.innovit@gmail.com</a>
                </div>
            </div>

            <div class="privacy-content">
                <p class="privacy-updated">Last updated: May 2026</p>
                <a href="https://solmatebyrdy.com/privacy-policy" class="privacy-url">https://solmatebyrdy.com/privacy-policy</a>

                <p>SolMate (&quot;we&quot;, &quot;our&quot;, or &quot;us&quot;) values your privacy. This Privacy Policy explains how we collect, use, and protect your information when you use our mobile and web application.</p>

                <h4>1. Information We Collect</h4>
                <ul>
                    <li>Personal information such as name and email address</li>
                    <li>Account login credentials</li>
                    <li>Usage data and app interactions</li>
                </ul>

                <h4>2. How We Use Your Information</h4>
                <ul>
                    <li>To provide and manage our services</li>
                    <li>To authenticate users and maintain account security</li>
                    <li>To improve user experience</li>
                </ul>

                <h4>3. Data Protection</h4>
                <p>We take appropriate measures to protect your personal data.</p>

                <h4>4. Third-Party Services</h4>
                <p>We may use services like Firebase for notifications and analytics.</p>

                <h4>5. Changes to This Policy</h4>
                <p>We may update this Privacy Policy anytime and will reflect changes here.</p>

                <h4>6. Contact Us</h4>
                <p>Email: solmate.innovit@gmail.com</p>
            </div>
        </section>
    </div>
</main>
</body>
</html>
