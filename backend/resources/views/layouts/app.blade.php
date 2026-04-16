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
            font-family: Arial, sans-serif;
            line-height: 1.5;
            background: #f4f6f8;
            color: #1f2933;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #f4f6f8;
        }

        a {
            color: #0f5f9c;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        .shell {
            max-width: 1040px;
            margin: 0 auto;
            padding: 24px 16px 48px;
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

        .brand {
            font-weight: 700;
            color: #102a43;
        }

        .card {
            background: #ffffff;
            border: 1px solid #d9e2ec;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.04);
        }

        .card + .card {
            margin-top: 16px;
        }

        .narrow {
            max-width: 520px;
            margin: 48px auto 0;
        }

        .page-title {
            margin: 0 0 8px;
            font-size: 28px;
            color: #102a43;
        }

        .page-copy {
            margin: 0 0 20px;
            color: #52606d;
        }

        .status,
        .error-box,
        .info-box {
            margin-bottom: 16px;
            padding: 12px 14px;
            border-radius: 10px;
            font-size: 14px;
        }

        .status {
            background: #e3f9e5;
            color: #1f5132;
            border: 1px solid #b7e6be;
        }

        .error-box {
            background: #fde8e8;
            color: #8a1c1c;
            border: 1px solid #f8b4b4;
        }

        .info-box {
            background: #e8f1fb;
            color: #124e78;
            border: 1px solid #bfd8f4;
        }

        .form-grid {
            display: grid;
            gap: 16px;
        }

        .form-grid.two-columns {
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        }

        label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #243b53;
            margin-bottom: 6px;
        }

        input {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #bcccdc;
            border-radius: 8px;
            font-size: 14px;
            background: #fff;
        }

        select,
        textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #bcccdc;
            border-radius: 8px;
            font-size: 14px;
            background: #fff;
        }

        input:focus {
            outline: 2px solid #9bd0ff;
            border-color: #0f5f9c;
        }

        select:focus,
        textarea:focus {
            outline: 2px solid #9bd0ff;
            border-color: #0f5f9c;
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
            color: #0f5f9c;
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
            margin-top: 8px;
        }

        button,
        .button-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 16px;
            border-radius: 8px;
            border: 1px solid #0f5f9c;
            background: #0f5f9c;
            color: #ffffff;
            font-size: 14px;
            cursor: pointer;
        }

        button.secondary,
        .button-link.secondary {
            background: #ffffff;
            color: #0f5f9c;
        }

        button[disabled] {
            opacity: 0.6;
            cursor: wait;
        }

        .muted {
            color: #52606d;
            font-size: 14px;
        }

        .stack {
            display: grid;
            gap: 12px;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 16px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }

        .summary-grid {
            display: grid;
            gap: 12px;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        }

        .summary-card {
            padding: 16px;
            border: 1px solid #d9e2ec;
            border-radius: 12px;
            background: #f8fbff;
        }

        .summary-label {
            color: #52606d;
            font-size: 13px;
            margin-bottom: 8px;
        }

        .summary-value {
            color: #102a43;
            font-size: 28px;
            font-weight: 700;
            line-height: 1;
        }

        .request-list {
            display: grid;
            gap: 16px;
        }

        .request-card {
            padding: 18px;
            border: 1px solid #d9e2ec;
            border-radius: 12px;
            background: #fbfcfe;
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
            font-weight: 700;
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
            padding: 12px 14px;
            border: 1px solid #e4e7eb;
            border-radius: 10px;
            background: #ffffff;
        }

        .detail-label {
            display: block;
            margin-bottom: 4px;
            color: #7b8794;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.04em;
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
            padding: 5px 10px;
            border-radius: 999px;
            border: 1px solid transparent;
            font-size: 12px;
            font-weight: 700;
            text-transform: capitalize;
        }

        .badge-neutral {
            background: #f0f4f8;
            color: #334e68;
            border-color: #d9e2ec;
        }

        .badge-warning {
            background: #fff7d6;
            color: #8d5d00;
            border-color: #f6d776;
        }

        .badge-info {
            background: #e8f1fb;
            color: #124e78;
            border-color: #bfd8f4;
        }

        .badge-primary {
            background: #e0f2fe;
            color: #075985;
            border-color: #93c5fd;
        }

        .badge-success {
            background: #e3f9e5;
            color: #1f5132;
            border-color: #b7e6be;
        }

        .list-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            padding: 12px 14px;
            border: 1px solid #e4e7eb;
            border-radius: 10px;
            background: #ffffff;
        }

        @media (max-width: 720px) {
            .assignment-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="shell">
        @auth
            <div class="nav">
                <div>
                    <div class="brand">Solmate Website</div>
                    <div class="muted">Logged in as {{ auth()->user()->name }} ({{ auth()->user()->role }})</div>
                </div>
                <div class="nav-links">
                    <a href="{{ route('dashboard') }}">Dashboard</a>
                    @if (in_array(auth()->user()->role, [\App\Models\User::ROLE_ADMIN, \App\Models\User::ROLE_TECHNICIAN], true))
                        <a href="{{ route('quotations.item-builder') }}">Quotation Item Builder</a>
                    @endif
                    @if (auth()->user()->role === \App\Models\User::ROLE_ADMIN)
                        <a href="{{ route('admin.profile.show') }}">Profile</a>
                        <a href="{{ route('admin.quotation-settings') }}">Quotation Settings</a>
                        <a href="{{ route('admin.pricing-catalog') }}">Pricing Catalog</a>
                        <a href="{{ route('admin.technicians.create') }}">Register Technician</a>
                        <a href="{{ route('admin.request-assignments') }}">Request Assignments</a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="secondary">Logout</button>
                    </form>
                </div>
            </div>
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

        @yield('content')
    </div>

    @stack('scripts')
</body>
</html>
