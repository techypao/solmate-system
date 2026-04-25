@extends('layouts.app', ['title' => 'Admin Services'])

@php
    $sortedInspectionRequests = $inspectionRequests
        ->sortByDesc(fn ($request) => optional($request->created_at)->timestamp ?? 0)
        ->values();

    $sortedServiceRequests = $serviceRequests
        ->sortByDesc(fn ($request) => optional($request->created_at)->timestamp ?? 0)
        ->values();

    $isInstallationRequest = fn ($request) => \Illuminate\Support\Str::of((string) ($request->request_type ?? ''))
        ->lower()
        ->replace(['_', '-'], ' ')
        ->contains('installation');

    $installationRequests = $sortedServiceRequests
        ->filter($isInstallationRequest)
        ->values();

    $maintenanceRequests = $sortedServiceRequests
        ->reject($isInstallationRequest)
        ->values();

    $serviceTabCounts = [
        'inspection' => $sortedInspectionRequests->count(),
        'installation' => $installationRequests->count(),
        'maintenance' => $maintenanceRequests->count(),
    ];

    $statusClasses = [
        'pending' => 'badge badge-warning',
        'approved' => 'badge badge-info',
        'scheduled' => 'badge badge-primary',
        'assigned' => 'badge badge-info',
        'in_progress' => 'badge badge-primary',
        'cancelled' => 'badge badge-danger',
        'declined' => 'badge badge-danger',
        'completed' => 'badge badge-success',
    ];

    $serviceStatusOptions = [
        'pending' => 'Pending',
        'approved' => 'Approved',
        'scheduled' => 'Scheduled',
        'assigned' => 'Assigned',
        'in_progress' => 'In Progress',
        'cancelled' => 'Cancelled',
        'declined' => 'Declined',
        'completed' => 'Completed',
    ];

    $serviceRequestRecords = $sortedServiceRequests
        ->map(fn ($request) => [
            'requestKey' => "service-{$request->id}",
            'date_needed' => $request->date_needed
                ? \Illuminate\Support\Carbon::parse($request->date_needed)->toDateString()
                : null,
            'status' => $request->status,
        ])
        ->values()
        ->all();

    $inspectionRequestRecords = $sortedInspectionRequests
        ->map(fn ($request) => [
            'requestKey' => "inspection-{$request->id}",
            'date_needed' => $request->date_needed
                ? \Illuminate\Support\Carbon::parse($request->date_needed)->toDateString()
                : null,
            'status' => $request->status,
        ])
        ->values()
        ->all();
@endphp

