@extends('layouts.app', ['title' => 'Login'])

@section('content')
    <style>
        .auth-shell {
            position: relative;
            display: flex;
            align-items: center;
            min-height: clamp(680px, calc(100vh - 120px), 860px);
            padding: 32px 0 52px;
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
            top: 28px;
            right: -60px;
            width: 260px;
            height: 260px;
            background: radial-gradient(circle, rgba(212, 160, 23, .18), rgba(212, 160, 23, 0));
        }

        .auth-shell::after {
            left: -70px;
            bottom: 20px;
            width: 280px;
            height: 280px;
            background: radial-gradient(circle, rgba(32, 167, 201, .16), rgba(32, 167, 201, 0));
        }

        .auth-card {
            position: relative;
            z-index: 1;
            display: grid;
            grid-template-columns: minmax(300px, 390px) minmax(0, 1fr);
            width: min(100%, 1080px);
            margin: 0 auto;
            overflow: hidden;
            border: 1px solid #DDE7EE;
            border-radius: 32px;
            background:
                linear-gradient(180deg, rgba(255, 255, 255, .95), rgba(248, 251, 255, .98)),
                #ffffff;
            box-shadow: 0 34px 80px rgba(15, 23, 42, .14);
        }

        .auth-panel-brand {
            position: relative;
            display: flex;
            padding: 46px 38px;
            color: #ffffff;
            background:
                radial-gradient(circle at top left, rgba(255, 255, 255, .18), transparent 34%),
                linear-gradient(145deg, #123A5A 0%, #20A7C9 58%, #20A7C9 100%);
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

        .auth-brand-inner {
            display: flex;
            flex-direction: column;
            width: 100%;
        }

        .auth-brand {
            display: inline-flex;
            align-items: center;
            gap: 0;
            margin-bottom: 26px;
            text-decoration: none;
            width: fit-content;
            padding: 12px 16px;
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.96);
            box-shadow: 0 18px 38px rgba(15, 47, 74, 0.22);
        }

        .auth-brand-logo {
            display: block;
            width: auto;
            height: 44px;
        }

        .auth-brand-copy {
            display: flex;
            flex: 1;
            flex-direction: column;
            max-width: 320px;
        }

        .auth-kicker {
            display: inline-block;
            margin-bottom: 16px;
            color: #EAF9FD;
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
            color: #E6C200;
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
            margin: 0 0 28px;
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
            background: #E6C200;
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
            margin-top: auto;
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
            color: #123A5A;
            background: #ffffff;
            border-color: #ffffff;
        }

        .auth-panel-form {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 38px;
            background:
                radial-gradient(circle at top right, rgba(212, 160, 23, .07), transparent 28%),
                linear-gradient(180deg, rgba(248, 250, 252, .88) 0%, rgba(255, 255, 255, 1) 18%),
                #ffffff;
        }

        .auth-form-surface {
            width: min(100%, 560px);
            padding: 32px clamp(22px, 3vw, 34px);
            border: 1px solid #DDE7EE;
            border-radius: 28px;
            background: linear-gradient(180deg, rgba(255, 255, 255, .98), rgba(248, 251, 255, 1));
            box-shadow: 0 24px 54px rgba(15, 23, 42, .08);
        }

        .auth-form-head {
            margin-bottom: 28px;
        }

        .auth-form-kicker {
            display: inline-block;
            margin-bottom: 10px;
            color: #F4D000;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: .16em;
            text-transform: uppercase;
        }

        .auth-form-title {
            margin: 0 0 10px;
            color: #123A5A;
            font-size: clamp(1.9rem, 4vw, 2.5rem);
            line-height: 1.08;
            letter-spacing: -.03em;
        }

        .auth-form-copy {
            margin: 0;
            max-width: 520px;
            color: #5E7288;
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
            color: #123A5A;
            font-size: 13px;
            font-weight: 700;
        }

        .auth-field input {
            width: 100%;
            min-height: 54px;
            border: 1px solid #DDE7EE;
            border-radius: 16px;
            background: #ffffff;
            padding: 0 16px;
            color: #0F2F4A;
            font-size: 15px;
            transition: border-color .2s ease, box-shadow .2s ease, background-color .2s ease;
        }

        .auth-field input::placeholder {
            color: #7F92A3;
        }

        .auth-field input:focus {
            border-color: #7DDFF2;
            background: #ffffff;
            box-shadow: 0 0 0 4px rgba(32, 167, 201, .14);
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
            color: #20A7C9;
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
            color: #5E7288;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
        }

        .auth-options .checkbox-inline input {
            width: 16px;
            height: 16px;
            accent-color: #F4D000;
        }

        .auth-submit-row {
            display: grid;
            gap: 14px;
            padding-top: 4px;
        }

        .auth-submit-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            min-height: 54px;
            padding: 0 28px;
            border: 0;
            border-radius: 999px;
            background: linear-gradient(135deg, #F4D000 0%, #E6C200 100%);
            color: #0F2F4A;
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
            display: inline-flex;
            justify-content: center;
            color: #20A7C9;
            font-size: 14px;
            font-weight: 700;
            text-decoration: none;
        }

        .auth-inline-link:hover {
            color: #123A5A;
            text-decoration: underline;
        }

        @media (max-width: 960px) {
            .auth-shell {
                min-height: auto;
            }

            .auth-card {
                grid-template-columns: 1fr;
            }

            .auth-panel-brand,
            .auth-panel-form {
                padding: 34px 26px;
            }

            .auth-form-surface {
                width: 100%;
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

            .auth-form-surface {
                padding: 24px 18px;
                border-radius: 22px;
            }

        }
    </style>

    <section class="auth-shell" aria-label="Login page">
        <div class="auth-card">
            <aside class="auth-panel-brand">
                <div class="auth-brand-inner">
                    <a href="{{ route('home') }}" class="auth-brand" aria-label="RDY home">
                        <img src="{{ asset('images/rdy-logo-transparent.png') }}" alt="RDY logo" class="auth-brand-logo">
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
                <div class="auth-form-surface">
                    <div class="auth-form-head">
                        <span class="auth-form-kicker">Sign In</span>
                        <h2 class="auth-form-title">Login to SolMate</h2>
                        <p class="auth-form-copy">Use your website account to access your SolMate workspace. Enter your email and password to continue.</p>
                    </div>

                    <form method="POST" action="{{ route('login.attempt') }}" class="auth-form-grid">
                        @csrf

                        <div class="auth-field">
                            <label for="email">Email</label>
                            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email" placeholder="you@example.com">
                            <div class="field-error">@error('email') {{ $message }} @enderror</div>
                        </div>

                        <div class="auth-field auth-password-field">
                            <label for="password">Password</label>
                            <div class="auth-input-wrap">
                                <input id="password" type="password" name="password" required autocomplete="current-password" placeholder="Enter your password">
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
