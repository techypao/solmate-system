@extends('layouts.app', ['title' => 'Customer List'])

@section('content')
@php
    $canManageCustomers = auth()->user()?->hasAdminPermission(\App\Models\User::PERMISSION_MANAGE_CUSTOMERS) ?? false;
    $canManageRequests = auth()->user()?->hasAdminPermission(\App\Models\User::PERMISSION_MANAGE_REQUESTS) ?? false;
@endphp
<div class="admin-page-stack">

    <div class="card admin-hero-card">
        <div class="section-header">
            <div>
                <p class="admin-page-eyebrow">Admin Customer Management</p>
                <h1 class="page-title">Customers</h1>
                <p class="page-copy">All registered customer accounts in SolMate are listed below. Use this page to get an overview of your customer base.</p>
            </div>
            @if ($canManageRequests)
                <a class="button-link secondary" href="{{ route('admin.request-assignments') }}">Open Request Assignments</a>
            @endif
        </div>

        <div class="summary-grid">
            <div class="summary-card">
                <div class="summary-label">Active customers</div>
                <div class="summary-value">{{ $customers->count() }}</div>
            </div>
            <div class="summary-card">
                <div class="summary-label">Archived customers</div>
                <div class="summary-value">{{ $archivedCustomers->count() }}</div>
            </div>
            <div class="summary-card">
                <div class="summary-label">At cancellation limit</div>
                <div class="summary-value">{{ $customers->where('cancellation_count', '>=', 2)->count() }}</div>
            </div>
            <div class="summary-card">
                <div class="summary-label">Delete requests</div>
                <div class="summary-value">{{ $customers->whereNotNull('delete_requested_at')->count() }}</div>
            </div>
        </div>
    </div>

    <div class="card admin-section-surface">
        <div class="section-header">
            <div>
                <h2 class="admin-section-title">Registered Customers</h2>
                <p class="admin-section-copy">Each entry represents an active customer account with access to SolMate services.</p>
            </div>
            <span class="badge badge-neutral">{{ $customers->count() }} active</span>
        </div>

        @if ($customers->isEmpty())
            <div class="info-box" style="margin-bottom: 0;">No customer accounts have been registered yet.</div>
        @else
            <div class="stack">
                @foreach ($customers as $customer)
                    <div class="list-row" id="customer-{{ $customer->id }}">
                        <div style="flex: 1; min-width: 0;">
                            <strong>
                                <a href="{{ route('admin.customers.show', $customer) }}" style="color:inherit; text-decoration:none;">
                                    {{ $customer->name }}
                                </a>
                            </strong>
                            <div class="muted">{{ $customer->email }}</div>
                            @if ($customer->delete_requested_at)
                                <div style="margin-top:8px; padding:10px 12px; border-radius:10px; background:#fff1f2; border:1px solid #fecaca; color:#991b1b; font-size:13px; line-height:1.55;">
                                    <strong>Requested account deletion {{ $customer->delete_requested_at->format('M d, Y h:i A') }}</strong>
                                    <div>{{ $customer->delete_request_reason }}</div>
                                </div>
                            @endif
                        </div>
                        <div class="muted" style="font-size: 13px; white-space: nowrap;">
                            Last activity {{ optional($customer->last_login_at ?? $customer->created_at)->format('M d, Y') }}
                        </div>
                        @if ($customer->cancellation_count > 0)
                            <span class="badge" style="background:{{ $customer->cancellation_count >= 2 ? '#fee2e2' : '#fef3c7' }}; color:{{ $customer->cancellation_count >= 2 ? '#dc2626' : '#92400e' }}; border:1px solid {{ $customer->cancellation_count >= 2 ? '#fca5a5' : '#fde68a' }};">
                                {{ $customer->cancellation_count }}/3 cancellations
                            </span>
                        @endif
                        @if ($customer->delete_requested_at)
                            <span class="badge" style="background:#fee2e2; color:#dc2626; border:1px solid #fca5a5;">
                                Delete requested
                            </span>
                        @endif
                        <span class="badge badge-neutral">Customer</span>
                        <div style="display:flex; gap:8px; flex-shrink:0;">
                            <a href="{{ route('admin.customers.show', $customer) }}"
                               class="button-link secondary"
                               style="padding: 6px 14px; font-size: 13px;">View</a>

                            @if ($canManageCustomers)
                                <a href="{{ route('admin.customers.edit', $customer) }}"
                                   class="button-link secondary"
                                   style="padding: 6px 14px; font-size: 13px;">Edit</a>

                                <form method="POST"
                                      action="{{ route('admin.customers.archive', $customer) }}"
                                      onsubmit="return confirm('Archive customer {{ addslashes($customer->name) }}? They will lose website and app access, but their records will be kept.')">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                            class="button-link secondary"
                                            style="padding:6px 14px; font-size:13px; border-color:#facc15; color:#a16207;">
                                        Archive
                                    </button>
                                </form>

                                <form method="POST"
                                      action="{{ route('admin.customers.destroy', $customer) }}"
                                      onsubmit="return confirm('Delete customer {{ addslashes($customer->name) }}? This will permanently remove their account and cascade-delete their quotations, requests, inspections, and testimonies.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="button-link"
                                            style="padding:6px 14px; font-size:13px; background:#fee2e2; color:#dc2626; border:1.5px solid #fca5a5; border-radius:8px; cursor:pointer; font-weight:600; text-decoration:none; display:inline-flex; align-items:center;">
                                        Delete
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <div class="card admin-section-surface">
        <div class="section-header">
            <div>
                <h2 class="admin-section-title">Archived Customers</h2>
                <p class="admin-section-copy">Archived customers are blocked from signing in, but their historical records remain available.</p>
            </div>
            <span class="badge badge-neutral">{{ $archivedCustomers->count() }} archived</span>
        </div>

        @if ($archivedCustomers->isEmpty())
            <div class="info-box" style="margin-bottom: 0;">No archived customers.</div>
        @else
            <div class="stack">
                @foreach ($archivedCustomers as $customer)
                    <div class="list-row">
                        <div style="flex: 1; min-width: 0;">
                            <strong>{{ $customer->name }}</strong>
                            <div class="muted">{{ $customer->email }}</div>
                        </div>
                        <div class="muted" style="font-size: 13px; white-space: nowrap;">
                            Archived {{ optional($customer->archived_at)->format('M d, Y') }}
                        </div>
                        @if ($customer->cancellation_count > 0)
                            <span class="badge" style="background:#fee2e2; color:#dc2626; border:1px solid #fca5a5;">
                                {{ $customer->cancellation_count }} cancellations
                            </span>
                        @endif
                        <span class="badge" style="background:#fef3c7; color:#92400e; border:1px solid #fde68a;">Archived</span>
                        @if ($canManageCustomers)
                            <div style="display:flex; gap:8px; flex-shrink:0;">
                                <form method="POST"
                                      action="{{ route('admin.customers.restore', $customer) }}"
                                      onsubmit="return confirm('Restore customer {{ addslashes($customer->name) }}? Their login access will be re-enabled.')">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                            class="button-link secondary"
                                            style="padding:6px 14px; font-size:13px; border-color:#86efac; color:#166534;">
                                        Restore
                                    </button>
                                </form>

                                <form method="POST"
                                      action="{{ route('admin.customers.destroy', $customer) }}"
                                      onsubmit="return confirm('Delete archived customer {{ addslashes($customer->name) }} permanently? This action cannot be undone.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="button-link"
                                            style="padding:6px 14px; font-size:13px; background:#fee2e2; color:#dc2626; border:1.5px solid #fca5a5; border-radius:8px; cursor:pointer; font-weight:600; text-decoration:none; display:inline-flex; align-items:center;">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>

</div>
@endsection