@section('content')
    <style>
        .assignment-page {
            display: grid;
            gap: 24px;
        }

        .assignment-page .request-card .info-box {
            background: #f8fbff;
        }

        .assignment-page .request-card form + form {
            padding-top: 16px;
            border-top: 1px solid #e2e8f0;
        }

        .assignment-page .request-card .stack {
            gap: 16px;
        }

        .services-workspace {
            display: grid;
            gap: 22px;
        }

        .services-toolbar {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
        }

        .services-tab-list {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px;
            max-width: 100%;
            overflow-x: auto;
            border: 1px solid #dbe4f0;
            border-radius: 999px;
            background: #f8fbff;
            -webkit-overflow-scrolling: touch;
        }

        .services-tab-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 18px;
            border-radius: 999px;
            color: #4b5b73;
            font-weight: 700;
            text-decoration: none;
            white-space: nowrap;
            transition: background-color .2s ease, color .2s ease, box-shadow .2s ease;
        }

        .services-tab-link:hover {
            background: rgba(255, 255, 255, .88);
            color: #16324f;
        }

        .services-tab-link.active {
            color: #fff;
            background: linear-gradient(135deg, #173b63, #2a5b92);
            box-shadow: 0 12px 26px rgba(23, 59, 99, .22);
        }

        .services-tab-count {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 22px;
            height: 22px;
            padding: 0 7px;
            border-radius: 999px;
            background: rgba(255, 255, 255, .18);
            font-size: 12px;
            font-weight: 800;
        }

        .services-tab-link:not(.active) .services-tab-count {
            background: #dbe4f0;
            color: #4b5b73;
        }

        .services-toolbar-copy {
            margin: 0;
            max-width: 420px;
        }

        .services-tab-panel {
            display: none;
            gap: 18px;
        }

        .services-tab-panel.active {
            display: grid;
        }

        .services-panel-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
        }

        .services-panel-copy {
            margin: 6px 0 0;
        }

        .services-empty-state {
            margin-bottom: 0;
        }

        @media (max-width: 768px) {
            .services-tab-list {
                width: 100%;
            }

            .services-tab-link {
                padding: 10px 14px;
                font-size: 14px;
            }
        }
    </style>

    <div class="assignment-page">
    <div class="card admin-hero-card">
        <div class="section-header">
            <div>
                <p class="admin-page-eyebrow">Admin Operations</p>
                <h1 class="page-title">Admin Services</h1>
                <p class="page-copy">Review inspection, installation, and maintenance requests, assign technicians, and keep the official services workflow under admin control.</p>
            </div>
            <a class="button-link secondary" href="{{ route('admin.technicians.create') }}">Register Technician</a>
        </div>

        <div class="summary-grid">
            <div class="summary-card">
                <div class="summary-label">Available technicians</div>
                <div class="summary-value">{{ $technicians->count() }}</div>
            </div>
            <div class="summary-card">
                <div class="summary-label">Services awaiting review</div>
                <div class="summary-value">{{ $sortedServiceRequests->filter(fn ($request) => filled($request->technician_marked_done_at) && $request->status !== 'completed')->count() }}</div>
            </div>
            <div class="summary-card">
                <div class="summary-label">Unassigned services</div>
                <div class="summary-value">{{ $sortedServiceRequests->whereNull('technician_id')->count() }}</div>
            </div>
            <div class="summary-card">
                <div class="summary-label">Unassigned inspection requests</div>
                <div class="summary-value">{{ $sortedInspectionRequests->whereNull('technician_id')->count() }}</div>
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

    <div id="service-requests-section" class="card admin-section-surface services-workspace">
        <div class="section-header">
            <div>
                <h2 class="admin-section-title">Services</h2>
                <p class="page-copy" style="margin-bottom: 0;">Use the tabs below to work through inspection, installation, and maintenance requests. Each list keeps the same request cards and admin actions, sorted newest first.</p>
            </div>
            <span class="badge badge-neutral">{{ $sortedInspectionRequests->count() + $sortedServiceRequests->count() }} total</span>
        </div>

        <div class="services-toolbar">
            <nav class="services-tab-list" aria-label="Services categories" role="tablist">
                <a
                    id="services-tab-inspection"
                    href="#inspection-requests-section"
                    class="services-tab-link active"
                    data-services-tab-link="inspection"
                    role="tab"
                    aria-selected="true"
                >
                    Inspection
                    <span class="services-tab-count">{{ $sortedInspectionRequests->count() }}</span>
                </a>
                <a
                    id="services-tab-installation"
                    href="#installation-requests-section"
                    class="services-tab-link"
                    data-services-tab-link="installation"
                    role="tab"
                    aria-selected="false"
                >
                    Installation
                    <span class="services-tab-count">{{ $installationRequests->count() }}</span>
                </a>
                <a
                    id="services-tab-maintenance"
                    href="#maintenance-requests-section"
                    class="services-tab-link"
                    data-services-tab-link="maintenance"
                    role="tab"
                    aria-selected="false"
                >
                    Maintenance
                    <span class="services-tab-count">{{ $maintenanceRequests->count() }}</span>
                </a>
            </nav>
            <p class="page-copy services-toolbar-copy">Latest submitted requests appear first in every tab. Technician assignment, preferred date changes, completion review, and official status updates still work the same way.</p>
        </div>

        <section
            id="inspection-requests-section"
            class="services-tab-panel active"
            data-services-panel="inspection"
            role="tabpanel"
            aria-labelledby="services-tab-inspection"
        >
            <div class="services-panel-head">
                <div>
                    <h3 class="admin-section-title" style="margin-bottom: 0;">Inspection</h3>
                    <p class="page-copy services-panel-copy">Inspection requests stay grouped here with customer details, the official preferred date, current status, and technician assignment in one place.</p>
                </div>
                <span class="badge badge-neutral">{{ $sortedInspectionRequests->count() }} total</span>
            </div>

            @if ($sortedInspectionRequests->isEmpty())
                <div class="info-box services-empty-state">No inspection requests yet.</div>
            @else
                <div class="request-list">
                    @foreach ($sortedInspectionRequests as $inspectionRequest)
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

                        <div id="inspection-request-{{ $inspectionRequest->id }}" class="request-card">
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
                                    <span class="detail-label">Contact Number</span>
                                    <strong>{{ $inspectionRequest->contact_number ?: 'Not provided' }}</strong>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Address</span>
                                    <strong>{{ $inspectionRequest->address ?: 'Not provided' }}</strong>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Request Type</span>
                                    <strong>Inspection</strong>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Preferred Date</span>
                                    <strong data-preferred-date-for="{{ $requestKey }}">{{ $dateNeeded }}</strong>
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
                                class="preferred-date-form"
                                data-endpoint="/api/inspection-requests/{{ $inspectionRequest->id }}/preferred-date"
                                data-request-key="{{ $requestKey }}"
                            >
                                <label for="inspection_date_needed_{{ $inspectionRequest->id }}">Official preferred date</label>
                                <div class="assignment-row">
                                    <div>
                                        <input
                                            id="inspection_date_needed_{{ $inspectionRequest->id }}"
                                            name="date_needed"
                                            type="date"
                                            value="{{ $inspectionRequest->date_needed ? \Illuminate\Support\Carbon::parse($inspectionRequest->date_needed)->toDateString() : '' }}"
                                            required
                                        >
                                        <div class="muted" style="margin-top: 8px;">Use this when the original customer date needs to be adjusted for technician availability. Some dates may already be reserved by other active requests.</div>
                                        <div class="muted" style="margin-top: 8px;" data-availability-helper></div>
                                    </div>
                                    <button type="submit">Save preferred date</button>
                                </div>
                                <div class="field-error" data-form-error></div>
                            </form>

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
        </section>

        <section
            id="installation-requests-section"
            class="services-tab-panel"
            data-services-panel="installation"
            role="tabpanel"
            aria-labelledby="services-tab-installation"
        >
            <div class="services-panel-head">
                <div>
                    <h3 class="admin-section-title" style="margin-bottom: 0;">Installation</h3>
                    <p class="page-copy services-panel-copy">Installation requests stay grouped here so admin can keep scheduling, technician assignments, completion review, and official status updates aligned.</p>
                </div>
                <span class="badge badge-neutral">{{ $installationRequests->count() }} total</span>
            </div>

            @if ($installationRequests->isEmpty())
                <div class="info-box services-empty-state">No installation requests yet.</div>
            @else
                <div class="request-list">
                    @foreach ($installationRequests as $serviceRequest)
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

                        <div id="service-request-{{ $serviceRequest->id }}" class="request-card">
                            <div class="request-header">
                                <div>
                                    <div class="request-title">Installation Request #{{ $serviceRequest->id }}</div>
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
                                    <span class="detail-label">Contact Number</span>
                                    <strong>{{ $serviceRequest->contact_number ?: 'Not provided' }}</strong>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Address</span>
                                    <strong>{{ $serviceRequest->address ?: 'Not provided' }}</strong>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Request Type</span>
                                    <strong>{{ $serviceRequest->request_type ?: 'Not specified' }}</strong>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Preferred Date</span>
                                    <strong data-service-preferred-date-for="{{ $requestKey }}">{{ $dateNeeded }}</strong>
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
                                    class="service-preferred-date-form"
                                    data-endpoint="/api/admin/service-requests/{{ $serviceRequest->id }}/preferred-date"
                                    data-request-key="{{ $requestKey }}"
                                >
                                    <label for="service_date_needed_{{ $serviceRequest->id }}">Official preferred date</label>
                                    <div class="assignment-row">
                                        <div>
                                            <input
                                                id="service_date_needed_{{ $serviceRequest->id }}"
                                                name="date_needed"
                                                type="date"
                                                value="{{ $serviceRequest->date_needed ? \Illuminate\Support\Carbon::parse($serviceRequest->date_needed)->toDateString() : '' }}"
                                                required
                                            >
                                            <div class="muted" style="margin-top: 8px;">Adjust this when the customer's requested service date needs to move for technician availability. Some dates may already be reserved by other active requests.</div>
                                            <div class="muted" style="margin-top: 8px;" data-availability-helper></div>
                                        </div>
                                        <button type="submit">Save preferred date</button>
                                    </div>
                                    <div class="field-error" data-form-error></div>
                                </form>

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
        </section>

        <section
            id="maintenance-requests-section"
            class="services-tab-panel"
            data-services-panel="maintenance"
            role="tabpanel"
            aria-labelledby="services-tab-maintenance"
        >
            <div class="services-panel-head">
                <div>
                    <h3 class="admin-section-title" style="margin-bottom: 0;">Maintenance</h3>
                    <p class="page-copy services-panel-copy">Maintenance requests stay grouped here so recurring service work, repairs, and admin follow-up remain easy to review.</p>
                </div>
                <span class="badge badge-neutral">{{ $maintenanceRequests->count() }} total</span>
            </div>

            @if ($maintenanceRequests->isEmpty())
                <div class="info-box services-empty-state">No maintenance requests yet.</div>
            @else
                <div class="request-list">
                    @foreach ($maintenanceRequests as $serviceRequest)
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

                        <div id="service-request-{{ $serviceRequest->id }}" class="request-card">
                            <div class="request-header">
                                <div>
                                    <div class="request-title">Maintenance Request #{{ $serviceRequest->id }}</div>
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
                                    <span class="detail-label">Contact Number</span>
                                    <strong>{{ $serviceRequest->contact_number ?: 'Not provided' }}</strong>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Address</span>
                                    <strong>{{ $serviceRequest->address ?: 'Not provided' }}</strong>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Request Type</span>
                                    <strong>{{ $serviceRequest->request_type ?: 'Not specified' }}</strong>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Preferred Date</span>
                                    <strong data-service-preferred-date-for="{{ $requestKey }}">{{ $dateNeeded }}</strong>
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
                                    class="service-preferred-date-form"
                                    data-endpoint="/api/admin/service-requests/{{ $serviceRequest->id }}/preferred-date"
                                    data-request-key="{{ $requestKey }}"
                                >
                                    <label for="service_date_needed_{{ $serviceRequest->id }}">Official preferred date</label>
                                    <div class="assignment-row">
                                        <div>
                                            <input
                                                id="service_date_needed_{{ $serviceRequest->id }}"
                                                name="date_needed"
                                                type="date"
                                                value="{{ $serviceRequest->date_needed ? \Illuminate\Support\Carbon::parse($serviceRequest->date_needed)->toDateString() : '' }}"
                                                required
                                            >
                                            <div class="muted" style="margin-top: 8px;">Adjust this when the customer's requested service date needs to move for technician availability. Some dates may already be reserved by other active requests.</div>
                                            <div class="muted" style="margin-top: 8px;" data-availability-helper></div>
                                        </div>
                                        <button type="submit">Save preferred date</button>
                                    </div>
                                    <div class="field-error" data-form-error></div>
                                </form>

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
        </section>
    </div>
    </div>
