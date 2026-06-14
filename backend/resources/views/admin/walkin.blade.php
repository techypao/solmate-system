@extends('layouts.app', ['title' => 'Walkin'])

@section('content')
    @include('customer.partials.preferred-date-picker-styles')

    <style>
        .walkin-page {
            display: grid;
            gap: 24px;
        }

        .walkin-form {
            display: grid;
            gap: 16px;
        }

        .walkin-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
        }

        .walkin-field {
            display: grid;
            gap: 8px;
        }

        .walkin-field--wide {
            grid-column: 1 / -1;
        }

        .walkin-actions {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }

        .walkin-list {
            display: grid;
            gap: 14px;
        }

        .walkin-card {
            display: grid;
            gap: 14px;
            padding: 18px;
            border: 1px solid var(--solmate-border);
            border-radius: 18px;
            background: #ffffff;
            box-shadow: var(--solmate-shadow-soft);
        }

        .walkin-card-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }

        .walkin-card-title {
            margin: 0;
            color: var(--solmate-blue-900);
            font-size: 17px;
            font-weight: 800;
        }

        .walkin-card-sub {
            margin: 4px 0 0;
            color: var(--solmate-copy);
            font-size: 13px;
        }

        .walkin-detail-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
            gap: 12px;
        }

        .walkin-detail {
            padding: 12px;
            border: 1px solid var(--solmate-border);
            border-radius: 14px;
            background: var(--solmate-surface-muted);
        }

        .walkin-detail span {
            display: block;
            margin-bottom: 4px;
            color: var(--solmate-copy);
            font-size: 11px;
            font-weight: 800;
            letter-spacing: .06em;
            text-transform: uppercase;
        }

        .walkin-detail strong {
            color: var(--solmate-blue-900);
            font-size: 14px;
        }

        @media (max-width: 760px) {
            .walkin-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="walkin-page">
        <div class="card admin-hero-card">
            <div class="section-header">
                <div>
                    <p class="admin-page-eyebrow">Operations</p>
                    <h1 class="page-title">Walkin</h1>
                    <p class="page-copy">Create manual inspection requests for non-registered customers from phone calls, email inquiries, or walk-in conversations.</p>
                </div>
                <span class="badge badge-info">Manual Inspection Request</span>
            </div>
            <div id="walkin-success" class="status" style="display: none; margin-bottom: 0;"></div>
            <div id="walkin-error" class="error-box" style="display: none; margin-bottom: 0;"></div>
        </div>

        <div class="card">
            <div class="section-header">
                <div>
                    <h2 class="admin-section-title">Create Walkin Request</h2>
                    <p class="admin-section-copy">After creation, the request can be assigned and tracked through the normal inspection workflow.</p>
                </div>
            </div>

            <form id="walkin-form" class="walkin-form" data-endpoint="/api/admin/manual-inspection-requests">
                <div class="walkin-grid">
                    <div class="walkin-field">
                        <label for="walkin_customer_name">Customer Name</label>
                        <input id="walkin_customer_name" name="customer_name" type="text" maxlength="255" required>
                        <div class="field-error" data-field-error="customer_name"></div>
                    </div>

                    <div class="walkin-field">
                        <label for="walkin_customer_email">Customer Email</label>
                        <input id="walkin_customer_email" name="customer_email" type="email" maxlength="255" required>
                        <div class="field-error" data-field-error="customer_email"></div>
                    </div>

                    <div class="walkin-field">
                        <label for="walkin_contact_number">Contact Number</label>
                        <input id="walkin_contact_number" name="contact_number" type="tel" maxlength="30" required>
                        <div class="field-error" data-field-error="contact_number"></div>
                    </div>

                    <div class="walkin-field">
                        <label for="walkin_date_needed">Preferred Date</label>
                        <input id="walkin_date_needed" name="date_needed" type="hidden" required>
                        <div id="walkin_date_picker" data-walkin-date-picker></div>
                        <div class="field-error" data-field-error="date_needed"></div>
                    </div>

                    <div class="walkin-field walkin-field--wide">
                        <label for="walkin_address_details">Address Details</label>
                        <textarea id="walkin_address_details" name="address_details" maxlength="255" required></textarea>
                        <div class="field-error" data-field-error="address_details"></div>
                    </div>

                    <div class="walkin-field walkin-field--wide">
                        <label for="walkin_details">Notes and Details</label>
                        <textarea id="walkin_details" name="details" required></textarea>
                        <div class="field-error" data-field-error="details"></div>
                    </div>
                </div>

                <div class="walkin-actions">
                    <p class="page-copy" style="margin: 0;">The customer will not see this in a customer account.</p>
                    <button type="submit">Create Walkin Request</button>
                </div>
                <div class="field-error" data-form-error></div>
            </form>
        </div>

        <div class="card">
            <div class="section-header">
                <div>
                    <h2 class="admin-section-title">Recent Walkin Requests</h2>
                    <p class="admin-section-copy">Open Services to assign a technician, update official status, and continue the inspection-based quotation workflow.</p>
                </div>
                <span class="badge badge-neutral">{{ $manualInspectionRequests->count() }} total</span>
            </div>

            @if ($manualInspectionRequests->isEmpty())
                <div class="info-box" style="margin-bottom: 0;">No walkin requests yet.</div>
            @else
                <div class="walkin-list">
                    @foreach ($manualInspectionRequests as $request)
                        <div class="walkin-card">
                            <div class="walkin-card-head">
                                <div>
                                    <p class="walkin-card-title">Walkin Request #{{ $request->id }}</p>
                                    <p class="walkin-card-sub">{{ $request->displayCustomerName() }} · {{ \Illuminate\Support\Str::headline($request->status) }}</p>
                                </div>
                                <a class="button-link secondary" href="{{ route('admin.request-assignments') }}#inspection-request-{{ $request->id }}">Open in Services</a>
                            </div>
                            <div class="walkin-detail-grid">
                                <div class="walkin-detail">
                                    <span>Email</span>
                                    <strong>{{ $request->displayCustomerEmail() }}</strong>
                                </div>
                                <div class="walkin-detail">
                                    <span>Contact Number</span>
                                    <strong>{{ $request->contact_number ?: 'Not provided' }}</strong>
                                </div>
                                <div class="walkin-detail">
                                    <span>Preferred Date</span>
                                    <strong>{{ $request->date_needed ? \Illuminate\Support\Carbon::parse($request->date_needed)->format('M d, Y') : 'Not specified' }}</strong>
                                </div>
                                <div class="walkin-detail">
                                    <span>Technician</span>
                                    <strong>{{ $request->technician?->name ?? 'Not assigned' }}</strong>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
    @include('customer.partials.preferred-date-picker-script')
    <script>
        (function () {
            const form = document.getElementById('walkin-form');
            const successBox = document.getElementById('walkin-success');
            const errorBox = document.getElementById('walkin-error');
            const reservedDateMessage = 'Selected date is already reserved. Please choose another date.';

            if (!form) {
                return;
            }

            function getCookie(name) {
                const prefix = `${name}=`;
                const parts = document.cookie.split(';');

                for (const part of parts) {
                    const trimmed = part.trim();

                    if (trimmed.startsWith(prefix)) {
                        return decodeURIComponent(trimmed.substring(prefix.length));
                    }
                }

                return null;
            }

            function setVisible(element, visible) {
                if (element) {
                    element.style.display = visible ? 'block' : 'none';
                }
            }

            function clearMessages() {
                if (successBox) {
                    successBox.textContent = '';
                    setVisible(successBox, false);
                }

                if (errorBox) {
                    errorBox.textContent = '';
                    setVisible(errorBox, false);
                }

                form.querySelectorAll('[data-field-error]').forEach((element) => {
                    element.textContent = '';
                });

                const formError = form.querySelector('[data-form-error]');
                if (formError) {
                    formError.textContent = '';
                }
            }

            async function ensureCsrfCookie() {
                await fetch('/sanctum/csrf-cookie', {
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
            }

            async function submitJson(endpoint, payload) {
                await ensureCsrfCookie();

                const response = await fetch(endpoint, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-XSRF-TOKEN': getCookie('XSRF-TOKEN') || '',
                    },
                    body: JSON.stringify(payload),
                });

                const responseBody = await response.json().catch(() => ({}));

                if (response.status === 422) {
                    const errors = responseBody.errors || {};
                    const firstError = Object.values(errors)[0];
                    const message = Array.isArray(firstError)
                        ? firstError[0]
                        : (responseBody.message || 'Please review the form.');
                    const validationError = new Error(message);
                    validationError.errors = errors;
                    throw validationError;
                }

                if (!response.ok) {
                    throw new Error(responseBody.message || 'Request could not be completed.');
                }

                return responseBody;
            }

            const picker = typeof window.createPreferredDatePicker === 'function'
                ? window.createPreferredDatePicker({
                    inputId: 'walkin_date_needed',
                    mountId: 'walkin_date_picker',
                    endpoint: '/api/preferred-date-availability?type=inspection',
                    helperText: 'Booked inspection dates are unavailable and cannot be selected.',
                    fetchErrorText: 'Live reserved-date updates could not be loaded right now. Backend validation still applies when you create the request.',
                    placeholder: 'Select a preferred date',
                })
                : null;

            form.addEventListener('submit', async (event) => {
                event.preventDefault();
                clearMessages();

                const button = form.querySelector('button[type="submit"]');
                const formError = form.querySelector('[data-form-error]');

                await picker?.refreshAvailability?.();

                if (picker?.isSelectedDateUnavailable?.()) {
                    const dateError = form.querySelector('[data-field-error="date_needed"]');
                    if (dateError) {
                        dateError.textContent = reservedDateMessage;
                    }
                    if (formError) {
                        formError.textContent = reservedDateMessage;
                    }
                    return;
                }

                const payload = {
                    customer_name: form.elements.namedItem('customer_name')?.value?.trim() || '',
                    customer_email: form.elements.namedItem('customer_email')?.value?.trim() || '',
                    contact_number: form.elements.namedItem('contact_number')?.value?.trim() || '',
                    address_details: form.elements.namedItem('address_details')?.value?.trim() || '',
                    date_needed: picker?.getValue?.() || form.elements.namedItem('date_needed')?.value || '',
                    details: form.elements.namedItem('details')?.value?.trim() || '',
                };

                button.disabled = true;
                button.textContent = 'Creating...';

                try {
                    await submitJson(form.dataset.endpoint, payload);
                    window.location.reload();
                } catch (error) {
                    const errors = error.errors || {};

                    Object.keys(errors).forEach((field) => {
                        const fieldError = form.querySelector(`[data-field-error="${field}"]`);
                        if (fieldError) {
                            fieldError.textContent = Array.isArray(errors[field]) ? errors[field][0] : errors[field];
                        }
                    });

                    if (formError) {
                        formError.textContent = error.message || 'Could not create the walkin request.';
                    }

                    if (errorBox) {
                        errorBox.textContent = error.message || 'Could not create the walkin request.';
                        setVisible(errorBox, true);
                    }
                } finally {
                    button.disabled = false;
                    button.textContent = 'Create Walkin Request';
                }
            });
        })();
    </script>
@endpush
