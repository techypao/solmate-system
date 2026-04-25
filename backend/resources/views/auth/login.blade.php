@extends('layouts.app', ['title' => 'Login'])

@section('content')
    <style>
        .auth-shell {
            position: relative;
            padding: 36px 0 56px;
        }

        .auth-shell::before,
        .auth-shell::after {
            content: '';
            position: absolute;
            border-radius: 999px;
            pointer-events: none;
            z-index: 0;
        }

        .auth-shell::before {
            top: 8px;
            right: -70px;
            width: 220px;
            height: 220px;
            background: radial-gradient(circle, rgba(212, 160, 23, .16), rgba(212, 160, 23, 0));
        }

        .auth-shell::after {
            left: -80px;
            bottom: 12px;
            width: 240px;
            height: 240px;
            background: radial-gradient(circle, rgba(59, 130, 246, .12), rgba(59, 130, 246, 0));
        }

        .auth-card {
            position: relative;
            z-index: 1;
            display: grid;
            grid-template-columns: minmax(280px, 420px) minmax(0, 1fr);
            overflow: hidden;
            border: 1px solid #dbe6f2;
            border-radius: 32px;
            background: #ffffff;
            box-shadow: 0 30px 70px rgba(15, 23, 42, .14);
        }

        .auth-panel-brand {
            position: relative;
            padding: 44px 38px;
            color: #ffffff;
            background:
                radial-gradient(circle at top left, rgba(255, 255, 255, .18), transparent 34%),
                linear-gradient(145deg, #102a43 0%, #1e4068 58%, #335f94 100%);
        }

        .auth-panel-brand::before,
        .auth-panel-brand::after {
            content: '';
            position: absolute;
            border-radius: 999px;
            opacity: .22;
        }

        .auth-panel-brand::before {
            right: -58px;
            bottom: -78px;
            width: 210px;
            height: 210px;
            background: linear-gradient(135deg, rgba(212, 160, 23, .95), rgba(212, 160, 23, .22));
        }

        .auth-panel-brand::after {
            left: -90px;
            top: 56px;
            width: 170px;
            height: 170px;
            background: rgba(147, 197, 253, .34);
        }

        .auth-brand-inner,
        .auth-panel-form {
            position: relative;
            z-index: 1;
        }

        .auth-brand {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 26px;
            color: #ffffff;
            font-size: 22px;
            font-weight: 800;
            text-decoration: none;
            letter-spacing: -.02em;
        }

        .auth-brand-mark {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 42px;
            height: 42px;
            border: 1px solid rgba(255, 255, 255, .28);
            border-radius: 14px;
            background: rgba(255, 255, 255, .12);
            font-size: 13px;
            font-weight: 900;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .auth-brand-copy {
            max-width: 320px;
        }

        .auth-kicker {
            display: inline-block;
            margin-bottom: 16px;
            color: #dbeafe;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: .18em;
            text-transform: uppercase;
        }

        .auth-brand-title {
            margin: 0 0 12px;
            color: #ffffff;
            font-size: clamp(2rem, 4vw, 2.9rem);
            line-height: 1.06;
            letter-spacing: -.03em;
        }

        .auth-brand-title span {
            color: #f4c542;
        }

        .auth-brand-text {
            margin: 0 0 26px;
            color: rgba(255, 255, 255, .82);
            font-size: 15px;
            line-height: 1.7;
        }

        .auth-brand-points {
            display: grid;
            gap: 12px;
            margin: 0 0 34px;
            padding: 0;
            list-style: none;
        }

        .auth-brand-points li {
            display: flex;
            align-items: center;
            gap: 10px;
            color: rgba(255, 255, 255, .88);
            font-size: 14px;
            line-height: 1.5;
        }

        .auth-brand-points li::before {
            content: '';
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #f4c542;
            box-shadow: 0 0 0 5px rgba(244, 197, 66, .15);
            flex-shrink: 0;
        }

        .auth-switch-card {
            display: inline-flex;
            flex-direction: column;
            gap: 10px;
            padding: 18px 20px;
            border: 1px solid rgba(255, 255, 255, .18);
            border-radius: 22px;
            background: rgba(255, 255, 255, .08);
            backdrop-filter: blur(8px);
        }

        .auth-switch-label {
            margin: 0;
            color: rgba(255, 255, 255, .72);
            font-size: 13px;
        }

        .auth-switch-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 46px;
            padding: 0 22px;
            border: 1px solid rgba(255, 255, 255, .48);
            border-radius: 999px;
            color: #ffffff;
            font-size: 14px;
            font-weight: 800;
            text-decoration: none;
            transition: background-color .2s ease, color .2s ease, border-color .2s ease;
        }

        .auth-switch-btn:hover {
            color: #102a43;
            background: #ffffff;
            border-color: #ffffff;
        }

        .auth-panel-form {
            padding: 44px 42px;
            background:
                linear-gradient(180deg, rgba(248, 250, 252, .88) 0%, rgba(255, 255, 255, 1) 14%),
                #ffffff;
        }

        .auth-form-head {
            margin-bottom: 28px;
        }

        .auth-form-kicker {
            display: inline-block;
            margin-bottom: 10px;
            color: #d4a017;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: .16em;
            text-transform: uppercase;
        }

        .auth-form-title {
            margin: 0 0 10px;
            color: #102a43;
            font-size: clamp(1.9rem, 4vw, 2.5rem);
            line-height: 1.08;
            letter-spacing: -.03em;
        }

        .auth-form-copy {
            margin: 0;
            max-width: 520px;
            color: #64748b;
            font-size: 15px;
            line-height: 1.7;
        }

        .auth-form-grid {
            display: grid;
            gap: 18px;
        }

        .auth-field {
            display: grid;
            gap: 8px;
        }

        .auth-field label,
        .auth-options label {
            color: #17324d;
            font-size: 13px;
            font-weight: 700;
        }

        .auth-field input {
            min-height: 54px;
            border: 1px solid #d7e3ef;
            border-radius: 16px;
            background: #f8fbff;
            padding: 0 16px;
            color: #0f172a;
            font-size: 15px;
            transition: border-color .2s ease, box-shadow .2s ease, background-color .2s ease;
        }

        .auth-field input:focus {
            border-color: #8db4de;
            background: #ffffff;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, .12);
            outline: none;
        }

        .auth-field .field-error {
            min-height: 18px;
            margin: 0;
            color: #b91c1c;
            font-size: 12px;
        }

        .auth-password-field {
            gap: 8px;
        }

        .auth-input-wrap {
            position: relative;
        }

        .auth-password-field input {
            padding-right: 80px;
        }

        .auth-password-field .password-toggle {
            position: absolute;
            top: 50%;
            right: 14px;
            transform: translateY(-50%);
            border: 0;
            background: transparent;
            color: #1e4068;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: .04em;
            text-transform: uppercase;
            cursor: pointer;
        }

        .auth-options {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
        }

        .auth-options .checkbox-inline {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            color: #475569;
            font-size: 14px;
            font-weight: 600;
        }

        .auth-options .checkbox-inline input {
            width: 16px;
            height: 16px;
            accent-color: #d4a017;
        }

        .auth-submit-row {
            display: flex;
            align-items: center;
            gap: 14px;
            flex-wrap: wrap;
            padding-top: 4px;
        }

        .auth-submit-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 54px;
            padding: 0 28px;
            border: 0;
            border-radius: 999px;
            background: linear-gradient(135deg, #d4a017 0%, #b8880f 100%);
            color: #ffffff;
            font-size: 15px;
            font-weight: 800;
            letter-spacing: .02em;
            box-shadow: 0 16px 30px rgba(212, 160, 23, .22);
            cursor: pointer;
            transition: transform .2s ease, box-shadow .2s ease, opacity .2s ease;
        }

        .auth-submit-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 20px 34px rgba(212, 160, 23, .26);
        }

        .auth-inline-link {
            color: #1e4068;
            font-size: 14px;
            font-weight: 700;
            text-decoration: none;
        }

        .auth-inline-link:hover {
            color: #102a43;
            text-decoration: underline;
        }

        @media (max-width: 960px) {
            .auth-card {
                grid-template-columns: 1fr;
            }

            .auth-panel-brand,
            .auth-panel-form {
                padding: 34px 26px;
            }
        }

        @media (max-width: 640px) {
            .auth-shell {
                padding: 20px 0 40px;
            }

            .auth-panel-brand,
            .auth-panel-form {
                padding: 28px 18px;
            }

            .auth-card {
                border-radius: 24px;
            }

            .auth-options,
            .auth-submit-row {
                align-items: stretch;
            }

            .auth-submit-btn {
                width: 100%;
            }
        }
    </style>

    <section class="auth-shell" aria-label="Login page">
        <div class="auth-card">
            <aside class="auth-panel-brand">
                <div class="auth-brand-inner">
                    <a href="{{ route('home') }}" class="auth-brand" aria-label="SolMate home">
                        <span class="auth-brand-mark">SM</span>
                        <span>SolMate</span>
                    </a>

                    <div class="auth-brand-copy">
                        <span class="auth-kicker">Secure Access</span>
                        <h1 class="auth-brand-title">Welcome <span>Back!</span></h1>
                        <p class="auth-brand-text">Sign in to continue managing your SolMate account, review updates, and stay connected with your solar service journey.</p>

                        <ul class="auth-brand-points">
                            <li>Access your customer and service pages in one place.</li>
                            <li>Review your latest SolMate updates with a clean, secure sign-in.</li>
                            <li>Keep your account ready for quotations, bookings, and tracking.</li>
                        </ul>

                        <div class="auth-switch-card">
                            <p class="auth-switch-label">Need a customer account first?</p>
                            <a href="{{ route('register') }}" class="auth-switch-btn">Create Account</a>
                        </div>
                    </div>
                </div>
            </aside>

            <div class="auth-panel-form">
                <div class="auth-form-head">
                    <span class="auth-form-kicker">Sign In</span>
                    <h2 class="auth-form-title">Login to SolMate</h2>
                    <p class="auth-form-copy">Use your website account to access your SolMate workspace. Enter your email and password to continue.</p>
                </div>

                <form method="POST" action="{{ route('login.attempt') }}" class="auth-form-grid">
                    @csrf

                    <div class="auth-field">
                        <label for="email">Email</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email">
                        <div class="field-error">@error('email') {{ $message }} @enderror</div>
                    </div>

                    <div class="auth-field auth-password-field">
                        <label for="password">Password</label>
                        <div class="auth-input-wrap">
                            <input id="password" type="password" name="password" required autocomplete="current-password">
                            <button type="button" class="password-toggle" data-password-toggle data-target="password">Show</button>
                        </div>
                        <div class="field-error">@error('password') {{ $message }} @enderror</div>
                    </div>

                    <div class="auth-options">
                        <label class="checkbox-inline">
                            <input type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
                            Remember me
                        </label>
                    </div>

                    <div class="auth-submit-row">
                        <button type="submit" class="auth-submit-btn">Sign In</button>
                        <a class="auth-inline-link" href="{{ route('register') }}">New to SolMate? Register</a>
                    </div>
                </form>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        document.querySelectorAll('[data-password-toggle]').forEach(button => {
            button.addEventListener('click', () => {
                const input = document.getElementById(button.dataset.target);

                if (!input) {
                    return;
                }

                const showingPassword = input.type === 'text';
                input.type = showingPassword ? 'password' : 'text';
                button.textContent = showingPassword ? 'Show' : 'Hide';
            });
        });
    </script>
@endpush
