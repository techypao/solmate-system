@extends('layouts.app', ['title' => 'Admin Profile'])

@section('content')
    <div class="card">
        <div class="section-header">
            <div>
                <h1 class="page-title">Admin Profile</h1>
                <p class="page-copy">Manage your admin account details and keep your website login credentials up to date.</p>
            </div>
            <a class="button-link secondary" href="{{ route('admin.quotation-settings') }}">Back to Admin Tools</a>
        </div>

        <div class="summary-grid">
            <div class="summary-card">
                <div class="summary-label">Account name</div>
                <div class="summary-value" style="font-size: 22px; line-height: 1.2;">{{ $user->name }}</div>
            </div>
            <div class="summary-card">
                <div class="summary-label">Email</div>
                <div class="muted" style="font-size: 16px; color: #102a43;">{{ $user->email }}</div>
            </div>
            <div class="summary-card">
                <div class="summary-label">Role</div>
                <div class="muted" style="font-size: 16px; color: #102a43;">{{ \Illuminate\Support\Str::headline($user->role) }}</div>
            </div>
            <div class="summary-card">
                <div class="summary-label">Session</div>
                <div class="muted" style="font-size: 16px; color: #102a43;">Signed in</div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="section-header">
            <div>
                <h2 style="margin: 0 0 6px;">Update account information</h2>
                <p class="page-copy" style="margin-bottom: 0;">Edit your admin name and email. Changes apply to your own website account only.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.profile.update') }}" class="form-grid">
            @csrf
            @method('PUT')

            <div class="form-grid two-columns">
                <div>
                    <label for="name">Name</label>
                    <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}" required autofocus>
                    <div class="field-error">@error('name') {{ $message }} @enderror</div>
                </div>

                <div>
                    <label for="email">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" required>
                    <div class="field-error">@error('email') {{ $message }} @enderror</div>
                </div>
            </div>

            <div class="actions">
                <button type="submit">Save profile</button>
            </div>
        </form>
    </div>

    <div class="card">
        <div class="section-header">
            <div>
                <h2 style="margin: 0 0 6px;">Change password</h2>
                <p class="page-copy" style="margin-bottom: 0;">Enter your current password before setting a new one for your admin web account.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.profile.password.update') }}" class="form-grid">
            @csrf
            @method('PUT')

            <div class="form-grid two-columns">
                <div>
                    <label for="current_password">Current password</label>
                    <input id="current_password" type="password" name="current_password" required>
                    <div class="field-error">@error('current_password') {{ $message }} @enderror</div>
                </div>

                <div>
                    <label for="new_password">New password</label>
                    <input id="new_password" type="password" name="new_password" required>
                    <div class="field-error">@error('new_password') {{ $message }} @enderror</div>
                </div>
            </div>

            <div class="form-grid two-columns">
                <div>
                    <label for="new_password_confirmation">Confirm new password</label>
                    <input id="new_password_confirmation" type="password" name="new_password_confirmation" required>
                    <div class="field-error"></div>
                </div>
            </div>

            <div class="actions">
                <button type="submit">Update password</button>
            </div>
        </form>
    </div>
@endsection
