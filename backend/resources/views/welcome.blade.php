<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SolMate &mdash; Smart Solar Installation Management</title>
    <style>
        html { scroll-behavior: smooth; scroll-padding-top: 68px; }
        *, *::before, *::after { box-sizing: border-box; }
        :root { font-family: Arial, sans-serif; line-height: 1.5; color: #0F2F4A; }
        body { margin: 0; background: #ffffff; }
        a { text-decoration: none; color: inherit; }

        /* HEADER */
        .gst-header { position: sticky; top: 0; z-index: 100; background: linear-gradient(180deg, #ffffff 0%, #F8FAFC 100%); border-bottom: 1px solid rgba(32,167,201,0.12); box-shadow: 0 10px 24px rgba(18,58,90,0.06); }
        .gst-header-inner { max-width: 1200px; margin: 0 auto; padding: 0 28px; height: 68px; display: flex; align-items: center; justify-content: space-between; }
        .gst-brand { display: inline-flex; align-items: center; text-decoration: none; line-height: 0; }
        .gst-logo { display: block; width: auto; max-width: 100%; height: auto; }
        .gst-logo--header { height: 42px; }
        .gst-logo--footer { height: 52px; }
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

        /* HERO */
        .gst-hero { background: linear-gradient(135deg, #F8FAFC 0%, #EAF9FD 52%, #fff8e7 100%); padding: 104px 32px 108px; }
        .gst-hero-inner { max-width: 1200px; margin: 0 auto; display: grid; grid-template-columns: minmax(0, 1.05fr) minmax(320px, 0.95fr); gap: 72px; align-items: center; }
        .gst-hero-copy { max-width: 560px; }
        .gst-hero-tag { display: inline-flex; align-items: center; gap: 8px; padding: 6px 14px; background: #EAF9FD; border: 1px solid rgba(125,223,242,0.6); border-radius: 999px; font-size: 12.5px; font-weight: 700; color: #20A7C9; margin-bottom: 24px; }
        .gst-hero-tag-dot { width: 6px; height: 6px; border-radius: 50%; background: #20A7C9; display: inline-block; }
        .gst-hero-h1 { font-size: 52px; font-weight: 700; color: #0F2F4A; line-height: 1.08; margin: 0 0 22px; letter-spacing: -0.8px; }
        .gst-hero-h1 span { color: #F4D000; }
        .gst-hero-p { font-size: 17px; color: #5E7288; line-height: 1.85; margin: 0 0 34px; max-width: 500px; }
        .gst-hero-actions { display: flex; gap: 14px; align-items: center; flex-wrap: wrap; margin-bottom: 18px; }
        .gst-cta-primary { padding: 14px 30px; font-size: 15px; font-weight: 700; color: #ffffff; background: #123A5A; border: none; border-radius: 10px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; transition: background .15s, transform .1s; }
        .gst-cta-primary:hover { background: #0F2F4A; transform: translateY(-1px); text-decoration: none; color: #ffffff; }
        .gst-cta-secondary { padding: 14px 30px; font-size: 15px; font-weight: 600; color: #123A5A; background: transparent; border: 2px solid #123A5A; border-radius: 10px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; transition: background .15s, color .15s; }
        .gst-cta-secondary:hover { background: #123A5A; color: #ffffff; text-decoration: none; }
        .gst-hero-note { font-size: 13px; color: #5E7288; margin: 0; }
        .gst-hero-visual { position: relative; }
        .gst-hero-card-main { position: relative; overflow: hidden; background: linear-gradient(180deg, #ffffff 0%, #F8FAFC 100%); border-radius: 24px; padding: 34px; box-shadow: 0 24px 60px rgba(15,23,42,0.12), 0 4px 12px rgba(15,23,42,0.06); border: 1px solid rgba(148,163,184,0.22); }
        .gst-hero-card-main::before { content: ""; position: absolute; top: -54px; right: -48px; width: 164px; height: 164px; border-radius: 50%; background: radial-gradient(circle, rgba(212,160,23,0.24) 0%, rgba(212,160,23,0) 72%); }
        .gst-hero-card-badge { display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; background: #FFF7CC; border: 1px solid rgba(212,160,23,0.24); border-radius: 999px; font-size: 12px; font-weight: 600; color: #92400e; margin-bottom: 18px; }
        .gst-hero-card-title { font-size: 24px; font-weight: 700; color: #0F2F4A; margin: 0 0 10px; line-height: 1.25; }
        .gst-hero-card-sub { font-size: 14px; color: #5E7288; margin: 0 0 28px; line-height: 1.7; max-width: 420px; }
        .gst-hero-feature-list { display: grid; gap: 14px; margin-bottom: 24px; }
        .gst-hero-feature-item { display: flex; align-items: flex-start; gap: 14px; padding: 16px 18px; background: rgba(248,250,252,0.9); border: 1px solid #DDE7EE; border-radius: 16px; }
        .gst-hero-feature-icon { width: 42px; height: 42px; border-radius: 12px; background: linear-gradient(135deg, #123A5A, #20A7C9); color: #ffffff; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: inset 0 1px 0 rgba(255,255,255,0.14); }
        .gst-hero-feature-title { font-size: 15px; font-weight: 700; color: #123A5A; margin: 0 0 4px; }
        .gst-hero-feature-desc { font-size: 13px; color: #5E7288; margin: 0; line-height: 1.6; }
        .gst-hero-card-footer { display: flex; align-items: center; justify-content: space-between; gap: 14px; padding: 16px 18px; background: #123A5A; border-radius: 16px; color: #ffffff; }
        .gst-hero-card-footer-title { font-size: 14px; font-weight: 700; margin: 0 0 4px; }
        .gst-hero-card-footer-copy { font-size: 12.5px; color: #DDE7EE; margin: 0; line-height: 1.6; }
        .gst-hero-card-footer-icon { width: 46px; height: 46px; border-radius: 14px; background: rgba(255,255,255,0.12); display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0; color: #f8d774; }

        /* TRUST */
        .gst-trust { background: #f8fafc; padding: 68px 32px; }
        .gst-trust-inner { max-width: 1200px; margin: 0 auto; }
        .gst-trust-grid { display: grid; grid-template-columns: repeat(3,1fr); gap: 24px; }
        .gst-trust-card { background: #ffffff; border: 1px solid #DDE7EE; border-radius: 14px; padding: 28px 24px; box-shadow: 0 2px 8px rgba(15,23,42,0.04); transition: box-shadow .2s, transform .2s; }
        .gst-trust-card:hover { box-shadow: 0 8px 24px rgba(15,23,42,0.09); transform: translateY(-2px); }
        .gst-trust-icon { width: 44px; height: 44px; background: #EAF9FD; border-radius: 10px; display: flex; align-items: center; justify-content: center; margin-bottom: 16px; color: #123A5A; }
        .gst-trust-title { font-size: 15px; font-weight: 700; color: #0F2F4A; margin: 0 0 8px; }
        .gst-trust-desc { font-size: 13.5px; color: #5E7288; line-height: 1.65; margin: 0; }
        .gst-services-cta { text-align: center; margin-top: 36px; }
        .gst-services-cta a { display: inline-flex; align-items: center; gap: 8px; font-size: 14.5px; font-weight: 600; color: #123A5A; border: 2px solid #123A5A; padding: 12px 26px; border-radius: 10px; text-decoration: none; transition: background .15s, color .15s; }
        .gst-services-cta a:hover { background: #123A5A; color: #ffffff; }

        /* APP DOWNLOAD */
        .gst-app { background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%); padding: 80px 32px; }
        .gst-app-inner { max-width: 1200px; margin: 0 auto; }
        .gst-app-shell { max-width: 980px; margin: 0 auto; display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 22px; align-items: stretch; }
        .gst-app-card,
        .gst-app-panel { background: #ffffff; border: 1px solid #DDE7EE; border-radius: 22px; box-shadow: 0 14px 36px rgba(15,23,42,0.07); }
        .gst-app-card {
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: center;
            min-height: 360px;
            padding: 28px;
            background:
                radial-gradient(circle at top right, rgba(244,208,0,0.18), transparent 34%),
                linear-gradient(135deg, #123A5A 0%, #123A5A 56%, #1f4d76 100%);
            color: #ffffff;
        }
        .gst-app-card::after {
            content: "";
            position: absolute;
            right: -40px;
            bottom: -55px;
            width: 190px;
            height: 190px;
            border-radius: 50%;
            background: rgba(255,255,255,0.08);
            pointer-events: none;
        }
        .gst-app-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 7px 12px;
            border-radius: 999px;
            background: rgba(255,255,255,0.12);
            border: 1px solid rgba(255,255,255,0.18);
            color: #f8d774;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }
        .gst-app-title { margin: 18px 0 10px; font-size: 28px; font-weight: 700; line-height: 1.15; letter-spacing: -0.03em; }
        .gst-app-copy { margin: 0; max-width: 620px; font-size: 15px; line-height: 1.8; color: rgba(255,255,255,0.84); }
        .gst-app-points { display: grid; gap: 12px; margin: 24px 0 0; }
        .gst-app-point { display: flex; align-items: flex-start; gap: 12px; padding: 14px 16px; border-radius: 16px; background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.12); }
        .gst-app-point-dot { width: 10px; height: 10px; border-radius: 50%; background: #F4D000; margin-top: 5px; flex-shrink: 0; }
        .gst-app-point strong { display: block; font-size: 14px; margin-bottom: 4px; color: #ffffff; }
        .gst-app-point span { display: block; font-size: 13px; line-height: 1.65; color: rgba(255,255,255,0.78); }
        .gst-app-panel { padding: 30px; display: flex; flex-direction: column; justify-content: center; }
        .gst-app-panel-tag { display: inline-flex; align-items: center; width: fit-content; padding: 6px 11px; border-radius: 999px; background: #FFF7CC; color: #92400e; font-size: 11px; font-weight: 700; letter-spacing: 0.05em; text-transform: uppercase; }
        .gst-app-panel-title { margin: 14px 0 8px; font-size: 22px; font-weight: 700; color: #123A5A; line-height: 1.2; }
        .gst-app-panel-copy { margin: 0; font-size: 14px; line-height: 1.75; color: #5E7288; }
        .gst-app-qr-frame { position: relative; z-index: 1; width: min(178px, 100%); margin: 24px auto 0; padding: 12px; border-radius: 20px; background: #ffffff; border: 1px solid #DDE7EE; box-shadow: 0 12px 28px rgba(15,23,42,0.09); }
        .gst-app-qr-frame img { display: block; width: 100%; height: auto; border-radius: 14px; }
        .gst-app-panel-note { margin-top: 18px; margin-bottom: 18px; padding: 14px 16px; border-radius: 14px; background: #F8FAFC; border: 1px dashed #DDE7EE; color: #5E7288; font-size: 13px; line-height: 1.65; }

        /* NEWS */
        .gst-news { background: linear-gradient(180deg, #ffffff 0%, #F8FAFC 100%); padding: 80px 32px; }

        /* PROMOTIONS */
        .gst-promos { background: linear-gradient(135deg, #F8FAFC 0%, #EAF9FD 60%, #fff8e7 100%); padding: 80px 32px; }
        .gst-promos-inner { max-width: 1200px; margin: 0 auto; }
        .gst-promos-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 24px; }
        .gst-promo-card { display: flex; flex-direction: column; background: #ffffff; border-radius: 20px; overflow: hidden; border: 1px solid #DDE7EE; box-shadow: 0 8px 24px rgba(15,23,42,0.07); transition: transform .2s, box-shadow .2s; }
        .gst-promo-card:hover { transform: translateY(-3px); box-shadow: 0 16px 40px rgba(15,23,42,0.12); }
        .gst-promo-card-media { aspect-ratio: 16 / 7; overflow: hidden; background: #DDE7EE; }
        .gst-promo-card-media img { width: 100%; height: 100%; display: block; object-fit: cover; }
        .gst-promo-card-media--gradient { background: linear-gradient(135deg, #123A5A 0%, #1f4d76 56%, #20A7C9 100%); display: flex; align-items: center; justify-content: center; }
        .gst-promo-no-img-icon { color: rgba(255,255,255,0.6); }
        .gst-promo-card-body { display: flex; flex-direction: column; gap: 12px; padding: 22px 22px 24px; flex: 1; }
        .gst-promo-card-tag { display: inline-flex; align-items: center; gap: 6px; padding: 4px 12px; background: #FFF7CC; border: 1px solid rgba(212,160,23,0.28); border-radius: 999px; font-size: 11.5px; font-weight: 700; color: #92400e; text-transform: uppercase; letter-spacing: 0.05em; width: fit-content; }
        .gst-promo-card-title { font-size: 18px; font-weight: 800; color: #0F2F4A; line-height: 1.3; margin: 0; }
        .gst-promo-card-desc { font-size: 13.5px; color: #5E7288; line-height: 1.7; margin: 0; flex: 1; }
        .gst-promo-card-cta { display: inline-flex; align-items: center; gap: 7px; margin-top: 4px; padding: 11px 22px; background: #123A5A; color: #ffffff; font-size: 14px; font-weight: 700; border-radius: 10px; text-decoration: none; width: fit-content; transition: background .15s, transform .1s; }
        .gst-promo-card-cta:hover { background: #0F2F4A; transform: translateY(-1px); color: #ffffff; text-decoration: none; }
        .gst-promo-card-cta-static { display: inline-flex; align-items: center; margin-top: 4px; padding: 11px 22px; background: #EAF9FD; color: #123A5A; font-size: 14px; font-weight: 700; border-radius: 10px; border: 1.5px solid #20A7C9; width: fit-content; }
        .gst-news-inner { max-width: 1200px; margin: 0 auto; }
        .gst-news-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 24px; }
        .gst-news-card {
            display: flex;
            flex-direction: column;
            gap: 0;
            min-height: 100%;
            background: linear-gradient(180deg, #ffffff 0%, #fdfefe 100%);
            border: 1px solid #DDE7EE;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 14px 36px rgba(15,23,42,0.07);
            transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
        }
        .gst-news-card:hover {
            transform: translateY(-4px);
            border-color: rgba(212,160,23,0.46);
            box-shadow: 0 20px 40px rgba(15,23,42,0.1);
        }
        .gst-news-card-media {
            position: relative;
            aspect-ratio: 16 / 9;
            overflow: hidden;
            background: linear-gradient(135deg, #EAF9FD 0%, #f6fbff 52%, #fff5d8 100%);
        }
        .gst-news-card-media img {
            width: 100%;
            height: 100%;
            display: block;
            object-fit: cover;
        }
        .gst-news-card-media-placeholder {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            color: #5E7288;
            font-size: 13px;
            font-weight: 600;
            line-height: 1.7;
            text-align: center;
            background: linear-gradient(180deg, rgba(255,255,255,0.14) 0%, rgba(255,255,255,0.28) 100%);
        }
        .gst-news-card-body {
            display: flex;
            flex-direction: column;
            gap: 18px;
            flex: 1;
            padding: 24px 24px 26px;
        }
        .gst-news-card-tag {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            width: fit-content;
            padding: 7px 12px;
            border-radius: 999px;
            background: #FFF7CC;
            color: #92400e;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }
        .gst-news-card-tag::before {
            content: "";
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #F4D000;
            display: inline-block;
        }
        .gst-news-card-title {
            font-size: 22px;
            font-weight: 700;
            color: #0F2F4A;
            line-height: 1.28;
            margin: 0;
            letter-spacing: -0.02em;
        }
        .gst-news-card-desc {
            margin: 0;
            color: #5E7288;
            font-size: 14px;
            line-height: 1.75;
            flex: 1;
        }
        .gst-news-card-meta {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
            padding: 16px 0 0;
            border-top: 1px solid #DDE7EE;
        }
        .gst-news-card-meta-label {
            display: block;
            margin-bottom: 4px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #7F92A3;
        }
        .gst-news-card-meta-value {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #123A5A;
            line-height: 1.5;
            word-break: break-word;
        }
        .gst-news-card-link {
            align-self: flex-start;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 18px;
            border: 1.5px solid #123A5A;
            border-radius: 10px;
            background: transparent;
            color: #123A5A;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: background .15s ease, color .15s ease, border-color .15s ease;
        }
        .gst-news-card-link:hover {
            background: #123A5A;
            color: #ffffff;
            border-color: #123A5A;
            text-decoration: none;
        }
        .gst-news-empty {
            max-width: 720px;
            margin: 0 auto;
            padding: 30px 28px;
            border: 1px dashed #DDE7EE;
            border-radius: 20px;
            background: linear-gradient(180deg, #F8FAFC 0%, #ffffff 100%);
            text-align: center;
            box-shadow: 0 10px 28px rgba(15,23,42,0.05);
        }
        .gst-news-empty-title {
            margin: 0;
            font-size: 20px;
            font-weight: 700;
            color: #123A5A;
        }
        .gst-news-empty-copy {
            margin: 10px 0 0;
            font-size: 14px;
            line-height: 1.7;
            color: #5E7288;
        }

        /* SECTION HEADING */
        .gst-section-heading { text-align: center; margin-bottom: 52px; }
        .gst-section-eyebrow { display: inline-block; font-size: 12px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: #F4D000; margin-bottom: 10px; }
        .gst-section-title { font-size: 34px; font-weight: 700; color: #0F2F4A; margin: 0 0 12px; line-height: 1.2; }
        .gst-section-sub { font-size: 16px; color: #5E7288; max-width: 520px; margin: 0 auto; line-height: 1.7; }

        /* TESTIMONIES */
        .gst-testimonies { background: #ffffff; padding: 80px 32px; }
        .gst-testimonies-inner { max-width: 1200px; margin: 0 auto; }
        .gst-testimonies-carousel-shell { max-width: 1080px; margin: 0 auto; }
        .gst-testimonies-carousel { position: relative; background: linear-gradient(180deg, #ffffff 0%, #f6faff 100%); border: 1px solid #DDE7EE; border-radius: 28px; box-shadow: 0 22px 50px rgba(15,23,42,0.08); overflow: hidden; }
        .gst-testimonies-viewport { position: relative; min-height: 520px; aspect-ratio: 16 / 9; background: linear-gradient(135deg, #EAF9FD 0%, #f7fbff 45%, #fff5d8 100%); }
        .gst-testimonies-track { display: flex; width: 100%; height: 100%; transition: transform .75s ease; will-change: transform; }
        .gst-testimony-slide { position: relative; min-width: 100%; height: 100%; overflow: hidden; }
        .gst-testimony-slide img { width: 100%; height: 100%; display: block; object-fit: cover; }
        .gst-testimony-slide-overlay { position: absolute; inset: 0; background: linear-gradient(180deg, rgba(15,23,42,0.04) 0%, rgba(15,23,42,0.18) 52%, rgba(15,23,42,0.68) 100%); pointer-events: none; }
        .gst-testimony-slide-meta { position: absolute; left: 28px; right: 28px; bottom: 26px; display: flex; align-items: flex-end; justify-content: space-between; gap: 18px; z-index: 2; }
        .gst-testimony-slide-copy { max-width: 540px; }
        .gst-testimony-slide-kicker { display: inline-flex; align-items: center; gap: 8px; padding: 7px 12px; border-radius: 999px; background: rgba(255,255,255,0.16); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.2); color: #f8fafc; font-size: 12px; font-weight: 700; letter-spacing: 0.04em; text-transform: uppercase; margin-bottom: 12px; }
        .gst-testimony-slide-kicker::before { content: ""; width: 7px; height: 7px; border-radius: 50%; background: #f8d774; display: inline-block; }
        .gst-testimony-slide-title { font-size: 28px; font-weight: 700; color: #ffffff; line-height: 1.18; margin: 0 0 8px; letter-spacing: -0.03em; }
        .gst-testimony-slide-sub { font-size: 14px; color: rgba(226,232,240,0.96); margin: 0; line-height: 1.75; }
        .gst-testimony-slide-index { display: inline-flex; align-items: center; justify-content: center; min-width: 56px; height: 56px; padding: 0 16px; border-radius: 16px; background: rgba(16,42,67,0.68); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.18); color: #ffffff; font-size: 14px; font-weight: 700; }
        .gst-testimonies-arrow { position: absolute; top: 50%; transform: translateY(-50%); width: 48px; height: 48px; border: none; border-radius: 999px; background: rgba(255,255,255,0.88); color: #123A5A; box-shadow: 0 10px 24px rgba(15,23,42,0.16); display: inline-flex; align-items: center; justify-content: center; cursor: pointer; transition: transform .15s ease, background .15s ease, color .15s ease; z-index: 3; }
        .gst-testimonies-arrow:hover { background: #123A5A; color: #ffffff; transform: translateY(-50%) scale(1.02); }
        .gst-testimonies-arrow:focus-visible { outline: 3px solid rgba(14,165,233,0.35); outline-offset: 2px; }
        .gst-testimonies-arrow-prev { left: 20px; }
        .gst-testimonies-arrow-next { right: 20px; }
        .gst-testimonies-controls { display: flex; align-items: center; justify-content: space-between; gap: 18px; padding: 20px 24px 22px; background: #ffffff; border-top: 1px solid #DDE7EE; }
        .gst-testimonies-source { font-size: 13px; color: #5E7288; }
        .gst-testimonies-source strong { color: #123A5A; font-weight: 700; }
        .gst-testimonies-dots { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; justify-content: flex-end; }
        .gst-testimonies-dot { width: 10px; height: 10px; padding: 0; border: none; border-radius: 999px; background: #DDE7EE; cursor: pointer; transition: width .2s ease, background .2s ease, transform .2s ease; }
        .gst-testimonies-dot.is-active { width: 28px; background: #F4D000; }
        .gst-testimonies-dot:focus-visible { outline: 3px solid rgba(14,165,233,0.35); outline-offset: 2px; }
        .gst-testimonies-state-msg { text-align: center; padding: 64px 24px; color: #5E7288; font-size: 15px; line-height: 1.7; }
        .gst-testimonies-state-msg strong { display: block; font-size: 18px; color: #123A5A; margin-bottom: 8px; }
        .gst-testimony-placeholder { position: relative; height: 100%; display: flex; align-items: flex-end; padding: 34px; background: linear-gradient(140deg, #dff0ff 0%, #ecf8ff 34%, #fff7dc 100%); }
        .gst-testimony-placeholder::before { content: ""; position: absolute; top: 38px; right: 52px; width: 120px; height: 120px; border-radius: 50%; background: radial-gradient(circle, rgba(212,160,23,0.9) 0%, rgba(212,160,23,0.35) 42%, rgba(212,160,23,0) 72%); }
        .gst-testimony-placeholder::after { content: ""; position: absolute; inset: auto -60px -88px auto; width: 320px; height: 320px; border-radius: 50%; background: radial-gradient(circle, rgba(14,165,233,0.22) 0%, rgba(14,165,233,0) 72%); }
        .gst-testimony-placeholder-scene { position: absolute; inset: 0; overflow: hidden; }
        .gst-testimony-placeholder-ground { position: absolute; left: -8%; right: -8%; bottom: -18%; height: 44%; background: linear-gradient(180deg, rgba(16,42,67,0.06) 0%, rgba(16,42,67,0.14) 100%); border-radius: 50% 50% 0 0; }
        .gst-testimony-placeholder-panel { position: absolute; bottom: 104px; width: 148px; height: 92px; border-radius: 18px; background: linear-gradient(180deg, #143a5c 0%, #123A5A 100%); border: 2px solid rgba(255,255,255,0.18); box-shadow: 0 24px 34px rgba(16,42,67,0.16); transform: skew(-14deg); overflow: hidden; }
        .gst-testimony-placeholder-panel::before { content: ""; position: absolute; inset: 10px; background-image: linear-gradient(to right, rgba(255,255,255,0.18) 1px, transparent 1px), linear-gradient(to bottom, rgba(255,255,255,0.18) 1px, transparent 1px); background-size: 22px 100%, 100% 18px; border-radius: 10px; }
        .gst-testimony-placeholder-panel.is-left { left: 10%; }
        .gst-testimony-placeholder-panel.is-center { left: 34%; bottom: 92px; }
        .gst-testimony-placeholder-panel.is-right { right: 12%; }
        .gst-testimony-placeholder-card { position: relative; z-index: 1; width: min(100%, 430px); padding: 26px; background: rgba(255,255,255,0.82); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.55); border-radius: 22px; box-shadow: 0 20px 44px rgba(15,23,42,0.12); }
        .gst-testimony-placeholder-chip { display: inline-flex; align-items: center; gap: 8px; padding: 6px 12px; background: #123A5A; color: #ffffff; border-radius: 999px; font-size: 12px; font-weight: 700; margin-bottom: 14px; }
        .gst-testimony-placeholder-chip::before { content: ""; width: 8px; height: 8px; border-radius: 50%; background: #f8d774; display: inline-block; }
        .gst-testimony-placeholder-title { font-size: 28px; font-weight: 700; color: #123A5A; line-height: 1.16; margin: 0 0 10px; letter-spacing: -0.03em; }
        .gst-testimony-placeholder-sub { font-size: 14px; line-height: 1.75; color: #5E7288; margin: 0; }
        .gst-testimonies-view-all { text-align: center; margin-top: 36px; }
        .gst-testimonies-view-all a { display: inline-flex; align-items: center; gap: 6px; font-size: 14.5px; font-weight: 600; color: #123A5A; border: 2px solid #123A5A; padding: 10px 24px; border-radius: 8px; text-decoration: none; transition: background .15s, color .15s; }
        .gst-testimonies-view-all a:hover { background: #123A5A; color: #ffffff; }

        /* ABOUT */
        .gst-about { background: #F8FAFC; padding: 80px 32px; }
        .gst-about-inner { max-width: 1200px; margin: 0 auto; display: grid; grid-template-columns: 1fr 1fr; gap: 64px; align-items: center; }
        .gst-about-eyebrow { display: inline-block; font-size: 12px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: #F4D000; margin-bottom: 12px; }
        .gst-about-title { font-size: 36px; font-weight: 700; color: #0F2F4A; line-height: 1.2; margin: 0 0 18px; }
        .gst-about-p { font-size: 15.5px; color: #5E7288; line-height: 1.8; margin: 0 0 24px; }
        .gst-about-highlights { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .gst-about-highlight { background: #ffffff; border: 1px solid #DDE7EE; border-radius: 14px; padding: 22px; box-shadow: 0 2px 8px rgba(15,23,42,0.04); }
        .gst-about-highlight-icon { width: 38px; height: 38px; background: #EAF9FD; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #123A5A; margin-bottom: 12px; }
        .gst-about-highlight-title { font-size: 14px; font-weight: 700; color: #0F2F4A; margin: 0 0 6px; }
        .gst-about-highlight-desc { font-size: 13px; color: #5E7288; line-height: 1.6; margin: 0; }

        /* CTA */
        .gst-cta-section { background: #123A5A; padding: 88px 32px; }
        .gst-cta-inner { max-width: 680px; margin: 0 auto; text-align: center; }
        .gst-cta-eyebrow { display: inline-block; font-size: 12px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: #F4D000; margin-bottom: 14px; }
        .gst-cta-title { font-size: 38px; font-weight: 700; color: #ffffff; line-height: 1.2; margin: 0 0 16px; }
        .gst-cta-title span { color: #F4D000; }
        .gst-cta-p { font-size: 16px; color: #7F92A3; line-height: 1.7; margin: 0 0 42px; }
        .gst-cta-buttons { display: flex; justify-content: center; gap: 16px; flex-wrap: wrap; }
        .gst-cta-btn-primary { padding: 15px 36px; font-size: 15px; font-weight: 700; color: #123A5A; background: #F4D000; border: none; border-radius: 10px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; transition: background .15s, transform .1s; }
        .gst-cta-btn-primary:hover { background: #E6C200; transform: translateY(-1px); color: #123A5A; text-decoration: none; }
        .gst-cta-btn-secondary { padding: 15px 36px; font-size: 15px; font-weight: 600; color: #ffffff; background: transparent; border: 2px solid rgba(255,255,255,0.3); border-radius: 10px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; transition: border-color .15s, background .15s; }
        .gst-cta-btn-secondary:hover { border-color: rgba(255,255,255,0.7); background: rgba(255,255,255,0.07); text-decoration: none; color: #ffffff; }

        /* FOOTER */
        .solmate-footer { background: linear-gradient(180deg, #3A7EA6 0%, #2A6B92 42%, #1C5476 100%); color: #DDE7EE; font-family: Arial, sans-serif; border-top: 1px solid rgba(125,223,242,0.28); }
        .solmate-footer-inner { max-width: 1200px; margin: 0 auto; padding: 56px 32px 0; }
        .solmate-footer-upper { display: grid; grid-template-columns: 2fr 1fr 1fr 0.6fr; gap: 48px; padding-bottom: 48px; }
        .solmate-footer-brand-link { text-decoration: none; display: inline-flex; align-items: center; margin-bottom: 16px; line-height: 0; }
        .solmate-footer-brand-link:hover { text-decoration: none; }
        .solmate-footer-desc { font-size: 13.5px; line-height: 1.75; color: rgba(255,255,255,0.84); max-width: 300px; margin: 0; }
        .solmate-footer-col-heading { font-size: 13px; font-weight: 700; color: #DDE7EE; letter-spacing: 0.04em; text-transform: uppercase; margin: 0 0 18px; }
        .solmate-footer-links { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 11px; }
        .solmate-footer-links a { font-size: 13.5px; color: rgba(255,255,255,0.88); text-decoration: none; transition: color .15s; }
        .solmate-footer-links a:hover { color: #7DDFF2; text-decoration: none; }
        .solmate-footer-socials { display: flex; flex-direction: column; gap: 12px; }
        .solmate-footer-social-btn { display: flex; align-items: center; justify-content: center; width: 38px; height: 38px; border-radius: 50%; background: rgba(255,255,255,0.12); border: 1px solid rgba(255,255,255,0.22); color: #DDE7EE; text-decoration: none; transition: background .15s, color .15s; line-height: 0; }
        .solmate-footer-social-btn:hover { background: #F4D000; color: #0F2F4A; text-decoration: none; }
        .solmate-footer-divider { border: none; border-top: 1px solid rgba(255,255,255,0.16); margin: 0; }
        .solmate-footer-bottom { max-width: 1200px; margin: 0 auto; padding: 20px 32px; display: flex; align-items: center; justify-content: space-between; gap: 24px; flex-wrap: wrap; }
        .solmate-footer-copyright { font-size: 12.5px; color: rgba(234,249,253,0.8); margin: 0; line-height: 1.5; }
        .solmate-footer-contact-items { display: flex; align-items: center; gap: 32px; flex-wrap: wrap; }
        .solmate-footer-contact-item { display: flex; align-items: center; gap: 8px; font-size: 12.5px; color: rgba(234,249,253,0.86); }
        .solmate-footer-contact-item svg { flex-shrink: 0; color: #7DDFF2; }

        /* RESPONSIVE */
        @media (max-width: 1000px) {
            .gst-hero-inner { gap: 40px; }
            .gst-trust-grid { grid-template-columns: repeat(2,1fr); }
            .gst-app-shell { grid-template-columns: 1fr; }
            .gst-news-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .gst-promos-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .solmate-footer-upper { grid-template-columns: 1fr 1fr; gap: 36px; }
        }
        @media (max-width: 720px) {
            .gst-hero-inner, .gst-about-inner { grid-template-columns: 1fr; gap: 36px; }
            .gst-hero-copy { max-width: none; }
            .gst-hero-card-main { padding: 28px 22px; }
            .gst-hero-h1 { font-size: 34px; }
            .gst-hero { padding: 60px 20px 64px; }
            .gst-trust, .gst-app, .gst-news, .gst-promos, .gst-testimonies, .gst-about, .gst-cta-section { padding: 56px 20px; }
            .gst-trust-grid { grid-template-columns: 1fr; }
            .gst-news-grid { grid-template-columns: 1fr; }
            .gst-promos-grid { grid-template-columns: 1fr; }
            .gst-about-highlights { grid-template-columns: 1fr; }
            .gst-section-title { font-size: 26px; }
            .gst-cta-title { font-size: 28px; }
            .gst-about-title { font-size: 28px; }
            .gst-app-title { font-size: 26px; }
            .gst-header-inner { padding: 0 16px; }
            .gst-nav-links { display: none; }
            .gst-testimonies-viewport { min-height: 420px; }
            .gst-testimony-slide-meta { left: 22px; right: 22px; bottom: 22px; align-items: flex-start; flex-direction: column; }
            .gst-testimonies-controls { flex-direction: column; align-items: flex-start; }
            .gst-testimonies-dots { justify-content: flex-start; }
            .gst-testimony-slide-title, .gst-testimony-placeholder-title { font-size: 24px; }
            .gst-testimony-placeholder { padding: 24px; }
            .gst-testimony-placeholder-panel { width: 118px; height: 72px; bottom: 124px; }
            .gst-testimony-placeholder-panel.is-center { bottom: 110px; }
            .gst-news-card-body { padding: 22px 20px 24px; }
            .gst-app-card, .gst-app-panel { padding-left: 22px; padding-right: 22px; }
        }
        @media (max-width: 560px) {
            .solmate-footer-upper { grid-template-columns: 1fr; gap: 28px; }
            .solmate-footer-inner { padding: 40px 20px 0; }
            .solmate-footer-bottom { flex-direction: column; align-items: flex-start; padding: 20px; gap: 14px; }
            .solmate-footer-contact-items { gap: 16px; }
            .gst-testimonies-viewport { min-height: 360px; aspect-ratio: auto; }
            .gst-testimonies-arrow { width: 42px; height: 42px; top: auto; bottom: 96px; transform: none; }
            .gst-testimonies-arrow:hover { transform: scale(1.02); }
            .gst-testimonies-arrow-prev { left: 14px; }
            .gst-testimonies-arrow-next { right: 14px; }
            .gst-testimony-slide-meta { left: 18px; right: 18px; bottom: 18px; }
            .gst-testimony-slide-index { min-width: 48px; height: 48px; }
            .gst-testimony-placeholder-card { padding: 22px 20px; }
            .gst-news-card-meta { grid-template-columns: 1fr; gap: 12px; }
            .gst-news-card-link { width: 100%; justify-content: center; }
            .gst-news-empty { padding: 24px 20px; }
        }
        @media (max-width: 480px) {
            .gst-hero-actions, .gst-cta-buttons { flex-direction: column; align-items: flex-start; }
            .gst-cta-buttons { align-items: center; }
            .gst-cta-primary, .gst-cta-secondary { width: 100%; justify-content: center; }
            .gst-hero-card-footer { align-items: flex-start; }
        }
    </style>
</head>
<body>

{{-- HEADER --}}
<header class="gst-header" aria-label="Site header">
    <div class="gst-header-inner">
        <a href="#home" class="gst-brand" aria-label="RDY home">
            <img src="{{ asset('images/rdy-logo-transparent.png') }}" alt="RDY logo" class="gst-logo gst-logo--header">
        </a>
        <nav class="gst-nav-links" aria-label="Public navigation">
            <a href="#rdy" class="gst-nav-link">RDY</a>
            <a href="#services" class="gst-nav-link">Services</a>
            <a href="#news" class="gst-nav-link">News</a>
            <a href="{{ route('public.testimonies') }}" class="gst-nav-link">All Reviews</a>
            <a href="#about" class="gst-nav-link">About</a>
            <a href="{{ route('public.contact') }}" class="gst-nav-link">Contact</a>
            <a href="#download-app" class="gst-nav-link">Download App</a>
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

{{-- TESTIMONIES / RDY --}}
<section class="gst-testimonies" id="rdy" aria-label="Visual highlights">
        <div class="gst-testimonies-inner">
        <div class="gst-section-heading">
            <span class="gst-section-eyebrow">SolMate by RDY</span>
            <h2 class="gst-section-title">SolMate by RDY</h2>
            <p class="gst-section-sub">Take a look at real solar projects we've completed — from homes to businesses, powered by RDY.</p>
        </div>
        <div class="gst-testimonies-carousel-shell">
            <div class="gst-testimonies-carousel" id="landing-testimonies-carousel">
                <div class="gst-testimonies-state-msg" id="landing-testimonies-loading">
                    <strong>Loading visual highlights...</strong>
                    Preparing the latest SolMate gallery.
                </div>
            </div>
        </div>
    </div>
</section>

{{-- HERO --}}
<section class="gst-hero" id="home" aria-label="Hero">
    <div class="gst-hero-inner">
        <div class="gst-hero-copy">
            <div class="gst-hero-tag">
                <span class="gst-hero-tag-dot" aria-hidden="true"></span>
                SolMate by RDY
            </div>
            <h1 class="gst-hero-h1">
                Start Your <span>Solar Journey</span> Today
            </h1>
            <p class="gst-hero-p">
                Get an instant solar estimate, request a site inspection, and track your service
                &mdash; all in one place.
            </p>
            <div class="gst-hero-actions">
                <a href="{{ route('customer.quotation') }}" class="gst-cta-primary">
                    Get Free Quotation
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                </a>
                <a href="{{ route('customer.inspection') }}" class="gst-cta-secondary">Request Inspection</a>
                <a href="#download-app" class="gst-cta-secondary">Download Now</a>
            </div>
            <p class="gst-hero-note">Create an account or sign in to save your requests and follow each update inside SolMate.</p>
        </div>
        <div class="gst-hero-visual" aria-label="SolMate feature overview">
            <div class="gst-hero-card-main">
                <div class="gst-hero-card-badge">&#9728;&#65039; SolMate by RDY</div>
                <h2 class="gst-hero-card-title">Everything you need to begin with solar, without the noise.</h2>
                <p class="gst-hero-card-sub">Start with the essentials: estimate your system, book a site visit, and stay informed as your request moves forward.</p>
                <div class="gst-hero-feature-list">
                    <div class="gst-hero-feature-item">
                        <div class="gst-hero-feature-icon" aria-hidden="true">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v4"/><path d="M12 18v4"/><path d="m4.93 4.93 2.83 2.83"/><path d="m16.24 16.24 2.83 2.83"/><path d="M2 12h4"/><path d="M18 12h4"/><path d="m4.93 19.07 2.83-2.83"/><path d="m16.24 7.76 2.83-2.83"/><circle cx="12" cy="12" r="4"/></svg>
                        </div>
                        <div>
                            <p class="gst-hero-feature-title">Instant Quotation</p>
                            <p class="gst-hero-feature-desc">See an initial solar estimate based on your power usage and planning needs.</p>
                        </div>
                    </div>
                    <div class="gst-hero-feature-item">
                        <div class="gst-hero-feature-icon" aria-hidden="true">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18"/><path d="M5 21V7l8-4 6 3v15"/><path d="M9 9h.01"/><path d="M9 13h.01"/><path d="M9 17h.01"/><path d="M13 13h.01"/><path d="M13 17h.01"/></svg>
                        </div>
                        <div>
                            <p class="gst-hero-feature-title">Site Inspection</p>
                            <p class="gst-hero-feature-desc">Request a technician visit to confirm your site and prepare for the next step.</p>
                        </div>
                    </div>
                    <div class="gst-hero-feature-item">
                        <div class="gst-hero-feature-icon" aria-hidden="true">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="m7 15 4-4 3 3 5-7"/></svg>
                        </div>
                        <div>
                            <p class="gst-hero-feature-title">Service Tracking</p>
                            <p class="gst-hero-feature-desc">Follow request updates from quotation through inspection and ongoing support.</p>
                        </div>
                    </div>
                </div>
                <div class="gst-hero-card-footer">
                    <div>
                        <p class="gst-hero-card-footer-title">Built for a clear first step</p>
                        <p class="gst-hero-card-footer-copy">No inflated numbers, just the core tools to help you plan your solar system with confidence.</p>
                    </div>
                    <div class="gst-hero-card-footer-icon" aria-hidden="true">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v3"/><path d="M18.36 5.64 16.95 7.05"/><path d="M21 12h-3"/><path d="m18.36 18.36-1.41-1.41"/><path d="M12 21v-3"/><path d="m7.05 16.95-1.41 1.41"/><path d="M6 12H3"/><path d="M7.05 7.05 5.64 5.64"/><path d="M12 8a4 4 0 0 0-4 4c0 1.5.8 2.82 2 3.52V17a1 1 0 0 0 1 1h2a1 1 0 0 0 1-1v-1.48A4 4 0 0 0 12 8Z"/></svg>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- SERVICES --}}
<section class="gst-trust" id="services" aria-label="Our services">
    <div class="gst-trust-inner">
        <div class="gst-section-heading">
            <span class="gst-section-eyebrow">Services</span>
            <h2 class="gst-section-title">Our Services</h2>
            <p class="gst-section-sub">Explore the core solar services available through SolMate and the RDY Solar team.</p>
        </div>
        <div class="gst-trust-grid">
            <div class="gst-trust-card">
                <div class="gst-trust-icon" aria-hidden="true">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 21l-4.35-4.35"/><circle cx="11" cy="11" r="7"/></svg>
                </div>
                <div class="gst-trust-title">Inspection</div>
                <p class="gst-trust-desc">Professional solar site inspection to assess system needs, safety, and installation readiness.</p>
            </div>
            <div class="gst-trust-card">
                <div class="gst-trust-icon" aria-hidden="true">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18"/><path d="M5 21V7l8-4 6 3v15"/><path d="M9 9h.01"/><path d="M9 13h.01"/><path d="M9 17h.01"/><path d="M13 13h.01"/><path d="M13 17h.01"/></svg>
                </div>
                <div class="gst-trust-title">Installation</div>
                <p class="gst-trust-desc">Reliable solar panel installation handled by trained professionals for residential and commercial clients.</p>
            </div>
            <div class="gst-trust-card">
                <div class="gst-trust-icon" aria-hidden="true">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 7h-9"/><path d="M14 17H5"/><circle cx="17" cy="17" r="3"/><circle cx="7" cy="7" r="3"/></svg>
                </div>
                <div class="gst-trust-title">Maintenance</div>
                <p class="gst-trust-desc">Regular maintenance services to keep solar systems efficient, safe, and performing at their best.</p>
            </div>
        </div>
        <div class="gst-services-cta">
            <a href="{{ route('public.contact') }}">
                Contact Us
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </a>
        </div>
    </div>
</section>

{{-- NEWS --}}
<section class="gst-news" id="news" aria-label="Latest news">
    <div class="gst-news-inner">
        <div class="gst-section-heading">
            <span class="gst-section-eyebrow">News</span>
            <h2 class="gst-section-title">Latest News</h2>
            <p class="gst-section-sub">Read the latest solar updates, announcements, and articles shared by RDY Solar.</p>
        </div>
        @php
            $publicNewsArticles = $newsArticles ?? collect();
        @endphp
        @if ($publicNewsArticles->isNotEmpty())
            <div class="gst-news-grid">
                @foreach ($publicNewsArticles as $article)
                    <article class="gst-news-card">
                        <div class="gst-news-card-media">
                            @if ($article->thumbnail_url)
                                <img src="{{ $article->thumbnail_url }}" alt="{{ $article->title }} thumbnail">
                            @else
                                <div class="gst-news-card-media-placeholder">No thumbnail available for this article yet.</div>
                            @endif
                        </div>
                        <div class="gst-news-card-body">
                            <span class="gst-news-card-tag">Latest Article</span>
                            <h3 class="gst-news-card-title">{{ $article->title }}</h3>
                            <p class="gst-news-card-desc">{{ $article->description ?: 'No description metadata was available for this article.' }}</p>
                            <div class="gst-news-card-meta">
                                <div>
                                    <span class="gst-news-card-meta-label">Source</span>
                                    <span class="gst-news-card-meta-value">{{ $article->source_name ?: 'Unknown source' }}</span>
                                </div>
                                <div>
                                    <span class="gst-news-card-meta-label">Date Added</span>
                                    <span class="gst-news-card-meta-value">{{ optional($article->created_at)->format('M d, Y') ?: 'Not available' }}</span>
                                </div>
                            </div>
                            <a href="{{ $article->article_url }}" class="gst-news-card-link" target="_blank" rel="noopener noreferrer">
                                Read Article
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7 17 17 7"/><path d="M8 7h9v9"/></svg>
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>
        @else
            <div class="gst-news-empty">
                <p class="gst-news-empty-title">No news articles available yet.</p>
                <p class="gst-news-empty-copy">New articles shared by RDY Solar will appear here once the admin starts adding article links.</p>
            </div>
        @endif
    </div>
</section>

{{-- PROMOTIONS --}}
@php $publicPromotions = $promotions ?? collect(); @endphp
@if ($publicPromotions->isNotEmpty())
<section class="gst-promos" id="promotions" aria-label="Special offers and promotions">
    <div class="gst-promos-inner">
        <div class="gst-section-heading">
            <span class="gst-section-eyebrow">Special Offers</span>
            <h2 class="gst-section-title">Promotions</h2>
            <p class="gst-section-sub">Limited-time deals and solar offers from RDY Solar Panel Installation.</p>
        </div>
        <div class="gst-promos-grid">
            @foreach ($publicPromotions as $promo)
                <article class="gst-promo-card">
                    @if ($promo->image_url)
                        <div class="gst-promo-card-media">
                            <img src="{{ $promo->image_url }}" alt="{{ $promo->title }} banner">
                        </div>
                    @else
                        <div class="gst-promo-card-media gst-promo-card-media--gradient">
                            <span class="gst-promo-no-img-icon" aria-hidden="true">
                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                            </span>
                        </div>
                    @endif
                    <div class="gst-promo-card-body">
                        <span class="gst-promo-card-tag">
                            @if ($promo->end_date)
                                Ends {{ $promo->end_date->format('M d, Y') }}
                            @else
                                Special Offer
                            @endif
                        </span>
                        <h3 class="gst-promo-card-title">{{ $promo->title }}</h3>
                        @if ($promo->description)
                            <p class="gst-promo-card-desc">{{ $promo->description }}</p>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
@endif

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
            <div class="gst-testimonies-view-all" style="text-align:left; margin-top: 28px;">
                <a href="{{ route('public.testimonies') }}">
                    View All Reviews
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                </a>
            </div>
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

{{-- APP DOWNLOAD --}}
<section class="gst-app" id="download-app" aria-label="Download app">
    <div class="gst-app-inner">
        <div class="gst-section-heading">
            <span class="gst-section-eyebrow">Mobile App</span>
            <h2 class="gst-section-title">Download Our Mobile App</h2>
            <p class="gst-section-sub">Install the app using one of the methods below:</p>
        </div>
        <div class="gst-app-shell">
            <article class="gst-app-card">
                <span class="gst-app-badge">Method 1</span>
                <h3 class="gst-app-title">QR Code</h3>
                <p class="gst-app-copy">Scan this QR code using your phone to download the app.</p>
                <div class="gst-app-qr-frame">
                    <img src="{{ asset('images/app-qr.png') }}" alt="QR code to download the SolMate mobile app APK">
                </div>
            </article>
            <aside class="gst-app-panel">
                <span class="gst-app-panel-tag">Method 2</span>
                <h3 class="gst-app-panel-title">Direct Download</h3>
                <p class="gst-app-panel-copy">Use this option if you are already browsing on your Android phone.</p>
                <div class="gst-app-panel-note">After downloading, open the APK file on your phone to install the SolMate app.</div>
                <div class="gst-hero-actions">
                    <a href="https://drive.google.com/file/d/1AZnWwNtpJDn7MQWgTo4BKrHEams2B_mh/view?usp=sharing" class="gst-cta-primary" target="_blank" rel="noopener">Download APK</a>
                </div>
            </aside>
        </div>
    </div>
</section>

{{-- FOOTER --}}
<footer class="solmate-footer" aria-label="Site footer">
    <div class="solmate-footer-inner">
        <div class="solmate-footer-upper">
            <div>
                <a href="{{ route('home') }}" class="solmate-footer-brand-link" aria-label="RDY home">
                    <img src="{{ asset('images/rdy-logo-transparent.png') }}" alt="RDY logo" class="gst-logo gst-logo--footer">
                </a>
                <p class="solmate-footer-desc">SolMate is a smart solar panel installation management system designed to streamline planning, monitoring, and deployment. We help installers, homeowners, and businesses transition to clean energy with efficiency and confidence.</p>
            </div>
            <div>
                <p class="solmate-footer-col-heading">Quick Links</p>
                <ul class="solmate-footer-links">
                    <li><a href="{{ route('home') }}">Home</a></li>
                    <li><a href="#about">About Us</a></li>
                    <li><a href="#services">Services</a></li>
                    <li><a href="#news">News</a></li>
                    <li><a href="#rdy">RDY</a></li>
                    <li><a href="{{ route('public.testimonies') }}">All Reviews</a></li>
                    <li><a href="{{ route('public.contact') }}">Contact Us</a></li>
                    <li><a href="#download-app">Download App</a></li>
                    <li><a href="{{ url('/privacy-policy') }}">Privacy Policy</a></li>
                    <li><a href="{{ route('login') }}">Log In</a></li>
                </ul>
            </div>
            <div>
                <p class="solmate-footer-col-heading">Services</p>
                <ul class="solmate-footer-links">
                    <li><a href="#services">Solar Installation</a></li>
                    <li><a href="#services">System Maintenance</a></li>
                    <li><a href="#services">Site Assessment</a></li>
                    <li><a href="#services">ROI &amp; Quotation Estimation</a></li>
                    <li><a href="{{ route('public.contact') }}">Consultation</a></li>
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
    var carousel = document.getElementById("landing-testimonies-carousel");
    if (!carousel) return;
    var prefersReducedMotion = window.matchMedia && window.matchMedia("(prefers-reduced-motion: reduce)").matches;
    var autoplayDelay = 3000;
    var activeIndex = 0;
    var autoplayTimer = null;
    var isPaused = false;
    var slides = [];
    var track = null;
    var dots = [];

    function escapeHtml(v) {
        return String(v == null ? "" : v)
            .replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;").replace(/'/g, "&#039;");
    }

    function normaliseSlides(items) {
        var imageSlides = [];
        if (!Array.isArray(items)) return imageSlides;

        items.forEach(function (item, index) {
            var src = item && (item.src || item.image_url || item.url);
            if (!src) return;

            imageSlides.push({
                type: "image",
                src: String(src),
                alt: item && item.alt
                    ? String(item.alt)
                    : "SolMate visual highlight " + (index + 1),
            });
        });

        return imageSlides;
    }

    function createButton(className, label, iconPath) {
        var button = document.createElement("button");
        button.type = "button";
        button.className = className;
        button.setAttribute("aria-label", label);
        button.innerHTML = "<svg width=\"18\" height=\"18\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2.4\" stroke-linecap=\"round\" stroke-linejoin=\"round\" aria-hidden=\"true\">" + iconPath + "</svg>";
        return button;
    }

    function createImageSlide(slide, index, total) {
        var item = document.createElement("article");
        item.className = "gst-testimony-slide";
        item.setAttribute("aria-label", "Gallery slide " + (index + 1) + " of " + total);
        item.innerHTML =
            "<img src=\"" + escapeHtml(slide.src) + "\" alt=\"" + escapeHtml(slide.alt) + "\" loading=\"" + (index === 0 ? "eager" : "lazy") + "\">"
            + "<div class=\"gst-testimony-slide-overlay\" aria-hidden=\"true\"></div>";
        return item;
    }

    function updateCarousel() {
        if (!track || slides.length === 0) return;
        track.style.transform = "translateX(-" + (activeIndex * 100) + "%)";
        dots.forEach(function (dot, index) {
            var isActive = index === activeIndex;
            dot.classList.toggle("is-active", isActive);
            dot.setAttribute("aria-current", isActive ? "true" : "false");
        });
    }

    function goTo(index) {
        if (slides.length === 0) return;
        activeIndex = (index + slides.length) % slides.length;
        updateCarousel();
    }

    function stopAutoplay() {
        if (autoplayTimer) {
            window.clearInterval(autoplayTimer);
            autoplayTimer = null;
        }
    }

    function startAutoplay() {
        stopAutoplay();
        if (prefersReducedMotion || slides.length <= 1) return;
        autoplayTimer = window.setInterval(function () {
            if (!isPaused) goTo(activeIndex + 1);
        }, autoplayDelay);
    }

    function attachPauseHandlers(element) {
        element.addEventListener("mouseenter", function () {
            isPaused = true;
        });
        element.addEventListener("mouseleave", function () {
            isPaused = false;
        });
        element.addEventListener("focusin", function () {
            isPaused = true;
        });
        element.addEventListener("focusout", function (event) {
            if (!element.contains(event.relatedTarget)) {
                isPaused = false;
            }
        });
    }

    function render(slideItems, sourceLabel) {
        var hasMultipleSlides;
        var controlMessage;
        var viewport;
        var controls;
        var dotsWrap;

        slides = Array.isArray(slideItems) ? slideItems : [];
        activeIndex = 0;
        dots = [];
        carousel.innerHTML = "";

        if (slides.length === 0) {
            carousel.innerHTML = "<div class=\"gst-testimonies-state-msg\"><strong>No visual highlights available yet.</strong>Please check back once new admin-managed images are published.</div>";
            return;
        }

        hasMultipleSlides = slides.length > 1;
        viewport = document.createElement("div");
        viewport.className = "gst-testimonies-viewport";

        track = document.createElement("div");
        track.className = "gst-testimonies-track";
        viewport.appendChild(track);

        slides.forEach(function (slide, index) {
            track.appendChild(createImageSlide(slide, index, slides.length));
        });

        if (hasMultipleSlides) {
            var prevButton = createButton("gst-testimonies-arrow gst-testimonies-arrow-prev", "Previous slide", "<path d=\"m15 18-6-6 6-6\"/>");
            var nextButton = createButton("gst-testimonies-arrow gst-testimonies-arrow-next", "Next slide", "<path d=\"m9 18 6-6-6-6\"/>");

            prevButton.addEventListener("click", function () {
                goTo(activeIndex - 1);
            });
            nextButton.addEventListener("click", function () {
                goTo(activeIndex + 1);
            });

            viewport.appendChild(prevButton);
            viewport.appendChild(nextButton);
        }

        carousel.appendChild(viewport);

        controls = document.createElement("div");
        controls.className = "gst-testimonies-controls";
        controls.innerHTML = "";

        dotsWrap = document.createElement("div");
        dotsWrap.className = "gst-testimonies-dots";

        if (hasMultipleSlides) {
            slides.forEach(function (_, index) {
                var dot = document.createElement("button");
                dot.type = "button";
                dot.className = "gst-testimonies-dot";
                dot.setAttribute("aria-label", "Go to slide " + (index + 1));
                dot.addEventListener("click", function () {
                    goTo(index);
                });
                dots.push(dot);
                dotsWrap.appendChild(dot);
            });
        }

        controls.appendChild(dotsWrap);
        carousel.appendChild(controls);
        attachPauseHandlers(carousel);
        updateCarousel();
        startAutoplay();
    }

    fetch("/api/public/visual-highlights", {
        method: "GET",
        headers: { "Accept": "application/json", "X-Requested-With": "XMLHttpRequest" }
    }).then(function (res) {
        if (!res.ok) throw new Error("HTTP " + res.status);
        return res.json();
    }).then(function (payload) {
        render(normaliseSlides(payload && payload.data), "Admin-managed visual highlights");
    }).catch(function () {
        render([], "Admin-managed visual highlights");
    });
})();
</script>
</body>
</html>
