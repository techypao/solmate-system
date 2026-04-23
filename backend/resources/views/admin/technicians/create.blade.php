@extends('layouts.app', ['title' => 'Manage Technicians'])

@section('content')
<div class="admin-page-stack">

    {{-- HERO --}}
    <div class="card admin-hero-card">
        <div class="section-header">
            <div>
                <p class="admin-page-eyebrow">Admin Team Management</p>
                <h1 class="page-title">Manage Technicians</h1>
                <p class="page-copy">Create, edit, and remove technician accounts. Removing a technician clears their existing request assignments automatically.</p>
            </div>
            <a class="button-link secondary" href="{{ route('admin.request-assignments') }}">Open Assignments</a>
        </div>
        <div class="summary-grid">
            <div class="summary-card">
                <div class="summary-label">Total technicians</div>
                <div class="summary-value">{{ $technicians->count() }}</div>
            </div>
            <div class="summary-card">
                <div class="summary-label">Access</div>
                <div class="muted">Admin-only page</div>
            </div>
        </div>
    </div>

    {{-- ADD FORM --}}
    <div class="card admin-section-surface">
        <div class="section-header">
            <div>
                <h2 class="admin-section-title">Add New Technician</h2>
                <p class="admin-section-copy">Fill in the fields to create a new technician login account.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.technicians.store') }}" class="form-grid">
            @csrf

            <div class="form-grid two-columns">
                <div>
                    <label for="name">Full Name</label>
                    <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus placeholder="e.g. Juan dela Cruz">
                    <div class="field-error">@error('name') {{ $message }} @enderror</div>
                </div>
                <div>
                    <label for="email">Email Address</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required placeholder="technician@example.com">
                    <div class="field-error">@error('email') {{ $message }} @enderror</div>
                </div>
            </div>

            <div class="form-grid two-columns">
                <div>
                    <label for="password">Password</label>
                    <input id="password" type="password" name="password" required placeholder="Min. 8 characters">
                    <div class="field-error">@error('password') {{ $message }} @enderror</div>
                </div>
                <div>
                    <label for="password_confirmation">Confirm Password</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required>
                    <div class="field-error"></div>
                </div>
            </div>

            <div class="actions">
                <button type="submit">Add Technician</button>
            </div>
        </form>
    </div>

    {{-- TECHNICIAN LIST --}}
    <div class="card admin-section-surface">
        <div class="section-header">
            <div>
                <h2 class="admin-section-title">Existing Technicians</h2>
                <p class="page-copy" style="margin-bottom:0;">Use the actions on each row to edit details or remove an account.</p>
            </div>
            <span class="badge badge-neutral">{{ $technicians->count() }} total</span>
        </div>

        @if ($technicians->isEmpty())
            <div class="info-box" style="margin-bottom:0;">No technician accounts created yet.</div>
        @else
            <div class="stack">
                @foreach ($technicians as $tech)
                    <div class="list-row">
                        <div style="flex:1; min-width:0;">
                            <strong>{{ $tech->name }}</strong>
                            <div class="muted">{{ $tech->email }}</div>
                            <div class="muted" style="font-size:12px;">Joined {{ $tech->created_at->format('M d, Y') }}</div>
                        </div>
                        <span class="badge badge-neutral" style="white-space:nowrap;">Technician</span>
                        <div style="display:flex; gap:8px; flex-shrink:0;">
                            <a href="{{ route('admin.technicians.edit', $tech) }}"
                               class="button-link secondary"
                               style="padding:6px 14px; font-size:13px;">Edit</a>

                            <form method="POST"
                                  action="{{ route('admin.technicians.destroy', $tech) }}"
                                  onsubmit="return confirm('Remove technician {{ addslashes($tech->name) }}? This will clear their current assignments but not delete past records.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="button-link"
                                        style="padding:6px 14px; font-size:13px; background:#fee2e2; color:#dc2626; border:1.5px solid #fca5a5; border-radius:8px; cursor:pointer; font-weight:600; text-decoration:none; display:inline-flex; align-items:center;">
                                    Remove
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

