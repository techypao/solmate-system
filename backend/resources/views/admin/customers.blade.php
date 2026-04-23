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
                <div class="summary-label">Total customers</div>
                <div class="summary-value">{{ $customers->count() }}</div>
            </div>
            <div class="summary-card">
                <div class="summary-label">Account access</div>
                <div class="muted">Admin-only page</div>
            </div>
        </div>
    </div>

    <div class="card admin-section-surface">
        <div class="section-header">
            <div>
                <h2 class="admin-section-title">Registered Customers</h2>
                <p class="admin-section-copy">Each entry represents a verified customer account with access to SolMate services.</p>
            </div>
            <span class="badge badge-neutral">{{ $customers->count() }} total</span>
        </div>

        @if ($customers->isEmpty())
            <div class="info-box" style="margin-bottom: 0;">No customer accounts have been registered yet.</div>
        @else
            <div class="stack">
                @foreach ($customers as $customer)
                    <div class="list-row">
                        <div>
                            <strong>{{ $customer->name }}</strong>
                            <div class="muted">{{ $customer->email }}</div>
                        </div>
                        <div class="muted" style="font-size: 13px; white-space: nowrap;">
                            Joined {{ $customer->created_at->format('M d, Y') }}
                        </div>
                        <span class="badge badge-neutral">Customer</span>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

</div>
@endsection
