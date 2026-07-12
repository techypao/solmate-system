@extends('layouts.app', ['title' => 'Customer Details'])

@section('content')
<div class="admin-page-stack">

    <div class="card admin-hero-card">
        <div class="section-header">
            <div>
                <p class="admin-page-eyebrow">Admin Customer Management</p>
                <h1 class="page-title">{{ $customer->name }}</h1>
                <p class="page-copy">Customer profile, service history, requests, feedback, chat, and tracked activity in one place.</p>
            </div>
            <div style="display:flex; gap:8px; flex-wrap:wrap;">
                <a class="button-link secondary" href="{{ route('admin.customers') }}">Back to Customers</a>
                <a class="button-link secondary" href="{{ route('admin.customers.edit', $customer) }}">Edit Customer</a>
                <a class="button-link secondary" href="{{ route('admin.request-assignments') }}">Open Requests</a>
            </div>
        </div>

        <div class="summary-grid">
            <div class="summary-card">
                <div class="summary-label">Quotations</div>
                <div class="summary-value">{{ $quotations->count() }}</div>
            </div>
            <div class="summary-card">
                <div class="summary-label">Requests</div>
                <div class="summary-value">{{ $inspectionRequests->count() + $serviceRequests->count() }}</div>
            </div>
            <div class="summary-card">
                <div class="summary-label">Feedback</div>
                <div class="summary-value">{{ $testimonies->count() }}</div>
            </div>
            <div class="summary-card">
                <div class="summary-label">Cancellations</div>
                <div class="summary-value">{{ $customer->cancellation_count }}</div>
            </div>
        </div>
    </div>

    <div class="card admin-section-surface">
        <div class="section-header">
            <div>
                <h2 class="admin-section-title">Customer Profile</h2>
                <p class="admin-section-copy">Core account information and current account signals.</p>
            </div>
            <span class="badge badge-neutral">Active Customer</span>
        </div>

        <div class="detail-grid">
            <div class="detail-item">
                <span class="detail-label">Email</span>
                <strong>{{ $customer->email }}</strong>
            </div>
            <div class="detail-item">
                <span class="detail-label">Contact number</span>
                <strong>{{ $customer->contact_number ?: 'Not provided' }}</strong>
            </div>
            <div class="detail-item">
                <span class="detail-label">Landline</span>
                <strong>{{ $customer->landline_number ?: 'Not provided' }}</strong>
            </div>
            <div class="detail-item">
                <span class="detail-label">Joined</span>
                <strong>{{ $customer->created_at?->format('M d, Y h:i A') }}</strong>
            </div>
            <div class="detail-item">
                <span class="detail-label">Last activity</span>
                <strong>{{ optional($customer->last_login_at ?? $customer->created_at)->format('M d, Y') }}</strong>
            </div>
            <div class="detail-item">
                <span class="detail-label">Address</span>
                <strong>{{ $customer->address ?: 'Not provided' }}</strong>
            </div>
        </div>

        @if ($customer->delete_requested_at)
            <div class="info-box" style="margin-top:16px; background:#fff1f2; border-color:#fecaca; color:#991b1b;">
                <strong>Requested account deletion {{ $customer->delete_requested_at->format('M d, Y h:i A') }}</strong>
                <div>{{ $customer->delete_request_reason ?: 'No reason provided.' }}</div>
            </div>
        @endif

        <div class="actions" style="margin-top:18px; display:flex; gap:8px; flex-wrap:wrap;">
            <form method="POST"
                  action="{{ route('admin.customers.archive', $customer) }}"
                  onsubmit="return confirm('Archive customer {{ addslashes($customer->name) }}? They will lose website and app access, but their records will be kept.')">
                @csrf
                @method('PATCH')
                <button type="submit" class="button-link secondary" style="border-color:#facc15; color:#a16207;">
                    Archive Customer
                </button>
            </form>

            <form method="POST"
                  action="{{ route('admin.customers.destroy', $customer) }}"
                  onsubmit="return confirm('Delete customer {{ addslashes($customer->name) }}? This will permanently remove their account and cascade-delete their records.')">
                @csrf
                @method('DELETE')
                <button type="submit" class="button-link" style="background:#fee2e2; color:#dc2626; border:1.5px solid #fca5a5; border-radius:8px; cursor:pointer; font-weight:600;">
                    Delete Customer
                </button>
            </form>
        </div>
    </div>

    <div class="card admin-section-surface">
        <div class="section-header">
            <div>
                <h2 class="admin-section-title">Activity Timeline</h2>
                <p class="admin-section-copy">Existing records plus tracked future activity appear here, newest first.</p>
            </div>
            <span class="badge badge-neutral">{{ $timeline->count() }} events</span>
        </div>

        @if ($timeline->isEmpty())
            <div class="info-box" style="margin-bottom:0;">No activity has been recorded yet.</div>
        @else
            <div class="stack">
                @foreach ($timeline as $item)
                    <div class="list-row">
                        <div style="flex:1; min-width:0;">
                            <strong>{{ $item['label'] }}</strong>
                            @if ($item['description'])
                                <div class="muted">{{ $item['description'] }}</div>
                            @endif
                        </div>
                        <span class="badge badge-neutral">{{ $item['badge'] }}</span>
                        <div class="muted" style="font-size:13px; white-space:nowrap;">
                            {{ $item['occurred_at']->format('M d, Y h:i A') }}
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <div class="card admin-section-surface">
        <div class="section-header">
            <div>
                <h2 class="admin-section-title">Quotations</h2>
                <p class="admin-section-copy">Pre-inspection estimates and inspection-based quotations connected to this customer.</p>
            </div>
            <span class="badge badge-neutral">{{ $quotations->count() }} total</span>
        </div>

        @if ($quotations->isEmpty())
            <div class="info-box" style="margin-bottom:0;">No quotations yet.</div>
        @else
            <div class="stack">
                @foreach ($quotations as $quotation)
                    <div class="list-row">
                        <div style="flex:1; min-width:0;">
                            <strong>#{{ $quotation->id }} {{ ucfirst((string) $quotation->quotation_type) }} quotation</strong>
                            <div class="muted">
                                Status: {{ ucfirst((string) $quotation->status) }}
                                @if ($quotation->project_cost)
                                    · Project cost: PHP {{ number_format((float) $quotation->project_cost, 2) }}
                                @endif
                                @if ($quotation->discount_request_status)
                                    · Discount: {{ ucfirst((string) $quotation->discount_request_status) }}
                                @endif
                            </div>
                        </div>
                        <span class="badge badge-neutral">{{ $quotation->created_at->format('M d, Y') }}</span>
                        <a class="button-link secondary" href="{{ route('quotations.item-builder', ['quotation_id' => $quotation->id]) }}" style="padding:6px 14px; font-size:13px;">Open</a>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <div class="card admin-section-surface">
        <div class="section-header">
            <div>
                <h2 class="admin-section-title">Inspection Requests</h2>
                <p class="admin-section-copy">Site assessment requests submitted by this customer.</p>
            </div>
            <span class="badge badge-neutral">{{ $inspectionRequests->count() }} total</span>
        </div>

        @if ($inspectionRequests->isEmpty())
            <div class="info-box" style="margin-bottom:0;">No inspection requests yet.</div>
        @else
            <div class="stack">
                @foreach ($inspectionRequests as $inspectionRequest)
                    <div class="list-row">
                        <div style="flex:1; min-width:0;">
                            <strong>#{{ $inspectionRequest->id }} Inspection request</strong>
                            <div class="muted">
                                Status: {{ ucfirst((string) $inspectionRequest->status) }}
                                @if ($inspectionRequest->technician)
                                    · Technician: {{ $inspectionRequest->technician->name }}
                                @endif
                                @if ($inspectionRequest->date_needed)
                                    · Preferred date: {{ $inspectionRequest->date_needed }}
                                @endif
                            </div>
                            @if ($inspectionRequest->cancellation_note)
                                <div class="muted">Cancellation note: {{ $inspectionRequest->cancellation_note }}</div>
                            @endif
                        </div>
                        <span class="badge badge-neutral">{{ $inspectionRequest->created_at->format('M d, Y') }}</span>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <div class="card admin-section-surface">
        <div class="section-header">
            <div>
                <h2 class="admin-section-title">Service Requests</h2>
                <p class="admin-section-copy">Installation, maintenance, and other service requests from this customer.</p>
            </div>
            <span class="badge badge-neutral">{{ $serviceRequests->count() }} total</span>
        </div>

        @if ($serviceRequests->isEmpty())
            <div class="info-box" style="margin-bottom:0;">No service requests yet.</div>
        @else
            <div class="stack">
                @foreach ($serviceRequests as $serviceRequest)
                    <div class="list-row">
                        <div style="flex:1; min-width:0;">
                            <strong>#{{ $serviceRequest->id }} {{ $serviceRequest->service_request_option_label ?: $serviceRequest->request_type }}</strong>
                            <div class="muted">
                                Status: {{ ucfirst((string) $serviceRequest->status) }}
                                @if ($serviceRequest->technician)
                                    · Technician: {{ $serviceRequest->technician->name }}
                                @endif
                                @if ($serviceRequest->date_needed)
                                    · Preferred date: {{ $serviceRequest->date_needed->format('M d, Y') }}
                                @endif
                            </div>
                            @if ($serviceRequest->cancellation_note)
                                <div class="muted">Cancellation note: {{ $serviceRequest->cancellation_note }}</div>
                            @endif
                        </div>
                        <span class="badge badge-neutral">{{ $serviceRequest->created_at->format('M d, Y') }}</span>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <div class="card admin-section-surface">
        <div class="section-header">
            <div>
                <h2 class="admin-section-title">Feedback</h2>
                <p class="admin-section-copy">Customer testimonies and moderation status.</p>
            </div>
            <span class="badge badge-neutral">{{ $testimonies->count() }} total</span>
        </div>

        @if ($testimonies->isEmpty())
            <div class="info-box" style="margin-bottom:0;">No feedback submitted yet.</div>
        @else
            <div class="stack">
                @foreach ($testimonies as $testimony)
                    <div class="list-row">
                        <div style="flex:1; min-width:0;">
                            <strong>{{ $testimony->title ?: 'Untitled feedback' }}</strong>
                            <div class="muted">Rating: {{ $testimony->rating }}/5 · Status: {{ ucfirst((string) $testimony->status) }}</div>
                            <div class="muted">{{ \Illuminate\Support\Str::limit($testimony->message, 160) }}</div>
                        </div>
                        <span class="badge badge-neutral">{{ $testimony->created_at->format('M d, Y') }}</span>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <div class="card admin-section-surface">
        <div class="section-header">
            <div>
                <h2 class="admin-section-title">Chat</h2>
                <p class="admin-section-copy">Latest support conversation state and recent messages.</p>
            </div>
            @if ($chatConversation)
                <span class="badge badge-neutral">{{ ucfirst((string) $chatConversation->status) }}</span>
            @endif
        </div>

        @if (! $chatConversation)
            <div class="info-box" style="margin-bottom:0;">No chat conversation yet.</div>
        @else
            <div class="detail-grid" style="margin-bottom:16px;">
                <div class="detail-item">
                    <span class="detail-label">Last message</span>
                    <strong>{{ optional($chatConversation->last_message_at)->format('M d, Y h:i A') ?: 'Not available' }}</strong>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Admin assigned</span>
                    <strong>{{ $chatConversation->admin?->name ?: 'None' }}</strong>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Fallbacks</span>
                    <strong>{{ $chatConversation->bot_fallback_count }}</strong>
                </div>
            </div>

            @if ($chatMessages->isEmpty())
                <div class="info-box" style="margin-bottom:0;">No chat messages yet.</div>
            @else
                <div class="stack">
                    @foreach ($chatMessages as $message)
                        <div class="list-row">
                            <div style="flex:1; min-width:0;">
                                <strong>{{ ucfirst((string) $message->sender_type) }}</strong>
                                <div class="muted">{{ \Illuminate\Support\Str::limit($message->body, 180) }}</div>
                            </div>
                            <span class="badge badge-neutral">{{ $message->created_at->format('M d, h:i A') }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        @endif
    </div>

    <div class="card admin-section-surface">
        <div class="section-header">
            <div>
                <h2 class="admin-section-title">Account Audit</h2>
                <p class="admin-section-copy">Archive and restore decisions recorded for this customer.</p>
            </div>
            <span class="badge badge-neutral">{{ $archiveAudits->count() }} records</span>
        </div>

        @if ($archiveAudits->isEmpty())
            <div class="info-box" style="margin-bottom:0;">No archive or restore history.</div>
        @else
            <div class="stack">
                @foreach ($archiveAudits as $audit)
                    <div class="list-row">
                        <div style="flex:1; min-width:0;">
                            <strong>{{ ucfirst((string) $audit->action) }}</strong>
                            <div class="muted">
                                Reason: {{ $audit->reason ?: 'Not specified' }}
                                @if ($audit->performedBy)
                                    · By: {{ $audit->performedBy->name }}
                                @endif
                            </div>
                        </div>
                        <span class="badge badge-neutral">{{ $audit->created_at->format('M d, Y h:i A') }}</span>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

</div>
@endsection
