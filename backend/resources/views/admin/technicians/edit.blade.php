@extends('layouts.app', ['title' => 'Edit Technician'])

@section('content')
<div class="admin-page-stack">
    @php
        $technicianDisplayName = trim(implode(' ', array_filter([$technician->first_name, $technician->last_name]))) ?: $technician->name;
    @endphp

    {{-- HERO --}}
    <div class="card admin-hero-card">
        <div class="section-header">
            <div>
                <p class="admin-page-eyebrow">Admin Team Management</p>
                <h1 class="page-title">Edit Technician</h1>
                <p class="page-copy">Update the technician profile and account details. Leave the password fields blank to keep the current password.</p>
            </div>
            <a class="button-link secondary" href="{{ route('admin.technicians.create') }}">Back to Technicians</a>
        </div>
    </div>

    {{-- EDIT FORM --}}
    <div class="card admin-section-surface">
        <div class="section-header">
            <div>
                <h2 class="admin-section-title">Account Details</h2>
                <p class="admin-section-copy">Editing account for <strong>{{ $technicianDisplayName }}</strong>.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.technicians.update', $technician) }}" class="form-grid">
            @csrf
            @method('PUT')

            <div class="form-grid two-columns">
                <div>
                    <label for="first_name">First Name</label>
                    <input id="first_name" type="text" name="first_name"
                           value="{{ old('first_name', $technician->first_name) }}"
                           required autofocus>
                    <div class="field-error">@error('first_name') {{ $message }} @enderror</div>
                </div>
                <div>
                    <label for="last_name">Last Name</label>
                    <input id="last_name" type="text" name="last_name"
                           value="{{ old('last_name', $technician->last_name) }}"
                           required>
                    <div class="field-error">@error('last_name') {{ $message }} @enderror</div>
                </div>
            </div>

            <div class="form-grid two-columns">
                <div>
                    <label for="email">Email Address</label>
                    <input id="email" type="email" name="email"
                           value="{{ old('email', $technician->email) }}"
                           required>
                    <div class="field-error">@error('email') {{ $message }} @enderror</div>
                </div>
                <div>
                    <label for="contact_number">Contact Number</label>
                    <input id="contact_number" type="text" name="contact_number"
                           value="{{ old('contact_number', $technician->contact_number) }}"
                           required inputmode="numeric" pattern="[0-9]{11}" maxlength="11" placeholder="09XXXXXXXXX">
                    <div class="muted" style="font-size: 12px; margin-top: 6px; line-height: 1.5;">Enter an 11-digit mobile number.</div>
                    <div class="field-error">@error('contact_number') {{ $message }} @enderror</div>
                </div>
            </div>

            <div class="form-grid two-columns">
                <div>
                    <label for="password">New Password <span class="muted">(optional)</span></label>
                    <input id="password" type="password" name="password"
                           placeholder="Leave blank to keep current password">
                    <div class="muted" style="font-size: 12px; margin-top: 6px; line-height: 1.5;">Password must be at least 8 characters, include 1 uppercase letter, and 1 special character.</div>
                    <div class="field-error">@error('password') {{ $message }} @enderror</div>
                </div>
            </div>

            <div class="form-grid">
                <div>
                    <label for="password_confirmation">Confirm New Password</label>
                    <input id="password_confirmation" type="password" name="password_confirmation"
                           placeholder="Repeat new password">
                    <div class="field-error">@error('password_confirmation') {{ $message }} @enderror</div>
                </div>
            </div>

            <div class="actions">
                <button type="submit">Save Changes</button>
                <a class="button-link secondary" href="{{ route('admin.technicians.create') }}">Cancel</a>
                <button type="button" class="danger"
                        onclick="document.getElementById('remove-technician-form').submit()"
                        onmousedown="if(!confirm('Remove {{ addslashes($technicianDisplayName) }}? Their current assignments will be cleared.')) event.preventDefault()">
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
