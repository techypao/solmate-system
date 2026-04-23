<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SolMate &mdash; Smart Solar Installation Management</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        :root { font-family: Arial, sans-serif; line-height: 1.5; color: #1e2937; }
        body { margin: 0; background: #ffffff; }
        a { text-decoration: none; color: inherit; }

        /* HEADER */
        .gst-header { position: sticky; top: 0; z-index: 100; background: #f8f4ec; box-shadow: 0 1px 4px rgba(0,0,0,0.06); }
        .gst-header-inner { max-width: 1200px; margin: 0 auto; padding: 0 28px; height: 68px; display: flex; align-items: center; justify-content: space-between; }
        .gst-brand { display: inline-flex; align-items: baseline; text-decoration: none; }
        .gst-brand-sol { font-size: 22px; font-weight: 700; color: #102a43; letter-spacing: -0.3px; }
        .gst-brand-mate { font-size: 22px; font-weight: 700; color: #d4a017; letter-spacing: -0.3px; }
        .gst-nav-links { display: flex; align-items: center; gap: 32px; }
        .gst-nav-link { font-size: 14px; font-weight: 500; color: #4b5563; text-decoration: none; padding: 4px 0; border-bottom: 2px solid transparent; transition: color .15s, border-color .15s; }
        .gst-nav-link:hover { color: #102a43; border-bottom-color: #d4a017; text-decoration: none; }
        .gst-header-actions { display: flex; align-items: center; gap: 12px; }
        .gst-btn-login { padding: 8px 20px; font-size: 14px; font-weight: 500; color: #102a43; background: transparent; border: 1.5px solid #d9e2ec; border-radius: 8px; text-decoration: none; display: inline-flex; align-items: center; transition: border-color .15s, background .15s; }
        .gst-btn-login:hover { border-color: #102a43; background: rgba(16,42,67,0.04); text-decoration: none; }
        .gst-btn-register { padding: 8px 20px; font-size: 14px; font-weight: 600; color: #ffffff; background: #102a43; border: 1.5px solid #102a43; border-radius: 8px; text-decoration: none; display: inline-flex; align-items: center; transition: background .15s; }
        .gst-btn-register:hover { background: #0d2237; text-decoration: none; }

        /* HERO */
        .gst-hero { background: linear-gradient(135deg, #f0f7ff 0%, #fafff8 55%, #fff8e7 100%); padding: 96px 32px 100px; }
        .gst-hero-inner { max-width: 1200px; margin: 0 auto; display: grid; grid-template-columns: 1fr 1fr; gap: 64px; align-items: center; }
        .gst-hero-tag { display: inline-flex; align-items: center; gap: 8px; padding: 5px 14px; background: #e0f2fe; border: 1px solid #b3e0f8; border-radius: 999px; font-size: 12.5px; font-weight: 600; color: #0369a1; margin-bottom: 22px; }
        .gst-hero-tag-dot { width: 6px; height: 6px; border-radius: 50%; background: #0ea5e9; display: inline-block; }
        .gst-hero-h1 { font-size: 50px; font-weight: 700; color: #0f172a; line-height: 1.12; margin: 0 0 22px; letter-spacing: -0.6px; }
        .gst-hero-h1 span { color: #d4a017; }
        .gst-hero-p { font-size: 17px; color: #475569; line-height: 1.8; margin: 0 0 38px; max-width: 460px; }
        .gst-hero-actions { display: flex; gap: 14px; align-items: center; flex-wrap: wrap; }
        .gst-cta-primary { padding: 14px 30px; font-size: 15px; font-weight: 700; color: #ffffff; background: #102a43; border: none; border-radius: 10px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; transition: background .15s, transform .1s; }
        .gst-cta-primary:hover { background: #0d2237; transform: translateY(-1px); text-decoration: none; color: #ffffff; }
        .gst-cta-secondary { padding: 14px 30px; font-size: 15px; font-weight: 600; color: #102a43; background: transparent; border: 2px solid #102a43; border-radius: 10px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; transition: background .15s, color .15s; }
        .gst-cta-secondary:hover { background: #102a43; color: #ffffff; text-decoration: none; }
        .gst-cta-text { font-size: 14px; font-weight: 500; color: #64748b; text-decoration: none; transition: color .15s; }
        .gst-cta-text:hover { color: #102a43; }
        .gst-hero-visual { position: relative; }
        .gst-hero-card-main { background: #ffffff; border-radius: 20px; padding: 32px; box-shadow: 0 20px 60px rgba(15,23,42,0.12), 0 4px 12px rgba(15,23,42,0.06); border: 1px solid rgba(0,0,0,0.05); }
        .gst-hero-card-badge { display: inline-flex; align-items: center; gap: 6px; padding: 5px 12px; background: #fef3c7; border-radius: 999px; font-size: 12px; font-weight: 600; color: #92400e; margin-bottom: 18px; }
        .gst-hero-card-title { font-size: 17px; font-weight: 700; color: #0f172a; margin: 0 0 5px; }
        .gst-hero-card-sub { font-size: 13px; color: #64748b; margin: 0 0 22px; }
        .gst-hero-stats { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 22px; }
        .gst-hero-stat { padding: 14px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; }
        .gst-hero-stat-value { font-size: 22px; font-weight: 700; color: #102a43; line-height: 1; margin-bottom: 4px; }
        .gst-hero-stat-label { font-size: 12px; color: #64748b; }
        .gst-hero-progress-label { display: flex; justify-content: space-between; font-size: 12px; color: #64748b; margin-bottom: 7px; }
        .gst-hero-progress-bar { height: 6px; background: #e2e8f0; border-radius: 999px; overflow: hidden; }
        .gst-hero-progress-fill { height: 100%; width: 78%; background: linear-gradient(90deg, #102a43, #0ea5e9); border-radius: 999px; }
        .gst-hero-float-card { position: absolute; bottom: -18px; right: -16px; background: #102a43; color: #ffffff; padding: 14px 18px; border-radius: 14px; box-shadow: 0 8px 24px rgba(16,42,67,0.3); min-width: 130px; }
        .gst-hero-float-value { font-size: 18px; font-weight: 700; line-height: 1; }
        .gst-hero-float-label { font-size: 11px; color: #94a3b8; margin-top: 4px; }

        /* TRUST */
        .gst-trust { background: #f8fafc; padding: 68px 32px; }
        .gst-trust-inner { max-width: 1200px; margin: 0 auto; }
        .gst-trust-grid { display: grid; grid-template-columns: repeat(4,1fr); gap: 24px; }
        .gst-trust-card { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 28px 24px; box-shadow: 0 2px 8px rgba(15,23,42,0.04); transition: box-shadow .2s, transform .2s; }
        .gst-trust-card:hover { box-shadow: 0 8px 24px rgba(15,23,42,0.09); transform: translateY(-2px); }
        .gst-trust-icon { width: 44px; height: 44px; background: #eff6ff; border-radius: 10px; display: flex; align-items: center; justify-content: center; margin-bottom: 16px; color: #102a43; }
        .gst-trust-title { font-size: 15px; font-weight: 700; color: #0f172a; margin: 0 0 8px; }
        .gst-trust-desc { font-size: 13.5px; color: #64748b; line-height: 1.65; margin: 0; }

        /* SECTION HEADING */
        .gst-section-heading { text-align: center; margin-bottom: 52px; }
        .gst-section-eyebrow { display: inline-block; font-size: 12px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: #d4a017; margin-bottom: 10px; }
        .gst-section-title { font-size: 34px; font-weight: 700; color: #0f172a; margin: 0 0 12px; line-height: 1.2; }
        .gst-section-sub { font-size: 16px; color: #64748b; max-width: 520px; margin: 0 auto; line-height: 1.7; }

        /* TESTIMONIES */
        .gst-testimonies { background: #ffffff; padding: 80px 32px; }
        .gst-testimonies-inner { max-width: 1200px; margin: 0 auto; }
        .gst-testimonies-grid { display: grid; grid-template-columns: repeat(3,1fr); gap: 24px; }
        .gst-testimony-card { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 14px; padding: 28px; box-shadow: 0 2px 8px rgba(15,23,42,0.04); display: flex; flex-direction: column; }
        .gst-testimony-quote-icon { width: 32px; height: 32px; background: #e0f2fe; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #0369a1; margin-bottom: 16px; flex-shrink: 0; }
        .gst-testimony-text { font-size: 14.5px; color: #374151; line-height: 1.75; margin: 0 0 20px; flex-grow: 1; }
        .gst-testimony-footer { display: flex; align-items: center; justify-content: space-between; gap: 12px; }
        .gst-testimony-name { font-size: 14px; font-weight: 700; color: #0f172a; }
        .gst-testimony-stars { display: flex; gap: 2px; }
        .gst-testimony-star-full { color: #d4a017; font-size: 14px; line-height: 1; }
        .gst-testimony-star-empty { color: #e2e8f0; font-size: 14px; line-height: 1; }
        .gst-testimonies-state-msg { grid-column: 1 / -1; text-align: center; padding: 56px 20px; color: #94a3b8; font-size: 15px; }
        .gst-testimonies-view-all { text-align: center; margin-top: 36px; }
        .gst-testimonies-view-all a { display: inline-flex; align-items: center; gap: 6px; font-size: 14.5px; font-weight: 600; color: #102a43; border: 2px solid #102a43; padding: 10px 24px; border-radius: 8px; text-decoration: none; transition: background .15s, color .15s; }
        .gst-testimonies-view-all a:hover { background: #102a43; color: #ffffff; }

        /* ABOUT */
        .gst-about { background: #f0f7ff; padding: 80px 32px; }
        .gst-about-inner { max-width: 1200px; margin: 0 auto; display: grid; grid-template-columns: 1fr 1fr; gap: 64px; align-items: center; }
        .gst-about-eyebrow { display: inline-block; font-size: 12px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: #d4a017; margin-bottom: 12px; }
        .gst-about-title { font-size: 36px; font-weight: 700; color: #0f172a; line-height: 1.2; margin: 0 0 18px; }
        .gst-about-p { font-size: 15.5px; color: #475569; line-height: 1.8; margin: 0 0 24px; }
        .gst-about-highlights { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .gst-about-highlight { background: #ffffff; border: 1px solid #d9e2ec; border-radius: 14px; padding: 22px; box-shadow: 0 2px 8px rgba(15,23,42,0.04); }
        .gst-about-highlight-icon { width: 38px; height: 38px; background: #eff6ff; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #102a43; margin-bottom: 12px; }
        .gst-about-highlight-title { font-size: 14px; font-weight: 700; color: #0f172a; margin: 0 0 6px; }
        .gst-about-highlight-desc { font-size: 13px; color: #64748b; line-height: 1.6; margin: 0; }

        /* CTA */
        .gst-cta-section { background: #102a43; padding: 88px 32px; }
        .gst-cta-inner { max-width: 680px; margin: 0 auto; text-align: center; }
        .gst-cta-eyebrow { display: inline-block; font-size: 12px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: #d4a017; margin-bottom: 14px; }
        .gst-cta-title { font-size: 38px; font-weight: 700; color: #ffffff; line-height: 1.2; margin: 0 0 16px; }
        .gst-cta-title span { color: #d4a017; }
        .gst-cta-p { font-size: 16px; color: #94a3b8; line-height: 1.7; margin: 0 0 42px; }
        .gst-cta-buttons { display: flex; justify-content: center; gap: 16px; flex-wrap: wrap; }
        .gst-cta-btn-primary { padding: 15px 36px; font-size: 15px; font-weight: 700; color: #102a43; background: #d4a017; border: none; border-radius: 10px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; transition: background .15s, transform .1s; }
        .gst-cta-btn-primary:hover { background: #c49215; transform: translateY(-1px); color: #102a43; text-decoration: none; }
        .gst-cta-btn-secondary { padding: 15px 36px; font-size: 15px; font-weight: 600; color: #ffffff; background: transparent; border: 2px solid rgba(255,255,255,0.3); border-radius: 10px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; transition: border-color .15s, background .15s; }
        .gst-cta-btn-secondary:hover { border-color: rgba(255,255,255,0.7); background: rgba(255,255,255,0.07); text-decoration: none; color: #ffffff; }

        /* FOOTER */
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
        @media (max-width: 1000px) {
            .gst-trust-grid { grid-template-columns: repeat(2,1fr); }
            .gst-testimonies-grid { grid-template-columns: repeat(2,1fr); }
            .solmate-footer-upper { grid-template-columns: 1fr 1fr; gap: 36px; }
        }
        @media (max-width: 720px) {
            .gst-hero-inner, .gst-about-inner { grid-template-columns: 1fr; gap: 36px; }
            .gst-hero-visual { display: none; }
            .gst-hero-h1 { font-size: 34px; }
            .gst-hero { padding: 60px 20px 64px; }
            .gst-trust, .gst-testimonies, .gst-about, .gst-cta-section { padding: 56px 20px; }
            .gst-trust-grid { grid-template-columns: 1fr; }
            .gst-testimonies-grid { grid-template-columns: 1fr; }
            .gst-about-highlights { grid-template-columns: 1fr; }
            .gst-section-title { font-size: 26px; }
            .gst-cta-title { font-size: 28px; }
            .gst-about-title { font-size: 28px; }
            .gst-header-inner { padding: 0 16px; }
            .gst-nav-links { display: none; }
        }
        @media (max-width: 560px) {
            .solmate-footer-upper { grid-template-columns: 1fr; gap: 28px; }
            .solmate-footer-inner { padding: 40px 20px 0; }
            .solmate-footer-bottom { flex-direction: column; align-items: flex-start; padding: 20px; gap: 14px; }
            .solmate-footer-contact-items { gap: 16px; }
        }
        @media (max-width: 480px) {
            .gst-hero-actions, .gst-cta-buttons { flex-direction: column; align-items: flex-start; }
            .gst-cta-buttons { align-items: center; }
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
            <a href="#about" class="gst-nav-link">About</a>
            <a href="#testimonials" class="gst-nav-link">Testimonials</a>
            <a href="{{ route('public.testimonies') }}" class="gst-nav-link">All Reviews</a>
            <a href="{{ route('public.contact') }}" class="gst-nav-link">Contact</a>
        </nav>
        <div class="gst-header-actions">
            <a href="{{ route('login') }}" class="gst-btn-login">Log in</a>
            @if (Route::has('register'))
                <a href="{{ route('register') }}" class="gst-btn-register">Register</a>
            @endif
        </div>
    </div>
</header>

{{-- HERO --}}
<section class="gst-hero" id="hero" aria-label="Hero">
    <div class="gst-hero-inner">
        <div>
            <div class="gst-hero-tag">
                <span class="gst-hero-tag-dot" aria-hidden="true"></span>
                Smart Solar Management Platform
            </div>
            <h1 class="gst-hero-h1">
                Solar Installation<br>Made <span>Simple</span><br>&amp; Smart
            </h1>
            <p class="gst-hero-p">
                SolMate streamlines your entire solar panel installation journey &mdash; from initial
                quotation and site assessment to scheduling, monitoring, and after-service support.
                Reliable, organized, and built for you.
            </p>
            <div class="gst-hero-actions">
                <a href="{{ route('login') }}" class="gst-cta-primary">
                    Get Started
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                </a>
                @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="gst-cta-secondary">Create Account</a>
                @endif
                <a href="#about" class="gst-cta-text">Learn More &darr;</a>
            </div>
        </div>
        <div class="gst-hero-visual" aria-hidden="true">
            <div class="gst-hero-card-main">
                <div class="gst-hero-card-badge">&#9728;&#65039; Active Installations</div>
                <div class="gst-hero-card-title">Installation Dashboard</div>
                <div class="gst-hero-card-sub">Track your solar project status in real-time</div>
                <div class="gst-hero-stats">
                    <div class="gst-hero-stat"><div class="gst-hero-stat-value">124+</div><div class="gst-hero-stat-label">Installations Done</div></div>
                    <div class="gst-hero-stat"><div class="gst-hero-stat-value">98%</div><div class="gst-hero-stat-label">Satisfaction Rate</div></div>
                    <div class="gst-hero-stat"><div class="gst-hero-stat-value">4.9 &#9733;</div><div class="gst-hero-stat-label">Average Rating</div></div>
                    <div class="gst-hero-stat"><div class="gst-hero-stat-value">3 yrs</div><div class="gst-hero-stat-label">In Operation</div></div>
                </div>
                <div>
                    <div class="gst-hero-progress-label"><span>Projects Completed This Month</span><span>78%</span></div>
                    <div class="gst-hero-progress-bar"><div class="gst-hero-progress-fill"></div></div>
                </div>
            </div>
            <div class="gst-hero-float-card">
                <div class="gst-hero-float-value">&#9889; Live</div>
                <div class="gst-hero-float-label">Service Tracking</div>
            </div>
        </div>
    </div>
</section>

{{-- TRUST HIGHLIGHTS --}}
<section class="gst-trust" aria-label="Why SolMate">
    <div class="gst-trust-inner">
        <div class="gst-trust-grid">
            <div class="gst-trust-card">
                <div class="gst-trust-icon" aria-hidden="true">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                </div>
                <div class="gst-trust-title">Reliable Solar Services</div>
                <p class="gst-trust-desc">Professional installation and maintenance from certified technicians backed by years of hands-on expertise.</p>
            </div>
            <div class="gst-trust-card">
                <div class="gst-trust-icon" aria-hidden="true">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                </div>
                <div class="gst-trust-title">Organized Installation Workflow</div>
                <p class="gst-trust-desc">From quotation to completion, every step is carefully managed and tracked inside one clean platform.</p>
            </div>
            <div class="gst-trust-card">
                <div class="gst-trust-icon" aria-hidden="true">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                </div>
                <div class="gst-trust-title">Easy Tracking &amp; Coordination</div>
                <p class="gst-trust-desc">Monitor your project progress in real time and stay coordinated with your assigned service team.</p>
            </div>
            <div class="gst-trust-card">
                <div class="gst-trust-icon" aria-hidden="true">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </div>
                <div class="gst-trust-title">Customer-Focused Support</div>
                <p class="gst-trust-desc">Dedicated support from initial consultation to post-installation follow-up, every step of the way.</p>
            </div>
        </div>
    </div>
</section>

{{-- TESTIMONIES --}}
<section class="gst-testimonies" id="testimonials" aria-label="Client testimonials">
    <div class="gst-testimonies-inner">
        <div class="gst-section-heading">
            <span class="gst-section-eyebrow">Client Feedback</span>
            <h2 class="gst-section-title">What Our Clients Say</h2>
            <p class="gst-section-sub">Real experiences from homeowners and businesses who trusted SolMate for their solar journey.</p>
        </div>
        <div class="gst-testimonies-grid" id="landing-testimonies-grid">
            <div class="gst-testimonies-state-msg" id="landing-testimonies-loading">Loading testimonials&hellip;</div>
        </div>
        <div class="gst-testimonies-view-all">
            <a href="{{ route('public.testimonies') }}">
                View All Reviews
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </a>
        </div>
    </div>
</section>

{{-- ABOUT US --}}
<section class="gst-about" id="about" aria-label="About RDY Solar Panel Installation">
    <div class="gst-about-inner">
        <div>
            <span class="gst-about-eyebrow">About Us</span>
            <h2 class="gst-about-title">Powering Homes &amp;<br>Businesses Since 2020</h2>
            <p class="gst-about-p">
                RDY Solar Panel Installation was founded at the height of the pandemic in 2020 &mdash;
                a time when many Filipino families were looking for ways to reduce their electricity bills
                and achieve greater energy independence. From humble beginnings, we grew into a trusted
                name in solar installation across the region.
            </p>
            <p class="gst-about-p" style="margin-bottom: 0;">
                Today, we proudly serve hundreds of residential and commercial clients across Metro Manila
                (NCR), Rizal, Bulacan, and Laguna. Our team of certified technicians is dedicated to
                delivering quality workmanship, honest pricing, and after-sales support you can count on.
            </p>
        </div>
        <div class="gst-about-highlights">
            <div class="gst-about-highlight">
                <div class="gst-about-highlight-icon" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                </div>
                <div class="gst-about-highlight-title">Founded in 2020</div>
                <p class="gst-about-highlight-desc">Born during the pandemic with a mission to make solar energy accessible to every Filipino household.</p>
            </div>
            <div class="gst-about-highlight">
                <div class="gst-about-highlight-icon" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                </div>
                <div class="gst-about-highlight-title">Serving 4 Regions</div>
                <p class="gst-about-highlight-desc">Covering NCR, Rizal, Bulacan, and Laguna &mdash; with a growing presence across Luzon.</p>
            </div>
            <div class="gst-about-highlight">
                <div class="gst-about-highlight-icon" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                </div>
                <div class="gst-about-highlight-title">Hundreds of Happy Clients</div>
                <p class="gst-about-highlight-desc">A rapidly growing customer base that trusts RDY for reliable solar solutions and after-sales care.</p>
            </div>
            <div class="gst-about-highlight">
                <div class="gst-about-highlight-icon" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"/><path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/></svg>
                </div>
                <div class="gst-about-highlight-title">Clean Energy Advocates</div>
                <p class="gst-about-highlight-desc">Committed to reducing carbon footprint one rooftop at a time, helping the Philippines go green.</p>
            </div>
        </div>
    </div>
</section>

{{-- CTA --}}
<section class="gst-cta-section" id="get-started" aria-label="Get started">
    <div class="gst-cta-inner">
        <span class="gst-cta-eyebrow">Join SolMate Today</span>
        <h2 class="gst-cta-title">Ready to Go <span>Solar</span>?</h2>
        <p class="gst-cta-p">
            Sign in to manage your solar installation, track your service requests, and
            monitor your project &mdash; all in one organized platform.
        </p>
        <div class="gst-cta-buttons">
            <a href="{{ route('login') }}" class="gst-cta-btn-primary">Log In to Your Account</a>
            @if (Route::has('register'))
                <a href="{{ route('register') }}" class="gst-cta-btn-secondary">Create a Free Account</a>
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
                    <li><a href="#about">About Us</a></li>
                    <li><a href="#testimonials">Testimonials</a></li>
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
    var grid = document.getElementById("landing-testimonies-grid");

    function escapeHtml(v) {
        return String(v == null ? "" : v)
            .replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;").replace(/'/g, "&#039;");
    }

    function buildStars(rating) {
        var total = 5, filled = Math.min(5, Math.max(0, Math.round(Number(rating) || 0))), html = "";
        for (var i = 0; i < total; i++) {
            html += i < filled
                ? "<span class=\"gst-testimony-star-full\" aria-hidden=\"true\">\u2605</span>"
                : "<span class=\"gst-testimony-star-empty\" aria-hidden=\"true\">\u2605</span>";
        }
        return html;
    }

    function render(testimonies) {
        grid.innerHTML = "";
        var sample = Array.isArray(testimonies) ? testimonies.slice(0, 3) : [];
        if (sample.length === 0) {
            grid.innerHTML = "<div class=\"gst-testimonies-state-msg\">No testimonials available yet. Be the first to share your experience!</div>";
            return;
        }
        sample.forEach(function (t) {
            var name = escapeHtml(t.user && t.user.name ? t.user.name : "Anonymous Customer");
            var body = escapeHtml(t.message || t.title || "Great service!");
            var stars = buildStars(t.rating);
            var card = document.createElement("div");
            card.className = "gst-testimony-card";
            card.innerHTML =
                "<div class=\"gst-testimony-quote-icon\" aria-hidden=\"true\">"
                + "<svg width=\"16\" height=\"16\" viewBox=\"0 0 24 24\" fill=\"currentColor\"><path d=\"M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z\"/></svg>"
                + "</div>"
                + "<p class=\"gst-testimony-text\">" + body + "</p>"
                + "<div class=\"gst-testimony-footer\">"
                + "<span class=\"gst-testimony-name\">" + name + "</span>"
                + "<div class=\"gst-testimony-stars\" aria-label=\"Rating: " + escapeHtml(String(t.rating || 0)) + " out of 5\">" + stars + "</div>"
                + "</div>";
            grid.appendChild(card);
        });
    }

    fetch("/api/public/testimonies", {
        method: "GET",
        headers: { "Accept": "application/json", "X-Requested-With": "XMLHttpRequest" }
    }).then(function (res) {
        if (!res.ok) throw new Error("HTTP " + res.status);
        return res.json();
    }).then(function (payload) {
        render(Array.isArray(payload) ? payload : (payload.data || []));
    }).catch(function () {
        grid.innerHTML = "<div class=\"gst-testimonies-state-msg\">Testimonials unavailable right now.</div>";
    });
})();
</script>
</body>
</html>
