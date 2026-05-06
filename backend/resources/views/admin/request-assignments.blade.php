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

    $adminServicePopup = session('admin_service_popup');
    $formatAdminDateTime = fn ($value) => $value
        ? \Illuminate\Support\Carbon::parse($value)->format('M d, Y g:i A')
        : null;
    $parseRequestDetailCards = function (?string $details) {
        return collect(preg_split('/\r\n|\r|\n/', trim((string) $details)))
            ->map(fn ($line) => trim((string) $line))
            ->filter()
            ->map(function (string $line) {
                if (str_contains($line, ':')) {
                    [$label, $value] = array_map('trim', explode(':', $line, 2));

                    return [
                        'label' => $label !== '' ? $label : 'Request Details / Description',
                        'value' => $value !== '' ? $value : 'Not provided',
                        'wide' => mb_strlen($value) > 96,
                    ];
                }

                return [
                    'label' => 'Request Details / Description',
                    'value' => $line,
                    'wide' => mb_strlen($line) > 96,
                ];
            })
            ->values();
    };

    $latestRequestReview = function ($serviceRequest) {
        return $serviceRequest->testimonies()
            ->latest('created_at')
            ->first();
    };
    $awaitingCompletionReviewCount = $sortedServiceRequests
        ->filter(fn ($request) => $request->completionReport && $request->status !== 'completed')
        ->count()
        + $sortedInspectionRequests
            ->filter(fn ($request) => $request->completionReport && $request->status !== 'completed')
            ->count();
@endphp

