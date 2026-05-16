@extends('layouts.app', ['title' => 'Customer List'])

@section('content')
<div class="admin-page-stack">

    <div class="card admin-hero-card">
        <div class="section-header">
            <div>
                <p class="admin-page-eyebrow">Admin Customer Management</p>
                <h1 class="page-title">Customers</h1>
                <p class="page-copy">All registered customer accounts in SolMate are listed below. Use this page to get an overview of your customer base.</p>
            </div>
            <a class="button-link secondary" href="{{ route('admin.request-assignments') }}">Open Request Assignments</a>
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
                    <div class="list-row">
                        <div style="flex: 1; min-width: 0;">
                            <strong>{{ $customer->name }}</strong>
                            <div class="muted">{{ $customer->email }}</div>
                        </div>
                        <div class="muted" style="font-size: 13px; white-space: nowrap;">
                            Joined {{ $customer->created_at->format('M d, Y') }}
                        </div>
                        <span class="badge badge-neutral">Customer</span>
                        <div style="display:flex; gap:8px; flex-shrink:0;">
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
                        <span class="badge" style="background:#fef3c7; color:#92400e; border:1px solid #fde68a;">Archived</span>
                        <div style="display:flex; gap:8px; flex-shrink:0;">
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
                    </div>
                @endforeach
            </div>
        @endif
    </div>

</div>
@endsection
