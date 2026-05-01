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
            background: radial-gradient(circle, rgba(59, 130, 246, .14), rgba(59, 130, 246, 0));
        }

        .auth-card {
            position: relative;
            z-index: 1;
            display: grid;
            grid-template-columns: minmax(300px, 390px) minmax(0, 1fr);
            width: min(100%, 1120px);
            margin: 0 auto;
            overflow: hidden;
            border: 1px solid #dbe6f2;
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

        .auth-brand-inner {
            display: flex;
            flex-direction: column;
            width: 100%;
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
            display: flex;
            flex: 1;
            flex-direction: column;
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
            color: #102a43;
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
            border: 1px solid #e2e8f0;
            border-radius: 28px;
            background: linear-gradient(180deg, rgba(255, 255, 255, .98), rgba(248, 251, 255, 1));
            box-shadow: 0 24px 54px rgba(15, 23, 42, .08);
        }

        .auth-form-head {
            margin-bottom: 24px;
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
            max-width: 560px;
            color: #64748b;
            font-size: 15px;
            line-height: 1.7;
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
            width: min(100% - 32px, 360px);
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
            background: linear-gradient(180deg, #f4c542 0%, #d4a017 100%);
        }

        .auth-success-toast-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
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
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px 16px;
        }

        .auth-field {
            display: grid;
            gap: 8px;
            align-content: start;
        }

        .auth-field.auth-field-full,
        .auth-submit-row {
            grid-column: 1 / -1;
        }

        .auth-field label {
            color: #17324d;
            font-size: 13px;
            font-weight: 700;
        }

        .auth-field input {
            width: 100%;
            min-height: 54px;
            border: 1px solid #d7e3ef;
            border-radius: 16px;
            background: #ffffff;
            padding: 0 16px;
            color: #0f172a;
            font-size: 15px;
            transition: border-color .2s ease, box-shadow .2s ease, background-color .2s ease;
        }

        .auth-field input::placeholder {
            color: #94a3b8;
        }

        .auth-field input:focus {
            border-color: #8db4de;
            background: #ffffff;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, .12);
            outline: none;
        }

        .auth-field-support {
            display: grid;
            align-content: start;
            gap: 6px;
            min-height: 42px;
        }

        .auth-field-helper {
            margin: 0;
            color: #52606d;
            font-size: 12px;
            line-height: 1.5;
        }

        .auth-field-helper-spacer {
            visibility: hidden;
        }

        .auth-field .field-error {
            min-height: 18px;
            margin: 0;
            color: #b91c1c;
            font-size: 12px;
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

        .auth-submit-btn:disabled {
            opacity: .7;
            cursor: wait;
            transform: none;
        }

        .auth-inline-link {
            display: inline-flex;
            justify-content: center;
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

            .auth-form-surface {
                width: 100%;
            }
        }

        @media (max-width: 720px) {
            .auth-form-grid {
                grid-template-columns: 1fr;
            }

            .auth-field-support {
                min-height: auto;
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
        <p class="auth-success-toast-copy">You can sign in with your new SolMate account in a moment.</p>
    </div>

    <section class="auth-shell" aria-label="Register page">
        <div class="auth-card">
            <aside class="auth-panel-brand">
                <div class="auth-brand-inner">
                    <a href="{{ route('home') }}" class="auth-brand" aria-label="SolMate home">
                        <span class="auth-brand-mark">SM</span>
                        <span>SolMate</span>
                    </a>

                    <div class="auth-brand-copy">
                        <h1 class="auth-brand-title">Join <span>SolMate!</span></h1>
                        <p class="auth-brand-text">Create your SolMate customer account to request services, review solar updates, and manage your journey in one secure place.</p>

                        <ul class="auth-brand-points">
                            <li>Book quotations, inspections, installations, and maintenance faster.</li>
                            <li>Keep your account ready for service tracking and follow-up updates.</li>
                            <li>Stay connected with a simple, professional customer experience.</li>
                        </ul>

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
                        <span class="auth-form-kicker">Create Account</span>
                        <h2 class="auth-form-title">Register for SolMate</h2>
                    </div>

                    <div class="auth-feedback">
                        <div class="status" id="register-success-box" hidden>Registration successful. Please login.</div>
                        <div class="error-box" id="register-error-box" hidden></div>
                    </div>

                    <form
                        class="auth-form-grid"
                        id="register-form"
                        novalidate
                        data-login-page-url="{{ route('login') }}"
                        data-registration-success-message="{{ session('registration_success', '') }}"
                    >
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
                                <p class="auth-field-helper">Enter an 11-digit mobile number.</p>
                                <div class="field-error" data-error-for="contact_number">@error('contact_number') {{ $message }} @enderror</div>
                            </div>
                        </div>

                        <div class="auth-field">
                            <label for="landline_number">LANDLINE NUMBER (Optional)</label>
                            <input id="landline_number" type="text" name="landline_number" value="{{ old('landline_number') }}" autocomplete="tel-national" placeholder="e.g. (02) 8123-4567">
                            <div class="auth-field-support">
                                <p class="auth-field-helper">Optional. You may enter a home or office landline number.</p>
                                <div class="field-error" data-error-for="landline_number">@error('landline_number') {{ $message }} @enderror</div>
                            </div>
                        </div>

                        <div class="auth-field auth-field-full">
                            <label for="address">Address</label>
                            <input id="address" type="text" name="address" value="{{ old('address') }}" required autocomplete="street-address" placeholder="House number, street, barangay, city">
                            <div class="field-error" data-error-for="address">@error('address') {{ $message }} @enderror</div>
                        </div>

                        <div class="auth-field auth-field-full">
                            <label for="password">Password</label>
                            <input id="password" type="password" name="password" required autocomplete="new-password" placeholder="Create a secure password">
                            <div class="auth-field-support">
                                <p class="auth-field-helper">Password must be at least 8 characters, include 1 uppercase letter, and 1 special character.</p>
                                <div class="field-error" data-error-for="password">@error('password') {{ $message }} @enderror</div>
                            </div>
                        </div>

                        <div class="auth-field auth-field-full">
                            <label for="password_confirmation">Confirm Password</label>
                            <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="Re-enter your password">
                            <div class="auth-field-support">
                                <div class="field-error" data-error-for="password_confirmation">@error('password_confirmation') {{ $message }} @enderror</div>
                            </div>
                        </div>

                        <div class="auth-submit-row">
                            <button type="submit" class="auth-submit-btn" id="register-submit-button">Create Account</button>
                            <a class="auth-inline-link" href="{{ route('login') }}">Already have an account? Login</a>
                        </div>
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

                const successBox = document.getElementById('register-success-box');
                const errorBox = document.getElementById('register-error-box');
                const submitButton = document.getElementById('register-submit-button');
                const registerApiUrl = '/api/register';
                const loginPageUrl = form.dataset.loginPageUrl || '/login';
                const flashedSuccessMessage = form.dataset.registrationSuccessMessage || '';

                const showSuccessToast = (message) => {
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
                    }, 2000);
                };

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

                const showGeneralError = (message) => {
                    if (!errorBox) {
                        return;
                    }

                    errorBox.textContent = message;
                    errorBox.hidden = false;
                };

                const showFieldErrors = (errors) => {
                    Object.entries(errors).forEach(([field, messages]) => {
                        const fieldError = form.querySelector(`[data-error-for="${field}"]`);

                        if (!fieldError) {
                            return;
                        }

                        fieldError.textContent = Array.isArray(messages) ? (messages[0] || '') : String(messages || '');
                    });
                };

                const buildRegisterState = () => {
                    const formData = new FormData(form);

                    return {
                        first_name: String(formData.get('first_name') || '').trim(),
                        last_name: String(formData.get('last_name') || '').trim(),
                        email: String(formData.get('email') || '').trim(),
                        contact_number: String(formData.get('contact_number') || '').replace(/\D/g, '').slice(0, 11),
                        address: String(formData.get('address') || '').trim(),
                        landline_number: String(formData.get('landline_number') || '').trim(),
                        password: String(formData.get('password') || ''),
                        password_confirmation: String(formData.get('password_confirmation') || ''),
                    };
                };

                const contactNumberInput = form.querySelector('#contact_number');

                if (contactNumberInput) {
                    contactNumberInput.addEventListener('input', event => {
                        const input = event.currentTarget;

                        if (!(input instanceof HTMLInputElement)) {
                            return;
                        }

                        input.value = input.value.replace(/\D/g, '').slice(0, 11);
                    });
                }

                if (flashedSuccessMessage) {
                    showSuccessToast(flashedSuccessMessage);
                    redirectToLoginWithDelay();
                }

                form.addEventListener('submit', async event => {
                    event.preventDefault();
                    clearErrors();

                    const state = buildRegisterState();

                    if (submitButton) {
                        submitButton.disabled = true;
                        submitButton.textContent = 'Creating Account...';
                    }

                    try {
                        const response = await fetch(registerApiUrl, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            body: JSON.stringify(state),
                        });

                        const payload = await response.json().catch(() => ({}));

                        if (!response.ok) {
                            if (payload.errors) {
                                showFieldErrors(payload.errors);
                            }

                            showGeneralError(payload.message || 'We could not create your account right now.');
                            return;
                        }

                        showSuccessToast('Account successfully created! Redirecting to login page...');
                        redirectToLoginWithDelay();
                    } catch (error) {
                        showGeneralError('We could not reach the registration service. Please try again.');
                    } finally {
                        if (submitButton) {
                            submitButton.disabled = false;
                            submitButton.textContent = 'Create Account';
                        }
                    }
                });
            };

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', attachRegisterSubmitHandler, { once: true });
                return;
            }

            attachRegisterSubmitHandler();
        })();
    </script>
@endpush
