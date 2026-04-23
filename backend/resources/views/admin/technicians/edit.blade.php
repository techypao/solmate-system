@extends('layouts.app', ['title' => 'Edit Technician'])

@section('content')
<div class="admin-page-stack">

    {{-- HERO --}}
    <div class="card admin-hero-card">
        <div class="section-header">
            <div>
                <p class="admin-page-eyebrow">Admin Team Management</p>
                <h1 class="page-title">Edit Technician</h1>
                <p class="page-copy">Update the name or email for this technician account. Leave the password fields blank to keep the current password.</p>
            </div>
            <a class="button-link secondary" href="{{ route('admin.technicians.create') }}">Back to Technicians</a>
        </div>
    </div>

    {{-- EDIT FORM --}}
    <div class="card admin-section-surface">
        <div class="section-header">
            <div>
                <h2 class="admin-section-title">Account Details</h2>
                <p class="admin-section-copy">Editing account for <strong>{{ $technician->name }}</strong>.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.technicians.update', $technician) }}" class="form-grid">
            @csrf
            @method('PUT')

            <div class="form-grid two-columns">
                <div>
                    <label for="name">Full Name</label>
                    <input id="name" type="text" name="name"
                           value="{{ old('name', $technician->name) }}"
                           required autofocus>
                    <div class="field-error">@error('name') {{ $message }} @enderror</div>
                </div>
                <div>
                    <label for="email">Email Address</label>
                    <input id="email" type="email" name="email"
                           value="{{ old('email', $technician->email) }}"
                           required>
                    <div class="field-error">@error('email') {{ $message }} @enderror</div>
                </div>
            </div>

            <div class="form-grid two-columns">
                <div>
                    <label for="password">New Password <span class="muted">(optional)</span></label>
                    <input id="password" type="password" name="password"
                           placeholder="Leave blank to keep current password">
                    <div class="field-error">@error('password') {{ $message }} @enderror</div>
                </div>
                <div>
                    <label for="password_confirmation">Confirm New Password</label>
                    <input id="password_confirmation" type="password" name="password_confirmation"
                           placeholder="Repeat new password">
                    <div class="field-error"></div>
                </div>
            </div>

            <div class="actions">
                <button type="submit">Save Changes</button>
                <a class="button-link secondary" href="{{ route('admin.technicians.create') }}">Cancel</a>
                <button type="button" class="danger"
                        onclick="document.getElementById('remove-technician-form').submit()"
                        onmousedown="if(!confirm('Remove {{ addslashes($technician->name) }}? Their current assignments will be cleared.')) event.preventDefault()">
                    Remove Technician
                </button>
            </div>
        </form>

        <form id="remove-technician-form"
              method="POST"
              action="{{ route('admin.technicians.destroy', $technician) }}"
              style="display: none;">
            @csrf
            @method('DELETE')
        </form>
    </div>

</div>
@endsection
