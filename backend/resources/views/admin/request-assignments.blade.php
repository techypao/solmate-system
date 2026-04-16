@extends('layouts.app', ['title' => 'Admin Request Assignments'])

@php
    $statusClasses = [
        'pending' => 'badge badge-warning',
        'assigned' => 'badge badge-info',
        'in_progress' => 'badge badge-primary',
        'completed' => 'badge badge-success',
    ];

    $serviceStatusOptions = [
        'pending' => 'Pending',
        'assigned' => 'Assigned',
        'in_progress' => 'In Progress',
        'completed' => 'Completed',
    ];
@endphp

@section('content')
    <div class="card">
        <div class="section-header">
            <div>
                <h1 class="page-title">Admin Request Assignments</h1>
                <p class="page-copy">Review request details, assign technicians, and keep the official service request status under admin control.</p>
            </div>
            <a class="button-link secondary" href="{{ route('admin.technicians.create') }}">Register Technician</a>
        </div>

        <div class="summary-grid">
            <div class="summary-card">
                <div class="summary-label">Available technicians</div>
                <div class="summary-value">{{ $technicians->count() }}</div>
            </div>
            <div class="summary-card">
                <div class="summary-label">Service requests awaiting review</div>
                <div class="summary-value">{{ $serviceRequests->filter(fn ($request) => filled($request->technician_marked_done_at) && $request->status !== 'completed')->count() }}</div>
            </div>
            <div class="summary-card">
                <div class="summary-label">Unassigned service requests</div>
                <div class="summary-value">{{ $serviceRequests->whereNull('technician_id')->count() }}</div>
            </div>
            <div class="summary-card">
                <div class="summary-label">Unassigned inspection requests</div>
                <div class="summary-value">{{ $inspectionRequests->whereNull('technician_id')->count() }}</div>
            </div>
        </div>

        @if ($technicians->isEmpty())
            <div class="error-box" style="margin-top: 16px; margin-bottom: 0;">
                No technician users were found. Create at least one technician account before assigning requests.
            </div>
        @endif

        <div id="assignment-success" class="status" style="display: none; margin-top: 16px; margin-bottom: 0;"></div>
        <div id="assignment-error" class="error-box" style="display: none; margin-top: 16px; margin-bottom: 0;"></div>
    </div>

    <div class="card">
        <div class="section-header">
            <div>
                <h2 style="margin: 0;">Service Requests</h2>
                <p class="page-copy" style="margin-bottom: 0;">Technicians can mark a service as done for review, but the official service status stays under admin control here.</p>
            </div>
            <span class="badge badge-neutral">{{ $serviceRequests->count() }} total</span>
        </div>

        @if ($serviceRequests->isEmpty())
            <div class="info-box" style="margin-bottom: 0;">No service requests found.</div>
        @else
            <div class="request-list">
                @foreach ($serviceRequests as $serviceRequest)
                    @php
                        $requestKey = "service-{$serviceRequest->id}";
                        $statusClass = $statusClasses[$serviceRequest->status] ?? 'badge badge-neutral';
                        $isAssigned = filled($serviceRequest->technician_id);
                        $hasCompletionRequest = filled($serviceRequest->technician_marked_done_at);
                        $buttonLabel = $isAssigned ? 'Update assignment' : 'Assign technician';
                        $dateNeeded = $serviceRequest->date_needed
                            ? \Illuminate\Support\Carbon::parse($serviceRequest->date_needed)->format('M d, Y')
                            : 'Not specified';
                        $technicianSummary = $serviceRequest->technician
                            ? "{$serviceRequest->technician->name} ({$serviceRequest->technician->email})"
                            : 'Not assigned';
                        $completionHeading = 'Completion review';
                        $completionStateClass = 'badge badge-neutral';
                        $completionStateLabel = 'No completion request';

                        if ($hasCompletionRequest && $serviceRequest->status !== 'completed') {
                            $completionStateClass = 'badge badge-warning';
                            $completionStateLabel = 'Awaiting admin review';
                        } elseif ($hasCompletionRequest && $serviceRequest->status === 'completed') {
                            $completionStateClass = 'badge badge-success';
                            $completionStateLabel = 'Admin confirmed completion';
                        }

                        if ($hasCompletionRequest && $serviceRequest->status !== 'completed') {
                            $completionMessage = 'Technician marked this service as done on '
                                . \Illuminate\Support\Carbon::parse($serviceRequest->technician_marked_done_at)->format('M d, Y g:i A')
                                . '. Review the work and set the official status below.';
                        } elseif ($hasCompletionRequest && $serviceRequest->status === 'completed') {
                            $completionMessage = 'Technician marked this service as done on '
                                . \Illuminate\Support\Carbon::parse($serviceRequest->technician_marked_done_at)->format('M d, Y g:i A')
                                . ', and the official service status is now completed.';
                        } else {
                            $completionMessage = 'No technician completion request has been submitted yet.';
                        }
                    @endphp

                    <div class="request-card">
                        <div class="request-header">
                            <div>
                                <div class="request-title">Service Request #{{ $serviceRequest->id }}</div>
                                <div class="muted">Customer: {{ $serviceRequest->customer?->name ?? 'Unknown customer' }}</div>
                            </div>

                            <div class="request-badges">
                                <span class="{{ $statusClass }}" data-status-for="{{ $requestKey }}">
                                    {{ \Illuminate\Support\Str::headline($serviceRequest->status) }}
                                </span>
                                <span
                                    class="{{ $isAssigned ? 'badge badge-neutral' : 'badge badge-warning' }}"
                                    data-assignment-state-for="{{ $requestKey }}"
                                >
                                    {{ $isAssigned ? 'Assigned' : 'Needs technician' }}
                                </span>
                                <span
                                    class="{{ $completionStateClass }}"
                                    data-completion-state-for="{{ $requestKey }}"
                                >
                                    {{ $completionStateLabel }}
                                </span>
                            </div>
                        </div>

                        <div class="detail-grid" style="margin-bottom: 14px;">
                            <div class="detail-item">
                                <span class="detail-label">Customer Email</span>
                                <strong>{{ $serviceRequest->customer?->email ?? 'Not available' }}</strong>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Request Type</span>
                                <strong>{{ $serviceRequest->request_type ?: 'Not specified' }}</strong>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Date Needed</span>
                                <strong>{{ $dateNeeded }}</strong>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Assigned Technician</span>
                                <strong data-technician-for="{{ $requestKey }}">{{ $technicianSummary }}</strong>
                            </div>
                        </div>

                        <div class="info-box" style="margin-bottom: 14px;">
                            <strong>Request details:</strong> {{ $serviceRequest->details }}
                        </div>

                        <div class="info-box" style="margin-bottom: 14px;">
                            <strong data-completion-heading-for="{{ $requestKey }}">{{ $completionHeading }}:</strong>
                            <span data-completion-message-for="{{ $requestKey }}">{{ $completionMessage }}</span>
                        </div>

                        <div class="stack">
                            <form
                                class="assignment-form"
                                data-endpoint="/api/service-requests/{{ $serviceRequest->id }}/assign-technician"
                                data-request-key="{{ $requestKey }}"
                                data-default-label="{{ $buttonLabel }}"
                            >
                                <label for="service_technician_{{ $serviceRequest->id }}">Technician assignment</label>
                                <div class="assignment-row">
                                    <div>
                                        <select
                                            id="service_technician_{{ $serviceRequest->id }}"
                                            name="technician_id"
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
                                        <div class="muted" style="margin-top: 8px;">Assign or reassign the technician responsible for this service request.</div>
                                    </div>
                                    <button type="submit" @disabled($technicians->isEmpty())>{{ $buttonLabel }}</button>
                                </div>
                                <div class="field-error" data-form-error></div>
                            </form>

                            <form
                                class="service-status-form"
                                data-endpoint="/api/admin/service-requests/{{ $serviceRequest->id }}/status"
                                data-request-key="{{ $requestKey }}"
                            >
                                <label for="service_status_{{ $serviceRequest->id }}">Official service status</label>
                                <div class="assignment-row">
                                    <div>
                                        <select
                                            id="service_status_{{ $serviceRequest->id }}"
                                            name="status"
                                            required
                                        >
                                            @foreach ($serviceStatusOptions as $value => $label)
                                                <option value="{{ $value }}" @selected($serviceRequest->status === $value)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                        <div class="muted" style="margin-top: 8px;">Admin is the source of truth for the official service request status.</div>
                                    </div>
                                    <button type="submit">Save official status</button>
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
        <div class="section-header">
            <div>
                <h2 style="margin: 0;">Inspection Requests</h2>
                <p class="page-copy" style="margin-bottom: 0;">Inspection requests stay grouped here with customer details, current status, and technician assignment in one place.</p>
            </div>
            <span class="badge badge-neutral">{{ $inspectionRequests->count() }} total</span>
        </div>

        @if ($inspectionRequests->isEmpty())
            <div class="info-box" style="margin-bottom: 0;">No inspection requests found.</div>
        @else
            <div class="request-list">
                @foreach ($inspectionRequests as $inspectionRequest)
                    @php
                        $requestKey = "inspection-{$inspectionRequest->id}";
                        $statusClass = $statusClasses[$inspectionRequest->status] ?? 'badge badge-neutral';
                        $isAssigned = filled($inspectionRequest->technician_id);
                        $buttonLabel = $isAssigned ? 'Update assignment' : 'Assign technician';
                        $dateNeeded = $inspectionRequest->date_needed
                            ? \Illuminate\Support\Carbon::parse($inspectionRequest->date_needed)->format('M d, Y')
                            : 'Not specified';
                        $technicianSummary = $inspectionRequest->technician
                            ? "{$inspectionRequest->technician->name} ({$inspectionRequest->technician->email})"
                            : 'Not assigned';
                    @endphp

                    <div class="request-card">
                        <div class="request-header">
                            <div>
                                <div class="request-title">Inspection Request #{{ $inspectionRequest->id }}</div>
                                <div class="muted">Customer: {{ $inspectionRequest->customer?->name ?? 'Unknown customer' }}</div>
                            </div>

                            <div class="request-badges">
                                <span class="{{ $statusClass }}" data-status-for="{{ $requestKey }}">
                                    {{ \Illuminate\Support\Str::headline($inspectionRequest->status) }}
                                </span>
                                <span
                                    class="{{ $isAssigned ? 'badge badge-neutral' : 'badge badge-warning' }}"
                                    data-assignment-state-for="{{ $requestKey }}"
                                >
                                    {{ $isAssigned ? 'Assigned' : 'Needs technician' }}
                                </span>
                            </div>
                        </div>

                        <div class="detail-grid" style="margin-bottom: 14px;">
                            <div class="detail-item">
                                <span class="detail-label">Customer Email</span>
                                <strong>{{ $inspectionRequest->customer?->email ?? 'Not available' }}</strong>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Date Needed</span>
                                <strong>{{ $dateNeeded }}</strong>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Current Status</span>
                                <strong>{{ \Illuminate\Support\Str::headline($inspectionRequest->status) }}</strong>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Assigned Technician</span>
                                <strong data-technician-for="{{ $requestKey }}">{{ $technicianSummary }}</strong>
                            </div>
                        </div>

                        <div class="info-box" style="margin-bottom: 14px;">
                            <strong>Request details:</strong> {{ $inspectionRequest->details }}
                        </div>

                        <form
                            class="assignment-form"
                            data-endpoint="/api/inspection-requests/{{ $inspectionRequest->id }}/assign-technician"
                            data-request-key="{{ $requestKey }}"
                            data-default-label="{{ $buttonLabel }}"
                        >
                            <label for="inspection_technician_{{ $inspectionRequest->id }}">Technician assignment</label>
                            <div class="assignment-row">
                                <div>
                                    <select
                                        id="inspection_technician_{{ $inspectionRequest->id }}"
                                        name="technician_id"
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
                                    <div class="muted" style="margin-top: 8px;">Select a technician, then save the assignment.</div>
                                </div>
                                <button type="submit" @disabled($technicians->isEmpty())>{{ $buttonLabel }}</button>
                            </div>
                            <div class="field-error" data-form-error></div>
                        </form>
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
        const serviceStatusForms = document.querySelectorAll('.service-status-form');

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

        function formatStatus(status) {
            return (status || 'unknown').replace(/_/g, ' ');
        }

        function statusBadgeClass(status) {
            switch (status) {
                case 'pending':
                    return 'badge badge-warning';
                case 'assigned':
                    return 'badge badge-info';
                case 'in_progress':
                    return 'badge badge-primary';
                case 'completed':
                    return 'badge badge-success';
                default:
                    return 'badge badge-neutral';
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

        function clearGlobalMessages() {
            successBox.textContent = '';
            errorBox.textContent = '';
            setVisible(successBox, false);
            setVisible(errorBox, false);
        }

        async function submitJson(endpoint, payload) {
            await ensureCsrfCookie();

            const response = await fetch(endpoint, {
                method: 'PUT',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-XSRF-TOKEN': getCookie('XSRF-TOKEN') || '',
                },
                body: JSON.stringify(payload),
            });

            const responseBody = await response.json();

            if (response.status === 422) {
                const errors = responseBody.errors || {};
                const firstError = Object.values(errors)[0];
                const message = Array.isArray(firstError)
                    ? firstError[0]
                    : (responseBody.message || 'Please review the form.');

                throw new Error(message);
            }

            if (!response.ok) {
                throw new Error(responseBody.message || 'Request could not be completed.');
            }

            return responseBody;
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
                const assignmentStateBadge = document.querySelector(`[data-assignment-state-for="${requestKey}"]`);
                const completionStateBadge = document.querySelector(`[data-completion-state-for="${requestKey}"]`);
                const completionMessage = document.querySelector(`[data-completion-message-for="${requestKey}"]`);
                const selectedOption = select.options[select.selectedIndex];

                inlineError.textContent = '';
                button.disabled = true;
                button.textContent = 'Saving...';

                try {
                    const responseBody = await submitJson(form.dataset.endpoint, {
                        technician_id: Number(select.value),
                    });

                    if (technicianLabel) {
                        technicianLabel.textContent = selectedOption.textContent.trim();
                    }

                    if (assignmentStateBadge) {
                        assignmentStateBadge.textContent = 'Assigned';
                        assignmentStateBadge.className = 'badge badge-neutral';
                    }

                    if (completionStateBadge) {
                        completionStateBadge.textContent = 'No completion request';
                        completionStateBadge.className = 'badge badge-neutral';
                    }

                    if (completionMessage) {
                        completionMessage.textContent = 'No technician completion request has been submitted yet.';
                    }

                    form.dataset.defaultLabel = 'Update assignment';
                    successBox.textContent = responseBody.message || 'Technician assigned successfully.';
                    setVisible(successBox, true);
                } catch (error) {
                    inlineError.textContent = error.message || 'Could not assign technician.';
                    errorBox.textContent = error.message || 'Could not assign technician.';
                    setVisible(errorBox, true);
                } finally {
                    button.disabled = false;
                    button.textContent = form.dataset.defaultLabel || 'Assign technician';
                }
            });
        });

        serviceStatusForms.forEach((form) => {
            form.addEventListener('submit', async (event) => {
                event.preventDefault();
                clearGlobalMessages();

                const select = form.elements.namedItem('status');
                const button = form.querySelector('button[type="submit"]');
                const inlineError = form.querySelector('[data-form-error]');
                const requestKey = form.dataset.requestKey;
                const statusBadge = document.querySelector(`[data-status-for="${requestKey}"]`);
                const completionStateBadge = document.querySelector(`[data-completion-state-for="${requestKey}"]`);
                const completionMessage = document.querySelector(`[data-completion-message-for="${requestKey}"]`);

                inlineError.textContent = '';
                button.disabled = true;
                button.textContent = 'Saving...';

                try {
                    const responseBody = await submitJson(form.dataset.endpoint, {
                        status: select.value,
                    });

                    const updatedRequest = responseBody.data || null;
                    const updatedStatus = updatedRequest?.status || select.value;
                    const completionRequestedAt = updatedRequest?.technician_marked_done_at || null;

                    if (statusBadge) {
                        statusBadge.textContent = formatStatus(updatedStatus);
                        statusBadge.className = statusBadgeClass(updatedStatus);
                    }

                    if (completionStateBadge) {
                        if (completionRequestedAt && updatedStatus === 'completed') {
                            completionStateBadge.textContent = 'Admin confirmed completion';
                            completionStateBadge.className = 'badge badge-success';
                        } else if (completionRequestedAt) {
                            completionStateBadge.textContent = 'Awaiting admin review';
                            completionStateBadge.className = 'badge badge-warning';
                        } else {
                            completionStateBadge.textContent = 'No completion request';
                            completionStateBadge.className = 'badge badge-neutral';
                        }
                    }

                    if (completionMessage) {
                        if (completionRequestedAt && updatedStatus === 'completed') {
                            completionMessage.textContent = 'Technician completion was reviewed by admin and the official status is now completed.';
                        } else if (completionRequestedAt) {
                            completionMessage.textContent = 'Technician marked this service as done and it is still awaiting final admin confirmation.';
                        } else if (updatedStatus === 'completed') {
                            completionMessage.textContent = 'Admin marked this service request as completed.';
                        } else {
                            completionMessage.textContent = 'No technician completion request has been submitted yet.';
                        }
                    }

                    successBox.textContent = responseBody.message || 'Official service request status updated successfully.';
                    setVisible(successBox, true);
                } catch (error) {
                    inlineError.textContent = error.message || 'Could not update the official service request status.';
                    errorBox.textContent = error.message || 'Could not update the official service request status.';
                    setVisible(errorBox, true);
                } finally {
                    button.disabled = false;
                    button.textContent = 'Save official status';
                }
            });
        });
    </script>
@endpush
