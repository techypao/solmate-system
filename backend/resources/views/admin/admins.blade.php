@extends('layouts.app', ['title' => 'Staff Accounts'])

@section('content')
<div class="admin-page-stack">

    {{-- HERO --}}
    <div class="card admin-hero-card">
        <div class="section-header">
            <div>
                <p class="admin-page-eyebrow">Admin Team Management</p>
                <h1 class="page-title">Staff Accounts</h1>
                <p class="page-copy">Create Super Admin and staff accounts with role-based access to the admin workspace.</p>
            </div>
        </div>
        <div class="summary-grid">
            <div class="summary-card">
                <div class="summary-label">Total accounts</div>
                <div class="summary-value">{{ $admins->count() }}</div>
            </div>
            <div class="summary-card">
                <div class="summary-label">Super admins</div>
                <div class="summary-value">{{ $admins->filter->isSuperAdmin()->count() }}</div>
            </div>
        </div>
    </div>

    @error('admin')
        <div class="info-box" style="border-color:#fecaca; background:#fef2f2; color:#991b1b;">{{ $message }}</div>
    @enderror

    {{-- ADD FORM --}}
    <div class="card admin-section-surface">
        <div class="section-header">
            <div>
                <h2 class="admin-section-title">Add Staff Account</h2>
                <p class="admin-section-copy">Staff accounts are active immediately and use the same login page as admins.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.admins.store') }}" class="form-grid">
            @csrf

            <div class="form-grid two-columns">
                <div>
                    <label for="first_name">First Name</label>
                    <input id="first_name" type="text" name="first_name" value="{{ old('first_name') }}" required autofocus placeholder="e.g. Maria">
                    <div class="field-error">@error('first_name') {{ $message }} @enderror</div>
                </div>
                <div>
                    <label for="last_name">Last Name</label>
                    <input id="last_name" type="text" name="last_name" value="{{ old('last_name') }}" required placeholder="e.g. Santos">
                    <div class="field-error">@error('last_name') {{ $message }} @enderror</div>
                </div>
            </div>

            <div class="form-grid two-columns">
                <div>
                    <label for="email">Email Address</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required placeholder="admin@example.com">
                    <div class="field-error">@error('email') {{ $message }} @enderror</div>
                </div>
                <div>
                    <label for="contact_number">Contact Number</label>
                    <input id="contact_number" type="text" name="contact_number" value="{{ old('contact_number') }}" required inputmode="numeric" pattern="[0-9]{11}" maxlength="11" placeholder="09XXXXXXXXX">
                    <div class="muted" style="font-size: 12px; margin-top: 6px; line-height: 1.5;">Enter an 11-digit mobile number.</div>
                    <div class="field-error">@error('contact_number') {{ $message }} @enderror</div>
                </div>
            </div>

            <div class="form-grid two-columns">
                <div>
                    <label for="password">Password</label>
                    <input id="password" type="password" name="password" required placeholder="Min. 8 characters">
                    <div class="muted" style="font-size: 12px; margin-top: 6px; line-height: 1.5;">Password must be at least 8 characters, include 1 uppercase letter, and 1 special character.</div>
                    <div class="field-error">@error('password') {{ $message }} @enderror</div>
                </div>
                <div>
                    <label for="admin_role">Staff Role</label>
                    <select id="admin_role" name="admin_role" required>
                        @foreach ($adminRoleOptions as $roleValue => $roleLabel)
                            <option value="{{ $roleValue }}" @selected(old('admin_role', \App\Models\User::ADMIN_ROLE_OPERATIONS) === $roleValue)>
                                {{ $roleLabel }}
                            </option>
                        @endforeach
                    </select>
                    <div class="field-error">@error('admin_role') {{ $message }} @enderror</div>
                </div>
            </div>

            <div class="form-grid">
                <div>
                    <label for="password_confirmation">Confirm Password</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required>
                    <div class="field-error">@error('password_confirmation') {{ $message }} @enderror</div>
                </div>
            </div>

            <div class="actions">
                <button type="submit">Add Staff Account</button>
            </div>
        </form>
    </div>

    {{-- ADMIN LIST --}}
    <div class="card admin-section-surface">
        <div class="section-header">
            <div>
                <h2 class="admin-section-title">Existing Staff Accounts</h2>
                <p class="page-copy" style="margin-bottom:0;">Review staff roles and remove access when it is no longer needed.</p>
            </div>
            <span class="badge badge-neutral">{{ $admins->count() }} total</span>
        </div>

        @if ($admins->isEmpty())
            <div class="info-box" style="margin-bottom:0;">No staff accounts found.</div>
        @else
            <div class="stack">
                @foreach ($admins as $admin)
                    @php
                        $adminDisplayName = trim(implode(' ', array_filter([$admin->first_name, $admin->last_name]))) ?: $admin->name;
                        $isCurrentAdmin = auth()->id() === $admin->id;
                        $adminRoleLabel = $admin->adminRoleLabel();
                        $adminRoleColor = match ($admin->adminRole()) {
                            \App\Models\User::ADMIN_ROLE_SUPER_ADMIN => ['#e0f2fe', '#075985', '#7dd3fc'],
                            \App\Models\User::ADMIN_ROLE_OPERATIONS => ['#dcfce7', '#166534', '#86efac'],
                            \App\Models\User::ADMIN_ROLE_SUPPORT => ['#fef9c3', '#854d0e', '#fde047'],
                            \App\Models\User::ADMIN_ROLE_CONTENT => ['#f3e8ff', '#6b21a8', '#d8b4fe'],
                            default => ['#f3f4f6', '#374151', '#d1d5db'],
                        };
                    @endphp
                    <div class="list-row">
                        <div style="flex:1; min-width:0;">
                            <strong>{{ $adminDisplayName }}</strong>
                            <div class="muted">{{ $admin->email }}</div>
                            <div class="muted" style="font-size:12px;">Contact {{ $admin->contact_number ?: 'Not provided' }}</div>
                            <div class="muted" style="font-size:12px;">Joined {{ $admin->created_at->format('M d, Y') }}</div>
                        </div>
                        <span class="badge" style="white-space:nowrap; background:{{ $adminRoleColor[0] }}; color:{{ $adminRoleColor[1] }}; border:1px solid {{ $adminRoleColor[2] }};">
                            {{ $adminRoleLabel }}
                        </span>
                        @if ($isCurrentAdmin)
                            <span class="badge badge-neutral" style="white-space:nowrap;">You</span>
                        @endif
                        <div style="display:flex; gap:8px; flex-shrink:0;">
                            @if ($isCurrentAdmin)
                                <button type="button"
                                        class="button-link"
                                        disabled
                                        style="padding:6px 14px; font-size:13px; background:#f3f4f6; color:#6b7280; border:1.5px solid #d1d5db; border-radius:8px; cursor:not-allowed; font-weight:600;">
                                    Current
                                </button>
                            @else
                                <form method="POST"
                                      action="{{ route('admin.admins.destroy', $admin) }}"
                                      onsubmit="return confirm('Delete staff account {{ addslashes($adminDisplayName) }}? This will permanently remove their login access.')">
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

</div>
@endsection
