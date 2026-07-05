@extends('layouts.app', ['title' => 'Register'])

@section('content')
    <style>
        .auth-shell {
            position: relative;
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
            width: min(100%, 1120px);
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
            width: fit-content;
            margin-bottom: 26px;
            padding: 12px 16px;
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.96);
            box-shadow: 0 18px 38px rgba(15, 47, 74, 0.22);
            text-decoration: none;
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

        .auth-brand-title {
            margin: 0 0 12px;
            color: #ffffff;
            font-size: clamp(2rem, 4vw, 2.9rem);
            line-height: 1.06;
            letter-spacing: 0;
        }

        .auth-brand-title span {
            color: #E6C200;
        }

        .auth-switch-card {
            display: inline-flex;
            flex-direction: column;
            gap: 10px;
            margin-top: auto;
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
            color: #123A5A;
            background: #ffffff;
            border-color: #ffffff;
        }

        .auth-panel-form {
            display: flex;
            justify-content: center;
            padding: 38px;
            background:
                radial-gradient(circle at top right, rgba(212, 160, 23, .07), transparent 28%),
                linear-gradient(180deg, rgba(248, 250, 252, .88) 0%, rgba(255, 255, 255, 1) 18%),
                #ffffff;
        }

        .auth-form-surface {
            width: min(100%, 680px);
            padding: 32px clamp(22px, 3vw, 34px);
            border: 1px solid #DDE7EE;
            border-radius: 28px;
            background: linear-gradient(180deg, rgba(255, 255, 255, .98), rgba(248, 251, 255, 1));
            box-shadow: 0 24px 54px rgba(15, 23, 42, .08);
        }

        .auth-form-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 18px;
            margin-bottom: 22px;
        }

        .auth-form-kicker {
            display: inline-block;
            margin-bottom: 10px;
            color: #D4A017;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: .14em;
            text-transform: uppercase;
        }

        .auth-form-title {
            margin: 0;
            color: #123A5A;
            font-size: clamp(1.75rem, 4vw, 2.35rem);
            line-height: 1.08;
            letter-spacing: 0;
        }

        .auth-step-count {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 74px;
            min-height: 34px;
            padding: 0 12px;
            border: 1px solid rgba(32, 167, 201, .22);
            border-radius: 999px;
            background: #EAF9FD;
            color: #123A5A;
            font-size: 12px;
            font-weight: 800;
            white-space: nowrap;
        }

        .auth-stepper {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 8px;
            margin-bottom: 18px;
        }

        .auth-step-tab {
            position: relative;
            display: grid;
            gap: 6px;
            min-width: 0;
            padding: 12px;
            border: 1px solid #DDE7EE;
            border-radius: 18px;
            background: #ffffff;
            color: #5E7288;
            text-align: left;
            cursor: pointer;
            transition: border-color .2s ease, background-color .2s ease, box-shadow .2s ease;
        }

        .auth-step-tab:disabled {
            cursor: not-allowed;
            opacity: .64;
        }

        .auth-step-tab.is-active {
            border-color: rgba(32, 167, 201, .48);
            background: linear-gradient(180deg, #F7FCFE, #FFFFFF);
            box-shadow: 0 12px 26px rgba(32, 167, 201, .1);
        }

        .auth-step-tab.is-complete {
            border-color: rgba(212, 160, 23, .38);
        }

        .auth-step-tab-number {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: #EEF4F8;
            color: #123A5A;
            font-size: 12px;
            font-weight: 800;
        }

        .auth-step-tab.is-active .auth-step-tab-number,
        .auth-step-tab.is-complete .auth-step-tab-number {
            background: #F4D000;
            color: #0F2F4A;
        }

        .auth-step-tab-label {
            overflow: hidden;
            color: #123A5A;
            font-size: 13px;
            font-weight: 800;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .auth-progress {
            height: 8px;
            margin-bottom: 24px;
            overflow: hidden;
            border-radius: 999px;
            background: #E8F0F6;
        }

        .auth-progress-bar {
            width: 33.333%;
            height: 100%;
            border-radius: inherit;
            background: linear-gradient(90deg, #F4D000 0%, #20A7C9 100%);
            transition: width .24s ease;
        }

        .auth-feedback {
            margin-bottom: 18px;
        }

        .auth-success-toast {
            position: fixed;
            top: 28px;
            right: 28px;
            z-index: 80;
            display: grid;
            gap: 8px;
            width: min(100% - 32px, 380px);
            padding: 18px 20px;
            border: 1px solid rgba(212, 160, 23, .32);
            border-radius: 22px;
            background: linear-gradient(145deg, rgba(16, 42, 67, .98), rgba(30, 64, 104, .97));
            box-shadow: 0 24px 54px rgba(15, 23, 42, .22);
            color: #ffffff;
            opacity: 0;
            pointer-events: none;
            transform: translateY(-12px);
            transition: opacity .24s ease, transform .24s ease;
        }

        .auth-success-toast.is-visible {
            opacity: 1;
            pointer-events: auto;
            transform: translateY(0);
        }

        .auth-success-toast::before {
            content: '';
            position: absolute;
            inset: 0 auto 0 0;
            width: 5px;
            border-radius: 22px 0 0 22px;
            background: linear-gradient(180deg, #E6C200 0%, #F4D000 100%);
        }

        .auth-success-toast-badge {
            display: inline-flex;
            align-items: center;
            width: fit-content;
            padding: 6px 10px;
            border-radius: 999px;
            background: rgba(244, 197, 66, .14);
            color: #f8dd84;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: .12em;
            text-transform: uppercase;
        }

        .auth-success-toast-title {
            margin: 0;
            color: #ffffff;
            font-size: 16px;
            font-weight: 800;
            line-height: 1.45;
        }

        .auth-success-toast-copy {
            margin: 0;
            color: rgba(255, 255, 255, .8);
            font-size: 13px;
            line-height: 1.6;
        }

        .auth-form-grid {
            display: grid;
            gap: 18px 16px;
        }

        .auth-step {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px 16px;
        }

        .auth-step[hidden] {
            display: none;
        }

        .auth-step-heading {
            grid-column: 1 / -1;
            display: grid;
            gap: 6px;
            margin-bottom: 2px;
        }

        .auth-step-title {
            margin: 0;
            color: #123A5A;
            font-size: 18px;
            font-weight: 800;
            line-height: 1.3;
        }

        .auth-field {
            display: grid;
            gap: 8px;
            align-content: start;
        }

        .auth-field.auth-field-full,
        .auth-actions,
        .auth-inline-link {
            grid-column: 1 / -1;
        }

        .auth-field label {
            color: #123A5A;
            font-size: 13px;
            font-weight: 700;
        }

        .auth-field input,
        .auth-field select {
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

        .auth-field select {
            appearance: none;
            background-image:
                linear-gradient(45deg, transparent 50%, #5E7288 50%),
                linear-gradient(135deg, #5E7288 50%, transparent 50%);
            background-position:
                calc(100% - 20px) 24px,
                calc(100% - 14px) 24px;
            background-size: 6px 6px, 6px 6px;
            background-repeat: no-repeat;
            padding-right: 42px;
        }

        .auth-field input::placeholder {
            color: #7F92A3;
        }

        .auth-field input:focus,
        .auth-field select:focus {
            border-color: #7DDFF2;
            background-color: #ffffff;
            box-shadow: 0 0 0 4px rgba(32, 167, 201, .14);
            outline: none;
        }

        .auth-field input:disabled,
        .auth-field select:disabled {
            background: #F3F7FA;
            color: #7F92A3;
        }

        .auth-password-wrap {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 74px;
            gap: 10px;
        }

        .auth-password-wrap input {
            min-width: 0;
        }

        .auth-password-toggle {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 74px;
            min-height: 54px;
            border: 1px solid #DDE7EE;
            border-radius: 16px;
            background: #EEF4F8;
            color: #123A5A;
            font-size: 13px;
            font-weight: 800;
            text-align: center;
            white-space: nowrap;
            cursor: pointer;
            transition: background-color .2s ease, border-color .2s ease;
        }

        .auth-password-toggle:hover {
            border-color: #BFD0DC;
            background: #E6EFF5;
        }

        .auth-password-checklist {
            display: grid;
            gap: 8px;
            margin: 2px 0 0;
            padding: 0;
            list-style: none;
        }

        .auth-password-check {
            display: flex;
            align-items: center;
            gap: 9px;
            color: #5E7288;
            font-size: 12px;
            line-height: 1.4;
        }

        .auth-password-check::before {
            content: '';
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 18px;
            height: 18px;
            border: 1px solid #C8D7E2;
            border-radius: 50%;
            background: #ffffff;
            color: #ffffff;
            font-size: 12px;
            font-weight: 800;
            flex-shrink: 0;
        }

        .auth-password-check.is-met {
            color: #0C6B3A;
        }

        .auth-password-check.is-met::before {
            content: '✓';
            border-color: #0C8D4A;
            background: #0C8D4A;
        }

        .auth-field-support {
            display: grid;
            align-content: start;
            gap: 6px;
            min-height: 42px;
        }

        .auth-field-helper {
            margin: 0;
            color: #5E7288;
            font-size: 12px;
            line-height: 1.5;
        }

        .auth-field .field-error {
            min-height: 18px;
            margin: 0;
            color: #b91c1c;
            font-size: 12px;
        }

        .error-box,
        .status {
            padding: 14px 16px;
            border-radius: 16px;
            font-size: 13px;
            line-height: 1.5;
        }

        .error-box {
            border: 1px solid #F0B4B4;
            background: #FFF2F2;
            color: #991B1B;
        }

        .status {
            border: 1px solid #A7D7BC;
            background: #F0FAF4;
            color: #0C6B3A;
        }

        .auth-actions {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding-top: 4px;
        }

        .auth-action-spacer {
            flex: 1;
        }

        .auth-submit-btn,
        .auth-secondary-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 52px;
            padding: 0 24px;
            border-radius: 999px;
            font-size: 15px;
            font-weight: 800;
            cursor: pointer;
            transition: transform .2s ease, box-shadow .2s ease, opacity .2s ease, background-color .2s ease;
        }

        .auth-submit-btn {
            min-width: 170px;
            border: 0;
            background: linear-gradient(135deg, #F4D000 0%, #E6C200 100%);
            color: #0F2F4A;
            box-shadow: 0 16px 30px rgba(212, 160, 23, .22);
        }

        .auth-submit-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 20px 34px rgba(212, 160, 23, .26);
        }

        .auth-secondary-btn {
            border: 1px solid #DDE7EE;
            background: #ffffff;
            color: #123A5A;
        }

        .auth-secondary-btn:hover {
            background: #F7FBFD;
        }

        .auth-submit-btn:disabled,
        .auth-secondary-btn:disabled {
            opacity: .7;
            cursor: wait;
            transform: none;
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
            .auth-card {
                grid-template-columns: 1fr;
            }

            .auth-panel-brand,
            .auth-panel-form {
                padding: 34px 26px;
            }

            .auth-brand-copy,
            .auth-form-surface {
                max-width: none;
                width: 100%;
            }
        }

        @media (max-width: 720px) {
            .auth-form-head {
                display: grid;
            }

            .auth-stepper,
            .auth-step {
                grid-template-columns: 1fr;
            }

            .auth-step-tab {
                grid-template-columns: auto minmax(0, 1fr);
                align-items: center;
            }

            .auth-field-support {
                min-height: auto;
            }

            .auth-actions {
                display: grid;
                grid-template-columns: 1fr;
            }

            .auth-submit-btn,
            .auth-secondary-btn {
                width: 100%;
            }

            .auth-action-spacer {
                display: none;
            }
        }

        @media (max-width: 640px) {
            .auth-shell {
                padding: 20px 0 40px;
            }

            .auth-success-toast {
                top: 18px;
                right: 16px;
                left: 16px;
                width: auto;
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

    <div class="auth-success-toast" id="register-success-toast" hidden role="status" aria-live="polite">
        <span class="auth-success-toast-badge">Success</span>
        <p class="auth-success-toast-title" id="register-success-toast-message">Account successfully created! Redirecting to login page...</p>
        <p class="auth-success-toast-copy">Please verify your email before signing in.</p>
    </div>

    <section class="auth-shell" aria-label="Register page">
        <div class="auth-card">
            <aside class="auth-panel-brand">
                <div class="auth-brand-inner">
                    <a href="{{ route('home') }}" class="auth-brand" aria-label="RDY home">
                        <img src="{{ asset('images/rdy-logo-transparent.png') }}" alt="RDY logo" class="auth-brand-logo">
                    </a>

                    <div class="auth-brand-copy">
                        <h1 class="auth-brand-title">Join <span>SolMate!</span></h1>

                        <div class="auth-switch-card">
                            <p class="auth-switch-label">Already registered with SolMate?</p>
                            <a href="{{ route('login') }}" class="auth-switch-btn">Sign In</a>
                        </div>
                    </div>
                </div>
            </aside>

            <div class="auth-panel-form">
                <div class="auth-form-surface">
                    <div class="auth-form-head">
                        <div>
                            <span class="auth-form-kicker">Create Account</span>
                            <h2 class="auth-form-title">Register for SolMate</h2>
                        </div>
                        <span class="auth-step-count" id="register-step-count">Step 1 of 3</span>
                    </div>

                    <div class="auth-stepper" aria-label="Registration steps">
                        <button class="auth-step-tab is-active" type="button" data-step-indicator="0" aria-current="step">
                            <span class="auth-step-tab-number">1</span>
                            <span class="auth-step-tab-label">Personal</span>
                        </button>
                        <button class="auth-step-tab" type="button" data-step-indicator="1" disabled>
                            <span class="auth-step-tab-number">2</span>
                            <span class="auth-step-tab-label">Address</span>
                        </button>
                        <button class="auth-step-tab" type="button" data-step-indicator="2" disabled>
                            <span class="auth-step-tab-number">3</span>
                            <span class="auth-step-tab-label">Password</span>
                        </button>
                    </div>

                    <div class="auth-progress" aria-hidden="true">
                        <div class="auth-progress-bar" id="register-progress-bar"></div>
                    </div>

                    <div class="auth-feedback">
                        <div class="status" id="register-success-box" hidden>Registration successful. Please verify your email before logging in.</div>
                        <div class="error-box" id="register-error-box" hidden></div>
                    </div>

                    <form
                        class="auth-form-grid"
                        id="register-form"
                        action="{{ route('register.store') }}"
                        method="post"
                        novalidate
                        data-register-api-url="{{ url('/api/register') }}"
                        data-login-page-url="{{ route('login') }}"
                        data-registration-success-message="{{ session('registration_success', '') }}"
                    >
                        @csrf
                        <input id="address" type="hidden" name="address" value="{{ old('address') }}">

                        <div class="auth-step" data-register-step="0">
                            <div class="auth-step-heading">
                                <h3 class="auth-step-title">Personal Details</h3>
                            </div>

                            <div class="auth-field">
                                <label for="first_name">First Name</label>
                                <input id="first_name" type="text" name="first_name" value="{{ old('first_name') }}" required autofocus autocomplete="given-name" placeholder="Enter your first name">
                                <div class="field-error" data-error-for="first_name">@error('first_name') {{ $message }} @enderror</div>
                            </div>

                            <div class="auth-field">
                                <label for="last_name">Last Name</label>
                                <input id="last_name" type="text" name="last_name" value="{{ old('last_name') }}" required autocomplete="family-name" placeholder="Enter your last name">
                                <div class="field-error" data-error-for="last_name">@error('last_name') {{ $message }} @enderror</div>
                            </div>

                            <div class="auth-field auth-field-full">
                                <label for="email">Email</label>
                                <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email" placeholder="you@example.com">
                                <div class="field-error" data-error-for="email">@error('email') {{ $message }} @enderror</div>
                            </div>

                            <div class="auth-field">
                                <label for="contact_number">Contact Number</label>
                                <input
                                    id="contact_number"
                                    type="text"
                                    name="contact_number"
                                    value="{{ old('contact_number') }}"
                                    required
                                    autocomplete="tel"
                                    inputmode="numeric"
                                    pattern="[0-9]{11}"
                                    maxlength="11"
                                    placeholder="09XXXXXXXXX"
                                >
                                <div class="auth-field-support">
                                    <div class="field-error" data-error-for="contact_number">@error('contact_number') {{ $message }} @enderror</div>
                                </div>
                            </div>

                            <div class="auth-field">
                                <label for="landline_number">Landline Number (Optional)</label>
                                <input id="landline_number" type="text" name="landline_number" value="{{ old('landline_number') }}" autocomplete="tel-national" maxlength="30" placeholder="e.g. (02) 8123-4567">
                                <div class="auth-field-support">
                                    <div class="field-error" data-error-for="landline_number">@error('landline_number') {{ $message }} @enderror</div>
                                </div>
                            </div>
                        </div>

                        <div class="auth-step" data-register-step="1" hidden>
                            <div class="auth-step-heading">
                                <h3 class="auth-step-title">Address Details</h3>
                            </div>

                            <div class="auth-field">
                                <label for="house_number">House / Unit / Block / Lot</label>
                                <input id="house_number" type="text" name="house_number" value="{{ old('house_number') }}" required autocomplete="address-line1" placeholder="e.g. Unit 4B, Block 8 Lot 12">
                                <div class="field-error" data-error-for="house_number"></div>
                            </div>

                            <div class="auth-field">
                                <label for="street_name">Street Name (Optional)</label>
                                <input id="street_name" type="text" name="street_name" value="{{ old('street_name') }}" autocomplete="address-line2" placeholder="e.g. Mabini Street">
                                <div class="field-error" data-error-for="street_name"></div>
                            </div>

                            <div class="auth-field auth-field-full">
                                <label for="barangay">Barangay (Optional)</label>
                                <input id="barangay" type="text" name="barangay" value="{{ old('barangay') }}" autocomplete="address-line3" placeholder="e.g. Barangay San Antonio">
                                <div class="auth-field-support">
                                    <div class="field-error" data-error-for="barangay"></div>
                                </div>
                            </div>

                            <div class="auth-field">
                                <label for="province_code">Province / NCR</label>
                                <select id="province_code" name="province_code" required autocomplete="address-level1">
                                    <option value="">Loading Philippine locations...</option>
                                </select>
                                <div class="auth-field-support">
                                    <p class="auth-field-helper" id="location-status">Loading provinces and NCR.</p>
                                    <div class="field-error" data-error-for="province_code"></div>
                                </div>
                            </div>

                            <div class="auth-field">
                                <label for="city_municipality">City / Municipality</label>
                                <select id="city_municipality" name="city_municipality" required autocomplete="address-level2" disabled>
                                    <option value="">Select province first</option>
                                </select>
                                <div class="auth-field-support">
                                    <div class="field-error" data-error-for="city_municipality"></div>
                                </div>
                            </div>

                            <div class="auth-field auth-field-full">
                                <div class="field-error" data-error-for="address">@error('address') {{ $message }} @enderror</div>
                            </div>
                        </div>

                        <div class="auth-step" data-register-step="2" hidden>
                            <div class="auth-step-heading">
                                <h3 class="auth-step-title">Secure Account</h3>
                            </div>

                            <div class="auth-field auth-field-full">
                                <label for="password">Password</label>
                                <div class="auth-password-wrap">
                                    <input id="password" type="password" name="password" required autocomplete="new-password" placeholder="Create a secure password">
                                    <button class="auth-password-toggle" type="button" data-toggle-password="password" aria-label="Show password">Show</button>
                                </div>
                                <div class="auth-field-support">
                                    <ul class="auth-password-checklist" aria-label="Password requirements">
                                        <li class="auth-password-check" data-password-rule="length" role="checkbox" aria-checked="false">At least 8 characters</li>
                                        <li class="auth-password-check" data-password-rule="uppercase" role="checkbox" aria-checked="false">One uppercase letter</li>
                                        <li class="auth-password-check" data-password-rule="special" role="checkbox" aria-checked="false">One special character</li>
                                    </ul>
                                    <div class="field-error" data-error-for="password">@error('password') {{ $message }} @enderror</div>
                                </div>
                            </div>

                            <div class="auth-field auth-field-full">
                                <label for="password_confirmation">Confirm Password</label>
                                <div class="auth-password-wrap">
                                    <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="Re-enter your password">
                                    <button class="auth-password-toggle" type="button" data-toggle-password="password_confirmation" aria-label="Show password confirmation">Show</button>
                                </div>
                                <div class="auth-field-support">
                                    <div class="field-error" data-error-for="password_confirmation">@error('password_confirmation') {{ $message }} @enderror</div>
                                </div>
                            </div>
                        </div>

                        <div class="auth-actions">
                            <button type="button" class="auth-secondary-btn" id="register-back-button" hidden>Back</button>
                            <span class="auth-action-spacer" aria-hidden="true"></span>
                            <button type="button" class="auth-submit-btn" id="register-next-button">Next</button>
                            <button type="submit" class="auth-submit-btn" id="register-submit-button" hidden>Create Account</button>
                        </div>

                        <a class="auth-inline-link" href="{{ route('login') }}">Already have an account? Login</a>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        (() => {
            const attachRegisterSubmitHandler = () => {
                const form = document.getElementById('register-form');
                const successToast = document.getElementById('register-success-toast');
                const successToastMessage = document.getElementById('register-success-toast-message');

                if (!form || form.dataset.submitHandlerAttached === 'true') {
                    return;
                }

                form.dataset.submitHandlerAttached = 'true';

                const steps = Array.from(form.querySelectorAll('[data-register-step]'));
                const stepIndicators = Array.from(document.querySelectorAll('[data-step-indicator]'));
                const stepCount = document.getElementById('register-step-count');
                const progressBar = document.getElementById('register-progress-bar');
                const successBox = document.getElementById('register-success-box');
                const errorBox = document.getElementById('register-error-box');
                const backButton = document.getElementById('register-back-button');
                const nextButton = document.getElementById('register-next-button');
                const submitButton = document.getElementById('register-submit-button');
                const provinceSelect = document.getElementById('province_code');
                const citySelect = document.getElementById('city_municipality');
                const addressInput = document.getElementById('address');
                const locationStatus = document.getElementById('location-status');
                const passwordInput = document.getElementById('password');
                const passwordRuleItems = Array.from(form.querySelectorAll('[data-password-rule]'));
                const registerApiUrl = form.dataset.registerApiUrl || '/api/register';
                const loginPageUrl = form.dataset.loginPageUrl || '/login';
                const flashedSuccessMessage = form.dataset.registrationSuccessMessage || '';
                const locationApiBaseUrl = 'https://psgc.gitlab.io/api';
                const ncrLocation = {
                    code: '130000000',
                    name: 'Metro Manila / NCR',
                    kind: 'region',
                };

                let currentStep = 0;
                let maxUnlockedStep = 0;
                let locationLoadFailed = false;

                const fieldsByStep = [
                    ['first_name', 'last_name', 'email', 'contact_number'],
                    ['house_number', 'province_code', 'city_municipality'],
                    ['password', 'password_confirmation'],
                ];

                const fieldLabels = {
                    first_name: 'First name',
                    last_name: 'Last name',
                    email: 'Email',
                    contact_number: 'Contact number',
                    house_number: 'House, unit, block, or lot',
                    street_name: 'Street name',
                    barangay: 'Barangay',
                    province_code: 'Province or NCR',
                    city_municipality: 'City or municipality',
                    password: 'Password',
                    password_confirmation: 'Password confirmation',
                };

                const customerNamePattern = /^(?=.*\p{L})[\p{L}\s.'-]+$/u;

                const setFieldError = (field, message) => {
                    const fieldError = form.querySelector(`[data-error-for="${field}"]`);

                    if (fieldError) {
                        fieldError.textContent = message;
                    }
                };

                const getField = field => form.elements[field];

                const getFieldValue = field => {
                    const element = getField(field);

                    if (!element) {
                        return '';
                    }

                    return String(element.value || '').trim();
                };

                const normalizeCustomerName = value => value
                    .trim()
                    .replace(/\s+/g, ' ')
                    .toLocaleLowerCase('en-PH')
                    .replace(/(^|[\s.'-])(\p{L})/gu, (match, separator, letter) => `${separator}${letter.toLocaleUpperCase('en-PH')}`);

                const sanitizeCustomerName = value => value.replace(/[^\p{L}\s.'-]/gu, '');

                const clearErrors = () => {
                    if (successBox) {
                        successBox.hidden = true;
                    }

                    if (errorBox) {
                        errorBox.hidden = true;
                        errorBox.textContent = '';
                    }

                    form.querySelectorAll('[data-error-for]').forEach(node => {
                        node.textContent = '';
                    });
                };

                const showGeneralError = message => {
                    if (!errorBox) {
                        return;
                    }

                    errorBox.textContent = message;
                    errorBox.hidden = false;
                };

                const showSuccessToast = message => {
                    if (!successToast || !successToastMessage) {
                        return;
                    }

                    successToastMessage.textContent = message;
                    successToast.hidden = false;

                    window.requestAnimationFrame(() => {
                        successToast.classList.add('is-visible');
                    });
                };

                const redirectToLoginWithDelay = () => {
                    window.setTimeout(() => {
                        window.location.href = loginPageUrl;
                    }, 2200);
                };

                const selectedOptionText = selectElement => {
                    if (!selectElement || selectElement.selectedIndex < 0) {
                        return '';
                    }

                    const option = selectElement.options[selectElement.selectedIndex];

                    return option?.dataset.name || option?.textContent?.trim() || '';
                };

                const buildAddress = () => {
                    const parts = [
                        getFieldValue('house_number'),
                        getFieldValue('street_name'),
                        getFieldValue('barangay'),
                        selectedOptionText(citySelect),
                        selectedOptionText(provinceSelect),
                    ].filter(Boolean);

                    return parts.join(', ');
                };

                const syncAddressInput = () => {
                    if (!addressInput) {
                        return;
                    }

                    addressInput.value = buildAddress();
                };

                const buildRegisterState = () => {
                    syncAddressInput();

                    return {
                        first_name: normalizeCustomerName(getFieldValue('first_name')),
                        last_name: normalizeCustomerName(getFieldValue('last_name')),
                        email: getFieldValue('email'),
                        contact_number: getFieldValue('contact_number').replace(/\D/g, '').slice(0, 11),
                        address: String(addressInput?.value || '').trim(),
                        landline_number: getFieldValue('landline_number'),
                        password: String(getField('password')?.value || ''),
                        password_confirmation: String(getField('password_confirmation')?.value || ''),
                    };
                };

                const focusField = field => {
                    const element = getField(field);

                    if (element && typeof element.focus === 'function') {
                        element.focus({ preventScroll: false });
                    }
                };

                const getPasswordValidationError = password => {
                    if (password.length < 8) {
                        return 'Password must be at least 8 characters.';
                    }

                    if (!/[A-Z]/.test(password)) {
                        return 'Password must contain at least one uppercase letter.';
                    }

                    if (!/[^A-Za-z0-9]/.test(password)) {
                        return 'Password must contain at least one special character.';
                    }

                    return '';
                };

                const getPasswordRuleState = password => ({
                    length: password.length >= 8,
                    uppercase: /[A-Z]/.test(password),
                    special: /[^A-Za-z0-9]/.test(password),
                });

                const updatePasswordChecklist = () => {
                    const ruleState = getPasswordRuleState(String(passwordInput?.value || ''));

                    passwordRuleItems.forEach(item => {
                        const rule = item.dataset.passwordRule || '';
                        const isMet = Boolean(ruleState[rule]);

                        item.classList.toggle('is-met', isMet);
                        item.setAttribute('aria-checked', isMet ? 'true' : 'false');
                    });
                };

                const validateStep = stepIndex => {
                    clearErrors();

                    for (const field of fieldsByStep[stepIndex] || []) {
                        if (!getFieldValue(field)) {
                            setFieldError(field, `${fieldLabels[field]} is required.`);
                            focusField(field);
                            return false;
                        }
                    }

                    if (stepIndex === 0) {
                        const email = getField('email');
                        const contactNumber = getFieldValue('contact_number').replace(/\D/g, '');
                        const firstName = getFieldValue('first_name');
                        const lastName = getFieldValue('last_name');

                        if (!customerNamePattern.test(firstName)) {
                            setFieldError('first_name', 'First name may only contain letters, spaces, periods, apostrophes, and hyphens.');
                            focusField('first_name');
                            return false;
                        }

                        if (!customerNamePattern.test(lastName)) {
                            setFieldError('last_name', 'Last name may only contain letters, spaces, periods, apostrophes, and hyphens.');
                            focusField('last_name');
                            return false;
                        }

                        if (email && !email.validity.valid) {
                            setFieldError('email', 'Please enter a valid email address.');
                            focusField('email');
                            return false;
                        }

                        if (contactNumber.length !== 11) {
                            setFieldError('contact_number', 'Contact number must be exactly 11 digits.');
                            focusField('contact_number');
                            return false;
                        }
                    }

                    if (stepIndex === 1) {
                        if (locationLoadFailed) {
                            setFieldError('province_code', 'Philippine location list is unavailable. Please refresh and try again.');
                            focusField('province_code');
                            return false;
                        }

                        syncAddressInput();

                        if (!String(addressInput?.value || '').trim()) {
                            setFieldError('address', 'Complete address details are required.');
                            focusField('house_number');
                            return false;
                        }
                    }

                    if (stepIndex === 2) {
                        const password = String(getField('password')?.value || '');
                        const passwordConfirmation = String(getField('password_confirmation')?.value || '');
                        const passwordError = getPasswordValidationError(password);

                        if (passwordError) {
                            setFieldError('password', passwordError);
                            focusField('password');
                            return false;
                        }

                        if (password !== passwordConfirmation) {
                            setFieldError('password_confirmation', 'Password confirmation does not match.');
                            focusField('password_confirmation');
                            return false;
                        }
                    }

                    return true;
                };

                const validateAllSteps = () => {
                    for (let index = 0; index < steps.length; index += 1) {
                        if (!validateStep(index)) {
                            showStep(index);
                            return false;
                        }
                    }

                    clearErrors();
                    return true;
                };

                const showFieldErrors = errors => {
                    Object.entries(errors).forEach(([field, messages]) => {
                        const message = Array.isArray(messages) ? (messages[0] || '') : String(messages || '');
                        const mappedField = field === 'address' ? 'address' : field;
                        setFieldError(mappedField, message);
                    });
                };

                const stepIndexForErrors = errors => {
                    const errorFields = Object.keys(errors || {});

                    if (errorFields.some(field => ['first_name', 'last_name', 'email', 'contact_number', 'landline_number'].includes(field))) {
                        return 0;
                    }

                    if (errorFields.some(field => ['address', 'house_number', 'street_name', 'barangay', 'province_code', 'city_municipality'].includes(field))) {
                        return 1;
                    }

                    if (errorFields.some(field => ['password', 'password_confirmation'].includes(field))) {
                        return 2;
                    }

                    return currentStep;
                };

                const showStep = stepIndex => {
                    currentStep = Math.max(0, Math.min(stepIndex, steps.length - 1));

                    steps.forEach((step, index) => {
                        step.hidden = index !== currentStep;
                    });

                    stepIndicators.forEach((button, index) => {
                        button.classList.toggle('is-active', index === currentStep);
                        button.classList.toggle('is-complete', index < currentStep);
                        button.disabled = index > maxUnlockedStep;
                        button.setAttribute('aria-current', index === currentStep ? 'step' : 'false');
                    });

                    if (stepCount) {
                        stepCount.textContent = `Step ${currentStep + 1} of ${steps.length}`;
                    }

                    if (progressBar) {
                        progressBar.style.width = `${((currentStep + 1) / steps.length) * 100}%`;
                    }

                    if (backButton) {
                        backButton.hidden = currentStep === 0;
                    }

                    if (nextButton) {
                        nextButton.hidden = currentStep === steps.length - 1;
                    }

                    if (submitButton) {
                        submitButton.hidden = currentStep !== steps.length - 1;
                    }
                };

                const fillSelect = (selectElement, placeholder, items) => {
                    if (!selectElement) {
                        return;
                    }

                    selectElement.innerHTML = '';
                    selectElement.append(new Option(placeholder, ''));

                    items.forEach(item => {
                        const option = new Option(item.name, item.code);
                        option.dataset.name = item.name;
                        option.dataset.kind = item.kind || '';
                        selectElement.append(option);
                    });
                };

                const fetchJson = async url => {
                    const response = await fetch(url, {
                        headers: {
                            Accept: 'application/json',
                        },
                    });

                    if (!response.ok) {
                        throw new Error(`Request failed with status ${response.status}`);
                    }

                    return response.json();
                };

                const sortByName = items => items.sort((a, b) => a.name.localeCompare(b.name));

                const loadProvinces = async () => {
                    if (!provinceSelect) {
                        return;
                    }

                    provinceSelect.disabled = true;
                    fillSelect(provinceSelect, 'Loading Philippine locations...', []);

                    try {
                        const provinces = await fetchJson(`${locationApiBaseUrl}/provinces/`);
                        const normalizedProvinces = sortByName(provinces.map(province => ({
                            code: province.code,
                            name: province.name,
                            kind: 'province',
                        })));

                        fillSelect(provinceSelect, 'Select province or NCR', [
                            ncrLocation,
                            ...normalizedProvinces,
                        ]);

                        provinceSelect.disabled = false;
                        locationLoadFailed = false;

                        if (locationStatus) {
                            locationStatus.textContent = 'Philippines only. NCR is included in this list.';
                        }
                    } catch (error) {
                        locationLoadFailed = true;
                        fillSelect(provinceSelect, 'Unable to load locations', []);

                        if (locationStatus) {
                            locationStatus.textContent = 'We could not load the Philippine location list. Please refresh the page.';
                        }
                    }
                };

                const loadCities = async () => {
                    if (!provinceSelect || !citySelect) {
                        return;
                    }

                    const selectedOption = provinceSelect.options[provinceSelect.selectedIndex];
                    const selectedCode = provinceSelect.value;
                    const selectedKind = selectedOption?.dataset.kind || 'province';

                    citySelect.disabled = true;
                    fillSelect(citySelect, selectedCode ? 'Loading cities and municipalities...' : 'Select province first', []);

                    if (!selectedCode) {
                        return;
                    }

                    try {
                        const endpoint = selectedKind === 'region'
                            ? `${locationApiBaseUrl}/regions/${selectedCode}/cities-municipalities/`
                            : `${locationApiBaseUrl}/provinces/${selectedCode}/cities-municipalities/`;
                        const cities = await fetchJson(endpoint);
                        const normalizedCities = sortByName(cities.map(city => ({
                            code: city.code,
                            name: city.name,
                        })));

                        fillSelect(citySelect, 'Select city or municipality', normalizedCities);
                        citySelect.disabled = false;
                    } catch (error) {
                        fillSelect(citySelect, 'Unable to load cities', []);
                        setFieldError('city_municipality', 'City and municipality list is unavailable. Please refresh and try again.');
                    }
                };

                const contactNumberInput = form.querySelector('#contact_number');
                const landlineInput = form.querySelector('#landline_number');

                if (contactNumberInput) {
                    contactNumberInput.addEventListener('input', event => {
                        const input = event.currentTarget;

                        if (!(input instanceof HTMLInputElement)) {
                            return;
                        }

                        input.value = input.value.replace(/\D/g, '').slice(0, 11);
                    });
                }

                if (landlineInput) {
                    landlineInput.addEventListener('input', event => {
                        const input = event.currentTarget;

                        if (!(input instanceof HTMLInputElement)) {
                            return;
                        }

                        input.value = input.value.replace(/[^0-9()+\-\s]/g, '').slice(0, 30);
                    });
                }

                ['first_name', 'last_name'].forEach(field => {
                    const input = getField(field);

                    if (input) {
                        input.addEventListener('input', () => {
                            input.value = sanitizeCustomerName(input.value);
                        });

                        input.addEventListener('blur', () => {
                            input.value = normalizeCustomerName(input.value);
                        });
                    }
                });

                if (passwordInput) {
                    passwordInput.addEventListener('input', updatePasswordChecklist);
                    updatePasswordChecklist();
                }

                form.querySelectorAll('[data-toggle-password]').forEach(button => {
                    button.addEventListener('click', () => {
                        const target = document.getElementById(button.dataset.togglePassword || '');

                        if (!(target instanceof HTMLInputElement)) {
                            return;
                        }

                        const willShow = target.type === 'password';
                        target.type = willShow ? 'text' : 'password';
                        button.textContent = willShow ? 'Hide' : 'Show';
                        button.setAttribute('aria-label', willShow ? 'Hide password' : 'Show password');
                    });
                });

                form.querySelectorAll('#house_number, #street_name, #barangay').forEach(input => {
                    input.addEventListener('input', syncAddressInput);
                });

                if (provinceSelect) {
                    provinceSelect.addEventListener('change', async () => {
                        clearErrors();
                        await loadCities();
                        syncAddressInput();
                    });
                }

                if (citySelect) {
                    citySelect.addEventListener('change', syncAddressInput);
                }

                stepIndicators.forEach(button => {
                    button.addEventListener('click', () => {
                        const nextStep = Number(button.dataset.stepIndicator || 0);

                        if (nextStep <= maxUnlockedStep) {
                            clearErrors();
                            showStep(nextStep);
                        }
                    });
                });

                if (backButton) {
                    backButton.addEventListener('click', () => {
                        clearErrors();
                        showStep(currentStep - 1);
                    });
                }

                if (nextButton) {
                    nextButton.addEventListener('click', () => {
                        if (!validateStep(currentStep)) {
                            return;
                        }

                        maxUnlockedStep = Math.max(maxUnlockedStep, currentStep + 1);
                        showStep(currentStep + 1);
                    });
                }

                if (flashedSuccessMessage) {
                    showSuccessToast(flashedSuccessMessage);
                    redirectToLoginWithDelay();
                }

                form.addEventListener('submit', async event => {
                    event.preventDefault();

                    if (!validateAllSteps()) {
                        return;
                    }

                    const state = buildRegisterState();

                    if (submitButton) {
                        submitButton.disabled = true;
                        submitButton.textContent = 'Creating Account...';
                    }

                    if (backButton) {
                        backButton.disabled = true;
                    }

                    try {
                        const response = await fetch(registerApiUrl, {
                            method: 'POST',
                            headers: {
                                Accept: 'application/json',
                                'Content-Type': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            body: JSON.stringify(state),
                        });

                        const payload = await response.json().catch(() => ({}));

                        if (!response.ok) {
                            if (payload.errors) {
                                const targetStep = stepIndexForErrors(payload.errors);
                                maxUnlockedStep = Math.max(maxUnlockedStep, targetStep);
                                showStep(targetStep);
                                showFieldErrors(payload.errors);
                            }

                            showGeneralError(payload.message || 'We could not create your account right now.');
                            return;
                        }

                        showSuccessToast('Account successfully created! Please verify your email before logging in.');
                        redirectToLoginWithDelay();
                    } catch (error) {
                        showGeneralError('We could not reach the registration service. Please try again.');
                    } finally {
                        if (submitButton) {
                            submitButton.disabled = false;
                            submitButton.textContent = 'Create Account';
                        }

                        if (backButton) {
                            backButton.disabled = false;
                        }
                    }
                });

                showStep(0);
                loadProvinces();
            };

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', attachRegisterSubmitHandler, { once: true });
                return;
            }

            attachRegisterSubmitHandler();
        })();
    </script>
@endpush
