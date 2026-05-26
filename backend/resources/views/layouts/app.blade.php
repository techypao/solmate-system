@php
    $authUser = auth()->user();
    $isCustomerShell = $authUser && $authUser->role === \App\Models\User::ROLE_CUSTOMER;
    $isAdminShell = $authUser && in_array($authUser->role, [\App\Models\User::ROLE_ADMIN, \App\Models\User::ROLE_TECHNICIAN], true);
    $isAdminUser = $authUser && $authUser->role === \App\Models\User::ROLE_ADMIN;
    $isTechnicianUser = $authUser && $authUser->role === \App\Models\User::ROLE_TECHNICIAN;
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Solmate Web' }}</title>
    <style>
        :root {
            color-scheme: light;
            font-family: "Plus Jakarta Sans", "Segoe UI", sans-serif;
            line-height: 1.5;
            background: #f8fafc;
            color: #0f2f4a;
            --solmate-blue-900: #123a5a;
            --solmate-blue-800: #0f2f4a;
            --solmate-blue-700: #20a7c9;
            --solmate-blue-100: #eaf9fd;
            --solmate-blue-50: #f2fafd;
            --solmate-gold-500: #f4d000;
            --solmate-gold-400: #e6c200;
            --solmate-gold-100: #fff7cc;
            --solmate-cyan-400: #7ddff2;
            --solmate-cyan-500: #20a7c9;
            --solmate-surface: #ffffff;
            --solmate-surface-muted: #f8fafc;
            --solmate-border: #dde7ee;
            --solmate-border-strong: #cbdbe5;
            --solmate-text: #0f2f4a;
            --solmate-copy: #5f7387;
            --solmate-shadow: 0 18px 42px rgba(18, 58, 90, 0.08);
            --solmate-shadow-soft: 0 8px 24px rgba(18, 58, 90, 0.06);
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background:
                radial-gradient(circle at top left, rgba(125, 223, 242, 0.18), transparent 24%),
                radial-gradient(circle at top right, rgba(244, 208, 0, 0.12), transparent 22%),
                linear-gradient(180deg, #ffffff 0%, #f8fafc 30%, #f8fafc 100%);
            color: var(--solmate-text);
        }

        a {
            color: var(--solmate-cyan-500);
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        .shell {
            max-width: 1120px;
            margin: 0 auto;
            padding: 28px 18px 56px;
        }

        .solmate-admin-shell .shell {
            max-width: 1280px;
            padding-top: 30px;
        }

        .nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            margin-bottom: 24px;
            padding: 16px 20px;
            background: #ffffff;
            border: 1px solid var(--solmate-border);
            border-radius: 12px;
            box-shadow: var(--solmate-shadow-soft);
        }

        .nav-links {
            display: flex;
            gap: 16px;
            align-items: center;
            flex-wrap: wrap;
        }

        .nav-link-with-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            white-space: nowrap;
        }

        .notification-count-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 20px;
            height: 20px;
            padding: 0 6px;
            border-radius: 999px;
            background: #cf4a4a;
            color: #ffffff;
            font-size: 11px;
            font-weight: 700;
            line-height: 1;
        }

        .brand {
            font-weight: 700;
            color: var(--solmate-blue-900);
        }

        .card {
            background: var(--solmate-surface);
            border: 1px solid var(--solmate-border);
            border-radius: 20px;
            padding: 24px;
            box-shadow: var(--solmate-shadow-soft);
            overflow: hidden;
        }

        .card + .card {
            margin-top: 20px;
        }

        .solmate-admin-shell .card {
            border-radius: 22px;
            border-color: var(--solmate-border);
            box-shadow: var(--solmate-shadow);
        }

        .narrow {
            max-width: 520px;
            margin: 48px auto 0;
        }

        .page-title {
            margin: 0 0 10px;
            font-size: 30px;
            line-height: 1.15;
            letter-spacing: -0.03em;
            font-weight: 800;
            color: var(--solmate-blue-900);
        }

        .solmate-admin-shell .page-title {
            margin-bottom: 12px;
            font-size: 32px;
        }

        .page-copy {
            margin: 0 0 20px;
            color: var(--solmate-copy);
            font-size: 15px;
            line-height: 1.7;
            max-width: 760px;
        }

        .solmate-admin-shell .page-copy {
            margin-bottom: 22px;
        }

        .status,
        .error-box,
        .info-box {
            margin-bottom: 16px;
            padding: 14px 16px;
            border-radius: 14px;
            font-size: 14px;
            line-height: 1.6;
            box-shadow: 0 4px 14px rgba(15, 23, 42, 0.03);
        }

        .status {
            background: var(--solmate-blue-100);
            color: var(--solmate-blue-900);
            border: 1px solid rgba(32, 167, 201, 0.28);
        }

        .error-box {
            background: #fff1f2;
            color: #8a1c1c;
            border: 1px solid #fecdd3;
        }

        .info-box {
            background: #eef9fd;
            color: var(--solmate-blue-900);
            border: 1px solid rgba(125, 223, 242, 0.5);
        }

        .solmate-toast {
            position: fixed;
            top: 24px;
            right: 24px;
            z-index: 1200;
            display: grid;
            gap: 8px;
            width: min(100% - 32px, 360px);
            padding: 18px 20px 18px 24px;
            border: 1px solid rgba(125, 223, 242, 0.42);
            border-radius: 22px;
            background: linear-gradient(145deg, rgba(18, 58, 90, 0.98), rgba(15, 47, 74, 0.96));
            box-shadow: 0 22px 52px rgba(18, 58, 90, 0.24);
            color: #ffffff;
            opacity: 0;
            pointer-events: none;
            transform: translateY(-12px);
            transition: opacity 0.24s ease, transform 0.24s ease;
        }

        .solmate-toast.is-visible {
            opacity: 1;
            transform: translateY(0);
        }

        .solmate-toast::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            bottom: 0;
            width: 5px;
            border-radius: 22px 0 0 22px;
            background: linear-gradient(180deg, var(--solmate-gold-500) 0%, var(--solmate-cyan-500) 100%);
        }

        .solmate-toast-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            width: fit-content;
            padding: 6px 10px;
            border-radius: 999px;
            background: rgba(125, 223, 242, 0.16);
            color: var(--solmate-cyan-400);
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }

        .solmate-toast-title {
            margin: 0;
            font-size: 16px;
            font-weight: 800;
            line-height: 1.45;
            color: #ffffff;
        }

        .solmate-toast-copy {
            margin: 0;
            font-size: 13px;
            line-height: 1.6;
            color: rgba(255, 255, 255, 0.8);
        }

        .form-grid {
            display: grid;
            gap: 18px;
        }

        .form-grid.two-columns {
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        }

        label {
            display: block;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            color: var(--solmate-copy);
            margin-bottom: 8px;
        }

        input {
            width: 100%;
            padding: 12px 14px;
            border: 1.5px solid var(--solmate-border-strong);
            border-radius: 12px;
            font-size: 14px;
            background: #fff;
            color: var(--solmate-text);
            transition: border-color 0.18s, box-shadow 0.18s, background 0.18s;
        }

        select,
        textarea {
            width: 100%;
            padding: 12px 14px;
            border: 1.5px solid var(--solmate-border-strong);
            border-radius: 12px;
            font-size: 14px;
            background: #fff;
            color: var(--solmate-text);
            transition: border-color 0.18s, box-shadow 0.18s, background 0.18s;
        }

        select {
            appearance: none;
            -webkit-appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2320a7c9' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 14px center;
            padding-right: 40px;
        }

        textarea {
            min-height: 112px;
            resize: vertical;
        }

        .solmate-admin-shell input:hover,
        .solmate-admin-shell select:hover,
        .solmate-admin-shell textarea:hover {
            border-color: rgba(32, 167, 201, 0.48);
        }

        .solmate-admin-shell input,
        .solmate-admin-shell select,
        .solmate-admin-shell textarea {
            background-color: var(--solmate-surface-muted);
        }

        input:focus {
            outline: none;
            border-color: var(--solmate-cyan-500);
            box-shadow: 0 0 0 4px rgba(32, 167, 201, 0.14);
        }

        select:focus,
        textarea:focus {
            outline: none;
            border-color: var(--solmate-cyan-500);
            box-shadow: 0 0 0 4px rgba(32, 167, 201, 0.14);
        }

        .password-field {
            position: relative;
        }

        .password-field input {
            padding-right: 92px;
        }

        .password-toggle {
            position: absolute;
            top: 37px;
            right: 12px;
            border: 0;
            background: transparent;
            color: var(--solmate-blue-800);
            font-size: 13px;
            font-weight: 700;
            padding: 0;
        }

        .remember-row {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .checkbox-inline {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin: 0;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            color: var(--solmate-blue-900);
            text-transform: none;
            letter-spacing: 0;
        }

        .checkbox-inline input {
            width: auto;
            margin: 0;
        }

        .field-error {
            margin-top: 6px;
            font-size: 13px;
            color: #b42318;
            min-height: 18px;
        }

        .actions {
            display: flex;
            gap: 12px;
            align-items: center;
            flex-wrap: wrap;
            margin-top: 10px;
        }

        button,
        .button-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 11px 18px;
            border-radius: 12px;
            border: 1px solid transparent;
            background: linear-gradient(135deg, var(--solmate-gold-500), var(--solmate-gold-400));
            color: var(--solmate-text);
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 10px 24px rgba(244, 208, 0, 0.22);
            transition: transform 0.16s, box-shadow 0.16s, opacity 0.16s, background 0.16s, color 0.16s, border-color 0.16s;
            text-decoration: none;
        }

        button.secondary,
        .button-link.secondary {
            background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
            color: var(--solmate-cyan-500);
            border-color: rgba(32, 167, 201, 0.28);
            box-shadow: 0 8px 18px rgba(18, 58, 90, 0.05);
        }

        button.neutral,
        .button-link.neutral {
            background: #f8fafc;
            color: var(--solmate-blue-800);
            border-color: var(--solmate-border);
            box-shadow: none;
        }

        button.danger,
        .button-link.danger {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: #ffffff;
            border-color: transparent;
            box-shadow: 0 10px 24px rgba(220, 38, 38, 0.18);
        }

        button:hover,
        .button-link:hover {
            transform: translateY(-1px);
            opacity: 0.97;
            text-decoration: none;
        }

        button.secondary:hover,
        .button-link.secondary:hover,
        button.neutral:hover,
        .button-link.neutral:hover {
            color: var(--solmate-blue-900);
            border-color: rgba(32, 167, 201, 0.44);
            background: #ffffff;
        }

        button:focus-visible,
        .button-link:focus-visible {
            outline: none;
            box-shadow: 0 0 0 4px rgba(32, 167, 201, 0.16);
        }

        button[disabled] {
            opacity: 0.6;
            cursor: wait;
            transform: none;
        }

        .muted {
            color: #5E7288;
            font-size: 14px;
            line-height: 1.65;
        }

        .stack {
            display: grid;
            gap: 14px;
        }

        .admin-page-stack {
            display: grid;
            gap: 24px;
        }

        .admin-hero-card {
            position: relative;
            overflow: hidden;
            background: linear-gradient(135deg, #ffffff 0%, #f2fafd 55%, #eefbff 100%);
            border-color: var(--solmate-border);
        }

        .admin-hero-card::after {
            content: '';
            position: absolute;
            right: -48px;
            top: -44px;
            width: 220px;
            height: 220px;
            border-radius: 50%;
            background: rgba(244, 208, 0, 0.12);
            pointer-events: none;
        }

        .admin-hero-card > * {
            position: relative;
            z-index: 1;
        }

        .admin-page-eyebrow {
            margin: 0 0 8px;
            color: var(--solmate-gold-500);
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }

        .admin-section-title {
            margin: 0;
            font-size: 21px;
            line-height: 1.2;
            color: var(--solmate-blue-900);
            font-weight: 800;
        }

        .admin-section-copy {
            margin: 6px 0 0;
            color: var(--solmate-copy);
            font-size: 14px;
            line-height: 1.65;
        }

        .admin-section-surface {
            background: linear-gradient(180deg, #ffffff 0%, #F8FAFC 100%);
        }

        .admin-inline-actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            align-items: center;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 16px;
            flex-wrap: wrap;
            margin-bottom: 22px;
        }

        .section-header > div:first-child {
            min-width: 0;
            flex: 1;
        }

        .summary-grid {
            display: grid;
            gap: 14px;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        }

        .summary-card {
            padding: 18px;
            border: 1px solid var(--solmate-border);
            border-radius: 16px;
            background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
            box-shadow: 0 8px 24px rgba(18, 58, 90, 0.05);
            position: relative;
        }

        .summary-card::before {
            content: '';
            position: absolute;
            inset: 0 auto 0 0;
            width: 4px;
            border-radius: 16px 0 0 16px;
            background: linear-gradient(180deg, var(--solmate-gold-500), var(--solmate-cyan-500));
            opacity: 0.9;
        }

        .summary-label {
            color: var(--solmate-copy);
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            margin-bottom: 10px;
        }

        .summary-value {
            color: var(--solmate-text);
            font-size: 30px;
            font-weight: 800;
            line-height: 1;
        }

        .request-list {
            display: grid;
            gap: 16px;
        }

        .request-card {
            padding: 20px;
            border: 1px solid var(--solmate-border);
            border-radius: 18px;
            background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
            box-shadow: 0 10px 28px rgba(18, 58, 90, 0.05);
            scroll-margin-top: 24px;
        }

        .request-card:target {
            border-color: rgba(32, 167, 201, 0.5);
            box-shadow: 0 0 0 4px rgba(32, 167, 201, 0.16);
        }

        .request-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 16px;
        }

        .request-title {
            color: #123A5A;
            font-size: 18px;
            font-weight: 800;
        }

        .request-badges {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .detail-grid {
            display: grid;
            gap: 12px;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        }

        .detail-item {
            padding: 14px 15px;
            border: 1px solid #DDE7EE;
            border-radius: 14px;
            background: #ffffff;
        }

        .detail-label {
            display: block;
            margin-bottom: 4px;
            color: var(--solmate-copy);
            font-size: 10px;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .assignment-row {
            display: grid;
            gap: 12px;
            grid-template-columns: minmax(0, 1fr) auto;
            align-items: end;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 5px 11px;
            border-radius: 999px;
            border: 1px solid transparent;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.04em;
            text-transform: capitalize;
        }

        .badge-neutral {
            background: #f8fafc;
            color: #5E7288;
            border-color: #dbe4ee;
        }

        .badge-warning {
            background: #FFF7CC;
            color: #a16207;
            border-color: #fde68a;
        }

        .badge-info {
            background: #EAF9FD;
            color: var(--solmate-cyan-500);
            border-color: rgba(125, 223, 242, 0.7);
        }

        .badge-primary {
            background: #EAF9FD;
            color: var(--solmate-cyan-500);
            border-color: rgba(32, 167, 201, 0.4);
        }

        .badge-success {
            background: #dcfce7;
            color: #15803d;
            border-color: #bbf7d0;
        }

        .badge-danger {
            background: #fee2e2;
            color: #dc2626;
            border-color: #fecaca;
        }

        .list-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            padding: 14px 15px;
            border: 1px solid #DDE7EE;
            border-radius: 14px;
            background: #ffffff;
        }

        .solmate-admin-shell table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background: #ffffff;
            border: 1px solid #DDE7EE;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.04);
        }

        .solmate-admin-shell th,
        .solmate-admin-shell td {
            padding: 14px 16px;
            border-bottom: 1px solid #DDE7EE;
            text-align: left;
            vertical-align: top;
        }

        .solmate-admin-shell th {
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #5E7288;
            background: #F8FAFC;
        }

        .solmate-admin-shell thead th:first-child {
            border-top-left-radius: 18px;
        }

        .solmate-admin-shell thead th:last-child {
            border-top-right-radius: 18px;
        }

        .solmate-admin-shell tbody tr:nth-child(even) td {
            background: #f8fafc;
        }

        .solmate-admin-shell tbody tr:hover td {
            background: #F8FAFC;
        }

        .solmate-admin-shell tbody tr:last-child td {
            border-bottom: none;
        }

        @media (max-width: 720px) {
            .assignment-row {
                grid-template-columns: 1fr;
            }

            .solmate-toast {
                top: 16px;
                right: 16px;
                left: 16px;
                width: auto;
            }
        }

        /* ===== CUSTOMER HEADER ===== */
        .solmate-nav {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 28px;
            height: 68px;
            background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
            border-radius: 18px;
            margin-bottom: 24px;
            box-shadow: 0 12px 30px rgba(18, 58, 90, 0.08);
            border: 1px solid rgba(32, 167, 201, 0.16);
        }

        .solmate-nav-left {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 160px;
        }

        .solmate-hamburger {
            background: rgba(125, 223, 242, 0.12);
            border: 1px solid rgba(32, 167, 201, 0.12);
            padding: 7px 8px;
            cursor: pointer;
            color: var(--solmate-blue-900);
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            line-height: 0;
            transition: background 0.15s, border-color 0.15s, color 0.15s;
        }

        .solmate-hamburger:hover {
            background: rgba(125, 223, 242, 0.2);
            border-color: rgba(32, 167, 201, 0.24);
            color: var(--solmate-blue-900);
        }

        .solmate-brand-link {
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            line-height: 0;
        }

        .solmate-brand-link:hover {
            text-decoration: none;
        }

        .solmate-logo {
            display: block;
            width: auto;
            max-width: 100%;
            height: auto;
        }

        .solmate-logo--nav {
            height: 40px;
        }

        .solmate-logo--sidebar {
            height: 42px;
        }

        .solmate-logo--footer {
            height: 50px;
        }

        .solmate-nav-center {
            display: flex;
            align-items: center;
            gap: 36px;
        }

        .solmate-nav-center--admin {
            gap: 10px;
            flex-wrap: wrap;
            justify-content: center;
            flex: 1;
        }

        .solmate-nav--admin {
            min-height: 82px;
            height: auto;
            padding: 18px 22px;
            background: linear-gradient(135deg, #ffffff 0%, #f2fafd 56%, #eefbff 100%);
            border: 1px solid rgba(32, 167, 201, 0.18);
            position: relative;
            overflow: hidden;
        }

        .solmate-nav--admin::after {
            content: '';
            position: absolute;
            right: -40px;
            top: -40px;
            width: 160px;
            height: 160px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(125, 223, 242, 0.22) 0%, rgba(244, 208, 0, 0.1) 74%);
            pointer-events: none;
        }

        .solmate-nav--admin > * {
            position: relative;
            z-index: 1;
        }

        .solmate-brand-stack {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .solmate-admin-kicker {
            display: inline-flex;
            align-items: center;
            width: fit-content;
            padding: 4px 10px;
            border-radius: 999px;
            background: rgba(125, 223, 242, 0.14);
            color: var(--solmate-blue-900);
            border: 1px solid rgba(32, 167, 201, 0.14);
            font-size: 10px;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .solmate-nav-link-with-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .solmate-nav-link {
            font-size: 14px;
            font-weight: 600;
            color: var(--solmate-copy);
            text-decoration: none;
            padding: 4px 0;
            border-bottom: 2px solid transparent;
            transition: color 0.15s, border-color 0.15s;
            white-space: nowrap;
        }

        .solmate-nav--admin .solmate-nav-link {
            padding: 10px 14px;
            border: 1px solid transparent;
            border-radius: 999px;
            border-bottom-width: 1px;
            background: rgba(255, 255, 255, 0.78);
            color: var(--solmate-blue-800);
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.18);
        }

        .solmate-nav-link:hover {
            color: #123A5A;
            text-decoration: none;
            border-bottom-color: var(--solmate-cyan-500);
        }

        .solmate-nav--admin .solmate-nav-link:hover {
            border-bottom-color: transparent;
            border-color: rgba(32, 167, 201, 0.24);
            background: #ffffff;
        }

        .solmate-nav-link.active {
            color: #123A5A;
            font-weight: 600;
            border-bottom-color: var(--solmate-gold-500);
        }

        .solmate-nav--admin .solmate-nav-link.active {
            border-color: rgba(18, 58, 90, 0.08);
            background: linear-gradient(135deg, #123A5A, #20A7C9);
            color: #ffffff;
            box-shadow: 0 12px 24px rgba(18, 58, 90, 0.18);
        }

        .solmate-nav-right {
            display: flex;
            align-items: center;
            min-width: 160px;
            justify-content: flex-end;
            gap: 10px;
        }

        .solmate-admin-nav-actions {
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .solmate-admin-logout-form {
            margin: 0;
        }

        .solmate-admin-logout-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 40px;
            padding: 10px 14px;
            border-radius: 999px;
            border: 1px solid rgba(32, 167, 201, 0.14);
            background: rgba(255, 255, 255, 0.92);
            color: var(--solmate-blue-800);
            font-size: 13px;
            font-weight: 700;
            box-shadow: 0 8px 18px rgba(15, 23, 42, 0.05);
        }

        .solmate-admin-logout-btn:hover {
            background: #ffffff;
            color: var(--solmate-blue-900);
            border-color: rgba(32, 167, 201, 0.24);
        }

        .solmate-profile-wrapper {
            position: relative;
        }

        .solmate-profile-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--solmate-blue-900);
            border: 2px solid rgba(244, 208, 0, 0.52);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0;
            transition: background 0.15s, transform 0.1s;
            color: #ffffff;
            line-height: 0;
            overflow: hidden;
        }

        .solmate-profile-btn:hover {
            background: var(--solmate-cyan-500);
            transform: scale(1.04);
        }

        .solmate-profile-btn.has-image {
            background: #ffffff;
            border: 2px solid var(--solmate-cyan-400);
        }

        .solmate-profile-btn-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .solmate-profile-btn-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
        }

        .solmate-profile-dropdown {
            display: none;
            position: absolute;
            right: 0;
            top: calc(100% + 10px);
            background: #ffffff;
            border: 1px solid #DDE7EE;
            border-radius: 14px;
            box-shadow: 0 20px 40px rgba(15, 23, 42, 0.14);
            min-width: 200px;
            z-index: 200;
            overflow: hidden;
        }

        .solmate-profile-dropdown.open {
            display: block;
        }

        .solmate-profile-dropdown-header {
            padding: 14px 16px;
            border-bottom: 1px solid var(--solmate-border);
            background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        }

        .solmate-profile-dropdown-name {
            font-weight: 600;
            font-size: 14px;
            color: var(--solmate-text);
            margin: 0 0 2px;
        }

        .solmate-profile-dropdown-email {
            font-size: 12px;
            color: var(--solmate-copy);
            margin: 0;
        }

        .solmate-profile-dropdown-actions {
            padding: 8px;
        }

        .solmate-logout-btn {
            width: 100%;
            text-align: left;
            background: none;
            border: none;
            padding: 9px 10px;
            font-size: 14px;
            color: var(--solmate-blue-900);
            cursor: pointer;
            border-radius: 6px;
            display: block;
            transition: background 0.12s;
        }

        .solmate-logout-btn:hover {
            background: var(--solmate-blue-100);
            color: var(--solmate-blue-900);
        }

        /* ===== SERVICES DROPDOWN ===== */
        .solmate-services-wrapper {
            position: relative;
        }

        .solmate-services-trigger {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 14px;
            font-weight: 500;
            color: var(--solmate-copy);
            background: none;
            border: none;
            padding: 4px 0;
            border-bottom: 2px solid transparent;
            cursor: pointer;
            transition: color 0.15s, border-color 0.15s;
            white-space: nowrap;
            line-height: inherit;
        }

        .solmate-services-trigger:hover {
            color: #123A5A;
            border-bottom-color: var(--solmate-cyan-500);
        }

        .solmate-services-trigger.active {
            color: #123A5A;
            font-weight: 600;
            border-bottom-color: var(--solmate-gold-500);
        }

        .solmate-services-chevron {
            transition: transform 0.18s ease;
            flex-shrink: 0;
            color: #7F92A3;
        }

        .solmate-services-trigger[aria-expanded="true"] .solmate-services-chevron {
            transform: rotate(180deg);
        }

        .solmate-services-trigger[aria-expanded="true"] .solmate-services-chevron {
            color: #F4D000;
        }

        .solmate-services-dropdown {
            display: none;
            position: absolute;
            top: calc(100% + 14px);
            left: 50%;
            transform: translateX(-50%);
            background: #ffffff;
            border: 1px solid #DDE7EE;
            border-radius: 12px;
            box-shadow: 0 18px 34px rgba(18, 58, 90, 0.12);
            min-width: 188px;
            z-index: 300;
            overflow: hidden;
            padding: 6px;
        }

        .solmate-services-dropdown.open {
            display: block;
        }

        .solmate-services-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            color: var(--solmate-blue-900);
            text-decoration: none;
            transition: background 0.12s, color 0.12s, border-color 0.12s;
            white-space: nowrap;
            border: 1px solid transparent;
        }

        .solmate-services-item:hover {
            background: var(--solmate-blue-100);
            color: #123A5A;
            text-decoration: none;
        }

        .solmate-services-item.active {
            color: #123A5A;
            font-weight: 600;
            background: linear-gradient(180deg, #fff7cc 0%, #fffdf2 100%);
            border-color: rgba(244, 208, 0, 0.28);
        }

        .solmate-services-item-icon {
            width: 28px;
            height: 28px;
            border-radius: 7px;
            background: linear-gradient(135deg, #123A5A, #20A7C9);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .solmate-services-item-soon {
            margin-left: auto;
            font-size: 10px;
            font-weight: 700;
            color: var(--solmate-cyan-500);
            background: rgba(125, 223, 242, 0.16);
            border-radius: 4px;
            padding: 1px 6px;
            letter-spacing: .3px;
            text-transform: uppercase;
        }

        @media (max-width: 680px) {
            .solmate-nav-center {
                display: none;
            }

            .solmate-nav {
                padding: 0 16px;
            }

            .solmate-admin-nav-actions {
                display: none;
            }
        }

        .admin-main {
            display: grid;
            gap: 24px;
        }

        .solmate-admin-shell .solmate-footer {
            margin-top: 72px;
        }

        /* ===== FOOTER ===== */
        .solmate-footer {
            background: linear-gradient(180deg, #3A7EA6 0%, #2A6B92 42%, #1C5476 100%);
            color: #DDE7EE;
            margin-top: 48px;
            font-family: Arial, sans-serif;
            border-top: 1px solid rgba(125, 223, 242, 0.28);
        }

        .solmate-footer-inner {
            max-width: 1200px;
            margin: 0 auto;
            padding: 56px 32px 0;
        }

        .solmate-footer-upper {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 0.6fr;
            gap: 48px;
            padding-bottom: 48px;
        }

        .solmate-footer-brand-link {
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            margin-bottom: 16px;
            line-height: 0;
        }

        .solmate-footer-brand-link:hover {
            text-decoration: none;
        }

        .solmate-footer-desc {
            font-size: 13.5px;
            line-height: 1.75;
            color: rgba(255, 255, 255, 0.84);
            max-width: 300px;
            margin: 0;
        }

        .solmate-footer-col-heading {
            font-size: 13px;
            font-weight: 700;
            color: #DDE7EE;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            margin: 0 0 18px;
        }

        .solmate-footer-links {
            list-style: none;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            gap: 11px;
        }

        .solmate-footer-links a {
            font-size: 13.5px;
            color: rgba(255, 255, 255, 0.88);
            text-decoration: none;
            transition: color 0.15s;
        }

        .solmate-footer-links a:hover {
            color: #7DDFF2;
            text-decoration: none;
        }

        .solmate-footer-socials {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .solmate-footer-social-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.22);
            color: #DDE7EE;
            text-decoration: none;
            transition: background 0.15s, color 0.15s;
            line-height: 0;
        }

        .solmate-footer-social-btn:hover {
            background: #F4D000;
            color: #0F2F4A;
            text-decoration: none;
        }

        .solmate-footer-divider {
            border: none;
            border-top: 1px solid rgba(255, 255, 255, 0.16);
            margin: 0;
        }

        .solmate-footer-bottom {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 24px;
            flex-wrap: wrap;
        }

        .solmate-footer-copyright {
            font-size: 12.5px;
            color: rgba(234, 249, 253, 0.8);
            margin: 0;
            line-height: 1.5;
        }

        .solmate-footer-contact-items {
            display: flex;
            align-items: center;
            gap: 32px;
            flex-wrap: wrap;
        }

        .solmate-footer-contact-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12.5px;
            color: rgba(234, 249, 253, 0.86);
        }

        .solmate-footer-contact-item svg {
            flex-shrink: 0;
            color: var(--solmate-cyan-400);
        }

        @media (max-width: 900px) {
            .solmate-footer-upper {
                grid-template-columns: 1fr 1fr;
                gap: 36px;
            }
        }

        @media (max-width: 560px) {
            .solmate-footer-upper {
                grid-template-columns: 1fr;
                gap: 28px;
            }

            .solmate-footer-inner {
                padding: 40px 20px 0;
            }

            .solmate-footer-bottom {
                flex-direction: column;
                align-items: flex-start;
                padding: 20px;
                gap: 14px;
            }

            .solmate-footer-contact-items {
                gap: 16px;
            }
        }

        /* ===== ADMIN SIDEBAR LAYOUT ===== */
        .solmate-admin-shell {
            background: #f8fafc;
            overflow-x: hidden; /* prevent page-level horizontal scroll */
        }

        .admin-layout {
            display: flex;
            position: relative;
        }

        /* Sidebar - light theme */
        .admin-sidebar {
            width: 220px;
            min-width: 220px;
            background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            z-index: 100;
            overflow-y: auto;
            transition: transform 0.25s ease;
            border-right: 1px solid #DDE7EE;
            box-shadow: 4px 0 24px rgba(18, 58, 90, 0.06);
        }

        .admin-sidebar.sidebar-hidden {
            transform: translateX(-220px);
        }

        /* Sidebar brand area */
        .admin-sidebar-brand {
            padding: 20px 18px 16px;
            border-bottom: 1px solid var(--solmate-border);
            flex-shrink: 0;
        }

        .admin-sidebar-brand .solmate-logo--sidebar {
            filter: drop-shadow(0 10px 20px rgba(15, 47, 74, 0.16));
        }

        .admin-sidebar-kicker {
            display: block;
            margin-top: 6px;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.09em;
            text-transform: uppercase;
            color: var(--solmate-copy);
        }

        /* Sidebar navigation */
        .admin-sidebar-nav {
            flex: 1;
            padding: 12px 10px;
            display: flex;
            flex-direction: column;
            gap: 2px;
            overflow-y: auto;
        }

        .admin-sidebar-nav-section {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #7F92A3;
            padding: 10px 12px 4px;
        }

        .admin-sidebar-divider {
            height: 1px;
            background: #E4EBF0;
            margin: 6px 12px;
        }

        .admin-sidebar-link {
            display: flex;
            align-items: center;
            gap: 9px;
            padding: 9px 12px;
            border-radius: 9px;
            font-size: 13.5px;
            font-weight: 500;
            color: #5E7288;
            text-decoration: none;
            transition: background 0.14s, color 0.14s, box-shadow 0.14s;
            white-space: nowrap;
        }

        .admin-sidebar-link:hover {
            background: rgba(125, 223, 242, 0.16);
            color: #123A5A;
            text-decoration: none;
        }

        .admin-sidebar-link.active {
            background: linear-gradient(135deg, #fff7cc 0%, #F4D000 100%);
            color: #0F2F4A;
            font-weight: 700;
            border-radius: 10px;
            box-shadow: inset 3px 0 0 #20A7C9, 0 10px 18px rgba(244, 208, 0, 0.18);
        }

        .admin-sidebar-link.disabled {
            opacity: 0.45;
            cursor: default;
            pointer-events: none;
        }

        .admin-sidebar-link .nav-icon {
            flex-shrink: 0;
            width: 16px;
            height: 16px;
            opacity: 0.55;
            color: currentColor;
        }

        .admin-sidebar-link.active .nav-icon {
            opacity: 1;
        }

        .admin-sidebar-link:hover .nav-icon {
            opacity: 0.8;
        }

        .admin-sidebar-link-badge {
            margin-left: auto;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 19px;
            height: 19px;
            padding: 0 5px;
            border-radius: 999px;
            background: #d64545;
            color: #ffffff;
            font-size: 10px;
            font-weight: 700;
            line-height: 1;
        }

        /* Sidebar footer */
        .admin-sidebar-foot {
            padding: 10px 10px 16px;
            border-top: 1px solid #f1f5f9;
            flex-shrink: 0;
        }

        .admin-sidebar-logout-btn {
            display: flex;
            align-items: center;
            gap: 9px;
            width: 100%;
            padding: 9px 12px;
            border-radius: 9px;
            border: 1px solid #DDE7EE;
            background: #f8fafc;
            color: #5E7288;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            text-align: left;
            transition: background 0.14s, color 0.14s;
            box-shadow: none;
        }

        .admin-sidebar-logout-btn:hover {
            background: #f1f5f9;
            color: #123A5A;
        }

        /* Admin main area (content beside sidebar) */
        .admin-main-area {
            flex: 1;
            min-width: 0; /* allow flex child to shrink below content width */
            overflow-x: hidden; /* contain any overflowing children */
            margin-left: 220px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background: #dce8f5;
            transition: margin-left 0.25s ease;
        }

        .admin-main-area.sidebar-collapsed {
            margin-left: 0;
        }

        /* Simplified admin topbar */
        .admin-topbar {
            position: sticky;
            top: 0;
            z-index: 50;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 0 24px;
            height: 58px;
            background: #ffffff;
            border-bottom: 1px solid #e8eff7;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
            flex-shrink: 0;
        }

        .admin-topbar-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .admin-topbar-toggle {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 34px;
            height: 34px;
            border-radius: 8px;
            border: 1px solid #DDE7EE;
            background: #f8fafc;
            color: #5E7288;
            cursor: pointer;
            transition: background 0.14s, color 0.14s;
            padding: 0;
            line-height: 0;
        }

        .admin-topbar-toggle:hover {
            background: #f1f5f9;
            color: #123A5A;
        }

        .admin-topbar-brand {
            display: none;
        }

        .admin-topbar-right {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* Admin content wrapper */
        .admin-content-shell {
            flex: 1;
            padding: 24px 24px 52px;
            width: 100%;
            max-width: 100%; /* do not exceed the column given by margin-left */
            box-sizing: border-box;
            overflow-x: hidden;
        }

        /* Hide site footer inside admin workspace */
        .solmate-admin-shell > .solmate-footer {
            display: none;
        }

        /* Sidebar overlay (mobile) */
        .admin-sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.45);
            z-index: 99;
        }

        .admin-sidebar-overlay.open {
            display: block;
        }

        @media (max-width: 900px) {
            .admin-sidebar {
                transform: translateX(-220px);
            }

            .admin-sidebar.open {
                transform: translateX(0);
            }

            .admin-main-area {
                margin-left: 0;
            }

            .admin-topbar-brand {
                display: flex;
                align-items: baseline;
                gap: 0;
            }
        }
    </style>
</head>
<body class="{{ $isAdminShell ? 'solmate-admin-shell' : 'solmate-site-shell' }} {{ $isAdminUser ? 'solmate-role-admin' : '' }} {{ $isTechnicianUser ? 'solmate-role-technician' : '' }}">

@php
    $authToastTitle = session('login_success') ?? session('logout_success');
    $authToastCopy = session('login_success')
        ? 'Welcome to SolMate. Your account is ready.'
        : (session('logout_success') ? 'You have safely signed out of your account.' : null);
@endphp

@if ($authToastTitle)
    <div class="solmate-toast" id="solmate-auth-toast" role="status" aria-live="polite">
        <span class="solmate-toast-badge">Success</span>
        <p class="solmate-toast-title">{{ $authToastTitle }}</p>
        <p class="solmate-toast-copy">{{ $authToastCopy }}</p>
    </div>
@endif

@if ($isAdminShell)
    {{-- ===== ADMIN LAYOUT: SIDEBAR + MAIN AREA ===== --}}

    {{-- Mobile overlay --}}
    <div class="admin-sidebar-overlay" id="adminSidebarOverlay" aria-hidden="true"></div>

    {{-- LEFT SIDEBAR --}}
    <aside class="admin-sidebar" id="adminSidebar" aria-label="Admin navigation">

        {{-- Brand area --}}
        <div class="admin-sidebar-brand">
            <a href="{{ route('dashboard') }}" class="solmate-brand-link" aria-label="RDY home">
                <img src="{{ asset('images/rdy-logo-transparent.png') }}" alt="RDY logo" class="solmate-logo solmate-logo--sidebar">
            </a>
            @auth
                <span class="admin-sidebar-kicker">{{ $isAdminUser ? 'Admin Workspace' : 'Technician Workspace' }}</span>
            @endauth
        </div>

        {{-- Navigation items --}}
        @auth
        <nav class="admin-sidebar-nav" aria-label="Admin menu">

            {{-- Dashboard --}}
            <a href="{{ route('dashboard') }}"
               class="admin-sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
                    <rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>
                </svg>
                Dashboard
            </a>

            @if (auth()->user()->role === \App\Models\User::ROLE_ADMIN)

                <div class="admin-sidebar-divider"></div>
                <span class="admin-sidebar-nav-section">People</span>

                {{-- Customers --}}
                <a href="{{ route('admin.customers') }}"
                   class="admin-sidebar-link {{ request()->routeIs('admin.customers') ? 'active' : '' }}">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                    Customers
                </a>

                {{-- Technician --}}
                <a href="{{ route('admin.technicians.create') }}"
                   class="admin-sidebar-link {{ request()->routeIs('admin.technicians.create') ? 'active' : '' }}">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/>
                        <line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/>
                    </svg>
                    Technician
                </a>

                <div class="admin-sidebar-divider"></div>
                <span class="admin-sidebar-nav-section">Operations</span>

                {{-- Services --}}
                     <a href="{{ route('admin.request-assignments') }}"
                   class="admin-sidebar-link {{ request()->routeIs('admin.request-assignments') ? 'active' : '' }}">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="12" cy="12" r="3"/>
                        <path d="M19.07 4.93l-1.41 1.41M4.93 4.93l1.41 1.41M19.07 19.07l-1.41-1.41M4.93 19.07l1.41-1.41M12 2v2M12 20v2M2 12h2M20 12h2"/>
                    </svg>
                    Services
                </a>

                {{-- Quotations --}}
                <a href="{{ route('quotations.item-builder') }}"
                   class="admin-sidebar-link {{ request()->routeIs('quotations.item-builder') ? 'active' : '' }}">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                        <line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>
                    </svg>
                    Quotations
                </a>

                {{-- Pricing Management --}}
                <a href="{{ route('admin.pricing-catalog') }}"
                   class="admin-sidebar-link {{ request()->routeIs('admin.pricing-catalog') ? 'active' : '' }}">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <line x1="8" y1="6" x2="21" y2="6"/>
                        <line x1="8" y1="12" x2="21" y2="12"/>
                        <line x1="8" y1="18" x2="21" y2="18"/>
                        <line x1="3" y1="6" x2="3.01" y2="6"/>
                        <line x1="3" y1="12" x2="3.01" y2="12"/>
                        <line x1="3" y1="18" x2="3.01" y2="18"/>
                    </svg>
                    Pricing Management
                </a>

                <div class="admin-sidebar-divider"></div>
                <span class="admin-sidebar-nav-section">Engagement</span>

                {{-- Testimonies --}}
                <a href="{{ route('admin.testimonies') }}"
                   class="admin-sidebar-link {{ request()->routeIs('admin.testimonies') ? 'active' : '' }}">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                    </svg>
                    Testimonies
                </a>

                {{-- Notifications with badge --}}
                <a href="{{ route('admin.notifications') }}"
                   class="admin-sidebar-link {{ request()->routeIs('admin.notifications') ? 'active' : '' }}">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                        <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                    </svg>
                    Notifications
                    <span id="admin-notification-badge" class="admin-sidebar-link-badge" style="display:none;">0</span>
                </a>

                <a href="{{ route('admin.chat') }}"
                   class="admin-sidebar-link {{ request()->routeIs('admin.chat') ? 'active' : '' }}">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                    </svg>
                    Support Chat
                </a>

                {{-- Reports --}}
                <a href="{{ route('admin.reports') }}"
                   class="admin-sidebar-link {{ request()->routeIs('admin.reports') ? 'active' : '' }}">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M18 20V10"/><path d="M12 20V4"/><path d="M6 20v-6"/>
                    </svg>
                    Reports
                </a>

                <div class="admin-sidebar-divider"></div>
                <span class="admin-sidebar-nav-section">Content</span>

                {{-- Visual Highlights --}}
                <a href="{{ route('admin.visual-highlights') }}"
                   class="admin-sidebar-link {{ request()->routeIs('admin.visual-highlights') ? 'active' : '' }}">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <rect x="3" y="3" width="18" height="18" rx="2"/>
                        <circle cx="8.5" cy="8.5" r="1.5"/>
                        <path d="M21 15l-5-5L5 21"/>
                    </svg>
                    Visual Highlights
                </a>

                {{-- Manage News --}}
                <a href="{{ route('admin.news-articles') }}"
                   class="admin-sidebar-link {{ request()->routeIs('admin.news-articles') ? 'active' : '' }}">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
                        <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>
                        <path d="M8 7h8"/>
                        <path d="M8 11h8"/>
                        <path d="M8 15h5"/>
                    </svg>
                    Manage News
                </a>

                {{-- Homepage Promotions --}}
                <a href="{{ route('admin.promotions') }}"
                   class="admin-sidebar-link {{ request()->routeIs('admin.promotions') ? 'active' : '' }}">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                    </svg>
                    Promotions
                </a>

                <div class="admin-sidebar-divider"></div>
                <span class="admin-sidebar-nav-section">System</span>

                {{-- Contact Messages --}}
                <a href="{{ route('admin.contact-messages') }}"
                   class="admin-sidebar-link {{ request()->routeIs('admin.contact-messages') ? 'active' : '' }}">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                        <polyline points="22,6 12,13 2,6"/>
                    </svg>
                    Contact Messages
                </a>

                {{-- Quotation Settings --}}
                <a href="{{ route('admin.quotation-settings') }}"
                   class="admin-sidebar-link {{ request()->routeIs('admin.quotation-settings') ? 'active' : '' }}">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
                    </svg>
                    Quotation Settings
                </a>

                {{-- Settings → Profile --}}
                <a href="{{ route('admin.profile.show') }}"
                   class="admin-sidebar-link {{ request()->routeIs('admin.profile.show') ? 'active' : '' }}">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="12" cy="12" r="3"/>
                        <path d="M19.07 4.93l-1.41 1.41M4.93 4.93l1.41 1.41M19.07 19.07l-1.41-1.41M4.93 19.07l1.41-1.41M12 2v2M12 20v2M2 12h2M20 12h2"/>
                    </svg>
                    Settings
                </a>

            @else

                {{-- Technician sees only item builder --}}
                <a href="{{ route('quotations.item-builder') }}"
                   class="admin-sidebar-link {{ request()->routeIs('quotations.item-builder') ? 'active' : '' }}">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                        <line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>
                    </svg>
                    Item Builder
                </a>

                <a href="{{ route('admin.request-assignments') }}"
                   class="admin-sidebar-link {{ request()->routeIs('admin.request-assignments') ? 'active' : '' }}">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <polyline points="9 11 12 14 22 4"/>
                        <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                    </svg>
                    Assignments
                </a>

            @endif

        </nav>
        @endauth

        {{-- Sidebar bottom: logout --}}
        @auth
        <div class="admin-sidebar-foot">
            @if ($isAdminUser)
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="admin-sidebar-logout-btn">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                            <polyline points="16 17 21 12 16 7"/>
                            <line x1="21" y1="12" x2="9" y2="12"/>
                        </svg>
                        Logout
                    </button>
                </form>
            @endif
        </div>
        @endauth

    </aside>{{-- /.admin-sidebar --}}

    {{-- MAIN AREA: topbar + content --}}
    <div class="admin-main-area" id="adminMainArea">

        {{-- Simplified topbar --}}
        @auth
        <header class="admin-topbar" aria-label="Admin topbar">
            <div class="admin-topbar-left">
                <button class="admin-topbar-toggle" id="adminSidebarToggle" aria-label="Toggle sidebar" type="button">
                    <svg width="18" height="18" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                        <path d="M3 5h14M3 10h14M3 15h14" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    </svg>
                </button>
            </div>
            <div class="admin-topbar-right">
                @php
                    $solmateProfileImageUrl = auth()->user()?->profile_picture
                        ? asset('storage/' . ltrim(auth()->user()->profile_picture, '/'))
                        : null;
                @endphp
                <div class="solmate-profile-wrapper">
                    <button class="solmate-profile-btn {{ $solmateProfileImageUrl ? 'has-image' : '' }}" id="solmateProfileBtn" aria-label="Open profile menu" type="button" aria-haspopup="true" aria-expanded="false" data-profile-menu-button>
                        <img
                            src="{{ $solmateProfileImageUrl ?: '' }}"
                            alt="{{ auth()->user()->name }} profile picture"
                            class="solmate-profile-btn-image"
                            data-profile-menu-image
                            @if (! $solmateProfileImageUrl) style="display:none;" @endif
                        >
                        <span class="solmate-profile-btn-icon" data-profile-menu-icon @if ($solmateProfileImageUrl) style="display:none;" @endif>
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path d="M12 12c2.761 0 5-2.239 5-5s-2.239-5-5-5-5 2.239-5 5 2.239 5 5 5zm0 2c-3.337 0-10 1.676-10 5v2h20v-2c0-3.324-6.663-5-10-5z"/>
                            </svg>
                        </span>
                    </button>
                    <div class="solmate-profile-dropdown" id="solmateProfileDropdown" role="menu">
                        <div class="solmate-profile-dropdown-header">
                            <p class="solmate-profile-dropdown-name">{{ auth()->user()->name }}</p>
                            <p class="solmate-profile-dropdown-email">{{ auth()->user()->email }}</p>
                        </div>
                        <div class="solmate-profile-dropdown-actions">
                            @if ($isAdminUser)
                                <a href="{{ route('admin.profile.show') }}" class="solmate-logout-btn" style="text-decoration:none;display:block;">Profile</a>
                            @endif
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="solmate-logout-btn">Logout</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </header>
        @endauth

        {{-- Admin content area --}}
        <div class="admin-content-shell">

            @if (session('status'))
                <div class="status">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="error-box">
                    <strong>Please review the form.</strong>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <main class="admin-main">
                @yield('content')
            </main>

        </div>{{-- /.admin-content-shell --}}

    </div>{{-- /.admin-main-area --}}

@else
    {{-- ===== CUSTOMER / PUBLIC LAYOUT ===== --}}
    <div class="shell">
        @auth
            @if ($isCustomerShell)
                {{-- ===== CUSTOMER HEADER ===== --}}
                <nav class="solmate-nav" aria-label="Customer navigation">
                    {{-- Left: brand --}}
                    <div class="solmate-nav-left">
                        <a href="{{ route('home') }}" class="solmate-brand-link" aria-label="RDY home">
                            <img src="{{ asset('images/rdy-logo-transparent.png') }}" alt="RDY logo" class="solmate-logo solmate-logo--nav">
                        </a>
                    </div>

                    {{-- Center: nav links --}}
                    <div class="solmate-nav-center">
                        <a href="{{ route('home') }}"
                           class="solmate-nav-link {{ request()->routeIs('home') ? 'active' : '' }}">Home</a>
                        <a href="{{ route('customer.quotation') }}"
                           class="solmate-nav-link {{ request()->routeIs('customer.quotation', 'customer.quotation.*', 'customer.final-quotation') ? 'active' : '' }}">Quotation</a>
                        {{-- Services dropdown --}}
                        <div class="solmate-services-wrapper">
                            <button
                                type="button"
                                id="solmateServicesBtn"
                                class="solmate-services-trigger {{ request()->routeIs('customer.inspection', 'customer.installation', 'customer.maintenance') ? 'active' : '' }}"
                                aria-haspopup="true"
                                aria-expanded="false"
                                aria-controls="solmateServicesDropdown"
                            >
                                Services
                                <svg class="solmate-services-chevron" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M6 9l6 6 6-6"/>
                                </svg>
                            </button>

                            <div id="solmateServicesDropdown" class="solmate-services-dropdown" role="menu">

                                {{-- Inspection --}}
                                <a href="{{ route('customer.inspection') }}" class="solmate-services-item {{ request()->routeIs('customer.inspection') ? 'active' : '' }}" role="menuitem">
                                    <span class="solmate-services-item-icon">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#F4D000" stroke-width="2" aria-hidden="true">
                                            <path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0118 0z"/>
                                            <circle cx="12" cy="10" r="3"/>
                                        </svg>
                                    </span>
                                    Inspection
                                </a>

                                {{-- Installation --}}
                                <a href="{{ route('customer.installation') }}" class="solmate-services-item {{ request()->routeIs('customer.installation') ? 'active' : '' }}" role="menuitem">
                                    <span class="solmate-services-item-icon">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#F4D000" stroke-width="2" aria-hidden="true">
                                            <rect x="2" y="7" width="20" height="14" rx="2"/>
                                            <path d="M16 21V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v16"/>
                                        </svg>
                                    </span>
                                    Installation
                                </a>

                                {{-- Maintenance --}}
                                <a href="{{ route('customer.maintenance') }}" class="solmate-services-item {{ request()->routeIs('customer.maintenance') ? 'active' : '' }}" role="menuitem">
                                    <span class="solmate-services-item-icon">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#F4D000" stroke-width="2" aria-hidden="true">
                                            <circle cx="12" cy="12" r="3"/>
                                            <path d="M19.07 4.93l-1.41 1.41M4.93 4.93l1.41 1.41M19.07 19.07l-1.41-1.41M4.93 19.07l1.41-1.41M12 2v2M12 20v2M2 12h2M20 12h2"/>
                                        </svg>
                                    </span>
                                    Maintenance
                                </a>

                            </div>
                        </div>{{-- /.solmate-services-wrapper --}}
                        <a href="{{ route('customer.tracking') }}"
                           class="solmate-nav-link {{ request()->routeIs('customer.tracking') ? 'active' : '' }}">Tracking</a>
                                <a href="{{ route('customer.chat') }}"
                                    class="solmate-nav-link {{ request()->routeIs('customer.chat') ? 'active' : '' }}">Chat</a>
                                <a href="{{ route('customer.mobile-app') }}"
                                    class="solmate-nav-link {{ request()->routeIs('customer.mobile-app') ? 'active' : '' }}">Download App</a>
                        <a href="{{ route('customer.testimonies') }}"
                           class="solmate-nav-link {{ request()->routeIs('customer.testimonies') ? 'active' : '' }}">Feedback</a>
                        <a href="{{ route('dashboard') }}"
                           class="solmate-nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">Profile</a>
                    </div>

                    {{-- Right: profile icon with dropdown --}}
                    <div class="solmate-nav-right">
                        @php
                            $solmateProfileImageUrl = auth()->user()?->profile_picture
                                ? asset('storage/' . ltrim(auth()->user()->profile_picture, '/'))
                                : null;
                        @endphp
                        <div class="solmate-profile-wrapper">
                            <button class="solmate-profile-btn {{ $solmateProfileImageUrl ? 'has-image' : '' }}" id="solmateProfileBtn" aria-label="Open profile menu" type="button" aria-haspopup="true" aria-expanded="false" data-profile-menu-button>
                                <img
                                    src="{{ $solmateProfileImageUrl ?: '' }}"
                                    alt="{{ auth()->user()->name }} profile picture"
                                    class="solmate-profile-btn-image"
                                    data-profile-menu-image
                                    @if (! $solmateProfileImageUrl) style="display:none;" @endif
                                >
                                <span class="solmate-profile-btn-icon" data-profile-menu-icon @if ($solmateProfileImageUrl) style="display:none;" @endif>
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                        <path d="M12 12c2.761 0 5-2.239 5-5s-2.239-5-5-5-5 2.239-5 5 2.239 5 5 5zm0 2c-3.337 0-10 1.676-10 5v2h20v-2c0-3.324-6.663-5-10-5z"/>
                                    </svg>
                                </span>
                            </button>
                            <div class="solmate-profile-dropdown" id="solmateProfileDropdown" role="menu">
                                <div class="solmate-profile-dropdown-header">
                                    <p class="solmate-profile-dropdown-name">{{ auth()->user()->name }}</p>
                                    <p class="solmate-profile-dropdown-email">{{ auth()->user()->email }}</p>
                                </div>
                                <div class="solmate-profile-dropdown-actions">
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="solmate-logout-btn">Logout</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </nav>
            @endif
        @endauth

        @if (session('status'))
            <div class="status">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="error-box">
                <strong>Please review the form.</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <main>
            @yield('content')
        </main>
    </div>{{-- /.shell (customer/public) --}}

@endif{{-- /@if ($isAdminShell) --}}

    {{-- ===== FOOTER ===== --}}
    <footer class="solmate-footer" aria-label="Site footer">
        <div class="solmate-footer-inner">
            <div class="solmate-footer-upper">

                {{-- Brand + description --}}
                <div>
                    <a href="{{ $isCustomerShell ? route('home') : route('landing') }}" class="solmate-footer-brand-link" aria-label="RDY home">
                        <img src="{{ asset('images/rdy-logo-transparent.png') }}" alt="RDY logo" class="solmate-logo solmate-logo--footer">
                    </a>
                    <p class="solmate-footer-desc">
                        SolMate is a smart solar panel installation management system designed to
                        streamline planning, monitoring, and deployment. We help installers,
                        homeowners, and businesses transition to clean energy with efficiency and
                        confidence.
                    </p>
                </div>

                @php
                    $customerFooterHomeUrl = $isCustomerShell ? route('home') : route('landing');
                    $customerFooterServicesUrl = $isCustomerShell ? route('customer.tracking') : route('landing') . '#services';
                    $customerFooterCalculatorUrl = $isCustomerShell ? route('customer.quotation.create') : route('login');
                    $customerFooterAboutUrl = $isCustomerShell ? route('public.contact') : route('landing') . '#about';
                    $customerFooterContactUrl = route('public.contact');
                    $customerFooterInstallationUrl = $isCustomerShell ? route('customer.installation') : route('landing') . '#services';
                    $customerFooterMaintenanceUrl = $isCustomerShell ? route('customer.maintenance') : route('landing') . '#services';
                    $customerFooterInspectionUrl = $isCustomerShell ? route('customer.inspection') : route('landing') . '#services';
                    $customerFooterQuotationUrl = $isCustomerShell ? route('customer.quotation.create') : route('landing') . '#services';
                    $customerFooterConsultationUrl = route('public.contact');
                @endphp

                {{-- Quick Links --}}
                <div>
                    <p class="solmate-footer-col-heading">Quick Links</p>
                    <ul class="solmate-footer-links">
                        <li><a href="{{ $customerFooterHomeUrl }}">Home</a></li>
                        <li><a href="{{ $customerFooterServicesUrl }}">Services</a></li>
                        <li><a href="{{ $customerFooterCalculatorUrl }}">Solar Calculator</a></li>
                        <li><a href="{{ $customerFooterAboutUrl }}">About Us</a></li>
                        <li><a href="{{ $customerFooterContactUrl }}">Contact</a></li>
                    </ul>
                </div>

                {{-- Services --}}
                <div>
                    <p class="solmate-footer-col-heading">Services</p>
                    <ul class="solmate-footer-links">
                        <li><a href="{{ $customerFooterInstallationUrl }}">Solar Installation</a></li>
                        <li><a href="{{ $customerFooterMaintenanceUrl }}">System Maintenance</a></li>
                        <li><a href="{{ $customerFooterInspectionUrl }}">Site Assessment</a></li>
                        <li><a href="{{ $customerFooterQuotationUrl }}">ROI &amp; Quotation Estimation</a></li>
                        <li><a href="{{ $customerFooterConsultationUrl }}">Consultation</a></li>
                    </ul>
                </div>

                {{-- Socials --}}
                <div>
                    <p class="solmate-footer-col-heading">Socials</p>
                    <div class="solmate-footer-socials">
                        {{-- Facebook --}}
                        <a href="#" class="solmate-footer-social-btn" aria-label="Facebook" target="_blank" rel="noopener noreferrer">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>
                        </a>
                        {{-- Instagram --}}
                        <a href="#" class="solmate-footer-social-btn" aria-label="Instagram" target="_blank" rel="noopener noreferrer">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>
                        </a>
                        {{-- X (Twitter) --}}
                        <a href="#" class="solmate-footer-social-btn" aria-label="X (Twitter)" target="_blank" rel="noopener noreferrer">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                        </a>
                        {{-- TikTok --}}
                        <a href="#" class="solmate-footer-social-btn" aria-label="TikTok" target="_blank" rel="noopener noreferrer">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-2.88 2.5 2.89 2.89 0 0 1-2.89-2.89 2.89 2.89 0 0 1 2.89-2.89c.28 0 .54.04.79.1V9.01a6.33 6.33 0 0 0-.79-.05 6.34 6.34 0 0 0-6.34 6.34 6.34 6.34 0 0 0 6.34 6.34 6.34 6.34 0 0 0 6.33-6.34V8.69a8.18 8.18 0 0 0 4.78 1.52V6.76a4.85 4.85 0 0 1-1.01-.07z"/></svg>
                        </a>
                    </div>
                </div>

            </div>
        </div>

        <hr class="solmate-footer-divider">

        <div class="solmate-footer-bottom">
            <p class="solmate-footer-copyright">
                &copy; {{ date('Y') }} RDY Solar Installation Inc.<br>
                All Rights Reserved.
            </p>
            <div class="solmate-footer-contact-items">
                <div class="solmate-footer-contact-item">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    <span>Address, Philippines</span>
                </div>
                <div class="solmate-footer-contact-item">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                    <span>rdysolarpanel@gmail.com</span>
                </div>
                <div class="solmate-footer-contact-item">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12a19.79 19.79 0 0 1-3.07-8.68A2 2 0 0 1 3.62 1h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.6a16 16 0 0 0 6 6l.96-.96a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                    <span>+63 9654326865</span>
                </div>
            </div>
        </div>
    </footer>

    @stack('scripts')

    @if ($authToastTitle)
        <script>
            (function () {
                const authToast = document.getElementById('solmate-auth-toast');

                if (!authToast) {
                    return;
                }

                window.requestAnimationFrame(function () {
                    authToast.classList.add('is-visible');
                });

                window.setTimeout(function () {
                    authToast.classList.remove('is-visible');

                    window.setTimeout(function () {
                        authToast.remove();
                    }, 240);
                }, 2200);
            })();
        </script>
    @endif

    @auth
        <script>
            (function () {
                    const profileBtn = document.getElementById('solmateProfileBtn');
                    const profileDropdown = document.getElementById('solmateProfileDropdown');

                    if (profileBtn && profileDropdown) {
                        profileBtn.addEventListener('click', function (e) {
                            e.stopPropagation();
                            const isOpen = profileDropdown.classList.toggle('open');
                            profileBtn.setAttribute('aria-expanded', String(isOpen));
                        });

                        document.addEventListener('click', function () {
                            profileDropdown.classList.remove('open');
                            profileBtn.setAttribute('aria-expanded', 'false');
                        });

                        document.addEventListener('keydown', function (e) {
                            if (e.key === 'Escape') {
                                profileDropdown.classList.remove('open');
                                profileBtn.setAttribute('aria-expanded', 'false');
                                profileBtn.focus();
                            }
                        });
                    }

                    // Services dropdown
                    const servicesBtn      = document.getElementById('solmateServicesBtn');
                    const servicesDropdown = document.getElementById('solmateServicesDropdown');

                    if (servicesBtn && servicesDropdown) {
                        servicesBtn.addEventListener('click', function (e) {
                            e.stopPropagation();
                            const isOpen = servicesDropdown.classList.toggle('open');
                            servicesBtn.setAttribute('aria-expanded', String(isOpen));
                        });

                        document.addEventListener('click', function () {
                            servicesDropdown.classList.remove('open');
                            servicesBtn.setAttribute('aria-expanded', 'false');
                        });

                        document.addEventListener('keydown', function (e) {
                            if (e.key === 'Escape') {
                                servicesDropdown.classList.remove('open');
                                servicesBtn.setAttribute('aria-expanded', 'false');
                                servicesBtn.focus();
                            }
                        });
                    }
            })();
        </script>
    @endauth

    @auth
        @if ($isAdminUser)
            <script>
                (function () {
                    const badge = document.getElementById('admin-notification-badge');

                    function setBadgeCount(count) {
                        if (!badge) {
                            return;
                        }

                        const normalizedCount = Number.isFinite(Number(count)) ? Math.max(0, Number(count)) : 0;

                        badge.textContent = String(normalizedCount);
                        badge.style.display = normalizedCount > 0 ? 'inline-flex' : 'none';
                    }

                    async function refreshUnreadCount() {
                        try {
                            const response = await fetch('/api/notifications/unread-count', {
                                credentials: 'same-origin',
                                headers: {
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest',
                                },
                            });

                            if (!response.ok) {
                                throw new Error('Could not load notification count.');
                            }

                            const payload = await response.json();
                            setBadgeCount(payload?.unread_count ?? 0);
                        } catch (error) {
                            setBadgeCount(0);
                        }
                    }

                    window.adminNotifications = {
                        refreshUnreadCount,
                        setBadgeCount,
                    };

                    refreshUnreadCount();
                    window.addEventListener('focus', refreshUnreadCount);
                    document.addEventListener('visibilitychange', () => {
                        if (document.visibilityState === 'visible') {
                            refreshUnreadCount();
                        }
                    });
                })();
            </script>
        @endif
    @endauth

    @if ($isAdminShell)
        <script>
            (function () {
                const sidebar  = document.getElementById('adminSidebar');
                const mainArea = document.getElementById('adminMainArea');
                const toggle   = document.getElementById('adminSidebarToggle');
                const overlay  = document.getElementById('adminSidebarOverlay');

                if (!sidebar || !toggle) return;

                var isMobile = window.matchMedia('(max-width: 900px)').matches;

                function openSidebar() {
                    sidebar.classList.add('open');
                    sidebar.classList.remove('sidebar-hidden');
                    if (overlay) { overlay.classList.add('open'); overlay.removeAttribute('aria-hidden'); }
                }

                function closeSidebar() {
                    if (isMobile) {
                        sidebar.classList.remove('open');
                        if (overlay) { overlay.classList.remove('open'); overlay.setAttribute('aria-hidden', 'true'); }
                    } else {
                        sidebar.classList.add('sidebar-hidden');
                        if (mainArea) mainArea.classList.add('sidebar-collapsed');
                    }
                }

                function toggleSidebar() {
                    isMobile = window.matchMedia('(max-width: 900px)').matches;
                    if (isMobile) {
                        if (sidebar.classList.contains('open')) {
                            closeSidebar();
                        } else {
                            openSidebar();
                        }
                    } else {
                        if (sidebar.classList.contains('sidebar-hidden')) {
                            sidebar.classList.remove('sidebar-hidden');
                            if (mainArea) mainArea.classList.remove('sidebar-collapsed');
                        } else {
                            closeSidebar();
                        }
                    }
                }

                toggle.addEventListener('click', toggleSidebar);

                if (overlay) {
                    overlay.addEventListener('click', function () {
                        closeSidebar();
                    });
                }

                window.addEventListener('resize', function () {
                    isMobile = window.matchMedia('(max-width: 900px)').matches;
                    if (!isMobile) {
                        sidebar.classList.remove('open');
                        if (overlay) { overlay.classList.remove('open'); overlay.setAttribute('aria-hidden', 'true'); }
                        if (sidebar.classList.contains('sidebar-hidden')) {
                            if (mainArea) mainArea.classList.add('sidebar-collapsed');
                        } else {
                            if (mainArea) mainArea.classList.remove('sidebar-collapsed');
                        }
                    }
                });
            })();
        </script>
    @endif
</body>
</html>