@section('content')
    <style>
        .assignment-page {
            display: grid;
            gap: 24px;
        }

        .assignment-page .request-card .info-box {
            background: #F8FAFC;
        }

        .assignment-page .request-card form + form {
            padding-top: 16px;
            border-top: 1px solid #DDE7EE;
        }

        .assignment-page .request-card .stack {
            gap: 16px;
        }

        .assignment-page .request-card .request-header {
            display: grid;
            grid-template-columns: minmax(0, 1.2fr) minmax(320px, 0.8fr);
            grid-template-areas:
                "copy actions"
                "summary summary";
            align-items: start;
            column-gap: 24px;
            row-gap: 18px;
            width: 100%;
            margin-bottom: 0;
        }

        .assignment-page .request-card {
            display: grid;
            gap: 18px;
            border: 1px solid #DDE7EE;
            border-radius: 20px;
            padding: 18px 20px;
            background: linear-gradient(180deg, #ffffff 0%, #F8FAFC 100%);
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.05);
            transition: border-color .2s ease, box-shadow .2s ease, transform .2s ease;
        }

        .assignment-page .request-card.is-active {
            border-color: #2a5b92;
            box-shadow: 0 18px 34px rgba(23, 59, 99, .16);
            transform: translateY(-1px);
        }

        .assignment-page .request-card-body {
            display: none;
            margin-top: 16px;
        }

        .assignment-page .request-card.is-active .request-card-body {
            display: grid;
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
            border: 1px solid #DDE7EE;
            border-radius: 999px;
            background: #F8FAFC;
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
            background: linear-gradient(135deg, #20A7C9, #2a5b92);
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
            background: #DDE7EE;
            color: #4b5b73;
        }

        .services-toolbar-copy {
            margin: 0;
            max-width: 420px;
        }

        .services-focus-banner {
            display: none;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 14px 16px;
            border: 1px solid #DDE7EE;
            border-radius: 16px;
            background: linear-gradient(135deg, #EAF9FD 0%, #F8FAFC 100%);
        }

        .services-focus-banner.is-visible {
            display: flex;
        }

        .services-focus-copy {
            margin: 0;
            color: #20A7C9;
            font-size: 14px;
            font-weight: 700;
        }

        .services-focus-subcopy {
            margin: 4px 0 0;
            color: #4b5b73;
            font-size: 13px;
        }

        .services-focus-chip {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 32px;
            padding: 0 12px;
            border-radius: 999px;
            background: #20A7C9;
            color: #fff;
            font-size: 12px;
            font-weight: 800;
            white-space: nowrap;
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

        .request-summary-grid {
            grid-area: summary;
            display: grid;
            width: 100%;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 12px;
            align-self: stretch;
        }

        .request-summary-item {
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 10px 12px;
            border: 1px solid #DDE7EE;
            border-radius: 14px;
            background: #F8FAFC;
            min-height: 100%;
        }

        .request-summary-label {
            display: block;
            margin-bottom: 4px;
            color: #5E7288;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .4px;
        }

        .request-summary-value {
            color: #123A5A;
            font-size: 13px;
            font-weight: 700;
            line-height: 1.45;
            word-break: break-word;
        }

        .request-header-main {
            display: contents;
        }

        .request-header-copy {
            grid-area: copy;
            display: grid;
            gap: 6px;
            min-width: 0;
        }

        .request-kicker {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            width: fit-content;
            padding: 4px 10px;
            border-radius: 999px;
            background: #EAF9FD;
            color: #20A7C9;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .45px;
        }

        .request-active-indicator {
            display: none;
            margin-left: 8px;
            padding: 4px 10px;
            border-radius: 999px;
            background: #20A7C9;
            color: #fff;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .45px;
        }

        .request-card.is-active .request-active-indicator {
            display: inline-flex;
        }

        .request-top-actions {
            grid-area: actions;
            display: grid;
            justify-items: end;
            align-content: start;
            gap: 10px;
            width: 100%;
            min-width: 0;
        }

        .request-top-actions .request-badges {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            justify-content: flex-end;
            width: 100%;
        }

        .request-toggle-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 40px;
            padding: 0 14px;
            border: 1px solid #DDE7EE;
            border-radius: 12px;
            background: #fff;
            color: #123A5A;
            font-size: 13px;
            font-weight: 800;
            cursor: pointer;
            transition: all .2s ease;
        }

        .request-toggle-btn:hover {
            border-color: #2a5b92;
            color: #20A7C9;
            background: #F8FAFC;
        }

        .request-card.is-active .request-toggle-btn {
            background: #20A7C9;
            border-color: #20A7C9;
            color: #fff;
        }

        .request-card-body .detail-grid {
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        }

        .request-section {
            display: grid;
            gap: 12px;
            padding: 18px;
            border: 1px solid #DDE7EE;
            border-radius: 18px;
            background: #ffffff;
        }

        .request-section-header {
            display: grid;
            gap: 4px;
        }

        .request-section-title {
            margin: 0;
            color: #123A5A;
            font-size: 15px;
            font-weight: 800;
        }

        .request-section-copy {
            margin: 0;
            color: #5E7288;
            font-size: 13px;
            line-height: 1.6;
        }

        .request-section .detail-grid {
            margin-bottom: 0 !important;
        }

        .request-section .detail-item {
            min-height: 100%;
            background: linear-gradient(180deg, #ffffff 0%, #F8FAFC 100%);
        }

        .request-section .detail-item strong {
            display: block;
            color: #123A5A;
            line-height: 1.65;
            word-break: break-word;
            white-space: pre-line;
        }

        .request-section .detail-item--wide {
            grid-column: 1 / -1;
        }

        .request-empty-card {
            padding: 16px 18px;
            border: 1px dashed #DDE7EE;
            border-radius: 16px;
            background: #F8FAFC;
            color: #5E7288;
            font-size: 13.5px;
            font-weight: 700;
            line-height: 1.6;
        }

        .request-card-body .stack {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 16px;
            align-items: stretch;
        }

        .request-card-body .stack > form {
            display: grid;
            grid-template-rows: auto 1fr auto;
            align-content: start;
            gap: 10px;
            min-height: 100%;
            padding: 16px;
            border: 1px solid #DDE7EE;
            border-radius: 16px;
            background: linear-gradient(180deg, #ffffff 0%, #F8FAFC 100%);
        }

        .request-card-body .stack > form label {
            margin-bottom: 0;
        }

        .request-card-body .assignment-row {
            grid-template-columns: 1fr;
            align-items: start;
            gap: 12px;
        }

        .request-card-body .assignment-row > div {
            min-width: 0;
        }

        .request-card-body .assignment-row input,
        .request-card-body .assignment-row select {
            min-height: 46px;
            height: 46px;
            padding-top: 10px;
            padding-bottom: 10px;
            line-height: 1.35;
        }

        .request-card-body .assignment-row button {
            width: 100%;
            min-width: 0;
            min-height: 46px;
            height: 46px;
            padding: 0 16px;
            align-self: stretch;
            border: none;
            background: linear-gradient(135deg, #F4D000 0%, #E6C200 100%);
            color: #0F2F4A;
            font-weight: 800;
            box-shadow: 0 10px 20px rgba(212, 160, 23, 0.16);
        }

        .request-card-body .assignment-row button:hover:not(:disabled) {
            background: linear-gradient(135deg, #E6C200 0%, #F4D000 100%);
        }

        .request-card-body .assignment-row button:disabled {
            opacity: .65;
            cursor: not-allowed;
            box-shadow: none;
        }

        .request-card-body .field-error {
            margin-top: 0;
        }

        .request-card-body [data-availability-helper] {
            display: none !important;
        }

        .request-detail-box {
            margin-bottom: 14px;
        }

        @media (max-width: 1200px) {
            .assignment-page .request-card .request-header {
                grid-template-columns: minmax(0, 1fr);
                grid-template-areas:
                    "copy"
                    "actions"
                    "summary";
            }

            .request-summary-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }

            .request-card-body .stack {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 768px) {
            .request-summary-grid {
                grid-template-columns: 1fr;
            }

            .request-top-actions {
                justify-items: start;
            }

            .request-top-actions .request-badges {
                justify-content: flex-start;
            }

            .services-tab-list {
                width: 100%;
            }

            .services-tab-link {
                padding: 10px 14px;
                font-size: 14px;
            }

            .services-focus-banner {
                align-items: flex-start;
                flex-direction: column;
            }

            .request-card-body .stack,
            .assignment-row {
                grid-template-columns: 1fr;
            }

            .request-card-body .assignment-row button {
                width: 100%;
                min-width: 0;
            }
        }
    </style>

    <form id="service-popup-redirect-form" method="POST" action="{{ route('admin.request-assignments.service-popup') }}" hidden>
        @csrf
        <input type="hidden" name="action" value="">
        <input type="hidden" name="redirect_to" value="">
    </form>

    @if (! empty($adminServicePopup['message']))
        <div class="solmate-toast" id="admin-service-popup-toast" role="status" aria-live="polite">
            <span class="solmate-toast-badge">Success</span>
            <p class="solmate-toast-title">{{ $adminServicePopup['message'] }}</p>
            <p class="solmate-toast-copy">Your update was saved and this notice will only appear once.</p>
        </div>
    @endif

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
                <div class="summary-label">Tasks awaiting completion review</div>
                <div class="summary-value">{{ $awaitingCompletionReviewCount }}</div>
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
            <p class="page-copy services-toolbar-copy">Requests stay compact by default. Open one request at a time to focus on assignment, date, and status updates.</p>
        </div>

        <section
            id="inspection-requests-section"
            class="services-tab-panel active"
            data-services-panel="inspection"
            role="tabpanel"
            aria-labelledby="services-tab-inspection"
        >
            <div class="services-focus-banner" data-services-focus-banner="inspection" aria-live="polite">
                <div>
                    <p class="services-focus-copy">Select an inspection request to start reviewing.</p>
                    <p class="services-focus-subcopy">The active request opens in place so it is easier to scan the list.</p>
                </div>
                <span class="services-focus-chip">Inspection</span>
            </div>
            <div class="services-panel-head">
                <div>
                    <h3 class="admin-section-title" style="margin-bottom: 0;">Inspection</h3>
                    <p class="page-copy services-panel-copy">Quick summaries first, then expand the request you want to work on.</p>
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
                            $completionReport = $inspectionRequest->completionReport;
                            $hasCompletionReport = filled($completionReport?->submitted_at);
                            $buttonLabel = $isAssigned ? 'Update assignment' : 'Assign technician';
                            $dateNeeded = $inspectionRequest->date_needed
                                ? \Illuminate\Support\Carbon::parse($inspectionRequest->date_needed)->format('M d, Y')
                                : 'Not specified';
                            $technicianSummary = $inspectionRequest->technician
                                ? "{$inspectionRequest->technician->name} ({$inspectionRequest->technician->email})"
                                : 'Not assigned';
                            $completionStateClass = 'badge badge-neutral';
                            $completionStateLabel = 'No report yet';

                            if ($hasCompletionReport && $inspectionRequest->status === 'completed') {
                                $completionStateClass = 'badge badge-success';
                                $completionStateLabel = 'Report approved';
                            } elseif ($hasCompletionReport) {
                                $completionStateClass = 'badge badge-warning';
                                $completionStateLabel = 'Awaiting admin review';
                            }

                            if ($hasCompletionReport && $inspectionRequest->status === 'completed') {
                                $inspectionWorkflowMessage = 'Technician submitted the completion notes on '
                                    . $formatAdminDateTime($completionReport->submitted_at)
                                    . ', and the official inspection status is now completed.';
                            } elseif ($hasCompletionReport) {
                                $inspectionWorkflowMessage = 'Technician submitted the completion notes on '
                                    . $formatAdminDateTime($completionReport->submitted_at)
                                    . '. Review the report below before marking the official inspection status as completed.';
                            } elseif ($isAssigned) {
                                $inspectionWorkflowMessage = 'Technician assignment and official inspection scheduling can be managed below.';
                            } else {
                                $inspectionWorkflowMessage = 'Assign a technician first, then keep the official inspection status updated here.';
                            }
                        @endphp

                        <div
                            id="inspection-request-{{ $inspectionRequest->id }}"
                            class="request-card"
                            data-request-card
                            data-request-tab="inspection"
                            data-request-label="Inspection Request #{{ $inspectionRequest->id }}"
                        >
                            <div class="request-header">
                                <div class="request-header-main">
                                    <div class="request-header-copy">
                                        <span class="request-kicker">New Inspection Request</span>
                                        <div class="request-title">
                                            Inspection Request #{{ $inspectionRequest->id }}
                                            <span class="request-active-indicator">Open now</span>
                                        </div>
                                        <div class="muted">Customer: {{ $inspectionRequest->customer?->name ?? 'Unknown customer' }}</div>
                                    </div>

                                    <div class="request-top-actions">
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
                                            <span
                                                class="{{ $completionStateClass }}"
                                                data-completion-state-for="{{ $requestKey }}"
                                            >
                                                {{ $completionStateLabel }}
                                            </span>
                                        </div>
                                        <button type="button" class="request-toggle-btn" data-request-toggle>
                                            Open request
                                        </button>
                                    </div>
                                </div>

                                <div class="request-summary-grid">
                                    <div class="request-summary-item">
                                        <span class="request-summary-label">Contact</span>
                                        <div class="request-summary-value">{{ $inspectionRequest->contact_number ?: 'Not provided' }}</div>
                                    </div>
                                    <div class="request-summary-item">
                                        <span class="request-summary-label">Preferred Date</span>
                                        <div class="request-summary-value" data-preferred-date-for="{{ $requestKey }}">{{ $dateNeeded }}</div>
                                    </div>
                                    <div class="request-summary-item">
                                        <span class="request-summary-label">Technician</span>
                                        <div class="request-summary-value" data-technician-for="{{ $requestKey }}">{{ $technicianSummary }}</div>
                                    </div>
                                    <div class="request-summary-item">
                                        <span class="request-summary-label">Official Status</span>
                                        <div class="request-summary-value" data-inspection-status-summary-for="{{ $requestKey }}">
                                            {{ \Illuminate\Support\Str::headline($inspectionRequest->status) }}
                                        </div>
                                    </div>
                                    <div class="request-summary-item">
                                        <span class="request-summary-label">Completion</span>
                                        <div class="request-summary-value" data-completion-summary-for="{{ $requestKey }}">
                                            {{ $completionStateLabel }}
                                        </div>
                                    </div>
                                    <div class="request-summary-item">
                                        <span class="request-summary-label">Address</span>
                                        <div class="request-summary-value">{{ $inspectionRequest->address ?: 'Not provided' }}</div>
                                    </div>
                                    <div class="request-summary-item">
                                        <span class="request-summary-label">Address Details</span>
                                        <div class="request-summary-value">{{ $inspectionRequest->address_details ?: 'Not provided' }}</div>
                                    </div>
                                    <div class="request-summary-item">
                                        <span class="request-summary-label">Request Type</span>
                                        <div class="request-summary-value">Inspection</div>
                                    </div>
                                </div>
                            </div>

                            <div class="request-card-body">
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
                                        <span class="detail-label">Address Details</span>
                                        <strong>{{ $inspectionRequest->address_details ?: 'Not provided' }}</strong>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">Request Type</span>
                                        <strong>Inspection</strong>
                                    </div>
                                </div>

                                <div class="info-box request-detail-box">
                                    <strong>Request details:</strong> {{ $inspectionRequest->details }}
                                </div>

                                <div class="info-box request-detail-box">
                                    <strong>Inspection workflow:</strong>
                                    <span data-completion-detail-for="{{ $requestKey }}">{{ $inspectionWorkflowMessage }}</span>
                                </div>

                                @if ($completionReport)
                                    <div class="detail-grid" style="margin-bottom: 14px;">
                                        <div class="detail-item">
                                            <span class="detail-label">Report Submitted By</span>
                                            <strong>{{ $completionReport->technician?->name ?? ($inspectionRequest->technician?->name ?? 'Assigned technician') }}</strong>
                                        </div>
                                        <div class="detail-item">
                                            <span class="detail-label">Task Completed At</span>
                                            <strong>{{ $formatAdminDateTime($completionReport->completed_at) ?? 'Not available' }}</strong>
                                        </div>
                                        <div class="detail-item">
                                            <span class="detail-label">Report Submitted At</span>
                                            <strong>{{ $formatAdminDateTime($completionReport->submitted_at) ?? 'Not available' }}</strong>
                                        </div>
                                        <div class="detail-item">
                                            <span class="detail-label">Approved At</span>
                                            <strong>{{ $formatAdminDateTime($completionReport->approved_at) ?? 'Pending admin review' }}</strong>
                                        </div>
                                    </div>

                                    <div class="info-box request-detail-box">
                                        <strong>Completion notes:</strong> {{ $completionReport->report_text }}
                                    </div>

                                    @if (filled($completionReport->findings))
                                        <div class="info-box request-detail-box">
                                            <strong>Findings:</strong> {{ $completionReport->findings }}
                                        </div>
                                    @endif

                                    @if (filled($completionReport->recommendations))
                                        <div class="info-box request-detail-box">
                                            <strong>Recommendations:</strong> {{ $completionReport->recommendations }}
                                        </div>
                                    @endif
                                @endif

                                <div class="stack">
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
                                            </div>
                                            <button type="submit" @disabled($technicians->isEmpty())>{{ $buttonLabel }}</button>
                                        </div>
                                        <div class="field-error" data-form-error></div>
                                    </form>

                                    <form
                                        class="inspection-status-form"
                                        data-endpoint="/api/admin/inspection-requests/{{ $inspectionRequest->id }}/status"
                                        data-request-key="{{ $requestKey }}"
                                    >
                                        <label for="inspection_status_{{ $inspectionRequest->id }}">Official inspection status</label>
                                        <div class="assignment-row">
                                            <div>
                                                <select
                                                    id="inspection_status_{{ $inspectionRequest->id }}"
                                                    name="status"
                                                    required
                                                >
                                                    @foreach ($serviceStatusOptions as $value => $label)
                                                        <option value="{{ $value }}" @selected($inspectionRequest->status === $value)>{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <button type="submit">Save official status</button>
                                        </div>
                                        <div class="field-error" data-form-error></div>
                                    </form>
                                </div>
                            </div>
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
            <div class="services-focus-banner" data-services-focus-banner="installation" aria-live="polite">
                <div>
                    <p class="services-focus-copy">Select an installation request to work on it.</p>
                    <p class="services-focus-subcopy">Once opened, that request becomes the active one in this tab.</p>
                </div>
                <span class="services-focus-chip">Installation</span>
            </div>
            <div class="services-panel-head">
                <div>
                    <h3 class="admin-section-title" style="margin-bottom: 0;">Installation</h3>
                    <p class="page-copy services-panel-copy">Keep the list short on screen, then open the specific installation request you want to update.</p>
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
                            $completionReport = $serviceRequest->completionReport;
                            $hasCompletionRequest = filled($completionReport?->submitted_at);
                            $buttonLabel = $isAssigned ? 'Update assignment' : 'Assign technician';
                            $dateNeeded = $serviceRequest->date_needed
                                ? \Illuminate\Support\Carbon::parse($serviceRequest->date_needed)->format('M d, Y')
                                : 'Not specified';
                            $technicianSummary = $serviceRequest->technician
                                ? "{$serviceRequest->technician->name} ({$serviceRequest->technician->email})"
                                : 'Not assigned';
                            $completionHeading = 'Completion review';
                            $completionStateClass = 'badge badge-neutral';
                            $completionStateLabel = 'No report yet';

                            if ($hasCompletionRequest && $serviceRequest->status !== 'completed') {
                                $completionStateClass = 'badge badge-warning';
                                $completionStateLabel = 'Awaiting admin review';
                            } elseif ($hasCompletionRequest && $serviceRequest->status === 'completed') {
                                $completionStateClass = 'badge badge-success';
                                $completionStateLabel = 'Report approved';
                            }

                            if ($hasCompletionRequest && $serviceRequest->status !== 'completed') {
                                $completionMessage = 'Technician submitted the completion notes on '
                                    . ($formatAdminDateTime($completionReport?->submitted_at) ?? $formatAdminDateTime($serviceRequest->technician_marked_done_at))
                                    . '. Review the report below before marking the official status as completed.';
                            } elseif ($hasCompletionRequest && $serviceRequest->status === 'completed') {
                                $completionMessage = 'Technician submitted the completion notes on '
                                    . ($formatAdminDateTime($completionReport?->submitted_at) ?? $formatAdminDateTime($serviceRequest->technician_marked_done_at))
                                    . ', and the official service status is now completed.';
                            } else {
                                $completionMessage = 'No technician completion notes have been submitted yet.';
                            }

                            $serviceDetailCards = $parseRequestDetailCards($serviceRequest->details);
                            $latestServiceReview = $latestRequestReview($serviceRequest);
                        @endphp

                        <div
                            id="service-request-{{ $serviceRequest->id }}"
                            class="request-card"
                            data-request-card
                            data-request-tab="installation"
                            data-request-label="Installation Request #{{ $serviceRequest->id }}"
                        >
                            <div class="request-header">
                                <div class="request-header-main">
                                    <div class="request-header-copy">
                                        <span class="request-kicker">New Installation Request</span>
                                        <div class="request-title">
                                            Installation Request #{{ $serviceRequest->id }}
                                            <span class="request-active-indicator">Open now</span>
                                        </div>
                                        <div class="muted">Customer: {{ $serviceRequest->customer?->name ?? 'Unknown customer' }}</div>
                                    </div>

                                    <div class="request-top-actions">
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
                                        <button type="button" class="request-toggle-btn" data-request-toggle>
                                            Open request
                                        </button>
                                    </div>
                                </div>

                                <div class="request-summary-grid">
                                    <div class="request-summary-item">
                                        <span class="request-summary-label">Preferred Date</span>
                                        <div class="request-summary-value" data-service-preferred-date-for="{{ $requestKey }}">{{ $dateNeeded }}</div>
                                    </div>
                                    <div class="request-summary-item">
                                        <span class="request-summary-label">Technician</span>
                                        <div class="request-summary-value" data-technician-for="{{ $requestKey }}">{{ $technicianSummary }}</div>
                                    </div>
                                    <div class="request-summary-item">
                                        <span class="request-summary-label">Official Status</span>
                                        <div class="request-summary-value" data-service-status-summary-for="{{ $requestKey }}">
                                            {{ \Illuminate\Support\Str::headline($serviceRequest->status) }}
                                        </div>
                                    </div>
                                    <div class="request-summary-item">
                                        <span class="request-summary-label">Completion</span>
                                        <div class="request-summary-value" data-completion-summary-for="{{ $requestKey }}">{{ $completionStateLabel }}</div>
                                    </div>
                                </div>
                            </div>

                            <div class="request-card-body">
                                <div class="request-section">
                                    <div class="request-section-header">
                                        <h4 class="request-section-title">Customer information</h4>
                                        <p class="request-section-copy">Primary contact and location details for this service request.</p>
                                    </div>
                                    <div class="detail-grid">
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
                                            <span class="detail-label">Address Details</span>
                                            <strong>{{ $serviceRequest->address_details ?: 'Not provided' }}</strong>
                                        </div>
                                        <div class="detail-item">
                                            <span class="detail-label">Request Type</span>
                                            <strong>{{ $serviceRequest->request_type ?: 'Not specified' }}</strong>
                                        </div>
                                    </div>
                                </div>

                                <div class="request-section">
                                    <div class="request-section-header">
                                        <h4 class="request-section-title">Request details</h4>
                                        <p class="request-section-copy">Structured request information displayed in the same card style as the customer section.</p>
                                    </div>
                                    @if ($serviceDetailCards->isNotEmpty())
                                        <div class="detail-grid">
                                            @foreach ($serviceDetailCards as $detailCard)
                                                <div class="detail-item {{ $detailCard['wide'] ? 'detail-item--wide' : '' }}">
                                                    <span class="detail-label">{{ $detailCard['label'] }}</span>
                                                    <strong>{{ $detailCard['value'] }}</strong>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="request-empty-card">No request details were provided.</div>
                                    @endif
                                </div>

                                <div class="request-section">
                                    <div class="request-section-header">
                                        <h4 class="request-section-title">Customer review</h4>
                                        <p class="request-section-copy">Latest submitted customer feedback for this request.</p>
                                    </div>
                                    @if ($latestServiceReview)
                                        <div class="detail-grid">
                                            <div class="detail-item">
                                                <span class="detail-label">Rating</span>
                                                <strong>{{ $latestServiceReview->rating ? $latestServiceReview->rating . ' / 5' : 'Not provided' }}</strong>
                                            </div>
                                            <div class="detail-item">
                                                <span class="detail-label">Review Date</span>
                                                <strong>{{ $formatAdminDateTime($latestServiceReview->created_at) ?? 'Not available' }}</strong>
                                            </div>
                                            <div class="detail-item detail-item--wide">
                                                <span class="detail-label">Review / Feedback</span>
                                                <strong>{{ $latestServiceReview->message ?: 'No written feedback provided.' }}</strong>
                                            </div>
                                        </div>
                                    @else
                                        <div class="request-empty-card">No customer review yet.</div>
                                    @endif
                                </div>

                                <div class="info-box request-detail-box">
                                    <strong data-completion-heading-for="{{ $requestKey }}">{{ $completionHeading }}:</strong>
                                    <span data-completion-detail-for="{{ $requestKey }}">{{ $completionMessage }}</span>
                                </div>

                                @if ($completionReport)
                                    <div class="detail-grid" style="margin-bottom: 14px;">
                                        <div class="detail-item">
                                            <span class="detail-label">Report Submitted By</span>
                                            <strong>{{ $completionReport->technician?->name ?? ($serviceRequest->technician?->name ?? 'Assigned technician') }}</strong>
                                        </div>
                                        <div class="detail-item">
                                            <span class="detail-label">Task Completed At</span>
                                            <strong>{{ $formatAdminDateTime($completionReport->completed_at) ?? 'Not available' }}</strong>
                                        </div>
                                        <div class="detail-item">
                                            <span class="detail-label">Report Submitted At</span>
                                            <strong>{{ $formatAdminDateTime($completionReport->submitted_at) ?? 'Not available' }}</strong>
                                        </div>
                                        <div class="detail-item">
                                            <span class="detail-label">Approved At</span>
                                            <strong>{{ $formatAdminDateTime($completionReport->approved_at) ?? 'Pending admin review' }}</strong>
                                        </div>
                                    </div>

                                    <div class="info-box request-detail-box">
                                        <strong>Completion notes:</strong> {{ $completionReport->report_text }}
                                    </div>

                                    @if (filled($completionReport->findings))
                                        <div class="info-box request-detail-box">
                                            <strong>Findings:</strong> {{ $completionReport->findings }}
                                        </div>
                                    @endif

                                    @if (filled($completionReport->recommendations))
                                        <div class="info-box request-detail-box">
                                            <strong>Recommendations:</strong> {{ $completionReport->recommendations }}
                                        </div>
                                    @endif
                                @endif

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
                                                    @if (($serviceRequest->status ?? null) === 'declined')
                                                        <option value="declined" selected disabled>Declined (legacy)</option>
                                                    @endif
                                                    @foreach ($serviceStatusOptions as $value => $label)
                                                        <option value="{{ $value }}" @selected($serviceRequest->status === $value)>{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <button type="submit">Save official status</button>
                                        </div>
                                        <div class="field-error" data-form-error></div>
                                    </form>
                                </div>
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
            <div class="services-focus-banner" data-services-focus-banner="maintenance" aria-live="polite">
                <div>
                    <p class="services-focus-copy">Select a maintenance request to review it.</p>
                    <p class="services-focus-subcopy">The open request is highlighted so it is obvious where you are working.</p>
                </div>
                <span class="services-focus-chip">Maintenance</span>
            </div>
            <div class="services-panel-head">
                <div>
                    <h3 class="admin-section-title" style="margin-bottom: 0;">Maintenance</h3>
                    <p class="page-copy services-panel-copy">Open only the maintenance request you need so the page feels faster to scan.</p>
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
                            $completionReport = $serviceRequest->completionReport;
                            $hasCompletionRequest = filled($completionReport?->submitted_at);
                            $buttonLabel = $isAssigned ? 'Update assignment' : 'Assign technician';
                            $dateNeeded = $serviceRequest->date_needed
                                ? \Illuminate\Support\Carbon::parse($serviceRequest->date_needed)->format('M d, Y')
                                : 'Not specified';
                            $technicianSummary = $serviceRequest->technician
                                ? "{$serviceRequest->technician->name} ({$serviceRequest->technician->email})"
                                : 'Not assigned';
                            $completionHeading = 'Completion review';
                            $completionStateClass = 'badge badge-neutral';
                            $completionStateLabel = 'No report yet';

                            if ($hasCompletionRequest && $serviceRequest->status !== 'completed') {
                                $completionStateClass = 'badge badge-warning';
                                $completionStateLabel = 'Awaiting admin review';
                            } elseif ($hasCompletionRequest && $serviceRequest->status === 'completed') {
                                $completionStateClass = 'badge badge-success';
                                $completionStateLabel = 'Report approved';
                            }

                            if ($hasCompletionRequest && $serviceRequest->status !== 'completed') {
                                $completionMessage = 'Technician submitted the completion notes on '
                                    . ($formatAdminDateTime($completionReport?->submitted_at) ?? $formatAdminDateTime($serviceRequest->technician_marked_done_at))
                                    . '. Review the report below before marking the official status as completed.';
                            } elseif ($hasCompletionRequest && $serviceRequest->status === 'completed') {
                                $completionMessage = 'Technician submitted the completion notes on '
                                    . ($formatAdminDateTime($completionReport?->submitted_at) ?? $formatAdminDateTime($serviceRequest->technician_marked_done_at))
                                    . ', and the official service status is now completed.';
                            } else {
                                $completionMessage = 'No technician completion notes have been submitted yet.';
                            }

                            $serviceDetailCards = $parseRequestDetailCards($serviceRequest->details);
                            $latestServiceReview = $latestRequestReview($serviceRequest);
                        @endphp

                        <div
                            id="service-request-{{ $serviceRequest->id }}"
                            class="request-card"
                            data-request-card
                            data-request-tab="maintenance"
                            data-request-label="Maintenance Request #{{ $serviceRequest->id }}"
                        >
                            <div class="request-header">
                                <div class="request-header-main">
                                    <div class="request-header-copy">
                                        <span class="request-kicker">New Maintenance Request</span>
                                        <div class="request-title">
                                            Maintenance Request #{{ $serviceRequest->id }}
                                            <span class="request-active-indicator">Open now</span>
                                        </div>
                                        <div class="muted">Customer: {{ $serviceRequest->customer?->name ?? 'Unknown customer' }}</div>
                                    </div>

                                    <div class="request-top-actions">
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
                                        <button type="button" class="request-toggle-btn" data-request-toggle>
                                            Open request
                                        </button>
                                    </div>
                                </div>

                                <div class="request-summary-grid">
                                    <div class="request-summary-item">
                                        <span class="request-summary-label">Preferred Date</span>
                                        <div class="request-summary-value" data-service-preferred-date-for="{{ $requestKey }}">{{ $dateNeeded }}</div>
                                    </div>
                                    <div class="request-summary-item">
                                        <span class="request-summary-label">Technician</span>
                                        <div class="request-summary-value" data-technician-for="{{ $requestKey }}">{{ $technicianSummary }}</div>
                                    </div>
                                    <div class="request-summary-item">
                                        <span class="request-summary-label">Official Status</span>
                                        <div class="request-summary-value" data-service-status-summary-for="{{ $requestKey }}">
                                            {{ \Illuminate\Support\Str::headline($serviceRequest->status) }}
                                        </div>
                                    </div>
                                    <div class="request-summary-item">
                                        <span class="request-summary-label">Completion</span>
                                        <div class="request-summary-value" data-completion-summary-for="{{ $requestKey }}">{{ $completionStateLabel }}</div>
                                    </div>
                                </div>
                            </div>

                            <div class="request-card-body">
                                <div class="request-section">
                                    <div class="request-section-header">
                                        <h4 class="request-section-title">Customer information</h4>
                                        <p class="request-section-copy">Primary contact and location details for this service request.</p>
                                    </div>
                                    <div class="detail-grid">
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
                                            <span class="detail-label">Address Details</span>
                                            <strong>{{ $serviceRequest->address_details ?: 'Not provided' }}</strong>
                                        </div>
                                        <div class="detail-item">
                                            <span class="detail-label">Request Type</span>
                                            <strong>{{ $serviceRequest->request_type ?: 'Not specified' }}</strong>
                                        </div>
                                    </div>
                                </div>

                                <div class="request-section">
                                    <div class="request-section-header">
                                        <h4 class="request-section-title">Request details</h4>
                                        <p class="request-section-copy">Structured request information displayed in the same card style as the customer section.</p>
                                    </div>
                                    @if ($serviceDetailCards->isNotEmpty())
                                        <div class="detail-grid">
                                            @foreach ($serviceDetailCards as $detailCard)
                                                <div class="detail-item {{ $detailCard['wide'] ? 'detail-item--wide' : '' }}">
                                                    <span class="detail-label">{{ $detailCard['label'] }}</span>
                                                    <strong>{{ $detailCard['value'] }}</strong>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="request-empty-card">No request details were provided.</div>
                                    @endif
                                </div>

                                <div class="request-section">
                                    <div class="request-section-header">
                                        <h4 class="request-section-title">Customer review</h4>
                                        <p class="request-section-copy">Latest submitted customer feedback for this request.</p>
                                    </div>
                                    @if ($latestServiceReview)
                                        <div class="detail-grid">
                                            <div class="detail-item">
                                                <span class="detail-label">Rating</span>
                                                <strong>{{ $latestServiceReview->rating ? $latestServiceReview->rating . ' / 5' : 'Not provided' }}</strong>
                                            </div>
                                            <div class="detail-item">
                                                <span class="detail-label">Review Date</span>
                                                <strong>{{ $formatAdminDateTime($latestServiceReview->created_at) ?? 'Not available' }}</strong>
                                            </div>
                                            <div class="detail-item detail-item--wide">
                                                <span class="detail-label">Review / Feedback</span>
                                                <strong>{{ $latestServiceReview->message ?: 'No written feedback provided.' }}</strong>
                                            </div>
                                        </div>
                                    @else
                                        <div class="request-empty-card">No customer review yet.</div>
                                    @endif
                                </div>

                                <div class="info-box request-detail-box">
                                    <strong data-completion-heading-for="{{ $requestKey }}">{{ $completionHeading }}:</strong>
                                    <span data-completion-detail-for="{{ $requestKey }}">{{ $completionMessage }}</span>
                                </div>

                                @if ($completionReport)
                                    <div class="detail-grid" style="margin-bottom: 14px;">
                                        <div class="detail-item">
                                            <span class="detail-label">Report Submitted By</span>
                                            <strong>{{ $completionReport->technician?->name ?? ($serviceRequest->technician?->name ?? 'Assigned technician') }}</strong>
                                        </div>
                                        <div class="detail-item">
                                            <span class="detail-label">Task Completed At</span>
                                            <strong>{{ $formatAdminDateTime($completionReport->completed_at) ?? 'Not available' }}</strong>
                                        </div>
                                        <div class="detail-item">
                                            <span class="detail-label">Report Submitted At</span>
                                            <strong>{{ $formatAdminDateTime($completionReport->submitted_at) ?? 'Not available' }}</strong>
                                        </div>
                                        <div class="detail-item">
                                            <span class="detail-label">Approved At</span>
                                            <strong>{{ $formatAdminDateTime($completionReport->approved_at) ?? 'Pending admin review' }}</strong>
                                        </div>
                                    </div>

                                    <div class="info-box request-detail-box">
                                        <strong>Completion notes:</strong> {{ $completionReport->report_text }}
                                    </div>

                                    @if (filled($completionReport->findings))
                                        <div class="info-box request-detail-box">
                                            <strong>Findings:</strong> {{ $completionReport->findings }}
                                        </div>
                                    @endif

                                    @if (filled($completionReport->recommendations))
                                        <div class="info-box request-detail-box">
                                            <strong>Recommendations:</strong> {{ $completionReport->recommendations }}
                                        </div>
                                    @endif
                                @endif

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
                                                    @if (($serviceRequest->status ?? null) === 'declined')
                                                        <option value="declined" selected disabled>Declined (legacy)</option>
                                                    @endif
                                                    @foreach ($serviceStatusOptions as $value => $label)
                                                        <option value="{{ $value }}" @selected($serviceRequest->status === $value)>{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <button type="submit">Save official status</button>
                                        </div>
                                        <div class="field-error" data-form-error></div>
                                    </form>
                                </div>
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
    <script type="application/json" id="__data_adminServicePopup">@json($adminServicePopup)</script>
    <script>
        const reservedDateMessage = 'Selected date is already reserved. Please choose another date.';
        const successBox = document.getElementById('assignment-success');
        const errorBox = document.getElementById('assignment-error');
        const popupRedirectForm = document.getElementById('service-popup-redirect-form');
        const popupActionInput = popupRedirectForm?.querySelector('input[name="action"]');
        const popupRedirectInput = popupRedirectForm?.querySelector('input[name="redirect_to"]');
        const serviceTabLinks = document.querySelectorAll('[data-services-tab-link]');
        const serviceTabPanels = document.querySelectorAll('[data-services-panel]');
        const assignmentForms = document.querySelectorAll('.assignment-form');
        const servicePreferredDateForms = document.querySelectorAll('.service-preferred-date-form');
        const preferredDateForms = document.querySelectorAll('.preferred-date-form');
        const serviceStatusForms = document.querySelectorAll('.service-status-form');
        const inspectionStatusForms = document.querySelectorAll('.inspection-status-form');
        const requestCards = document.querySelectorAll('[data-request-card]');
        const focusBanners = document.querySelectorAll('[data-services-focus-banner]');
        const lockingStatuses = new Set(['pending', 'approved', 'scheduled', 'assigned', 'in_progress']);
        const adminServicePopup = JSON.parse(document.getElementById('__data_adminServicePopup').textContent);
        const serviceRequestRecords = JSON.parse(document.getElementById('__data_serviceRequestRecords').textContent);
        const inspectionRequestRecords = JSON.parse(document.getElementById('__data_inspectionRequestRecords').textContent);
        const serviceTabCounts = JSON.parse(document.getElementById('__data_serviceTabCounts').textContent);
        const requestRecords = serviceRequestRecords.concat(inspectionRequestRecords);

        const adminServicePopupToast = document.getElementById('admin-service-popup-toast');

        if (adminServicePopup?.message) {
            window.addEventListener('load', () => {
                if (window.Swal && typeof window.Swal.fire === 'function') {
                    window.requestAnimationFrame(() => {
                        window.Swal.fire({
                            icon: 'success',
                            text: adminServicePopup.message,
                            confirmButtonText: 'OK',
                        });
                    });
                    return;
                }

                if (!adminServicePopupToast) {
                    return;
                }

                window.requestAnimationFrame(() => {
                    adminServicePopupToast.classList.add('is-visible');
                });

                window.setTimeout(() => {
                    adminServicePopupToast.classList.remove('is-visible');

                    window.setTimeout(() => {
                        adminServicePopupToast.remove();
                    }, 240);
                }, 2600);
            }, { once: true });
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
            element.style.display = visible ? 'block' : 'none';
        }

        function formatStatus(status) {
            return (status || 'unknown')
                .replace(/_/g, ' ')
                .replace(/\b\w/g, (character) => character.toUpperCase());
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

        function completionUiState(updatedRequest) {
            const completionReport = updatedRequest?.completion_report || null;
            const updatedStatus = updatedRequest?.status || null;

            if (completionReport && (completionReport.status === 'approved' || updatedStatus === 'completed')) {
                return {
                    label: 'Report approved',
                    className: 'badge badge-success',
                    message: 'Technician completion notes were reviewed by admin and the official status is now completed.',
                };
            }

            if (completionReport) {
                return {
                    label: 'Awaiting admin review',
                    className: 'badge badge-warning',
                    message: 'Technician submitted completion notes and they are awaiting admin review.',
                };
            }

            return {
                label: 'No report yet',
                className: 'badge badge-neutral',
                message: 'No technician completion notes have been submitted yet.',
            };
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

        function requestCardFromHash(hash) {
            if (typeof hash !== 'string' || !hash.startsWith('#')) {
                return null;
            }

            const candidate = document.getElementById(hash.slice(1));

            return candidate?.matches?.('[data-request-card]') ? candidate : null;
        }

        function resolveTabFromHash(hash) {
            const requestCard = requestCardFromHash(hash);

            if (requestCard?.dataset.requestTab) {
                return requestCard.dataset.requestTab;
            }

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

        function focusBannerForTab(tabKey) {
            return document.querySelector(`[data-services-focus-banner="${tabKey}"]`);
        }

        function setFocusedRequest(card) {
            requestCards.forEach((item) => {
                const isActive = item === card;
                item.classList.toggle('is-active', isActive);

                const button = item.querySelector('[data-request-toggle]');
                if (button) {
                    button.textContent = isActive ? 'Collapse request' : 'Open request';
                }
            });

            focusBanners.forEach((banner) => {
                banner.classList.remove('is-visible');
            });

            if (!card) {
                return;
            }

            const tabKey = card.dataset.requestTab;
            const requestLabel = card.dataset.requestLabel || 'Selected request';
            const focusBanner = focusBannerForTab(tabKey);

            if (focusBanner) {
                focusBanner.classList.add('is-visible');
                const copy = focusBanner.querySelector('.services-focus-copy');
                const subcopy = focusBanner.querySelector('.services-focus-subcopy');

                if (copy) {
                    copy.textContent = `You are now viewing ${requestLabel}.`;
                }

                if (subcopy) {
                    subcopy.textContent = 'This is the active request currently open in the list.';
                }
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

            const hashCard = requestCardFromHash(window.location.hash);
            const preferredCard = hashCard && hashCard.dataset.requestTab === tabKey
                ? hashCard
                : null;

            if (preferredCard) {
                setFocusedRequest(preferredCard);
            } else if (!document.querySelector(`[data-request-card].is-active[data-request-tab="${tabKey}"]`)) {
                const firstCard = document.querySelector(`[data-request-card][data-request-tab="${tabKey}"]`);
                setFocusedRequest(firstCard || null);
            }

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

        function redirectWithServicePopup(action, form) {
            if (!popupRedirectForm || !popupActionInput || !popupRedirectInput) {
                return false;
            }

            const requestCard = form?.closest?.('[data-request-card]');
            const redirectHash = requestCard?.id
                ? `#${requestCard.id}`
                : (window.location.hash || '#services-section');

            popupActionInput.value = action;
            popupRedirectInput.value = redirectHash;
            popupRedirectForm.submit();

            return true;
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

        requestCards.forEach((card) => {
            const toggle = card.querySelector('[data-request-toggle]');

            if (!toggle) {
                return;
            }

            toggle.addEventListener('click', () => {
                const isActive = card.classList.contains('is-active');

                if (isActive) {
                    setFocusedRequest(null);
                    return;
                }

                setFocusedRequest(card);
                card.scrollIntoView({ block: 'start', behavior: 'smooth' });
            });
        });

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

                    if (redirectWithServicePopup('preferred_date_changed', form)) {
                        return;
                    }

                    successBox.textContent = 'Preferred date has been successfully updated.';
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

                    if (redirectWithServicePopup('inspection_preferred_date_changed', form)) {
                        return;
                    }

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
                const completionMessage = document.querySelector(`[data-completion-detail-for="${requestKey}"]`);
                const completionSummary = document.querySelector(`[data-completion-summary-for="${requestKey}"]`);
                const statusBadge = document.querySelector(`[data-status-for="${requestKey}"]`);
                const serviceStatusSummary = document.querySelector(`[data-service-status-summary-for="${requestKey}"]`);
                const inspectionStatusSummary = document.querySelector(`[data-inspection-status-summary-for="${requestKey}"]`);
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

                        if (statusBadge) {
                            statusBadge.textContent = formatStatus(updatedStatus);
                            statusBadge.className = statusBadgeClass(updatedStatus);
                        }

                        if (serviceStatusSummary) {
                            serviceStatusSummary.textContent = formatStatus(updatedStatus);
                        }

                        if (inspectionStatusSummary) {
                            inspectionStatusSummary.textContent = formatStatus(updatedStatus);
                        }
                    }

                    if (completionStateBadge) {
                        completionStateBadge.textContent = 'No report yet';
                        completionStateBadge.className = 'badge badge-neutral';
                    }

                    if (completionMessage) {
                        completionMessage.textContent = 'No technician completion notes have been submitted yet.';
                    }

                    if (completionSummary) {
                        completionSummary.textContent = 'No report yet';
                    }

                    form.dataset.defaultLabel = 'Update assignment';

                    if (requestKey.startsWith('service-')) {
                        if (redirectWithServicePopup('technician_assigned', form)) {
                            return;
                        }
                    }

                    if (requestKey.startsWith('inspection-')) {
                        if (redirectWithServicePopup('inspection_technician_assigned', form)) {
                            return;
                        }
                    }

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
                const serviceStatusSummary = document.querySelector(`[data-service-status-summary-for="${requestKey}"]`);
                const completionStateBadge = document.querySelector(`[data-completion-state-for="${requestKey}"]`);
                const completionMessage = document.querySelector(`[data-completion-detail-for="${requestKey}"]`);
                const completionSummary = document.querySelector(`[data-completion-summary-for="${requestKey}"]`);

                inlineError.textContent = '';
                button.disabled = true;
                button.textContent = 'Saving...';

                try {
                    const responseBody = await submitJson(form.dataset.endpoint, {
                        status: select.value,
                    });

                    const updatedRequest = responseBody.data || null;
                    const updatedStatus = updatedRequest?.status || select.value;
                    const completionState = completionUiState(updatedRequest);

                    if (statusBadge) {
                        statusBadge.textContent = formatStatus(updatedStatus);
                        statusBadge.className = statusBadgeClass(updatedStatus);
                    }

                    if (serviceStatusSummary) {
                        serviceStatusSummary.textContent = formatStatus(updatedStatus);
                    }

                    updateRequestRecord(requestKey, {
                        status: updatedStatus,
                    });
                    refreshAllAvailabilityHints();

                    if (completionStateBadge) {
                        completionStateBadge.textContent = completionState.label;
                        completionStateBadge.className = completionState.className;
                    }

                    if (completionMessage) {
                        completionMessage.textContent = completionState.message;
                    }

                    if (completionSummary) {
                        completionSummary.textContent = completionState.label;
                    }

                    if (redirectWithServicePopup('status_changed', form)) {
                        return;
                    }

                    successBox.textContent = 'Service status has been successfully updated.';
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

        inspectionStatusForms.forEach((form) => {
            form.addEventListener('submit', async (event) => {
                event.preventDefault();
                clearGlobalMessages();

                const select = form.elements.namedItem('status');
                const button = form.querySelector('button[type="submit"]');
                const inlineError = form.querySelector('[data-form-error]');
                const requestKey = form.dataset.requestKey;
                const statusBadge = document.querySelector(`[data-status-for="${requestKey}"]`);
                const inspectionStatusSummary = document.querySelector(`[data-inspection-status-summary-for="${requestKey}"]`);
                const completionStateBadge = document.querySelector(`[data-completion-state-for="${requestKey}"]`);
                const completionMessage = document.querySelector(`[data-completion-detail-for="${requestKey}"]`);
                const completionSummary = document.querySelector(`[data-completion-summary-for="${requestKey}"]`);

                inlineError.textContent = '';
                button.disabled = true;
                button.textContent = 'Saving...';

                try {
                    const responseBody = await submitJson(form.dataset.endpoint, {
                        status: select.value,
                    });

                    const updatedRequest = responseBody.inspection_request || null;
                    const updatedStatus = updatedRequest?.status || select.value;
                    const completionState = completionUiState(updatedRequest);

                    if (statusBadge) {
                        statusBadge.textContent = formatStatus(updatedStatus);
                        statusBadge.className = statusBadgeClass(updatedStatus);
                    }

                    if (inspectionStatusSummary) {
                        inspectionStatusSummary.textContent = formatStatus(updatedStatus);
                    }

                    updateRequestRecord(requestKey, {
                        status: updatedStatus,
                    });
                    refreshAllAvailabilityHints();

                    if (completionStateBadge) {
                        completionStateBadge.textContent = completionState.label;
                        completionStateBadge.className = completionState.className;
                    }

                    if (completionMessage) {
                        completionMessage.textContent = completionState.message;
                    }

                    if (completionSummary) {
                        completionSummary.textContent = completionState.label;
                    }

                    if (redirectWithServicePopup('inspection_status_changed', form)) {
                        return;
                    }

                    successBox.textContent = responseBody.message || 'Official inspection request status updated successfully.';
                    setVisible(successBox, true);
                } catch (error) {
                    inlineError.textContent = error.message || 'Could not update the official inspection request status.';
                    errorBox.textContent = error.message || 'Could not update the official inspection request status.';
                    setVisible(errorBox, true);
                } finally {
                    button.disabled = false;
                    button.textContent = 'Save official status';
                }
            });
        });
    </script>
@endpush
