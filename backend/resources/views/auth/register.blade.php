@extends('layouts.app', ['title' => 'Register'])

@section('content')
    <div class="card narrow">
        <h1 class="page-title">Register</h1>
        <div class="info-box">REGISTER DEBUG VERSION 2026-04-24</div>
        <p class="page-copy">Create a basic website account. New registrations are saved as <strong>customer</strong> users by default.</p>

        <div class="status" id="register-success-box" hidden>Registration successful. Please login.</div>
        <div class="error-box" id="register-error-box" hidden></div>

        <form
            class="form-grid"
            id="register-form"
            onsubmit="return false;"
            data-login-page-url="{{ route('login') }}"
        >
            <div>
                <label for="name">Name</label>
                <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus>
                <div class="field-error" data-error-for="name">@error('name') {{ $message }} @enderror</div>
            </div>

            <div>
                <label for="email">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required>
                <div class="field-error" data-error-for="email">@error('email') {{ $message }} @enderror</div>
            </div>

            <div>
                <label for="address">Address</label>
                <input id="address" type="text" name="address" value="{{ old('address') }}" required autocomplete="street-address">
                <div class="field-error" data-error-for="address">@error('address') {{ $message }} @enderror</div>
            </div>

            <div>
                <label for="contact_number">Contact Number</label>
                <input id="contact_number" type="text" name="contact_number" value="{{ old('contact_number') }}" required autocomplete="tel">
                <div class="field-error" data-error-for="contact_number">@error('contact_number') {{ $message }} @enderror</div>
            </div>

            <div>
                <label for="password">Password</label>
                <input id="password" type="password" name="password" required>
                <div class="field-error" data-error-for="password">@error('password') {{ $message }} @enderror</div>
            </div>

            <div>
                <label for="password_confirmation">Confirm password</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required>
                <div class="field-error" data-error-for="password_confirmation">@error('password_confirmation') {{ $message }} @enderror</div>
            </div>

            <div class="actions">
                <button type="submit" id="register-submit-button">Register</button>
                <a class="button-link secondary" href="{{ route('login') }}">Back to login</a>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        (() => {
            console.log('REGISTER PAGE JS LOADED - DEBUG 2026-04-24');

            const attachRegisterSubmitHandler = () => {
                const form = document.getElementById('register-form');

                if (!form || form.dataset.submitHandlerAttached === 'true') {
                    return;
                }

                form.dataset.submitHandlerAttached = 'true';

                const successBox = document.getElementById('register-success-box');
                const errorBox = document.getElementById('register-error-box');
                const submitButton = document.getElementById('register-submit-button');
                const registerApiUrl = '/api/register';
                const loginPageUrl = form.dataset.loginPageUrl || '/login';

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
                        name: String(formData.get('name') || ''),
                        email: String(formData.get('email') || ''),
                        address: String(formData.get('address') || ''),
                        contact_number: String(formData.get('contact_number') || ''),
                        password: String(formData.get('password') || ''),
                        password_confirmation: String(formData.get('password_confirmation') || ''),
                    };
                };

                form.addEventListener('submit', async event => {
                    event.preventDefault();
                    event.stopPropagation();
                    console.log('REGISTER SUBMIT HANDLER RUNNING');
                    console.log('Posting to /api/register');
                    console.log('FINAL REGISTER URL:', registerApiUrl);

                    clearErrors();

                    const state = buildRegisterState();

                    if (submitButton) {
                        submitButton.disabled = true;
                        submitButton.textContent = 'Registering...';
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

                        if (successBox) {
                            successBox.hidden = false;
                            successBox.textContent = 'Registration successful. Please login.';
                        }

                        window.setTimeout(() => {
                            window.location.href = loginPageUrl;
                        }, 1000);
                    } catch (error) {
                        showGeneralError('We could not reach the registration service. Please try again.');
                    } finally {
                        if (submitButton) {
                            submitButton.disabled = false;
                            submitButton.textContent = 'Register';
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
