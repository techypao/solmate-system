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
            background: #f6f8fb;
            color: #1f2933;
            --solmate-blue-900: #102a43;
            --solmate-blue-800: #1e4068;
            --solmate-blue-700: #29527a;
            --solmate-blue-100: #eaf2fb;
            --solmate-blue-50: #f7fbff;
            --solmate-gold-500: #d4a017;
            --solmate-gold-400: #f4c542;
            --solmate-gold-100: #fef3c7;
            --solmate-surface: #ffffff;
            --solmate-surface-muted: #f8fbff;
            --solmate-border: #dbe7f3;
            --solmate-border-strong: #c7d7e7;
            --solmate-text: #102a43;
            --solmate-copy: #52606d;
            --solmate-shadow: 0 18px 42px rgba(15, 23, 42, 0.07);
            --solmate-shadow-soft: 0 8px 24px rgba(15, 23, 42, 0.05);
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background:
                radial-gradient(circle at top left, rgba(234, 242, 251, 0.95), transparent 28%),
                linear-gradient(180deg, #f8fbff 0%, #f5f7fb 28%, #f8fafc 100%);
            color: var(--solmate-text);
        }

        a {
            color: #0f5f9c;
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
            border: 1px solid #d9e2ec;
            border-radius: 12px;
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
            background: #d64545;
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
            background: #ecfdf3;
            color: #1f5132;
            border: 1px solid #b7e6be;
        }

        .error-box {
            background: #fff1f2;
            color: #8a1c1c;
            border: 1px solid #fecdd3;
        }

        .info-box {
            background: #eff6ff;
            color: #124e78;
            border: 1px solid #bfdbfe;
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
            color: #52606d;
            margin-bottom: 8px;
        }

        input {
            width: 100%;
            padding: 12px 14px;
            border: 1.5px solid #c9d6e3;
            border-radius: 12px;
            font-size: 14px;
            background: #fff;
            color: #102a43;
            transition: border-color 0.18s, box-shadow 0.18s, background 0.18s;
        }

        select,
        textarea {
            width: 100%;
            padding: 12px 14px;
            border: 1.5px solid #c9d6e3;
            border-radius: 12px;
            font-size: 14px;
            background: #fff;
            color: #102a43;
            transition: border-color 0.18s, box-shadow 0.18s, background 0.18s;
        }

        select {
            appearance: none;
            -webkit-appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
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
            border-color: #b7c9dc;
        }

        .solmate-admin-shell input,
        .solmate-admin-shell select,
        .solmate-admin-shell textarea {
            background-color: #fcfdff;
        }

        input:focus {
            outline: none;
            border-color: var(--solmate-gold-500);
            box-shadow: 0 0 0 4px rgba(212, 160, 23, 0.12);
        }

        select:focus,
        textarea:focus {
            outline: none;
            border-color: var(--solmate-gold-500);
            box-shadow: 0 0 0 4px rgba(212, 160, 23, 0.12);
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
            background: linear-gradient(135deg, var(--solmate-gold-400), var(--solmate-gold-500));
            color: #ffffff;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 10px 24px rgba(212, 160, 23, 0.18);
            transition: transform 0.16s, box-shadow 0.16s, opacity 0.16s, background 0.16s, color 0.16s, border-color 0.16s;
            text-decoration: none;
        }

        button.secondary,
        .button-link.secondary {
            background: linear-gradient(180deg, #ffffff 0%, #f7fbff 100%);
            color: var(--solmate-blue-800);
            border-color: var(--solmate-border-strong);
            box-shadow: 0 8px 18px rgba(15, 23, 42, 0.05);
        }

        button.neutral,
        .button-link.neutral {
            background: #f8fafc;
            color: var(--solmate-blue-800);
            border-color: #d7e1ea;
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
            border-color: #b8c8da;
            background: #ffffff;
        }

        button:focus-visible,
        .button-link:focus-visible {
            outline: none;
            box-shadow: 0 0 0 4px rgba(212, 160, 23, 0.18);
        }

        button[disabled] {
            opacity: 0.6;
            cursor: wait;
            transform: none;
        }

        .muted {
            color: #52606d;
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
            background: linear-gradient(135deg, #f8fbff 0%, #eef6ff 55%, #e3efff 100%);
            border-color: #d7e5f3;
        }

        .admin-hero-card::after {
            content: '';
            position: absolute;
            right: -48px;
            top: -44px;
            width: 220px;
            height: 220px;
            border-radius: 50%;
            background: rgba(212, 160, 23, 0.11);
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
            background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
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
            border: 1px solid #dbe7f3;
            border-radius: 16px;
            background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.04);
            position: relative;
        }

        .summary-card::before {
            content: '';
            position: absolute;
            inset: 0 auto 0 0;
            width: 4px;
            border-radius: 16px 0 0 16px;
            background: linear-gradient(180deg, var(--solmate-gold-400), var(--solmate-blue-800));
            opacity: 0.9;
        }

        .summary-label {
            color: #64748b;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            margin-bottom: 10px;
        }

        .summary-value {
            color: #102a43;
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
            border: 1px solid #dbe7f3;
            border-radius: 18px;
            background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
            box-shadow: 0 10px 28px rgba(15, 23, 42, 0.04);
            scroll-margin-top: 24px;
        }

        .request-card:target {
            border-color: #93c5fd;
            box-shadow: 0 0 0 4px rgba(147, 197, 253, 0.18);
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
            color: #102a43;
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
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            background: #ffffff;
        }

        .detail-label {
            display: block;
            margin-bottom: 4px;
            color: #7b8794;
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
            color: #475569;
            border-color: #dbe4ee;
        }

        .badge-warning {
            background: #fef3c7;
            color: #a16207;
            border-color: #fde68a;
        }

        .badge-info {
            background: #eff6ff;
            color: #1d4ed8;
            border-color: #bfdbfe;
        }

        .badge-primary {
            background: #dbeafe;
            color: #1d4ed8;
            border-color: #93c5fd;
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
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            background: #ffffff;
        }

        .solmate-admin-shell table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background: #ffffff;
            border: 1px solid #dbe7f3;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.04);
        }

        .solmate-admin-shell th,
        .solmate-admin-shell td {
            padding: 14px 16px;
            border-bottom: 1px solid #e2e8f0;
            text-align: left;
            vertical-align: top;
        }

        .solmate-admin-shell th {
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #64748b;
            background: #f8fbff;
        }

        .solmate-admin-shell thead th:first-child {
            border-top-left-radius: 18px;
        }

        .solmate-admin-shell thead th:last-child {
            border-top-right-radius: 18px;
        }

        .solmate-admin-shell tbody tr:nth-child(even) td {
            background: #fcfdff;
        }

        .solmate-admin-shell tbody tr:hover td {
            background: #fbfdff;
        }

        .solmate-admin-shell tbody tr:last-child td {
            border-bottom: none;
        }

        @media (max-width: 720px) {
            .assignment-row {
                grid-template-columns: 1fr;
            }
        }

        /* ===== CUSTOMER HEADER ===== */
        .solmate-nav {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 28px;
            height: 68px;
            background: #f8f4ec;
            border-radius: 18px;
            margin-bottom: 24px;
            box-shadow: 0 10px 28px rgba(15, 23, 42, 0.07);
            border: 1px solid rgba(212, 160, 23, 0.10);
        }

        .solmate-nav-left {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 160px;
        }

        .solmate-hamburger {
            background: none;
            border: none;
            padding: 7px 8px;
            cursor: pointer;
            color: #374151;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            line-height: 0;
            transition: background 0.15s;
        }

        .solmate-hamburger:hover {
            background: rgba(0, 0, 0, 0.06);
            color: #374151;
        }

        .solmate-brand-link {
            text-decoration: none;
            display: inline-flex;
            align-items: baseline;
            line-height: 1;
        }

        .solmate-brand-link:hover {
            text-decoration: none;
        }

        .solmate-brand-sol {
            font-size: 22px;
            font-weight: 700;
            color: #102a43;
            letter-spacing: -0.3px;
        }

        .solmate-brand-mate {
            font-size: 22px;
            font-weight: 700;
            color: #d4a017;
            letter-spacing: -0.3px;
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
            background: linear-gradient(135deg, #f8fbff 0%, #eef6ff 58%, #e8f1fb 100%);
            border: 1px solid #dbe7f3;
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
            background: rgba(212, 160, 23, 0.10);
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
            background: rgba(255, 255, 255, 0.8);
            color: var(--solmate-blue-800);
            border: 1px solid rgba(30, 64, 104, 0.08);
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
            color: #4b5563;
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
            background: rgba(255, 255, 255, 0.62);
            color: var(--solmate-blue-800);
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.18);
        }

        .solmate-nav-link:hover {
            color: #102a43;
            text-decoration: none;
            border-bottom-color: #d4a017;
        }

        .solmate-nav--admin .solmate-nav-link:hover {
            border-bottom-color: transparent;
            border-color: #c7d7e7;
            background: #ffffff;
        }

        .solmate-nav-link.active {
            color: #102a43;
            font-weight: 600;
            border-bottom-color: #102a43;
        }

        .solmate-nav--admin .solmate-nav-link.active {
            border-color: rgba(16, 42, 67, 0.10);
            background: linear-gradient(135deg, #102a43, #1e4068);
            color: #ffffff;
            box-shadow: 0 10px 20px rgba(16, 42, 67, 0.20);
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
            border: 1px solid #dbe7f3;
            background: rgba(255, 255, 255, 0.82);
            color: var(--solmate-blue-800);
            font-size: 13px;
            font-weight: 700;
            box-shadow: 0 8px 18px rgba(15, 23, 42, 0.05);
        }

        .solmate-admin-logout-btn:hover {
            background: #ffffff;
            color: var(--solmate-blue-900);
            border-color: #bfd0e2;
        }

        .solmate-profile-wrapper {
            position: relative;
        }

        .solmate-profile-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #d4a017;
            border: none;
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
            background: #c49215;
            transform: scale(1.04);
        }

        .solmate-profile-btn.has-image {
            background: #ffffff;
            border: 2px solid #d4a017;
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
            border: 1px solid #e2e8f0;
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
            border-bottom: 1px solid #f1f5f9;
            background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
        }

        .solmate-profile-dropdown-name {
            font-weight: 600;
            font-size: 14px;
            color: #1f2937;
            margin: 0 0 2px;
        }

        .solmate-profile-dropdown-email {
            font-size: 12px;
            color: #6b7280;
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
            color: #374151;
            cursor: pointer;
            border-radius: 6px;
            display: block;
            transition: background 0.12s;
        }

        .solmate-logout-btn:hover {
            background: #f9fafb;
            color: #111827;
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
            color: #4b5563;
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
            color: #102a43;
            border-bottom-color: #d4a017;
        }

        .solmate-services-trigger.active {
            color: #102a43;
            font-weight: 600;
            border-bottom-color: #102a43;
        }

        .solmate-services-chevron {
            transition: transform 0.18s ease;
            flex-shrink: 0;
            color: #94a3b8;
        }

        .solmate-services-trigger[aria-expanded="true"] .solmate-services-chevron {
            transform: rotate(180deg);
        }

        .solmate-services-trigger[aria-expanded="true"] .solmate-services-chevron {
            color: #d4a017;
        }

        .solmate-services-dropdown {
            display: none;
            position: absolute;
            top: calc(100% + 14px);
            left: 50%;
            transform: translateX(-50%);
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            box-shadow: 0 8px 28px rgba(0, 0, 0, 0.09);
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
            color: #374151;
            text-decoration: none;
            transition: background 0.12s, color 0.12s;
            white-space: nowrap;
        }

        .solmate-services-item:hover {
            background: #f8f4ec;
            color: #102a43;
            text-decoration: none;
        }

        .solmate-services-item.active {
            color: #102a43;
            font-weight: 600;
            background: #fef9ec;
        }

        .solmate-services-item-icon {
            width: 28px;
            height: 28px;
            border-radius: 7px;
            background: linear-gradient(135deg, #102a43, #1e4068);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .solmate-services-item-soon {
            margin-left: auto;
            font-size: 10px;
            font-weight: 700;
            color: #94a3b8;
            background: #f1f5f9;
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
            background: #0f1729;
            color: #cbd5e1;
            margin-top: 48px;
            font-family: Arial, sans-serif;
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

        .solmate-footer-brand-sol {
            font-size: 28px;
            font-weight: 700;
            color: #7dd3fc;
            letter-spacing: -0.3px;
        }

        .solmate-footer-brand-mate {
            font-size: 28px;
            font-weight: 700;
            color: #d4a017;
            letter-spacing: -0.3px;
        }

        .solmate-footer-brand-link {
            text-decoration: none;
            display: inline-flex;
            align-items: baseline;
            margin-bottom: 16px;
        }

        .solmate-footer-brand-link:hover {
            text-decoration: none;
        }

        .solmate-footer-desc {
            font-size: 13.5px;
            line-height: 1.75;
            color: #94a3b8;
            max-width: 300px;
            margin: 0;
        }

        .solmate-footer-col-heading {
            font-size: 13px;
            font-weight: 700;
            color: #e2e8f0;
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
            color: #94a3b8;
            text-decoration: none;
            transition: color 0.15s;
        }

        .solmate-footer-links a:hover {
            color: #e2e8f0;
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
            background: rgba(255, 255, 255, 0.07);
            border: 1px solid rgba(255, 255, 255, 0.10);
            color: #cbd5e1;
            text-decoration: none;
            transition: background 0.15s, color 0.15s;
            line-height: 0;
        }

        .solmate-footer-social-btn:hover {
            background: rgba(255, 255, 255, 0.14);
            color: #ffffff;
            text-decoration: none;
        }

        .solmate-footer-divider {
            border: none;
            border-top: 1px solid rgba(255, 255, 255, 0.07);
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
            color: #64748b;
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
            color: #94a3b8;
        }

        .solmate-footer-contact-item svg {
            flex-shrink: 0;
            color: #7dd3fc;
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
            background: #dce8f5;
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
            background: #ffffff;
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            z-index: 100;
            overflow-y: auto;
            transition: transform 0.25s ease;
            border-right: 1px solid #e2e8f0;
            box-shadow: 2px 0 16px rgba(15, 23, 42, 0.06);
        }

        .admin-sidebar.sidebar-hidden {
            transform: translateX(-220px);
        }

        /* Sidebar brand area */
        .admin-sidebar-brand {
            padding: 20px 18px 16px;
            border-bottom: 1px solid #f1f5f9;
            flex-shrink: 0;
        }

        .admin-sidebar-brand .solmate-brand-sol {
            color: #102a43;
        }

        .admin-sidebar-brand .solmate-brand-mate {
            color: #d4a017;
        }

        .admin-sidebar-kicker {
            display: block;
            margin-top: 6px;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.09em;
            text-transform: uppercase;
            color: #94a3b8;
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
            color: #b0bec9;
            padding: 10px 12px 4px;
        }

        .admin-sidebar-link {
            display: flex;
            align-items: center;
            gap: 9px;
            padding: 9px 12px;
            border-radius: 9px;
            font-size: 13.5px;
            font-weight: 500;
            color: #52606d;
            text-decoration: none;
            transition: background 0.14s, color 0.14s;
            white-space: nowrap;
        }

        .admin-sidebar-link:hover {
            background: #f1f5f9;
            color: #102a43;
            text-decoration: none;
        }

        .admin-sidebar-link.active {
            background: #102a43;
            color: #ffffff;
            font-weight: 700;
            border-radius: 10px;
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
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            color: #64748b;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            text-align: left;
            transition: background 0.14s, color 0.14s;
            box-shadow: none;
        }

        .admin-sidebar-logout-btn:hover {
            background: #f1f5f9;
            color: #102a43;
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
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            color: #475569;
            cursor: pointer;
            transition: background 0.14s, color 0.14s;
            padding: 0;
            line-height: 0;
        }

        .admin-topbar-toggle:hover {
            background: #f1f5f9;
            color: #102a43;
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

@if ($isAdminShell)
    {{-- ===== ADMIN LAYOUT: SIDEBAR + MAIN AREA ===== --}}

    {{-- Mobile overlay --}}
    <div class="admin-sidebar-overlay" id="adminSidebarOverlay" aria-hidden="true"></div>

    {{-- LEFT SIDEBAR --}}
    <aside class="admin-sidebar" id="adminSidebar" aria-label="Admin navigation">

        {{-- Brand area --}}
        <div class="admin-sidebar-brand">
            <a href="{{ route('dashboard') }}" class="solmate-brand-link" aria-label="SolMate home">
                <span class="solmate-brand-sol">Sol</span><span class="solmate-brand-mate">Mate</span>
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

                {{-- Inspections --}}
                <a href="{{ route('admin.request-assignments') }}#inspection-requests-section"
                   class="admin-sidebar-link {{ request()->routeIs('admin.request-assignments') ? 'active' : '' }}">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M9 11l3 3L22 4"/>
                        <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                    </svg>
                    Inspections
                </a>

                {{-- Services --}}
                <a href="{{ route('admin.request-assignments') }}#service-requests-section"
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

                {{-- Pricing Catalog --}}
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
                    Pricing Catalog
                </a>

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

                {{-- Reports (no route yet) --}}
                <span class="admin-sidebar-link disabled">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M18 20V10"/><path d="M12 20V4"/><path d="M6 20v-6"/>
                    </svg>
                    Reports
                </span>

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
                        <a href="{{ route('home') }}" class="solmate-brand-link" aria-label="SolMate home">
                            <span class="solmate-brand-sol">Sol</span><span class="solmate-brand-mate">Mate</span>
                        </a>
                    </div>

                    {{-- Center: nav links --}}
                    <div class="solmate-nav-center">
                        <a href="{{ route('home') }}"
                           class="solmate-nav-link {{ request()->routeIs('home') ? 'active' : '' }}">Home</a>
                        <a href="{{ route('customer.quotation') }}"
                           class="solmate-nav-link {{ request()->routeIs('customer.quotation') ? 'active' : '' }}">Quotation</a>
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

                                {{-- Installation --}}
                                <a href="{{ route('customer.installation') }}" class="solmate-services-item {{ request()->routeIs('customer.installation') ? 'active' : '' }}" role="menuitem">
                                    <span class="solmate-services-item-icon">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#d4a017" stroke-width="2" aria-hidden="true">
                                            <rect x="2" y="7" width="20" height="14" rx="2"/>
                                            <path d="M16 21V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v16"/>
                                        </svg>
                                    </span>
                                    Installation
                                </a>

                                {{-- Inspection --}}
                                <a href="{{ route('customer.inspection') }}" class="solmate-services-item {{ request()->routeIs('customer.inspection') ? 'active' : '' }}" role="menuitem">
                                    <span class="solmate-services-item-icon">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#d4a017" stroke-width="2" aria-hidden="true">
                                            <path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0118 0z"/>
                                            <circle cx="12" cy="10" r="3"/>
                                        </svg>
                                    </span>
                                    Inspection
                                </a>

                                {{-- Maintenance --}}
                                <a href="{{ route('customer.maintenance') }}" class="solmate-services-item {{ request()->routeIs('customer.maintenance') ? 'active' : '' }}" role="menuitem">
                                    <span class="solmate-services-item-icon">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#d4a017" stroke-width="2" aria-hidden="true">
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
                        <a href="{{ route('customer.testimonies') }}"
                           class="solmate-nav-link {{ request()->routeIs('customer.testimonies') ? 'active' : '' }}">Feedback</a>
                        <a href="{{ route('dashboard') }}"
                           class="solmate-nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">Dashboard</a>
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
                    <a href="{{ route('home') }}" class="solmate-footer-brand-link" aria-label="SolMate home">
                        <span class="solmate-footer-brand-sol">Sol</span><span class="solmate-footer-brand-mate">Mate</span>
                    </a>
                    <p class="solmate-footer-desc">
                        SolMate is a smart solar panel installation management system designed to
                        streamline planning, monitoring, and deployment. We help installers,
                        homeowners, and businesses transition to clean energy with efficiency and
                        confidence.
                    </p>
                </div>

                {{-- Quick Links --}}
                <div>
                    <p class="solmate-footer-col-heading">Quick Links</p>
                    <ul class="solmate-footer-links">
                        <li><a href="{{ route('home') }}">Home</a></li>
                        <li><a href="#">Services</a></li>
                        <li><a href="#">Solar Calculator</a></li>
                        <li><a href="#">About Us</a></li>
                        <li><a href="#">Contact</a></li>
                    </ul>
                </div>

                {{-- Services --}}
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
