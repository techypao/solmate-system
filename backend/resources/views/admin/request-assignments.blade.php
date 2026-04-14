@extends('layouts.app', ['title' => 'Admin Request Assignments'])

@section('content')
    <div class="card">
        <h1 class="page-title">Admin Request Assignments</h1>
        <p class="page-copy">Assign technicians to service requests and inspection requests from the website. This page reuses the existing backend assignment endpoints.</p>

        @if ($technicians->isEmpty())
            <div class="error-box">
                No technician users were found. Create at least one technician account before assigning requests.
            </div>
        @endif

        <div id="assignment-success" class="status" style="display: none;"></div>
        <div id="assignment-error" class="error-box" style="display: none;"></div>
    </div>

    <div class="card">
        <h2 style="margin-top: 0;">Service Requests</h2>

        @if ($serviceRequests->isEmpty())
            <div class="info-box">No service requests found.</div>
        @else
            <div class="stack">
                @foreach ($serviceRequests as $serviceRequest)
                    <div class="card" style="padding: 18px;">
                        <div class="stack">
                            <div><strong>Request #{{ $serviceRequest->id }}</strong></div>
                            <div class="muted">Customer: {{ $serviceRequest->customer?->name ?? 'Unknown customer' }}</div>
                            <div class="muted">Type: {{ $serviceRequest->request_type }}</div>
                            <div class="muted">Status: <span data-status-for="service-{{ $serviceRequest->id }}">{{ $serviceRequest->status }}</span></div>
                            <div class="muted">Current technician: <span data-technician-for="service-{{ $serviceRequest->id }}">{{ $serviceRequest->technician?->name ?? 'Not assigned' }}</span></div>
                            <div>{{ $serviceRequest->details }}</div>

                            <form
                                class="assignment-form"
                                data-endpoint="/api/service-requests/{{ $serviceRequest->id }}/assign-technician"
                                data-request-key="service-{{ $serviceRequest->id }}"
                            >
                                <label for="service_technician_{{ $serviceRequest->id }}">Assign technician</label>
                                <div class="actions">
                                    <select
                                        id="service_technician_{{ $serviceRequest->id }}"
                                        name="technician_id"
                                        style="min-width: 220px; padding: 10px 12px; border: 1px solid #bcccdc; border-radius: 8px;"
                                        required
                                        @disabled($technicians->isEmpty())
                                    >
                                        <option value="">Select technician</option>
                                        @foreach ($technicians as $technician)
                                            <option value="{{ $technician->id }}" @selected($serviceRequest->technician_id === $technician->id)>
                                                {{ $technician->name }} ({{ $technician->email }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <button type="submit" @disabled($technicians->isEmpty())>Assign technician</button>
                                </div>
                                <div class="field-error" data-form-error></div>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <div class="card">
        <h2 style="margin-top: 0;">Inspection Requests</h2>

        @if ($inspectionRequests->isEmpty())
            <div class="info-box">No inspection requests found.</div>
        @else
            <div class="stack">
                @foreach ($inspectionRequests as $inspectionRequest)
                    <div class="card" style="padding: 18px;">
                        <div class="stack">
                            <div><strong>Request #{{ $inspectionRequest->id }}</strong></div>
                            <div class="muted">Customer: {{ $inspectionRequest->customer?->name ?? 'Unknown customer' }}</div>
                            <div class="muted">Status: <span data-status-for="inspection-{{ $inspectionRequest->id }}">{{ $inspectionRequest->status }}</span></div>
                            <div class="muted">Current technician: <span data-technician-for="inspection-{{ $inspectionRequest->id }}">{{ $inspectionRequest->technician?->name ?? 'Not assigned' }}</span></div>
                            <div>{{ $inspectionRequest->details }}</div>

                            <form
                                class="assignment-form"
                                data-endpoint="/api/inspection-requests/{{ $inspectionRequest->id }}/assign-technician"
                                data-request-key="inspection-{{ $inspectionRequest->id }}"
                            >
                                <label for="inspection_technician_{{ $inspectionRequest->id }}">Assign technician</label>
                                <div class="actions">
                                    <select
                                        id="inspection_technician_{{ $inspectionRequest->id }}"
                                        name="technician_id"
                                        style="min-width: 220px; padding: 10px 12px; border: 1px solid #bcccdc; border-radius: 8px;"
                                        required
                                        @disabled($technicians->isEmpty())
                                    >
                                        <option value="">Select technician</option>
                                        @foreach ($technicians as $technician)
                                            <option value="{{ $technician->id }}" @selected($inspectionRequest->technician_id === $technician->id)>
                                                {{ $technician->name }} ({{ $technician->email }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <button type="submit" @disabled($technicians->isEmpty())>Assign technician</button>
                                </div>
                                <div class="field-error" data-form-error></div>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endsection

@push('scripts')
    <script>
        const successBox = document.getElementById('assignment-success');
        const errorBox = document.getElementById('assignment-error');
        const assignmentForms = document.querySelectorAll('.assignment-form');

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
            element.style.display = visible ? 'block' : 'none';
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

        function clearGlobalMessages() {
            successBox.textContent = '';
            errorBox.textContent = '';
            setVisible(successBox, false);
            setVisible(errorBox, false);
        }

        assignmentForms.forEach((form) => {
            form.addEventListener('submit', async (event) => {
                event.preventDefault();
                clearGlobalMessages();

                const select = form.elements.namedItem('technician_id');
                const button = form.querySelector('button[type="submit"]');
                const inlineError = form.querySelector('[data-form-error]');
                const requestKey = form.dataset.requestKey;
                const technicianLabel = document.querySelector(`[data-technician-for="${requestKey}"]`);
                const statusLabel = document.querySelector(`[data-status-for="${requestKey}"]`);
                const selectedOption = select.options[select.selectedIndex];

                inlineError.textContent = '';
                button.disabled = true;
                button.textContent = 'Assigning...';

                try {
                    await ensureCsrfCookie();

                    const response = await fetch(form.dataset.endpoint, {
                        method: 'PUT',
                        credentials: 'same-origin',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-XSRF-TOKEN': getCookie('XSRF-TOKEN') || '',
                        },
                        body: JSON.stringify({
                            technician_id: Number(select.value),
                        }),
                    });

                    const responseBody = await response.json();

                    if (response.status === 422) {
                        const errors = responseBody.errors || {};
                        const firstError = Object.values(errors)[0];
                        inlineError.textContent = Array.isArray(firstError) ? firstError[0] : (responseBody.message || 'Please review the form.');
                        throw new Error('Please review the form.');
                    }

                    if (!response.ok) {
                        throw new Error(responseBody.message || 'Could not assign technician.');
                    }

                    if (technicianLabel) {
                        technicianLabel.textContent = selectedOption.textContent;
                    }

                    if (statusLabel) {
                        statusLabel.textContent = 'assigned';
                    }

                    successBox.textContent = responseBody.message || 'Technician assigned successfully.';
                    setVisible(successBox, true);
                } catch (error) {
                    if (!inlineError.textContent) {
                        inlineError.textContent = error.message || 'Could not assign technician.';
                    }
                    errorBox.textContent = error.message || 'Could not assign technician.';
                    setVisible(errorBox, true);
                } finally {
                    button.disabled = false;
                    button.textContent = 'Assign technician';
                }
            });
        });
    </script>
@endpush