@endsection

@push('scripts')
    <script type="application/json" id="__data_serviceRequestRecords">@json($serviceRequestRecords)</script>
    <script type="application/json" id="__data_inspectionRequestRecords">@json($inspectionRequestRecords)</script>
    <script type="application/json" id="__data_serviceTabCounts">@json($serviceTabCounts)</script>
    <script>
        const reservedDateMessage = 'Selected date is already reserved. Please choose another date.';
        const successBox = document.getElementById('assignment-success');
        const errorBox = document.getElementById('assignment-error');
        const serviceTabLinks = document.querySelectorAll('[data-services-tab-link]');
        const serviceTabPanels = document.querySelectorAll('[data-services-panel]');
        const assignmentForms = document.querySelectorAll('.assignment-form');
        const servicePreferredDateForms = document.querySelectorAll('.service-preferred-date-form');
        const preferredDateForms = document.querySelectorAll('.preferred-date-form');
        const serviceStatusForms = document.querySelectorAll('.service-status-form');
        const lockingStatuses = new Set(['pending', 'approved', 'scheduled', 'assigned', 'in_progress']);
        const serviceRequestRecords = JSON.parse(document.getElementById('__data_serviceRequestRecords').textContent);
        const inspectionRequestRecords = JSON.parse(document.getElementById('__data_inspectionRequestRecords').textContent);
        const serviceTabCounts = JSON.parse(document.getElementById('__data_serviceTabCounts').textContent);
        const requestRecords = serviceRequestRecords.concat(inspectionRequestRecords);

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

        function normalizeDate(value) {
            if (!value) {
                return '';
            }

            return `${value}`.slice(0, 10);
        }

        function formatDisplayDate(value) {
            if (!value) {
                return 'Not specified';
            }

            const parsedDate = new Date(`${normalizeDate(value)}T00:00:00`);

            if (Number.isNaN(parsedDate.getTime())) {
                return value;
            }

            return parsedDate.toLocaleDateString(undefined, {
                month: 'short',
                day: '2-digit',
                year: 'numeric',
            });
        }

        function statusBadgeClass(status) {
            switch (status) {
                case 'pending':
                    return 'badge badge-warning';
                case 'approved':
                    return 'badge badge-info';
                case 'scheduled':
                    return 'badge badge-primary';
                case 'assigned':
                    return 'badge badge-info';
                case 'in_progress':
                    return 'badge badge-primary';
                case 'cancelled':
                case 'declined':
                    return 'badge badge-danger';
                case 'completed':
                    return 'badge badge-success';
                default:
                    return 'badge badge-neutral';
            }
        }

        function isRequestLocking(record) {
            return Boolean(record?.date_needed) && lockingStatuses.has(record.status);
        }

        function getReservedDatesExcluding(requestKey) {
            const reservedDates = new Set();

            requestRecords.forEach((record) => {
                if (record.requestKey === requestKey || !isRequestLocking(record)) {
                    return;
                }

                reservedDates.add(normalizeDate(record.date_needed));
            });

            return Array.from(reservedDates).sort();
        }

        function formatReservedDatesSummary(dates) {
            if (!dates.length) {
                return 'No other reserved dates are currently listed. Backend validation still applies when you save.';
            }

            const visibleDates = dates.slice(0, 6).map((date) => formatDisplayDate(date));
            const remainingCount = dates.length - visibleDates.length;

            return `Other reserved dates right now: ${visibleDates.join(', ')}${remainingCount > 0 ? `, +${remainingCount} more` : ''}. Backend validation still applies when you save.`;
        }

        function getRequestRecord(requestKey) {
            return requestRecords.find((record) => record.requestKey === requestKey) || null;
        }

        function renderAvailabilityForForm(form) {
            const requestKey = form.dataset.requestKey;
            const input = form.elements.namedItem('date_needed');
            const inlineError = form.querySelector('[data-form-error]');
            const helper = form.querySelector('[data-availability-helper]');
            const reservedDates = getReservedDatesExcluding(requestKey);
            const normalizedValue = normalizeDate(input?.value);

            if (helper) {
                helper.textContent = formatReservedDatesSummary(reservedDates);
            }

            if (normalizedValue && reservedDates.includes(normalizedValue)) {
                inlineError.textContent = reservedDateMessage;
            } else if (inlineError?.textContent === reservedDateMessage) {
                inlineError.textContent = '';
            }
        }

        function refreshAllAvailabilityHints() {
            servicePreferredDateForms.forEach(renderAvailabilityForForm);
            preferredDateForms.forEach(renderAvailabilityForForm);
        }

        function updateRequestRecord(requestKey, updates) {
            const record = getRequestRecord(requestKey);

            if (!record) {
                return;
            }

            Object.assign(record, updates);
        }

        function firstAvailableServiceTab() {
            if ((serviceTabCounts.installation || 0) > 0) {
                return 'installation';
            }

            if ((serviceTabCounts.maintenance || 0) > 0) {
                return 'maintenance';
            }

            return 'inspection';
        }

        function resolveTabFromHash(hash) {
            switch (hash) {
                case '#inspection-requests-section':
                    return 'inspection';
                case '#installation-requests-section':
                    return 'installation';
                case '#maintenance-requests-section':
                    return 'maintenance';
                case '#service-requests-section':
                case '#services-section':
                    return firstAvailableServiceTab();
                default:
                    return 'inspection';
            }
        }

        function panelHashForTab(tabKey) {
            switch (tabKey) {
                case 'installation':
                    return '#installation-requests-section';
                case 'maintenance':
                    return '#maintenance-requests-section';
                case 'inspection':
                default:
                    return '#inspection-requests-section';
            }
        }

        function setActiveServiceTab(tabKey, syncHash = false) {
            serviceTabLinks.forEach((link) => {
                const isActive = link.dataset.servicesTabLink === tabKey;
                link.classList.toggle('active', isActive);
                link.setAttribute('aria-selected', isActive ? 'true' : 'false');
            });

            serviceTabPanels.forEach((panel) => {
                const isActive = panel.dataset.servicesPanel === tabKey;
                panel.classList.toggle('active', isActive);
                panel.hidden = !isActive;
            });

            if (syncHash) {
                window.history.replaceState(null, '', panelHashForTab(tabKey));
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

        if (serviceTabLinks.length && serviceTabPanels.length) {
            serviceTabLinks.forEach((link) => {
                link.addEventListener('click', (event) => {
                    event.preventDefault();

                    const tabKey = link.dataset.servicesTabLink || 'inspection';
                    setActiveServiceTab(tabKey, true);

                    const activePanel = document.querySelector(`[data-services-panel="${tabKey}"]`);

                    if (activePanel) {
                        activePanel.scrollIntoView({ block: 'start', behavior: 'smooth' });
                    }
                });
            });

            window.addEventListener('hashchange', () => {
                setActiveServiceTab(resolveTabFromHash(window.location.hash), false);
            });

            setActiveServiceTab(resolveTabFromHash(window.location.hash), false);
        }

        refreshAllAvailabilityHints();

        servicePreferredDateForms.forEach((form) => {
            const input = form.elements.namedItem('date_needed');

            input.addEventListener('input', () => {
                renderAvailabilityForForm(form);
            });

            form.addEventListener('submit', async (event) => {
                event.preventDefault();
                clearGlobalMessages();

                const button = form.querySelector('button[type="submit"]');
                const inlineError = form.querySelector('[data-form-error]');
                const requestKey = form.dataset.requestKey;
                const preferredDateLabel = document.querySelector(`[data-service-preferred-date-for="${requestKey}"]`);

                inlineError.textContent = '';

                if (getReservedDatesExcluding(requestKey).includes(normalizeDate(input.value))) {
                    inlineError.textContent = reservedDateMessage;
                    errorBox.textContent = reservedDateMessage;
                    setVisible(errorBox, true);
                    return;
                }

                button.disabled = true;
                button.textContent = 'Saving...';

                try {
                    const responseBody = await submitJson(form.dataset.endpoint, {
                        date_needed: input.value,
                    });

                    const updatedRequest = responseBody.data || null;
                    const updatedDate = updatedRequest?.date_needed || input.value;

                    if (preferredDateLabel) {
                        preferredDateLabel.textContent = formatDisplayDate(updatedDate);
                    }

                    if (updatedDate) {
                        input.value = normalizeDate(updatedDate);
                    }

                    updateRequestRecord(requestKey, {
                        date_needed: normalizeDate(updatedDate),
                    });
                    refreshAllAvailabilityHints();
                    successBox.textContent = responseBody.message || 'Service preferred date updated successfully.';
                    setVisible(successBox, true);
                } catch (error) {
                    inlineError.textContent = error.message || 'Could not update the preferred date.';
                    errorBox.textContent = error.message || 'Could not update the preferred date.';
                    setVisible(errorBox, true);
                } finally {
                    button.disabled = false;
                    button.textContent = 'Save preferred date';
                }
            });
        });

        preferredDateForms.forEach((form) => {
            const input = form.elements.namedItem('date_needed');

            input.addEventListener('input', () => {
                renderAvailabilityForForm(form);
            });

            form.addEventListener('submit', async (event) => {
                event.preventDefault();
                clearGlobalMessages();

                const button = form.querySelector('button[type="submit"]');
                const inlineError = form.querySelector('[data-form-error]');
                const requestKey = form.dataset.requestKey;
                const preferredDateLabel = document.querySelector(`[data-preferred-date-for="${requestKey}"]`);

                inlineError.textContent = '';

                if (getReservedDatesExcluding(requestKey).includes(normalizeDate(input.value))) {
                    inlineError.textContent = reservedDateMessage;
                    errorBox.textContent = reservedDateMessage;
                    setVisible(errorBox, true);
                    return;
                }

                button.disabled = true;
                button.textContent = 'Saving...';

                try {
                    const responseBody = await submitJson(form.dataset.endpoint, {
                        date_needed: input.value,
                    });

                    const updatedRequest = responseBody.inspection_request || null;
                    const updatedDate = updatedRequest?.date_needed || input.value;

                    if (preferredDateLabel) {
                        preferredDateLabel.textContent = formatDisplayDate(updatedDate);
                    }

                    if (updatedDate) {
                        input.value = normalizeDate(updatedDate);
                    }

                    updateRequestRecord(requestKey, {
                        date_needed: normalizeDate(updatedDate),
                    });
                    refreshAllAvailabilityHints();
                    successBox.textContent = responseBody.message || 'Inspection preferred date updated successfully.';
                    setVisible(successBox, true);
                } catch (error) {
                    inlineError.textContent = error.message || 'Could not update the preferred date.';
                    errorBox.textContent = error.message || 'Could not update the preferred date.';
                    setVisible(errorBox, true);
                } finally {
                    button.disabled = false;
                    button.textContent = 'Save preferred date';
                }
            });
        });

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

                    const serviceRequest = responseBody.data || responseBody.service_request || null;
                    const inspectionRequest = responseBody.inspection_request || null;
                    const updatedStatus = serviceRequest?.status || inspectionRequest?.status || null;

                    if (updatedStatus) {
                        updateRequestRecord(requestKey, {
                            status: updatedStatus,
                        });
                        refreshAllAvailabilityHints();
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

                    updateRequestRecord(requestKey, {
                        status: updatedStatus,
                    });
                    refreshAllAvailabilityHints();

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
