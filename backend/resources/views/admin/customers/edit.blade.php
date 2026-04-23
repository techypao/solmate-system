@extends('layouts.app', ['title' => 'Edit Customer'])

@section('content')
<div class="admin-page-stack">

    {{-- HERO --}}
    <div class="card admin-hero-card">
        <div class="section-header">
            <div>
                <p class="admin-page-eyebrow">Admin Customer Management</p>
                <h1 class="page-title">Edit Customer</h1>
                <p class="page-copy">Correct the name or email address for this customer account. The customer's service history and requests are not affected.</p>
            </div>
            <a class="button-link secondary" href="{{ route('admin.customers') }}">Back to Customers</a>
        </div>
    </div>

    {{-- EDIT FORM --}}
    <div class="card admin-section-surface">
        <div class="section-header">
            <div>
                <h2 class="admin-section-title">Account Details</h2>
                <p class="admin-section-copy">Editing account for <strong>{{ $customer->name }}</strong>. Joined {{ $customer->created_at->format('M d, Y') }}.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.customers.update', $customer) }}" class="form-grid">
            @csrf
            @method('PUT')

            <div class="form-grid two-columns">
                <div>
                    <label for="name">Full Name</label>
                    <input id="name" type="text" name="name"
                           value="{{ old('name', $customer->name) }}"
                           required autofocus>
                    <div class="field-error">@error('name') {{ $message }} @enderror</div>
                </div>
                <div>
                    <label for="email">Email Address</label>
                    <input id="email" type="email" name="email"
                           value="{{ old('email', $customer->email) }}"
                           required>
                    <div class="field-error">@error('email') {{ $message }} @enderror</div>
                </div>
            </div>

            <div class="actions">
                <button type="submit">Save Changes</button>
                <a class="button-link secondary" href="{{ route('admin.customers') }}">Cancel</a>
            </div>
        </form>
    </div>

    {{-- INFO BOX --}}
    <div class="card admin-section-surface">
        <h2 class="admin-section-title" style="margin-bottom: 10px;">Why no password reset?</h2>
        <p class="muted" style="margin: 0;">Customer passwords are managed by the customer through self-service account settings. Admins can correct name and email only. If a customer is locked out, they should use the password reset flow.</p>
    </div>

</div>
@endsection
